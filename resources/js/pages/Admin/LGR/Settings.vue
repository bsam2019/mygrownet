<template>
  <AdminLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-gray-900">LGR System Settings</h1>
          <p class="mt-1 text-sm text-gray-600">
            Configure Loyalty Growth Reward system parameters and tier rates
          </p>
        </div>

        <form @submit.prevent="saveSettings">
          <!-- Tier Rates Section -->
          <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-indigo-50">
              <h2 class="text-lg font-semibold text-gray-900">Tier Daily Rates</h2>
              <p class="text-sm text-gray-600 mt-1">Configure LGR daily rates and max earnings per starter kit tier</p>
            </div>
            <div class="px-6 py-4">
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Lite Tier -->
                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                  <div class="flex items-center gap-2 mb-3">
                    <span class="text-2xl">📦</span>
                    <h3 class="font-semibold text-gray-900">Lite (K300)</h3>
                  </div>
                  <div class="space-y-3">
                    <div>
                      <label class="block text-xs font-medium text-gray-600 mb-1">Daily Rate (K)</label>
                      <input
                        v-model.number="tierRatesForm.lite.daily_rate"
                        type="number"
                        step="0.01"
                        min="0"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-gray-500 focus:border-gray-500"
                      />
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-gray-600 mb-1">Max Earnings (K)</label>
                      <input
                        v-model.number="tierRatesForm.lite.max_earnings"
                        type="number"
                        step="0.01"
                        min="0"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-gray-500 focus:border-gray-500"
                      />
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-gray-600 mb-1">Cycle Days</label>
                      <input
                        v-model.number="tierRatesForm.lite.cycle_days"
                        type="number"
                        step="1"
                        min="1"
                        max="365"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-gray-500 focus:border-gray-500"
                      />
                    </div>
                  </div>
                </div>

                <!-- Basic Tier -->
                <div class="border border-blue-200 rounded-lg p-4 bg-blue-50">
                  <div class="flex items-center gap-2 mb-3">
                    <span class="text-2xl">🎁</span>
                    <h3 class="font-semibold text-gray-900">Basic (K500)</h3>
                  </div>
                  <div class="space-y-3">
                    <div>
                      <label class="block text-xs font-medium text-blue-600 mb-1">Daily Rate (K)</label>
                      <input
                        v-model.number="tierRatesForm.basic.daily_rate"
                        type="number"
                        step="0.01"
                        min="0"
                        class="w-full px-3 py-2 border border-blue-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500"
                      />
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-blue-600 mb-1">Max Earnings (K)</label>
                      <input
                        v-model.number="tierRatesForm.basic.max_earnings"
                        type="number"
                        step="0.01"
                        min="0"
                        class="w-full px-3 py-2 border border-blue-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500"
                      />
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-blue-600 mb-1">Cycle Days</label>
                      <input
                        v-model.number="tierRatesForm.basic.cycle_days"
                        type="number"
                        step="1"
                        min="1"
                        max="365"
                        class="w-full px-3 py-2 border border-blue-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500"
                      />
                    </div>
                  </div>
                </div>

                <!-- Growth Plus Tier -->
                <div class="border border-emerald-200 rounded-lg p-4 bg-emerald-50">
                  <div class="flex items-center gap-2 mb-3">
                    <span class="text-2xl">🚀</span>
                    <h3 class="font-semibold text-gray-900">Growth Plus (K1,000)</h3>
                  </div>
                  <div class="space-y-3">
                    <div>
                      <label class="block text-xs font-medium text-emerald-600 mb-1">Daily Rate (K)</label>
                      <input
                        v-model.number="tierRatesForm.growth_plus.daily_rate"
                        type="number"
                        step="0.01"
                        min="0"
                        class="w-full px-3 py-2 border border-emerald-300 rounded-md text-sm focus:ring-emerald-500 focus:border-emerald-500"
                      />
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-emerald-600 mb-1">Max Earnings (K)</label>
                      <input
                        v-model.number="tierRatesForm.growth_plus.max_earnings"
                        type="number"
                        step="0.01"
                        min="0"
                        class="w-full px-3 py-2 border border-emerald-300 rounded-md text-sm focus:ring-emerald-500 focus:border-emerald-500"
                      />
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-emerald-600 mb-1">Cycle Days</label>
                      <input
                        v-model.number="tierRatesForm.growth_plus.cycle_days"
                        type="number"
                        step="1"
                        min="1"
                        max="365"
                        class="w-full px-3 py-2 border border-emerald-300 rounded-md text-sm focus:ring-emerald-500 focus:border-emerald-500"
                      />
                    </div>
                  </div>
                </div>

                <!-- Pro Tier -->
                <div class="border border-purple-200 rounded-lg p-4 bg-purple-50">
                  <div class="flex items-center gap-2 mb-3">
                    <span class="text-2xl">✨</span>
                    <h3 class="font-semibold text-gray-900">Pro (K2,000)</h3>
                  </div>
                  <div class="space-y-3">
                    <div>
                      <label class="block text-xs font-medium text-purple-600 mb-1">Daily Rate (K)</label>
                      <input
                        v-model.number="tierRatesForm.pro.daily_rate"
                        type="number"
                        step="0.01"
                        min="0"
                        class="w-full px-3 py-2 border border-purple-300 rounded-md text-sm focus:ring-purple-500 focus:border-purple-500"
                      />
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-purple-600 mb-1">Max Earnings (K)</label>
                      <input
                        v-model.number="tierRatesForm.pro.max_earnings"
                        type="number"
                        step="0.01"
                        min="0"
                        class="w-full px-3 py-2 border border-purple-300 rounded-md text-sm focus:ring-purple-500 focus:border-purple-500"
                      />
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-purple-600 mb-1">Cycle Days</label>
                      <input
                        v-model.number="tierRatesForm.pro.cycle_days"
                        type="number"
                        step="1"
                        min="1"
                        max="365"
                        class="w-full px-3 py-2 border border-purple-300 rounded-md text-sm focus:ring-purple-500 focus:border-purple-500"
                      />
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Tier Rates Summary -->
              <div class="mt-6 p-4 bg-gray-100 rounded-lg">
                <h4 class="text-sm font-semibold text-gray-700 mb-2">Rate Summary</h4>
                <div class="grid grid-cols-4 gap-4 text-xs">
                  <div v-for="(rates, tier) in tierRatesForm" :key="tier" class="text-center">
                    <p class="font-medium text-gray-900 capitalize">{{ tier.replace('_', ' ') }}</p>
                    <p class="text-gray-600">K{{ rates.daily_rate }}/day × {{ rates.cycle_days }} days</p>
                    <p class="text-gray-900 font-semibold">= K{{ (rates.daily_rate * rates.cycle_days).toFixed(2) }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- General Settings -->
          <div v-if="settings.general && settings.general.length > 0" class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">General Settings</h2>
            </div>
            <div class="px-6 py-4 space-y-4">
              <div v-for="setting in settings.general" :key="setting.key" class="flex items-start">
                <div class="flex-1">
                  <label :for="setting.key" class="block text-sm font-medium text-gray-700">
                    {{ setting.label }}
                  </label>
                  <p class="text-xs text-gray-500 mt-1">{{ setting.description }}</p>
                </div>
                <div class="ml-4">
                  <input
                    v-if="setting.type === 'boolean'"
                    :id="setting.key"
                    v-model="form[setting.key]"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <input
                    v-else-if="setting.type === 'integer'"
                    :id="setting.key"
                    v-model.number="form[setting.key]"
                    type="number"
                    step="1"
                    class="w-32 px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                  />
                  <input
                    v-else-if="setting.type === 'decimal'"
                    :id="setting.key"
                    v-model.number="form[setting.key]"
                    type="number"
                    step="0.01"
                    class="w-32 px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                  />
                  <input
                    v-else
                    :id="setting.key"
                    v-model="form[setting.key]"
                    type="text"
                    class="w-64 px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Withdrawal Settings -->
          <div v-if="settings.withdrawal && settings.withdrawal.length > 0" class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Withdrawal Settings</h2>
              <p class="text-sm text-gray-600 mt-1">Configure LGR withdrawal parameters</p>
            </div>
            <div class="px-6 py-4 space-y-4">
              <div v-for="setting in settings.withdrawal" :key="setting.key" class="flex items-start">
                <div class="flex-1">
                  <label :for="setting.key" class="block text-sm font-medium text-gray-700">
                    {{ setting.label }}
                  </label>
                  <p class="text-xs text-gray-500 mt-1">{{ setting.description }}</p>
                </div>
                <div class="ml-4">
                  <input
                    v-if="setting.type === 'boolean'"
                    :id="setting.key"
                    v-model="form[setting.key]"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <div v-else class="flex items-center">
                    <input
                      :id="setting.key"
                      v-model.number="form[setting.key]"
                      type="number"
                      step="0.01"
                      class="w-32 px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                    />
                    <span v-if="setting.key.includes('percentage')" class="ml-2 text-sm text-gray-600">%</span>
                    <span v-else class="ml-2 text-sm text-gray-600">K</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Transfer Settings -->
          <div v-if="settings.transfer && settings.transfer.length > 0" class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Transfer Settings</h2>
              <p class="text-sm text-gray-600 mt-1">Configure LGR to Wallet transfer parameters</p>
            </div>
            <div class="px-6 py-4 space-y-4">
              <div v-for="setting in settings.transfer" :key="setting.key" class="flex items-start">
                <div class="flex-1">
                  <label :for="setting.key" class="block text-sm font-medium text-gray-700">
                    {{ setting.label }}
                  </label>
                  <p class="text-xs text-gray-500 mt-1">{{ setting.description }}</p>
                </div>
                <div class="ml-4">
                  <input
                    v-if="setting.type === 'boolean'"
                    :id="setting.key"
                    v-model="form[setting.key]"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <div v-else class="flex items-center">
                    <input
                      :id="setting.key"
                      v-model.number="form[setting.key]"
                      type="number"
                      step="0.01"
                      class="w-32 px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                    />
                    <span v-if="setting.key.includes('percentage')" class="ml-2 text-sm text-gray-600">%</span>
                    <span v-else class="ml-2 text-sm text-gray-600">K</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Award Settings -->
          <div v-if="settings.awards && settings.awards.length > 0" class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Manual Award Settings</h2>
              <p class="text-sm text-gray-600 mt-1">Configure manual LGR award limits</p>
            </div>
            <div class="px-6 py-4 space-y-4">
              <div v-for="setting in settings.awards" :key="setting.key" class="flex items-start">
                <div class="flex-1">
                  <label :for="setting.key" class="block text-sm font-medium text-gray-700">
                    {{ setting.label }}
                  </label>
                  <p class="text-xs text-gray-500 mt-1">{{ setting.description }}</p>
                </div>
                <div class="ml-4 flex items-center">
                  <input
                    :id="setting.key"
                    v-model.number="form[setting.key]"
                    type="number"
                    step="0.01"
                    class="w-32 px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                  />
                  <span class="ml-2 text-sm text-gray-600">K</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Save Button -->
          <div class="flex justify-end">
            <button
              type="submit"
              :disabled="processing"
              class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-lg shadow-md hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
            >
              <span v-if="processing">Saving...</span>
              <span v-else>Save All Settings</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

interface Setting {
  key: string;
  value: any;
  type: string;
  label: string;
  description: string;
}

interface Settings {
  general?: Setting[];
  withdrawal?: Setting[];
  transfer?: Setting[];
  awards?: Setting[];
}

interface TierRate {
  daily_rate: number;
  max_earnings: number;
  cycle_days: number;
}

interface TierRates {
  lite: TierRate;
  basic: TierRate;
  growth_plus: TierRate;
  pro: TierRate;
}

const props = defineProps<{
  settings: Settings;
  tierRates: TierRates;
}>();

const processing = ref(false);

// Initialize form with current settings
const form = reactive<Record<string, any>>({});

// Populate form with current values
Object.entries(props.settings).forEach(([group, groupSettings]) => {
  if (Array.isArray(groupSettings)) {
    groupSettings.forEach((setting: Setting) => {
      form[setting.key] = setting.value;
    });
  }
});

// Initialize tier rates form
const tierRatesForm = reactive<TierRates>({
  lite: { ...props.tierRates.lite },
  basic: { ...props.tierRates.basic },
  growth_plus: { ...props.tierRates.growth_plus },
  pro: { ...props.tierRates.pro },
});

const saveSettings = () => {
  processing.value = true;

  const settingsArray = Object.entries(form).map(([key, value]) => ({
    key,
    value: value?.toString() ?? '',
  }));

  router.post(route('admin.lgr.settings.update'), {
    settings: settingsArray,
    tierRates: tierRatesForm,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      processing.value = false;
    },
    onError: () => {
      processing.value = false;
    },
  });
};
</script>
