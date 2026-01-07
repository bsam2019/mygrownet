<script setup lang="ts">
import { ref, computed, watch, onUnmounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import {
    XMarkIcon,
    ArrowLeftIcon,
    ArrowRightIcon,
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
    ComputerDesktopIcon,
    EyeIcon,
    ChevronDownIcon,
    FunnelIcon,
    MagnifyingGlassIcon,
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

const props = defineProps<{
    show: boolean;
    siteTemplates: SiteTemplate[];
    industries: Industry[];
}>();

const emit = defineEmits<{
    close: [];
}>();

const currentStep = ref(1);
const selectedSiteTemplate = ref<number | null>(null);
const selectedIndustry = ref<string>('all');
const searchQuery = ref('');
const previewTemplate = ref<SiteTemplate | null>(null);
const showPreviewModal = ref(false);
const showIndustryDropdown = ref(false);

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

const form = useForm({
    name: '',
    subdomain: '',
    site_template_id: null as number | null,
    description: '',
});

// Reset form when modal opens
watch(() => props.show, (newVal) => {
    if (newVal) {
        currentStep.value = 1;
        selectedSiteTemplate.value = null;
        selectedIndustry.value = 'all';
        searchQuery.value = '';
        showIndustryDropdown.value = false;
        form.reset();
    }
});

// Lock body scroll when preview modal is open
watch(showPreviewModal, (isOpen) => {
    if (isOpen) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
});

// Cleanup on unmount
onUnmounted(() => {
    document.body.style.overflow = '';
});

const filteredSiteTemplates = computed(() => {
    let templates = props.siteTemplates;
    
    // Filter by industry
    if (selectedIndustry.value !== 'all') {
        templates = templates.filter(t => t.industry === selectedIndustry.value);
    }
    
    // Filter by search query
    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase().trim();
        templates = templates.filter(t => 
            t.name.toLowerCase().includes(query) ||
            t.description?.toLowerCase().includes(query) ||
            t.industry?.toLowerCase().includes(query)
        );
    }
    
    return templates;
});

const selectedIndustryName = computed(() => {
    if (selectedIndustry.value === 'all') return 'All Industries';
    const industry = props.industries.find(i => i.slug === selectedIndustry.value);
    return industry?.name || 'All Industries';
});

const canProceedToStep2 = computed(() => {
    return form.name.trim().length > 0 && form.subdomain.trim().length >= 3;
});

const generateSubdomain = () => {
    if (!form.subdomain) {
        form.subdomain = form.name
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-|-$/g, '')
            .substring(0, 63);
    }
};

const getIndustryIcon = (industry: string) => {
    return industryIcons[industry] || BuildingOfficeIcon;
};

const selectTemplate = (id: number | null) => {
    selectedSiteTemplate.value = id;
    form.site_template_id = id;
};

const selectIndustry = (slug: string) => {
    selectedIndustry.value = slug;
    showIndustryDropdown.value = false;
};

const openPreview = (template: SiteTemplate) => {
    previewTemplate.value = template;
    showPreviewModal.value = true;
};

const closePreview = () => {
    showPreviewModal.value = false;
    previewTemplate.value = null;
};

const selectFromPreview = () => {
    if (previewTemplate.value) {
        selectTemplate(previewTemplate.value.id);
    }
    closePreview();
};

const nextStep = () => {
    if (currentStep.value === 1 && canProceedToStep2.value) {
        currentStep.value = 2;
    }
};

const prevStep = () => {
    if (currentStep.value === 2) {
        currentStep.value = 1;
    }
};

const close = () => {
    emit('close');
};

const submit = () => {
    form.post(route('growbuilder.sites.store'), {
        onSuccess: () => {
            close();
        },
    });
};

// Get live preview URL for template - use render route for clean preview
const getPreviewUrl = (templateId: number) => {
    return route('growbuilder.templates.render', templateId);
};
</script>

