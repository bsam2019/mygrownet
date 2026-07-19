<template>
  <CMSLayout title="Purchase Orders">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Purchase Orders</h1>
          <p class="mt-1 text-sm text-gray-500">Manage material purchase orders</p>
        </div>
        <Link
          :href="route('cms.purchase-orders.create')"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
        >
          <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
          Create Purchase Order
        </Link>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              v-model="filters.status"
              class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
              @change="applyFilters"
            >
              <option value="">All Statuses</option>
              <option value="draft">Draft</option>
              <option value="sent">Sent</option>
              <option value="confirmed">Confirmed</option>
              <option value="received">Received</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Job</label>
            <input
              v-model="filters.job_id"
              type="text"
              placeholder="Job ID"
              class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
              @input="debouncedSearch"
            />
          </div>
          <div class="flex items-end">
            <button
              @click="clearFilters"
              class="w-full px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition"
            >
              Clear Filters
            </button>
          </div>
        </div>
      </div>

      <!-- Purchase Orders Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  PO Number
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Supplier
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Job
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Order Date
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Total Amount
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
              <tr v-for="po in purchaseOrders.data" :key="po.id" class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <Link
                    :href="route('cms.purchase-orders.show', po.id)"
                    class="text-sm font-medium text-blue-600 hover:text-blue-900"
                  >
                    {{ po.po_number }}
                  </Link>
                </td>
                <td class="px-6 py-4">
                  <div class="flex flex-col">
                    <span class="text-sm font-medium text-gray-900">{{ po.supplier_name }}</span>
                    <span v-if="po.supplier_contact" class="text-xs text-gray-500">{{ po.supplier_contact }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  <Link
                    v-if="po.job"
                    :href="route('cms.jobs.show', po.job.id)"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    {{ po.job.job_number }}
                  </Link>
                  <span v-else class="text-gray-400">-</span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  {{ formatDate(po.order_date) }}
                </td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                  K {{ formatMoney(po.total_amount) }}
                </td>
                <td class="px-6 py-4">
                  <span
                    :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                      getStatusColor(po.status)
                    ]"
                  >
                    {{ getStatusLabel(po.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right text-sm font-medium">
                  <Link
                    :href="route('cms.purchase-orders.show', po.id)"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    View
                  </Link>
                </td>
              </tr>
              <tr v-if="purchaseOrders.data.length === 0">
                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                  <DocumentTextIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                  <p class="mt-2">No purchase orders found</p>
                  <Link
                    :href="route('cms.purchase-orders.create')"
                    class="mt-4 inline-flex items-center text-blue-600 hover:text-blue-700"
                  >
                    <PlusIcon class="h-5 w-5 mr-1" aria-hidden="true" />
                    Create your first purchase order
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="purchaseOrders.data.length > 0" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
          <Pagination :links="purchaseOrders.links" />
        </div>
      </div>
    </div>
  </CMSLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import CMSLayout from '@/Layouts/CMSLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { PlusIcon, DocumentTextIcon } from '@heroicons/vue/24/outline'
import { debounce } from 'lodash'

const props = defineProps({
  purchaseOrders: Object,
  filters: Object,
})

const filters = reactive({
  status: props.filters.status || '',
  job_id: props.filters.job_id || '',
})

const applyFilters = () => {
  router.get(route('cms.purchase-orders.index'), filters, {
    preserveState: true,
    preserveScroll: true,
  })
}

const debouncedSearch = debounce(() => {
  applyFilters()
}, 300)

const clearFilters = () => {
  filters.status = ''
  filters.job_id = ''
  applyFilters()
}

const formatMoney = (value) => {
  return parseFloat(value || 0).toFixed(2)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

const getStatusColor = (status) => {
  const colors = {
    draft: 'bg-gray-100 text-gray-800',
    sent: 'bg-blue-100 text-blue-800',
    confirmed: 'bg-indigo-100 text-indigo-800',
    received: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
  }
  return colors[status] || 'bg-gray-100 text-gray-800'
}

const getStatusLabel = (status) => {
  const labels = {
    draft: 'Draft',
    sent: 'Sent',
    confirmed: 'Confirmed',
    received: 'Received',
    cancelled: 'Cancelled',
  }
  return labels[status] || status
}
</script>
