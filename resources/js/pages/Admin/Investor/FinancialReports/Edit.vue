<template>
  <div class="min-h-screen bg-gray-50">
    <AdminSidebar />
    
    <div class="lg:pl-64">
      <div class="p-6">
        <!-- Header -->
        <div class="mb-6">
          <div class="flex items-center gap-4">
            <Link
              :href="route('admin.financial-reports.index')"
              class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
            >
              <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
            </Link>
            <div>
              <h1 class="text-2xl font-bold text-gray-900">Edit Financial Report</h1>
              <p class="mt-1 text-sm text-gray-500">Update {{ report.title }}</p>
            </div>
          </div>
        </div>

        <!-- Form -->
        <div class="max-w-4xl">
          <form @submit.prevent="submitForm" class="space-y-8">
            <!-- Basic Information -->
            <div class="bg-white rounded-lg shadow p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                    Report Title *
                  </label>
                  <input
                    id="title"
                    v-model="form.title"
                    type="text"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label for="report_type" class="block text-sm font-medium text-gray-700 mb-2">
                    Report Type
                  </label>
                  <input
                    id="report_type"
                    :value="report.report_type_label"
                    type="text"
                    disabled
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-500"
                  />
                </div>

                <div>
                  <label for="report_period" class="block text-sm font-medium text-gray-700 mb-2">
                    Report Period *
                  </label>
                  <input
                    id="report_period"
                    v-model="form.report_period"
                    type="text"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label for="report_date" class="block text-sm font-medium text-gray-700 mb-2">
                    Report Date *
                  </label>
                  <input
                    id="report_date"
                    v-model="form.report_date"
                    type="date"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
              </div>
            </div>

            <!-- Financial Data -->
            <div class="bg-white rounded-lg shadow p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Financial Data</h3>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label for="total_revenue" class="block text-sm font-medium text-gray-700 mb-2">
                    Total Revenue (K) *
                  </label>
                  <input
                    id="total_revenue"
                    v-model.number="form.total_revenue"
                    type="number"
                    step="0.01"
                    min="0"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label for="total_expenses" class="block text-sm font-medium text-gray-700 mb-2">
                    Total Expenses (K) *
                  </label>
                  <input
                    id="total_expenses"
                    v-model.number="form.total_expenses"
                    type="number"
                    step="0.01"
                    min="0"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label for="cash_flow" class="block text-sm font-medium text-gray-700 mb-2">
                    Cash Flow (K)
                  </label>
                  <input
                    id="cash_flow"
                    v-model.number="form.cash_flow"
                    type="number"
                    step="0.01"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label for="growth_rate" class="block text-sm font-medium text-gray-700 mb-2">
                    Growth Rate (%)
                  </label>
                  <input
                    id="growth_rate"
                    v-model.number="form.growth_rate"
                    type="number"
                    step="0.1"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
              </div>

              <!-- Net Profit Display -->
              <div v-if="form.total_revenue && form.total_expenses" class="mt-6 p-4 bg-gray-50 rounded-lg">
                <div class="flex justify-between items-center">
                  <span class="text-sm font-medium text-gray-700">Calculated Net Profit:</span>
                  <span class="text-lg font-bold" :class="netProfit >= 0 ? 'text-green-600' : 'text-red-600'">
                    K{{ formatNumber(netProfit) }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Platform Metrics -->
            <div class="bg-white rounded-lg shadow p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Platform Metrics</h3>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label for="total_members" class="block text-sm font-medium text-gray-700 mb-2">
                    Total Members
                  </label>
                  <input
                    id="total_members"
                    v-model.number="form.total_members"
                    type="number"
                    min="0"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label for="active_members" class="block text-sm font-medium text-gray-700 mb-2">
                    Active Members
                  </label>
                  <input
                    id="active_members"
                    v-model.number="form.active_members"
                    type="number"
                    min="0"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label for="monthly_recurring_revenue" class="block text-sm font-medium text-gray-700 mb-2">
                    Monthly Recurring Revenue (K)
                  </label>
                  <input
                    id="monthly_recurring_revenue"
                    v-model.number="form.monthly_recurring_revenue"
                    type="number"
                    step="0.01"
                    min="0"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label for="churn_rate" class="block text-sm font-medium text-gray-700 mb-2">
                    Churn Rate (%)
                  </label>
                  <input
                    id="churn_rate"
                    v-model.number="form.churn_rate"
                    type="number"
                    step="0.1"
                    min="0"
                    max="100"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
              </div>
            </div>

            <!-- Notes -->
            <div class="bg-white rounded-lg shadow p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Additional Notes</h3>
              
              <div>
                <textarea
                  id="notes"
                  v-model="form.notes"
                  rows="4"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Additional context or notes..."
                ></textarea>
              </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200">
              <Link
                :href="route('admin.financial-reports.index')"
                class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
              >
                Cancel
              </Link>
              <button
                type="submit"
                :disabled="submitting"
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                <span v-if="submitting">Saving...</span>
                <span v-else>Save Changes</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AdminSidebar from '@/components/AdminSidebar.vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

interface FinancialReport {
  id: number;
  title: string;
  report_type: string;
  report_type_label: string;
  report_period: string;
  report_date: string;
  total_revenue: number;
  total_expenses: number;
  cash_flow: number | null;
  growth_rate: number | null;
  total_members: number | null;
  active_members: number | null;
  monthly_recurring_revenue: number | null;
  churn_rate: number | null;
  notes: string | null;
}

const props = defineProps<{
  report: FinancialReport;
  reportTypes: Array<{ value: string; label: string }>;
}>();

const submitting = ref(false);

const form = reactive({
  title: props.report.title,
  report_period: props.report.report_period,
  report_date: props.report.report_date,
  total_revenue: props.report.total_revenue,
  total_expenses: props.report.total_expenses,
  cash_flow: props.report.cash_flow,
  growth_rate: props.report.growth_rate,
  total_members: props.report.total_members,
  active_members: props.report.active_members,
  monthly_recurring_revenue: props.report.monthly_recurring_revenue,
  churn_rate: props.report.churn_rate,
  notes: props.report.notes || '',
});

const netProfit = computed(() => {
  if (form.total_revenue && form.total_expenses) {
    return form.total_revenue - form.total_expenses;
  }
  return 0;
});

const formatNumber = (value: number): string => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value);
};

const submitForm = () => {
  submitting.value = true;

  router.put(route('admin.financial-reports.update', props.report.id), form, {
    onFinish: () => {
      submitting.value = false;
    },
  });
};
</script>
