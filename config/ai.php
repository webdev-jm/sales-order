<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Features
    |--------------------------------------------------------------------------
    |
    | Turns the AI features on or off for this installation. AI also stays
    | off while no provider below has an API key, so a fresh checkout
    | never calls out until a key has been added to the .env file.
    |
    */

    'enabled' => (bool) env('AI_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Provider and Failover
    |--------------------------------------------------------------------------
    |
    | "provider" answers every request. When it cannot (unreachable, busy,
    | out of quota, refusing the request or missing its key), the request
    | goes to each provider in "failover" in turn (comma-separated).
    |
    | Providers: gemini (alias google), anthropic (alias claude) and openai
    | (alias openai-compatible). Example: AI_PROVIDER=gemini, AI_FAILOVER=claude
    |
    | Once the AI Settings page has been saved, its order, models, effort
    | and prices replace these values. The API keys always stay here.
    |
    */

    'provider' => env('AI_PROVIDER', 'gemini'),

    'failover' => env('AI_FAILOVER', ''),

    /*
    |--------------------------------------------------------------------------
    | Laravel AI SDK Default
    |--------------------------------------------------------------------------
    |
    | This file is shared with the laravel/ai package. The application never
    | relies on its default: App\Services\Ai\AiClient builds the providers
    | from the blocks below (and the AI Settings page) for every call.
    |
    */

    'default' => env('AI_PROVIDER', 'gemini'),

    /*
    |--------------------------------------------------------------------------
    | AI Providers
    |--------------------------------------------------------------------------
    |
    | Each block is read by both the application and laravel/ai ("driver",
    | "key" and "url" are laravel/ai's names). Gemini and Claude are called
    | through laravel/ai; use the same Gemini key as the PAF application.
    |
    */

    'providers' => [
        'gemini' => [
            'driver'  => 'gemini',
            'key'     => env('GEMINI_API_KEY'),
            'url'     => env('GEMINI_URL', 'https://generativelanguage.googleapis.com/v1beta/'),
            'model'   => env('GEMINI_MODEL', 'gemini-3.5-flash'),
            'timeout' => (int) env('GEMINI_TIMEOUT', 180),

            // How much the model thinks before it answers. Thinking tokens are billed as
            // output, and copying a PO needs little of it. Leave empty for the model's default.
            'effort' => env('GEMINI_THINKING_LEVEL', 'low'),

            // Pause before the one retry after an overload (5xx), rate limit (429) or timeout.
            'retry_ms' => (int) env('GEMINI_RETRY_MS', 2500),
        ],

        'anthropic' => [
            'driver'  => 'anthropic',
            'key'     => env('ANTHROPIC_API_KEY'),
            'url'     => env('ANTHROPIC_URL', 'https://api.anthropic.com/v1'),
            'model'   => env('ANTHROPIC_MODEL', 'claude-haiku-5-5'),
            'timeout' => (int) env('ANTHROPIC_TIMEOUT', 180),

            // Claude's effort level (low, medium, high, xhigh, max): how much it thinks and
            // spends. Copying a PO needs little. Leave empty for the model's default.
            'effort' => env('ANTHROPIC_EFFORT', 'low'),

            // Retries after an overload, rate limit or timeout, and the pause before each.
            'max_retries' => (int) env('ANTHROPIC_MAX_RETRIES', 1),
            'retry_ms'    => (int) env('ANTHROPIC_RETRY_MS', 500),
        ],

        // Any server with the OpenAI Chat Completions API: OpenAI itself, OpenRouter, Groq,
        // DeepSeek or a local Ollama (any key works there). Reading PDFs needs a model and server
        // that accept PDF files, such as OpenAI or OpenRouter. PDFs are sent by the application's
        // own Chat Completions request, because laravel/ai's compatible driver only attaches images.
        'openai' => [
            'driver'  => 'openai-compatible',
            'key'     => env('OPENAI_API_KEY'),
            'url'     => env('OPENAI_BASE_URL', 'https://api.openai.com/v1/'),
            'model'   => env('OPENAI_MODEL', 'gpt-5-mini'),
            'timeout' => (int) env('OPENAI_TIMEOUT', 180),

            // reasoning_effort for reasoning models (minimal, low, medium, high). Leave empty for
            // models that do not reason, which reject the setting.
            'effort' => env('OPENAI_REASONING_EFFORT', 'low'),

            'retry_ms' => (int) env('OPENAI_RETRY_MS', 2500),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Model Lists
    |--------------------------------------------------------------------------
    |
    | Minutes the AI Settings page keeps each provider's model list before
    | asking the provider again. Listing models costs no tokens.
    |
    */

    'model_list_cache_minutes' => 60,

    /*
    |--------------------------------------------------------------------------
    | PO Scanning
    |--------------------------------------------------------------------------
    |
    | Limits for scanning account PO PDFs. A PDF is sent inline; Gemini caps
    | an inline request at 20 MB after base64 encoding and Claude at 32 MB,
    | so one file must stay well under that. 16,000 output tokens covers a
    | few hundred PO lines and keeps a non-streaming call well inside its
    | timeout.
    |
    | A PDF read once is kept for "cache_days", so scanning the same file for
    | the same account and format again costs no tokens. 0 turns this off.
    |
    */

    'po_scan' => [
        'max_file_kb' => 10240,
        'max_files' => 10,
        'max_output_tokens' => 16000,
        'cache_days' => (int) env('AI_SCAN_CACHE_DAYS', 30),
    ],

];
