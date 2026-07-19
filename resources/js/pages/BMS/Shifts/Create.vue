<template>
  <CMSLayout>
    <div class="p-6 max-w-3xl mx-auto">
      <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Create New Shift</h1>
        <p class="mt-1 text-sm text-gray-500">Define a new work shift template</p>
      </div>

      <form @submit.prevent="submit" class="bg-white rounded-lg shadow p-6 space-y-6">
        <!-- Basic Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Shift Name *</label>
            <input
              v-model="form.shift_name"
              type="text"
              required
              class="w-full rounded-lg border-gray-300"
              placeholder="e.g., Morning Shift"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Shift Code *</label>
            <input
              v-model="form.shift_code"
              type="text"
              required
              class="w-full rounded-lg border-gray-300"
              placeholder="e.g., MORNING"
            />
          </div>
        </div>

        <!-- Time Settings -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Start Time *</label>
            <input
              v-model="form.start_time"
              type="time"
              required
              class="w-full rounded-lg border-gray-300"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">End Time *</label>
            <input
              v-model="form.end_time"
              type="time"
              required
              class="w-full rounded-lg border-gray-300"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Break (minutes)</label>
            <input
              v-model.number="form.break_duration_minutes"
              type="number"
              min="0"
              class="w-full rounded-lg border-gray-300"
              placeholder="60"
            />
          </div>
        </div>

        <!-- Attendance Settings -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Grace Period (minutes)</label>
            <input
              v-model.number="form.grace_period_minutes"
              type="number"
              min="0"
              class="w-full rounded-lg border-gray-300"
              placeholder="15"
            />
            <p class="mt-1 text-xs text-gray-500">Late if clock in exceeds this</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Overtime Threshold (minutes)</label>
            <input
              v-model.number="form.overtime_threshold_minutes"
              type="number"
              min="0"
              class="w-full rounded-lg border-gray-300"
              placeholder="480"
            />
            <p class="mt-1 text-xs text-gray-500">Work beyond this counts as overtime</p>
          </div>
        </div>

        <!-- Working Hours -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Min Hours for Full Day</label>
            <input
              v-model.number="form.minimum_hours_full_day"
              type="number"
              step="0.5"
              min="0"
              class="w-full rounded-lg border-gray-300"
              placeholder="7.5"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Min Hours for Half Day</label>
            <input
              v-model.number="form.minimum_hours_half_day"
              type="number"
              step="0.5"
              min="0"
              class="w-full rounded-lg border-gray-300"
              placeholder="4"
            />
          </div>
        </div>

        <!-- Status -->
        <div class="flex items-center">
          <input
            v-model="form.is_active"
            type="checkbox"
            class="rounded border-gray-300 text-blue-600"
          />
          <label class="ml-2 text-sm text-gray-700">Active</label>
        </div>

        <!-- Actions -->
        <div class="flex gap-3 pt-4 border-t">
          <button
            type="submit"
            :disabled="form.processing"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
          >
            {{ form.processing ? 'Creating...' : 'Create Shift' }}
          </button>
          <Link
            :href="route('cms.shifts.index')"
            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
          >
            Cancel
          </Link>
        </div>
      </form>
    </div>
  </CMSLayout>
</template>

<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';

const form = useForm({
  shift_name: '',
  shift_code: '',
  start_time: '08:00',
  end_time: '17:00',
  break_duration_minutes: 60,
  grace_period_minutes: 15,
  overtime_threshold_minutes: 480,
  minimum_hours_full_day: 7.5,
  minimum_hours_half_day: 4,
  is_active: true,
});

const submit = () => {
  form.post(route('cms.shifts.store'));
};
</script>
