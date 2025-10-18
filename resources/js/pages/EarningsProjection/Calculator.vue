<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">
          MyGrowNet Earnings Projection Calculator
        </h1>
        <p class="text-lg text-gray-600 max-w-3xl mx-auto">
          Explore realistic earning scenarios based on your membership tier, network size, and activity level.
          All projections are estimates based on historical performance and market conditions.
        </p>
      </div>

      <!-- Earning Ranges Overview -->
      <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Monthly Earning Ranges by Tier</h2>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <div 
            v-for="(range, tier) in earning_ranges" 
            :key="tier"
            class="bg-white rounded-lg shadow p-4 text-center"
            :class="getTierColorClass(tier)"
          >
            <h3 class="font-semibold text-lg mb-2">{{ tier }}</h3>
            <p class="text-sm text-gray-600 mb-2">Monthly Range</p>
            <p class="font-bold text-lg">
              K{{ range.min.toLocaleString() }} - K{{ range.max.toLocaleString() }}
            </p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Calculator Form -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Calculate Your Projection</h2>
            
            <form @submit.prevent="calculateProjection" class="space-y-6">
              <!-- Tier Selection -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Membership Tier
                </label>
                <select 
                  v-model="form.tier" 
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  required
                >
                  <option value="">Select Tier</option>
                  <option v-for="tier in tiers" :key="tier" :value="tier">
                    {{ tier }}
                  </option>
                </select>
              </div>

              <!-- Active Referrals -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Active Referrals
                </label>
                <input 
                  v-model.number="form.active_referrals" 
                  type="number" 
                  min="1" 
                  max="100"
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  required
                />
                <p class="text-xs text-gray-500 mt-1">Number of direct referrals who are actively paying subscriptions</p>
              </div>

              <!-- Network Depth -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Network Depth
                </label>
                <select 
                  v-model.number="form.network_depth" 
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  required
                >
                  <option value="1">1 Level</option>
                  <option value="2">2 Levels</option>
                  <option value="3">3 Levels</option>
                  <option value="4">4 Levels</option>
                  <option value="5">5 Levels</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">How deep your referral network extends</p>
              </div>

              <!-- Projection Period -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Projection Period (Months)
                </label>
                <input 
                  v-model.number="form.months" 
                  type="number" 
                  min="1" 
                  max="24"
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  required
                />
              </div>

              <!-- Calculate Button -->
              <button 
                type="submit" 
                :disabled="loading"
                class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
              >
                <span v-if="loading">Calculating...</span>
                <span v-else>Calculate Projection</span>
              </button>
            </form>

            <!-- Quick Scenarios -->
            <div class="mt-6 pt-6 border-t border-gray-200">
              <h3 class="text-sm font-medium text-gray-700 mb-3">Quick Scenarios</h3>
              <div class="space-y-2">
                <button 
                  v-for="(scenario, name) in quickScenarios" 
                  :key="name"
                  @click="loadScenario(scenario)"
                  class="w-full text-left px-3 py-2 text-sm bg-gray-50 hover:bg-gray-100 rounded-md"
                >
                  {{ name.charAt(0).toUpperCase() + name.slice(1) }}: {{ scenario.referrals }} referrals, {{ scenario.depth }} levels
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Results Display -->
        <div class="lg:col-span-2">
          <div v-if="projection" class="space-y-6">
            <!-- Summary Card -->
            <div class="bg-white rounded-lg shadow-lg p-6">
              <h2 class="text-xl font-semibold text-gray-900 mb-4">Projection Summary</h2>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="text-center">
                  <p class="text-sm text-gray-600">Total Earnings</p>
                  <p class="text-2xl font-bold text-green-600">
                    K{{ projection.total_earnings.toLocaleString() }}
                  </p>
                </div>
                <div class="text-center">
                  <p class="text-sm text-gray-600">Average Monthly</p>
                  <p class="text-2xl font-bold text-blue-600">
                    K{{ Math.round(projection.average_monthly).toLocaleString() }}
                  </p>
                </div>
                <div class="text-center">
                  <p class="text-sm text-gray-600">Projection Period</p>
                  <p class="text-2xl font-bold text-gray-900">
                    {{ projection.scenario.months }} months
                  </p>
                </div>
              </div>
            </div>

            <!-- Income Breakdown Chart -->
            <div class="bg-white rounded-lg shadow-lg p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Income Breakdown</h3>
              <div class="space-y-3">
                <div 
                  v-for="(percentage, source) in projection.income_breakdown" 
                  :key="source"
                  class="flex items-center justify-between"
                >
                  <span class="text-sm font-medium text-gray-700 capitalize">
                    {{ source.replace('_', ' ') }}
                  </span>
                  <div class="flex items-center space-x-2">
                    <div class="w-32 bg-gray-200 rounded-full h-2">
                      <div 
                        class="bg-blue-600 h-2 rounded-full" 
                        :style="{ width: percentage + '%' }"
                      ></div>
                    </div>
                    <span class="text-sm font-semibold text-gray-900 w-8">{{ percentage }}%</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Monthly Projections Chart -->
            <div class="bg-white rounded-lg shadow-lg p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Monthly Earnings Projection</h3>
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Month</th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Subscription</th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Commissions</th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Team Bonus</th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Profit Share</th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Achievement</th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr 
                      v-for="month in projection.monthly_projections.slice(0, 12)" 
                      :key="month.month"
                      class="hover:bg-gray-50"
                    >
                      <td class="px-4 py-2 text-sm font-medium text-gray-900">{{ month.month }}</td>
                      <td class="px-4 py-2 text-sm text-gray-600">K{{ Math.round(month.subscription_share).toLocaleString() }}</td>
                      <td class="px-4 py-2 text-sm text-gray-600">K{{ Math.round(month.multilevel_commissions).toLocaleString() }}</td>
                      <td class="px-4 py-2 text-sm text-gray-600">K{{ Math.round(month.team_volume_bonus).toLocaleString() }}</td>
                      <td class="px-4 py-2 text-sm text-gray-600">K{{ Math.round(month.profit_sharing).toLocaleString() }}</td>
                      <td class="px-4 py-2 text-sm text-gray-600">K{{ Math.round(month.achievement_bonus).toLocaleString() }}</td>
                      <td class="px-4 py-2 text-sm font-semibold text-gray-900">K{{ Math.round(month.total).toLocaleString() }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Placeholder when no projection -->
          <div v-else class="bg-white rounded-lg shadow-lg p-8 text-center">
            <div class="text-gray-400 mb-4">
              <svg class="mx-auto h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Projection Yet</h3>
            <p class="text-gray-600">Fill out the form on the left to calculate your earnings projection.</p>
          </div>
        </div>
      </div>

      <!-- Compliance and Legal Information -->
      <div class="mt-8">
        <ComplianceDisclaimer />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import axios from 'axios'
import ComplianceDisclaimer from '@/components/EarningsProjection/ComplianceDisclaimer.vue'

// Props
const props = defineProps({
  earning_ranges: Object,
  tiers: Array
})

// Reactive data
const loading = ref(false)
const projection = ref(null)

const form = reactive({
  tier: '',
  active_referrals: 5,
  network_depth: 3,
  months: 12
})

const quickScenarios = {
  conservative: { referrals: 3, depth: 2 },
  realistic: { referrals: 5, depth: 3 },
  optimistic: { referrals: 10, depth: 4 }
}

// Methods
const calculateProjection = async () => {
  loading.value = true
  
  try {
    const response = await axios.post('/earnings-projection/calculate', form)
    
    if (response.data.success) {
      projection.value = response.data.projection
    } else {
      alert('Error: ' + response.data.message)
    }
  } catch (error) {
    console.error('Error calculating projection:', error)
    alert('An error occurred while calculating the projection.')
  } finally {
    loading.value = false
  }
}

const loadScenario = (scenario) => {
  form.active_referrals = scenario.referrals
  form.network_depth = scenario.depth
}

const getTierColorClass = (tier) => {
  const colors = {
    Bronze: 'border-l-4 border-gray-500',
    Silver: 'border-l-4 border-gray-400',
    Gold: 'border-l-4 border-yellow-500',
    Diamond: 'border-l-4 border-blue-500',
    Elite: 'border-l-4 border-purple-500'
  }
  return colors[tier] || ''
}
</script>