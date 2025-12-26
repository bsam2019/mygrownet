<section class="py-16 md:py-24" style="background-color: var(--primary-color)">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">
            {{ $content['title'] ?? 'Ready to Get Started?' }}
        </h2>
        
        @if(isset($content['description']))
        <p class="text-xl opacity-90 mb-8">{{ $content['description'] }}</p>
        @endif
        
        @if(isset($content['buttonText']))
        <a 
            href="{{ $content['buttonLink'] ?? '#' }}"
            class="inline-block px-8 py-3 bg-white font-semibold rounded-lg hover:bg-gray-100 transition"
            style="color: var(--primary-color)"
        >
            {{ $content['buttonText'] }}
        </a>
        @endif
    </div>
</section>
