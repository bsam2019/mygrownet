<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    ArrowLeftIcon,
    GlobeAltIcon,
    PaintBrushIcon,
    MagnifyingGlassIcon,
    ShareIcon,
    TrashIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    UsersIcon,
    ChevronRightIcon,
    SparklesIcon,
    PhotoIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';
import { ref, computed } from 'vue';
import axios from 'axios';

interface Site {
    id: number;
    name: string;
    subdomain: string;
    customDomain: string | null;
    description: string | null;
    status: string;
    plan: string;
    logo: string | null;
    favicon: string | null;
    theme: {
        primaryColor: string;
        secondaryColor: string;
        accentColor: string;
        backgroundColor: string;
        textColor: string;
        headingFont: string;
        bodyFont: string;
        borderRadius: number;
    } | null;
    seoSettings: {
        metaTitle: string;
        metaDescription: string;
        ogImage: string;
        googleAnalyticsId: string;
    } | null;
    socialLinks: {
        facebook: string;
        instagram: string;
        twitter: string;
        whatsapp: string;
        linkedin: string;
    } | null;
    contactInfo: {
        phone: string;
        email: string;
        address: string;
    } | null;
    settings?: {
        navigation?: {
            logo?: string;
        };
        splash?: {
            enabled?: boolean;
            style?: 'none' | 'minimal' | 'pulse' | 'wave' | 'gradient' | 'particles' | 'elegant';
            tagline?: string;
        };
    } | null;
}

const props = defineProps<{
    site: Site;
}>();

const activeTab = ref('general');
const showDeleteModal = ref(false);
const deleteConfirmation = ref('');
const isDeleting = ref(false);

// Use site.logo or fallback to settings.navigation.logo
const initialLogo = props.site.logo || props.site.settings?.navigation?.logo || '';

const form = useForm({
    name: props.site.name,
    subdomain: props.site.subdomain,
    custom_domain: props.site.customDomain || '',
    description: props.site.description || '',
    logo: initialLogo,
    favicon: props.site.favicon || '',
    // Theme
    primary_color: props.site.theme?.primaryColor || '#2563eb',
    secondary_color: props.site.theme?.secondaryColor || '#64748b',
    accent_color: props.site.theme?.accentColor || '#059669',
    background_color: props.site.theme?.backgroundColor || '#ffffff',
    text_color: props.site.theme?.textColor || '#1f2937',
    heading_font: props.site.theme?.headingFont || 'Inter',
    body_font: props.site.theme?.bodyFont || 'Inter',
    border_radius: props.site.theme?.borderRadius || 8,
    // Splash Screen
    splash_enabled: props.site.settings?.splash?.enabled !== false,
    splash_style: props.site.settings?.splash?.style || 'minimal',
    splash_tagline: props.site.settings?.splash?.tagline || '',
    // SEO
    meta_title: props.site.seoSettings?.metaTitle || '',
    meta_description: props.site.seoSettings?.metaDescription || '',
    og_image: props.site.seoSettings?.ogImage || '',
    google_analytics_id: props.site.seoSettings?.googleAnalyticsId || '',
    // Social
    facebook: props.site.socialLinks?.facebook || '',
    instagram: props.site.socialLinks?.instagram || '',
    twitter: props.site.socialLinks?.twitter || '',
    whatsapp: props.site.socialLinks?.whatsapp || '',
    linkedin: props.site.socialLinks?.linkedin || '',
    // Contact
    phone: props.site.contactInfo?.phone || '',
    email: props.site.contactInfo?.email || '',
    address: props.site.contactInfo?.address || '',
});

// Splash screen style options
const splashStyles = [
    { id: 'none', name: 'None', description: 'No splash screen' },
    { id: 'minimal', name: 'Minimal', description: 'Clean fade with bouncing logo' },
    { id: 'pulse', name: 'Pulse', description: 'Pulsing rings around logo' },
    { id: 'wave', name: 'Wave', description: 'Animated wave background' },
    { id: 'gradient', name: 'Gradient', description: 'Animated gradient with floating logo' },
    { id: 'particles', name: 'Particles', description: 'Floating particles effect' },
    { id: 'elegant', name: 'Elegant', description: 'Sophisticated with subtle animations' },
];

const fonts = [
    'Inter', 'Roboto', 'Open Sans', 'Lato', 'Montserrat', 'Poppins',
    'Playfair Display', 'Merriweather', 'Source Sans Pro', 'Nunito',
];

const tabs = [
    { id: 'general', name: 'General', icon: GlobeAltIcon },
    { id: 'theme', name: 'Theme', icon: PaintBrushIcon },
    { id: 'splash', name: 'Splash Screen', icon: SparklesIcon },
    { id: 'seo', name: 'SEO', icon: MagnifyingGlassIcon },
    { id: 'social', name: 'Social & Contact', icon: ShareIcon },
];

const submit = () => {
    form.put(route('growbuilder.sites.update', props.site.id));
};

const canDelete = computed(() => deleteConfirmation.value === props.site.subdomain);

const deleteSite = () => {
    if (!canDelete.value) return;
    isDeleting.value = true;
    router.delete(route('growbuilder.sites.destroy', props.site.id), {
        onFinish: () => {
            isDeleting.value = false;
        },
    });
};

const openDeleteModal = () => {
    deleteConfirmation.value = '';
    showDeleteModal.value = true;
};

