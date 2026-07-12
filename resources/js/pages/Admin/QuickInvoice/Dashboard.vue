<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { 
    DocumentTextIcon, 
    UsersIcon, 
    ChartBarIcon,
    CogIcon,
    CurrencyDollarIcon,
    CheckCircleIcon,
    XCircleIcon,
    ClockIcon,
    ExclamationTriangleIcon,
    SparklesIcon,
    BanknotesIcon,
} from '@heroicons/vue/24/outline';
import { ref } from 'vue';

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

interface BillingStats {
    total_subscriptions: number;
    on_trial: number;
    paid: number;
    free: number;
    expired: number;
    trial_expired: number;
    by_tier: Record<string, number>;
    total_revenue: number;
}

interface Tier {
    id: number;
    name: string;
    formatted_price: string;
    documents_per_month: number;
}

const props = defineProps<{
    stats: { today: Stats; week: Stats; month: Stats; };
    subscriptionStats: SubscriptionStats;
    recentActivity: Activity[];
    monetizationSettings: MonetizationSettings;
    trialSettings: { trial_days: number; tier_on_trial: string; require_payment_after_trial: boolean; };
    billingStats: BillingStats;
    tiers: Tier[];
}>();

const showSettings = ref(false);
const showTrialSettings = ref(false);
const isUpdatingSettings = ref(false);
const isUpdatingTrial = ref(false);
const isTogglingLimits = ref(false);

const settings = ref({
    usage_limits_enabled: props.monetizationSettings.usage_limits_enabled,
    free_tier_limit: props.monetizationSettings.free_tier_limit,
    require_subscription: props.monetizationSettings.require_subscription,
    grace_period_days: props.monetizationSettings.grace_period_days,
});

const trialSettings = ref({
    trial_days: props.trialSettings.trial_days,
    tier_on_trial: props.trialSettings.tier_on_trial,
    require_payment_after_trial: props.trialSettings.require_payment_after_trial,
});

const formatNumber = (num: number) => num.toLocaleString();
const formatMoney = (num: number) => 'K' + num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const toggleUsageLimits = async () => {
    if (isTogglingLimits.value) return;
    isTogglingLimits.value = true;
    try {
        await router.post(route('admin.quick-invoice.toggle-usage-limits'), { enabled: !settings.value.usage_limits_enabled });
        settings.value.usage_limits_enabled = !settings.value.usage_limits_enabled;
    } finally { isTogglingLimits.value = false; }
};

const updateSettings = async () => {
    if (isUpdatingSettings.value) return;
    isUpdatingSettings.value = true;
    try {
        await router.post(route('admin.quick-invoice.monetization-settings.update'), settings.value);
        showSettings.value = false;
    } finally { isUpdatingSettings.value = false; }
};

