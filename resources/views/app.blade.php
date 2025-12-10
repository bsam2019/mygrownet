<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        @php
            // Detect which module we're in
            $isBizBoost = request()->is('bizboost*');
            $isGrowBiz = request()->is('growbiz*');
            $isGrowFinance = request()->is('growfinance*');
            
            // Module-specific PWA configuration
            if ($isBizBoost) {
                $themeColor = '#7c3aed'; // Violet
                $appTitle = 'BizBoost';
                $manifestPath = '/bizboost-manifest.json';
                $iconPath = '/images/bizboost/icon-192x192.png';
                $faviconPath = '/images/bizboost/icon-192x192.png';
            } elseif ($isGrowBiz) {
                $themeColor = '#059669'; // Emerald
                $appTitle = 'GrowBiz';
                $manifestPath = '/growbiz-manifest.json';
                $iconPath = '/growbiz-assets/icon-192x192.png';
                $faviconPath = '/growbiz-assets/icon-192x192.png';
            } elseif ($isGrowFinance) {
                $themeColor = '#059669'; // Emerald
                $appTitle = 'GrowFinance';
                $manifestPath = '/growfinance-manifest.json';
                $iconPath = '/growfinance-assets/icon-192x192.png';
                $faviconPath = '/growfinance-assets/icon-192x192.png';
            } else {
                // Default: MyGrowNet
                $themeColor = '#2563eb'; // Blue
                $appTitle = 'MyGrowNet';
                $manifestPath = '/manifest.json';
                $iconPath = '/images/icon-192x192.png';
                $faviconPath = asset('logo.png');
            }
        @endphp
        
        <link rel="icon" type="image/png" href="{{ $faviconPath }}">
        <link rel="apple-touch-icon" href="{{ $faviconPath }}">
        
        <!-- PWA Meta Tags -->
        <meta name="theme-color" content="{{ $themeColor }}">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="{{ $appTitle }}">
        <meta name="application-name" content="{{ $appTitle }}">
        <meta name="app-version" content="{{ config('app.version', '1.0.0') }}">
        <meta name="format-detection" content="telephone=no">
        <meta name="msapplication-TileColor" content="{{ $themeColor }}">
        <meta name="msapplication-tap-highlight" content="no">
        
        <!-- PWA Manifest -->
        <link rel="manifest" href="{{ $manifestPath }}?v={{ config('app.version', '1.0.0') }}">
        
        <!-- Apple Touch Icons -->
        <link rel="apple-touch-icon" sizes="180x180" href="{{ $iconPath }}">
        <link rel="apple-touch-icon" sizes="152x152" href="{{ $iconPath }}">
        <link rel="apple-touch-icon" sizes="144x144" href="{{ $iconPath }}">
        <link rel="apple-touch-icon" sizes="120x120" href="{{ $iconPath }}">
        <link rel="apple-touch-icon" sizes="114x114" href="{{ $iconPath }}">
        <link rel="apple-touch-icon" sizes="76x76" href="{{ $iconPath }}">
        <link rel="apple-touch-icon" sizes="72x72" href="{{ $iconPath }}">
        <link rel="apple-touch-icon" sizes="60x60" href="{{ $iconPath }}">
        <link rel="apple-touch-icon" sizes="57x57" href="{{ $iconPath }}">
        
        <!-- Favicon -->
        <link rel="icon" type="image/png" sizes="32x32" href="{{ $iconPath }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ $iconPath }}">
        
        <!-- Apple Splash Screens for BizBoost -->
        @if($isBizBoost)
        <link rel="apple-touch-startup-image" href="/splash/bizboost-splash.png">
        @endif

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
            
            /* Elegant splash screen for PWA */
            #app-splash {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: linear-gradient(135deg, {{ $isBizBoost ? '#7c3aed' : '#2563eb' }} 0%, {{ $isBizBoost ? '#6d28d9' : '#1d4ed8' }} 100%);
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                z-index: 9999;
                transition: opacity 0.5s ease-out;
            }
            
            #app-splash.hidden {
                opacity: 0;
                pointer-events: none;
            }
            
            .splash-logo {
                width: 120px;
                height: 120px;
                background: white;
                border-radius: 28px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 32px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                animation: splash-bounce 1.5s ease-in-out infinite;
                padding: 16px;
            }
            
            .splash-logo img {
                width: 100%;
                height: 100%;
                object-fit: contain;
            }
            
            .splash-text {
                color: white;
                font-size: 32px;
                font-weight: 700;
                margin-bottom: 16px;
                letter-spacing: -0.5px;
            }
            
            .splash-tagline {
                color: rgba(255, 255, 255, 0.9);
                font-size: 16px;
                font-weight: 500;
                margin-bottom: 48px;
            }
            
            .splash-progress-container {
                width: 240px;
                height: 4px;
                background: rgba(255, 255, 255, 0.2);
                border-radius: 2px;
                overflow: hidden;
            }
            
            .splash-progress-bar {
                height: 100%;
                background: white;
                border-radius: 2px;
                width: 0%;
                animation: splash-progress 2s ease-in-out infinite;
            }
            
            @keyframes splash-progress {
                0% { width: 0%; }
                50% { width: 70%; }
                100% { width: 100%; }
            }
            
            @keyframes splash-bounce {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-10px); }
            }
            
            @keyframes splash-spin {
                to { transform: rotate(360deg); }
            }
        </style>

        <title inertia>{{ config('app.name', 'MyGrowNet') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @routes
        @vite(['resources/js/app.ts'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        <!-- Elegant Splash Screen -->
        <div id="app-splash">
            <div class="splash-logo">
                @if($isBizBoost)
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#7c3aed" class="w-16 h-16">
                    <path fill-rule="evenodd" d="M9 4.5a.75.75 0 01.721.544l.813 2.846a3.75 3.75 0 002.576 2.576l2.846.813a.75.75 0 010 1.442l-2.846.813a3.75 3.75 0 00-2.576 2.576l-.813 2.846a.75.75 0 01-1.442 0l-.813-2.846a3.75 3.75 0 00-2.576-2.576l-2.846-.813a.75.75 0 010-1.442l2.846-.813A3.75 3.75 0 007.466 7.89l.813-2.846A.75.75 0 019 4.5zM18 1.5a.75.75 0 01.728.568l.258 1.036c.236.94.97 1.674 1.91 1.91l1.036.258a.75.75 0 010 1.456l-1.036.258c-.94.236-1.674.97-1.91 1.91l-.258 1.036a.75.75 0 01-1.456 0l-.258-1.036a2.625 2.625 0 00-1.91-1.91l-1.036-.258a.75.75 0 010-1.456l1.036-.258a2.625 2.625 0 001.91-1.91l.258-1.036A.75.75 0 0118 1.5zM16.5 15a.75.75 0 01.712.513l.394 1.183c.15.447.5.799.948.948l1.183.395a.75.75 0 010 1.422l-1.183.395c-.447.15-.799.5-.948.948l-.395 1.183a.75.75 0 01-1.422 0l-.395-1.183a1.5 1.5 0 00-.948-.948l-1.183-.395a.75.75 0 010-1.422l1.183-.395c.447-.15.799-.5.948-.948l.395-1.183A.75.75 0 0116.5 15z" clip-rule="evenodd" />
                </svg>
                @else
                <img src="{{ asset('logo.png') }}" alt="MyGrowNet Logo">
                @endif
            </div>
            <div class="splash-text">{{ $appTitle }}</div>
            <div class="splash-tagline">{{ $isBizBoost ? 'Your Marketing Console' : 'Grow Together, Succeed Together' }}</div>
            <div class="splash-progress-container">
                <div class="splash-progress-bar"></div>
            </div>
        </div>
        
        @inertia
        
        <script>
            // Hide splash screen when Inertia page is loaded
            let appLoaded = false;
            let minTimeElapsed = false;
            
            // Ensure splash shows for at least 800ms for smooth experience
            setTimeout(function() {
                minTimeElapsed = true;
                if (appLoaded) {
                    hideSplash();
                }
            }, 800);
            
            // Listen for Inertia page load
            document.addEventListener('DOMContentLoaded', function() {
                // Wait for Inertia to finish loading
                const checkInertia = setInterval(function() {
                    if (document.querySelector('[data-page]')) {
                        clearInterval(checkInertia);
                        appLoaded = true;
                        if (minTimeElapsed) {
                            hideSplash();
                        }
                    }
                }, 50);
                
                // Fallback: hide after 3 seconds max
                setTimeout(function() {
                    clearInterval(checkInertia);
                    hideSplash();
                }, 3000);
            });
            
            function hideSplash() {
                const splash = document.getElementById('app-splash');
                if (splash && !splash.classList.contains('hidden')) {
                    splash.classList.add('hidden');
                    setTimeout(function() {
                        splash.remove();
                    }, 500);
                }
            }
        </script>
    </body>
</html>
