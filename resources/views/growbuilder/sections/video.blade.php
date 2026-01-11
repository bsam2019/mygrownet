@php
    $title = $content['title'] ?? '';
    $description = $content['description'] ?? '';
    $videoUrl = $content['videoUrl'] ?? '';
    
    $bgColor = $style['backgroundColor'] ?? '#ffffff';
    $textColor = $style['textColor'] ?? '#111827';
    
    // Convert YouTube/Vimeo URLs to embed format
    $embedUrl = '';
    if ($videoUrl) {
        if (str_contains($videoUrl, 'youtube.com/watch')) {
            preg_match('/v=([^&]+)/', $videoUrl, $matches);
            if (isset($matches[1])) {
                $embedUrl = 'https://www.youtube.com/embed/' . $matches[1];
            }
        } elseif (str_contains($videoUrl, 'youtu.be/')) {
            $embedUrl = 'https://www.youtube.com/embed/' . basename($videoUrl);
        } elseif (str_contains($videoUrl, 'vimeo.com/')) {
            $embedUrl = 'https://player.vimeo.com/video/' . basename($videoUrl);
        } else {
            $embedUrl = $videoUrl;
        }
    }
@endphp

<section class="py-16 md:py-24" style="background-color: {{ $bgColor }}; color: {{ $textColor }};">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($title)
            <h2 class="text-3xl md:text-4xl font-bold mb-4 text-center">{{ $title }}</h2>
        @endif
        
        @if($description)
            <p class="text-lg text-gray-600 mb-8 text-center max-w-2xl mx-auto">{{ $description }}</p>
        @endif
        
        @if($embedUrl)
            <div class="aspect-video rounded-xl overflow-hidden shadow-lg">
                <iframe 
                    src="{{ $embedUrl }}"
                    class="w-full h-full"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                ></iframe>
            </div>
        @else
            <div class="aspect-video bg-gray-100 rounded-xl flex items-center justify-center">
                <div class="text-center text-gray-500">
                    <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p>No video URL provided</p>
                </div>
            </div>
        @endif
    </div>
</section>
