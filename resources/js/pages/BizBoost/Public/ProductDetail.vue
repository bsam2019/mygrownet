<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeftIcon, ShoppingCartIcon } from '@heroicons/vue/24/outline';

const props = defineProps<{
    business: { id: number; name: string; slug: string; logo_path: string | null; phone: string | null; whatsapp: string | null; currency: string; profile: any };
    product: { id: number; name: string; description: string | null; price: number; sale_price: number | null; currency: string; category: string | null; is_featured: boolean; images: { path: string }[]; image_url: string | null; stock_quantity: number | null; track_inventory: boolean };
    relatedProducts: any[];
    cart: Record<string, any>;
}>();

const formatPrice = (amount: number) => {
    const cur = props.business.currency || 'ZMW';
    return `${cur} ${Number(amount).toFixed(2)}`;
};

const addToCart = (productId: number) => {
    router.post(route('bizboost.public.shop.cart.add', props.business.slug), {
        product_id: productId,
        quantity: 1,
    }, { preserveState: true });
};

const currentImage = computed(() => {
    return props.product.image_url || (props.product.images?.[0]?.path ? `/storage/${props.product.images[0].path}` : null);
});

const whatsappShare = computed(() => {
    const url = window.location.href;
    const text = `Check out ${props.product.name} at ${props.business.name} - ${formatPrice(props.product.sale_price || props.product.price)}\n${url}`;
    return `https://wa.me/?text=${encodeURIComponent(text)}`;
});
</script>

<template>
    <Head :title="`${product.name} - ${business.name}`" />
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-3xl mx-auto px-4 py-4">
            <Link :href="route('bizboost.public.shop', business.slug)" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-4">
                <ArrowLeftIcon class="h-4 w-4" /> Back to shop
            </Link>

            <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
                <div class="aspect-square bg-gray-100">
                    <img v-if="currentImage" :src="currentImage" :alt="product.name" class="w-full h-full object-cover" />
                    <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                        <svg class="h-20 w-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <div class="p-6">
                    <p v-if="product.category" class="text-xs font-medium text-emerald-600 uppercase tracking-wide">{{ product.category }}</p>
                    <h1 class="text-xl font-bold text-gray-900 mt-1">{{ product.name }}</h1>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="text-3xl font-bold text-gray-900">{{ formatPrice(product.sale_price || product.price) }}</span>
                        <span v-if="product.sale_price" class="text-lg text-gray-400 line-through">{{ formatPrice(product.price) }}</span>
                    </div>
                    <p v-if="product.description" class="mt-4 text-gray-600 text-sm leading-relaxed whitespace-pre-line">{{ product.description }}</p>
                    <p v-if="product.track_inventory" class="mt-3 text-sm" :class="product.stock_quantity && product.stock_quantity > 0 ? 'text-emerald-600' : 'text-red-500'">
                        {{ product.stock_quantity && product.stock_quantity > 0 ? `${product.stock_quantity} in stock` : 'Out of stock' }}
                    </p>
                    <div class="mt-6 flex gap-3">
                        <button @click="addToCart(product.id)" class="flex-1 py-3 bg-emerald-600 text-white rounded-xl font-medium hover:bg-emerald-700 flex items-center justify-center gap-2">
                            <ShoppingCartIcon class="h-5 w-5" /> Add to Cart
                        </button>
                        <a :href="whatsappShare" target="_blank" class="py-3 px-4 bg-green-500 text-white rounded-xl hover:bg-green-600 flex items-center justify-center">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Related Products -->
            <div v-if="relatedProducts.length > 0" class="mt-8">
                <h2 class="font-semibold text-gray-900 mb-4">You might also like</h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <Link v-for="rp in relatedProducts" :key="rp.id" :href="route('bizboost.public.shop.product', [business.slug, rp.id])" class="bg-white rounded-xl shadow-sm border p-3 hover:shadow-md transition-shadow">
                        <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden mb-2">
                            <img v-if="rp.image_url" :src="rp.image_url" class="w-full h-full object-cover" />
                            <div v-else class="w-full h-full flex items-center justify-center text-gray-300"><svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
                        </div>
                        <p class="text-sm font-medium text-gray-900 truncate">{{ rp.name }}</p>
                        <p class="text-sm font-semibold text-emerald-600">{{ formatPrice(rp.sale_price || rp.price) }}</p>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
