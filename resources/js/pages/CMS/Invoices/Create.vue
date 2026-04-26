<script setup lang="ts">
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';
import { PlusIcon, TrashIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { toast } from '@/utils/bizboost-toast';
import { useAutoSave } from '@/composables/useAutoSave';

defineOptions({
  layout: CMSLayout
})

interface Customer {
    id: number;
    name: string;
    email: string;
    phone: string;
    outstanding_balance: number;
}

interface Props {
    customers: Customer[];
    defaultNotes?: string;
    defaultTerms?: string;
}

const props = defineProps<Props>();
const page = usePage();

// Check if fabrication module is enabled
const fabricationEnabled = computed(() => {
    return page.props.company?.settings?.fabrication_module === true;
});

const form = useForm({
    customer_id: null as number | null,
    due_date: '',
    notes: props.defaultNotes ?? '',
    items: [
        { 
            description: '', 
            quantity: 1, 
            unit_price: 0,
            line_total: 0,
            width: null as number | null,
            height: null as number | null,
            area: null as number | null,
            dimensions: null as string | null,
            dimensions_value: 1,
        },
    ],
    subtotal: 0,
    discount_type: 'percentage' as 'percentage' | 'fixed',
    discount_value: 0,
    discount_amount: 0,
    tax_rate: 16,
    tax_amount: 0,
    total: 0,
});

const addItem = () => {
    form.items.push({ 
        description: '', 
        quantity: 1, 
        unit_price: 0,
        line_total: 0,
        width: null,
        height: null,
        area: null,
        dimensions: null,
        dimensions_value: 1,
    });
};

const removeItem = (index: number) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
        recalculateTotals();
    }
};

// Calculate area from dimensions
const calculateArea = (item: any) => {
    if (item.width && item.height) {
        const areaM2 = item.width * item.height;
        item.area = areaM2.toFixed(4);
        item.dimensions = `${(item.width * 1000).toFixed(0)}mm × ${(item.height * 1000).toFixed(0)}mm`;
        item.dimensions_value = areaM2;
        if (fabricationEnabled.value) {
            item.quantity = parseFloat(item.area);
        }
    } else {
        item.area = null;
        item.dimensions = null;
        item.dimensions_value = 1;
    }
    calculateLineTotal(item);
    recalculateTotals();
};

const calculateLineTotal = (item: any) => {
    // Simple line total: quantity × unit price
    item.line_total = item.quantity * item.unit_price;
    return item.line_total;
};

const recalculateTotals = () => {
    // Calculate subtotal (sum of all line totals)
    form.subtotal = form.items.reduce((sum, item) => sum + item.line_total, 0);
    
    // Calculate discount amount
    if (form.discount_type === 'percentage') {
        form.discount_amount = form.subtotal * (form.discount_value / 100);
    } else {
        form.discount_amount = form.discount_value;
    }
    
    // Calculate amount after discount
    const afterDiscount = form.subtotal - form.discount_amount;
    
    // Calculate tax amount
    form.tax_amount = afterDiscount * (form.tax_rate / 100);
    
    // Calculate final total
    form.total = afterDiscount + form.tax_amount;
};

const submit = () => {
    form.post(route('cms.invoices.store'), {
        onSuccess: () => {
            clearDraft();
            toast.success('Invoice created', 'The invoice has been created successfully');
        },
        onError: () => {
            toast.error('Failed to create invoice', 'Please check the form and try again');
        },
    });
};

// Auto-save
const { autoSaveStatus, loadDraft, hasDraft, clearDraft } = useAutoSave('cms-invoice-draft', form);

onMounted(() => {
    if (hasDraft()) {
        const draft = loadDraft();
        if (draft) {
            Object.assign(form, draft);
            toast.info('Draft restored', 'Your unsaved invoice has been restored');
        }
    }
});

