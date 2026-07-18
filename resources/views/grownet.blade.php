<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#3730a3">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="GrowNet">
        <meta name="application-name" content="GrowNet">
        <meta name="format-detection" content="telephone=no">
        <meta name="msapplication-TileColor" content="#3730a3">
        <meta name="msapplication-tap-highlight" content="no">
        <link rel="manifest" href="/manifest.php">
        <link rel="icon" type="image/png" href="/images/icon-192x192.png">
        <link rel="apple-touch-icon" href="/images/icon-192x192.png">
        <title inertia>GrowNet - Digital Content Platform</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
        <style>
            :root { --sat: env(safe-area-inset-top); --sar: env(safe-area-inset-right); --sab: env(safe-area-inset-bottom); --sal: env(safe-area-inset-left); }
            html { background-color: #eef2ff; -webkit-tap-highlight-color: transparent; }
            body { overscroll-behavior-y: none; -webkit-overflow-scrolling: touch; }
            #grownet-splash { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, #3730a3 0%, #312e81 100%); display: flex; flex-direction: column; align-items: center; justify-content: center; z-index: 9999; transition: opacity 0.4s ease-out; }
            #grownet-splash.hidden { opacity: 0; pointer-events: none; }
            .splash-icon { width: 100px; height: 100px; background: white; border-radius: 24px; display: flex; align-items: center; justify-content: center; margin-bottom: 24px; box-shadow: 0 16px 48px rgba(0,0,0,0.25); }
            .splash-icon svg { width: 56px; height: 56px; }
            .splash-title { color: white; font-size: 28px; font-weight: 700; margin-bottom: 8px; letter-spacing: -0.5px; }
            .splash-tagline { color: rgba(255,255,255,0.85); font-size: 15px; font-weight: 500; text-align: center; max-width: 280px; }
        </style>
        @routes
        @vite(['resources/js/app-grownet.ts'], 'build/grownet')
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        <div id="grownet-splash">
            <div class="splash-icon">
                <svg class="w-14 h-14 text-indigo-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
                </svg>
            </div>
            <div class="splash-title">GrowNet</div>
            <div class="splash-tagline">Digital Content Platform</div>
        </div>
        @inertia
        <script>
            (function() { var s = document.getElementById('grownet-splash'); if (!s) return; var l = false, m = false; setTimeout(function() { m = true; if (l) { s.classList.add('hidden'); setTimeout(function() { s.remove(); }, 400); } }, 600); document.addEventListener('DOMContentLoaded', function() { var c = setInterval(function() { if (document.querySelector('[data-page]')) { clearInterval(c); l = true; if (m) { s.classList.add('hidden'); setTimeout(function() { s.remove(); }, 400); } } }, 50); setTimeout(function() { clearInterval(c); s.classList.add('hidden'); setTimeout(function() { s.remove(); }, 400); }, 2500); }); })();
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', function() {
                    navigator.serviceWorker.register('/grownet-sw.js')
                        .then(function(reg) { console.log('GrowNet SW registered:', reg.scope); })
                        .catch(function(err) { console.log('GrowNet SW registration failed:', err); });
                });
            }
        </script>
    </body>
</html>
