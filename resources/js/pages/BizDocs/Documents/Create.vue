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
    XMarkIcon,
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

interface Template {
    id: number;
    name: string;
    document_type: string;
    visibility: string;
    industry_category: string;
    template_structure: any;
    is_default: boolean;
}

interface Props {
    documentType: string;
    businessProfile: BusinessProfile;
    customers: Customer[];
    templates: Template[];
    defaultTemplateId: number | null;
}

const props = defineProps<Props>();

// ─── Line item model ─────────────────────────────────────────────────
// dimensions:       raw user input — expression like "1.2*1.3" or blank
// dimensions_value: evaluated result of dimensions (defaults to 1 when blank)
// quantity:         plain numeric count
// effective qty for calculation = dimensions_value × quantity
interface LineItem {
    description:      string;
    dimensions:       string;
    dimensions_value: number;
    quantity:         number;
    unit_price:       number;
    discount_amount:  number;
}

const STORAGE_KEY    = `bizdocs_document_draft_${props.documentType}`;
const autoSaveStatus = ref<'saved' | 'saving' | 'idle'>('idle');
let   autoSaveTimeout: ReturnType<typeof setTimeout> | null = null;

const loadDraft = () => {
    const saved = localStorage.getItem(STORAGE_KEY);
    if (saved) { try { return JSON.parse(saved); } catch { return null; } }
    return null;
};
const draft = loadDraft();

// ─── Expression evaluator ─────────────────────────────────────────────
// Supports * × x for multiplication, e.g. 1.2*1.3 or 2.4×1.2
const evaluateDimensions = (expr: string): number => {
    if (!expr.trim()) return 1; // blank → no dimension multiplier
    try {
        const normalised = expr.replace(/\s/g, '').replace(/[x×]/g, '*');
        if (!/^[\d+\-*/.()]+$/.test(normalised)) return 1;
        const result = new Function(`return ${normalised}`)();
        return isNaN(result) || !isFinite(result) || result <= 0 ? 1 : result;
    } catch {
        return 1;
    }
};

// True when dimensions contains a valid evaluable expression (not just a number or blank).
// Guarded with ?? '' / ?? 1 because items from an older localStorage draft won't have
// the dimensions field — they'll be undefined until the draft is cleared.
const isDimensionExpression = (item: LineItem) => {
    const t = (item.dimensions ?? '').trim();
    return t !== '' && !/^\d+(\.\d+)?$/.test(t) && (item.dimensions_value ?? 1) !== 1;
};

const updateDimensions = (item: LineItem, value: string) => {
    item.dimensions       = value ?? '';
    item.dimensions_value = evaluateDimensions(value ?? '');
};

// Migrate items from draft — older drafts won't have dimensions/dimensions_value.
// Normalise every item so the template never sees undefined on these fields.
const normalisedDraftItems: LineItem[] = (draft?.items ?? [
    { description: '', dimensions: '', dimensions_value: 1, quantity: 1, unit_price: 0, discount_amount: 0 },
]).map((item: any) => ({
    description:      item.description      ?? '',
    dimensions:       item.dimensions       ?? '',
    dimensions_value: item.dimensions_value ?? 1,
    quantity:         item.quantity         ?? 1,
    unit_price:       item.unit_price       ?? 0,
    discount_amount:  item.discount_amount  ?? 0,
}));

const form = useForm({
    customer_id:          draft?.customer_id          || null as number | null,
    template_id:          draft?.template_id          || props.defaultTemplateId || null as number | null,
    document_type:        props.documentType,
    issue_date:           draft?.issue_date           || new Date().toISOString().split('T')[0],
    due_date:             draft?.due_date             || '',
    validity_date:        draft?.validity_date        || '',
    notes:                draft?.notes                || props.businessProfile?.defaultNotes        || '',
    terms:                draft?.terms                || props.businessProfile?.defaultTerms        || '',
    payment_instructions: draft?.payment_instructions || props.businessProfile?.defaultPaymentInstructions || '',
    discount_type:        draft?.discount_type        || 'amount' as 'amount' | 'percentage',
    discount_value:       draft?.discount_value       || 0,
    collect_tax:          draft?.collect_tax          !== undefined ? draft.collect_tax : true,
    items:                normalisedDraftItems,
});

