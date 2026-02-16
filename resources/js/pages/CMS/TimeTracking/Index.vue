<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { ClockIcon, PlayIcon, StopIcon, PlusIcon, CheckIcon, XMarkIcon } from '@heroicons/vue/24/outline';

const props = defineProps<{
  entries: any;
  workers: any[];
  jobs: any[];
  filters: any;
}>();

const selectedWorker = ref(props.filters.worker_id || '');
const selectedJob = ref(props.filters.job_id || '');
const selectedStatus = ref(props.filters.status || '');
const showRunningOnly = ref(props.filters.is_running === '1');

const runningTimer = computed(() => {
  return props.entries.data.find((e: any) => e.is_running);
});

function applyFilters() {
  router.get('/cms/time-tracking', {
    worker_id: selectedWorker.value || undefined,
    job_id: selectedJob.value || undefined,
    status: selectedStatus.value || undefined,
    is_running: showRunningOnly.value ? '1' : undefined,
  }, { preserveState: true });
}

function startTimer(workerId: number, jobId?: number) {
  router.post('/cms/time-tracking/start-timer', {
    worker_id: workerId,
    job_id: jobId,
    is_billable: true,
  });
}

function stopTimer(entryId: number) {
  router.post(`/cms/time-tracking/${entryId}/stop-timer`, {});
}

function formatDuration(minutes: number) {
  const hours = Math.floor(minutes / 60);
  const mins = minutes % 60;
  return `${hours}h ${mins}m`;
}
</script>

<template>
  <CMSLayout title="Time Tracking">
    <div class="space-y-6">
      <!-- Running Timer Alert -->
      <div v-if="runningTimer" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <ClockIcon class="h-6 w-6 text-blue-600 animate-pulse" aria-hidden="true" />
            <div>
              <p class="font-medium text-blue-900">Timer Running</p>
              <p class="text-sm text-blue-700">
                {{ runningTimer.worker.name }} - {{ runningTimer.job?.job_number || 'No job' }}
              </p>
            </div>
          </div>
          <button
            @click="stopTimer(runningTimer.id)"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 flex items-center gap-2"
          >
            <StopIcon class="h-5 w-5" aria-hidden="true" />
            Stop Timer
          </button>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <select v-model="selectedWorker" @change="applyFilters" class="rounded-lg border-gray-300">
            <option value="">All Workers</option>
            <option v-for="worker in workers" :key="worker.id" :value="worker.id">
              {{ worker.name }}
            </option>
          </select>

          <select v-model="selectedJob" @change="applyFilters" class="rounded-lg border-gray-300">
            <option value="">All Jobs</option>
            <option v-for="job in jobs" :key="job.id" :value="job.id">
              {{ job.job_number }}
            </option>
          </select>

          <select v-model="selectedStatus" @change="applyFilters" class="rounded-lg border-gray-300">
            <option value="">All Statuses</option>
            <option value="draft">Draft</option>
            <option value="submitted">Submitted</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
          </select>

          <label class="flex items-center gap-2">
            <input
              type="checkbox"
              v-model="showRunningOnly"
              @change="applyFilters"
              class="rounded border-gray-300"
            />
            <span class="text-sm">Running only</span>
          </label>
        </div>
      </div>

      <!-- Time Entries Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Worker</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Job</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Start Time</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="entry in entries.data" :key="entry.id">
              <td class="px-6 py-4 whitespace-nowrap text-sm">{{ entry.worker.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">{{ entry.job?.job_number || '-' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                {{ new Date(entry.start_time).toLocaleString() }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <span v-if="entry.is_running" class="text-blue-600 font-medium">Running...</span>
                <span v-else>{{ formatDuration(entry.duration_minutes) }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                K{{ entry.total_amount?.toFixed(2) || '0.00' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="{
                    'bg-gray-100 text-gray-800': entry.status === 'draft',
                    'bg-blue-100 text-blue-800': entry.status === 'submitted',
                    'bg-green-100 text-green-800': entry.status === 'approved',
                    'bg-red-100 text-red-800': entry.status === 'rejected',
                  }"
                  class="px-2 py-1 text-xs font-medium rounded-full"
                >
                  {{ entry.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                <button
                  v-if="entry.status === 'submitted'"
                  @click="router.post(`/cms/time-tracking/${entry.id}/approve`, {})"
                  class="text-green-600 hover:text-green-900 mr-3"
                  aria-label="Approve entry"
                >
                  <CheckIcon class="h-5 w-5" aria-hidden="true" />
                </button>
                <button
                  v-if="entry.status === 'submitted'"
                  @click="router.post(`/cms/time-tracking/${entry.id}/reject`, { rejection_reason: 'Rejected' })"
                  class="text-red-600 hover:text-red-900"
                  aria-label="Reject entry"
                >
                  <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </CMSLayout>
</template>
