<script setup lang="ts">
import { useForm, Head, Link } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

const form = useForm({
  project_id: null,
  boq_id: null,
  period_from: '',
  period_to: '',
  work_completed: [] as Array<{
    boq_item_id: number;
    quantity_completed: number;
  }>,
  retention_percentage: 10,
  notes: '',
});

const submit = () => {
  form.post(route('cms.progress-billing.certificates.store'));
};
</script>

<template>
  <Head title="New Progress Certificate" />
  
  <CMSLayout>
    <div class="max-w-4xl mx-auto space-y-6">
      <div class="flex items-center gap-4">
        <Link :href="route('cms.progress-billing.certificates.index')" class="p-2 hover:bg-gray-100 rounded-lg">
          <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
        </Link>
        <h1 class="text-2xl font-bold text-gray-900">New Progress Certificate</h1>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <div class="bg-white rounded-lg shadow p-6 space-y-4">
          <h2 class="text-lg font-semibold text-gray-900">Certificate Details</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Project <span class="text-red-500">*</span>
              </label>
              <input v-model="form.project_id" type="number" required class="w-full rounded-lg border-gray-300" placeholder="Project ID" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">BOQ</label>
              <input v-model="form.boq_id" type="number" class="w-full rounded-lg border-gray-300" placeholder="BOQ ID (optional)" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Period From <span class="text-red-500">*</span>
              </label>
              <input v-model="form.period_from" type="date" required class="w-full rounded-lg border-gray-300" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Period To <span class="text-red-500">*</span>
              </label>
              <input v-model="form.period_to" type="date" required class="w-full rounded-lg border-gray-300" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Retention Percentage</label>
              <input v-model="form.retention_percentage" type="number" step="0.01" class="w-full rounded-lg border-gray-300" placeholder="10" />
              <p class="mt-1 text-xs text-gray-500">Typically 5-10%</p>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
              <textarea v-model="form.notes" rows="3" class="w-full rounded-lg border-gray-300"></textarea>
            </div>
          </div>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <p class="text-sm text-blue-800">
            <strong>Note:</strong> After creating the certificate, you'll be able to add work completed items and calculate the payment amount.
          </p>
        </div>

        <div class="flex items-center justify-end gap-3">
          <Link :href="route('cms.progress-billing.certificates.index')" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
            Cancel
          </Link>
          <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
            {{ form.processing ? 'Creating...' : 'Create Certificate' }}
          </button>
        </div>
      </form>
    </div>
  </CMSLayout>
</template>