// AI Helper State
const aiLoading = ref<string | null>(null);
const aiError = ref<string | null>(null);

// AI Generate Description
const generateDescription = async () => {
    if (!form.name) {
        aiError.value = 'Please enter a site name first';
        return;
    }
    aiLoading.value = 'description';
    aiError.value = null;
    try {
        // Use the improve-text endpoint for simple text generation
        const response = await axios.post(route('growbuilder.ai.improve-text', props.site.id), {
            text: form.description || `Website for ${form.name}`,
            style: 'professional',
            instruction: `Generate a compelling 2-3 sentence website description for a business called "${form.name}". Make it professional, engaging, and suitable for a business website. Keep it under 200 characters.`,
        });
        if (response.data.improved_text) {
            form.description = response.data.improved_text;
        }
    } catch (error: any) {
        aiError.value = error.response?.data?.message || 'Failed to generate description';
    } finally {
        aiLoading.value = null;
    }
};

// AI Generate SEO
const generateSEO = async () => {
    aiLoading.value = 'seo';
    aiError.value = null;
    try {
        const response = await axios.post(route('growbuilder.ai.generate-meta', props.site.id), {
            page_title: form.name,
            page_content: form.description || form.name,
        });
        if (response.data.meta_description) {
            form.meta_title = form.name;
            form.meta_description = response.data.meta_description;
        }
    } catch (error: any) {
        aiError.value = error.response?.data?.message || 'Failed to generate SEO content';
    } finally {
        aiLoading.value = null;
    }
};

// AI Suggest Colors
const suggestColors = async () => {
    aiLoading.value = 'colors';
    aiError.value = null;
    try {
        const response = await axios.post(route('growbuilder.ai.suggest-colors', props.site.id), {
            business_type: form.name,
        });
        if (response.data.palette) {
            const colors = response.data.palette;
            if (colors.primary) form.primary_color = colors.primary;
            if (colors.secondary) form.secondary_color = colors.secondary;
            if (colors.accent) form.accent_color = colors.accent;
            if (colors.background) form.background_color = colors.background;
            if (colors.text) form.text_color = colors.text;
        }
    } catch (error: any) {
        aiError.value = error.response?.data?.message || 'Failed to suggest colors';
    } finally {
        aiLoading.value = null;
    }
};

// Logo & Favicon Upload
const logoInput = ref<HTMLInputElement | null>(null);
const uploadingLogo = ref(false);
const generatingFavicon = ref(false);

const triggerLogoUpload = () => {
    logoInput.value?.click();
};

const handleLogoUpload = async (event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) return;
    
    uploadingLogo.value = true;
    aiError.value = null;
    
    try {
        const formData = new FormData();
        formData.append('file', file);
        
        const response = await axios.post(route('growbuilder.media.store', props.site.id), formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        
        if (response.data.media?.url) {
            form.logo = response.data.media.url;
            // Auto-generate favicons from the new logo
            await autoGenerateFavicon(response.data.media.url);
        }
    } catch (error: any) {
        aiError.value = error.response?.data?.message || 'Failed to upload logo';
    } finally {
        uploadingLogo.value = false;
        if (input) input.value = '';
    }
};

const autoGenerateFavicon = async (logoUrl: string) => {
    generatingFavicon.value = true;
    try {
        const response = await axios.post(route('growbuilder.media.generate-favicon', props.site.id), {
            logoUrl: logoUrl
        });
        if (response.data.faviconUrl) {
            form.favicon = response.data.faviconUrl;
        }
    } catch (error) {
        // Silently fail - favicon generation is optional
        console.warn('Auto favicon generation failed:', error);
    } finally {
        generatingFavicon.value = false;
    }
};

const generateFaviconFromLogo = async () => {
    if (!form.logo) {
        aiError.value = 'Please upload a logo first';
        return;
    }
    
    generatingFavicon.value = true;
    aiError.value = null;
    
    try {
        const response = await axios.post(route('growbuilder.media.generate-favicon', props.site.id), {
            logoUrl: form.logo
        });
        
        if (response.data.faviconUrl) {
            form.favicon = response.data.faviconUrl;
        }
    } catch (error: any) {
        aiError.value = error.response?.data?.message || 'Failed to generate favicon';
    } finally {
        generatingFavicon.value = false;
    }
};

const clearLogo = () => {
    form.logo = '';
};

const clearFavicon = () => {
    form.favicon = '';
};

// Splash Preview
const previewKey = ref(0);

const restartPreview = () => {
    previewKey.value++;
};

// Helper to darken/lighten a hex color
const adjustColor = (hex: string, amount: number): string => {
    const color = hex.replace('#', '');
    const num = parseInt(color, 16);
    let r = (num >> 16) + amount;
    let g = ((num >> 8) & 0x00FF) + amount;
    let b = (num & 0x0000FF) + amount;
    r = Math.max(0, Math.min(255, r));
    g = Math.max(0, Math.min(255, g));
    b = Math.max(0, Math.min(255, b));
    return '#' + ((r << 16) | (g << 8) | b).toString(16).padStart(6, '0');
};
</script>

