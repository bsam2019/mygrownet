<template>
  <AdminLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-900">
        LGR System Settings
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6">
            <form @submit.prevent="updateSettings">
              <div class="space-y-6">
                <!-- System Status -->
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">System Status</h3>
                  <div class="mt-4">
                    <label class="flex items-center">
                      <input
                        v-model="form.lgr_enabled"
                        type="checkbox"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                      />
                      <span class="ml-2 text-sm text-gray-700">Enable LGR System</span>
                    </label>
                  </div>
                </div>

                <!-- Reward Settings -->
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">Reward Settings</h3>
                  <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                      <label class="block text-sm font-medium text-gray-700">
                        Daily Rate (K)
                      </label>
                      <input
                        v-model="form.lgr_daily_rate"
                        type="number"
                        step="0.01"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      />
                      <p class="mt-1 text-xs text-gray-500">
                        Amount earned per active day
                      </p>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700">
                        Cycle Duration (Days)
                      </label>
                      <input
                        v-model="form.lgr_cycle_duration"
                        type="number"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      />
                      <p class="mt-1 text-xs text-gray-500">
                        Total days in a reward cycle
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Conversion Settings -->
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">Conversion Settings</h3>
                  <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                      <label class="block text-sm font-medium text-gray-700">
                        Max Cash Conversion (%)
                      </label>
                      <input
                        v-model="form.lgr_max_cash_conversion"
                        type="number"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      />
                      <p class="mt-1 text-xs text-gray-500">
                        Maximum % of LGC convertible to cash
                      </p>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700">
                        Min Cash Conversion (K)
                      </label>
                      <input
                        v-model="form.lgr_min_cash_conversion"
                        type="number"
                        step="0.01"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      />
                      <p class="mt-1 text-xs text-gray-500">
                        Minimum amount for cash conversion
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Pool Settings -->
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">Pool Settings</h3>
                  <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">
                      Reserve Percentage (%)
                    </label>
                    <input
                      v-model="form.lgr_pool_reserve_percentage"
                      type="number"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:w-1/2"
                    />
                    <p class="mt-1 text-xs text-gray-500">
                      Percentage of pool to maintain as reserve
                    </p>
                  </div>
                </div>

                <!-- Pool Contribution Percentages -->
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">Pool Contributions</h3>
                  <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                      <label class="block text-sm font-medium text-gray-700">
                        Registration Fees (%)
                      </label>
                      <input
                        v-model="form.lgr_registration_fee_percentage"
                        type="number"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      />
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700">
                        Product Sales (%)
                      </label>
                      <input
                        v-model="form.lgr_product_sale_percentage"
                        type="number"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      />
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700">
                        Marketplace Fees (%)
                      </label>
                      <input
                        v-model="form.lgr_marketplace_fee_percentage"
                        type="number"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      />
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700">
                        Venture Fees (%)
                      </label>
                      <input
                        v-model="form.lgr_venture_fee_percentage"
                        type="number"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      />
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700">
                        Subscription Renewals (%)
                      </label>
                      <input
                        v-model="form.lgr_subscription_percentage"
                        type="number"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      />
                    </div>
                  </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                  <button
                    type="submit"
                    :disabled="processing"
                    class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                  >
                    {{ processing ? 'Saving...' : 'Save Settings' }}
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

interface Props {
  settings: Record<string, string>;
}

const props = defineProps<Props>();
const processing = ref(false);

const form = reactive({
  lgr_enabled: props.settings.lgr_enabled === 'true',
  lgr_daily_rate: parseFloat(props.settings.lgr_daily_rate || '30'),
  lgr_cycle_duration: parseInt(props.settings.lgr_cycle_duration || '70'),
  lgr_max_cash_conversion: parseInt(props.settings.lgr_max_cash_conversion || '40'),
  lgr_min_cash_conversion: parseFloat(props.settings.lgr_min_cash_conversion || '100'),
  lgr_pool_reserve_percentage: parseInt(props.settings.lgr_pool_reserve_percentage || '30'),
  lgr_registration_fee_percentage: parseInt(props.settings.lgr_registration_fee_percentage || '20'),
  lgr_product_sale_percentage: parseInt(props.settings.lgr_product_sale_percentage || '15'),
  lgr_marketplace_fee_percentage: parseInt(props.settings.lgr_marketplace_fee_percentage || '10'),
  lgr_venture_fee_percentage: parseInt(props.settings.lgr_venture_fee_percentage || '10'),
  lgr_subscription_percentage: parseInt(props.settings.lgr_subscription_percentage || '15'),
});

const updateSettings = () => {
  processing.value = true;
  
  const settings = Object.entries(form).map(([key, value]) => ({
    key,
    value: value.toString(),
  }));
  
  router.put(
    route('admin.lgr.settings.update'),
    { settings },
    {
      onFinish: () => {
        processing.value = false;
      },
    }
  );
};
</script>