const documentTypeLabel = computed(() => ({
    invoice:          'Invoice',
    receipt:          'Receipt',
    quotation:        'Quotation',
    delivery_note:    'Delivery Note',
    proforma_invoice: 'Proforma Invoice',
    credit_note:      'Credit Note',
    debit_note:       'Debit Note',
    purchase_order:   'Purchase Order',
}[props.documentType] ?? 'Document'));

// ─── Auto-save ────────────────────────────────────────────────────────
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

// ─── Items ────────────────────────────────────────────────────────────
const addLineItem = () =>
    form.items.push({ description: '', dimensions: '', dimensions_value: 1, quantity: 1, unit_price: 0, discount_amount: 0 });

const removeLineItem = (i: number) => {
    if (form.items.length > 1) form.items.splice(i, 1);
};

// Line total: dimensions_value × quantity × unit_price
const lineTotal = (item: LineItem) =>
    (item.dimensions_value ?? 1) * item.quantity * item.unit_price;

const totals = computed(() => {
    const subtotal      = form.items.reduce((s, i) => s + (i.dimensions_value ?? 1) * i.quantity * i.unit_price, 0);
    const discountAmount = form.discount_type === 'percentage' 
        ? (subtotal * form.discount_value) / 100 
        : form.discount_value;
    const afterDiscount = subtotal - discountAmount;
    const taxTotal      = form.collect_tax ? afterDiscount * (props.businessProfile.defaultTaxRate / 100) : 0;
    return { subtotal, discountTotal: discountAmount, taxTotal, grandTotal: afterDiscount + taxTotal };
});

const fmt = (n: number) => n.toFixed(2);

// ─── Template ─────────────────────────────────────────────────────────
const showTemplatePreview = ref(false);
const selectedTemplate = ref<Template | null>(null);

// Watch for template_id changes and update selectedTemplate
watch(() => form.template_id, (newId) => {
    if (!newId) {
        selectedTemplate.value = null;
        return;
    }
    
    const numericId = typeof newId === 'string' ? parseInt(newId, 10) : newId;
    selectedTemplate.value = props.templates.find(t => t.id === numericId) ?? null;
}, { immediate: true });

const formatIndustryCategory = (c: string) =>
    c.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');

// ─── Submit ───────────────────────────────────────────────────────────
const submit = () => {
    form.post('/bizdocs/documents', {
        onSuccess: () => { localStorage.removeItem(STORAGE_KEY); autoSaveStatus.value = 'idle'; },
        onError: (errors) => {
            const el = document.querySelector(`[name="${Object.keys(errors)[0]}"]`);
            el?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        },
    });
};

const clearDraft = async () => {
    const r = await Swal.fire({ title: 'Clear Draft?', text: 'This will reset the form.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc2626', cancelButtonColor: '#64748b', confirmButtonText: 'Yes, clear it', cancelButtonText: 'Cancel' });
    if (r.isConfirmed) {
        localStorage.removeItem(STORAGE_KEY);
        form.reset();
        form.items = [{ description: '', dimensions: '', dimensions_value: 1, quantity: 1, unit_price: 0, discount_amount: 0 }];
        form.discount_type = 'amount';
        form.discount_value = 0;
        form.collect_tax = true;
        autoSaveStatus.value = 'idle';
    }
};

// ─── Quick Add Customer ───────────────────────────────────────────────
const showQuickAddModal       = ref(false);
const quickCustomerProcessing = ref(false);
const quickCustomerErrors     = ref<Record<string, string>>({});
const quickCustomerForm       = ref({ name: '', phone: '', email: '', address: '' });

const formatPhone = (v: string): string => {
    if (!v) return '';
    const c = v.replace(/[^\d+]/g, '');
    if (c.startsWith('+260')) return c;
    const d = c.replace(/^\+/, '');
    if (d.startsWith('260')) return '+' + d;
    if (d.startsWith('0'))   return '+260' + d.substring(1);
    if (/^[97]/.test(d))     return '+260' + d;
    return d ? '+260' + d : '';
};
watch(() => quickCustomerForm.value.phone, (v) => {
    if (v && !v.startsWith('+260')) {
        const f = formatPhone(v);
        if (f !== v) quickCustomerForm.value.phone = f;
    }
});

