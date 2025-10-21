<template>
    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h1 class="text-2xl font-bold text-gray-900">Points System Settings</h1>
                <p class="text-gray-600 mt-1">Configure Life Points (LP) and Bonus Points (BP) for activities</p>
            </div>

            <!-- Tabs -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px">
                        <button
                            @click="activeTab = 'points'"
                            :class="[
                                'px-6 py-4 text-sm font-medium border-b-2 transition-colors',
                                activeTab === 'points'
                                    ? 'border-blue-500 text-blue-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                            ]"
                        >
                            Activity Points (LP & BP)
                        </button>
                        <button
                            @click="activeTab = 'requirements'"
                            :class="[
                                'px-6 py-4 text-sm font-medium border-b-2 transition-colors',
                                activeTab === 'requirements'
                                    ? 'border-blue-500 text-blue-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                            ]"
                        >
                            Level Requirements
                        </button>
                        <button
                            @click="activeTab = 'conversion'"
                            :class="[
                                'px-6 py-4 text-sm font-medium border-b-2 transition-colors',
                                activeTab === 'conversion'
                                    ? 'border-blue-500 text-blue-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                            ]"
                        >
                            BP Conversion Rate
                        </button>
                        <button
                            @click="activeTab = 'history'"
                            :class="[
                                'px-6 py-4 text-sm font-medium border-b-2 transition-colors',
                                activeTab === 'history'
                                    ? 'border-blue-500 text-blue-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                            ]"
                        >
                            Rate History
                        </button>
                    </nav>
                </div>

                <!-- Combined Points Tab (LP & BP) -->
                <div v-show="activeTab === 'points'" class="p-6">
                    <!-- Info Cards -->
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="p-4 bg-purple-50 border border-purple-200 rounded-lg">
                            <h3 class="text-sm font-semibold text-purple-900 mb-2">Life Points (LP)</h3>
                            <p class="text-sm text-purple-700">
                                Permanent points that never expire. Determine professional level and unlock benefits.
                            </p>
                        </div>
                        <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                            <h3 class="text-sm font-semibold text-green-900 mb-2">Bonus Points (BP)</h3>
                            <p class="text-sm text-green-700">
                                Reset monthly. Determine your share of the 60% profit pool distribution.
                            </p>
                        </div>
                    </div>

                    <!-- Combined Settings Table -->
                    <div class="space-y-4">
                        <div v-for="(setting, index) in lpSettings" :key="'combined-' + index" class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">{{ setting.name }}</h3>
                                    <p class="text-sm text-gray-600 mt-1">{{ setting.description }}</p>
                                </div>
                                <div class="flex items-center space-x-6">
                                    <!-- LP Input -->
                                    <div class="text-center">
                                        <div class="text-xs font-medium text-purple-600 mb-1">Life Points</div>
                                        <input
                                            v-model.number="setting.lp_value"
                                            type="number"
                                            min="0"
                                            class="w-20 px-2 py-2 border border-purple-300 rounded-md text-center font-bold text-lg bg-purple-50"
                                        />
                                        <div class="text-xs text-gray-500 mt-1">LP</div>
                                    </div>
                                    
                                    <!-- BP Input -->
                                    <div class="text-center">
                                        <div class="text-xs font-medium text-green-600 mb-1">Bonus Points</div>
                                        <input
                                            v-model.number="bpSettings[index].bp_value"
                                            type="number"
                                            min="0"
                                            class="w-20 px-2 py-2 border border-green-300 rounded-md text-center font-bold text-lg bg-green-50"
                                        />
                                        <div class="text-xs text-gray-500 mt-1">BP</div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex flex-col space-y-2">
                                        <button
                                            @click="saveBothPoints(setting, bpSettings[index])"
                                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium transition-colors"
                                        >
                                            Save Both
                                        </button>
                                        <button
                                            @click="toggleBothActivities(setting, bpSettings[index])"
                                            :class="[
                                                'px-4 py-2 rounded-md text-sm font-medium transition-colors',
                                                setting.is_active && bpSettings[index].is_active
                                                    ? 'bg-green-100 text-green-700 hover:bg-green-200'
                                                    : 'bg-red-100 text-red-700 hover:bg-red-200'
                                            ]"
                                        >
                                            {{ setting.is_active && bpSettings[index].is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Level Requirements Tab -->
                <div v-show="activeTab === 'requirements'" class="p-6">
                    <div class="mb-6 p-4 bg-indigo-50 border border-indigo-200 rounded-lg">
                        <h3 class="text-sm font-semibold text-indigo-900 mb-2">LP Requirements for Professional Levels</h3>
                        <p class="text-sm text-indigo-700">
                            Set the minimum Life Points (LP) required for members to qualify for each professional level.
                            Members must earn enough LP to unlock higher levels and subscription tiers.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div v-for="level in levelRequirements" :key="level.id" class="border border-gray-200 rounded-lg p-6 bg-white hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900">{{ level.name }}</h3>
                                    <p class="text-sm text-gray-600 mt-1">Level {{ level.level }}</p>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-gray-500 mb-1">Monthly Subscription</div>
                                    <div class="text-lg font-bold text-blue-600">K{{ level.subscription_price }}</div>
                                </div>
                            </div>
                            
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Minimum LP Required
                                    </label>
                                    <input
                                        v-model.number="level.lp_requirement"
                                        type="number"
                                        min="0"
                                        step="50"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-md text-center font-bold text-2xl"
                                        :class="level.level === 1 ? 'bg-gray-100' : ''"
                                        :disabled="level.level === 1"
                                    />
                                    <p class="text-xs text-gray-500 mt-1 text-center">
                                        {{ level.level === 1 ? 'Associate is the starting level (0 LP)' : 'LP needed to unlock this level' }}
                                    </p>
                                </div>
                                
                                <button
                                    v-if="level.level > 1"
                                    @click="updateLevelRequirement(level)"
                                    class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium transition-colors"
                                >
                                    Save Requirement
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Conversion Rate Tab -->
                <div v-show="activeTab === 'conversion'" class="p-6">
                    <div class="max-w-2xl mx-auto">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                            <div class="text-center">
                                <div class="text-sm text-blue-600 font-medium mb-2">Current Conversion Rate</div>
                                <div class="text-4xl font-bold text-blue-900">
                                    1 BP = K{{ currentRate.bp_to_kwacha_rate }}
                                </div>
                                <div class="text-sm text-blue-600 mt-2">
                                    Effective from {{ formatDate(currentRate.effective_from) }}
                                </div>
                            </div>
                        </div>

                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Set New Conversion Rate</h3>
                            <form @submit.prevent="updateConversionRate" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        New Rate (1 BP = K?)
                                    </label>
                                    <input
                                        v-model.number="newRate.rate"
                                        type="number"
                                        step="0.01"
                                        min="0.01"
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-md text-lg font-bold"
                                        placeholder="0.50"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Notes (Optional)
                                    </label>
                                    <textarea
                                        v-model="newRate.notes"
                                        rows="3"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md"
                                        placeholder="Reason for rate change..."
                                    ></textarea>
                                </div>
                                <button
                                    type="submit"
                                    :disabled="processing"
                                    class="w-full px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 font-medium"
                                >
                                    {{ processing ? 'Updating...' : 'Update Conversion Rate' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Rate History Tab -->
                <div v-show="activeTab === 'history'" class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rate</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Effective From</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Effective To</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="rate in rateHistory" :key="rate.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-lg font-bold">1 BP = K{{ rate.bp_to_kwacha_rate }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ formatDate(rate.effective_from) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ rate.effective_to ? formatDate(rate.effective_to) : 'Present' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="[
                                            'px-2 py-1 text-xs font-medium rounded-full',
                                            rate.is_current
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-gray-100 text-gray-800'
                                        ]">
                                            {{ rate.is_current ? 'Current' : 'Historical' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ rate.notes || '-' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Success Toast -->
            <div v-if="showToast" class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
                {{ toastMessage }}
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface LPSetting {
    id: number;
    activity_type: string;
    name: string;
    description: string;
    lp_value: number;
    is_active: boolean;
}

interface BPSetting {
    id: number;
    activity_type: string;
    name: string;
    description: string;
    bp_value: number;
    is_active: boolean;
}

interface ConversionRate {
    id: number;
    bp_to_kwacha_rate: number;
    effective_from: string;
    effective_to: string | null;
    is_current: boolean;
    notes: string | null;
}

interface LevelRequirement {
    id: number;
    level: number;
    name: string;
    subscription_price: number;
    lp_requirement: number;
}

interface Props {
    lpSettings: LPSetting[];
    bpSettings: BPSetting[];
    currentRate: ConversionRate;
    rateHistory: ConversionRate[];
    levelRequirements: LevelRequirement[];
}

const props = defineProps<Props>();

const activeTab = ref<'points' | 'requirements' | 'conversion' | 'history'>('points');
const processing = ref(false);
const showToast = ref(false);
const toastMessage = ref('');

const newRate = ref({
    rate: props.currentRate.bp_to_kwacha_rate,
    notes: ''
});

const updateLPValue = (setting: LPSetting) => {
    router.post(route('admin.settings.bp.update-lp-activity'), {
        id: setting.id,
        lp_value: setting.lp_value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showToastMessage('LP value updated successfully');
        },
        onError: (errors) => {
            showToastMessage('Failed to update LP value');
        }
    });
};

const toggleLPActivity = (setting: LPSetting) => {
    const newStatus = !setting.is_active;
    router.post(route('admin.settings.bp.toggle-lp-activity'), {
        id: setting.id,
        is_active: newStatus
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showToastMessage(`LP activity ${newStatus ? 'activated' : 'deactivated'}`);
        }
    });
};

const updateBPValue = (setting: BPSetting) => {
    router.post(route('admin.settings.bp.update-activity'), {
        id: setting.id,
        bp_value: setting.bp_value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showToastMessage('BP value updated successfully');
        },
        onError: (errors) => {
            showToastMessage('Failed to update BP value');
        }
    });
};

const toggleActivity = (setting: BPSetting) => {
    const newStatus = !setting.is_active;
    router.post(route('admin.settings.bp.toggle-activity'), {
        id: setting.id,
        is_active: newStatus
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showToastMessage(`BP activity ${newStatus ? 'activated' : 'deactivated'}`);
        }
    });
};

const updateConversionRate = () => {
    processing.value = true;
    router.post(route('admin.settings.bp.update-rate'), newRate.value, {
        preserveState: true,
        onSuccess: () => {
            showToastMessage('Conversion rate updated successfully');
            newRate.value.notes = '';
        },
        onFinish: () => {
            processing.value = false;
        }
    });
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const saveBothPoints = async (lpSetting: LPSetting, bpSetting: BPSetting) => {
    // Save LP
    await updateLPValue(lpSetting);
    // Save BP
    await updateBPValue(bpSetting);
};

const toggleBothActivities = async (lpSetting: LPSetting, bpSetting: BPSetting) => {
    const newStatus = !(lpSetting.is_active && bpSetting.is_active);
    
    // Toggle LP
    router.post(route('admin.settings.bp.toggle-lp-activity'), {
        id: lpSetting.id,
        is_active: newStatus
    }, {
        preserveScroll: true,
        preserveState: true,
    });
    
    // Toggle BP
    router.post(route('admin.settings.bp.toggle-activity'), {
        id: bpSetting.id,
        is_active: newStatus
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showToastMessage(`Activities ${newStatus ? 'activated' : 'deactivated'}`);
        }
    });
};

const updateLevelRequirement = (level: LevelRequirement) => {
    router.post(route('admin.settings.bp.update-level-requirement'), {
        level: level.level,
        lp_requirement: level.lp_requirement
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showToastMessage(`${level.name} LP requirement updated successfully`);
        },
        onError: () => {
            showToastMessage('Failed to update LP requirement');
        }
    });
};

const showToastMessage = (message: string) => {
    toastMessage.value = message;
    showToast.value = true;
    setTimeout(() => {
        showToast.value = false;
    }, 3000);
};
</script>
