<script setup lang="ts">
import { ref, computed, watch, nextTick } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { DocumentTextIcon, PrinterIcon, ArrowDownTrayIcon, EyeIcon } from '@heroicons/vue/24/outline';
import Swal from 'sweetalert2';

interface Template {
    id: number;
    name: string;
    document_type: string;
    visibility: string;
    industry_category: string;
}

interface Props {
    businessProfile: any;
    industryTemplates: Template[];
    customTemplates: Template[];
}

const props = defineProps<Props>();

const form = ref({
    document_type: 'invoice',
    template_id: null as number | null,
    quantity: 50,
    documents_per_page: 1,
    starting_number: '',
    page_size: 'A4',
    row_count: null as number | null,
});

const generating = ref(false);
const previewLoading = ref(false);
const previewHtml = ref<string>('');
const showPreview = ref(false);
const fullscreenPreview = ref(false);
const previewIframe = ref<HTMLIFrameElement | null>(null);
let previewTimeout: ReturnType<typeof setTimeout> | null = null;
let lastPreviewParams = '';

const documentTypes = [
    { value: 'invoice', label: 'Invoice', prefix: 'INV' },
    { value: 'receipt', label: 'Receipt', prefix: 'RCPT' },
    { value: 'quotation', label: 'Quotation', prefix: 'QTN' },
    { value: 'delivery_note', label: 'Delivery Note', prefix: 'DN' },
    { value: 'proforma_invoice', label: 'Proforma Invoice', prefix: 'PI' },
    { value: 'credit_note', label: 'Credit Note', prefix: 'CN' },
    { value: 'debit_note', label: 'Debit Note', prefix: 'DBN' },
    { value: 'purchase_order', label: 'Purchase Order', prefix: 'PO' },
];

const layoutOptions = [
    { value: 1, label: '1 per page', description: 'Full-page documents (A4)' },
    { value: 2, label: '2 per page', description: 'Half-page documents' },
    { value: 4, label: '4 per page', description: 'Quarter-page (Recommended for receipts)' },
    { value: 6, label: '6 per page', description: 'Compact layout (Good for receipts)' },
    { value: 8, label: '8 per page', description: 'Small receipts' },
    { value: 10, label: '10 per page', description: 'Mini receipts' },
];

const recommendedLayout = computed(() => {
    // Recommend layouts based on document type
    if (form.value.document_type === 'receipt') {
        return 4; // 4 receipts per page is ideal
    }
    return 1; // Full page for invoices, quotations, etc.
});

// Auto-set recommended layout when document type changes
watch(() => form.value.document_type, async (newType) => {
    form.value.documents_per_page = recommendedLayout.value;
    updateStartingNumber();
    await nextTick();
});

const pageSizeOptions = [
    { value: 'A4', label: 'A4', description: '210mm × 297mm (Standard)' },
    { value: 'A5', label: 'A5', description: '148mm × 210mm (Half A4)' },
    { value: 'custom', label: 'Custom', description: 'Custom dimensions' },
];

const filteredTemplates = computed(() => {
    return props.industryTemplates.filter(t => t.document_type === form.value.document_type);
});

const selectedDocType = computed(() => {
    return documentTypes.find(dt => dt.value === form.value.document_type);
});

const selectedTemplate = computed(() => {
    if (!form.value.template_id) return null;
    return [...props.industryTemplates, ...props.customTemplates].find(t => t.id === form.value.template_id);
});

const totalPages = computed(() => {
    return Math.ceil(form.value.quantity / form.value.documents_per_page);
});

const defaultRowCount = computed(() => {
    return {
        1: 8,   // Full page
        2: 4,   // Half page
        4: 2,   // Quarter page
        6: 1,   // Compact
        8: 1,   // Small
        10: 1,  // Mini
    }[form.value.documents_per_page] || 4;
});

const currentRowCount = computed(() => {
    return form.value.row_count ?? defaultRowCount.value;
});

const previewDimensions = computed(() => {
    if (form.value.page_size === 'A5') {
        return { width: '148mm', height: '210mm' };
    }
    return { width: '210mm', height: '297mm' }; // A4
});

// Calculate scale to fit preview in container
const previewScale = computed(() => {
    // A4 width is 210mm = ~793px at 96dpi
    // Increase scale to 85% to use more space
    return 0.85;
});

// Auto-generate starting number when document type changes
const updateStartingNumber = () => {
    const prefix = selectedDocType.value?.prefix || 'DOC';
    const year = new Date().getFullYear();
    form.value.starting_number = `${prefix}-${year}-0001`;
};

