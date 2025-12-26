<section class="py-16 md:py-24 bg-gray-50" id="services">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12" style="color: var(--primary-color)">
            {{ $content['title'] ?? 'Our Services' }}
        </h2>
        
        @if(isset($content['items']) && count($content['items']) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($content['items'] as $item)
            <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center mb-4" style="background-color: var(--primary-color)">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">{{ $item['title'] ?? '' }}</h3>
                <p class="text-gray-600">{{ $item['description'] ?? '' }}</p>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</section>
