<script setup lang="ts">
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { MagnifyingGlassIcon, PlusIcon, FunnelIcon } from '@heroicons/vue/24/outline'
import CMSLayoutNew from '@/Layouts/CMSLayoutNew.vue'

defineOptions({
  layout: CMSLayoutNew
})

interface Props {
  assets: any
  categories: string[]
  staff: any[]
  filters: any
}

const props = defineProps<Props>()

const search = ref(props.filters.search || '')
const selectedCategory = ref(props.filters.category || '')
const selectedStatus = ref(props.filters.status || '')
const selectedStaff = ref(props.filters.assigned_to || '')

const applyFilters = () => {
  router.get(route('cms.assets.index'), {
    search: search.value,
    category: selectedCategory.value,
    status: selectedStatus.value,
    assigned_to: selectedStaff.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}

const clearFilters = () => {
  search.value = ''
  selectedCategory.value = ''
  selectedStatus.value = ''
  selectedStaff.value = ''
  applyFilters()
}

const getStatusClass = (status: string) => {
  const classes = {
    available: 'bg-green-100 text-green-800',
    in_use: 'bg-blue-100 text-blue-800',
    maintenance: 'bg-orange-100 text-orange-800',
    retired: 'bg-gray-100 text-gray-800',
  }
  return classes[status as keyof typeof classes] || classes.available
}

const getConditionClass = (condition: string) => {
  const classes = {
    excellent: 'bg-green-100 text-green-800',
    good: 'bg-blue-100 text-blue-800',
    fair: 'bg-yellow-100 text-yellow-800',
    poor: 'bg-red-100 text-red-800',
  }
  return classes[condition as keyof typeof classes] || classes.good
}

const formatNumber = (value: number) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value)
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <!-- Page Header -->
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Assets</h1>
        <p class="mt-1 text-sm text-gray-500">Manage company assets and equipment</p>
      </div>
      <div class="mt-4 sm:mt-0">
        <Link
          :href="route('cms.assets.create')"
          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium"
        >
          <PlusIcon class="h-5 w-5" aria-hidden="true" />
          Add Asset
        </Link>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <div class="relative">
            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
            <input
              v-model="search"
              type="text"
              placeholder="Name, number, serial..."
              class="pl-10 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              @keyup.enter="applyFilters"
            />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
          <select
            v-model="selectedCategory"
            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option value="">All Categories</option>
            <option v-for="category in categories" :key="category" :value="category">
              {{ category }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select
            v-model="selectedStatus"
            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option value="">All Statuses</option>
            <option value="available">Available</option>
            <option value="in_use">In Use</option>
            <option value="maintenance">Maintenance</option>
            <option value="retired">Retired</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Assigned To</label>
          <select
            v-model="selectedStaff"
            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option value="">All Staff</option>
            <option v-for="member in staff" :key="member.id" :value="member.id">
              {{ member.user.name }}
            </option>
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

    <!-- Assets Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Asset
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Category
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Status
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Condition
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Assigned To
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Value
            </th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="asset in assets.data" :key="asset.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">
              <div>
                <div class="text-sm font-medium text-gray-900">{{ asset.name }}</div>
                <div class="text-sm text-gray-500">{{ asset.asset_number }}</div>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-900">{{ asset.category }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span
                :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusClass(asset.status)]"
              >
                {{ asset.status.replace('_', ' ').toUpperCase() }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span
                :class="['px-2 py-1 text-xs font-medium rounded-full', getConditionClass(asset.condition)]"
              >
                {{ asset.condition.toUpperCase() }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div v-if="asset.assigned_to" class="text-sm text-gray-900">
                {{ asset.assigned_to.user.name }}
              </div>
              <div v-else class="text-sm text-gray-500">-</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-900">K{{ formatNumber(asset.current_value) }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <Link
                :href="route('cms.assets.show', asset.id)"
                class="text-blue-600 hover:text-blue-900"
              >
                View
              </Link>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Empty State -->
      <div v-if="assets.data.length === 0" class="text-center py-12">
        <p class="text-gray-500">No assets found</p>
      </div>

      <!-- Pagination -->
      <div v-if="assets.data.length > 0" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Showing {{ assets.from }} to {{ assets.to }} of {{ assets.total }} results
          </div>
          <div class="flex gap-2">
            <Link
              v-if="assets.prev_page_url"
              :href="assets.prev_page_url"
              class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50"
            >
              Previous
            </Link>
            <Link
              v-if="assets.next_page_url"
              :href="assets.next_page_url"
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
