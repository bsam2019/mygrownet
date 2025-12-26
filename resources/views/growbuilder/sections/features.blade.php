<section class="py-16 md:py-24 bg-white" id="features">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12" style="color: var(--primary-color)">
            {{ $content['title'] ?? 'Features' }}
        </h2>
        
        @if(isset($content['items']) && count($content['items']) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @foreach($content['items'] as $item)
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center" style="background-color: var(--accent-color)">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-1">{{ $item['title'] ?? '' }}</h3>
                    <p class="text-gray-600">{{ $item['description'] ?? '' }}</p>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</section>
