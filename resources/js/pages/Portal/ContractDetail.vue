<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { DocumentCheckIcon } from '@heroicons/vue/24/outline'
import PortalLayout from '@/Layouts/PortalLayout.vue'

defineOptions({ layout: PortalLayout })

interface Signature {
  id: number
  party: string
  signer_name: string
  signed_at: string
  signature_data: string
  ip_address: string
}

interface Contract {
  id: number
  contract_number: string
  title: string
  description: string | null
  total_value: number
  currency: string
  status: string
  start_date: string
  end_date: string | null
  signed_by_customer: boolean
  signed_by_company: boolean
  signed_at: string | null
  terms: string | null
  notes: string | null
  company: { name: string; email: string; phone: string }
  template: { name: string } | null
  signatures: Signature[]
  renewals: any[]
}

interface Props {
  contract: Contract
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
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <Link :href="route('portal.contracts')" class="text-sm text-blue-600 hover:text-blue-700 mb-4 inline-flex items-center gap-1">← Back to Contracts</Link>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">{{ contract.title }}</h1>
          <p class="text-sm text-gray-500">{{ contract.contract_number }} · {{ contract.company?.name }}</p>
        </div>
        <span :class="['px-3 py-1 text-sm font-medium rounded-full', statusColors[contract.status] || 'bg-gray-100']">{{ contract.status }}</span>
      </div>

      <div class="grid grid-cols-2 gap-4 text-sm">
        <div><label class="text-gray-500 font-medium">Template</label><p class="text-gray-900">{{ contract.template?.name || '-' }}</p></div>
        <div><label class="text-gray-500 font-medium">Start Date</label><p class="text-gray-900">{{ formatDate(contract.start_date) }}</p></div>
        <div><label class="text-gray-500 font-medium">End Date</label><p class="text-gray-900">{{ formatDate(contract.end_date) }}</p></div>
        <div><label class="text-gray-500 font-medium">Total Value</label><p class="text-gray-900 font-semibold">{{ formatMoney(contract.total_value) }} {{ contract.currency }}</p></div>
      </div>
    </div>

    <!-- Signatures -->
    <div v-if="contract.signatures?.length" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
      <h2 class="text-lg font-semibold text-gray-900 mb-3">Signatures</h2>
      <div class="space-y-4">
        <div v-for="sig in contract.signatures" :key="sig.id" class="flex items-start gap-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
          <DocumentCheckIcon class="h-6 w-6 text-green-500 mt-0.5" />
          <div>
            <p class="text-sm font-medium text-gray-900 capitalize">{{ sig.party === 'company' ? 'Company' : 'Customer' }} — {{ sig.signer_name }}</p>
            <p class="text-xs text-gray-500">{{ formatDate(sig.signed_at) }}</p>
            <img v-if="sig.signature_data" :src="sig.signature_data" class="mt-2 h-10 border border-gray-300 rounded bg-white" alt="Signature" />
          </div>
        </div>
      </div>
    </div>

    <!-- Terms -->
    <div v-if="contract.terms" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
      <h2 class="text-lg font-semibold text-gray-900 mb-3">Terms &amp; Conditions</h2>
      <pre class="text-sm text-gray-700 whitespace-pre-wrap font-sans">{{ contract.terms }}</pre>
    </div>

    <!-- Company Info -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ contract.company?.name }}</h2>
      <p v-if="contract.company?.email" class="text-sm text-gray-600">Email: {{ contract.company.email }}</p>
      <p v-if="contract.company?.phone" class="text-sm text-gray-600">Phone: {{ contract.company.phone }}</p>
    </div>
  </div>
</template>
