<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import {
    LightBulbIcon,
    ExclamationCircleIcon,
    BanknotesIcon,
    ChartBarIcon,
    CurrencyDollarIcon,
} from '@heroicons/vue/24/outline';

const page = usePage();
const props = computed(() => page.props as any);
const recommendations = computed(() => props.value.recommendations || {});

const allItems = computed(() => {
    const r = recommendations.value;
    const items: any[] = [];
    (r.alerts || []).forEach((a: any) => items.push({ ...a, section: 'alerts' }));
    (r.cash_flow_warnings || []).forEach((w: any) => items.push({ ...w, section: 'cash_flow_warnings' }));
    (r.budget_alerts || []).forEach((b: any) => items.push({ ...b, section: 'budget_alerts' }));
    (r.anomaly_notifications || []).forEach((n: any) => items.push({ ...n, section: 'anomaly_notifications' }));
    (r.suggestions || []).forEach((s: any) => items.push({ ...s, section: 'suggestions' }));
    return items;
});

const criticalItems = computed(() => allItems.value.filter((i: any) => i.severity === 'critical'));
const warningItems = computed(() => allItems.value.filter((i: any) => i.severity === 'warning' || i.severity === 'high'));
const infoItems = computed(() => allItems.value.filter((i: any) => i.severity === 'info'));

const severityIcon = (severity: string) => {
    if (severity === 'critical') return ExclamationCircleIcon;
    if (severity === 'warning' || severity === 'high') return ExclamationCircleIcon;
    return LightBulbIcon;
};

const severityBg = (severity: string) => {
    if (severity === 'critical') return 'bg-red-50 border-red-200';
    if (severity === 'warning' || severity === 'high') return 'bg-amber-50 border-amber-200';
    return 'bg-blue-50 border-blue-200';
};

const severityDot = (severity: string) => {
    if (severity === 'critical') return 'bg-red-500';
    if (severity === 'warning' || severity === 'high') return 'bg-amber-500';
    return 'bg-blue-500';
};
</script>

<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Automated Recommendations</h1>
                <p class="text-gray-500 text-sm">Actionable insights based on your financial data</p>
            </div>

            <!-- Summary Counts -->
            <div class="grid grid-cols-3 gap-3 mb-6">
                <div class="bg-red-50 rounded-xl p-3 border border-red-200">
                    <p class="text-2xl font-bold text-red-700">{{ criticalItems.length }}</p>
                    <p class="text-xs text-red-600">Critical</p>
                </div>
                <div class="bg-amber-50 rounded-xl p-3 border border-amber-200">
                    <p class="text-2xl font-bold text-amber-700">{{ warningItems.length }}</p>
                    <p class="text-xs text-amber-600">Warnings</p>
                </div>
                <div class="bg-blue-50 rounded-xl p-3 border border-blue-200">
                    <p class="text-2xl font-bold text-blue-700">{{ infoItems.length }}</p>
                    <p class="text-xs text-blue-600">Info</p>
                </div>
            </div>

            <!-- Critical Alerts -->
            <div v-if="criticalItems.length > 0" class="mb-6">
                <h2 class="text-sm font-semibold text-red-700 mb-3">Critical</h2>
                <div class="space-y-2">
                    <div v-for="(item, i) in criticalItems" :key="'c' + i"
                        class="flex items-start gap-3 p-4 rounded-xl border bg-red-50 border-red-200"
                    >
                        <div class="w-2 h-2 rounded-full bg-red-500 mt-2 flex-shrink-0"></div>
                        <div>
                            <p class="font-semibold text-gray-900 text-sm">{{ item.title }}</p>
                            <p class="text-gray-600 text-xs mt-1">{{ item.message }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Warning Alerts -->
            <div v-if="warningItems.length > 0" class="mb-6">
                <h2 class="text-sm font-semibold text-amber-700 mb-3">Warnings</h2>
                <div class="space-y-2">
                    <div v-for="(item, i) in warningItems" :key="'w' + i"
                        class="flex items-start gap-3 p-4 rounded-xl border bg-amber-50 border-amber-200"
                    >
                        <div class="w-2 h-2 rounded-full bg-amber-500 mt-2 flex-shrink-0"></div>
                        <div>
                            <p class="font-semibold text-gray-900 text-sm">{{ item.title }}</p>
                            <p class="text-gray-600 text-xs mt-1">{{ item.message }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info / Suggestions -->
            <div v-if="infoItems.length > 0" class="mb-6">
                <h2 class="text-sm font-semibold text-blue-700 mb-3">Suggestions</h2>
                <div class="space-y-2">
                    <div v-for="(item, i) in infoItems" :key="'i' + i"
                        class="flex items-start gap-3 p-4 rounded-xl border bg-blue-50 border-blue-200"
                    >
                        <div class="w-2 h-2 rounded-full bg-blue-500 mt-2 flex-shrink-0"></div>
                        <div>
                            <p class="font-semibold text-gray-900 text-sm">{{ item.title }}</p>
                            <p class="text-gray-600 text-xs mt-1">{{ item.message }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="allItems.length === 0" class="bg-white rounded-2xl shadow-sm p-8 text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-emerald-100 flex items-center justify-center">
                    <svg class="w-8 h-8 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-1">All Clear</h3>
                <p class="text-sm text-gray-500">No recommendations at this time. Your financials look healthy.</p>
            </div>
        </div>
    </GrowFinanceLayout>
</template>
