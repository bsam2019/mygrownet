<script setup lang="ts">
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { PlusIcon, CheckIcon, XCircleIcon } from '@heroicons/vue/24/outline'
import CMSLayout from '@/Layouts/CMSLayout.vue'

defineOptions({
  layout: CMSLayout
})

interface AdjustmentItem {
  id: number
  material: { id: number; name: string }
  location: { id: number; name: string }
  quantity: number
  unit: string
  unit_cost: number | null
  notes: string | null
}

interface Adjustment {
  id: number
  adjustment_number: string
  adjustment_date: string
  adjustment_type: string
  reason: string
  status: string
  created_by: { id: number; name: string }
  notes: string | null
  items: AdjustmentItem[]
  created_at: string
}

interface Props {
  adjustments: { data: Adjustment[] }
  locations: { id: number; name: string }[]
  materials: { id: number; name: string; unit: string }[]
}

const props = defineProps<Props>()
const showCreateModal = ref(false)

const statusColors: Record<string, string> = {
  draft: 'bg-gray-100 text-gray-700',
  pending_approval: 'bg-yellow-100 text-yellow-800',
  approved: 'bg-green-100 text-green-800',
  rejected: 'bg-red-100 text-red-800',
}

const typeColors: Record<string, string> = {
  increase: 'bg-green-100 text-green-700',
  decrease: 'bg-red-100 text-red-700',
  correction: 'bg-blue-100 text-blue-700',
}

const formatDate = (date: string) => new Date(date).toLocaleDateString('en-US', {
  year: 'numeric', month: 'short', day: 'numeric',
})

const form = useForm({
  adjustment_date: new Date().toISOString().split('T')[0],
  adjustment_type: 'correction',
  reason: 'count_correction',
  created_by: '',
  notes: '',
  items: [] as { material_id: number; location_id: number; quantity: number; unit: string; unit_cost?: number }[],
})

const addItem = () => {
  form.items.push({ material_id: 0, location_id: 0, quantity: 0, unit: '' })
}

const removeItem = (index: number) => {
  form.items.splice(index, 1)
}

const submitForm = () => {
  form.post(route('cms.inventory.adjustments.store'), {
    preserveScroll: true,
    onSuccess: () => {
      showCreateModal.value = false
      form.reset()
    },
  })
}

const approveAdjustment = (id: number) => {
  useForm({}).post(route('cms.inventory.adjustments.approve', id), {
    preserveScroll: true,
  })
}

const typeLabel = (t: string) => t.charAt(0).toUpperCase() + t.slice(1)
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Stock Adjustments</h1>
        <p class="mt-1 text-sm text-gray-500">Record stock increases, decreases, and corrections</p>
      </div>
      <div class="mt-4 sm:mt-0">
        <button @click="showCreateModal = true" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
          <PlusIcon class="h-5 w-5" aria-hidden="true" />
          New Adjustment
        </button>
      </div>
    </div>

    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Adjustment #</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">By</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="adj in adjustments.data" :key="adj.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ adj.adjustment_number }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ formatDate(adj.adjustment_date) }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="['px-2 py-1 text-xs font-medium rounded-full', typeColors[adj.adjustment_type] || 'bg-gray-100']">
                  {{ typeLabel(adj.adjustment_type) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 capitalize">{{ adj.reason.replace(/_/g, ' ') }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-center">
                <span :class="['px-2 py-1 text-xs font-medium rounded-full', statusColors[adj.status] || 'bg-gray-100']">{{ adj.status }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ adj.created_by.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-right">
                <button
                  v-if="adj.status === 'pending_approval'"
                  @click="approveAdjustment(adj.id)"
                  class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-green-700 bg-green-50 rounded-lg hover:bg-green-100"
                >
                  <CheckIcon class="h-4 w-4" aria-hidden="true" />
                  Approve
                </button>
              </td>
            </tr>
            <tr v-if="adjustments.data.length === 0">
              <td colspan="7" class="px-6 py-12 text-center">
                <p class="text-sm text-gray-500">No adjustments yet</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Adjustment Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showCreateModal = false" />
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
          <form @submit.prevent="submitForm">
            <div class="bg-white px-6 py-5 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900">New Stock Adjustment</h3>
            </div>
            <div class="px-6 py-4 space-y-4 max-h-96 overflow-y-auto">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                  <input v-model="form.adjustment_date" type="date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                  <select v-model="form.adjustment_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="increase">Increase</option>
                    <option value="decrease">Decrease</option>
                    <option value="correction">Correction</option>
                  </select>
                </div>
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Reason *</label>
                  <select v-model="form.reason" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="count_correction">Count Correction</option>
                    <option value="damaged">Damaged</option>
                    <option value="expired">Expired</option>
                    <option value="found">Found</option>
                    <option value="lost">Lost</option>
                    <option value="other">Other</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Created By (User ID) *</label>
                  <input v-model="form.created_by" type="number" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea v-model="form.notes" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
              </div>
              <div class="border-t border-gray-200 pt-4">
                <div class="flex items-center justify-between mb-3">
                  <label class="text-sm font-medium text-gray-700">Items</label>
                  <button type="button" @click="addItem" class="text-sm text-blue-600 hover:text-blue-700 font-medium">+ Add Item</button>
                </div>
                <div v-for="(item, index) in form.items" :key="index" class="flex items-center gap-2 mb-3 p-3 bg-gray-50 rounded-lg">
                  <div class="flex-1">
                    <select v-model="item.material_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                      <option value="0">Material</option>
                      <option v-for="mat in materials" :key="mat.id" :value="mat.id">{{ mat.name }}</option>
                    </select>
                  </div>
                  <div class="flex-1">
                    <select v-model="item.location_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                      <option value="0">Location</option>
                      <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
                    </select>
                  </div>
                  <div class="w-20">
                    <input v-model="item.quantity" type="number" step="0.01" min="0" placeholder="Qty" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                  </div>
                  <div class="w-16">
                    <input v-model="item.unit" type="text" placeholder="Unit" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                  </div>
                  <button type="button" @click="removeItem(index)" class="text-red-500 hover:text-red-700">
                    <XCircleIcon class="h-5 w-5" aria-hidden="true" />
                  </button>
                </div>
              </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 flex items-center justify-end gap-3">
              <button type="button" @click="showCreateModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
              <button type="submit" :disabled="form.processing" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                {{ form.processing ? 'Saving...' : 'Save Adjustment' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>
