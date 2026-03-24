<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    DocumentTextIcon,
    PlusIcon,
    MagnifyingGlassIcon,
    EyeIcon,
    ChevronDownIcon,
    ArrowLeftIcon,
} from '@heroicons/vue/24/outline';
import { ref, computed } from 'vue';

interface Document {
    id: number;
    documentNumber: string;
    documentType: string;
    issueDate: string;
    dueDate?: string;
    grandTotal: number;
    currency: string;
    status: string;
    customerName: string;
}

interface Props {
    documents: Document[];
    totalCount: number;
    currentType?: string;
    businessProfile: any;
}

const props = defineProps<Props>();

const searchQuery  = ref('');
const selectedType = ref(props.currentType || 'all');

const documentTypes = [
    { value: 'all',              label: 'All Documents'     },
    { value: 'invoice',          label: 'Invoices'          },
    { value: 'receipt',          label: 'Receipts'          },
    { value: 'quotation',        label: 'Quotations'        },
    { value: 'delivery_note',    label: 'Delivery Notes'    },
    { value: 'proforma_invoice', label: 'Proforma Invoices' },
    { value: 'credit_note',      label: 'Credit Notes'      },
    { value: 'debit_note',       label: 'Debit Notes'       },
    { value: 'purchase_order',   label: 'Purchase Orders'   },
];

// Status badge config: [bg, text dot colour, label colour]
const statusConfig: Record<string, { dot: string; bg: string; text: string }> = {
    draft:        { dot: 'bg-slate-400',  bg: 'bg-slate-100',  text: 'text-slate-600'  },
    sent:         { dot: 'bg-blue-500',   bg: 'bg-blue-50',    text: 'text-blue-700'   },
    paid:         { dot: 'bg-emerald-500',bg: 'bg-emerald-50', text: 'text-emerald-700'},
    overdue:      { dot: 'bg-red-500',    bg: 'bg-red-50',     text: 'text-red-700'    },
    cancelled:    { dot: 'bg-slate-400',  bg: 'bg-slate-100',  text: 'text-slate-500'  },
    issued:       { dot: 'bg-emerald-500',bg: 'bg-emerald-50', text: 'text-emerald-700'},
    voided:       { dot: 'bg-red-500',    bg: 'bg-red-50',     text: 'text-red-700'    },
    accepted:     { dot: 'bg-emerald-500',bg: 'bg-emerald-50', text: 'text-emerald-700'},
    rejected:     { dot: 'bg-red-500',    bg: 'bg-red-50',     text: 'text-red-700'    },
    expired:      { dot: 'bg-amber-500',  bg: 'bg-amber-50',   text: 'text-amber-700'  },
    delivered:    { dot: 'bg-blue-500',   bg: 'bg-blue-50',    text: 'text-blue-700'   },
    acknowledged: { dot: 'bg-emerald-500',bg: 'bg-emerald-50', text: 'text-emerald-700'},
    partially_paid:{ dot: 'bg-amber-500', bg: 'bg-amber-50',   text: 'text-amber-700'  },
};

const typeLabels: Record<string, string> = {
    invoice:          'Invoice',
    receipt:          'Receipt',
    quotation:        'Quotation',
    delivery_note:    'Delivery Note',
    proforma_invoice: 'Proforma Invoice',
    credit_note:      'Credit Note',
    debit_note:       'Debit Note',
    purchase_order:   'Purchase Order',
};

// Type pill colours
const typePillConfig: Record<string, { bg: string; text: string }> = {
    invoice:          { bg: 'bg-blue-50',    text: 'text-blue-700'    },
    receipt:          { bg: 'bg-emerald-50', text: 'text-emerald-700' },
    quotation:        { bg: 'bg-violet-50',  text: 'text-violet-700'  },
    delivery_note:    { bg: 'bg-amber-50',   text: 'text-amber-700'   },
    proforma_invoice: { bg: 'bg-indigo-50',  text: 'text-indigo-700'  },
    credit_note:      { bg: 'bg-green-50',   text: 'text-green-700'   },
    debit_note:       { bg: 'bg-red-50',     text: 'text-red-700'     },
    purchase_order:   { bg: 'bg-purple-50',  text: 'text-purple-700'  },
};

const filteredDocuments = computed(() =>
    props.documents.filter(doc => {
        const q = searchQuery.value.toLowerCase();
        return (
            (doc?.documentNumber?.toLowerCase() || '').includes(q) ||
            (doc?.customerName?.toLowerCase() || '').includes(q)
        );
    })
);

