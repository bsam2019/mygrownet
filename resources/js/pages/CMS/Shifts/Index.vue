<template>
  <CMSLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900">Shift Management</h1>
          <p class="mt-1 text-sm text-gray-500">Manage work shifts and assignments</p>
        </div>
        <Link
          :href="route('cms.shifts.create')"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Create Shift
        </Link>
      </div>

      <!-- Shifts List -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="shift in shifts"
          :key="shift.id"
          class="bg-white rounded-lg shadow p-6 hover:shadow-md transition-shadow"
        >
          <div class="flex items-start justify-between mb-4">
            <div>
              <h3 class="text-lg font-semibold text-gray-900">{{ shift.shift_name }}</h3>
              <p class="text-sm text-gray-500 mt-1">{{ shift.shift_code }}</p>
            </div>
            <span
              :class="{
                'bg-green-100 text-green-800': shift.is_active,
                'bg-gray-100 text-gray-800': !shift.is_active,
              }"
              class="px-2 py-1 text-xs font-medium rounded-full"
            >
              {{ shift.is_active ? 'Active' : 'Inactive' }}
            </span>
          </div>

          <div class="space-y-2 text-sm">
            <div class="flex items-center text-gray-600">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              {{ shift.start_time }} - {{ shift.end_time }}
            </div>
            <div class="flex items-center text-gray-600">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              {{ shift.workers_count || 0 }} workers assigned
            </div>
          </div>

          <div class="mt-4 pt-4 border-t flex gap-2">
            <Link
              :href="route('cms.shifts.edit', shift.id)"
              class="flex-1 px-3 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded-lg text-center"
            >
              Edit
            </Link>
            <button
              @click="showAssignModal(shift)"
              class="flex-1 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg"
            >
              Assign Workers
            </button>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="shifts.length === 0" class="bg-white rounded-lg shadow text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No shifts</h3>
        <p class="mt-1 text-sm text-gray-500">Get started by creating a new shift.</p>
      </div>
    </div>
  </CMSLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';

interface Props {
  shifts: any[];
}

defineProps<Props>();

const showAssignModal = (shift: any) => {
  // TODO: Implement assign modal
  alert('Assign workers modal - to be implemented');
};
</script>
