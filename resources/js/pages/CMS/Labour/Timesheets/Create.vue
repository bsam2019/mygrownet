<script setup lang="ts">
import { useForm, Head, Link } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

const form = useForm({
  crew_id: null,
  project_id: null,
  job_id: null,
  work_date: '',
  start_time: '',
  end_time: '',
  break_minutes: 0,
  work_description: '',
  notes: '',
});

const submit = () => {
  form.post(route('cms.labour.timesheets.store'));
};
</script>

<template>
  <Head title="New Timesheet" />
  
  <CMSLayout>
    <div class="max-w-4xl mx-auto space-y-6">
      <div class="flex items-center gap-4">
        <Link :href="route('cms.labour.timesheets.index')" class="p-2 hover:bg-gray-100 rounded-lg">
          <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
        </Link>
        <h1 class="text-2xl font-bold text-gray-900">New Timesheet</h1>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <div class="bg-white rounded-lg shadow p-6 space-y-4">
          <h2 class="text-lg font-semibold text-gray-900">Timesheet Details</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Crew <span class="text-red-500">*</span>
              </label>
              <input v-model="form.crew_id" type="number" required class="w-full rounded-lg border-gray-300" placeholder="Crew ID" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Work Date <span class="text-red-500">*</span>
              </label>
              <input v-model="form.work_date" type="date" required class="w-full rounded-lg border-gray-300" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Start Time <span class="text-red-500">*</span>
              </label>
              <input v-model="form.start_time" type="time" required class="w-full rounded-lg border-gray-300" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                End Time <span class="text-red-500">*</span>
              </label>
              <input v-model="form.end_time" type="time" required class="w-full rounded-lg border-gray-300" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Break Minutes</label>
              <input v-model="form.break_minutes" type="number" class="w-full rounded-lg border-gray-300" placeholder="0" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Project</label>
              <input v-model="form.project_id" type="number" class="w-full rounded-lg border-gray-300" placeholder="Project ID (optional)" />
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Work Description</label>
              <textarea v-model="form.work_description" rows="3" class="w-full rounded-lg border-gray-300" placeholder="Describe the work performed..."></textarea>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
              <textarea v-model="form.notes" rows="2" class="w-full rounded-lg border-gray-300"></textarea>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-end gap-3">
          <Link :href="route('cms.labour.timesheets.index')" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
            Cancel
          </Link>
          <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
            {{ form.processing ? 'Creating...' : 'Create Timesheet' }}
          </button>
        </div>
      </form>
    </div>
  </CMSLayout>
</template>
