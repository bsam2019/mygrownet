@php
    $title = $content['title'] ?? 'Our Products';
    $subtitle = $content['subtitle'] ?? '';
    $columns = $content['columns'] ?? 3;
    $showPrice = $content['show_price'] ?? true;
    $showCart = $content['show_cart'] ?? true;
    $products = $content['products'] ?? [];
    $bgColor = $style['backgroundColor'] ?? '#ffffff';
    $textColor = $style['textColor'] ?? '#111827';
@endphp

<section class="py-16 px-4" style="background-color: {{ $bgColor }}; color: {{ $textColor }};">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold mb-4">{{ $title }}</h2>
            @if($subtitle)
                <p class="text-lg opacity-80 max-w-2xl mx-auto">{{ $subtitle }}</p>
            @endif
        </div>

        @if(count($products) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-{{ $columns }} gap-6">
                @foreach($products as $product)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden group">
                        @if(!empty($product['image']))
                            <div class="aspect-square overflow-hidden bg-gray-100">
                                <img 
                                    src="{{ $product['image'] }}" 
                                    alt="{{ $product['name'] }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                />
                            </div>
                        @else
                            <div class="aspect-square bg-gray-100 flex items-center justify-center">
                                <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        @endif
                        
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 mb-1">{{ $product['name'] }}</h3>
                            
                            @if(!empty($product['description']))
                                <p class="text-sm text-gray-500 mb-3 line-clamp-2">{{ $product['description'] }}</p>
                            @endif
                            
                            @if($showPrice)
                                <div class="flex items-center gap-2 mb-3">
                                    <span class="text-lg font-bold text-blue-600">K{{ number_format($product['price'] / 100, 2) }}</span>
                                    @if(!empty($product['compare_price']) && $product['compare_price'] > $product['price'])
                                        <span class="text-sm text-gray-400 line-through">K{{ number_format($product['compare_price'] / 100, 2) }}</span>
                                    @endif
                                </div>
                            @endif
                            
                            @if($showCart)
                                <button 
                                    onclick="addToCart({{ json_encode($product) }})"
                                    class="w-full py-2 px-4 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
                                >
                                    Add to Cart
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12 text-gray-500">
                <p>No products available yet.</p>
            </div>
        @endif
    </div>
</section>
