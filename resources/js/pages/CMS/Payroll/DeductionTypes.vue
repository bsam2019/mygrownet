<template>
  <CMSLayout title="Deduction Types">
    <div class="space-y-6">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900">Deduction Types</h1>
          <p class="mt-1 text-sm text-gray-500">Manage deduction types for payroll</p>
        </div>
        <button
          @click="showCreateModal = true"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          Add Deduction Type
        </button>
      </div>

      <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Default</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statutory</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="deduction in deductionTypes" :key="deduction.id">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ deduction.deduction_name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ deduction.deduction_code }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ deduction.calculation_type }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <span v-if="deduction.default_amount">K{{ deduction.default_amount.toFixed(2) }}</span>
                <span v-else-if="deduction.default_percentage">{{ deduction.default_percentage }}%</span>
                <span v-else class="text-gray-400">-</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <span v-if="deduction.is_statutory" class="text-red-600">Yes</span>
                <span v-else class="text-gray-400">No</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="[
                    'px-2 py-1 text-xs rounded-full',
                    deduction.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800',
                  ]"
                >
                  {{ deduction.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button
                  @click="editDeduction(deduction)"
                  class="text-blue-600 hover:text-blue-900"
                  :disabled="deduction.is_statutory"
                >
                  Edit
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div
        v-if="showCreateModal || editingDeduction"
        class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50"
      >
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            {{ editingDeduction ? 'Edit' : 'Create' }} Deduction Type
          </h3>
          <form @submit.prevent="submitForm" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Name</label>
              <input
                v-model="form.deduction_name"
                type="text"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
            </div>
            <div v-if="!editingDeduction">
              <label class="block text-sm font-medium text-gray-700">Code</label>
              <input
                v-model="form.deduction_code"
                type="text"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Calculation Type</label>
              <select
                v-model="form.calculation_type"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              >
                <option value="fixed">Fixed Amount</option>
                <option value="percentage_of_gross">Percentage of Gross</option>
                <option value="percentage_of_basic">Percentage of Basic</option>
                <option value="custom">Custom</option>
              </select>
            </div>
            <div v-if="form.calculation_type === 'fixed'">
              <label class="block text-sm font-medium text-gray-700">Default Amount</label>
              <input
                v-model.number="form.default_amount"
                type="number"
                step="0.01"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
            </div>
            <div v-if="form.calculation_type.includes('percentage')">
              <label class="block text-sm font-medium text-gray-700">Default Percentage</label>
              <input
                v-model.number="form.default_percentage"
                type="number"
                step="0.01"
                max="100"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
            </div>
            <div v-if="editingDeduction" class="flex items-center">
              <input
                v-model="form.is_active"
                type="checkbox"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label class="ml-2 block text-sm text-gray-900">Active</label>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
              <button
                type="button"
                @click="closeModal"
                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700"
              >
                {{ editingDeduction ? 'Update' : 'Create' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </CMSLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';

const props = defineProps<{
  deductionTypes: any[];
}>();

const showCreateModal = ref(false);
const editingDeduction = ref<any>(null);
const form = ref({
  deduction_name: '',
  deduction_code: '',
  calculation_type: 'fixed',
  default_amount: 0,
  default_percentage: 0,
  is_statutory: false,
  is_active: true,
});

function editDeduction(deduction: any) {
  if (deduction.is_statutory) return;
  editingDeduction.value = deduction;
  form.value = { ...deduction };
}

function closeModal() {
  showCreateModal.value = false;
  editingDeduction.value = null;
  form.value = {
    deduction_name: '',
    deduction_code: '',
    calculation_type: 'fixed',
    default_amount: 0,
    default_percentage: 0,
    is_statutory: false,
    is_active: true,
  };
}

function submitForm() {
  if (editingDeduction.value) {
    router.put(
      route('cms.payroll.configuration.deduction-types.update', editingDeduction.value.id),
      form.value,
      { onSuccess: closeModal }
    );
  } else {
    router.post(route('cms.payroll.configuration.deduction-types.store'), form.value, {
      onSuccess: closeModal,
    });
  }
}
</script>
