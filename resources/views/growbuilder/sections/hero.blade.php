@php
    $layout = $content['layout'] ?? 'centered';
    $title = $content['title'] ?? 'Welcome';
    $subtitle = $content['subtitle'] ?? '';
    $buttonText = $content['buttonText'] ?? '';
    $buttonLink = $content['buttonLink'] ?? '#';
    $secondaryButtonText = $content['secondaryButtonText'] ?? '';
    $secondaryButtonLink = $content['secondaryButtonLink'] ?? '#';
    $backgroundImage = $content['backgroundImage'] ?? null;
    $videoBackground = $content['videoBackground'] ?? null;
    $backgroundType = $content['backgroundType'] ?? 'solid';
    $image = $content['image'] ?? null;
    $textPosition = $content['textPosition'] ?? 'center';
    $slides = $content['slides'] ?? [];
    
    // Overlay settings
    $overlay = $content['overlay'] ?? true;
    $overlayColor = $content['overlayColor'] ?? 'black';
    $overlayOpacity = $content['overlayOpacity'] ?? 50;
    
    // Style
    $bgColor = $style['backgroundColor'] ?? '#1e40af';
    $textColor = $style['textColor'] ?? '#ffffff';
    $minHeight = ($style['minHeight'] ?? 500) . 'px';
    
    // Gradient
    $gradientFrom = $style['gradientFrom'] ?? null;
    $gradientTo = $style['gradientTo'] ?? null;
    $gradientDirection = $style['gradientDirection'] ?? 'to-r';
    
    $hasBackground = $backgroundImage || $videoBackground;
    
    // Text alignment
    $alignClass = match($textPosition) {
        'left' => 'text-left items-start',
        'right' => 'text-right items-end',
        default => 'text-center items-center',
    };
@endphp

@if($layout === 'slideshow' && count($slides) > 0)
    {{-- Slideshow Layout --}}
    <section class="relative overflow-hidden" style="min-height: {{ $minHeight }};">
        <div class="slideshow-container relative w-full h-full" style="min-height: {{ $minHeight }};">
            @foreach($slides as $index => $slide)
                <div class="slide absolute inset-0 transition-opacity duration-500 {{ $index === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0' }}" data-slide="{{ $index }}">
                    @if($slide['backgroundImage'] ?? null)
                        <img src="{{ $slide['backgroundImage'] }}" alt="{{ $slide['title'] ?? '' }}" class="w-full h-full object-cover">
                    @endif
                    <div class="absolute inset-0 bg-black/40"></div>
                    <div class="absolute inset-0 flex flex-col justify-center items-center text-center px-4 sm:px-6 lg:px-8">
                        <h1 class="font-bold mb-4 text-3xl sm:text-4xl lg:text-5xl text-white">{{ $slide['title'] ?? '' }}</h1>
                        @if($slide['subtitle'] ?? null)
                            <p class="mb-6 text-base sm:text-lg text-white/90 max-w-2xl">{{ $slide['subtitle'] }}</p>
                        @endif
                        @if($slide['buttonText'] ?? null)
                            <a href="{{ $slide['buttonLink'] ?? '#' }}" class="px-6 py-3 bg-white text-blue-600 font-semibold rounded-lg hover:bg-gray-100 transition">
                                {{ $slide['buttonText'] }}
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
            
            {{-- Slide Navigation Dots --}}
            <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-20 flex gap-2">
                @foreach($slides as $index => $slide)
                    <button 
                        onclick="goToSlide({{ $index }})"
                        class="slide-dot w-3 h-3 rounded-full transition-colors {{ $index === 0 ? 'bg-white' : 'bg-white/50 hover:bg-white/75' }}"
                        data-dot="{{ $index }}"
                        aria-label="Go to slide {{ $index + 1 }}"
                    ></button>
                @endforeach
            </div>
            
            {{-- Slide Arrows --}}
            <button 
                onclick="prevSlide()"
                class="absolute left-4 top-1/2 -translate-y-1/2 z-20 w-10 h-10 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center text-white transition"
                aria-label="Previous slide"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            </button>
            <button 
                onclick="nextSlide()"
                class="absolute right-4 top-1/2 -translate-y-1/2 z-20 w-10 h-10 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center text-white transition"
                aria-label="Next slide"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>
        </div>
        
        <script>
            (function() {
                let currentSlide = 0;
                const slides = document.querySelectorAll('.slide');
                const dots = document.querySelectorAll('.slide-dot');
                const totalSlides = {{ count($slides) }};
                const autoPlay = {{ ($content['autoPlay'] ?? false) ? 'true' : 'false' }};
                const interval = {{ $content['slideInterval'] ?? 5000 }};
                
                window.goToSlide = function(index) {
                    slides.forEach((slide, i) => {
                        slide.classList.toggle('opacity-100', i === index);
                        slide.classList.toggle('z-10', i === index);
                        slide.classList.toggle('opacity-0', i !== index);
                        slide.classList.toggle('z-0', i !== index);
                    });
                    dots.forEach((dot, i) => {
                        dot.classList.toggle('bg-white', i === index);
                        dot.classList.toggle('bg-white/50', i !== index);
                    });
                    currentSlide = index;
                };
                
                window.nextSlide = function() {
                    goToSlide((currentSlide + 1) % totalSlides);
                };
                
                window.prevSlide = function() {
                    goToSlide((currentSlide - 1 + totalSlides) % totalSlides);
                };
                
                if (autoPlay && totalSlides > 1) {
                    setInterval(nextSlide, interval);
                }
            })();
        </script>
    </section>