// Initialize starting number
updateStartingNumber();

// Load preview when user changes settings (but not on initial template selection)
const loadPreviewOnChange = async () => {
    if (form.value.template_id && !previewLoading.value) {
        // Small delay to allow other watchers to complete first
        await nextTick();
        setTimeout(async () => {
            if (!previewLoading.value) {
                await loadPreview();
            }
        }, 100);
    }
};

// Get current preview parameters as a string for comparison
const getPreviewParamsKey = () => {
    return JSON.stringify({
        template_id: form.value.template_id,
        documents_per_page: form.value.documents_per_page,
        starting_number: form.value.starting_number,
        page_size: form.value.page_size,
    });
};

// Watch for changes to layout and page size ONLY (not starting_number or row_count)
watch([() => form.value.documents_per_page, () => form.value.page_size], async () => {
    // Skip if no template selected or preview is loading
    if (!form.value.template_id || previewLoading.value) {
        return;
    }
    
    // Check if parameters actually changed
    const currentParams = getPreviewParamsKey();
    if (currentParams === lastPreviewParams) {
        return;
    }
    
    // Clear existing timeout
    if (previewTimeout) {
        clearTimeout(previewTimeout);
    }
    
    // Reset row count to default when layout changes
    if (form.value.documents_per_page !== 2) {
        form.value.row_count = null;
    }
    
    // Debounce preview loading by 500ms to avoid conflicts with template changes
    previewTimeout = setTimeout(async () => {
        if (!previewLoading.value && form.value.template_id) {
            await loadPreview();
        }
    }, 500);
});

// Watch row count changes and update preview instantly without reload
watch(() => form.value.row_count, (newCount) => {
    if (showPreview.value && previewHtml.value && !previewLoading.value) {
        updatePreviewRowCount(newCount ?? defaultRowCount.value);
    }
});

const loadPreview = async () => {
    if (!form.value.template_id) return;

    // Get current parameters
    const currentParams = getPreviewParamsKey();
    
    // Skip if we're already loading with the same parameters
    if (previewLoading.value && currentParams === lastPreviewParams) {
        return;
    }
    
    previewLoading.value = true;
    showPreview.value = true;

    try {
        const response = await fetch(route('bizdocs.stationery.preview'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                document_type: form.value.document_type,
                template_id: form.value.template_id,
                documents_per_page: form.value.documents_per_page,
                starting_number: form.value.starting_number,
                page_size: form.value.page_size,
                row_count: form.value.row_count,
            }),
        });

        previewHtml.value = await response.text();
        
        // Only update lastPreviewParams after successful load
        lastPreviewParams = currentParams;
    } catch (error: any) {
        console.error('Preview failed:', error);
        Swal.fire({
            icon: 'error',
            title: 'Preview Failed',
            text: error.message || 'An error occurred while loading preview',
        });
    } finally {
        previewLoading.value = false;
    }
};

const resizeIframe = () => {
    const iframe = previewIframe.value;
    if (!iframe || !iframe.contentDocument) return;
    
    const height = iframe.contentDocument.body?.scrollHeight;
    if (height) {
        iframe.style.height = height + 'px';
    }
};

const updatePreviewRowCount = (newRowCount: number) => {
    // Find all table bodies in the preview
    const previewContainer = document.querySelector('.w-full[v-html]') as HTMLElement;
    if (!previewContainer) return;

    const tables = previewContainer.querySelectorAll('.items-table tbody');
    
    tables.forEach((tbody) => {
        const currentRows = tbody.querySelectorAll('tr').length;
        
        if (newRowCount > currentRows) {
            // Add rows
            const rowsToAdd = newRowCount - currentRows;
            for (let i = 0; i < rowsToAdd; i++) {
                const newRow = document.createElement('tr');
                for (let j = 0; j < 6; j++) { // 6 columns
                    const td = document.createElement('td');
                    td.innerHTML = '&nbsp;';
                    newRow.appendChild(td);
                }
                tbody.appendChild(newRow);
            }
        } else if (newRowCount < currentRows) {
            // Remove rows
            const rowsToRemove = currentRows - newRowCount;
            for (let i = 0; i < rowsToRemove; i++) {
                const lastRow = tbody.querySelector('tr:last-child');
                if (lastRow) {
                    lastRow.remove();
                }
            }
        }
    });
};

