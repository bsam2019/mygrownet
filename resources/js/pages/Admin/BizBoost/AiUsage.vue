<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { MagnifyingGlassIcon, SparklesIcon } from '@heroicons/vue/24/outline';

interface UsageRow {
    business_name: string;
    business_id: number;
    total_credits: number;
    total_calls: number;
    successful_calls: number;
    last_used: string;
}

interface Props {
    usage: {
        data: UsageRow[];
        current_page: number;
        last_page: number;
        total: number;
    };
    contentTypes: string[];
    globalStats: {
        total_credits: number;
        total_calls: number;
        successful_calls: number;
    };
    filters: {
        search?: string;
        content_type?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const selectedType = ref(props.filters.content_type || '');

const applyFilters = () => {
    router.get('/admin/bizboost/ai-usage', {
        search: search.value || undefined,
        content_type: selectedType.value || undefined,
    }, { preserveState: true });
};
</script>

<template>
    <Head title="BizBoost AI Usage - Admin" />
    <AdminLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">AI Usage</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">AI content generation consumption across all businesses</p>
                </div>

                <!-- Global Stats -->
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-5">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Credits Used</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ globalStats.total_credits }}</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-5">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total API Calls</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ globalStats.total_calls }}</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-5">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Successful Calls</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ globalStats.successful_calls }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ globalStats.total_calls > 0 ? Math.round(globalStats.successful_calls / globalStats.total_calls * 100) : 0 }}% success rate</p>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-4 mb-6">
                    <div class="flex flex-wrap gap-3 items-end">
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Search Business</label>
                            <div class="relative">
                                <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Search by business name..."
                                    class="block w-full pl-9 pr-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 focus:outline-none"
                                    @keyup.enter="applyFilters"
                                />
                            </div>
                        </div>
                        <div class="w-44">
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Content Type</label>
                            <select
                                v-model="selectedType"
                                class="block w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 focus:outline-none"
                                @change="applyFilters"
                            >
                                <option value="">All Types</option>
                                <option v-for="t in contentTypes" :key="t" :value="t">{{ t }}</option>
                            </select>
                        </div>
                        <button @click="applyFilters" class="px-4 py-2 bg-violet-600 text-white text-sm font-medium rounded-lg hover:bg-violet-700 transition-colors">Filter</button>
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Business</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Credits</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Calls</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Successful</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Success Rate</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Last Used</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr v-for="u in usage.data" :key="u.business_id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-4 py-3">
                                        <Link :href="route('admin.bizboost.businesses.show', u.business_id)" class="text-sm font-medium text-gray-900 dark:text-white hover:text-violet-600 dark:hover:text-violet-400">
                                            {{ u.business_name }}
                                        </Link>
                                    </td>
                                    <td class="px-4 py-3 text-center text-sm font-semibold text-gray-900 dark:text-white">{{ u.total_credits }}</td>
                                    <td class="px-4 py-3 text-center text-sm text-gray-700 dark:text-gray-300">{{ u.total_calls }}</td>
                                    <td class="px-4 py-3 text-center text-sm text-gray-700 dark:text-gray-300">{{ u.successful_calls }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span :class="['text-xs font-medium px-2 py-0.5 rounded-full', u.total_calls > 0 && u.successful_calls / u.total_calls >= 0.8 ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : u.total_calls > 0 && u.successful_calls / u.total_calls >= 0.5 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400']">
                                            {{ u.total_calls > 0 ? Math.round(u.successful_calls / u.total_calls * 100) : 0 }}%
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-500 dark:text-gray-400">{{ u.last_used }}</td>
                                </tr>
                                <tr v-if="usage.data.length === 0">
                                    <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No AI usage data found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="usage.last_page > 1" class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                        <Pagination :links="usage.links" />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
