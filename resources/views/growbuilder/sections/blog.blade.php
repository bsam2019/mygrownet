@php
    // Fetch real posts from the database
    $posts = \App\Infrastructure\GrowBuilder\Models\SitePost::forSite($site['id'])
        ->where('status', 'published')
        ->where('visibility', 'public')
        ->latest('published_at')
        ->take($content['postsCount'] ?? 6)
        ->get();
    
    // Style settings with defaults
    $bgColor = $style['backgroundColor'] ?? '#ffffff';
    $textColor = $style['textColor'] ?? '#111827';
    $cardBgColor = $content['cardBackgroundColor'] ?? '#ffffff';
    $cardTextColor = $content['cardTextColor'] ?? '#111827';
    $cardBorderColor = $content['cardBorderColor'] ?? '#e5e7eb';
    $cardStyle = $content['cardStyle'] ?? 'bordered'; // bordered, shadow, minimal, filled
    $layout = $content['layout'] ?? 'grid'; // grid, list, featured
    $columns = $content['columns'] ?? 3;
    $showDate = $content['showDate'] ?? true;
    $showExcerpt = $content['showExcerpt'] ?? true;
    $showImage = $content['showImage'] ?? true;
    $imageStyle = $content['imageStyle'] ?? 'cover'; // cover, contain, rounded
    $buttonStyle = $content['buttonStyle'] ?? 'filled'; // filled, outline, text
@endphp

