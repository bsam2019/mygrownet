<script setup lang="ts">
import { ref } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import { PencilIcon } from '@heroicons/vue/24/outline'
import BMSLayout from '@/Layouts/BMSLayout.vue'

defineOptions({
  layout: BMSLayout
})

interface Props {
  asset: any
  assignments: any[]
  maintenance: any[]
}

const props = defineProps<Props>()

const showDeprModal = ref(false)

const deprForm = useForm({
  method: 'straight_line',
  useful_life_years: 5,
  salvage_value: 0,
  depreciation_start_date: props.asset?.purchase_date || '',
})

const openDeprModal = () => {
  if (props.asset?.depreciation) {
    deprForm.method = props.asset.depreciation.method
    deprForm.useful_life_years = props.asset.depreciation.useful_life_years
    deprForm.salvage_value = props.asset.depreciation.salvage_value
    deprForm.depreciation_start_date = props.asset.depreciation.depreciation_start_date
  }
  showDeprModal.value = true
}

const submitDepr = () => {
  deprForm.post(route('bms.assets.setup-depreciation', props.asset.id), {
    onSuccess: () => { showDeprModal.value = false }
  })
}

const applyDepreciation = () => {
  router.post(route('bms.assets.apply-depreciation', props.asset.id), {}, {
    preserveScroll: true,
  })
}

const formatNumber = (value: number) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value)
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const getStatusClass = (status: string) => {
  const classes = {
    available: 'bg-green-100 text-green-800',
    in_use: 'bg-blue-100 text-blue-800',
    maintenance: 'bg-orange-100 text-orange-800',
    retired: 'bg-gray-100 text-gray-800',
  }
  return classes[status as keyof typeof classes] || classes.available
}

