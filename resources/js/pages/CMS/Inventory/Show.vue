<script setup lang="ts">
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { PencilIcon, ExclamationTriangleIcon, ClockIcon, BriefcaseIcon } from '@heroicons/vue/24/outline'
import CMSLayoutNew from '@/Layouts/CMSLayoutNew.vue'
import StockMovementModal from '@/components/CMS/StockMovementModal.vue'

defineOptions({
  layout: CMSLayoutNew
})

interface Props {
  item: any
  movements: any[]
  jobUsage: any[]
}

const props = defineProps<Props>()

const showMovementModal = ref(false)

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
    hour: '2-digit',
    minute: '2-digit',
  })
}

const getMovementTypeClass = (type: string) => {
  const classes = {
    purchase: 'bg-green-100 text-green-800',
    usage: 'bg-red-100 text-red-800',
    adjustment: 'bg-blue-100 text-blue-800',
    return: 'bg-purple-100 text-purple-800',
    damage: 'bg-orange-100 text-orange-800',
    transfer: 'bg-gray-100 text-gray-800',
  }
  return classes[type as keyof typeof classes] || classes.adjustment
}

const getMovementTypeLabel = (type: string) => {
  return type.charAt(0).toUpperCase() + type.slice(1)
}

const isLowStock = props.item.current_stock <= props.item.minimum_stock
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <!-- Page Header -->
    <div class="mb-6">
      <Link :href="route('cms.inventory.index')" class="text-sm text-blue-600 hover:text-blue-800 mb-2 inline-block">
        ← Back to Inventory
      </Link>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">{{ item.name }}</h1>
          <p class="text-sm text-gray-500">{{ item.item_code }}</p>
        </div>
        <div class="flex items-center gap-3">
          <span
            v-if="isLowStock"
            class="inline-flex items-center gap-1 px-3 py-1 text-sm font-medium rounded-full bg-red-100 text-red-800"
          >
            <ExclamationTriangleIcon class="h-4 w-4" aria-hidden="true" />
            Low Stock
          </span>
          <span
            v-if="!item.is_active"
            class="px-3 py-1 text-sm font-medium rounded-full bg-gray-100 text-gray-800"
          >
            Inactive
          </span>
          <Link
            :href="route('cms.inventory.edit', item.id)"
            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium"
          >
            <PencilIcon class="h-4 w-4" aria-hidden="true" />
            Edit
          </Link>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Content -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Item Details -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Item Details</h2>
          </div>
          <div class="p-6 space-y-4">
            <div v-if="item.description">
              <label class="text-sm font-medium text-gray-500">Description</label>
              <p class="text-gray-900 whitespace-pre-wrap">{{ item.description }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="text-sm font-medium text-gray-500">Category</label>
                <p class="text-gray-900">{{ item.category }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">Unit</label>
                <p class="text-gray-900 capitalize">{{ item.unit }}</p>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="text-sm font-medium text-gray-500">Unit Cost</label>
                <p class="text-gray-900">K{{ formatNumber(item.unit_cost) }}</p>
              </div>
              <div v-if="item.selling_price">
                <label class="text-sm font-medium text-gray-500">Selling Price</label>
                <p class="text-gray-900">K{{ formatNumber(item.selling_price) }}</p>
              </div>
            </div>

            <div v-if="item.supplier">
              <label class="text-sm font-medium text-gray-500">Supplier</label>
              <p class="text-gray-900">{{ item.supplier }}</p>
            </div>

            <div v-if="item.location">
              <label class="text-sm font-medium text-gray-500">Storage Location</label>
              <p class="text-gray-900">{{ item.location }}</p>
            </div>
          </div>
        </div>

        <!-- Stock Movement History -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Stock Movement History</h2>
            <button
              @click="showMovementModal = true"
              class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium"
            >
              <ClockIcon class="h-4 w-4" aria-hidden="true" />
              Record Movement
            </button>
          </div>
          <div class="p-6">
            <div v-if="movements.length > 0" class="space-y-3">
              <div
                v-for="movement in movements"
                :key="movement.id"
                class="flex items-start justify-between p-4 bg-gray-50 rounded-lg border border-gray-200"
              >
                <div class="flex-1">
                  <div class="flex items-center gap-2 mb-2">
                    <span
                      :class="['px-2 py-1 text-xs font-medium rounded-full', getMovementTypeClass(movement.movement_type)]"
                    >
                      {{ getMovementTypeLabel(movement.movement_type) }}
                    </span>
                    <span class="text-sm text-gray-500">
                      {{ formatDate(movement.created_at) }}
                    </span>
                  </div>
                  <div class="grid grid-cols-3 gap-4 text-sm">
                    <div>
                      <span class="text-gray-500">Quantity:</span>
                      <span :class="['font-medium ml-1', movement.quantity > 0 ? 'text-green-600' : 'text-red-600']">
                        {{ movement.quantity > 0 ? '+' : '' }}{{ movement.quantity }}
                      </span>
                    </div>
                    <div>
                      <span class="text-gray-500">Before:</span>
                      <span class="font-medium ml-1">{{ movement.stock_before }}</span>
                    </div>
                    <div>
                      <span class="text-gray-500">After:</span>
                      <span class="font-medium ml-1">{{ movement.stock_after }}</span>
                    </div>
                  </div>
                  <div v-if="movement.unit_cost" class="text-sm text-gray-600 mt-1">
                    Unit Cost: K{{ formatNumber(movement.unit_cost) }}
                  </div>
                  <div v-if="movement.reference_number" class="text-sm text-gray-600 mt-1">
                    Ref: {{ movement.reference_number }}
                  </div>
                  <div v-if="movement.notes" class="text-sm text-gray-600 mt-1 italic">
                    {{ movement.notes }}
                  </div>
                  <div v-if="movement.job" class="text-sm text-gray-600 mt-1">
                    Job: {{ movement.job.job_number }}
                  </div>
                  <div class="text-xs text-gray-500 mt-2">
                    By: {{ movement.created_by?.user?.name }}
                  </div>
                </div>
              </div>
            </div>
            <p v-else class="text-sm text-gray-500 text-center py-8">
              No stock movements yet
            </p>
          </div>
        </div>

        <!-- Job Usage History -->
        <div v-if="jobUsage.length > 0" class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Job Usage History</h2>
          </div>
          <div class="p-6">
            <div class="space-y-3">
              <div
                v-for="usage in jobUsage"
                :key="usage.id"
                class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200"
              >
                <div class="flex items-center gap-3 flex-1 min-w-0">
                  <BriefcaseIcon class="h-6 w-6 text-gray-600 flex-shrink-0" aria-hidden="true" />
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900">
                      {{ usage.job.job_number }}
                    </p>
                    <p class="text-xs text-gray-500">
                      {{ usage.job.customer?.name }}
                    </p>
                    <p class="text-xs text-gray-600 mt-1">
                      Quantity: {{ usage.quantity_used }} @ K{{ formatNumber(usage.unit_cost) }} = K{{ formatNumber(usage.total_cost) }}
                    </p>
                  </div>
                </div>
                <Link
                  :href="route('cms.jobs.show', usage.job.id)"
                  class="flex-shrink-0 ml-3 text-blue-600 hover:text-blue-800 text-sm font-medium"
                >
                  View Job
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Stock Status -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-sm font-semibold text-gray-900 mb-4">Stock Status</h3>
          <div class="space-y-4">
            <div>
              <label class="text-sm font-medium text-gray-500">Current Stock</label>
              <p :class="['text-2xl font-bold', isLowStock ? 'text-red-600' : 'text-gray-900']">
                {{ item.current_stock }}
              </p>
              <p class="text-xs text-gray-500">{{ item.unit }}</p>
            </div>
            <div class="pt-4 border-t border-gray-200">
              <label class="text-sm font-medium text-gray-500">Minimum Stock</label>
              <p class="text-gray-900">{{ item.minimum_stock }}</p>
            </div>
            <div v-if="item.reorder_quantity" class="pt-4 border-t border-gray-200">
              <label class="text-sm font-medium text-gray-500">Reorder Quantity</label>
              <p class="text-gray-900">{{ item.reorder_quantity }}</p>
            </div>
            <div class="pt-4 border-t border-gray-200">
              <label class="text-sm font-medium text-gray-500">Stock Value</label>
              <p class="text-gray-900 font-semibold">
                K{{ formatNumber(item.current_stock * item.unit_cost) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Low Stock Alerts -->
        <div v-if="item.low_stock_alerts && item.low_stock_alerts.length > 0" class="bg-red-50 rounded-lg border border-red-200 p-6">
          <h3 class="text-sm font-semibold text-red-900 mb-4 flex items-center gap-2">
            <ExclamationTriangleIcon class="h-5 w-5" aria-hidden="true" />
            Active Alerts
          </h3>
          <div class="space-y-3">
            <div
              v-for="alert in item.low_stock_alerts"
              :key="alert.id"
              class="text-sm"
            >
              <p class="text-red-800 font-medium">Low stock alert</p>
              <p class="text-red-600 text-xs mt-1">
                Created: {{ formatDate(alert.created_at) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Metadata -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-sm font-semibold text-gray-900 mb-4">Metadata</h3>
          <div class="space-y-3 text-sm">
            <div>
              <label class="text-gray-500">Created By</label>
              <p class="text-gray-900">{{ item.created_by?.user?.name }}</p>
            </div>
            <div>
              <label class="text-gray-500">Created</label>
              <p class="text-gray-900">{{ formatDate(item.created_at) }}</p>
            </div>
            <div>
              <label class="text-gray-500">Last Updated</label>
              <p class="text-gray-900">{{ formatDate(item.updated_at) }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Stock Movement Modal -->
    <StockMovementModal
      :show="showMovementModal"
      :inventory-id="item.id"
      @close="showMovementModal = false"
    />
  </div>
</template>
