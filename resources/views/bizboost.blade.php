<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- BizBoost PWA Meta Tags -->
        <meta name="theme-color" content="#7c3aed">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="BizBoost">
        <meta name="application-name" content="BizBoost">
        <meta name="format-detection" content="telephone=no">
        <meta name="msapplication-TileColor" content="#7c3aed">
        <meta name="msapplication-tap-highlight" content="no">

        <!-- PWA Manifest -->
        <link rel="manifest" href="/manifest.php">

        <!-- Icons -->
        <link rel="icon" type="image/png" href="/bizboost-assets/icon-192x192.png">
        <link rel="apple-touch-icon" href="/bizboost-assets/icon-192x192.png">
        <link rel="apple-touch-icon" sizes="180x180" href="/bizboost-assets/icon-192x192.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/bizboost-assets/icon-96x96.png">

        <title inertia>BizBoost - Marketing & Growth Assistant</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        <style>
            :root {
                --sat: env(safe-area-inset-top);
                --sar: env(safe-area-inset-right);
                --sab: env(safe-area-inset-bottom);
                --sal: env(safe-area-inset-left);
            }

            html {
                background-color: #f8fafc;
                -webkit-tap-highlight-color: transparent;
                -webkit-touch-callout: none;
                -webkit-user-select: none;
                user-select: none;
            }

            body {
                overscroll-behavior-y: none;
                -webkit-overflow-scrolling: touch;
            }

            #bizboost-splash {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                z-index: 9999;
                transition: opacity 0.4s ease-out;
                padding-top: var(--sat);
            }

            #bizboost-splash.hidden {
                opacity: 0;
                pointer-events: none;
            }

            .splash-icon {
                width: 100px;
                height: 100px;
                background: white;
                border-radius: 24px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 24px;
                box-shadow: 0 16px 48px rgba(0, 0, 0, 0.25);
                animation: splash-pulse 1.5s ease-in-out infinite;
            }

            .splash-icon svg {
                width: 56px;
                height: 56px;
                color: #7c3aed;
            }

            .splash-title {
                color: white;
                font-size: 28px;
                font-weight: 700;
                margin-bottom: 8px;
                letter-spacing: -0.5px;
            }

            .splash-subtitle {
                color: rgba(255, 255, 255, 0.85);
                font-size: 14px;
                font-weight: 500;
            }

            .splash-loader {
                margin-top: 40px;
                width: 180px;
                height: 3px;
                background: rgba(255, 255, 255, 0.2);
                border-radius: 2px;
                overflow: hidden;
            }

            .splash-loader-bar {
                height: 100%;
                background: white;
                border-radius: 2px;
                animation: splash-loading 1.5s ease-in-out infinite;
            }

            @keyframes splash-pulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.05); }
            }

            @keyframes splash-loading {
                0% { width: 0%; margin-left: 0; }
                50% { width: 60%; margin-left: 20%; }
                100% { width: 0%; margin-left: 100%; }
            }

            input, textarea, [contenteditable] {
                -webkit-user-select: text;
                user-select: text;
            }
        </style>

        @routes
        @vite(['resources/js/app-bizboost.ts'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        <div id="bizboost-splash">
            <div class="splash-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z" />
                </svg>
            </div>
            <div class="splash-title">BizBoost</div>
            <div class="splash-subtitle">Marketing & Growth Assistant</div>
            <div class="splash-loader">
                <div class="splash-loader-bar"></div>
            </div>
        </div>

        @inertia

        <script>
            // Route name remapping for BizBoost subdomain
            // On bizboost.mygrownet.com, the main-domain bizboost.* routes have wrong URLs
            // (still pointing to /bizboost/ prefix). We override them with the subdomain
            // route definitions (bizboost.sub.*) which have the correct {uri} without prefix.
            if (window.location.hostname === 'bizboost.mygrownet.com') {
                if (typeof Ziggy !== 'undefined' && Ziggy.routes) {
                    for (const [name, route] of Object.entries(Ziggy.routes)) {
                        if (name.startsWith('bizboost.sub.')) {
                            const mainName = 'bizboost.' + name.substring('bizboost.sub.'.length);
                            Ziggy.routes[mainName] = route;
                        }
                    }
                }
            }

            let loaded = false;
            let minTime = false;

            setTimeout(() => { minTime = true; if (loaded) hideSplash(); }, 600);

            document.addEventListener('DOMContentLoaded', () => {
                const check = setInterval(() => {
                    if (document.querySelector('[data-page]')) {
                        clearInterval(check);
                        loaded = true;
                        if (minTime) hideSplash();
                    }
                }, 50);
                setTimeout(() => { clearInterval(check); hideSplash(); }, 2500);
            });

            function hideSplash() {
                const splash = document.getElementById('bizboost-splash');
                if (splash && !splash.classList.contains('hidden')) {
                    splash.classList.add('hidden');
                    setTimeout(() => splash.remove(), 400);
                }
            }

            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/bizboost-sw.js')
                        .catch(err => console.log('BizBoost SW registration failed:', err));
                });
            }
        </script>
    </body>
</html>
