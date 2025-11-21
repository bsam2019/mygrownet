<template>
  <MemberLayout>
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">ROI Calculator</h1>
        <p class="mt-2 text-gray-600">Calculate your return on investment with MyGrowNet</p>
      </div>

      <!-- Current Investment Summary -->
      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Your Current Investment</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="p-4 bg-blue-50 rounded-lg">
            <p class="text-sm text-gray-600">Starter Kit Investment</p>
            <p class="text-2xl font-bold text-blue-600">K {{ investments.starter_kit.toFixed(2) }}</p>
          </div>
          <div class="p-4 bg-green-50 rounded-lg">
            <p class="text-sm text-gray-600">Total Earnings</p>
            <p class="text-2xl font-bold text-green-600">K {{ investments.total_earnings.toFixed(2) }}</p>
          </div>
        </div>
        <div class="mt-4 p-4 bg-gray-50 rounded-lg">
          <p class="text-sm text-gray-600">Current ROI</p>
          <p class="text-3xl font-bold" :class="currentROI >= 0 ? 'text-green-600' : 'text-red-600'">
            {{ currentROI.toFixed(2) }}%
          </p>
        </div>
      </div>

      <!-- ROI Calculator Form -->
      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Calculate Potential ROI</h2>
        
        <div class="space-y-4">
          <!-- Initial Investment -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Initial Investment (K)
            </label>
            <input
              v-model.number="calculator.initial_investment"
              type="number"
              min="0"
              step="0.01"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>

          <!-- Monthly Team Growth -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Expected Monthly Team Growth
            </label>
            <input
              v-model.number="calculator.monthly_growth"
              type="number"
              min="0"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>

          <!-- Average Commission per Member -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Average Commission per Member (K)
            </label>
            <input
              v-model.number="calculator.avg_commission"
              type="number"
              min="0"
              step="0.01"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>

          <!-- Time Period -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Time Period (months)
            </label>
            <input
              v-model.number="calculator.months"
              type="number"
              min="1"
              max="60"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>

          <!-- Calculate Button -->
          <button
            @click="calculateROI"
            class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
          >
            Calculate ROI
          </button>
        </div>
      </div>

      <!-- Results -->
      <div v-if="results" class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Projected Results</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
          <div class="p-4 bg-blue-50 rounded-lg">
            <p class="text-sm text-gray-600">Total Team Size</p>
            <p class="text-2xl font-bold text-blue-600">{{ results.total_team }}</p>
          </div>
          <div class="p-4 bg-green-50 rounded-lg">
            <p class="text-sm text-gray-600">Total Earnings</p>
            <p class="text-2xl font-bold text-green-600">K {{ results.total_earnings.toFixed(2) }}</p>
          </div>
          <div class="p-4 bg-purple-50 rounded-lg">
            <p class="text-sm text-gray-600">Projected ROI</p>
            <p class="text-2xl font-bold text-purple-600">{{ results.roi.toFixed(2) }}%</p>
          </div>
        </div>

        <!-- Monthly Breakdown Chart -->
        <div class="mt-6">
          <h3 class="text-md font-semibold text-gray-900 mb-3">Monthly Breakdown</h3>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Month</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Team Size</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monthly Earnings</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cumulative</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="month in results.breakdown" :key="month.month">
                  <td class="px-4 py-3 text-sm text-gray-900">{{ month.month }}</td>
                  <td class="px-4 py-3 text-sm text-gray-900">{{ month.team_size }}</td>
                  <td class="px-4 py-3 text-sm text-green-600">K {{ month.monthly_earnings.toFixed(2) }}</td>
                  <td class="px-4 py-3 text-sm font-medium text-gray-900">K {{ month.cumulative.toFixed(2) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Break-even Point -->
        <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
          <p class="text-sm font-medium text-yellow-900">
            Break-even Point: Month {{ results.breakeven_month }}
          </p>
          <p class="text-xs text-yellow-700 mt-1">
            You'll recover your initial investment in {{ results.breakeven_month }} months
          </p>
        </div>
      </div>
    </div>
  </MemberLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import MemberLayout from '@/Layouts/MemberLayout.vue';

interface Props {
  investments: {
    starter_kit: number;
    total_earnings: number;
  };
  userTier: string;
}

const props = defineProps<Props>();

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
