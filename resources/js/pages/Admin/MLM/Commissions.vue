<template>
  <AdminLayout>
    <Head title="Commission Management" />
    
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Commission Management</h1>
          <p class="text-gray-600">Oversee and manage MLM commission payments</p>
        </div>
        
        <div class="flex items-center space-x-4">
          <button 
            @click="exportCommissions"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
          >
            <ArrowDownTrayIcon class="-ml-1 mr-2 h-4 w-4" />
            Export
          </button>
          
          <button 
            v-if="selectedCommissions.length > 0"
            @click="showBulkActionModal = true"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
          >
            Process Selected ({{ selectedCommissions.length }})
          </button>
        </div>
      </div>

      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <ClockIcon class="h-6 w-6 text-yellow-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Pending</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ pendingCount }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <CheckCircleIcon class="h-6 w-6 text-green-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Paid Today</dt>
                  <dd class="text-lg font-medium text-gray-900">
                    K{{ formatNumber(statistics.by_status?.paid?.total_amount || 0) }}
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <CurrencyDollarIcon class="h-6 w-6 text-blue-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Total Volume</dt>
                  <dd class="text-lg font-medium text-gray-900">
                    K{{ formatNumber(Object.values(statistics.by_type || {}).reduce((sum: number, stat: any) => sum + (stat.total_amount || 0), 0)) }}
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <ChartBarIcon class="h-6 w-6 text-purple-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Avg Commission</dt>
                  <dd class="text-lg font-medium text-gray-900">
                    K{{ formatNumber(Object.values(statistics.by_type || {}).reduce((sum: number, stat: any) => sum + (stat.avg_amount || 0), 0) / Object.keys(statistics.by_type || {}).length || 0) }}
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Status</label>
              <select 
                v-model="filters.status" 
                @change="applyFilters"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              >
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="paid">Paid</option>
                <option value="rejected">Rejected</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Type</label>
              <select 
                v-model="filters.type" 
                @change="applyFilters"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              >
                <option value="">All Types</option>
                <option value="REFERRAL">Referral</option>
                <option value="TEAM_VOLUME">Team Volume</option>
                <option value="PERFORMANCE">Performance</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Level</label>
              <select 
                v-model="filters.level" 
                @change="applyFilters"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              >
                <option value="">All Levels</option>
                <option value="1">Level 1</option>
                <option value="2">Level 2</option>
                <option value="3">Level 3</option>
                <option value="4">Level 4</option>
                <option value="5">Level 5</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">From Date</label>
              <input 
                v-model="filters.date_from" 
                @change="applyFilters"
                type="date"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">To Date</label>
              <input 
                v-model="filters.date_to" 
                @change="applyFilters"
                type="date"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Search</label>
              <input 
                v-model="filters.search" 
                @input="debounceSearch"
                type="text"
                placeholder="Search by name or email..."
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Commissions Table -->
      <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-4 py-5 sm:p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
              Commission Records
            </h3>
            
            <div class="flex items-center space-x-2">
              <input 
                type="checkbox" 
                :checked="allSelected"
                @change="toggleSelectAll"
                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
              <label class="text-sm text-gray-700">Select All</label>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Select
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Referrer
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Referee
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Level
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Type
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Amount
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Created
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr 
                  v-for="commission in commissions.data" 
                  :key="commission.id"
                  class="hover:bg-gray-50"
                >
                  <td class="px-6 py-4 whitespace-nowrap">
                    <input 
                      type="checkbox" 
                      :value="commission.id"
                      v-model="selectedCommissions"
                      class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    />
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">
                      {{ commission.referrer?.name || 'N/A' }}
                    </div>
                    <div class="text-sm text-gray-500">
                      {{ commission.referrer?.email || 'N/A' }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">
                      {{ commission.referee?.name || 'N/A' }}
                    </div>
                    <div class="text-sm text-gray-500">
                      {{ commission.referee?.email || 'N/A' }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                      Level {{ commission.level }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                      commission.commission_type === 'REFERRAL' ? 'bg-green-100 text-green-800' :
                      commission.commission_type === 'TEAM_VOLUME' ? 'bg-purple-100 text-purple-800' :
                      'bg-orange-100 text-orange-800'
                    ]">
                      {{ commission.commission_type }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    K{{ formatNumber(commission.amount) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                      commission.status === 'paid' ? 'bg-green-100 text-green-800' :
                      commission.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                      'bg-red-100 text-red-800'
                    ]">
                      {{ commission.status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatDate(commission.created_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex space-x-2">
                      <button 
                        @click="viewCommissionDetails(commission.id)"
                        class="text-blue-600 hover:text-blue-900"
                      >
                        View
                      </button>
                      <button 
                        v-if="commission.status === 'pending'"
                        @click="adjustCommission(commission)"
                        class="text-indigo-600 hover:text-indigo-900"
                      >
                        Adjust
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="mt-6">
            <nav class="flex items-center justify-between">
              <div class="flex-1 flex justify-between sm:hidden">
                <Link 
                  v-if="commissions.prev_page_url"
                  :href="commissions.prev_page_url"
                  class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                >
                  Previous
                </Link>
                <Link 
                  v-if="commissions.next_page_url"
                  :href="commissions.next_page_url"
                  class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                >
                  Next
                </Link>
              </div>
              <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                  <p class="text-sm text-gray-700">
                    Showing {{ commissions.from }} to {{ commissions.to }} of {{ commissions.total }} results
                  </p>
                </div>
                <div>
                  <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                    <Link 
                      v-for="link in commissions.links" 
                      :key="link.label"
                      :href="link.url"
                      v-html="link.label"
                      :class="[
                        'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                        link.active 
                          ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' 
                          : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                      ]"
                    />
                  </nav>
                </div>
              </div>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <!-- Commission Details Modal -->
    <CommissionDetailsModal 
      :show="showDetailsModal"
      :commission-id="selectedCommissionId"
      @close="showDetailsModal = false"
    />

    <!-- Commission Adjustment Modal -->
    <CommissionAdjustmentModal 
      :show="showAdjustmentModal"
      :commission="commissionToAdjust"
      @close="showAdjustmentModal = false"
      @adjusted="handleCommissionAdjusted"
    />

    <!-- Bulk Action Modal -->
    <BulkActionModal 
      :show="showBulkActionModal"
      :commission-ids="selectedCommissions"
      @close="showBulkActionModal = false"
      @processed="handleBulkProcessed"
    />
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import CommissionDetailsModal from '@/components/Admin/MLM/CommissionDetailsModal.vue'
import CommissionAdjustmentModal from '@/components/Admin/MLM/CommissionAdjustmentModal.vue'
import BulkActionModal from '@/components/Admin/MLM/BulkActionModal.vue'
import {
  ArrowDownTrayIcon,
  ChartBarIcon,
  CheckCircleIcon,
  ClockIcon,
  CurrencyDollarIcon,
} from '@heroicons/vue/24/outline'

interface Props {
  commissions: any
  filters: any
  statistics: any
  pendingCount: number
}

const props = defineProps<Props>()

const selectedCommissions = ref<number[]>([])
const showDetailsModal = ref(false)
const showAdjustmentModal = ref(false)
const showBulkActionModal = ref(false)
const selectedCommissionId = ref<number | null>(null)
const commissionToAdjust = ref<any>(null)

const filters = ref({
  status: props.filters.status || '',
  type: props.filters.type || '',
  level: props.filters.level || '',
  search: props.filters.search || '',
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || '',
})

const allSelected = computed(() => {
  return props.commissions.data.length > 0 && 
         selectedCommissions.value.length === props.commissions.data.length
})

const formatNumber = (value: number): string => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  }).format(value)
}

const formatDate = (date: string): string => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

let searchTimeout: NodeJS.Timeout

const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 500)
}

const applyFilters = () => {
  router.get(route('admin.mlm.commissions'), filters.value, {
    preserveState: true,
    replace: true,
  })
}

const toggleSelectAll = () => {
  if (allSelected.value) {
    selectedCommissions.value = []
  } else {
    selectedCommissions.value = props.commissions.data.map((c: any) => c.id)
  }
}

const viewCommissionDetails = (commissionId: number) => {
  selectedCommissionId.value = commissionId
  showDetailsModal.value = true
}

const adjustCommission = (commission: any) => {
  commissionToAdjust.value = commission
  showAdjustmentModal.value = true
}

const handleCommissionAdjusted = () => {
  showAdjustmentModal.value = false
  router.reload({ only: ['commissions', 'statistics'] })
}

const handleBulkProcessed = () => {
  showBulkActionModal.value = false
  selectedCommissions.value = []
  router.reload({ only: ['commissions', 'statistics', 'pendingCount'] })
}

const exportCommissions = () => {
  const params = new URLSearchParams(filters.value as any)
  window.open(route('admin.mlm.export-commissions') + '?' + params.toString())
}
</script>