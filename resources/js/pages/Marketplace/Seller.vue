<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import MarketplaceLayout from '@/layouts/MarketplaceLayout.vue';
import axios from 'axios';
import {
    ShieldCheckIcon,
    StarIcon,
    MapPinIcon,
    CalendarIcon,
    ShoppingBagIcon,
    CheckBadgeIcon,
    SparklesIcon,
    HeartIcon,
    ChatBubbleLeftRightIcon,
    ShareIcon,
    FunnelIcon,
    Squares2X2Icon,
    ListBulletIcon,
    ChevronDownIcon,
    ShoppingCartIcon,
    TruckIcon,
    ClockIcon,
    UserGroupIcon,
    FireIcon,
    LinkIcon,
    CheckIcon,
} from '@heroicons/vue/24/outline';
import { StarIcon as StarIconSolid, HeartIcon as HeartIconSolid } from '@heroicons/vue/24/solid';

interface Review {
    id: number;
    rating: number;
    comment: string;
    created_at: string;
    formatted_date: string;
    buyer: { id: number; name: string };
    product?: { id: number; name: string; slug: string };
}

interface RatingBreakdown {
    [key: number]: { count: number; percentage: number };
}

interface Seller {
    id: number;
    user_id: number;
    business_name: string;
    description: string | null;
    province: string;
    district: string | null;
    trust_level: string;
    kyc_status: string;
    total_orders: number;
    rating: number | null;
    review_count?: number;
    response_rate?: number;
    response_time?: string;
    followers_count?: number;
    created_at: string;
    cover_image_url?: string | null;
    logo_url?: string | null;
}

interface Product {
    id: number;
    name: string;
    slug: string;
    price: number;
    compare_price: number | null;
    images: string[];
    sold_count?: number;
    rating?: number;
}

interface Props {
    seller: Seller;
    products: {
        data: Product[];
        links: any[];
        meta?: any;
    };
    categories?: { id: number; name: string; slug: string; count: number }[];
    isFollowing?: boolean;
    ratingBreakdown?: RatingBreakdown;
    reviews?: Review[];
}

const props = defineProps<Props>();
const page = usePage();

const activeTab = ref<'products' | 'about' | 'reviews'>('products');
const viewMode = ref<'grid' | 'list'>('grid');
const sortBy = ref('default');
const selectedCategory = ref('all');
const isFollowing = ref(props.isFollowing || false);
const followersCount = ref(props.seller.followers_count || 0);
const showShareMenu = ref(false);
const followLoading = ref(false);
const linkCopied = ref(false);
const showChatModal = ref(false);
const chatMessage = ref('');
const chatSubject = ref('');
const chatSending = ref(false);
const chatError = ref('');
const chatSuccess = ref(false);
const shareButtonRef = ref<HTMLElement | null>(null);

const isAuthenticated = computed(() => !!(page.props as any).auth?.user);

const shareMenuStyle = computed(() => {
    if (!shareButtonRef.value) {
        return { top: '0px', left: '0px' };
    }
    const rect = shareButtonRef.value.getBoundingClientRect();
    return {
        top: `${rect.bottom + 8}px`,
        right: `${window.innerWidth - rect.right}px`,
    };
});

const sortOptions = [
    { value: 'default', label: 'Default' },
    { value: 'price_low', label: 'Price: Low to High' },
    { value: 'price_high', label: 'Price: High to Low' },
    { value: 'newest', label: 'Newest First' },
    { value: 'best_selling', label: 'Best Selling' },
    { value: 'rating', label: 'Top Rated' },
];

const sortedProducts = computed(() => {
    let filtered = [...(props.products.data || [])];
    switch (sortBy.value) {
        case 'price_low':
            return filtered.sort((a, b) => a.price - b.price);
        case 'price_high':
            return filtered.sort((a, b) => b.price - a.price);
        case 'newest':
            return filtered.reverse();
        case 'best_selling':
            return filtered.sort((a, b) => (b.sold_count || 0) - (a.sold_count || 0));
        case 'rating':
            return filtered.sort((a, b) => (b.rating || 0) - (a.rating || 0));
        default:
            return filtered;
    }
});

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 0,
    }).format(price / 100);
};

