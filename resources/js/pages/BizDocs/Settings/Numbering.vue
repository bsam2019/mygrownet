<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ArrowLeftIcon, Cog6ToothIcon } from '@heroicons/vue/24/outline';

interface BusinessProfile {
    id: number;
    businessName: string;
}

interface Sequence {
    prefix: string;
    padding: number;
    last_number: number;
    next_number: number;
    include_year?: boolean;
}

interface Props {
    businessProfile: BusinessProfile;
    sequences: Record<string, Sequence>;
    currentYear: number;
}

const props = defineProps<Props>();

const documentTypes = [
    { value: 'invoice',         label: 'Invoice',         defaultPrefix: 'INV'  },
    { value: 'receipt',         label: 'Receipt',         defaultPrefix: 'RCPT' },
    { value: 'quotation',       label: 'Quotation',       defaultPrefix: 'QTN'  },
    { value: 'delivery_note',   label: 'Delivery Note',   defaultPrefix: 'DN'   },
    { value: 'proforma_invoice',label: 'Proforma Invoice',defaultPrefix: 'PRO'  },
    { value: 'credit_note',     label: 'Credit Note',     defaultPrefix: 'CN'   },
    { value: 'debit_note',      label: 'Debit Note',      defaultPrefix: 'DN'   },
    { value: 'purchase_order',  label: 'Purchase Order',  defaultPrefix: 'PO'   },
];

// Type badge colours — matches Documents index
const typeBadge: Record<string, { bg: string; text: string }> = {
    invoice:          { bg: 'bg-blue-50',    text: 'text-blue-700'    },
    receipt:          { bg: 'bg-emerald-50', text: 'text-emerald-700' },
    quotation:        { bg: 'bg-violet-50',  text: 'text-violet-700'  },
    delivery_note:    { bg: 'bg-amber-50',   text: 'text-amber-700'   },
    proforma_invoice: { bg: 'bg-sky-50',     text: 'text-sky-700'     },
    credit_note:      { bg: 'bg-pink-50',    text: 'text-pink-700'    },
    debit_note:       { bg: 'bg-orange-50',  text: 'text-orange-700'  },
    purchase_order:   { bg: 'bg-teal-50',    text: 'text-teal-700'    },
};

const forms: Record<string, ReturnType<typeof useForm>> = {};

documentTypes.forEach(type => {
    const seq = props.sequences[type.value];
    forms[type.value] = useForm({
        document_type:  type.value,
        prefix:         seq?.prefix         ?? type.defaultPrefix,
        padding:        seq?.padding        ?? 4,
        include_year:   seq?.include_year   ?? true,
        reset_number:   null as number | null,
    });
});

const previewNumber = (type: string) => {
    const form   = forms[type];
    const seq    = props.sequences[type];
    const number = form.reset_number !== null ? (form.reset_number as number) + 1 : seq.next_number;
    const padded = String(number).padStart(form.padding as number, '0');
    return form.include_year
        ? `${form.prefix}-${props.currentYear}-${padded}`
        : `${form.prefix}${padded}`;
};

const updateSettings = (type: string) => {
    forms[type].post('/bizdocs/settings/numbering', {
        preserveScroll: true,
        onSuccess: () => forms[type].reset('reset_number'),
    });
};
</script>

