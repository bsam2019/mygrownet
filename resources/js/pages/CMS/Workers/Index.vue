<script setup lang="ts">
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { MagnifyingGlassIcon, PlusIcon, FunnelIcon } from '@heroicons/vue/24/outline'
import CMSLayoutNew from '@/Layouts/CMSLayoutNew.vue'

defineOptions({
  layout: CMSLayoutNew
})

interface Props {
  workers: any
  filters: any
}

const props = defineProps<Props>()

const search = ref(props.filters.search || '')
const selectedWorkerType = ref(props.filters.worker_type || '')
const selectedStatus = ref(props.filters.status || '')

const applyFilters = () => {
  router.get(route('cms.payroll.workers.index'), {
    search: search.value,
    worker_type: selectedWorkerType.value,
    status: selectedStatus.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}

const clearFilters = () => {
  search.value = ''
  selectedWorkerType.value = ''
  selectedStatus.value = ''
  applyFilters()
}

const getStatusClass = (status: string) => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    inactive: 'bg-gray-100 text-gray-800',
  }
  return classes[status as keyof typeof classes] || classes.active
}

const getWorkerTypeClass = (type: string) => {
  const classes = {
    casual: 'bg-blue-100 text-blue-800',
    contract: 'bg-purple-100 text-purple-800',
    permanent: 'bg-green-100 text-green-800',
  }
  return classes[type as keyof typeof classes] || classes.casual
}

const formatNumber = (value: number) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value)
}
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <!-- Page Header -->
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Workers</h1>
        <p class="mt-1 text-sm text-gray-500">Manage workers and contractors</p>
      </div>
      <div class="mt-4 sm:mt-0">
        <Link
          :href="route('cms.payroll.workers.create')"
          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium"
        >
          <PlusIcon class="h-5 w-5" aria-hidden="true" />
          Add Worker
        </Link>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <div class="relative">
            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
            <input
              v-model="search"
              type="text"
              placeholder="Name, number, phone..."
              class="pl-10 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              @keyup.enter="applyFilters"
            />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Worker Type</label>
          <select
            v-model="selectedWorkerType"
            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option value="">All Types</option>
            <option value="casual">Casual</option>
            <option value="contract">Contract</option>
            <option value="permanent">Permanent</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select
            v-model="selectedStatus"
            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option value="">All Statuses</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
      </div>

      <div class="flex gap-3 mt-4">
        <button
          @click="applyFilters"
          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium"
        >
          <FunnelIcon class="h-4 w-4" aria-hidden="true" />
          Apply Filters
        </button>
        <button
          @click="clearFilters"
          class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
        >
          Clear
        </button>
      </div>
    </div>

    <!-- Workers Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Worker
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Type
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Contact
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Rates
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Status
            </th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="worker in workers.data" :key="worker.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">
              <div>
                <div class="text-sm font-medium text-gray-900">{{ worker.name }}</div>
                <div class="text-sm text-gray-500">{{ worker.worker_number }}</div>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span
                :class="['px-2 py-1 text-xs font-medium rounded-full', getWorkerTypeClass(worker.worker_type)]"
              >
                {{ worker.worker_type.toUpperCase() }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-900">{{ worker.phone }}</div>
              <div v-if="worker.email" class="text-sm text-gray-500">{{ worker.email }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div v-if="worker.hourly_rate" class="text-sm text-gray-900">
                K{{ formatNumber(worker.hourly_rate) }}/hr
              </div>
              <div v-if="worker.daily_rate" class="text-sm text-gray-900">
                K{{ formatNumber(worker.daily_rate) }}/day
              </div>
              <div v-if="worker.commission_rate" class="text-sm text-gray-500">
                {{ worker.commission_rate }}% commission
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span
                :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusClass(worker.status)]"
              >
                {{ worker.status.toUpperCase() }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <Link
                :href="route('cms.payroll.workers.show', worker.id)"
                class="text-blue-600 hover:text-blue-900"
              >
                View
              </Link>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Empty State -->
      <div v-if="workers.data.length === 0" class="text-center py-12">
        <p class="text-gray-500">No workers found</p>
      </div>

      <!-- Pagination -->
      <div v-if="workers.data.length > 0" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Showing {{ workers.from }} to {{ workers.to }} of {{ workers.total }} results
          </div>
          <div class="flex gap-2">
            <Link
              v-if="workers.prev_page_url"
              :href="workers.prev_page_url"
              class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50"
            >
              Previous
            </Link>
            <Link
              v-if="workers.next_page_url"
              :href="workers.next_page_url"
              class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50"
            >
              Next
            </Link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