<template>
    <AppLayout>
        <Head :title="`Settings - ${site.name}`" />

        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        :href="route('growbuilder.index')"
                        class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-4"
                    >
                        <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                        Back to Dashboard
                    </Link>

                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Site Settings</h1>
                            <p class="text-sm text-gray-500">{{ site.name }} • {{ site.subdomain }}.mygrownet.com</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <Link
                                :href="route('growbuilder.sites.users', site.id)"
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                            >
                                <UsersIcon class="h-4 w-4" aria-hidden="true" />
                                Manage Users
                            </Link>
                            <span :class="[
                                'px-3 py-1 text-sm font-medium rounded-full',
                                site.status === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                            ]">
                                {{ site.status }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions Card -->
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-6 mb-6 text-white">
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div>
                            <h2 class="text-lg font-semibold">Site Management</h2>
                            <p class="text-blue-100 text-sm mt-1">Manage users, roles, permissions, and payment settings</p>
                        </div>
                        <div class="flex items-center gap-3 flex-wrap">
                            <Link
                                :href="route('growbuilder.payment.config', site.id)"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition-colors"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                                Payment Settings
                                <ChevronRightIcon class="h-4 w-4" aria-hidden="true" />
                            </Link>
                            <Link
                                :href="route('growbuilder.sites.roles', site.id)"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition-colors"
                            >
                                Manage Roles
                                <ChevronRightIcon class="h-4 w-4" aria-hidden="true" />
                            </Link>
                            <Link
                                :href="route('growbuilder.sites.users', site.id)"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition-colors"
                            >
                                Manage Users
                                <ChevronRightIcon class="h-4 w-4" aria-hidden="true" />
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <!-- AI Error Toast -->
                    <div v-if="aiError" class="mx-6 mt-6 p-3 bg-red-50 border border-red-200 rounded-lg flex items-center justify-between">
                        <p class="text-sm text-red-700">{{ aiError }}</p>
                        <button @click="aiError = null" class="text-red-500 hover:text-red-700">×</button>
                    </div>
                    
                    <div class="border-b border-gray-200">
                        <nav class="flex -mb-px">
                            <button
                                v-for="tab in tabs"
                                :key="tab.id"
                                type="button"
                                :class="[
                                    'flex items-center gap-2 px-6 py-4 text-sm font-medium border-b-2 transition',
                                    activeTab === tab.id
                                        ? 'border-blue-600 text-blue-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                ]"
                                @click="activeTab = tab.id"
                            >
                                <component :is="tab.icon" class="h-5 w-5" aria-hidden="true" />
                                {{ tab.name }}
                            </button>
                        </nav>
                    </div>

                    <form @submit.prevent="submit" class="p-6">
                        <!-- General Tab -->
                        <div v-show="activeTab === 'general'" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Site Name *</label>
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        required
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Subdomain *</label>
                                    <div class="flex">
                                        <input
                                            v-model="form.subdomain"
                                            type="text"
                                            required
                                            class="flex-1 px-4 py-2.5 border border-gray-300 rounded-l-xl focus:ring-2 focus:ring-blue-500"
                                        />
                                        <span class="px-4 py-2.5 bg-gray-100 border border-l-0 border-gray-300 rounded-r-xl text-gray-500 text-sm">
                                            .mygrownet.com
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Custom Domain</label>
                                <input
                                    v-model="form.custom_domain"
                                    type="text"
                                    placeholder="www.yourdomain.com"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500"
                                />
                                <p class="mt-1 text-xs text-gray-500">Point your domain's CNAME to {{ site.subdomain }}.mygrownet.com</p>
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-sm font-medium text-gray-700">Description</label>
                                    <button
                                        type="button"
                                        @click="generateDescription"
                                        :disabled="aiLoading === 'description'"
                                        class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50 rounded-lg transition disabled:opacity-50"
                                    >
                                        <SparklesIcon class="h-3.5 w-3.5" :class="{ 'animate-spin': aiLoading === 'description' }" aria-hidden="true" />
                                        {{ aiLoading === 'description' ? 'Generating...' : 'AI Generate' }}
                                    </button>
                                </div>
                                <textarea
                                    v-model="form.description"
                                    rows="3"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500"
                                    placeholder="Brief description of your website..."
                                ></textarea>
                            </div>

                            <!-- Logo & Favicon -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Logo Upload -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Site Logo</label>
                                    <div v-if="form.logo" class="mb-3 relative inline-block">
                                        <img :src="form.logo" alt="Logo" class="h-16 object-contain rounded-lg border border-gray-200 bg-gray-50 p-2" />
                                        <button 
                                            type="button"
                                            @click="clearLogo" 
                                            class="absolute -top-2 -right-2 p-1 bg-red-500 text-white rounded-full hover:bg-red-600 shadow-sm"
                                            aria-label="Remove logo"
                                        >
                                            <XMarkIcon class="w-3 h-3" aria-hidden="true" />
                                        </button>
                                    </div>
                                    <input
                                        ref="logoInput"
                                        type="file"
                                        accept="image/*"
                                        class="hidden"
                                        @change="handleLogoUpload"
                                    />
                                    <button
                                        type="button"
                                        @click="triggerLogoUpload"
                                        :disabled="uploadingLogo"
                                        class="w-full flex items-center justify-center gap-2 px-4 py-3 border-2 border-dashed border-gray-300 rounded-xl text-gray-500 hover:border-blue-400 hover:text-blue-600 transition-colors disabled:opacity-50"
                                    >
                                        <PhotoIcon v-if="!uploadingLogo" class="w-5 h-5" aria-hidden="true" />
                                        <svg v-else class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span class="text-sm">{{ uploadingLogo ? 'Uploading...' : (form.logo ? 'Change Logo' : 'Upload Logo') }}</span>
                                    </button>
                                    <p class="mt-2 text-xs text-gray-500">Recommended: PNG or SVG, at least 200x200px</p>
                                </div>
                                
                                <!-- Favicon Upload -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Favicon</label>
                                    <p class="text-xs text-gray-500 mb-3">Auto-generated from your logo in multiple sizes (16x16, 32x32, 180x180)</p>
                                    <div v-if="form.favicon" class="mb-3 flex items-center gap-3">
                                        <img :src="form.favicon" alt="Favicon" class="h-8 w-8 object-contain rounded border border-gray-200 bg-gray-50" />
                                        <span class="text-sm text-green-600 flex items-center gap-1">
                                            <CheckCircleIcon class="w-4 h-4" aria-hidden="true" />
                                            Favicon generated
                                        </span>
                                        <button 
                                            type="button"
                                            @click="clearFavicon" 
                                            class="text-xs text-red-500 hover:text-red-700"
                                        >
                                            Remove
                                        </button>
                                    </div>
                                    <button
                                        type="button"
                                        @click="generateFaviconFromLogo"
                                        :disabled="!form.logo || generatingFavicon"
                                        class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-100 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <SparklesIcon class="w-5 h-5" :class="{ 'animate-spin': generatingFavicon }" aria-hidden="true" />
                                        <span class="text-sm font-medium">{{ generatingFavicon ? 'Generating...' : (form.favicon ? 'Regenerate from Logo' : 'Generate from Logo') }}</span>
                                    </button>
                                    <p v-if="!form.logo" class="mt-2 text-xs text-amber-600">Upload a logo first to generate favicon</p>
                                </div>
                            </div>
                        </div>

                        <!-- Theme Tab -->
                        <div v-show="activeTab === 'theme'" class="space-y-6">
                            <div>
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-medium text-gray-900">Colors</h3>
                                    <button
                                        type="button"
                                        @click="suggestColors"
                                        :disabled="aiLoading === 'colors'"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition disabled:opacity-50"
                                    >
                                        <SparklesIcon class="h-4 w-4" :class="{ 'animate-spin': aiLoading === 'colors' }" aria-hidden="true" />
                                        {{ aiLoading === 'colors' ? 'Suggesting...' : 'AI Suggest Colors' }}
                                    </button>
                                </div>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Primary</label>
                                        <div class="flex gap-2">
                                            <input
                                                v-model="form.primary_color"
                                                type="color"
                                                class="w-12 h-10 rounded-lg border border-gray-300 cursor-pointer"
                                            />
                                            <input
                                                v-model="form.primary_color"
                                                type="text"
                                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono"
                                            />
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Secondary</label>
                                        <div class="flex gap-2">
                                            <input
                                                v-model="form.secondary_color"
                                                type="color"
                                                class="w-12 h-10 rounded-lg border border-gray-300 cursor-pointer"
                                            />
                                            <input
                                                v-model="form.secondary_color"
                                                type="text"
                                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono"
                                            />
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Accent</label>
                                        <div class="flex gap-2">
                                            <input
                                                v-model="form.accent_color"
                                                type="color"
                                                class="w-12 h-10 rounded-lg border border-gray-300 cursor-pointer"
                                            />
                                            <input
                                                v-model="form.accent_color"
                                                type="text"
                                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono"
                                            />
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Background</label>
                                        <div class="flex gap-2">
                                            <input
                                                v-model="form.background_color"
                                                type="color"
                                                class="w-12 h-10 rounded-lg border border-gray-300 cursor-pointer"
                                            />
                                            <input
                                                v-model="form.background_color"
                                                type="text"
                                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono"
                                            />
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Text</label>
                                        <div class="flex gap-2">
                                            <input
                                                v-model="form.text_color"
                                                type="color"
                                                class="w-12 h-10 rounded-lg border border-gray-300 cursor-pointer"
                                            />
                                            <input
                                                v-model="form.text_color"
                                                type="text"
                                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Typography</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Heading Font</label>
                                        <select
                                            v-model="form.heading_font"
                                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500"
                                        >
                                            <option v-for="font in fonts" :key="font" :value="font">{{ font }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Body Font</label>
                                        <select
                                            v-model="form.body_font"
                                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500"
                                        >
                                            <option v-for="font in fonts" :key="font" :value="font">{{ font }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Border Radius: {{ form.border_radius }}px</label>
                                <input
                                    v-model="form.border_radius"
                                    type="range"
                                    min="0"
                                    max="24"
                                    class="w-full"
                                />
                                <div class="flex justify-between text-xs text-gray-400 mt-1">
                                    <span>Sharp</span>
                                    <span>Rounded</span>
                                </div>
                            </div>

                            <!-- Theme Preview -->
                            <div class="p-6 rounded-xl border border-gray-200" :style="{ backgroundColor: form.background_color }">
                                <h4 :style="{ color: form.text_color, fontFamily: form.heading_font }" class="text-xl font-bold mb-2">Preview Heading</h4>
                                <p :style="{ color: form.text_color, fontFamily: form.body_font }" class="mb-4 opacity-80">This is how your text will look on your website.</p>
                                <button
                                    type="button"
                                    :style="{ backgroundColor: form.primary_color, borderRadius: form.border_radius + 'px' }"
                                    class="px-4 py-2 text-white font-medium"
                                >
                                    Sample Button
                                </button>
                            </div>
                        </div>

                        <!-- Splash Screen Tab -->
                        <div v-show="activeTab === 'splash'" class="space-y-6">
                            <div class="p-4 bg-indigo-50 rounded-xl border border-indigo-100">
                                <div class="flex gap-3">
                                    <SparklesIcon class="h-5 w-5 text-indigo-600 flex-shrink-0 mt-0.5" aria-hidden="true" />
                                    <div>
                                        <h4 class="font-medium text-indigo-900">Loading Splash Screen</h4>
                                        <p class="text-sm text-indigo-700 mt-1">Choose a stylish loading animation that shows while your site loads. Uses your site's logo and primary color.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Enable/Disable -->
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                                <div>
                                    <h4 class="font-medium text-gray-900">Enable Splash Screen</h4>
                                    <p class="text-sm text-gray-500">Show a loading animation when visitors first open your site</p>
                                </div>
                                <button
                                    type="button"
                                    @click="form.splash_enabled = !form.splash_enabled"
                                    :class="[
                                        'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2',
                                        form.splash_enabled ? 'bg-indigo-600' : 'bg-gray-200'
                                    ]"
                                >
                                    <span
                                        :class="[
                                            'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                                            form.splash_enabled ? 'translate-x-5' : 'translate-x-0'
                                        ]"
                                    />
                                </button>
                            </div>

                            <!-- Style Selection -->
                            <div v-if="form.splash_enabled">
                                <label class="block text-sm font-medium text-gray-700 mb-3">Choose Style</label>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                    <button
                                        v-for="style in splashStyles"
                                        :key="style.id"
                                        type="button"
                                        @click="form.splash_style = style.id"
                                        :class="[
                                            'relative p-4 rounded-xl border-2 text-left transition-all',
                                            form.splash_style === style.id
                                                ? 'border-indigo-600 bg-indigo-50 ring-2 ring-indigo-600'
                                                : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'
                                        ]"
                                    >
                                        <div class="flex items-center gap-3 mb-2">
                                            <div 
                                                class="w-10 h-10 rounded-lg flex items-center justify-center text-lg"
                                                :class="form.splash_style === style.id ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600'"
                                            >
                                                {{ style.id === 'none' ? '✕' : style.id === 'minimal' ? '○' : style.id === 'pulse' ? '◎' : style.id === 'wave' ? '〰' : style.id === 'gradient' ? '◐' : style.id === 'particles' ? '✦' : '◇' }}
                                            </div>
                                            <div>
                                                <h4 class="font-medium text-gray-900">{{ style.name }}</h4>
                                            </div>
                                        </div>
                                        <p class="text-xs text-gray-500">{{ style.description }}</p>
                                        <CheckCircleIcon 
                                            v-if="form.splash_style === style.id"
                                            class="absolute top-2 right-2 h-5 w-5 text-indigo-600"
                                            aria-hidden="true"
                                        />
                                    </button>
                                </div>
                            </div>

                            <!-- Tagline -->
                            <div v-if="form.splash_enabled && form.splash_style !== 'none'">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tagline (optional)</label>
                                <input
                                    v-model="form.splash_tagline"
                                    type="text"
                                    placeholder="e.g., Quality products, delivered fast"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500"
                                    maxlength="60"
                                />
                                <p class="mt-1 text-xs text-gray-500">Short text shown below your site name (max 60 characters)</p>
                            </div>

                            <!-- Live Preview -->
                            <div v-if="form.splash_enabled && form.splash_style !== 'none'" class="mt-6">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-sm font-medium text-gray-700">Live Preview</h4>
                                    <button
                                        type="button"
                                        @click="restartPreview"
                                        class="text-xs text-indigo-600 hover:text-indigo-700 font-medium"
                                    >
                                        ↻ Replay Animation
                                    </button>
                                </div>
                                <div class="relative h-80 rounded-xl overflow-hidden border border-gray-200 shadow-inner">
                                    <!-- Minimal Style Preview -->
                                    <div v-if="form.splash_style === 'minimal'" class="splash-preview minimal-preview" :key="previewKey">
                                        <div class="preview-logo-wrapper minimal-bounce" :style="{ backgroundColor: form.primary_color }">
                                            {{ form.name?.charAt(0) || 'S' }}
                                        </div>
                                        <h3 class="preview-title">{{ form.name || 'Site Name' }}</h3>
                                        <p v-if="form.splash_tagline" class="preview-tagline">{{ form.splash_tagline }}</p>
                                        <div class="minimal-loader-track">
                                            <div class="minimal-loader-bar" :style="{ backgroundColor: form.primary_color }"></div>
                                        </div>
                                    </div>

                                    <!-- Pulse Style Preview -->
                                    <div v-else-if="form.splash_style === 'pulse'" class="splash-preview pulse-preview" :key="previewKey">
                                        <div class="pulse-container">
                                            <div class="pulse-ring" :style="{ borderColor: form.primary_color + '40' }"></div>
                                            <div class="pulse-ring delay-1" :style="{ borderColor: form.primary_color + '40' }"></div>
                                            <div class="pulse-ring delay-2" :style="{ borderColor: form.primary_color + '40' }"></div>
                                            <div class="preview-logo-wrapper" :style="{ backgroundColor: form.primary_color }">
                                                {{ form.name?.charAt(0) || 'S' }}
                                            </div>
                                        </div>
                                        <h3 class="preview-title">{{ form.name || 'Site Name' }}</h3>
                                        <p v-if="form.splash_tagline" class="preview-tagline">{{ form.splash_tagline }}</p>
                                    </div>

                                    <!-- Wave Style Preview -->
                                    <div v-else-if="form.splash_style === 'wave'" class="splash-preview wave-preview" :key="previewKey">
                                        <svg class="wave-svg" viewBox="0 0 1440 320" preserveAspectRatio="none">
                                            <path class="wave-path wave-1" :fill="form.primary_color + '30'" d="M0,192L48,197.3C96,203,192,213,288,229.3C384,245,480,267,576,250.7C672,235,768,181,864,181.3C960,181,1056,235,1152,234.7C1248,235,1344,181,1392,154.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
                                            <path class="wave-path wave-2" :fill="form.primary_color + '60'" d="M0,256L48,240C96,224,192,192,288,181.3C384,171,480,181,576,197.3C672,213,768,235,864,224C960,213,1056,171,1152,165.3C1248,160,1344,192,1392,208L1440,224L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
                                        </svg>
                                        <div class="wave-content">
                                            <div class="preview-logo-wrapper" :style="{ backgroundColor: form.primary_color }">
                                                {{ form.name?.charAt(0) || 'S' }}
                                            </div>
                                            <h3 class="preview-title">{{ form.name || 'Site Name' }}</h3>
                                            <p v-if="form.splash_tagline" class="preview-tagline">{{ form.splash_tagline }}</p>
                                        </div>
                                    </div>

                                    <!-- Gradient Style Preview -->
                                    <div v-else-if="form.splash_style === 'gradient'" class="splash-preview gradient-preview" :style="{ background: `linear-gradient(135deg, ${form.primary_color} 0%, ${adjustColor(form.primary_color, -40)} 100%)` }" :key="previewKey">
                                        <div class="gradient-shine"></div>
                                        <div class="gradient-content">
                                            <div class="preview-logo-wrapper gradient-float white-logo" :style="{ color: form.primary_color }">
                                                {{ form.name?.charAt(0) || 'S' }}
                                            </div>
                                            <h3 class="preview-title text-white">{{ form.name || 'Site Name' }}</h3>
                                            <p v-if="form.splash_tagline" class="preview-tagline text-white-80">{{ form.splash_tagline }}</p>
                                            <div class="gradient-dots">
                                                <span class="dot"></span>
                                                <span class="dot"></span>
                                                <span class="dot"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Particles Style Preview -->
                                    <div v-else-if="form.splash_style === 'particles'" class="splash-preview particles-preview" :key="previewKey">
                                        <div class="particles-container">
                                            <div v-for="i in 15" :key="i" class="particle" :style="{ 
                                                backgroundColor: i % 2 === 0 ? form.primary_color : form.primary_color + '60',
                                                animationDelay: `${i * 0.15}s`,
                                                left: `${(i * 7) % 100}%`
                                            }"></div>
                                        </div>
                                        <div class="particles-content">
                                            <div class="preview-logo-wrapper" :style="{ backgroundColor: form.primary_color }">
                                                {{ form.name?.charAt(0) || 'S' }}
                                            </div>
                                            <h3 class="preview-title text-white">{{ form.name || 'Site Name' }}</h3>
                                            <p v-if="form.splash_tagline" class="preview-tagline text-white-70">{{ form.splash_tagline }}</p>
                                        </div>
                                    </div>

                                    <!-- Elegant Style Preview -->
                                    <div v-else-if="form.splash_style === 'elegant'" class="splash-preview elegant-preview" :key="previewKey">
                                        <div class="elegant-pattern"></div>
                                        <div class="elegant-content">
                                            <div class="elegant-line" :style="{ backgroundColor: form.primary_color }"></div>
                                            <div class="preview-logo-wrapper elegant-scale elegant-border" :style="{ color: form.primary_color, borderColor: form.primary_color }">
                                                {{ form.name?.charAt(0) || 'S' }}
                                            </div>
                                            <h3 class="preview-title elegant-title">{{ form.name || 'Site Name' }}</h3>
                                            <p v-if="form.splash_tagline" class="preview-tagline elegant-tagline">{{ form.splash_tagline }}</p>
                                            <div class="elegant-line" :style="{ backgroundColor: form.primary_color }"></div>
                                            <div class="elegant-spinner" :style="{ borderTopColor: form.primary_color }"></div>
                                        </div>
                                    </div>

                                    <!-- Style Label -->
                                    <div class="absolute bottom-2 right-2 px-2 py-1 bg-black/60 text-white text-xs rounded-lg capitalize">
                                        {{ form.splash_style }} style
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SEO Tab -->
                        <div v-show="activeTab === 'seo'" class="space-y-6">
                            <div class="p-4 bg-blue-50 rounded-xl border border-blue-100">
                                <div class="flex items-center justify-between">
                                    <div class="flex gap-3">
                                        <MagnifyingGlassIcon class="h-5 w-5 text-blue-600 flex-shrink-0 mt-0.5" aria-hidden="true" />
                                        <div>
                                            <h4 class="font-medium text-blue-900">Search Engine Optimization</h4>
                                            <p class="text-sm text-blue-700 mt-1">Help your site rank better in Google and other search engines.</p>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        @click="generateSEO"
                                        :disabled="aiLoading === 'seo'"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition disabled:opacity-50"
                                    >
                                        <SparklesIcon class="h-4 w-4" :class="{ 'animate-spin': aiLoading === 'seo' }" aria-hidden="true" />
                                        {{ aiLoading === 'seo' ? 'Generating...' : 'AI Generate SEO' }}
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Meta Title</label>
                                <input
                                    v-model="form.meta_title"
                                    type="text"
                                    maxlength="60"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500"
                                    placeholder="Your Site Name | Tagline"
                                />
                                <p class="mt-1 text-xs text-gray-500">{{ form.meta_title.length }}/60 characters</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label>
                                <textarea
                                    v-model="form.meta_description"
                                    rows="3"
                                    maxlength="160"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500"
                                    placeholder="A brief description of your website for search results..."
                                ></textarea>
                                <p class="mt-1 text-xs text-gray-500">{{ form.meta_description.length }}/160 characters</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Social Share Image (OG Image)</label>
                                <input
                                    v-model="form.og_image"
                                    type="url"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500"
                                    placeholder="https://..."
                                />
                                <p class="mt-1 text-xs text-gray-500">Recommended: 1200x630 pixels</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Google Analytics ID</label>
                                <input
                                    v-model="form.google_analytics_id"
                                    type="text"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500"
                                    placeholder="G-XXXXXXXXXX or UA-XXXXXXXX-X"
                                />
                            </div>

                            <!-- Search Preview -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Search Preview</label>
                                <div class="p-4 bg-white border border-gray-200 rounded-xl">
                                    <p class="text-blue-800 text-lg hover:underline cursor-pointer">
                                        {{ form.meta_title || site.name }}
                                    </p>
                                    <p class="text-green-700 text-sm">{{ site.subdomain }}.mygrownet.com</p>
                                    <p class="text-gray-600 text-sm mt-1">
                                        {{ form.meta_description || 'Add a meta description to improve your search ranking.' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Social & Contact Tab -->
                        <div v-show="activeTab === 'social'" class="space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Social Media Links</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Facebook</label>
                                        <input
                                            v-model="form.facebook"
                                            type="url"
                                            placeholder="https://facebook.com/yourpage"
                                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Instagram</label>
                                        <input
                                            v-model="form.instagram"
                                            type="url"
                                            placeholder="https://instagram.com/yourpage"
                                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Twitter / X</label>
                                        <input
                                            v-model="form.twitter"
                                            type="url"
                                            placeholder="https://twitter.com/yourpage"
                                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">LinkedIn</label>
                                        <input
                                            v-model="form.linkedin"
                                            type="url"
                                            placeholder="https://linkedin.com/company/yourpage"
                                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500"
                                        />
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">WhatsApp Number</label>
                                        <input
                                            v-model="form.whatsapp"
                                            type="tel"
                                            placeholder="+260977123456"
                                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500"
                                        />
                                        <p class="mt-1 text-xs text-gray-500">Include country code for WhatsApp click-to-chat</p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Contact Information</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                        <input
                                            v-model="form.phone"
                                            type="tel"
                                            placeholder="+260 977 123 456"
                                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                        <input
                                            v-model="form.email"
                                            type="email"
                                            placeholder="contact@yourbusiness.com"
                                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                                        <textarea
                                            v-model="form.address"
                                            rows="2"
                                            placeholder="123 Cairo Road, Lusaka, Zambia"
                                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500"
                                        ></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="mt-8 pt-6 border-t border-gray-200 flex items-center justify-between">
                            <button
                                type="button"
                                class="inline-flex items-center gap-2 px-4 py-2 text-red-600 hover:bg-red-50 rounded-xl transition"
                                @click="openDeleteModal"
                            >
                                <TrashIcon class="h-5 w-5" aria-hidden="true" />
                                Delete Site
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-6 py-2.5 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 disabled:opacity-50 transition"
                            >
                                {{ form.processing ? 'Saving...' : 'Save Changes' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div
            v-if="showDeleteModal"
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
            @click.self="showDeleteModal = false"
        >
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                <div class="flex items-center gap-4 mb-4">
                    <div class="p-3 bg-red-100 rounded-full">
                        <ExclamationTriangleIcon class="h-6 w-6 text-red-600" aria-hidden="true" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Delete Site</h3>
                        <p class="text-sm text-gray-500">30-day grace period applies</p>
                    </div>
                </div>
                <p class="text-gray-600 mb-3">
                    Are you sure you want to delete <strong>{{ site.name }}</strong>?
                </p>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                    <p class="text-sm text-blue-800">
                        <strong>Note:</strong> Your site will be scheduled for deletion and permanently removed after 30 days. You can restore it within this period.
                    </p>
                </div>
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-4">
                    <p class="text-sm text-amber-800">
                        To confirm, type <strong class="font-mono">{{ site.subdomain }}</strong> below:
                    </p>
                </div>
                <input
                    v-model="deleteConfirmation"
                    type="text"
                    :placeholder="site.subdomain"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 mb-4 font-mono"
                    @keyup.enter="canDelete && deleteSite()"
                />
                <div class="flex gap-3 justify-end">
                    <button
                        type="button"
                        class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-xl transition"
                        @click="showDeleteModal = false"
                        :disabled="isDeleting"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="px-4 py-2 bg-red-600 text-white font-medium rounded-xl hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="!canDelete || isDeleting"
                        @click="deleteSite"
                    >
                        {{ isDeleting ? 'Processing...' : 'Schedule Deletion' }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Splash Preview Styles */
.splash-preview {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.preview-logo-wrapper {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 700;
    color: white;
    margin-bottom: 12px;
}

.preview-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 4px;
}

.preview-tagline {
    font-size: 0.875rem;
    color: #6b7280;
}

/* Minimal Style */
.minimal-preview {
    background: white;
}

.minimal-bounce {
    animation: minimalBounce 1.5s ease-in-out infinite;
}

@keyframes minimalBounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
}

.minimal-loader-track {
    width: 120px;
    height: 3px;
    background: #e5e7eb;
    border-radius: 2px;
    margin-top: 16px;
    overflow: hidden;
}

.minimal-loader-bar {
    height: 100%;
    width: 0%;
    border-radius: 2px;
    animation: minimalLoad 1.5s ease-in-out infinite;
}

@keyframes minimalLoad {
    0% { width: 0%; margin-left: 0; }
    50% { width: 60%; margin-left: 0; }
    100% { width: 0%; margin-left: 100%; }
}

/* Pulse Style */
.pulse-preview {
    background: white;
}

.pulse-container {
    position: relative;
    width: 90px;
    height: 90px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
}

.pulse-container .preview-logo-wrapper {
    position: relative;
    z-index: 2;
    margin-bottom: 0;
}

.pulse-ring {
    position: absolute;
    width: 100%;
    height: 100%;
    border: 3px solid;
    border-radius: 50%;
    animation: pulseRing 2s ease-out infinite;
}

.pulse-ring.delay-1 { animation-delay: 0.4s; }
.pulse-ring.delay-2 { animation-delay: 0.8s; }

@keyframes pulseRing {
    0% { transform: scale(0.7); opacity: 1; }
    100% { transform: scale(1.6); opacity: 0; }
}

/* Wave Style */
.wave-preview {
    background: linear-gradient(180deg, #f8fafc 0%, #e2e8f0 100%);
    position: relative;
    overflow: hidden;
}

.wave-svg {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 45%;
}

.wave-path {
    animation: waveMove 3s ease-in-out infinite;
}

.wave-1 { animation-delay: 0s; }
.wave-2 { animation-delay: 0.5s; }

@keyframes waveMove {
    0%, 100% { transform: translateX(0); }
    50% { transform: translateX(-3%); }
}

.wave-content {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    align-items: center;
}

/* Gradient Style */
.gradient-preview {
    position: relative;
    overflow: hidden;
}

.gradient-shine {
    position: absolute;
    inset: 0;
    background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.15) 50%, transparent 70%);
    animation: gradientShine 2.5s ease-in-out infinite;
}

@keyframes gradientShine {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

.gradient-content {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.gradient-float {
    animation: gradientFloat 2s ease-in-out infinite;
}

@keyframes gradientFloat {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.white-logo {
    background: white !important;
}

.text-white { color: white; }
.text-white-80 { color: rgba(255,255,255,0.8); }
.text-white-70 { color: rgba(255,255,255,0.7); }

.gradient-dots {
    display: flex;
    gap: 6px;
    margin-top: 16px;
}

.gradient-dots .dot {
    width: 8px;
    height: 8px;
    background: white;
    border-radius: 50%;
    animation: dotPulse 1.4s ease-in-out infinite;
}

.gradient-dots .dot:nth-child(2) { animation-delay: 0.2s; }
.gradient-dots .dot:nth-child(3) { animation-delay: 0.4s; }

@keyframes dotPulse {
    0%, 80%, 100% { transform: scale(0.6); opacity: 0.5; }
    40% { transform: scale(1); opacity: 1; }
}

/* Particles Style */
.particles-preview {
    background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
    position: relative;
    overflow: hidden;
}

.particles-container {
    position: absolute;
    inset: 0;
}

.particle {
    position: absolute;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    bottom: -10px;
    animation: particleRise 3.5s linear infinite;
}

@keyframes particleRise {
    0% { transform: translateY(0) rotate(0deg); opacity: 0; }
    10% { opacity: 1; }
    90% { opacity: 1; }
    100% { transform: translateY(-350px) rotate(720deg); opacity: 0; }
}

.particles-content {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    align-items: center;
}

/* Elegant Style */
.elegant-preview {
    background: #fafafa;
    position: relative;
}

.elegant-pattern {
    position: absolute;
    inset: 0;
    background-image: radial-gradient(circle at 1px 1px, #e5e7eb 1px, transparent 0);
    background-size: 24px 24px;
    opacity: 0.6;
}

.elegant-content {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.elegant-line {
    width: 40px;
    height: 2px;
    border-radius: 1px;
    margin: 12px 0;
}

.elegant-scale {
    animation: elegantScale 2s ease-in-out infinite;
}

@keyframes elegantScale {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.elegant-border {
    background: transparent !important;
    border: 2px solid;
}

.elegant-title {
    font-weight: 600;
    letter-spacing: 0.03em;
}

.elegant-tagline {
    font-style: italic;
}

.elegant-spinner {
    width: 18px;
    height: 18px;
    border: 2px solid #e5e7eb;
    border-top-width: 2px;
    border-radius: 50%;
    animation: elegantSpin 1s linear infinite;
    margin-top: 8px;
}

@keyframes elegantSpin {
    to { transform: rotate(360deg); }
}
</style>
