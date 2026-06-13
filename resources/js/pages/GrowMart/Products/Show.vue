<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import GrowMartLayout from '@/layouts/GrowMartLayout.vue';
import { ShoppingBagIcon, MinusIcon, PlusIcon, ArrowLeftIcon, HeartIcon as HeartOutline } from '@heroicons/vue/24/outline';
import { HeartIcon as HeartSolid } from '@heroicons/vue/24/solid';
import { StarIcon } from '@heroicons/vue/20/solid';
import { growmartImage } from '@/lib/growmart';

interface ProductImage {
    id: number;
    path: string;
}

interface Review {
    id: number;
    user_name: string;
    rating: number;
    review_text: string | null;
    created_at: string;
}

interface Product {
    id: number;
    name: string;
    slug: string;
    description: string;
    unit: string;
    price: number;
    price_formatted: string;
    compare_price: number | null;
    compare_price_formatted?: string;
    has_discount: boolean;
    discount_percentage: number;
    category?: string;
    images: ProductImage[];
    stock: number;
    wishlisted: boolean;
}

interface RelatedProduct {
    id: number;
    name: string;
    slug: string;
    unit: string;
    price: number;
    price_formatted: string;
    image?: string;
}

interface Props {
    product: Product;
    relatedProducts: RelatedProduct[];
    cartCount: number;
    reviews: Review[];
    averageRating: number;
    totalReviews: number;
}

const props = defineProps<Props>();

const quantity = ref(1);
const selectedImage = ref(0);
const addedToCart = ref(false);

const reviewForm = useForm({
    product_id: props.product.id,
    rating: 5,
    review_text: '',
});

function submitReview() {
    reviewForm.post(route('growmart.reviews.store'), {
        preserveScroll: true,
        onSuccess: () => reviewForm.reset('review_text'),
    });
}

function toggleWishlist() {
    router.post(route('growmart.wishlist.toggle'), {
        product_id: props.product.id,
    }, { preserveScroll: true });
}

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        router.get(route('growmart.products.index'));
    }
};

const totalPrice = computed(() => {
    return props.product.price * quantity.value;
});

const totalPriceFormatted = computed(() => {
    return 'K' + (totalPrice.value / 100).toFixed(2);
});

const addToCart = () => {
    router.post(route('growmart.cart.add'), {
        product_id: props.product.id,
        quantity: quantity.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            addedToCart.value = true;
            setTimeout(() => addedToCart.value = false, 2000);
        },
    });
};
</script>

