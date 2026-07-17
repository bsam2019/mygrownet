<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { ref } from 'vue';
import {
    BookOpenIcon,
    ShieldCheckIcon,
    CubeIcon,
    WrenchIcon,
    DocumentTextIcon,
    TrashIcon,
    ArrowUpTrayIcon,
    CurrencyDollarIcon,
    CreditCardIcon,
} from '@heroicons/vue/24/outline';

const activeSection = ref<string | null>(null);

const toggleSection = (id: string) => {
    activeSection.value = activeSection.value === id ? null : id;
};

const sections = [
    {
        id: 'extensions',
        title: 'Industry Extensions',
        icon: CubeIcon,
        articles: [
            {
                id: 'pharmacy',
                title: 'Pharmacy — Controlled Medicines',
                icon: ShieldCheckIcon,
                content: `The Pharmacy extension adds controlled medicine tracking. When enabled for your company, a "Controlled Medicines" section appears in the sidebar. You can record medicines by name, dosage, schedule (e.g. 2/2 for Second Schedule), quantity, and expiry date. This helps pharmacies comply with regulatory requirements for tracking scheduled drugs.`,
            },
            {
                id: 'manufacturing',
                title: 'Manufacturing — BOMs & Work Orders',
                icon: WrenchIcon,
                content: `The Manufacturing extension adds bill of materials (BOM) and work order management. Use BOMs to define how finished goods are made from raw materials (quantities and costs). Create work orders to schedule production — the system auto-calculates material requirements and issues stock from inventory when the work order starts. Track production costs and see profit margins on manufactured items.`,
            },
            {
                id: 'restaurant',
                title: 'Restaurant — Recipes & Wastage',
                icon: DocumentTextIcon,
                content: `The Restaurant extension adds recipe management and wastage tracking. Define recipes with ingredient quantities and costs — the system calculates total cost and profit margin per serving. Track menu costing snapshots over time. Log wastage records (spoilage, returns, expired items) with quantities, reasons, and cost impact. Helps restaurants control food costs and reduce waste.`,
            },
        ],
    },
    {
        id: 'import-export',
        title: 'CSV Import & Templates',
        icon: ArrowUpTrayIcon,
        articles: [
            {
                id: 'csv-templates',
                title: 'Downloadable Templates',
                icon: DocumentTextIcon,
                content: `Each list page (Items, Suppliers, Bins) has a "Template" button that downloads a CSV file with the correct column headers. Use these templates to prepare your data in Excel or Google Sheets. Column headers match exactly what the system expects.`,
            },
            {
                id: 'csv-import',
                title: 'Bulk Import via CSV',
                icon: ArrowUpTrayIcon,
                content: `On the Items, Suppliers, and Bins pages, click "Import CSV" and select your prepared file. The system reads the CSV, validates the data, and inserts records in bulk. For Items, the import automatically resolves bin names to bin IDs. For Bins, it resolves department names. Any errors are reported per row so you can fix and retry.`,
            },
        ],
    },
    {
        id: 'pricing',
        title: 'Pricing & Trial Periods',
        icon: CurrencyDollarIcon,
        articles: [
            {
                id: 'dual-currency',
                title: 'Dual-Currency Pricing (USD / ZMW)',
                icon: CurrencyDollarIcon,
                content: `Each extension has pricing in both USD and Zambian Kwacha (ZMW). The admin sets both prices. When a company subscribes, the system knows which currency applies based on the company's country. USD pricing applies internationally; ZMW pricing applies in Zambia.`,
            },
            {
                id: 'trial',
                title: 'Free Trial Period',
                icon: CreditCardIcon,
                content: `When an admin assigns an extension to a company, if the extension has trial days configured (default: 14), the company's status is set to "trial". Trial users get full access to all features until the trial expires. The admin can see trial status and expiry dates in the Extensions admin page. After trial ends, the company needs an active subscription to continue using the extension features.`,
            },
        ],
    },
];
</script>

<template>
    <StockFlowLayout title="Help &amp; Documentation">
        <Head title="Help — StockFlow" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="mb-8">
                    <div class="flex items-center gap-3 mb-2">
                        <BookOpenIcon class="h-8 w-8 text-emerald-600" />
                        <h1 class="text-2xl font-bold text-gray-900">Help &amp; Documentation</h1>
                    </div>
                    <p class="text-gray-500">Learn about StockFlow features, extensions, and how to use them.</p>
                </div>

                <div v-for="section in sections" :key="section.id" class="mb-6">
                    <button @click="toggleSection(section.id)"
                        class="w-full flex items-center justify-between rounded-xl bg-white px-6 py-4 shadow-sm ring-1 ring-gray-900/5 hover:ring-emerald-500/50 transition-all">
                        <div class="flex items-center gap-3">
                            <component :is="section.icon" class="h-6 w-6 text-emerald-600" />
                            <h2 class="text-lg font-semibold text-gray-900">{{ section.title }}</h2>
                        </div>
                        <svg :class="['h-5 w-5 text-gray-400 transition-transform', activeSection === section.id ? 'rotate-180' : '']" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div v-if="activeSection === section.id" class="mt-3 space-y-3">
                        <div v-for="article in section.articles" :key="article.id"
                            class="rounded-xl bg-white px-6 py-5 shadow-sm ring-1 ring-gray-900/5">
                            <div class="flex items-center gap-2 mb-3">
                                <component :is="article.icon" class="h-5 w-5 text-emerald-500" />
                                <h3 class="font-semibold text-gray-900">{{ article.title }}</h3>
                            </div>
                            <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-line">{{ article.content }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl bg-emerald-50 px-6 py-5 text-center">
                    <p class="text-sm text-emerald-700">
                        Need more help? Contact support at
                        <a href="mailto:support@mygrownet.com" class="font-medium underline">support@mygrownet.com</a>
                    </p>
                </div>
            </div>
        </div>
    </StockFlowLayout>
</template>
