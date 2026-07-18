<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php
            $sfCompany = null;
            if (str_starts_with(request()->route()?->getName() ?? '', 'stockflow.sub.')) {
                try {
                    $account = request()->route('account');
                    if ($account) {
                        $sfCompany = \App\Infrastructure\Persistence\Eloquent\StockFlow\SaCompanyModel::where('subdomain', $account)->first();
                    }
                } catch (\Exception $e) {}
            }
            $brandColor = $sfCompany ? ($sfCompany->settings['brand_color'] ?? '#2563eb') : '#2563eb';
            $companyName = $sfCompany?->name ?? 'StockFlow';
            $companyTagline = $sfCompany ? ($sfCompany->settings['tagline'] ?? '') : 'Inventory & Audit Management';
            $companyInitial = $sfCompany ? mb_substr($sfCompany->name, 0, 1) : 'S';
        @endphp

        <style>
            html { background-color: oklch(1 0 0); }
            html.dark { background-color: oklch(0.145 0 0); }
            #stockflow-splash { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, {{ $brandColor }} 0%, #1d4ed8 100%); display: flex; flex-direction: column; align-items: center; justify-content: center; z-index: 9999; transition: opacity 0.5s ease-out; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
            #stockflow-splash.hidden { opacity: 0; pointer-events: none; }
            .splash-logo { width: 120px; height: 120px; background: white; border-radius: 24px; display: flex; align-items: center; justify-content: center; margin-bottom: 24px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
            .splash-logo .splash-initial { font-size: 52px; font-weight: 700; color: {{ $brandColor }}; line-height: 1; }
            .splash-logo img { width: 100%; height: 100%; object-fit: contain; }
            .splash-text { color: white; font-size: 28px; font-weight: 700; margin-bottom: 8px; letter-spacing: -0.5px; }
            .splash-tagline { color: rgba(255,255,255,0.9); font-size: 15px; font-weight: 500; margin-bottom: 48px; text-align: center; max-width: 280px; }
            .splash-progress-container { width: 200px; height: 3px; background: rgba(255,255,255,0.2); border-radius: 2px; overflow: hidden; }
            .splash-progress-bar { height: 100%; background: white; border-radius: 2px; width: 0%; animation: splash-progress 1.5s ease-in-out infinite; }
            @keyframes splash-progress { 0% { width: 0%; margin-left: 0%; } 50% { width: 70%; margin-left: 15%; } 100% { width: 100%; margin-left: 0%; } }
        </style>

        <link rel="icon" type="image/png" href="/logo.png">
        <link rel="apple-touch-icon" href="/logo.png">
        <meta name="theme-color" content="{{ $brandColor }}">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="{{ $companyName }}">
        <meta name="application-name" content="{{ $companyName }}">
        <meta name="format-detection" content="telephone=no">
        <meta name="msapplication-TileColor" content="{{ $brandColor }}">
        <meta name="msapplication-tap-highlight" content="no">

        <title inertia>{{ $companyName }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <script>
            window.__sfSubdomain = true;
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.getRegistrations().then(function(r) { r.forEach(function(s) { s.unregister(); }); });
            }
        </script>
        @routes
        @vite(['resources/js/app-stockflow.ts'], 'build/stockflow')
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        <div id="stockflow-splash">
            <div class="splash-logo">
                @if($sfCompany && !empty($sfCompany->settings['logo_url']))
                <img src="{{ $sfCompany->settings['logo_url'] }}" alt="{{ $companyName }} Logo">
                @else
                <span class="splash-initial">{{ $companyInitial }}</span>
                @endif
            </div>
            <div class="splash-text">{{ $companyName }}</div>
            @if($companyTagline)
            <div class="splash-tagline">{{ $companyTagline }}</div>
            @endif
            <div class="splash-progress-container">
                <div class="splash-progress-bar"></div>
            </div>
        </div>

        @inertia

        <script>
            (function() {
                var splash = document.getElementById('stockflow-splash');
                if (!splash) return;
                var appLoaded = false;
                var minTimeElapsed = false;
                setTimeout(function() { minTimeElapsed = true; if (appLoaded) hideSplash(); }, 800);
                document.addEventListener('inertia:finish', function() { appLoaded = true; if (minTimeElapsed) hideSplash(); });
                document.addEventListener('DOMContentLoaded', function() { setTimeout(function() { if (!appLoaded) hideSplash(); }, 3000); });
                function hideSplash() { if (splash && !splash.classList.contains('hidden')) { splash.classList.add('hidden'); setTimeout(function() { if (splash.parentNode) splash.remove(); }, 500); } }
                setTimeout(function() { if (splash && splash.parentNode) splash.style.display = 'none'; }, 5000);
            })();
        </script>
    </body>
</html>