<template>
    <Head :title="product.name + ' - GrowMart'" />

    <GrowMartLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <!-- Breadcrumb -->
            <div class="flex items-center gap-2 mb-4">
                <button @click="goBack" class="p-1.5 -ml-1.5 text-gray-500 hover:text-emerald-600 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer">
                    <ArrowLeftIcon class="w-5 h-5" />
                </button>
                <div class="text-sm text-gray-500">
                <Link :href="route('growmart.home')" class="hover:text-emerald-600">Home</Link>
                <span class="mx-2">/</span>
                <Link :href="route('growmart.products.index', { category: product.category })" class="hover:text-emerald-600">{{ product.category }}</Link>
                <span class="mx-2">/</span>
                <span class="text-gray-900">{{ product.name }}</span>
            </div>
            </div>

            <div class="grid md:grid-cols-2 gap-8 mb-8">
                <!-- Images -->
                <div>
                    <div class="aspect-square bg-gray-50 rounded-xl overflow-hidden mb-3">
                        <img v-if="product.images[selectedImage]" :src="growmartImage(product.images[selectedImage]?.path)" class="w-full h-full object-cover" />
                        <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                            <ShoppingBagIcon class="h-24 w-24" />
                        </div>
                    </div>
                    <div v-if="product.images.length > 1" class="flex gap-2">
                        <button
                            v-for="(img, idx) in product.images" :key="img.id"
                            @click="selectedImage = idx"
                            class="w-16 h-16 rounded-lg overflow-hidden border-2 transition-colors"
                            :class="selectedImage === idx ? 'border-emerald-500' : 'border-gray-200'"
                        >
                            <img :src="growmartImage(img.path)" class="w-full h-full object-cover" />
                        </button>
                    </div>
                </div>

                <!-- Info -->
                <div>
                    <p class="text-sm text-emerald-600 font-medium mb-1">{{ product.category }}</p>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ product.name }}</h1>
                    <p class="text-sm text-gray-500 mb-4">Per {{ product.unit }}</p>

                    <div class="flex items-center gap-3 mb-4">
                        <div>
                            <span class="text-3xl font-bold text-emerald-700">{{ totalPriceFormatted }}</span>
                            <p class="text-xs text-gray-400 mt-0.5">{{ product.price_formatted }} per {{ product.unit }}</p>
                        </div>
                        <span v-if="product.has_discount" class="text-lg text-gray-400 line-through">{{ product.compare_price_formatted }}</span>
                        <span v-if="product.has_discount" class="bg-red-100 text-red-700 text-xs font-bold px-2 py-0.5 rounded">-{{ product.discount_percentage }}%</span>
                        <button @click="toggleWishlist" class="ml-auto p-2 rounded-full hover:bg-gray-100 transition-colors">
                            <HeartSolid v-if="product.wishlisted" class="w-6 h-6 text-red-500" />
                            <HeartOutline v-else class="w-6 h-6 text-gray-400 hover:text-red-400" />
                        </button>
                    </div>

                    <p class="text-sm text-gray-600 mb-6">{{ product.description }}</p>

                    <div class="flex items-center gap-4 mb-6">
                        <div class="flex items-center border border-gray-300 rounded-lg">
                            <button @click="quantity = Math.max(1, quantity - 1)" class="p-2 hover:bg-gray-50"><MinusIcon class="h-5 w-5 text-gray-600" /></button>
                            <span class="px-4 font-medium text-gray-900 min-w-[3rem] text-center">{{ quantity }}</span>
                            <button @click="quantity = Math.min(99, quantity + 1)" class="p-2 hover:bg-gray-50"><PlusIcon class="h-5 w-5 text-gray-600" /></button>
                        </div>
                        <button @click="addToCart" :disabled="product.stock <= 0" class="flex-1 bg-emerald-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            {{ product.stock <= 0 ? 'Out of Stock' : addedToCart ? '✓ Added!' : 'Add to Cart' }}
                        </button>
                    </div>

                    <div class="text-sm text-gray-500">
                        <p v-if="product.stock > 0"><span class="text-emerald-600 font-medium">In Stock:</span> {{ product.stock }} {{ product.unit }}(s)</p>
                        <p v-else class="text-red-600 font-medium">Out of Stock</p>
                    </div>
                </div>
            </div>

            <!-- Related Products -->
            <div v-if="relatedProducts.length > 0">
                <h2 class="text-xl font-bold text-gray-900 mb-4">You May Also Like</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <Link
                        v-for="rp in relatedProducts" :key="rp.id"
                        :href="route('growmart.products.show', rp.slug)"
                        class="bg-white rounded-lg border border-gray-200 overflow-hidden hover:border-emerald-300 hover:shadow-md transition-all group"
                    >
                        <div class="aspect-square bg-gray-50 overflow-hidden">
                            <img v-if="rp.image" :src="growmartImage(rp.image)" class="w-full h-full object-cover group-hover:scale-105 transition-transform" />
                            <div v-else class="w-full h-full flex items-center justify-center text-gray-300"><ShoppingBagIcon class="h-10 w-10" /></div>
                        </div>
                        <div class="p-3">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ rp.name }}</p>
                            <p class="text-xs text-gray-400">{{ rp.unit }}</p>
                            <p class="font-bold text-emerald-700 mt-1">{{ rp.price_formatted }}</p>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Reviews -->
            <div class="mt-12 border-t border-gray-200 pt-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900">Customer Reviews</h2>
                    <div v-if="totalReviews > 0" class="flex items-center gap-2">
                        <div class="flex items-center">
                            <StarIcon v-for="i in 5" :key="i" class="w-5 h-5" :class="i <= Math.round(averageRating) ? 'text-yellow-400' : 'text-gray-200'" />
                        </div>
                        <span class="text-sm text-gray-500">({{ averageRating }} / 5, {{ totalReviews }} reviews)</span>
                    </div>
                </div>

                <div v-if="reviews.length === 0" class="text-center py-8 text-gray-400">
                    No reviews yet. Be the first to review this product!
                </div>

                <div v-else class="space-y-4 mb-8">
                    <div v-for="review in reviews" :key="review.id" class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-medium text-gray-900">{{ review.user_name }}</span>
                            <span class="text-xs text-gray-400">{{ review.created_at }}</span>
                        </div>
                        <div class="flex items-center mb-2">
                            <StarIcon v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= review.rating ? 'text-yellow-400' : 'text-gray-200'" />
                        </div>
                        <p v-if="review.review_text" class="text-sm text-gray-600">{{ review.review_text }}</p>
                    </div>
                </div>

                <!-- Write Review -->
                <form @submit.prevent="submitReview" class="bg-gray-50 rounded-xl p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Write a Review</h3>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                        <div class="flex gap-1">
                            <button v-for="i in 5" :key="i" type="button" @click="reviewForm.rating = i">
                                <StarIcon class="w-8 h-8 cursor-pointer transition-colors" :class="i <= reviewForm.rating ? 'text-yellow-400' : 'text-gray-200 hover:text-yellow-300'" />
                            </button>
                        </div>
                        <p v-if="reviewForm.errors.rating" class="text-sm text-red-600 mt-1">{{ reviewForm.errors.rating }}</p>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Review</label>
                        <textarea v-model="reviewForm.review_text" rows="3" placeholder="Share your experience with this product..."
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                    </div>
                    <button type="submit" :disabled="reviewForm.processing"
                        class="px-6 py-2.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 disabled:opacity-50 transition-colors">
                        {{ reviewForm.processing ? 'Submitting...' : 'Submit Review' }}
                    </button>
                </form>
            </div>
        </div>
    </GrowMartLayout>
</template>
