<script setup lang="ts">
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    DocumentTextIcon,
    PlusIcon,
    TrashIcon,
    CalendarIcon,
    CheckCircleIcon,
    ArrowLeftIcon,
} from '@heroicons/vue/24/outline';
import { computed, ref, watch, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

interface Customer {
    id: number;
    name: string;
    address?: string;
    phone?: string;
    email?: string;
}

interface BusinessProfile {
    id: number;
    businessName: string;
    defaultCurrency: string;
    defaultTaxRate: number;
    defaultTerms?: string;
    defaultNotes?: string;
    defaultPaymentInstructions?: string;
}

interface Props {
    document?: any;
    documentType: string;
    businessProfile: BusinessProfile;
    customers: Customer[];
}

const props = defineProps<Props>();

const isEditMode = computed(() => !!props.document);

interface LineItem {
    description: string;
    quantity: number;
    unit_price: number;
    discount_amount: number;
}

const STORAGE_KEY     = `bizdocs_document_draft_${props.documentType}`;
const autoSaveStatus  = ref<'saved' | 'saving' | 'idle'>('idle');
let   autoSaveTimeout: ReturnType<typeof setTimeout> | null = null;

const loadDraft = () => {
    const saved = localStorage.getItem(STORAGE_KEY);
    if (saved) {
        try { return JSON.parse(saved); }
        catch (e) { return null; }
    }
    return null;
};

const draft = loadDraft();

// Use document data if editing, otherwise use draft
const initialData = props.document || draft;

const form = useForm({
    customer_id:          initialData?.customerId || initialData?.customer_id || null as number | null,
    document_type:        props.documentType,
    issue_date:           initialData?.issueDate || initialData?.issue_date || new Date().toISOString().split('T')[0],
    due_date:             initialData?.dueDate || initialData?.due_date || '',
    validity_date:        initialData?.validityDate || initialData?.validity_date || '',
    notes:                initialData?.notes || props.businessProfile?.defaultNotes || '',
    terms:                initialData?.terms || props.businessProfile?.defaultTerms || '',
    payment_instructions: initialData?.paymentInstructions || initialData?.payment_instructions || props.businessProfile?.defaultPaymentInstructions || '',
    items: (initialData?.items || [
        { description: '', quantity: 1, unit_price: 0, discount_amount: 0 },
    ]).map((item: any) => ({
        description: item.description || '',
        quantity: item.quantity || 1,
        unit_price: item.unitPrice || item.unit_price || 0,
        discount_amount: item.discountAmount || item.discount_amount || 0,
    })) as LineItem[],
});

const documentTypeLabel = computed(() => {
    const labels: Record<string, string> = {
        invoice:          'Invoice',
        receipt:          'Receipt',
        quotation:        'Quotation',
        delivery_note:    'Delivery Note',
        proforma_invoice: 'Proforma Invoice',
        credit_note:      'Credit Note',
        debit_note:       'Debit Note',
        purchase_order:   'Purchase Order',
    };
    return labels[props.documentType] || 'Document';
});

const saveDraft = () => {
    autoSaveStatus.value = 'saving';
    localStorage.setItem(STORAGE_KEY, JSON.stringify(form.data()));
    setTimeout(() => {
        autoSaveStatus.value = 'saved';
        setTimeout(() => { autoSaveStatus.value = 'idle'; }, 2000);
    }, 300);
};

watch(() => form.data(), () => {
    if (autoSaveTimeout) clearTimeout(autoSaveTimeout);
    autoSaveTimeout = setTimeout(saveDraft, 1000);
}, { deep: true });

const addLineItem = () =>
    form.items.push({ description: '', quantity: 1, unit_price: 0, discount_amount: 0 });

const removeLineItem = (index: number) => {
    if (form.items.length > 1) form.items.splice(index, 1);
};

const lineTotal = (item: LineItem) => {
    const sub = item.quantity * item.unit_price;
    return sub - item.discount_amount;
};

const totals = computed(() => {
    const subtotal      = form.items.reduce((s, i) => s + i.quantity * i.unit_price, 0);
    const discountTotal = form.items.reduce((s, i) => s + i.discount_amount, 0);
    const afterDiscount = subtotal - discountTotal;
    const taxTotal      = afterDiscount * (props.businessProfile.defaultTaxRate / 100);
    return { subtotal, taxTotal, discountTotal, grandTotal: afterDiscount + taxTotal };
});

const fmt = (n: number) => n.toFixed(2);

const submit = () => {
    if (isEditMode.value) {
        // Update existing document
        form.put(`/bizdocs/documents/${props.document.id}`, {
            onSuccess: () => {
                localStorage.removeItem(STORAGE_KEY);
                autoSaveStatus.value = 'idle';
            },
        });
    } else {
        // Create new document
        form.post('/bizdocs/documents', {
            onSuccess: () => {
                localStorage.removeItem(STORAGE_KEY);
                autoSaveStatus.value = 'idle';
            },
        });
    }
};

const clearDraft = async () => {
    const result = await Swal.fire({
        title: 'Clear Draft?',
        text: 'Clear the saved draft and reset the form?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, clear it',
        cancelButtonText: 'Cancel',
    });

    if (result.isConfirmed) {
        localStorage.removeItem(STORAGE_KEY);
        form.reset();
        form.items = [{ description: '', quantity: 1, unit_price: 0, tax_rate: 0, discount_amount: 0 }];
        autoSaveStatus.value = 'idle';
        
        await Swal.fire({
            icon: 'success',
            title: 'Draft Cleared',
            text: 'Form has been reset',
            timer: 1500,
            showConfirmButton: false,
        });
    }
};

// Quick add customer modal
const showQuickAddModal = ref(false);
const quickCustomerForm = ref({
    name: '',
    phone: '',
    email: '',
    address: '',
});
const quickCustomerErrors = ref<Record<string, string>>({});
const quickCustomerProcessing = ref(false);

// Auto-format phone number with +260 prefix for Zambian numbers
const formatPhoneNumber = (value: string): string => {
    if (!value) return '';
    
    // Remove all non-digit characters except +
    const cleaned = value.replace(/[^\d+]/g, '');
    
    // If starts with +260, keep as is
    if (cleaned.startsWith('+260')) {
        return cleaned;
    }
    
    // Remove any existing + at the start
    const digits = cleaned.replace(/^\+/, '');
    
    // If starts with 260, add +
    if (digits.startsWith('260')) {
        return '+' + digits;
    }
    
    // If starts with 0 (local format), replace with +260
    if (digits.startsWith('0')) {
        return '+260' + digits.substring(1);
    }
    
    // If starts with 9 or 7 (mobile without 0), add +260
    if (digits.match(/^[97]/)) {
        return '+260' + digits;
    }
    
    // Otherwise, add +260 prefix if there are digits
    return digits ? '+260' + digits : '';
};

watch(() => quickCustomerForm.value.phone, (newValue) => {
    if (newValue && !newValue.startsWith('+260')) {
        const formatted = formatPhoneNumber(newValue);
        if (formatted !== newValue) {
            quickCustomerForm.value.phone = formatted;
        }
    }
});

const openQuickAdd = () => {
    quickCustomerForm.value = { name: '', phone: '', email: '', address: '' };
    quickCustomerErrors.value = {};
    showQuickAddModal.value = true;
};

const closeQuickAdd = () => {
    showQuickAddModal.value = false;
    quickCustomerForm.value = { name: '', phone: '', email: '', address: '' };
    quickCustomerErrors.value = {};
};

const submitQuickCustomer = async () => {
    quickCustomerProcessing.value = true;
    quickCustomerErrors.value = {};

    try {
        console.log('Submitting customer:', quickCustomerForm.value);
        
        const response = await axios.post('/bizdocs/customers', quickCustomerForm.value, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        console.log('Customer creation response:', response.data);

        if (response.data.success && response.data.customer) {
            const newCustomer = response.data.customer;
            
            // Add to customers array
            props.customers.push({
                id: newCustomer.id,
                name: newCustomer.name,
                phone: newCustomer.phone || '',
                email: newCustomer.email || '',
                address: newCustomer.address || '',
            });
            
            // Select the new customer
            form.customer_id = newCustomer.id;
            
            closeQuickAdd();
        } else {
            console.error('Unexpected response format:', response.data);
            quickCustomerErrors.value = { general: 'Unexpected response from server' };
        }
    } catch (error: any) {
        console.error('Customer creation error:', error);
        console.error('Error response:', error.response?.data);
        
        if (error.response?.data?.errors) {
            quickCustomerErrors.value = error.response.data.errors;
        } else if (error.response?.data?.message) {
            quickCustomerErrors.value = { general: error.response.data.message };
        } else {
            quickCustomerErrors.value = { general: error.response?.data?.error || error.message || 'Failed to create customer' };
        }
    } finally {
        quickCustomerProcessing.value = false;
    }
};

onMounted(() => {
    if (draft) {
        autoSaveStatus.value = 'saved';
        setTimeout(() => { autoSaveStatus.value = 'idle'; }, 3000);
    }
});
</script>

<template>
    <Head :title="`${isEditMode ? 'Edit' : 'Create'} ${documentTypeLabel}`" />

    <AppLayout>
        <div class="min-h-screen bg-slate-50 py-8 px-4">
            <div class="max-w-5xl mx-auto">

                <!-- Page header -->
                <div class="flex items-start justify-between mb-7">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <Link
                                href="/bizdocs/documents"
                                class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 hover:text-blue-600 transition-colors group">
                                <ArrowLeftIcon class="w-3.5 h-3.5 group-hover:-translate-x-0.5 transition-transform" />
                                Back to Documents
                            </Link>
                            <span class="text-slate-300">·</span>
                            <span class="inline-flex items-center gap-2 text-xs font-bold tracking-widest text-blue-600 uppercase">
                                <span class="w-5 h-px bg-blue-500 inline-block"></span>
                                BizDocs
                            </span>
                        </div>
                        <h1 class="text-2xl font-bold text-slate-900 leading-none">
                            {{ isEditMode ? 'Edit' : 'Create' }} {{ documentTypeLabel }}
                        </h1>
                        <p class="text-sm text-slate-400 mt-1">
                            {{ isEditMode ? 'Update the details below' : 'Fill in the details below to generate your document' }}
                        </p>
                    </div>

                    <!-- Auto-save pill -->
                    <Transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="opacity-0 translate-y-1"
                        enter-to-class="opacity-100 translate-y-0"
                        leave-active-class="transition duration-150 ease-in"
                        leave-from-class="opacity-100"
                        leave-to-class="opacity-0">
                        <div v-if="autoSaveStatus === 'saving'"
                            class="flex items-center gap-1.5 text-xs text-slate-500 bg-white border border-slate-200 rounded-full px-3 py-1.5 shadow-sm mt-1">
                            <svg class="animate-spin w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                            </svg>
                            Saving draft…
                        </div>
                        <div v-else-if="autoSaveStatus === 'saved'"
                            class="flex items-center gap-1.5 text-xs text-green-600 bg-green-50 border border-green-200 rounded-full px-3 py-1.5 mt-1">
                            <CheckCircleIcon class="w-3.5 h-3.5" />
                            Draft saved
                        </div>
                    </Transition>
                </div>

                <form @submit.prevent="submit" class="space-y-4">

                    <!-- ── Section 01: Document Details ── -->
                    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                        <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100">
                            <span class="text-xs font-bold tracking-widest text-blue-500 uppercase">01</span>
                            <span class="flex-1 h-px bg-slate-100"></span>
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Document Details</span>
                        </div>

                        <div class="px-6 py-5">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                                <!-- Customer -->
                                <div class="sm:col-span-2">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label for="customer_id" class="bizdocs-label mb-0">
                                            Customer <span class="text-red-400">*</span>
                                        </label>
                                        <button
                                            type="button"
                                            @click="openQuickAdd"
                                            class="inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-700 transition-colors">
                                            <PlusIcon class="w-3.5 h-3.5" />
                                            Quick Add
                                        </button>
                                    </div>
                                    <select
                                        id="customer_id"
                                        v-model="form.customer_id"
                                        required
                                        class="bizdocs-input bg-white"
                                        :class="{ 'bizdocs-input-error': form.errors.customer_id }">
                                        <option :value="null">Select a customer…</option>
                                        <option v-for="c in customers" :key="c.id" :value="c.id">
                                            {{ c.name }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.customer_id" class="bizdocs-error">
                                        {{ form.errors.customer_id }}
                                    </p>
                                </div>

                                <!-- Issue Date -->
                                <div>
                                    <label for="issue_date" class="bizdocs-label">
                                        Issue Date <span class="text-red-400">*</span>
                                    </label>
                                    <div class="relative">
                                        <CalendarIcon class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-300 pointer-events-none" />
                                        <input
                                            id="issue_date"
                                            v-model="form.issue_date"
                                            type="date"
                                            required
                                            class="bizdocs-input pl-10" />
                                    </div>
                                </div>

                                <!-- Due Date (invoices) -->
                                <div v-if="documentType === 'invoice'">
                                    <label for="due_date" class="bizdocs-label">Due Date</label>
                                    <div class="relative">
                                        <CalendarIcon class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-300 pointer-events-none" />
                                        <input
                                            id="due_date"
                                            v-model="form.due_date"
                                            type="date"
                                            class="bizdocs-input pl-10" />
                                    </div>
                                </div>

                                <!-- Valid Until (quotations) -->
                                <div v-if="documentType === 'quotation'">
                                    <label for="validity_date" class="bizdocs-label">Valid Until</label>
                                    <div class="relative">
                                        <CalendarIcon class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-300 pointer-events-none" />
                                        <input
                                            id="validity_date"
                                            v-model="form.validity_date"
                                            type="date"
                                            class="bizdocs-input pl-10" />
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- ── Section 02: Line Items ── -->
                    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                        <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100">
                            <span class="text-xs font-bold tracking-widest text-blue-500 uppercase">02</span>
                            <span class="flex-1 h-px bg-slate-100"></span>
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Line Items</span>
                            <button
                                type="button"
                                @click="addLineItem"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-900 text-white text-xs font-semibold rounded-lg hover:bg-blue-600 transition-all duration-200">
                                <PlusIcon class="w-3.5 h-3.5" />
                                Add Item
                            </button>
                        </div>

                        <div class="px-6 py-5 space-y-3">

                            <!-- Column headers (desktop) -->
                            <div class="hidden md:grid grid-cols-10 gap-3 px-3">
                                <span class="col-span-5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Description</span>
                                <span class="col-span-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">Qty</span>
                                <span class="col-span-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">Unit Price</span>
                                <span class="col-span-1"></span>
                            </div>

                            <!-- Line item rows -->
                            <div
                                v-for="(item, index) in form.items"
                                :key="index"
                                class="group border border-slate-200 rounded-lg hover:border-blue-200 transition-colors overflow-hidden">

                                <!-- Row inputs -->
                                <div class="grid grid-cols-10 gap-3 p-3">

                                    <!-- Description -->
                                    <div class="col-span-10 md:col-span-5">
                                        <label :for="`desc_${index}`" class="bizdocs-label md:hidden">Description *</label>
                                        <input
                                            :id="`desc_${index}`"
                                            v-model="item.description"
                                            type="text"
                                            required
                                            placeholder="Item or service description"
                                            class="bizdocs-input" />
                                    </div>

                                    <!-- Qty -->
                                    <div class="col-span-4 md:col-span-2">
                                        <label :for="`qty_${index}`" class="bizdocs-label md:hidden">Qty *</label>
                                        <input
                                            :id="`qty_${index}`"
                                            v-model.number="item.quantity"
                                            type="number"
                                            step="0.01"
                                            min="0.01"
                                            required
                                            class="bizdocs-input text-right" />
                                    </div>

                                    <!-- Unit Price -->
                                    <div class="col-span-5 md:col-span-2">
                                        <label :for="`price_${index}`" class="bizdocs-label md:hidden">Price *</label>
                                        <input
                                            :id="`price_${index}`"
                                            v-model.number="item.unit_price"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            required
                                            class="bizdocs-input text-right" />
                                    </div>

                                    <!-- Remove -->
                                    <div class="col-span-1 flex items-end justify-center pb-0.5">
                                        <button
                                            type="button"
                                            @click="removeLineItem(index)"
                                            :disabled="form.items.length === 1"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-300 hover:text-red-500 hover:bg-red-50 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                                            aria-label="Remove item">
                                            <TrashIcon class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>

                                <!-- Line total strip -->
                                <div class="flex items-center justify-between bg-slate-50 border-t border-slate-100 px-3 py-1.5">
                                    <span class="text-xs text-slate-400">Line total</span>
                                    <span class="text-xs font-semibold text-slate-700">
                                        {{ businessProfile.defaultCurrency }} {{ fmt(lineTotal(item)) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Totals summary -->
                            <div class="flex justify-end pt-2">
                                <div class="w-full sm:w-72 border border-slate-200 rounded-lg overflow-hidden">
                                    <div class="flex justify-between px-4 py-2 text-xs text-slate-600 border-b border-slate-100">
                                        <span>Subtotal</span>
                                        <span class="font-medium text-slate-800">{{ businessProfile.defaultCurrency }} {{ fmt(totals.subtotal) }}</span>
                                    </div>
                                    <div class="flex justify-between px-4 py-2 text-xs text-slate-600 border-b border-slate-100">
                                        <span>Tax ({{ businessProfile.defaultTaxRate }}%)</span>
                                        <span class="font-medium text-slate-800">{{ businessProfile.defaultCurrency }} {{ fmt(totals.taxTotal) }}</span>
                                    </div>
                                    <div class="flex justify-between px-4 py-2 text-xs text-slate-600 border-b border-slate-100">
                                        <span>Discount</span>
                                        <span class="font-medium text-slate-800">−{{ businessProfile.defaultCurrency }} {{ fmt(totals.discountTotal) }}</span>
                                    </div>
                                    <div class="flex justify-between px-4 py-3 bg-slate-900">
                                        <span class="text-sm font-bold text-white">Grand Total</span>
                                        <span class="text-sm font-bold text-blue-300">
                                            {{ businessProfile.defaultCurrency }} {{ fmt(totals.grandTotal) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- ── Section 03: Additional Information ── -->
                    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                        <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100">
                            <span class="text-xs font-bold tracking-widest text-slate-400 uppercase">03</span>
                            <span class="flex-1 h-px bg-slate-100"></span>
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Additional Information</span>
                        </div>

                        <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-4">

                            <!-- Notes (always shown) -->
                            <div :class="documentType === 'invoice' ? 'sm:col-span-1' : 'sm:col-span-2'">
                                <label for="notes" class="bizdocs-label">Notes</label>
                                <textarea
                                    id="notes"
                                    v-model="form.notes"
                                    rows="3"
                                    placeholder="Any notes or special instructions…"
                                    class="bizdocs-input resize-none" />
                            </div>

                            <!-- Payment Instructions (invoices only) -->
                            <div v-if="documentType === 'invoice'">
                                <label for="payment_instructions" class="bizdocs-label">Payment Instructions</label>
                                <textarea
                                    id="payment_instructions"
                                    v-model="form.payment_instructions"
                                    rows="3"
                                    placeholder="How should the customer pay?"
                                    class="bizdocs-input resize-none" />
                            </div>

                            <!-- Terms (full width) -->
                            <div class="sm:col-span-2">
                                <label for="terms" class="bizdocs-label">Terms &amp; Conditions</label>
                                <textarea
                                    id="terms"
                                    v-model="form.terms"
                                    rows="3"
                                    placeholder="Enter your standard terms and conditions…"
                                    class="bizdocs-input resize-none" />
                            </div>

                        </div>
                    </div>

                    <!-- ── Action row ── -->
                    <div class="flex items-center justify-between pt-2">
                        <div class="flex items-center gap-2">
                            <!-- Clear draft -->
                            <button
                                v-if="autoSaveStatus !== 'idle' || draft"
                                type="button"
                                @click="clearDraft"
                                class="px-4 py-2 text-xs font-medium text-slate-500 hover:text-red-500 hover:bg-red-50 rounded-lg border border-slate-200 transition-all">
                                Clear Draft
                            </button>
                            <!-- Cancel -->
                            <a
                                href="/bizdocs/documents"
                                class="px-4 py-2 text-xs font-medium text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-all">
                                Cancel
                            </a>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="group inline-flex items-center gap-2 px-6 py-2.5 bg-slate-900 text-white text-sm font-semibold rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">

                            <span v-if="!form.processing">{{ isEditMode ? 'Update' : 'Create' }} {{ documentTypeLabel }}</span>
                            <span v-else class="flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                                </svg>
                                {{ isEditMode ? 'Updating' : 'Creating' }}…
                            </span>

                            <svg v-if="!form.processing"
                                class="w-4 h-4 group-hover:translate-x-0.5 transition-transform"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </button>
                    </div>

                </form>
            </div>
        </div>

        <!-- Quick Add Customer Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0">
                <div
                    v-if="showQuickAddModal"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
                    @click.self="closeQuickAdd">

                    <Transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95"
                        enter-to-class="opacity-100 scale-100"
                        leave-active-class="transition duration-150 ease-in"
                        leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-95">
                        <div
                            v-if="showQuickAddModal"
                            class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden"
                            @click.stop>

                            <!-- Modal header -->
                            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                                <div>
                                    <h3 class="text-lg font-bold text-slate-900">Quick Add Customer</h3>
                                    <p class="text-xs text-slate-400 mt-0.5">Add a one-off customer quickly</p>
                                </div>
                                <button
                                    type="button"
                                    @click="closeQuickAdd"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors"
                                    aria-label="Close modal">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            <!-- Modal body -->
                            <form @submit.prevent="submitQuickCustomer" class="px-6 py-5">
                                <div class="space-y-4">

                                    <div>
                                        <label for="quick_name" class="bizdocs-label">
                                            Customer Name <span class="text-red-400">*</span>
                                        </label>
                                        <input
                                            id="quick_name"
                                            v-model="quickCustomerForm.name"
                                            type="text"
                                            required
                                            placeholder="Enter customer name"
                                            class="bizdocs-input"
                                            :class="{ 'bizdocs-input-error': quickCustomerErrors.name }" />
                                        <p v-if="quickCustomerErrors.name" class="bizdocs-error">
                                            {{ quickCustomerErrors.name }}
                                        </p>
                                    </div>

                                    <div>
                                        <label for="quick_phone" class="bizdocs-label">Phone</label>
                                        <input
                                            id="quick_phone"
                                            v-model="quickCustomerForm.phone"
                                            type="tel"
                                            placeholder="+260 97X XXX XXX"
                                            class="bizdocs-input" />
                                    </div>

                                    <div>
                                        <label for="quick_email" class="bizdocs-label">Email</label>
                                        <input
                                            id="quick_email"
                                            v-model="quickCustomerForm.email"
                                            type="email"
                                            placeholder="customer@example.com"
                                            class="bizdocs-input" />
                                    </div>

                                    <div>
                                        <label for="quick_address" class="bizdocs-label">Address</label>
                                        <textarea
                                            id="quick_address"
                                            v-model="quickCustomerForm.address"
                                            rows="2"
                                            placeholder="Customer address (optional)"
                                            class="bizdocs-input resize-none" />
                                    </div>

                                    <p v-if="quickCustomerErrors.general" class="text-xs text-red-500">
                                        {{ quickCustomerErrors.general }}
                                    </p>

                                </div>

                                <!-- Modal footer -->
                                <div class="flex items-center justify-end gap-2 mt-6 pt-5 border-t border-slate-100">
                                    <button
                                        type="button"
                                        @click="closeQuickAdd"
                                        class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-all">
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        :disabled="quickCustomerProcessing"
                                        class="inline-flex items-center gap-2 px-5 py-2 bg-slate-900 text-white text-sm font-semibold rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                                        <span v-if="!quickCustomerProcessing">Add Customer</span>
                                        <span v-else class="flex items-center gap-2">
                                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                                            </svg>
                                            Adding...
                                        </span>
                                    </button>
                                </div>
                            </form>

                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>

<style scoped>
.bizdocs-label {
    @apply block text-xs font-semibold text-slate-600 mb-1.5 tracking-wide;
}

.bizdocs-input {
    @apply block w-full px-3.5 py-2.5 text-sm text-slate-800 bg-slate-50
           border border-slate-200 rounded-lg placeholder-slate-300
           focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10
           transition-all duration-150 outline-none;
}

.bizdocs-input-error {
    @apply border-red-300 bg-red-50 focus:border-red-400 focus:ring-red-500/10;
}

.bizdocs-error {
    @apply mt-1.5 text-xs text-red-500;
}
</style>