const updateTrialSettings = async () => {
    if (isUpdatingTrial.value) return;
    isUpdatingTrial.value = true;
    try {
        await router.post(route('admin.quick-invoice.trial-settings.update'), trialSettings.value);
        showTrialSettings.value = false;
    } finally { isUpdatingTrial.value = false; }
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
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Quick Invoice Admin</h1>
                            <p class="text-gray-600">Monitor usage, manage subscriptions, and control monetization</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <button @click="showTrialSettings = true"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">
                                <ClockIcon class="h-4 w-4" />
                                Trial Settings
                            </button>
                            <button @click="showSettings = true"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                                <CogIcon class="h-4 w-4" />
                                Monetization
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Usage Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-2 bg-blue-100 rounded-lg"><DocumentTextIcon class="h-6 w-6 text-blue-600" /></div>
                            <span class="text-xs text-gray-500">Today</span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900">{{ formatNumber(stats.today.total_documents) }}</p>
                        <p class="text-sm text-gray-600">Documents Created</p>
                    </div>
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-2 bg-green-100 rounded-lg"><UsersIcon class="h-6 w-6 text-green-600" /></div>
                            <span class="text-xs text-gray-500">This Week</span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900">{{ formatNumber(stats.week.unique_users) }}</p>
                        <p class="text-sm text-gray-600">Active Users</p>
                    </div>
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-2 bg-purple-100 rounded-lg"><ChartBarIcon class="h-6 w-6 text-purple-600" /></div>
                            <span class="text-xs text-gray-500">This Month</span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900">{{ formatNumber(stats.month.total_documents) }}</p>
                        <p class="text-sm text-gray-600">Total Documents</p>
                    </div>
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-2 bg-amber-100 rounded-lg"><CurrencyDollarIcon class="h-6 w-6 text-amber-600" /></div>
                            <span class="text-xs text-gray-500">Total Revenue</span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900">{{ formatMoney(billingStats.total_revenue) }}</p>
                        <p class="text-sm text-gray-600">{{ billingStats.paid }} Paid Subscriptions</p>
                    </div>
                </div>

                <!-- Billing Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-2 bg-blue-100 rounded-lg"><SparklesIcon class="h-6 w-6 text-blue-600" /></div>
                            <span class="text-xs text-gray-500">On Trial</span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900">{{ formatNumber(billingStats.on_trial) }}</p>
                        <p class="text-sm text-gray-600">Users in trial period</p>
                    </div>
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-2 bg-green-100 rounded-lg"><CheckCircleIcon class="h-6 w-6 text-green-600" /></div>
                            <span class="text-xs text-gray-500">Paid</span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900">{{ formatNumber(billingStats.paid) }}</p>
                        <p class="text-sm text-gray-600">Active paid subscriptions</p>
                    </div>
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-2 bg-red-100 rounded-lg"><ExclamationTriangleIcon class="h-6 w-6 text-red-600" /></div>
                            <span class="text-xs text-gray-500">Trial Expired</span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900">{{ formatNumber(billingStats.trial_expired) }}</p>
                        <p class="text-sm text-gray-600">Need to upgrade or downgrade</p>
                    </div>
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-2 bg-gray-100 rounded-lg"><UsersIcon class="h-6 w-6 text-gray-600" /></div>
                            <span class="text-xs text-gray-500">Free</span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900">{{ formatNumber(billingStats.free) }}</p>
                        <p class="text-sm text-gray-600">On free tier</p>
                    </div>
                </div>

                <!-- Charts and Tables -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Document Types (This Month)</h3>
                        <div class="space-y-3">
                            <div v-for="(count, type) in stats.month.by_type" :key="type" class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700 capitalize">{{ type.replace('_', ' ') }}</span>
                                <span class="text-sm font-semibold text-gray-900">{{ formatNumber(count) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Subscription Distribution</h3>
                        <div class="space-y-3">
                            <div v-for="(count, tier) in subscriptionStats.by_tier" :key="tier" class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">{{ tier }}</span>
                                <span class="text-sm font-semibold text-gray-900">{{ formatNumber(count) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Billing Status</h3>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Trial</span>
                                <span class="text-sm font-semibold text-blue-600">{{ formatNumber(billingStats.on_trial) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Paid</span>
                                <span class="text-sm font-semibold text-green-600">{{ formatNumber(billingStats.paid) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Free</span>
                                <span class="text-sm font-semibold text-gray-600">{{ formatNumber(billingStats.free) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Trial Expired</span>
                                <span class="text-sm font-semibold text-red-600">{{ formatNumber(billingStats.trial_expired) }}</span>
                            </div>
                            <div class="border-t pt-3 flex items-center justify-between">
                                <span class="text-sm font-bold text-gray-900">Total</span>
                                <span class="text-sm font-bold text-gray-900">{{ formatNumber(billingStats.total_subscriptions) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Activity</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Document</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Template</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Source</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="activity in recentActivity" :key="activity.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ activity.user_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 capitalize">{{ activity.document_type.replace('_', ' ') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 capitalize">{{ activity.template_used }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="['inline-flex px-2 py-1 text-xs font-medium rounded-full', getSourceBadgeColor(activity.integration_source)]">{{ activity.integration_source }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ activity.created_at }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monetization Settings Modal -->
        <div v-if="showSettings" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-6 w-full max-w-md mx-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Monetization Settings</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Free Tier Document Limit</label>
                        <input v-model.number="settings.free_tier_limit" type="number" min="0" max="1000" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Grace Period (Days)</label>
                        <input v-model.number="settings.grace_period_days" type="number" min="0" max="30" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div class="flex items-center gap-3">
                        <input v-model="settings.require_subscription" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" />
                        <label class="text-sm font-medium text-gray-700">Require Subscription for Premium Features</label>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="showSettings = false" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</button>
                    <button @click="updateSettings" :disabled="isUpdatingSettings" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">{{ isUpdatingSettings ? 'Saving...' : 'Save Changes' }}</button>
                </div>
            </div>
        </div>

        <!-- Trial Settings Modal -->
        <div v-if="showTrialSettings" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-6 w-full max-w-md mx-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Trial Settings</h3>
                <p class="text-sm text-gray-500 mb-4">Configure the free trial period for new Quick Invoice users.</p>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Trial Duration (Days)</label>
                        <input v-model.number="trialSettings.trial_days" type="number" min="0" max="365" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
                        <p class="text-xs text-gray-400 mt-1">Set to 0 to disable trials</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Trial Tier</label>
                        <select v-model="trialSettings.tier_on_trial" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option v-for="tier in tiers.filter(t => !t.formatted_price.includes('Free'))" :key="tier.id" :value="tier.name">{{ tier.name }} ({{ tier.formatted_price }}/month)</option>
                        </select>
                        <p class="text-xs text-gray-400 mt-1">New users get this tier for free during trial</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <input v-model="trialSettings.require_payment_after_trial" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" />
                        <label class="text-sm font-medium text-gray-700">Require payment after trial ends</label>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="showTrialSettings = false" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</button>
                    <button @click="updateTrialSettings" :disabled="isUpdatingTrial" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50">{{ isUpdatingTrial ? 'Saving...' : 'Save Changes' }}</button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
