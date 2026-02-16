<template>
  <CMSLayout title="Attendance Summary">
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900">Attendance Summary</h1>
          <p class="mt-1 text-sm text-gray-500">View attendance statistics and trends</p>
        </div>
        <Link
          :href="route('cms.attendance.index')"
          class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
        >
          Back to Records
        </Link>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Worker</label>
            <select v-model="filters.worker_id" @change="loadSummary" class="w-full rounded-lg border-gray-300">
              <option value="">Select worker</option>
              <option v-for="worker in workers" :key="worker.id" :value="worker.id">
                {{ worker.name }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
            <input v-model="filters.start_date" type="date" @change="loadSummary" class="w-full rounded-lg border-gray-300" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
            <input v-model="filters.end_date" type="date" @change="loadSummary" class="w-full rounded-lg border-gray-300" />
          </div>
        </div>
      </div>

      <!-- Summary Cards -->
      <div v-if="summary" class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Present Days</p>
              <p class="text-3xl font-bold text-green-600 mt-2">{{ summary.attendance.present_days }}</p>
            </div>
            <div class="p-3 bg-green-100 rounded-lg">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Absent Days</p>
              <p class="text-3xl font-bold text-red-600 mt-2">{{ summary.attendance.absent_days }}</p>
            </div>
            <div class="p-3 bg-red-100 rounded-lg">
              <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Late Days</p>
              <p class="text-3xl font-bold text-orange-600 mt-2">{{ summary.attendance.late_days }}</p>
            </div>
            <div class="p-3 bg-orange-100 rounded-lg">
              <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Attendance Rate</p>
              <p class="text-3xl font-bold text-blue-600 mt-2">{{ summary.attendance.attendance_rate }}%</p>
            </div>
            <div class="p-3 bg-blue-100 rounded-lg">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Hours Breakdown -->
      <div v-if="summary" class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Hours Breakdown</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="text-center p-4 bg-gray-50 rounded-lg">
            <p class="text-3xl font-bold text-gray-900">{{ summary.hours.total_hours }}</p>
            <p class="text-sm text-gray-500 mt-1">Total Hours</p>
          </div>
          <div class="text-center p-4 bg-blue-50 rounded-lg">
            <p class="text-3xl font-bold text-blue-600">{{ summary.hours.regular_hours }}</p>
            <p class="text-sm text-gray-500 mt-1">Regular Hours</p>
          </div>
          <div class="text-center p-4 bg-orange-50 rounded-lg">
            <p class="text-3xl font-bold text-orange-600">{{ summary.hours.overtime_hours }}</p>
            <p class="text-sm text-gray-500 mt-1">Overtime Hours</p>
          </div>
        </div>
      </div>
    </div>
  </CMSLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';

interface Props {
  summary?: any;
  workers: any[];
  filters: {
    worker_id?: number;
    start_date?: string;
    end_date?: string;
  };
}

const props = defineProps<Props>();

const filters = ref({
  worker_id: props.filters.worker_id || '',
  start_date: props.filters.start_date || '',
  end_date: props.filters.end_date || '',
});

const loadSummary = () => {
  router.get(route('cms.attendance.summary'), filters.value, {
    preserveState: true,
  });
};
</script>
