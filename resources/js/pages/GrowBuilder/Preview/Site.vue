<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref, onMounted } from 'vue';
import { Bars3Icon, XMarkIcon, StarIcon, CheckIcon, ShoppingBagIcon, ShoppingCartIcon } from '@heroicons/vue/24/outline';
import { StarIcon as StarSolidIcon } from '@heroicons/vue/24/solid';
import SplashScreen from '@/components/GrowBuilder/SplashScreens.vue';

interface Section {
    id: string;
    type: string;
    content: Record<string, any>;
    style: Record<string, any>;
}

interface Page {
    id: number;
    title: string;
    slug: string;
    content: { sections: Section[] };
    isHomepage: boolean;
}

interface NavPage {
    id: number;
    title: string;
    slug: string;
    isHomepage: boolean;
}

interface Site {
    id: number;
    name: string;
    subdomain: string;
    theme: Record<string, any> | null;
    logo: string | null;
    favicon: string | null;
    url: string;
}

interface Settings {
    navigation?: {
        logoText?: string;
        logo?: string;
        navItems?: any[];
        showCta?: boolean;
        ctaText?: string;
        ctaLink?: string;
        sticky?: boolean;
        // Auth buttons
        showAuthButtons?: boolean;
        showLoginButton?: boolean;
        showRegisterButton?: boolean;
        loginText?: string;
        registerText?: string;
        loginStyle?: 'link' | 'outline' | 'solid';
        registerStyle?: 'link' | 'outline' | 'solid';
    };
    footer?: {
        logo?: string;
        copyrightText?: string;
        columns?: any[];
        socialLinks?: any[];
        backgroundColor?: string;
        textColor?: string;
    };
    splash?: {
        enabled?: boolean;
        style?: 'none' | 'minimal' | 'pulse' | 'wave' | 'gradient' | 'particles' | 'elegant';
        tagline?: string;
    };
}

interface Product {
    id: number;
    name: string;
    slug: string;
    price: number;
    priceFormatted: string;
    comparePrice: number | null;
    comparePriceFormatted: string | null;
    image: string | null;
    images: string[];
    shortDescription: string | null;
    category: string | null;
    inStock: boolean;
    isFeatured: boolean;
    hasDiscount: boolean;
    discountPercentage: number;
}

interface CartItem {
    product: Product;
    quantity: number;
}

const props = defineProps<{
    site: Site;
    page: Page;
    pages: NavPage[];
    settings: Settings | null;
    products?: Product[];
    isPreview?: boolean;
    showWatermark?: boolean;
    isTemplatePreview?: boolean;
}>();

// Mobile menu state
const mobileMenuOpen = ref(false);

// Cart state
const cart = ref<CartItem[]>([]);
const cartOpen = ref(false);
const cartNotification = ref('');

// Slideshow state
const currentSlide = ref<Record<string, number>>({});
const slideIntervals = ref<Record<string, ReturnType<typeof setInterval>>>({});

// Product search/filter state
const productSearch = ref('');
const selectedCategory = ref('');
const productSort = ref('default');

// Testimonial carousel state
const currentTestimonial = ref<Record<string, number>>({});

const sections = computed(() => props.page.content?.sections || []);
const navigation = computed(() => props.settings?.navigation || {});
const footer = computed(() => props.settings?.footer || {});

// Products for display (from props)
const displayProducts = computed(() => props.products || []);

// Get unique product categories
const productCategories = computed(() => {
    const categories = new Set<string>();
    displayProducts.value.forEach(p => {
        if (p.category) categories.add(p.category);
    });
    return Array.from(categories).sort();
});

// Filtered and sorted products
const filteredProducts = computed(() => {
    let products = [...displayProducts.value];
    
    // Apply search filter
    if (productSearch.value.trim()) {
        const search = productSearch.value.toLowerCase();
        products = products.filter(p => 
            p.name.toLowerCase().includes(search) ||
            (p.shortDescription && p.shortDescription.toLowerCase().includes(search)) ||
            (p.category && p.category.toLowerCase().includes(search))
        );
    }
    
    // Apply category filter
    if (selectedCategory.value) {
        products = products.filter(p => p.category === selectedCategory.value);
    }
    
    // Apply sorting
    switch (productSort.value) {
        case 'price-asc':
            products.sort((a, b) => a.price - b.price);
            break;
        case 'price-desc':
            products.sort((a, b) => b.price - a.price);
            break;
        case 'name-asc':
            products.sort((a, b) => a.name.localeCompare(b.name));
            break;
        case 'name-desc':
            products.sort((a, b) => b.name.localeCompare(a.name));
            break;
    }
    
    return products;
});

// Check if page has a products section (to show cart even without products)
const hasProductsSection = computed(() => {
    return sections.value.some(s => s.type === 'products' || s.type === 'product-search');
});

// Show cart when there are products OR when page has products section
const showCart = computed(() => {
    return displayProducts.length > 0 || hasProductsSection.value;
});

// Cart computed properties
const cartItemCount = computed(() => cart.value.reduce((sum, item) => sum + item.quantity, 0));
const cartTotal = computed(() => cart.value.reduce((sum, item) => sum + (item.product.price * item.quantity), 0));
const cartTotalFormatted = computed(() => 'K' + (cartTotal.value / 100).toFixed(2));

// Cart key for localStorage (unique per site)
const cartStorageKey = computed(() => `gb_cart_${props.site.subdomain}`);

// Load cart from localStorage on mount
onMounted(() => {
    const savedCart = localStorage.getItem(cartStorageKey.value);
    if (savedCart) {
        try {
            cart.value = JSON.parse(savedCart);
        } catch (e) {
            cart.value = [];
        }
    }
    // Initialize slideshows
    initSlideshows();
});

// Save cart to localStorage
const saveCart = () => {
    localStorage.setItem(cartStorageKey.value, JSON.stringify(cart.value));
};

// Add product to cart
const addToCart = (product: Product) => {
    const existingItem = cart.value.find(item => item.product.id === product.id);
    if (existingItem) {
        existingItem.quantity++;
    } else {
        cart.value.push({ product, quantity: 1 });
    }
    saveCart();
    
    // Show notification
    cartNotification.value = `${product.name} added to cart`;
    setTimeout(() => {
        cartNotification.value = '';
    }, 2000);
};

// Update cart item quantity
const updateCartQuantity = (productId: number, quantity: number) => {
    const item = cart.value.find(item => item.product.id === productId);
    if (item) {
        if (quantity <= 0) {
            removeFromCart(productId);
        } else {
            item.quantity = quantity;
            saveCart();
        }
    }
};

// Remove item from cart
const removeFromCart = (productId: number) => {
    cart.value = cart.value.filter(item => item.product.id !== productId);
    saveCart();
};

// Clear cart
const clearCart = () => {
    cart.value = [];
    saveCart();
};

// Slideshow functions
const setSlide = (sectionId: string, index: number) => {
    currentSlide.value[sectionId] = index;
};

const nextSlide = (sectionId: string, totalSlides: number) => {
    const current = currentSlide.value[sectionId] || 0;
    currentSlide.value[sectionId] = (current + 1) % totalSlides;
};

const prevSlide = (sectionId: string, totalSlides: number) => {
    const current = currentSlide.value[sectionId] || 0;
    currentSlide.value[sectionId] = (current - 1 + totalSlides) % totalSlides;
};

// Initialize slideshow auto-play
const initSlideshows = () => {
    sections.value.forEach((section: Section) => {
        if (section.type === 'hero' && section.content.layout === 'slideshow' && section.content.autoPlay && section.content.slides?.length > 1) {
            const interval = section.content.slideInterval || 5000;
            slideIntervals.value[section.id] = setInterval(() => {
                nextSlide(section.id, section.content.slides.length);
            }, interval);
        }
    });
};

// Testimonial carousel functions
const nextTestimonial = (sectionId: string, total: number) => {
    const current = currentTestimonial.value[sectionId] || 0;
    currentTestimonial.value[sectionId] = (current + 1) % total;
};

const prevTestimonial = (sectionId: string, total: number) => {
    const current = currentTestimonial.value[sectionId] || 0;
    currentTestimonial.value[sectionId] = (current - 1 + total) % total;
};

// Get product URL
const getProductUrl = (product: Product) => {
    if (isOnSubdomain.value) {
        return `/product/${product.slug}`;
    }
    return `/sites/${props.site.subdomain}/product/${product.slug}`;
};

// Proceed to checkout
const proceedToCheckout = () => {
    // Store cart in sessionStorage for checkout page
    sessionStorage.setItem(`gb_checkout_${props.site.subdomain}`, JSON.stringify(cart.value));
    if (isOnSubdomain.value) {
        window.location.href = '/checkout';
    } else {
        window.location.href = `/sites/${props.site.subdomain}/checkout`;
    }
};

// Use navItems from settings if available, otherwise fall back to pages
const navLinks = computed(() => {
    if (navigation.value.navItems && navigation.value.navItems.length > 0) {
        return navigation.value.navItems;
    }
    // Fallback to pages prop
    return props.pages.map(p => ({
        id: `nav-${p.id}`,
        label: p.title,
        url: p.isHomepage ? '/' : `/${p.slug}`,
        pageId: p.id,
        isExternal: false,
    }));
});

// Get template ID from URL for template preview navigation
const getTemplateIdFromUrl = () => {
    const match = window.location.pathname.match(/\/templates\/(\d+)/);
    return match ? match[1] : null;
};

// Detect if we're viewing on a subdomain (e.g., ndelimas.mygrownet.com)
const isOnSubdomain = computed(() => {
    const host = window.location.hostname;
    // Check if hostname matches subdomain pattern (including www.subdomain.mygrownet.com)
    const match = host.match(/^(?:www\.)?([a-z0-9-]+)\.mygrownet\.com$/i);
    if (match) {
        const subdomain = match[1].toLowerCase();
        // Exclude reserved subdomains and main domain
        const reserved = ['www', 'mygrownet', 'api', 'admin', 'mail', 'staging', 'dev', 'growbuilder', 'app', 'dashboard'];
        const isReserved = reserved.includes(subdomain);
        // Check if it matches our site's subdomain
        const matchesSite = subdomain === props.site.subdomain.toLowerCase();
        return !isReserved && matchesSite;
    }
    // Also check for custom domains (not mygrownet.com)
    return !host.includes('mygrownet.com') && !host.includes('localhost') && !host.includes('127.0.0.1');
});

const getPageUrl = (page: NavPage) => {
    // For template preview, use template render route
    if (props.isTemplatePreview) {
        const templateId = getTemplateIdFromUrl();
        if (templateId) {
            if (page.isHomepage) return `/growbuilder/templates/${templateId}/render`;
            return `/growbuilder/templates/${templateId}/render/${page.slug}`;
        }
    }
    // On subdomain, use relative paths
    if (isOnSubdomain.value) {
        if (page.isHomepage) return '/';
        return `/${page.slug}`;
    }
    if (page.isHomepage) return `/sites/${props.site.subdomain}`;
    return `/sites/${props.site.subdomain}/${page.slug}`;
};

