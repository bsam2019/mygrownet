<template>
  <div>
    <!-- Page Header -->
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Customers</h1>
        <p class="mt-1 text-sm text-gray-500">Manage your customer database</p>
      </div>
      <button
        @click="slideOver?.open('customer')"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium flex items-center gap-2"
      >
        <PlusIcon class="h-5 w-5" aria-hidden="true" />
        <span>Add Customer</span>
      </button>
    </div>

    <!-- Filters -->
    <div>
      <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input
              v-model="filters.search"
              @input="applyFilters"
              type="text"
              placeholder="Search by name, number, phone, or email..."
              class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              v-model="filters.status"
              @change="applyFilters"
              class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
            >
              <option value="active">All Statuses</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Customers Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer #</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jobs</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Balance</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="customer in customers.data" :key="customer.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ customer.customer_number }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ customer.name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <div>{{ customer.phone }}</div>
                <div v-if="customer.email" class="text-xs">{{ customer.email }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ customer.jobs_count || 0 }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <span :class="customer.outstanding_balance > 0 ? 'text-red-600 font-medium' : 'text-gray-900'">
                  K{{ formatNumber(customer.outstanding_balance || 0) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="customer.status === 'active' 
                  ? 'px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800'
                  : 'px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800'
                ">
                  {{ customer.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <Link
                  :href="route('cms.customers.show', customer.id)"
                  class="text-blue-600 hover:text-blue-800"
                >
                  View
                </Link>
              </td>
            </tr>
            <tr v-if="customers.data.length === 0">
              <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                No customers found. Add your first customer to get started.
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div v-if="customers.data.length > 0" class="px-6 py-4 border-t border-gray-200">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-500">
              Showing {{ customers.from }} to {{ customers.to }} of {{ customers.total }} customers
            </div>
            <div class="flex gap-2">
              <Link
                v-for="link in customers.links"
                :key="link.label"
                :href="link.url"
                :class="[
                  'px-3 py-1 rounded',
                  link.active
                    ? 'bg-blue-600 text-white'
                    : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300',
                  !link.url && 'opacity-50 cursor-not-allowed',
                ]"
                v-html="link.label"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, inject } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import CMSLayout from '@/Layouts/CMSLayout.vue'
import { PlusIcon } from '@heroicons/vue/24/outline'

defineOptions({
  layout: CMSLayout
})

interface Props {
  customers: any
  filters: {
    search?: string
    status?: string
  }
}

const props = defineProps<Props>()

// Get slideOver from layout
const slideOver: any = inject('slideOver')

const filters = ref({ ...props.filters })

const applyFilters = () => {
  router.get(route('cms.customers.index'), filters.value, {
    preserveState: true,
    preserveScroll: true,
  })
}

const formatNumber = (value: number) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value)
}
</script>
