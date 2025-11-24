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
        <h1 class="text-2xl font-bold text-gray-900">Edit Investment Round</h1>
        <p class="text-gray-600 mt-1">Update investment opportunity details</p>
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
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea
                v-model="form.description"
                required
                rows="3"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              ></textarea>
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
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
              <p class="mt-1 text-xs text-gray-500">
                💡 Maximum valuation at which CIUs convert to shares
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
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
              <p class="mt-1 text-xs text-gray-500">
                💡 Split proportionally among all investors
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Expected ROI</label>
              <input
                v-model="form.expected_roi"
                type="text"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
          </div>
        </div>

        <!-- Use of Funds -->
        <div>
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Use of Funds</h2>
          
          <!-- Header Row with Labels -->
          <div class="grid grid-cols-12 gap-3 mb-2 px-1">
            <div class="col-span-6">
              <label class="text-xs font-medium text-gray-700">Category Name</label>
            </div>
            <div class="col-span-2">
              <label class="text-xs font-medium text-gray-700">Percentage (%)</label>
            </div>
            <div class="col-span-3">
              <label class="text-xs font-medium text-gray-700">Amount (K)</label>
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
                  placeholder="200000"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-right"
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
            class="mt-3 text-blue-600 hover:text-blue-700 text-sm"
          >
            + Add Item
          </button>
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
            {{ processing ? 'Updating...' : 'Update Investment Round' }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { route } from 'ziggy-js'

interface InvestmentRound {
  id: number
  name: string
  description: string
  goalAmount: number
  minimumInvestment: number
  valuation: number
  equityPercentage: number
  expectedRoi: string
  useOfFunds: Array<{
    label: string
    percentage: number
    amount: number
  }>
}

interface Props {
  round: InvestmentRound
}

const props = defineProps<Props>()

const processing = ref(false)

const form = ref({
  name: props.round.name,
  description: props.round.description,
  goal_amount: props.round.goalAmount,
  minimum_investment: props.round.minimumInvestment,
  valuation: props.round.valuation,
  equity_percentage: props.round.equityPercentage,
  expected_roi: props.round.expectedRoi,
  use_of_funds: props.round.useOfFunds
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
  
  router.put(route('admin.investment-rounds.update', props.round.id), form.value, {
    onFinish: () => {
      processing.value = false
    }
  })
}
</script>
