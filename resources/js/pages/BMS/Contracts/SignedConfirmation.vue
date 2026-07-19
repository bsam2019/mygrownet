<script setup lang="ts">
import { Link } from '@inertiajs/vue3'

interface Props {
  contract: any
  signedPdfUrl: string | null
}

defineProps<Props>()

const formatDate = (date: string) => date ? new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '-'
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md text-center">
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <div class="text-green-600 mb-4">
          <svg class="h-16 w-16 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Contract Signed!</h1>
        <p class="text-gray-500 mb-6">{{ contract.title }} has been signed by all parties.</p>

        <div class="text-sm text-gray-700 mb-6">
          <p><span class="font-medium">Contract:</span> {{ contract.contract_number }}</p>
          <p><span class="font-medium">Signed:</span> {{ formatDate(contract.signed_at) }}</p>
        </div>

        <div class="flex flex-col gap-3">
          <a v-if="signedPdfUrl" :href="signedPdfUrl" target="_blank" class="px-6 py-3 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700">
            Download Signed PDF
          </a>
          <Link :href="route('bms.contracts.show', contract.id)" class="px-6 py-3 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700">
            View Contract Details
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>
