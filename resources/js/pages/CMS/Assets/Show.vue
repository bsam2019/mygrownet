<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { PencilIcon } from '@heroicons/vue/24/outline'
import CMSLayoutNew from '@/Layouts/CMSLayoutNew.vue'

defineOptions({
  layout: CMSLayoutNew
})

interface Props {
  asset: any
  assignments: any[]
  maintenance: any[]
}

const props = defineProps<Props>()

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
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
      <Link :href="route('cms.assets.index')" class="text-sm text-blue-600 hover:text-blue-800 mb-2 inline-block">
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
            :href="route('cms.assets.edit', asset.id)"
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
      </div>
    </div>
  </div>
</template>
