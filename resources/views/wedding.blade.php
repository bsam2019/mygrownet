<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        <!-- Wedding specific meta -->
        <meta name="theme-color" content="#6b7280">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">

        <!-- Open Graph / Facebook (for WhatsApp, Facebook, etc.) -->
        @if(isset($page['props']['ogMeta']))
        <meta property="og:type" content="{{ $page['props']['ogMeta']['type'] ?? 'website' }}" />
        <meta property="og:url" content="{{ $page['props']['ogMeta']['url'] ?? url()->current() }}" />
        <meta property="og:title" content="{{ $page['props']['ogMeta']['title'] ?? 'Wedding Invitation' }}" />
        <meta property="og:description" content="{{ $page['props']['ogMeta']['description'] ?? 'You are invited to celebrate our wedding!' }}" />
        <meta property="og:image" content="{{ $page['props']['ogMeta']['image'] ?? '' }}" />
        <meta property="og:image:width" content="1200" />
        <meta property="og:image:height" content="630" />
        <meta property="og:site_name" content="Wedding Invitation" />
        
        <!-- Twitter -->
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:url" content="{{ $page['props']['ogMeta']['url'] ?? url()->current() }}" />
        <meta name="twitter:title" content="{{ $page['props']['ogMeta']['title'] ?? 'Wedding Invitation' }}" />
        <meta name="twitter:description" content="{{ $page['props']['ogMeta']['description'] ?? 'You are invited to celebrate our wedding!' }}" />
        <meta name="twitter:image" content="{{ $page['props']['ogMeta']['image'] ?? '' }}" />
        
        <!-- General description -->
        <meta name="description" content="{{ $page['props']['ogMeta']['description'] ?? 'You are invited to celebrate our wedding!' }}" />
        @endif

        {{-- Inline style for clean white background and prevent flash --}}
        <style>
            html, body {
                background-color: #ffffff;
            }
            /* Hide content until Vue mounts to prevent flash */
            #app:empty {
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                background-color: #ffffff;
            }
            #app:empty::after {
                content: '';
                width: 24px;
                height: 24px;
                border: 2px solid #e5e7eb;
                border-top-color: #6b7280;
                border-radius: 50%;
                animation: spin 0.8s linear infinite;
            }
            @keyframes spin {
                to { transform: rotate(360deg); }
            }
        </style>

        <title inertia>Wedding</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @routes
        @vite(['resources/js/app.ts'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased bg-white">
        @inertia
    </body>
</html>
