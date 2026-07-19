<template>
  <CMSLayout title="Job Postings">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Job Postings</h1>
          <p class="text-sm text-gray-600 mt-1">Manage job openings and recruitment</p>
        </div>
        <Link
          :href="route('cms.recruitment.job-postings.create')"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          Post New Job
        </Link>
      </div>

      <!-- Filters -->
      <div class="flex gap-4">
        <button
          v-for="status in ['all', 'draft', 'published', 'closed']"
          :key="status"
          @click="filterStatus = status"
          :class="[
            'px-4 py-2 rounded-lg text-sm font-medium',
            filterStatus === status
              ? 'bg-blue-600 text-white'
              : 'bg-white text-gray-700 hover:bg-gray-50'
          ]"
        >
          {{ status.charAt(0).toUpperCase() + status.slice(1) }}
        </button>
      </div>

      <!-- Job Postings List -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Job Title</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Applications</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deadline</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="job in filteredJobs" :key="job.id">
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900">{{ job.job_title }}</div>
                <div class="text-sm text-gray-500">{{ job.positions_available }} position(s)</div>
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ job.department?.name || 'N/A' }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                <Link
                  :href="route('cms.recruitment.applications.index', job.id)"
                  class="text-blue-600 hover:text-blue-800"
                >
                  {{ job.applications?.length || 0 }} applications
                </Link>
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ job.application_deadline || 'No deadline' }}
              </td>
              <td class="px-6 py-4">
                <span
                  :class="[
                    'px-2 py-1 text-xs font-medium rounded-full',
                    job.status === 'published' ? 'bg-green-100 text-green-800' :
                    job.status === 'closed' ? 'bg-gray-100 text-gray-800' :
                    'bg-yellow-100 text-yellow-800'
                  ]"
                >
                  {{ job.status }}
                </span>
              </td>
              <td class="px-6 py-4 text-right text-sm">
                <button class="text-blue-600 hover:text-blue-800">View</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </CMSLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';

const props = defineProps<{
  jobPostings: any[];
}>();

const filterStatus = ref('all');

const filteredJobs = computed(() => {
  if (filterStatus.value === 'all') return props.jobPostings;
  return props.jobPostings.filter(job => job.status === filterStatus.value);
});
</script>
