<script setup lang="ts">
import { useForm, Head, Link } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

const form = useForm({
  name: '',
  company_name: '',
  trade: '',
  email: '',
  phone: '',
  address: '',
  tax_id: '',
  insurance_expiry: '',
  certifications: [] as string[],
  hourly_rate: null,
  notes: '',
});

const addCertification = () => {
  form.certifications.push('');
};

const removeCertification = (index: number) => {
  form.certifications.splice(index, 1);
};

const submit = () => {
  form.post(route('cms.subcontractors.store'));
};
</script>

<template>
  <Head title="Add Subcontractor" />
  
  <CMSLayout>
    <div class="max-w-4xl mx-auto space-y-6">
      <div class="flex items-center gap-4">
        <Link :href="route('cms.subcontractors.index')" class="p-2 hover:bg-gray-100 rounded-lg">
          <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
        </Link>
        <h1 class="text-2xl font-bold text-gray-900">Add Subcontractor</h1>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <div class="bg-white rounded-lg shadow p-6 space-y-4">
          <h2 class="text-lg font-semibold text-gray-900">Basic Information</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Name <span class="text-red-500">*</span>
              </label>
              <input v-model="form.name" type="text" required class="w-full rounded-lg border-gray-300" />
              <div v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Company Name</label>
              <input v-model="form.company_name" type="text" class="w-full rounded-lg border-gray-300" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Trade <span class="text-red-500">*</span>
              </label>
              <input v-model="form.trade" type="text" required class="w-full rounded-lg border-gray-300" placeholder="e.g., Plumbing, Electrical" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Hourly Rate</label>
              <input v-model="form.hourly_rate" type="number" step="0.01" class="w-full rounded-lg border-gray-300" placeholder="0.00" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
              <input v-model="form.email" type="email" class="w-full rounded-lg border-gray-300" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
              <input v-model="form.phone" type="text" class="w-full rounded-lg border-gray-300" />
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
              <textarea v-model="form.address" rows="2" class="w-full rounded-lg border-gray-300"></textarea>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Tax ID</label>
              <input v-model="form.tax_id" type="text" class="w-full rounded-lg border-gray-300" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Insurance Expiry</label>
              <input v-model="form.insurance_expiry" type="date" class="w-full rounded-lg border-gray-300" />
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
              <textarea v-model="form.notes" rows="3" class="w-full rounded-lg border-gray-300"></textarea>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-end gap-3">
          <Link :href="route('cms.subcontractors.index')" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
            Cancel
          </Link>
          <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
            {{ form.processing ? 'Creating...' : 'Create Subcontractor' }}
          </button>
        </div>
      </form>
    </div>
  </CMSLayout>
</template>
