<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import CMSLayout from '@/Layouts/CMSLayout.vue'
import PageToolbar from '@/components/CMS/PageToolbar.vue'
import { MenuItem } from '@headlessui/vue'
import { 
  MagnifyingGlassIcon, 
  FunnelIcon,
  ArrowDownTrayIcon,
  PlusIcon,
  DocumentDuplicateIcon,
  TrashIcon
} from '@heroicons/vue/24/outline'

interface Props {
  invoices: any
  summary: any
  filters: {
    status: string
    search: string
  }
  statuses: Array<{ value: string; label: string; color: string }>
}

const props = defineProps<Props>()

const searchQuery = ref(props.filters.search)
const selectedStatus = ref(props.filters.status)

const filteredInvoices = computed(() => {
  return props.invoices.data || []
})

const handleSearch = () => {
  router.get(route('cms.invoices.index'), {
    search: searchQuery.value,
    status: selectedStatus.value
  }, { preserveState: true })
}

const handleStatusFilter = (status: string) => {
  selectedStatus.value = status
  router.get(route('cms.invoices.index'), {
    search: searchQuery.value,
    status: status
  }, { preserveState: true })
}

const handleExport = () => {
  alert('Export functionality - to be implemented')
}

const handleBulkDelete = () => {
  alert('Bulk delete - to be implemented')
}

const handleCreateInvoice = () => {
  router.visit(route('cms.invoices.create'))
}
</script>

<template>
  <Head title="Invoices" />
  
  <CMSLayout>
    <PageToolbar 
      title="Invoices" 
      :subtitle="`${invoices.total || 0} total invoices`"
      :show-more-menu="true"
    >
      <!-- Filters Slot -->
      <template #filters>
        <!-- Status Filter -->
        <select
          v-model="selectedStatus"
          @change="handleStatusFilter(selectedStatus)"
          class="rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="all">All Status</option>
          <option 
            v-for="status in statuses" 
            :key="status.value" 
            :value="status.value"
          >
            {{ status.label }}
          </option>
        </select>

        <!-- Search -->
        <div class="relative">
          <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
          <input
            v-model="searchQuery"
            @keyup.enter="handleSearch"
            type="text"
            placeholder="Search invoices..."
            class="pl-10 pr-4 py-2 rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500"
          />
        </div>
      </template>

      <!-- Actions Slot -->
      <template #actions>
        <button
          @click="handleExport"
          class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
        >
          <ArrowDownTrayIcon class="h-5 w-5" aria-hidden="true" />
          Export
        </button>

        <button
          @click="handleCreateInvoice"
          class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700"
        >
          <PlusIcon class="h-5 w-5" aria-hidden="true" />
          New Invoice
        </button>
      </template>

      <!-- More Menu Slot -->
      <template #menu>
        <MenuItem v-slot="{ active }">
          <button
            @click="handleBulkDelete"
            :class="[
              active ? 'bg-gray-100' : '',
              'flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-700'
            ]"
          >
            <TrashIcon class="h-5 w-5" aria-hidden="true" />
            Bulk Delete
          </button>
        </MenuItem>
        <MenuItem v-slot="{ active }">
          <button
            :class="[
              active ? 'bg-gray-100' : '',
              'flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-700'
            ]"
          >
            <DocumentDuplicateIcon class="h-5 w-5" aria-hidden="true" />
            Duplicate Selected
          </button>
        </MenuItem>
      </template>

      <!-- Mobile Filters Slot -->
      <template #mobile-filters>
        <div class="flex flex-col gap-2">
          <select
            v-model="selectedStatus"
            @change="handleStatusFilter(selectedStatus)"
            class="rounded-lg border-gray-300 text-sm"
          >
            <option value="all">All Status</option>
            <option 
              v-for="status in statuses" 
              :key="status.value" 
              :value="status.value"
            >
              {{ status.label }}
            </option>
          </select>

          <div class="relative">
            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
            <input
              v-model="searchQuery"
              @keyup.enter="handleSearch"
              type="text"
              placeholder="Search..."
              class="w-full pl-10 pr-4 py-2 rounded-lg border-gray-300 text-sm"
            />
          </div>
        </div>
      </template>
    </PageToolbar>

    <!-- Content Area -->
    <div class="p-6">
      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg border border-gray-200 p-4">
          <p class="text-sm text-gray-500">Total Amount</p>
          <p class="text-2xl font-bold text-gray-900">K{{ summary?.total_amount || 0 }}</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4">
          <p class="text-sm text-gray-500">Paid</p>
          <p class="text-2xl font-bold text-green-600">K{{ summary?.paid_amount || 0 }}</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4">
          <p class="text-sm text-gray-500">Pending</p>
          <p class="text-2xl font-bold text-amber-600">K{{ summary?.pending_amount || 0 }}</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4">
          <p class="text-sm text-gray-500">Overdue</p>
          <p class="text-2xl font-bold text-red-600">K{{ summary?.overdue_amount || 0 }}</p>
        </div>
      </div>

      <!-- Invoices Table -->
      <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice #</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="invoice in filteredInvoices" :key="invoice.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ invoice.invoice_number }}</td>
              <td class="px-6 py-4 text-sm text-gray-700">{{ invoice.customer?.name }}</td>
              <td class="px-6 py-4 text-sm text-gray-900">K{{ invoice.total_amount }}</td>
              <td class="px-6 py-4">
                <span :class="[
                  'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                  invoice.status === 'paid' ? 'bg-green-100 text-green-800' :
                  invoice.status === 'pending' ? 'bg-amber-100 text-amber-800' :
                  'bg-red-100 text-red-800'
                ]">
                  {{ invoice.status }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-500">{{ invoice.invoice_date }}</td>
            </tr>
          </tbody>
        </table>

        <div v-if="filteredInvoices.length === 0" class="text-center py-12">
          <p class="text-gray-500">No invoices found</p>
        </div>
      </div>
    </div>
  </CMSLayout>
</template>
