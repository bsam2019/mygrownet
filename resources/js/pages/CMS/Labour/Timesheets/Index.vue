<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { PlusIcon, CheckIcon, XMarkIcon } from '@heroicons/vue/24/outline';

interface Timesheet {
  id: number;
  crew: { name: string };
  work_date: string;
  hours_worked: number;
  status: string;
  project?: { name: string };
}

const props = defineProps<{
  timesheets: {
    data: Timesheet[];
  };
}>();

const statusColors = {
  draft: 'bg-gray-100 text-gray-800',
  submitted: 'bg-blue-100 text-blue-800',
  approved: 'bg-green-100 text-green-800',
  rejected: 'bg-red-100 text-red-800',
};

const approve = (id: number) => {
  router.post(route('cms.labour.timesheets.approve', id));
};

const reject = (id: number) => {
  const notes = prompt('Rejection reason:');
  if (notes) {
    router.post(route('cms.labour.timesheets.reject', id), { notes });
  }
};
</script>

<template>
  <Head title="Timesheets" />
  
  <CMSLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Timesheets</h1>
          <p class="mt-1 text-sm text-gray-500">Track labour hours and approve timesheets</p>
        </div>
        <Link
          :href="route('cms.labour.timesheets.create')"
          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          <PlusIcon class="h-5 w-5" aria-hidden="true" />
          New Timesheet
        </Link>
      </div>

      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Crew</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hours</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="timesheet in timesheets.data" :key="timesheet.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ timesheet.crew.name }}</td>
              <td class="px-6 py-4 text-sm text-gray-900">{{ timesheet.work_date }}</td>
              <td class="px-6 py-4 text-sm text-gray-900">{{ timesheet.hours_worked }}h</td>
              <td class="px-6 py-4 text-sm text-gray-500">{{ timesheet.project?.name || 'N/A' }}</td>
              <td class="px-6 py-4">
                <span :class="statusColors[timesheet.status]" class="px-2 py-1 text-xs font-medium rounded-full">
                  {{ timesheet.status.toUpperCase() }}
                </span>
              </td>
              <td class="px-6 py-4 text-right text-sm space-x-2">
                <button
                  v-if="timesheet.status === 'submitted'"
                  @click="approve(timesheet.id)"
                  class="text-green-600 hover:text-green-800"
                >
                  <CheckIcon class="h-5 w-5 inline" aria-hidden="true" />
                </button>
                <button
                  v-if="timesheet.status === 'submitted'"
                  @click="reject(timesheet.id)"
                  class="text-red-600 hover:text-red-800"
                >
                  <XMarkIcon class="h-5 w-5 inline" aria-hidden="true" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </CMSLayout>
</template>
