<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Business Advisor
    |--------------------------------------------------------------------------
    |
    | The advisor talks to any OpenAI-compatible Chat Completions endpoint
    | (OpenAI, OpenRouter, Azure, Groq, Together, or a local Ollama / LM Studio
    | / vLLM server). Only aggregated, anonymised financial figures are sent —
    | never customer names or other PII.
    |
    */

    'enabled' => (bool) env('AI_ENABLED', false),

    // OpenAI-compatible base URL (no trailing slash). The client appends
    // "/chat/completions".
    'base_url' => rtrim((string) env('AI_BASE_URL', 'https://api.openai.com/v1'), '/'),

    'api_key' => env('AI_API_KEY'),

    'model' => env('AI_MODEL', 'gpt-4o-mini'),

    'timeout' => (int) env('AI_TIMEOUT', 60),

    'max_tokens' => (int) env('AI_MAX_TOKENS', 1500),

    'temperature' => (float) env('AI_TEMPERATURE', 0.3),

    // Optional function-calling drill-down for providers that support tools.
    // Off by default — the aggregated snapshot is injected into the prompt.
    'use_tools' => (bool) env('AI_USE_TOOLS', false),

];