const getNavItemUrl = (navItem: any) => {
    if (navItem.isExternal) return navItem.url;
    
    // For template preview, use template render route
    if (props.isTemplatePreview) {
        const templateId = getTemplateIdFromUrl();
        if (templateId) {
            if (navItem.url === '/' || navItem.url === '') return `/growbuilder/templates/${templateId}/render`;
            // Remove leading slash if present
            const slug = navItem.url.startsWith('/') ? navItem.url.slice(1) : navItem.url;
            return `/growbuilder/templates/${templateId}/render/${slug}`;
        }
    }
    
    // On subdomain, use relative paths
    if (isOnSubdomain.value) {
        return navItem.url || '/';
    }
    
    if (navItem.url === '/') return `/sites/${props.site.subdomain}`;
    return `/sites/${props.site.subdomain}${navItem.url}`;
};

const isNavItemActive = (navItem: any) => {
    if (navItem.pageId) {
        return navItem.pageId === props.page.id;
    }
    // Check by URL
    const currentSlug = props.page.isHomepage ? '/' : `/${props.page.slug}`;
    return navItem.url === currentSlug;
};

// Auth URLs for site members
const loginUrl = computed(() => isOnSubdomain.value ? '/login' : `/sites/${props.site.subdomain}/login`);
const registerUrl = computed(() => isOnSubdomain.value ? '/register' : `/sites/${props.site.subdomain}/register`);

// Hero section helper functions (matching editor)
const getHeroBackgroundStyle = (section: Section) => {
    const s = section.style;
    const minHeight = s?.minHeight ? `${s.minHeight}px` : '500px';
    
    // Check for gradient background
    if (s?.backgroundType === 'gradient' && s?.gradientFrom && s?.gradientTo) {
        const degMap: Record<string, string> = {
            'to-r': '90deg',
            'to-b': '180deg',
            'to-br': '135deg',
            'to-tr': '45deg',
        };
        const direction = degMap[s.gradientDirection || 'to-r'] || '90deg';
        return {
            background: `linear-gradient(${direction}, ${s.gradientFrom}, ${s.gradientTo})`,
            minHeight,
        };
    }
    
    return {
        backgroundColor: s?.backgroundColor || '#1e40af',
        minHeight,
    };
};

const hasImageBackground = (section: Section) => {
    const c = section.content;
    return (!c.backgroundType || c.backgroundType === 'image') && c.backgroundImage;
};

const hasVideoBackground = (section: Section) => {
    const c = section.content;
    return c.backgroundType === 'video' && c.videoBackground;
};

const getOverlayStyle = (section: Section) => {
    const c = section.content;
    const color = c.overlayColor || 'black';
    const opacity = (c.overlayOpacity ?? 50) / 100;
    
    if (color === 'gradient') {
        const from = c.overlayGradientFrom || '#2563eb';
        const to = c.overlayGradientTo || '#7c3aed';
        const midpoint = c.overlayGradientMidpoint || 50;
        const opacityHex = Math.round(opacity * 255).toString(16).padStart(2, '0');
        return {
            background: `linear-gradient(135deg, ${from}${opacityHex} 0%, ${from}${opacityHex} ${Math.max(0, midpoint - 20)}%, ${to}${opacityHex} ${Math.min(100, midpoint + 20)}%, ${to}${opacityHex} 100%)`,
        };
    } else if (color === 'white') {
        return { backgroundColor: `rgba(255, 255, 255, ${opacity})` };
    } else {
        return { backgroundColor: `rgba(0, 0, 0, ${opacity})` };
    }
};

const closeMobileMenu = () => {
    mobileMenuOpen.value = false;
};

// Helper to get element transform from saved offsets (matching builder behavior)
const getElementTransform = (section: Section, elementKey: string): string => {
    const offset = section.content?.elementOffsets?.[elementKey];
    if (!offset || offset.y === 0) return '';
    return `translateY(${offset.y}px)`;
};
</script>

