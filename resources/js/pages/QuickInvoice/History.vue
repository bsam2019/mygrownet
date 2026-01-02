<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeftIcon, DocumentTextIcon, TrashIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';
import { ref } from 'vue';

interface Document {
    id: string;
    type: string;
    type_label: string;
    document_number: string;
    client_info: { name: string };
    total: number;
    currency: string;
    status: string;
    created_at: string;
}

interface Props {
    documents: Document[];
}

const props = defineProps<Props>();
const documents = ref(props.documents);

const formatCurrency = (amount: number, currency: string) => {
    const symbols: Record<string, string> = { ZMW: 'K', USD: '$', EUR: '€', GBP: '£', ZAR: 'R' };
    return `${symbols[currency] || currency} ${amount.toFixed(2)}`;
};

const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
};

const deleteDocument = async (id: string) => {
    if (!confirm('Are you sure you want to delete this document?')) return;
    
    try {
        await axios.delete(route('quick-invoice.destroy', id));
        documents.value = documents.value.filter(d => d.id !== id);
    } catch (error) {
        alert('Failed to delete document');
    }
};

const downloadPdf = (id: string) => {
    window.open(route('quick-invoice.pdf', id), '_blank');
};
</script>

<template>
    <Head title="Document History" />
    
    <div class="min-h-screen bg-gray-50">
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center gap-4">
                    <Link :href="route('quick-invoice.index')" class="p-2 hover:bg-gray-100 rounded-lg" aria-label="Go back">
                        <ArrowLeftIcon class="w-5 h-5 text-gray-600" aria-hidden="true" />
                    </Link>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">Document History</h1>
                        <p class="text-sm text-gray-500">Your recently created documents</p>
                    </div>
                </div>
            </div>
        </header>

        <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div v-if="documents.length === 0" class="text-center py-12">
                <DocumentTextIcon class="w-12 h-12 text-gray-400 mx-auto mb-4" aria-hidden="true" />
                <h3 class="text-lg font-medium text-gray-900 mb-2">No documents yet</h3>
                <p class="text-gray-500 mb-6">Create your first invoice or delivery note</p>
                <Link :href="route('quick-invoice.index')" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700">
                    Create Document
                </Link>
            </div>

            <div v-else class="space-y-4">
                <div v-for="doc in documents" :key="doc.id" class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <DocumentTextIcon class="w-5 h-5 text-blue-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ doc.document_number }}</p>
                            <p class="text-sm text-gray-500">{{ doc.type_label }} • {{ doc.client_info.name }}</p>
                            <p class="text-xs text-gray-400">{{ formatDate(doc.created_at) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="font-semibold text-gray-900">{{ formatCurrency(doc.total, doc.currency) }}</span>
                        <div class="flex gap-2">
                            <button @click="downloadPdf(doc.id)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" aria-label="Download PDF">
                                <ArrowDownTrayIcon class="w-5 h-5" aria-hidden="true" />
                            </button>
                            <button @click="deleteDocument(doc.id)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" aria-label="Delete document">
                                <TrashIcon class="w-5 h-5" aria-hidden="true" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
