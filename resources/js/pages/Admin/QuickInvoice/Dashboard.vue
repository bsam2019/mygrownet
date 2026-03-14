<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { 
    DocumentTextIcon, 
    UsersIcon, 
    ChartBarIcon,
    CogIcon,
    EyeIcon,
    CalendarIcon,
    TrendingUpIcon,
    CurrencyDollarIcon,
    CheckCircleIcon,
    XCircleIcon
} from '@heroicons/vue/24/outline';
import { computed, ref } from 'vue';

interface Stats {
    total_documents: number;
    unique_users: number;
    unique_sessions: number;
    by_type: Record<string, number>;
    by_template: Record<string, number>;
    by_source: Record<string, number>;
}

interface SubscriptionStats {
    total_users: number;
    active_subscriptions: number;
    by_tier: Record<string, number>;
}

interface Activity {
    id: string;
    user_name: string;
    document_type: string;
    template_used: string;
    integration_source: string;
    created_at: string;
}

interface MonetizationSettings {
    usage_limits_enabled: boolean;
    free_tier_limit: number;
    require_subscription: boolean;
    grace_period_days: number;
}

interface Tier {
    id: number;
    name: string;
    formatted_price: string;
    documents_per_month: number;
}

const props = defineProps<{
    stats: {
        today: Stats;
        week: Stats;
        month: Stats;
    };
    subscriptionStats: SubscriptionStats;
    recentActivity: Activity[];
    monetizationSettings: MonetizationSettings;
    tiers: Tier[];
}>();

const showSettings = ref(false);
const isUpdatingSettings = ref(false);
const isTogglingLimits = ref(false);

const settings = ref({
    usage_limits_enabled: props.monetizationSettings.usage_limits_enabled,
    free_tier_limit: props.monetizationSettings.free_tier_limit,
    require_subscription: props.monetizationSettings.require_subscription,
    grace_period_days: props.monetizationSettings.grace_period_days,
});

const formatNumber = (num: number) => num.toLocaleString();

const toggleUsageLimits = async () => {
    if (isTogglingLimits.value) return;
    
    isTogglingLimits.value = true;
    
    try {
        await router.post(route('admin.quick-invoice.toggle-usage-limits'), {
            enabled: !settings.value.usage_limits_enabled
        });
        
        settings.value.usage_limits_enabled = !settings.value.usage_limits_enabled;
    } finally {
        isTogglingLimits.value = false;
    }
};

const updateSettings = async () => {
    if (isUpdatingSettings.value) return;
    
    isUpdatingSettings.value = true;
    
    try {
        await router.post(route('admin.quick-invoice.monetization-settings.update'), settings.value);
        showSettings.value = false;
    } finally {
        isUpdatingSettings.value = false;
    }
};

const getDocumentTypeIcon = (type: string) => {
    switch (type) {
        case 'invoice': return DocumentTextIcon;
        case 'quotation': return DocumentTextIcon;
        case 'receipt': return DocumentTextIcon;
        case 'delivery_note': return DocumentTextIcon;
        default: return DocumentTextIcon;
    }
};

