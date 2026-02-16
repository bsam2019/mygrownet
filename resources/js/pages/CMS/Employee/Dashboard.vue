<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { 
  CalendarDaysIcon, 
  ClockIcon, 
  DocumentTextIcon, 
  MegaphoneIcon,
  UserCircleIcon,
  AcademicCapIcon
} from '@heroicons/vue/24/outline';

const props = defineProps<{
  worker: any;
  pending_leave_requests: number;
  recent_attendance: any[];
  announcements: any[];
}>();

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};
</script>

<template>
  <Head title="Employee Portal" />
  
  <CMSLayout>
    <div class="space-y-6">
      <!-- Welcome Header -->
      <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg p-6 text-white">
        <div class="flex items-center gap-4">
          <UserCircleIcon class="h-16 w-16" aria-hidden="true" />
          <div>
            <h1 class="text-2xl font-semibold">Welcome, {{ worker.first_name }}!</h1>
            <p class="text-blue-100 mt-1">{{ worker.job_title }} - {{ worker.department?.name }}</p>
          </div>
        </div>
      </div>

      <!-- Quick Stats -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-6 rounded-lg border border-gray-200">
          <div class="flex items-center gap-3">
            <CalendarDaysIcon class="h-8 w-8 text-green-600" aria-hidden="true" />
            <div>
              <div class="text-sm text-gray-500">Leave Balance</div>
              <div class="text-2xl font-semibold text-gray-900">
                {{ worker.leave_balances?.find((b: any) => b.leave_type?.name === 'Annual Leave')?.balance || 0 }} days
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white p-6 rounded-lg border border-gray-200">
          <div class="flex items-center gap-3">
            <ClockIcon class="h-8 w-8 text-blue-600" aria-hidden="true" />
            <div>
              <div class="text-sm text-gray-500">Pending Requests</div>
              <div class="text-2xl font-semibold text-gray-900">{{ pending_leave_requests }}</div>
            </div>
          </div>
        </div>

        <div class="bg-white p-6 rounded-lg border border-gray-200">
          <div class="flex items-center gap-3">
            <AcademicCapIcon class="h-8 w-8 text-purple-600" aria-hidden="true" />
            <div>
              <div class="text-sm text-gray-500">Skills</div>
              <div class="text-2xl font-semibold text-gray-900">{{ worker.skills?.length || 0 }}</div>
            </div>
          </div>
        </div>

        <div class="bg-white p-6 rounded-lg border border-gray-200">
          <div class="flex items-center gap-3">
            <DocumentTextIcon class="h-8 w-8 text-orange-600" aria-hidden="true" />
            <div>
              <div class="text-sm text-gray-500">Documents</div>
              <div class="text-2xl font-semibold text-gray-900">View</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Announcements -->
      <div v-if="announcements.length > 0" class="bg-white rounded-lg border border-gray-200 p-6">
        <div class="flex items-center gap-2 mb-4">
          <MegaphoneIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
          <h2 class="text-lg font-semibold text-gray-900">Announcements</h2>
        </div>
        <div class="space-y-3">
          <div
            v-for="announcement in announcements.slice(0, 3)"
            :key="announcement.id"
            class="p-4 bg-blue-50 border border-blue-200 rounded-lg"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <h3 class="font-medium text-gray-900">{{ announcement.title }}</h3>
                <p class="text-sm text-gray-600 mt-1">{{ announcement.content }}</p>
              </div>
              <span
                v-if="announcement.priority === 'urgent'"
                class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full"
              >
                Urgent
              </span>
            </div>
            <div class="text-xs text-gray-500 mt-2">{{ formatDate(announcement.publish_date) }}</div>
          </div>
        </div>
      </div>

      <!-- Recent Attendance -->
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Attendance</h2>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead>
              <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Check In</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Check Out</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="record in recent_attendance" :key="record.id">
                <td class="px-4 py-3 text-sm text-gray-900">{{ formatDate(record.date) }}</td>
                <td class="px-4 py-3 text-sm text-gray-900">{{ record.check_in_time || '-' }}</td>
                <td class="px-4 py-3 text-sm text-gray-900">{{ record.check_out_time || '-' }}</td>
                <td class="px-4 py-3">
                  <span
                    :class="{
                      'bg-green-100 text-green-800': record.status === 'present',
                      'bg-yellow-100 text-yellow-800': record.status === 'late',
                      'bg-red-100 text-red-800': record.status === 'absent',
                    }"
                    class="px-2 py-1 text-xs font-medium rounded-full"
                  >
                    {{ record.status }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </CMSLayout>
</template>
