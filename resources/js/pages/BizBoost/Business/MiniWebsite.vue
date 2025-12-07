<script setup lang="ts">
/**
 * MiniWebsite Builder - Enhanced version with About tab and improved templates
 * Uses MiniWebsitePreview component for WYSIWYG preview
 */
import { Head, useForm, router } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import MiniWebsitePreview from '@/Components/BizBoost/MiniWebsitePreview.vue';
import {
    GlobeAltIcon,
    ClipboardDocumentIcon,
    CheckIcon,
    DevicePhoneMobileIcon,
    ComputerDesktopIcon,
    PaintBrushIcon,
    DocumentTextIcon,
    Cog6ToothIcon,
    PhotoIcon,
    ArrowTopRightOnSquareIcon,
    ShoppingBagIcon,
    EnvelopeIcon,
    ChatBubbleLeftRightIcon,
    UserGroupIcon,
    ClockIcon,
    SparklesIcon,
    StarIcon,
    BuildingOfficeIcon,
    PlusIcon,
    TrashIcon,
    WrenchScrewdriverIcon,
} from '@heroicons/vue/24/outline';
import { ref, computed } from 'vue';

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

interface BusinessProfile {
    about: string | null;
    business_story: string | null;
    mission: string | null;
    vision: string | null;
    founding_year: number | null;
    business_hours: BusinessHours | null;
    team_members: TeamMember[] | null;
    achievements: Achievement[] | null;
    services: Service[] | null;
    testimonials: Testimonial[] | null;
    tagline: string | null;
    contact_email: string | null;
    hero_image_path: string | null;
    about_image_path: string | null;
    show_products: boolean;
    show_services: boolean;
    show_gallery: boolean;
    show_testimonials: boolean;
    show_business_hours: boolean;
    show_contact_form: boolean;
    show_whatsapp_button: boolean;
    show_social_links: boolean;
    is_published: boolean;
    theme_settings: ThemeSettings | null;
}

interface ThemeSettings {
    primary_color: string;
    secondary_color: string;
    accent_color: string;
    nav_color: string;
    template: 'professional' | 'elegant' | 'bold' | 'minimal' | 'creative' | 'corporate' | 'luxury' | 'modern';
    font_style: 'default' | 'elegant' | 'modern' | 'playful';
    button_style: 'rounded' | 'pill' | 'square';
    hero_style: 'gradient' | 'image' | 'split' | 'wave' | 'diagonal';
    about_layout: 'story' | 'stats' | 'team' | 'minimal';
}

interface Product {
    id: number;
    name: string;
    price: number;
    sale_price: number | null;
    primary_image: { path: string } | null;
}

interface Business {
    id: number;
    name: string;
    slug: string;
    industry: string;
    description: string | null;
    logo_path: string | null;
    phone: string | null;
    whatsapp: string | null;
    email: string | null;
    address: string | null;
    city: string | null;
    social_links: Record<string, string> | null;
    profile: BusinessProfile | null;
}

interface Props {
    business: Business;
    publicUrl: string;
    products: Product[];
}

const props = defineProps<Props>();

const copied = ref(false);
const activeTab = ref<'content' | 'about' | 'design' | 'settings'>('content');
const previewMode = ref<'desktop' | 'mobile'>('desktop');

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

const defaultBusinessHours: BusinessHours = {
    monday: { open: '08:00', close: '17:00' },
    tuesday: { open: '08:00', close: '17:00' },
    wednesday: { open: '08:00', close: '17:00' },
    thursday: { open: '08:00', close: '17:00' },
    friday: { open: '08:00', close: '17:00' },
    saturday: { open: '09:00', close: '13:00', closed: false },
    sunday: { open: '09:00', close: '13:00', closed: true },
};

const form = useForm({
    // Basic content
    about: props.business.profile?.about || '',
    tagline: props.business.profile?.tagline || '',
    contact_email: props.business.profile?.contact_email || props.business.email || '',
    hero_image: null as File | null,
    about_image: null as File | null,
    
    // Enhanced About fields
    business_story: props.business.profile?.business_story || '',
    mission: props.business.profile?.mission || '',
    vision: props.business.profile?.vision || '',
    founding_year: props.business.profile?.founding_year || null,
    business_hours: props.business.profile?.business_hours || defaultBusinessHours,
    team_members: props.business.profile?.team_members || [] as TeamMember[],
    achievements: props.business.profile?.achievements || [] as Achievement[],
    services: props.business.profile?.services || [] as Service[],
    testimonials: props.business.profile?.testimonials || [] as Testimonial[],
    
    // Section toggles
    show_products: props.business.profile?.show_products ?? true,
    show_services: props.business.profile?.show_services ?? true,
    show_gallery: props.business.profile?.show_gallery ?? false,
    show_testimonials: props.business.profile?.show_testimonials ?? false,
    show_business_hours: props.business.profile?.show_business_hours ?? true,
    show_contact_form: props.business.profile?.show_contact_form ?? true,
    show_whatsapp_button: props.business.profile?.show_whatsapp_button ?? true,
    show_social_links: props.business.profile?.show_social_links ?? true,
    is_published: props.business.profile?.is_published ?? false,
    
    // Theme
    theme_settings: {
        ...defaultTheme,
        ...(props.business.profile?.theme_settings || {}),
    } as ThemeSettings,
});

