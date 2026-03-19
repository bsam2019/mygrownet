<template>
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8">
                <Link :href="route('growbuilder.clients.show', client.id)"
                      class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 mb-4">
                    <ArrowLeftIcon class="h-4 w-4 mr-2" aria-hidden="true" />
                    Back to Client
                </Link>
                
                <div class="flex items-start justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ client.client_name }} - Analytics</h1>
                        <p v-if="client.company_name" class="mt-1 text-lg text-gray-600">{{ client.company_name }}</p>
                    </div>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-600">Total Sites</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">{{ stats.total_sites }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-600">Active Sites</div>
                    <div class="mt-2 text-3xl font-bold text-green-600">{{ stats.active_sites }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-600">Total Storage</div>
                    <div class="mt-2 text-3xl font-bold text-blue-600">{{ formatStorage(stats.total_storage_mb) }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-600">Total Pages</div>
                    <div class="mt-2 text-3xl font-bold text-purple-600">{{ stats.total_pages }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Storage by Site -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Storage by Site</h2>
                    <div v-if="storage_by_site.length > 0" class="space-y-3">
                        <div v-for="site in storage_by_site" :key="site.site_name" class="flex items-center justify-between">
                            <span class="text-sm text-gray-700">{{ site.site_name }}</span>
                            <div class="flex items-center gap-3">
                                <div class="w-32 bg-gray-200 rounded-full h-2">
                                    <div 
                                        class="bg-blue-600 h-2 rounded-full" 
                                        :style="{ width: `${(site.storage_mb / stats.total_storage_mb) * 100}%` }"
                                    ></div>
                                </div>
                                <span class="text-sm font-medium text-gray-900 w-16 text-right">
                                    {{ formatStorage(site.storage_mb) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-gray-500">
                        No storage data available
                    </div>
                </div>

                <!-- Pages by Site -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Pages by Site</h2>
                    <div v-if="pages_by_site.length > 0" class="space-y-3">
                        <div v-for="site in pages_by_site" :key="site.site_name" class="flex items-center justify-between">
                            <span class="text-sm text-gray-700">{{ site.site_name }}</span>
                            <div class="flex items-center gap-3">
                                <div class="w-32 bg-gray-200 rounded-full h-2">
                                    <div 
                                        class="bg-purple-600 h-2 rounded-full" 
                                        :style="{ width: `${(site.pages_count / stats.total_pages) * 100}%` }"
                                    ></div>
                                </div>
                                <span class="text-sm font-medium text-gray-900 w-16 text-right">
                                    {{ site.pages_count }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-gray-500">
                        No page data available
                    </div>
                </div>

                <!-- Sites by Status -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Sites by Status</h2>
                    <div v-if="sites_by_status.length > 0" class="space-y-3">
                        <div v-for="item in sites_by_status" :key="item.status" class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span 
                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                    :class="getStatusClass(item.status)"
                                >
                                    {{ item.status }}
                                </span>
                            </div>
                            <span class="text-lg font-bold text-gray-900">{{ item.count }}</span>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-gray-500">
                        No status data available
                    </div>
                </div>

                <!-- Sites Timeline -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Sites Created Over Time</h2>
                    <div v-if="sites_timeline.length > 0" class="space-y-3">
                        <div v-for="item in sites_timeline" :key="item.month" class="flex items-center justify-between">
                            <span class="text-sm text-gray-700">{{ formatMonth(item.month) }}</span>
                            <div class="flex items-center gap-3">
                                <div class="w-32 bg-gray-200 rounded-full h-2">
                                    <div 
                                        class="bg-green-600 h-2 rounded-full" 
                                        :style="{ width: `${(item.count / stats.total_sites) * 100}%` }"
                                    ></div>
                                </div>
                                <span class="text-sm font-medium text-gray-900 w-16 text-right">
                                    {{ item.count }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-gray-500">
                        No timeline data available
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

interface Client {
    id: number;
    client_code: string;
    client_name: string;
    company_name: string | null;
    status: string;
}

interface Stats {
    total_sites: number;
    active_sites: number;
    suspended_sites: number;
    total_storage_mb: number;
    total_pages: number;
    avg_pages_per_site: number;
}

interface StorageBySite {
    site_name: string;
    storage_mb: number;
}

interface PagesBySite {
    site_name: string;
    pages_count: number;
}

interface SitesByStatus {
    status: string;
    count: number;
}

interface SitesTimeline {
    month: string;
    count: number;
}

interface Props {
    client: Client;
    stats: Stats;
    storage_by_site: StorageBySite[];
    pages_by_site: PagesBySite[];
    sites_by_status: SitesByStatus[];
    sites_timeline: SitesTimeline[];
}

defineProps<Props>();

function formatStorage(mb: number): string {
    if (mb < 1024) {
        return `${mb.toFixed(1)} MB`;
    }
    return `${(mb / 1024).toFixed(2)} GB`;
}

function formatMonth(month: string): string {
    const [year, monthNum] = month.split('-');
    const date = new Date(parseInt(year), parseInt(monthNum) - 1);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short' });
}

function getStatusClass(status: string): string {
    const classes = {
        draft: 'bg-gray-100 text-gray-800',
        active: 'bg-green-100 text-green-800',
        suspended: 'bg-red-100 text-red-800',
        archived: 'bg-gray-100 text-gray-600',
    };
    return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-800';
}
</script>
