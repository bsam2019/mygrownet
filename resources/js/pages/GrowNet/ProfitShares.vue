<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import MemberLayout from '@/layouts/MemberLayout.vue'

interface ProfitShare {
  id: number
  professional_level: string
  share_amount: string
  status: string
  paid_at: string | null
  created_at: string
}

defineProps<{
  profitShares: ProfitShare[]
}>()

const getStatusColor = (status: string) => {
  return status === 'paid' 
    ? 'bg-green-100 text-green-800' 
    : 'bg-yellow-100 text-yellow-800'
}
</script>

<template>
  <Head title="My Profit Shares" />
  <MemberLayout>
    <div class="p-6">
      <h1 class="text-2xl font-bold text-gray-900 mb-6">My Quarterly Profit Shares</h1>

      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div v-if="profitShares.length === 0" class="p-8 text-center text-gray-500">
          No profit shares yet. Profit shares are distributed quarterly based on company project performance.
        </div>

        <table v-else class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Level</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paid Date</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="share in profitShares" :key="share.id">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ share.created_at }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ share.professional_level }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                K{{ share.share_amount }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusColor(share.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                  {{ share.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ share.paid_at || '-' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h3 class="font-semibold text-blue-900 mb-2">About Profit Sharing</h3>
        <p class="text-sm text-blue-800">
          MyGrowNet invests in community projects and shares 60% of profits with all active members quarterly.
          Your share is based on your professional level and business points. Stay active to qualify!
        </p>
      </div>
    </div>
  </MemberLayout>
</template>