const formatDate = (date: string) =>
    new Date(date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });

const formatAmount = (amount: number, currency: string) =>
    `${currency} ${amount.toFixed(2)}`;

const statusFor = (s: string | undefined) =>
    s ? (statusConfig[s.toLowerCase()] ?? { dot: 'bg-slate-400', bg: 'bg-slate-100', text: 'text-slate-600' })
      : { dot: 'bg-slate-400', bg: 'bg-slate-100', text: 'text-slate-600' };

const typeFor = (t: string) =>
    typePillConfig[t] ?? { bg: 'bg-slate-100', text: 'text-slate-600' };

const onTypeChange = () => {
    const url = selectedType.value !== 'all'
        ? `/bizdocs/documents?type=${selectedType.value}`
        : '/bizdocs/documents';
    (window as any).Inertia?.visit(url);
};

// Quick stats from the full document list
const stats = computed(() => ({
    total:   props.documents.length,
    paid:    props.documents.filter(d => d?.status === 'paid').length,
    overdue: props.documents.filter(d => d?.status === 'overdue').length,
    draft:   props.documents.filter(d => d?.status === 'draft').length,
}));
</script>

<template>
    <Head title="Documents" />

    <AppLayout>
        <div class="min-h-screen bg-slate-50 py-8 px-4">
            <div class="max-w-7xl mx-auto">

                <!-- Page header -->
                <div class="flex items-start justify-between mb-7">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <Link
                                href="/bizdocs/dashboard"
                                class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 hover:text-blue-600 transition-colors group">
                                <ArrowLeftIcon class="w-3.5 h-3.5 group-hover:-translate-x-0.5 transition-transform" />
                                Back to Dashboard
                            </Link>
                            <span class="text-slate-300">·</span>
                            <span class="inline-flex items-center gap-2 text-xs font-bold tracking-widest text-blue-600 uppercase">
                                <span class="w-5 h-px bg-blue-500 inline-block"></span>
                                BizDocs
                            </span>
                        </div>
                        <h1 class="text-2xl font-bold text-slate-900 leading-none">Documents</h1>
                        <p class="text-sm text-slate-400 mt-1">{{ totalCount }} total records</p>
                    </div>

                    <Link
                        href="/bizdocs/documents/create?type=invoice"
                        class="group inline-flex items-center gap-2 px-4 py-2.5 bg-slate-900 text-white text-sm font-semibold rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200">
                        <PlusIcon class="w-4 h-4" />
                        New Document
                    </Link>
                </div>

                <!-- Quick-stat strip -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
                    <div class="bg-white border border-slate-200 rounded-xl px-4 py-3 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center flex-shrink-0">
                            <DocumentTextIcon class="w-4 h-4 text-slate-500" />
                        </div>
                        <div>
                            <p class="text-lg font-bold text-slate-900 leading-none">{{ stats.total }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">All Documents</p>
                        </div>
                    </div>
                    <div class="bg-white border border-slate-200 rounded-xl px-4 py-3 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center flex-shrink-0">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span>
                        </div>
                        <div>
                            <p class="text-lg font-bold text-slate-900 leading-none">{{ stats.paid }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">Paid</p>
                        </div>
                    </div>
                    <div class="bg-white border border-slate-200 rounded-xl px-4 py-3 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center flex-shrink-0">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block"></span>
                        </div>
                        <div>
                            <p class="text-lg font-bold text-slate-900 leading-none">{{ stats.overdue }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">Overdue</p>
                        </div>
                    </div>
                    <div class="bg-white border border-slate-200 rounded-xl px-4 py-3 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center flex-shrink-0">
                            <span class="w-2.5 h-2.5 rounded-full bg-slate-400 inline-block"></span>
                        </div>
                        <div>
                            <p class="text-lg font-bold text-slate-900 leading-none">{{ stats.draft }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">Drafts</p>
                        </div>
                    </div>
                </div>

                <!-- Search + filter bar -->
                <div class="flex flex-col sm:flex-row gap-3 mb-4">
                    <!-- Search -->
                    <div class="relative flex-1">
                        <MagnifyingGlassIcon class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search by document number or customer…"
                            class="w-full pl-10 pr-4 py-2.5 text-sm text-slate-800 bg-white border border-slate-200 rounded-lg placeholder-slate-300 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 outline-none transition-all duration-150 shadow-sm" />
                    </div>

                    <!-- Type filter -->
                    <div class="relative">
                        <select
                            v-model="selectedType"
                            @change="onTypeChange"
                            class="appearance-none pl-4 pr-9 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 outline-none shadow-sm transition-all duration-150 cursor-pointer">
                            <option v-for="t in documentTypes" :key="t.value" :value="t.value">
                                {{ t.label }}
                            </option>
                        </select>
                        <ChevronDownIcon class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                    </div>
                </div>

                <!-- Documents table -->
                <div v-if="filteredDocuments.length > 0"
                    class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <!-- Head -->
                            <thead>
                                <tr class="border-b border-slate-100">
                                    <th class="px-5 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">
                                        Document #
                                    </th>
                                    <th class="px-5 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">
                                        Type
                                    </th>
                                    <th class="px-5 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">
                                        Customer
                                    </th>
                                    <th class="px-5 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">
                                        Date
                                    </th>
                                    <th class="px-5 py-3 text-right text-xs font-bold text-slate-400 uppercase tracking-wider">
                                        Amount
                                    </th>
                                    <th class="px-5 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-5 py-3 text-right text-xs font-bold text-slate-400 uppercase tracking-wider">
                                        &nbsp;
                                    </th>
                                </tr>
                            </thead>

                            <!-- Body -->
                            <tbody class="divide-y divide-slate-100">
                                <tr
                                    v-for="doc in filteredDocuments"
                                    :key="doc.id"
                                    class="group hover:bg-slate-50 transition-colors duration-100">

                                    <!-- Document # -->
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <span class="text-sm font-semibold text-slate-800 font-mono tracking-tight">
                                            {{ doc.documentNumber }}
                                        </span>
                                    </td>

                                    <!-- Type pill -->
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold"
                                            :class="[typeFor(doc.documentType).bg, typeFor(doc.documentType).text]">
                                            {{ typeLabels[doc.documentType] || doc.documentType }}
                                        </span>
                                    </td>

                                    <!-- Customer -->
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <span class="text-sm text-slate-700">{{ doc.customerName }}</span>
                                    </td>

                                    <!-- Date -->
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <span class="text-sm text-slate-600">{{ formatDate(doc.issueDate) }}</span>
                                        <p v-if="doc.dueDate" class="text-xs text-slate-400 mt-0.5">
                                            Due {{ formatDate(doc.dueDate) }}
                                        </p>
                                    </td>

                                    <!-- Amount -->
                                    <td class="px-5 py-3.5 whitespace-nowrap text-right">
                                        <span class="text-sm font-semibold text-slate-800 tabular-nums">
                                            {{ formatAmount(doc.grandTotal, doc.currency) }}
                                        </span>
                                    </td>

                                    <!-- Status badge -->
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium capitalize"
                                            :class="[statusFor(doc.status).bg, statusFor(doc.status).text]">
                                            <span class="w-1.5 h-1.5 rounded-full flex-shrink-0"
                                                :class="statusFor(doc.status).dot"></span>
                                            {{ doc.status?.replace('_', ' ') || 'Unknown' }}
                                        </span>
                                    </td>

                                    <!-- View action -->
                                    <td class="px-5 py-3.5 whitespace-nowrap text-right">
                                        <Link
                                            :href="`/bizdocs/documents/${doc.id}`"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg border border-transparent hover:border-blue-100 transition-all duration-150 group-hover:border-slate-200">
                                            <EyeIcon class="w-3.5 h-3.5" />
                                            View
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-else
                    class="bg-white border border-slate-200 rounded-xl py-16 text-center">
                    <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <DocumentTextIcon class="w-6 h-6 text-slate-400" />
                    </div>
                    <h3 class="text-sm font-semibold text-slate-700 mb-1">
                        {{ searchQuery ? 'No results found' : 'No documents yet' }}
                    </h3>
                    <p class="text-xs text-slate-400 mb-5">
                        {{ searchQuery
                            ? 'Try a different document number or customer name'
                            : 'Create your first document to get started' }}
                    </p>
                    <Link
                        v-if="!searchQuery"
                        href="/bizdocs/documents/create?type=invoice"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-slate-900 text-white text-sm font-semibold rounded-lg hover:bg-blue-600 transition-all duration-200">
                        <PlusIcon class="w-4 h-4" />
                        Create First Document
                    </Link>
                </div>

            </div>
        </div>
    </AppLayout>
</template>