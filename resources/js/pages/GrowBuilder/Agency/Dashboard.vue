<template>
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">{{ agency.name }}</h1>
                <div class="mt-2 flex items-center gap-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                          :class="statusClass">
                        {{ agency.status }}
                    </span>
                    <span class="text-sm text-gray-600">
                        Plan: <span class="font-medium">{{ agency.plan }}</span>
                    </span>
                    <span v-if="agency.is_trial" class="text-sm text-amber-600">
                        Trial ends {{ agency.trial_ends_at }}
                    </span>
                </div>
            </div>

            <!-- Navigation Tabs -->
            <div class="border-b border-gray-200 mb-8">
                <nav class="-mb-px flex space-x-8">
                    <Link 
                        :href="route('growbuilder.dashboard')"
                        class="py-2 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm"
                    >
                        <GlobeAltIcon class="h-5 w-5 inline mr-2" aria-hidden="true" />
                        Sites
                    </Link>
                    <Link 
                        :href="route('growbuilder.clients.index')"
                        class="py-2 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm"
                    >
                        <UsersIcon class="h-5 w-5 inline mr-2" aria-hidden="true" />
                        Clients
                    </Link>
                    <Link 
                        :href="route('growbuilder.agency.dashboard')"
                        class="py-2 px-1 border-b-2 border-blue-500 text-blue-600 font-medium text-sm"
                    >
                        <CogIcon class="h-5 w-5 inline mr-2" aria-hidden="true" />
                        Agency
                    </Link>
                </nav>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-600">Total Sites</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">{{ stats.total_sites }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-600">Total Clients</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">{{ stats.total_clients }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-600">Team Members</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">{{ stats.active_team_members }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-600">Storage Used</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">{{ stats.storage_used_gb }} GB</div>
                </div>
            </div>

            <!-- Quota Cards -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Storage Quota -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Storage Usage</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Used</span>
                            <span class="font-medium">{{ formatStorage(quota.storage.used) }} / {{ formatStorage(quota.storage.limit) }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="h-2 rounded-full transition-all"
                                 :class="getProgressBarClass(quota.storage.warning_level)"
                                 :style="{ width: quota.storage.percentage + '%' }">
                            </div>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ quota.storage.percentage }}% used</span>
                            <span class="text-gray-600">{{ formatStorage(quota.storage.remaining) }} remaining</span>
                        </div>
                    </div>
                </div>

                <!-- Sites Quota -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Sites Usage</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Used</span>
                            <span class="font-medium">{{ quota.sites.used }} / {{ quota.sites.limit }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="h-2 rounded-full transition-all"
                                 :class="getProgressBarClass(quota.sites.warning_level)"
                                 :style="{ width: quota.sites.percentage + '%' }">
                            </div>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ quota.sites.percentage }}% used</span>
                            <span class="text-gray-600">{{ quota.sites.remaining }} remaining</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Team Members -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Team Members</h3>
                </div>
                <div class="p-6">
                    <div v-if="team_members.length > 0" class="space-y-4">
                        <div v-for="member in team_members" :key="member.id"
                             class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
                            <div>
                                <div class="font-medium text-gray-900">{{ member.name }}</div>
                                <div class="text-sm text-gray-600">{{ member.email }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-medium text-gray-900">{{ member.role }}</div>
                                <div class="text-xs text-gray-500">{{ member.joined_at }}</div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-gray-500">
                        No team members yet
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { GlobeAltIcon, UsersIcon, CogIcon } from '@heroicons/vue/24/outline';

interface Props {
    agency: {
        id: number;
        name: string;
        status: string;
        plan: string;
        is_trial: boolean;
        trial_ends_at: string | null;
    };
    quota: {
        storage: {
            used: number;
            limit: number;
            remaining: number;
            percentage: number;
            warning_level: string;
        };
        sites: {
            used: number;
            limit: number;
            remaining: number;
            percentage: number;
            warning_level: string;
        };
        team_members: {
            used: number;
            limit: number;
            remaining: number;
        };
    };
    stats: {
        total_sites: number;
        total_clients: number;
        active_team_members: number;
        storage_used_gb: number;
    };
    team_members: Array<{
        id: number;
        name: string;
        email: string;
        role: string;
        status: string;
        joined_at: string;
    }>;
    recent_activity: Array<any>;
}

const props = defineProps<Props>();

const statusClass = computed(() => {
    switch (props.agency.status) {
        case 'active':
            return 'bg-green-100 text-green-800';
        case 'trial':
            return 'bg-blue-100 text-blue-800';
        case 'suspended':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
});

function formatStorage(mb: number): string {
    if (mb >= 1024) {
        return (mb / 1024).toFixed(1) + ' GB';
    }
    return mb + ' MB';
}

function getProgressBarClass(warningLevel: string): string {
    switch (warningLevel) {
        case 'critical':
            return 'bg-red-600';
        case 'danger':
            return 'bg-orange-500';
        case 'warning':
            return 'bg-yellow-500';
        default:
            return 'bg-blue-600';
    }
}
</script>
