<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { CloudIcon, UsersIcon, HardDriveIcon, FileTextIcon } from 'lucide-vue-next';

interface Props {
    stats: {
        total_users: number;
        total_storage_used: number;
        total_files: number;
        active_subscriptions: number;
    };
    planStats: Array<{
        name: string;
        subscribers: number;
    }>;
}

const props = defineProps<Props>();

const formatBytes = (bytes: number) => {
    if (bytes === 0) return '0 B';
    
    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    let size = bytes;
    let unitIndex = 0;
    
    while (size >= 1024 && unitIndex < units.length - 1) {
        size /= 1024;
        unitIndex++;
    }
    
    return `${size.toFixed(2)} ${units[unitIndex]}`;
};
</script>

<template>
    <Head title="GrowBackup Admin Dashboard" />

    <AdminLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                        <CloudIcon class="h-8 w-8 text-blue-600" />
                        GrowBackup Administration
                    </h1>
                    <p class="mt-2 text-gray-600">Manage cloud storage plans and user subscriptions</p>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Total Users</p>
                                <p class="text-3xl font-bold text-gray-900">{{ stats.total_users }}</p>
                            </div>
                            <UsersIcon class="h-12 w-12 text-blue-500" />
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Storage Used</p>
                                <p class="text-3xl font-bold text-gray-900">{{ formatBytes(stats.total_storage_used) }}</p>
                            </div>
                            <HardDriveIcon class="h-12 w-12 text-green-500" />
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Total Files</p>
                                <p class="text-3xl font-bold text-gray-900">{{ stats.total_files.toLocaleString() }}</p>
                            </div>
                            <FileTextIcon class="h-12 w-12 text-purple-500" />
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Active Subscriptions</p>
                                <p class="text-3xl font-bold text-gray-900">{{ stats.active_subscriptions }}</p>
                            </div>
                            <CloudIcon class="h-12 w-12 text-indigo-500" />
                        </div>
                    </div>
                </div>

                <!-- Plan Distribution -->
                <div class="bg-white rounded-lg shadow p-6 mb-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Plan Distribution</h2>
                    <div class="space-y-4">
                        <div v-for="plan in planStats" :key="plan.name" class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium text-gray-700">{{ plan.name }}</span>
                                    <span class="text-sm text-gray-600">{{ plan.subscribers }} subscribers</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div 
                                        class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                                        :style="{ width: `${(plan.subscribers / stats.active_subscriptions) * 100}%` }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <Link 
                        :href="route('admin.growbackup.plans')"
                        class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow"
                    >
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Manage Plans</h3>
                        <p class="text-gray-600 text-sm">Edit pricing, storage quotas, and features</p>
                    </Link>

                    <Link 
                        :href="route('admin.growbackup.users')"
                        class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow"
                    >
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">User Management</h3>
                        <p class="text-gray-600 text-sm">View users and award bonus storage</p>
                    </Link>

                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Analytics</h3>
                        <p class="text-gray-600 text-sm">Coming soon: Detailed usage analytics</p>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
