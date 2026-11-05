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
    | Only the API keys come from the .env file. Everything else about the
    | providers is set on the AI Settings page.
    |
    */

    'enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | Provider and Failover
    |--------------------------------------------------------------------------
    |
    | The defaults until the AI Settings page is saved: "provider" is the
    | main provider, and "failover" lists the backups tried in turn when
    | it cannot answer (unreachable, busy, out of quota, refusing the
    | request or missing its key), comma-separated.
    |
    | Providers: gemini, anthropic, openai, groq and qwen. Once the AI
    | Settings page has been saved, its order, models, effort, server
    | addresses and prices replace these defaults.
    |
    */

    'provider' => 'gemini',

    'failover' => '',

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

    'default' => 'gemini',

    /*
    |--------------------------------------------------------------------------
    | AI Providers
    |--------------------------------------------------------------------------
    |
    | Each block is read by both the application and laravel/ai ("driver",
    | "key" and "url" are laravel/ai's names). Gemini and Claude are called
    | through laravel/ai; use the same Gemini key as the PAF application.
    |
    | The model, effort and (for OpenAI, Groq and Qwen) the server address
    | below are defaults; the AI Settings page overrides them.
    |
    */

    'providers' => [
        'gemini' => [
            'driver'  => 'gemini',
            // Prompt caching: tokens read from the provider's cache, and written to it, cost this share of the
            // input price (see the model's price page).
            'cache_read_price'  => 0.1,
            'cache_write_price' => 1.0,
            'key'     => env('GEMINI_API_KEY'),
            'url'     => 'https://generativelanguage.googleapis.com/v1beta/',
            'model'   => 'gemini-3.5-flash',
            'timeout' => 180,

            // How much the model thinks before it answers. Thinking tokens are billed as
            // output, and copying a PO needs little of it. Leave empty for the model's default.
            'effort' => 'low',

            // Pause before the one retry after an overload (5xx), rate limit (429) or timeout.
            'retry_ms' => 2500,
        ],

        'anthropic' => [
            'driver'  => 'anthropic',
            // Prompt caching: tokens read from the provider's cache, and written to it, cost this share of the
            // input price (see the model's price page).
            'cache_read_price'  => 0.1,
            'cache_write_price' => 1.25,
            'key'     => env('ANTHROPIC_API_KEY'),
            'url'     => 'https://api.anthropic.com/v1',
            'model'   => 'claude-haiku-5-5',
            'timeout' => 180,

            // Claude's effort level (low, medium, high, xhigh, max): how much it thinks and
            // spends. Copying a PO needs little. Leave empty for the model's default.
            'effort' => 'low',

            // Retries after an overload, rate limit or timeout, and the pause before each.
            'max_retries' => 1,
            'retry_ms'    => 500,
        ],

        // Any server with the OpenAI Chat Completions API: OpenAI itself, OpenRouter, DeepSeek
        // or a local Ollama (any key works there). Reading PDFs needs a model and server
        // that accept PDF files, such as OpenAI or OpenRouter. PDFs are sent by the application's
        // own Chat Completions request, because laravel/ai's compatible driver only attaches images.
        'openai' => [
            'driver'  => 'openai-compatible',
            // Prompt caching: tokens read from the provider's cache, and written to it, cost this share of the
            // input price (see the model's price page).
            'cache_read_price'  => 0.1,
            'cache_write_price' => 1.0,
            'key'     => env('OPENAI_API_KEY'),
            'url'     => 'https://api.openai.com/v1/',
            'model'   => 'gpt-5-mini',
            'timeout' => 180,

            // reasoning_effort for reasoning models (minimal, low, medium, high). Leave empty for
            // models that do not reason, which reject the setting.
            'effort' => 'low',

            'retry_ms' => 2500,
        ],

        // Groq runs open models fast. Its API takes text and, on vision models, images, but no
        // PDF files, so PO scans skip it and go to the next provider that reads PDFs.
        'groq' => [
            'driver'  => 'groq',
            // Prompt caching: tokens read from the provider's cache, and written to it, cost this share of the
            // input price (see the model's price page).
            'cache_read_price'  => 0.5,
            'cache_write_price' => 1.0,
            'key'     => env('GROQ_API_KEY'),
            'url'     => 'https://api.groq.com/openai/v1/',
            'model'   => 'openai/gpt-oss-120b',
            'timeout' => 120,

            // reasoning_effort for reasoning models such as gpt-oss. Leave empty for models that
            // do not reason, which reject the setting.
            'effort' => 'low',

            'retry_ms' => 2500,
        ],

        // Alibaba Cloud Model Studio (DashScope), through its OpenAI-compatible mode. The address
        // depends on the region the key was made in (international: dashscope-intl, China:
        // dashscope). Like Groq it reads text and images but no PDF files.
        'qwen' => [
            'driver'  => 'openai-compatible',
            // Prompt caching: tokens read from the provider's cache, and written to it, cost this share of the
            // input price (see the model's price page).
            'cache_read_price'  => 0.2,
            'cache_write_price' => 1.0,
            'key'     => env('QWEN_API_KEY'),
            'url'     => 'https://dashscope-intl.aliyuncs.com/compatible-mode/v1/',
            'model'   => 'qwen-plus',
            'timeout' => 120,
            'effort'  => null,

            'retry_ms' => 2500,
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
    | Only providers that read PDF files are asked to scan.
    |
    */

    'po_scan' => [
        'max_file_kb' => 10240,
        'max_files' => 10,
        'max_output_tokens' => 16000,
        'cache_days' => 30,
    ],

];
