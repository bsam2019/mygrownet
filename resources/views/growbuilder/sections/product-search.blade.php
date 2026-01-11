@php
    $placeholder = $content['placeholder'] ?? 'Search products...';
    $showCategories = $content['showCategories'] ?? true;
    $showSort = $content['showSort'] ?? true;
    
    $bgColor = $style['backgroundColor'] ?? '#ffffff';
    $textColor = $style['textColor'] ?? '#111827';
@endphp

<section class="py-8" style="background-color: {{ $bgColor }}; color: {{ $textColor }};">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row gap-4 items-center">
            <!-- Search Input -->
            <div class="flex-1 w-full">
                <div class="relative">
                    <input 
                        type="text" 
                        placeholder="{{ $placeholder }}"
                        class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
            
            @if($showCategories)
                <select class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Categories</option>
                </select>
            @endif
            
            @if($showSort)
                <select class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="newest">Newest</option>
                    <option value="price-low">Price: Low to High</option>
                    <option value="price-high">Price: High to Low</option>
                    <option value="popular">Most Popular</option>
                </select>
            @endif
        </div>
    </div>
</section>