@elseif($layout === 'split-left' || $layout === 'split-right')
    {{-- Split Layout --}}
    <section class="relative overflow-hidden" style="background-color: {{ $bgColor }}; color: {{ $textColor }}; min-height: {{ $minHeight }};">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
            <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 items-center">
                {{-- Text Content --}}
                <div class="{{ $layout === 'split-left' ? 'lg:order-2' : 'lg:order-1' }}">
                    <h1 class="font-bold mb-4 text-3xl sm:text-4xl lg:text-5xl">{{ $title }}</h1>
                    @if($subtitle)
                        <p class="mb-6 text-base sm:text-lg opacity-90">{{ $subtitle }}</p>
                    @endif
                    <div class="flex flex-wrap gap-3">
                        @if($buttonText)
                            <a href="{{ $buttonLink }}" class="px-6 py-3 bg-white text-blue-600 font-semibold rounded-lg hover:bg-gray-100 transition">
                                {{ $buttonText }}
                            </a>
                        @endif
                        @if($secondaryButtonText)
                            <a href="{{ $secondaryButtonLink }}" class="px-6 py-3 border-2 border-white/50 font-semibold rounded-lg hover:bg-white/10 transition">
                                {{ $secondaryButtonText }}
                            </a>
                        @endif
                    </div>
                </div>
                
                {{-- Image --}}
                <div class="{{ $layout === 'split-left' ? 'lg:order-1' : 'lg:order-2' }}">
                    @if($image)
                        <img src="{{ $image }}" alt="{{ $title }}" class="w-full h-auto rounded-xl shadow-2xl">
                    @endif
                </div>
            </div>
        </div>
    </section>

@else
    {{-- Centered Layout (default) --}}
    <section 
        class="relative overflow-hidden flex flex-col justify-center {{ $alignClass }}"
        style="
            @if($backgroundType === 'gradient' && $gradientFrom && $gradientTo)
                background: linear-gradient({{ $gradientDirection === 'to-b' ? '180deg' : ($gradientDirection === 'to-br' ? '135deg' : '90deg') }}, {{ $gradientFrom }}, {{ $gradientTo }});
            @else
                background-color: {{ $bgColor }};
            @endif
            color: {{ $textColor }};
            min-height: {{ $minHeight }};
        "
    >
        {{-- Background Image --}}
        @if($backgroundImage && $backgroundType !== 'gradient')
            <div class="absolute inset-0 z-0">
                <img src="{{ $backgroundImage }}" alt="" class="w-full h-full object-cover">
            </div>
        @endif
        
        {{-- Video Background --}}
        @if($videoBackground && $backgroundType === 'video')
            <div class="absolute inset-0 z-0 overflow-hidden">
                <video autoplay muted loop playsinline class="w-full h-full object-cover">
                    <source src="{{ $videoBackground }}" type="video/mp4">
                </video>
            </div>
        @endif
        
        {{-- Overlay --}}
        @if($hasBackground && $overlay)
            <div 
                class="absolute inset-0 z-10"
                style="
                    @if($overlayColor === 'gradient')
                        background: linear-gradient(135deg, rgba(37, 99, 235, {{ $overlayOpacity / 100 }}) 0%, rgba(124, 58, 237, {{ $overlayOpacity / 100 }}) 100%);
                    @elseif($overlayColor === 'white')
                        background-color: rgba(255, 255, 255, {{ $overlayOpacity / 100 }});
                    @else
                        background-color: rgba(0, 0, 0, {{ $overlayOpacity / 100 }});
                    @endif
                "
            ></div>
        @endif
        
        {{-- Content --}}
        <div class="relative z-20 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-32 {{ $alignClass }}">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6">{{ $title }}</h1>
            
            @if($subtitle)
                <p class="text-xl md:text-2xl opacity-90 mb-8 max-w-2xl {{ $textPosition === 'center' ? 'mx-auto' : '' }}">
                    {{ $subtitle }}
                </p>
            @endif
            
            @if($buttonText || $secondaryButtonText)
                <div class="flex flex-wrap gap-4 {{ $textPosition === 'center' ? 'justify-center' : '' }}">
                    @if($buttonText)
                        <a 
                            href="{{ $buttonLink }}"
                            class="inline-block px-8 py-3 bg-white text-gray-900 font-semibold rounded-lg hover:bg-gray-100 transition"
                        >
                            {{ $buttonText }}
                        </a>
                    @endif
                    
                    @if($secondaryButtonText)
                        <a 
                            href="{{ $secondaryButtonLink }}"
                            class="inline-block px-8 py-3 border-2 border-white/50 font-semibold rounded-lg hover:bg-white/10 transition"
                        >
                            {{ $secondaryButtonText }}
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </section>
@endif
