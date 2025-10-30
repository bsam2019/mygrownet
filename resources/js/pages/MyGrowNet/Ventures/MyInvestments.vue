<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import MemberLayout from '@/layouts/MemberLayout.vue';
import { BuildingOffice2Icon, ChartBarIcon, DocumentTextIcon } from '@heroicons/vue/24/outline';

interface Investment {
    id: number;
    amount: number;
    shares: number;
    status: string;
    payment_method: string;
    created_at: string;
    venture: {
        id: number;
        title: string;
        slug: string;
        status: string;
        category: {
            name: string;
        };
    };
}

interface Props {
    investments: {
        data: Investment[];
        links: any;
        meta: any;
    };
}

const props = defineProps<Props>();

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 0,
    }).format(amount);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        pending: 'bg-yellow-100 text-yellow-800',
        confirmed: 'bg-green-100 text-green-800',
        refunded: 'bg-red-100 text-red-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const getVentureStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        draft: 'bg-gray-100 text-gray-800',
        pending_approval: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-blue-100 text-blue-800',
        funding: 'bg-indigo-100 text-indigo-800',
        funded: 'bg-green-100 text-green-800',
        active: 'bg-emerald-100 text-emerald-800',
        completed: 'bg-purple-100 text-purple-800',
        cancelled: 'bg-red-100 text-red-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const totalInvested = props.investments.data.reduce((sum, inv) => sum + inv.amount, 0);
const totalShares = props.investments.data.reduce((sum, inv) => sum + inv.shares, 0);
</script>

<template>
    <Head title="My Investments" />

    <MemberLayout>
        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">My Venture Investments</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Track your investments and shareholdings across all ventures
                    </p>
                </div>

                <!-- Summary Cards -->
                <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="rounded-lg bg-white p-6 shadow">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-blue-100 p-3">
                                <BuildingOffice2Icon class="h-6 w-6 text-blue-600" />
                            </div>
                            <div>
                                <div class="text-sm text-gray-600">Total Ventures</div>
                                <div class="text-2xl font-bold text-gray-900">{{ investments.data.length }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg bg-white p-6 shadow">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-green-100 p-3">
                                <ChartBarIcon class="h-6 w-6 text-green-600" />
                            </div>
                            <div>
                                <div class="text-sm text-gray-600">Total Invested</div>
                                <div class="text-2xl font-bold text-gray-900">{{ formatCurrency(totalInvested) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg bg-white p-6 shadow">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-indigo-100 p-3">
                                <DocumentTextIcon class="h-6 w-6 text-indigo-600" />
                            </div>
                            <div>
                                <div class="text-sm text-gray-600">Total Shares</div>
                                <div class="text-2xl font-bold text-gray-900">{{ totalShares.toLocaleString() }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Investments List -->
                <div class="rounded-lg bg-white shadow">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h2 class="text-lg font-semibold text-gray-900">Investment History</h2>
                    </div>

                    <div v-if="investments.data.length === 0" class="px-6 py-12 text-center">
                        <BuildingOffice2Icon class="mx-auto h-12 w-12 text-gray-400" />
                        <h3 class="mt-2 text-sm font-semibold text-gray-900">No investments yet</h3>
                        <p class="mt-1 text-sm text-gray-500">Start investing in ventures to build your portfolio.</p>
                        <div class="mt-6">
                            <Link
                                :href="route('ventures.index')"
                                class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500"
                            >
                                Browse Ventures
                            </Link>
                        </div>
                    </div>

                    <div v-else class="divide-y divide-gray-200">
                        <div
                            v-for="investment in investments.data"
                            :key="investment.id"
                            class="px-6 py-4 hover:bg-gray-50 transition-colors"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-start gap-3">
                                        <div class="flex-1">
                                            <Link
                                                :href="route('ventures.show', investment.venture.slug)"
                                                class="text-lg font-semibold text-gray-900 hover:text-blue-600"
                                            >
                                                {{ investment.venture.title }}
                                            </Link>
                                            <div class="mt-1 flex items-center gap-2">
                                                <span class="text-sm text-gray-600">{{ investment.venture.category.name }}</span>
                                                <span class="text-gray-400">•</span>
                                                <span :class="['inline-flex items-center rounded-full px-2 py-1 text-xs font-medium', getVentureStatusColor(investment.venture.status)]">
                                                    {{ investment.venture.status.replace('_', ' ') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-3 grid grid-cols-2 gap-4 sm:grid-cols-4">
                                        <div>
                                            <div class="text-xs text-gray-500">Investment Amount</div>
                                            <div class="font-semibold text-gray-900">{{ formatCurrency(investment.amount) }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-500">Shares</div>
                                            <div class="font-semibold text-gray-900">{{ investment.shares.toLocaleString() }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-500">Payment Method</div>
                                            <div class="font-semibold text-gray-900">
                                                {{ investment.payment_method === 'wallet' ? 'Wallet' : 'Mobile Money' }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-500">Date</div>
                                            <div class="font-semibold text-gray-900">{{ formatDate(investment.created_at) }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col items-end gap-2">
                                    <span :class="['inline-flex items-center rounded-full px-3 py-1 text-xs font-medium', getStatusColor(investment.status)]">
                                        {{ investment.status }}
                                    </span>
                                    <Link
                                        :href="route('mygrownet.ventures.investment-details', investment.id)"
                                        class="text-sm font-medium text-blue-600 hover:text-blue-500"
                                    >
                                        View Details →
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="investments.links && investments.data.length > 0" class="border-t border-gray-200 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Showing {{ investments.meta.from }} to {{ investments.meta.to }} of {{ investments.meta.total }} investments
                            </div>
                            <div class="flex gap-2">
                                <Link
                                    v-for="link in investments.links"
                                    :key="link.label"
                                    :href="link.url"
                                    :class="[
                                        'px-3 py-1 rounded text-sm',
                                        link.active
                                            ? 'bg-blue-600 text-white'
                                            : link.url
                                            ? 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
                                            : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                    ]"
                                    v-html="link.label"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>
