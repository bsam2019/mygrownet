<template>
  <div class="bg-white rounded-lg shadow border p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-medium text-gray-900">Performance Statistics</h3>
      <button
        @click="$emit('view-detailed-analytics')"
        class="text-sm text-blue-600 hover:text-blue-800 font-medium"
      >
        View Analytics
      </button>
    </div>

    <div v-if="isLoading" class="flex justify-center py-8">
      <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
    </div>

    <div v-else class="space-y-6">
      <!-- Overall Performance Metrics -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="text-center p-3 bg-green-50 rounded-lg">
          <p class="text-2xl font-bold text-green-600">{{ formatNumber(performanceStats.averageScore, 1) }}</p>
          <p class="text-xs text-gray-600">Avg Performance</p>
        </div>
        <div class="text-center p-3 bg-blue-50 rounded-lg">
          <p class="text-2xl font-bold text-blue-600">{{ performanceStats.topPerformers }}</p>
          <p class="text-xs text-gray-600">Top Performers</p>
        </div>
        <div class="text-center p-3 bg-yellow-50 rounded-lg">
          <p class="text-2xl font-bold text-yellow-600">{{ formatPercentage(performanceStats.goalAchievementRate) }}</p>
          <p class="text-xs text-gray-600">Goal Achievement</p>
        </div>
        <div class="text-center p-3 bg-purple-50 rounded-lg">
          <p class="text-2xl font-bold text-purple-600">{{ formatCurrency(performanceStats.totalCommissions) }}</p>
          <p class="text-xs text-gray-600">Total Commissions</p>
        </div>
      </div>

      <!-- Performance Distribution -->
      <div>
        <h4 class="text-sm font-medium text-gray-900 mb-3">Performance Distribution</h4>
        <div class="space-y-3">
          <div
            v-for="range in performanceDistribution"
            :key="range.label"
            class="flex items-center justify-between"
          >
            <div class="flex items-center space-x-3 flex-1">
              <div
                class="w-4 h-4 rounded"
                :style="{ backgroundColor: range.color }"
              ></div>
              <span class="text-sm text-gray-700">{{ range.label }}</span>
            </div>
            <div class="flex items-center space-x-3">
              <div class="flex-1 w-24">
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div
                    class="h-2 rounded-full transition-all duration-300"
                    :style="{ 
                      backgroundColor: range.color, 
                      width: `${Math.min(range.percentage, 100)}%` 
                    }"
                  ></div>
                </div>
              </div>
              <div class="text-right w-16">
                <span class="text-sm font-medium text-gray-900">{{ range.count }}</span>
                <span class="text-xs text-gray-500 ml-1">({{ formatPercentage(range.percentage) }})</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Top Performers -->
      <div>
        <h4 class="text-sm font-medium text-gray-900 mb-3">Top Performers This Month</h4>
        <div class="space-y-3">
          <div
            v-for="(performer, index) in topPerformers.slice(0, 5)"
            :key="performer.id"
            class="flex items-center justify-between p-3 bg-green-50 rounded-lg"
          >
            <div class="flex items-center space-x-3">
              <div class="flex-shrink-0 h-8 w-8 bg-green-100 rounded-full flex items-center justify-center">
                <span class="text-sm font-medium text-green-600">{{ index + 1 }}</span>
              </div>
              <div class="flex-shrink-0">
                <div class="h-8 w-8 bg-gray-200 rounded-full flex items-center justify-center">
                  <span class="text-xs font-medium text-gray-600">
                    {{ getInitials(performer.name) }}
                  </span>
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ performer.name }}</p>
                <p class="text-xs text-gray-500">{{ performer.department }} • {{ performer.position }}</p>
              </div>
            </div>
            <div class="text-right">
              <p class="text-sm font-medium text-gray-900">{{ formatNumber(performer.score, 1) }}</p>
              <p class="text-xs text-gray-500">{{ formatCurrency(performer.commissions) }} earned</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Performance Trends -->
      <div>
        <h4 class="text-sm font-medium text-gray-900 mb-3">Performance Trends (Last 6 Months)</h4>
        <div class="flex items-end justify-between h-20 space-x-1">
          <div
            v-for="(trend, index) in performanceTrends"
            :key="index"
            class="flex-1 bg-blue-200 rounded-t transition-all duration-300 hover:bg-blue-300"
            :style="{ height: `${(trend.score / 10) * 100}%` }"
            :title="`${trend.month}: ${trend.score}`"
          ></div>
        </div>
        <div class="flex justify-between text-xs text-gray-500 mt-1">
          <span>{{ performanceTrends[0]?.month || '' }}</span>
          <span>{{ performanceTrends[performanceTrends.length - 1]?.month || '' }}</span>
        </div>
      </div>

      <!-- Commission Performance -->
      <div>
        <h4 class="text-sm font-medium text-gray-900 mb-3">Commission Performance</h4>
        <div class="grid grid-cols-2 gap-4">
          <div class="text-center p-3 bg-green-50 rounded-lg">
            <p class="text-lg font-semibold text-green-600">{{ formatCurrency(commissionStats.thisMonth) }}</p>
            <p class="text-xs text-gray-600">This Month</p>
          </div>
          <div class="text-center p-3 bg-blue-50 rounded-lg">
            <p class="text-lg font-semibold text-blue-600">{{ formatCurrency(commissionStats.lastMonth) }}</p>
            <p class="text-xs text-gray-600">Last Month</p>
          </div>
        </div>
        <div class="mt-3 text-center">
          <span
            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
            :class="commissionStats.growth >= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
          >
            <svg
              class="w-3 h-3 mr-1"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                v-if="commissionStats.growth >= 0"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"
              />
              <path
                v-else
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"
              />
            </svg>
            {{ Math.abs(commissionStats.growth).toFixed(1) }}% {{ commissionStats.growth >= 0 ? 'increase' : 'decrease' }}
          </span>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="pt-4 border-t border-gray-200">
        <div class="grid grid-cols-2 gap-2">
          <button
            @click="$emit('conduct-review')"
            class="bg-blue-600 text-white text-sm font-medium py-2 px-3 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Conduct Review
          </button>
          <button
            @click="$emit('view-reports')"
            class="bg-gray-100 text-gray-700 text-sm font-medium py-2 px-3 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
          >
            View Reports
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { formatNumber, formatCurrency, formatPercentage } from '@/utils/formatting'