<template>
    <Head title="Document Numbering Settings" />

    <AppLayout>
        <div class="min-h-screen bg-slate-50 py-8 px-4">
            <div class="max-w-6xl mx-auto">

                <!-- Back link -->
                <Link
                    href="/bizdocs/dashboard"
                    class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 hover:text-slate-800 mb-5 transition-colors">
                    <ArrowLeftIcon class="w-3.5 h-3.5" />
                    Back to Dashboard
                </Link>

                <!-- Page header -->
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="inline-flex items-center gap-2 text-xs font-bold tracking-widest text-blue-600 uppercase">
                                <span class="w-5 h-px bg-blue-500 inline-block"></span>
                                BizDocs · Settings
                            </span>
                        </div>
                        <h1 class="text-2xl font-bold text-slate-900 leading-none">Document Numbering</h1>
                        <p class="text-sm text-slate-400 mt-1">Configure the numbering format for each document type</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center flex-shrink-0">
                        <Cog6ToothIcon class="w-5 h-5 text-slate-500" />
                    </div>
                </div>

                <!-- Info panel -->
                <div class="bg-white border border-slate-200 rounded-xl overflow-hidden mb-6">
                    <div class="flex items-center gap-3 px-5 py-3.5 border-b border-slate-100">
                        <span class="text-xs font-bold tracking-widest text-blue-500 uppercase">How it works</span>
                        <span class="flex-1 h-px bg-slate-100"></span>
                    </div>
                    <div class="px-5 py-4 grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs text-slate-600">
                        <div>
                            <p class="font-semibold text-slate-700 mb-1">With Year</p>
                            <code class="inline-block bg-slate-100 text-slate-700 font-mono px-2 py-1 rounded text-xs mb-1.5">INV-2026-0001</code>
                            <p class="text-slate-400 leading-relaxed">Prefix + Year + Padded number. Resets to 1 each January.</p>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-700 mb-1">Without Year</p>
                            <code class="inline-block bg-slate-100 text-slate-700 font-mono px-2 py-1 rounded text-xs mb-1.5">INV0070</code>
                            <p class="text-slate-400 leading-relaxed">Prefix + Padded number only. Useful for sequential continuity.</p>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-700 mb-1">Starting Number</p>
                            <code class="inline-block bg-slate-100 text-slate-700 font-mono px-2 py-1 rounded text-xs mb-1.5">Starting at 69 → INV70</code>
                            <p class="text-slate-400 leading-relaxed">Set when migrating from an existing numbering system.</p>
                        </div>
                    </div>
                </div>

                <!-- Document type cards grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div
                        v-for="docType in documentTypes"
                        :key="docType.value"
                        class="bg-white border border-slate-200 rounded-xl overflow-hidden">

                        <!-- Card header -->
                        <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100">
                            <div class="flex items-center gap-2.5">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold"
                                    :class="[typeBadge[docType.value]?.bg, typeBadge[docType.value]?.text]">
                                    {{ docType.defaultPrefix }}
                                </span>
                                <h3 class="text-sm font-bold text-slate-800">{{ docType.label }}</h3>
                            </div>
                            <span class="text-xs text-slate-400">
                                {{ sequences[docType.value]?.last_number ?? 0 }} issued
                            </span>
                        </div>

                        <!-- Form body -->
                        <form @submit.prevent="updateSettings(docType.value)" class="px-5 py-4 space-y-4">

                            <div class="grid grid-cols-2 gap-3">

                                <!-- Prefix -->
                                <div>
                                    <label :for="`prefix-${docType.value}`" class="bizdocs-label">Prefix</label>
                                    <input
                                        :id="`prefix-${docType.value}`"
                                        v-model="forms[docType.value].prefix"
                                        type="text"
                                        maxlength="10"
                                        placeholder="INV"
                                        class="bizdocs-input font-mono" />
                                    <p class="mt-1 text-xs text-slate-400">Max 10 characters</p>
                                </div>

                                <!-- Padding -->
                                <div>
                                    <label :for="`padding-${docType.value}`" class="bizdocs-label">Padding</label>
                                    <select
                                        :id="`padding-${docType.value}`"
                                        v-model.number="forms[docType.value].padding"
                                        class="bizdocs-input bg-white">
                                        <option :value="1">1 digit  (1, 2…)</option>
                                        <option :value="2">2 digits (01, 02…)</option>
                                        <option :value="3">3 digits (001…)</option>
                                        <option :value="4">4 digits (0001…)</option>
                                        <option :value="5">5 digits (00001…)</option>
                                        <option :value="6">6 digits (000001…)</option>
                                    </select>
                                    <p class="mt-1 text-xs text-slate-400">Leading zeros</p>
                                </div>
                            </div>

                            <!-- Include Year toggle -->
                            <div class="flex items-start gap-3 px-3.5 py-3 bg-slate-50 border border-slate-200 rounded-lg">
                                <input
                                    :id="`year-${docType.value}`"
                                    type="checkbox"
                                    v-model="forms[docType.value].include_year"
                                    class="mt-0.5 w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500 cursor-pointer" />
                                <label :for="`year-${docType.value}`" class="cursor-pointer flex-1">
                                    <span class="text-xs font-semibold text-slate-700 block">Include Year</span>
                                    <span class="text-xs text-slate-400">
                                        {{ forms[docType.value].include_year
                                            ? `On: ${forms[docType.value].prefix}-${currentYear}-0001`
                                            : `Off: ${forms[docType.value].prefix}0001` }}
                                    </span>
                                </label>
                            </div>

                            <!-- Starting Number -->
                            <div>
                                <label :for="`reset-${docType.value}`" class="bizdocs-label">
                                    Starting Number
                                    <span class="ml-1 text-xs font-normal text-slate-400">(optional)</span>
                                </label>
                                <input
                                    :id="`reset-${docType.value}`"
                                    v-model.number="forms[docType.value].reset_number"
                                    type="number"
                                    min="0"
                                    class="bizdocs-input"
                                    :placeholder="`Continue from ${sequences[docType.value]?.next_number ?? 1}`" />
                                <!-- Backwards warning -->
                                <p
                                    v-if="forms[docType.value].reset_number !== null &&
                                          (forms[docType.value].reset_number as number) < (sequences[docType.value]?.last_number ?? 0)"
                                    class="mt-1.5 text-xs text-amber-600 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                    </svg>
                                    Counter will reset backwards. Next: {{ (forms[docType.value].reset_number as number) + 1 }}
                                </p>
                            </div>

                            <!-- Preview -->
                            <div class="flex items-center justify-between px-4 py-3 border border-slate-200 rounded-lg bg-slate-50">
                                <div>
                                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Next number preview</p>
                                    <p class="text-xl font-mono font-bold text-slate-900 tracking-tight">
                                        {{ previewNumber(docType.value) }}
                                    </p>
                                </div>
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                                    :class="typeBadge[docType.value]?.bg">
                                    <span class="text-xs font-bold" :class="typeBadge[docType.value]?.text">
                                        {{ forms[docType.value].prefix?.toString().slice(0, 2) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Save -->
                            <button
                                type="submit"
                                :disabled="forms[docType.value].processing"
                                class="group w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-900 text-white text-sm font-semibold rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">

                                <span v-if="!forms[docType.value].processing">Save Settings</span>
                                <span v-else class="flex items-center gap-2">
                                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                                    </svg>
                                    Saving…
                                </span>

                                <svg v-if="!forms[docType.value].processing"
                                    class="w-4 h-4 group-hover:translate-x-0.5 transition-transform"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                            </button>

                        </form>
                    </div>
                </div>

            </div>
        </div>
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
</style>