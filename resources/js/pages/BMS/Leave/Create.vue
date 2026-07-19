<template>
  <CMSLayout title="New Leave Request">
    <div class="max-w-3xl mx-auto">
      <div class="mb-6">
        <Link :href="route('cms.leave.index')" class="text-blue-600 hover:text-blue-800 text-sm">
          ← Back to Leave Requests
        </Link>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-semibold text-gray-900 mb-6">New Leave Request</h1>

        <form @submit.prevent="submit">
          <!-- Employee Selection -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Employee <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.worker_id"
              @change="loadLeaveBalance"
              class="w-full rounded-lg border-gray-300"
              :class="{ 'border-red-500': form.errors.worker_id }"
              required
            >
              <option value="">Select Employee</option>
              <option v-for="worker in workers" :key="worker.id" :value="worker.id">
                {{ worker.name }} ({{ worker.worker_number }}) - {{ worker.job_title || 'No Title' }}
              </option>
            </select>
            <p v-if="form.errors.worker_id" class="mt-1 text-sm text-red-600">
              {{ form.errors.worker_id }}
            </p>
          </div>

          <!-- Leave Type -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Leave Type <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.leave_type_id"
              @change="loadLeaveBalance"
              class="w-full rounded-lg border-gray-300"
              :class="{ 'border-red-500': form.errors.leave_type_id }"
              required
            >
              <option value="">Select Leave Type</option>
              <option v-for="type in leaveTypes" :key="type.id" :value="type.id">
                {{ type.leave_type_name }} ({{ type.default_days_per_year }} days/year)
              </option>
            </select>
            <p v-if="form.errors.leave_type_id" class="mt-1 text-sm text-red-600">
              {{ form.errors.leave_type_id }}
            </p>
          </div>

          <!-- Leave Balance Info -->
          <div v-if="leaveBalance" class="mb-6 p-4 bg-blue-50 rounded-lg">
            <h3 class="text-sm font-medium text-blue-900 mb-2">Leave Balance</h3>
            <div class="grid grid-cols-3 gap-4 text-sm">
              <div>
                <p class="text-blue-600">Total Days</p>
                <p class="font-semibold text-blue-900">{{ leaveBalance.total_days }}</p>
              </div>
              <div>
                <p class="text-blue-600">Used Days</p>
                <p class="font-semibold text-blue-900">{{ leaveBalance.used_days }}</p>
              </div>
              <div>
                <p class="text-blue-600">Available Days</p>
                <p class="font-semibold text-green-900">{{ leaveBalance.available_days }}</p>
              </div>
            </div>
          </div>

          <!-- Date Range -->
          <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Start Date <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.start_date"
                type="date"
                class="w-full rounded-lg border-gray-300"
                :class="{ 'border-red-500': form.errors.start_date }"
                required
              />
              <p v-if="form.errors.start_date" class="mt-1 text-sm text-red-600">
                {{ form.errors.start_date }}
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                End Date <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.end_date"
                type="date"
                class="w-full rounded-lg border-gray-300"
                :class="{ 'border-red-500': form.errors.end_date }"
                :min="form.start_date"
                required
              />
              <p v-if="form.errors.end_date" class="mt-1 text-sm text-red-600">
                {{ form.errors.end_date }}
              </p>
            </div>
          </div>

          <!-- Reason -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Reason
            </label>
            <textarea
              v-model="form.reason"
              rows="3"
              class="w-full rounded-lg border-gray-300"
              :class="{ 'border-red-500': form.errors.reason }"
              placeholder="Provide a reason for this leave request..."
            ></textarea>
            <p v-if="form.errors.reason" class="mt-1 text-sm text-red-600">
              {{ form.errors.reason }}
            </p>
          </div>

          <!-- Contact During Leave -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Contact During Leave
            </label>
            <input
              v-model="form.contact_during_leave"
              type="text"
              class="w-full rounded-lg border-gray-300"
              placeholder="Phone number or email"
            />
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end gap-3">
            <Link
              :href="route('cms.leave.index')"
              class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200"
            >
              Cancel
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
            >
              {{ form.processing ? 'Creating...' : 'Create Leave Request' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </CMSLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';

interface Props {
  workers: any[];
  leaveTypes: any[];
}

const props = defineProps<Props>();

const form = useForm({
  worker_id: '',
  leave_type_id: '',
  start_date: '',
  end_date: '',
  reason: '',
  contact_during_leave: '',
});

const leaveBalance = ref<any>(null);

const loadLeaveBalance = async () => {
  if (form.worker_id && form.leave_type_id) {
    // In a real implementation, fetch leave balance from API
    // For now, just clear it
    leaveBalance.value = null;
  }
};

const submit = () => {
  form.post(route('cms.leave.store'));
};
</script>
