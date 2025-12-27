<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { HardDrive, Search, RefreshCw, AlertTriangle, CheckCircle, Edit2, Save, X } from 'lucide-vue-next';

interface Site {
    id: number;
    name: string;
    subdomain: string;
    status: string;
    plan: string;
    user: { id: number; name: string; email: string } | null;
    storage_used: number;
    storage_limit: number;
    storage_used_formatted: string;
    storage_limit_formatted: string;
    storage_percentage: number;
    storage_calculated_at: string | null;
    is_over_limit: boolean;
    is_near_limit: boolean;
}

interface Props {
    sites: { data: Site[]; links: any[]; current_page: number; last_page: number };
    storageStats: {
        total_used: number;
        total_used_formatted: string;
        total_allocated: number;
        total_allocated_formatted: string;
        sites_over_limit: number;
        sites_near_limit: number;
        average_usage: number;
        average_usage_formatted: string;
    };
    planLimits: Record<string, number>;
    filters: { search?: string; storage_status?: string; sort?: string; dir?: string };
}

const props = defineProps<Props>();
const search = ref(props.filters.search || '');
const storageStatus = ref(props.filters.storage_status || 'all');
const editingSiteId = ref<number | null>(null);
const editingLimit = ref<number>(0);
const isRecalculating = ref(false);
</script>

<template>
    <AdminLayout>
        <Head title="GrowBuilder - Storage Management" />
        <div class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <HardDrive class="h-7 w-7 text-blue-600" aria-hidden="true" />
                        Storage Management
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Monitor and manage storage for all GrowBuilder sites</p>
                </div>
                <button
                    @click="() => { isRecalculating = true; router.post(route('admin.growbuilder.recalculate-all-storage'), {}, { onFinish: () => isRecalculating = false }); }"
                    :disabled="isRecalculating"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                >
                    <RefreshCw :class="['h-4 w-4', isRecalculating && 'animate-spin']" aria-hidden="true" />
                    Recalculate All
                </button>
            </div>

            <!-- Storage Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ storageStats.total_used_formatted }}</div>
                    <div class="text-sm text-gray-500">Total Used</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                    <div class="text-2xl font-bold text-blue-600">{{ storageStats.total_allocated_formatted }}</div>
                    <div class="text-sm text-gray-500">Total Allocated</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                    <div class="text-2xl font-bold text-red-600">{{ storageStats.sites_over_limit }}</div>
                    <div class="text-sm text-gray-500">Over Limit</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                    <div class="text-2xl font-bold text-yellow-600">{{ storageStats.sites_near_limit }}</div>
                    <div class="text-sm text-gray-500">Near Limit (80%+)</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 mb-6 border border-gray-200 dark:border-gray-700">
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1 relative">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
                        <input v-model="search" type="text" placeholder="Search sites or owners..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            @keyup.enter="router.get(route('admin.growbuilder.storage'), { search: search || undefined, storage_status: storageStatus !== 'all' ? storageStatus : undefined }, { preserveState: true })" />
                    </div>
                    <select v-model="storageStatus" @change="router.get(route('admin.growbuilder.storage'), { search: search || undefined, storage_status: storageStatus !== 'all' ? storageStatus : undefined }, { preserveState: true })"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="all">All Sites</option>
                        <option value="over">Over Limit</option>
                        <option value="near">Near Limit (80%+)</option>
                        <option value="normal">Normal</option>
                    </select>
                </div>
            </div>

            <!-- Sites Table -->
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Site</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Owner</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Plan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Storage Used</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Limit</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Usage</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="site in sites.data" :key="site.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <AlertTriangle v-if="site.is_over_limit" class="h-4 w-4 text-red-500" aria-hidden="true" />
                                    <AlertTriangle v-else-if="site.is_near_limit" class="h-4 w-4 text-yellow-500" aria-hidden="true" />
                                    <CheckCircle v-else class="h-4 w-4 text-green-500" aria-hidden="true" />
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-white">{{ site.name }}</div>
                                        <div class="text-sm text-gray-500">{{ site.subdomain }}.mygrownet.com</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div v-if="site.user" class="text-sm">
                                    <div class="text-gray-900 dark:text-white">{{ site.user.name }}</div>
                                    <div class="text-gray-500">{{ site.user.email }}</div>
                                </div>
                                <span v-else class="text-gray-400">Unknown</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 capitalize">
                                    {{ site.plan }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white font-medium">
                                {{ site.storage_used_formatted }}
                            </td>
                            <td class="px-4 py-3">
                                <div v-if="editingSiteId === site.id" class="flex items-center gap-2">
                                    <input v-model.number="editingLimit" type="number" min="0" step="1048576"
                                        class="w-32 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
                                    <button @click="() => { router.put(route('admin.growbuilder.update-storage-limit', site.id), { storage_limit: editingLimit }, { onSuccess: () => editingSiteId = null }); }"
                                        class="p-1 text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 rounded" aria-label="Save">
                                        <Save class="h-4 w-4" aria-hidden="true" />
                                    </button>
                                    <button @click="editingSiteId = null" class="p-1 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 rounded" aria-label="Cancel">
                                        <X class="h-4 w-4" aria-hidden="true" />
                                    </button>
                                </div>
                                <div v-else class="flex items-center gap-2">
                                    <span class="text-sm text-gray-900 dark:text-white">{{ site.storage_limit_formatted }}</span>
                                    <button @click="() => { editingSiteId = site.id; editingLimit = site.storage_limit; }"
                                        class="p-1 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded" aria-label="Edit limit">
                                        <Edit2 class="h-3 w-3" aria-hidden="true" />
                                    </button>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 h-2 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden w-24">
                                        <div :class="['h-full rounded-full', site.is_over_limit ? 'bg-red-500' : site.is_near_limit ? 'bg-yellow-500' : 'bg-green-500']"
                                            :style="{ width: Math.min(site.storage_percentage, 100) + '%' }"></div>
                                    </div>
                                    <span :class="['text-xs font-medium', site.is_over_limit ? 'text-red-600' : site.is_near_limit ? 'text-yellow-600' : 'text-gray-600 dark:text-gray-400']">
                                        {{ site.storage_percentage }}%
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end">
                                    <button @click="router.post(route('admin.growbuilder.recalculate-storage', site.id))"
                                        class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg" aria-label="Recalculate storage">
                                        <RefreshCw class="h-4 w-4" aria-hidden="true" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="sites.data.length === 0">
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">No sites found</td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="sites.last_page > 1" class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex justify-center gap-2">
                    <Link v-for="link in sites.links" :key="link.label" :href="link.url || '#'"
                        :class="['px-3 py-1 rounded text-sm', link.active ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700', !link.url && 'opacity-50 cursor-not-allowed']"
                        v-html="link.label" />
                </div>
            </div>

            <!-- Plan Limits Reference -->
            <div class="mt-6 bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                <h3 class="font-medium text-gray-900 dark:text-white mb-3">Default Storage Limits by Plan</h3>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div v-for="(limit, plan) in planLimits" :key="plan" class="text-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="text-sm font-medium text-gray-900 dark:text-white capitalize">{{ plan }}</div>
                        <div class="text-lg font-bold text-blue-600">{{ (limit / 1024 / 1024).toFixed(0) }} MB</div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
