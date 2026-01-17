<template>
  <AdminLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-900">
        Commission Settings
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
        <!-- Stats Cards -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">
            <p class="text-sm font-medium text-gray-500">Paid This Month</p>
            <p class="mt-2 text-2xl font-bold text-green-600">K{{ formatNumber(stats.total_paid_this_month) }}</p>
          </div>
          <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">
            <p class="text-sm font-medium text-gray-500">Commissions This Month</p>
            <p class="mt-2 text-2xl font-bold text-blue-600">{{ stats.commissions_this_month }}</p>
          </div>
          <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">
            <p class="text-sm font-medium text-gray-500">Non-Kit Commissions</p>
            <p class="mt-2 text-2xl font-bold text-amber-600">{{ stats.non_kit_commissions_this_month }}</p>
          </div>
          <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">
            <p class="text-sm font-medium text-gray-500">Average Commission</p>
            <p class="mt-2 text-2xl font-bold text-purple-600">K{{ formatNumber(stats.average_commission) }}</p>
          </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
          <!-- Settings Form -->
          <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Commission Configuration</h3>
              
              <form @submit.prevent="saveSettings" class="space-y-6">
                <!-- Enable/Disable -->
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                  <div>
                    <label class="font-medium text-gray-900">Enable Commissions</label>
                    <p class="text-sm text-gray-500">Turn MLM commissions on or off</p>
                  </div>
                  <button
                    type="button"
                    @click="form.enabled = !form.enabled"
                    :class="[
                      'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2',
                      form.enabled ? 'bg-blue-600' : 'bg-gray-200'
                    ]"
                  >
                    <span
                      :class="[
                        'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                        form.enabled ? 'translate-x-5' : 'translate-x-0'
                      ]"
                    />
                  </button>
                </div>

                <!-- Base Percentage -->
                <div>
                  <label class="block text-sm font-medium text-gray-700">
                    Commission Base Percentage
                  </label>
                  <p class="text-xs text-gray-500 mb-2">
                    Percentage of purchase price used as commission base (e.g., 50% means K500 purchase = K250 base)
                  </p>
                  <div class="flex items-center gap-2">
                    <input
                      v-model.number="form.base_percentage"
                      type="number"
                      min="1"
                      max="100"
                      step="1"
                      class="block w-24 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    />
                    <span class="text-gray-500">%</span>
                  </div>
                </div>

                <!-- Non-Kit Multiplier -->
                <div>
                  <label class="block text-sm font-medium text-gray-700">
                    Non-Kit Member Multiplier
                  </label>
                  <p class="text-xs text-gray-500 mb-2">
                    Percentage of commission earned by members without starter kit (0% = no commission, 50% = half commission)
                  </p>
                  <div class="flex items-center gap-2">
                    <input
                      v-model.number="form.non_kit_multiplier_percentage"
                      type="number"
                      min="0"
                      max="100"
                      step="1"
                      class="block w-24 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    />
                    <span class="text-gray-500">%</span>
                  </div>
                </div>

                <!-- Level Rates -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Commission Rates by Level
                  </label>
                  <p class="text-xs text-gray-500 mb-3">
                    Total: {{ totalPayoutPercentage }}% (applied to commission base)
                  </p>
                  <div class="space-y-2">
                    <div
                      v-for="level in 7"
                      :key="level"
                      class="flex items-center gap-3"
                    >
                      <span class="w-28 text-sm text-gray-600">
                        Level {{ level }} ({{ levelNames[level] }})
                      </span>
                      <input
                        v-model.number="form.level_rates[level]"
                        type="number"
                        min="0"
                        max="50"
                        step="0.5"
                        class="block w-20 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                      />
                      <span class="text-gray-500 text-sm">%</span>
                    </div>
                  </div>
                </div>

                <!-- Save Button -->
                <div class="flex items-center justify-between pt-4 border-t">
                  <p v-if="totalPayoutPercentage > 100" class="text-sm text-red-600">
                    ⚠️ Total payout exceeds 100%
                  </p>
                  <button
                    type="submit"
                    :disabled="saving || totalPayoutPercentage > 100"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    {{ saving ? 'Saving...' : 'Save Settings' }}
                  </button>
                </div>
              </form>
            </div>
          </div>

          <!-- Calculator Preview -->
          <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Commission Calculator</h3>
              
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Purchase Amount</label>
                  <div class="flex items-center gap-2 mt-1">
                    <span class="text-gray-500">K</span>
                    <input
                      v-model.number="calculator.amount"
                      type="number"
                      min="1"
                      class="block w-32 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    />
                  </div>
                </div>

                <div class="flex items-center gap-3">
                  <input
                    v-model="calculator.hasKit"
                    type="checkbox"
                    id="calc-has-kit"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                  />
                  <label for="calc-has-kit" class="text-sm text-gray-700">
                    Referrer has Starter Kit
                  </label>
                </div>

                <button
                  @click="calculatePreview"
                  class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
                >
                  Calculate
                </button>

                <!-- Results -->
                <div v-if="previewResults" class="mt-4 p-4 bg-gray-50 rounded-lg">
                  <div class="mb-3 pb-3 border-b">
                    <p class="text-sm text-gray-600">Commission Base ({{ form.base_percentage }}%)</p>
                    <p class="text-lg font-semibold">K{{ formatNumber(previewResults.base_amount) }}</p>
                  </div>
                  
                  <div class="space-y-2">
                    <div
                      v-for="(calc, level) in previewResults.calculations"
                      :key="level"
                      class="flex justify-between text-sm"
                    >
                      <span class="text-gray-600">
                        Level {{ level }} ({{ calc.level_rate }}%)
                        <span v-if="!calculator.hasKit" class="text-amber-600">× {{ form.non_kit_multiplier_percentage }}%</span>
                      </span>
                      <span class="font-medium">K{{ formatNumber(calc.commission_amount) }}</span>
                    </div>
                  </div>

                  <div class="mt-3 pt-3 border-t flex justify-between">
                    <span class="font-medium text-gray-900">Total Payout</span>
                    <span class="font-bold text-green-600">
                      K{{ formatNumber(previewResults.total_payout) }}
                      ({{ previewResults.effective_payout_percentage }}%)
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Commissions -->
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Commissions</h3>
            
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Referrer</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">From</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Level</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Purchase</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Base</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Commission</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Kit Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Date</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  <tr v-for="commission in recentCommissions" :key="commission.id">
                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">
                      {{ commission.referrer?.name || 'N/A' }}
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">
                      {{ commission.referee || 'N/A' }}
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-sm">
                      <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                        Level {{ commission.level }}
                      </span>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">
                      K{{ formatNumber(commission.package_amount) }}
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">
                      K{{ formatNumber(commission.commission_base_amount || commission.package_amount * 0.5) }}
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-green-600">
                      K{{ formatNumber(commission.amount) }}
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-sm">
                      <span
                        :class="[
                          'px-2 py-1 rounded-full text-xs',
                          commission.referrer_has_kit !== false
                            ? 'bg-green-100 text-green-800'
                            : 'bg-amber-100 text-amber-800'
                        ]"
                      >
                        {{ commission.referrer_has_kit !== false ? 'Has Kit' : 'No Kit (' + (commission.non_kit_multiplier * 100) + '%)' }}
                      </span>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">
                      {{ commission.created_at }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface Props {
  settings: {
    base_percentage: number;
    non_kit_multiplier_percentage: number;
    level_rates: Record<number, number>;
    enabled: boolean;
    total_payout_percentage: number;
  };
  stats: {
    total_paid_this_month: number;
    total_paid_last_month: number;
    commissions_this_month: number;
    pending_commissions: number;
    non_kit_commissions_this_month: number;
    average_commission: number;
  };
  recentCommissions: Array<any>;
  levelNames: Record<number, string>;
}

const props = defineProps<Props>();

const form = ref({
  base_percentage: props.settings.base_percentage,
  non_kit_multiplier_percentage: props.settings.non_kit_multiplier_percentage,
  level_rates: { ...props.settings.level_rates },
  enabled: props.settings.enabled,
});

const saving = ref(false);

const calculator = ref({
  amount: 500,
  hasKit: true,
});

const previewResults = ref<any>(null);

const totalPayoutPercentage = computed(() => {
  return Object.values(form.value.level_rates).reduce((sum, rate) => sum + (rate || 0), 0);
});

const formatNumber = (num: number) => {
  return (num || 0).toFixed(2);
};

const saveSettings = () => {
  saving.value = true;
  router.post(route('admin.commission-settings.update'), form.value, {
    preserveScroll: true,
    onFinish: () => {
      saving.value = false;
    },
  });
};

const calculatePreview = async () => {
  try {
    const response = await fetch(route('admin.commission-settings.preview'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify({
        purchase_amount: calculator.value.amount,
        referrer_has_kit: calculator.value.hasKit,
      }),
    });
    previewResults.value = await response.json();
  } catch (error) {
    console.error('Preview calculation failed:', error);
  }
};
</script>
