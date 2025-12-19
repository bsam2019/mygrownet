<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { ref, computed } from 'vue';
import { 
    ArrowLeftIcon,
    PlusIcon,
    PencilIcon,
    TrashIcon,
    CogIcon,
    UserGroupIcon,
    CurrencyDollarIcon,
    CheckIcon,
    XMarkIcon,
    ArrowPathIcon
} from '@heroicons/vue/24/outline';

interface Feature {
    id?: number;
    feature_key: string;
    feature_name: string;
    feature_type: 'boolean' | 'limit' | 'text';
    value_boolean: boolean;
    value_limit: number | null;
    value_text: string | null;
    is_active: boolean;
}

interface Tier {
    id: number | null;
    module_id: string;
    tier_key: string;
    name: string;
    description: string | null;
    price_monthly: number;
    price_annual: number;
    user_limit: number | null;
    storage_limit_mb: number | null;
    is_active: boolean;
    is_default: boolean;
    sort_order: number;
    features: Feature[] | Record<string, any>;
    from_config?: boolean;
}

interface Discount {
    id: number;
    name: string;
    discount_type: string;
    discount_value: number;
    is_active: boolean;
    starts_at: string | null;
    ends_at: string | null;
}


interface Subscriber {
    id: number;
    name: string;
    email: string;
    subscription_tier: string;
    status: string;
    expires_at: string | null;
    created_at: string;
}

interface Stats {
    subscribers_by_tier: Record<string, number>;
    total_subscribers: number;
    monthly_revenue: number;
}

const props = defineProps<{
    moduleId: string;
    module: Record<string, any>;
    tiers: Tier[];
    discounts: Discount[];
    subscribers: Subscriber[];
    stats: Stats;
}>();

const showTierModal = ref(false);
const showFeatureModal = ref(false);
const editingTier = ref<Tier | null>(null);
const editingTierForFeatures = ref<Tier | null>(null);

const tierForm = useForm({
    tier_key: '',
    name: '',
    description: '',
    price_monthly: 0,
    price_annual: 0,
    user_limit: null as number | null,
    storage_limit_mb: null as number | null,
    is_active: true,
    is_default: false,
    sort_order: 0,
});

const featuresForm = useForm({
    features: [] as Feature[],
});

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 0,
    }).format(amount);
};

const openTierModal = (tier?: Tier) => {
    if (tier) {
        editingTier.value = tier;
        tierForm.tier_key = tier.tier_key;
        tierForm.name = tier.name;
        tierForm.description = tier.description || '';
        tierForm.price_monthly = tier.price_monthly;
        tierForm.price_annual = tier.price_annual;
        tierForm.user_limit = tier.user_limit;
        tierForm.storage_limit_mb = tier.storage_limit_mb;
        tierForm.is_active = tier.is_active;
        tierForm.is_default = tier.is_default;
        tierForm.sort_order = tier.sort_order;
    } else {
        editingTier.value = null;
        tierForm.reset();
    }
    showTierModal.value = true;
};

const closeTierModal = () => {
    showTierModal.value = false;
    editingTier.value = null;
    tierForm.reset();
};

const saveTier = () => {
    if (editingTier.value?.id) {
        tierForm.put(route('admin.module-subscriptions.tiers.update', [props.moduleId, editingTier.value.id]), {
            onSuccess: () => closeTierModal(),
        });
    } else {
        tierForm.post(route('admin.module-subscriptions.tiers.store', props.moduleId), {
            onSuccess: () => closeTierModal(),
        });
    }
};

const deleteTier = (tier: Tier) => {
    if (!tier.id) return;
    if (confirm(`Are you sure you want to delete the "${tier.name}" tier?`)) {
        router.delete(route('admin.module-subscriptions.tiers.destroy', [props.moduleId, tier.id]));
    }
};

const openFeatureModal = (tier: Tier) => {
    editingTierForFeatures.value = tier;
    const features = Array.isArray(tier.features) ? tier.features : Object.entries(tier.features || {}).map(([key, value]) => ({
        feature_key: key,
        feature_name: key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()),
        feature_type: typeof value === 'boolean' ? 'boolean' : (typeof value === 'number' ? 'limit' : 'text'),
        value_boolean: typeof value === 'boolean' ? value : false,
        value_limit: typeof value === 'number' ? value : null,
        value_text: typeof value === 'string' ? value : null,
        is_active: true,
    })) as Feature[];
    featuresForm.features = features;
    showFeatureModal.value = true;
};

