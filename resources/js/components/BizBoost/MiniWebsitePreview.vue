<script setup lang="ts">
/**
 * MiniWebsitePreview - Enhanced component for mini-website rendering
 * Supports enhanced About section, services, testimonials, and more template variations
 */
import { computed, ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    MapPinIcon,
    PhoneIcon,
    EnvelopeIcon,
    ChevronDownIcon,
    PhotoIcon,
    SparklesIcon,
    ClockIcon,
    CheckBadgeIcon,
    UserGroupIcon,
} from '@heroicons/vue/24/outline';
import { StarIcon as StarSolidIcon } from '@heroicons/vue/24/solid';

interface TeamMember {
    name: string;
    role: string;
    image?: string;
}

interface Achievement {
    title: string;
    year?: number;
}

interface Service {
    name: string;
    description: string;
    price?: string;
    icon?: string;
}

interface Testimonial {
    name: string;
    content: string;
    rating: number;
}

interface BusinessHours {
    [key: string]: { open: string; close: string; closed?: boolean };
}

interface ThemeSettings {
    primary_color: string;
    secondary_color: string;
    accent_color: string;
    nav_color?: string;
    template: string;
    font_style: 'default' | 'elegant' | 'modern' | 'playful';
    button_style: 'rounded' | 'pill' | 'square';
    hero_style: 'gradient' | 'image' | 'split' | 'wave' | 'diagonal';
    about_layout?: 'story' | 'stats' | 'team' | 'minimal';
}

interface Product {
    id: number;
    name: string;
    price: number;
    sale_price: number | null;
    primary_image: { path: string } | null;
}

interface Props {
    businessName: string;
    businessSlug?: string;
    industry: string;
    logoPath: string | null;
    phone: string | null;
    whatsapp: string | null;
    email: string | null;
    address: string | null;
    city: string | null;
    tagline: string;
    about: string;
    businessStory?: string;
    mission?: string;
    vision?: string;
    foundingYear?: number | null;
    businessHours?: BusinessHours | null;
    teamMembers?: TeamMember[];
    achievements?: Achievement[];
    services?: Service[];
    testimonials?: Testimonial[];
    contactEmail: string;
    heroImagePath: string | null;
    heroImagePreviewUrl?: string | null;
    aboutImagePath?: string | null;
    aboutImagePreviewUrl?: string | null;
    showProducts: boolean;
    showServices?: boolean;
    showBusinessHours?: boolean;
    showTestimonials?: boolean;
    showContactForm: boolean;
    showWhatsappButton: boolean;
    showSocialLinks: boolean;
    themeSettings: ThemeSettings;
    products: Product[];
    isPreview?: boolean;
    previewMode?: 'desktop' | 'mobile';
    enableLinks?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    isPreview: false,
    previewMode: 'desktop',
    enableLinks: false,
    businessSlug: '',
    heroImagePreviewUrl: null,
    aboutImagePath: null,
    aboutImagePreviewUrl: null,
    businessStory: '',
    mission: '',
    vision: '',
    foundingYear: null,
    businessHours: null,
    teamMembers: () => [],
    achievements: () => [],
    services: () => [],
    testimonials: () => [],
    showServices: true,
    showBusinessHours: true,
    showTestimonials: false,
});

const isLoaded = ref(false);

onMounted(() => {
    setTimeout(() => {
        isLoaded.value = true;
    }, 100);
});

const defaultTheme: ThemeSettings = {
    primary_color: '#7c3aed',
    secondary_color: '#4f46e5',
    accent_color: '#10b981',
    nav_color: '#111827',
    template: 'professional',
    font_style: 'default',
    button_style: 'rounded',
    hero_style: 'gradient',
    about_layout: 'story',
};

const theme = computed(() => ({
    ...defaultTheme,
    ...props.themeSettings,
}));

const cssVars = computed(() => ({
    '--primary': theme.value.primary_color,
    '--secondary': theme.value.secondary_color,
    '--accent': theme.value.accent_color,
    '--nav': theme.value.nav_color || '#111827',
}));

// Determine if nav color is light or dark for text contrast
const isNavLight = computed(() => {
    const hex = (theme.value.nav_color || '#111827').replace('#', '');
    const r = parseInt(hex.substr(0, 2), 16);
    const g = parseInt(hex.substr(2, 2), 16);
    const b = parseInt(hex.substr(4, 2), 16);
    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
    return luminance > 0.5;
});