const getTrustBadge = (level: string) => {
    const badges: Record<string, { icon: any; label: string; color: string; bg: string; border: string }> = {
        'new': { 
            icon: SparklesIcon, 
            label: 'New Seller', 
            color: 'text-gray-700', 
            bg: 'bg-gray-100',
            border: 'border-gray-200'
        },
        'verified': { 
            icon: CheckBadgeIcon, 
            label: 'Verified', 
            color: 'text-blue-700', 
            bg: 'bg-blue-50',
            border: 'border-blue-200'
        },
        'trusted': { 
            icon: ShieldCheckIcon, 
            label: 'Trusted', 
            color: 'text-emerald-700', 
            bg: 'bg-emerald-50',
            border: 'border-emerald-200'
        },
        'top': { 
            icon: FireIcon, 
            label: 'Top Seller', 
            color: 'text-orange-700', 
            bg: 'bg-gradient-to-r from-orange-50 to-amber-50',
            border: 'border-orange-200'
        },
    };
    return badges[level] || badges['new'];
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-ZM', {
        year: 'numeric',
        month: 'short',
    });
};

const toggleFollow = async () => {
    if (!isAuthenticated.value) {
        router.visit(route('login'));
        return;
    }
    
    followLoading.value = true;
    try {
        if (isFollowing.value) {
            const response = await axios.delete(route('marketplace.shop.unfollow', props.seller.id));
            isFollowing.value = false;
            followersCount.value = response.data.followers_count;
        } else {
            const response = await axios.post(route('marketplace.shop.follow', props.seller.id));
            isFollowing.value = true;
            followersCount.value = response.data.followers_count;
        }
    } catch (error) {
        console.error('Follow error:', error);
    } finally {
        followLoading.value = false;
    }
};

const shareShop = async (platform: string) => {
    const shopUrl = window.location.href;
    const shopName = props.seller.business_name;
    const text = `Check out ${shopName} on GrowMarket!`;
    
    switch (platform) {
        case 'copy':
            await navigator.clipboard.writeText(shopUrl);
            linkCopied.value = true;
            setTimeout(() => {
                linkCopied.value = false;
                showShareMenu.value = false;
            }, 2000);
            break;
        case 'whatsapp':
            window.open(`https://wa.me/?text=${encodeURIComponent(text + ' ' + shopUrl)}`, '_blank');
            showShareMenu.value = false;
            break;
        case 'facebook':
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shopUrl)}`, '_blank');
            showShareMenu.value = false;
            break;
        case 'twitter':
            window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(shopUrl)}`, '_blank');
            showShareMenu.value = false;
            break;
    }
};

const addToCart = (productId: number, event: Event) => {
    event.preventDefault();
    event.stopPropagation();
    router.post(route('marketplace.cart.add'), {
        product_id: productId,
        quantity: 1,
    }, { preserveScroll: true });
};

const goToReviews = () => {
    activeTab.value = 'reviews';
    // Scroll to top of tabs
    window.scrollTo({ top: 400, behavior: 'smooth' });
};

const openChatModal = () => {
    if (!isAuthenticated.value) {
        router.visit(route('login'));
        return;
    }
    chatSubject.value = `Inquiry about ${props.seller.business_name}`;
    chatMessage.value = '';
    chatError.value = '';
    chatSuccess.value = false;
    showChatModal.value = true;
};

const closeChatModal = () => {
    showChatModal.value = false;
    chatMessage.value = '';
    chatSubject.value = '';
    chatError.value = '';
};

const sendChatMessage = async () => {
    if (!chatMessage.value.trim() || !chatSubject.value.trim()) {
        chatError.value = 'Please enter a subject and message';
        return;
    }
    
    chatSending.value = true;
    chatError.value = '';
    
    try {
        await axios.post(route('mygrownet.messages.store'), {
            recipient_id: props.seller.user_id,
            subject: chatSubject.value,
            body: chatMessage.value,
        });
        
        chatSuccess.value = true;
        setTimeout(() => {
            closeChatModal();
        }, 2000);
    } catch (error: any) {
        chatError.value = error.response?.data?.message || 'Failed to send message. Please try again.';
    } finally {
        chatSending.value = false;
    }
};
</script>

