<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#ea580c">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="Venture Builder">
        <meta name="application-name" content="Venture Builder">
        <meta name="format-detection" content="telephone=no">
        <meta name="msapplication-TileColor" content="#ea580c">
        <meta name="msapplication-tap-highlight" content="no">
        <link rel="manifest" href="/manifest.php">
        <link rel="icon" type="image/png" href="/images/icon-192x192.png">
        <link rel="apple-touch-icon" href="/images/icon-192x192.png">
        <title inertia>Venture Builder - Investment Platform</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
        <style>
            :root { --sat: env(safe-area-inset-top); --sar: env(safe-area-inset-right); --sab: env(safe-area-inset-bottom); --sal: env(safe-area-inset-left); }
            html { background-color: #1c1917; -webkit-tap-highlight-color: transparent; }
            body { overscroll-behavior-y: none; -webkit-overflow-scrolling: touch; margin: 0; }
            #venture-splash { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(160deg, #1c1917 0%, #292524 40%, #431407 100%); display: flex; flex-direction: column; align-items: center; justify-content: center; z-index: 9999; transition: opacity 0.5s ease-out; }
            #venture-splash.hidden { opacity: 0; pointer-events: none; }
            .splash-ring-wrap { position: relative; width: 140px; height: 140px; margin-bottom: 32px; }
            .splash-ring-wrap svg { transform: rotate(-90deg); }
            .splash-ring-bg { fill: none; stroke: rgba(251,146,60,0.15); stroke-width: 4; }
            .splash-ring-fill { fill: none; stroke: url(#ringGradient); stroke-width: 4; stroke-linecap: round; stroke-dasharray: 377; stroke-dashoffset: 377; animation: ring-fill 2s ease-out forwards; }
            @keyframes ring-fill { 0% { stroke-dashoffset: 377; } 100% { stroke-dashoffset: 75; } }
            .splash-icon-wrap { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 88px; height: 88px; background: white; border-radius: 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 32px rgba(0,0,0,0.3); animation: icon-pulse 2.5s ease-in-out infinite; }
            .splash-icon-wrap svg { width: 48px; height: 48px; color: #ea580c; }
            @keyframes icon-pulse { 0%, 100% { box-shadow: 0 8px 32px rgba(0,0,0,0.3), 0 0 0 0 rgba(251,146,60,0.3); } 50% { box-shadow: 0 8px 32px rgba(0,0,0,0.3), 0 0 0 16px rgba(251,146,60,0); } }
            .splash-title { color: white; font-size: 28px; font-weight: 700; margin-bottom: 8px; letter-spacing: -0.5px; opacity: 0; transform: translateY(12px); animation: fade-up 0.6s 0.3s ease-out forwards; }
            .splash-tagline { color: rgba(251,191,36,0.85); font-size: 15px; font-weight: 500; text-align: center; max-width: 280px; opacity: 0; transform: translateY(12px); animation: fade-up 0.6s 0.6s ease-out forwards; }
            .splash-sparkle { position: absolute; width: 4px; height: 4px; background: #fbbf24; border-radius: 50%; opacity: 0; animation: sparkle 3s ease-in-out infinite; }
            .splash-sparkle:nth-child(1) { top: 15%; left: 20%; animation-delay: 0.2s; }
            .splash-sparkle:nth-child(2) { top: 25%; right: 25%; animation-delay: 0.8s; width: 3px; height: 3px; }
            .splash-sparkle:nth-child(3) { bottom: 30%; left: 30%; animation-delay: 1.4s; }
            .splash-sparkle:nth-child(4) { bottom: 20%; right: 20%; animation-delay: 2s; width: 5px; height: 5px; }
            .splash-sparkle:nth-child(5) { top: 40%; left: 12%; animation-delay: 0.5s; width: 3px; height: 3px; }
            .splash-sparkle:nth-child(6) { top: 35%; right: 15%; animation-delay: 1.1s; }
            .splash-sparkle:nth-child(7) { bottom: 40%; right: 10%; animation-delay: 1.7s; width: 3px; height: 3px; }
            @keyframes sparkle { 0%, 100% { opacity: 0; transform: scale(0); } 50% { opacity: 0.8; transform: scale(1); } }
            @keyframes fade-up { to { opacity: 1; transform: translateY(0); } }
            .splash-sub { color: rgba(255,255,255,0.3); font-size: 11px; font-weight: 500; letter-spacing: 1px; text-transform: uppercase; margin-top: 48px; opacity: 0; animation: fade-up 0.6s 0.9s ease-out forwards; }
        </style>
        @routes
        @vite(['resources/js/app-venture.ts'], 'build/venture')
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        <div id="venture-splash">
            <svg style="position:absolute;width:0;height:0;">
                <defs>
                    <linearGradient id="ringGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#f59e0b" />
                        <stop offset="100%" stop-color="#ea580c" />
                    </linearGradient>
                </defs>
            </svg>
            <div class="splash-sparkle"></div>
            <div class="splash-sparkle"></div>
            <div class="splash-sparkle"></div>
            <div class="splash-sparkle"></div>
            <div class="splash-sparkle"></div>
            <div class="splash-sparkle"></div>
            <div class="splash-sparkle"></div>
            <div class="splash-ring-wrap">
                <svg width="140" height="140" viewBox="0 0 140 140">
                    <circle class="splash-ring-bg" cx="70" cy="70" r="60" />
                    <circle class="splash-ring-fill" cx="70" cy="70" r="60" />
                </svg>
                <div class="splash-icon-wrap">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605" />
                    </svg>
                </div>
            </div>
            <div class="splash-title">Venture Builder</div>
            <div class="splash-tagline">Investment Platform</div>
            <div class="splash-sub">by MyGrowNet</div>
        </div>
        @inertia
        <script>
            (function() { var s = document.getElementById('venture-splash'); if (!s) return; var l = false, m = false; setTimeout(function() { m = true; if (l) { s.classList.add('hidden'); setTimeout(function() { s.remove(); }, 400); } }, 600); document.addEventListener('DOMContentLoaded', function() { var c = setInterval(function() { if (document.querySelector('[data-page]')) { clearInterval(c); l = true; if (m) { s.classList.add('hidden'); setTimeout(function() { s.remove(); }, 400); } } }, 50); setTimeout(function() { clearInterval(c); s.classList.add('hidden'); setTimeout(function() { s.remove(); }, 400); }, 2500); }); })();
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', function() {
                    navigator.serviceWorker.register('/venture-sw.js')
                        .then(function(reg) { console.log('Venture SW registered:', reg.scope); })
                        .catch(function(err) { console.log('Venture SW registration failed:', err); });
                });
            }
        </script>
    </body>
</html>
