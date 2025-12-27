<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Trash2, RotateCcw, Search, AlertTriangle } from 'lucide-vue-next';

interface Site {
    id: number;
    name: string;
    subdomain: string;
    scheduled_deletion_at: string | null;
    deletion_reason: string | null;
    created_at: string;
    user: { id: number; name: string; email: string } | null;
}

interface Props {
    sites: {
        data: Site[];
        links: any[];
        current_page: number;
        last_page: number;
    };
    filters: { search?: string };
}

const props = defineProps<Props>();
const search = ref(props.filters.search || '');

const applyFilters = () => {
    router.get(route('admin.growbuilder.deleted'), {
        search: search.value || undefined,
    }, { preserveState: true });
};

const restoreSite = (site: Site) => {
    if (confirm(`Restore "${site.name}"? It will be set to draft status.`)) {
        router.post(route('admin.growbuilder.restore', site.id));
    }
};

const forceDelete = (site: Site) => {
    if (confirm(`PERMANENTLY DELETE "${site.name}"? This cannot be undone!`)) {
        router.delete(route('admin.growbuilder.force-delete', site.id));
    }
};

const formatDate = (date: string) => new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });

const getDaysRemaining = (scheduledDate: string | null) => {
    if (!scheduledDate) return 'N/A';
    const days = Math.ceil((new Date(scheduledDate).getTime() - Date.now()) / (1000 * 60 * 60 * 24));
    return days > 0 ? `${days} days` : 'Overdue';
};
</script>

<template>
    <AdminLayout>
        <Head title="GrowBuilder - Deleted Sites" />

        <div class="p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <Trash2 class="h-7 w-7 text-red-600" aria-hidden="true" />
                    Deleted Sites
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Sites scheduled for permanent deletion (30-day grace period)</p>
            </div>

            <!-- Warning Banner -->
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4 mb-6 flex items-start gap-3">
                <AlertTriangle class="h-5 w-5 text-amber-600 flex-shrink-0 mt-0.5" aria-hidden="true" />
                <div>
                    <p class="text-amber-800 dark:text-amber-200 font-medium">Grace Period Active</p>
                    <p class="text-amber-700 dark:text-amber-300 text-sm">Sites can be restored within 30 days of deletion. After that, they are permanently removed.</p>
                </div>
            </div>

            <!-- Search -->
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 mb-6 border border-gray-200 dark:border-gray-700">
                <div class="flex gap-4">
                    <div class="flex-1 relative">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search deleted sites..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                    <button @click="applyFilters" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Search</button>
                </div>
            </div>

            <!-- Sites Table -->
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Site</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Owner</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Reason</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Time Remaining</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="site in sites.data" :key="site.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900 dark:text-white">{{ site.name }}</div>
                                <div class="text-sm text-gray-500">{{ site.subdomain }}.mygrownet.com</div>
                            </td>
                            <td class="px-4 py-3">
                                <div v-if="site.user" class="text-sm">
                                    <div class="text-gray-900 dark:text-white">{{ site.user.name }}</div>
                                    <div class="text-gray-500">{{ site.user.email }}</div>
                                </div>
                                <span v-else class="text-gray-400">Unknown</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ site.deletion_reason || 'User requested' }}</td>
                            <td class="px-4 py-3">
                                <span :class="[
                                    'px-2 py-1 text-xs font-medium rounded-full',
                                    getDaysRemaining(site.scheduled_deletion_at) === 'Overdue' 
                                        ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'
                                        : 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400'
                                ]">
                                    {{ getDaysRemaining(site.scheduled_deletion_at) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        @click="restoreSite(site)"
                                        class="p-2 text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg"
                                        aria-label="Restore site"
                                    >
                                        <RotateCcw class="h-4 w-4" aria-hidden="true" />
                                    </button>
                                    <button
                                        @click="forceDelete(site)"
                                        class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg"
                                        aria-label="Delete permanently"
                                    >
                                        <Trash2 class="h-4 w-4" aria-hidden="true" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="sites.data.length === 0">
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">No deleted sites</td>
                        </tr>
                    </tbody>
                </table>

                <div v-if="sites.last_page > 1" class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex justify-center gap-2">
                    <Link
                        v-for="link in sites.links"
                        :key="link.label"
                        :href="link.url || '#'"
                        :class="[
                            'px-3 py-1 rounded text-sm',
                            link.active ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700',
                            !link.url && 'opacity-50 cursor-not-allowed'
                        ]"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
