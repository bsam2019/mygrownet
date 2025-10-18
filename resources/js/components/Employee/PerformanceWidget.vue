<template>
  <div class="bg-white rounded-lg shadow border p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-medium text-gray-900">Performance Overview</h3>
      <button
        @click="$emit('view-detailed-performance')"
        class="text-sm text-blue-600 hover:text-blue-800 font-medium"
      >
        View Details
      </button>
    </div>

    <div v-if="isLoading" class="flex justify-center py-8">
      <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
    </div>

    <div v-else-if="performance" class="space-y-6">
      <!-- Overall Score -->
      <div class="text-center">
        <div class="relative inline-flex items-center justify-center w-24 h-24">
          <svg class="w-24 h-24 transform -rotate-90" viewBox="0 0 100 100">
            <circle
              cx="50"
              cy="50"
              r="40"
              stroke="currentColor"
              stroke-width="8"
              fill="transparent"
              class="text-gray-200"
            />
            <circle
              cx="50"
              cy="50"
              r="40"
              stroke="currentColor"
              stroke-width="8"
              fill="transparent"
              :stroke-dasharray="circumference"
              :stroke-dashoffset="circumference - (performance.overallScore / 10) * circumference"
              class="text-blue-600 transition-all duration-300"
            />
          </svg>
          <div class="absolute inset-0 flex items-center justify-center">
            <span class="text-2xl font-bold text-gray-900">{{ formatNumber(performance.overallScore, 1) }}</span>
          </div>
        </div>
        <p class="text-sm text-gray-500 mt-2">Overall Performance Score</p>
      </div>

      <!-- Performance Metrics -->
      <div class="grid grid-cols-2 gap-4">
        <div class="text-center p-3 bg-gray-50 rounded-lg">
          <p class="text-lg font-semibold text-gray-900">{{ performance.goalsAchieved || 0 }}</p>
          <p class="text-xs text-gray-500">Goals Achieved</p>
        </div>
        <div class="text-center p-3 bg-gray-50 rounded-lg">
          <p class="text-lg font-semibold text-gray-900">{{ formatPercentage(performance.clientRetentionRate || 0) }}</p>
          <p class="text-xs text-gray-500">Client Retention</p>
        </div>
        <div class="text-center p-3 bg-gray-50 rounded-lg">
          <p class="text-lg font-semibold text-gray-900">{{ performance.newClientsAcquired || 0 }}</p>
          <p class="text-xs text-gray-500">New Clients</p>
        </div>
        <div class="text-center p-3 bg-gray-50 rounded-lg">
          <p class="text-lg font-semibold text-gray-900">{{ formatCurrency(performance.revenueGenerated || 0) }}</p>
          <p class="text-xs text-gray-500">Revenue Generated</p>
        </div>
      </div>

      <!-- Recent Goals -->
      <div v-if="recentGoals.length > 0">
        <h4 class="text-sm font-medium text-gray-900 mb-3">Current Goals</h4>
        <div class="space-y-3">
          <div
            v-for="goal in recentGoals.slice(0, 3)"
            :key="goal.id"
            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
          >
            <div class="flex-1">
              <p class="text-sm font-medium text-gray-900">{{ goal.title }}</p>
              <p class="text-xs text-gray-500">Due: {{ formatDate(goal.dueDate) }}</p>
            </div>
            <div class="flex items-center space-x-2">
              <div class="w-16 bg-gray-200 rounded-full h-2">
                <div
                  class="h-2 rounded-full transition-all duration-300"
                  :class="getProgressColor(goal.progress)"
                  :style="{ width: `${Math.min(goal.progress, 100)}%` }"
                ></div>
              </div>
              <span class="text-xs font-medium text-gray-600">{{ Math.round(goal.progress) }}%</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Performance Trend -->
      <div v-if="performanceTrend.length > 0">
        <h4 class="text-sm font-medium text-gray-900 mb-3">Performance Trend</h4>
        <div class="flex items-end justify-between h-16 space-x-1">
          <div
            v-for="(point, index) in performanceTrend.slice(-6)"
            :key="index"
            class="flex-1 bg-blue-200 rounded-t"
            :style="{ height: `${(point.score / 10) * 100}%` }"
            :title="`${point.period}: ${point.score}`"
          ></div>
        </div>
        <div class="flex justify-between text-xs text-gray-500 mt-1">
          <span>{{ performanceTrend[Math.max(0, performanceTrend.length - 6)]?.period || '' }}</span>
          <span>{{ performanceTrend[performanceTrend.length - 1]?.period || '' }}</span>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="pt-4 border-t border-gray-200">
        <div class="flex space-x-2">
          <button
            @click="$emit('set-goal')"
            class="flex-1 bg-blue-600 text-white text-sm font-medium py-2 px-3 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Set Goal
          </button>
          <button
            @click="$emit('view-reviews')"
            class="flex-1 bg-gray-100 text-gray-700 text-sm font-medium py-2 px-3 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
          >
            View Reviews
          </button>
        </div>
      </div>
    </div>

    <div v-else class="text-center py-8">
      <p class="text-gray-500">No performance data available</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { formatNumber, formatCurrency, formatPercentage } from '@/utils/formatting'

interface Performance {
  overallScore: number
  goalsAchieved?: number
  clientRetentionRate?: number
  newClientsAcquired?: number
  revenueGenerated?: number
}

interface Goal {
  id: number
  title: string
  progress: number
  dueDate: string
}

interface TrendPoint {
  period: string
  score: number
}

// Props
const props = defineProps<{
  employeeId?: number
}>()

// Emits
defineEmits<{
  'view-detailed-performance': []
  'set-goal': []
  'view-reviews': []
}>()

// Reactive data
const isLoading = ref(false)
const performance = ref<Performance | null>(null)
const recentGoals = ref<Goal[]>([])
const performanceTrend = ref<TrendPoint[]>([])

// Computed
const circumference = computed(() => 2 * Math.PI * 40)

// Methods
const getProgressColor = (progress: number): string => {
  if (progress >= 80) return 'bg-green-500'
  if (progress >= 60) return 'bg-yellow-500'
  if (progress >= 40) return 'bg-orange-500'
  return 'bg-red-500'
}

const formatDate = (dateString: string): string => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

const loadPerformanceData = async () => {
  if (!props.employeeId) return
  
  isLoading.value = true
  try {
    const response = await fetch(`/api/employee/${props.employeeId}/performance-summary`)
    if (response.ok) {
      const data = await response.json()
      performance.value = data.performance
      recentGoals.value = data.recentGoals || []
      performanceTrend.value = data.performanceTrend || []
    }
  } catch (error) {
    console.error('Failed to load performance data:', error)
  } finally {
    isLoading.value = false
  }
}

// Lifecycle
onMounted(() => {
  if (props.employeeId) {
    loadPerformanceData()
  }
})
</script>