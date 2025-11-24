<template>
  <AdminLayout>
    <div class="p-6 max-w-4xl mx-auto">
      <!-- Header -->
      <div class="mb-6">
        <Link
          :href="route('admin.investment-rounds.index')"
          class="text-blue-600 hover:text-blue-700 text-sm mb-2 inline-block"
        >
          ← Back to Investment Rounds
        </Link>
        <h1 class="text-2xl font-bold text-gray-900">Create Investment Round</h1>
        <p class="text-gray-600 mt-1">Set up a new investment opportunity</p>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="bg-white rounded-lg shadow p-6 space-y-6">
        <!-- Basic Information -->
        <div>
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h2>
          
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Round Name</label>
              <input
                v-model="form.name"
                type="text"
                required
                placeholder="e.g., Seed Round - Convertible Investment"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
              <p class="mt-1 text-xs text-gray-500">
                Examples: "Seed Round", "Pre-Launch Funding", "Convertible Investment Round"
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea
                v-model="form.description"
                required
                rows="3"
                placeholder="e.g., Seeking K500,000 through Convertible Investment Units to launch platform and acquire first 1,000 members"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              ></textarea>
              <p class="mt-1 text-xs text-gray-500">
                Explain what you're raising for and what investors will get (CIUs, profit sharing, etc.)
              </p>
            </div>
          </div>
        </div>

        <!-- Financial Details -->
        <div>
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Financial Details</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Goal Amount (K)</label>
              <input
                v-model.number="form.goal_amount"
                type="number"
                required
                min="0"
                step="1000"
                placeholder="500000"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Investment (K)</label>
              <input
                v-model.number="form.minimum_investment"
                type="number"
                required
                min="0"
                step="1000"
                placeholder="25000"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Valuation Cap (K)
                <span class="text-xs text-gray-500 font-normal ml-1">
                  (Maximum conversion valuation)
                </span>
              </label>
              <input
                v-model.number="form.valuation"
                type="number"
                required
                min="0"
                step="100000"
                placeholder="3000000"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
              <p class="mt-1 text-xs text-gray-500">
                💡 For convertible structure: This caps the valuation at conversion. Recommended: K2M-K5M for early stage.
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Investor Pool (%)
                <span class="text-xs text-gray-500 font-normal ml-1">
                  (Total equity for all investors)
                </span>
              </label>
              <input
                v-model.number="form.equity_percentage"
                type="number"
                required
                min="0"
                max="100"
                step="1"
                placeholder="25"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
              <p class="mt-1 text-xs text-gray-500">
                💡 Recommended: 20-30% for seed round. This is split among all investors proportionally.
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Expected ROI</label>
              <input
                v-model="form.expected_roi"
                type="text"
                required
                placeholder="3-5x"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
          </div>
        </div>

        <!-- Use of Funds -->
        <div>
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Use of Funds</h2>
            <span class="text-sm text-gray-600">
              Total: {{ totalPercentage }}% 
              <span v-if="totalPercentage !== 100" class="text-red-600">(should be 100%)</span>
              <span v-else class="text-green-600">✓</span>
            </span>
          </div>
          
          <!-- Header Row with Labels -->
          <div class="grid grid-cols-12 gap-3 mb-2 px-1">
            <div class="col-span-6">
              <label class="text-xs font-medium text-gray-700">Category Name</label>
            </div>
            <div class="col-span-2">
              <label class="text-xs font-medium text-gray-700">Percentage (%)</label>
            </div>
            <div class="col-span-3">
              <label class="text-xs font-medium text-gray-700">Amount (K) - Auto</label>
            </div>
            <div class="col-span-1"></div>
          </div>

          <div class="space-y-3">
            <div v-for="(fund, index) in form.use_of_funds" :key="index" class="grid grid-cols-12 gap-3 items-center">
              <div class="col-span-6">
                <input
                  v-model="fund.label"
                  type="text"
                  required
                  placeholder="e.g., Technology & Platform"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>
              <div class="col-span-2">
                <input
                  v-model.number="fund.percentage"
                  @input="calculateAmount(index)"
                  type="number"
                  required
                  min="0"
                  max="100"
                  step="1"
                  placeholder="40"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-center"
                />
              </div>
              <div class="col-span-3">
                <input
                  v-model.number="fund.amount"
                  @input="calculatePercentage(index)"
                  type="number"
                  required
                  min="0"
                  placeholder="Or enter amount"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-right"
                  title="Auto-calculated from %, or enter amount to calculate %"
                />
              </div>
              <div class="col-span-1 flex justify-center">
                <button
                  v-if="form.use_of_funds.length > 1"
                  type="button"
                  @click="removeFund(index)"
                  class="px-3 py-2 text-red-600 hover:text-red-700 hover:bg-red-50 rounded"
                  title="Remove this item"
                >
                  ✕
                </button>
              </div>
            </div>
          </div>

          <button
            type="button"
            @click="addFund"
            class="mt-3 text-blue-600 hover:text-blue-700 text-sm font-medium"
          >
            + Add Category
          </button>
          
          <p class="mt-2 text-sm text-gray-500">
            💡 Tip: Enter percentages and amounts will be calculated automatically based on your goal amount
          </p>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t">
          <Link
            :href="route('admin.investment-rounds.index')"
            class="px-4 py-2 text-gray-700 hover:text-gray-900"
          >
            Cancel
          </Link>
          <button
            type="submit"
            :disabled="processing"
            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ processing ? 'Creating...' : 'Create Investment Round' }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { route } from 'ziggy-js'

const processing = ref(false)

const form = ref({
  name: '',
  description: '',
  goal_amount: 0,
  minimum_investment: 0,
  valuation: 0,
  equity_percentage: 0,
  expected_roi: '',
  use_of_funds: [
    { label: '', percentage: 0, amount: 0 }
  ]
})

const totalPercentage = computed(() => {
  return form.value.use_of_funds.reduce((sum, fund) => sum + (fund.percentage || 0), 0)
})

const calculateAmount = (index: number) => {
  const fund = form.value.use_of_funds[index]
  if (form.value.goal_amount && fund.percentage) {
    fund.amount = Math.round((form.value.goal_amount * fund.percentage) / 100)
  }
}

const calculatePercentage = (index: number) => {
  const fund = form.value.use_of_funds[index]
  if (form.value.goal_amount && fund.amount) {
    fund.percentage = Math.round((fund.amount / form.value.goal_amount) * 100)
  }
}

// Recalculate all amounts when goal amount changes
const recalculateAllAmounts = () => {
  form.value.use_of_funds.forEach((fund, index) => {
    if (fund.percentage) {
      calculateAmount(index)
    }
  })
}

// Watch goal amount changes
import { watch } from 'vue'
watch(() => form.value.goal_amount, () => {
  recalculateAllAmounts()
})

const addFund = () => {
  form.value.use_of_funds.push({ label: '', percentage: 0, amount: 0 })
}

const removeFund = (index: number) => {
  form.value.use_of_funds.splice(index, 1)
}

const submit = () => {
  processing.value = true
  
  router.post(route('admin.investment-rounds.store'), form.value, {
    onFinish: () => {
      processing.value = false
    }
  })
}
</script>
