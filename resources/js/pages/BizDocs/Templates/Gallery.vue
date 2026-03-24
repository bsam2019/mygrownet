<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, computed } from 'vue';
import { ArrowLeftIcon, CheckCircleIcon, EyeIcon } from '@heroicons/vue/24/outline';

interface Template {
    id: number;
    name: string;
    document_type: string;
    visibility: string;
    industry_category: string;
    template_structure: any;
    is_default: boolean;
    thumbnail_path?: string;
}

interface Props {
    templates: Template[];
    documentType: string;
    currentTemplateId: number | null;
}

const props = defineProps<Props>();

const selectedCategory = ref<string>('all');
const previewTemplate = ref<Template | null>(null);
const currentPage = ref<number>(1);
const itemsPerPage = 6;

const categories = computed(() => {
    const cats = new Set<string>();
    props.templates.forEach(t => {
        if (t.industry_category) cats.add(t.industry_category);
    });
    return ['all', ...Array.from(cats)];
});

const filteredTemplates = computed(() => {
    let filtered = props.templates;
    if (selectedCategory.value !== 'all') {
        filtered = filtered.filter(t => t.industry_category === selectedCategory.value);
    }
    return filtered;
});

const paginatedTemplates = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return filteredTemplates.value.slice(start, end);
});

const totalPages = computed(() => {
    return Math.ceil(filteredTemplates.value.length / itemsPerPage);
});

