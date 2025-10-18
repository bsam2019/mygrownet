<template>
  <div class="bg-white rounded-lg shadow border p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-medium text-gray-900">Client Portfolio</h3>
      <button
        @click="$emit('view-all-clients')"
        class="text-sm text-blue-600 hover:text-blue-800 font-medium"
      >
        View All Clients
      </button>
    </div>

    <div v-if="isLoading" class="flex justify-center py-8">
      <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
    </div>

    <div v-else-if="portfolioSummary" class="space-y-6">
      <!-- Portfolio Summary -->
      <div class="grid grid-cols-3 gap-4">
        <div class="text-center p-3 bg-blue-50 rounded-lg">
          <p class="text-2xl font-bold text-blue-600">{{ portfolioSummary.totalClients }}</p>
          <p class="text-xs text-gray-600">Total Clients</p>
        </div>
        <div class="text-center p-3 bg-green-50 rounded-lg">
          <p class="text-2xl font-bold text-green-600">{{ portfolioSummary.activeInvestments }}</p>
          <p class="text-xs text-gray-600">Active Investments</p>
        </div>
        <div class="text-center p-3 bg-purple-50 rounded-lg">
          <p class="text-2xl font-bold text-purple-600">{{ formatCurrency(portfolioSummary.totalValue) }}</p>
          <p class="text-xs text-gray-600">Portfolio Value</p>
        </div>
      </div>

      <!-- Recent Client Activity -->
      <div v-if="recentActivity.length > 0">
        <h4 class="text-sm font-medium text-gray-900 mb-3">Recent Client Activity</h4>
        <div class="space-y-3">
          <div
            v-for="activity in recentActivity.slice(0, 4)"
            :key="activity.id"
            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
          >
            <div class="flex items-center space-x-3">
              <div class="flex-shrink-0">
                <div class="h-8 w-8 bg-gray-200 rounded-full flex items-center justify-center">
                  <span class="text-xs font-medium text-gray-600">
                    {{ getInitials(activity.clientName) }}
                  </span>
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ activity.clientName }}</p>
                <p class="text-xs text-gray-500">{{ activity.description }}</p>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <span
                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                :class="getActivityTypeClass(activity.type)"
              >
                {{ formatActivityType(activity.type) }}
              </span>
              <span class="text-xs text-gray-400">{{ formatDate(activity.date) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Top Clients by Investment -->
      <div v-if="topClients.length > 0">
        <h4 class="text-sm font-medium text-gray-900 mb-3">Top Clients by Investment</h4>
        <div class="space-y-2">
          <div
            v-for="(client, index) in topClients.slice(0, 5)"
            :key="client.id"
            class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg"
          >
            <div class="flex items-center space-x-3">
              <span class="text-sm font-medium text-gray-500 w-4">{{ index + 1 }}</span>
              <div class="flex-shrink-0">
                <div class="h-6 w-6 bg-blue-100 rounded-full flex items-center justify-center">
                  <span class="text-xs font-medium text-blue-600">
                    {{ getInitials(client.name) }}
                  </span>
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ client.name }}</p>
                <p class="text-xs text-gray-500">{{ client.tier }}</p>
              </div>
            </div>
            <div class="text-right">
              <p class="text-sm font-medium text-gray-900">{{ formatCurrency(client.investmentAmount) }}</p>
              <p class="text-xs text-gray-500">{{ formatPercentage(client.returnRate) }} return</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Commission Summary -->
      <div class="pt-4 border-t border-gray-200">
        <div class="flex items-center justify-between mb-3">
          <h4 class="text-sm font-medium text-gray-900">Commission Summary</h4>
          <span class="text-xs text-gray-500">This Month</span>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div class="text-center p-3 bg-green-50 rounded-lg">
            <p class="text-lg font-semibold text-green-600">{{ formatCurrency(commissionSummary.earned) }}</p>
            <p class="text-xs text-gray-600">Earned</p>
          </div>
          <div class="text-center p-3 bg-yellow-50 rounded-lg">
            <p class="text-lg font-semibold text-yellow-600">{{ formatCurrency(commissionSummary.pending) }}</p>
            <p class="text-xs text-gray-600">Pending</p>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="pt-4 border-t border-gray-200">
        <div class="grid grid-cols-2 gap-2">
          <button
            @click="$emit('add-client')"
            class="bg-blue-600 text-white text-sm font-medium py-2 px-3 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Add Client
          </button>
          <button
            @click="$emit('view-commissions')"
            class="bg-gray-100 text-gray-700 text-sm font-medium py-2 px-3 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
          >
            View Commissions
          </button>
        </div>
      </div>
    </div>

    <div v-else class="text-center py-8">
      <p class="text-gray-500">No client portfolio data available</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { formatCurrency, formatPercentage } from '@/utils/formatting'

interface PortfolioSummary {
  totalClients: number
  activeInvestments: number
  totalValue: number
}

interface ClientActivity {
  id: number
  clientName: string
  description: string
  type: string
  date: string
}

interface TopClient {
  id: number
  name: string
  tier: string
  investmentAmount: number
  returnRate: number
}

interface CommissionSummary {
  earned: number
  pending: number
}

// Props
const props = defineProps<{
  employeeId?: number
}>()

// Emits
defineEmits<{
  'view-all-clients': []
  'add-client': []
  'view-commissions': []
}>()

// Reactive data
const isLoading = ref(false)
const portfolioSummary = ref<PortfolioSummary | null>(null)
const recentActivity = ref<ClientActivity[]>([])
const topClients = ref<TopClient[]>([])
const commissionSummary = ref<CommissionSummary>({ earned: 0, pending: 0 })

// Methods
const getInitials = (name: string): string => {
  return name.split(' ').map(n => n.charAt(0)).join('').toUpperCase().slice(0, 2)
}

const getActivityTypeClass = (type: string): string => {
  switch (type.toLowerCase()) {
    case 'investment':
      return 'bg-green-100 text-green-800'
    case 'withdrawal':
      return 'bg-red-100 text-red-800'
    case 'upgrade':
      return 'bg-blue-100 text-blue-800'
    case 'meeting':
      return 'bg-purple-100 text-purple-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const formatActivityType = (type: string): string => {
  return type.charAt(0).toUpperCase() + type.slice(1).toLowerCase()
}

const formatDate = (dateString: string): string => {
  const date = new Date(dateString)
  const now = new Date()
  const diffTime = Math.abs(now.getTime() - date.getTime())
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  
  if (diffDays === 1) return '1d ago'
  if (diffDays < 7) return `${diffDays}d ago`
  if (diffDays < 30) return `${Math.ceil(diffDays / 7)}w ago`
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

const loadClientPortfolio = async () => {
  if (!props.employeeId) return
  
  isLoading.value = true
  try {
    const response = await fetch(`/api/employee/${props.employeeId}/client-portfolio`)
    if (response.ok) {
      const data = await response.json()
      portfolioSummary.value = data.summary
      recentActivity.value = data.recentActivity || []
      topClients.value = data.topClients || []
      commissionSummary.value = data.commissionSummary || { earned: 0, pending: 0 }
    }
  } catch (error) {
    console.error('Failed to load client portfolio:', error)
  } finally {
    isLoading.value = false
  }
}

// Lifecycle
onMounted(() => {
  if (props.employeeId) {
    loadClientPortfolio()
  }
})
</script>