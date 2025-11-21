<template>
  <div class="p-8">
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-gray-900">Financial Plan</h2>
      <p class="mt-2 text-gray-600">Enter your financial data - we'll calculate projections automatically</p>
    </div>

    <!-- Calculator Tabs -->
    <div class="mb-6">
      <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              activeTab === tab.id
                ? 'border-blue-600 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
            ]"
          >
            {{ tab.name }}
          </button>
        </nav>
      </div>
    </div>

    <!-- Tab Content -->
    <div class="space-y-6">
      <!-- Startup Costs -->
      <div v-if="activeTab === 'startup'" class="bg-gray-50 p-6 rounded-lg">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Startup Costs</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div v-for="(value, key) in localForm.startupCosts" :key="key">
            <label class="block text-sm font-medium text-gray-700 mb-2 capitalize">
              {{ key.replace(/_/g, ' ') }}
            </label>
            <div class="relative">
              <span class="absolute left-3 top-3 text-gray-500">K</span>
              <input
                v-model.number="localForm.startupCosts[key]"
                type="number"
                min="0"
                class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg"
                @input="calculateTotals"
              />
            </div>
          </div>
        </div>
        <div class="mt-6 p-4 bg-blue-50 rounded-lg">
          <div class="flex justify-between items-center">
            <span class="text-lg font-semibold text-gray-900">Total Startup Costs:</span>
            <span class="text-2xl font-bold text-blue-600">K {{ formatNumber(totalStartupCosts) }}</span>
          </div>
        </div>
      </div>

      <!-- Monthly Operating Costs -->
      <div v-if="activeTab === 'operating'" class="bg-gray-50 p-6 rounded-lg">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Monthly Operating Costs</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div v-for="(value, key) in localForm.monthlyOperatingCosts" :key="key">
            <label class="block text-sm font-medium text-gray-700 mb-2 capitalize">
              {{ key.replace(/_/g, ' ') }}
            </label>
            <div class="relative">
              <span class="absolute left-3 top-3 text-gray-500">K</span>
              <input
                v-model.number="localForm.monthlyOperatingCosts[key]"
                type="number"
                min="0"
                class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg"
                @input="calculateTotals"
              />
            </div>
          </div>
        </div>
        <div class="mt-6 p-4 bg-amber-50 rounded-lg">
          <div class="flex justify-between items-center">
            <span class="text-lg font-semibold text-gray-900">Total Monthly Costs:</span>
            <span class="text-2xl font-bold text-amber-600">K {{ formatNumber(totalMonthlyCosts) }}</span>
          </div>
        </div>
      </div>

      <!-- Revenue Projections -->
      <div v-if="activeTab === 'revenue'" class="bg-gray-50 p-6 rounded-lg">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Revenue Projections</h3>
        <div class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Price per Unit/Service (K)
              </label>
              <input
                v-model.number="localForm.revenueProjections.pricePerUnit"
                type="number"
                min="0"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                @input="calculateTotals"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Expected Monthly Sales Volume
              </label>
              <input
                v-model.number="localForm.revenueProjections.monthlySalesVolume"
                type="number"
                min="0"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                @input="calculateTotals"
              />
            </div>
          </div>
          <div class="mt-6 p-4 bg-green-50 rounded-lg">
            <div class="space-y-2">
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-700">Monthly Revenue:</span>
                <span class="text-lg font-bold text-green-600">K {{ formatNumber(monthlyRevenue) }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-700">Annual Revenue:</span>
                <span class="text-lg font-bold text-green-600">K {{ formatNumber(annualRevenue) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Profit & Loss -->
      <div v-if="activeTab === 'profit'" class="bg-gray-50 p-6 rounded-lg">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Profit & Loss Projection</h3>
        <div class="space-y-4">
          <div class="bg-white p-4 rounded-lg border border-gray-200">
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-gray-700">Monthly Revenue</span>
                <span class="font-semibold text-green-600">K {{ formatNumber(monthlyRevenue) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-700">Monthly Costs</span>
                <span class="font-semibold text-red-600">- K {{ formatNumber(totalMonthlyCosts) }}</span>
              </div>
              <div class="border-t border-gray-200 pt-3 flex justify-between">
                <span class="text-lg font-bold text-gray-900">Monthly Profit</span>
                <span class="text-lg font-bold" :class="monthlyProfit >= 0 ? 'text-green-600' : 'text-red-600'">
                  K {{ formatNumber(monthlyProfit) }}
                </span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Profit Margin</span>
                <span class="text-sm font-semibold" :class="profitMargin >= 0 ? 'text-green-600' : 'text-red-600'">
                  {{ profitMargin.toFixed(1) }}%
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Break-even Analysis -->
      <div v-if="activeTab === 'breakeven'" class="bg-gray-50 p-6 rounded-lg">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Break-even Analysis</h3>
        <div class="space-y-4">
          <div class="bg-white p-6 rounded-lg border border-gray-200">
            <div class="text-center space-y-4">
              <div>
                <p class="text-sm text-gray-600 mb-2">Units to Sell to Break Even</p>
                <p class="text-4xl font-bold text-blue-600">{{ breakEvenUnits }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600 mb-2">Months to Break Even</p>
                <p class="text-4xl font-bold text-purple-600">{{ breakEvenMonths }}</p>
              </div>
              <div class="pt-4 border-t border-gray-200">
                <p class="text-xs text-gray-500">
                  Based on your startup costs of K{{ formatNumber(totalStartupCosts) }} and monthly profit of K{{ formatNumber(monthlyProfit) }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Navigation -->
    <div class="mt-8 flex justify-between pt-6 border-t border-gray-200">
      <button
        @click="$emit('previous')"
        type="button"
        class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
      >
        Previous
      </button>
      <div class="flex space-x-3">
        <button
          @click="$emit('save')"
          type="button"
          class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
        >
          Save Draft
        </button>
        <button
          @click="handleNext"
          type="button"
          class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          Next Step
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';

interface Props {
  modelValue: any;
}

const props = defineProps<Props>();
const emit = defineEmits(['update:modelValue', 'next', 'previous', 'save']);

const localForm = ref({ ...props.modelValue });

// Initialize financial data if not present
onMounted(() => {
  if (!localForm.value.startupCosts) {
    localForm.value.startupCosts = {
      equipment: 0,
      inventory: 0,
      licenses_permits: 0,
      marketing: 0,
      rent_deposit: 0,
      other: 0,
    };
  }
  if (!localForm.value.monthlyOperatingCosts) {
    localForm.value.monthlyOperatingCosts = {
      rent: 0,
      salaries: 0,
      utilities: 0,
      supplies: 0,
      marketing: 0,
      transport: 0,
      other: 0,
    };
  }
  if (!localForm.value.revenueProjections) {
    localForm.value.revenueProjections = {
      pricePerUnit: 0,
      monthlySalesVolume: 0,
    };
  }
});

watch(localForm, (newVal) => {
  emit('update:modelValue', newVal);
}, { deep: true });

const tabs = [
  { id: 'startup', name: 'Startup Costs' },
  { id: 'operating', name: 'Operating Costs' },
  { id: 'revenue', name: 'Revenue' },
  { id: 'profit', name: 'Profit & Loss' },
  { id: 'breakeven', name: 'Break-even' },
];

const activeTab = ref('startup');

const totalStartupCosts = computed(() => {
  return Object.values(localForm.value.startupCosts || {}).reduce((sum: number, val: any) => sum + (Number(val) || 0), 0);
});

const totalMonthlyCosts = computed(() => {
  return Object.values(localForm.value.monthlyOperatingCosts || {}).reduce((sum: number, val: any) => sum + (Number(val) || 0), 0);
});

const monthlyRevenue = computed(() => {
  const price = localForm.value.revenueProjections?.pricePerUnit || 0;
  const volume = localForm.value.revenueProjections?.monthlySalesVolume || 0;
  return price * volume;
});

const annualRevenue = computed(() => monthlyRevenue.value * 12);

const monthlyProfit = computed(() => monthlyRevenue.value - totalMonthlyCosts.value);

const profitMargin = computed(() => {
  if (monthlyRevenue.value === 0) return 0;
  return (monthlyProfit.value / monthlyRevenue.value) * 100;
});

const breakEvenUnits = computed(() => {
  const price = localForm.value.revenueProjections?.pricePerUnit || 0;
  if (price === 0) return 0;
  return Math.ceil((totalStartupCosts.value + totalMonthlyCosts.value) / price);
});

const breakEvenMonths = computed(() => {
  if (monthlyProfit.value <= 0) return '∞';
  return Math.ceil(totalStartupCosts.value / monthlyProfit.value);
});

const calculateTotals = () => {
  // Trigger reactivity
  localForm.value = { ...localForm.value };
};

const formatNumber = (num: number) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(num);
};

const handleNext = () => {
  emit('next');
};
</script>
