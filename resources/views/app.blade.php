<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('logo.png') }}">
        
        <!-- PWA Meta Tags -->
        <meta name="theme-color" content="#2563eb">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="MyGrowNet">
        <meta name="app-version" content="{{ config('app.version', '1.0.0') }}">
        <link rel="manifest" href="/manifest.json?v={{ config('app.version', '1.0.0') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="/images/icon-192x192.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/images/icon-192x192.png">

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
                background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
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
                <img src="{{ asset('logo.png') }}" alt="MyGrowNet Logo">
            </div>
            <div class="splash-text">MyGrowNet</div>
            <div class="splash-tagline">Grow Together, Succeed Together</div>
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
