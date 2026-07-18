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
            html { background-color: #fff7ed; -webkit-tap-highlight-color: transparent; }
            body { overscroll-behavior-y: none; -webkit-overflow-scrolling: touch; }
            #venture-splash { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%); display: flex; flex-direction: column; align-items: center; justify-content: center; z-index: 9999; transition: opacity 0.4s ease-out; }
            #venture-splash.hidden { opacity: 0; pointer-events: none; }
            .splash-icon { width: 100px; height: 100px; background: white; border-radius: 24px; display: flex; align-items: center; justify-content: center; margin-bottom: 24px; box-shadow: 0 16px 48px rgba(0,0,0,0.25); }
            .splash-icon svg { width: 56px; height: 56px; }
            .splash-title { color: white; font-size: 28px; font-weight: 700; margin-bottom: 8px; letter-spacing: -0.5px; }
            .splash-tagline { color: rgba(255,255,255,0.85); font-size: 15px; font-weight: 500; text-align: center; max-width: 280px; }
        </style>
        @routes
        @vite(['resources/js/app-venture.ts'], 'build/venture')
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        <div id="venture-splash">
            <div class="splash-icon">
                <svg class="w-14 h-14 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" />
                </svg>
            </div>
            <div class="splash-title">Venture Builder</div>
            <div class="splash-tagline">Investment Platform</div>
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
