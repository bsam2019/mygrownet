<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import {
    MagnifyingGlassIcon,
    BuildingStorefrontIcon,
    CheckCircleIcon,
    XCircleIcon,
} from '@heroicons/vue/24/outline';

interface Business {
    id: number;
    name: string;
    slug: string;
    industry: string | null;
    user_name: string;
    user_email: string;
    is_active: boolean;
    onboarding_completed: boolean;
    posts_count: number;
    campaigns_count: number;
    sales_count: number;
    products_count: number;
    customers_count: number;
    created_at: string;
}

interface Props {
    businesses: {
        data: Business[];
        current_page: number;
        last_page: number;
        total: number;
    };
    industries: string[];
    filters: {
        search?: string;
        industry?: string;
        onboarded?: boolean;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const selectedIndustry = ref(props.filters.industry || '');
const showOnboarded = ref(props.filters.onboarded ?? false);

const applyFilters = () => {
    router.get('/admin/bizboost/businesses', {
        search: search.value || undefined,
        industry: selectedIndustry.value || undefined,
        onboarded: showOnboarded.value || undefined,
    }, { preserveState: true });
};

const toggleActive = (id: number) => {
    router.post(`/admin/bizboost/businesses/${id}/toggle-active`);
};
</script>

<template>
    <Head title="BizBoost Businesses - Admin" />
    <AdminLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Businesses</h1>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ businesses.total }} total</div>
                </div>

                <!-- Filters -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-4 mb-6">
                    <div class="flex flex-wrap gap-3 items-end">
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Search</label>
                            <div class="relative">
                                <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Search businesses or owners..."
                                    class="block w-full pl-9 pr-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 focus:outline-none"
                                    @keyup.enter="applyFilters"
                                />
                            </div>
                        </div>
                        <div class="w-44">
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Industry</label>
                            <select
                                v-model="selectedIndustry"
                                class="block w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 focus:outline-none"
                                @change="applyFilters"
                            >
                                <option value="">All Industries</option>
                                <option v-for="ind in industries" :key="ind" :value="ind">{{ ind }}</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-2 pb-[2px]">
                            <input
                                v-model="showOnboarded"
                                type="checkbox"
                                class="rounded border-gray-300 text-violet-600 focus:ring-violet-500"
                                @change="applyFilters"
                            />
                            <label class="text-sm text-gray-700 dark:text-gray-300">Onboarded only</label>
                        </div>
                        <button
                            @click="applyFilters"
                            class="px-4 py-2 bg-violet-600 text-white text-sm font-medium rounded-lg hover:bg-violet-700 transition-colors"
                        >
                            Filter
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Business</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Owner</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Posts</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Campaigns</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Sales</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Products</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr v-for="b in businesses.data" :key="b.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-4 py-3">
                                        <Link :href="route('admin.bizboost.businesses.show', b.id)" class="text-sm font-medium text-gray-900 dark:text-white hover:text-violet-600 dark:hover:text-violet-400">
                                            {{ b.name }}
                                        </Link>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ b.industry || 'N/A' }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ b.user_name }}</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">{{ b.user_email }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-center text-sm text-gray-700 dark:text-gray-300">{{ b.posts_count }}</td>
                                    <td class="px-4 py-3 text-center text-sm text-gray-700 dark:text-gray-300">{{ b.campaigns_count }}</td>
                                    <td class="px-4 py-3 text-center text-sm text-gray-700 dark:text-gray-300">{{ b.sales_count }}</td>
                                    <td class="px-4 py-3 text-center text-sm text-gray-700 dark:text-gray-300">{{ b.products_count }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <CheckCircleIcon v-if="b.is_active" class="h-4 w-4 text-green-500" title="Active" />
                                            <XCircleIcon v-else class="h-4 w-4 text-red-400" title="Inactive" />
                                            <span :class="['text-xs px-1.5 py-0.5 rounded-full font-medium', b.onboarding_completed ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400']">
                                                {{ b.onboarding_completed ? 'Onboarded' : 'Pending' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <Link :href="route('admin.bizboost.businesses.show', b.id)" class="text-xs text-violet-600 hover:text-violet-700 dark:text-violet-400 font-medium">
                                                View
                                            </Link>
                                            <button
                                                @click="toggleActive(b.id)"
                                                :class="['text-xs font-medium', b.is_active ? 'text-red-600 hover:text-red-700 dark:text-red-400' : 'text-green-600 hover:text-green-700 dark:text-green-400']"
                                            >
                                                {{ b.is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="businesses.data.length === 0">
                                    <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No businesses found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="businesses.last_page > 1" class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                        <Pagination :links="businesses.links" />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