const formatCurrency = (amount: number) => {
    return `K${amount.toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
};
</script>

<template>
    <Head title="Create Invoice - CMS" />

    <div class="px-4 sm:px-6 lg:px-8 py-8">
        <div class="max-w-5xl mx-auto">
            <!-- Header -->
            <div class="mb-6">
                <button
                    @click="router.visit(route('cms.invoices.index'))"
                    class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-3 transition-colors"
                >
                    <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
                    Back to Invoices
                </button>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-gray-900">Create Invoice</h1>
                    <span v-if="autoSaveStatus === 'saving'" class="text-xs text-gray-400 flex items-center gap-1">
                        <svg class="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                        Saving draft…
                    </span>
                    <span v-else-if="autoSaveStatus === 'saved'" class="text-xs text-green-600 flex items-center gap-1">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Draft saved
                    </span>
                </div>
                <p class="mt-1 text-sm text-gray-500">Create a new invoice for a customer</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Customer Selection -->
                <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Customer Information</h2>
                    
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Customer <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.customer_id"
                                required
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors duration-200"
                            >
                                <option :value="null">Select customer...</option>
                                <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                                    {{ customer.name }} ({{ formatCurrency(customer.outstanding_balance) }} outstanding)
                                </option>
                            </select>
                            <p v-if="form.errors.customer_id" class="mt-1 text-sm text-red-600">{{ form.errors.customer_id }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                            <input
                                v-model="form.due_date"
                                type="date"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors duration-200"
                            />
                            <p v-if="form.errors.due_date" class="mt-1 text-sm text-red-600">{{ form.errors.due_date }}</p>
                        </div>
                    </div>
                </div>

                <!-- Invoice Items -->
                <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">Invoice Items</h2>
                        <button
                            type="button"
                            @click="addItem"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                        >
                            <PlusIcon class="h-4 w-4" aria-hidden="true" />
                            Add Item
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Description</th>
                                    <th v-if="fabricationEnabled" class="px-3 py-2 text-left text-xs font-medium text-gray-500">Width (m)</th>
                                    <th v-if="fabricationEnabled" class="px-3 py-2 text-left text-xs font-medium text-gray-500">Height (m)</th>
                                    <th v-if="fabricationEnabled" class="px-3 py-2 text-left text-xs font-medium text-gray-500">Area (m²)</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">{{ fabricationEnabled ? 'Area/Qty' : 'Qty' }}</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Unit Price</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Total</th>
                                    <th class="px-3 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="(item, index) in form.items" :key="index">
                                    <td class="px-3 py-2">
                                        <input
                                            v-model="item.description"
                                            type="text"
                                            placeholder="Description"
                                            required
                                            class="block w-full min-w-[200px] rounded border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500 transition-colors duration-200"
                                        />
                                    </td>
                                    <!-- Fabrication: Width -->
                                    <td v-if="fabricationEnabled" class="px-3 py-2">
                                        <input
                                            v-model.number="item.width"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            placeholder="0.00"
                                            @input="calculateArea(item)"
                                            class="block w-20 rounded border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500 transition-colors duration-200"
                                        />
                                    </td>
                                    <!-- Fabrication: Height -->
                                    <td v-if="fabricationEnabled" class="px-3 py-2">
                                        <input
                                            v-model.number="item.height"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            placeholder="0.00"
                                            @input="calculateArea(item)"
                                            class="block w-20 rounded border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500 transition-colors duration-200"
                                        />
                                    </td>
                                    <!-- Fabrication: Area (calculated) -->
                                    <td v-if="fabricationEnabled" class="px-3 py-2">
                                        <input
                                            :value="item.area || ''"
                                            type="text"
                                            readonly
                                            placeholder="Auto"
                                            class="block w-20 rounded border-gray-200 bg-gray-50 shadow-sm text-sm text-gray-600"
                                        />
                                    </td>
                                    <td class="px-3 py-2">
                                        <input
                                            v-model.number="item.quantity"
                                            type="number"
                                            step="0.01"
                                            min="0.01"
                                            required
                                            :readonly="fabricationEnabled && item.area"
                                            @input="calculateLineTotal(item); recalculateTotals()"
                                            :class="[
                                                'block w-20 rounded border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500 transition-colors duration-200',
                                                fabricationEnabled && item.area ? 'bg-gray-50 text-gray-600' : ''
                                            ]"
                                        />
                                    </td>
                                    <td class="px-3 py-2">
                                        <input
                                            v-model.number="item.unit_price"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            required
                                            @input="calculateLineTotal(item); recalculateTotals()"
                                            class="block w-24 rounded border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500 transition-colors duration-200"
                                        />
                                    </td>
                                    <td class="px-3 py-2 text-sm font-medium text-gray-900">
                                        {{ formatCurrency(item.line_total) }}
                                    </td>
                                    <td class="px-3 py-2">
                                        <button
                                            v-if="form.items.length > 1"
                                            type="button"
                                            @click="removeItem(index)"
                                            class="text-red-600 hover:text-red-800 transition-colors"
                                            aria-label="Remove item"
                                        >
                                            <TrashIcon class="h-5 w-5" aria-hidden="true" />
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals -->
                    <div class="mt-6 border-t pt-4">
                        <div class="flex justify-end">
                            <div class="w-80 space-y-3">
                                <!-- Subtotal -->
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Subtotal:</span>
                                    <span class="font-medium">{{ formatCurrency(form.subtotal) }}</span>
                                </div>
                                
                                <!-- Discount -->
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-600">Discount:</span>
                                        <select
                                            v-model="form.discount_type"
                                            @change="recalculateTotals()"
                                            class="text-xs rounded border-gray-300 py-1 px-2 focus:border-blue-500 focus:ring-blue-500"
                                        >
                                            <option value="percentage">%</option>
                                            <option value="fixed">K</option>
                                        </select>
                                        <input
                                            v-model.number="form.discount_value"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            placeholder="0"
                                            @input="recalculateTotals()"
                                            class="w-20 text-xs rounded border-gray-300 py-1 px-2 focus:border-blue-500 focus:ring-blue-500"
                                        />
                                    </div>
                                    <span class="font-medium text-red-600">-{{ formatCurrency(form.discount_amount) }}</span>
                                </div>
                                
                                <!-- Tax -->
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-600">Tax (VAT):</span>
                                        <input
                                            v-model.number="form.tax_rate"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="100"
                                            @input="recalculateTotals()"
                                            class="w-16 text-xs rounded border-gray-300 py-1 px-2 focus:border-blue-500 focus:ring-blue-500"
                                        />
                                        <span class="text-xs text-gray-500">%</span>
                                    </div>
                                    <span class="font-medium">{{ formatCurrency(form.tax_amount) }}</span>
                                </div>
                                
                                <!-- Total -->
                                <div class="flex justify-between border-t pt-3 text-lg font-bold">
                                    <span>Total:</span>
                                    <span>{{ formatCurrency(form.total) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Additional Information</h2>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea
                            v-model="form.notes"
                            rows="3"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors duration-200"
                            placeholder="Add any notes or special instructions..."
                        />
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3">
                    <button
                        type="button"
                        @click="router.visit(route('cms.invoices.index'))"
                        class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <span v-if="form.processing">Creating...</span>
                        <span v-else>Create Invoice</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
