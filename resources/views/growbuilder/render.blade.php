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
            --primary-color: {{ $site['theme']['primaryColor'] ?? '#2563eb' }};
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
    </style>
</head>
<body class="min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    @if($site['logo'])
                        <img src="{{ $site['logo'] }}" alt="{{ $site['name'] }}" class="h-8">
                    @else
                        <span class="text-xl font-bold" style="color: var(--primary-color)">{{ $site['name'] }}</span>
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
            </div>
        </div>
    </nav>

    <!-- Page Content -->
    <main>
        @foreach($page['sections'] as $section)
            @include('growbuilder.sections.' . $section['type'], [
                'content' => $section['content'], 
                'style' => $section['style'] ?? [],
                'site' => $site,
                'subdomain' => $subdomain ?? $site['subdomain'] ?? ''
            ])
        @endforeach
    </main>

    @if($site['hasEcommerce'] ?? false)
        @include('growbuilder.partials.cart')
    @endif

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
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
                <p>&copy; {{ date('Y') }} {{ $site['name'] }}. All rights reserved.</p>
                <p class="mt-2">
                    <a href="https://mygrownet.com/growbuilder" class="hover:text-white">Powered by GrowBuilder</a>
                </p>
            </div>
        </div>
    </footer>
    @if($site['hasEcommerce'] ?? false)
        @include('growbuilder.partials.cart-scripts')
    @endif
</body>
</html>
