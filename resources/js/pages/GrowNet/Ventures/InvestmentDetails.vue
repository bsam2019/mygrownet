<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ArrowLeftIcon, CheckCircleIcon, ClockIcon, DocumentTextIcon, CurrencyDollarIcon } from '@heroicons/vue/24/outline';

interface Venture {
    id: number;
    title: string;
    slug: string;
    status: string;
    category: { name: string };
}

interface Shareholder {
    id: number;
    certificate_number: string;
    shares_owned: number;
    equity_percentage: number;
    status: string;
    total_dividends_received: number;
    dividends: Dividend[];
}

interface Dividend {
    id: number;
    dividend_period: string;
    amount: number;
    status: string;
    payment_date: string;
    paid_at: string;
    payment_reference: string;
}

interface VentureUpdate {
    id: number;
    title: string;
    content: string;
    type: string;
    published_at: string;
    is_pinned: boolean;
}

interface Investment {
    id: number;
    amount: number;
    shares_allocated: number;
    equity_percentage: number;
    status: string;
    payment_method: string;
    payment_reference: string;
    payment_confirmed_at: string;
    is_shareholder: boolean;
    shareholder_registered_at: string;
    shareholder_certificate_number: string;
    created_at: string;
    venture: Venture;
    shareholder?: Shareholder;
}

const props = defineProps<{
    investment: Investment;
}>();

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 0,
    }).format(amount);
};

const formatDate = (date: string | null) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        pending: 'bg-yellow-100 text-yellow-800',
        confirmed: 'bg-green-100 text-green-800',
        completed: 'bg-blue-100 text-blue-800',
        processing: 'bg-indigo-100 text-indigo-800',
        refunded: 'bg-red-100 text-red-800',
        cancelled: 'bg-gray-100 text-gray-800',
        declared: 'bg-yellow-100 text-yellow-800',
        paid: 'bg-green-100 text-green-800',
        failed: 'bg-red-100 text-red-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const getUpdateTypeIcon = (type: string) => {
    const icons: Record<string, string> = {
        milestone: '🏆',
        financial: '📊',
        operational: '⚙️',
        announcement: '📢',
        alert: '⚠️',
        general: '📝',
    };
    return icons[type] || '📝';
};
</script>

