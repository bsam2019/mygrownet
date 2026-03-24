<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { DocumentTextIcon, PrinterIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline';
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
});

const generating = ref(false);
const generatedPdfUrl = ref<string | null>(null);

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
    { value: 1, label: '1 per page', description: 'Full-size documents (A4)' },
    { value: 2, label: '2 per page', description: 'Half-page documents' },
    { value: 4, label: '4 per page', description: 'Quarter-page receipts' },
];

const filteredTemplates = computed(() => {
    return props.industryTemplates.filter(t => t.document_type === form.value.document_type);
});

const selectedDocType = computed(() => {
    return documentTypes.find(dt => dt.value === form.value.document_type);
});

const totalPages = computed(() => {
    return Math.ceil(form.value.quantity / form.value.documents_per_page);
});

// Auto-generate starting number when document type changes
const updateStartingNumber = () => {
    const prefix = selectedDocType.value?.prefix || 'DOC';
    const year = new Date().getFullYear();
    form.value.starting_number = `${prefix}-${year}-0001`;
};

// Initialize starting number
updateStartingNumber();

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
    generatedPdfUrl.value = null;

    try {
        const response = await fetch(route('bizdocs.stationery.generate'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify(form.value),
        });

        const data = await response.json();

        if (data.success) {
            generatedPdfUrl.value = data.download_url;
            
            Swal.fire({
                icon: 'success',
                title: 'Stationery Generated!',
                html: `
                    <p>Your stationery book with ${form.value.quantity} documents is ready.</p>
                    <p class="text-sm text-gray-600 mt-2">Total pages: ${totalPages.value}</p>
                `,
                showCancelButton: true,
                confirmButtonText: 'Download PDF',
                cancelButtonText: 'Generate Another',
            }).then((result) => {
                if (result.isConfirmed && generatedPdfUrl.value) {
                    window.open(generatedPdfUrl.value, '_blank');
                }
            });
        } else {
            throw new Error(data.message || 'Failed to generate stationery');
        }
    } catch (error: any) {
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
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Print Stationery Generator</h1>
                    <p class="mt-2 text-gray-600">
                        Generate print-ready document books with pre-numbered blank documents
                    </p>
                </div>

                <!-- Main Form -->
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <div class="space-y-6">
                        <!-- Document Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Document Type
                            </label>
                            <select
                                v-model="form.document_type"
                                @change="updateStartingNumber"
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
                                Choose the template design for your stationery
                            </p>
                        </div>

                        <!-- Quantity and Layout -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                                    Maximum: 500 documents
                                </p>
                            </div>

                            <!-- Documents Per Page -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Layout
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
                                    Total pages: {{ totalPages }}
                                </p>
                            </div>
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
                            <p class="mt-1 text-sm text-gray-500">
                                First document will be numbered: {{ form.starting_number }}
                            </p>
                        </div>

                        <!-- Preview Info -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <DocumentTextIcon class="h-5 w-5 text-blue-600 mt-0.5 mr-3" aria-hidden="true" />
                                <div class="text-sm text-blue-800">
                                    <p class="font-medium mb-1">What you'll get:</p>
                                    <ul class="list-disc list-inside space-y-1">
                                        <li>{{ form.quantity }} blank {{ selectedDocType?.label.toLowerCase() }} documents</li>
                                        <li>Pre-numbered from {{ form.starting_number }}</li>
                                        <li>{{ form.documents_per_page }} document(s) per page ({{ totalPages }} total pages)</li>
                                        <li>Print-ready PDF for binding into physical book</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Generate Button -->
                        <div class="flex items-center justify-between pt-4 border-t">
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
                                :disabled="generating"
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

                        <!-- Download Button (shown after generation) -->
                        <div v-if="generatedPdfUrl" class="pt-4 border-t">
                            <a
                                :href="generatedPdfUrl"
                                target="_blank"
                                class="inline-flex items-center justify-center w-full px-6 py-3 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700"
                            >
                                <ArrowDownTrayIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                                Download Generated PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