const heroBackground = computed(() => {
    const style = theme.value.hero_style;
    if (style === 'image' || style === 'split') {
        if (props.heroImagePreviewUrl) {
            return `url('${props.heroImagePreviewUrl}')`;
        }
        if (props.heroImagePath) {
            return `url('/storage/${props.heroImagePath}')`;
        }
    }
    
    // Different gradient styles based on hero_style
    switch (style) {
        case 'wave':
            return `linear-gradient(180deg, ${theme.value.primary_color} 0%, ${theme.value.secondary_color} 50%, ${theme.value.primary_color} 100%)`;
        case 'diagonal':
            return `linear-gradient(135deg, ${theme.value.primary_color} 0%, ${theme.value.secondary_color} 50%, ${theme.value.accent_color} 100%)`;
        default:
            return `linear-gradient(135deg, ${theme.value.primary_color} 0%, ${theme.value.secondary_color} 100%)`;
    }
});

const buttonStyle = computed(() => {
    const base = { backgroundColor: theme.value.primary_color };
    switch (theme.value.button_style) {
        case 'pill': return { ...base, borderRadius: '9999px' };
        case 'square': return { ...base, borderRadius: '0' };
        default: return { ...base, borderRadius: '0.5rem' };
    }
});

const fontClass = computed(() => {
    switch (theme.value.font_style) {
        case 'elegant': return 'font-serif';
        case 'modern': return 'font-sans tracking-tight';
        case 'playful': return 'font-sans';
        default: return 'font-sans';
    }
});

const formatPrice = (price: number) => `K${price.toLocaleString()}`;

const whatsappLink = computed(() => {
    const phone = props.whatsapp || props.phone;
    if (!phone) return '#';
    const cleaned = phone.replace(/\D/g, '');
    return `https://wa.me/${cleaned}`;
});

const isMobile = computed(() => props.isPreview && props.previewMode === 'mobile');

const yearsInBusiness = computed(() => {
    if (!props.foundingYear) return null;
    return new Date().getFullYear() - props.foundingYear;
});

const validServices = computed(() => props.services?.filter(s => s.name) || []);
const validTeamMembers = computed(() => props.teamMembers?.filter(m => m.name) || []);
const validTestimonials = computed(() => props.testimonials?.filter(t => t.name && t.content) || []);
const validAchievements = computed(() => props.achievements?.filter(a => a.title) || []);

const scrollToSection = (sectionId: string) => {
    if (props.isPreview) return;
    const element = document.getElementById(sectionId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth' });
    }
};

const formatDay = (day: string) => day.charAt(0).toUpperCase() + day.slice(1);
const daysOfWeek = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

// Computed for about section image - uses dedicated about image or falls back to hero
const aboutSectionImage = computed(() => {
    if (props.aboutImagePreviewUrl) return props.aboutImagePreviewUrl;
    if (props.aboutImagePath) return `/storage/${props.aboutImagePath}`;
    if (props.heroImagePreviewUrl) return props.heroImagePreviewUrl;
    if (props.heroImagePath) return `/storage/${props.heroImagePath}`;
    return null;
});
</script>

