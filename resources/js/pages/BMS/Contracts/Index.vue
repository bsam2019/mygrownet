<script setup lang="ts">
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { PlusIcon, FunnelIcon } from '@heroicons/vue/24/outline'
import BMSLayout from '@/Layouts/BMSLayout.vue'
import Pagination from '@/Components/Pagination.vue'

defineOptions({ layout: BMSLayout })

interface Props {
  contracts: { data: any[]; links: any }
  filters: { search?: string; status?: string }
  stats: { active: number; expiring_soon: number; overdue: number }
}

const props = defineProps<Props>()

const search = ref(props.filters.search || '')
const selectedStatus = ref(props.filters.status || '')

const applyFilters = () => {
  router.get(route('bms.contracts.index'), { search: search.value, status: selectedStatus.value }, { preserveState: true })
}

const formatMoney = (amount: number) => 'K' + amount.toLocaleString('en-US', { minimumFractionDigits: 2 })
const formatDate = (date: string) => date ? new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '-'

const statusColors: Record<string, string> = {
  draft: 'bg-gray-100 text-gray-700',
  active: 'bg-green-100 text-green-700',
  expired: 'bg-red-100 text-red-700',
  terminated: 'bg-red-100 text-red-700',
  renewed: 'bg-blue-100 text-blue-700',
}
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Contracts</h1>
      <Link :href="route('bms.contracts.create')" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
        <PlusIcon class="h-5 w-5" /> New Contract
      </Link>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-3 gap-4 mb-6">
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <p class="text-sm text-gray-500 font-medium">Active</p>
        <p class="text-2xl font-bold text-green-600 mt-1">{{ stats.active }}</p>
      </div>
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <p class="text-sm text-gray-500 font-medium">Expiring Soon (30d)</p>
        <p class="text-2xl font-bold text-yellow-600 mt-1">{{ stats.expiring_soon }}</p>
      </div>
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <p class="text-sm text-gray-500 font-medium">Overdue</p>
        <p class="text-2xl font-bold text-red-600 mt-1">{{ stats.overdue }}</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
      <div class="flex items-center gap-4">
        <div class="flex-1">
          <input v-model="search" @input="applyFilters" placeholder="Search contracts..." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
        </div>
        <select v-model="selectedStatus" @change="applyFilters" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
          <option value="">All Statuses</option>
          <option value="draft">Draft</option>
          <option value="active">Active</option>
          <option value="expired">Expired</option>
          <option value="terminated">Terminated</option>
          <option value="renewed">Renewed</option>
        </select>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contract #</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Start</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">End</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Value</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="c in contracts.data" :key="c.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ c.contract_number }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-medium">
                <Link :href="route('bms.contracts.show', c.id)">{{ c.title }}</Link>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ c.customer?.name || '-' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ formatDate(c.start_date) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm" :class="c.status === 'active' && c.end_date && new Date(c.end_date) < new Date() ? 'text-red-600 font-medium' : 'text-gray-600'">{{ formatDate(c.end_date) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">{{ formatMoney(c.total_value) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-center">
                <span :class="['px-2 py-1 text-xs font-medium rounded-full', statusColors[c.status] || 'bg-gray-100']">{{ c.status }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                <Link :href="route('bms.contracts.show', c.id)" class="text-blue-600 hover:text-blue-700 font-medium">View</Link>
              </td>
            </tr>
            <tr v-if="contracts.data.length === 0">
              <td colspan="8" class="px-6 py-12 text-center text-sm text-gray-500">No contracts found</td>
            </tr>
          </tbody>
        </table>
      </div>
      <Pagination :links="contracts.links" />
    </div>
  </div>
</template>