<template>
    <Head :title="`${seller.business_name} - Official Store`" />
    
    <MarketplaceLayout>
        <div class="min-h-screen bg-gray-100">
            <!-- Cover Banner -->
            <div class="relative h-48 md:h-64 bg-gradient-to-r from-gray-900 via-gray-800 to-gray-900 overflow-hidden">
                <img 
                    v-if="seller.cover_image_url"
                    :src="seller.cover_image_url"
                    :alt="seller.business_name"
                    class="w-full h-full object-cover opacity-60"
                />
                <div v-else class="absolute inset-0 bg-gradient-to-br from-orange-600 via-amber-500 to-orange-600">
                    <div class="absolute inset-0 opacity-20" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.4\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
                </div>
            </div>

            <!-- Store Profile Card -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-20 relative z-10">
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="p-6">
                        <div class="flex flex-col lg:flex-row gap-6">
                            <!-- Store Logo & Basic Info -->
                            <div class="flex items-start gap-5">
                                <!-- Logo -->
                                <div class="relative flex-shrink-0">
                                    <div class="w-24 h-24 md:w-28 md:h-28 bg-white rounded-xl shadow-md border-4 border-white flex items-center justify-center overflow-hidden">
                                        <img 
                                            v-if="seller.logo_url"
                                            :src="seller.logo_url"
                                            :alt="seller.business_name"
                                            class="w-full h-full object-cover"
                                        />
                                        <span v-else class="text-4xl md:text-5xl font-bold text-transparent bg-clip-text bg-gradient-to-br from-orange-500 to-amber-600">
                                            {{ seller.business_name.charAt(0) }}
                                        </span>
                                    </div>
                                    <!-- Online Indicator -->
                                    <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-green-500 rounded-full border-4 border-white"></div>
                                </div>

                                <!-- Store Name & Badge -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-3 flex-wrap">
                                        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 truncate">
                                            {{ seller.business_name }}
                                        </h1>
                                        <span :class="['inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border', getTrustBadge(seller.trust_level).bg, getTrustBadge(seller.trust_level).color, getTrustBadge(seller.trust_level).border]">
                                            <component :is="getTrustBadge(seller.trust_level).icon" class="h-3.5 w-3.5" aria-hidden="true" />
                                            {{ getTrustBadge(seller.trust_level).label }}
                                        </span>
                                    </div>
                                    
                                    <div class="flex items-center gap-4 mt-2 text-sm text-gray-500">
                                        <span class="flex items-center gap-1">
                                            <MapPinIcon class="h-4 w-4" aria-hidden="true" />
                                            {{ seller.district ? `${seller.district}, ` : '' }}{{ seller.province }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <CalendarIcon class="h-4 w-4" aria-hidden="true" />
                                            Joined {{ formatDate(seller.created_at) }}
                                        </span>
                                    </div>

                                    <!-- Rating Stars (Clickable) -->
                                    <button 
                                        @click="goToReviews"
                                        class="flex items-center gap-3 mt-3 hover:opacity-80 transition-opacity cursor-pointer"
                                        aria-label="View reviews"
                                    >
                                        <div class="flex items-center gap-1">
                                            <template v-for="i in 5" :key="i">
                                                <StarIconSolid 
                                                    :class="[
                                                        'h-4 w-4',
                                                        i <= Math.round(seller.rating || 0) ? 'text-amber-400' : 'text-gray-200'
                                                    ]" 
                                                    aria-hidden="true" 
                                                />
                                            </template>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900">{{ (seller.rating || 0).toFixed(1) }}</span>
                                        <span class="text-sm text-orange-600 hover:underline">({{ seller.review_count || 0 }} reviews)</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Stats & Actions -->
                            <div class="flex-1 lg:flex-none">
                                <!-- Quick Stats -->
                                <div class="grid grid-cols-4 gap-4 mb-4">
                                    <div class="text-center">
                                        <div class="text-xl md:text-2xl font-bold text-gray-900">{{ sortedProducts.length }}</div>
                                        <div class="text-xs text-gray-500">Products</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-xl md:text-2xl font-bold text-gray-900">{{ seller.total_orders || 0 }}+</div>
                                        <div class="text-xs text-gray-500">Sold</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-xl md:text-2xl font-bold text-gray-900">{{ seller.response_rate ?? 98 }}%</div>
                                        <div class="text-xs text-gray-500">Response</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-xl md:text-2xl font-bold text-orange-600">{{ followersCount }}</div>
                                        <div class="text-xs text-gray-500">Followers</div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex items-center gap-2">
                                    <button
                                        @click="toggleFollow"
                                        :disabled="followLoading"
                                        :class="[
                                            'flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg font-medium text-sm transition-all disabled:opacity-50',
                                            isFollowing 
                                                ? 'bg-orange-50 text-orange-600 border border-orange-200 hover:bg-orange-100' 
                                                : 'bg-orange-500 text-white hover:bg-orange-600'
                                        ]"
                                    >
                                        <svg v-if="followLoading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <HeartIconSolid v-else-if="isFollowing" class="h-4 w-4" aria-hidden="true" />
                                        <HeartIcon v-else class="h-4 w-4" aria-hidden="true" />
                                        {{ isFollowing ? 'Following' : 'Follow' }}
                                    </button>
                                    <button 
                                        @click="openChatModal"
                                        class="flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-100 text-gray-700 rounded-lg font-medium text-sm hover:bg-gray-200 transition-colors"
                                    >
                                        <ChatBubbleLeftRightIcon class="h-4 w-4" aria-hidden="true" />
                                        Chat
                                    </button>
                                    <div class="relative">
                                        <button 
                                            ref="shareButtonRef"
                                            @click="showShareMenu = !showShareMenu"
                                            class="p-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
                                            aria-label="Share shop"
                                        >
                                            <ShareIcon class="h-4 w-4" aria-hidden="true" />
                                        </button>
                                        
                                        <!-- Share Dropdown - positioned to open to the left -->
                                        <Teleport to="body">
                                            <div 
                                                v-if="showShareMenu"
                                                class="fixed z-[100]"
                                                :style="shareMenuStyle"
                                            >
                                                <div class="w-48 bg-white rounded-lg shadow-xl border border-gray-200 py-2">
                                                    <button 
                                                        @click="shareShop('copy')"
                                                        class="w-full flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                                                    >
                                                        <CheckIcon v-if="linkCopied" class="h-4 w-4 text-green-500" aria-hidden="true" />
                                                        <LinkIcon v-else class="h-4 w-4" aria-hidden="true" />
                                                        {{ linkCopied ? 'Link Copied!' : 'Copy Link' }}
                                                    </button>
                                                    <button 
                                                        @click="shareShop('whatsapp')"
                                                        class="w-full flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                                                    >
                                                        <svg class="h-4 w-4 text-green-500" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                                        WhatsApp
                                                    </button>
                                                    <button 
                                                        @click="shareShop('facebook')"
                                                        class="w-full flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                                                    >
                                                        <svg class="h-4 w-4 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                                        Facebook
                                                    </button>
                                                    <button 
                                                        @click="shareShop('twitter')"
                                                        class="w-full flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                                                    >
                                                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                                        X (Twitter)
                                                    </button>
                                                </div>
                                            </div>
                                            <!-- Backdrop to close menu -->
                                            <div 
                                                v-if="showShareMenu"
                                                class="fixed inset-0 z-[99]"
                                                @click="showShareMenu = false"
                                            ></div>
                                        </Teleport>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Seller Highlights Bar -->
                        <div class="mt-6 pt-6 border-t border-gray-100">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div class="flex items-center gap-3 p-3 bg-green-50 rounded-lg">
                                    <div class="p-2 bg-green-100 rounded-lg">
                                        <ShieldCheckIcon class="h-5 w-5 text-green-600" aria-hidden="true" />
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">Verified Seller</div>
                                        <div class="text-xs text-gray-500">KYC Approved</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 p-3 bg-blue-50 rounded-lg">
                                    <div class="p-2 bg-blue-100 rounded-lg">
                                        <TruckIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">Fast Shipping</div>
                                        <div class="text-xs text-gray-500">1-3 Days Delivery</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 p-3 bg-purple-50 rounded-lg">
                                    <div class="p-2 bg-purple-100 rounded-lg">
                                        <ClockIcon class="h-5 w-5 text-purple-600" aria-hidden="true" />
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">Quick Response</div>
                                        <div class="text-xs text-gray-500">{{ seller.response_time || 'Within 1 hour' }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 p-3 bg-amber-50 rounded-lg">
                                    <div class="p-2 bg-amber-100 rounded-lg">
                                        <UserGroupIcon class="h-5 w-5 text-amber-600" aria-hidden="true" />
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ seller.total_orders }}+ Orders</div>
                                        <div class="text-xs text-gray-500">Happy Customers</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation & Filters -->
            <div class="bg-white border-b border-gray-200 sticky top-0 z-20 shadow-sm mt-6">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between">
                        <!-- Tabs -->
                        <nav class="flex gap-1">
                            <button
                                @click="activeTab = 'products'"
                                :class="[
                                    'px-5 py-4 font-medium text-sm border-b-2 transition-colors',
                                    activeTab === 'products'
                                        ? 'border-orange-500 text-orange-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700'
                                ]"
                            >
                                All Products
                            </button>
                            <button
                                @click="activeTab = 'about'"
                                :class="[
                                    'px-5 py-4 font-medium text-sm border-b-2 transition-colors',
                                    activeTab === 'about'
                                        ? 'border-orange-500 text-orange-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700'
                                ]"
                            >
                                About Store
                            </button>
                            <button
                                @click="activeTab = 'reviews'"
                                :class="[
                                    'px-5 py-4 font-medium text-sm border-b-2 transition-colors',
                                    activeTab === 'reviews'
                                        ? 'border-orange-500 text-orange-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700'
                                ]"
                            >
                                Reviews ({{ seller.review_count || 0 }})
                            </button>
                        </nav>

                        <!-- View & Sort Controls -->
                        <div v-if="activeTab === 'products'" class="flex items-center gap-3">
                            <!-- Sort Dropdown -->
                            <div class="relative">
                                <select
                                    v-model="sortBy"
                                    class="appearance-none bg-gray-50 border border-gray-200 rounded-lg pl-3 pr-8 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                >
                                    <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">
                                        {{ opt.label }}
                                    </option>
                                </select>
                                <ChevronDownIcon class="absolute right-2 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" aria-hidden="true" />
                            </div>

                            <!-- View Toggle -->
                            <div class="flex items-center bg-gray-100 rounded-lg p-1">
                                <button
                                    @click="viewMode = 'grid'"
                                    :class="['p-1.5 rounded', viewMode === 'grid' ? 'bg-white shadow-sm' : 'text-gray-500']"
                                    aria-label="Grid view"
                                >
                                    <Squares2X2Icon class="h-4 w-4" aria-hidden="true" />
                                </button>
                                <button
                                    @click="viewMode = 'list'"
                                    :class="['p-1.5 rounded', viewMode === 'list' ? 'bg-white shadow-sm' : 'text-gray-500']"
                                    aria-label="List view"
                                >
                                    <ListBulletIcon class="h-4 w-4" aria-hidden="true" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <!-- Products Tab -->
                <div v-show="activeTab === 'products'">
                    <!-- Category Filter Pills -->
                    <div v-if="categories?.length" class="flex items-center gap-2 mb-6 overflow-x-auto pb-2">
                        <button
                            @click="selectedCategory = 'all'"
                            :class="[
                                'flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-colors',
                                selectedCategory === 'all'
                                    ? 'bg-orange-500 text-white'
                                    : 'bg-white text-gray-700 border border-gray-200 hover:border-orange-300'
                            ]"
                        >
                            All Products
                        </button>
                        <button
                            v-for="cat in categories"
                            :key="cat.id"
                            @click="selectedCategory = cat.slug"
                            :class="[
                                'flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-colors',
                                selectedCategory === cat.slug
                                    ? 'bg-orange-500 text-white'
                                    : 'bg-white text-gray-700 border border-gray-200 hover:border-orange-300'
                            ]"
                        >
                            {{ cat.name }} ({{ cat.count }})
                        </button>
                    </div>

                    <!-- Grid View -->
                    <div v-if="sortedProducts.length && viewMode === 'grid'" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                        <Link
                            v-for="product in sortedProducts"
                            :key="product.id"
                            :href="route('marketplace.product', product.slug)"
                            class="group bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg hover:border-orange-200 transition-all duration-300"
                        >
                            <!-- Product Image -->
                            <div class="aspect-square bg-gray-50 relative overflow-hidden">
                                <img
                                    v-if="product.images?.length"
                                    :src="product.images[0]"
                                    :alt="product.name"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                                    <ShoppingBagIcon class="h-12 w-12" aria-hidden="true" />
                                </div>
                                
                                <!-- Discount Badge -->
                                <div
                                    v-if="product.compare_price && product.compare_price > product.price"
                                    class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded"
                                >
                                    -{{ Math.round((1 - product.price / product.compare_price) * 100) }}%
                                </div>

                                <!-- Quick Add Button -->
                                <button
                                    @click="addToCart(product.id, $event)"
                                    class="absolute bottom-2 right-2 p-2 bg-white/90 backdrop-blur-sm rounded-full shadow-lg opacity-0 group-hover:opacity-100 transition-opacity hover:bg-orange-500 hover:text-white"
                                    aria-label="Add to cart"
                                >
                                    <ShoppingCartIcon class="h-5 w-5" aria-hidden="true" />
                                </button>
                            </div>

                            <!-- Product Info -->
                            <div class="p-3">
                                <h3 class="font-medium text-gray-900 text-sm line-clamp-2 mb-2 group-hover:text-orange-600 transition-colors leading-snug min-h-[2.5rem]">
                                    {{ product.name }}
                                </h3>

                                <!-- Price -->
                                <div class="flex items-baseline gap-2 mb-2">
                                    <span class="text-lg font-bold text-orange-600">
                                        {{ formatPrice(product.price) }}
                                    </span>
                                    <span
                                        v-if="product.compare_price && product.compare_price > product.price"
                                        class="text-xs text-gray-400 line-through"
                                    >
                                        {{ formatPrice(product.compare_price) }}
                                    </span>
                                </div>

                                <!-- Rating & Sold -->
                                <div class="flex items-center justify-between text-xs text-gray-500">
                                    <div class="flex items-center gap-1">
                                        <StarIconSolid class="h-3.5 w-3.5 text-amber-400" aria-hidden="true" />
                                        <span>{{ (product.rating || 4.5).toFixed(1) }}</span>
                                    </div>
                                    <span>{{ product.sold_count || 0 }} sold</span>
                                </div>
                            </div>
                        </Link>
                    </div>

                    <!-- List View -->
                    <div v-else-if="sortedProducts.length && viewMode === 'list'" class="space-y-4">
                        <Link
                            v-for="product in sortedProducts"
                            :key="product.id"
                            :href="route('marketplace.product', product.slug)"
                            class="group flex gap-4 bg-white rounded-xl border border-gray-200 p-4 hover:shadow-lg hover:border-orange-200 transition-all"
                        >
                            <div class="w-32 h-32 flex-shrink-0 bg-gray-50 rounded-lg overflow-hidden">
                                <img
                                    v-if="product.images?.length"
                                    :src="product.images[0]"
                                    :alt="product.name"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform"
                                />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-medium text-gray-900 group-hover:text-orange-600 transition-colors mb-2">
                                    {{ product.name }}
                                </h3>
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="flex items-center gap-1">
                                        <StarIconSolid class="h-4 w-4 text-amber-400" aria-hidden="true" />
                                        <span class="text-sm">{{ (product.rating || 4.5).toFixed(1) }}</span>
                                    </div>
                                    <span class="text-sm text-gray-500">{{ product.sold_count || 0 }} sold</span>
                                </div>
                                <div class="flex items-baseline gap-2">
                                    <span class="text-xl font-bold text-orange-600">{{ formatPrice(product.price) }}</span>
                                    <span v-if="product.compare_price" class="text-sm text-gray-400 line-through">
                                        {{ formatPrice(product.compare_price) }}
                                    </span>
                                </div>
                            </div>
                            <button
                                @click="addToCart(product.id, $event)"
                                class="self-center px-4 py-2 bg-orange-500 text-white rounded-lg font-medium hover:bg-orange-600 transition-colors"
                            >
                                Add to Cart
                            </button>
                        </Link>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="text-center py-16 bg-white rounded-xl border border-gray-200">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-4">
                            <ShoppingBagIcon class="h-10 w-10 text-gray-400" aria-hidden="true" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">No Products Yet</h3>
                        <p class="text-gray-500 max-w-md mx-auto">
                            This store is setting up. Check back soon for amazing products!
                        </p>
                    </div>
                </div>

                <!-- About Tab -->
                <div v-show="activeTab === 'about'" class="grid lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Description -->
                        <div class="bg-white rounded-xl border border-gray-200 p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">About This Store</h2>
                            <p class="text-gray-600 leading-relaxed">
                                {{ seller.description || 'This seller hasn\'t added a description yet. Contact them to learn more about their products and services.' }}
                            </p>
                        </div>

                        <!-- Store Policies -->
                        <div class="bg-white rounded-xl border border-gray-200 p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Store Policies</h2>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div class="flex items-start gap-3">
                                    <TruckIcon class="h-5 w-5 text-orange-500 mt-0.5" aria-hidden="true" />
                                    <div>
                                        <h3 class="font-medium text-gray-900">Shipping</h3>
                                        <p class="text-sm text-gray-500">Ships within 1-3 business days</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <ShieldCheckIcon class="h-5 w-5 text-green-500 mt-0.5" aria-hidden="true" />
                                    <div>
                                        <h3 class="font-medium text-gray-900">Buyer Protection</h3>
                                        <p class="text-sm text-gray-500">Full refund if item not received</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar Stats -->
                    <div class="space-y-6">
                        <div class="bg-white rounded-xl border border-gray-200 p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Store Performance</h2>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600">Rating</span>
                                    <div class="flex items-center gap-1">
                                        <StarIconSolid class="h-4 w-4 text-amber-400" aria-hidden="true" />
                                        <span class="font-semibold">{{ (seller.rating || 0).toFixed(1) }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600">Products</span>
                                    <span class="font-semibold">{{ sortedProducts.length }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600">Total Sales</span>
                                    <span class="font-semibold">{{ seller.total_orders || 0 }}+</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600">Response Rate</span>
                                    <span class="font-semibold text-green-600">{{ seller.response_rate ?? 98 }}%</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600">Followers</span>
                                    <span class="font-semibold text-orange-600">{{ followersCount }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600">Reviews</span>
                                    <span class="font-semibold">{{ seller.review_count || 0 }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl border border-gray-200 p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Location</h2>
                            <div class="flex items-start gap-3">
                                <MapPinIcon class="h-5 w-5 text-orange-500 mt-0.5" aria-hidden="true" />
                                <div>
                                    <p class="font-medium text-gray-900">{{ seller.province }}</p>
                                    <p v-if="seller.district" class="text-sm text-gray-500">{{ seller.district }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reviews Tab -->
                <div v-show="activeTab === 'reviews'" class="space-y-6">
                    <!-- Reviews Summary -->
                    <div class="bg-white rounded-xl border border-gray-200 p-6">
                        <div class="flex flex-col md:flex-row gap-8">
                            <!-- Overall Rating -->
                            <div class="text-center md:text-left">
                                <div class="text-5xl font-bold text-gray-900 mb-2">{{ (seller.rating || 0).toFixed(1) }}</div>
                                <div class="flex items-center justify-center md:justify-start gap-1 mb-2">
                                    <template v-for="i in 5" :key="i">
                                        <StarIconSolid 
                                            :class="['h-5 w-5', i <= Math.round(seller.rating || 0) ? 'text-amber-400' : 'text-gray-200']" 
                                            aria-hidden="true" 
                                        />
                                    </template>
                                </div>
                                <p class="text-sm text-gray-500">Based on {{ seller.review_count || 0 }} reviews</p>
                            </div>

                            <!-- Rating Breakdown -->
                            <div class="flex-1 space-y-2">
                                <div v-for="stars in [5, 4, 3, 2, 1]" :key="stars" class="flex items-center gap-3">
                                    <span class="text-sm text-gray-600 w-12">{{ stars }} star</span>
                                    <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                                        <div 
                                            class="h-full bg-amber-400 rounded-full transition-all"
                                            :style="{ width: (ratingBreakdown?.[stars]?.percentage || 0) + '%' }"
                                        ></div>
                                    </div>
                                    <span class="text-sm text-gray-500 w-10">{{ ratingBreakdown?.[stars]?.percentage || 0 }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reviews List -->
                    <div v-if="reviews && reviews.length > 0" class="space-y-4">
                        <div 
                            v-for="review in reviews" 
                            :key="review.id"
                            class="bg-white rounded-xl border border-gray-200 p-6"
                        >
                            <div class="flex items-start gap-4">
                                <!-- Avatar -->
                                <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <span class="text-orange-600 font-semibold">{{ review.buyer?.name?.charAt(0) || 'U' }}</span>
                                </div>
                                
                                <div class="flex-1 min-w-0">
                                    <!-- Header -->
                                    <div class="flex items-center justify-between mb-2">
                                        <div>
                                            <span class="font-medium text-gray-900">{{ review.buyer?.name || 'Anonymous' }}</span>
                                            <span class="text-gray-400 mx-2">•</span>
                                            <span class="text-sm text-gray-500">{{ review.formatted_date }}</span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <template v-for="i in 5" :key="i">
                                                <StarIconSolid 
                                                    :class="['h-4 w-4', i <= review.rating ? 'text-amber-400' : 'text-gray-200']" 
                                                    aria-hidden="true" 
                                                />
                                            </template>
                                        </div>
                                    </div>
                                    
                                    <!-- Product Link -->
                                    <Link 
                                        v-if="review.product"
                                        :href="route('marketplace.product', review.product.slug)"
                                        class="text-sm text-orange-600 hover:underline mb-2 inline-block"
                                    >
                                        {{ review.product.name }}
                                    </Link>
                                    
                                    <!-- Comment -->
                                    <p class="text-gray-600">{{ review.comment }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                            <ChatBubbleLeftRightIcon class="h-8 w-8 text-gray-400" aria-hidden="true" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">No Reviews Yet</h3>
                        <p class="text-gray-500 max-w-md mx-auto">
                            This seller hasn't received any reviews yet. Be the first to leave a review after your purchase!
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Modal -->
        <Teleport to="body">
            <div 
                v-if="showChatModal" 
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
                @click.self="closeChatModal"
            >
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-black/50" @click="closeChatModal"></div>
                
                <!-- Modal -->
                <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-orange-500 to-amber-500 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <ChatBubbleLeftRightIcon class="h-6 w-6 text-white" aria-hidden="true" />
                                <h3 class="text-lg font-semibold text-white">Message Seller</h3>
                            </div>
                            <button 
                                @click="closeChatModal"
                                class="p-1 hover:bg-white/20 rounded-full transition-colors"
                                aria-label="Close chat modal"
                            >
                                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <p class="text-white/80 text-sm mt-1">Send a message to {{ seller.business_name }}</p>
                    </div>
                    
                    <!-- Success State -->
                    <div v-if="chatSuccess" class="p-8 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                            <CheckIcon class="h-8 w-8 text-green-600" aria-hidden="true" />
                        </div>
                        <h4 class="text-lg font-semibold text-gray-900 mb-2">Message Sent!</h4>
                        <p class="text-gray-500">The seller will respond to your message soon.</p>
                    </div>
                    
                    <!-- Form -->
                    <form v-else @submit.prevent="sendChatMessage" class="p-6 space-y-4">
                        <!-- Error Message -->
                        <div v-if="chatError" class="p-3 bg-red-50 border border-red-200 rounded-lg text-red-600 text-sm">
                            {{ chatError }}
                        </div>
                        
                        <!-- Subject -->
                        <div>
                            <label for="chat-subject" class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                            <input
                                id="chat-subject"
                                v-model="chatSubject"
                                type="text"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                placeholder="What's this about?"
                                required
                            />
                        </div>
                        
                        <!-- Message -->
                        <div>
                            <label for="chat-message" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                            <textarea
                                id="chat-message"
                                v-model="chatMessage"
                                rows="4"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 resize-none"
                                placeholder="Type your message here..."
                                required
                            ></textarea>
                        </div>
                        
                        <!-- Actions -->
                        <div class="flex items-center justify-end gap-3 pt-2">
                            <button
                                type="button"
                                @click="closeChatModal"
                                class="px-4 py-2.5 text-gray-700 font-medium rounded-lg hover:bg-gray-100 transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="chatSending"
                                class="px-6 py-2.5 bg-orange-500 text-white font-medium rounded-lg hover:bg-orange-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                            >
                                <svg v-if="chatSending" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ chatSending ? 'Sending...' : 'Send Message' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </MarketplaceLayout>
</template>