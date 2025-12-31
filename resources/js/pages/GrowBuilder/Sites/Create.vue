<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { 
    ArrowLeftIcon, 
    CheckIcon, 
    DocumentDuplicateIcon,
    SparklesIcon,
    BuildingOfficeIcon,
    ScaleIcon,
    HeartIcon,
    AcademicCapIcon,
    HomeIcon,
    ShoppingBagIcon,
    BriefcaseIcon,
    XMarkIcon,
    EyeIcon,
    ComputerDesktopIcon,
} from '@heroicons/vue/24/outline';

interface SiteTemplatePage {
    title: string;
    slug: string;
    isHomepage: boolean;
}

interface SiteTemplate {
    id: number;
    name: string;
    slug: string;
    description: string;
    industry: string;
    thumbnail: string | null;
    theme: Record<string, string> | null;
    isPremium: boolean;
    pagesCount: number;
    pages: SiteTemplatePage[];
}

interface Industry {
    slug: string;
    name: string;
    icon: string;
}

interface Template {
    id: number;
    name: string;
    slug: string;
    category: string;
    categoryLabel: string;
    description: string | null;
    thumbnail: string | null;
    previewImage: string | null;
    isPremium: boolean;
    price: number;
}

const props = defineProps<{
    templates: Template[];
    siteTemplates: SiteTemplate[];
    industries: Industry[];
}>();

const selectedSiteTemplate = ref<number | null>(null);
const selectedIndustry = ref<string>('all');
const previewTemplate = ref<SiteTemplate | null>(null);
const showPreviewModal = ref(false);

// Industry icons mapping
const industryIcons: Record<string, any> = {
    consulting: BriefcaseIcon,
    restaurant: SparklesIcon,
    beauty: SparklesIcon,
    legal: ScaleIcon,
    fitness: HeartIcon,
    realestate: HomeIcon,
    education: AcademicCapIcon,
    ecommerce: ShoppingBagIcon,
};

const filteredSiteTemplates = computed(() => {
    if (selectedIndustry.value === 'all') {
        return props.siteTemplates;
    }
    return props.siteTemplates.filter(t => t.industry === selectedIndustry.value);
});

const form = useForm({
    name: '',
    subdomain: '',
    site_template_id: null as number | null,
    description: '',
});

const generateSubdomain = () => {
    form.subdomain = form.name
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-|-$/g, '')
        .substring(0, 63);
};