const methodLabels: Record<string, string> = {
  straight_line: 'Straight Line',
  declining_balance: 'Declining Balance',
  sum_of_years_digits: 'Sum of Years Digits',
}
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
      <Link :href="route('bms.assets.index')" class="text-sm text-blue-600 hover:text-blue-800 mb-2 inline-block">
        ← Back to Assets
      </Link>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">{{ asset.name }}</h1>
          <p class="text-sm text-gray-500">{{ asset.asset_number }}</p>
        </div>
        <div class="flex items-center gap-3">
          <span :class="['px-3 py-1 text-sm font-medium rounded-full', getStatusClass(asset.status)]">
            {{ asset.status.replace('_', ' ').toUpperCase() }}
          </span>
          <Link
            :href="route('bms.assets.edit', asset.id)"
            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium"
          >
            <PencilIcon class="h-4 w-4" aria-hidden="true" />
            Edit
          </Link>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 space-y-6">
        <!-- Asset Details -->
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Asset Details</h2>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="text-sm font-medium text-gray-500">Category</label>
              <p class="text-gray-900">{{ asset.category }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Condition</label>
              <p class="text-gray-900 capitalize">{{ asset.condition }}</p>
            </div>
            <div v-if="asset.serial_number">
              <label class="text-sm font-medium text-gray-500">Serial Number</label>
              <p class="text-gray-900">{{ asset.serial_number }}</p>
            </div>
            <div v-if="asset.model">
              <label class="text-sm font-medium text-gray-500">Model</label>
              <p class="text-gray-900">{{ asset.model }}</p>
            </div>
            <div v-if="asset.manufacturer">
              <label class="text-sm font-medium text-gray-500">Manufacturer</label>
              <p class="text-gray-900">{{ asset.manufacturer }}</p>
            </div>
            <div v-if="asset.location">
              <label class="text-sm font-medium text-gray-500">Location</label>
              <p class="text-gray-900">{{ asset.location }}</p>
            </div>
          </div>
        </div>

        <!-- Assignment History -->
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Assignment History</h2>
          <div v-if="assignments.length > 0" class="space-y-3">
            <div
              v-for="assignment in assignments"
              :key="assignment.id"
              class="p-4 bg-gray-50 rounded-lg border border-gray-200"
            >
              <div class="flex justify-between items-start">
                <div>
                  <p class="font-medium text-gray-900">{{ assignment.assigned_to_user.user.name }}</p>
                  <p class="text-sm text-gray-600">
                    {{ formatDate(assignment.assigned_date) }}
                    <span v-if="assignment.returned_date"> - {{ formatDate(assignment.returned_date) }}</span>
                    <span v-else class="text-blue-600 font-medium"> (Current)</span>
                  </p>
                </div>
              </div>
            </div>
          </div>
          <p v-else class="text-sm text-gray-500">No assignment history</p>
        </div>

        <!-- Maintenance History -->
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Maintenance History</h2>
          <div v-if="maintenance.length > 0" class="space-y-3">
            <div
              v-for="record in maintenance"
              :key="record.id"
              class="p-4 bg-gray-50 rounded-lg border border-gray-200"
            >
              <div class="flex justify-between items-start">
                <div>
                  <p class="font-medium text-gray-900 capitalize">{{ record.maintenance_type }}</p>
                  <p class="text-sm text-gray-600">{{ record.description }}</p>
                  <p class="text-sm text-gray-500 mt-1">
                    Scheduled: {{ formatDate(record.scheduled_date) }}
                  </p>
                </div>
                <span :class="[
                  'px-2 py-1 text-xs font-medium rounded-full',
                  record.status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800'
                ]">
                  {{ record.status.toUpperCase() }}
                </span>
              </div>
            </div>
          </div>
          <p v-else class="text-sm text-gray-500">No maintenance records</p>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Financial Info -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-sm font-semibold text-gray-900 mb-4">Financial Information</h3>
          <div class="space-y-3">
            <div>
              <label class="text-sm font-medium text-gray-500">Purchase Cost</label>
              <p class="text-gray-900 font-semibold">K{{ formatNumber(asset.purchase_cost) }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Current Value</label>
              <p class="text-gray-900 font-semibold">K{{ formatNumber(asset.current_value) }}</p>
            </div>
            <div v-if="asset.purchase_date">
              <label class="text-sm font-medium text-gray-500">Purchase Date</label>
              <p class="text-gray-900">{{ formatDate(asset.purchase_date) }}</p>
            </div>
          </div>
        </div>

        <!-- Assignment Info -->
        <div v-if="asset.assigned_to" class="bg-white rounded-lg shadow p-6">
          <h3 class="text-sm font-semibold text-gray-900 mb-4">Current Assignment</h3>
          <div class="space-y-3">
            <div>
              <label class="text-sm font-medium text-gray-500">Assigned To</label>
              <p class="text-gray-900">{{ asset.assigned_to.user.name }}</p>
            </div>
            <div v-if="asset.assigned_date">
              <label class="text-sm font-medium text-gray-500">Since</label>
              <p class="text-gray-900">{{ formatDate(asset.assigned_date) }}</p>
            </div>
          </div>
        </div>

        <!-- Warranty Info -->
        <div v-if="asset.warranty_expiry" class="bg-white rounded-lg shadow p-6">
          <h3 class="text-sm font-semibold text-gray-900 mb-4">Warranty</h3>
          <div class="space-y-3">
            <div>
              <label class="text-sm font-medium text-gray-500">Expires</label>
              <p class="text-gray-900">{{ formatDate(asset.warranty_expiry) }}</p>
            </div>
          </div>
        </div>

        <!-- Depreciation Info -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-900">Depreciation</h3>
            <button @click="openDeprModal" class="text-xs text-blue-600 hover:text-blue-700 font-medium">
              {{ asset.depreciation ? 'Edit' : 'Setup' }}
            </button>
          </div>
          <div v-if="asset.depreciation" class="space-y-3">
            <div>
              <label class="text-sm font-medium text-gray-500">Method</label>
              <p class="text-gray-900 text-sm">{{ methodLabels[asset.depreciation.method] || asset.depreciation.method }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Useful Life</label>
              <p class="text-gray-900 text-sm">{{ asset.depreciation.useful_life_years }} years</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Salvage Value</label>
              <p class="text-gray-900 text-sm">K{{ formatNumber(asset.depreciation.salvage_value) }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Annual Depreciation</label>
              <p class="text-gray-900 text-sm font-semibold">K{{ formatNumber(asset.depreciation.annual_depreciation) }}</p>
            </div>
            <div v-if="asset.depreciation.depreciation_start_date">
              <label class="text-sm font-medium text-gray-500">Start Date</label>
              <p class="text-gray-900 text-sm">{{ formatDate(asset.depreciation.depreciation_start_date) }}</p>
            </div>
            <button @click="applyDepreciation" class="w-full mt-2 px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg text-xs font-medium hover:bg-blue-100">
              Recalculate Current Value
            </button>
          </div>
          <p v-else class="text-sm text-gray-400 italic">Not configured</p>
        </div>
      </div>
    </div>

    <!-- Depreciation Setup Modal -->
    <div v-if="showDeprModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="showDeprModal = false">
      <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ asset.depreciation ? 'Edit' : 'Setup' }} Depreciation</h2>
        <form @submit.prevent="submitDepr" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Method</label>
            <select v-model="deprForm.method" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
              <option value="straight_line">Straight Line</option>
              <option value="declining_balance">Declining Balance</option>
              <option value="sum_of_years_digits">Sum of Years Digits</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Useful Life (years)</label>
            <input v-model.number="deprForm.useful_life_years" type="number" min="1" max="100" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Salvage Value</label>
            <input v-model.number="deprForm.salvage_value" type="number" min="0" step="0.01" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Depreciation Start Date</label>
            <input v-model="deprForm.depreciation_start_date" type="date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
          </div>
          <div v-if="deprForm.errors.method || deprForm.errors.useful_life_years || deprForm.errors.salvage_value" class="text-sm text-red-600">{{ deprForm.errors.method || deprForm.errors.useful_life_years || deprForm.errors.salvage_value }}</div>
          <div class="flex justify-end gap-3 pt-2">
            <button type="button" @click="showDeprModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg">Cancel</button>
            <button type="submit" :disabled="deprForm.processing" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50">
              {{ deprForm.processing ? 'Saving...' : 'Save' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
