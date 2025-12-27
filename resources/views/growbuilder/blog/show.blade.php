<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $post->meta_title ?? $post->title }} - {{ $site->name }}</title>
    <meta name="description" content="{{ $post->meta_description ?? $post->excerpt ?? Str::limit(strip_tags($post->content), 160) }}">
    @if($post->featured_image)
    <meta property="og:image" content="{{ $post->featured_image }}">
    @endif
    @if($site->favicon)
        <link rel="icon" type="image/png" sizes="32x32" href="{{ $site->favicon }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ $site->favicon }}">
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary-color: {{ $site->theme['primaryColor'] ?? '#2563eb' }};
        }
        .prose { max-width: 65ch; }
        .prose p { margin-bottom: 1.25em; line-height: 1.75; }
        .prose h2 { font-size: 1.5em; font-weight: 700; margin-top: 2em; margin-bottom: 1em; }
        .prose h3 { font-size: 1.25em; font-weight: 600; margin-top: 1.5em; margin-bottom: 0.75em; }
        .prose ul, .prose ol { margin: 1.25em 0; padding-left: 1.5em; }
        .prose li { margin: 0.5em 0; }
        .prose a { color: var(--primary-color); text-decoration: underline; }
        .prose blockquote { border-left: 4px solid var(--primary-color); padding-left: 1em; margin: 1.5em 0; font-style: italic; color: #4b5563; }
        .prose pre { background: #1f2937; color: #f9fafb; padding: 1em; border-radius: 0.5em; overflow-x: auto; margin: 1.5em 0; }
        .prose code { background: #f3f4f6; padding: 0.2em 0.4em; border-radius: 0.25em; font-size: 0.875em; }
        .prose pre code { background: transparent; padding: 0; }
        .prose img { border-radius: 0.5em; margin: 1.5em 0; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="/sites/{{ $subdomain }}" class="flex items-center gap-3">
                    @if($site->settings['navigation']['logo'] ?? $site->logo)
                    <img src="{{ $site->settings['navigation']['logo'] ?? $site->logo }}" alt="{{ $site->name }}" class="h-8 w-auto">
                    @endif
                    <span class="font-semibold text-gray-900">{{ $site->name }}</span>
                </a>
                <a href="/sites/{{ $subdomain }}/blog" class="text-sm text-gray-600 hover:text-gray-900">← Back to Blog</a>
            </div>
        </div>
    </nav>

    <!-- Article -->
    <article class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Header -->
        <header class="mb-8">
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
                <time datetime="{{ $post->published_at?->toISOString() }}">
                    {{ $post->published_at?->format('F j, Y') ?? $post->created_at->format('F j, Y') }}
                </time>
                <span>·</span>
                <span>{{ $post->views_count ?? 0 }} views</span>
            </div>
            <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ $post->title }}</h1>
            @if($post->excerpt)
            <p class="text-xl text-gray-600">{{ $post->excerpt }}</p>
            @endif
        </header>

        <!-- Featured Image -->
        @if($post->featured_image)
        <div class="mb-8">
            <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="w-full rounded-xl">
        </div>
        @endif

        <!-- Content -->
        <div class="prose text-gray-700">
            {!! nl2br(e($post->content)) !!}
        </div>

        <!-- Author -->
        @if($post->author)
        <div class="mt-12 pt-8 border-t border-gray-200">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-semibold" style="background-color: var(--primary-color)">
                    {{ strtoupper(substr($post->author->name, 0, 1)) }}
                </div>
                <div>
                    <p class="font-medium text-gray-900">{{ $post->author->name }}</p>
                    <p class="text-sm text-gray-500">Author</p>
                </div>
            </div>
        </div>
        @endif
    </article>

    <!-- Related Posts -->
    @if($relatedPosts->count() > 0)
    <section class="bg-white border-t border-gray-200 py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-8">Related Posts</h2>
            <div class="grid md:grid-cols-3 gap-6">
                @foreach($relatedPosts as $related)
                <article class="group">
                    @if($related->featured_image)
                    <a href="/sites/{{ $subdomain }}/blog/{{ $related->slug }}">
                        <img src="{{ $related->featured_image }}" alt="{{ $related->title }}" class="w-full h-32 object-cover rounded-lg mb-3">
                    </a>
                    @endif
                    <h3 class="font-semibold text-gray-900 group-hover:underline">
                        <a href="/sites/{{ $subdomain }}/blog/{{ $related->slug }}" style="color: var(--primary-color)">
                            {{ $related->title }}
                        </a>
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $related->published_at?->format('M j, Y') ?? $related->created_at->format('M j, Y') }}
                    </p>
                </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm text-gray-500">
            © {{ date('Y') }} {{ $site->name }}. All rights reserved.
        </div>
    </footer>
</body>
</html>
