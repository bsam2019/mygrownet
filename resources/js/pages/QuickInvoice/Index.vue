<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { DocumentTextIcon, TruckIcon, DocumentCheckIcon, ReceiptPercentIcon, ArrowRightIcon, CheckIcon } from '@heroicons/vue/24/outline';

interface DocumentType {
    value: string;
    label: string;
    description: string;
}

interface Props {
    documentTypes: DocumentType[];
}

defineProps<Props>();

const selectedTemplate = ref('classic');

const templates = [
    { id: 'classic', name: 'Classic', description: 'Traditional professional style' },
    { id: 'modern', name: 'Modern', description: 'Contemporary clean design' },
    { id: 'minimal', name: 'Minimal', description: 'Simple and elegant' },
    { id: 'professional', name: 'Professional', description: 'Corporate business style' },
    { id: 'bold', name: 'Bold', description: 'Eye-catching vibrant design' },
];

const getIcon = (type: string) => {
    switch (type) {
        case 'invoice': return DocumentTextIcon;
        case 'delivery_note': return TruckIcon;
        case 'quotation': return DocumentCheckIcon;
        case 'receipt': return ReceiptPercentIcon;
        default: return DocumentTextIcon;
    }
};

const getColor = (type: string) => {
    switch (type) {
        case 'invoice': return 'bg-blue-500';
        case 'delivery_note': return 'bg-emerald-500';
        case 'quotation': return 'bg-amber-500';
        case 'receipt': return 'bg-purple-500';
        default: return 'bg-gray-500';
    }
};
</script>

