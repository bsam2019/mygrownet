<section class="py-16 md:py-24 bg-gray-50" id="gallery">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12" style="color: var(--primary-color)">
            {{ $content['title'] ?? 'Gallery' }}
        </h2>
        
        @if(isset($content['images']) && count($content['images']) > 0)
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($content['images'] as $image)
            <div class="aspect-square overflow-hidden rounded-lg">
                <img 
                    src="{{ $image['url'] ?? $image }}" 
                    alt="{{ $image['alt'] ?? 'Gallery image' }}"
                    class="w-full h-full object-cover hover:scale-105 transition duration-300"
                    loading="lazy"
                >
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-12 text-gray-500">
            <p>No images added yet</p>
        </div>
        @endif
    </div>
</section>
