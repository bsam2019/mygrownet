<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#059669">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="BizDocs">
        <meta name="application-name" content="BizDocs">
        <meta name="format-detection" content="telephone=no">
        <meta name="msapplication-TileColor" content="#059669">
        <meta name="msapplication-tap-highlight" content="no">
        <link rel="manifest" href="/manifest.php">
        <link rel="icon" type="image/png" href="/images/icon-192x192.png">
        <link rel="apple-touch-icon" href="/images/icon-192x192.png">
        <title inertia>BizDocs - Document Management</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
        <style>
            :root { --sat: env(safe-area-inset-top); --sar: env(safe-area-inset-right); --sab: env(safe-area-inset-bottom); --sal: env(safe-area-inset-left); }
            html { background-color: #f0fdfa; -webkit-tap-highlight-color: transparent; }
            body { overscroll-behavior-y: none; -webkit-overflow-scrolling: touch; }
            #bizdocs-splash { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, #059669 0%, #047857 100%); display: flex; flex-direction: column; align-items: center; justify-content: center; z-index: 9999; transition: opacity 0.4s ease-out; }
            #bizdocs-splash.hidden { opacity: 0; pointer-events: none; }
            .splash-icon { width: 100px; height: 100px; background: white; border-radius: 24px; display: flex; align-items: center; justify-content: center; margin-bottom: 24px; box-shadow: 0 16px 48px rgba(0,0,0,0.25); }
            .splash-icon svg { width: 56px; height: 56px; }
            .splash-title { color: white; font-size: 28px; font-weight: 700; margin-bottom: 8px; letter-spacing: -0.5px; }
            .splash-tagline { color: rgba(255,255,255,0.85); font-size: 15px; font-weight: 500; text-align: center; max-width: 280px; }
        </style>
        @routes
        @vite(['resources/js/app.ts'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        <div id="bizdocs-splash">
            <div class="splash-icon">
                <svg class="w-14 h-14 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
            </div>
            <div class="splash-title">BizDocs</div>
            <div class="splash-tagline">Document Management</div>
        </div>
        @inertia
        <script>
            (function() { var s = document.getElementById('bizdocs-splash'); if (s) { document.addEventListener('inertia:start', function() { s.classList.add('hidden'); }); } })();
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', function() {
                    navigator.serviceWorker.register('/bizdocs-sw.js')
                        .then(function(reg) { console.log('BizDocs SW registered:', reg.scope); })
                        .catch(function(err) { console.log('BizDocs SW registration failed:', err); });
                });
            }
        </script>
    </body>
</html>
