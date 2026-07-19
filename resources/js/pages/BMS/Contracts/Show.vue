<script setup lang="ts">
import { ref } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import { PencilIcon, CheckCircleIcon, XCircleIcon, ArrowPathIcon, DocumentArrowDownIcon, PaperAirplaneIcon } from '@heroicons/vue/24/outline'
import BMSLayout from '@/Layouts/BMSLayout.vue'
import SignaturePad from '@/components/BMS/SignaturePad.vue'

defineOptions({ layout: BMSLayout })

interface Props {
  contract: any
  signedPdfUrl: string | null
}

const props = defineProps<Props>()

const showRenewModal = ref(false)
const showSignModal = ref(false)
const signParty = ref<'customer' | 'company'>('company')
const renewForm = useForm({ new_end_date: '', total_value: 0, notes: '' })
const signing = ref(false)

const openRenewModal = () => {
  renewForm.new_end_date = ''
  renewForm.total_value = props.contract.total_value
  renewForm.notes = ''
  showRenewModal.value = true
}

const submitRenew = () => {
  renewForm.post(route('bms.contracts.renew', props.contract.id), {
    onSuccess: () => { showRenewModal.value = false }
  })
}

const openSignModal = (party: 'customer' | 'company') => {
  signParty.value = party
  showSignModal.value = true
}

const handleSignatureDone = (dataUrl: string) => {
  signing.value = true
  router.post(route('bms.contracts.sign', props.contract.id), {
    party: signParty.value,
    signature_data: dataUrl,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      signing.value = false
      showSignModal.value = false
    },
    onError: () => { signing.value = false }
  })
}

const sendForSigning = () => {
  router.post(route('bms.contracts.send-for-signing', props.contract.id), {}, { preserveScroll: true })
}

const activate = () => router.post(route('bms.contracts.activate', props.contract.id), {}, { preserveScroll: true })
const terminate = () => router.post(route('bms.contracts.terminate', props.contract.id), {}, { preserveScroll: true })

const formatMoney = (amount: number) => 'K' + amount.toLocaleString('en-US', { minimumFractionDigits: 2 })
const formatDate = (date: string) => date ? new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '-'

const statusColors: Record<string, string> = {
  draft: 'bg-gray-100 text-gray-700',
  active: 'bg-green-100 text-green-700',
  expired: 'bg-red-100 text-red-700',
  terminated: 'bg-red-100 text-red-700',
  renewed: 'bg-blue-100 text-blue-700',
}

const isOverdue = props.contract.status === 'active' && props.contract.end_date && new Date(props.contract.end_date) < new Date()
const isFullySigned = props.contract.signed_by_customer && props.contract.signed_by_company
</script>

