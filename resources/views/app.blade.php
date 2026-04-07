<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="app-version" content="{{ config('app.version') }}">
        
        {{-- Dark mode detection - MUST run before styles to prevent flash --}}
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

        {{-- Inline style to set the HTML background color --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
            
            /* Splash screen for PWA */
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
                width: 160px;
                height: 160px;
                background: white;
                border-radius: 32px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 32px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                animation: splash-bounce 1.5s ease-in-out infinite;
                padding: 24px;
            }
            
            .splash-logo img {
                width: 100%;
                height: 100%;
                object-fit: contain;
                object-position: center;
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
        </style>
        
        <!-- Favicon -->
        <link rel="icon" type="image/png" href="/logo.png">
        <link rel="apple-touch-icon" href="/logo.png">
        
        <!-- PWA Meta Tags -->
        <meta name="theme-color" content="#2563eb">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="MyGrowNet">
        <meta name="application-name" content="MyGrowNet">
        <meta name="format-detection" content="telephone=no">
        <meta name="msapplication-TileColor" content="#2563eb">
        <meta name="msapplication-tap-highlight" content="no">
        
        <!-- PWA Manifest -->
        <link rel="manifest" href="/manifest.json">
        
        <!-- Apple Touch Icons -->
        <link rel="apple-touch-icon" sizes="180x180" href="/images/icon-192x192.png">
        <link rel="apple-touch-icon" sizes="152x152" href="/images/icon-152x152.png">
        <link rel="apple-touch-icon" sizes="144x144" href="/images/icon-144x144.png">
        <link rel="apple-touch-icon" sizes="120x120" href="/images/icon-128x128.png">
        <link rel="apple-touch-icon" sizes="114x114" href="/images/icon-128x128.png">
        <link rel="apple-touch-icon" sizes="76x76" href="/images/icon-72x72.png">
        <link rel="apple-touch-icon" sizes="72x72" href="/images/icon-72x72.png">
        <link rel="apple-touch-icon" sizes="60x60" href="/images/icon-72x72.png">
        <link rel="apple-touch-icon" sizes="57x57" href="/images/icon-72x72.png">
        
        <!-- Apple Splash Screens -->
        <link rel="apple-touch-startup-image" href="/splash.html" />
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
        
        <!-- Standard Favicon Sizes -->
        <link rel="icon" type="image/png" sizes="32x32" href="/images/icon-192x192.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/images/icon-192x192.png">

        <title inertia>{{ config('app.name', 'MyGrowNet') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @routes
        @vite(['resources/js/app.ts'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        <!-- Splash Screen -->
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
            // Mobile error logging
            window.mobileErrors = [];
            window.addEventListener('error', function(e) {
                window.mobileErrors.push({
                    message: e.message,
                    file: e.filename,
                    line: e.lineno,
                    col: e.colno,
                    time: new Date().toISOString()
                });
                console.error('Global error:', e.message, e.filename, e.lineno);
            });
            
            window.addEventListener('unhandledrejection', function(e) {
                window.mobileErrors.push({
                    message: 'Promise rejection: ' + e.reason,
                    time: new Date().toISOString()
                });
                console.error('Unhandled promise rejection:', e.reason);
            });
            
            // Log page load
            console.log('Page loading started:', new Date().toISOString());
            console.log('User Agent:', navigator.userAgent);
            
            // Register Service Worker
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', function() {
                    navigator.serviceWorker.register('/service-worker.js')
                        .then(function(registration) {
                            console.log('ServiceWorker registered:', registration.scope);
                        })
                        .catch(function(error) {
                            console.log('ServiceWorker registration failed:', error);
                        });
                });
            }
            
            // Hide splash screen when Inertia page is loaded
            (function() {
                var splash = document.getElementById('app-splash');
                if (!splash) return; // No splash screen for this page
                
                var appLoaded = false;
                var minTimeElapsed = false;
                
                // Ensure splash shows for at least 800ms for smooth experience
                setTimeout(function() {
                    minTimeElapsed = true;
                    if (appLoaded) {
                        hideSplash();
                    }
                }, 800);
                
                // Listen for Inertia finish event (more reliable than polling)
                document.addEventListener('inertia:finish', function() {
                    appLoaded = true;
                    if (minTimeElapsed) {
                        hideSplash();
                    }
                });
                
                // Fallback: also listen for DOMContentLoaded
                document.addEventListener('DOMContentLoaded', function() {
                    // Fallback: hide after 3 seconds max
                    setTimeout(function() {
                        if (!appLoaded) {
                            hideSplash();
                            console.log('Splash hidden by timeout');
                        }
                    }, 3000);
                });
                
                function hideSplash() {
                    if (splash && !splash.classList.contains('hidden')) {
                        splash.classList.add('hidden');
                        setTimeout(function() {
                            if (splash.parentNode) {
                                splash.remove();
                                console.log('Splash removed');
                            }
                        }, 500);
                    }
                }
                
                // Emergency fallback: force hide after 5 seconds no matter what
                setTimeout(function() {
                    if (splash && splash.parentNode) {
                        splash.style.display = 'none';
                        console.log('Splash force-hidden by emergency timeout');
                    }
                }, 5000);
            })();
        </script>
    </body>
</html>
