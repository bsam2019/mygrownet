<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import {
    DocumentTextIcon,
    CreditCardIcon,
    ShoppingCartIcon,
    CurrencyDollarIcon,
    ArchiveBoxIcon,
    ChartBarIcon,
    ClockIcon,
    ArrowTrendingUpIcon,
} from '@heroicons/vue/24/outline';
import { useStockflowRoute } from '@/composables/useStockflowRoute';

const { route } = useStockflowRoute();

interface ReportCard {
    title: string;
    description: string;
    icon: any;
    href: string;
    color: string;
}

const reports: ReportCard[] = [
    { title: 'Sales Report', description: 'View and export sales within a date range, with payment method breakdown', icon: CreditCardIcon, href: route('stockflow.sub.sales.report'), color: 'emerald' },
    { title: 'Purchase Orders Report', description: 'Purchase order history with supplier and status breakdown', icon: ShoppingCartIcon, href: route('stockflow.sub.purchases.report'), color: 'blue' },
    { title: 'Cash Summary', description: 'Cash register activity including sales, expenses, banking, and variance', icon: CurrencyDollarIcon, href: route('stockflow.sub.cash.summary'), color: 'amber' },
    { title: 'Inventory Report', description: 'Current stock levels, total value, low stock and out-of-stock items', icon: ArchiveBoxIcon, href: route('stockflow.sub.inventory.report'), color: 'indigo' },
    { title: 'Audit Reports', description: 'View completed audit reports and export to PDF or CSV', icon: DocumentTextIcon, href: route('stockflow.sub.audits.index'), color: 'purple' },
    { title: 'ABC Analysis', description: 'Classify inventory by value (A=80%, B=15%, C=5%)', icon: ChartBarIcon, href: route('stockflow.sub.reports.abc'), color: 'red' },
    { title: 'Stock Aging', description: 'Items grouped by expiry date buckets', icon: ClockIcon, href: route('stockflow.sub.reports.aging'), color: 'amber' },
    { title: 'Inventory Turnover', description: 'Fast and slow moving items by turnover rate', icon: ArrowTrendingUpIcon, href: route('stockflow.sub.reports.turnover'), color: 'blue' },
];
</script>

<template>
    <StockFlowLayout title="Reports">
        <Head title="Reports - StockFlow" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-gray-900">Reports</h1>
                    <p class="mt-1 text-sm text-gray-500">Generate and download business reports</p>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="report in reports"
                        :key="report.title"
                        :href="report.href"
                        class="group rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 transition-all hover:shadow-md hover:-translate-y-0.5"
                    >
                        <div
                            class="mb-4 inline-flex rounded-xl p-3 ring-1 ring-inset"
                            :class="{
                                'bg-emerald-50 text-emerald-600 ring-emerald-200': report.color === 'emerald',
                                'bg-blue-50 text-blue-600 ring-blue-200': report.color === 'blue',
                                'bg-amber-50 text-amber-600 ring-amber-200': report.color === 'amber',
                                'bg-indigo-50 text-indigo-600 ring-indigo-200': report.color === 'indigo',
                                'bg-purple-50 text-purple-600 ring-purple-200': report.color === 'purple',
                                'bg-red-50 text-red-600 ring-red-200': report.color === 'red',
                            }"
                        >
                            <component :is="report.icon" class="h-6 w-6" />
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 group-hover:text-emerald-600">{{ report.title }}</h3>
                        <p class="mt-2 text-sm text-gray-500">{{ report.description }}</p>
                    </Link>
                </div>
            </div>
        </div>
    </StockFlowLayout>
</template>
