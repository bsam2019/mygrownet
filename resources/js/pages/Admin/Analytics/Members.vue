<template>
    <AdminLayout title="Member Analytics">
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800 leading-tight">
                Member Analytics
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Key Metrics -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Total Members</dt>
                                        <dd class="text-lg font-medium text-gray-900">{{ formatNumber(stats.total_members) }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Active Members</dt>
                                        <dd class="text-lg font-medium text-gray-900">{{ formatNumber(stats.active_members) }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">New This Month</dt>
                                        <dd class="text-lg font-medium text-gray-900">{{ formatNumber(stats.new_this_month) }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Activity Rate</dt>
                                        <dd class="text-lg font-medium text-gray-900">{{ stats.activity_rate }}%</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activity Levels -->
                <div class="bg-white shadow rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Member Activity Levels</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="text-center p-4 bg-green-50 rounded-lg border border-green-200">
                                <div class="text-2xl font-bold text-green-600">{{ formatNumber(activity_levels.highly_active) }}</div>
                                <div class="text-sm font-medium text-gray-900">Highly Active</div>
                                <div class="text-xs text-gray-500">≥500 MAP</div>
                            </div>
                            <div class="text-center p-4 bg-blue-50 rounded-lg border border-blue-200">
                                <div class="text-2xl font-bold text-blue-600">{{ formatNumber(activity_levels.moderately_active) }}</div>
                                <div class="text-sm font-medium text-gray-900">Moderately Active</div>
                                <div class="text-xs text-gray-500">200-499 MAP</div>
                            </div>
                            <div class="text-center p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                                <div class="text-2xl font-bold text-yellow-600">{{ formatNumber(activity_levels.low_active) }}</div>
                                <div class="text-sm font-medium text-gray-900">Low Active</div>
                                <div class="text-xs text-gray-500">1-199 MAP</div>
                            </div>
                            <div class="text-center p-4 bg-red-50 rounded-lg border border-red-200">
                                <div class="text-2xl font-bold text-red-600">{{ formatNumber(activity_levels.inactive) }}</div>
                                <div class="text-sm font-medium text-gray-900">Inactive</div>
                                <div class="text-xs text-gray-500">0 MAP</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Professional Level Distribution -->
                <div class="bg-white shadow rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Professional Level Distribution</h3>
                        <div class="space-y-3">
                            <div v-for="level in level_progression" :key="level.professional_level" class="flex items-center">
                                <div class="w-32 text-sm font-medium text-gray-900">
                                    Level {{ level.professional_level }}
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center">
                                        <div class="flex-1 bg-gray-200 rounded-full h-4 mr-3">
                                            <div 
                                                class="bg-blue-600 h-4 rounded-full flex items-center justify-end pr-2" 
                                                :style="{ width: (level.count / stats.total_members * 100) + '%' }"
                                            >
                                                <span class="text-xs text-white font-semibold">{{ level.count }}</span>
                                            </div>
                                        </div>
                                        <div class="text-sm text-gray-600 w-16 text-right">
                                            {{ ((level.count / stats.total_members) * 100).toFixed(1) }}%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Member Growth Trend -->
                <div class="bg-white shadow rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Member Growth Trend (Last 12 Months)</h3>
                        <div class="overflow-x-auto">
                            <div class="flex items-end space-x-2 h-64">
                                <div v-for="month in member_growth" :key="month.date" class="flex-1 flex flex-col items-center">
                                    <div class="w-full bg-blue-600 rounded-t hover:bg-blue-700 transition-colors cursor-pointer" 
                                         :style="{ height: (month.new_members / Math.max(...member_growth.map(m => m.new_members)) * 100) + '%' }"
                                         :title="`${month.date}: ${month.new_members} new members`">
                                    </div>
                                    <div class="text-xs text-gray-600 mt-2 transform -rotate-45 origin-top-left whitespace-nowrap">
                                        {{ month.date }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps<{
    stats: {
        total_members: number;
        active_members: number;
        new_this_month: number;
        activity_rate: number;
    };
    member_growth: Array<{
        date: string;
        new_members: number;
    }>;
    activity_levels: {
        highly_active: number;
        moderately_active: number;
        low_active: number;
        inactive: number;
    };
    level_progression: Array<{
        professional_level: number;
        count: number;
    }>;
}>();

const formatNumber = (num: number) => {
    return new Intl.NumberFormat().format(num);
};
</script>
