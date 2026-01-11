@php
    $title = $content['title'] ?? 'Find Us';
    $embedUrl = $content['embedUrl'] ?? '';
    $address = $content['address'] ?? '';
    $showAddress = $content['showAddress'] ?? true;
    
    $bgColor = $style['backgroundColor'] ?? '#ffffff';
    $textColor = $style['textColor'] ?? '#111827';
@endphp

<section class="py-16 md:py-24" style="background-color: {{ $bgColor }}; color: {{ $textColor }};">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($title)
            <h2 class="text-3xl md:text-4xl font-bold mb-8 text-center">{{ $title }}</h2>
        @endif
        
        <div class="grid grid-cols-1 {{ $showAddress && $address ? 'lg:grid-cols-3' : '' }} gap-8">
            @if($showAddress && $address)
                <div class="lg:col-span-1">
                    <div class="bg-gray-50 p-6 rounded-xl h-full">
                        <h3 class="font-semibold text-lg mb-4">Our Location</h3>
                        <div class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <p class="text-gray-600">{{ $address }}</p>
                        </div>
                    </div>
                </div>
            @endif
            
            <div class="{{ $showAddress && $address ? 'lg:col-span-2' : '' }}">
                @if($embedUrl)
                    <div class="aspect-video rounded-xl overflow-hidden shadow-lg">
                        <iframe 
                            src="{{ $embedUrl }}"
                            class="w-full h-full"
                            style="border:0;"
                            allowfullscreen=""
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                        ></iframe>
                    </div>
                @else
                    <div class="aspect-video bg-gray-100 rounded-xl flex items-center justify-center">
                        <div class="text-center text-gray-500">
                            <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                            </svg>
                            <p>No map embed URL provided</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
