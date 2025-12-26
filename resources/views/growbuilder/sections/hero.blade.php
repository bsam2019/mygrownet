@php
    $bgColor = $style['backgroundColor'] ?? 'var(--primary-color)';
    $textColor = $style['textColor'] ?? '#ffffff';
@endphp

<section class="relative py-20 md:py-32" style="background-color: {{ $bgColor }}; color: {{ $textColor }}">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6">
            {{ $content['title'] ?? 'Welcome' }}
        </h1>
        
        @if(isset($content['subtitle']))
        <p class="text-xl md:text-2xl opacity-90 mb-8 max-w-2xl mx-auto">
            {{ $content['subtitle'] }}
        </p>
        @endif
        
        @if(isset($content['buttonText']))
        <a 
            href="{{ $content['buttonLink'] ?? '#' }}"
            class="inline-block px-8 py-3 bg-white text-gray-900 font-semibold rounded-lg hover:bg-gray-100 transition"
        >
            {{ $content['buttonText'] }}
        </a>
        @endif
    </div>
</section>
