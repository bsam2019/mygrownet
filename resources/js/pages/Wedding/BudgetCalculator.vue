<template>
  <div class="min-h-screen bg-gradient-to-br from-pink-50 to-purple-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Wedding Budget Calculator</h1>
        <p class="text-gray-600">Get a personalized budget estimate for your perfect wedding</p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Calculator Form -->
        <div class="bg-white rounded-xl shadow-lg p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-6">Calculate Your Budget</h2>
          
          <form @submit.prevent="calculateBudget" class="space-y-6">
            <!-- Guest Count -->
            <div>
              <label for="guest_count" class="block text-sm font-medium text-gray-700 mb-2">
                Number of Guests
              </label>
              <input
                id="guest_count"
                v-model="form.guest_count"
                type="number"
                min="1"
                max="1000"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
                placeholder="100"
                required
              />
            </div>

            <!-- Wedding Style -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-3">
                Wedding Style
              </label>
              <div class="space-y-3">
                <label v-for="style in weddingStyles" :key="style.value" class="flex items-center">
                  <input
                    v-model="form.wedding_style"
                    :value="style.value"
                    type="radio"
                    class="h-4 w-4 text-pink-600 focus:ring-pink-500 border-gray-300"
                  />
                  <div class="ml-3">
                    <div class="text-sm font-medium text-gray-900">{{ style.label }}</div>
                    <div class="text-sm text-gray-500">{{ style.description }}</div>
                  </div>
                </label>
              </div>
            </div>

            <!-- Calculate Button -->
            <button
              type="submit"
              :disabled="loading || !form.guest_count || !form.wedding_style"
              class="w-full bg-pink-600 text-white py-3 px-4 rounded-lg hover:bg-pink-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="loading">Calculating...</span>
              <span v-else>Calculate Budget</span>
            </button>
          </form>
        </div>

        <!-- Results -->
        <div v-if="results" class="bg-white rounded-xl shadow-lg p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-6">Your Budget Estimate</h2>
          
          <!-- Total Budget -->
          <div class="bg-gradient-to-r from-pink-500 to-purple-600 rounded-lg p-6 text-white mb-6">
            <div class="text-center">
              <div class="text-3xl font-bold">{{ results.recommended_budget.formatted }}</div>
              <div class="text-pink-100 mt-1">Recommended Total Budget</div>
            </div>
          </div>

          <!-- Budget Breakdown -->
          <div class="space-y-4">
            <h3 class="font-semibold text-gray-900">Budget Breakdown</h3>
            <div class="space-y-3">
              <div
                v-for="(item, category) in results.budget_breakdown"
                :key="category"
                class="flex justify-between items-center p-3 bg-gray-50 rounded-lg"
              >
                <div>
                  <div class="font-medium text-gray-900 capitalize">{{ formatCategory(category) }}</div>
                  <div class="text-sm text-gray-500">{{ item.percentage }}% of budget</div>
                </div>
                <div class="text-right">
                  <div class="font-semibold text-gray-900">{{ item.formatted }}</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Cost Per Guest -->
          <div class="mt-6 p-4 bg-blue-50 rounded-lg">
            <div class="flex justify-between items-center">
              <span class="text-gray-700">Cost per guest:</span>
              <span class="font-semibold text-blue-600">
                {{ formatCurrency(results.recommended_budget.amount / results.guest_count) }}
              </span>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="mt-6 space-y-3">
            <Link
              :href="route('wedding.create')"
              class="w-full bg-pink-600 text-white py-3 px-4 rounded-lg hover:bg-pink-700 transition-colors text-center block"
            >
              Create Wedding Event with This Budget
            </Link>
            <button
              @click="shareResults"
              class="w-full bg-gray-100 text-gray-700 py-3 px-4 rounded-lg hover:bg-gray-200 transition-colors"
            >
              Share Results
            </button>
          </div>
        </div>

        <!-- Tips -->
        <div v-else class="bg-white rounded-xl shadow-lg p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-6">💡 Budget Planning Tips</h2>
          <div class="space-y-4 text-gray-600">
            <div class="flex items-start">
              <div class="w-2 h-2 bg-pink-500 rounded-full mt-2 mr-3 flex-shrink-0"></div>
              <p>Venue and catering typically account for 40-50% of your total budget</p>
            </div>
            <div class="flex items-start">
              <div class="w-2 h-2 bg-pink-500 rounded-full mt-2 mr-3 flex-shrink-0"></div>
              <p>Photography and videography are investments that last a lifetime</p>
            </div>
            <div class="flex items-start">
              <div class="w-2 h-2 bg-pink-500 rounded-full mt-2 mr-3 flex-shrink-0"></div>
              <p>Consider the season - peak wedding months cost more</p>
            </div>
            <div class="flex items-start">
              <div class="w-2 h-2 bg-pink-500 rounded-full mt-2 mr-3 flex-shrink-0"></div>
              <p>Always add 10-15% buffer for unexpected expenses</p>
            </div>
            <div class="flex items-start">
              <div class="w-2 h-2 bg-pink-500 rounded-full mt-2 mr-3 flex-shrink-0"></div>
              <p>Book vendors early to secure better rates</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Back Link -->
      <div class="text-center mt-8">
        <Link
          :href="route('wedding.index')"
          class="text-gray-600 hover:text-gray-800 transition-colors"
        >
          ← Back to Wedding Dashboard
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Link } from '@inertiajs/vue3'
import axios from 'axios'

const form = reactive({
  guest_count: '',
  wedding_style: 'standard'
})

const results = ref(null)
const loading = ref(false)

const weddingStyles = [
  {
    value: 'budget',
    label: 'Budget Wedding',
    description: 'Simple and elegant, K800 per guest'
  },
  {
    value: 'standard',
    label: 'Standard Wedding',
    description: 'Traditional celebration, K1,200 per guest'
  },
  {
    value: 'premium',
    label: 'Premium Wedding',
    description: 'Upscale and luxurious, K2,000 per guest'
  },
  {
    value: 'luxury',
    label: 'Luxury Wedding',
    description: 'Ultra-premium experience, K3,500 per guest'
  }
]

const calculateBudget = async () => {
  loading.value = true
  
  try {
    const response = await axios.post(route('wedding.calculate-budget'), form)
    results.value = response.data
  } catch (error) {
    console.error('Error calculating budget:', error)
    // Handle error
  } finally {
    loading.value = false
  }
}

const formatCategory = (category) => {
  const labels = {
    venue: 'Venue & Catering',
    photography: 'Photography & Video',
    decoration: 'Flowers & Decoration',
    music: 'Music & Entertainment',
    transport: 'Transportation',
    makeup: 'Beauty & Makeup',
    attire: 'Wedding Attire',
    miscellaneous: 'Other Expenses'
  }
  return labels[category] || category
}

const formatCurrency = (amount) => {
  return `K${parseFloat(amount).toLocaleString()}`
}

const shareResults = () => {
  if (navigator.share && results.value) {
    navigator.share({
      title: 'My Wedding Budget Estimate',
      text: `My wedding budget estimate: ${results.value.recommended_budget.formatted} for ${results.value.guest_count} guests`,
      url: window.location.href
    })
  } else {
    // Fallback: copy to clipboard
    const text = `My wedding budget estimate: ${results.value.recommended_budget.formatted} for ${results.value.guest_count} guests`
    navigator.clipboard.writeText(text)
    // Show toast notification
  }
}
</script>