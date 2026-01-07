<template>
  <div class="min-h-screen bg-gradient-to-br from-pink-50 to-purple-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Wedding Planning</h1>
            <p class="text-gray-600 mt-1">Tools to help plan your perfect day</p>
          </div>
          <Link
            :href="route('wedding.index')"
            class="text-purple-600 hover:text-purple-700 flex items-center gap-2"
          >
            <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
            Back to Dashboard
          </Link>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Wedding Event Summary -->
      <div v-if="weddingEvent" class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-xl font-semibold text-gray-900">{{ weddingEvent.partnerName }}</h2>
            <p class="text-gray-600">{{ formatDate(weddingEvent.weddingDate) }}</p>
          </div>
          <div class="text-right">
            <p class="text-2xl font-bold text-green-600">{{ formatCurrency(weddingEvent.budget) }}</p>
            <p class="text-sm text-gray-500">Total Budget</p>
          </div>
        </div>
      </div>

      <div class="grid lg:grid-cols-2 gap-8">
        <!-- Timeline -->
        <div class="bg-white rounded-xl shadow-lg p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <CalendarIcon class="h-5 w-5 text-purple-600" aria-hidden="true" />
            Wedding Timeline
          </h3>
          <div class="space-y-4">
            <div 
              v-for="(item, index) in timeline" 
              :key="index"
              class="flex items-start gap-4 p-3 rounded-lg hover:bg-gray-50"
            >
              <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center flex-shrink-0">
                <span class="text-purple-600 text-sm font-medium">{{ index + 1 }}</span>
              </div>
              <div>
                <p class="font-medium text-gray-900">{{ item.task }}</p>
                <p class="text-sm text-gray-500">{{ item.timeframe }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Budget Breakdown -->
        <div class="bg-white rounded-xl shadow-lg p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <CurrencyDollarIcon class="h-5 w-5 text-green-600" aria-hidden="true" />
            Budget Breakdown
          </h3>
          <div class="space-y-3">
            <div 
              v-for="(item, index) in budgetItems" 
              :key="index"
              class="flex items-center justify-between p-3 rounded-lg bg-gray-50"
            >
              <span class="text-gray-700">{{ item.category }}</span>
              <span class="font-medium text-gray-900">{{ formatCurrency(item.amount) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Progress -->
      <div class="mt-8 bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
          <ChartBarIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
          Planning Progress
        </h3>
        <div class="mb-2 flex justify-between text-sm">
          <span class="text-gray-600">Overall Progress</span>
          <span class="font-medium text-gray-900">{{ progress?.percentage || 0 }}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-3">
          <div 
            class="bg-gradient-to-r from-purple-500 to-pink-500 h-3 rounded-full transition-all duration-500"
            :style="{ width: (progress?.percentage || 0) + '%' }"
          ></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import {
  ArrowLeftIcon,
  CalendarIcon,
  CurrencyDollarIcon,
  ChartBarIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  weddingEvent: Object,
  budgetBreakdown: [Array, Object],
  timeline: Array,
  progress: Object,
})

// Normalize budgetBreakdown to array
const budgetItems = computed(() => {
  if (!props.budgetBreakdown) return []
  if (Array.isArray(props.budgetBreakdown)) return props.budgetBreakdown
  // Convert object to array
  return Object.entries(props.budgetBreakdown).map(([category, amount]) => ({
    category,
    amount: typeof amount === 'object' ? amount.amount : amount
  }))
})

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatCurrency = (amount) => {
  if (amount === null || amount === undefined) return 'K0'
  const value = typeof amount === 'object' && amount !== null ? amount.amount : amount
  return `K${parseFloat(value || 0).toLocaleString()}`
}
</script>