const heroImagePreview = ref<string | null>(
    props.business.profile?.hero_image_path 
        ? `/storage/${props.business.profile.hero_image_path}` 
        : null
);

const aboutImagePreview = ref<string | null>(
    props.business.profile?.about_image_path 
        ? `/storage/${props.business.profile.about_image_path}` 
        : null
);

const handleHeroImageChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
        form.hero_image = file;
        heroImagePreview.value = URL.createObjectURL(file);
    }
};

const handleAboutImageChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
        form.about_image = file;
        aboutImagePreview.value = URL.createObjectURL(file);
    }
};

// Team member management
const addTeamMember = () => {
    form.team_members.push({ name: '', role: '' });
};

const removeTeamMember = (index: number) => {
    form.team_members.splice(index, 1);
};

// Achievement management
const addAchievement = () => {
    form.achievements.push({ title: '', year: new Date().getFullYear() });
};

const removeAchievement = (index: number) => {
    form.achievements.splice(index, 1);
};

// Service management
const addService = () => {
    form.services.push({ name: '', description: '', price: '' });
};

const removeService = (index: number) => {
    form.services.splice(index, 1);
};

// Testimonial management
const addTestimonial = () => {
    form.testimonials.push({ name: '', content: '', rating: 5 });
};

const removeTestimonial = (index: number) => {
    form.testimonials.splice(index, 1);
};

const submit = () => {
    form.post(route('bizboost.business.mini-website.update'), {
        preserveScroll: true,
        forceFormData: true,
    });
};

const togglePublish = () => {
    if (props.business.profile?.is_published) {
        router.post(route('bizboost.business.mini-website.unpublish'));
    } else {
        router.post(route('bizboost.business.mini-website.publish'));
    }
};

const copyUrl = () => {
    navigator.clipboard.writeText(props.publicUrl);
    copied.value = true;
    setTimeout(() => (copied.value = false), 2000);
};

const previewSite = () => {
    window.open(props.publicUrl, '_blank');
};

// Enhanced template presets with distinct layouts
const templatePresets = [
    { 
        id: 'professional', 
        name: 'Professional', 
        desc: 'Clean corporate look', 
        primary: '#2563eb', 
        secondary: '#1e40af', 
        accent: '#059669',
        nav: '#111827',
        heroStyle: 'gradient',
        aboutLayout: 'stats',
        buttonStyle: 'rounded'
    },
    { 
        id: 'elegant', 
        name: 'Elegant', 
        desc: 'Sophisticated & refined', 
        primary: '#7c3aed', 
        secondary: '#5b21b6', 
        accent: '#f59e0b',
        nav: '#1f2937',
        heroStyle: 'image',
        aboutLayout: 'story',
        buttonStyle: 'pill'
    },
    { 
        id: 'bold', 
        name: 'Bold', 
        desc: 'Strong & impactful', 
        primary: '#dc2626', 
        secondary: '#991b1b', 
        accent: '#fbbf24',
        nav: '#0f0f0f',
        heroStyle: 'diagonal',
        aboutLayout: 'stats',
        buttonStyle: 'square'
    },
    { 
        id: 'minimal', 
        name: 'Minimal', 
        desc: 'Simple & clean', 
        primary: '#374151', 
        secondary: '#1f2937', 
        accent: '#10b981',
        nav: '#ffffff',
        heroStyle: 'gradient',
        aboutLayout: 'minimal',
        buttonStyle: 'rounded'
    },
    { 
        id: 'creative', 
        name: 'Creative', 
        desc: 'Vibrant & fun', 
        primary: '#ec4899', 
        secondary: '#be185d', 
        accent: '#06b6d4',
        nav: '#831843',
        heroStyle: 'wave',
        aboutLayout: 'team',
        buttonStyle: 'pill'
    },
    { 
        id: 'corporate', 
        name: 'Corporate', 
        desc: 'Business formal', 
        primary: '#0f172a', 
        secondary: '#1e293b', 
        accent: '#3b82f6',
        nav: '#0f172a',
        heroStyle: 'split',
        aboutLayout: 'stats',
        buttonStyle: 'square'
    },
    { 
        id: 'luxury', 
        name: 'Luxury', 
        desc: 'Premium & exclusive', 
        primary: '#78350f', 
        secondary: '#451a03', 
        accent: '#d97706',
        nav: '#1c1917',
        heroStyle: 'image',
        aboutLayout: 'story',
        buttonStyle: 'pill'
    },
    { 
        id: 'modern', 
        name: 'Modern', 
        desc: 'Contemporary style', 
        primary: '#0891b2', 
        secondary: '#0e7490', 
        accent: '#f43f5e',
        nav: '#164e63',
        heroStyle: 'diagonal',
        aboutLayout: 'team',
        buttonStyle: 'rounded'
    },
];

const applyTemplate = (template: typeof templatePresets[0]) => {
    form.theme_settings.template = template.id as any;
    form.theme_settings.primary_color = template.primary;
    form.theme_settings.secondary_color = template.secondary;
    form.theme_settings.accent_color = template.accent;
    form.theme_settings.nav_color = template.nav;
    form.theme_settings.hero_style = template.heroStyle as any;
    form.theme_settings.about_layout = template.aboutLayout as any;
    form.theme_settings.button_style = template.buttonStyle as any;
};

const yearsInBusiness = computed(() => {
    if (!form.founding_year) return null;
    return new Date().getFullYear() - form.founding_year;
});