<section class="py-16 md:py-24" id="blog" style="background-color: {{ $bgColor }}; color: {{ $textColor }}">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold mb-4" style="color: {{ $style['titleColor'] ?? 'var(--primary-color)' }}">
                {{ $content['title'] ?? 'Latest News' }}
            </h2>
            @if(isset($content['description']) && $content['description'])
            <p class="text-lg max-w-2xl mx-auto" style="color: {{ $style['descriptionColor'] ?? '#6b7280' }}">
                {{ $content['description'] }}
            </p>
            @endif
        </div>
        
        @if($posts->count() > 0)
            @if($layout === 'featured' && $posts->count() > 0)
            <!-- Featured Layout: First post large, rest in grid -->
            <div class="space-y-8">
                @php $featuredPost = $posts->first(); @endphp
                <article class="@if($cardStyle === 'shadow') shadow-lg @elseif($cardStyle === 'bordered') border @elseif($cardStyle === 'filled') @endif rounded-xl overflow-hidden"
                    style="background-color: {{ $cardBgColor }}; @if($cardStyle === 'bordered') border-color: {{ $cardBorderColor }}; @endif">
                    <div class="md:flex">
                        @if($showImage)
                        <div class="md:w-1/2">
                            <a href="/sites/{{ $subdomain }}/blog/{{ $featuredPost->slug }}">
                                @if($featuredPost->featured_image)
                                <img src="{{ $featuredPost->featured_image }}" alt="{{ $featuredPost->title }}" 
                                    class="w-full h-64 md:h-full object-{{ $imageStyle === 'contain' ? 'contain' : 'cover' }} @if($imageStyle === 'rounded') rounded-lg m-4 @endif">
                                @else
                                <div class="w-full h-64 md:h-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                                    </svg>
                                </div>
                                @endif
                            </a>
                        </div>
                        @endif
                        <div class="p-8 @if($showImage) md:w-1/2 @endif flex flex-col justify-center">
                            @if($showDate)
                            <time class="text-sm mb-3" style="color: {{ $style['metaColor'] ?? '#6b7280' }}" datetime="{{ $featuredPost->published_at?->toISOString() }}">
                                {{ $featuredPost->published_at?->format('F j, Y') ?? $featuredPost->created_at->format('F j, Y') }}
                            </time>
                            @endif
                            <h3 class="text-2xl md:text-3xl font-bold mb-4">
                                <a href="/sites/{{ $subdomain }}/blog/{{ $featuredPost->slug }}" class="hover:underline" style="color: {{ $cardTextColor }}">
                                    {{ $featuredPost->title }}
                                </a>
                            </h3>
                            @if($showExcerpt && $featuredPost->excerpt)
                            <p class="text-lg mb-6" style="color: {{ $style['excerptColor'] ?? '#6b7280' }}">{{ $featuredPost->excerpt }}</p>
                            @endif
                            <a href="/sites/{{ $subdomain }}/blog/{{ $featuredPost->slug }}" 
                                class="inline-flex items-center gap-2 font-medium @if($buttonStyle === 'filled') px-4 py-2 rounded-lg text-white @elseif($buttonStyle === 'outline') px-4 py-2 rounded-lg border-2 @endif"
                                style="@if($buttonStyle === 'filled') background-color: var(--primary-color); @elseif($buttonStyle === 'outline') border-color: var(--primary-color); color: var(--primary-color); @else color: var(--primary-color); @endif">
                                Read more →
                            </a>
                        </div>
                    </div>
                </article>
                
                @if($posts->count() > 1)
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($posts->skip(1) as $post)
                        @include('growbuilder.sections.partials.blog-card', compact('post', 'subdomain', 'cardStyle', 'cardBgColor', 'cardTextColor', 'cardBorderColor', 'showImage', 'showDate', 'showExcerpt', 'imageStyle', 'buttonStyle', 'style'))
                    @endforeach
                </div>
                @endif
            </div>
            
            @elseif($layout === 'list')
            <!-- List Layout -->
            <div class="space-y-6">
                @foreach($posts as $post)
                <article class="@if($cardStyle === 'shadow') shadow-lg @elseif($cardStyle === 'bordered') border @elseif($cardStyle === 'filled') @endif rounded-xl overflow-hidden"
                    style="background-color: {{ $cardBgColor }}; @if($cardStyle === 'bordered') border-color: {{ $cardBorderColor }}; @endif">
                    <div class="md:flex">
                        @if($showImage)
                        <div class="md:w-1/3">
                            <a href="/sites/{{ $subdomain }}/blog/{{ $post->slug }}">
                                @if($post->featured_image)
                                <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="w-full h-48 md:h-full object-{{ $imageStyle === 'contain' ? 'contain' : 'cover' }}">
                                @else
                                <div class="w-full h-48 md:h-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                                    </svg>
                                </div>
                                @endif
                            </a>
                        </div>
                        @endif
                        <div class="p-6 @if($showImage) md:w-2/3 @endif">
                            @if($showDate)
                            <time class="text-sm mb-2 block" style="color: {{ $style['metaColor'] ?? '#6b7280' }}" datetime="{{ $post->published_at?->toISOString() }}">
                                {{ $post->published_at?->format('M j, Y') ?? $post->created_at->format('M j, Y') }}
                            </time>
                            @endif
                            <h3 class="text-xl font-semibold mb-2">
                                <a href="/sites/{{ $subdomain }}/blog/{{ $post->slug }}" class="hover:underline" style="color: {{ $cardTextColor }}">
                                    {{ $post->title }}
                                </a>
                            </h3>
                            @if($showExcerpt && $post->excerpt)
                            <p class="mb-4 line-clamp-2" style="color: {{ $style['excerptColor'] ?? '#6b7280' }}">{{ $post->excerpt }}</p>
                            @endif
                            <a href="/sites/{{ $subdomain }}/blog/{{ $post->slug }}" class="text-sm font-medium" style="color: var(--primary-color)">
                                Read more →
                            </a>
                        </div>
                    </div>
                </article>
                @endforeach
            </div>
            
            @else
            <!-- Grid Layout (default) -->
            <div class="grid gap-8 @if($columns == 2) md:grid-cols-2 @elseif($columns == 4) md:grid-cols-2 lg:grid-cols-4 @else md:grid-cols-2 lg:grid-cols-3 @endif">
                @foreach($posts as $post)
                    @include('growbuilder.sections.partials.blog-card', compact('post', 'subdomain', 'cardStyle', 'cardBgColor', 'cardTextColor', 'cardBorderColor', 'showImage', 'showDate', 'showExcerpt', 'imageStyle', 'buttonStyle', 'style'))
                @endforeach
            </div>
            @endif
        
        <!-- View All Button -->
        @if($content['showViewAll'] ?? true)
        <div class="text-center mt-10">
            <a href="/sites/{{ $subdomain }}/blog" 
                class="inline-flex items-center gap-2 px-6 py-3 font-semibold rounded-lg transition hover:opacity-90
                @if($buttonStyle === 'outline') border-2 @endif"
                style="@if($buttonStyle === 'filled') background-color: var(--primary-color); color: white; @elseif($buttonStyle === 'outline') border-color: var(--primary-color); color: var(--primary-color); background: transparent; @else color: var(--primary-color); @endif">
                {{ $content['viewAllText'] ?? 'View All Posts' }}
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                </svg>
            </a>
        </div>
        @endif
        @else
        <!-- Empty State -->
        <div class="text-center py-12 rounded-xl" style="background-color: {{ $cardBgColor }}">
            <svg class="w-16 h-16 mx-auto mb-4" style="color: {{ $style['metaColor'] ?? '#9ca3af' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
            </svg>
            <h3 class="text-lg font-medium mb-2" style="color: {{ $cardTextColor }}">No posts yet</h3>
            <p style="color: {{ $style['metaColor'] ?? '#6b7280' }}">Check back soon for new content!</p>
        </div>
        @endif
    </div>
</section>
