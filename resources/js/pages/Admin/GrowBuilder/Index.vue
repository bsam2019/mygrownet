<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Globe, Trash2, Play, Pause, Search, ExternalLink } from 'lucide-vue-next';

interface Site {
    id: number;
    name: string;
    subdomain: string;
    status: string;
    plan: string;
    published_at: string | null;
    created_at: string;
    updated_at: string;
    user: { id: number; name: string; email: string } | null;
    storage_used?: number;
    storage_limit?: number;
    storage_used_formatted?: string;
    storage_limit_formatted?: string;
    storage_percentage?: number;
}

interface Props {
    sites: {
        data: Site[];
        links: any[];
        current_page: number;
        last_page: number;
    };
    stats: { total: number; published: number; draft: number; deleted: number };
    storageStats?: {
        total_used_formatted: string;
        total_allocated_formatted: string;
        sites_over_limit: number;
        sites_near_limit: number;
    };
    filters: { search?: string; status?: string };
}

const props = defineProps<Props>();
const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || 'all');

const applyFilters = () => {
    router.get(route('admin.growbuilder.index'), {
        search: search.value || undefined,
        status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    }, { preserveState: true });
};

const togglePublish = (site: Site) => {
    if (confirm(`Are you sure you want to ${site.status === 'published' ? 'unpublish' : 'publish'} "${site.name}"?`)) {
        router.post(route('admin.growbuilder.toggle-publish', site.id));
    }
};

const forceDelete = (site: Site) => {
    if (confirm(`PERMANENTLY DELETE "${site.name}"? This cannot be undone!`)) {
        router.delete(route('admin.growbuilder.force-delete', site.id));
    }
};

const formatDate = (date: string) => new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
</script>

<template>
    <AdminLayout>
        <Head title="GrowBuilder - All Sites" />
        <div class="p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <Globe class="h-7 w-7 text-blue-600" aria-hidden="true" />
                    GrowBuilder Sites
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Manage all GrowBuilder sites across the platform</p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</div>
                    <div class="text-sm text-gray-500">Total Sites</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                    <div class="text-2xl font-bold text-green-600">{{ stats.published }}</div>
                    <div class="text-sm text-gray-500">Published</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                    <div class="text-2xl font-bold text-yellow-600">{{ stats.draft }}</div>
                    <div class="text-sm text-gray-500">Draft</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                    <div class="text-2xl font-bold text-red-600">{{ stats.deleted }}</div>
                    <div class="text-sm text-gray-500">Deleted</div>
                </div>
                <div v-if="storageStats" class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                    <div class="text-2xl font-bold text-blue-600">{{ storageStats.total_used_formatted }}</div>
                    <div class="text-sm text-gray-500">Storage Used</div>
                </div>
                <div v-if="storageStats" class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                    <div class="text-2xl font-bold text-orange-600">{{ storageStats.sites_over_limit + storageStats.sites_near_limit }}</div>
                    <div class="text-sm text-gray-500">Storage Alerts</div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 mb-6 border border-gray-200 dark:border-gray-700">
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1 relative">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
                        <input v-model="search" type="text" placeholder="Search sites, subdomains, or owners..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            @keyup.enter="applyFilters" />
                    </div>
                    <select v-model="statusFilter" @change="applyFilters"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="all">All Status</option>
                        <option value="published">Published</option>
                        <option value="draft">Draft</option>
                    </select>
                    <button @click="applyFilters" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Search</button>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Site</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Owner</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Created</th>
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
                            <td class="px-4 py-3">
                                <span :class="['px-2 py-1 text-xs font-medium rounded-full', site.status === 'published' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400']">
                                    {{ site.status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ formatDate(site.created_at) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a :href="`/sites/${site.subdomain}`" target="_blank" class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg" :aria-label="`Preview ${site.name}`">
                                        <ExternalLink class="h-4 w-4" aria-hidden="true" />
                                    </a>
                                    <button @click="togglePublish(site)" :class="['p-2 rounded-lg', site.status === 'published' ? 'text-yellow-600 hover:bg-yellow-50 dark:hover:bg-yellow-900/20' : 'text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20']" :aria-label="site.status === 'published' ? 'Unpublish' : 'Publish'">
                                        <Pause v-if="site.status === 'published'" class="h-4 w-4" aria-hidden="true" />
                                        <Play v-else class="h-4 w-4" aria-hidden="true" />
                                    </button>
                                    <button @click="forceDelete(site)" class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg" aria-label="Delete permanently">
                                        <Trash2 class="h-4 w-4" aria-hidden="true" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="sites.data.length === 0">
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">No sites found</td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="sites.last_page > 1" class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex justify-center gap-2">
                    <Link v-for="link in sites.links" :key="link.label" :href="link.url || '#'" :class="['px-3 py-1 rounded text-sm', link.active ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700', !link.url && 'opacity-50 cursor-not-allowed']" v-html="link.label" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