interface PerformanceStats {
  averageScore: number
  topPerformers: number
  goalAchievementRate: number
  totalCommissions: number
}

interface PerformanceDistribution {
  label: string
  count: number
  percentage: number
  color: string
}

interface TopPerformer {
  id: number
  name: string
  department: string
  position: string
  score: number
  commissions: number
}

interface PerformanceTrend {
  month: string
  score: number
}

interface CommissionStats {
  thisMonth: number
  lastMonth: number
  growth: number
}

// Emits
defineEmits<{
  'view-detailed-analytics': []
  'conduct-review': []
  'view-reports': []
}>()

// Reactive data
const isLoading = ref(false)
const performanceStats = ref<PerformanceStats>({
  averageScore: 0,
  topPerformers: 0,
  goalAchievementRate: 0,
  totalCommissions: 0
})

const performanceDistribution = ref<PerformanceDistribution[]>([
  { label: 'Excellent (9-10)', count: 0, percentage: 0, color: '#10b981' },
  { label: 'Good (7-8)', count: 0, percentage: 0, color: '#3b82f6' },
  { label: 'Average (5-6)', count: 0, percentage: 0, color: '#f59e0b' },
  { label: 'Poor (0-4)', count: 0, percentage: 0, color: '#ef4444' }
])

const topPerformers = ref<TopPerformer[]>([])
const performanceTrends = ref<PerformanceTrend[]>([])
const commissionStats = ref<CommissionStats>({
  thisMonth: 0,
  lastMonth: 0,
  growth: 0
})

// Methods
const getInitials = (name: string): string => {
  return name.split(' ').map(n => n.charAt(0)).join('').toUpperCase().slice(0, 2)
}

const loadPerformanceStats = async () => {
  isLoading.value = true
  try {
    const response = await fetch('/api/admin/performance-stats')
    if (response.ok) {
      const data = await response.json()
      performanceStats.value = data.stats
      performanceDistribution.value = data.distribution || performanceDistribution.value
      topPerformers.value = data.topPerformers || []
      performanceTrends.value = data.trends || []
      commissionStats.value = data.commissionStats || commissionStats.value
    }
  } catch (error) {
    console.error('Failed to load performance stats:', error)
  } finally {
    isLoading.value = false
  }
}

// Lifecycle
onMounted(() => {
  loadPerformanceStats()
})
</script>