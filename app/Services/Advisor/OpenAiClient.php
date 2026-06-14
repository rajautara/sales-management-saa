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
}