<template>
    <Head :title="`${page.title} - ${site.name}`" />

    <!-- Splash Screen -->
    <SplashScreen
        v-if="settings?.splash?.enabled !== false"
        :site-name="site.name"
        :logo="navigation.logo || site.logo"
        :primary-color="site.theme?.primaryColor || '#2563eb'"
        :style="settings?.splash?.style || 'minimal'"
        :tagline="settings?.splash?.tagline || ''"
    />

    <div class="min-h-screen bg-white">
        <!-- Mobile-Friendly Navigation -->
        <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Logo -->
                    <div class="flex items-center">
                        <a :href="getPageUrl({ id: 0, title: '', slug: '', isHomepage: true })" class="flex items-center">
                            <img v-if="navigation.logo" :src="navigation.logo" class="h-8" :alt="site.name" />
                            <span v-else class="text-lg sm:text-xl font-bold text-gray-900">{{ navigation.logoText || site.name }}</span>
                        </a>
                    </div>

                    <!-- Desktop Navigation -->
                    <div class="hidden md:flex items-center gap-6">
                        <a
                            v-for="navItem in navLinks"
                            :key="navItem.id"
                            :href="getNavItemUrl(navItem)"
                            :target="navItem.isExternal ? '_blank' : undefined"
                            :class="['text-sm font-medium transition-colors', isNavItemActive(navItem) ? 'text-blue-600' : 'text-gray-600 hover:text-gray-900']"
                        >
                            {{ navItem.label }}
                        </a>
                        
                        <!-- Auth Buttons -->
                        <template v-if="navigation.showAuthButtons">
                            <a
                                v-if="navigation.showLoginButton !== false"
                                :href="loginUrl"
                                :class="[
                                    'text-sm font-medium transition-colors',
                                    navigation.loginStyle === 'solid' ? 'px-4 py-2 bg-gray-100 text-gray-900 rounded-lg hover:bg-gray-200' :
                                    navigation.loginStyle === 'outline' ? 'px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50' :
                                    'text-gray-600 hover:text-gray-900'
                                ]"
                            >
                                {{ navigation.loginText || 'Login' }}
                            </a>
                            <a
                                v-if="navigation.showRegisterButton !== false"
                                :href="registerUrl"
                                :class="[
                                    'text-sm font-medium transition-colors',
                                    navigation.registerStyle === 'outline' ? 'px-4 py-2 border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50' :
                                    navigation.registerStyle === 'link' ? 'text-blue-600 hover:text-blue-700' :
                                    'px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700'
                                ]"
                            >
                                {{ navigation.registerText || 'Sign Up' }}
                            </a>
                        </template>
                        
                        <!-- Cart Button (Desktop) -->
                        <button
                            v-if="showCart"
                            @click="cartOpen = true"
                            class="relative p-2 text-gray-600 hover:text-gray-900 transition-colors"
                            aria-label="Open cart"
                        >
                            <ShoppingCartIcon class="h-6 w-6" aria-hidden="true" />
                            <span 
                                v-if="cartItemCount > 0"
                                class="absolute -top-1 -right-1 bg-blue-600 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center"
                            >
                                {{ cartItemCount > 9 ? '9+' : cartItemCount }}
                            </span>
                        </button>
                        
                        <a
                            v-if="navigation.showCta"
                            :href="navigation.ctaLink || '#contact'"
                            class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
                        >
                            {{ navigation.ctaText || 'Contact Us' }}
                        </a>
                    </div>

                    <!-- Mobile Menu Button + Cart -->
                    <div class="flex items-center gap-2 md:hidden">
                        <!-- Cart Button (Mobile) -->
                        <button
                            v-if="showCart"
                            @click="cartOpen = true"
                            class="relative p-2 text-gray-600 hover:text-gray-900"
                            aria-label="Open cart"
                        >
                            <ShoppingCartIcon class="h-6 w-6" aria-hidden="true" />
                            <span 
                                v-if="cartItemCount > 0"
                                class="absolute -top-1 -right-1 bg-blue-600 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center"
                            >
                                {{ cartItemCount > 9 ? '9+' : cartItemCount }}
                            </span>
                        </button>
                        
                        <button
                            @click="mobileMenuOpen = !mobileMenuOpen"
                            class="p-2 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100"
                            :aria-label="mobileMenuOpen ? 'Close menu' : 'Open menu'"
                        >
                            <XMarkIcon v-if="mobileMenuOpen" class="h-6 w-6" aria-hidden="true" />
                            <Bars3Icon v-else class="h-6 w-6" aria-hidden="true" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu Panel -->
            <div
                v-if="mobileMenuOpen"
                class="md:hidden border-t border-gray-200 bg-white"
            >
                <div class="px-4 py-3 space-y-1">
                    <a
                        v-for="navItem in navLinks"
                        :key="navItem.id"
                        :href="getNavItemUrl(navItem)"
                        :target="navItem.isExternal ? '_blank' : undefined"
                        @click="closeMobileMenu"
                        :class="[
                            'block px-3 py-3 rounded-lg text-base font-medium transition-colors',
                            isNavItemActive(navItem) 
                                ? 'bg-blue-50 text-blue-600' 
                                : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'
                        ]"
                    >
                        {{ navItem.label }}
                    </a>
                    
                    <!-- Auth Buttons in Mobile Menu -->
                    <template v-if="navigation.showAuthButtons">
                        <div class="border-t border-gray-100 pt-3 mt-3 space-y-2">
                            <a
                                v-if="navigation.showLoginButton !== false"
                                :href="loginUrl"
                                @click="closeMobileMenu"
                                class="block px-3 py-3 rounded-lg text-base font-medium text-gray-700 hover:bg-gray-50 transition-colors"
                            >
                                {{ navigation.loginText || 'Login' }}
                            </a>
                            <a
                                v-if="navigation.showRegisterButton !== false"
                                :href="registerUrl"
                                @click="closeMobileMenu"
                                class="block w-full px-4 py-3 bg-blue-600 text-white text-center font-medium rounded-lg hover:bg-blue-700 transition-colors"
                            >
                                {{ navigation.registerText || 'Sign Up' }}
                            </a>
                        </div>
                    </template>
                    
                    <a
                        v-if="navigation.showCta"
                        :href="navigation.ctaLink || '#contact'"
                        @click="closeMobileMenu"
                        class="block w-full mt-3 px-4 py-3 bg-blue-600 text-white text-center font-medium rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        {{ navigation.ctaText || 'Contact Us' }}
                    </a>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main>
            <template v-for="section in sections" :key="section.id">
                <!-- Hero Section - Multiple Layouts -->
                <section
                    v-if="section.type === 'hero'"
                    class="relative overflow-hidden"
                    :style="getHeroBackgroundStyle(section)"
                >
                    <!-- Slideshow Layout -->
                    <template v-if="section.content.layout === 'slideshow' && section.content.slides?.length">
                        <div class="relative" style="min-height: inherit;">
                            <!-- Slides -->
                            <div 
                                v-for="(slide, idx) in section.content.slides" 
                                :key="idx"
                                class="absolute inset-0 transition-opacity duration-500"
                                :class="idx === (currentSlide[section.id] || 0) ? 'opacity-100 z-10' : 'opacity-0 z-0'"
                            >
                                <img v-if="slide.backgroundImage" :src="slide.backgroundImage" class="w-full h-full object-cover" :alt="slide.title" />
                                <div class="absolute inset-0 bg-black/40"></div>
                                <div class="absolute inset-0 flex flex-col justify-center items-center text-center px-4 sm:px-6 lg:px-8">
                                    <h1 class="font-bold mb-4 text-3xl sm:text-4xl lg:text-5xl text-white">{{ slide.title }}</h1>
                                    <p v-if="slide.subtitle" class="mb-6 text-base sm:text-lg text-white/90 max-w-2xl">{{ slide.subtitle }}</p>
                                    <a v-if="slide.buttonText" :href="slide.buttonLink || '#'" class="px-6 py-3 bg-white text-blue-600 font-semibold rounded-lg hover:bg-gray-100 transition-colors">
                                        {{ slide.buttonText }}
                                    </a>
                                </div>
                            </div>
                            <!-- Slide Navigation Dots -->
                            <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-20 flex gap-2">
                                <button 
                                    v-for="(_, idx) in section.content.slides" 
                                    :key="idx"
                                    @click="setSlide(section.id, idx)"
                                    class="w-3 h-3 rounded-full transition-colors"
                                    :class="idx === (currentSlide[section.id] || 0) ? 'bg-white' : 'bg-white/50 hover:bg-white/75'"
                                    :aria-label="`Go to slide ${idx + 1}`"
                                ></button>
                            </div>
                            <!-- Slide Arrows -->
                            <button 
                                @click="prevSlide(section.id, section.content.slides.length)"
                                class="absolute left-4 top-1/2 -translate-y-1/2 z-20 w-10 h-10 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center text-white transition-colors"
                                aria-label="Previous slide"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                            </button>
                            <button 
                                @click="nextSlide(section.id, section.content.slides.length)"
                                class="absolute right-4 top-1/2 -translate-y-1/2 z-20 w-10 h-10 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center text-white transition-colors"
                                aria-label="Next slide"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </button>
                        </div>
                    </template>

                    <!-- Split Layout (Image Left or Right) -->
                    <template v-else-if="section.content.layout === 'split-left' || section.content.layout === 'split-right'">
                        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
                            <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 items-center" :class="section.content.layout === 'split-left' ? 'lg:flex-row-reverse' : ''">
                                <!-- Text Content -->
                                <div :class="section.content.layout === 'split-left' ? 'lg:order-2' : 'lg:order-1'">
                                    <h1 class="font-bold mb-4 text-3xl sm:text-4xl lg:text-5xl" :style="{ color: section.style?.textColor || '#ffffff' }">
                                        {{ section.content.title || 'Hero Title' }}
                                    </h1>
                                    <p class="mb-6 text-base sm:text-lg" :style="{ color: section.style?.textColor || '#ffffff', opacity: 0.9 }">
                                        {{ section.content.subtitle || 'Subtitle text' }}
                                    </p>
                                    <div class="flex flex-wrap gap-3">
                                        <a :href="section.content.buttonLink || '#'" class="px-6 py-3 bg-white text-blue-600 font-semibold rounded-lg hover:bg-gray-100 transition-colors">
                                            {{ section.content.buttonText || 'Get Started' }}
                                        </a>
                                        <a v-if="section.content.secondaryButtonText" :href="section.content.secondaryButtonLink || '#'" class="px-6 py-3 border-2 font-semibold rounded-lg hover:bg-white/10 transition-colors" :style="{ borderColor: section.style?.textColor || '#ffffff', color: section.style?.textColor || '#ffffff' }">
                                            {{ section.content.secondaryButtonText }}
                                        </a>
                                    </div>
                                </div>
                                <!-- Image -->
                                <div :class="section.content.layout === 'split-left' ? 'lg:order-1' : 'lg:order-2'">
                                    <img v-if="section.content.image" :src="section.content.image" class="rounded-xl shadow-2xl w-full" :alt="section.content.title" />
                                    <div v-else class="aspect-video bg-white/10 rounded-xl flex items-center justify-center">
                                        <span class="text-white/50 text-sm">Add hero image</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Default Centered Layout -->
                    <template v-else>
                        <!-- Background Image -->
                        <div v-if="hasImageBackground(section)" class="absolute inset-0">
                            <img :src="section.content.backgroundImage" class="w-full h-full object-cover" />
                            <div class="absolute inset-0" :style="getOverlayStyle(section)"></div>
                        </div>

                        <!-- Video Background -->
                        <div v-else-if="hasVideoBackground(section)" class="absolute inset-0">
                            <iframe
                                :src="section.content.videoBackground"
                                class="absolute w-full h-full object-cover"
                                style="transform: scale(1.5); pointer-events: none;"
                                frameborder="0"
                                allow="autoplay; fullscreen"
                                allowfullscreen
                            ></iframe>
                            <div class="absolute inset-0" :style="getOverlayStyle(section)"></div>
                        </div>
                        
                        <!-- Content -->
                        <div
                            class="flex flex-col justify-center relative z-10 py-16 px-4 sm:px-6 lg:px-8"
                            style="min-height: inherit;"
                            :class="{
                                'text-left items-start': section.content.textPosition === 'left',
                                'text-center items-center': !section.content.textPosition || section.content.textPosition === 'center',
                                'text-right items-end': section.content.textPosition === 'right'
                            }"
                        >
                            <div 
                                class="w-full max-w-3xl"
                                :class="{
                                    'mx-auto': !section.content.textPosition || section.content.textPosition === 'center',
                                    'ml-auto': section.content.textPosition === 'right'
                                }"
                            >
                                <div :style="{ transform: getElementTransform(section, 'title') }">
                                    <h1 class="font-bold mb-4 text-3xl sm:text-4xl lg:text-5xl" :style="{ color: section.style?.textColor || '#ffffff' }">
                                        {{ section.content.title || 'Hero Title' }}
                                    </h1>
                                </div>
                                <div :style="{ transform: getElementTransform(section, 'subtitle') }">
                                    <p class="mb-6 text-base sm:text-lg" :style="{ color: section.style?.textColor || '#ffffff', opacity: 0.9 }">
                                        {{ section.content.subtitle || 'Subtitle text' }}
                                    </p>
                                </div>
                                <div :style="{ transform: getElementTransform(section, 'buttons') }" class="flex flex-wrap gap-3" :class="{ 'justify-start': section.content.textPosition === 'left', 'justify-center': !section.content.textPosition || section.content.textPosition === 'center', 'justify-end': section.content.textPosition === 'right' }">
                                    <a :href="section.content.buttonLink || '#'" class="px-6 py-3 bg-white text-blue-600 font-semibold rounded-lg hover:bg-gray-100 transition-colors">
                                        {{ section.content.buttonText || 'Get Started' }}
                                    </a>
                                    <a v-if="section.content.secondaryButtonText" :href="section.content.secondaryButtonLink || '#'" class="px-6 py-3 border-2 font-semibold rounded-lg hover:bg-white/10 transition-colors" :style="{ borderColor: section.style?.textColor || '#ffffff', color: section.style?.textColor || '#ffffff' }">
                                        {{ section.content.secondaryButtonText }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </template>
                </section>

                <!-- Page Header Section - Mobile Optimized -->
                <section
                    v-else-if="section.type === 'page-header'"
                    class="relative overflow-hidden"
                    :style="{
                        backgroundColor: section.content.backgroundColor || '#1e40af',
                        color: section.content.textColor || '#ffffff',
                        minHeight: section.style?.minHeight ? `${section.style.minHeight}px` : '180px'
                    }"
                >
                    <div v-if="section.content.backgroundImage" class="absolute inset-0">
                        <img :src="section.content.backgroundImage" class="w-full h-full object-cover" />
                        <div class="absolute inset-0 bg-black/40"></div>
                    </div>
                    <div
                        class="relative z-10 h-full flex flex-col justify-center py-10 sm:py-12 px-4 sm:px-6 lg:px-8"
                        :class="{
                            'items-start text-left': section.content.textPosition === 'left',
                            'items-center text-center': !section.content.textPosition || section.content.textPosition === 'center',
                            'items-end text-right': section.content.textPosition === 'right'
                        }"
                        :style="section.content.contentOffset ? `transform: translateY(${section.content.contentOffset}px)` : ''"
                    >
                        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold mb-2">{{ section.content.title }}</h1>
                        <p v-if="section.content.subtitle" class="text-base sm:text-lg opacity-90">{{ section.content.subtitle }}</p>
                    </div>
                </section>

                <!-- Stats Section - Mobile Optimized -->
                <section
                    v-else-if="section.type === 'stats'"
                    class="py-12 sm:py-16 px-4 sm:px-6 lg:px-8 overflow-hidden"
                    :style="{ 
                        backgroundColor: section.style?.backgroundColor || '#2563eb',
                        color: section.style?.textColor || '#ffffff'
                    }"
                >
                    <div 
                        class="max-w-6xl mx-auto"
                        :style="section.content.contentOffset ? `transform: translateY(${section.content.contentOffset}px)` : ''"
                    >
                        <div v-if="section.content.title" :class="{
                            'text-left': section.content.textPosition === 'left',
                            'text-center': !section.content.textPosition || section.content.textPosition === 'center',
                            'text-right': section.content.textPosition === 'right'
                        }">
                            <h2 class="text-2xl sm:text-3xl font-bold mb-8 sm:mb-12">{{ section.content.title }}</h2>
                        </div>
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8 text-center">
                            <div v-for="(stat, idx) in section.content.items" :key="idx" class="p-4">
                                <p class="text-3xl sm:text-4xl lg:text-5xl font-bold mb-2">{{ stat.value }}</p>
                                <p class="text-sm sm:text-base opacity-80">{{ stat.label }}</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Services Section - Multiple Layouts -->
                <section
                    v-else-if="section.type === 'services'"
                    class="py-12 sm:py-16 px-4 sm:px-6 lg:px-8 overflow-hidden"
                    :style="{ backgroundColor: section.style?.backgroundColor }"
                >
                    <div class="max-w-6xl mx-auto">
                        <div :class="{
                            'text-left': section.content.textPosition === 'left',
                            'text-center': !section.content.textPosition || section.content.textPosition === 'center',
                            'text-right': section.content.textPosition === 'right'
                        }">
                            <h2 class="text-2xl sm:text-3xl font-bold mb-3 sm:mb-4">{{ section.content.title }}</h2>
                            <p v-if="section.content.subtitle" class="text-gray-600 mb-8 sm:mb-12 max-w-2xl" :class="{ 'mx-auto': !section.content.textPosition || section.content.textPosition === 'center', 'ml-auto': section.content.textPosition === 'right' }">{{ section.content.subtitle }}</p>
                        </div>
                        
                        <!-- List Layout -->
                        <div v-if="section.content.layout === 'list'" class="space-y-6">
                            <div v-for="(item, idx) in section.content.items" :key="idx" class="flex gap-4 sm:gap-6 items-start p-4 sm:p-6 bg-white rounded-xl shadow-sm border border-gray-100">
                                <div v-if="item.icon" class="flex-shrink-0 w-12 h-12 sm:w-14 sm:h-14 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <span class="text-blue-600 text-xl sm:text-2xl">{{ item.icon === 'chart' ? '📊' : item.icon === 'code' ? '💻' : item.icon === 'sparkles' ? '✨' : item.icon === 'briefcase' ? '💼' : item.icon === 'globe' ? '🌍' : item.icon === 'cog' ? '⚙️' : item.icon === 'users' ? '👥' : item.icon === 'shield' ? '🛡️' : '📦' }}</span>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg sm:text-xl font-semibold mb-2">{{ item.title }}</h3>
                                    <p class="text-gray-600 text-sm sm:text-base">{{ item.description }}</p>
                                    <a v-if="item.link" :href="item.link" class="inline-flex items-center gap-1 mt-3 text-blue-600 hover:text-blue-700 text-sm font-medium">
                                        Learn more <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Cards with Images Layout -->
                        <div v-else-if="section.content.layout === 'cards-images'" :class="`grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-${section.content.columns || 3} gap-6 sm:gap-8`">
                            <div v-for="(item, idx) in section.content.items" :key="idx" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden group">
                                <div v-if="item.image" class="aspect-video bg-gray-100 overflow-hidden">
                                    <img :src="item.image" :alt="item.title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                                </div>
                                <div class="p-5 sm:p-6">
                                    <h3 class="text-lg sm:text-xl font-semibold mb-2 sm:mb-3">{{ item.title }}</h3>
                                    <p class="text-gray-600 text-sm sm:text-base">{{ item.description }}</p>
                                    <a v-if="item.link" :href="item.link" class="inline-flex items-center gap-1 mt-4 text-blue-600 hover:text-blue-700 text-sm font-medium">
                                        Learn more <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Alternating Rows Layout -->
                        <div v-else-if="section.content.layout === 'alternating'" class="space-y-12 sm:space-y-16">
                            <div v-for="(item, idx) in section.content.items" :key="idx" class="flex flex-col lg:flex-row gap-8 items-center" :class="idx % 2 === 1 ? 'lg:flex-row-reverse' : ''">
                                <div class="lg:w-1/2">
                                    <div v-if="item.icon" class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                                        <span class="text-blue-600 text-2xl">{{ item.icon === 'chart' ? '📊' : item.icon === 'code' ? '💻' : item.icon === 'sparkles' ? '✨' : item.icon === 'briefcase' ? '💼' : item.icon === 'globe' ? '🌍' : item.icon === 'cog' ? '⚙️' : item.icon === 'users' ? '👥' : item.icon === 'shield' ? '🛡️' : '📦' }}</span>
                                    </div>
                                    <h3 class="text-xl sm:text-2xl font-semibold mb-3">{{ item.title }}</h3>
                                    <p class="text-gray-600">{{ item.description }}</p>
                                    <a v-if="item.link" :href="item.link" class="inline-flex items-center gap-1 mt-4 text-blue-600 hover:text-blue-700 font-medium">
                                        Learn more <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                    </a>
                                </div>
                                <div class="lg:w-1/2">
                                    <img v-if="item.image" :src="item.image" :alt="item.title" class="rounded-xl shadow-lg w-full" />
                                    <div v-else class="aspect-video bg-gray-100 rounded-xl flex items-center justify-center">
                                        <span class="text-gray-400 text-sm">Service image</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Default Grid Layout -->
                        <div v-else :class="`grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-${section.content.columns || 3} gap-6 sm:gap-8`">
                            <div v-for="(item, idx) in section.content.items" :key="idx" class="bg-white p-5 sm:p-6 rounded-xl shadow-sm border border-gray-100">
                                <div v-if="item.icon" class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                                    <span class="text-blue-600 text-xl">{{ item.icon === 'chart' ? '📊' : item.icon === 'code' ? '💻' : item.icon === 'sparkles' ? '✨' : item.icon === 'briefcase' ? '💼' : item.icon === 'globe' ? '🌍' : item.icon === 'cog' ? '⚙️' : item.icon === 'users' ? '👥' : item.icon === 'shield' ? '🛡️' : '📦' }}</span>
                                </div>
                                <h3 class="text-lg sm:text-xl font-semibold mb-2 sm:mb-3">{{ item.title }}</h3>
                                <p class="text-gray-600 text-sm sm:text-base">{{ item.description }}</p>
                                <a v-if="item.link" :href="item.link" class="inline-flex items-center gap-1 mt-4 text-blue-600 hover:text-blue-700 text-sm font-medium">
                                    Learn more <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Features Section - Multiple Layouts -->
                <section
                    v-else-if="section.type === 'features'"
                    class="py-12 sm:py-16 px-4 sm:px-6 lg:px-8 overflow-hidden"
                    :style="{ backgroundColor: section.style?.backgroundColor }"
                >
                    <div class="max-w-6xl mx-auto">
                        <div :class="{
                            'text-left': section.content.textPosition === 'left',
                            'text-center': !section.content.textPosition || section.content.textPosition === 'center',
                            'text-right': section.content.textPosition === 'right'
                        }">
                            <h2 class="text-2xl sm:text-3xl font-bold mb-3 sm:mb-4">{{ section.content.title }}</h2>
                            <p v-if="section.content.subtitle" class="text-gray-600 mb-8 sm:mb-12 max-w-2xl" :class="{ 'mx-auto': !section.content.textPosition || section.content.textPosition === 'center', 'ml-auto': section.content.textPosition === 'right' }">{{ section.content.subtitle }}</p>
                        </div>
                        
                        <!-- Checklist Layout -->
                        <div v-if="section.content.layout === 'checklist'" class="max-w-3xl mx-auto space-y-4">
                            <div v-for="(item, idx) in section.content.items" :key="idx" class="flex gap-3 items-start p-4 bg-white rounded-lg border border-gray-100">
                                <CheckIcon class="w-6 h-6 text-green-500 flex-shrink-0 mt-0.5" aria-hidden="true" />
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ item.title }}</h3>
                                    <p v-if="item.description" class="text-gray-600 text-sm mt-1">{{ item.description }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Numbered Steps Layout -->
                        <div v-else-if="section.content.layout === 'steps'" class="max-w-4xl mx-auto">
                            <div class="relative">
                                <!-- Connecting Line -->
                                <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-blue-200 hidden sm:block"></div>
                                <div class="space-y-8">
                                    <div v-for="(item, idx) in section.content.items" :key="idx" class="relative flex gap-4 sm:gap-6">
                                        <div class="flex-shrink-0 w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-lg z-10">
                                            {{ idx + 1 }}
                                        </div>
                                        <div class="flex-1 pt-2">
                                            <h3 class="text-lg sm:text-xl font-semibold mb-2">{{ item.title }}</h3>
                                            <p class="text-gray-600">{{ item.description }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Default Grid Layout -->
                        <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-6 sm:gap-8">
                            <div v-for="(item, idx) in section.content.items" :key="idx" class="flex gap-4">
                                <div class="flex-shrink-0 w-10 h-10 sm:w-12 sm:h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <CheckIcon class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" aria-hidden="true" />
                                </div>
                                <div>
                                    <h3 class="text-base sm:text-lg font-semibold mb-1 sm:mb-2">{{ item.title }}</h3>
                                    <p class="text-gray-600 text-sm sm:text-base">{{ item.description }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- About Section - Multiple Layouts -->
                <section
                    v-else-if="section.type === 'about'"
                    class="py-12 sm:py-16 px-4 sm:px-6 lg:px-8 overflow-hidden"
                    :style="{ backgroundColor: section.style?.backgroundColor }"
                >
                    <div class="max-w-6xl mx-auto">
                        <!-- Image Top Layout -->
                        <template v-if="section.content.layout === 'image-top'">
                            <div v-if="section.content.image" class="mb-8 sm:mb-12">
                                <img :src="section.content.image" class="rounded-xl shadow-lg w-full max-h-96 object-cover" :alt="section.content.title" />
                            </div>
                            <div class="max-w-3xl mx-auto" :class="{
                                'text-left': section.content.textPosition === 'left',
                                'text-center': !section.content.textPosition || section.content.textPosition === 'center',
                                'text-right': section.content.textPosition === 'right'
                            }">
                                <h2 class="text-2xl sm:text-3xl font-bold mb-4 sm:mb-6">{{ section.content.title }}</h2>
                                <p class="text-gray-600 leading-relaxed text-sm sm:text-base">{{ section.content.description }}</p>
                            </div>
                        </template>
                        
                        <!-- Image Left/Right Layout -->
                        <template v-else>
                            <div class="flex flex-col gap-8 sm:gap-12 items-center" :class="[
                                section.content.image ? 'lg:flex-row' : '',
                                section.content.layout === 'image-left' ? 'lg:flex-row-reverse' : ''
                            ]">
                                <div :class="[
                                    section.content.image ? 'lg:w-1/2' : 'w-full max-w-3xl',
                                    {
                                        'text-left': section.content.textPosition === 'left',
                                        'text-center': section.content.textPosition === 'center',
                                        'text-right': section.content.textPosition === 'right'
                                    }
                                ]">
                                    <h2 class="text-2xl sm:text-3xl font-bold mb-4 sm:mb-6">{{ section.content.title }}</h2>
                                    <p class="text-gray-600 leading-relaxed text-sm sm:text-base mb-4">{{ section.content.description }}</p>
                                    <ul v-if="section.content.features" class="space-y-2 sm:space-y-3">
                                        <li v-for="(feature, idx) in section.content.features" :key="idx" class="flex items-start gap-3">
                                            <CheckIcon class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" aria-hidden="true" />
                                            <span class="text-gray-700 text-sm sm:text-base">{{ feature }}</span>
                                        </li>
                                    </ul>
                                </div>
                                <div v-if="section.content.image" class="lg:w-1/2 w-full">
                                    <img :src="section.content.image" class="rounded-xl shadow-lg w-full" :alt="section.content.title" />
                                </div>
                            </div>
                        </template>
                    </div>
                </section>

                <!-- Testimonials Section - Multiple Layouts -->
                <section
                    v-else-if="section.type === 'testimonials'"
                    class="py-12 sm:py-16 px-4 sm:px-6 lg:px-8 overflow-hidden"
                    :style="{ backgroundColor: section.style?.backgroundColor || '#f8fafc' }"
                >
                    <div class="max-w-6xl mx-auto">
                        <div :class="{
                            'text-left': section.content.textPosition === 'left',
                            'text-center': !section.content.textPosition || section.content.textPosition === 'center',
                            'text-right': section.content.textPosition === 'right'
                        }">
                            <h2 class="text-2xl sm:text-3xl font-bold mb-3 sm:mb-4">{{ section.content.title }}</h2>
                            <p v-if="section.content.subtitle" class="text-gray-600 mb-8 sm:mb-12 max-w-2xl" :class="{ 'mx-auto': !section.content.textPosition || section.content.textPosition === 'center', 'ml-auto': section.content.textPosition === 'right' }">{{ section.content.subtitle }}</p>
                        </div>
                        
                        <!-- Carousel Layout -->
                        <div v-if="section.content.layout === 'carousel'" class="relative">
                            <div class="overflow-hidden">
                                <div v-for="(item, idx) in section.content.items" :key="idx" :class="idx === (currentTestimonial[section.id] || 0) ? 'block' : 'hidden'">
                                    <div class="max-w-3xl mx-auto text-center">
                                        <div v-if="item.rating" class="flex gap-1 justify-center mb-4">
                                            <StarSolidIcon v-for="n in item.rating" :key="n" class="w-5 h-5 sm:w-6 sm:h-6 text-yellow-400" aria-hidden="true" />
                                        </div>
                                        <p class="text-xl sm:text-2xl text-gray-700 mb-6 italic">"{{ item.text }}"</p>
                                        <div class="flex items-center justify-center gap-4">
                                            <div v-if="item.image" class="w-14 h-14 sm:w-16 sm:h-16 rounded-full overflow-hidden">
                                                <img :src="item.image" class="w-full h-full object-cover" :alt="item.name" />
                                            </div>
                                            <div v-else class="w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-blue-100 flex items-center justify-center">
                                                <span class="text-blue-600 font-bold text-xl">{{ item.name?.charAt(0) }}</span>
                                            </div>
                                            <div class="text-left">
                                                <p class="font-semibold text-lg">{{ item.name }}</p>
                                                <p class="text-gray-500">{{ item.role }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Navigation -->
                            <div class="flex justify-center gap-2 mt-8">
                                <button v-for="(_, idx) in section.content.items" :key="idx" @click="currentTestimonial[section.id] = idx" class="w-3 h-3 rounded-full transition-colors" :class="idx === (currentTestimonial[section.id] || 0) ? 'bg-blue-600' : 'bg-gray-300 hover:bg-gray-400'" :aria-label="`Go to testimonial ${idx + 1}`"></button>
                            </div>
                            <button @click="prevTestimonial(section.id, section.content.items.length)" class="absolute left-0 top-1/2 -translate-y-1/2 w-10 h-10 bg-white shadow-md rounded-full flex items-center justify-center hover:bg-gray-50" aria-label="Previous testimonial">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                            </button>
                            <button @click="nextTestimonial(section.id, section.content.items.length)" class="absolute right-0 top-1/2 -translate-y-1/2 w-10 h-10 bg-white shadow-md rounded-full flex items-center justify-center hover:bg-gray-50" aria-label="Next testimonial">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </button>
                        </div>
                        
                        <!-- Single Large Quote Layout -->
                        <div v-else-if="section.content.layout === 'single'" class="max-w-4xl mx-auto text-center">
                            <div v-if="section.content.items?.[0]" class="relative">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 text-blue-200 mx-auto mb-6" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
                                <p class="text-xl sm:text-2xl lg:text-3xl text-gray-700 mb-8 italic leading-relaxed">"{{ section.content.items[0].text }}"</p>
                                <div class="flex items-center justify-center gap-4">
                                    <div v-if="section.content.items[0].image" class="w-16 h-16 sm:w-20 sm:h-20 rounded-full overflow-hidden">
                                        <img :src="section.content.items[0].image" class="w-full h-full object-cover" :alt="section.content.items[0].name" />
                                    </div>
                                    <div class="text-left">
                                        <p class="font-bold text-lg sm:text-xl">{{ section.content.items[0].name }}</p>
                                        <p class="text-gray-500">{{ section.content.items[0].role }}</p>
                                        <div v-if="section.content.items[0].rating" class="flex gap-1 mt-1">
                                            <StarSolidIcon v-for="n in section.content.items[0].rating" :key="n" class="w-4 h-4 text-yellow-400" aria-hidden="true" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Photos Layout (larger images) -->
                        <div v-else-if="section.content.layout === 'photos'" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div v-for="(item, idx) in section.content.items" :key="idx" class="bg-white p-6 sm:p-8 rounded-xl shadow-sm border border-gray-100">
                                <div class="flex items-start gap-4 mb-4">
                                    <div v-if="item.image" class="w-16 h-16 sm:w-20 sm:h-20 rounded-full overflow-hidden flex-shrink-0">
                                        <img :src="item.image" class="w-full h-full object-cover" :alt="item.name" />
                                    </div>
                                    <div v-else class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                        <span class="text-blue-600 font-bold text-2xl">{{ item.name?.charAt(0) }}</span>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-lg">{{ item.name }}</p>
                                        <p class="text-gray-500 text-sm">{{ item.role }}</p>
                                        <div v-if="item.rating" class="flex gap-1 mt-1">
                                            <StarSolidIcon v-for="n in item.rating" :key="n" class="w-4 h-4 text-yellow-400" aria-hidden="true" />
                                        </div>
                                    </div>
                                </div>
                                <p class="text-gray-700 italic">"{{ item.text }}"</p>
                            </div>
                        </div>
                        
                        <!-- Default Grid Layout -->
                        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                            <div v-for="(item, idx) in section.content.items" :key="idx" class="bg-white p-5 sm:p-6 rounded-xl shadow-sm border border-gray-100">
                                <div v-if="item.rating" class="flex gap-1 mb-3 sm:mb-4">
                                    <StarSolidIcon v-for="n in item.rating" :key="n" class="w-4 h-4 sm:w-5 sm:h-5 text-yellow-400" aria-hidden="true" />
                                </div>
                                <p class="text-gray-700 mb-4 sm:mb-6 text-sm sm:text-base italic">"{{ item.text }}"</p>
                                <div class="flex items-center gap-3">
                                    <div v-if="item.image" class="w-10 h-10 sm:w-12 sm:h-12 rounded-full overflow-hidden bg-gray-200">
                                        <img :src="item.image" class="w-full h-full object-cover" :alt="item.name" />
                                    </div>
                                    <div v-else class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-blue-100 flex items-center justify-center">
                                        <span class="text-blue-600 font-semibold text-sm sm:text-base">{{ item.name?.charAt(0) }}</span>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-sm sm:text-base">{{ item.name }}</p>
                                        <p class="text-gray-500 text-xs sm:text-sm">{{ item.role }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Pricing Section - Mobile Optimized -->
                <section
                    v-else-if="section.type === 'pricing'"
                    class="py-12 sm:py-16 px-4 sm:px-6 lg:px-8 overflow-hidden"
                    :style="{ backgroundColor: section.style?.backgroundColor }"
                >
                    <div 
                        class="max-w-6xl mx-auto"
                        :style="section.content.contentOffset ? `transform: translateY(${section.content.contentOffset}px)` : ''"
                    >
                        <div :class="{
                            'text-left': section.content.textPosition === 'left',
                            'text-center': !section.content.textPosition || section.content.textPosition === 'center',
                            'text-right': section.content.textPosition === 'right'
                        }">
                            <h2 class="text-2xl sm:text-3xl font-bold mb-3 sm:mb-4">{{ section.content.title }}</h2>
                            <p v-if="section.content.subtitle" class="text-gray-600 mb-8 sm:mb-12 max-w-2xl" :class="{ 'mx-auto': !section.content.textPosition || section.content.textPosition === 'center', 'ml-auto': section.content.textPosition === 'right' }">{{ section.content.subtitle }}</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                            <div 
                                v-for="(plan, idx) in section.content.plans" 
                                :key="idx" 
                                class="bg-white p-5 sm:p-6 rounded-xl shadow-sm border-2 transition-all"
                                :class="plan.popular ? 'border-blue-500 ring-2 ring-blue-100' : 'border-gray-100'"
                            >
                                <div v-if="plan.popular" class="text-center mb-4">
                                    <span class="bg-blue-100 text-blue-600 text-xs font-semibold px-3 py-1 rounded-full">Most Popular</span>
                                </div>
                                <h3 class="text-lg sm:text-xl font-semibold text-center mb-2">{{ plan.name }}</h3>
                                <p class="text-3xl sm:text-4xl font-bold text-center mb-4 sm:mb-6">{{ plan.price }}</p>
                                <ul class="space-y-3 mb-6">
                                    <li v-for="(feature, fIdx) in plan.features" :key="fIdx" class="flex items-start gap-2">
                                        <CheckIcon class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" aria-hidden="true" />
                                        <span class="text-gray-600 text-sm sm:text-base">{{ feature }}</span>
                                    </li>
                                </ul>
                                <button 
                                    class="w-full py-3 rounded-lg font-semibold transition-colors text-sm sm:text-base"
                                    :class="plan.popular ? 'bg-blue-600 text-white hover:bg-blue-700' : 'bg-gray-100 text-gray-900 hover:bg-gray-200'"
                                >
                                    {{ plan.buttonText || 'Get Started' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Team Section - Multiple Layouts -->
                <section
                    v-else-if="section.type === 'team'"
                    class="py-12 sm:py-16 px-4 sm:px-6 lg:px-8 overflow-hidden"
                    :style="{ backgroundColor: section.style?.backgroundColor }"
                >
                    <div class="max-w-6xl mx-auto">
                        <div :class="{
                            'text-left': section.content.textPosition === 'left',
                            'text-center': !section.content.textPosition || section.content.textPosition === 'center',
                            'text-right': section.content.textPosition === 'right'
                        }">
                            <h2 class="text-2xl sm:text-3xl font-bold mb-8 sm:mb-12">{{ section.content.title }}</h2>
                        </div>
                        
                        <!-- Social Links Layout -->
                        <div v-if="section.content.layout === 'social'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8">
                            <div v-for="(member, idx) in section.content.items" :key="idx" class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 text-center">
                                <div class="w-24 h-24 mx-auto mb-4 rounded-full overflow-hidden bg-gray-200">
                                    <img v-if="member.image" :src="member.image" class="w-full h-full object-cover" :alt="member.name" />
                                    <div v-else class="w-full h-full flex items-center justify-center bg-blue-100">
                                        <span class="text-blue-600 font-bold text-2xl">{{ member.name?.charAt(0) }}</span>
                                    </div>
                                </div>
                                <h3 class="font-semibold text-lg">{{ member.name }}</h3>
                                <p class="text-gray-500 text-sm mb-3">{{ member.role }}</p>
                                <p v-if="member.bio" class="text-gray-600 text-sm mb-4">{{ member.bio }}</p>
                                <div class="flex justify-center gap-3">
                                    <a v-if="member.linkedin" :href="member.linkedin" target="_blank" class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 hover:bg-blue-200 transition-colors" aria-label="LinkedIn">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                                    </a>
                                    <a v-if="member.twitter" :href="member.twitter" target="_blank" class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 hover:bg-blue-200 transition-colors" aria-label="Twitter">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                                    </a>
                                    <a v-if="member.email" :href="`mailto:${member.email}`" class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 hover:bg-blue-200 transition-colors" aria-label="Email">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Compact List Layout -->
                        <div v-else-if="section.content.layout === 'compact'" class="max-w-4xl mx-auto space-y-4">
                            <div v-for="(member, idx) in section.content.items" :key="idx" class="flex items-center gap-4 p-4 bg-white rounded-lg border border-gray-100">
                                <div class="w-14 h-14 rounded-full overflow-hidden bg-gray-200 flex-shrink-0">
                                    <img v-if="member.image" :src="member.image" class="w-full h-full object-cover" :alt="member.name" />
                                    <div v-else class="w-full h-full flex items-center justify-center bg-blue-100">
                                        <span class="text-blue-600 font-bold">{{ member.name?.charAt(0) }}</span>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold">{{ member.name }}</h3>
                                    <p class="text-gray-500 text-sm">{{ member.role }}</p>
                                </div>
                                <div v-if="member.linkedin || member.email" class="flex gap-2">
                                    <a v-if="member.linkedin" :href="member.linkedin" target="_blank" class="text-gray-400 hover:text-blue-600" aria-label="LinkedIn">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                                    </a>
                                    <a v-if="member.email" :href="`mailto:${member.email}`" class="text-gray-400 hover:text-blue-600" aria-label="Email">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Default Grid Layout -->
                        <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6 sm:gap-8">
                            <div v-for="(member, idx) in section.content.items" :key="idx" class="text-center">
                                <div class="w-20 h-20 sm:w-28 sm:h-28 lg:w-32 lg:h-32 mx-auto mb-3 sm:mb-4 rounded-full overflow-hidden bg-gray-200">
                                    <img v-if="member.image" :src="member.image" class="w-full h-full object-cover" :alt="member.name" />
                                    <div v-else class="w-full h-full flex items-center justify-center bg-blue-100">
                                        <span class="text-blue-600 font-bold text-2xl sm:text-3xl">{{ member.name?.charAt(0) }}</span>
                                    </div>
                                </div>
                                <h3 class="font-semibold text-sm sm:text-base lg:text-lg">{{ member.name }}</h3>
                                <p class="text-gray-500 text-xs sm:text-sm">{{ member.role }}</p>
                                <p v-if="member.bio" class="text-xs sm:text-sm text-gray-600 mt-2 hidden sm:block">{{ member.bio }}</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- FAQ Section - Multiple Layouts -->
                <section
                    v-else-if="section.type === 'faq'"
                    class="py-12 sm:py-16 px-4 sm:px-6 lg:px-8 overflow-hidden"
                    :style="{ backgroundColor: section.style?.backgroundColor }"
                >
                    <div class="max-w-6xl mx-auto">
                        <div :class="{
                            'text-left': section.content.textPosition === 'left',
                            'text-center': !section.content.textPosition || section.content.textPosition === 'center',
                            'text-right': section.content.textPosition === 'right'
                        }">
                            <h2 class="text-2xl sm:text-3xl font-bold mb-8 sm:mb-12">{{ section.content.title }}</h2>
                        </div>
                        
                        <!-- Two Column Layout -->
                        <div v-if="section.content.layout === 'two-column'" class="grid md:grid-cols-2 gap-6 sm:gap-8">
                            <div v-for="(item, idx) in section.content.items" :key="idx" class="bg-white p-5 sm:p-6 rounded-xl border border-gray-100">
                                <h3 class="font-semibold text-gray-900 mb-3">{{ item.question }}</h3>
                                <p class="text-gray-600 text-sm sm:text-base">{{ item.answer }}</p>
                            </div>
                        </div>
                        
                        <!-- Simple List Layout -->
                        <div v-else-if="section.content.layout === 'list'" class="max-w-3xl mx-auto space-y-6">
                            <div v-for="(item, idx) in section.content.items" :key="idx" class="border-b border-gray-200 pb-6 last:border-0">
                                <h3 class="font-semibold text-gray-900 mb-2 text-lg">{{ item.question }}</h3>
                                <p class="text-gray-600">{{ item.answer }}</p>
                            </div>
                        </div>
                        
                        <!-- Default Accordion Layout -->
                        <div v-else class="max-w-3xl mx-auto space-y-3 sm:space-y-4">
                            <details v-for="(item, idx) in section.content.items" :key="idx" class="border border-gray-200 rounded-lg group">
                                <summary class="p-4 sm:p-5 cursor-pointer font-semibold text-sm sm:text-base hover:bg-gray-50 list-none flex justify-between items-center">
                                    {{ item.question }}
                                    <span class="text-gray-400 group-open:rotate-180 transition-transform">▼</span>
                                </summary>
                                <p class="px-4 sm:px-5 pb-4 sm:pb-5 text-gray-600 text-sm sm:text-base">{{ item.answer }}</p>
                            </details>
                        </div>
                    </div>
                </section>

                <!-- Gallery Section - Mobile Optimized -->
                <section
                    v-else-if="section.type === 'gallery'"
                    class="py-12 sm:py-16 px-4 sm:px-6 lg:px-8 overflow-hidden"
                    :style="{ backgroundColor: section.style?.backgroundColor }"
                >
                    <div 
                        class="max-w-6xl mx-auto"
                        :style="section.content.contentOffset ? `transform: translateY(${section.content.contentOffset}px)` : ''"
                    >
                        <div :class="{
                            'text-left': section.content.textPosition === 'left',
                            'text-center': !section.content.textPosition || section.content.textPosition === 'center',
                            'text-right': section.content.textPosition === 'right'
                        }">
                            <h2 class="text-2xl sm:text-3xl font-bold mb-8 sm:mb-12">{{ section.content.title }}</h2>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
                            <div 
                                v-for="(image, idx) in section.content.images" 
                                :key="idx" 
                                class="aspect-square rounded-lg overflow-hidden bg-gray-100"
                            >
                                <img :src="image.url || image" class="w-full h-full object-cover hover:scale-105 transition-transform" :alt="image.alt || `Gallery image ${idx + 1}`" />
                            </div>
                            <div v-if="!section.content.images?.length" class="col-span-full text-center py-12 text-gray-400">
                                No images added yet
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Blog Section - Mobile Optimized -->
                <section
                    v-else-if="section.type === 'blog'"
                    class="py-12 sm:py-16 px-4 sm:px-6 lg:px-8 overflow-hidden"
                    :style="{ backgroundColor: section.style?.backgroundColor }"
                >
                    <div 
                        class="max-w-6xl mx-auto"
                        :style="section.content.contentOffset ? `transform: translateY(${section.content.contentOffset}px)` : ''"
                    >
                        <div :class="{
                            'text-left': section.content.textPosition === 'left',
                            'text-center': !section.content.textPosition || section.content.textPosition === 'center',
                            'text-right': section.content.textPosition === 'right'
                        }">
                            <h2 class="text-2xl sm:text-3xl font-bold mb-8 sm:mb-12">{{ section.content.title }}</h2>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                            <article v-for="(post, idx) in section.content.posts" :key="idx" class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                                <div class="aspect-video bg-gray-100">
                                    <img v-if="post.image" :src="post.image" class="w-full h-full object-cover" :alt="post.title" />
                                    <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                                        <span class="text-4xl">📰</span>
                                    </div>
                                </div>
                                <div class="p-4 sm:p-6">
                                    <p class="text-xs sm:text-sm text-gray-500 mb-2">{{ post.date }}</p>
                                    <h3 class="font-semibold text-base sm:text-lg mb-2 line-clamp-2">{{ post.title }}</h3>
                                    <p class="text-gray-600 text-sm line-clamp-3">{{ post.excerpt }}</p>
                                </div>
                            </article>
                        </div>
                    </div>
                </section>

                <!-- Contact Section - Mobile Optimized -->
                <section
                    v-else-if="section.type === 'contact'"
                    class="py-12 sm:py-16 px-4 sm:px-6 lg:px-8 overflow-hidden"
                    :style="{ backgroundColor: section.style?.backgroundColor || '#f9fafb' }"
                >
                    <div 
                        class="max-w-2xl mx-auto"
                        :style="section.content.contentOffset ? `transform: translateY(${section.content.contentOffset}px)` : ''"
                    >
                        <div :class="{
                            'text-left': section.content.textPosition === 'left',
                            'text-center': !section.content.textPosition || section.content.textPosition === 'center',
                            'text-right': section.content.textPosition === 'right'
                        }">
                            <h2 class="text-2xl sm:text-3xl font-bold mb-3 sm:mb-4">{{ section.content.title }}</h2>
                            <p class="text-gray-600 mb-6 sm:mb-8 text-sm sm:text-base">{{ section.content.description }}</p>
                        </div>
                        
                        <!-- Contact Info -->
                        <div v-if="section.content.email || section.content.phone || section.content.address" class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6 sm:mb-8">
                            <div v-if="section.content.email" class="text-center p-4 bg-white rounded-lg">
                                <span class="text-2xl mb-2 block">✉️</span>
                                <p class="text-sm text-gray-600">{{ section.content.email }}</p>
                            </div>
                            <div v-if="section.content.phone" class="text-center p-4 bg-white rounded-lg">
                                <span class="text-2xl mb-2 block">📞</span>
                                <p class="text-sm text-gray-600">{{ section.content.phone }}</p>
                            </div>
                            <div v-if="section.content.address" class="text-center p-4 bg-white rounded-lg">
                                <span class="text-2xl mb-2 block">📍</span>
                                <p class="text-sm text-gray-600">{{ section.content.address }}</p>
                            </div>
                        </div>

                        <!-- Contact Form -->
                        <form v-if="section.content.showForm" class="space-y-4 bg-white p-5 sm:p-6 rounded-xl shadow-sm">
                            <input type="text" placeholder="Your Name" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm sm:text-base" />
                            <input type="email" placeholder="Your Email" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm sm:text-base" />
                            <textarea placeholder="Your Message" rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm sm:text-base resize-none"></textarea>
                            <button type="submit" class="w-full py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors text-sm sm:text-base">
                                Send Message
                            </button>
                        </form>
                    </div>
                </section>

                <!-- CTA Section - Mobile Optimized with Offset Support -->
                <section
                    v-else-if="section.type === 'cta'"
                    class="relative overflow-hidden"
                    :style="{ 
                        backgroundColor: section.style?.backgroundColor || '#2563eb'
                    }"
                >
                    <!-- Standard Layout with Offset Support -->
                    <div 
                        class="py-12 sm:py-16 px-4 sm:px-6 lg:px-8"
                        :class="{
                            'text-left': section.content.textPosition === 'left',
                            'text-center': !section.content.textPosition || section.content.textPosition === 'center',
                            'text-right': section.content.textPosition === 'right'
                        }"
                    >
                        <div 
                            class="max-w-3xl"
                            :class="{
                                '': section.content.textPosition === 'left',
                                'mx-auto': !section.content.textPosition || section.content.textPosition === 'center',
                                'ml-auto': section.content.textPosition === 'right'
                            }"
                        >
                            <h2 class="text-2xl sm:text-3xl font-bold text-white mb-3 sm:mb-4">
                                {{ section.content.title }}
                            </h2>
                            <p class="text-white/90 mb-6 sm:mb-8 text-sm sm:text-base">
                                {{ section.content.description }}
                            </p>
                            <a 
                                :href="section.content.buttonLink || '#'" 
                                class="inline-block px-6 sm:px-8 py-3 bg-white text-blue-600 font-semibold rounded-lg hover:bg-gray-100 transition-colors text-sm sm:text-base"
                            >
                                {{ section.content.buttonText || 'Contact Us' }}
                            </a>
                        </div>
                    </div>
                </section>

                <!-- Member CTA Section - Membership Signup Promotion -->
                <section
                    v-else-if="section.type === 'member-cta'"
                    class="relative overflow-hidden"
                    :style="{ 
                        backgroundColor: section.style?.backgroundColor || section.content.backgroundColor || '#1e40af',
                        color: section.content.textColor || '#ffffff'
                    }"
                >
                    <div class="py-12 sm:py-16 px-4 sm:px-6 lg:px-8">
                        <div class="max-w-4xl mx-auto text-center">
                            <!-- Icon -->
                            <div class="w-16 h-16 mx-auto mb-6 bg-white/20 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                            </div>
                            
                            <!-- Title -->
                            <h2 class="text-2xl sm:text-3xl font-bold mb-3">{{ section.content.title || 'Join Our Community' }}</h2>
                            
                            <!-- Subtitle -->
                            <p v-if="section.content.subtitle" class="text-lg sm:text-xl opacity-90 mb-4">{{ section.content.subtitle }}</p>
                            
                            <!-- Description -->
                            <p v-if="section.content.description" class="text-base sm:text-lg opacity-80 mb-8 max-w-2xl mx-auto">
                                {{ section.content.description }}
                            </p>
                            
                            <!-- Benefits -->
                            <div v-if="section.content.benefits?.length" class="flex flex-wrap justify-center gap-3 sm:gap-4 mb-8">
                                <div 
                                    v-for="(benefit, idx) in section.content.benefits" 
                                    :key="idx"
                                    class="flex items-center gap-2 bg-white/10 px-3 sm:px-4 py-2 rounded-full"
                                >
                                    <CheckIcon class="w-4 h-4 flex-shrink-0" aria-hidden="true" />
                                    <span class="text-xs sm:text-sm">{{ benefit }}</span>
                                </div>
                            </div>
                            
                            <!-- Buttons -->
                            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                                <!-- Register Button -->
                                <a 
                                    href="/register"
                                    :class="[
                                        'px-6 sm:px-8 py-3 font-semibold rounded-lg transition-colors text-sm sm:text-base',
                                        section.content.registerButtonStyle === 'outline' 
                                            ? 'border-2 border-white hover:bg-white/10' 
                                            : 'bg-white text-blue-600 hover:bg-gray-100'
                                    ]"
                                >
                                    {{ section.content.registerText || 'Sign Up Now' }}
                                </a>
                                
                                <!-- Login Link -->
                                <a 
                                    v-if="section.content.showLoginLink !== false"
                                    href="/login"
                                    class="text-sm opacity-80 hover:opacity-100 underline"
                                >
                                    {{ section.content.loginText || 'Already a member? Login' }}
                                </a>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Text Section - Mobile Optimized -->
                <section
                    v-else-if="section.type === 'text'"
                    class="py-10 sm:py-12 px-4 sm:px-6 lg:px-8 overflow-hidden"
                    :style="{ backgroundColor: section.style?.backgroundColor }"
                >
                    <div 
                        class="max-w-4xl mx-auto prose prose-sm sm:prose-base lg:prose-lg" 
                        v-html="section.content.content"
                        :style="section.content.contentOffset ? `transform: translateY(${section.content.contentOffset}px)` : ''"
                    ></div>
                </section>

                <!-- Map Section - Mobile Optimized -->
                <section
                    v-else-if="section.type === 'map'"
                    class="py-12 sm:py-16 px-4 sm:px-6 lg:px-8 overflow-hidden"
                    :style="{ backgroundColor: section.style?.backgroundColor }"
                >
                    <div 
                        class="max-w-4xl mx-auto"
                        :style="section.content.contentOffset ? `transform: translateY(${section.content.contentOffset}px)` : ''"
                    >
                        <h2 v-if="section.content.title" class="text-2xl sm:text-3xl font-bold text-center mb-6 sm:mb-8">{{ section.content.title }}</h2>
                        <div class="aspect-video sm:aspect-[16/9] rounded-xl overflow-hidden bg-gray-100">
                            <iframe v-if="section.content.embedUrl" :src="section.content.embedUrl" class="w-full h-full" frameborder="0" allowfullscreen></iframe>
                            <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                                <span class="text-4xl">🗺️</span>
                            </div>
                        </div>
                        <p v-if="section.content.showAddress && section.content.address" class="text-center mt-4 text-gray-600 text-sm sm:text-base">
                            📍 {{ section.content.address }}
                        </p>
                    </div>
                </section>

                <!-- Video Section - Mobile Optimized -->
                <section
                    v-else-if="section.type === 'video'"
                    class="py-12 sm:py-16 px-4 sm:px-6 lg:px-8 overflow-hidden"
                    :style="{ backgroundColor: section.style?.backgroundColor }"
                >
                    <div 
                        class="max-w-4xl mx-auto text-center"
                        :style="section.content.contentOffset ? `transform: translateY(${section.content.contentOffset}px)` : ''"
                    >
                        <h2 v-if="section.content.title" class="text-2xl sm:text-3xl font-bold mb-3 sm:mb-4">{{ section.content.title }}</h2>
                        <p v-if="section.content.description" class="text-gray-600 mb-6 sm:mb-8 text-sm sm:text-base">{{ section.content.description }}</p>
                        <div class="aspect-video rounded-xl overflow-hidden bg-gray-900">
                            <iframe v-if="section.content.videoUrl" :src="section.content.videoUrl" class="w-full h-full" frameborder="0" allowfullscreen></iframe>
                            <div v-else class="w-full h-full flex items-center justify-center text-gray-500">
                                <span class="text-5xl">▶️</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Divider Section -->
                <section
                    v-else-if="section.type === 'divider'"
                    class="flex items-center justify-center px-4"
                    :style="{ height: `${section.content.height || 40}px` }"
                >
                    <div v-if="section.content.style === 'line'" class="w-full max-w-4xl border-t" :style="{ borderColor: section.content.color || '#e5e7eb' }"></div>
                    <div v-else-if="section.content.style === 'dots'" class="flex gap-2">
                        <span v-for="n in 3" :key="n" class="w-2 h-2 rounded-full" :style="{ backgroundColor: section.content.color || '#e5e7eb' }"></span>
                    </div>
                </section>

                <!-- Product Search Section -->
                <section
                    v-else-if="section.type === 'product-search'"
                    class="py-6 sm:py-8 px-4 sm:px-6 lg:px-8"
                    :style="{ backgroundColor: section.style?.backgroundColor }"
                >
                    <div class="max-w-6xl mx-auto">
                        <div class="flex flex-col sm:flex-row gap-4 items-stretch sm:items-center">
                            <!-- Search Input -->
                            <div class="flex-1 relative">
                                <input
                                    v-model="productSearch"
                                    type="text"
                                    :placeholder="section.content.placeholder || 'Search products...'"
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                />
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            
                            <!-- Category Filter -->
                            <select
                                v-if="section.content.showCategories && productCategories.length > 0"
                                v-model="selectedCategory"
                                class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm bg-white"
                            >
                                <option value="">All Categories</option>
                                <option v-for="cat in productCategories" :key="cat" :value="cat">{{ cat }}</option>
                            </select>
                            
                            <!-- Sort Options -->
                            <select
                                v-if="section.content.showSort"
                                v-model="productSort"
                                class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm bg-white"
                            >
                                <option value="default">Sort by</option>
                                <option value="price-asc">Price: Low to High</option>
                                <option value="price-desc">Price: High to Low</option>
                                <option value="name-asc">Name: A-Z</option>
                                <option value="name-desc">Name: Z-A</option>
                            </select>
                        </div>
                    </div>
                </section>

                <!-- Products Section - Mobile Optimized -->
                <section
                    v-else-if="section.type === 'products'"
                    class="py-12 sm:py-16 px-4 sm:px-6 lg:px-8 overflow-hidden"
                    :style="{ backgroundColor: section.style?.backgroundColor }"
                >
                    <div 
                        class="max-w-6xl mx-auto"
                        :style="section.content.contentOffset ? `transform: translateY(${section.content.contentOffset}px)` : ''"
                    >
                        <div :class="{
                            'text-left': section.content.textPosition === 'left',
                            'text-center': !section.content.textPosition || section.content.textPosition === 'center',
                            'text-right': section.content.textPosition === 'right'
                        }">
                            <h2 class="text-2xl sm:text-3xl font-bold mb-3 sm:mb-4">{{ section.content.title || 'Our Products' }}</h2>
                            <p v-if="section.content.subtitle" class="text-gray-600 mb-8 sm:mb-12 max-w-2xl" :class="{ 'mx-auto': !section.content.textPosition || section.content.textPosition === 'center', 'ml-auto': section.content.textPosition === 'right' }">{{ section.content.subtitle }}</p>
                        </div>
                        
                        <!-- Real Products Grid -->
                        <div v-if="filteredProducts.length > 0" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                            <div 
                                v-for="product in filteredProducts" 
                                :key="product.id" 
                                class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-100 group hover:shadow-md transition-shadow"
                            >
                                <!-- Product Image -->
                                <a :href="getProductUrl(product)" class="block relative aspect-square bg-gray-100 overflow-hidden">
                                    <img 
                                        v-if="product.image" 
                                        :src="product.image" 
                                        :alt="product.name"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                    />
                                    <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                                        <ShoppingBagIcon class="w-12 h-12" aria-hidden="true" />
                                    </div>
                                    
                                    <!-- Discount Badge -->
                                    <span 
                                        v-if="product.hasDiscount" 
                                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded"
                                    >
                                        -{{ product.discountPercentage }}%
                                    </span>
                                    
                                    <!-- Featured Badge -->
                                    <span 
                                        v-if="product.isFeatured && !product.hasDiscount" 
                                        class="absolute top-2 left-2 bg-blue-500 text-white text-xs font-semibold px-2 py-1 rounded"
                                    >
                                        Featured
                                    </span>
                                    
                                    <!-- Out of Stock Overlay -->
                                    <div 
                                        v-if="!product.inStock" 
                                        class="absolute inset-0 bg-black/50 flex items-center justify-center"
                                    >
                                        <span class="bg-white text-gray-900 text-xs font-semibold px-3 py-1 rounded">Out of Stock</span>
                                    </div>
                                </a>
                                
                                <!-- Product Info -->
                                <div class="p-3 sm:p-4">
                                    <a :href="getProductUrl(product)" class="block">
                                        <h3 class="font-semibold text-sm sm:text-base mb-1 line-clamp-2 hover:text-blue-600 transition-colors">{{ product.name }}</h3>
                                    </a>
                                    <p v-if="product.category" class="text-xs text-gray-500 mb-2">{{ product.category }}</p>
                                    
                                    <!-- Price -->
                                    <div class="flex items-center gap-2 mb-3">
                                        <span class="text-blue-600 font-bold text-sm sm:text-base">{{ product.priceFormatted }}</span>
                                        <span v-if="product.comparePriceFormatted" class="text-gray-400 text-xs line-through">{{ product.comparePriceFormatted }}</span>
                                    </div>
                                    
                                    <!-- Add to Cart Button -->
                                    <button 
                                        v-if="product.inStock"
                                        @click="addToCart(product)"
                                        class="w-full py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center gap-2"
                                    >
                                        <ShoppingCartIcon class="w-4 h-4" aria-hidden="true" />
                                        Add to Cart
                                    </button>
                                    <button 
                                        v-else
                                        disabled
                                        class="w-full py-2 bg-gray-200 text-gray-500 text-sm font-medium rounded-lg cursor-not-allowed"
                                    >
                                        Out of Stock
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Empty State -->
                        <div v-else class="text-center py-12">
                            <ShoppingBagIcon class="w-16 h-16 mx-auto text-gray-300 mb-4" aria-hidden="true" />
                            <p v-if="displayProducts.length > 0 && filteredProducts.length === 0" class="text-gray-500">
                                No products match your search
                                <button @click="productSearch = ''; selectedCategory = ''; productSort = 'default'" class="block mx-auto mt-2 text-blue-600 hover:text-blue-700 text-sm">
                                    Clear filters
                                </button>
                            </p>
                            <p v-else class="text-gray-500">No products available yet</p>
                        </div>
                        
                        <!-- View All Link -->
                        <div v-if="section.content.showViewAll && filteredProducts.length > 0" class="text-center mt-8">
                            <a 
                                :href="`/sites/${site.subdomain}/shop`"
                                class="inline-flex items-center gap-2 text-blue-600 font-medium hover:text-blue-700 transition-colors"
                            >
                                View All Products
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </section>

                <!-- Default/Unknown Section -->
                <section v-else class="py-10 sm:py-12 px-4 sm:px-6 lg:px-8 text-center text-gray-500">
                    <p class="text-sm sm:text-base">{{ section.type }} section</p>
                </section>
            </template>
        </main>

        <!-- Footer - Mobile Optimized -->
        <footer
            class="py-10 sm:py-12 px-4 sm:px-6 lg:px-8"
            :style="{ backgroundColor: footer.backgroundColor || '#1f2937', color: footer.textColor || '#ffffff' }"
        >
            <div class="max-w-6xl mx-auto">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 mb-8">
                    <!-- Brand Column -->
                    <div class="sm:col-span-2 lg:col-span-1">
                        <img v-if="footer.logo" :src="footer.logo" class="h-10 mb-4" :alt="site.name" />
                        <h3 v-else class="text-lg font-bold mb-4">{{ site.name }}</h3>
                        <p class="text-sm opacity-80">Your trusted partner for growth and success.</p>
                    </div>
                    
                    <!-- Footer Columns -->
                    <div v-for="column in footer.columns" :key="column.id">
                        <h4 class="font-semibold mb-3 text-sm sm:text-base">{{ column.title }}</h4>
                        <ul class="space-y-2">
                            <li v-for="link in column.links" :key="link.id">
                                <a :href="link.url" class="text-sm opacity-80 hover:opacity-100 transition-opacity">{{ link.label }}</a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Social Links -->
                <div v-if="footer.socialLinks?.length" class="flex justify-center sm:justify-start gap-4 mb-6">
                    <a 
                        v-for="social in footer.socialLinks" 
                        :key="social.id" 
                        :href="social.url"
                        class="w-10 h-10 bg-white/10 rounded-full flex items-center justify-center hover:bg-white/20 transition-colors"
                    >
                        <span class="text-sm uppercase">{{ social.platform?.charAt(0) }}</span>
                    </a>
                </div>

                <!-- Copyright -->
                <div class="pt-6 sm:pt-8 border-t border-white/20 text-center">
                    <p class="text-xs sm:text-sm opacity-70">{{ footer.copyrightText || `© ${new Date().getFullYear()} ${site.name}. All rights reserved.` }}</p>
                </div>
            </div>
        </footer>

        <!-- Powered by MyGrowNet Watermark (Free Tier) -->
        <div 
            v-if="showWatermark"
            class="fixed bottom-4 right-4 z-40"
        >
            <a 
                href="https://mygrownet.com" 
                target="_blank"
                rel="noopener"
                class="flex items-center gap-2 px-3 py-2 bg-white/95 backdrop-blur-sm rounded-lg shadow-lg border border-gray-200 hover:shadow-xl transition-shadow group"
            >
                <img src="/logo.png" alt="MyGrowNet" class="h-5 w-auto" />
                <span class="text-xs text-gray-600 group-hover:text-gray-900 transition-colors">
                    Powered by <span class="font-semibold text-blue-600">MyGrowNet</span>
                </span>
            </a>
        </div>

        <!-- Cart Notification Toast -->
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="translate-y-2 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-2 opacity-0"
        >
            <div 
                v-if="cartNotification"
                class="fixed bottom-4 left-1/2 -translate-x-1/2 bg-gray-900 text-white px-4 py-3 rounded-lg shadow-lg flex items-center gap-2 z-50"
            >
                <CheckIcon class="w-5 h-5 text-green-400" aria-hidden="true" />
                {{ cartNotification }}
            </div>
        </Transition>

        <!-- Cart Sidebar -->
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="cartOpen" class="fixed inset-0 bg-black/50 z-50" @click="cartOpen = false"></div>
        </Transition>
        
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="translate-x-0"
            leave-to-class="translate-x-full"
        >
            <div 
                v-if="cartOpen"
                class="fixed right-0 top-0 h-full w-full max-w-md bg-white shadow-xl z-50 flex flex-col"
            >
                <!-- Cart Header -->
                <div class="flex items-center justify-between p-4 border-b">
                    <h2 class="text-lg font-semibold">Shopping Cart</h2>
                    <button 
                        @click="cartOpen = false"
                        class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                        aria-label="Close cart"
                    >
                        <XMarkIcon class="w-5 h-5" aria-hidden="true" />
                    </button>
                </div>

                <!-- Cart Items -->
                <div class="flex-1 overflow-y-auto p-4">
                    <div v-if="cart.length === 0" class="text-center py-12">
                        <ShoppingBagIcon class="w-16 h-16 mx-auto text-gray-300 mb-4" aria-hidden="true" />
                        <p class="text-gray-500 mb-4">Your cart is empty</p>
                        <button 
                            @click="cartOpen = false"
                            class="text-blue-600 font-medium hover:text-blue-700"
                        >
                            Continue Shopping
                        </button>
                    </div>

                    <div v-else class="space-y-4">
                        <div 
                            v-for="item in cart" 
                            :key="item.product.id"
                            class="flex gap-4 p-3 bg-gray-50 rounded-lg"
                        >
                            <!-- Product Image -->
                            <div class="w-20 h-20 bg-gray-200 rounded-lg overflow-hidden flex-shrink-0">
                                <img 
                                    v-if="item.product.image" 
                                    :src="item.product.image" 
                                    :alt="item.product.name"
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                                    <ShoppingBagIcon class="w-8 h-8" aria-hidden="true" />
                                </div>
                            </div>

                            <!-- Product Details -->
                            <div class="flex-1 min-w-0">
                                <h3 class="font-medium text-sm line-clamp-2">{{ item.product.name }}</h3>
                                <p class="text-blue-600 font-semibold text-sm mt-1">{{ item.product.priceFormatted }}</p>
                                
                                <!-- Quantity Controls -->
                                <div class="flex items-center gap-2 mt-2">
                                    <button 
                                        @click="updateCartQuantity(item.product.id, item.quantity - 1)"
                                        class="w-7 h-7 flex items-center justify-center bg-white border rounded hover:bg-gray-50"
                                        aria-label="Decrease quantity"
                                    >
                                        -
                                    </button>
                                    <span class="w-8 text-center text-sm">{{ item.quantity }}</span>
                                    <button 
                                        @click="updateCartQuantity(item.product.id, item.quantity + 1)"
                                        class="w-7 h-7 flex items-center justify-center bg-white border rounded hover:bg-gray-50"
                                        aria-label="Increase quantity"
                                    >
                                        +
                                    </button>
                                </div>
                            </div>

                            <!-- Remove Button -->
                            <button 
                                @click="removeFromCart(item.product.id)"
                                class="text-gray-400 hover:text-red-500 transition-colors self-start"
                                aria-label="Remove item"
                            >
                                <XMarkIcon class="w-5 h-5" aria-hidden="true" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Cart Footer -->
                <div v-if="cart.length > 0" class="border-t p-4 space-y-4">
                    <!-- Subtotal -->
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="text-xl font-bold">{{ cartTotalFormatted }}</span>
                    </div>

                    <!-- Actions -->
                    <div class="space-y-2">
                        <button 
                            @click="proceedToCheckout"
                            class="w-full py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors"
                        >
                            Proceed to Checkout
                        </button>
                        <button 
                            @click="cartOpen = false"
                            class="w-full py-3 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors"
                        >
                            Continue Shopping
                        </button>
                    </div>

                    <!-- Clear Cart -->
                    <button 
                        @click="clearCart"
                        class="w-full text-sm text-gray-500 hover:text-red-500 transition-colors"
                    >
                        Clear Cart
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
/* Ensure smooth transitions */
details summary::-webkit-details-marker {
    display: none;
}

/* Line clamp utilities */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