<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <Link :href="route('bms.contracts.index')" class="text-sm text-blue-600 hover:text-blue-700 mb-4 inline-flex items-center gap-1">← Back to Contracts</Link>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">{{ contract.title }}</h1>
          <p class="text-sm text-gray-500">{{ contract.contract_number }}</p>
        </div>
        <div class="flex items-center gap-2">
          <span v-if="isOverdue" class="px-3 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">OVERDUE</span>
          <span :class="['px-3 py-1 text-sm font-medium rounded-full', statusColors[contract.status] || 'bg-gray-100']">{{ contract.status }}</span>
          <Link v-if="contract.status === 'draft'" :href="route('bms.contracts.edit', contract.id)" class="p-2 text-gray-400 hover:text-gray-600">
            <PencilIcon class="h-5 w-5" />
          </Link>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-wrap gap-2 mb-4">
        <button v-if="contract.status === 'draft'" @click="activate" class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-600 text-white rounded-lg text-xs font-medium hover:bg-green-700">
          <CheckCircleIcon class="h-4 w-4" /> Activate
        </button>
        <button v-if="contract.status === 'active' && !contract.signed_by_customer" @click="openSignModal('customer')" class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-medium hover:bg-blue-700">
          <PencilIcon class="h-4 w-4" /> Sign as Customer
        </button>
        <button v-if="contract.status === 'active' && !contract.signed_by_company" @click="openSignModal('company')" class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-medium hover:bg-blue-700">
          <PencilIcon class="h-4 w-4" /> Sign as Company
        </button>
        <button v-if="contract.status === 'active' && !contract.signed_by_customer && contract.customer?.email" @click="sendForSigning" class="inline-flex items-center gap-1 px-3 py-1.5 bg-purple-600 text-white rounded-lg text-xs font-medium hover:bg-purple-700">
          <PaperAirplaneIcon class="h-4 w-4" /> Send for Signing
        </button>
        <button v-if="isFullySigned && signedPdfUrl" @click="window.open(signedPdfUrl)" class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-xs font-medium hover:bg-emerald-700">
          <DocumentArrowDownIcon class="h-4 w-4" /> View Signed PDF
        </button>
        <button v-if="contract.status === 'active'" @click="openRenewModal" class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-xs font-medium hover:bg-indigo-700">
          <ArrowPathIcon class="h-4 w-4" /> Renew
        </button>
        <button v-if="contract.status === 'active'" @click="terminate" class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-600 text-white rounded-lg text-xs font-medium hover:bg-red-700">
          <XCircleIcon class="h-4 w-4" /> Terminate
        </button>
      </div>

      <div class="grid grid-cols-2 gap-4 text-sm">
        <div><label class="text-gray-500 font-medium">Customer</label><p class="text-gray-900">{{ contract.customer?.name || '-' }}</p></div>
        <div><label class="text-gray-500 font-medium">Template</label><p class="text-gray-900">{{ contract.template?.name || '-' }}</p></div>
        <div><label class="text-gray-500 font-medium">Start Date</label><p class="text-gray-900">{{ formatDate(contract.start_date) }}</p></div>
        <div><label class="text-gray-500 font-medium">End Date</label><p class="text-gray-900">{{ formatDate(contract.end_date) }}</p></div>
        <div><label class="text-gray-500 font-medium">Total Value</label><p class="text-gray-900 font-semibold">{{ formatMoney(contract.total_value) }} {{ contract.currency }}</p></div>
        <div><label class="text-gray-500 font-medium">Status</label><p class="text-gray-900 capitalize">{{ contract.status }}</p></div>
        <div><label class="text-gray-500 font-medium">Signed by Customer</label><p class="text-gray-900">{{ contract.signed_by_customer ? 'Yes' : 'No' }}</p></div>
        <div><label class="text-gray-500 font-medium">Signed by Company</label><p class="text-gray-900">{{ contract.signed_by_company ? 'Yes' : 'No' }}</p></div>
        <div v-if="contract.signed_at" class="col-span-2"><label class="text-gray-500 font-medium">Signed At</label><p class="text-gray-900">{{ formatDate(contract.signed_at) }}</p></div>
        <div v-if="contract.approved_by"><label class="text-gray-500 font-medium">Approved By</label><p class="text-gray-900">{{ contract.approved_by?.name || '-' }}</p></div>
      </div>
    </div>

    <!-- Signature History -->
    <div v-if="contract.signatures?.length" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
      <h2 class="text-lg font-semibold text-gray-900 mb-3">Signatures</h2>
      <div class="space-y-4">
        <div v-for="sig in contract.signatures" :key="sig.id" class="flex items-start gap-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
          <div class="flex-1">
            <p class="text-sm font-medium text-gray-900 capitalize">{{ sig.party === 'company' ? 'Company' : 'Customer' }}</p>
            <p class="text-xs text-gray-500">{{ sig.signer_name }} · {{ formatDate(sig.signed_at) }} · IP: {{ sig.ip_address }}</p>
            <img v-if="sig.signature_data" :src="sig.signature_data" class="mt-2 h-12 border border-gray-300 rounded bg-white" alt="Signature" />
          </div>
        </div>
      </div>
    </div>

    <!-- Terms -->
    <div v-if="contract.terms" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
      <h2 class="text-lg font-semibold text-gray-900 mb-3">Terms & Conditions</h2>
      <pre class="text-sm text-gray-700 whitespace-pre-wrap font-sans">{{ contract.terms }}</pre>
    </div>

    <!-- Notes -->
    <div v-if="contract.notes" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
      <h2 class="text-lg font-semibold text-gray-900 mb-3">Notes</h2>
      <p class="text-sm text-gray-700">{{ contract.notes }}</p>
    </div>

    <!-- Renewal History -->
    <div v-if="contract.renewals?.length" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      <h2 class="text-lg font-semibold text-gray-900 mb-3">Renewal History</h2>
      <div class="space-y-3">
        <div v-for="r in contract.renewals" :key="r.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
          <div>
            <p class="text-sm font-medium text-gray-900">{{ formatDate(r.renewal_date) }}</p>
            <p v-if="r.notes" class="text-xs text-gray-500">{{ r.notes }}</p>
          </div>
          <span v-if="r.renewed_contract" class="text-sm">
            <Link :href="route('bms.contracts.show', r.renewed_contract.id)" class="text-blue-600 hover:text-blue-700">View Renewed</Link>
          </span>
        </div>
      </div>
    </div>

    <!-- Signature Modal -->
    <div v-if="showSignModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="showSignModal = false">
      <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-lg mx-4">
        <h2 class="text-lg font-semibold text-gray-900 mb-1">
          Sign as {{ signParty === 'company' ? 'Company' : 'Customer' }}
        </h2>
        <p class="text-sm text-gray-500 mb-4">Draw your signature in the box below using your mouse or finger (on touch devices)</p>

        <div class="flex justify-center mb-4">
          <SignaturePad @done="handleSignatureDone" />
        </div>

        <div class="flex justify-end gap-3 pt-2">
          <button type="button" @click="showSignModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg">Cancel</button>
          <button disabled class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium opacity-50 cursor-not-allowed">
            Sign via pad above
          </button>
        </div>
      </div>
    </div>

    <!-- Renew Modal -->
    <div v-if="showRenewModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="showRenewModal = false">
      <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Renew Contract</h2>
        <form @submit.prevent="submitRenew" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">New End Date</label>
            <input v-model="renewForm.new_end_date" type="date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
            <p v-if="renewForm.errors.new_end_date" class="mt-1 text-sm text-red-600">{{ renewForm.errors.new_end_date }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Total Value (optional)</label>
            <input v-model.number="renewForm.total_value" type="number" min="0" step="0.01" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea v-model="renewForm.notes" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
          </div>
          <div class="flex justify-end gap-3 pt-2">
            <button type="button" @click="showRenewModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg">Cancel</button>
            <button type="submit" :disabled="renewForm.processing" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 disabled:opacity-50">
              {{ renewForm.processing ? 'Renewing...' : 'Renew Contract' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
