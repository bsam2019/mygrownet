<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Blog - {{ $site->name }}</title>
    <meta name="description" content="Latest posts from {{ $site->name }}">
    @if($site->favicon)
        <link rel="icon" type="image/png" sizes="32x32" href="{{ $site->favicon }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ $site->favicon }}">
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary-color: {{ $site->theme['primaryColor'] ?? '#2563eb' }};
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="/sites/{{ $subdomain }}" class="flex items-center gap-3">
                    @if($site->settings['navigation']['logo'] ?? $site->logo)
                    <img src="{{ $site->settings['navigation']['logo'] ?? $site->logo }}" alt="{{ $site->name }}" class="h-8 w-auto">
                    @endif
                    <span class="font-semibold text-gray-900">{{ $site->name }}</span>
                </a>
                <a href="/sites/{{ $subdomain }}" class="text-sm text-gray-600 hover:text-gray-900">← Back to Site</a>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <header class="bg-white border-b border-gray-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <h1 class="text-4xl font-bold text-gray-900">Blog</h1>
            <p class="text-lg text-gray-600 mt-2">Latest news and updates</p>
        </div>
    </header>

    <!-- Posts Grid -->
    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        @if($posts->count() > 0)
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($posts as $post)
            <article class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow">
                @if($post->featured_image)
                <a href="/sites/{{ $subdomain }}/blog/{{ $post->slug }}">
                    <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="w-full h-48 object-cover">
                </a>
                @else
                <div class="w-full h-48 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                    <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                    </svg>
                </div>
                @endif
                <div class="p-6">
                    <div class="flex items-center gap-2 text-sm text-gray-500 mb-3">
                        <time datetime="{{ $post->published_at?->toISOString() }}">
                            {{ $post->published_at?->format('M j, Y') ?? $post->created_at->format('M j, Y') }}
                        </time>
                        <span>·</span>
                        <span>{{ $post->views_count ?? 0 }} views</span>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">
                        <a href="/sites/{{ $subdomain }}/blog/{{ $post->slug }}" class="hover:underline" style="color: var(--primary-color)">
                            {{ $post->title }}
                        </a>
                    </h2>
                    @if($post->excerpt)
                    <p class="text-gray-600 line-clamp-3">{{ $post->excerpt }}</p>
                    @endif
                    <a href="/sites/{{ $subdomain }}/blog/{{ $post->slug }}" class="inline-flex items-center gap-1 mt-4 text-sm font-medium" style="color: var(--primary-color)">
                        Read more →
                    </a>
                </div>
            </article>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($posts->hasPages())
        <div class="mt-12">
            {{ $posts->links() }}
        </div>
        @endif
        @else
        <div class="text-center py-16">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No posts yet</h3>
            <p class="text-gray-500">Check back soon for new content!</p>
        </div>
        @endif
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm text-gray-500">
            © {{ date('Y') }} {{ $site->name }}. All rights reserved.
        </div>
    </footer>
</body>
</html>