const generateStationery = async () => {
    if (!form.value.template_id) {
        Swal.fire({
            icon: 'warning',
            title: 'Template Required',
            text: 'Please select a template',
        });
        return;
    }

    generating.value = true;

    try {
        const response = await fetch(route('bizdocs.stationery.generate'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify(form.value),
        });

        if (!response.ok) {
            // Try to parse error message
            const contentType = response.headers.get('content-type');
            let errorMessage = 'Failed to generate stationery';
            
            if (contentType && contentType.includes('application/json')) {
                const data = await response.json();
                errorMessage = data.message || errorMessage;
            } else {
                const text = await response.text();
                console.error('Server error:', text);
            }
            
            throw new Error(errorMessage);
        }

        // Get the PDF blob
        const blob = await response.blob();
        
        // Create download link
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `stationery_${form.value.document_type}_${new Date().toISOString().slice(0,10)}_${form.value.quantity}_docs.pdf`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        
        Swal.fire({
            icon: 'success',
            title: 'Stationery Generated!',
            html: `
                <p>Your stationery book with ${form.value.quantity} documents has been downloaded.</p>
                <p class="text-sm text-gray-600 mt-2">Total pages: ${totalPages.value}</p>
            `,
            confirmButtonText: 'Generate Another',
        });
    } catch (error: any) {
        console.error('Generation error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Generation Failed',
            text: error.message || 'An error occurred while generating stationery',
        });
    } finally {
        generating.value = false;
    }
};
</script>

