@php
    $title = $content['title'] ?? 'Frequently Asked Questions';
    $items = $content['items'] ?? [];
    $layout = $content['layout'] ?? 'accordion';
    $textPosition = $content['textPosition'] ?? 'center';
    
    $bgColor = $style['backgroundColor'] ?? '#ffffff';
    $textColor = $style['textColor'] ?? '#111827';
    
    $alignClass = match($textPosition) {
        'left' => 'text-left',
        'right' => 'text-right',
        default => 'text-center',
    };
@endphp

<section class="py-16 md:py-24" style="background-color: {{ $bgColor }}; color: {{ $textColor }};">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl md:text-4xl font-bold mb-12 {{ $alignClass }}">{{ $title }}</h2>
        
        <div class="space-y-4">
            @foreach($items as $index => $item)
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <button 
                        type="button"
                        class="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50 transition"
                        onclick="this.parentElement.classList.toggle('faq-open'); this.querySelector('svg').classList.toggle('rotate-180');"
                    >
                        <span class="font-medium text-lg">{{ $item['question'] ?? 'Question' }}</span>
                        <svg class="w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div class="faq-answer hidden px-6 pb-4">
                        <p class="text-gray-600">{{ $item['answer'] ?? '' }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<style>
    .faq-open .faq-answer {
        display: block;
    }
</style>
