<template>
  <CMSLayout page-title="Workflows">
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Workflows</h1>
          <p class="mt-1 text-sm text-gray-500">Manage task workflows and stages</p>
        </div>
        <button
          @click="showCreateModal = true"
          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
        >
          <PlusIcon class="h-5 w-5" aria-hidden="true" />
          New Workflow
        </button>
      </div>

      <!-- Empty State -->
      <div v-if="workflows.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
        <ArrowPathIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">No workflows yet</h3>
        <p class="mt-1 text-sm text-gray-500">Get started by creating your first workflow.</p>
        <button
          @click="showCreateModal = true"
          class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
        >
          <PlusIcon class="h-5 w-5" aria-hidden="true" />
          Create First Workflow
        </button>
      </div>

      <!-- Workflows List -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div v-for="workflow in workflows" :key="workflow.id" class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-semibold text-gray-900">{{ workflow.name }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ workflow.description }}</p>
              </div>
              <div class="flex items-center gap-2">
                <span class="px-3 py-1 text-sm font-medium text-blue-700 bg-blue-100 rounded-full">
                  {{ workflow.tasks_count }} tasks
                </span>
                <button
                  @click="editWorkflow(workflow)"
                  class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition"
                  aria-label="Edit workflow"
                >
                  <PencilIcon class="h-5 w-5" aria-hidden="true" />
                </button>
              </div>
            </div>
          </div>

          <div class="p-6">
            <div class="flex items-center justify-between mb-3">
              <p class="text-sm font-medium text-gray-700">Stages:</p>
              <button
                @click="addStage(workflow)"
                class="text-sm text-blue-600 hover:text-blue-700 font-medium"
              >
                + Add Stage
              </button>
            </div>
            <div class="space-y-2">
              <div
                v-for="stage in workflow.stages"
                :key="stage.id"
                class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:border-blue-300 transition"
              >
                <div
                  class="w-3 h-3 rounded-full flex-shrink-0"
                  :style="{ backgroundColor: stage.color || '#6b7280' }"
                ></div>
                <div class="flex-1">
                  <p class="text-sm font-medium text-gray-900">{{ stage.name }}</p>
                  <p v-if="stage.requires_approval" class="text-xs text-gray-500">Requires approval</p>
                </div>
                <span class="text-xs text-gray-500">Step {{ stage.sequence_order }}</span>
              </div>
              <div v-if="!workflow.stages || workflow.stages.length === 0" class="text-center py-4 text-sm text-gray-500">
                No stages yet. Add your first stage.
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Workflow Modal -->
    <div
      v-if="showCreateModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="closeModal"
    >
      <div class="bg-white rounded-lg shadow-xl max-w-lg w-full">
        <div class="p-6 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">
              {{ editingWorkflow ? 'Edit Workflow' : 'Create Workflow' }}
            </h2>
            <button
              @click="closeModal"
              class="p-2 hover:bg-gray-100 rounded-lg transition"
              aria-label="Close modal"
            >
              <XMarkIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
            </button>
          </div>
        </div>

        <form @submit.prevent="saveWorkflow" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Workflow Name</label>
            <input
              v-model="form.name"
              type="text"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="e.g., Development Process"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <textarea
              v-model="form.description"
              rows="3"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Describe this workflow..."
            ></textarea>
          </div>

          <div class="pt-4 border-t border-gray-200 flex gap-3">
            <button
              type="button"
              @click="closeModal"
              class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
            >
              {{ editingWorkflow ? 'Update' : 'Create' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Add Stage Modal -->
    <div
      v-if="showStageModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="closeStageModal"
    >
      <div class="bg-white rounded-lg shadow-xl max-w-lg w-full">
        <div class="p-6 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">Add Stage</h2>
            <button
              @click="closeStageModal"
              class="p-2 hover:bg-gray-100 rounded-lg transition"
              aria-label="Close modal"
            >
              <XMarkIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
            </button>
          </div>
        </div>

        <form @submit.prevent="saveStage" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Stage Name</label>
            <input
              v-model="stageForm.name"
              type="text"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="e.g., In Progress"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Color</label>
            <input
              v-model="stageForm.color"
              type="color"
              class="w-full h-10 px-2 border border-gray-300 rounded-lg"
            />
          </div>

          <div class="flex items-center gap-2">
            <input
              v-model="stageForm.requires_approval"
              type="checkbox"
              id="requires_approval"
              class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            />
            <label for="requires_approval" class="text-sm text-gray-700">Requires approval</label>
          </div>

          <div class="pt-4 border-t border-gray-200 flex gap-3">
            <button
              type="button"
              @click="closeStageModal"
              class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
            >
              Add Stage
            </button>
          </div>
        </form>
      </div>
    </div>
  </CMSLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import CMSLayout from '@/Layouts/CMSLayout.vue'
import {
  PlusIcon,
  ArrowPathIcon,
  XMarkIcon,
  PencilIcon
} from '@heroicons/vue/24/outline'

interface Props {
  workflows: any[]
}

const props = defineProps<Props>()

const showCreateModal = ref(false)
const showStageModal = ref(false)
const editingWorkflow = ref<any>(null)
const selectedWorkflow = ref<any>(null)

const form = ref({
  name: '',
  description: ''
})

const stageForm = ref({
  name: '',
  color: '#3b82f6',
  requires_approval: false
})

const editWorkflow = (workflow: any) => {
  editingWorkflow.value = workflow
  form.value = {
    name: workflow.name,
    description: workflow.description || ''
  }
  showCreateModal.value = true
}

const addStage = (workflow: any) => {
  selectedWorkflow.value = workflow
  stageForm.value = {
    name: '',
    color: '#3b82f6',
    requires_approval: false
  }
  showStageModal.value = true
}

const closeModal = () => {
  showCreateModal.value = false
  editingWorkflow.value = null
  form.value = { name: '', description: '' }
}

const closeStageModal = () => {
  showStageModal.value = false
  selectedWorkflow.value = null
  stageForm.value = { name: '', color: '#3b82f6', requires_approval: false }
}

const saveWorkflow = () => {
  if (editingWorkflow.value) {
    router.put(route('cms.operations.workflows.update', editingWorkflow.value.id), form.value, {
      onSuccess: () => closeModal()
    })
  } else {
    router.post(route('cms.operations.workflows.store'), form.value, {
      onSuccess: () => closeModal()
    })
  }
}

const saveStage = () => {
  router.post(route('cms.operations.workflows.stages.store', selectedWorkflow.value.id), stageForm.value, {
    onSuccess: () => closeStageModal()
  })
}
</script>
