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
} from '@heroicons/vue/24/outline';
import { ref } from 'vue';

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
}

const props = defineProps<{
    site: Site;
}>();

const activeTab = ref('general');
const showDeleteModal = ref(false);

const form = useForm({
    name: props.site.name,
    subdomain: props.site.subdomain,
    custom_domain: props.site.customDomain || '',
    description: props.site.description || '',
    logo: props.site.logo || '',
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

const fonts = [
    'Inter', 'Roboto', 'Open Sans', 'Lato', 'Montserrat', 'Poppins',
    'Playfair Display', 'Merriweather', 'Source Sans Pro', 'Nunito',
];

const tabs = [
    { id: 'general', name: 'General', icon: GlobeAltIcon },
    { id: 'theme', name: 'Theme', icon: PaintBrushIcon },
    { id: 'seo', name: 'SEO', icon: MagnifyingGlassIcon },
    { id: 'social', name: 'Social & Contact', icon: ShareIcon },
];

const submit = () => {
    form.put(route('growbuilder.sites.update', props.site.id));
};

const deleteSite = () => {
    router.delete(route('growbuilder.sites.destroy', props.site.id));
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
                        <div class="flex items-center gap-2">
                            <span :class="[
                                'px-3 py-1 text-sm font-medium rounded-full',
                                site.status === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                            ]">
                                {{ site.status }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
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
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea
                                    v-model="form.description"
                                    rows="3"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500"
                                    placeholder="Brief description of your website..."
                                ></textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Logo URL</label>
                                    <input
                                        v-model="form.logo"
                                        type="url"
                                        placeholder="https://..."
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Favicon URL</label>
                                    <input
                                        v-model="form.favicon"
                                        type="url"
                                        placeholder="https://..."
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Theme Tab -->
                        <div v-show="activeTab === 'theme'" class="space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Colors</h3>
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

                        <!-- SEO Tab -->
                        <div v-show="activeTab === 'seo'" class="space-y-6">
                            <div class="p-4 bg-blue-50 rounded-xl border border-blue-100">
                                <div class="flex gap-3">
                                    <MagnifyingGlassIcon class="h-5 w-5 text-blue-600 flex-shrink-0 mt-0.5" aria-hidden="true" />
                                    <div>
                                        <h4 class="font-medium text-blue-900">Search Engine Optimization</h4>
                                        <p class="text-sm text-blue-700 mt-1">Help your site rank better in Google and other search engines.</p>
                                    </div>
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
                                @click="showDeleteModal = true"
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
                        <p class="text-sm text-gray-500">This action cannot be undone.</p>
                    </div>
                </div>
                <p class="text-gray-600 mb-6">
                    Are you sure you want to delete <strong>{{ site.name }}</strong>? All pages, products, and data will be permanently removed.
                </p>
                <div class="flex gap-3 justify-end">
                    <button
                        type="button"
                        class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-xl transition"
                        @click="showDeleteModal = false"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="px-4 py-2 bg-red-600 text-white font-medium rounded-xl hover:bg-red-700 transition"
                        @click="deleteSite"
                    >
                        Delete Site
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
