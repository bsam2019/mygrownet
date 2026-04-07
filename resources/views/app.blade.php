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

        {{-- Inline styles for HTML background + splash screen --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }
            html.dark {
                background-color: oklch(0.145 0 0);
            }

            /* ================================================
               PWA SPLASH SCREEN
               All rules use !important to survive Tailwind
               preflight / CSS reset that loads later via Vite
               ================================================ */
            #app-splash {
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                width: 100% !important;
                height: 100% !important;
                background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                justify-content: center !important;
                z-index: 99999 !important;
                opacity: 1 !important;
                transition: opacity 0.5s ease-out !important;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif !important;
                margin: 0 !important;
                padding: 0 !important;
                box-sizing: border-box !important;
            }

            #app-splash.splash-hiding {
                opacity: 0 !important;
                pointer-events: none !important;
            }

            #app-splash .splash-logo {
                width: 120px !important;
                height: 120px !important;
                background: white !important;
                border-radius: 28px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                margin-bottom: 28px !important;
                padding: 20px !important;
                box-sizing: border-box !important;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3) !important;
                animation: splashBounce 2.2s ease-in-out infinite !important;
                overflow: hidden !important;
                flex-shrink: 0 !important;
            }

            #app-splash .splash-logo img {
                width: 100% !important;
                height: 100% !important;
                max-width: 100% !important;
                display: block !important;
                object-fit: contain !important;
                border: none !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            #app-splash .splash-text {
                color: #ffffff !important;
                font-size: 30px !important;
                font-weight: 700 !important;
                margin: 0 0 8px 0 !important;
                padding: 0 !important;
                letter-spacing: -0.5px !important;
                line-height: 1.2 !important;
                text-align: center !important;
            }

            #app-splash .splash-tagline {
                color: rgba(255, 255, 255, 0.85) !important;
                font-size: 15px !important;
                font-weight: 500 !important;
                margin: 0 0 52px 0 !important;
                padding: 0 16px !important;
                text-align: center !important;
                max-width: 280px !important;
                line-height: 1.5 !important;
            }

            #app-splash .splash-progress-container {
                width: 180px !important;
                height: 3px !important;
                background: rgba(255, 255, 255, 0.25) !important;
                border-radius: 99px !important;
                overflow: hidden !important;
                display: block !important;
            }

            #app-splash .splash-progress-bar {
                height: 100% !important;
                background: rgba(255, 255, 255, 0.9) !important;
                border-radius: 99px !important;
                width: 0% !important;
                display: block !important;
                animation: splashProgress 1.6s ease-in-out infinite !important;
            }

            #app-splash .splash-version {
                position: absolute !important;
                bottom: 32px !important;
                color: rgba(255, 255, 255, 0.4) !important;
                font-size: 12px !important;
                font-weight: 400 !important;
                letter-spacing: 0.5px !important;
            }

            @keyframes splashProgress {
                0%   { width: 0%;   margin-left: 0%; }
                50%  { width: 65%;  margin-left: 17%; }
                100% { width: 0%;   margin-left: 100%; }
            }

            @keyframes splashBounce {
                0%, 100% { transform: translateY(0px) scale(1); }
                50%       { transform: translateY(-10px) scale(1.03); }
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
        <link rel="apple-touch-icon" sizes="76x76"   href="/images/icon-72x72.png">
        <link rel="apple-touch-icon" sizes="72x72"   href="/images/icon-72x72.png">
        <link rel="apple-touch-icon" sizes="60x60"   href="/images/icon-72x72.png">
        <link rel="apple-touch-icon" sizes="57x57"   href="/images/icon-72x72.png">

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

        {{-- ============================================================
             SPLASH SCREEN
             Rendered as first child of <body> so it shows instantly.
             Tailwind preflight runs later; all styles use !important.
             ============================================================ --}}
        <div id="app-splash" aria-hidden="true" role="presentation">
            <div class="splash-logo">
                <img src="{{ asset('logo.png') }}" alt="MyGrowNet">
            </div>
            <div class="splash-text">MyGrowNet</div>
            <div class="splash-tagline">Grow Together, Succeed Together</div>
            <div class="splash-progress-container">
                <div class="splash-progress-bar"></div>
            </div>
            <div class="splash-version">v{{ config('app.version', '1.0') }}</div>
        </div>

        @inertia

        <script>
            // ─── Mobile error logging ───────────────────────────────────────
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

            console.log('Page loading started:', new Date().toISOString());
            console.log('User Agent:', navigator.userAgent);

            // ─── Service Worker ─────────────────────────────────────────────
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', function() {
                    navigator.serviceWorker.register('/service-worker.js')
                        .then(function(reg)  { console.log('SW registered:', reg.scope); })
                        .catch(function(err) { console.log('SW failed:', err); });
                });
            }

            // ─── PWA install prompt ─────────────────────────────────────────
            window.addEventListener('beforeinstallprompt', function(e) {
                console.log('beforeinstallprompt captured');
                // Don't call preventDefault here — let Vue components handle it
            });

            // ─── Splash hide logic ──────────────────────────────────────────
            (function() {
                var splash = document.getElementById('app-splash');
                if (!splash) return;

                var appLoaded    = false;
                var minElapsed   = false;
                var hideInvoked  = false;

                // Minimum display time so the splash never flickers
                var MIN_DISPLAY_MS = 900;

                setTimeout(function() {
                    minElapsed = true;
                    if (appLoaded) hideSplash();
                }, MIN_DISPLAY_MS);

                // Primary trigger: Inertia signals the page is ready
                document.addEventListener('inertia:finish', function() {
                    appLoaded = true;
                    if (minElapsed) hideSplash();
                });

                // Secondary fallback: Inertia mounted (fires after Vue mounts)
                document.addEventListener('inertia:navigate', function() {
                    appLoaded = true;
                    if (minElapsed) hideSplash();
                });

                // Hard fallback: give up after 4 s and show the app anyway
                setTimeout(function() {
                    if (!hideInvoked) {
                        hideSplash();
                        console.warn('Splash hidden by 4 s hard timeout');
                    }
                }, 4000);

                function hideSplash() {
                    if (hideInvoked || !splash) return;
                    hideInvoked = true;

                    splash.classList.add('splash-hiding');

                    // Remove from DOM after the CSS transition finishes (500 ms)
                    setTimeout(function() {
                        if (splash && splash.parentNode) {
                            splash.parentNode.removeChild(splash);
                            console.log('Splash removed from DOM');
                        }
                    }, 520);
                }
            })();
        </script>
    </body>
</html>