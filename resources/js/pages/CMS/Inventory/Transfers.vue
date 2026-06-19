<script setup lang="ts">
import { ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import { ArrowPathIcon, PlusIcon, TruckIcon, CheckIcon, XCircleIcon } from '@heroicons/vue/24/outline'
import CMSLayout from '@/Layouts/CMSLayout.vue'

defineOptions({
  layout: CMSLayout
})

interface TransferItem {
  id: number
  material: { id: number; name: string }
  quantity: number
  received_quantity: number | null
  unit: string
}

interface Transfer {
  id: number
  transfer_number: string
  from_location: { id: number; name: string }
  to_location: { id: number; name: string }
  transfer_date: string
  status: string
  requested_by: { id: number; name: string }
  notes: string | null
  items: TransferItem[]
  created_at: string
}

interface Props {
  transfers: { data: Transfer[] }
  locations: { id: number; name: string }[]
  materials: { id: number; name: string; unit: string }[]
}

const props = defineProps<Props>()
const showCreateModal = ref(false)
const showReceiveModal = ref(false)
const selectedTransfer = ref<Transfer | null>(null)
const receiveQuantities = ref<Record<number, number>>({})

const statusColors: Record<string, string> = {
  pending: 'bg-yellow-100 text-yellow-800',
  in_transit: 'bg-blue-100 text-blue-800',
  received: 'bg-green-100 text-green-800',
  cancelled: 'bg-red-100 text-red-800',
}

const formatDate = (date: string) => new Date(date).toLocaleDateString('en-US', {
  year: 'numeric', month: 'short', day: 'numeric',
})

const form = useForm({
  from_location_id: '',
  to_location_id: '',
  transfer_date: new Date().toISOString().split('T')[0],
  requested_by: '',
  notes: '',
  items: [] as { material_id: number; quantity: number; unit: string; notes?: string }[],
})

const addItem = () => {
  form.items.push({ material_id: 0, quantity: 0, unit: '', notes: '' })
}

const removeItem = (index: number) => {
  form.items.splice(index, 1)
}

const submitForm = () => {
  form.post(route('cms.inventory.transfers.store'), {
    preserveScroll: true,
    onSuccess: () => {
      showCreateModal.value = false
      form.reset()
    },
  })
}

const approveTransfer = (id: number) => {
  useForm({}).post(route('cms.inventory.transfers.approve', id), {
    preserveScroll: true,
  })
}

const openReceiveModal = (transfer: Transfer) => {
  selectedTransfer.value = transfer
  receiveQuantities.value = {}
  transfer.items.forEach(item => {
    receiveQuantities.value[item.id] = item.received_quantity ?? item.quantity
  })
  showReceiveModal.value = true
}

const submitReceive = () => {
  if (!selectedTransfer.value) return
  useForm({ items: receiveQuantities.value }).post(
    route('cms.inventory.transfers.receive', selectedTransfer.value.id),
    { preserveScroll: true, onSuccess: () => { showReceiveModal.value = false } },
  )
}
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Stock Transfers</h1>
        <p class="mt-1 text-sm text-gray-500">Transfer stock between locations</p>
      </div>
      <div class="mt-4 sm:mt-0">
        <button
          @click="showCreateModal = true"
          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium"
        >
          <PlusIcon class="h-5 w-5" aria-hidden="true" />
          New Transfer
        </button>
      </div>
    </div>

    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Transfer #</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">From</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">To</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="transfer in transfers.data" :key="transfer.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                {{ transfer.transfer_number }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ transfer.from_location.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ transfer.to_location.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ formatDate(transfer.transfer_date) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-center">
                <span :class="['px-2 py-1 text-xs font-medium rounded-full', statusColors[transfer.status] || 'bg-gray-100']">
                  {{ transfer.status }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-600">
                {{ transfer.items.length }} item{{ transfer.items.length !== 1 ? 's' : '' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right">
                <div class="flex items-center justify-end gap-2">
                  <button
                    v-if="transfer.status === 'pending'"
                    @click="approveTransfer(transfer.id)"
                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100"
                  >
                    <CheckIcon class="h-4 w-4" aria-hidden="true" />
                    Approve
                  </button>
                  <button
                    v-if="transfer.status === 'in_transit'"
                    @click="openReceiveModal(transfer)"
                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-green-700 bg-green-50 rounded-lg hover:bg-green-100"
                  >
                    <TruckIcon class="h-4 w-4" aria-hidden="true" />
                    Receive
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="transfers.data.length === 0">
              <td colspan="7" class="px-6 py-12 text-center">
                <ArrowPathIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                <p class="mt-2 text-sm text-gray-500">No transfers yet</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Transfer Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showCreateModal = false" />
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
          <form @submit.prevent="submitForm">
            <div class="bg-white px-6 py-5 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900">New Stock Transfer</h3>
            </div>
            <div class="px-6 py-4 space-y-4 max-h-96 overflow-y-auto">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">From Location *</label>
                  <select v-model="form.from_location_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">Select source</option>
                    <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
                  </select>
                  <p v-if="form.errors.from_location_id" class="mt-1 text-sm text-red-600">{{ form.errors.from_location_id }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">To Location *</label>
                  <select v-model="form.to_location_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">Select destination</option>
                    <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
                  </select>
                  <p v-if="form.errors.to_location_id" class="mt-1 text-sm text-red-600">{{ form.errors.to_location_id }}</p>
                </div>
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Transfer Date *</label>
                  <input v-model="form.transfer_date" type="date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Requested By *</label>
                  <input v-model="form.requested_by" type="number" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" placeholder="User ID" />
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea v-model="form.notes" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
              </div>
              <div class="border-t border-gray-200 pt-4">
                <div class="flex items-center justify-between mb-3">
                  <label class="text-sm font-medium text-gray-700">Transfer Items</label>
                  <button type="button" @click="addItem" class="text-sm text-blue-600 hover:text-blue-700 font-medium">+ Add Item</button>
                </div>
                <div v-for="(item, index) in form.items" :key="index" class="flex items-center gap-3 mb-3 p-3 bg-gray-50 rounded-lg">
                  <div class="flex-1">
                    <select v-model="item.material_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                      <option value="0">Select material</option>
                      <option v-for="mat in materials" :key="mat.id" :value="mat.id">{{ mat.name }}</option>
                    </select>
                  </div>
                  <div class="w-24">
                    <input v-model="item.quantity" type="number" step="0.01" min="0" placeholder="Qty" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                  </div>
                  <div class="w-20">
                    <input v-model="item.unit" type="text" placeholder="Unit" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                  </div>
                  <button type="button" @click="removeItem(index)" class="text-red-500 hover:text-red-700">
                    <XCircleIcon class="h-5 w-5" aria-hidden="true" />
                  </button>
                </div>
                <p v-if="form.errors.items" class="mt-1 text-sm text-red-600">{{ form.errors.items }}</p>
              </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 flex items-center justify-end gap-3">
              <button type="button" @click="showCreateModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
              <button type="submit" :disabled="form.processing" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                {{ form.processing ? 'Creating...' : 'Create Transfer' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Receive Transfer Modal -->
    <div v-if="showReceiveModal && selectedTransfer" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showReceiveModal = false" />
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <form @submit.prevent="submitReceive">
            <div class="bg-white px-6 py-5 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900">Receive Transfer</h3>
              <p class="text-sm text-gray-500 mt-1">{{ selectedTransfer.transfer_number }}</p>
            </div>
            <div class="px-6 py-4 space-y-4">
              <p class="text-sm text-gray-600">
                From: <strong>{{ selectedTransfer.from_location.name }}</strong> → To: <strong>{{ selectedTransfer.to_location.name }}</strong>
              </p>
              <div v-for="item in selectedTransfer.items" :key="item.id" class="p-3 bg-gray-50 rounded-lg">
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ item.material.name }}</label>
                <div class="flex items-center gap-3">
                  <input
                    v-model="receiveQuantities[item.id]"
                    type="number"
                    step="0.01"
                    min="0"
                    :max="item.quantity"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                  />
                  <span class="text-sm text-gray-500">/ {{ item.quantity }} {{ item.unit }}</span>
                </div>
              </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 flex items-center justify-end gap-3">
              <button type="button" @click="showReceiveModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
              <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">Confirm Receipt</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>
