<template>
  <BMSLayout page-title="What-If Scenarios">
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">What-If Scenarios</h1>
          <p class="mt-1 text-sm text-gray-500">Simulate task reassignments and analyze impact before applying changes</p>
        </div>
        <button
          @click="showCreateModal = true"
          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
        >
          <BeakerIcon class="h-5 w-5" aria-hidden="true" />
          New Scenario
        </button>
      </div>

      <!-- Scenarios List -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div
          v-for="scenario in scenarios"
          :key="scenario.id"
          class="bg-white rounded-lg shadow p-6"
        >
          <div class="flex items-start justify-between mb-4">
            <div>
              <h3 class="text-lg font-semibold text-gray-900">{{ scenario.name }}</h3>
              <p class="text-sm text-gray-500 mt-1">{{ scenario.description }}</p>
              <p class="text-xs text-gray-400 mt-2">
                Created {{ formatDate(scenario.created_at) }} by {{ scenario.created_by_name }}
              </p>
            </div>
            <span
              :class="[
                'px-3 py-1 rounded-full text-xs font-semibold',
                scenario.status === 'applied' ? 'bg-green-100 text-green-700' :
                scenario.status === 'rejected' ? 'bg-red-100 text-red-700' :
                'bg-blue-100 text-blue-700'
              ]"
            >
              {{ scenario.status.charAt(0).toUpperCase() + scenario.status.slice(1) }}
            </span>
          </div>

          <!-- Metrics Comparison -->
          <div class="border-t border-gray-200 pt-4 mb-4">
            <h4 class="text-sm font-semibold text-gray-700 mb-3">Impact Analysis</h4>
            
            <div class="grid grid-cols-2 gap-4">
              <!-- Before -->
              <div>
                <p class="text-xs text-gray-500 mb-2">Before</p>
                <div class="space-y-2">
                  <div>
                    <p class="text-xs text-gray-600">Overloaded Users</p>
                    <p class="text-lg font-bold text-red-600">{{ scenario.metrics_before.overloaded_users }}</p>
                  </div>
                  <div>
                    <p class="text-xs text-gray-600">Avg Utilization</p>
                    <p class="text-lg font-bold text-gray-900">{{ scenario.metrics_before.average_utilization }}%</p>
                  </div>
                </div>
              </div>

              <!-- After -->
              <div>
                <p class="text-xs text-gray-500 mb-2">After</p>
                <div class="space-y-2">
                  <div>
                    <p class="text-xs text-gray-600">Overloaded Users</p>
                    <p class="text-lg font-bold text-green-600">{{ scenario.metrics_after.overloaded_users }}</p>
                  </div>
                  <div>
                    <p class="text-xs text-gray-600">Avg Utilization</p>
                    <p class="text-lg font-bold text-gray-900">{{ scenario.metrics_after.average_utilization }}%</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Improvement -->
            <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
              <div class="flex items-center gap-2">
                <ArrowTrendingUpIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                <div>
                  <p class="text-sm font-semibold text-blue-900">
                    {{ calculateImprovement(scenario) }}% Improvement
                  </p>
                  <p class="text-xs text-blue-700">in workload balance</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div v-if="scenario.status === 'pending'" class="flex gap-2">
            <button
              @click="applyScenario(scenario.id)"
              class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-medium"
            >
              Apply Changes
            </button>
            <button
              @click="rejectScenario(scenario.id)"
              class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm font-medium"
            >
              Reject
            </button>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="scenarios.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
        <BeakerIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">No scenarios yet</h3>
        <p class="mt-1 text-sm text-gray-500">Create a what-if scenario to simulate task reassignments.</p>
        <button
          @click="showCreateModal = true"
          class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
        >
          <BeakerIcon class="h-5 w-5" aria-hidden="true" />
          Create First Scenario
        </button>
      </div>
    </div>

    <!-- Create Scenario Modal -->
    <div
      v-if="showCreateModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="showCreateModal = false"
    >
      <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">Create What-If Scenario</h2>
            <button
              @click="showCreateModal = false"
              class="p-2 hover:bg-gray-100 rounded-lg transition"
              aria-label="Close modal"
            >
              <XMarkIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
            </button>
          </div>
        </div>

        <form @submit.prevent="createScenario" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Scenario Name</label>
            <input
              v-model="form.name"
              type="text"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="e.g., Balance Q1 Workload"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <textarea
              v-model="form.description"
              rows="3"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Describe what this scenario tests..."
            ></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Scenario Type</label>
            <select
              v-model="form.type"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="workload_balance">Workload Balance</option>
              <option value="resource_optimization">Resource Optimization</option>
              <option value="deadline_adjustment">Deadline Adjustment</option>
            </select>
          </div>

          <div class="pt-4 border-t border-gray-200 flex gap-3">
            <button
              type="button"
              @click="showCreateModal = false"
              class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
            >
              Create Scenario
            </button>
          </div>
        </form>
      </div>
    </div>
  </BMSLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import BMSLayout from '@/Layouts/BMSLayout.vue'
import {
  BeakerIcon,
  ArrowTrendingUpIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline'

interface Scenario {
  id: number
  name: string
  description: string
  status: 'pending' | 'applied' | 'rejected'
  created_at: string
  created_by_name: string
  metrics_before: {
    overloaded_users: number
    average_utilization: number
  }
  metrics_after: {
    overloaded_users: number
    average_utilization: number
  }
}

interface Props {
  scenarios: Scenario[]
}

defineProps<Props>()

const showCreateModal = ref(false)
const form = ref({
  name: '',
  description: '',
  type: 'workload_balance',
  changes: []
})

const formatDate = (dateString: string) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const calculateImprovement = (scenario: Scenario) => {
  const before = scenario.metrics_before.overloaded_users
  const after = scenario.metrics_after.overloaded_users
  if (before === 0) return 0
  return Math.round(((before - after) / before) * 100)
}

const createScenario = () => {
  router.post(route('bms.operations.scenarios.store'), form.value, {
    onSuccess: () => {
      showCreateModal.value = false
      form.value = {
        name: '',
        description: '',
        type: 'workload_balance',
        changes: []
      }
    }
  })
}

const applyScenario = (scenarioId: number) => {
  if (confirm('Are you sure you want to apply this scenario? This will reassign tasks.')) {
    router.post(route('bms.operations.scenarios.apply', scenarioId))
  }
}

const rejectScenario = (scenarioId: number) => {
  router.post(route('bms.operations.scenarios.reject', scenarioId))
}
</script>