const daysOfWeek = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
</script>

<template>
    <Head title="Mini-Website Builder - BizBoost" />
    <BizBoostLayout title="Mini-Website Builder">
        <div class="flex flex-col lg:flex-row gap-6 h-[calc(100vh-8rem)]">
            <!-- Editor Panel -->
            <div class="w-full lg:w-[440px] flex flex-col overflow-hidden flex-shrink-0">
                <!-- Status Card -->
                <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-4 mb-4 flex-shrink-0">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div :class="[
                                'p-2.5 rounded-xl',
                                business.profile?.is_published ? 'bg-emerald-100' : 'bg-gray-100'
                            ]">
                                <GlobeAltIcon :class="[
                                    'h-5 w-5',
                                    business.profile?.is_published ? 'text-emerald-600' : 'text-gray-400'
                                ]" aria-hidden="true" />
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900">
                                    {{ business.profile?.is_published ? 'Site is Live' : 'Not Published' }}
                                </h3>
                                <p class="text-xs text-gray-500 truncate max-w-[180px]">
                                    {{ business.profile?.is_published ? publicUrl : 'Publish to go live' }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <template v-if="business.profile?.is_published">
                                <button
                                    @click="copyUrl"
                                    class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                                    :aria-label="copied ? 'Copied!' : 'Copy URL'"
                                >
                                    <CheckIcon v-if="copied" class="h-4 w-4 text-emerald-600" aria-hidden="true" />
                                    <ClipboardDocumentIcon v-else class="h-4 w-4" aria-hidden="true" />
                                </button>
                                <button
                                    @click="previewSite"
                                    class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                                    aria-label="Open site"
                                >
                                    <ArrowTopRightOnSquareIcon class="h-4 w-4" aria-hidden="true" />
                                </button>
                            </template>
                            <button
                                @click="togglePublish"
                                :class="[
                                    'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                                    business.profile?.is_published
                                        ? 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                        : 'bg-violet-600 text-white hover:bg-violet-700'
                                ]"
                            >
                                {{ business.profile?.is_published ? 'Unpublish' : 'Publish' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 flex-1 flex flex-col overflow-hidden">
                    <div class="flex border-b border-gray-200 flex-shrink-0 overflow-x-auto">
                        <button
                            v-for="tab in [
                                { id: 'content', label: 'Content', icon: DocumentTextIcon },
                                { id: 'about', label: 'About', icon: BuildingOfficeIcon },
                                { id: 'design', label: 'Design', icon: PaintBrushIcon },
                                { id: 'settings', label: 'Settings', icon: Cog6ToothIcon },
                            ]"
                            :key="tab.id"
                            type="button"
                            @click.prevent="activeTab = tab.id as any"
                            :class="[
                                'flex-1 flex items-center justify-center gap-1.5 px-3 py-3 text-sm font-medium transition-colors whitespace-nowrap',
                                activeTab === tab.id
                                    ? 'text-violet-600 border-b-2 border-violet-600 bg-violet-50/50'
                                    : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'
                            ]"
                        >
                            <component :is="tab.icon" class="h-4 w-4" aria-hidden="true" />
                            {{ tab.label }}
                        </button>
                    </div>

                    <!-- Tab Content -->
                    <form @submit.prevent="submit" class="flex-1 overflow-y-auto p-5 space-y-5">
                        <!-- Content Tab -->
                        <template v-if="activeTab === 'content'">
                            <!-- Hero Image -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Hero Image</label>
                                <div class="relative">
                                    <div 
                                        v-if="heroImagePreview"
                                        class="relative h-32 rounded-lg overflow-hidden bg-gray-100"
                                    >
                                        <img :src="heroImagePreview" class="w-full h-full object-cover" alt="Hero preview" />
                                        <button
                                            type="button"
                                            @click="heroImagePreview = null; form.hero_image = null"
                                            class="absolute top-2 right-2 p-1.5 bg-black/50 rounded-full text-white hover:bg-black/70 transition-colors"
                                            aria-label="Remove image"
                                        >
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                    <label 
                                        v-else
                                        class="flex flex-col items-center justify-center h-32 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-violet-400 hover:bg-violet-50/50 transition-colors"
                                    >
                                        <PhotoIcon class="h-8 w-8 text-gray-400" aria-hidden="true" />
                                        <span class="mt-2 text-sm text-gray-500">Upload hero image</span>
                                        <span class="text-xs text-gray-400">1920x600 recommended</span>
                                        <input type="file" class="hidden" accept="image/*" @change="handleHeroImageChange" />
                                    </label>
                                </div>
                            </div>

                            <!-- Tagline -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tagline</label>
                                <input
                                    v-model="form.tagline"
                                    type="text"
                                    class="w-full rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500 text-sm"
                                    placeholder="Your catchy business slogan"
                                    maxlength="120"
                                />
                                <p class="mt-1 text-xs text-gray-400">{{ form.tagline.length }}/120 characters</p>
                            </div>

                            <!-- About (Short) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Short Description</label>
                                <textarea
                                    v-model="form.about"
                                    rows="3"
                                    class="w-full rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500 text-sm"
                                    placeholder="Brief intro shown in the About section"
                                    maxlength="500"
                                ></textarea>
                                <p class="mt-1 text-xs text-gray-400">{{ form.about.length }}/500 characters</p>
                            </div>

                            <!-- Contact Email -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Contact Email</label>
                                <input
                                    v-model="form.contact_email"
                                    type="email"
                                    class="w-full rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500 text-sm"
                                    placeholder="contact@yourbusiness.com"
                                />
                            </div>

                            <!-- Services Section -->
                            <div class="border-t border-gray-200 pt-5">
                                <div class="flex items-center justify-between mb-3">
                                    <label class="text-sm font-medium text-gray-700">Services</label>
                                    <button
                                        type="button"
                                        @click="addService"
                                        class="text-xs text-violet-600 hover:text-violet-700 flex items-center gap-1"
                                    >
                                        <PlusIcon class="h-3.5 w-3.5" aria-hidden="true" />
                                        Add Service
                                    </button>
                                </div>
                                <div v-if="form.services.length === 0" class="text-center py-4 bg-gray-50 rounded-lg">
                                    <WrenchScrewdriverIcon class="h-8 w-8 text-gray-300 mx-auto mb-2" aria-hidden="true" />
                                    <p class="text-sm text-gray-500">No services added yet</p>
                                </div>
                                <div v-else class="space-y-3">
                                    <div 
                                        v-for="(service, index) in form.services" 
                                        :key="index"
                                        class="p-3 bg-gray-50 rounded-lg space-y-2"
                                    >
                                        <div class="flex items-center gap-2">
                                            <input
                                                v-model="service.name"
                                                type="text"
                                                class="flex-1 rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500 text-sm"
                                                placeholder="Service name"
                                            />
                                            <input
                                                v-model="service.price"
                                                type="text"
                                                class="w-24 rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500 text-sm"
                                                placeholder="K100"
                                            />
                                            <button
                                                type="button"
                                                @click="removeService(index)"
                                                class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg"
                                                aria-label="Remove service"
                                            >
                                                <TrashIcon class="h-4 w-4" aria-hidden="true" />
                                            </button>
                                        </div>
                                        <textarea
                                            v-model="service.description"
                                            rows="2"
                                            class="w-full rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500 text-sm"
                                            placeholder="Brief description"
                                        ></textarea>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- About Tab (Enhanced) -->
                        <template v-else-if="activeTab === 'about'">
                            <!-- About Section Image -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">About Section Image</label>
                                <div class="relative">
                                    <div 
                                        v-if="aboutImagePreview"
                                        class="relative h-32 rounded-lg overflow-hidden bg-gray-100"
                                    >
                                        <img :src="aboutImagePreview" class="w-full h-full object-cover" alt="About section preview" />
                                        <button
                                            type="button"
                                            @click="aboutImagePreview = null; form.about_image = null"
                                            class="absolute top-2 right-2 p-1.5 bg-black/50 rounded-full text-white hover:bg-black/70 transition-colors"
                                            aria-label="Remove about image"
                                        >
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                    <label 
                                        v-else
                                        class="flex flex-col items-center justify-center h-32 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-violet-400 hover:bg-violet-50/50 transition-colors"
                                    >
                                        <PhotoIcon class="h-8 w-8 text-gray-400" aria-hidden="true" />
                                        <span class="mt-2 text-sm text-gray-500">Upload about image</span>
                                        <span class="text-xs text-gray-400">800x600 recommended</span>
                                        <input type="file" class="hidden" accept="image/*" @change="handleAboutImageChange" />
                                    </label>
                                </div>
                                <p class="mt-1 text-xs text-gray-400">This image appears alongside your business story</p>
                            </div>

                            <!-- Business Story -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    <span class="flex items-center gap-2">
                                        <SparklesIcon class="h-4 w-4 text-violet-500" aria-hidden="true" />
                                        Your Business Story
                                    </span>
                                </label>
                                <textarea
                                    v-model="form.business_story"
                                    rows="4"
                                    class="w-full rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500 text-sm"
                                    placeholder="Share your journey. How did your business start? What drives you?"
                                ></textarea>
                            </div>

                            <!-- Mission & Vision -->
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Mission Statement</label>
                                    <input
                                        v-model="form.mission"
                                        type="text"
                                        class="w-full rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500 text-sm"
                                        placeholder="What you do and why"
                                        maxlength="200"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Vision Statement</label>
                                    <input
                                        v-model="form.vision"
                                        type="text"
                                        class="w-full rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500 text-sm"
                                        placeholder="Where you're headed"
                                        maxlength="200"
                                    />
                                </div>
                            </div>

                            <!-- Founding Year -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Year Founded</label>
                                <div class="flex items-center gap-3">
                                    <input
                                        v-model.number="form.founding_year"
                                        type="number"
                                        min="1900"
                                        :max="new Date().getFullYear()"
                                        class="w-32 rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500 text-sm"
                                        placeholder="2020"
                                    />
                                    <span v-if="yearsInBusiness" class="text-sm text-gray-500">
                                        {{ yearsInBusiness }} year{{ yearsInBusiness > 1 ? 's' : '' }} in business
                                    </span>
                                </div>
                            </div>

                            <!-- Business Hours -->
                            <div class="border-t border-gray-200 pt-5">
                                <div class="flex items-center justify-between mb-3">
                                    <label class="text-sm font-medium text-gray-700 flex items-center gap-2">
                                        <ClockIcon class="h-4 w-4 text-blue-500" aria-hidden="true" />
                                        Business Hours
                                    </label>
                                </div>
                                <div class="space-y-2">
                                    <div 
                                        v-for="day in daysOfWeek" 
                                        :key="day"
                                        class="flex items-center gap-2 text-sm"
                                    >
                                        <span class="w-24 capitalize text-gray-600">{{ day }}</span>
                                        <label class="flex items-center gap-1.5">
                                            <input
                                                type="checkbox"
                                                :checked="!form.business_hours[day]?.closed"
                                                @change="form.business_hours[day].closed = !($event.target as HTMLInputElement).checked"
                                                class="rounded border-gray-300 text-violet-600 focus:ring-violet-500"
                                            />
                                            <span class="text-xs text-gray-500">Open</span>
                                        </label>
                                        <template v-if="!form.business_hours[day]?.closed">
                                            <input
                                                v-model="form.business_hours[day].open"
                                                type="time"
                                                class="rounded border-gray-300 focus:border-violet-500 focus:ring-violet-500 text-xs py-1"
                                            />
                                            <span class="text-gray-400">-</span>
                                            <input
                                                v-model="form.business_hours[day].close"
                                                type="time"
                                                class="rounded border-gray-300 focus:border-violet-500 focus:ring-violet-500 text-xs py-1"
                                            />
                                        </template>
                                        <span v-else class="text-gray-400 text-xs">Closed</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Team Members -->
                            <div class="border-t border-gray-200 pt-5">
                                <div class="flex items-center justify-between mb-3">
                                    <label class="text-sm font-medium text-gray-700 flex items-center gap-2">
                                        <UserGroupIcon class="h-4 w-4 text-emerald-500" aria-hidden="true" />
                                        Team Members
                                    </label>
                                    <button
                                        type="button"
                                        @click="addTeamMember"
                                        class="text-xs text-violet-600 hover:text-violet-700 flex items-center gap-1"
                                    >
                                        <PlusIcon class="h-3.5 w-3.5" aria-hidden="true" />
                                        Add Member
                                    </button>
                                </div>
                                <div v-if="form.team_members.length === 0" class="text-center py-4 bg-gray-50 rounded-lg">
                                    <UserGroupIcon class="h-8 w-8 text-gray-300 mx-auto mb-2" aria-hidden="true" />
                                    <p class="text-sm text-gray-500">No team members added</p>
                                </div>
                                <div v-else class="space-y-2">
                                    <div 
                                        v-for="(member, index) in form.team_members" 
                                        :key="index"
                                        class="flex items-center gap-2 p-2 bg-gray-50 rounded-lg"
                                    >
                                        <input
                                            v-model="member.name"
                                            type="text"
                                            class="flex-1 rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500 text-sm"
                                            placeholder="Name"
                                        />
                                        <input
                                            v-model="member.role"
                                            type="text"
                                            class="flex-1 rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500 text-sm"
                                            placeholder="Role"
                                        />
                                        <button
                                            type="button"
                                            @click="removeTeamMember(index)"
                                            class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg"
                                            aria-label="Remove team member"
                                        >
                                            <TrashIcon class="h-4 w-4" aria-hidden="true" />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Achievements -->
                            <div class="border-t border-gray-200 pt-5">
                                <div class="flex items-center justify-between mb-3">
                                    <label class="text-sm font-medium text-gray-700 flex items-center gap-2">
                                        <StarIcon class="h-4 w-4 text-amber-500" aria-hidden="true" />
                                        Achievements & Awards
                                    </label>
                                    <button
                                        type="button"
                                        @click="addAchievement"
                                        class="text-xs text-violet-600 hover:text-violet-700 flex items-center gap-1"
                                    >
                                        <PlusIcon class="h-3.5 w-3.5" aria-hidden="true" />
                                        Add Achievement
                                    </button>
                                </div>
                                <div v-if="form.achievements.length === 0" class="text-center py-4 bg-gray-50 rounded-lg">
                                    <StarIcon class="h-8 w-8 text-gray-300 mx-auto mb-2" aria-hidden="true" />
                                    <p class="text-sm text-gray-500">No achievements added</p>
                                </div>
                                <div v-else class="space-y-2">
                                    <div 
                                        v-for="(achievement, index) in form.achievements" 
                                        :key="index"
                                        class="flex items-center gap-2 p-2 bg-gray-50 rounded-lg"
                                    >
                                        <input
                                            v-model="achievement.title"
                                            type="text"
                                            class="flex-1 rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500 text-sm"
                                            placeholder="Achievement title"
                                        />
                                        <input
                                            v-model.number="achievement.year"
                                            type="number"
                                            class="w-20 rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500 text-sm"
                                            placeholder="Year"
                                        />
                                        <button
                                            type="button"
                                            @click="removeAchievement(index)"
                                            class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg"
                                            aria-label="Remove achievement"
                                        >
                                            <TrashIcon class="h-4 w-4" aria-hidden="true" />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Testimonials -->
                            <div class="border-t border-gray-200 pt-5">
                                <div class="flex items-center justify-between mb-3">
                                    <label class="text-sm font-medium text-gray-700 flex items-center gap-2">
                                        <ChatBubbleLeftRightIcon class="h-4 w-4 text-indigo-500" aria-hidden="true" />
                                        Customer Testimonials
                                    </label>
                                    <button
                                        type="button"
                                        @click="addTestimonial"
                                        class="text-xs text-violet-600 hover:text-violet-700 flex items-center gap-1"
                                    >
                                        <PlusIcon class="h-3.5 w-3.5" aria-hidden="true" />
                                        Add Testimonial
                                    </button>
                                </div>
                                <div v-if="form.testimonials.length === 0" class="text-center py-4 bg-gray-50 rounded-lg">
                                    <ChatBubbleLeftRightIcon class="h-8 w-8 text-gray-300 mx-auto mb-2" aria-hidden="true" />
                                    <p class="text-sm text-gray-500">No testimonials added</p>
                                </div>
                                <div v-else class="space-y-3">
                                    <div 
                                        v-for="(testimonial, index) in form.testimonials" 
                                        :key="index"
                                        class="p-3 bg-gray-50 rounded-lg space-y-2"
                                    >
                                        <div class="flex items-center gap-2">
                                            <input
                                                v-model="testimonial.name"
                                                type="text"
                                                class="flex-1 rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500 text-sm"
                                                placeholder="Customer name"
                                            />
                                            <select
                                                v-model.number="testimonial.rating"
                                                class="rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500 text-sm"
                                            >
                                                <option :value="5">⭐⭐⭐⭐⭐</option>
                                                <option :value="4">⭐⭐⭐⭐</option>
                                                <option :value="3">⭐⭐⭐</option>
                                                <option :value="2">⭐⭐</option>
                                                <option :value="1">⭐</option>
                                            </select>
                                            <button
                                                type="button"
                                                @click="removeTestimonial(index)"
                                                class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg"
                                                aria-label="Remove testimonial"
                                            >
                                                <TrashIcon class="h-4 w-4" aria-hidden="true" />
                                            </button>
                                        </div>
                                        <textarea
                                            v-model="testimonial.content"
                                            rows="2"
                                            class="w-full rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500 text-sm"
                                            placeholder="What did they say about your business?"
                                        ></textarea>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Design Tab -->
                        <template v-else-if="activeTab === 'design'">
                            <!-- Template Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">Choose Template</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <button
                                        v-for="template in templatePresets"
                                        :key="template.id"
                                        type="button"
                                        @click.prevent="applyTemplate(template)"
                                        :class="[
                                            'flex flex-col items-start gap-2 p-3 border rounded-xl transition-all text-left',
                                            form.theme_settings.template === template.id
                                                ? 'border-violet-500 bg-violet-50 ring-2 ring-violet-500/20'
                                                : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'
                                        ]"
                                    >
                                        <div class="flex items-center gap-2 w-full">
                                            <div class="flex -space-x-1">
                                                <div class="w-4 h-4 rounded-full ring-1 ring-white shadow-sm" :style="{ backgroundColor: template.primary }"></div>
                                                <div class="w-4 h-4 rounded-full ring-1 ring-white shadow-sm" :style="{ backgroundColor: template.secondary }"></div>
                                                <div class="w-4 h-4 rounded-full ring-1 ring-white shadow-sm" :style="{ backgroundColor: template.accent }"></div>
                                            </div>
                                            <CheckIcon 
                                                v-if="form.theme_settings.template === template.id" 
                                                class="h-4 w-4 text-violet-600 ml-auto" 
                                                aria-hidden="true" 
                                            />
                                        </div>
                                        <div>
                                            <span class="text-sm font-medium text-gray-900">{{ template.name }}</span>
                                            <p class="text-xs text-gray-500">{{ template.desc }}</p>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <!-- Custom Colors -->
                            <div class="border-t border-gray-200 pt-5">
                                <label class="block text-sm font-medium text-gray-700 mb-3">Custom Colors</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Primary</label>
                                        <input
                                            v-model="form.theme_settings.primary_color"
                                            type="color"
                                            class="h-9 w-full rounded-lg border-gray-300 cursor-pointer"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Secondary</label>
                                        <input
                                            v-model="form.theme_settings.secondary_color"
                                            type="color"
                                            class="h-9 w-full rounded-lg border-gray-300 cursor-pointer"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Accent</label>
                                        <input
                                            v-model="form.theme_settings.accent_color"
                                            type="color"
                                            class="h-9 w-full rounded-lg border-gray-300 cursor-pointer"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Navigation Bar</label>
                                        <input
                                            v-model="form.theme_settings.nav_color"
                                            type="color"
                                            class="h-9 w-full rounded-lg border-gray-300 cursor-pointer"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Hero Style -->
                            <div class="border-t border-gray-200 pt-5">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Hero Style</label>
                                <div class="grid grid-cols-3 gap-2">
                                    <button
                                        v-for="style in [
                                            { id: 'gradient', name: 'Gradient', icon: '🌈' },
                                            { id: 'image', name: 'Image', icon: '🖼️' },
                                            { id: 'split', name: 'Split', icon: '◧' },
                                            { id: 'wave', name: 'Wave', icon: '🌊' },
                                            { id: 'diagonal', name: 'Diagonal', icon: '◢' },
                                        ]"
                                        :key="style.id"
                                        type="button"
                                        @click.prevent="form.theme_settings.hero_style = style.id as any"
                                        :class="[
                                            'p-2.5 border rounded-lg transition-all text-center',
                                            form.theme_settings.hero_style === style.id
                                                ? 'border-violet-500 bg-violet-50'
                                                : 'border-gray-200 hover:border-gray-300'
                                        ]"
                                    >
                                        <span class="text-base">{{ style.icon }}</span>
                                        <p class="text-xs font-medium text-gray-700 mt-0.5">{{ style.name }}</p>
                                    </button>
                                </div>
                            </div>

                            <!-- About Section Layout -->
                            <div class="border-t border-gray-200 pt-5">
                                <label class="block text-sm font-medium text-gray-700 mb-2">About Section Layout</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <button
                                        v-for="layout in [
                                            { id: 'story', name: 'Story Focus', desc: 'Narrative style' },
                                            { id: 'stats', name: 'Stats Focus', desc: 'Numbers & facts' },
                                            { id: 'team', name: 'Team Focus', desc: 'People first' },
                                            { id: 'minimal', name: 'Minimal', desc: 'Clean & simple' },
                                        ]"
                                        :key="layout.id"
                                        type="button"
                                        @click.prevent="form.theme_settings.about_layout = layout.id as any"
                                        :class="[
                                            'p-3 border rounded-lg transition-all text-left',
                                            form.theme_settings.about_layout === layout.id
                                                ? 'border-violet-500 bg-violet-50'
                                                : 'border-gray-200 hover:border-gray-300'
                                        ]"
                                    >
                                        <span class="text-sm font-medium text-gray-900">{{ layout.name }}</span>
                                        <p class="text-xs text-gray-500">{{ layout.desc }}</p>
                                    </button>
                                </div>
                            </div>

                            <!-- Button Style -->
                            <div class="border-t border-gray-200 pt-5">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Button Style</label>
                                <div class="grid grid-cols-3 gap-2">
                                    <button
                                        v-for="style in [
                                            { id: 'rounded', name: 'Rounded' },
                                            { id: 'pill', name: 'Pill' },
                                            { id: 'square', name: 'Square' },
                                        ]"
                                        :key="style.id"
                                        type="button"
                                        @click.prevent="form.theme_settings.button_style = style.id as any"
                                        :class="[
                                            'p-2.5 border rounded-lg transition-all text-center text-sm font-medium',
                                            form.theme_settings.button_style === style.id
                                                ? 'border-violet-500 bg-violet-50 text-violet-700'
                                                : 'border-gray-200 hover:border-gray-300 text-gray-600'
                                        ]"
                                    >
                                        {{ style.name }}
                                    </button>
                                </div>
                            </div>

                            <!-- Font Style -->
                            <div class="border-t border-gray-200 pt-5">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Font Style</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <button
                                        v-for="font in [
                                            { id: 'default', name: 'Default', sample: 'Aa' },
                                            { id: 'elegant', name: 'Elegant', sample: 'Aa' },
                                            { id: 'modern', name: 'Modern', sample: 'Aa' },
                                            { id: 'playful', name: 'Playful', sample: 'Aa' },
                                        ]"
                                        :key="font.id"
                                        type="button"
                                        @click.prevent="form.theme_settings.font_style = font.id as any"
                                        :class="[
                                            'p-3 border rounded-lg transition-all text-center',
                                            form.theme_settings.font_style === font.id
                                                ? 'border-violet-500 bg-violet-50'
                                                : 'border-gray-200 hover:border-gray-300'
                                        ]"
                                    >
                                        <span 
                                            class="text-2xl block mb-1"
                                            :class="{
                                                'font-sans': font.id === 'default' || font.id === 'modern',
                                                'font-serif': font.id === 'elegant',
                                            }"
                                        >{{ font.sample }}</span>
                                        <span class="text-xs font-medium text-gray-700">{{ font.name }}</span>
                                    </button>
                                </div>
                            </div>
                        </template>

                        <!-- Settings Tab -->
                        <template v-else-if="activeTab === 'settings'">
                            <div class="space-y-3">
                                <h4 class="text-sm font-semibold text-gray-900">Display Sections</h4>
                                
                                <label class="flex items-center justify-between p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <ShoppingBagIcon class="h-5 w-5 text-violet-500" aria-hidden="true" />
                                        <div>
                                            <span class="text-sm font-medium text-gray-900">Products Section</span>
                                            <p class="text-xs text-gray-500">Show your products catalog</p>
                                        </div>
                                    </div>
                                    <input
                                        v-model="form.show_products"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-violet-600 focus:ring-violet-500 h-5 w-5"
                                    />
                                </label>

                                <label class="flex items-center justify-between p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <WrenchScrewdriverIcon class="h-5 w-5 text-cyan-500" aria-hidden="true" />
                                        <div>
                                            <span class="text-sm font-medium text-gray-900">Services Section</span>
                                            <p class="text-xs text-gray-500">Display your services</p>
                                        </div>
                                    </div>
                                    <input
                                        v-model="form.show_services"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-violet-600 focus:ring-violet-500 h-5 w-5"
                                    />
                                </label>

                                <label class="flex items-center justify-between p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <ClockIcon class="h-5 w-5 text-blue-500" aria-hidden="true" />
                                        <div>
                                            <span class="text-sm font-medium text-gray-900">Business Hours</span>
                                            <p class="text-xs text-gray-500">Show opening hours</p>
                                        </div>
                                    </div>
                                    <input
                                        v-model="form.show_business_hours"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-violet-600 focus:ring-violet-500 h-5 w-5"
                                    />
                                </label>

                                <label class="flex items-center justify-between p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <ChatBubbleLeftRightIcon class="h-5 w-5 text-indigo-500" aria-hidden="true" />
                                        <div>
                                            <span class="text-sm font-medium text-gray-900">Testimonials</span>
                                            <p class="text-xs text-gray-500">Customer reviews</p>
                                        </div>
                                    </div>
                                    <input
                                        v-model="form.show_testimonials"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-violet-600 focus:ring-violet-500 h-5 w-5"
                                    />
                                </label>

                                <label class="flex items-center justify-between p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <EnvelopeIcon class="h-5 w-5 text-emerald-500" aria-hidden="true" />
                                        <div>
                                            <span class="text-sm font-medium text-gray-900">Contact Form</span>
                                            <p class="text-xs text-gray-500">Let visitors send messages</p>
                                        </div>
                                    </div>
                                    <input
                                        v-model="form.show_contact_form"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-violet-600 focus:ring-violet-500 h-5 w-5"
                                    />
                                </label>

                                <label class="flex items-center justify-between p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                        </svg>
                                        <div>
                                            <span class="text-sm font-medium text-gray-900">WhatsApp Button</span>
                                            <p class="text-xs text-gray-500">Quick chat via WhatsApp</p>
                                        </div>
                                    </div>
                                    <input
                                        v-model="form.show_whatsapp_button"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-violet-600 focus:ring-violet-500 h-5 w-5"
                                    />
                                </label>

                                <label class="flex items-center justify-between p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <GlobeAltIcon class="h-5 w-5 text-pink-500" aria-hidden="true" />
                                        <div>
                                            <span class="text-sm font-medium text-gray-900">Social Media Links</span>
                                            <p class="text-xs text-gray-500">Facebook, Instagram, etc.</p>
                                        </div>
                                    </div>
                                    <input
                                        v-model="form.show_social_links"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-violet-600 focus:ring-violet-500 h-5 w-5"
                                    />
                                </label>
                            </div>
                        </template>

                        <!-- Save Button -->
                        <div class="pt-4 border-t border-gray-200">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full px-4 py-2.5 bg-violet-600 text-white rounded-lg font-medium hover:bg-violet-700 disabled:opacity-50 transition-colors flex items-center justify-center gap-2"
                            >
                                <svg v-if="form.processing" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ form.processing ? 'Saving...' : 'Save Changes' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Preview Panel -->
            <div class="hidden lg:flex flex-1 flex-col overflow-hidden">
                <!-- Preview Header -->
                <div class="bg-white rounded-t-xl shadow-sm ring-1 ring-gray-200 px-4 py-3 flex items-center justify-between flex-shrink-0">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-700">Preview</span>
                    </div>
                    <div class="flex items-center gap-1 bg-gray-100 rounded-lg p-1">
                        <button
                            @click="previewMode = 'desktop'"
                            :class="[
                                'p-1.5 rounded-md transition-colors',
                                previewMode === 'desktop' ? 'bg-white shadow-sm text-violet-600' : 'text-gray-500 hover:text-gray-700'
                            ]"
                            aria-label="Desktop preview"
                        >
                            <ComputerDesktopIcon class="h-4 w-4" aria-hidden="true" />
                        </button>
                        <button
                            @click="previewMode = 'mobile'"
                            :class="[
                                'p-1.5 rounded-md transition-colors',
                                previewMode === 'mobile' ? 'bg-white shadow-sm text-violet-600' : 'text-gray-500 hover:text-gray-700'
                            ]"
                            aria-label="Mobile preview"
                        >
                            <DevicePhoneMobileIcon class="h-4 w-4" aria-hidden="true" />
                        </button>
                    </div>
                </div>

                <!-- Preview Content -->
                <div class="flex-1 bg-gray-100 rounded-b-xl overflow-hidden flex items-start justify-center p-4">
                    <div 
                        :class="[
                            'bg-white shadow-xl overflow-hidden transition-all duration-300',
                            previewMode === 'mobile' 
                                ? 'w-[375px] rounded-[2rem] ring-8 ring-gray-800' 
                                : 'w-full h-full rounded-lg'
                        ]"
                    >
                        <div class="h-full overflow-y-auto">
                            <MiniWebsitePreview
                                :business-name="business.name"
                                :business-slug="business.slug"
                                :industry="business.industry"
                                :logo-path="business.logo_path"
                                :phone="business.phone"
                                :whatsapp="business.whatsapp"
                                :email="business.email"
                                :address="business.address"
                                :city="business.city"
                                :tagline="form.tagline"
                                :about="form.about"
                                :business-story="form.business_story"
                                :mission="form.mission"
                                :vision="form.vision"
                                :founding-year="form.founding_year"
                                :business-hours="form.business_hours"
                                :team-members="form.team_members"
                                :achievements="form.achievements"
                                :services="form.services"
                                :testimonials="form.testimonials"
                                :contact-email="form.contact_email"
                                :hero-image-path="business.profile?.hero_image_path || null"
                                :hero-image-preview-url="heroImagePreview"
                                :about-image-path="business.profile?.about_image_path || null"
                                :about-image-preview-url="aboutImagePreview"
                                :show-products="form.show_products"
                                :show-services="form.show_services"
                                :show-business-hours="form.show_business_hours"
                                :show-testimonials="form.show_testimonials"
                                :show-contact-form="form.show_contact_form"
                                :show-whatsapp-button="form.show_whatsapp_button"
                                :show-social-links="form.show_social_links"
                                :theme-settings="form.theme_settings"
                                :products="products"
                                :is-preview="true"
                                :preview-mode="previewMode"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
