<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto bg-white">
    <!-- Header -->
    <div class="sticky top-0 bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between">
      <h2 class="text-lg font-semibold text-gray-900">ROI Calculator</h2>
      <button @click="$emit('close')" class="p-2 text-gray-500 hover:text-gray-700">
        <XMarkIcon class="h-6 w-6" />
      </button>
    </div>

    <div class="p-4 space-y-4">
      <!-- Current Investment -->
      <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-lg">
        <p class="text-sm text-gray-600 mb-2">Your Investment</p>
        <p class="text-2xl font-bold text-blue-600">K {{ investments.starter_kit.toFixed(2) }}</p>
        <p class="text-sm text-gray-600 mt-2">Total Earnings</p>
        <p class="text-xl font-bold text-green-600">K {{ investments.total_earnings.toFixed(2) }}</p>
        <div class="mt-3 pt-3 border-t border-blue-200">
          <p class="text-sm text-gray-600">Current ROI</p>
          <p class="text-2xl font-bold" :class="currentROI >= 0 ? 'text-green-600' : 'text-red-600'">
            {{ currentROI.toFixed(2) }}%
          </p>
        </div>
      </div>

      <!-- Calculator Form -->
      <div class="bg-white border border-gray-200 rounded-lg p-4 space-y-4">
        <h3 class="font-semibold text-gray-900">Calculate Potential ROI</h3>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Initial Investment (K)
          </label>
          <input
            v-model.number="calculator.initial_investment"
            type="number"
            min="0"
            step="0.01"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Monthly Team Growth
          </label>
          <input
            v-model.number="calculator.monthly_growth"
            type="number"
            min="0"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Avg Commission per Member (K)
          </label>
          <input
            v-model.number="calculator.avg_commission"
            type="number"
            min="0"
            step="0.01"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Time Period (months)
          </label>
          <input
            v-model.number="calculator.months"
            type="number"
            min="1"
            max="60"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg"
          />
        </div>

        <button
          @click="calculateROI"
          class="w-full px-4 py-3 bg-blue-600 text-white rounded-lg font-medium"
        >
          Calculate ROI
        </button>
      </div>

      <!-- Results -->
      <div v-if="results" class="bg-white border border-gray-200 rounded-lg p-4 space-y-4">
        <h3 class="font-semibold text-gray-900">Projected Results</h3>
        
        <div class="grid grid-cols-3 gap-2">
          <div class="bg-blue-50 p-3 rounded-lg text-center">
            <p class="text-xs text-gray-600">Team Size</p>
            <p class="text-lg font-bold text-blue-600">{{ results.total_team }}</p>
          </div>
          <div class="bg-green-50 p-3 rounded-lg text-center">
            <p class="text-xs text-gray-600">Earnings</p>
            <p class="text-lg font-bold text-green-600">K{{ results.total_earnings.toFixed(0) }}</p>
          </div>
          <div class="bg-purple-50 p-3 rounded-lg text-center">
            <p class="text-xs text-gray-600">ROI</p>
            <p class="text-lg font-bold text-purple-600">{{ results.roi.toFixed(0) }}%</p>
          </div>
        </div>

        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
          <p class="text-sm font-medium text-yellow-900">
            Break-even: Month {{ results.breakeven_month }}
          </p>
        </div>

        <!-- Monthly Breakdown -->
        <div class="max-h-64 overflow-y-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 sticky top-0">
              <tr>
                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500">Month</th>
                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500">Team</th>
                <th class="px-2 py-2 text-right text-xs font-medium text-gray-500">Earnings</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="month in results.breakdown" :key="month.month">
                <td class="px-2 py-2 text-gray-900">{{ month.month }}</td>
                <td class="px-2 py-2 text-gray-900">{{ month.team_size }}</td>
                <td class="px-2 py-2 text-right text-green-600">K{{ month.cumulative.toFixed(0) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';

interface Props {
  show: boolean;
  investments: {
    starter_kit: number;
    total_earnings: number;
  };
}

const props = defineProps<Props>();
defineEmits(['close']);

const calculator = ref({
  initial_investment: props.investments.starter_kit,
  monthly_growth: 3,
  avg_commission: 50,
  months: 12,
});

const results = ref<any>(null);

const currentROI = computed(() => {
  if (props.investments.starter_kit === 0) return 0;
  return ((props.investments.total_earnings - props.investments.starter_kit) / props.investments.starter_kit) * 100;
});

const calculateROI = () => {
  const breakdown = [];
  let teamSize = 0;
  let cumulative = 0;
  let breakevenMonth = 0;

  for (let month = 1; month <= calculator.value.months; month++) {
    teamSize += calculator.value.monthly_growth;
    const monthlyEarnings = teamSize * calculator.value.avg_commission;
    cumulative += monthlyEarnings;

    breakdown.push({
      month,
      team_size: teamSize,
      monthly_earnings: monthlyEarnings,
      cumulative,
    });

    if (breakevenMonth === 0 && cumulative >= calculator.value.initial_investment) {
      breakevenMonth = month;
    }
  }

  const totalEarnings = cumulative;
  const roi = ((totalEarnings - calculator.value.initial_investment) / calculator.value.initial_investment) * 100;

  results.value = {
    total_team: teamSize,
    total_earnings: totalEarnings,
    roi,
    breakdown,
    breakeven_month: breakevenMonth || calculator.value.months,
  };
};
</script>