<template>
    <div :class="['min-h-full bg-white', fontClass]" :style="cssVars">
        <!-- Navigation Bar -->
        <nav 
            class="sticky top-0 z-50 backdrop-blur-lg border-b"
            :class="[
                isMobile ? 'px-4 py-3' : 'px-6 py-4',
                isNavLight ? 'border-gray-200' : 'border-gray-800'
            ]"
            :style="{ backgroundColor: (theme.nav_color || '#111827') + 'f2' }"
        >
            <div class="flex items-center justify-between max-w-6xl mx-auto">
                <div class="flex items-center gap-3">
                    <div 
                        class="overflow-hidden flex-shrink-0 shadow-sm ring-1"
                        :class="[
                            isMobile ? 'w-8 h-8 rounded-lg' : 'w-10 h-10 rounded-xl',
                            isNavLight ? 'bg-gray-200 ring-gray-300' : 'bg-gray-700 ring-gray-700'
                        ]"
                    >
                        <img
                            v-if="logoPath"
                            :src="`/storage/${logoPath}`"
                            :alt="businessName"
                            class="w-full h-full object-cover"
                        />
                        <div 
                            v-else 
                            class="w-full h-full flex items-center justify-center text-white font-bold"
                            :class="isMobile ? 'text-sm' : 'text-lg'"
                            :style="{ background: `linear-gradient(135deg, ${theme.primary_color}, ${theme.secondary_color})` }"
                        >
                            {{ businessName[0] }}
                        </div>
                    </div>
                    <span 
                        class="font-semibold"
                        :class="[
                            isMobile ? 'text-sm hidden' : 'text-base', 
                            isMobile ? '' : 'sm:block',
                            isNavLight ? 'text-gray-900' : 'text-white'
                        ]"
                    >
                        {{ businessName }}
                    </span>
                </div>
                
                <div v-if="!isMobile" class="hidden md:flex items-center gap-1 absolute left-1/2 -translate-x-1/2">
                    <button
                        @click="scrollToSection('hero')"
                        class="px-4 py-2 text-sm font-medium rounded-lg transition-colors"
                        :class="isNavLight ? 'text-gray-600 hover:text-gray-900 hover:bg-black/5' : 'text-gray-300 hover:text-white hover:bg-white/10'"
                    >
                        Home
                    </button>
                    <button
                        v-if="about || businessStory"
                        @click="scrollToSection('about')"
                        class="px-4 py-2 text-sm font-medium rounded-lg transition-colors"
                        :class="isNavLight ? 'text-gray-600 hover:text-gray-900 hover:bg-black/5' : 'text-gray-300 hover:text-white hover:bg-white/10'"
                    >
                        About
                    </button>
                    <button
                        v-if="showServices && validServices.length > 0"
                        @click="scrollToSection('services')"
                        class="px-4 py-2 text-sm font-medium rounded-lg transition-colors"
                        :class="isNavLight ? 'text-gray-600 hover:text-gray-900 hover:bg-black/5' : 'text-gray-300 hover:text-white hover:bg-white/10'"
                    >
                        Services
                    </button>
                    <button
                        v-if="showProducts && products.length > 0"
                        @click="scrollToSection('products')"
                        class="px-4 py-2 text-sm font-medium rounded-lg transition-colors"
                        :class="isNavLight ? 'text-gray-600 hover:text-gray-900 hover:bg-black/5' : 'text-gray-300 hover:text-white hover:bg-white/10'"
                    >
                        Products
                    </button>
                    <button
                        @click="scrollToSection('contact')"
                        class="px-4 py-2 text-sm font-medium rounded-lg transition-colors"
                        :class="isNavLight ? 'text-gray-600 hover:text-gray-900 hover:bg-black/5' : 'text-gray-300 hover:text-white hover:bg-white/10'"
                    >
                        Contact
                    </button>
                </div>
                
                <a
                    v-if="showWhatsappButton && (whatsapp || phone)"
                    :href="enableLinks ? whatsappLink : '#'"
                    :target="enableLinks ? '_blank' : undefined"
                    class="inline-flex items-center gap-2 text-white font-semibold rounded-lg transition-all hover:opacity-90 hover:shadow-md"
                    :class="isMobile ? 'px-3 py-1.5 text-xs' : 'px-4 py-2 text-sm'"
                    :style="{ backgroundColor: theme.primary_color }"
                >
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    <span :class="isMobile ? 'hidden' : 'inline'">WhatsApp</span>
                </a>
            </div>
        </nav>

        <!-- Hero Section -->
        <section 
            id="hero" 
            class="relative overflow-hidden flex items-center justify-center"
            :class="isMobile ? 'min-h-[320px]' : 'min-h-[500px]'"
        >
            <div 
                class="absolute inset-0 bg-cover bg-center transition-transform duration-700"
                :class="isLoaded ? 'scale-100' : 'scale-110'"
                :style="{ backgroundImage: heroBackground }"
            >
                <div class="absolute inset-0 bg-gradient-to-br from-black/60 via-black/40 to-black/70"></div>
            </div>

            <!-- Decorative elements based on hero style -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div 
                    v-if="theme.hero_style === 'wave'"
                    class="absolute bottom-0 left-0 right-0 h-24"
                    :style="{ background: `linear-gradient(to top, white, transparent)` }"
                ></div>
                <div 
                    v-if="theme.hero_style === 'diagonal'"
                    class="absolute -bottom-1/4 -right-1/4 w-[600px] h-[600px] rounded-full opacity-10"
                    :style="{ backgroundColor: theme.accent_color }"
                ></div>
                <div 
                    class="absolute -top-1/2 -right-1/4 w-[800px] h-[800px] rounded-full opacity-20 blur-3xl"
                    :style="{ backgroundColor: theme.accent_color }"
                ></div>
            </div>

            <div 
                class="relative z-10 text-center"
                :class="isMobile ? 'px-4' : 'px-6 max-w-5xl mx-auto'"
            >
                <h1 
                    class="font-bold text-white mb-4 transition-all duration-700 delay-300 tracking-tight"
                    :class="[
                        isLoaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8',
                        isMobile ? 'text-2xl' : 'text-4xl sm:text-5xl md:text-6xl'
                    ]"
                >
                    {{ businessName }}
                </h1>

                <p 
                    v-if="tagline"
                    class="text-white/90 max-w-2xl mx-auto mb-3 transition-all duration-700 delay-400"
                    :class="[
                        isLoaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8',
                        isMobile ? 'text-sm' : 'text-xl md:text-2xl'
                    ]"
                >
                    {{ tagline }}
                </p>

                <div 
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 backdrop-blur-sm border border-white/20 text-white/80 text-sm font-medium mb-8 transition-all duration-700 delay-500"
                    :class="isLoaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'"
                >
                    <span class="w-2 h-2 rounded-full animate-pulse" :style="{ backgroundColor: theme.accent_color }"></span>
                    <span class="capitalize">{{ industry }}</span>
                    <span v-if="yearsInBusiness" class="text-white/60">• {{ yearsInBusiness }}+ years</span>
                </div>

                <div 
                    class="flex flex-wrap items-center justify-center gap-3 transition-all duration-700 delay-600"
                    :class="isLoaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'"
                >
                    <a
                        v-if="showWhatsappButton && (whatsapp || phone)"
                        :href="enableLinks ? whatsappLink : '#'"
                        :target="enableLinks ? '_blank' : undefined"
                        class="inline-flex items-center justify-center gap-2 font-semibold transition-all transform hover:scale-105 bg-green-500 hover:bg-green-600 text-white shadow-lg shadow-green-500/30"
                        :class="isMobile ? 'px-4 py-2.5 text-sm rounded-lg' : 'px-6 py-3 rounded-xl'"
                    >
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        Chat on WhatsApp
                    </a>
                    <a
                        v-if="phone"
                        :href="enableLinks ? `tel:${phone}` : '#'"
                        class="inline-flex items-center justify-center gap-2 font-semibold transition-all transform hover:scale-105 text-white shadow-lg"
                        :class="isMobile ? 'px-4 py-2.5 text-sm rounded-lg' : 'px-6 py-3 rounded-xl'"
                        :style="{ 
                            backgroundColor: theme.primary_color,
                            boxShadow: `0 10px 40px -10px ${theme.primary_color}80`
                        }"
                    >
                        <PhoneIcon class="h-5 w-5" aria-hidden="true" />
                        Call Now
                    </a>
                </div>
            </div>

            <div 
                v-if="!isPreview"
                class="absolute bottom-8 left-1/2 -translate-x-1/2 transition-all duration-700 delay-700"
                :class="isLoaded ? 'opacity-100' : 'opacity-0'"
            >
                <button 
                    @click="scrollToSection(about || businessStory ? 'about' : 'products')"
                    class="flex flex-col items-center gap-2 text-white/60 hover:text-white transition-colors"
                    aria-label="Scroll down"
                >
                    <span class="text-xs font-medium tracking-wider uppercase">Explore</span>
                    <ChevronDownIcon class="h-5 w-5 animate-bounce" aria-hidden="true" />
                </button>
            </div>
        </section>

        <!-- Enhanced About Section -->
        <section 
            v-if="about || businessStory" 
            id="about" 
            class="bg-gray-50"
            :class="isMobile ? 'py-12 px-4' : 'py-24'"
        >
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <!-- Story Layout -->
                <template v-if="theme.about_layout === 'story' || !theme.about_layout">
                    <div :class="isMobile ? '' : 'grid lg:grid-cols-2 gap-12 lg:gap-20 items-center'">
                        <div>
                            <div 
                                class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium mb-6"
                                :style="{ backgroundColor: theme.primary_color + '15', color: theme.primary_color }"
                            >
                                <span class="w-1.5 h-1.5 rounded-full" :style="{ backgroundColor: theme.primary_color }"></span>
                                About Us
                            </div>
                            <h2 
                                class="font-bold text-gray-900 mb-6 leading-tight"
                                :class="isMobile ? 'text-2xl' : 'text-3xl md:text-4xl'"
                            >
                                {{ tagline || `Welcome to ${businessName}` }}
                            </h2>
                            
                            <!-- Mission & Vision -->
                            <div v-if="mission || vision" class="space-y-4 mb-6">
                                <div v-if="mission" class="p-4 rounded-xl bg-white shadow-sm border border-gray-100">
                                    <p class="text-xs font-semibold uppercase tracking-wider mb-1" :style="{ color: theme.primary_color }">Our Mission</p>
                                    <p class="text-gray-700">{{ mission }}</p>
                                </div>
                                <div v-if="vision" class="p-4 rounded-xl bg-white shadow-sm border border-gray-100">
                                    <p class="text-xs font-semibold uppercase tracking-wider mb-1" :style="{ color: theme.accent_color }">Our Vision</p>
                                    <p class="text-gray-700">{{ vision }}</p>
                                </div>
                            </div>
                            
                            <div class="prose prose-lg text-gray-600 leading-relaxed">
                                <p v-if="businessStory" class="whitespace-pre-line">{{ businessStory }}</p>
                                <p v-else-if="about" class="whitespace-pre-line">{{ about }}</p>
                            </div>
                            
                            <!-- Quick Stats -->
                            <div class="grid grid-cols-2 gap-4 mt-10">
                                <div class="p-4 rounded-xl bg-white shadow-sm border border-gray-100">
                                    <div 
                                        class="w-10 h-10 rounded-lg flex items-center justify-center mb-3"
                                        :style="{ backgroundColor: theme.primary_color + '15' }"
                                    >
                                        <MapPinIcon class="h-5 w-5" :style="{ color: theme.primary_color }" aria-hidden="true" />
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">Location</p>
                                    <p class="text-sm text-gray-500">{{ city || 'Zambia' }}</p>
                                </div>
                                <div class="p-4 rounded-xl bg-white shadow-sm border border-gray-100">
                                    <div 
                                        class="w-10 h-10 rounded-lg flex items-center justify-center mb-3"
                                        :style="{ backgroundColor: theme.accent_color + '15' }"
                                    >
                                        <StarSolidIcon class="h-5 w-5" :style="{ color: theme.accent_color }" aria-hidden="true" />
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">{{ yearsInBusiness ? 'Experience' : 'Industry' }}</p>
                                    <p class="text-sm text-gray-500 capitalize">{{ yearsInBusiness ? `${yearsInBusiness}+ years` : industry }}</p>
                                </div>
                            </div>
                        </div>

                        <div v-if="!isMobile" class="relative hidden lg:block">
                            <div class="aspect-square rounded-3xl overflow-hidden shadow-2xl">
                                <img
                                    v-if="aboutSectionImage"
                                    :src="aboutSectionImage"
                                    :alt="`About ${businessName}`"
                                    class="w-full h-full object-cover"
                                />
                                <div 
                                    v-else
                                    class="w-full h-full flex items-center justify-center"
                                    :style="{ background: `linear-gradient(135deg, ${theme.primary_color}20, ${theme.secondary_color}20)` }"
                                >
                                    <div class="text-center">
                                        <div 
                                            class="w-32 h-32 mx-auto rounded-2xl flex items-center justify-center mb-4"
                                            :style="{ background: `linear-gradient(135deg, ${theme.primary_color}, ${theme.secondary_color})` }"
                                        >
                                            <span class="text-6xl font-bold text-white">{{ businessName[0] }}</span>
                                        </div>
                                        <p class="text-xl font-semibold text-gray-700">{{ businessName }}</p>
                                    </div>
                                </div>
                            </div>
                            <div 
                                class="absolute -bottom-6 -right-6 w-32 h-32 rounded-2xl -z-10"
                                :style="{ backgroundColor: theme.primary_color + '20' }"
                            ></div>
                        </div>
                    </div>
                </template>

                <!-- Stats Layout -->
                <template v-else-if="theme.about_layout === 'stats'">
                    <div class="text-center mb-12">
                        <div 
                            class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium mb-4"
                            :style="{ backgroundColor: theme.primary_color + '15', color: theme.primary_color }"
                        >
                            <span class="w-1.5 h-1.5 rounded-full" :style="{ backgroundColor: theme.primary_color }"></span>
                            About Us
                        </div>
                        <h2 
                            class="font-bold text-gray-900 mb-4"
                            :class="isMobile ? 'text-2xl' : 'text-3xl md:text-4xl'"
                        >
                            {{ businessName }}
                        </h2>
                        <p class="text-lg text-gray-600 max-w-2xl mx-auto">{{ about || businessStory }}</p>
                    </div>
                    
                    <!-- Stats Grid -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-12">
                        <div class="text-center p-6 bg-white rounded-2xl shadow-sm">
                            <div 
                                class="text-4xl font-bold mb-2"
                                :style="{ color: theme.primary_color }"
                            >{{ yearsInBusiness || '1' }}+</div>
                            <p class="text-sm text-gray-600">Years Experience</p>
                        </div>
                        <div class="text-center p-6 bg-white rounded-2xl shadow-sm">
                            <div 
                                class="text-4xl font-bold mb-2"
                                :style="{ color: theme.secondary_color }"
                            >{{ products.length || '10' }}+</div>
                            <p class="text-sm text-gray-600">Products</p>
                        </div>
                        <div class="text-center p-6 bg-white rounded-2xl shadow-sm">
                            <div 
                                class="text-4xl font-bold mb-2"
                                :style="{ color: theme.accent_color }"
                            >{{ validServices.length || '5' }}+</div>
                            <p class="text-sm text-gray-600">Services</p>
                        </div>
                        <div class="text-center p-6 bg-white rounded-2xl shadow-sm">
                            <div 
                                class="text-4xl font-bold mb-2"
                                :style="{ color: theme.primary_color }"
                            >100%</div>
                            <p class="text-sm text-gray-600">Satisfaction</p>
                        </div>
                    </div>
                    
                    <!-- Mission & Vision Cards -->
                    <div v-if="mission || vision" class="grid md:grid-cols-2 gap-6">
                        <div v-if="mission" class="p-6 rounded-2xl bg-white shadow-sm border-l-4" :style="{ borderColor: theme.primary_color }">
                            <h3 class="font-semibold text-gray-900 mb-2">Our Mission</h3>
                            <p class="text-gray-600">{{ mission }}</p>
                        </div>
                        <div v-if="vision" class="p-6 rounded-2xl bg-white shadow-sm border-l-4" :style="{ borderColor: theme.accent_color }">
                            <h3 class="font-semibold text-gray-900 mb-2">Our Vision</h3>
                            <p class="text-gray-600">{{ vision }}</p>
                        </div>
                    </div>
                </template>

                <!-- Team Layout -->
                <template v-else-if="theme.about_layout === 'team'">
                    <div class="text-center mb-12">
                        <div 
                            class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium mb-4"
                            :style="{ backgroundColor: theme.primary_color + '15', color: theme.primary_color }"
                        >
                            <UserGroupIcon class="h-4 w-4" aria-hidden="true" />
                            Meet Our Team
                        </div>
                        <h2 
                            class="font-bold text-gray-900 mb-4"
                            :class="isMobile ? 'text-2xl' : 'text-3xl md:text-4xl'"
                        >
                            The People Behind {{ businessName }}
                        </h2>
                        <p class="text-lg text-gray-600 max-w-2xl mx-auto">{{ about }}</p>
                    </div>
                    
                    <!-- Team Grid -->
                    <div v-if="validTeamMembers.length > 0" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        <div 
                            v-for="(member, index) in validTeamMembers" 
                            :key="index"
                            class="text-center"
                        >
                            <div 
                                class="w-24 h-24 mx-auto rounded-full mb-4 flex items-center justify-center text-white text-2xl font-bold"
                                :style="{ background: `linear-gradient(135deg, ${theme.primary_color}, ${theme.secondary_color})` }"
                            >
                                {{ member.name[0] }}
                            </div>
                            <h3 class="font-semibold text-gray-900">{{ member.name }}</h3>
                            <p class="text-sm text-gray-500">{{ member.role }}</p>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-gray-500">
                        <UserGroupIcon class="h-12 w-12 mx-auto mb-3 text-gray-300" aria-hidden="true" />
                        <p>Team members will appear here</p>
                    </div>
                </template>

                <!-- Minimal Layout -->
                <template v-else-if="theme.about_layout === 'minimal'">
                    <div class="max-w-3xl mx-auto text-center">
                        <h2 
                            class="font-bold text-gray-900 mb-6"
                            :class="isMobile ? 'text-2xl' : 'text-3xl md:text-4xl'"
                        >
                            About {{ businessName }}
                        </h2>
                        <p class="text-lg text-gray-600 leading-relaxed">{{ businessStory || about }}</p>
                        <div v-if="mission" class="mt-8 p-6 bg-white rounded-xl shadow-sm">
                            <p class="text-gray-700 italic">"{{ mission }}"</p>
                        </div>
                    </div>
                </template>
            </div>
        </section>

        <!-- Services Section -->
        <section 
            v-if="showServices && validServices.length > 0" 
            id="services"
            class="bg-white"
            :class="isMobile ? 'py-12 px-4' : 'py-24'"
        >
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <div class="text-center mb-12">
                    <div 
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium mb-4"
                        :style="{ backgroundColor: theme.primary_color + '15', color: theme.primary_color }"
                    >
                        <SparklesIcon class="h-4 w-4" aria-hidden="true" />
                        Our Services
                    </div>
                    <h2 
                        class="font-bold text-gray-900 mb-4"
                        :class="isMobile ? 'text-2xl' : 'text-3xl md:text-4xl'"
                    >
                        What We Offer
                    </h2>
                </div>

                <div class="grid gap-6" :class="isMobile ? 'grid-cols-1' : 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3'">
                    <div 
                        v-for="(service, index) in validServices" 
                        :key="index"
                        class="p-6 bg-gray-50 rounded-2xl hover:shadow-lg transition-shadow"
                    >
                        <div 
                            class="w-12 h-12 rounded-xl flex items-center justify-center mb-4"
                            :style="{ backgroundColor: theme.primary_color + '15' }"
                        >
                            <CheckBadgeIcon class="h-6 w-6" :style="{ color: theme.primary_color }" aria-hidden="true" />
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">{{ service.name }}</h3>
                        <p class="text-gray-600 text-sm mb-3">{{ service.description }}</p>
                        <p v-if="service.price" class="font-bold" :style="{ color: theme.primary_color }">{{ service.price }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Business Hours Section -->
        <section 
            v-if="showBusinessHours && businessHours" 
            class="bg-gray-50"
            :class="isMobile ? 'py-12 px-4' : 'py-16'"
        >
            <div class="max-w-2xl mx-auto px-4 sm:px-6">
                <div class="text-center mb-8">
                    <div 
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium mb-4"
                        :style="{ backgroundColor: theme.primary_color + '15', color: theme.primary_color }"
                    >
                        <ClockIcon class="h-4 w-4" aria-hidden="true" />
                        Business Hours
                    </div>
                </div>
                
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <div class="space-y-3">
                        <div 
                            v-for="day in daysOfWeek" 
                            :key="day"
                            class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0"
                        >
                            <span class="font-medium text-gray-900 capitalize">{{ formatDay(day) }}</span>
                            <span 
                                v-if="businessHours[day]?.closed"
                                class="text-gray-400"
                            >Closed</span>
                            <span v-else class="text-gray-600">
                                {{ businessHours[day]?.open || '09:00' }} - {{ businessHours[day]?.close || '17:00' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Products Section -->
        <section 
            v-if="showProducts && products.length > 0" 
            id="products"
            :class="[
                'bg-white',
                isMobile ? 'py-12 px-4' : 'py-24'
            ]"
        >
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <div class="text-center mb-12">
                    <div 
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium mb-4"
                        :style="{ backgroundColor: theme.primary_color + '15', color: theme.primary_color }"
                    >
                        <span class="w-1.5 h-1.5 rounded-full" :style="{ backgroundColor: theme.primary_color }"></span>
                        Our Products
                    </div>
                    <h2 
                        class="font-bold text-gray-900 mb-4"
                        :class="isMobile ? 'text-2xl' : 'text-3xl md:text-4xl'"
                    >
                        What We Offer
                    </h2>
                </div>

                <div 
                    class="grid gap-6"
                    :class="isMobile ? 'grid-cols-2' : 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4'"
                >
                    <component
                        :is="enableLinks && businessSlug ? Link : 'div'"
                        v-for="product in products.slice(0, isMobile ? 4 : 8)"
                        :key="product.id"
                        :href="enableLinks && businessSlug ? route('bizboost.public.product', [businessSlug, product.id]) : undefined"
                        class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100"
                    >
                        <div class="aspect-square bg-gray-100 overflow-hidden relative">
                            <img
                                v-if="product.primary_image"
                                :src="`/storage/${product.primary_image.path}`"
                                :alt="product.name"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100">
                                <PhotoIcon class="h-16 w-16 text-gray-300" aria-hidden="true" />
                            </div>
                            <div 
                                v-if="product.sale_price"
                                class="absolute top-3 left-3 px-2 py-1 rounded-full text-xs font-bold text-white"
                                :style="{ backgroundColor: theme.accent_color }"
                            >
                                Sale
                            </div>
                        </div>
                        <div :class="isMobile ? 'p-3' : 'p-4'">
                            <h3 
                                class="font-semibold text-gray-900 truncate"
                                :class="isMobile ? 'text-sm' : 'text-base'"
                            >
                                {{ product.name }}
                            </h3>
                            <div class="flex items-center gap-2 mt-2">
                                <span 
                                    class="font-bold"
                                    :class="isMobile ? 'text-base' : 'text-lg'"
                                    :style="{ color: theme.primary_color }"
                                >
                                    {{ formatPrice(product.sale_price || product.price) }}
                                </span>
                                <span 
                                    v-if="product.sale_price"
                                    class="text-gray-400 line-through text-sm"
                                >
                                    {{ formatPrice(product.price) }}
                                </span>
                            </div>
                        </div>
                    </component>
                </div>

                <div v-if="products.length > (isMobile ? 4 : 8)" class="text-center mt-10">
                    <component
                        :is="enableLinks && businessSlug ? Link : 'button'"
                        :href="enableLinks && businessSlug ? route('bizboost.public.products', businessSlug) : undefined"
                        class="inline-flex items-center gap-2 text-white font-semibold shadow-lg transition-all hover:shadow-xl px-6 py-3 rounded-xl"
                        :style="buttonStyle"
                    >
                        View All Products
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </component>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section 
            v-if="showTestimonials && validTestimonials.length > 0" 
            class="bg-gray-50"
            :class="isMobile ? 'py-12 px-4' : 'py-24'"
        >
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <div class="text-center mb-12">
                    <div 
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium mb-4"
                        :style="{ backgroundColor: theme.primary_color + '15', color: theme.primary_color }"
                    >
                        <StarSolidIcon class="h-4 w-4" aria-hidden="true" />
                        Testimonials
                    </div>
                    <h2 
                        class="font-bold text-gray-900 mb-4"
                        :class="isMobile ? 'text-2xl' : 'text-3xl md:text-4xl'"
                    >
                        What Our Customers Say
                    </h2>
                </div>

                <div class="grid gap-6" :class="isMobile ? 'grid-cols-1' : 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3'">
                    <div 
                        v-for="(testimonial, index) in validTestimonials" 
                        :key="index"
                        class="p-6 bg-white rounded-2xl shadow-sm"
                    >
                        <div class="flex items-center gap-1 mb-4">
                            <StarSolidIcon 
                                v-for="star in testimonial.rating" 
                                :key="star"
                                class="h-5 w-5"
                                :style="{ color: theme.accent_color }"
                                aria-hidden="true"
                            />
                        </div>
                        <p class="text-gray-600 mb-4 italic">"{{ testimonial.content }}"</p>
                        <div class="flex items-center gap-3">
                            <div 
                                class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold"
                                :style="{ backgroundColor: theme.primary_color }"
                            >
                                {{ testimonial.name[0] }}
                            </div>
                            <span class="font-medium text-gray-900">{{ testimonial.name }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section 
            id="contact"
            :class="[
                'bg-gray-50',
                isMobile ? 'py-12 px-4' : 'py-24'
            ]"
        >
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <div class="text-center mb-12">
                    <div 
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium mb-4"
                        :style="{ backgroundColor: theme.primary_color + '15', color: theme.primary_color }"
                    >
                        <span class="w-1.5 h-1.5 rounded-full" :style="{ backgroundColor: theme.primary_color }"></span>
                        Contact Us
                    </div>
                    <h2 
                        class="font-bold text-gray-900 mb-4"
                        :class="isMobile ? 'text-2xl' : 'text-3xl md:text-4xl'"
                    >
                        Get In Touch
                    </h2>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                        We'd love to hear from you. Reach out anytime!
                    </p>
                </div>

                <div 
                    class="grid gap-6 max-w-3xl mx-auto"
                    :class="isMobile ? 'grid-cols-1' : 'grid-cols-1 md:grid-cols-3'"
                >
                    <a
                        v-if="phone"
                        :href="enableLinks ? `tel:${phone}` : '#'"
                        class="flex flex-col items-center p-6 bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow text-center"
                    >
                        <div 
                            class="w-14 h-14 rounded-xl flex items-center justify-center mb-4"
                            :style="{ backgroundColor: theme.primary_color + '15' }"
                        >
                            <PhoneIcon class="h-6 w-6" :style="{ color: theme.primary_color }" aria-hidden="true" />
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-1">Call Us</h3>
                        <p class="text-gray-600 text-sm">{{ phone }}</p>
                    </a>

                    <a
                        v-if="contactEmail || email"
                        :href="enableLinks ? `mailto:${contactEmail || email}` : '#'"
                        class="flex flex-col items-center p-6 bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow text-center"
                    >
                        <div 
                            class="w-14 h-14 rounded-xl flex items-center justify-center mb-4"
                            :style="{ backgroundColor: theme.secondary_color + '15' }"
                        >
                            <EnvelopeIcon class="h-6 w-6" :style="{ color: theme.secondary_color }" aria-hidden="true" />
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-1">Email Us</h3>
                        <p class="text-gray-600 text-sm truncate max-w-full">{{ contactEmail || email }}</p>
                    </a>

                    <div
                        v-if="address || city"
                        class="flex flex-col items-center p-6 bg-white rounded-2xl shadow-sm text-center"
                    >
                        <div 
                            class="w-14 h-14 rounded-xl flex items-center justify-center mb-4"
                            :style="{ backgroundColor: theme.accent_color + '15' }"
                        >
                            <MapPinIcon class="h-6 w-6" :style="{ color: theme.accent_color }" aria-hidden="true" />
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-1">Visit Us</h3>
                        <p class="text-gray-600 text-sm">{{ address || city }}</p>
                    </div>
                </div>

                <!-- WhatsApp CTA -->
                <div v-if="showWhatsappButton && (whatsapp || phone)" class="text-center mt-10">
                    <a
                        :href="enableLinks ? whatsappLink : '#'"
                        :target="enableLinks ? '_blank' : undefined"
                        class="inline-flex items-center gap-3 px-8 py-4 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-2xl shadow-lg shadow-green-500/30 transition-all hover:scale-105"
                    >
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        Chat with us on WhatsApp
                    </a>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white py-8">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 text-center">
                <p class="text-gray-400 text-sm">
                    © {{ new Date().getFullYear() }} {{ businessName }}. All rights reserved.
                </p>
                <p class="text-gray-500 text-xs mt-2">
                    Powered by BizBoost
                </p>
            </div>
        </footer>
    </div>
</template>