const selectSiteTemplate = (id: number | null) => {
    selectedSiteTemplate.value = id;
    form.site_template_id = id;
    
    // Scroll to form after selecting template
    setTimeout(() => {
        const formElement = document.querySelector('form');
        if (formElement) {
            const submitButton = formElement.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    }, 100);
};

const openPreview = (template: SiteTemplate) => {
    previewTemplate.value = template;
    showPreviewModal.value = true;
};

const closePreview = () => {
    showPreviewModal.value = false;
    previewTemplate.value = null;
};

const selectAndClose = () => {
    if (previewTemplate.value) {
        selectSiteTemplate(previewTemplate.value.id);
    }
    closePreview();
};

const createWithTemplate = () => {
    if (previewTemplate.value) {
        form.site_template_id = previewTemplate.value.id;
        selectedSiteTemplate.value = previewTemplate.value.id;
        closePreview();
        
        // Scroll to form and focus on name input after modal closes
        setTimeout(() => {
            const nameInput = document.querySelector('#name') as HTMLInputElement;
            if (nameInput) {
                // Scroll to the website details section
                const detailsSection = nameInput.closest('.bg-white');
                if (detailsSection) {
                    detailsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
                // Focus the input after scrolling
                setTimeout(() => {
                    nameInput.focus();
                }, 500);
            }
        }, 200);
    }
};

const submit = () => {
    form.post(route('growbuilder.sites.store'));
};

const getIndustryIcon = (industry: string) => {
    return industryIcons[industry] || BuildingOfficeIcon;
};
</script>

<template>
    <AppLayout>
        <Head title="Create Website - GrowBuilder" />

        <div class="py-6">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <Link
                        :href="route('growbuilder.index')"
                        class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-4"
                    >
                        <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                        Back to Dashboard
                    </Link>
                    <h1 class="text-2xl font-bold text-gray-900">Create New Website</h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Choose a professionally designed template to get started quickly
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-8">
                    <!-- Basic Info -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Website Details</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Website Name
                                </label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="My Business"
                                    @blur="generateSubdomain"
                                    required
                                />
                                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                            </div>

                            <div>
                                <label for="subdomain" class="block text-sm font-medium text-gray-700 mb-1">
                                    Subdomain
                                </label>
                                <div class="flex">
                                    <input
                                        id="subdomain"
                                        v-model="form.subdomain"
                                        type="text"
                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-l-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="my-business"
                                        required
                                    />
                                    <span class="inline-flex items-center px-3 py-2 bg-gray-100 border border-l-0 border-gray-300 rounded-r-lg text-sm text-gray-500">
                                        .mygrownet.com
                                    </span>
                                </div>
                                <p v-if="form.errors.subdomain" class="mt-1 text-sm text-red-600">{{ form.errors.subdomain }}</p>
                            </div>

                            <div class="md:col-span-2">
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                    Description (optional)
                                </label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="2"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Brief description of your website"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Site Template Selection -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900">Choose a Template</h2>
                                <p class="text-sm text-gray-500">Select a professionally designed template with all pages included</p>
                            </div>
                            <span class="text-sm text-gray-500">{{ filteredSiteTemplates.length }} templates</span>
                        </div>

                        <!-- Industry Filter -->
                        <div class="flex flex-wrap gap-2 mb-6">
                            <button
                                type="button"
                                :class="[
                                    'px-3 py-1.5 text-sm font-medium rounded-full transition',
                                    selectedIndustry === 'all'
                                        ? 'bg-blue-600 text-white'
                                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                ]"
                                @click="selectedIndustry = 'all'"
                            >
                                All Industries
                            </button>
                            <button
                                v-for="industry in industries"
                                :key="industry.slug"
                                type="button"
                                :class="[
                                    'px-3 py-1.5 text-sm font-medium rounded-full transition inline-flex items-center gap-1.5',
                                    selectedIndustry === industry.slug
                                        ? 'bg-blue-600 text-white'
                                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                ]"
                                @click="selectedIndustry = industry.slug"
                            >
                                <component :is="getIndustryIcon(industry.slug)" class="h-4 w-4" aria-hidden="true" />
                                {{ industry.name }}
                            </button>
                        </div>

                        <!-- Templates Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                            <!-- Blank Template -->
                            <button
                                type="button"
                                :class="[
                                    'relative border-2 rounded-xl text-left transition overflow-hidden group',
                                    selectedSiteTemplate === null
                                        ? 'border-blue-500 ring-2 ring-blue-200'
                                        : 'border-gray-200 hover:border-gray-300'
                                ]"
                                @click="selectSiteTemplate(null)"
                            >
                                <div class="h-44 bg-gradient-to-br from-gray-50 to-gray-100 flex flex-col items-center justify-center">
                                    <DocumentDuplicateIcon class="h-12 w-12 text-gray-300 mb-2" aria-hidden="true" />
                                    <span class="text-gray-400 text-sm">Start Fresh</span>
                                </div>
                                <div class="p-4">
                                    <h3 class="font-semibold text-gray-900">Blank Canvas</h3>
                                    <p class="text-sm text-gray-500 mt-1">Build your own design from scratch</p>
                                    <div class="mt-2 flex items-center gap-2 text-xs text-gray-400">
                                        <span>0 pages</span>
                                    </div>
                                </div>
                                <div
                                    v-if="selectedSiteTemplate === null"
                                    class="absolute top-3 right-3 w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center"
                                >
                                    <CheckIcon class="h-4 w-4 text-white" aria-hidden="true" />
                                </div>
                            </button>

                            <!-- Site Template Cards -->
                            <div
                                v-for="template in filteredSiteTemplates"
                                :key="template.id"
                                :class="[
                                    'relative border-2 rounded-xl text-left transition overflow-hidden group',
                                    selectedSiteTemplate === template.id
                                        ? 'border-blue-500 ring-2 ring-blue-200'
                                        : 'border-gray-200 hover:border-gray-300'
                                ]"
                            >
                                <!-- Thumbnail -->
                                <div class="relative h-44 bg-gray-100 overflow-hidden">
                                    <img
                                        v-if="template.thumbnail"
                                        :src="template.thumbnail"
                                        :alt="template.name"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                    />
                                    <div v-else class="w-full h-full flex items-center justify-center">
                                        <component :is="getIndustryIcon(template.industry)" class="h-16 w-16 text-gray-300" aria-hidden="true" />
                                    </div>
                                    
                                    <!-- Hover overlay with preview button -->
                                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                        <button
                                            type="button"
                                            class="px-3 py-1.5 bg-white text-gray-900 text-sm font-medium rounded-lg hover:bg-gray-100 transition inline-flex items-center gap-1"
                                            @click.stop="openPreview(template)"
                                        >
                                            <EyeIcon class="h-4 w-4" aria-hidden="true" />
                                            Quick View
                                        </button>
                                        <Link
                                            :href="route('growbuilder.templates.live', template.id)"
                                            target="_blank"
                                            class="px-3 py-1.5 bg-gray-800 text-white text-sm font-medium rounded-lg hover:bg-gray-900 transition inline-flex items-center gap-1"
                                            @click.stop
                                        >
                                            <ComputerDesktopIcon class="h-4 w-4" aria-hidden="true" />
                                            Live Preview
                                        </Link>
                                    </div>

                                    <!-- Premium badge -->
                                    <span
                                        v-if="template.isPremium"
                                        class="absolute top-3 left-3 px-2 py-0.5 bg-gradient-to-r from-purple-600 to-indigo-600 text-white text-xs font-medium rounded-full"
                                    >
                                        Premium
                                    </span>

                                    <!-- Theme color indicator -->
                                    <div v-if="template.theme" class="absolute bottom-3 left-3 flex gap-1">
                                        <span
                                            v-if="template.theme.primaryColor"
                                            class="w-4 h-4 rounded-full border-2 border-white shadow-sm"
                                            :style="{ backgroundColor: template.theme.primaryColor }"
                                        ></span>
                                        <span
                                            v-if="template.theme.secondaryColor"
                                            class="w-4 h-4 rounded-full border-2 border-white shadow-sm"
                                            :style="{ backgroundColor: template.theme.secondaryColor }"
                                        ></span>
                                        <span
                                            v-if="template.theme.accentColor"
                                            class="w-4 h-4 rounded-full border-2 border-white shadow-sm"
                                            :style="{ backgroundColor: template.theme.accentColor }"
                                        ></span>
                                    </div>
                                </div>

                                <!-- Info -->
                                <button
                                    type="button"
                                    class="w-full p-4 text-left"
                                    @click="selectSiteTemplate(template.id)"
                                >
                                    <h3 class="font-semibold text-gray-900">{{ template.name }}</h3>
                                    <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ template.description }}</p>
                                    <div class="mt-2 flex items-center gap-3 text-xs text-gray-400">
                                        <span class="inline-flex items-center gap-1">
                                            <DocumentDuplicateIcon class="h-3.5 w-3.5" aria-hidden="true" />
                                            {{ template.pagesCount }} pages
                                        </span>
                                        <span class="capitalize">{{ template.industry }}</span>
                                    </div>
                                </button>

                                <!-- Selected indicator -->
                                <div
                                    v-if="selectedSiteTemplate === template.id"
                                    class="absolute top-3 right-3 w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center"
                                >
                                    <CheckIcon class="h-4 w-4 text-white" aria-hidden="true" />
                                </div>
                            </div>
                        </div>

                        <!-- Empty state -->
                        <div v-if="filteredSiteTemplates.length === 0 && selectedIndustry !== 'all'" class="text-center py-12">
                            <BuildingOfficeIcon class="h-12 w-12 text-gray-300 mx-auto mb-3" aria-hidden="true" />
                            <p class="text-gray-500">No templates found for this industry</p>
                            <button
                                type="button"
                                class="mt-2 text-blue-600 hover:text-blue-700 text-sm font-medium"
                                @click="selectedIndustry = 'all'"
                            >
                                View all templates
                            </button>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-end gap-3">
                        <Link
                            :href="route('growbuilder.index')"
                            class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50 transition"
                        >
                            {{ form.processing ? 'Creating...' : 'Create Website' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Preview Modal -->
        <Teleport to="body">
            <div
                v-if="showPreviewModal && previewTemplate"
                class="fixed inset-0 z-50 overflow-y-auto"
                @click.self="closePreview"
            >
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="fixed inset-0 bg-black/60 transition-opacity" @click="closePreview"></div>
                    
                    <div class="relative bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
                        <!-- Modal Header -->
                        <div class="flex items-center justify-between p-4 border-b">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ previewTemplate.name }}</h3>
                                <p class="text-sm text-gray-500">{{ previewTemplate.description }}</p>
                            </div>
                            <button
                                type="button"
                                class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100"
                                @click="closePreview"
                                aria-label="Close preview"
                            >
                                <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                            </button>
                        </div>

                        <!-- Modal Content -->
                        <div class="p-6 overflow-y-auto max-h-[60vh]">
                            <!-- Large Preview Image -->
                            <div class="rounded-xl overflow-hidden bg-gray-100 mb-6">
                                <img
                                    v-if="previewTemplate.thumbnail"
                                    :src="previewTemplate.thumbnail"
                                    :alt="previewTemplate.name"
                                    class="w-full h-64 object-cover"
                                />
                                <div v-else class="w-full h-64 flex items-center justify-center">
                                    <component :is="getIndustryIcon(previewTemplate.industry)" class="h-24 w-24 text-gray-300" aria-hidden="true" />
                                </div>
                            </div>

                            <!-- Template Details -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Pages Included -->
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-3">Pages Included ({{ previewTemplate.pagesCount }})</h4>
                                    <ul class="space-y-2">
                                        <li
                                            v-for="page in previewTemplate.pages"
                                            :key="page.slug"
                                            class="flex items-center gap-2 text-sm text-gray-600"
                                        >
                                            <DocumentDuplicateIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
                                            {{ page.title }}
                                            <span v-if="page.isHomepage" class="text-xs bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded">Home</span>
                                        </li>
                                    </ul>
                                </div>

                                <!-- Theme Colors -->
                                <div v-if="previewTemplate.theme">
                                    <h4 class="font-medium text-gray-900 mb-3">Color Scheme</h4>
                                    <div class="flex flex-wrap gap-3">
                                        <div v-if="previewTemplate.theme.primaryColor" class="flex items-center gap-2">
                                            <span
                                                class="w-8 h-8 rounded-lg border shadow-sm"
                                                :style="{ backgroundColor: previewTemplate.theme.primaryColor }"
                                            ></span>
                                            <span class="text-sm text-gray-600">Primary</span>
                                        </div>
                                        <div v-if="previewTemplate.theme.secondaryColor" class="flex items-center gap-2">
                                            <span
                                                class="w-8 h-8 rounded-lg border shadow-sm"
                                                :style="{ backgroundColor: previewTemplate.theme.secondaryColor }"
                                            ></span>
                                            <span class="text-sm text-gray-600">Secondary</span>
                                        </div>
                                        <div v-if="previewTemplate.theme.accentColor" class="flex items-center gap-2">
                                            <span
                                                class="w-8 h-8 rounded-lg border shadow-sm"
                                                :style="{ backgroundColor: previewTemplate.theme.accentColor }"
                                            ></span>
                                            <span class="text-sm text-gray-600">Accent</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="flex items-center justify-between gap-3 p-4 border-t bg-gray-50">
                            <Link
                                :href="route('growbuilder.templates.live', previewTemplate.id)"
                                target="_blank"
                                class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition inline-flex items-center gap-2"
                            >
                                <ComputerDesktopIcon class="h-4 w-4" aria-hidden="true" />
                                Live Preview
                            </Link>
                            <div class="flex items-center gap-3">
                                <button
                                    type="button"
                                    class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition"
                                    @click="selectAndClose"
                                >
                                    Select Template
                                </button>
                                <button
                                    type="button"
                                    class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition inline-flex items-center gap-2"
                                    @click="createWithTemplate"
                                >
                                    <SparklesIcon class="h-4 w-4" aria-hidden="true" />
                                    Create with Template
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
