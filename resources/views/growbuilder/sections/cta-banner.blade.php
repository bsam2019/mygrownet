<section class="py-16 md:py-24" id="cta-banner" style="background-color: {{ $style['backgroundColor'] ?? '#18181b' }}; color: {{ $style['textColor'] ?? '#ffffff' }};">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        @if(isset($content['title']))
        <h2 class="text-3xl md:text-4xl font-bold mb-4">
            {{ $content['title'] }}
        </h2>
        @endif
        
        @if(isset($content['subtitle']))
        <p class="text-lg mb-8 opacity-90">
            {{ $content['subtitle'] }}
        </p>
        @endif
        
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            @if(isset($content['buttonText']) && isset($content['buttonLink']))
            <a href="{{ $content['buttonLink'] }}" class="inline-block px-8 py-3 rounded-lg font-semibold transition" style="background-color: var(--primary-color); color: white;">
                {{ $content['buttonText'] }}
            </a>
            @endif
            
            @if(isset($content['secondaryButtonText']) && isset($content['secondaryButtonLink']))
            <a href="{{ $content['secondaryButtonLink'] }}" class="inline-block px-8 py-3 rounded-lg font-semibold border-2 border-current transition hover:bg-white/10">
                {{ $content['secondaryButtonText'] }}
            </a>
            @endif
        </div>
    </div>
</section>
