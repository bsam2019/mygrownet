<template>
  <BMSLayout :page-title="`Task: ${task.title}`">
    <div class="max-w-7xl mx-auto space-y-6">
      <!-- Task Header -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <div class="flex items-center gap-3">
              <h1 class="text-2xl font-bold text-gray-900">{{ task.title }}</h1>
              <span class="px-2 py-1 text-xs font-medium rounded-full"
                :class="{
                  'bg-gray-100 text-gray-700': task.status === 'pending',
                  'bg-blue-100 text-blue-700': task.status === 'in_progress',
                  'bg-green-100 text-green-700': task.status === 'completed',
                  'bg-red-100 text-red-700': task.status === 'blocked',
                }"
              >
                {{ task.status.replace('_', ' ') }}
              </span>
              <span class="px-2 py-1 text-xs font-medium rounded-full"
                :class="{
                  'bg-gray-100 text-gray-700': task.priority === 'low',
                  'bg-yellow-100 text-yellow-700': task.priority === 'medium',
                  'bg-orange-100 text-orange-700': task.priority === 'high',
                  'bg-red-100 text-red-700': task.priority === 'urgent',
                }"
              >
                {{ task.priority }}
              </span>
            </div>
            <p class="mt-1 text-sm text-gray-500">{{ task.task_number }}</p>
          </div>

          <div class="flex gap-2">
            <button
              v-if="task.status === 'pending'"
              @click="startTask"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition"
            >
              Start Task
            </button>
            <button
              v-if="task.status === 'in_progress'"
              @click="completeTask"
              class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition"
            >
              Complete Task
            </button>
            <button
              v-if="task.status === 'in_progress'"
              @click="showBlockModal = true"
              class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition"
            >
              Block Task
            </button>
          </div>
        </div>

        <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
          <div>
            <p class="text-sm font-medium text-gray-500">Assigned To</p>
            <p class="mt-1 text-sm text-gray-900">{{ task.assigned_user?.name || 'Unassigned' }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500">Due Date</p>
            <p class="mt-1 text-sm text-gray-900">
              {{ task.due_date ? new Date(task.due_date).toLocaleDateString() : 'Not set' }}
            </p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500">Estimated Hours</p>
            <p class="mt-1 text-sm text-gray-900">{{ task.estimated_hours || '-' }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500">Workflow Stage</p>
            <p class="mt-1 text-sm text-gray-900">{{ task.workflow_stage?.name || 'No workflow' }}</p>
          </div>
        </div>

        <div v-if="task.description" class="mt-6">
          <p class="text-sm font-medium text-gray-500">Description</p>
          <p class="mt-2 text-sm text-gray-700 whitespace-pre-wrap">{{ task.description }}</p>
        </div>

        <!-- Progress Bar -->
        <div v-if="task.progress_percentage > 0" class="mt-6">
          <div class="flex items-center justify-between mb-2">
            <p class="text-sm font-medium text-gray-500">Progress</p>
            <p class="text-sm font-medium text-gray-900">{{ task.progress_percentage }}%</p>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="bg-blue-600 h-2 rounded-full" :style="{ width: task.progress_percentage + '%' }"></div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Comments -->
          <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Comments</h2>
            </div>
            <div class="p-6">
              <div v-if="task.comments?.length === 0" class="text-center py-8 text-gray-500">
                No comments yet
              </div>
              <div v-else class="space-y-4 mb-6">
                <div v-for="comment in task.comments" :key="comment.id" class="flex gap-4">
                  <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                      <span class="text-xs font-medium text-gray-600">{{ comment.user?.name?.charAt(0) }}</span>
                    </div>
                  </div>
                  <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900">{{ comment.user?.name }}</p>
                    <p class="mt-1 text-sm text-gray-700">{{ comment.comment }}</p>
                    <p class="mt-1 text-xs text-gray-500">{{ new Date(comment.created_at).toLocaleString() }}</p>
                  </div>
                </div>
              </div>

              <!-- Add Comment Form -->
              <form @submit.prevent="addComment" class="mt-4">
                <textarea
                  v-model="newComment"
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Add a comment..."
                ></textarea>
                <div class="mt-2 flex justify-end">
                  <button
                    type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition"
                  >
                    Add Comment
                  </button>
                </div>
              </form>
            </div>
          </div>

          <!-- Activity Log -->
          <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Activity Log</h2>
            </div>
            <div class="p-6">
              <div v-if="task.logs.length === 0" class="text-center py-8 text-gray-500">
                No activity yet
              </div>
              <div v-else class="space-y-4">
                <div v-for="log in task.logs" :key="log.id" class="flex gap-4">
                  <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                      <span class="text-xs font-medium text-gray-600">{{ log.user?.name?.charAt(0) }}</span>
                    </div>
                  </div>
                  <div class="flex-1">
                    <p class="text-sm text-gray-900">
                      <span class="font-medium">{{ log.user?.name }}</span>
                      <span class="text-gray-600"> {{ log.action }} </span>
                    </p>
                    <p v-if="log.note" class="mt-1 text-sm text-gray-600">{{ log.note }}</p>
                    <p class="mt-1 text-xs text-gray-500">{{ new Date(log.created_at).toLocaleString() }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Attachments -->
          <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Attachments</h2>
            </div>
            <div class="p-6">
              <div v-if="task.attachments?.length === 0" class="text-center py-4 text-gray-500 text-sm">
                No attachments
              </div>
              <div v-else class="space-y-2 mb-4">
                <div v-for="attachment in task.attachments" :key="attachment.id" class="flex items-center justify-between p-2 bg-gray-50 rounded">
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ attachment.file_name }}</p>
                    <p class="text-xs text-gray-500">{{ formatFileSize(attachment.file_size) }}</p>
                  </div>
                  <button
                    @click="deleteAttachment(attachment.id)"
                    class="ml-2 text-red-600 hover:text-red-700"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>

              <!-- Upload Form -->
              <form @submit.prevent="uploadAttachment">
                <input
                  ref="fileInput"
                  type="file"
                  @change="handleFileSelect"
                  class="hidden"
                />
                <button
                  type="button"
                  @click="$refs.fileInput.click()"
                  class="w-full px-4 py-2 text-sm font-medium text-blue-600 border border-blue-600 rounded-lg hover:bg-blue-50 transition"
                >
                  Upload File
                </button>
              </form>
            </div>
          </div>

          <!-- Time Tracking -->
          <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Time Tracking</h2>
            </div>
            <div class="p-6">
              <div v-if="task.time_entries?.length === 0" class="text-center py-4 text-gray-500 text-sm">
                No time entries
              </div>
              <div v-else class="space-y-2 mb-4">
                <div v-for="entry in task.time_entries" :key="entry.id" class="p-2 bg-gray-50 rounded">
                  <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-900">{{ entry.user?.name }}</p>
                    <p class="text-sm font-medium text-gray-900">{{ entry.hours }}h</p>
                  </div>
                  <p class="text-xs text-gray-500 mt-1">{{ new Date(entry.started_at).toLocaleString() }}</p>
                </div>
              </div>

              <button
                @click="showTimeEntryModal = true"
                class="w-full px-4 py-2 text-sm font-medium text-blue-600 border border-blue-600 rounded-lg hover:bg-blue-50 transition"
              >
                Log Time
              </button>
            </div>
          </div>

          <!-- Dependencies -->
          <div v-if="task.dependencies?.length > 0" class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Dependencies</h2>
            </div>
            <div class="p-6">
              <div class="space-y-2">
                <div v-for="dep in task.dependencies" :key="dep.id" class="p-2 bg-gray-50 rounded">
                  <p class="text-sm font-medium text-gray-900">{{ dep.depends_on_task?.title }}</p>
                  <p class="text-xs text-gray-500">{{ dep.dependency_type.replace('_', ' ') }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Block Task Modal -->
    <div v-if="showBlockModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Block Task</h3>
        <form @submit.prevent="blockTask">
          <textarea
            v-model="blockReason"
            rows="4"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="Reason for blocking..."
            required
          ></textarea>
          <div class="mt-4 flex justify-end gap-2">
            <button
              type="button"
              @click="showBlockModal = false"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition"
            >
              Block Task
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Time Entry Modal -->
    <div v-if="showTimeEntryModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Log Time</h3>
        <form @submit.prevent="logTime">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Hours</label>
              <input
                v-model.number="timeEntry.hours"
                type="number"
                step="0.5"
                min="0"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                required
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea
                v-model="timeEntry.description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              ></textarea>
            </div>
          </div>
          <div class="mt-4 flex justify-end gap-2">
            <button
              type="button"
              @click="showTimeEntryModal = false"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition"
            >
              Log Time
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

interface Props {
  task: any
}

const props = defineProps<Props>()

const newComment = ref('')
const showBlockModal = ref(false)
const blockReason = ref('')
const showTimeEntryModal = ref(false)
const timeEntry = ref({
  hours: 0,
  description: '',
})
const selectedFile = ref<File | null>(null)
const fileInput = ref<HTMLInputElement | null>(null)

const startTask = () => {
  router.post(route('bms.operations.tasks.start', props.task.id))
}

const completeTask = () => {
  router.post(route('bms.operations.tasks.complete', props.task.id))
}

const blockTask = () => {
  router.post(route('bms.operations.tasks.block', props.task.id), {
    reason: blockReason.value,
  }, {
    onSuccess: () => {
      showBlockModal.value = false
      blockReason.value = ''
    },
  })
}

const addComment = () => {
  if (!newComment.value.trim()) return

  router.post(route('bms.operations.tasks.comments.store', props.task.id), {
    comment: newComment.value,
  }, {
    onSuccess: () => {
      newComment.value = ''
    },
  })
}

const handleFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    selectedFile.value = target.files[0]
    uploadAttachment()
  }
}

const uploadAttachment = () => {
  if (!selectedFile.value) return

  const formData = new FormData()
  formData.append('file', selectedFile.value)

  router.post(route('bms.operations.tasks.attachments.store', props.task.id), formData, {
    onSuccess: () => {
      selectedFile.value = null
      if (fileInput.value) {
        fileInput.value.value = ''
      }
    },
  })
}

const deleteAttachment = (attachmentId: number) => {
  if (confirm('Are you sure you want to delete this attachment?')) {
    router.delete(route('bms.operations.tasks.attachments.delete', [props.task.id, attachmentId]))
  }
}

const logTime = () => {
  router.post(route('bms.operations.tasks.time-entries.store', props.task.id), {
    started_at: new Date().toISOString(),
    hours: timeEntry.value.hours,
    description: timeEntry.value.description,
  }, {
    onSuccess: () => {
      showTimeEntryModal.value = false
      timeEntry.value = { hours: 0, description: '' }
    },
  })
}

const formatFileSize = (bytes: number): string => {
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB'
  return (bytes / (1024 * 1024)).toFixed(1) + ' MB'
}
</script>