const openQuickAdd  = () => { quickCustomerForm.value = { name: '', phone: '', email: '', address: '' }; quickCustomerErrors.value = {}; showQuickAddModal.value = true; };
const closeQuickAdd = () => { showQuickAddModal.value = false; };

const submitQuickCustomer = async () => {
    quickCustomerProcessing.value = true;
    quickCustomerErrors.value     = {};
    try {
        const { data } = await axios.post('/bizdocs/customers', quickCustomerForm.value, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (data.success && data.customer) {
            props.customers.push({ id: data.customer.id, name: data.customer.name, phone: data.customer.phone || '', email: data.customer.email || '', address: data.customer.address || '' });
            form.customer_id = data.customer.id;
            closeQuickAdd();
        }
    } catch (error: any) {
        quickCustomerErrors.value = error.response?.data?.errors || { general: error.response?.data?.message || 'Failed to create customer' };
    } finally {
        quickCustomerProcessing.value = false;
    }
};

onMounted(() => {
    if (draft) { autoSaveStatus.value = 'saved'; setTimeout(() => { autoSaveStatus.value = 'idle'; }, 3000); }
});
</script>

<template>
    <Head :title="`Create ${documentTypeLabel}`" />

    <AppLayout>
        <div class="min-h-screen bg-slate-50 py-8 px-4">
            <div class="max-w-5xl mx-auto">

                <!-- Page header -->
                <div class="flex items-start justify-between mb-7">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <Link href="/bizdocs/documents"
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
                        <h1 class="text-2xl font-bold text-slate-900 leading-none">Create {{ documentTypeLabel }}</h1>
                        <p class="text-sm text-slate-400 mt-1">Fill in the details below to generate your document</p>
                    </div>

                    <!-- Auto-save pill -->
                    <Transition
                        enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 translate-y-1" enter-to-class="opacity-100 translate-y-0"
                        leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
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

                    <!-- Document type switcher -->
                    <div class="bg-white border border-slate-200 rounded-xl px-5 py-4 flex flex-col sm:flex-row sm:items-center gap-3">
                        <div class="flex items-center gap-3 flex-1">
                            <span class="text-xs font-bold tracking-widest text-blue-500 uppercase flex-shrink-0">Type</span>
                            <span class="h-px bg-slate-100 flex-1"></span>
                        </div>
                        <div class="relative">
                            <select
                                :value="documentType"
                                @change="(e) => router.visit(`/bizdocs/documents/create?type=${(e.target as HTMLSelectElement).value}`)"
                                class="bizdocs-input bg-white pr-8 sm:w-56 appearance-none cursor-pointer">
                                <option value="invoice">Invoice</option>
                                <option value="quotation">Quotation</option>
                                <option value="proforma_invoice">Proforma Invoice</option>
                                <option value="receipt">Receipt</option>
                                <option value="delivery_note">Delivery Note</option>
                                <option value="credit_note">Credit Note</option>
                                <option value="debit_note">Debit Note</option>
                                <option value="purchase_order">Purchase Order</option>
                            </select>
                            <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Template selector -->
                    <div v-if="templates.length > 0" class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                        <div class="flex items-center gap-3 px-5 py-3.5 border-b border-slate-100">
                            <span class="text-xs font-bold tracking-widest text-blue-500 uppercase">Template</span>
                            <span class="flex-1 h-px bg-slate-100"></span>
                            <Link :href="`/bizdocs/templates/gallery?type=${documentType}`"
                                class="text-xs font-medium text-blue-600 hover:text-blue-700 transition-colors">
                                Browse all →
                            </Link>
                        </div>
                        <div class="px-5 py-4">
                            <div class="flex gap-2">
                                <select v-model.number="form.template_id" class="bizdocs-input bg-white flex-1">
                                    <option :value="null">Default Template</option>
                                    <optgroup v-if="templates.filter(t => t.visibility === 'industry').length" label="Industry Templates">
                                        <option v-for="t in templates.filter(t => t.visibility === 'industry')" :key="t.id" :value="t.id">
                                            {{ t.name }}{{ t.is_default ? ' (Recommended)' : '' }}
                                        </option>
                                    </optgroup>
                                    <optgroup v-if="templates.filter(t => t.visibility === 'business').length" label="My Custom Templates">
                                        <option v-for="t in templates.filter(t => t.visibility === 'business')" :key="t.id" :value="t.id">{{ t.name }}</option>
                                    </optgroup>
                                </select>
                                <button v-if="form.template_id" type="button" @click="showTemplatePreview = true"
                                    class="px-3.5 py-2 text-xs font-semibold text-blue-600 bg-blue-50 border border-blue-100 rounded-lg hover:bg-blue-100 transition-all">
                                    Preview
                                </button>
                            </div>
                            <div v-if="selectedTemplate" :key="`template-info-${selectedTemplate.id}`" class="mt-3 flex items-center gap-2 px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg">
                                <div class="w-6 h-6 bg-blue-100 rounded flex items-center justify-center flex-shrink-0">
                                    <DocumentTextIcon class="w-3.5 h-3.5 text-blue-600" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-slate-800 truncate">{{ selectedTemplate.name }}</p>
                                    <p class="text-xs text-slate-400">
                                        {{ selectedTemplate.visibility === 'industry' ? 'Industry' : 'Custom' }}
                                        <span v-if="selectedTemplate.industry_category"> · {{ formatIndustryCategory(selectedTemplate.industry_category) }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── 01: Document Details ── -->
                    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                        <div class="flex items-center gap-3 px-5 py-3.5 border-b border-slate-100">
                            <span class="text-xs font-bold tracking-widest text-blue-500 uppercase">01</span>
                            <span class="flex-1 h-px bg-slate-100"></span>
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Document Details</span>
                        </div>
                        <div class="px-5 py-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="sm:col-span-2">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label for="customer_id" class="bizdocs-label mb-0">Customer <span class="text-red-400">*</span></label>
                                        <button type="button" @click="openQuickAdd"
                                            class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                                            <PlusIcon class="w-3.5 h-3.5" />
                                            Quick Add
                                        </button>
                                    </div>
                                    <select id="customer_id" v-model="form.customer_id" required
                                        class="bizdocs-input bg-white"
                                        :class="{ 'bizdocs-input-error': form.errors.customer_id }">
                                        <option :value="null">Select a customer…</option>
                                        <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
                                    </select>
                                    <p v-if="form.errors.customer_id" class="bizdocs-error">{{ form.errors.customer_id }}</p>
                                </div>
                                <div>
                                    <label for="issue_date" class="bizdocs-label">Issue Date <span class="text-red-400">*</span></label>
                                    <div class="relative">
                                        <CalendarIcon class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-300 pointer-events-none" />
                                        <input id="issue_date" v-model="form.issue_date" type="date" required class="bizdocs-input pl-10" />
                                    </div>
                                </div>
                                <div v-if="documentType === 'invoice'">
                                    <label for="due_date" class="bizdocs-label">Due Date</label>
                                    <div class="relative">
                                        <CalendarIcon class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-300 pointer-events-none" />
                                        <input id="due_date" v-model="form.due_date" type="date" class="bizdocs-input pl-10" />
                                    </div>
                                </div>
                                <div v-if="documentType === 'quotation'">
                                    <label for="validity_date" class="bizdocs-label">Valid Until</label>
                                    <div class="relative">
                                        <CalendarIcon class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-300 pointer-events-none" />
                                        <input id="validity_date" v-model="form.validity_date" type="date" class="bizdocs-input pl-10" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── 02: Line Items ── -->
                    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                        <div class="flex items-center gap-3 px-5 py-3.5 border-b border-slate-100">
                            <span class="text-xs font-bold tracking-widest text-blue-500 uppercase">02</span>
                            <span class="flex-1 h-px bg-slate-100"></span>
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Line Items</span>
                            <button type="button" @click="addLineItem"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-900 text-white text-xs font-semibold rounded-lg hover:bg-blue-600 transition-all duration-200">
                                <PlusIcon class="w-3.5 h-3.5" />
                                Add Item
                            </button>
                        </div>

                        <div class="px-5 py-4">

                            <!-- ── Desktop table ── -->
                            <!--
                                Columns: Description | Dimensions (optional) | Qty | Unit Price | Total | ×
                                grid:    grow         ~9rem                    ~5rem ~8rem        ~7rem   ~2rem
                                Dimensions is optional — blank = no multiplier effect, printed as-is on document.
                                When a valid expression is entered (e.g. 1.2*1.3), it evaluates to a numeric
                                multiplier and a small result chip appears below the field.
                            -->
                            <div class="hidden md:block">
                                <!-- Column headers -->
                                <div class="grid items-center gap-2 px-2 mb-2"
                                    style="grid-template-columns: 1fr 9rem 5rem 8rem 7rem 2rem;">
                                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Description</span>
                                    <div>
                                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Size/Area</span>
                                        <span class="block text-[10px] text-slate-300 font-normal normal-case mt-0.5">optional · e.g. 1.2×1.3</span>
                                    </div>
                                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Qty</span>
                                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider text-right">Unit Price</span>
                                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider text-right">Total</span>
                                    <span></span>
                                </div>

                                <!-- Item rows -->
                                <div class="space-y-1.5">
                                    <div
                                        v-for="(item, index) in form.items"
                                        :key="index"
                                        class="grid items-start gap-2 px-2 py-2 rounded-lg border border-slate-100 hover:border-blue-100 hover:bg-slate-50/60 transition-all"
                                        style="grid-template-columns: 1fr 9rem 5rem 8rem 7rem 2rem;">

                                        <!-- Description -->
                                        <input
                                            :id="`desc_${index}`"
                                            v-model="item.description"
                                            type="text"
                                            required
                                            placeholder="Item or service description…"
                                            class="bizdocs-input py-2 text-sm" />

                                        <!-- Dimensions (optional) -->
                                        <div>
                                            <input
                                                :id="`dim_${index}`"
                                                v-model="item.dimensions"
                                                @input="updateDimensions(item, ($event.target as HTMLInputElement).value)"
                                                type="text"
                                                placeholder="e.g. 1.2×1.3"
                                                class="bizdocs-input py-2 text-sm font-mono"
                                                :title="item.dimensions ? `= ${item.dimensions_value}` : 'Optional: enter dimensions or leave blank'" />
                                            <!-- Evaluated result chip — only for expressions -->
                                            <div v-if="isDimensionExpression(item)"
                                                class="flex items-center gap-1 mt-1 px-1">
                                                <span class="text-[10px] text-slate-400">=</span>
                                                <span class="text-[11px] font-semibold text-emerald-600 font-mono">{{ fmt(item.dimensions_value) }}</span>
                                                <span class="text-[10px] text-slate-300">m²</span>
                                            </div>
                                        </div>

                                        <!-- Qty (plain number) -->
                                        <input
                                            :id="`qty_${index}`"
                                            v-model.number="item.quantity"
                                            type="number"
                                            step="0.01"
                                            min="0.01"
                                            required
                                            class="bizdocs-input py-2 text-sm text-right tabular-nums" />

                                        <!-- Unit Price -->
                                        <input
                                            :id="`price_${index}`"
                                            v-model.number="item.unit_price"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            required
                                            class="bizdocs-input py-2 text-sm text-right tabular-nums" />

                                        <!-- Line total -->
                                        <div class="flex items-center justify-end h-[38px]">
                                            <div class="text-right">
                                                <span class="text-sm font-semibold text-slate-700 tabular-nums block">
                                                    {{ fmt(lineTotal(item)) }}
                                                </span>
                                                <!-- Show effective qty when dimensions are active -->
                                                <span v-if="isDimensionExpression(item) || item.dimensions_value !== 1"
                                                    class="text-[10px] text-slate-400 font-mono">
                                                    ×{{ fmt(item.dimensions_value) }}
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Remove -->
                                        <div class="flex items-center justify-center h-[38px]">
                                            <button
                                                type="button"
                                                @click="removeLineItem(index)"
                                                :disabled="form.items.length === 1"
                                                class="w-7 h-7 flex items-center justify-center rounded-md text-slate-300 hover:text-red-500 hover:bg-red-50 disabled:opacity-20 disabled:cursor-not-allowed transition-colors"
                                                aria-label="Remove item">
                                                <TrashIcon class="w-3.5 h-3.5" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ── Mobile stacked layout ── -->
                            <div class="md:hidden space-y-3">
                                <div v-for="(item, index) in form.items" :key="`m-${index}`"
                                    class="border border-slate-200 rounded-lg overflow-hidden">
                                    <div class="grid gap-3 p-3">
                                        <div>
                                            <label :for="`m_desc_${index}`" class="bizdocs-label">Description *</label>
                                            <input :id="`m_desc_${index}`" v-model="item.description" type="text" required
                                                placeholder="Item or service description" class="bizdocs-input" />
                                        </div>
                                        <div>
                                            <label :for="`m_dim_${index}`" class="bizdocs-label">
                                                Size/Area
                                                <span class="ml-1 font-normal text-slate-400">(optional · e.g. 1.2×1.3)</span>
                                            </label>
                                            <input :id="`m_dim_${index}`"
                                                v-model="item.dimensions"
                                                @input="updateDimensions(item, ($event.target as HTMLInputElement).value)"
                                                type="text" placeholder="Leave blank if not applicable"
                                                class="bizdocs-input font-mono" />
                                            <p v-if="isDimensionExpression(item)" class="text-xs text-emerald-600 mt-1 font-mono">
                                                = {{ fmt(item.dimensions_value) }} m²
                                            </p>
                                        </div>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label :for="`m_qty_${index}`" class="bizdocs-label">Qty *</label>
                                                <input :id="`m_qty_${index}`" v-model.number="item.quantity"
                                                    type="number" step="0.01" min="0.01" required
                                                    class="bizdocs-input text-right tabular-nums" />
                                            </div>
                                            <div>
                                                <label :for="`m_price_${index}`" class="bizdocs-label">Unit Price *</label>
                                                <input :id="`m_price_${index}`" v-model.number="item.unit_price"
                                                    type="number" step="0.01" min="0" required
                                                    class="bizdocs-input text-right tabular-nums" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between bg-slate-50 border-t border-slate-100 px-3 py-1.5">
                                        <div>
                                            <span class="text-xs text-slate-400">Line total</span>
                                            <span v-if="isDimensionExpression(item)" class="text-xs text-slate-400 ml-1">(dim × qty × price)</span>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <span class="text-xs font-semibold text-slate-700 tabular-nums">
                                                {{ businessProfile.defaultCurrency }} {{ fmt(lineTotal(item)) }}
                                            </span>
                                            <button type="button" @click="removeLineItem(index)"
                                                :disabled="form.items.length === 1"
                                                class="w-7 h-7 flex items-center justify-center rounded-md text-slate-300 hover:text-red-500 hover:bg-red-50 disabled:opacity-20 transition-colors">
                                                <TrashIcon class="w-3.5 h-3.5" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Totals -->
                            <div class="flex justify-end mt-4 pt-3 border-t border-slate-100">
                                <div class="w-full sm:w-72 border border-slate-200 rounded-lg overflow-hidden">
                                    <div class="flex justify-between px-4 py-2 text-xs text-slate-600 border-b border-slate-100">
                                        <span>Subtotal</span>
                                        <span class="font-medium text-slate-800 tabular-nums">{{ businessProfile.defaultCurrency }} {{ fmt(totals.subtotal) }}</span>
                                    </div>
                                    
                                    <!-- Discount Controls -->
                                    <div class="px-4 py-2 bg-amber-50 border-b border-amber-100">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-xs font-medium text-amber-900">Discount</span>
                                            <div class="flex items-center gap-2">
                                                <select v-model="form.discount_type"
                                                    class="px-2 py-1 text-xs border border-amber-200 rounded focus:ring-1 focus:ring-amber-500 focus:border-amber-500 bg-white">
                                                    <option value="percentage">%</option>
                                                    <option value="amount">{{ businessProfile.defaultCurrency }}</option>
                                                </select>
                                                <input
                                                    v-model.number="form.discount_value"
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    placeholder="0"
                                                    class="w-20 px-2 py-1 text-xs text-right border border-amber-200 rounded focus:ring-1 focus:ring-amber-500 focus:border-amber-500" />
                                                <button v-if="form.discount_value > 0" type="button" @click="form.discount_value = 0"
                                                    class="px-2 py-1 text-xs text-amber-600 hover:text-amber-700 hover:bg-amber-100 rounded transition-colors">
                                                    Clear
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div v-if="totals.discountTotal > 0" class="flex justify-between px-4 py-2 text-xs text-slate-600 border-b border-slate-100">
                                        <span>Discount Applied</span>
                                        <span class="font-medium text-amber-600 tabular-nums">−{{ businessProfile.defaultCurrency }} {{ fmt(totals.discountTotal) }}</span>
                                    </div>
                                    
                                    <!-- Tax Toggle -->
                                    <div class="px-4 py-2 bg-blue-50 border-b border-blue-100">
                                        <label class="flex items-center justify-between gap-2 cursor-pointer">
                                            <span class="text-xs font-medium text-blue-900">Collect VAT ({{ businessProfile.defaultTaxRate }}%)</span>
                                            <input
                                                v-model="form.collect_tax"
                                                type="checkbox"
                                                class="w-4 h-4 text-blue-600 border-blue-300 rounded focus:ring-blue-500 focus:ring-2" />
                                        </label>
                                    </div>
                                    
                                    <div v-if="form.collect_tax" class="flex justify-between px-4 py-2 text-xs text-slate-600 border-b border-slate-100">
                                        <span>VAT ({{ businessProfile.defaultTaxRate }}%)</span>
                                        <span class="font-medium text-slate-800 tabular-nums">{{ businessProfile.defaultCurrency }} {{ fmt(totals.taxTotal) }}</span>
                                    </div>
                                    
                                    <div class="flex justify-between px-4 py-3 bg-slate-900">
                                        <span class="text-sm font-bold text-white">Grand Total</span>
                                        <span class="text-sm font-bold text-blue-300 tabular-nums">{{ businessProfile.defaultCurrency }} {{ fmt(totals.grandTotal) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── 03: Additional Information ── -->
                    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                        <div class="flex items-center gap-3 px-5 py-3.5 border-b border-slate-100">
                            <span class="text-xs font-bold tracking-widest text-slate-400 uppercase">03</span>
                            <span class="flex-1 h-px bg-slate-100"></span>
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Additional Information</span>
                        </div>
                        <div class="px-5 py-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div :class="documentType === 'invoice' ? 'sm:col-span-1' : 'sm:col-span-2'">
                                <label for="notes" class="bizdocs-label">Notes</label>
                                <textarea id="notes" v-model="form.notes" rows="3" placeholder="Any notes or special instructions…" class="bizdocs-input resize-none" />
                            </div>
                            <div v-if="documentType === 'invoice'">
                                <label for="payment_instructions" class="bizdocs-label">Payment Instructions</label>
                                <textarea id="payment_instructions" v-model="form.payment_instructions" rows="3" placeholder="How should the customer pay?" class="bizdocs-input resize-none" />
                            </div>
                            <div class="sm:col-span-2">
                                <label for="terms" class="bizdocs-label">Terms &amp; Conditions</label>
                                <textarea id="terms" v-model="form.terms" rows="3" placeholder="Enter your standard terms and conditions…" class="bizdocs-input resize-none" />
                            </div>
                        </div>
                    </div>

                    <!-- Action row -->
                    <div class="flex items-center justify-between pt-2">
                        <div class="flex items-center gap-2">
                            <button v-if="autoSaveStatus !== 'idle' || draft" type="button" @click="clearDraft"
                                class="px-4 py-2 text-xs font-medium text-slate-500 hover:text-red-500 hover:bg-red-50 rounded-lg border border-slate-200 transition-all">
                                Clear Draft
                            </button>
                            <a href="/bizdocs/documents"
                                class="px-4 py-2 text-xs font-medium text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-all">
                                Cancel
                            </a>
                        </div>
                        <button type="submit" :disabled="form.processing"
                            class="group inline-flex items-center gap-2 px-6 py-2.5 bg-slate-900 text-white text-sm font-semibold rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                            <span v-if="!form.processing">Create {{ documentTypeLabel }}</span>
                            <span v-else class="flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                                Creating…
                            </span>
                            <svg v-if="!form.processing" class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </button>
                    </div>

                </form>
            </div>
        </div>

        <!-- ── Quick Add Customer Modal ── -->
        <Teleport to="body">
            <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="showQuickAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" @click.self="closeQuickAdd">
                    <Transition enter-active-class="transition duration-150 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-100 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                        <div v-if="showQuickAddModal" class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden border border-slate-200" @click.stop>
                            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                                <div>
                                    <p class="text-sm font-bold text-slate-900">Quick Add Customer</p>
                                    <p class="text-xs text-slate-400 mt-0.5">Add a new customer without leaving this page</p>
                                </div>
                                <button type="button" @click="closeQuickAdd" class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors" aria-label="Close">
                                    <XMarkIcon class="w-4 h-4" />
                                </button>
                            </div>
                            <form @submit.prevent="submitQuickCustomer" class="px-6 py-5 space-y-4">
                                <div>
                                    <label for="quick_name" class="bizdocs-label">Customer Name <span class="text-red-400">*</span></label>
                                    <input id="quick_name" v-model="quickCustomerForm.name" type="text" required placeholder="Full name or business name" class="bizdocs-input" :class="{ 'bizdocs-input-error': quickCustomerErrors.name }" />
                                    <p v-if="quickCustomerErrors.name" class="bizdocs-error">{{ quickCustomerErrors.name }}</p>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label for="quick_phone" class="bizdocs-label">Phone</label>
                                        <input id="quick_phone" v-model="quickCustomerForm.phone" type="tel" placeholder="+260 97X XXX XXX" class="bizdocs-input" />
                                    </div>
                                    <div>
                                        <label for="quick_email" class="bizdocs-label">Email</label>
                                        <input id="quick_email" v-model="quickCustomerForm.email" type="email" placeholder="customer@example.com" class="bizdocs-input" />
                                    </div>
                                </div>
                                <div>
                                    <label for="quick_address" class="bizdocs-label">Address</label>
                                    <textarea id="quick_address" v-model="quickCustomerForm.address" rows="2" placeholder="Customer address (optional)" class="bizdocs-input resize-none" />
                                </div>
                                <p v-if="quickCustomerErrors.general" class="text-xs text-red-500">{{ quickCustomerErrors.general }}</p>
                                <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                                    <button type="button" @click="closeQuickAdd" class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-all">Cancel</button>
                                    <button type="submit" :disabled="quickCustomerProcessing" class="inline-flex items-center gap-2 px-5 py-2 bg-slate-900 text-white text-sm font-semibold rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 transition-all">
                                        <span v-if="!quickCustomerProcessing">Add Customer</span>
                                        <span v-else class="flex items-center gap-2">
                                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                                            Adding…
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>

        <!-- ── Template Preview Modal ── -->
        <Teleport to="body">
            <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="showTemplatePreview && selectedTemplate" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" @click.self="showTemplatePreview = false">
                    <Transition enter-active-class="transition duration-150 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-100 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                        <div v-if="showTemplatePreview && selectedTemplate" class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] flex flex-col overflow-hidden border border-slate-200" @click.stop>
                            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                                <div>
                                    <p class="text-sm font-bold text-slate-900">{{ selectedTemplate.name }}</p>
                                    <p class="text-xs text-slate-400 mt-0.5">
                                        {{ selectedTemplate.visibility === 'industry' ? 'Industry Template' : 'Custom Template' }}
                                        <span v-if="selectedTemplate.industry_category"> · {{ formatIndustryCategory(selectedTemplate.industry_category) }}</span>
                                    </p>
                                </div>
                                <button type="button" @click="showTemplatePreview = false" class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors">
                                    <XMarkIcon class="w-4 h-4" />
                                </button>
                            </div>
                            <div class="flex-1 overflow-y-auto p-4 bg-slate-50 relative">
                                <!-- Loading skeleton -->
                                <div class="absolute inset-4 bg-white rounded-lg shadow animate-pulse">
                                    <div class="h-full flex items-center justify-center">
                                        <div class="text-center">
                                            <div class="w-12 h-12 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin mx-auto mb-3"></div>
                                            <p class="text-sm text-slate-500">Loading template preview...</p>
                                        </div>
                                    </div>
                                </div>
                                <iframe 
                                    :key="`prev-${selectedTemplate.id}`" 
                                    :src="`/bizdocs/templates/${selectedTemplate.id}/preview`" 
                                    class="w-full border-0 rounded-lg shadow relative z-10" 
                                    style="min-height:800px;"
                                    @load="$event.target.previousElementSibling?.remove()"
                                ></iframe>
                            </div>
                            <div class="flex justify-end px-6 py-3 border-t border-slate-100">
                                <button type="button" @click="showTemplatePreview = false" class="px-5 py-2 bg-slate-900 text-white text-sm font-semibold rounded-lg hover:bg-blue-600 transition-all">Close Preview</button>
                            </div>
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