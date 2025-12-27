<article class="@if($cardStyle === 'shadow') shadow-lg @elseif($cardStyle === 'bordered') border @endif rounded-xl overflow-hidden hover:shadow-lg transition-shadow"
    style="background-color: {{ $cardBgColor }}; @if($cardStyle === 'bordered') border-color: {{ $cardBorderColor }}; @endif">
    @if($showImage)
    <a href="/sites/{{ $subdomain }}/blog/{{ $post->slug }}">
        @if($post->featured_image)
        <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" 
            class="w-full h-48 object-{{ $imageStyle === 'contain' ? 'contain' : 'cover' }} @if($imageStyle === 'rounded') rounded-lg m-2 @endif">
        @else
        <div class="w-full h-48 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
            </svg>
        </div>
        @endif
    </a>
    @endif
    <div class="p-6">
        @if($showDate)
        <time class="text-sm mb-3 block" style="color: {{ $style['metaColor'] ?? '#6b7280' }}" datetime="{{ $post->published_at?->toISOString() }}">
            {{ $post->published_at?->format('M j, Y') ?? $post->created_at->format('M j, Y') }}
        </time>
        @endif
        <h3 class="text-xl font-semibold mb-2">
            <a href="/sites/{{ $subdomain }}/blog/{{ $post->slug }}" class="hover:underline" style="color: {{ $cardTextColor }}">
                {{ $post->title }}
            </a>
        </h3>
        @if($showExcerpt && $post->excerpt)
        <p class="line-clamp-3 mb-4" style="color: {{ $style['excerptColor'] ?? '#6b7280' }}">{{ $post->excerpt }}</p>
        @endif
        <a href="/sites/{{ $subdomain }}/blog/{{ $post->slug }}" 
            class="inline-flex items-center gap-1 text-sm font-medium"
            style="color: var(--primary-color)">
            Read more →
        </a>
    </div>
</article>
