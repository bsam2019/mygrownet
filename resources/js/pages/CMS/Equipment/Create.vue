<script setup lang="ts">
import { useForm, Head, Link } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

const form = useForm({
  name: '',
  equipment_code: '',
  type: '',
  manufacturer: '',
  model: '',
  serial_number: '',
  purchase_date: '',
  purchase_cost: null,
  current_value: null,
  depreciation_rate: null,
  maintenance_interval_days: null,
  last_maintenance_date: '',
  notes: '',
});

const submit = () => {
  form.post(route('cms.equipment.store'));
};
</script>

<template>
  <Head title="Add Equipment" />
  
  <CMSLayout>
    <div class="max-w-4xl mx-auto space-y-6">
      <div class="flex items-center gap-4">
        <Link :href="route('cms.equipment.index')" class="p-2 hover:bg-gray-100 rounded-lg">
          <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
        </Link>
        <h1 class="text-2xl font-bold text-gray-900">Add Equipment</h1>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <div class="bg-white rounded-lg shadow p-6 space-y-4">
          <h2 class="text-lg font-semibold text-gray-900">Basic Information</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Equipment Name <span class="text-red-500">*</span>
              </label>
              <input v-model="form.name" type="text" required class="w-full rounded-lg border-gray-300" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Equipment Code <span class="text-red-500">*</span>
              </label>
              <input v-model="form.equipment_code" type="text" required class="w-full rounded-lg border-gray-300" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Type <span class="text-red-500">*</span>
              </label>
              <input v-model="form.type" type="text" required class="w-full rounded-lg border-gray-300" placeholder="e.g., Excavator, Crane" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Manufacturer</label>
              <input v-model="form.manufacturer" type="text" class="w-full rounded-lg border-gray-300" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Model</label>
              <input v-model="form.model" type="text" class="w-full rounded-lg border-gray-300" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Serial Number</label>
              <input v-model="form.serial_number" type="text" class="w-full rounded-lg border-gray-300" />
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 space-y-4">
          <h2 class="text-lg font-semibold text-gray-900">Financial Information</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Date</label>
              <input v-model="form.purchase_date" type="date" class="w-full rounded-lg border-gray-300" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Cost</label>
              <input v-model="form.purchase_cost" type="number" step="0.01" class="w-full rounded-lg border-gray-300" placeholder="0.00" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Current Value</label>
              <input v-model="form.current_value" type="number" step="0.01" class="w-full rounded-lg border-gray-300" placeholder="0.00" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Depreciation Rate (%)</label>
              <input v-model="form.depreciation_rate" type="number" step="0.01" class="w-full rounded-lg border-gray-300" placeholder="0.00" />
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 space-y-4">
          <h2 class="text-lg font-semibold text-gray-900">Maintenance</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Maintenance Interval (Days)</label>
              <input v-model="form.maintenance_interval_days" type="number" class="w-full rounded-lg border-gray-300" placeholder="90" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Last Maintenance Date</label>
              <input v-model="form.last_maintenance_date" type="date" class="w-full rounded-lg border-gray-300" />
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
              <textarea v-model="form.notes" rows="3" class="w-full rounded-lg border-gray-300"></textarea>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-end gap-3">
          <Link :href="route('cms.equipment.index')" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
            Cancel
          </Link>
          <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
            {{ form.processing ? 'Creating...' : 'Create Equipment' }}
          </button>
        </div>
      </form>
    </div>
  </CMSLayout>
</template>
