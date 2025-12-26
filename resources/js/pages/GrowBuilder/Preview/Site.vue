<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Bars3Icon, XMarkIcon, StarIcon, CheckIcon } from '@heroicons/vue/24/outline';
import { StarIcon as StarSolidIcon } from '@heroicons/vue/24/solid';

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
}

const props = defineProps<{
    site: Site;
    page: Page;
    pages: NavPage[];
    settings: Settings | null;
}>();

// Mobile menu state
const mobileMenuOpen = ref(false);

const sections = computed(() => props.page.content?.sections || []);
const navigation = computed(() => props.settings?.navigation || {});
const footer = computed(() => props.settings?.footer || {});

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

const getPageUrl = (page: NavPage) => {
    if (page.isHomepage) return `/sites/${props.site.subdomain}`;
    return `/sites/${props.site.subdomain}/${page.slug}`;
};

const getNavItemUrl = (navItem: any) => {
    if (navItem.isExternal) return navItem.url;
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
const loginUrl = computed(() => `/sites/${props.site.subdomain}/login`);
const registerUrl = computed(() => `/sites/${props.site.subdomain}/register`);

const closeMobileMenu = () => {
    mobileMenuOpen.value = false;
};
</script>

<template>
    <Head :title="`${page.title} - ${site.name}`" />

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
                        
                        <a
                            v-if="navigation.showCta"
                            :href="navigation.ctaLink || '#contact'"
                            class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
                        >
                            {{ navigation.ctaText || 'Contact Us' }}
                        </a>
                    </div>

                    <!-- Mobile Menu Button -->
                    <div class="flex items-center md:hidden">
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
                <!-- Hero Section - Mobile Optimized with Offset Support -->
                <section
                    v-if="section.type === 'hero'"
                    class="relative overflow-hidden"
                    :style="{ 
                        minHeight: section.style?.minHeight ? `${section.style.minHeight}px` : '500px',
                        backgroundColor: section.style?.backgroundColor || '#1e40af'
                    }"
                >
                    <!-- Background - only show gradient if no custom background color or image -->
                    <div 
                        class="absolute inset-0"
                        :style="{ backgroundColor: section.style?.backgroundColor || '#1e40af' }"
                    >
                        <img v-if="section.content.backgroundImage" :src="section.content.backgroundImage" class="w-full h-full object-cover" />
                        <div v-if="section.content.backgroundImage" class="absolute inset-0 bg-black/50"></div>
                    </div>
                    
                    <!-- Standard Layout with Offset Support -->
                    <div
                        class="relative z-10 h-full flex flex-col justify-center py-12 sm:py-16 lg:py-20 px-4 sm:px-6 lg:px-8"
                        :class="{
                            'items-start text-left': section.content.textPosition === 'left',
                            'items-center text-center': !section.content.textPosition || section.content.textPosition === 'center',
                            'items-end text-right': section.content.textPosition === 'right'
                        }"
                        :style="section.content.contentOffset ? `transform: translateY(${section.content.contentOffset}px)` : ''"
                    >
                        <div 
                            class="w-full max-w-4xl"
                            :class="{
                                '': section.content.textPosition === 'left',
                                'mx-auto': !section.content.textPosition || section.content.textPosition === 'center',
                                'ml-auto': section.content.textPosition === 'right'
                            }"
                        >
                            <h1 
                                class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-4"
                                :style="section.content.elementOffsets?.title ? `transform: translateY(${section.content.elementOffsets.title.y}px)` : ''"
                            >
                                {{ section.content.title }}
                            </h1>
                            <p 
                                class="text-base sm:text-lg lg:text-xl text-white/90 mb-6 sm:mb-8"
                                :style="section.content.elementOffsets?.subtitle ? `transform: translateY(${section.content.elementOffsets.subtitle.y}px)` : ''"
                            >
                                {{ section.content.subtitle }}
                            </p>
                            <div 
                                class="flex flex-col sm:flex-row gap-3 sm:gap-4" 
                                :class="{ 
                                    'justify-start': section.content.textPosition === 'left',
                                    'sm:justify-center': !section.content.textPosition || section.content.textPosition === 'center',
                                    'justify-end': section.content.textPosition === 'right'
                                }"
                                :style="section.content.elementOffsets?.buttons ? `transform: translateY(${section.content.elementOffsets.buttons.y}px)` : ''"
                            >
                                <a 
                                    :href="section.content.buttonLink || '#'" 
                                    class="inline-block px-6 sm:px-8 py-3 bg-white text-blue-600 font-semibold rounded-lg hover:bg-gray-100 transition-colors text-center"
                                >
                                    {{ section.content.buttonText || 'Get Started' }}
                                </a>
                                <a 
                                    v-if="section.content.secondaryButtonText"
                                    :href="section.content.secondaryButtonLink || '#'" 
                                    class="inline-block px-6 sm:px-8 py-3 border-2 border-white text-white font-semibold rounded-lg hover:bg-white/10 transition-colors text-center"
                                >
                                    {{ section.content.secondaryButtonText }}
                                </a>
                            </div>
                        </div>
                    </div>
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
                    :style="{ backgroundColor: section.style?.backgroundColor || '#2563eb' }"
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
                            <h2 class="text-2xl sm:text-3xl font-bold text-white mb-8 sm:mb-12">{{ section.content.title }}</h2>
                        </div>
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8 text-center text-white">
                            <div v-for="(stat, idx) in section.content.items" :key="idx" class="p-4">
                                <p class="text-3xl sm:text-4xl lg:text-5xl font-bold mb-2">{{ stat.value }}</p>
                                <p class="text-sm sm:text-base text-white/80">{{ stat.label }}</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Services Section - Mobile Optimized -->
                <section
                    v-else-if="section.type === 'services'"
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
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                            <div v-for="(item, idx) in section.content.items" :key="idx" class="bg-white p-5 sm:p-6 rounded-xl shadow-sm border border-gray-100">
                                <div v-if="item.icon" class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                                    <span class="text-blue-600 text-xl">{{ item.icon === 'chart' ? '📊' : item.icon === 'code' ? '💻' : item.icon === 'sparkles' ? '✨' : item.icon === 'briefcase' ? '💼' : item.icon === 'globe' ? '🌍' : item.icon === 'cog' ? '⚙️' : item.icon === 'users' ? '👥' : item.icon === 'shield' ? '🛡️' : '📦' }}</span>
                                </div>
                                <h3 class="text-lg sm:text-xl font-semibold mb-2 sm:mb-3">{{ item.title }}</h3>
                                <p class="text-gray-600 text-sm sm:text-base">{{ item.description }}</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Features Section - Mobile Optimized -->
                <section
                    v-else-if="section.type === 'features'"
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
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 sm:gap-8">
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

                <!-- About Section - Mobile Optimized -->
                <section
                    v-else-if="section.type === 'about'"
                    class="py-12 sm:py-16 px-4 sm:px-6 lg:px-8 overflow-hidden"
                    :style="{ backgroundColor: section.style?.backgroundColor }"
                >
                    <div 
                        class="max-w-6xl mx-auto"
                        :style="section.content.contentOffset ? `transform: translateY(${section.content.contentOffset}px)` : ''"
                    >
                        <div 
                            class="flex flex-col gap-8 sm:gap-12 items-center" 
                            :class="[
                                section.content.image ? 'lg:flex-row' : '',
                                section.content.imagePosition === 'left' ? 'lg:flex-row-reverse' : ''
                            ]"
                        >
                            <div 
                                :class="[
                                    section.content.image ? 'lg:w-1/2' : 'w-full max-w-3xl',
                                    {
                                        'text-left': section.content.textPosition === 'left',
                                        'text-center': section.content.textPosition === 'center',
                                        'text-right': section.content.textPosition === 'right'
                                    }
                                ]"
                            >
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
                    </div>
                </section>

                <!-- Testimonials Section - Mobile Optimized -->
                <section
                    v-else-if="section.type === 'testimonials'"
                    class="py-12 sm:py-16 px-4 sm:px-6 lg:px-8 overflow-hidden"
                    :style="{ backgroundColor: section.style?.backgroundColor || '#f8fafc' }"
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

                <!-- Team Section - Mobile Optimized -->
                <section
                    v-else-if="section.type === 'team'"
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
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6 sm:gap-8">
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

                <!-- FAQ Section - Mobile Optimized -->
                <section
                    v-else-if="section.type === 'faq'"
                    class="py-12 sm:py-16 px-4 sm:px-6 lg:px-8 overflow-hidden"
                    :style="{ backgroundColor: section.style?.backgroundColor }"
                >
                    <div 
                        class="max-w-3xl mx-auto"
                        :style="section.content.contentOffset ? `transform: translateY(${section.content.contentOffset}px)` : ''"
                    >
                        <div :class="{
                            'text-left': section.content.textPosition === 'left',
                            'text-center': !section.content.textPosition || section.content.textPosition === 'center',
                            'text-right': section.content.textPosition === 'right'
                        }">
                            <h2 class="text-2xl sm:text-3xl font-bold mb-8 sm:mb-12">{{ section.content.title }}</h2>
                        </div>
                        <div class="space-y-3 sm:space-y-4">
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
                        :style="section.content.contentOffset ? `transform: translateY(${section.content.contentOffset}px)` : ''"
                    >
                        <div 
                            class="max-w-3xl"
                            :class="{
                                '': section.content.textPosition === 'left',
                                'mx-auto': !section.content.textPosition || section.content.textPosition === 'center',
                                'ml-auto': section.content.textPosition === 'right'
                            }"
                        >
                            <h2 
                                class="text-2xl sm:text-3xl font-bold text-white mb-3 sm:mb-4"
                                :style="section.content.elementOffsets?.title ? `transform: translateY(${section.content.elementOffsets.title.y}px)` : ''"
                            >
                                {{ section.content.title }}
                            </h2>
                            <p 
                                class="text-white/90 mb-6 sm:mb-8 text-sm sm:text-base"
                                :style="section.content.elementOffsets?.description ? `transform: translateY(${section.content.elementOffsets.description.y}px)` : ''"
                            >
                                {{ section.content.description }}
                            </p>
                            <a 
                                :href="section.content.buttonLink || '#'" 
                                class="inline-block px-6 sm:px-8 py-3 bg-white text-blue-600 font-semibold rounded-lg hover:bg-gray-100 transition-colors text-sm sm:text-base"
                                :style="section.content.elementOffsets?.button ? `transform: translateY(${section.content.elementOffsets.button.y}px)` : ''"
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
                        <h2 class="text-2xl sm:text-3xl font-bold text-center mb-8 sm:mb-12">{{ section.content.title }}</h2>
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                            <div v-for="n in 4" :key="n" class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-100">
                                <div class="aspect-square bg-gray-100"></div>
                                <div class="p-3 sm:p-4">
                                    <h3 class="font-semibold text-sm sm:text-base mb-1">Product {{ n }}</h3>
                                    <p class="text-blue-600 font-bold text-sm sm:text-base">K99.00</p>
                                </div>
                            </div>
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
