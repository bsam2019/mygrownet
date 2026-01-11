<section class="py-16 md:py-24 bg-gray-50" id="stats">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(isset($content['title']))
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12" style="color: var(--primary-color)">
            {{ $content['title'] }}
        </h2>
        @endif
        
        @if(isset($content['subtitle']))
        <p class="text-lg text-gray-600 text-center mb-12 max-w-3xl mx-auto">
            {{ $content['subtitle'] }}
        </p>
        @endif
        
        @if(isset($content['items']) && count($content['items']) > 0)
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
            @foreach($content['items'] as $item)
            <div class="text-center">
                <div class="text-4xl md:text-5xl font-bold mb-2" style="color: var(--primary-color)">
                    {{ $item['value'] ?? $item['number'] ?? '0' }}
                </div>
                <div class="text-gray-600 font-medium">
                    {{ $item['label'] ?? $item['title'] ?? '' }}
                </div>
                @if(isset($item['description']))
                <p class="text-sm text-gray-500 mt-1">{{ $item['description'] }}</p>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>
</section>
