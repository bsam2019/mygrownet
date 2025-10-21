<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AdminLayout from '@/layouts/AdminLayout.vue'

interface ProfitShare {
  id: number
  quarter: string
  year: number
  quarter_number: number
  total_project_profit: string
  member_share_amount: string
  company_retained: string
  total_active_members: number
  distribution_method: string
  status: string
  approved_at: string | null
  distributed_at: string | null
}

defineProps<{
  profitShares: ProfitShare[]
}>()

const getStatusColor = (status: string) => {
  const colors = {
    draft: 'bg-gray-100 text-gray-800',
    calculated: 'bg-blue-100 text-blue-800',
    approved: 'bg-green-100 text-green-800',
    distributed: 'bg-emerald-100 text-emerald-800',
  }
  return colors[status as keyof typeof colors] || 'bg-gray-100 text-gray-800'
}

const approve = (id: number) => {
  if (confirm('Are you sure you want to approve this profit share?')) {
    router.post(route('admin.profit-sharing.approve', id))
  }
}

const distribute = (id: number) => {
  if (confirm('This will credit all member wallets. Are you sure?')) {
    router.post(route('admin.profit-sharing.distribute', id))
  }
}
</script>

<template>
  <Head title="Quarterly Profit Sharing" />
  <AdminLayout>
    <div class="p-6">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Quarterly Profit Sharing</h1>
        <a
          :href="route('admin.profit-sharing.create')"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          Create New Quarter
        </a>
      </div>

      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quarter</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Profit</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Member Share</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Active Members</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="share in profitShares" :key="share.id">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ share.quarter }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ share.total_project_profit }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ share.member_share_amount }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ share.total_active_members }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <span class="capitalize">{{ share.distribution_method.replace('_', ' ') }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusColor(share.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                  {{ share.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                <button
                  v-if="share.status === 'calculated'"
                  @click="approve(share.id)"
                  class="text-green-600 hover:text-green-900"
                >
                  Approve
                </button>
                <button
                  v-if="share.status === 'approved'"
                  @click="distribute(share.id)"
                  class="text-blue-600 hover:text-blue-900"
                >
                  Distribute
                </button>
                <span v-if="share.status === 'distributed'" class="text-gray-400">
                  Completed
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AdminLayout>
</template>
