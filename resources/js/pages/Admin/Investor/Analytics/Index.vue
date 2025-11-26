<script setup lang="ts">
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
    ChartBarIcon,
    EnvelopeIcon,
    EyeIcon,
    CursorArrowRaysIcon,
    UserGroupIcon,
    ClockIcon,
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
} from '@heroicons/vue/24/outline';

interface EmailStats {
    total: number;
    sent: number;
    failed: number;
    pending: number;
    opened: number;
    clicked: number;
    open_rate: number;
    click_rate: number;
    delivery_rate: number;
    by_type: Record<string, { open_rate: number; click_rate: number }>;
}

interface AnnouncementStats {
    total: number;
    published: number;
    total_reads: number;
    avg_read_rate: number;
    by_type: Record<string, { count: number; reads: number }>;
}

interface MessageStats {
    total: number;
    from_investors: number;
    to_investors: number;
    avg_response_time_hours: number;
    unread: number;
}

interface InvestorActivity {
    total_investors: number;
    active_last_7_days: number;
    active_last_30_days: number;
    login_trend: { date: string; count: number }[];
}

interface Props {
    emailStats: EmailStats;
    announcementStats: AnnouncementStats;
    messageStats: MessageStats;
    investorActivity: InvestorActivity;
}

const props = defineProps<Props>();

const emailTypeLabels: Record<string, string> = {
    announcement: 'Announcements',
    financial_report: 'Financial Reports',
    dividend: 'Dividends',
    meeting: 'Meetings',
    message: 'Messages',
};

function formatNumber(num: number): string {
    return new Intl.NumberFormat().format(num);
}

function formatPercent(num: number): string {
    return `${num.toFixed(1)}%`;
}

function formatHours(hours: number): string {
    if (hours < 1) return `${Math.round(hours * 60)} min`;
    if (hours < 24) return `${hours.toFixed(1)} hrs`;
    return `${(hours / 24).toFixed(1)} days`;
}
</script>

<template>
    <AdminLayout title="Investor Analytics">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Investor Analytics</h1>
                    <p class="text-gray-500">Track engagement and communication metrics</p>
                </div>
            </div>

            <!-- Overview Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total Investors -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <UserGroupIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Total Investors</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ formatNumber(investorActivity.total_investors) }}
                            </p>
                        </div>
                    </div>
                    <div class="mt-3 text-sm">
                        <span class="text-green-600">
                            {{ investorActivity.active_last_30_days }} active
                        </span>
                        <span class="text-gray-400"> last 30 days</span>
                    </div>
                </div>

                <!-- Email Delivery Rate -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <EnvelopeIcon class="h-5 w-5 text-green-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Email Delivery Rate</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ formatPercent(emailStats.delivery_rate) }}
                            </p>
                        </div>
                    </div>
                    <div class="mt-3 text-sm text-gray-500">
                        {{ formatNumber(emailStats.sent) }} of {{ formatNumber(emailStats.total) }} sent
                    </div>
                </div>

                <!-- Email Open Rate -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <EyeIcon class="h-5 w-5 text-purple-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Email Open Rate</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ formatPercent(emailStats.open_rate) }}
                            </p>
                        </div>
                    </div>
                    <div class="mt-3 text-sm text-gray-500">
                        {{ formatNumber(emailStats.opened) }} emails opened
                    </div>
                </div>

                <!-- Avg Response Time -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-amber-100 rounded-lg">
                            <ClockIcon class="h-5 w-5 text-amber-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Avg Response Time</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ formatHours(messageStats.avg_response_time_hours) }}
                            </p>
                        </div>
                    </div>
                    <div class="mt-3 text-sm text-gray-500">
                        {{ messageStats.unread }} messages pending
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Email Performance by Type -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Email Performance by Type</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div
                                v-for="(stats, type) in emailStats.by_type"
                                :key="type"
                                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                            >
                                <span class="font-medium text-gray-900">
                                    {{ emailTypeLabels[type] || type }}
                                </span>
                                <div class="flex items-center gap-6 text-sm">
                                    <div class="flex items-center gap-1">
                                        <EyeIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
                                        <span class="text-gray-600">{{ formatPercent(stats.open_rate) }}</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <CursorArrowRaysIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
                                        <span class="text-gray-600">{{ formatPercent(stats.click_rate) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Announcement Engagement -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Announcement Engagement</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="text-center p-4 bg-blue-50 rounded-lg">
                                <p class="text-3xl font-bold text-blue-600">
                                    {{ formatNumber(announcementStats.total) }}
                                </p>
                                <p class="text-sm text-gray-600">Total Announcements</p>
                            </div>
                            <div class="text-center p-4 bg-green-50 rounded-lg">
                                <p class="text-3xl font-bold text-green-600">
                                    {{ formatPercent(announcementStats.avg_read_rate) }}
                                </p>
                                <p class="text-sm text-gray-600">Avg Read Rate</p>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div
                                v-for="(stats, type) in announcementStats.by_type"
                                :key="type"
                                class="flex items-center justify-between"
                            >
                                <span class="text-sm text-gray-600 capitalize">{{ type }}</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-gray-900">{{ stats.count }}</span>
                                    <span class="text-xs text-gray-400">({{ stats.reads }} reads)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Message Statistics -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Message Statistics</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-3 gap-4">
                            <div class="text-center">
                                <p class="text-2xl font-bold text-gray-900">
                                    {{ formatNumber(messageStats.total) }}
                                </p>
                                <p class="text-xs text-gray-500">Total Messages</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-blue-600">
                                    {{ formatNumber(messageStats.from_investors) }}
                                </p>
                                <p class="text-xs text-gray-500">From Investors</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-green-600">
                                    {{ formatNumber(messageStats.to_investors) }}
                                </p>
                                <p class="text-xs text-gray-500">To Investors</p>
                            </div>
                        </div>
                        <div class="mt-6 p-4 bg-amber-50 rounded-lg" v-if="messageStats.unread > 0">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-amber-800">Unread messages requiring attention</span>
                                <span class="font-bold text-amber-600">{{ messageStats.unread }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Investor Activity -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Investor Activity</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="p-4 border border-gray-200 rounded-lg">
                                <p class="text-sm text-gray-500">Active (7 days)</p>
                                <p class="text-2xl font-bold text-gray-900">
                                    {{ investorActivity.active_last_7_days }}
                                </p>
                                <p class="text-xs text-gray-400">
                                    {{ formatPercent((investorActivity.active_last_7_days / investorActivity.total_investors) * 100) }} of total
                                </p>
                            </div>
                            <div class="p-4 border border-gray-200 rounded-lg">
                                <p class="text-sm text-gray-500">Active (30 days)</p>
                                <p class="text-2xl font-bold text-gray-900">
                                    {{ investorActivity.active_last_30_days }}
                                </p>
                                <p class="text-xs text-gray-400">
                                    {{ formatPercent((investorActivity.active_last_30_days / investorActivity.total_investors) * 100) }} of total
                                </p>
                            </div>
                        </div>
                        <!-- Simple activity bars -->
                        <div class="space-y-2">
                            <p class="text-sm font-medium text-gray-700">Login Activity (Last 7 Days)</p>
                            <div class="flex items-end gap-1 h-20">
                                <div
                                    v-for="(day, index) in investorActivity.login_trend"
                                    :key="index"
                                    class="flex-1 bg-blue-500 rounded-t"
                                    :style="{ height: `${Math.max(10, (day.count / Math.max(...investorActivity.login_trend.map(d => d.count))) * 100)}%` }"
                                    :title="`${day.date}: ${day.count} logins`"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