<template>
    <AppLayout title="Stationery Generator">
        <Head title="Stationery Generator" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Print Stationery Generator</h1>
                    <p class="mt-2 text-gray-600">
                        Generate print-ready document books with pre-numbered blank documents
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left: Configuration Form (1/3 width) -->
                    <div class="bg-white shadow-sm rounded-lg p-6 lg:col-span-1">
                        <h2 class="text-lg font-semibold text-gray-900 mb-6">Configuration</h2>
                        
                        <form @submit.prevent class="space-y-6">
                            <!-- Document Type -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Document Type
                                </label>
                                <select
                                    v-model="form.document_type"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                    <option v-for="type in documentTypes" :key="type.value" :value="type.value">
                                        {{ type.label }}
                                    </option>
                                </select>
                            </div>

                            <!-- Template Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Template
                                </label>
                                <select
                                    v-model="form.template_id"
                                    @change="loadPreviewOnChange"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                    <option :value="null">Select a template...</option>
                                    <optgroup label="Industry Templates" v-if="filteredTemplates.length > 0">
                                        <option v-for="template in filteredTemplates" :key="template.id" :value="template.id">
                                            {{ template.name }}
                                        </option>
                                    </optgroup>
                                    <optgroup label="My Custom Templates" v-if="customTemplates.length > 0">
                                        <option v-for="template in customTemplates" :key="template.id" :value="template.id">
                                            {{ template.name }}
                                        </option>
                                    </optgroup>
                                </select>
                                <p class="mt-1 text-sm text-gray-500">
                                    Preview will load automatically
                                </p>
                            </div>

                            <!-- Page Size -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Page Size
                                </label>
                                <select
                                    v-model="form.page_size"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                    <option v-for="option in pageSizeOptions" :key="option.value" :value="option.value">
                                        {{ option.label }} - {{ option.description }}
                                    </option>
                                </select>
                                <p class="mt-1 text-sm text-gray-500">
                                    Preview updates automatically
                                </p>
                            </div>

                            <!-- Documents Per Page -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Documents Per Page
                                </label>
                                <select
                                    v-model.number="form.documents_per_page"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                    <option v-for="option in layoutOptions" :key="option.value" :value="option.value">
                                        {{ option.label }} - {{ option.description }}
                                    </option>
                                </select>
                                <p class="mt-1 text-sm text-gray-500">
                                    Preview updates automatically
                                </p>
                            </div>

                            <!-- Row Count Control -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Table Rows
                                </label>
                                <div class="flex items-center gap-3">
                                    <button
                                        type="button"
                                        @click="form.row_count = Math.max(1, currentRowCount - 1)"
                                        class="p-2 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                        :disabled="currentRowCount <= 1"
                                        aria-label="Decrease row count"
                                    >
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                        </svg>
                                    </button>
                                    
                                    <div class="flex-1 text-center">
                                        <div class="text-2xl font-bold text-gray-900">{{ currentRowCount }}</div>
                                        <div class="text-xs text-gray-500">rows per document</div>
                                    </div>
                                    
                                    <button
                                        type="button"
                                        @click="form.row_count = Math.min(50, currentRowCount + 1)"
                                        class="p-2 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                        :disabled="currentRowCount >= 50"
                                        aria-label="Increase row count"
                                    >
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                    </button>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">
                                    Default: {{ defaultRowCount }} rows (adjust to fit your needs)
                                </p>
                            </div>

                            <!-- Quantity -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Number of Documents
                                </label>
                                <input
                                    v-model.number="form.quantity"
                                    type="number"
                                    min="1"
                                    max="500"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                                <p class="mt-1 text-sm text-gray-500">
                                    Total pages: {{ totalPages }}
                                </p>
                            </div>

                            <!-- Starting Number -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Starting Document Number
                                </label>
                                <input
                                    v-model="form.starting_number"
                                    type="text"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="INV-2026-0001"
                                />
                            </div>

                            <!-- Summary Info -->
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex items-start">
                                    <DocumentTextIcon class="h-5 w-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" aria-hidden="true" />
                                    <div class="text-sm text-blue-800">
                                        <p class="font-medium mb-1">Summary:</p>
                                        <ul class="list-disc list-inside space-y-1">
                                            <li>{{ form.quantity }} {{ selectedDocType?.label.toLowerCase() }} documents</li>
                                            <li>{{ form.documents_per_page }} per page ({{ totalPages }} total pages)</li>
                                            <li>{{ currentRowCount }} rows per document</li>
                                            <li>Page size: {{ form.page_size }}</li>
                                            <li>Starting from {{ form.starting_number }}</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center justify-between pt-4 border-t gap-3">
                                <button
                                    type="button"
                                    @click="$inertia.visit(route('bizdocs.dashboard'))"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                                >
                                    Cancel
                                </button>
                                
                                <button
                                    type="button"
                                    @click="generateStationery"
                                    :disabled="generating || !form.template_id"
                                    class="inline-flex items-center px-6 py-3 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <PrinterIcon v-if="!generating" class="h-5 w-5 mr-2" aria-hidden="true" />
                                    <svg v-else class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ generating ? 'Generating...' : 'Generate Stationery' }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Right: Live Preview (2/3 width) -->
                    <div class="bg-white shadow-sm rounded-lg p-6 lg:col-span-2" :class="{ 'fixed inset-0 z-50': fullscreenPreview }">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">Live Preview</h2>
                            <div class="flex items-center gap-2">
                                <span v-if="selectedTemplate" class="text-sm text-gray-500">
                                    {{ selectedTemplate.name }}
                                </span>
                                <button
                                    v-if="showPreview && !fullscreenPreview"
                                    @click="fullscreenPreview = true"
                                    class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-md"
                                    title="Fullscreen"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                                    </svg>
                                </button>
                                <button
                                    v-if="fullscreenPreview"
                                    @click="fullscreenPreview = false"
                                    class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-md"
                                    title="Exit Fullscreen"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div v-if="!form.template_id" class="flex flex-col items-center justify-center h-96 text-center border-2 border-dashed border-gray-300 rounded-lg">
                            <EyeIcon class="h-12 w-12 text-gray-400 mb-4" aria-hidden="true" />
                            <p class="text-gray-500">Select a template to see preview</p>
                        </div>

                        <div v-else-if="previewLoading" class="flex flex-col items-center justify-center h-96">
                            <svg class="animate-spin h-10 w-10 text-blue-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="text-gray-500">Loading preview...</p>
                        </div>

                        <div v-else-if="showPreview" class="border border-gray-200 rounded-lg overflow-hidden bg-gray-50" :class="{ 'h-screen': fullscreenPreview }">
                            <div class="bg-gray-100 px-4 py-2 text-xs text-gray-600 border-b border-gray-200 flex items-center justify-between">
                                <span>Sample: {{ form.documents_per_page }} document(s) per page</span>
                                <span class="text-gray-400">Preview scaled to fit ({{ Math.round(previewScale * 100) }}%)</span>
                            </div>
                            <div class="overflow-auto flex items-start justify-center" :style="{ height: fullscreenPreview ? 'calc(100vh - 150px)' : '1000px', background: '#e5e7eb', padding: '8px' }">
                                <div :style="{ transformOrigin: 'top center', transform: `scale(${previewScale})` }">
                                    <div style="width: 210mm; background: white; box-shadow: 0 4px 16px rgba(0,0,0,0.2);">
                                        <iframe
                                            :srcdoc="previewHtml"
                                            style="width: 210mm; border: none; display: block;"
                                            ref="previewIframe"
                                            @load="resizeIframe"
                                        ></iframe>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