const getSourceBadgeColor = (source: string) => {
    switch (source) {
        case 'standalone': return 'bg-blue-100 text-blue-800';
        case 'cms': return 'bg-green-100 text-green-800';
        case 'api': return 'bg-purple-100 text-purple-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Quick Invoice Admin Dashboard" />

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Quick Invoice Admin</h1>
                            <p class="text-gray-600">Monitor usage, manage subscriptions, and control monetization</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <!-- Usage Limits Toggle -->
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-medium text-gray-700">Usage Limits</span>
                                <button
                                    @click="toggleUsageLimits"
                                    :disabled="isTogglingLimits"
                                    :class="[
                                        'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                                        settings.usage_limits_enabled ? 'bg-blue-600' : 'bg-gray-200',
                                        isTogglingLimits ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
                                    ]"
                                >
                                    <span
                                        :class="[
                                            'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                                            settings.usage_limits_enabled ? 'translate-x-6' : 'translate-x-1'
                                        ]"
                                    />
                                </button>
                                <CheckCircleIcon v-if="settings.usage_limits_enabled" class="h-5 w-5 text-green-500" />
                                <XCircleIcon v-else class="h-5 w-5 text-gray-400" />
                            </div>
                            
                            <button
                                @click="showSettings = true"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                            >
                                <CogIcon class="h-4 w-4" />
                                Settings
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Today's Documents -->
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <DocumentTextIcon class="h-6 w-6 text-blue-600" />
                            </div>
                            <span class="text-xs text-gray-500">Today</span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900">{{ formatNumber(stats.today.total_documents) }}</p>
                        <p class="text-sm text-gray-600">Documents Created</p>
                    </div>

                    <!-- Active Users -->
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <UsersIcon class="h-6 w-6 text-green-600" />
                            </div>
                            <span class="text-xs text-gray-500">This Week</span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900">{{ formatNumber(stats.week.unique_users) }}</p>
                        <p class="text-sm text-gray-600">Active Users</p>
                    </div>

                    <!-- Monthly Documents -->
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-2 bg-purple-100 rounded-lg">
                                <ChartBarIcon class="h-6 w-6 text-purple-600" />
                            </div>
                            <span class="text-xs text-gray-500">This Month</span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900">{{ formatNumber(stats.month.total_documents) }}</p>
                        <p class="text-sm text-gray-600">Total Documents</p>
                    </div>

                    <!-- Subscriptions -->
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-2 bg-amber-100 rounded-lg">
                                <CurrencyDollarIcon class="h-6 w-6 text-amber-600" />
                            </div>
                            <span class="text-xs text-gray-500">Active</span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900">{{ formatNumber(subscriptionStats.active_subscriptions) }}</p>
                        <p class="text-sm text-gray-600">Subscriptions</p>
                    </div>
                </div>

                <!-- Charts and Tables -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Document Types -->
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Document Types (This Month)</h3>
                        <div class="space-y-3">
                            <div v-for="(count, type) in stats.month.by_type" :key="type" class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <component :is="getDocumentTypeIcon(type)" class="h-5 w-5 text-gray-600" />
                                    <span class="text-sm font-medium text-gray-700 capitalize">{{ type.replace('_', ' ') }}</span>
                                </div>
                                <span class="text-sm font-semibold text-gray-900">{{ formatNumber(count) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Subscription Tiers -->
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Subscription Distribution</h3>
                        <div class="space-y-3">
                            <div v-for="(count, tier) in subscriptionStats.by_tier" :key="tier" class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">{{ tier }}</span>
                                <span class="text-sm font-semibold text-gray-900">{{ formatNumber(count) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Activity</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Document</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Template</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="activity in recentActivity" :key="activity.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ activity.user_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 capitalize">
                                        {{ activity.document_type.replace('_', ' ') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 capitalize">
                                        {{ activity.template_used }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="['inline-flex px-2 py-1 text-xs font-medium rounded-full', getSourceBadgeColor(activity.integration_source)]">
                                            {{ activity.integration_source }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ activity.created_at }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings Modal -->
        <div v-if="showSettings" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-6 w-full max-w-md mx-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Monetization Settings</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Free Tier Document Limit</label>
                        <input
                            v-model.number="settings.free_tier_limit"
                            type="number"
                            min="0"
                            max="1000"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        />
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Grace Period (Days)</label>
                        <input
                            v-model.number="settings.grace_period_days"
                            type="number"
                            min="0"
                            max="30"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        />
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <input
                            v-model="settings.require_subscription"
                            type="checkbox"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        />
                        <label class="text-sm font-medium text-gray-700">Require Subscription for Premium Features</label>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 mt-6">
                    <button
                        @click="showSettings = false"
                        class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200"
                    >
                        Cancel
                    </button>
                    <button
                        @click="updateSettings"
                        :disabled="isUpdatingSettings"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ isUpdatingSettings ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
  