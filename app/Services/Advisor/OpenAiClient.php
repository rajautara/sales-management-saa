<?php

namespace App\Services\Advisor;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Thin wrapper around an OpenAI-compatible Chat Completions endpoint.
 *
 * Works with OpenAI, OpenRouter, Azure, Groq, Together, or a local
 * Ollama / LM Studio / vLLM server — anything that speaks the
 * POST {base_url}/chat/completions format.
 */
class OpenAiClient
{
    /**
     * Send a chat completion request and return the assistant message text.
     *
     * @param  array<int, array<string, mixed>>  $messages
     * @param  array<string, mixed>  $options
     */
    public function chat(array $messages, array $options = []): string
    {
        $baseUrl = config('ai.base_url');
        $apiKey = config('ai.api_key');

        if (blank($apiKey)) {
            throw new RuntimeException('AI advisor is not configured. Set AI_API_KEY in your environment.');
        }

        $payload = array_merge([
            'model' => config('ai.model'),
            'messages' => $messages,
            'max_tokens' => (int) config('ai.max_tokens'),
            'temperature' => (float) config('ai.temperature'),
        ], $options);

        $response = Http::withToken($apiKey)
            ->timeout((int) config('ai.timeout'))
            ->acceptJson()
            ->asJson()
            ->post($baseUrl.'/chat/completions', $payload);

        if ($response->failed()) {
            $status = $response->status();
            $message = $response->json('error.message') ?? $response->body();

            throw new RuntimeException("AI request failed (HTTP {$status}): {$message}");
        }

        $content = $response->json('choices.0.message.content');

        if (! is_string($content) || $content === '') {
            throw new RuntimeException('AI returned an empty response.');
        }

        return trim($content);
    }

    /**
     * Stream a chat completion, invoking $onDelta for each text chunk, and
     * return the full assembled text. Uses the OpenAI SSE format.
     *
     * @param  array<int, array<string, mixed>>  $messages
     * @param  callable(string): void  $onDelta
     * @param  array<string, mixed>  $options
     */
    public function streamChat(array $messages, callable $onDelta, array $options = []): string
    {
        $baseUrl = config('ai.base_url');
        $apiKey = config('ai.api_key');

        if (blank($apiKey)) {
            throw new RuntimeException('AI advisor is not configured. Set AI_API_KEY in your environment.');
        }

        $payload = array_merge([
            'model' => config('ai.model'),
            'messages' => $messages,
            'max_tokens' => (int) config('ai.max_tokens'),
            'temperature' => (float) config('ai.temperature'),
            'stream' => true,
        ], $options);

        $response = Http::withToken($apiKey)
            ->timeout((int) config('ai.timeout'))
            ->withOptions(['stream' => true])
            ->post($baseUrl.'/chat/completions', $payload);

        if ($response->failed()) {
            throw new RuntimeException('AI request failed (HTTP '.$response->status().').');
        }

        $body = $response->toPsrResponse()->getBody();
        $buffer = '';
        $full = '';

        while (! $body->eof()) {
            $buffer .= $body->read(1024);

            while (($newline = strpos($buffer, "\n")) !== false) {
                $line = trim(substr($buffer, 0, $newline));
                $buffer = substr($buffer, $newline + 1);

                if ($line === '' || ! str_starts_with($line, 'data:')) {
                    continue;
                }

                $data = trim(substr($line, 5));

                if ($data === '[DONE]') {
                    return trim($full);
                }

                $delta = json_decode($data, true)['choices'][0]['delta']['content'] ?? null;

                if (is_string($delta) && $delta !== '') {
                    $full .= $delta;
                    $onDelta($delta);
                }
            }
        }

        return trim($full);
    }
}