<template>
    <Head title="Investment Details" />

    <AppLayout>
        <div class="py-6">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <!-- Back Button -->
                <Link
                    :href="route('mygrownet.ventures.my-investments')"
                    class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 mb-6"
                >
                    <ArrowLeftIcon class="h-4 w-4" />
                    Back to My Investments
                </Link>

                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">{{ investment.venture.title }}</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ investment.venture.category.name }}
                        <span class="mx-2">•</span>
                        <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium', getStatusColor(investment.status)]">
                            {{ investment.status }}
                        </span>
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Investment Summary -->
                        <div class="rounded-lg bg-white p-6 shadow">
                            <h2 class="mb-4 text-lg font-semibold text-gray-900">Investment Summary</h2>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="rounded-lg bg-gray-50 p-4">
                                    <div class="text-sm text-gray-600">Amount Invested</div>
                                    <div class="mt-1 text-xl font-bold text-gray-900">{{ formatCurrency(investment.amount) }}</div>
                                </div>
                                <div class="rounded-lg bg-gray-50 p-4">
                                    <div class="text-sm text-gray-600">Shares Allocated</div>
                                    <div class="mt-1 text-xl font-bold text-gray-900">{{ investment.shares_allocated?.toLocaleString() || 'Pending' }}</div>
                                </div>
                                <div class="rounded-lg bg-gray-50 p-4">
                                    <div class="text-sm text-gray-600">Payment Method</div>
                                    <div class="mt-1 font-semibold text-gray-900">
                                        {{ investment.payment_method === 'wallet' ? 'MyGrow Wallet' : 'Mobile Money' }}
                                    </div>
                                </div>
                                <div class="rounded-lg bg-gray-50 p-4">
                                    <div class="text-sm text-gray-600">Date</div>
                                    <div class="mt-1 font-semibold text-gray-900">{{ formatDate(investment.created_at) }}</div>
                                </div>
                            </div>
                            <div class="mt-4 border-t border-gray-200 pt-4">
                                <div class="text-sm text-gray-600">Reference</div>
                                <div class="font-mono text-sm font-semibold text-gray-900">{{ investment.payment_reference }}</div>
                            </div>
                        </div>

                        <!-- Shareholder Info -->
                        <div v-if="investment.shareholder" class="rounded-lg bg-white p-6 shadow">
                            <h2 class="mb-4 text-lg font-semibold text-gray-900">Shareholder Details</h2>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="rounded-lg bg-green-50 p-4">
                                    <div class="text-sm text-green-700">Certificate Number</div>
                                    <div class="mt-1 font-mono font-semibold text-green-900">{{ investment.shareholder.certificate_number }}</div>
                                </div>
                                <div class="rounded-lg bg-green-50 p-4">
                                    <div class="text-sm text-green-700">Registration Date</div>
                                    <div class="mt-1 font-semibold text-green-900">{{ formatDate(investment.shareholder_registered_at) }}</div>
                                </div>
                                <div class="rounded-lg bg-green-50 p-4">
                                    <div class="text-sm text-green-700">Shares Owned</div>
                                    <div class="mt-1 font-semibold text-green-900">{{ investment.shareholder.shares_owned.toLocaleString() }}</div>
                                </div>
                                <div class="rounded-lg bg-green-50 p-4">
                                    <div class="text-sm text-green-700">Equity Percentage</div>
                                    <div class="mt-1 font-semibold text-green-900">{{ investment.shareholder.equity_percentage.toFixed(2) }}%</div>
                                </div>
                            </div>
                        </div>

                        <!-- Dividends -->
                        <div v-if="investment.shareholder && investment.shareholder.dividends.length > 0" class="rounded-lg bg-white p-6 shadow">
                            <h2 class="mb-4 text-lg font-semibold text-gray-900">Dividend History</h2>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Period</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Amount</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <tr v-for="dividend in investment.shareholder.dividends" :key="dividend.id" class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ dividend.dividend_period }}</td>
                                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ formatCurrency(dividend.amount) }}</td>
                                            <td class="px-4 py-3">
                                                <span :class="['inline-flex rounded-full px-2 py-1 text-xs font-medium', getStatusColor(dividend.status)]">
                                                    {{ dividend.status }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500">{{ formatDate(dividend.paid_at || dividend.payment_date) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-4 border-t border-gray-200 pt-4">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Total Dividends Received</span>
                                    <span class="font-bold text-green-600">{{ formatCurrency(investment.shareholder.total_dividends_received) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Venture Status -->
                        <div class="rounded-lg bg-white p-6 shadow">
                            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-500">Venture Status</h3>
                            <div class="flex items-center gap-3">
                                <div v-if="investment.venture.status === 'active'" class="rounded-full bg-green-100 p-2">
                                    <CheckCircleIcon class="h-5 w-5 text-green-600" />
                                </div>
                                <div v-else class="rounded-full bg-yellow-100 p-2">
                                    <ClockIcon class="h-5 w-5 text-yellow-600" />
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900">{{ investment.venture.status }}</div>
                                    <div class="text-sm text-gray-500">
                                        {{ investment.venture.status === 'active' ? 'Venture is operational' : 'Venture is in progress' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="rounded-lg bg-white p-6 shadow">
                            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-500">Quick Actions</h3>
                            <div class="space-y-3">
                                <Link
                                    :href="route('ventures.show', investment.venture.slug)"
                                    class="flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500"
                                >
                                    View Venture
                                </Link>
                                <Link
                                    :href="route('mygrownet.ventures.dividends')"
                                    class="flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                                >
                                    <CurrencyDollarIcon class="h-4 w-4" />
                                    View All Dividends
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
