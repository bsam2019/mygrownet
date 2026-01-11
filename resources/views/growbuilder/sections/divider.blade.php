@php
    $dividerStyle = $content['style'] ?? 'line';
    $height = $content['height'] ?? 40;
    
    $bgColor = $style['backgroundColor'] ?? '#ffffff';
    $lineColor = $style['textColor'] ?? '#e5e7eb';
@endphp

<section style="background-color: {{ $bgColor }}; height: {{ $height }}px;">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-full flex items-center justify-center">
        @if($dividerStyle === 'line')
            <hr class="w-full border-t" style="border-color: {{ $lineColor }};">
        @elseif($dividerStyle === 'dots')
            <div class="flex gap-2">
                <span class="w-2 h-2 rounded-full" style="background-color: {{ $lineColor }};"></span>
                <span class="w-2 h-2 rounded-full" style="background-color: {{ $lineColor }};"></span>
                <span class="w-2 h-2 rounded-full" style="background-color: {{ $lineColor }};"></span>
            </div>
        @else
            {{-- space - just empty space --}}
        @endif
    </div>
</section>
