<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        <!-- GrowBiz PWA Meta Tags -->
        <meta name="theme-color" content="#059669">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="GrowBiz">
        <meta name="application-name" content="GrowBiz">
        <meta name="format-detection" content="telephone=no">
        <meta name="msapplication-TileColor" content="#059669">
        <meta name="msapplication-tap-highlight" content="no">
        
        <!-- PWA Manifest -->
        <link rel="manifest" href="/growbiz-manifest.json">
        
        <!-- Icons -->
        <link rel="icon" type="image/png" href="/growbiz-assets/icon-192x192.png">
        <link rel="apple-touch-icon" href="/growbiz-assets/icon-192x192.png">
        <link rel="apple-touch-icon" sizes="180x180" href="/growbiz-assets/icon-192x192.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/growbiz-assets/icon-96x96.png">

        <title inertia>GrowBiz - SME Management</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        <style>
            /* PWA Standalone Mode Styles */
            html {
                background-color: #f9fafb;
                -webkit-tap-highlight-color: transparent;
                -webkit-touch-callout: none;
                -webkit-user-select: none;
                user-select: none;
            }
            
            /* Safe area insets for notched devices */
            :root {
                --sat: env(safe-area-inset-top);
                --sar: env(safe-area-inset-right);
                --sab: env(safe-area-inset-bottom);
                --sal: env(safe-area-inset-left);
            }

            /* Prevent overscroll/bounce on iOS */
            body {
                overscroll-behavior-y: none;
                -webkit-overflow-scrolling: touch;
            }
            
            /* Splash screen */
            #growbiz-splash {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: linear-gradient(135deg, #059669 0%, #047857 100%);
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                z-index: 9999;
                transition: opacity 0.4s ease-out;
                padding-top: var(--sat);
            }
            
            #growbiz-splash.hidden {
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
                color: #059669;
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
            
            /* Enable text selection for inputs */
            input, textarea, [contenteditable] {
                -webkit-user-select: text;
                user-select: text;
            }
        </style>

        @routes
        @vite(['resources/js/app.ts'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        <!-- GrowBiz Splash Screen -->
        <div id="growbiz-splash">
            <div class="splash-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                </svg>
            </div>
            <div class="splash-title">GrowBiz</div>
            <div class="splash-subtitle">SME Management</div>
            <div class="splash-loader">
                <div class="splash-loader-bar"></div>
            </div>
        </div>
        
        @inertia
        
        <script>
            // Hide splash screen
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
                const splash = document.getElementById('growbiz-splash');
                if (splash && !splash.classList.contains('hidden')) {
                    splash.classList.add('hidden');
                    setTimeout(() => splash.remove(), 400);
                }
            }
            
            // Register service worker for PWA
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/growbiz-sw.js')
                        .catch(err => console.log('SW registration failed:', err));
                });
            }
        </script>
    </body>
</html>
