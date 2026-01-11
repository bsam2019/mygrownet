@php
    $title = $content['title'] ?? 'Page Title';
    $subtitle = $content['subtitle'] ?? '';
    $backgroundImage = $content['backgroundImage'] ?? null;
    $textPosition = $content['textPosition'] ?? 'center';
    
    $bgColor = $style['backgroundColor'] ?? '#1e40af';
    $textColor = $style['textColor'] ?? '#ffffff';
    
    $alignClass = match($textPosition) {
        'left' => 'text-left',
        'right' => 'text-right',
        default => 'text-center',
    };
@endphp

<section 
    class="py-16 md:py-24 relative"
    style="background-color: {{ $bgColor }}; color: {{ $textColor }};"
>
    @if($backgroundImage)
        <div class="absolute inset-0 z-0">
            <img src="{{ $backgroundImage }}" alt="" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-black/50"></div>
        </div>
    @endif
    
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="{{ $alignClass }}">
            <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold mb-4">{{ $title }}</h1>
            @if($subtitle)
                <p class="text-lg md:text-xl opacity-90 max-w-2xl {{ $textPosition === 'center' ? 'mx-auto' : '' }}">
                    {{ $subtitle }}
                </p>
            @endif
        </div>
    </div>
</section>
