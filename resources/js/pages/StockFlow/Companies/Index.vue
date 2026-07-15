<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import LoadingSkeleton from '@/components/StockFlow/LoadingSkeleton.vue';
import Pagination from '@/components/StockFlow/Pagination.vue';
import { PlusIcon } from '@heroicons/vue/24/outline';

interface Company {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
    city: string | null;
    country: string | null;
    currency: string;
    status: string;
}

interface Props {
    companies: {
        data: Company[];
        links: { url: string | null; label: string; active: boolean }[];
        meta: { current_page: number; last_page: number; total: number; from: number; to: number };
    };
}

defineProps<Props>();
</script>

<template>
    <StockFlowLayout title="Companies">
        <Head title="Companies - StockFlow" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <h1 class="text-2xl font-bold text-gray-900">Companies</h1>
                    <Link :href="route('stockflow.sub.companies.create')" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                        <PlusIcon class="h-5 w-5" aria-hidden="true" />
                        Create Company
                    </Link>
                </div>

                <LoadingSkeleton v-if="!companies.data?.length" type="card" />
                <template v-else>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <Link
                            v-for="company in companies.data"
                            :key="company.id"
                            :href="route('stockflow.sub.companies.show', company.id)"
                            class="rounded-xl bg-white p-6 shadow-sm transition hover:shadow-md"
                        >
                            <h3 class="font-semibold text-gray-900">{{ company.name }}</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ company.city }}{{ company.city && company.country ? ', ' : '' }}{{ company.country }}</p>
                            <div class="mt-3 flex items-center gap-3 text-sm text-gray-600">
                                <span v-if="company.email" class="truncate">{{ company.email }}</span>
                                <span v-if="company.phone">{{ company.phone }}</span>
                            </div>
                            <div class="mt-2 flex items-center gap-2">
                                <span class="text-xs text-gray-400">Currency: {{ company.currency }}</span>
                                <span class="inline-flex rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 capitalize">{{ company.status }}</span>
                            </div>
                        </Link>
                        <div v-if="!companies.data?.length" class="col-span-full py-12 text-center text-gray-500">
                            No companies yet
                        </div>
                    </div>
                    <Pagination :links="companies.links" :meta="companies.meta" />
                </template>
            </div>
        </div>
    </StockFlowLayout>
</template>
