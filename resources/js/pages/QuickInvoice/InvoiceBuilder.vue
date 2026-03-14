<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import QuickInvoiceLayout from '@/Layouts/QuickInvoiceLayout.vue';
import { ArrowLeftIcon, DocumentTextIcon, PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';

interface Template {
    id: string;
    name: string;
    layout_json: any;
    primary_color: string;
    secondary_color: string;
    font_family: string;
    logo_url?: string;
}

const props = defineProps<{
    template: Template;
}>();

// Invoice data
const invoiceNumber = ref('INV-' + Date.now().toString().slice(-6));
const invoiceDate = ref(new Date().toISOString().split('T')[0]);
const dueDate = ref(new Date(Date.now() + 30*24*60*60*1000).toISOString().split('T')[0]);

const customerName = ref('');
const customerEmail = ref('');
const customerPhone = ref('');
const customerAddress = ref('');

const companyName = ref('Your Company');
const companyAddress = ref('');
const companyPhone = ref('');
const companyEmail = ref('');

const items = ref([
    { description: '', quantity: 1, unit_price: 0 }
]);

const taxRate = ref(16);
const discount = ref(0);
const notes = ref('');

// Calculations
const subtotal = computed(() => {
    return items.value.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
});

const taxAmount = computed(() => {
    return (subtotal.value * taxRate.value) / 100;
});

const total = computed(() => {
    return subtotal.value + taxAmount.value - discount.value;
});

const addItem = () => {
    items.value.push({ description: '', quantity: 1, unit_price: 0 });
};

const removeItem = (index: number) => {
    if (items.value.length > 1) {
        items.value.splice(index, 1);
    }
};

const generateInvoice = () => {
    const data = {
        template_id: props.template.id,
        invoice_number: invoiceNumber.value,
        invoice_date: invoiceDate.value,
        due_date: dueDate.value,
        customer_name: customerName.value,
        customer_email: customerEmail.value,
        customer_phone: customerPhone.value,
        customer_address: customerAddress.value,
        company_name: companyName.value,
        company_address: companyAddress.value,
        company_phone: companyPhone.value,
        company_email: companyEmail.value,
        items: items.value,
        tax_rate: taxRate.value,
        discount: discount.value,
        notes: notes.value,
        subtotal: subtotal.value,
        tax_amount: taxAmount.value,
        total: total.value,
    };
    
    router.post(route('quick-invoice.generate'), data);
};
</script>

<template>
    <QuickInvoiceLayout>
        <Head :title="`Create Invoice - ${template.name}`" />
        
        <div class="min-h-screen bg-slate-50">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 py-8">
                
                <!-- Header -->
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-4">
                        <button @click="$inertia.visit(route('quick-invoice.design-studio'))" 
                            class="p-2 text-slate-400 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition">
                            <ArrowLeftIcon class="h-5 w-5" />
                        </button>
                        <div>
                            <h1 class="text-2xl font-bold text-slate-900">Create Invoice</h1>
                            <p class="text-sm text-slate-500">Using template: {{ template.name }}</p>
                        </div>
                    </div>
                    <button @click="generateInvoice"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition shadow-sm">
                        <DocumentTextIcon class="h-5 w-5" />
                        Generate Invoice
                    </button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    
                    <!-- Form -->
                    <div class="space-y-6">
                        
                        <!-- Invoice Details -->
                        <div class="bg-white rounded-2xl border border-slate-200 p-6">
                            <h2 class="text-lg font-bold text-slate-900 mb-4">Invoice Details</h2>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Invoice Number</label>
                                    <input v-model="invoiceNumber" type="text" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Invoice Date</label>
                                    <input v-model="invoiceDate" type="date" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Due Date</label>
                                    <input v-model="dueDate" type="date" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
                                </div>
                            </div>
                        </div>

                        <!-- Customer Details -->
                        <div class="bg-white rounded-2xl border border-slate-200 p-6">
                            <h2 class="text-lg font-bold text-slate-900 mb-4">Customer Details</h2>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Customer Name</label>
                                    <input v-model="customerName" type="text" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Email</label>
                                    <input v-model="customerEmail" type="email" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Phone</label>
                                    <input v-model="customerPhone" type="tel" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Address</label>
                                    <textarea v-model="customerAddress" rows="2" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Items -->
                        <div class="bg-white rounded-2xl border border-slate-200 p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-lg font-bold text-slate-900">Items</h2>
                                <button @click="addItem" class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-semibold text-indigo-600 hover:bg-indigo-50 rounded-lg transition">
                                    <PlusIcon class="h-4 w-4" />Add Item
                                </button>
                            </div>
                            <div class="space-y-3">
                                <div v-for="(item, index) in items" :key="index" class="flex gap-2">
                                    <input v-model="item.description" placeholder="Description" class="flex-1 px-3 py-2 border border-slate-200 rounded-lg text-sm" />
                                    <input v-model.number="item.quantity" type="number" placeholder="Qty" class="w-20 px-3 py-2 border border-slate-200 rounded-lg text-sm" />
                                    <input v-model.number="item.unit_price" type="number" step="0.01" placeholder="Price" class="w-28 px-3 py-2 border border-slate-200 rounded-lg text-sm" />
                                    <button @click="removeItem(index)" class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                        <TrashIcon class="h-5 w-5" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Totals -->
                        <div class="bg-white rounded-2xl border border-slate-200 p-6">
                            <h2 class="text-lg font-bold text-slate-900 mb-4">Totals</h2>
                            <div class="space-y-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-600">Subtotal</span>
                                    <span class="font-semibold">K {{ subtotal.toFixed(2) }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-slate-600">Tax Rate (%)</span>
                                    <input v-model.number="taxRate" type="number" class="w-20 px-2 py-1 border border-slate-200 rounded text-sm text-right" />
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-600">Tax Amount</span>
                                    <span class="font-semibold">K {{ taxAmount.toFixed(2) }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-slate-600">Discount</span>
                                    <input v-model.number="discount" type="number" step="0.01" class="w-28 px-2 py-1 border border-slate-200 rounded text-sm text-right" />
                                </div>
                                <div class="flex justify-between text-lg font-bold pt-3 border-t border-slate-200">
                                    <span>Total</span>
                                    <span class="text-indigo-600">K {{ total.toFixed(2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="bg-white rounded-2xl border border-slate-200 p-6">
                            <h2 class="text-lg font-bold text-slate-900 mb-4">Notes</h2>
                            <textarea v-model="notes" rows="3" placeholder="Add any additional notes or terms..." class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"></textarea>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div class="lg:sticky lg:top-8 h-fit">
                        <div class="bg-white rounded-2xl border border-slate-200 p-6">
                            <h2 class="text-lg font-bold text-slate-900 mb-4">Preview</h2>
                            <div class="bg-slate-50 rounded-lg p-4 text-center text-slate-500">
                                <DocumentTextIcon class="h-16 w-16 mx-auto mb-3 text-slate-300" />
                                <p class="text-sm">Invoice preview will appear here</p>
                                <p class="text-xs mt-1">Click "Generate Invoice" to create PDF</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </QuickInvoiceLayout>
</template>
