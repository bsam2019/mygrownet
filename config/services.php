<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'brevo' => [
        'api_key' => env('BREVO_API_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Social Media API Credentials (BizBoost)
    |--------------------------------------------------------------------------
    */

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'api_version' => env('FACEBOOK_API_VERSION', 'v18.0'),
    ],

    'tiktok' => [
        'client_key' => env('TIKTOK_CLIENT_KEY'),
        'client_secret' => env('TIKTOK_CLIENT_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Firebase Cloud Messaging
    |--------------------------------------------------------------------------
    */

    'firebase' => [
        'project_id' => env('FIREBASE_PROJECT_ID'),
        'credentials' => env('FIREBASE_CREDENTIALS', storage_path('app/firebase-credentials.json')),
        'server_key' => env('FIREBASE_SERVER_KEY'), // Legacy fallback
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Gateway Configuration
    |--------------------------------------------------------------------------
    */

    'payment' => [
        'default_gateway' => env('PAYMENT_DEFAULT_GATEWAY', 'moneyunify'),
    ],

    'moneyunify' => [
        'base_url' => env('MONEYUNIFY_BASE_URL', 'https://api.moneyunify.com/v2'),
        'muid' => env('MONEYUNIFY_MUID'), // MoneyUnify ID - get from dashboard
        'webhook_secret' => env('MONEYUNIFY_WEBHOOK_SECRET'),
        'mock_mode' => env('MONEYUNIFY_MOCK_MODE', false), // Enable for testing without real API
    ],

    'pawapay' => [
        'base_url' => env('PAWAPAY_BASE_URL', 'https://api.pawapay.io'),
        'api_token' => env('PAWAPAY_API_TOKEN'),
        'webhook_secret' => env('PAWAPAY_WEBHOOK_SECRET'),
        'mock_mode' => env('PAWAPAY_MOCK_MODE', false), // Enable for testing without real API
    ],

    'nowpayments' => [
        'api_key' => env('NOWPAYMENTS_API_KEY'),
        'ipn_secret' => env('NOWPAYMENTS_IPN_SECRET'), // For webhook signature verification
        'sandbox' => env('NOWPAYMENTS_SANDBOX', false), // Use sandbox for testing
    ],

    'exchangerate' => [
        'api_key' => env('EXCHANGERATE_API_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Configuration (GrowBuilder AI Features)
    |--------------------------------------------------------------------------
    | Supports multiple providers:
    | - nvidia: DeepSeek models via NVIDIA API (fast, quality results)
    | - openai: OpenAI GPT models (paid)
    | - gemini: Google Gemini (FREE tier available)
    | - ollama: Local Ollama server (FREE, runs locally)
    |
    | To use NVIDIA/DeepSeek (recommended):
    | 1. Get your NVIDIA API key
    | 2. Set AI_PROVIDER=nvidia and AI_NVIDIA_KEY in .env
    | 3. Users can select between deepseek-v4-pro and deepseek-v4-flash
    */

    'ai' => [
        'provider' => env('AI_PROVIDER', 'nvidia'), // nvidia, openai, gemini, ollama

        // NVIDIA (DeepSeek via NVIDIA API - recommended)
        'nvidia_key' => env('AI_NVIDIA_KEY'),
        'nvidia_model' => env('AI_NVIDIA_MODEL', 'deepseek-ai/deepseek-v4-flash'),
        'nvidia_url' => 'https://integrate.api.nvidia.com/v1',

        // OpenAI (paid)
        'openai_key' => env('OPENAI_API_KEY'),
        'openai_model' => env('OPENAI_MODEL', 'gpt-3.5-turbo'),
        'openai_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),

        // Google Gemini (FREE tier)
        'gemini_key' => env('AI_GEMINI_KEY'),
        'gemini_model' => env('AI_GEMINI_MODEL', 'gemini-pro'),

        // Ollama (FREE - local)
        'ollama_url' => env('AI_OLLAMA_URL', 'http://localhost:11434/api'),
        'ollama_model' => env('AI_OLLAMA_MODEL', 'llama3'),
    ],

    // Legacy OpenAI config (for backward compatibility)
    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-3.5-turbo'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
    ],

];