const closeFeatureModal = () => {
    showFeatureModal.value = false;
    editingTierForFeatures.value = null;
    featuresForm.reset();
};

const addFeature = () => {
    featuresForm.features.push({
        feature_key: '',
        feature_name: '',
        feature_type: 'boolean',
        value_boolean: false,
        value_limit: null,
        value_text: null,
        is_active: true,
    });
};

const removeFeature = (index: number) => {
    featuresForm.features.splice(index, 1);
};

const saveFeatures = () => {
    if (!editingTierForFeatures.value?.id) return;
    featuresForm.put(route('admin.module-subscriptions.tiers.features', [props.moduleId, editingTierForFeatures.value.id]), {
        onSuccess: () => closeFeatureModal(),
    });
};

const seedFromConfig = () => {
    if (confirm('This will create database records from the config file. Existing records will be updated. Continue?')) {
        router.post(route('admin.module-subscriptions.seed-from-config', props.moduleId));
    }
};
</script>

<template>
    <AdminLayout>
        <Head :title="`${module.name} - Subscription Management`" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('admin.module-subscriptions.index')" class="p-2 hover:bg-gray-100 rounded-lg">
                        <ArrowLeftIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ module.name }}</h1>
                        <p class="text-sm text-gray-500">{{ module.description }}</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button
                        @click="seedFromConfig"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        <ArrowPathIcon class="h-5 w-5" aria-hidden="true" />
                        Seed from Config
                    </button>
                    <button
                        @click="openTierModal()"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700"
                    >
                        <PlusIcon class="h-5 w-5" aria-hidden="true" />
                        Add Tier
                    </button>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <UserGroupIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Total Subscribers</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.total_subscribers }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <CurrencyDollarIcon class="h-6 w-6 text-green-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Monthly Revenue</p>
                            <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(stats.monthly_revenue) }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <p class="text-sm text-gray-500 mb-2">Subscribers by Tier</p>
                    <div class="space-y-1">
                        <div v-for="(count, tier) in stats.subscribers_by_tier" :key="tier" class="flex justify-between text-sm">
                            <span class="text-gray-600 capitalize">{{ tier }}</span>
                            <span class="font-medium">{{ count }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tiers -->
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Subscription Tiers</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tier</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monthly</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Annual</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Limits</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Source</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="tier in tiers" :key="tier.tier_key">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900">{{ tier.name }}</div>
                                    <div class="text-sm text-gray-500">{{ tier.tier_key }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ formatCurrency(tier.price_monthly) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ formatCurrency(tier.price_annual) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span v-if="tier.user_limit">{{ tier.user_limit }} users</span>
                                    <span v-else>Unlimited</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span v-if="tier.is_active" class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>
                                    <span v-else class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Inactive</span>
                                    <span v-if="tier.is_default" class="ml-1 px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">Default</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span v-if="tier.from_config" class="text-amber-600">Config</span>
                                    <span v-else class="text-green-600">Database</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openFeatureModal(tier)" class="p-1 text-gray-400 hover:text-gray-600" title="Edit Features">
                                            <CogIcon class="h-5 w-5" aria-hidden="true" />
                                        </button>
                                        <button v-if="tier.id" @click="openTierModal(tier)" class="p-1 text-gray-400 hover:text-blue-600" title="Edit Tier">
                                            <PencilIcon class="h-5 w-5" aria-hidden="true" />
                                        </button>
                                        <button v-if="tier.id" @click="deleteTier(tier)" class="p-1 text-gray-400 hover:text-red-600" title="Delete Tier">
                                            <TrashIcon class="h-5 w-5" aria-hidden="true" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Subscribers -->
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Recent Subscribers</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tier</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expires</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="sub in subscribers" :key="sub.id">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900">{{ sub.name }}</div>
                                    <div class="text-sm text-gray-500">{{ sub.email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 capitalize">{{ sub.subscription_tier }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="sub.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'" class="px-2 py-1 text-xs font-medium rounded-full capitalize">{{ sub.status }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ sub.expires_at || 'Never' }}</td>
                            </tr>
                            <tr v-if="subscribers.length === 0">
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">No subscribers yet</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tier Modal -->
        <div v-if="showTierModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/50" @click="closeTierModal"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6">
                    <h3 class="text-lg font-semibold mb-4">{{ editingTier ? 'Edit Tier' : 'Add Tier' }}</h3>
                    <form @submit.prevent="saveTier" class="space-y-4">
                        <div v-if="!editingTier">
                            <label class="block text-sm font-medium text-gray-700">Tier Key</label>
                            <input v-model="tierForm.tier_key" type="text" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g., basic, pro, enterprise" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <input v-model="tierForm.name" type="text" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea v-model="tierForm.description" rows="2" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Monthly Price (ZMW)</label>
                                <input v-model.number="tierForm.price_monthly" type="number" min="0" step="0.01" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Annual Price (ZMW)</label>
                                <input v-model.number="tierForm.price_annual" type="number" min="0" step="0.01" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">User Limit</label>
                                <input v-model.number="tierForm.user_limit" type="number" min="1" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Unlimited" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Storage (MB)</label>
                                <input v-model.number="tierForm.storage_limit_mb" type="number" min="1" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Unlimited" />
                            </div>
                        </div>
                        <div class="flex items-center gap-6">
                            <label class="flex items-center gap-2">
                                <input v-model="tierForm.is_active" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                <span class="text-sm text-gray-700">Active</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input v-model="tierForm.is_default" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                <span class="text-sm text-gray-700">Default Tier</span>
                            </label>
                        </div>
                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" @click="closeTierModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                            <button type="submit" :disabled="tierForm.processing" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                {{ tierForm.processing ? 'Saving...' : 'Save' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Feature Modal -->
        <div v-if="showFeatureModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/50" @click="closeFeatureModal"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
                    <h3 class="text-lg font-semibold mb-4">Edit Features - {{ editingTierForFeatures?.name }}</h3>
                    <form @submit.prevent="saveFeatures" class="space-y-4">
                        <div v-for="(feature, index) in featuresForm.features" :key="index" class="p-4 bg-gray-50 rounded-lg space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Feature {{ index + 1 }}</span>
                                <button type="button" @click="removeFeature(index)" class="text-red-500 hover:text-red-700">
                                    <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                                </button>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <input v-model="feature.feature_key" type="text" placeholder="Feature key (e.g., products)" class="block w-full rounded-lg border-gray-300 shadow-sm text-sm" />
                                <input v-model="feature.feature_name" type="text" placeholder="Display name" class="block w-full rounded-lg border-gray-300 shadow-sm text-sm" />
                            </div>
                            <div class="flex items-center gap-4">
                                <select v-model="feature.feature_type" class="rounded-lg border-gray-300 shadow-sm text-sm">
                                    <option value="boolean">Boolean (Yes/No)</option>
                                    <option value="limit">Limit (Number)</option>
                                    <option value="text">Text</option>
                                </select>
                                <input v-if="feature.feature_type === 'boolean'" v-model="feature.value_boolean" type="checkbox" class="rounded border-gray-300 text-blue-600" />
                                <input v-else-if="feature.feature_type === 'limit'" v-model.number="feature.value_limit" type="number" placeholder="Limit (empty=unlimited)" class="flex-1 rounded-lg border-gray-300 shadow-sm text-sm" />
                                <input v-else v-model="feature.value_text" type="text" placeholder="Value" class="flex-1 rounded-lg border-gray-300 shadow-sm text-sm" />
                            </div>
                        </div>
                        <button type="button" @click="addFeature" class="w-full py-2 border-2 border-dashed border-gray-300 rounded-lg text-sm text-gray-500 hover:border-gray-400 hover:text-gray-600">
                            <PlusIcon class="h-5 w-5 inline mr-1" aria-hidden="true" /> Add Feature
                        </button>
                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" @click="closeFeatureModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                            <button type="submit" :disabled="featuresForm.processing || !editingTierForFeatures?.id" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                {{ featuresForm.processing ? 'Saving...' : 'Save Features' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
