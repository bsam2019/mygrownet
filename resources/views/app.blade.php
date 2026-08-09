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

            // Check for GrowBuilder site rendering props
            $pageProps = $page['props'] ?? [];
            $gbSite = $pageProps['site'] ?? null;
            $gbSeo = $pageProps['seo'] ?? null;
            $gbPage = $pageProps['page'] ?? null;

            $isGbSite = !empty($gbSite) && !empty($gbSite['name']);

            if ($isGbSite) {
                $siteName = $gbSite['name'];
                $pageTitle = $gbPage['title'] ?? 'Home';
                $isHomepage = !empty($gbPage['isHomepage']);

                $metaTitle = $gbPage['metaTitle'] ?? null;
                $siteSeoTitle = $gbSite['seoSettings']['metaTitle'] ?? null;

                if ($isHomepage) {
                    $docTitle = $metaTitle ?: ($siteSeoTitle ?: $siteName);
                } else {
                    $docTitle = ($metaTitle ?: $pageTitle) . ' | ' . $siteName;
                }

                $docDescription = $gbSeo['description'] 
                    ?? ($gbPage['metaDescription'] 
                    ?? ($gbSite['seoSettings']['metaDescription'] 
                    ?? ($gbSite['description'] 
                    ?? ('Welcome to ' . $siteName))));

                $docOgImage = $gbSeo['ogImage'] 
                    ?? ($gbPage['ogImage'] 
                    ?? ($gbSite['seoSettings']['ogImage'] 
                    ?? ($gbSite['logo'] 
                    ?? null)));

                $docFavicon = $gbSeo['favicon'] 
                    ?? ($gbSite['favicon'] 
                    ?? ($gbSite['logo'] 
                    ?? '/logo.png'));

                $docUrl = $gbSeo['canonical'] ?? request()->fullUrl();

                $jsonLd = [
                    '@context' => 'https://schema.org',
                    '@type' => 'WebSite',
                    'name' => $siteName,
                    'url' => $docUrl,
                ];
                if (!empty($docDescription)) {
                    $jsonLd['description'] = $docDescription;
                }
                $jsonLdString = json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            } else {
                $siteName = $sfCompany ? ($sfCompany->name ?? 'MyGrowNet') : config('app.name', 'MyGrowNet');
                $docTitle = $sfCompany ? ($sfCompany->name ?? 'MyGrowNet') : config('app.name', 'MyGrowNet');
                $docDescription = null;
                $docOgImage = null;
                $docFavicon = '/logo.png';
                $docUrl = request()->fullUrl();
                $jsonLdString = null;
            }

            $brandColor = $sfCompany ? ($sfCompany->settings['brand_color'] ?? '#2563eb') : '#2563eb';
            $companyName = $isGbSite ? $siteName : ($sfCompany?->name ?? 'MyGrowNet');
            $companyTagline = $sfCompany ? ($sfCompany->settings['tagline'] ?? '') : ($isGbSite ? '' : 'Grow Together, Succeed Together');
            $companyInitial = $sfCompany ? mb_substr($sfCompany->name, 0, 1) : ($isGbSite ? mb_substr($siteName, 0, 1) : 'M');
        @endphp

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
                background: linear-gradient(135deg, {{ $brandColor }} 0%, #1d4ed8 100%);
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                z-index: 9999;
                transition: opacity 0.5s ease-out;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            }
            
            #app-splash.hidden {
                opacity: 0;
                pointer-events: none;
            }
            
            .splash-logo {
                width: 120px;
                height: 120px;
                background: white;
                border-radius: 24px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 24px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                animation: splash-bounce 2s ease-in-out infinite;
            }

            .splash-logo .splash-initial {
                font-size: 52px;
                font-weight: 700;
                color: {{ $brandColor }};
                line-height: 1;
            }

            .splash-logo img {
                width: 100%;
                height: 100%;
                object-fit: contain;
                padding: 16px;
            }
            
            .splash-text {
                color: white;
                font-size: 28px;
                font-weight: 700;
                margin-bottom: 8px;
                letter-spacing: -0.5px;
            }
            
            .splash-tagline {
                color: rgba(255, 255, 255, 0.9);
                font-size: 15px;
                font-weight: 500;
                margin-bottom: 48px;
                text-align: center;
                max-width: 280px;
            }
            
            .splash-progress-container {
                width: 200px;
                height: 3px;
                background: rgba(255, 255, 255, 0.2);
                border-radius: 2px;
                overflow: hidden;
            }
            
            .splash-progress-bar {
                height: 100%;
                background: white;
                border-radius: 2px;
                width: 0%;
                animation: splash-progress 1.5s ease-in-out infinite;
            }
            
            @keyframes splash-progress {
                0% { width: 0%; margin-left: 0%; }
                50% { width: 70%; margin-left: 15%; }
                100% { width: 100%; margin-left: 0%; }
            }
            
            @keyframes splash-bounce {
                0%, 100% { transform: translateY(0) scale(1); }
                50% { transform: translateY(-8px) scale(1.02); }
            }
        </style>
        
        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ $docFavicon }}">
        <link rel="apple-touch-icon" href="{{ $docFavicon }}">
        
        <!-- PWA Meta Tags -->
        <meta name="theme-color" content="{{ $brandColor }}">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="{{ $companyName }}">
        <meta name="application-name" content="{{ $companyName }}">
        <meta name="format-detection" content="telephone=no">
        <meta name="msapplication-TileColor" content="{{ $brandColor }}">
        <meta name="msapplication-tap-highlight" content="no">

        @if($isGbSite)
        <!-- GrowBuilder Site SEO Meta Tags -->
        @if($docDescription)<meta name="description" content="{{ $docDescription }}">@endif
        <meta property="og:site_name" content="{{ $siteName }}">
        <meta property="og:title" content="{{ $docTitle }}">
        @if($docDescription)<meta property="og:description" content="{{ $docDescription }}">@endif
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ $docUrl }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $docTitle }}">
        @if($docDescription)<meta name="twitter:description" content="{{ $docDescription }}">@endif
        <link rel="canonical" href="{{ $docUrl }}">
        @if($docOgImage)
        <meta property="og:image" content="{{ $docOgImage }}">
        <meta name="twitter:image" content="{{ $docOgImage }}">
        @endif
        
        @if($jsonLdString)
        <!-- JSON-LD Structured Data -->
        <script type="application/ld+json">
        {!! $jsonLdString !!}
        </script>
        @endif
        @endif
        
        <!-- PWA Manifest (disabled for StockFlow subdomains and GrowBuilder custom sites) -->
        @if(!$sfCompany && !$isGbSite)
        <link rel="manifest" href="/manifest.json">
        @endif
        
        <!-- Apple Splash Screens -->
        <link rel="apple-touch-startup-image" href="/splash.html" />
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
        
        <!-- Standard Favicon Sizes -->
        <link rel="icon" type="image/png" sizes="32x32" href="{{ $docFavicon }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ $docFavicon }}">

        <title inertia>{{ $docTitle }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @if(str_starts_with(request()->route()?->getName() ?? '', 'stockflow.sub.'))
        <script>
            window.__sfSubdomain = true;
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.getRegistrations().then(function(r) { r.forEach(function(s) { s.unregister(); }); });
            }
        </script>
        @endif
        @routes
        @vite(['resources/js/app.ts'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        <!-- Splash Screen -->
        <div id="app-splash">
            <div class="splash-logo">
                @if($sfCompany && !empty($sfCompany->settings['logo_url']))
                <img src="{{ $sfCompany->settings['logo_url'] }}" alt="{{ $companyName }} Logo">
                @elseif($sfCompany)
                <span class="splash-initial">{{ $companyInitial }}</span>
                @else
                <img src="/logo.png" alt="MyGrowNet">
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
            
            // Register Service Worker (disabled for StockFlow subdomains)
            if ('serviceWorker' in navigator && !window.__sfSubdomain) {
                window.addEventListener('load', function() {
                    navigator.serviceWorker.register('/sw.js')
                        .then(function(registration) {
                            console.log('ServiceWorker registered:', registration.scope);
                        })
                        .catch(function(error) {
                            console.log('ServiceWorker registration failed:', error);
                        });
                });
            }
            
            // Make sure beforeinstallprompt event can fire
            console.log('Listening for beforeinstallprompt event...');
            window.addEventListener('beforeinstallprompt', function(e) {
                console.log('beforeinstallprompt event captured in app.blade.php');
                // Don't prevent default here - let Vue components handle it
            });
            
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
