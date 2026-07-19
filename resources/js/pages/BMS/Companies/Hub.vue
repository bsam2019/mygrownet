<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { BuildingOfficeIcon, PlusIcon, ArrowRightIcon, StarIcon as StarOutline } from '@heroicons/vue/24/outline';
import { StarIcon as StarSolid } from '@heroicons/vue/24/solid';

interface Metrics {
    active_jobs: number;
    pending_invoices: number;
    outstanding: number;
    monthly_revenue: number;
    total_customers: number;
}

interface Company {
    company_id: number;
    company_name: string;
    industry: string | null;
    logo: string | null;
    role: string;
    status: string;
    metrics: Metrics;
}

const props = defineProps<{
    companies: Company[];
    defaultCompanyId: number | null;
}>();

const fmt = (n: number) => n.toLocaleString('en-ZM', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

function enter(companyId: number) {
    router.post(route('bms.companies.enter', companyId));
}

function toggleDefault(companyId: number) {
    const newId = props.defaultCompanyId === companyId ? null : companyId;
    router.post(route('bms.companies.set-default'), {
        company_id: newId,
    });
}
</script>

<template>
    <Head title="My Companies - CMS" />

    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-blue-50 flex items-center justify-center py-12 px-4">
        <div class="w-full max-w-2xl">

            <!-- Header -->
            <div class="text-center mb-10">
                <div class="inline-flex items-center justify-center mb-4">
                    <img src="/logo.png" alt="MyGrowNet" class="h-12 object-contain" />
                </div>
                <h1 class="text-3xl font-bold text-gray-900">My Companies</h1>
                <p class="mt-2 text-gray-500">Select a company to manage, or create a new one.</p>
            </div>

            <!-- Existing companies -->
            <div v-if="companies.length > 0" class="space-y-3 mb-6">
                <div
                    v-for="c in companies"
                    :key="c.company_id"
                    class="group flex items-center gap-3 bg-white rounded-xl border border-gray-200 shadow-sm hover:border-blue-400 hover:shadow-md transition"
                >
                    <!-- Default star toggle -->
                    <button
                        @click.stop="toggleDefault(c.company_id)"
                        class="ml-4 flex-shrink-0 text-gray-300 hover:text-yellow-400 transition"
                        :title="c.company_id === defaultCompanyId ? 'Remove as default' : 'Set as default'"
                    >
                        <StarSolid v-if="c.company_id === defaultCompanyId" class="h-5 w-5 text-yellow-400" aria-hidden="true" />
                        <StarOutline v-else class="h-5 w-5" aria-hidden="true" />
                    </button>

                    <!-- Company button -->
                    <button
                        @click="enter(c.company_id)"
                        class="flex-1 flex items-center gap-4 py-5 pr-5 text-left"
                    >
                        <!-- Logo / initials -->
                        <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-600 transition">
                            <img v-if="c.logo" :src="c.logo" :alt="c.company_name" class="w-12 h-12 rounded-xl object-cover" />
                            <span v-else class="text-lg font-bold text-blue-600 group-hover:text-white transition">
                                {{ c.company_name.charAt(0).toUpperCase() }}
                            </span>
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="text-base font-semibold text-gray-900 truncate">{{ c.company_name }}</p>
                            <p class="text-sm text-gray-500 truncate">
                                {{ c.role }}
                                <span v-if="c.industry" class="ml-2 text-gray-400">· {{ c.industry.replace(/_/g, ' ') }}</span>
                                <span v-if="c.company_id === defaultCompanyId" class="ml-2 text-xs text-yellow-600 font-medium">Default</span>
                            </p>
                            <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-xs">
                                <span class="text-blue-600 font-medium">{{ c.metrics.active_jobs }}</span>
                                <span class="text-gray-400">jobs</span>
                                <span class="text-amber-600 font-medium">{{ c.metrics.pending_invoices }}</span>
                                <span class="text-gray-400">pending</span>
                                <span class="text-red-600 font-medium">K{{ fmt(c.metrics.outstanding) }}</span>
                                <span class="text-gray-400">due</span>
                                <span class="text-green-600 font-medium">K{{ fmt(c.metrics.monthly_revenue) }}</span>
                                <span class="text-gray-400">rev</span>
                                <span class="text-gray-600 font-medium">{{ c.metrics.total_customers }}</span>
                                <span class="text-gray-400">cust</span>
                            </div>
                        </div>

                        <ArrowRightIcon class="h-5 w-5 text-gray-400 group-hover:text-blue-600 transition flex-shrink-0" aria-hidden="true" />
                    </button>
                </div>
            </div>

            <!-- Empty state -->
            <div v-else class="text-center py-10 bg-white rounded-xl border border-dashed border-gray-300 mb-6">
                <BuildingOfficeIcon class="h-12 w-12 text-gray-300 mx-auto mb-3" aria-hidden="true" />
                <p class="text-gray-500 text-sm">You don't have any companies yet.</p>
                <p class="text-gray-400 text-xs mt-1">Create your first company to get started.</p>
            </div>

            <!-- Create new company -->
            <Link
                :href="route('bms.companies.create')"
                class="w-full flex items-center justify-center gap-2 bg-blue-600 text-white rounded-xl py-4 font-semibold hover:bg-blue-700 transition shadow-lg hover:shadow-xl"
            >
                <PlusIcon class="h-5 w-5" aria-hidden="true" />
                Create New Company
            </Link>

            <p class="text-center text-xs text-gray-400 mt-4">
                14-day free trial · No credit card required
            </p>
        </div>
    </div>
</template>
