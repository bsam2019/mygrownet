<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { XMarkIcon, CheckIcon } from '@heroicons/vue/24/outline';

interface Block {
    id: string;
    type: string;
    config?: any;
}

const props = defineProps<{
    block: Block | null;
    show: boolean;
}>();

const emit = defineEmits<{
    close: [];
    save: [config: any];
}>();

// Local copy of config to edit before saving
const localConfig = ref<any>({});

watch(() => props.block, (newBlock) => {
    if (newBlock?.config) {
        localConfig.value = JSON.parse(JSON.stringify(newBlock.config));
    } else {
        localConfig.value = {};
    }
}, { immediate: true });

watch(() => props.show, (visible) => {
    if (visible && props.block?.config) {
        localConfig.value = JSON.parse(JSON.stringify(props.block.config));
    }
});

const blockType = computed(() => props.block?.type || '');

const blockLabel = computed(() => {
    const labels: Record<string, string> = {
        'header': 'Side-by-Side Header',
        'company-header-centered': 'Centered Company Header',
        'invoice-title-bar': 'Invoice Title Bar',
        'invoice-meta': 'Invoice Meta',
        'invoice-meta-inline': 'Meta Inline',
        'customer-details': 'Bill To Card',
        'customer-form-lines': 'Customer Form Lines',
        'customer-split': 'Bill To / Company Split',
        'items-table': 'Items Table (Modern)',
        'items-table-classic': 'Items Table (Classic)',
        'items-table-minimal': 'Items Table (Minimal)',
        'totals': 'Totals Block',
        'totals-classic': 'Totals Classic',
        'payment-details': 'Payment Details',
        'notes-terms': 'Notes & Terms',
        'signature-lines': 'Signature Lines',
        'thank-you-footer': 'Thank You Footer',
        'text': 'Text Block',
        'divider': 'Divider',
    };
    return labels[blockType.value] || blockType.value;
});

const blockIcon = computed(() => {
    const icons: Record<string, string> = {
        'header': '↔️', 'company-header-centered': '🏢', 'invoice-title-bar': '📄',
        'invoice-meta': '📋', 'invoice-meta-inline': '🔢',
        'customer-details': '👤', 'customer-form-lines': '📝', 'customer-split': '👥',
        'items-table': '📊', 'items-table-classic': '🗒️', 'items-table-minimal': '📃',
        'totals': '💰', 'totals-classic': '🧾', 'payment-details': '🏦',
        'notes-terms': '📜', 'signature-lines': '✍️', 'thank-you-footer': '🙏',
        'text': '💬', 'divider': '➖',
    };
    return icons[blockType.value] || '📦';
});

const handleSave = () => emit('save', localConfig.value);
const handleClose = () => emit('close');

// Column presets for table blocks
const tableColumnOptions = [
    { value: 'ser', label: 'Ser #' },
    { value: 'description', label: 'Description' },
    { value: 'qty', label: 'Quantity' },
    { value: 'price', label: 'Unit Price' },
    { value: 'unit_cost', label: 'Unit Cost' },
    { value: 'amount', label: 'Amount' },
    { value: 'total', label: 'Total' },
];

const toggleColumn = (col: string) => {
    const cols: string[] = localConfig.value.columns || [];
    const idx = cols.indexOf(col);
    if (idx >= 0) {
        if (cols.length > 2) localConfig.value.columns = cols.filter(c => c !== col);
    } else {
        localConfig.value.columns = [...cols, col];
    }
};

const hasColumn = (col: string) => (localConfig.value.columns || []).includes(col);
</script>

