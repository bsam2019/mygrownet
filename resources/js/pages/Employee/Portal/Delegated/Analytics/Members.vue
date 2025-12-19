<script setup lang="ts">
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/layouts/EmployeePortalLayout.vue';
import {
    ChartBarIcon,
    UsersIcon,
    CheckCircleIcon,
    CalendarIcon,
    ArrowTrendingUpIcon,
} from '@heroicons/vue/24/outline';

interface Props {
    employee: { id: number; full_name: string };
    stats: {
        total_members: number;
        verified_members: number;
        new_this_month: number;
        new_this_week: number;
    };
    growthTrend: {
        date: string;
        count: number;
    }[];
}

const props = defineProps<Props>();

const verificationRate = computed(() => {
    if (props.stats.total_members === 0) return 0;
    return Math.round((props.stats.verified_members / props.stats.total_members) * 100);
});

const maxTrendCount = computed(() => {
    return Math.max(...props.growthTrend.map(d => d.count), 1);
});

const getBarHeight = (count: number) => {
    return Math.max((count / maxTrendCount.value) * 100, 5);
};
</script>

<template>
    <EmployeePortalLayout>
        <Head title="Member Analytics - Delegated" />

        <div class="max-w-6xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <ChartBarIcon class="h-6 w-6 text-purple-600" aria-hidden="true" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Member Analytics</h1>
                        <p class="text-sm text-gray-500">Overview of member statistics and growth</p>
                    </div>
                </div>
                <span class="px-3 py-1 text-xs font-medium bg-purple-100 text-purple-700 rounded-full">
                    Delegated Access
                </span>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-blue-100 rounded-xl">
                            <UsersIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Total Members</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.total_members.toLocaleString() }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-green-100 rounded-xl">
                            <CheckCircleIcon class="h-6 w-6 text-green-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Verified Members</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.verified_members.toLocaleString() }}</p>
                            <p class="text-xs text-green-600">{{ verificationRate }}% verified</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-purple-100 rounded-xl">
                            <CalendarIcon class="h-6 w-6 text-purple-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">New This Month</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.new_this_month.toLocaleString() }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-amber-100 rounded-xl">
                            <ArrowTrendingUpIcon class="h-6 w-6 text-amber-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">New This Week</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.new_this_week.toLocaleString() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Growth Chart -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">New Members (Last 7 Days)</h2>
                    <p class="text-sm text-gray-500">Daily registration trend</p>
                </div>

                <div class="p-6">
                    <div class="flex items-end justify-between gap-2 h-48">
                        <div
                            v-for="day in growthTrend"
                            :key="day.date"
                            class="flex-1 flex flex-col items-center gap-2"
                        >
                            <span class="text-sm font-medium text-gray-900">{{ day.count }}</span>
                            <div
                                class="w-full bg-gradient-to-t from-purple-500 to-purple-400 rounded-t-lg transition-all duration-300"
                                :style="{ height: `${getBarHeight(day.count)}%` }"
                            ></div>
                            <span class="text-xs text-gray-500">{{ day.date }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Verification Progress -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Email Verification Rate</h3>
                <div class="relative pt-1">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">{{ stats.verified_members.toLocaleString() }} verified</span>
                        <span class="text-sm font-medium text-gray-700">{{ verificationRate }}%</span>
                    </div>
                    <div class="overflow-hidden h-3 text-xs flex rounded-full bg-gray-200">
                        <div
                            :style="{ width: `${verificationRate}%` }"
                            class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-gradient-to-r from-green-500 to-green-400 transition-all duration-500"
                        ></div>
                    </div>
                    <div class="flex items-center justify-between mt-2 text-xs text-gray-500">
                        <span>{{ (stats.total_members - stats.verified_members).toLocaleString() }} unverified</span>
                        <span>{{ stats.total_members.toLocaleString() }} total</span>
                    </div>
                </div>
            </div>

            <!-- Access Notice -->
            <div class="bg-purple-50 rounded-xl p-4 text-sm text-purple-700">
                <p class="font-medium">Delegated Access Notice</p>
                <p>You are viewing member analytics through delegated permissions. This data is read-only and all access is logged for security purposes.</p>
            </div>
        </div>
    </EmployeePortalLayout>
</template>
