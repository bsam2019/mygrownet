<script setup lang="ts">
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { PlusIcon, CheckCircleIcon, ClipboardDocumentCheckIcon } from '@heroicons/vue/24/outline'
import CMSLayout from '@/Layouts/CMSLayout.vue'

defineOptions({
  layout: CMSLayout
})

interface CountItem {
  id: number
  material: { id: number; name: string }
  location: { id: number; name: string }
  system_quantity: number
  counted_quantity: number | null
  variance: number | null
  variance_percentage: number | null
  unit: string
}

interface StockCount {
  id: number
  count_number: string
  count_date: string
  count_type: string
  status: string
  location: { id: number; name: string } | null
  counted_by: { id: number; name: string }
  items: CountItem[]
  created_at: string
}

interface Props {
  counts: { data: StockCount[] }
  locations: { id: number; name: string }[]
  materials: { id: number; name: string; unit: string }[]
}

const props = defineProps<Props>()
const showCreateModal = ref(false)

const statusColors: Record<string, string> = {
  scheduled: 'bg-gray-100 text-gray-700',
  in_progress: 'bg-blue-100 text-blue-800',
  completed: 'bg-green-100 text-green-800',
  cancelled: 'bg-red-100 text-red-800',
}

const formatDate = (date: string) => new Date(date).toLocaleDateString('en-US', {
  year: 'numeric', month: 'short', day: 'numeric',
})

const form = useForm({
  count_date: new Date().toISOString().split('T')[0],
  count_type: 'full',
  location_id: '',
  counted_by: '',
  items: [] as { material_id: number; location_id: number; counted_quantity: number; unit: string }[],
})

const addItem = () => {
  form.items.push({ material_id: 0, location_id: 0, counted_quantity: 0, unit: '' })
}

const removeItem = (index: number) => {
  form.items.splice(index, 1)
}

const submitForm = () => {
  form.post(route('cms.inventory.counts.store'), {
    preserveScroll: true,
    onSuccess: () => {
      showCreateModal.value = false
      form.reset()
    },
  })
}

const completeCount = (id: number) => {
  useForm({}).post(route('cms.inventory.counts.complete', id), {
    preserveScroll: true,
  })
}
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Stock Counts</h1>
        <p class="mt-1 text-sm text-gray-500">Schedule and perform physical inventory counts</p>
      </div>
      <div class="mt-4 sm:mt-0">
        <button @click="showCreateModal = true" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
          <PlusIcon class="h-5 w-5" aria-hidden="true" />
          New Count
        </button>
      </div>
    </div>

    <div class="space-y-4">
      <div v-for="count in counts.data" :key="count.id" class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <ClipboardDocumentCheckIcon class="h-6 w-6 text-gray-400" aria-hidden="true" />
            <div>
              <h3 class="text-sm font-semibold text-gray-900">{{ count.count_number }}</h3>
              <p class="text-xs text-gray-500">{{ formatDate(count.count_date) }} — {{ count.count_type }} count</p>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <span :class="['px-2 py-1 text-xs font-medium rounded-full', statusColors[count.status] || 'bg-gray-100']">{{ count.status }}</span>
            <button
              v-if="count.status === 'in_progress'"
              @click="completeCount(count.id)"
              class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-green-700 bg-green-50 rounded-lg hover:bg-green-100"
            >
              <CheckCircleIcon class="h-4 w-4" aria-hidden="true" />
              Complete
            </button>
          </div>
        </div>
        <div v-if="count.items.length > 0" class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 uppercase">Material</th>
                <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                <th class="px-6 py-2 text-right text-xs font-medium text-gray-500 uppercase">System</th>
                <th class="px-6 py-2 text-right text-xs font-medium text-gray-500 uppercase">Counted</th>
                <th class="px-6 py-2 text-right text-xs font-medium text-gray-500 uppercase">Variance</th>
                <th class="px-6 py-2 text-right text-xs font-medium text-gray-500 uppercase">Var %</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="item in count.items" :key="item.id" class="text-sm">
                <td class="px-6 py-2 text-gray-900">{{ item.material.name }}</td>
                <td class="px-6 py-2 text-gray-600">{{ item.location.name }}</td>
                <td class="px-6 py-2 text-right text-gray-900">{{ item.system_quantity }}</td>
                <td class="px-6 py-2 text-right text-gray-900">{{ item.counted_quantity ?? '-' }}</td>
                <td :class="['px-6 py-2 text-right font-medium', (item.variance ?? 0) !== 0 ? 'text-red-600' : 'text-green-600']">
                  {{ item.variance ?? '-' }}
                </td>
                <td class="px-6 py-2 text-right text-gray-600">{{ item.variance_percentage != null ? item.variance_percentage + '%' : '-' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="px-6 py-4 text-sm text-gray-500">No items recorded</div>
      </div>

      <div v-if="counts.data.length === 0" class="bg-white shadow-sm rounded-lg border border-gray-200 p-12 text-center">
        <ClipboardDocumentCheckIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
        <p class="mt-2 text-sm text-gray-500">No stock counts yet</p>
        <button @click="showCreateModal = true" class="mt-3 text-sm font-medium text-blue-600 hover:text-blue-700">Schedule your first count →</button>
      </div>
    </div>

    <!-- Create Count Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showCreateModal = false" />
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
          <form @submit.prevent="submitForm">
            <div class="bg-white px-6 py-5 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900">New Stock Count</h3>
            </div>
            <div class="px-6 py-4 space-y-4 max-h-96 overflow-y-auto">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Count Date *</label>
                  <input v-model="form.count_date" type="date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Count Type *</label>
                  <select v-model="form.count_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="full">Full Count</option>
                    <option value="partial">Partial Count</option>
                    <option value="cycle">Cycle Count</option>
                  </select>
                </div>
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                  <select v-model="form.location_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">All Locations</option>
                    <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Counted By (User ID) *</label>
                  <input v-model="form.counted_by" type="number" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                </div>
              </div>
              <div class="border-t border-gray-200 pt-4">
                <div class="flex items-center justify-between mb-3">
                  <label class="text-sm font-medium text-gray-700">Items to Count</label>
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
                    <input v-model="item.counted_quantity" type="number" step="0.01" min="0" placeholder="Qty" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                  </div>
                  <div class="w-16">
                    <input v-model="item.unit" type="text" placeholder="Unit" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" />
                  </div>
                  <button type="button" @click="removeItem(index)" class="text-red-500 hover:text-red-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                  </button>
                </div>
              </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 flex items-center justify-end gap-3">
              <button type="button" @click="showCreateModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
              <button type="submit" :disabled="form.processing" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                {{ form.processing ? 'Saving...' : 'Create Count' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>
