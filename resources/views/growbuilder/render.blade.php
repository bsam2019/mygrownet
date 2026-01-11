@php
    $splashEnabled = ($settings['splash']['enabled'] ?? true) !== false;
    $splashStyle = $settings['splash']['style'] ?? 'minimal';
    $splashTagline = $settings['splash']['tagline'] ?? '';
    $primaryColor = $site['theme']['primaryColor'] ?? '#2563eb';
    $navSettings = $settings['navigation'] ?? [];
    $footerSettings = $settings['footer'] ?? [];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page['metaTitle'] ?? $page['title'] }} - {{ $site['name'] }}</title>
    
    @if($page['metaDescription'])
    <meta name="description" content="{{ $page['metaDescription'] }}">
    @endif
    
    <!-- Open Graph -->
    <meta property="og:title" content="{{ $page['metaTitle'] ?? $page['title'] }}">
    @if($page['metaDescription'])
    <meta property="og:description" content="{{ $page['metaDescription'] }}">
    @endif
    @if($page['ogImage'])
    <meta property="og:image" content="{{ $page['ogImage'] }}">
    @endif
    
    <!-- Favicons -->
    @if(!empty($site['favicons']))
        <link rel="icon" type="image/png" sizes="32x32" href="{{ $site['favicons']['favicon-32x32.png'] ?? $site['favicon'] }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ $site['favicons']['favicon-16x16.png'] ?? $site['favicon'] }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ $site['favicons']['apple-touch-icon.png'] ?? $site['favicon'] }}">
    @elseif($site['favicon'])
        <link rel="icon" href="{{ $site['favicon'] }}">
        <link rel="apple-touch-icon" href="{{ $site['favicon'] }}">
    @endif
    
    <!-- Tailwind CSS CDN for rendered sites -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: {{ $primaryColor }};
            --secondary-color: {{ $site['theme']['secondaryColor'] ?? '#64748b' }};
            --accent-color: {{ $site['theme']['accentColor'] ?? '#059669' }};
            --background-color: {{ $site['theme']['backgroundColor'] ?? '#ffffff' }};
            --text-color: {{ $site['theme']['textColor'] ?? '#1f2937' }};
            --border-radius: {{ $site['theme']['borderRadius'] ?? 8 }}px;
        }
        
        body {
            font-family: '{{ $site['theme']['bodyFont'] ?? 'Inter' }}', sans-serif;
            color: var(--text-color);
            background-color: var(--background-color);
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: '{{ $site['theme']['headingFont'] ?? 'Inter' }}', sans-serif;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-radius: var(--border-radius);
        }
        
        .btn-primary:hover {
            filter: brightness(1.1);
        }
        
        /* Splash Screen Styles */
        .splash-overlay {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            transition: opacity 0.5s ease, transform 0.5s ease;
        }
        
        .splash-overlay.hiding {
            opacity: 0;
            transform: scale(1.02);
            pointer-events: none;
        }
        
        .splash-overlay.hidden {
            display: none;
        }
        
        .splash-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            padding: 2rem;
        }
        
        .splash-logo-wrapper {
            width: 80px;
            height: 80px;
            margin-bottom: 1.5rem;
        }
        
        .splash-logo {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        .splash-logo-text {
            width: 100%;
            height: 100%;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            background-color: var(--primary-color);
        }
        
        .splash-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
            text-align: center;
        }
        
        .splash-tagline {
            font-size: 1rem;
            color: #6b7280;
            text-align: center;
        }
        
        /* Minimal style */
        .splash-minimal .splash-logo-wrapper {
            animation: minimalBounce 1.5s ease-in-out infinite;
        }
        
        .minimal-loader {
            width: 200px;
            height: 4px;
            border-radius: 2px;
            margin-top: 2rem;
            overflow: hidden;
            background-color: rgba(37, 99, 235, 0.2);
        }
        
        .minimal-loader-bar {
            height: 100%;
            width: 0%;
            border-radius: 2px;
            background-color: var(--primary-color);
            animation: minimalLoad 1.5s ease-in-out infinite;
        }
        
        @keyframes minimalBounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        @keyframes minimalLoad {
            0% { width: 0%; margin-left: 0; }
            50% { width: 70%; margin-left: 0; }
            100% { width: 0%; margin-left: 100%; }
        }
        
        /* Pulse style */
        .pulse-container {
            position: relative;
            width: 120px;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }
        
        .pulse-container .splash-logo-wrapper {
            position: relative;
            z-index: 2;
            margin-bottom: 0;
        }
        
        .pulse-ring {
            position: absolute;
            width: 100%;
            height: 100%;
            border: 3px solid rgba(37, 99, 235, 0.2);
            border-radius: 50%;
            animation: pulseRing 2s ease-out infinite;
        }
        
        .pulse-ring.delay-1 { animation-delay: 0.4s; }
        .pulse-ring.delay-2 { animation-delay: 0.8s; }
        
        @keyframes pulseRing {
            0% { transform: scale(0.8); opacity: 1; }
            100% { transform: scale(1.8); opacity: 0; }
        }
        
        /* Gradient style */
        .splash-gradient {
            background: linear-gradient(135deg, var(--primary-color) 0%, color-mix(in srgb, var(--primary-color) 70%, #000) 100%);
        }
        
        .splash-gradient .splash-title,
        .splash-gradient .splash-tagline {
            color: white;
        }
        
        .splash-gradient .splash-tagline {
            opacity: 0.8;
        }
        
        .splash-gradient .splash-logo-text {
            background: white;
            color: var(--primary-color);
        }
        
        .gradient-float {
            animation: gradientFloat 2s ease-in-out infinite;
        }
        
        @keyframes gradientFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }
        
        .gradient-dots {
            display: flex;
            gap: 0.5rem;
            margin-top: 2rem;
        }
        
        .gradient-dots .dot {
            width: 10px;
            height: 10px;
            background: white;
            border-radius: 50%;
            animation: dotPulse 1.4s ease-in-out infinite;
        }
        
        .gradient-dots .dot:nth-child(2) { animation-delay: 0.2s; }
        .gradient-dots .dot:nth-child(3) { animation-delay: 0.4s; }
        
        @keyframes dotPulse {
            0%, 80%, 100% { transform: scale(0.6); opacity: 0.5; }
            40% { transform: scale(1); opacity: 1; }
        }
        
        /* Elegant style */
        .splash-elegant {
            background: #fafafa;
        }
        
        .elegant-line {
            width: 60px;
            height: 3px;
            border-radius: 2px;
            background-color: var(--primary-color);
            margin-bottom: 2rem;
        }
        
        .elegant-line.bottom {
            margin-top: 2rem;
            margin-bottom: 1.5rem;
        }
        
        .elegant-scale {
            animation: elegantScale 2s ease-in-out infinite;
        }
        
        @keyframes elegantScale {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .elegant-spinner {
            width: 24px;
            height: 24px;
            border: 2px solid #e5e7eb;
            border-top-color: var(--primary-color);
            border-radius: 50%;
            animation: elegantSpin 1s linear infinite;
        }
        
        @keyframes elegantSpin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="min-h-screen">
    <!-- Splash Screen -->
    @if($splashEnabled && $splashStyle !== 'none')
    <div id="splash-screen" class="splash-overlay splash-{{ $splashStyle }}">
        <div class="splash-content">
            @if($splashStyle === 'minimal')
                <div class="splash-logo-wrapper">
                    @if($navSettings['logo'] ?? $site['logo'])
                        <img src="{{ $navSettings['logo'] ?? $site['logo'] }}" alt="{{ $site['name'] }}" class="splash-logo">
                    @else
                        <div class="splash-logo-text">{{ substr($site['name'], 0, 1) }}</div>
                    @endif
                </div>
                <h1 class="splash-title">{{ $site['name'] }}</h1>
                @if($splashTagline)
                    <p class="splash-tagline">{{ $splashTagline }}</p>
                @endif
                <div class="minimal-loader">
                    <div class="minimal-loader-bar"></div>
                </div>
            @elseif($splashStyle === 'pulse')
                <div class="pulse-container">
                    <div class="pulse-ring"></div>
                    <div class="pulse-ring delay-1"></div>
                    <div class="pulse-ring delay-2"></div>
                    <div class="splash-logo-wrapper">
                        @if($navSettings['logo'] ?? $site['logo'])
                            <img src="{{ $navSettings['logo'] ?? $site['logo'] }}" alt="{{ $site['name'] }}" class="splash-logo">
                        @else
                            <div class="splash-logo-text">{{ substr($site['name'], 0, 1) }}</div>
                        @endif
                    </div>
                </div>
                <h1 class="splash-title">{{ $site['name'] }}</h1>
                @if($splashTagline)
                    <p class="splash-tagline">{{ $splashTagline }}</p>
                @endif
            @elseif($splashStyle === 'gradient')
                <div class="splash-logo-wrapper gradient-float">
                    @if($navSettings['logo'] ?? $site['logo'])
                        <img src="{{ $navSettings['logo'] ?? $site['logo'] }}" alt="{{ $site['name'] }}" class="splash-logo">
                    @else
                        <div class="splash-logo-text">{{ substr($site['name'], 0, 1) }}</div>
                    @endif
                </div>
                <h1 class="splash-title">{{ $site['name'] }}</h1>
                @if($splashTagline)
                    <p class="splash-tagline">{{ $splashTagline }}</p>
                @endif
                <div class="gradient-dots">
                    <span class="dot"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>
                </div>
            @elseif($splashStyle === 'elegant')
                <div class="elegant-line"></div>
                <div class="splash-logo-wrapper elegant-scale">
                    @if($navSettings['logo'] ?? $site['logo'])
                        <img src="{{ $navSettings['logo'] ?? $site['logo'] }}" alt="{{ $site['name'] }}" class="splash-logo">
                    @else
                        <div class="splash-logo-text" style="background: transparent; border: 3px solid var(--primary-color); color: var(--primary-color);">{{ substr($site['name'], 0, 1) }}</div>
                    @endif
                </div>
                <h1 class="splash-title">{{ $site['name'] }}</h1>
                @if($splashTagline)
                    <p class="splash-tagline" style="font-style: italic;">{{ $splashTagline }}</p>
                @endif
                <div class="elegant-line bottom"></div>
                <div class="elegant-spinner"></div>
            @else
                {{-- Default/wave/particles - use minimal as fallback --}}
                <div class="splash-logo-wrapper">
                    @if($navSettings['logo'] ?? $site['logo'])
                        <img src="{{ $navSettings['logo'] ?? $site['logo'] }}" alt="{{ $site['name'] }}" class="splash-logo">
                    @else
                        <div class="splash-logo-text">{{ substr($site['name'], 0, 1) }}</div>
                    @endif
                </div>
                <h1 class="splash-title">{{ $site['name'] }}</h1>
                @if($splashTagline)
                    <p class="splash-tagline">{{ $splashTagline }}</p>
                @endif
                <div class="minimal-loader">
                    <div class="minimal-loader-bar"></div>
                </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Navigation -->
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    @if($navSettings['logo'] ?? $site['logo'])
                        <a href="/">
                            <img src="{{ $navSettings['logo'] ?? $site['logo'] }}" alt="{{ $site['name'] }}" class="h-8">
                        </a>
                    @else
                        <a href="/" class="text-xl font-bold" style="color: var(--primary-color)">
                            {{ $navSettings['logoText'] ?? $site['name'] }}
                        </a>
                    @endif
                </div>
                
                <div class="hidden md:flex items-center space-x-8">
                    @foreach($navigation as $navItem)
                        <a 
                            href="{{ $navItem['isHomepage'] ? '/' : '/' . $navItem['slug'] }}"
                            class="text-gray-600 hover:text-gray-900 transition"
                        >
                            {{ $navItem['title'] }}
                        </a>
                    @endforeach
                    
                    @if($navSettings['showCta'] ?? false)
                        <a 
                            href="{{ $navSettings['ctaLink'] ?? '#contact' }}"
                            class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition"
                            style="background-color: var(--primary-color);"
                        >
                            {{ $navSettings['ctaText'] ?? 'Contact Us' }}
                        </a>
                    @endif
                </div>
                
                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button type="button" class="text-gray-600" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile menu -->
        <div id="mobile-menu" class="hidden md:hidden border-t">
            <div class="px-4 py-2 space-y-1">
                @foreach($navigation as $navItem)
                    <a 
                        href="{{ $navItem['isHomepage'] ? '/' : '/' . $navItem['slug'] }}"
                        class="block py-2 text-gray-600 hover:text-gray-900"
                    >
                        {{ $navItem['title'] }}
                    </a>
                @endforeach
                
                @if($navSettings['showCta'] ?? false)
                    <a 
                        href="{{ $navSettings['ctaLink'] ?? '#contact' }}"
                        class="block w-full mt-3 px-4 py-3 text-white text-center font-medium rounded-lg transition"
                        style="background-color: var(--primary-color);"
                    >
                        {{ $navSettings['ctaText'] ?? 'Contact Us' }}
                    </a>
                @endif
            </div>
        </div>
    </nav>

    <!-- Page Content -->
    <main>
        @foreach($page['sections'] as $section)
            @php
                $sectionView = 'growbuilder.sections.' . $section['type'];
                $viewExists = view()->exists($sectionView);
            @endphp
            
            @if($viewExists)
                @include($sectionView, [
                    'content' => $section['content'], 
                    'style' => $section['style'] ?? [],
                    'site' => $site,
                    'subdomain' => $subdomain ?? $site['subdomain'] ?? ''
                ])
            @else
                {{-- Fallback for missing section types --}}
                @include('growbuilder.sections.fallback', [
                    'sectionType' => $section['type'],
                    'content' => $section['content'], 
                    'style' => $section['style'] ?? [],
                ])
            @endif
        @endforeach
    </main>

    @if($site['hasEcommerce'] ?? false)
        @include('growbuilder.partials.cart')
    @endif

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12" style="background-color: {{ $footerSettings['backgroundColor'] ?? '#111827' }}; color: {{ $footerSettings['textColor'] ?? '#ffffff' }};">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4">{{ $site['name'] }}</h3>
                    @if($site['description'])
                        <p class="text-gray-400">{{ $site['description'] }}</p>
                    @endif
                </div>
                
                @if(!empty($site['contactInfo']))
                <div>
                    <h3 class="text-lg font-semibold mb-4">Contact</h3>
                    <div class="space-y-2 text-gray-400">
                        @if(isset($site['contactInfo']['phone']))
                            <p>{{ $site['contactInfo']['phone'] }}</p>
                        @endif
                        @if(isset($site['contactInfo']['email']))
                            <p>{{ $site['contactInfo']['email'] }}</p>
                        @endif
                        @if(isset($site['contactInfo']['address']))
                            <p>{{ $site['contactInfo']['address'] }}</p>
                        @endif
                    </div>
                </div>
                @endif
                
                @if(!empty($site['socialLinks']))
                <div>
                    <h3 class="text-lg font-semibold mb-4">Follow Us</h3>
                    <div class="flex space-x-4">
                        @if(isset($site['socialLinks']['facebook']))
                            <a href="{{ $site['socialLinks']['facebook'] }}" class="text-gray-400 hover:text-white" target="_blank">Facebook</a>
                        @endif
                        @if(isset($site['socialLinks']['whatsapp']))
                            <a href="https://wa.me/{{ $site['socialLinks']['whatsapp'] }}" class="text-gray-400 hover:text-white" target="_blank">WhatsApp</a>
                        @endif
                    </div>
                </div>
                @endif
            </div>
            
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400 text-sm">
                <p>{{ $footerSettings['copyrightText'] ?? '© ' . date('Y') . ' ' . $site['name'] . '. All rights reserved.' }}</p>
                <p class="mt-2">
                    <a href="https://mygrownet.com/growbuilder" class="hover:text-white">Powered by GrowBuilder</a>
                </p>
            </div>
        </div>
    </footer>
    
    @if($site['hasEcommerce'] ?? false)
        @include('growbuilder.partials.cart-scripts')
    @endif
    
    <!-- Splash Screen Script -->
    @if($splashEnabled && $splashStyle !== 'none')
    <script>
        (function() {
            const splash = document.getElementById('splash-screen');
            if (!splash) return;
            
            function hideSplash() {
                splash.classList.add('hiding');
                setTimeout(() => {
                    splash.classList.add('hidden');
                }, 500);
            }
            
            // Hide after 1.5 seconds
            setTimeout(hideSplash, 1500);
        })();
    </script>
    @endif
</body>
</html>
