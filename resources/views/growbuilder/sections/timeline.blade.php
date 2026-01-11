<section class="py-16 md:py-24" id="timeline" style="background-color: {{ $style['backgroundColor'] ?? '#18181b' }}; color: {{ $style['textColor'] ?? '#ffffff' }};">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(isset($content['title']))
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-4">
            {{ $content['title'] }}
        </h2>
        @endif
        
        @if(isset($content['subtitle']))
        <p class="text-lg text-center mb-12 opacity-80">
            {{ $content['subtitle'] }}
        </p>
        @endif
        
        @if(isset($content['items']) && count($content['items']) > 0)
        <div class="relative">
            @if(($content['layout'] ?? 'vertical') === 'horizontal')
            <div class="flex flex-col md:flex-row justify-between items-start gap-8">
                @foreach($content['items'] as $item)
                <div class="flex-1 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center text-2xl font-bold" style="background-color: {{ $style['lineColor'] ?? '#f59e0b' }}; color: {{ $style['backgroundColor'] ?? '#18181b' }};">
                        {{ $item['year'] ?? $loop->iteration }}
                    </div>
                    <h3 class="text-xl font-semibold mb-2">{{ $item['title'] ?? '' }}</h3>
                    <p class="opacity-80">{{ $item['description'] ?? '' }}</p>
                </div>
                @endforeach
            </div>
            @else
            <div class="space-y-8">
                @foreach($content['items'] as $item)
                <div class="flex gap-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center font-bold" style="background-color: {{ $style['lineColor'] ?? '#f59e0b' }}; color: {{ $style['backgroundColor'] ?? '#18181b' }};">
                        {{ $item['year'] ?? $loop->iteration }}
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold mb-1">{{ $item['title'] ?? '' }}</h3>
                        <p class="opacity-80">{{ $item['description'] ?? '' }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
        @endif
    </div>
</section>