<template>
    <Head title="Quick Invoice Generator" />
    
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-emerald-50">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                            <DocumentTextIcon class="w-6 h-6 text-white" aria-hidden="true" />
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">Quick Invoice</h1>
                            <p class="text-sm text-gray-500">by MyGrowNet</p>
                        </div>
                    </div>
                    <Link 
                        :href="route('quick-invoice.history')" 
                        class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                    >
                        View History
                    </Link>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Hero Section -->
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                    Create Professional Documents
                    <span class="text-blue-600">Instantly</span>
                </h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Generate invoices, delivery notes, quotations, and receipts in seconds. 
                    No account required. Share via WhatsApp, email, or download as PDF.
                </p>
            </div>

            <!-- Template Preview Section -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6 sm:p-8 mb-12">
                <h3 class="text-xl font-bold text-gray-900 mb-2 text-center">Choose Your Template</h3>
                <p class="text-gray-500 text-center mb-8">Select a style that matches your brand</p>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Template Selector -->
                    <div class="space-y-3">
                        <button
                            v-for="template in templates"
                            :key="template.id"
                            @click="selectedTemplate = template.id"
                            :class="[
                                'w-full flex items-center gap-4 p-4 rounded-xl border-2 transition-all text-left',
                                selectedTemplate === template.id 
                                    ? 'border-blue-500 bg-blue-50' 
                                    : 'border-gray-200 hover:border-gray-300 bg-white'
                            ]"
                        >
                            <div :class="[
                                'w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0',
                                selectedTemplate === template.id ? 'bg-blue-500' : 'bg-gray-100'
                            ]">
                                <CheckIcon v-if="selectedTemplate === template.id" class="w-5 h-5 text-white" aria-hidden="true" />
                                <span v-else class="text-sm font-bold text-gray-400">{{ template.name.charAt(0) }}</span>
                            </div>
                            <div class="flex-1">
                                <h4 :class="['font-semibold', selectedTemplate === template.id ? 'text-blue-700' : 'text-gray-900']">
                                    {{ template.name }}
                                </h4>
                                <p class="text-sm text-gray-500">{{ template.description }}</p>
                            </div>
                        </button>
                    </div>

                    <!-- Live Preview -->
                    <div class="bg-gray-100 rounded-xl p-4 flex items-center justify-center min-h-[400px]">
                        <!-- Classic Template Preview -->
                        <div v-if="selectedTemplate === 'classic'" class="w-full max-w-sm bg-white rounded-lg shadow-lg overflow-hidden transform scale-90">
                            <div class="h-3 bg-blue-600"></div>
                            <div class="p-4">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <div class="w-12 h-12 bg-blue-100 rounded flex items-center justify-center mb-2">
                                            <span class="text-blue-600 font-bold text-xs">LOGO</span>
                                        </div>
                                        <p class="text-xs font-bold text-gray-800">Acme Corp Ltd</p>
                                        <p class="text-[10px] text-gray-500">123 Business Street</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-lg font-bold text-blue-600">INVOICE</p>
                                        <p class="text-[10px] text-gray-500">#INV-001</p>
                                        <p class="text-[10px] text-gray-500">Jan 2, 2026</p>
                                    </div>
                                </div>
                                <div class="border-t border-b py-2 mb-3">
                                    <p class="text-[10px] text-gray-500">Bill To:</p>
                                    <p class="text-xs font-medium">John Smith</p>
                                </div>
                                <table class="w-full text-[10px] mb-3">
                                    <thead><tr class="border-b"><th class="text-left py-1">Item</th><th class="text-right">Qty</th><th class="text-right">Price</th></tr></thead>
                                    <tbody>
                                        <tr><td class="py-1">Web Design</td><td class="text-right">1</td><td class="text-right">K2,500</td></tr>
                                        <tr><td class="py-1">Hosting</td><td class="text-right">12</td><td class="text-right">K1,200</td></tr>
                                    </tbody>
                                </table>
                                <div class="border-t pt-2 text-right">
                                    <p class="text-xs"><span class="text-gray-500">Subtotal:</span> <span class="font-medium">K3,700</span></p>
                                    <p class="text-xs"><span class="text-gray-500">Tax (16%):</span> <span class="font-medium">K592</span></p>
                                    <p class="text-sm font-bold text-blue-600 mt-1">Total: K4,292</p>
                                </div>
                            </div>
                        </div>

                        <!-- Modern Template Preview -->
                        <div v-else-if="selectedTemplate === 'modern'" class="w-full max-w-sm bg-white rounded-lg shadow-lg overflow-hidden transform scale-90">
                            <div class="h-16 bg-gradient-to-r from-indigo-600 to-purple-600 p-4 flex items-center justify-between">
                                <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                                    <span class="text-white font-bold text-xs">AC</span>
                                </div>
                                <p class="text-white font-bold">INVOICE</p>
                            </div>
                            <div class="p-4">
                                <div class="flex justify-between mb-4">
                                    <div>
                                        <p class="text-xs font-bold text-gray-800">Acme Corp Ltd</p>
                                        <p class="text-[10px] text-gray-500">info@acme.com</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[10px] text-indigo-600 font-medium">#INV-001</p>
                                        <p class="text-[10px] text-gray-500">Jan 2, 2026</p>
                                    </div>
                                </div>
                                <div class="bg-indigo-50 rounded-lg p-3 mb-3">
                                    <p class="text-[10px] text-indigo-600 font-medium">BILL TO</p>
                                    <p class="text-xs font-medium text-gray-800">John Smith</p>
                                </div>
                                <div class="space-y-2 mb-3">
                                    <div class="flex justify-between text-[10px] py-1 border-b border-dashed">
                                        <span>Web Design × 1</span><span class="font-medium">K2,500</span>
                                    </div>
                                    <div class="flex justify-between text-[10px] py-1 border-b border-dashed">
                                        <span>Hosting × 12</span><span class="font-medium">K1,200</span>
                                    </div>
                                </div>
                                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg p-3 text-white text-right">
                                    <p class="text-[10px] opacity-80">Total Amount</p>
                                    <p class="text-lg font-bold">K4,292</p>
                                </div>
                            </div>
                        </div>

                        <!-- Minimal Template Preview -->
                        <div v-else-if="selectedTemplate === 'minimal'" class="w-full max-w-sm bg-white rounded-lg shadow-lg overflow-hidden transform scale-90 p-6">
                            <div class="flex justify-between items-start mb-6">
                                <div>
                                    <p class="text-xs font-bold text-gray-400 tracking-widest">INVOICE</p>
                                    <p class="text-[10px] text-gray-400">#INV-001</p>
                                </div>
                                <p class="text-[10px] text-gray-400">Jan 2, 2026</p>
                            </div>
                            <div class="mb-6">
                                <p class="text-sm font-medium text-gray-800">Acme Corp Ltd</p>
                                <p class="text-[10px] text-gray-400">123 Business Street</p>
                            </div>
                            <div class="mb-6">
                                <p class="text-[10px] text-gray-400 mb-1">Billed to</p>
                                <p class="text-xs font-medium text-gray-800">John Smith</p>
                            </div>
                            <div class="border-t border-gray-100 pt-4 space-y-2 mb-4">
                                <div class="flex justify-between text-[10px]">
                                    <span class="text-gray-600">Web Design</span><span>K2,500</span>
                                </div>
                                <div class="flex justify-between text-[10px]">
                                    <span class="text-gray-600">Hosting (12 mo)</span><span>K1,200</span>
                                </div>
                            </div>
                            <div class="border-t border-gray-200 pt-4 text-right">
                                <p class="text-lg font-light text-gray-800">K4,292</p>
                            </div>
                        </div>

                        <!-- Professional Template Preview -->
                        <div v-else-if="selectedTemplate === 'professional'" class="w-full max-w-sm bg-white rounded-lg shadow-lg overflow-hidden transform scale-90">
                            <div class="h-12 bg-slate-800 flex items-center px-4">
                                <p class="text-white font-bold text-sm">ACME CORP</p>
                            </div>
                            <div class="p-4">
                                <div class="flex justify-between items-center mb-4 pb-3 border-b-2 border-slate-800">
                                    <div>
                                        <p class="text-lg font-bold text-slate-800">INVOICE</p>
                                        <p class="text-[10px] text-gray-500">#INV-001 | Jan 2, 2026</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[10px] text-gray-500">Due Date</p>
                                        <p class="text-xs font-medium">Jan 16, 2026</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <p class="text-[10px] text-gray-500 font-medium">FROM</p>
                                        <p class="text-xs font-medium">Acme Corp Ltd</p>
                                        <p class="text-[10px] text-gray-500">123 Business St</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-500 font-medium">TO</p>
                                        <p class="text-xs font-medium">John Smith</p>
                                        <p class="text-[10px] text-gray-500">456 Client Ave</p>
                                    </div>
                                </div>
                                <table class="w-full text-[10px] mb-3">
                                    <thead><tr class="bg-slate-100"><th class="text-left p-2">Description</th><th class="text-right p-2">Amount</th></tr></thead>
                                    <tbody>
                                        <tr class="border-b"><td class="p-2">Web Design</td><td class="text-right p-2">K2,500</td></tr>
                                        <tr class="border-b"><td class="p-2">Hosting (12 mo)</td><td class="text-right p-2">K1,200</td></tr>
                                    </tbody>
                                </table>
                                <div class="bg-slate-800 text-white p-3 rounded flex justify-between items-center">
                                    <span class="text-xs">Total Due</span>
                                    <span class="text-lg font-bold">K4,292</span>
                                </div>
                            </div>
                        </div>

                        <!-- Bold Template Preview -->
                        <div v-else-if="selectedTemplate === 'bold'" class="w-full max-w-sm rounded-lg shadow-lg overflow-hidden transform scale-90">
                            <div class="bg-gradient-to-br from-orange-500 to-red-600 p-4">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center">
                                        <span class="text-orange-600 font-black text-sm">AC</span>
                                    </div>
                                    <div class="text-right text-white">
                                        <p class="text-xl font-black">INVOICE</p>
                                        <p class="text-xs opacity-80">#INV-001</p>
                                    </div>
                                </div>
                                <p class="text-white font-bold">Acme Corp Ltd</p>
                                <p class="text-white/70 text-xs">info@acme.com</p>
                            </div>
                            <div class="bg-white p-4">
                                <div class="bg-orange-50 rounded-lg p-3 mb-4 -mt-6 shadow">
                                    <p class="text-[10px] text-orange-600 font-bold">BILL TO</p>
                                    <p class="text-sm font-bold text-gray-800">John Smith</p>
                                </div>
                                <div class="space-y-2 mb-4">
                                    <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                        <span class="text-xs font-medium">Web Design</span>
                                        <span class="text-xs font-bold text-orange-600">K2,500</span>
                                    </div>
                                    <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                        <span class="text-xs font-medium">Hosting</span>
                                        <span class="text-xs font-bold text-orange-600">K1,200</span>
                                    </div>
                                </div>
                                <div class="border-t-2 border-orange-500 pt-3 text-right">
                                    <p class="text-[10px] text-gray-500">Total Amount</p>
                                    <p class="text-2xl font-black text-orange-600">K4,292</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Document Type Selection -->
            <div class="mb-12">
                <h3 class="text-xl font-bold text-gray-900 mb-6 text-center">What would you like to create?</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <Link
                        v-for="docType in documentTypes"
                        :key="docType.value"
                        :href="`/quick-invoice/create?type=${docType.value}&template=${selectedTemplate}`"
                        class="group relative bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg hover:border-blue-300 transition-all duration-200"
                    >
                        <div class="flex items-start gap-4">
                            <div :class="[getColor(docType.value), 'w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0']">
                                <component :is="getIcon(docType.value)" class="w-6 h-6 text-white" aria-hidden="true" />
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">
                                    {{ docType.label }}
                                </h3>
                                <p class="text-sm text-gray-500 mt-1">
                                    {{ docType.description }}
                                </p>
                            </div>
                            <ArrowRightIcon class="w-5 h-5 text-gray-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all" aria-hidden="true" />
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Features -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-6 text-center">Why Use Quick Invoice?</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div class="text-center">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <span class="text-2xl">⚡</span>
                        </div>
                        <h4 class="font-medium text-gray-900">Instant</h4>
                        <p class="text-sm text-gray-500 mt-1">Create documents in under a minute</p>
                    </div>
                    <div class="text-center">
                        <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <span class="text-2xl">🆓</span>
                        </div>
                        <h4 class="font-medium text-gray-900">Free</h4>
                        <p class="text-sm text-gray-500 mt-1">No account or payment required</p>
                    </div>
                    <div class="text-center">
                        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <span class="text-2xl">📱</span>
                        </div>
                        <h4 class="font-medium text-gray-900">Share Anywhere</h4>
                        <p class="text-sm text-gray-500 mt-1">WhatsApp, Email, or Download</p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <p class="text-center text-sm text-gray-500">
                    Powered by <a href="/" class="text-blue-600 hover:underline">MyGrowNet</a> — 
                    Empowering businesses across Zambia
                </p>
            </div>
        </footer>
    </div>
</template>
