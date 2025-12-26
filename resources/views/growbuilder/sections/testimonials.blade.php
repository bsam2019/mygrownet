<section class="py-16 md:py-24 bg-white" id="testimonials">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12" style="color: var(--primary-color)">
            {{ $content['title'] ?? 'What Our Customers Say' }}
        </h2>
        
        @if(isset($content['items']) && count($content['items']) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($content['items'] as $item)
            <div class="bg-gray-50 p-6 rounded-lg">
                <div class="flex items-center mb-4">
                    @if(isset($item['image']) && $item['image'])
                    <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-12 h-12 rounded-full mr-4">
                    @else
                    <div class="w-12 h-12 rounded-full mr-4 flex items-center justify-center text-white font-bold" style="background-color: var(--primary-color)">
                        {{ substr($item['name'] ?? 'A', 0, 1) }}
                    </div>
                    @endif
                    <div>
                        <p class="font-semibold">{{ $item['name'] ?? 'Anonymous' }}</p>
                        @if(isset($item['role']))
                        <p class="text-sm text-gray-500">{{ $item['role'] }}</p>
                        @endif
                    </div>
                </div>
                <p class="text-gray-600 italic">"{{ $item['text'] ?? '' }}"</p>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</section>