<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="fixed inset-0 z-50 overflow-y-auto"
        >
            <div class="flex min-h-full items-center justify-center p-4">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/50 transition-opacity" @click="close"></div>

                <!-- Modal -->
                <div 
                    :class="[
                        'relative bg-white rounded-2xl shadow-2xl w-full transition-all duration-300 max-h-[90vh] flex flex-col',
                        currentStep === 1 ? 'max-w-lg' : 'max-w-5xl'
                    ]"
                >
                    <!-- Compact Header with Progress -->
                    <div class="flex items-center justify-between px-5 py-3 border-b shrink-0">
                        <div class="flex items-center gap-4">
                            <h2 class="text-lg font-bold text-gray-900">Create New Website</h2>
                            <!-- Inline Progress -->
                            <div class="hidden sm:flex items-center gap-2 text-sm">
                                <span :class="currentStep >= 1 ? 'text-blue-600 font-medium' : 'text-gray-400'">Details</span>
                                <span class="text-gray-300">→</span>
                                <span :class="currentStep >= 2 ? 'text-blue-600 font-medium' : 'text-gray-400'">Template</span>
                            </div>
                        </div>
                        <button
                            type="button"
                            class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition"
                            @click="close"
                            aria-label="Close modal"
                        >
                            <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                        </button>
                    </div>

                    <!-- Content - Scrollable -->
                    <div class="p-5 overflow-y-auto flex-1">
                        <!-- Step 1: Website Details -->
                        <div v-if="currentStep === 1" class="space-y-5">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Website Name <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                    placeholder="My Business"
                                    @blur="generateSubdomain"
                                />
                                <p v-if="form.errors.name" class="mt-1.5 text-sm text-red-600">{{ form.errors.name }}</p>
                            </div>

                            <div>
                                <label for="subdomain" class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Subdomain <span class="text-red-500">*</span>
                                </label>
                                <div class="flex">
                                    <input
                                        id="subdomain"
                                        v-model="form.subdomain"
                                        type="text"
                                        class="flex-1 px-3.5 py-2.5 border border-gray-300 rounded-l-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                        placeholder="my-business"
                                    />
                                    <span class="inline-flex items-center px-3.5 py-2.5 bg-gray-100 border border-l-0 border-gray-300 rounded-r-xl text-sm text-gray-500">
                                        .mygrownet.com
                                    </span>
                                </div>
                                <p v-if="form.errors.subdomain" class="mt-1.5 text-sm text-red-600">{{ form.errors.subdomain }}</p>
                                <p v-else class="mt-1.5 text-xs text-gray-500">Only lowercase letters, numbers, and hyphens</p>
                            </div>

                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Description <span class="text-gray-400">(optional)</span>
                                </label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="2"
                                    class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition resize-none"
                                    placeholder="Brief description of your website"
                                ></textarea>
                            </div>
                        </div>

                        <!-- Step 2: Template Selection -->
                        <div v-else-if="currentStep === 2">
                            <!-- Filters Row - Industry Dropdown + Search -->
                            <div class="flex items-center gap-3 mb-4">
                                <!-- Industry Filter Dropdown -->
                                <div class="relative">
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-2 px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition"
                                        @click="showIndustryDropdown = !showIndustryDropdown"
                                    >
                                        <FunnelIcon class="h-4 w-4 text-gray-500" aria-hidden="true" />
                                        {{ selectedIndustryName }}
                                        <ChevronDownIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
                                    </button>
                                    
                                    <!-- Dropdown -->
                                    <div 
                                        v-if="showIndustryDropdown"
                                        class="absolute left-0 mt-1 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-10 max-h-64 overflow-y-auto"
                                    >
                                        <button
                                            type="button"
                                            :class="[
                                                'w-full text-left px-3 py-2 text-sm hover:bg-gray-50 transition',
                                                selectedIndustry === 'all' ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700'
                                            ]"
                                            @click="selectIndustry('all')"
                                        >
                                            All Industries
                                        </button>
                                        <button
                                            v-for="industry in industries"
                                            :key="industry.slug"
                                            type="button"
                                            :class="[
                                                'w-full text-left px-3 py-2 text-sm hover:bg-gray-50 transition',
                                                selectedIndustry === industry.slug ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700'
                                            ]"
                                            @click="selectIndustry(industry.slug)"
                                        >
                                            {{ industry.name }}
                                        </button>
                                    </div>
                                    
                                    <!-- Click outside to close -->
                                    <div 
                                        v-if="showIndustryDropdown" 
                                        class="fixed inset-0 z-0" 
                                        @click="showIndustryDropdown = false"
                                    ></div>
                                </div>

                                <!-- Search Input -->
                                <div class="relative flex-1 max-w-xs">
                                    <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" aria-hidden="true" />
                                    <input
                                        v-model="searchQuery"
                                        type="text"
                                        placeholder="Search templates..."
                                        class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                    />
                                </div>
                                
                                <span class="text-sm text-gray-500 ml-auto">{{ filteredSiteTemplates.length }} templates</span>
                            </div>

                            <!-- Templates Grid -->
                            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                                <!-- Blank Template -->
                                <button
                                    type="button"
                                    :class="[
                                        'relative border-2 rounded-xl text-left transition overflow-hidden group',
                                        selectedSiteTemplate === null
                                            ? 'border-blue-500 ring-2 ring-blue-200'
                                            : 'border-gray-200 hover:border-gray-300'
                                    ]"
                                    @click="selectTemplate(null)"
                                >
                                    <div class="h-24 bg-gradient-to-br from-gray-50 to-gray-100 flex flex-col items-center justify-center">
                                        <DocumentDuplicateIcon class="h-8 w-8 text-gray-300 mb-1" aria-hidden="true" />
                                        <span class="text-gray-400 text-xs">Blank</span>
                                    </div>
                                    <div class="p-2">
                                        <h3 class="font-medium text-gray-900 text-sm">Start Fresh</h3>
                                    </div>
                                    <div
                                        v-if="selectedSiteTemplate === null"
                                        class="absolute top-2 right-2 w-5 h-5 bg-blue-600 rounded-full flex items-center justify-center"
                                    >
                                        <CheckIcon class="h-3 w-3 text-white" aria-hidden="true" />
                                    </div>
                                </button>

                                <!-- Template Cards -->
                                <div
                                    v-for="template in filteredSiteTemplates"
                                    :key="template.id"
                                    :class="[
                                        'relative border-2 rounded-xl text-left transition overflow-hidden group cursor-pointer',
                                        selectedSiteTemplate === template.id
                                            ? 'border-blue-500 ring-2 ring-blue-200'
                                            : 'border-gray-200 hover:border-gray-300'
                                    ]"
                                    @click="selectTemplate(template.id)"
                                >
                                    <div class="relative h-24 bg-gray-100 overflow-hidden">
                                        <img
                                            v-if="template.thumbnail"
                                            :src="template.thumbnail"
                                            :alt="template.name"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                        />
                                        <div v-else class="w-full h-full flex items-center justify-center">
                                            <component :is="getIndustryIcon(template.industry)" class="h-8 w-8 text-gray-300" aria-hidden="true" />
                                        </div>

                                        <!-- Hover overlay -->
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                            <button
                                                type="button"
                                                class="px-2 py-1 bg-white text-gray-900 text-xs font-medium rounded-lg hover:bg-gray-100 transition inline-flex items-center gap-1"
                                                @click.stop="openPreview(template)"
                                            >
                                                <EyeIcon class="h-3 w-3" aria-hidden="true" />
                                                Preview
                                            </button>
                                        </div>

                                        <!-- Premium badge -->
                                        <span
                                            v-if="template.isPremium"
                                            class="absolute top-1.5 left-1.5 px-1.5 py-0.5 bg-gradient-to-r from-purple-600 to-indigo-600 text-white text-[10px] font-medium rounded-full"
                                        >
                                            Pro
                                        </span>
                                    </div>

                                    <div class="p-2">
                                        <h3 class="font-medium text-gray-900 text-sm truncate">{{ template.name }}</h3>
                                        <p class="text-xs text-gray-500">{{ template.pagesCount }} pages</p>
                                    </div>

                                    <div
                                        v-if="selectedSiteTemplate === template.id"
                                        class="absolute top-1.5 right-1.5 w-5 h-5 bg-blue-600 rounded-full flex items-center justify-center"
                                    >
                                        <CheckIcon class="h-3 w-3 text-white" aria-hidden="true" />
                                    </div>
                                </div>
                            </div>

                            <!-- Empty state -->
                            <div v-if="filteredSiteTemplates.length === 0" class="text-center py-6">
                                <BuildingOfficeIcon class="h-10 w-10 text-gray-300 mx-auto mb-2" aria-hidden="true" />
                                <p class="text-gray-500 text-sm">
                                    {{ searchQuery ? 'No templates match your search' : 'No templates for this industry' }}
                                </p>
                                <button
                                    type="button"
                                    class="mt-2 text-blue-600 hover:text-blue-700 text-sm font-medium"
                                    @click="selectedIndustry = 'all'; searchQuery = ''"
                                >
                                    Clear filters
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Footer - Always visible -->
                    <div class="flex items-center justify-between px-5 py-3 border-t bg-gray-50 rounded-b-2xl shrink-0">
                        <button
                            v-if="currentStep > 1"
                            type="button"
                            class="inline-flex items-center gap-1.5 px-3 py-2 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-100 transition"
                            @click="prevStep"
                        >
                            <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                            Back
                        </button>
                        <div v-else></div>

                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                class="px-3 py-2 text-gray-600 text-sm font-medium rounded-lg hover:bg-gray-100 transition"
                                @click="close"
                            >
                                Cancel
                            </button>

                            <button
                                v-if="currentStep === 1"
                                type="button"
                                :disabled="!canProceedToStep2"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
                                @click="nextStep"
                            >
                                Next
                                <ArrowRightIcon class="h-4 w-4" aria-hidden="true" />
                            </button>

                            <button
                                v-else
                                type="button"
                                :disabled="form.processing"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50 transition"
                                @click="submit"
                            >
                                <SparklesIcon v-if="!form.processing" class="h-4 w-4" aria-hidden="true" />
                                {{ form.processing ? 'Creating...' : 'Create Website' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Preview Modal - Fullscreen -->
        <div
            v-if="showPreviewModal && previewTemplate"
            class="fixed inset-0 z-[60] bg-gray-900 overflow-hidden"
        >
            <!-- Fullscreen iframe with elegant scrollbar -->
            <div class="w-full h-full overflow-hidden preview-frame">
                <iframe
                    :src="getPreviewUrl(previewTemplate.id)"
                    class="w-full h-full border-0"
                    :title="`Preview of ${previewTemplate.name}`"
                ></iframe>
            </div>

            <!-- Floating toolbar at bottom -->
            <div class="absolute bottom-6 left-1/2 -translate-x-1/2 bg-white/95 backdrop-blur-sm rounded-full shadow-2xl px-3 py-2 flex items-center gap-1">
                <div class="px-3 py-1 border-r border-gray-200">
                    <p class="text-sm font-medium text-gray-900">{{ previewTemplate.name }}</p>
                </div>
                <a
                    :href="getPreviewUrl(previewTemplate.id)"
                    target="_blank"
                    class="p-2 text-gray-600 hover:bg-gray-100 rounded-full transition"
                    title="Open in new tab"
                >
                    <ComputerDesktopIcon class="h-5 w-5" aria-hidden="true" />
                </a>
                <button
                    type="button"
                    class="px-4 py-2 text-gray-700 font-medium rounded-full hover:bg-gray-100 transition"
                    @click="closePreview"
                >
                    Close
                </button>
                <button
                    type="button"
                    class="px-4 py-2 bg-blue-600 text-white font-medium rounded-full hover:bg-blue-700 transition inline-flex items-center gap-1.5"
                    @click="selectFromPreview"
                >
                    <CheckIcon class="h-4 w-4" aria-hidden="true" />
                    Use Template
                </button>
            </div>

            <!-- Close button top right -->
            <button
                type="button"
                class="absolute top-4 right-4 p-2.5 bg-white/90 backdrop-blur-sm text-gray-700 rounded-full shadow-lg hover:bg-white transition"
                @click="closePreview"
                aria-label="Close preview"
            >
                <XMarkIcon class="h-5 w-5" aria-hidden="true" />
            </button>
        </div>
    </Teleport>
</template>

<style scoped>
/* Hide outer scrollbar, let iframe handle scrolling */
.preview-frame {
    scrollbar-width: none;
    -ms-overflow-style: none;
}
.preview-frame::-webkit-scrollbar {
    display: none;
}
</style>
