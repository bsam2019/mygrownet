<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
    ArrowLeftIcon, 
    DevicePhoneMobileIcon,
    DeviceTabletIcon,
    ComputerDesktopIcon,
    CheckIcon,
    DocumentDuplicateIcon,
} from '@heroicons/vue/24/outline';

interface TemplatePage {
    id: number;
    title: string;
    slug: string;
    isHomepage: boolean;
    showInNav: boolean;
}

interface SiteTemplate {
    id: number;
    name: string;
    slug: string;
    description: string;
    industry: string;
    thumbnail: string | null;
    theme: Record<string, string> | null;
    settings: Record<string, any> | null;
    isPremium: boolean;
    pages: TemplatePage[];
}

const props = defineProps<{
    template: SiteTemplate;
    previewUrl: string;
}>();

const currentPageSlug = ref('home');
const viewportSize = ref<'desktop' | 'tablet' | 'mobile'>('desktop');
const iframeLoading = ref(true);

const currentPage = computed(() => {
    return props.template.pages.find(p => p.slug === currentPageSlug.value) 
        || props.template.pages.find(p => p.isHomepage)
        || props.template.pages[0];
});

const viewportClass = computed(() => {
    switch (viewportSize.value) {
        case 'mobile': return 'w-[375px]';
        case 'tablet': return 'w-[768px]';
        default: return 'w-full';
    }
});

const iframeUrl = computed(() => {
    return `${props.previewUrl}/${currentPageSlug.value}`;
});

const onIframeLoad = () => {
    iframeLoading.value = false;
};

watch(currentPageSlug, () => {
    iframeLoading.value = true;
});
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex flex-col">
        <Head :title="`Preview: ${template.name} - GrowBuilder`" />

        <!-- Top Bar -->
        <div class="bg-white border-b border-gray-200 sticky top-0 z-50 flex-shrink-0">
            <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6">
                <div class="flex items-center justify-between h-10">
                    <!-- Back & Template Info -->
                    <div class="flex items-center gap-2">
                        <Link
                            :href="route('growbuilder.sites.create')"
                            class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-gray-700"
                        >
                            <ArrowLeftIcon class="h-3.5 w-3.5" aria-hidden="true" />
                            Back
                        </Link>
                        <div class="h-3 w-px bg-gray-200"></div>
                        <h1 class="text-xs font-semibold text-gray-900">{{ template.name }}</h1>
                    </div>

                    <!-- Viewport Switcher -->
                    <div class="flex items-center gap-0.5 bg-gray-100 rounded-md p-0.5">
                        <button
                            type="button"
                            :class="[
                                'p-1 rounded transition',
                                viewportSize === 'desktop' ? 'bg-white shadow text-blue-600' : 'text-gray-500 hover:text-gray-700'
                            ]"
                            @click="viewportSize = 'desktop'"
                            aria-label="Desktop view"
                        >
                            <ComputerDesktopIcon class="h-3.5 w-3.5" aria-hidden="true" />
                        </button>
                        <button
                            type="button"
                            :class="[
                                'p-1 rounded transition',
                                viewportSize === 'tablet' ? 'bg-white shadow text-blue-600' : 'text-gray-500 hover:text-gray-700'
                            ]"
                            @click="viewportSize = 'tablet'"
                            aria-label="Tablet view"
                        >
                            <DeviceTabletIcon class="h-3.5 w-3.5" aria-hidden="true" />
                        </button>
                        <button
                            type="button"
                            :class="[
                                'p-1 rounded transition',
                                viewportSize === 'mobile' ? 'bg-white shadow text-blue-600' : 'text-gray-500 hover:text-gray-700'
                            ]"
                            @click="viewportSize = 'mobile'"
                            aria-label="Mobile view"
                        >
                            <DevicePhoneMobileIcon class="h-3.5 w-3.5" aria-hidden="true" />
                        </button>
                    </div>

                    <!-- Use Template Button -->
                    <Link
                        :href="route('growbuilder.sites.create', { template: template.id })"
                        class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-600 text-white text-xs font-medium rounded-md hover:bg-blue-700 transition"
                    >
                        <CheckIcon class="h-3.5 w-3.5" aria-hidden="true" />
                        Use Template
                    </Link>
                </div>
            </div>
        </div>

        <!-- Page Navigation -->
        <div class="bg-white border-b border-gray-200 flex-shrink-0">
            <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6">
                <div class="flex items-center gap-0.5 py-1 overflow-x-auto">
                    <button
                        v-for="page in template.pages"
                        :key="page.slug"
                        type="button"
                        :class="[
                            'px-2 py-0.5 text-xs font-medium rounded transition whitespace-nowrap inline-flex items-center gap-1',
                            currentPageSlug === page.slug
                                ? 'bg-blue-100 text-blue-700'
                                : 'text-gray-600 hover:bg-gray-100'
                        ]"
                        @click="currentPageSlug = page.slug"
                    >
                        <DocumentDuplicateIcon class="h-3 w-3" aria-hidden="true" />
                        {{ page.title }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Preview Frame -->
        <div class="flex-1 py-2 px-3 overflow-auto">
            <div 
                :class="[
                    'mx-auto bg-white shadow-xl rounded-lg overflow-hidden transition-all duration-300 relative',
                    viewportClass
                ]"
                style="height: calc(100vh - 100px); min-height: 500px;"
            >
                <!-- Loading Overlay -->
                <div 
                    v-if="iframeLoading"
                    class="absolute inset-0 bg-white flex items-center justify-center z-10"
                >
                    <div class="text-center">
                        <div class="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
                        <p class="text-sm text-gray-500">Loading preview...</p>
                    </div>
                </div>

                <!-- Preview iframe -->
                <iframe
                    :src="iframeUrl"
                    class="w-full h-full border-0"
                    @load="onIframeLoad"
                    :title="`Preview of ${template.name} - ${currentPage?.title || 'Home'}`"
                ></iframe>
            </div>
        </div>
    </div>
</template>