<template>
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="show && block"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
            @click.self="handleClose">

            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 scale-95 translate-y-2"
                enter-to-class="opacity-100 scale-100 translate-y-0"
            >
                <div v-if="show" class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[85vh] flex flex-col overflow-hidden">

                    <!-- Header -->
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-slate-100 rounded-xl flex items-center justify-center text-lg">
                                {{ blockIcon }}
                            </div>
                            <div>
                                <h2 class="text-sm font-bold text-slate-900">Configure Block</h2>
                                <p class="text-xs text-slate-500">{{ blockLabel }}</p>
                            </div>
                        </div>
                        <button @click="handleClose" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition">
                            <XMarkIcon class="h-5 w-5" />
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="flex-1 overflow-y-auto p-6 space-y-5">

                        <!-- ══════════════ HEADER (side-by-side) ══════════════ -->
                        <template v-if="blockType === 'header'">
                            <div class="field-group">
                                <label class="field-label">Alignment</label>
                                <div class="flex gap-2">
                                    <button v-for="opt in [{ v: 'flex-start', l: 'Left' }, { v: 'space-between', l: 'Apart' }, { v: 'flex-end', l: 'Right' }]"
                                        :key="opt.v" @click="localConfig.alignment = opt.v"
                                        :class="['flex-1 py-2 text-xs font-semibold rounded-xl border-2 transition', localConfig.alignment === opt.v ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-slate-200 text-slate-600 hover:border-slate-300']">
                                        {{ opt.l }}
                                    </button>
                                </div>
                            </div>
                            <div class="field-group">
                                <label class="field-label">Visibility</label>
                                <div class="space-y-2">
                                    <toggle-row v-model="localConfig.showLogo" label="Show Logo" />
                                    <toggle-row v-model="localConfig.showCompanyInfo" label="Show Company Info" />
                                </div>
                            </div>
                        </template>

                        <!-- ══════════════ COMPANY HEADER CENTERED ══════════════ -->
                        <template v-else-if="blockType === 'company-header-centered'">
                            <div class="field-group">
                                <label class="field-label">Name Size</label>
                                <div class="flex gap-2">
                                    <button v-for="opt in [{ v: 'normal', l: 'Normal' }, { v: 'large-name', l: 'Large' }]"
                                        :key="opt.v" @click="localConfig.style = opt.v"
                                        :class="['flex-1 py-2 text-xs font-semibold rounded-xl border-2 transition', localConfig.style === opt.v ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-slate-200 text-slate-600 hover:border-slate-300']">
                                        {{ opt.l }}
                                    </button>
                                </div>
                            </div>
                            <div class="field-group">
                                <label class="field-label">Show / Hide</label>
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between px-3 py-2.5 rounded-xl bg-slate-50">
                                        <span class="text-sm text-slate-700">Logo</span>
                                        <label class="toggle-switch"><input type="checkbox" v-model="localConfig.showLogo" class="sr-only peer"/><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
                                    </div>
                                    <div class="flex items-center justify-between px-3 py-2.5 rounded-xl bg-slate-50">
                                        <span class="text-sm text-slate-700">Address</span>
                                        <label class="toggle-switch"><input type="checkbox" v-model="localConfig.showAddress" class="sr-only peer"/><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
                                    </div>
                                    <div class="flex items-center justify-between px-3 py-2.5 rounded-xl bg-slate-50">
                                        <span class="text-sm text-slate-700">Phone</span>
                                        <label class="toggle-switch"><input type="checkbox" v-model="localConfig.showPhone" class="sr-only peer"/><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
                                    </div>
                                    <div class="flex items-center justify-between px-3 py-2.5 rounded-xl bg-slate-50">
                                        <span class="text-sm text-slate-700">Email</span>
                                        <label class="toggle-switch"><input type="checkbox" v-model="localConfig.showEmail" class="sr-only peer"/><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
                                    </div>
                                    <div class="flex items-center justify-between px-3 py-2.5 rounded-xl bg-slate-50">
                                        <span class="text-sm text-slate-700">Website</span>
                                        <label class="toggle-switch"><input type="checkbox" v-model="localConfig.showWebsite" class="sr-only peer"/><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- ══════════════ INVOICE TITLE BAR ══════════════ -->
                        <template v-else-if="blockType === 'invoice-title-bar'">
                            <div class="field-group">
                                <label class="field-label">Title Text</label>
                                <input type="text" v-model="localConfig.title" placeholder="INVOICE" class="field-input" />
                            </div>
                            <div class="field-group">
                                <label class="field-label">Style</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <button @click="localConfig.style = 'bold-bar'"
                                        :class="['py-3 text-xs font-semibold rounded-xl border-2 transition text-center', localConfig.style === 'bold-bar' ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-slate-200 text-slate-600 hover:border-slate-300']">
                                        <div class="text-lg mb-1">🏷️</div>Bold Bar
                                    </button>
                                    <button @click="localConfig.style = 'large-title'"
                                        :class="['py-3 text-xs font-semibold rounded-xl border-2 transition text-center', localConfig.style === 'large-title' ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-slate-200 text-slate-600 hover:border-slate-300']">
                                        <div class="text-lg mb-1">📰</div>Large Title
                                    </button>
                                </div>
                            </div>
                            <div class="flex items-center justify-between px-3 py-2.5 rounded-xl bg-slate-50">
                                <span class="text-sm text-slate-700">Show Invoice Number</span>
                                <label class="toggle-switch"><input type="checkbox" v-model="localConfig.showNumber" class="sr-only peer"/><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
                            </div>
                        </template>

                        <!-- ══════════════ INVOICE META ══════════════ -->
                        <template v-else-if="blockType === 'invoice-meta'">
                            <div class="flex items-center justify-between px-3 py-2.5 rounded-xl bg-slate-50">
                                <span class="text-sm text-slate-700">Show "INVOICE" Heading</span>
                                <label class="toggle-switch"><input type="checkbox" v-model="localConfig.showTitle" class="sr-only peer"/><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
                            </div>
                            <div class="field-group" v-if="localConfig.showTitle">
                                <label class="field-label">Heading Alignment</label>
                                <div class="flex gap-2">
                                    <button v-for="opt in [{ v: 'left', l: 'Left' }, { v: 'center', l: 'Center' }, { v: 'right', l: 'Right' }]"
                                        :key="opt.v" @click="localConfig.titleAlignment = opt.v"
                                        :class="['flex-1 py-2 text-xs font-semibold rounded-xl border-2 transition', localConfig.titleAlignment === opt.v ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-slate-200 text-slate-600 hover:border-slate-300']">
                                        {{ opt.l }}
                                    </button>
                                </div>
                            </div>
                            <div class="field-group" v-if="localConfig.showTitle">
                                <label class="field-label">Custom Heading Text (optional)</label>
                                <input type="text" v-model="localConfig.titleText" placeholder="INVOICE" class="field-input" />
                            </div>
                        </template>

                        <!-- ══════════════ INVOICE META INLINE ══════════════ -->
                        <template v-else-if="blockType === 'invoice-meta-inline'">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between px-3 py-2.5 rounded-xl bg-slate-50">
                                    <span class="text-sm text-slate-700">Show Invoice Number</span>
                                    <label class="toggle-switch"><input type="checkbox" v-model="localConfig.showInvoiceNumber" class="sr-only peer"/><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
                                </div>
                                <div class="flex items-center justify-between px-3 py-2.5 rounded-xl bg-slate-50">
                                    <span class="text-sm text-slate-700">Show Date</span>
                                    <label class="toggle-switch"><input type="checkbox" v-model="localConfig.showDate" class="sr-only peer"/><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
                                </div>
                            </div>
                        </template>

                        <!-- ══════════════ CUSTOMER DETAILS ══════════════ -->
                        <template v-else-if="blockType === 'customer-details'">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between px-3 py-2.5 rounded-xl bg-slate-50">
                                    <span class="text-sm text-slate-700">Show Address</span>
                                    <label class="toggle-switch"><input type="checkbox" v-model="localConfig.showAddress" class="sr-only peer"/><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
                                </div>
                                <div class="flex items-center justify-between px-3 py-2.5 rounded-xl bg-slate-50">
                                    <span class="text-sm text-slate-700">Show Phone</span>
                                    <label class="toggle-switch"><input type="checkbox" v-model="localConfig.showPhone" class="sr-only peer"/><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
                                </div>
                                <div class="flex items-center justify-between px-3 py-2.5 rounded-xl bg-slate-50">
                                    <span class="text-sm text-slate-700">Show Email</span>
                                    <label class="toggle-switch"><input type="checkbox" v-model="localConfig.showEmail" class="sr-only peer"/><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
                                </div>
                            </div>
                        </template>

                        <!-- ══════════════ CUSTOMER FORM LINES ══════════════ -->
                        <template v-else-if="blockType === 'customer-form-lines'">
                            <div class="field-group">
                                <label class="field-label">Box Style</label>
                                <div class="flex gap-2">
                                    <button v-for="opt in [{ v: 'lines', l: 'Open Lines' }, { v: 'bordered-box', l: 'Bordered Box' }]"
                                        :key="opt.v" @click="localConfig.style = opt.v"
                                        :class="['flex-1 py-2 text-xs font-semibold rounded-xl border-2 transition', localConfig.style === opt.v ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-slate-200 text-slate-600 hover:border-slate-300']">
                                        {{ opt.l }}
                                    </button>
                                </div>
                            </div>
                            <div class="field-group">
                                <label class="field-label">Fields to Show</label>
                                <div class="space-y-2">
                                    <div v-for="f in [
                                        { key: 'showOrderNumber', label: 'Order Number' },
                                        { key: 'showDate', label: 'Date' },
                                        { key: 'showName', label: 'Customer Name' },
                                        { key: 'showAddress', label: 'Address' },
                                        { key: 'showCity', label: 'City / Zip' },
                                        { key: 'showPhone', label: 'Phone' },
                                        { key: 'showEmail', label: 'Email' },
                                        { key: 'showPO', label: 'PO #' },
                                        { key: 'showSoldBy', label: 'Sold By' },
                                        { key: 'showPaymentMethod', label: 'Payment Method (Cash / Card / Check)' },
                                    ]" :key="f.key"
                                        class="flex items-center justify-between px-3 py-2.5 rounded-xl bg-slate-50">
                                        <span class="text-sm text-slate-700">{{ f.label }}</span>
                                        <label class="toggle-switch"><input type="checkbox" v-model="localConfig[f.key]" class="sr-only peer"/><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- ══════════════ CUSTOMER SPLIT ══════════════ -->
                        <template v-else-if="blockType === 'customer-split'">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between px-3 py-2.5 rounded-xl bg-slate-50">
                                    <span class="text-sm text-slate-700">Show Bill To (left)</span>
                                    <label class="toggle-switch"><input type="checkbox" v-model="localConfig.showBillTo" class="sr-only peer"/><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
                                </div>
                                <div class="flex items-center justify-between px-3 py-2.5 rounded-xl bg-slate-50">
                                    <span class="text-sm text-slate-700">Show Company Info (right)</span>
                                    <label class="toggle-switch"><input type="checkbox" v-model="localConfig.showCompanyRight" class="sr-only peer"/><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
                                </div>
                            </div>
                        </template>

                        <!-- ══════════════ ITEMS TABLE (MODERN) ══════════════ -->
                        <template v-else-if="blockType === 'items-table'">
                            <div class="field-group">
                                <label class="field-label">Table Style</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <button v-for="opt in [{ v: 'striped', l: '🦓 Striped' }, { v: 'plain', l: '📋 Plain' }]"
                                        :key="opt.v" @click="localConfig.style = opt.v"
                                        :class="['py-2 text-xs font-semibold rounded-xl border-2 transition', localConfig.style === opt.v ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-slate-200 text-slate-600 hover:border-slate-300']">
                                        {{ opt.l }}
                                    </button>
                                </div>
                            </div>
                            <div class="flex items-center justify-between px-3 py-2.5 rounded-xl bg-slate-50">
                                <span class="text-sm text-slate-700">Show Row Borders</span>
                                <label class="toggle-switch"><input type="checkbox" v-model="localConfig.showBorders" class="sr-only peer"/><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
                            </div>
                            <div class="field-group">
                                <label class="field-label">Columns <span class="text-slate-400 font-normal">(min 2)</span></label>
                                <div class="flex flex-wrap gap-2">
                                    <button v-for="col in tableColumnOptions" :key="col.value"
                                        @click="toggleColumn(col.value)"
                                        :class="['px-3 py-1.5 text-xs font-semibold rounded-lg border-2 transition', hasColumn(col.value) ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-slate-200 text-slate-500 hover:border-slate-300']">
                                        {{ col.label }}
                                    </button>
                                </div>
                                <p class="text-xs text-slate-400 mt-1">Selected order: {{ (localConfig.columns || []).join(' → ') }}</p>
                            </div>
                        </template>

                        <!-- ══════════════ ITEMS TABLE CLASSIC ══════════════ -->
                        <template v-else-if="blockType === 'items-table-classic'">
                            <div class="field-group">
                                <label class="field-label">Columns</label>
                                <div class="flex flex-wrap gap-2">
                                    <button v-for="col in [{ value: 'qty', label: 'Qty' }, { value: 'description', label: 'Description' }, { value: 'price', label: 'Price' }, { value: 'amount', label: 'Amount' }, { value: 'total', label: 'Total' }]"
                                        :key="col.value" @click="toggleColumn(col.value)"
                                        :class="['px-3 py-1.5 text-xs font-semibold rounded-lg border-2 transition', hasColumn(col.value) ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-slate-200 text-slate-500 hover:border-slate-300']">
                                        {{ col.label }}
                                    </button>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between px-3 py-2.5 rounded-xl bg-slate-50">
                                    <span class="text-sm text-slate-700">Show Sales Tax Row</span>
                                    <label class="toggle-switch"><input type="checkbox" v-model="localConfig.showSalesTax" class="sr-only peer"/><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
                                </div>
                                <div class="flex items-center justify-between px-3 py-2.5 rounded-xl bg-slate-50">
                                    <span class="text-sm text-slate-700">Show Total Row</span>
                                    <label class="toggle-switch"><input type="checkbox" v-model="localConfig.showTotal" class="sr-only peer"/><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
                                </div>
                            </div>
                        </template>

                        <!-- ══════════════ ITEMS TABLE MINIMAL ══════════════ -->
                        <template v-else-if="blockType === 'items-table-minimal'">
                            <div class="field-group">
                                <label class="field-label">Columns</label>
                                <div class="flex flex-wrap gap-2">
                                    <button v-for="col in [{ value: 'description', label: 'Description' }, { value: 'qty', label: 'Qty' }, { value: 'price', label: 'Price' }, { value: 'total', label: 'Total' }]"
                                        :key="col.value" @click="toggleColumn(col.value)"
                                        :class="['px-3 py-1.5 text-xs font-semibold rounded-lg border-2 transition', hasColumn(col.value) ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-slate-200 text-slate-500 hover:border-slate-300']">
                                        {{ col.label }}
                                    </button>
                                </div>
                            </div>
                        </template>

                        <!-- ══════════════ TOTALS ══════════════ -->
                        <template v-else-if="blockType === 'totals'">
                            <div class="field-group">
                                <label class="field-label">Alignment</label>
                                <div class="flex gap-2">
                                    <button v-for="opt in [{ v: 'left', l: 'Left' }, { v: 'center', l: 'Center' }, { v: 'right', l: 'Right' }]"
                                        :key="opt.v" @click="localConfig.alignment = opt.v"
                                        :class="['flex-1 py-2 text-xs font-semibold rounded-xl border-2 transition', localConfig.alignment === opt.v ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-slate-200 text-slate-600 hover:border-slate-300']">
                                        {{ opt.l }}
                                    </button>
                                </div>
                            </div>
                            <div class="field-group">
                                <label class="field-label">Rows to Display</label>
                                <div class="space-y-2">
                                    <div v-for="f in [
                                        { key: 'showSubtotal', label: 'Subtotal' },
                                        { key: 'showTax', label: 'Tax' },
                                        { key: 'showDiscount', label: 'Discount' },
                                    ]" :key="f.key"
                                        class="flex items-center justify-between px-3 py-2.5 rounded-xl bg-slate-50">
                                        <span class="text-sm text-slate-700">{{ f.label }}</span>
                                        <label class="toggle-switch"><input type="checkbox" v-model="localConfig[f.key]" class="sr-only peer"/><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- ══════════════ TOTALS CLASSIC ══════════════ -->
                        <template v-else-if="blockType === 'totals-classic'">
                            <div class="flex items-center justify-between px-3 py-2.5 rounded-xl bg-slate-50">
                                <span class="text-sm text-slate-700">Show Sales Tax Row</span>
                                <label class="toggle-switch"><input type="checkbox" v-model="localConfig.showSalesTax" class="sr-only peer"/><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
                            </div>
                        </template>

                        <!-- ══════════════ PAYMENT DETAILS ══════════════ -->
                        <template v-else-if="blockType === 'payment-details'">
                            <div class="field-group">
                                <label class="field-label">Section Label</label>
                                <input type="text" v-model="localConfig.label" placeholder="PAYMENT DETAILS" class="field-input" />
                            </div>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between px-3 py-2.5 rounded-xl bg-slate-50">
                                    <span class="text-sm text-slate-700">Show Bank Details</span>
                                    <label class="toggle-switch"><input type="checkbox" v-model="localConfig.showBank" class="sr-only peer"/><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
                                </div>
                                <div class="flex items-center justify-between px-3 py-2.5 rounded-xl bg-slate-50">
                                    <span class="text-sm text-slate-700">Show PayPal</span>
                                    <label class="toggle-switch"><input type="checkbox" v-model="localConfig.showPaypal" class="sr-only peer"/><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
                                </div>
                            </div>
                        </template>

                        <!-- ══════════════ NOTES & TERMS ══════════════ -->
                        <template v-else-if="blockType === 'notes-terms'">
                            <div class="field-group">
                                <label class="field-label">Section Label</label>
                                <input type="text" v-model="localConfig.label" placeholder="NOTES & TERMS" class="field-input" />
                            </div>
                            <div class="field-group">
                                <label class="field-label">Placeholder Text</label>
                                <textarea v-model="localConfig.placeholder" rows="3" placeholder="Enter notes or terms here..." class="field-input resize-none"></textarea>
                            </div>
                            <div class="field-group">
                                <label class="field-label">Style</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <button v-for="opt in [{ v: 'bar', l: '🏷️ Colored Bar' }, { v: 'plain', l: '📋 Plain' }]"
                                        :key="opt.v" @click="localConfig.style = opt.v"
                                        :class="['py-2.5 text-xs font-semibold rounded-xl border-2 transition', localConfig.style === opt.v ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-slate-200 text-slate-600 hover:border-slate-300']">
                                        {{ opt.l }}
                                    </button>
                                </div>
                            </div>
                        </template>

                        <!-- ══════════════ SIGNATURE LINES ══════════════ -->
                        <template v-else-if="blockType === 'signature-lines'">
                            <div class="field-group">
                                <label class="field-label">Lines to Include</label>
                                <div class="space-y-2">
                                    <div v-for="f in [
                                        { key: 'showReceivedBy', label: 'Received By' },
                                        { key: 'showSignature', label: 'Signature' },
                                        { key: 'showPrintName', label: 'Print Name' },
                                    ]" :key="f.key"
                                        class="flex items-center justify-between px-3 py-2.5 rounded-xl bg-slate-50">
                                        <span class="text-sm text-slate-700">{{ f.label }}</span>
                                        <label class="toggle-switch"><input type="checkbox" v-model="localConfig[f.key]" class="sr-only peer"/><div class="toggle-track"></div><div class="toggle-thumb"></div></label>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- ══════════════ THANK YOU FOOTER ══════════════ -->
                        <template v-else-if="blockType === 'thank-you-footer'">
                            <div class="field-group">
                                <label class="field-label">Footer Message</label>
                                <input type="text" v-model="localConfig.message" placeholder="THANK YOU FOR YOUR BUSINESS" class="field-input" />
                            </div>
                        </template>

                        <!-- ══════════════ TEXT BLOCK ══════════════ -->
                        <template v-else-if="blockType === 'text'">
                            <div class="field-group">
                                <label class="field-label">Content</label>
                                <textarea v-model="localConfig.text" rows="5" placeholder="Enter your text here..." class="field-input resize-none"></textarea>
                            </div>
                            <div class="field-group">
                                <label class="field-label">Text Alignment</label>
                                <div class="flex gap-2">
                                    <button v-for="opt in [{ v: 'left', l: '⬅ Left' }, { v: 'center', l: '↔ Center' }, { v: 'right', l: 'Right ➡' }]"
                                        :key="opt.v" @click="localConfig.alignment = opt.v"
                                        :class="['flex-1 py-2 text-xs font-semibold rounded-xl border-2 transition', localConfig.alignment === opt.v ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-slate-200 text-slate-600 hover:border-slate-300']">
                                        {{ opt.l }}
                                    </button>
                                </div>
                            </div>
                            <div class="field-group">
                                <label class="field-label">Font Size</label>
                                <div class="flex gap-2 flex-wrap">
                                    <button v-for="opt in [{ v: 'text-xs', l: 'XS' }, { v: 'text-sm', l: 'SM' }, { v: 'text-base', l: 'MD' }, { v: 'text-lg', l: 'LG' }, { v: 'text-xl', l: 'XL' }]"
                                        :key="opt.v" @click="localConfig.fontSize = opt.v"
                                        :class="['flex-1 py-2 text-xs font-semibold rounded-xl border-2 transition', localConfig.fontSize === opt.v ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-slate-200 text-slate-600 hover:border-slate-300']">
                                        {{ opt.l }}
                                    </button>
                                </div>
                            </div>
                        </template>

                        <!-- ══════════════ DIVIDER ══════════════ -->
                        <template v-else-if="blockType === 'divider'">
                            <div class="field-group">
                                <label class="field-label">Line Style</label>
                                <div class="flex gap-2">
                                    <button v-for="opt in [{ v: 'solid', l: '—— Solid' }, { v: 'dashed', l: '- - Dashed' }, { v: 'dotted', l: '··· Dotted' }]"
                                        :key="opt.v" @click="localConfig.style = opt.v"
                                        :class="['flex-1 py-2 text-xs font-semibold rounded-xl border-2 transition', localConfig.style === opt.v ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-slate-200 text-slate-600 hover:border-slate-300']">
                                        {{ opt.l }}
                                    </button>
                                </div>
                            </div>
                            <div class="field-group">
                                <label class="field-label">Color</label>
                                <div class="flex items-center gap-3">
                                    <input type="color" v-model="localConfig.color" class="w-10 h-10 rounded-xl border-2 border-slate-200 cursor-pointer shrink-0 p-0.5" />
                                    <input type="text" v-model="localConfig.color" class="field-input" placeholder="#e2e8f0" />
                                </div>
                            </div>
                            <div class="field-group">
                                <label class="field-label">Thickness</label>
                                <div class="flex gap-2">
                                    <button v-for="opt in [{ v: '1px', l: 'Thin' }, { v: '2px', l: 'Medium' }, { v: '4px', l: 'Thick' }]"
                                        :key="opt.v" @click="localConfig.thickness = opt.v"
                                        :class="['flex-1 py-2 text-xs font-semibold rounded-xl border-2 transition', localConfig.thickness === opt.v ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-slate-200 text-slate-600 hover:border-slate-300']">
                                        {{ opt.l }}
                                    </button>
                                </div>
                            </div>
                        </template>

                        <!-- ══════════════ FALLBACK ══════════════ -->
                        <template v-else>
                            <div class="text-center py-8 text-slate-400">
                                <p class="text-sm">No configuration options for this block type.</p>
                            </div>
                        </template>

                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 border-t border-slate-100 flex gap-3 shrink-0 bg-slate-50/50">
                        <button @click="handleClose"
                            class="flex-1 px-4 py-2.5 border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-white transition">
                            Cancel
                        </button>
                        <button @click="handleSave"
                            class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 transition shadow-sm shadow-indigo-200">
                            <CheckIcon class="h-4 w-4" />Apply Changes
                        </button>
                    </div>
                </div>
            </Transition>
        </div>
    </Transition>
</template>

<style scoped>
.field-group { @apply space-y-2; }
.field-label { @apply block text-xs font-bold text-slate-500 uppercase tracking-wider; }
.field-input {
    @apply w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-800
    focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
    placeholder-slate-300 transition;
}

/* Toggle switch */
.toggle-switch { @apply relative cursor-pointer; }
.toggle-track {
    @apply w-10 h-5 bg-slate-200 rounded-full transition-colors duration-200;
}
.toggle-thumb {
    @apply absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow-sm
    transition-transform duration-200;
}
input.sr-only:checked ~ .toggle-track { @apply bg-indigo-500; }
input.sr-only:checked ~ .toggle-thumb { @apply translate-x-5; }
</style>