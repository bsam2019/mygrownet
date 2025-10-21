<template>
    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h1 class="text-2xl font-bold text-gray-900">Bonus Points Settings</h1>
                <p class="text-gray-600 mt-1">Configure BP values for activities and set conversion rates</p>
            </div>

            <!-- Tabs -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px">
                        <button
                            @click="activeTab = 'activities'"
                            :class="[
                                'px-6 py-4 text-sm font-medium border-b-2 transition-colors',
                                activeTab === 'activities'
                                    ? 'border-blue-500 text-blue-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                            ]"
                        >
                            Activity BP Values
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
                            Conversion Rate
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

                <!-- Activity BP Values Tab -->
                <div v-show="activeTab === 'activities'" class="p-6">
                    <div class="space-y-4">
                        <div v-for="setting in bpSettings" :key="setting.id" class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">{{ setting.name }}</h3>
                                    <p class="text-sm text-gray-600 mt-1">{{ setting.description }}</p>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <div class="text-right">
                                        <input
                                            v-model.number="setting.bp_value"
                                            type="number"
                                            min="0"
                                            class="w-24 px-3 py-2 border border-gray-300 rounded-md text-center font-bold text-lg"
                                            @change="updateBPValue(setting)"
                                        />
                                        <div class="text-xs text-gray-500 mt-1">BP</div>
                                    </div>
                                    <button
                                        @click="toggleActivity(setting)"
                                        :class="[
                                            'px-4 py-2 rounded-md text-sm font-medium transition-colors',
                                            setting.is_active
                                                ? 'bg-green-100 text-green-700 hover:bg-green-200'
                                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                        ]"
                                    >
                                        {{ setting.is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </div>
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

interface Props {
    bpSettings: BPSetting[];
    currentRate: ConversionRate;
    rateHistory: ConversionRate[];
}

const props = defineProps<Props>();

const activeTab = ref<'activities' | 'conversion' | 'history'>('activities');
const processing = ref(false);
const showToast = ref(false);
const toastMessage = ref('');

const newRate = ref({
    rate: props.currentRate.bp_to_kwacha_rate,
    notes: ''
});

const updateBPValue = (setting: BPSetting) => {
    router.post(route('admin.settings.bp.update-activity'), {
        id: setting.id,
        bp_value: setting.bp_value
    }, {
        preserveState: true,
        onSuccess: () => {
            showToastMessage('BP value updated successfully');
        }
    });
};

const toggleActivity = (setting: BPSetting) => {
    router.post(route('admin.settings.bp.toggle-activity'), {
        id: setting.id,
        is_active: !setting.is_active
    }, {
        preserveState: true,
        onSuccess: () => {
            setting.is_active = !setting.is_active;
            showToastMessage(`Activity ${setting.is_active ? 'activated' : 'deactivated'}`);
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

const showToastMessage = (message: string) => {
    toastMessage.value = message;
    showToast.value = true;
    setTimeout(() => {
        showToast.value = false;
    }, 3000);
};
</script>
