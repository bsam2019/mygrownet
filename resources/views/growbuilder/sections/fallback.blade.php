@php
    $bgColor = $style['backgroundColor'] ?? '#f9fafb';
    $textColor = $style['textColor'] ?? '#111827';
    $title = $content['title'] ?? '';
@endphp

{{-- Fallback section for unknown section types --}}
<section class="py-16" style="background-color: {{ $bgColor }}; color: {{ $textColor }};">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($title)
            <h2 class="text-3xl font-bold mb-6 text-center">{{ $title }}</h2>
        @endif
        
        {{-- Render any text content that might exist --}}
        @if(isset($content['description']))
            <p class="text-lg text-center max-w-3xl mx-auto">{{ $content['description'] }}</p>
        @elseif(isset($content['content']))
            <div class="prose max-w-3xl mx-auto">
                {!! $content['content'] !!}
            </div>
        @elseif(isset($content['subtitle']))
            <p class="text-lg text-center max-w-3xl mx-auto">{{ $content['subtitle'] }}</p>
        @endif
        
        {{-- Render items if they exist --}}
        @if(isset($content['items']) && is_array($content['items']) && count($content['items']) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
                @foreach($content['items'] as $item)
                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        @if(isset($item['title']))
                            <h3 class="font-semibold text-lg mb-2">{{ $item['title'] }}</h3>
                        @elseif(isset($item['name']))
                            <h3 class="font-semibold text-lg mb-2">{{ $item['name'] }}</h3>
                        @endif
                        
                        @if(isset($item['description']))
                            <p class="text-gray-600">{{ $item['description'] }}</p>
                        @elseif(isset($item['text']))
                            <p class="text-gray-600">{{ $item['text'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
