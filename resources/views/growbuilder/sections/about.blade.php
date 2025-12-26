<section class="py-16 md:py-24 bg-white" id="about">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            <div>
                <h2 class="text-3xl md:text-4xl font-bold mb-6" style="color: var(--primary-color)">
                    {{ $content['title'] ?? 'About Us' }}
                </h2>
                <p class="text-lg text-gray-600 leading-relaxed">
                    {{ $content['description'] ?? '' }}
                </p>
            </div>
            
            @if(isset($content['image']) && $content['image'])
            <div>
                <img 
                    src="{{ $content['image'] }}" 
                    alt="{{ $content['title'] ?? 'About' }}"
                    class="rounded-lg shadow-lg w-full"
                >
            </div>
            @endif
        </div>
    </div>
</section>
