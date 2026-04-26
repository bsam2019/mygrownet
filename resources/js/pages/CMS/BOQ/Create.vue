<script setup lang="ts">
import { useForm, Head, Link } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { ArrowLeftIcon, PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';

const form = useForm({
  project_id: null,
  job_id: null,
  title: '',
  description: '',
  template_id: null,
  items: [] as Array<{
    item_number: string;
    description: string;
    unit: string;
    quantity: number;
    rate: number;
    category: string;
  }>,
});

const addItem = () => {
  form.items.push({
    item_number: `${form.items.length + 1}`,
    description: '',
    unit: '',
    quantity: 0,
    rate: 0,
    category: '',
  });
};

const removeItem = (index: number) => {
  form.items.splice(index, 1);
};

const calculateTotal = () => {
  return form.items.reduce((sum, item) => sum + (item.quantity * item.rate), 0);
};

const submit = () => {
  form.post(route('cms.boq.store'));
};
</script>

<template>
  <Head title="Create BOQ" />
  
  <CMSLayout>
    <div class="max-w-6xl mx-auto space-y-6">
      <div class="flex items-center gap-4">
        <Link :href="route('cms.boq.index')" class="p-2 hover:bg-gray-100 rounded-lg">
          <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
        </Link>
        <h1 class="text-2xl font-bold text-gray-900">Create Bill of Quantities</h1>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <div class="bg-white rounded-lg shadow p-6 space-y-4">
          <h2 class="text-lg font-semibold text-gray-900">BOQ Information</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Title <span class="text-red-500">*</span>
              </label>
              <input v-model="form.title" type="text" required class="w-full rounded-lg border-gray-300" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Project</label>
              <input v-model="form.project_id" type="number" class="w-full rounded-lg border-gray-300" placeholder="Project ID (optional)" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Job</label>
              <input v-model="form.job_id" type="number" class="w-full rounded-lg border-gray-300" placeholder="Job ID (optional)" />
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea v-model="form.description" rows="2" class="w-full rounded-lg border-gray-300"></textarea>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 space-y-4">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">BOQ Items</h2>
            <button type="button" @click="addItem" class="inline-flex items-center gap-2 px-3 py-1.5 text-sm bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100">
              <PlusIcon class="h-4 w-4" aria-hidden="true" />
              Add Item
            </button>
          </div>

          <div v-if="form.items.length === 0" class="text-center py-8 text-gray-500">
            No items added yet. Click "Add Item" to start.
          </div>

          <div v-else class="space-y-3">
            <div v-for="(item, index) in form.items" :key="index" class="p-4 bg-gray-50 rounded-lg">
              <div class="flex items-start gap-3">
                <div class="flex-1 grid grid-cols-1 md:grid-cols-6 gap-3">
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Item #</label>
                    <input v-model="item.item_number" type="text" class="w-full rounded-lg border-gray-300 text-sm" required />
                  </div>
                  <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
                    <input v-model="item.description" type="text" class="w-full rounded-lg border-gray-300 text-sm" required />
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Unit</label>
                    <input v-model="item.unit" type="text" class="w-full rounded-lg border-gray-300 text-sm" placeholder="m², kg" required />
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Quantity</label>
                    <input v-model="item.quantity" type="number" step="0.01" class="w-full rounded-lg border-gray-300 text-sm" required />
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Rate</label>
                    <input v-model="item.rate" type="number" step="0.01" class="w-full rounded-lg border-gray-300 text-sm" required />
                  </div>
                </div>
                <button type="button" @click="removeItem(index)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg mt-6">
                  <TrashIcon class="h-5 w-5" aria-hidden="true" />
                </button>
              </div>
              <div class="mt-2 text-right text-sm text-gray-600">
                Amount: K{{ (item.quantity * item.rate).toLocaleString() }}
              </div>
            </div>

            <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg">
              <span class="font-semibold text-gray-900">Total Amount</span>
              <span class="text-xl font-bold text-blue-600">K{{ calculateTotal().toLocaleString() }}</span>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-end gap-3">
          <Link :href="route('cms.boq.index')" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
            Cancel
          </Link>
          <button type="submit" :disabled="form.processing || form.items.length === 0" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
            {{ form.processing ? 'Creating...' : 'Create BOQ' }}
          </button>
        </div>
      </form>
    </div>
  </CMSLayout>
</template>
