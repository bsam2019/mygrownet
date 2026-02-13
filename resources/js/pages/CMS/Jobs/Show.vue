<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <!-- Page Header -->
    <div class="mb-6">
      <Link :href="route('cms.jobs.index')" class="text-sm text-blue-600 hover:text-blue-800 mb-2 inline-block">
        ← Back to Jobs
      </Link>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">{{ job.job_number }}</h1>
          <p class="text-sm text-gray-500">{{ job.job_type }}</p>
        </div>
        <span :class="getStatusClass(job.status)">
          {{ formatStatus(job.status) }}
        </span>
      </div>
    </div>

    <!-- Content -->
    <div>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Job Details -->
          <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Job Details</h2>
            </div>
            <div class="p-6 space-y-4">
              <div>
                <label class="text-sm font-medium text-gray-500">Customer</label>
                <p class="text-gray-900">{{ job.customer?.name }}</p>
                <p class="text-sm text-gray-500">{{ job.customer?.customer_number }}</p>
              </div>

              <div v-if="job.description">
                <label class="text-sm font-medium text-gray-500">Description</label>
                <p class="text-gray-900 whitespace-pre-wrap">{{ job.description }}</p>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="text-sm font-medium text-gray-500">Quoted Value</label>
                  <p class="text-gray-900">K{{ formatNumber(job.quoted_value || 0) }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Priority</label>
                  <p class="text-gray-900 capitalize">{{ job.priority }}</p>
                </div>
              </div>

              <div v-if="job.deadline">
                <label class="text-sm font-medium text-gray-500">Deadline</label>
                <p class="text-gray-900">{{ formatDate(job.deadline) }}</p>
              </div>

              <div v-if="job.notes">
                <label class="text-sm font-medium text-gray-500">Internal Notes</label>
                <p class="text-gray-900 whitespace-pre-wrap">{{ job.notes }}</p>
              </div>
            </div>
          </div>

          <!-- Attachments -->
          <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Attachments</h2>
            </div>
            <div class="p-6">
              <div v-if="job.attachments && job.attachments.length > 0" class="space-y-3 mb-4">
                <div
                  v-for="attachment in job.attachments"
                  :key="attachment.id"
                  class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200"
                >
                  <div class="flex items-center gap-3 flex-1 min-w-0">
                    <DocumentIcon class="h-6 w-6 text-gray-600 flex-shrink-0" aria-hidden="true" />
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-medium text-gray-900 truncate">
                        {{ attachment.file_name }}
                      </p>
                      <p class="text-xs text-gray-500">
                        {{ formatDate(attachment.created_at) }}
                      </p>
                    </div>
                  </div>
                  <a
                    :href="attachment.file_path"
                    target="_blank"
                    class="flex-shrink-0 ml-3 text-blue-600 hover:text-blue-800 text-sm font-medium"
                  >
                    View
                  </a>
                </div>
              </div>
              <p v-else class="text-sm text-gray-500 mb-4">No attachments yet</p>
              
              <button
                v-if="job.status !== 'completed'"
                @click="showAttachmentModal = true"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium"
              >
                <PaperClipIcon class="h-4 w-4" aria-hidden="true" />
                Add Attachment
              </button>
            </div>
          </div>

          <!-- Costing (if completed) -->
          <div v-if="job.status === 'completed'" class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Job Costing</h2>
            </div>
            <div class="p-6 space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="text-sm font-medium text-gray-500">Actual Value</label>
                  <p class="text-gray-900 font-semibold">K{{ formatNumber(job.actual_value || 0) }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Total Cost</label>
                  <p class="text-gray-900">K{{ formatNumber(job.total_cost || 0) }}</p>
                </div>
              </div>

              <div class="grid grid-cols-3 gap-4">
                <div>
                  <label class="text-sm font-medium text-gray-500">Material</label>
                  <p class="text-gray-900">K{{ formatNumber(job.material_cost || 0) }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Labor</label>
                  <p class="text-gray-900">K{{ formatNumber(job.labor_cost || 0) }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Overhead</label>
                  <p class="text-gray-900">K{{ formatNumber(job.overhead_cost || 0) }}</p>
                </div>
              </div>

              <div class="pt-4 border-t border-gray-200">
                <label class="text-sm font-medium text-gray-500">Profit</label>
                <p :class="[
                  'text-lg font-bold',
                  (job.profit_amount || 0) >= 0 ? 'text-green-600' : 'text-red-600'
                ]">
                  K{{ formatNumber(job.profit_amount || 0) }}
                  <span class="text-sm font-normal">
                    ({{ job.profit_margin || 0 }}%)
                  </span>
                </p>
              </div>
            </div>
          </div>

          <!-- Status History -->
          <JobStatusHistory :history="job.status_history || []" />
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Actions -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Actions</h3>
            <div class="space-y-3">
              <button
                v-if="job.status === 'pending' && !job.assigned_to"
                @click="showAssignModal = true"
                class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
              >
                Assign Job
              </button>

              <button
                v-if="job.status === 'in_progress'"
                @click="showCompleteModal = true"
                class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
              >
                Complete Job
              </button>
            </div>
          </div>

          <!-- Assignment Info -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Assignment</h3>
            <div class="space-y-3">
              <div>
                <label class="text-sm font-medium text-gray-500">Assigned To</label>
                <p class="text-gray-900">{{ job.assigned_to?.user?.name || 'Not assigned' }}</p>
              </div>
              <div v-if="job.started_at">
                <label class="text-sm font-medium text-gray-500">Started</label>
                <p class="text-gray-900">{{ formatDate(job.started_at) }}</p>
              </div>
              <div v-if="job.completed_at">
                <label class="text-sm font-medium text-gray-500">Completed</label>
                <p class="text-gray-900">{{ formatDate(job.completed_at) }}</p>
              </div>
            </div>
          </div>

          <!-- Metadata -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Metadata</h3>
            <div class="space-y-3 text-sm">
              <div>
                <label class="text-gray-500">Created By</label>
                <p class="text-gray-900">{{ job.created_by?.user?.name }}</p>
              </div>
              <div>
                <label class="text-gray-500">Created</label>
                <p class="text-gray-900">{{ formatDate(job.created_at) }}</p>
              </div>
              <div v-if="job.is_locked">
                <label class="text-gray-500">Status</label>
                <p class="text-amber-600 font-medium">🔒 Locked</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Attachment Upload Modal -->
    <AttachmentUploadModal
      :show="showAttachmentModal"
      :job-id="job.id"
      @close="showAttachmentModal = false"
    />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { DocumentIcon, PaperClipIcon } from '@heroicons/vue/24/outline'
import CMSLayoutNew from '@/Layouts/CMSLayoutNew.vue'
import AttachmentUploadModal from '@/components/CMS/AttachmentUploadModal.vue'
import JobStatusHistory from '@/components/CMS/JobStatusHistory.vue'

defineOptions({
  layout: CMSLayoutNew
})

interface Props {
  job: any
}

const props = defineProps<Props>()

const showAssignModal = ref(false)
const showCompleteModal = ref(false)
const showAttachmentModal = ref(false)

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

const formatStatus = (status: string) => {
  return status.replace('_', ' ').replace(/\b\w/g, (l) => l.toUpperCase())
}

const getStatusClass = (status: string) => {
  const classes = {
    pending: 'px-3 py-1 text-sm font-medium rounded-full bg-gray-100 text-gray-800',
    in_progress: 'px-3 py-1 text-sm font-medium rounded-full bg-blue-100 text-blue-800',
    completed: 'px-3 py-1 text-sm font-medium rounded-full bg-green-100 text-green-800',
    cancelled: 'px-3 py-1 text-sm font-medium rounded-full bg-red-100 text-red-800',
  }
  return classes[status as keyof typeof classes] || classes.pending
}
</script>
