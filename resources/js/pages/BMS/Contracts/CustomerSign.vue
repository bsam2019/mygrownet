<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import SignaturePad from '@/components/BMS/SignaturePad.vue'

interface Props {
  contract: any
  token: string
}

const props = defineProps<Props>()

const signatureDone = ref(false)
const signatureData = ref('')
const signerName = ref(props.contract.customer?.name || '')

const handleSignatureDone = (dataUrl: string) => {
  signatureData.value = dataUrl
  signatureDone.value = true
}

const submitSignature = () => {
  if (!signatureData.value || !signerName.value) return

  router.post(route('public.contracts.submit-signature', {
    contract: props.contract.id,
    token: props.token,
  }), {
    signature_data: signatureData.value,
    signer_name: signerName.value,
  }, {
    preserveScroll: true,
  })
}

const formatMoney = (amount: number) => 'K' + amount.toLocaleString('en-US', { minimumFractionDigits: 2 })
const formatDate = (date: string) => date ? new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '-'
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <div class="w-full max-w-xl">
      <div class="text-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Sign Contract</h1>
        <p class="mt-1 text-sm text-gray-500">Please review and sign the contract below</p>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-2">{{ contract.title }}</h2>
        <p class="text-xs text-gray-500 mb-4">{{ contract.contract_number }}</p>

        <div class="grid grid-cols-2 gap-3 text-sm">
          <div><span class="text-gray-500">Customer:</span><br /><span class="text-gray-900 font-medium">{{ contract.customer?.name || '-' }}</span></div>
          <div><span class="text-gray-500">Value:</span><br /><span class="text-gray-900 font-medium">{{ formatMoney(contract.total_value) }} {{ contract.currency }}</span></div>
          <div><span class="text-gray-500">Start Date:</span><br /><span class="text-gray-900">{{ formatDate(contract.start_date) }}</span></div>
          <div><span class="text-gray-500">End Date:</span><br /><span class="text-gray-900">{{ formatDate(contract.end_date) }}</span></div>
        </div>

        <div v-if="contract.terms" class="mt-4 pt-4 border-t border-gray-200">
          <h3 class="text-sm font-semibold text-gray-700 mb-2">Terms & Conditions</h3>
          <pre class="text-xs text-gray-600 whitespace-pre-wrap font-sans max-h-48 overflow-y-auto">{{ contract.terms }}</pre>
        </div>
      </div>

      <div v-if="!signatureDone" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-base font-semibold text-gray-900 mb-4">Your Signature</h2>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Your Name</label>
          <input v-model="signerName" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="Enter your full name" />
        </div>

        <p class="text-sm text-gray-500 mb-3">Draw your signature below using your finger (on phone/tablet) or mouse (on computer):</p>
        <div class="flex justify-center">
          <SignaturePad @done="handleSignatureDone" :width="500" :height="180" />
        </div>
      </div>

      <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6 text-center">
        <div class="text-green-600 mb-2">
          <svg class="h-12 w-12 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <h2 class="text-lg font-semibold text-gray-900 mb-1">Signature Captured</h2>
        <p class="text-sm text-gray-500 mb-4">Please confirm to submit your signature</p>

        <div class="mb-4">
          <p class="text-sm text-gray-700 font-medium">Signing as: {{ signerName }}</p>
          <img :src="signatureData" class="mx-auto mt-2 h-12 border border-gray-300 rounded bg-white" alt="Your signature" />
        </div>

        <div class="flex justify-center gap-3">
          <button @click="signatureDone = false; signatureData = ''" class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg border border-gray-300">Redraw</button>
          <button @click="submitSignature" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Confirm & Submit</button>
        </div>
      </div>

      <p class="text-center text-xs text-gray-400">By signing, you agree to the terms and conditions of this contract.</p>
    </div>
  </div>
</template>
