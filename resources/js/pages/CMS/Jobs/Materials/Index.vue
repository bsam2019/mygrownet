<template>
  <CMSLayout :title="`Job Materials - ${job.job_number}`">
    <div class="space-y-6">
      <!-- Header -->
      <div>
        <Link
          :href="route('cms.jobs.show', job.id)"
          class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-4"
        >
          <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
          Back to Job
        </Link>
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Material Planning</h1>
            <p class="mt-1 text-sm text-gray-500">
              {{ job.customer.name }} - {{ job.job_type }}
            </p>
          </div>
          <button
            @click="showAddMaterial = true"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
          >
            <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
            Add Material
          </button>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <CubeIcon class="h-8 w-8 text-blue-600" aria-hidden="true" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Total Materials</p>
              <p class="text-2xl font-bold text-gray-900">{{ summary.materials_count }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <CurrencyDollarIcon class="h-8 w-8 text-green-600" aria-hidden="true" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Planned Cost</p>
              <p class="text-2xl font-bold text-gray-900">K {{ formatMoney(summary.total_planned_cost) }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <ChartBarIcon class="h-8 w-8 text-purple-600" aria-hidden="true" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Actual Cost</p>
              <p class="text-2xl font-bold text-gray-900">
                K {{ formatMoney(summary.total_actual_cost || 0) }}
              </p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <ArrowTrendingUpIcon
                :class="[
                  'h-8 w-8',
                  summary.variance >= 0 ? 'text-red-600' : 'text-green-600'
                ]"
                aria-hidden="true"
              />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Variance</p>
              <p
                :class="[
                  'text-2xl font-bold',
                  summary.variance >= 0 ? 'text-red-600' : 'text-green-600'
                ]"
              >
                {{ summary.variance >= 0 ? '+' : '' }}K {{ formatMoney(summary.variance || 0) }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Status Summary -->
      <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-6">
            <div class="flex items-center">
              <span class="inline-block w-3 h-3 bg-gray-400 rounded-full mr-2"></span>
              <span class="text-sm text-gray-600">Planned: {{ summary.planned_count }}</span>
            </div>
            <div class="flex items-center">
              <span class="inline-block w-3 h-3 bg-amber-400 rounded-full mr-2"></span>
              <span class="text-sm text-gray-600">Ordered: {{ summary.ordered_count }}</span>
            </div>
            <div class="flex items-center">
              <span class="inline-block w-3 h-3 bg-blue-400 rounded-full mr-2"></span>
              <span class="text-sm text-gray-600">Received: {{ summary.received_count }}</span>
            </div>
            <div class="flex items-center">
              <span class="inline-block w-3 h-3 bg-green-400 rounded-full mr-2"></span>
              <span class="text-sm text-gray-600">Used: {{ summary.used_count }}</span>
            </div>
          </div>
          <div class="flex items-center space-x-2">
            <button
              v-if="templates.length > 0"
              @click="showTemplateModal = true"
              class="px-3 py-2 text-sm text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100"
            >
              Apply Template
            </button>
            <button
              v-if="hasPlannedMaterials"
              @click="createPurchaseOrder"
              class="px-3 py-2 text-sm text-white bg-indigo-600 rounded-lg hover:bg-indigo-700"
            >
              Create Purchase Order
            </button>
          </div>
        </div>
      </div>

      <!-- Materials Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Material
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Planned Qty
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Unit Price
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Planned Cost
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actual Cost
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Variance
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="plan in job.material_plans" :key="plan.id" class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <div class="flex flex-col">
                    <span class="text-sm font-medium text-gray-900">{{ plan.material.name }}</span>
                    <span class="text-xs text-gray-500">{{ plan.material.code }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  {{ formatNumber(plan.planned_quantity) }} {{ plan.material.unit }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  K {{ formatMoney(plan.unit_price) }}
                </td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                  K {{ formatMoney(plan.total_cost) }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  <span v-if="plan.actual_total_cost">
                    K {{ formatMoney(plan.actual_total_cost) }}
                  </span>
                  <span v-else class="text-gray-400">-</span>
                </td>
                <td class="px-6 py-4 text-sm">
                  <span
                    v-if="plan.actual_total_cost"
                    :class="[
                      'font-medium',
                      getVariance(plan) >= 0 ? 'text-red-600' : 'text-green-600'
                    ]"
                  >
                    {{ getVariance(plan) >= 0 ? '+' : '' }}K {{ formatMoney(Math.abs(getVariance(plan))) }}
                  </span>
                  <span v-else class="text-gray-400">-</span>
                </td>
                <td class="px-6 py-4">
                  <span
                    :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                      getStatusColor(plan.status)
                    ]"
                  >
                    {{ getStatusLabel(plan.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right text-sm font-medium space-x-2">
                  <button
                    @click="editMaterial(plan)"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    Edit
                  </button>
                  <button
                    v-if="plan.status === 'planned'"
                    @click="deleteMaterial(plan)"
                    class="text-red-600 hover:text-red-900"
                  >
                    Remove
                  </button>
                </td>
              </tr>
              <tr v-if="job.material_plans.length === 0">
                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                  <CubeIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                  <p class="mt-2">No materials planned yet</p>
                  <button
                    @click="showAddMaterial = true"
                    class="mt-4 inline-flex items-center text-blue-600 hover:text-blue-700"
                  >
                    <PlusIcon class="h-5 w-5 mr-1" aria-hidden="true" />
                    Add your first material
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Add Material Modal -->
      <AddMaterialModal
        :show="showAddMaterial"
        :job="job"
        :materials="materials"
        @close="showAddMaterial = false"
        @added="handleMaterialAdded"
      />

      <!-- Edit Material Modal -->
      <EditMaterialModal
        :show="showEditMaterial"
        :job="job"
        :plan="selectedPlan"
        @close="showEditMaterial = false"
        @updated="handleMaterialUpdated"
      />

      <!-- Apply Template Modal -->
      <ApplyTemplateModal
        :show="showTemplateModal"
        :job="job"
        :templates="templates"
        @close="showTemplateModal = false"
        @applied="handleTemplateApplied"
      />
    </div>
  </CMSLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import CMSLayout from '@/Layouts/CMSLayout.vue'
import AddMaterialModal from './Components/AddMaterialModal.vue'
import EditMaterialModal from './Components/EditMaterialModal.vue'
import ApplyTemplateModal from './Components/ApplyTemplateModal.vue'
import {
  ArrowLeftIcon,
  PlusIcon,
  CubeIcon,
  CurrencyDollarIcon,
  ChartBarIcon,
  ArrowTrendingUpIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  job: Object,
  materials: Array,
  templates: Array,
  summary: Object,
})

const showAddMaterial = ref(false)
const showEditMaterial = ref(false)
const showTemplateModal = ref(false)
const selectedPlan = ref(null)

const hasPlannedMaterials = computed(() => {
  return props.job.material_plans.some(plan => plan.status === 'planned')
})

const formatMoney = (value) => {
  return parseFloat(value || 0).toFixed(2)
}

const formatNumber = (value) => {
  return parseFloat(value || 0).toFixed(2)
}

const getVariance = (plan) => {
  if (!plan.actual_total_cost) return 0
  return parseFloat(plan.actual_total_cost) - parseFloat(plan.total_cost)
}

const getStatusColor = (status) => {
  const colors = {
    planned: 'bg-gray-100 text-gray-800',
    ordered: 'bg-amber-100 text-amber-800',
    received: 'bg-blue-100 text-blue-800',
    used: 'bg-green-100 text-green-800',
  }
  return colors[status] || 'bg-gray-100 text-gray-800'
}

const getStatusLabel = (status) => {
  const labels = {
    planned: 'Planned',
    ordered: 'Ordered',
    received: 'Received',
    used: 'Used',
  }
  return labels[status] || status
}

const editMaterial = (plan) => {
  selectedPlan.value = plan
  showEditMaterial.value = true
}

const deleteMaterial = (plan) => {
  if (confirm('Are you sure you want to remove this material?')) {
    router.delete(route('cms.jobs.materials.destroy', [props.job.id, plan.id]), {
      preserveScroll: true,
    })
  }
}

const createPurchaseOrder = () => {
  router.get(route('cms.purchase-orders.create-from-job', props.job.id))
}

const handleMaterialAdded = () => {
  showAddMaterial.value = false
  router.reload({ only: ['job', 'summary'] })
}

const handleMaterialUpdated = () => {
  showEditMaterial.value = false
  router.reload({ only: ['job', 'summary'] })
}

const handleTemplateApplied = () => {
  showTemplateModal.value = false
  router.reload({ only: ['job', 'summary'] })
}
</script>
