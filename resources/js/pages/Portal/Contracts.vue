<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { DocumentCheckIcon } from '@heroicons/vue/24/outline'
import PortalLayout from '@/Layouts/PortalLayout.vue'

defineOptions({ layout: PortalLayout })

interface Contract {
  id: number
  contract_number: string
  title: string
  total_value: number
  currency: string
  status: string
  start_date: string
  end_date: string | null
  signed_by_customer: boolean
  signed_by_company: boolean
  signed_at: string | null
  company: { name: string }
  template: { name: string } | null
}

interface Props {
  contracts: { data: Contract[] }
}

defineProps<Props>()

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
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Contracts</h1>

    <div v-if="contracts.data.length === 0" class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
      <DocumentCheckIcon class="h-12 w-12 mx-auto text-gray-300 mb-3" />
      <p class="text-gray-500">No contracts yet</p>
    </div>

    <div v-else class="space-y-3">
      <div v-for="c in contracts.data" :key="c.id" class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
          <div class="flex-1">
            <Link :href="route('portal.contract-detail', c.id)" class="text-base font-semibold text-blue-600 hover:text-blue-700">{{ c.title }}</Link>
            <p class="text-xs text-gray-500 mt-0.5">{{ c.contract_number }} · {{ c.company?.name }} · {{ formatDate(c.start_date) }} - {{ formatDate(c.end_date) }}</p>
          </div>
          <div class="text-right flex items-center gap-4">
            <div>
              <p class="text-sm font-semibold text-gray-900">{{ formatMoney(c.total_value) }} {{ c.currency }}</p>
              <p class="text-xs text-gray-500">
                <span v-if="c.signed_by_customer && c.signed_by_company" class="text-green-600">Signed {{ formatDate(c.signed_at) }}</span>
                <span v-else-if="c.signed_by_customer" class="text-amber-600">Awaiting company</span>
                <span v-else-if="c.signed_by_company" class="text-amber-600">Awaiting your signature</span>
                <span v-else class="text-gray-400">Not signed</span>
              </p>
            </div>
            <span :class="['px-2.5 py-0.5 text-xs font-medium rounded-full', statusColors[c.status] || 'bg-gray-100']">{{ c.status }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
