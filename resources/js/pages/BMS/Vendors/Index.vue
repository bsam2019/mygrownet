<script setup lang="ts">
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { PlusIcon } from '@heroicons/vue/24/outline'
import CMSLayout from '@/Layouts/CMSLayout.vue'
import Pagination from '@/Components/Pagination.vue'

defineOptions({ layout: CMSLayout })

interface Props {
  vendors: { data: any[]; links: any }
  filters: { search?: string; vendor_type?: string; status?: string }
  vendorTypes: string[]
}

const props = defineProps<Props>()

const search = ref(props.filters.search || '')
const typeFilter = ref(props.filters.vendor_type || '')
const statusFilter = ref(props.filters.status || '')

const applyFilters = () => {
  router.get(route('cms.vendors.index'), { search: search.value, vendor_type: typeFilter.value, status: statusFilter.value }, { preserveState: true })
}

const formatDate = (date: string) => date ? new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '-'
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Vendors</h1>
      <Link :href="route('cms.vendors.create')" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
        <PlusIcon class="h-5 w-5" /> New Vendor
      </Link>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
      <div class="flex items-center gap-4">
        <input v-model="search" @input="applyFilters" placeholder="Search vendors..." class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm" />
        <select v-model="typeFilter" @change="applyFilters" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
          <option value="">All Types</option>
          <option v-for="t in vendorTypes" :key="t" :value="t">{{ t }}</option>
        </select>
        <select v-model="statusFilter" @change="applyFilters" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
          <option value="">All Statuses</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
          <option value="blacklisted">Blacklisted</option>
        </select>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vendor</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Orders</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Spent</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="v in vendors.data" :key="v.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <Link :href="route('cms.vendors.show', v.id)" class="text-sm font-medium text-blue-600 hover:text-blue-700">{{ v.name }}</Link>
                <p class="text-xs text-gray-500">{{ v.vendor_number }}</p>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                <p>{{ v.email || '-' }}</p>
                <p class="text-xs text-gray-400">{{ v.phone || '' }}</p>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ v.vendor_type || '-' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">{{ v.total_orders || 0 }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">K{{ (v.total_spent || 0).toLocaleString() }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-center">
                <span :class="['px-2 py-1 text-xs font-medium rounded-full', v.status === 'active' ? 'bg-green-100 text-green-700' : v.status === 'blacklisted' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-500']">{{ v.status }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                <Link :href="route('cms.vendors.show', v.id)" class="text-blue-600 hover:text-blue-700 font-medium">View</Link>
              </td>
            </tr>
            <tr v-if="vendors.data.length === 0">
              <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">No vendors found</td>
            </tr>
          </tbody>
        </table>
      </div>
      <Pagination :links="vendors.links" />
    </div>
  </div>
</template>
