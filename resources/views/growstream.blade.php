<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#121212">
        <link rel="manifest" href="/manifest.php">
        <link rel="icon" type="image/png" href="/logo.png">
        <title inertia>GrowStream</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Be+Vietnam+Pro:wght@400;500;600&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">
        <style>
            html { background-color: #121212; }
            body { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; background-color: #121212; color: #e6e6e6; }
            #app-loading {
                position: fixed; inset: 0; display: flex; align-items: center; justify-content: center;
                background: #121212; z-index: 9999; transition: opacity 0.3s;
            }
            #app-loading .spinner {
                width: 32px; height: 32px; border: 3px solid #2c2c2f;
                border-top-color: #a73400; border-radius: 50%; animation: spin 0.7s linear infinite;
            }
            @keyframes spin { to { transform: rotate(360deg); } }
        </style>
        @routes
        @vite(['resources/js/app-growstream.ts'], 'build/growstream')
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        <div id="app-loading"><div class="spinner"></div></div>
        @inertia
        <script>document.addEventListener('DOMContentLoaded',function(){var l=document.getElementById('app-loading');if(l){l.style.opacity='0';setTimeout(function(){l.remove()},300);}});</script>
        @if(config('growstream.pwa.enabled', true))
            <script>
                if ('serviceWorker' in navigator) {
                    window.addEventListener('load', function () {
                        navigator.serviceWorker.register('/growstream-sw.js', { updateViaCache: 'none' })
                            .then(function (reg) { console.log('[GrowStream PWA] Service Worker registered:', reg.scope); })
                            .catch(function (err) { console.warn('[GrowStream PWA] Registration failed:', err); });
                    });
                }
            </script>
        @endif
    </body>
</html>