const goToPage = (page: number) => {
    currentPage.value = page;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

// Reset to page 1 when category changes
const changeCategory = (cat: string) => {
    selectedCategory.value = cat;
    currentPage.value = 1;
};

const formatCategory = (cat: string): string => {
    return cat.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
};

const formatDocType = (type: string): string => {
    const labels: Record<string, string> = {
        invoice: 'Invoice',
        receipt: 'Receipt',
        quotation: 'Quotation',
        delivery_note: 'Delivery Note',
        proforma_invoice: 'Proforma Invoice',
        credit_note: 'Credit Note',
        debit_note: 'Debit Note',
        purchase_order: 'Purchase Order',
    };
    return labels[type] || type;
};

const selectTemplate = async (template: Template) => {
    // Use the template's document type, not the gallery's filter
    const docType = props.documentType === 'all' ? template.document_type : props.documentType;
    
    router.post(`/bizdocs/templates/${template.id}/set-default`, {
        document_type: docType,
    }, {
        preserveScroll: false,
        preserveState: false,
        onSuccess: () => {
            // Redirect to create page with the correct document type
            // Force a fresh visit to ensure new data is loaded
            router.visit(`/bizdocs/documents/create?type=${docType}`, {
                preserveState: false,
                preserveScroll: false,
            });
        }
    });
};

const openPreview = (template: Template) => {
    previewTemplate.value = template;
};

const closePreview = () => {
    previewTemplate.value = null;
};
</script>

<template>
    <Head :title="`${formatDocType(documentType)} Templates`" />

    <AppLayout>
        <div class="min-h-screen bg-slate-50 py-8 px-4">
            <div class="max-w-7xl mx-auto">

                <!-- Header -->
                <div class="mb-8">
                    <a
                        :href="`/bizdocs/documents/create?type=${documentType}`"
                        class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-blue-600 mb-3">
                        <ArrowLeftIcon class="w-4 h-4" />
                        Back to Create Document
                    </a>
                    <h1 class="text-3xl font-bold text-slate-900">Choose Your Template</h1>
                    <p class="text-slate-500 mt-2">Select a template design for your {{ formatDocType(documentType).toLowerCase() }}s</p>
                </div>

                <!-- Category Filter -->
                <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
                    <button
                        v-for="cat in categories"
                        :key="cat"
                        @click="changeCategory(cat)"
                        :class="[
                            'px-4 py-2 rounded-lg text-sm font-medium transition-all whitespace-nowrap',
                            selectedCategory === cat
                                ? 'bg-indigo-600 text-white'
                                : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'
                        ]">
                        {{ formatCategory(cat) }}
                    </button>
                </div>

                <!-- Results count -->
                <div class="mb-4 text-sm text-slate-600">
                    Showing {{ paginatedTemplates.length }} of {{ filteredTemplates.length }} templates
                </div>

                <!-- Templates Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <div
                        v-for="template in paginatedTemplates"
                        :key="template.id"
                        class="bg-white rounded-xl border-2 transition-all hover:shadow-lg"
                        :class="currentTemplateId === template.id ? 'border-indigo-500' : 'border-slate-200'">
                        
                        <!-- Template Preview -->
                        <div class="aspect-[8.5/11] bg-slate-100 rounded-t-xl overflow-hidden relative group">
                            <!-- Loading skeleton -->
                            <div class="absolute inset-0 bg-gradient-to-br from-slate-200 to-slate-100 animate-pulse"></div>
                            
                            <!-- Lazy-loaded PDF Preview iframe -->
                            <iframe
                                :src="`/bizdocs/templates/${template.id}/preview`"
                                class="w-full h-full border-0 pointer-events-none scale-[0.4] origin-top-left relative z-10"
                                style="width: 250%; height: 250%;"
                                scrolling="no"
                                loading="lazy"
                                @load="$event.target.previousElementSibling?.remove()"
                            ></iframe>
                            
                            <!-- Preview overlay -->
                            <div class="absolute inset-0 bg-slate-900/0 group-hover:bg-slate-900/50 transition-all flex items-center justify-center opacity-0 group-hover:opacity-100 z-20">
                                <button
                                    @click="openPreview(template)"
                                    class="px-4 py-2 bg-white text-slate-900 rounded-lg font-medium flex items-center gap-2 shadow-lg">
                                    <EyeIcon class="w-4 h-4" aria-hidden="true" />
                                    Full Preview
                                </button>
                            </div>

                            <!-- Current badge -->
                            <div v-if="currentTemplateId === template.id" class="absolute top-3 right-3 z-20">
                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-indigo-600 text-white text-xs font-semibold rounded-full shadow-lg">
                                    <CheckCircleIcon class="w-3 h-3" aria-hidden="true" />
                                    Current
                                </span>
                            </div>
                        </div>

                        <!-- Template Info -->
                        <div class="p-4">
                            <h3 class="font-semibold text-slate-900">{{ template.name }}</h3>
                            <p class="text-xs text-slate-500 mt-1">
                                {{ template.visibility === 'industry' ? 'Industry Template' : 'Custom Template' }}
                                <span v-if="template.industry_category"> • {{ formatCategory(template.industry_category) }}</span>
                            </p>

                            <button
                                v-if="currentTemplateId !== template.id"
                                @click="selectTemplate(template)"
                                class="w-full mt-4 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-all">
                                Use This Template
                            </button>
                            <div v-else class="w-full mt-4 px-4 py-2 bg-indigo-50 text-indigo-700 text-sm font-medium rounded-lg text-center">
                                Currently Selected
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="totalPages > 1" class="flex items-center justify-center gap-2">
                    <button
                        @click="goToPage(currentPage - 1)"
                        :disabled="currentPage === 1"
                        :class="[
                            'px-4 py-2 rounded-lg text-sm font-medium transition-all',
                            currentPage === 1
                                ? 'bg-slate-100 text-slate-400 cursor-not-allowed'
                                : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'
                        ]">
                        Previous
                    </button>
                    
                    <div class="flex gap-1">
                        <button
                            v-for="page in totalPages"
                            :key="page"
                            @click="goToPage(page)"
                            :class="[
                                'w-10 h-10 rounded-lg text-sm font-medium transition-all',
                                currentPage === page
                                    ? 'bg-indigo-600 text-white'
                                    : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'
                            ]">
                            {{ page }}
                        </button>
                    </div>
                    
                    <button
                        @click="goToPage(currentPage + 1)"
                        :disabled="currentPage === totalPages"
                        :class="[
                            'px-4 py-2 rounded-lg text-sm font-medium transition-all',
                            currentPage === totalPages
                                ? 'bg-slate-100 text-slate-400 cursor-not-allowed'
                                : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'
                        ]">
                        Next
                    </button>
                </div>

            </div>
        </div>

        <!-- Preview Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0">
                <div
                    v-if="previewTemplate"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-sm"
                    @click.self="closePreview">
                    
                    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col">
                        <!-- Header -->
                        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900">{{ previewTemplate.name }}</h3>
                                <p class="text-sm text-slate-500">Template Preview</p>
                            </div>
                            <button
                                @click="closePreview"
                                class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Preview Content -->
                        <div class="flex-1 overflow-y-auto p-6 bg-slate-100">
                            <div class="bg-white rounded-lg shadow-2xl max-w-3xl mx-auto overflow-hidden" style="min-height: 800px;">
                                <!-- Full-size PDF preview - use key to force reload -->
                                <iframe
                                    :key="`preview-${previewTemplate.id}`"
                                    :src="`/bizdocs/templates/${previewTemplate.id}/preview`"
                                    class="w-full h-full border-0"
                                    style="min-height: 800px;"
                                ></iframe>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="flex items-center justify-between px-6 py-4 border-t border-slate-200 bg-slate-50">
                            <button
                                @click="closePreview"
                                class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg">
                                Close
                            </button>
                            <button
                                @click="selectTemplate(previewTemplate); closePreview()"
                                class="px-6 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700">
                                Use This Template
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>
