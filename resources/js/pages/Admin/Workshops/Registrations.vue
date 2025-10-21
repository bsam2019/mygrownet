<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { ArrowLeftIcon } from 'lucide-vue-next'

interface Registration {
  id: number
  workshop_title: string
  user_name: string
  user_email: string
  status: string
  registered_at: string
  notes: string | null
}

const props = defineProps<{
  registrations: Registration[]
}>()

const getStatusColor = (status: string) => {
  const colors = {
    pending_payment: 'bg-yellow-100 text-yellow-800',
    registered: 'bg-green-100 text-green-800',
    attended: 'bg-blue-100 text-blue-800',
    completed: 'bg-purple-100 text-purple-800',
    cancelled: 'bg-red-100 text-red-800',
  }
  return colors[status as keyof typeof colors] || 'bg-gray-100 text-gray-800'
}

const getStatusLabel = (status: string) => {
  const labels = {
    pending_payment: 'Pending Payment',
    registered: 'Registered',
    attended: 'Attended',
    completed: 'Completed',
    cancelled: 'Cancelled',
  }
  return labels[status as keyof typeof labels] || status
}
</script>

<template>
  <Head title="Workshop Registrations" />
  
  <AdminLayout>
    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center space-x-4">
          <Link
            :href="route('admin.workshops.index')"
            class="text-gray-600 hover:text-gray-900"
          >
            <ArrowLeftIcon class="w-5 h-5" />
          </Link>
          <h1 class="text-2xl font-semibold text-gray-900">Workshop Registrations</h1>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Workshop</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Member</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Registered</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="registration in registrations" :key="registration.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 text-sm font-medium text-gray-900">
                {{ registration.workshop_title }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-600">
                {{ registration.user_name }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-600">
                {{ registration.user_email }}
              </td>
              <td class="px-6 py-4">
                <span :class="['px-2 py-1 text-xs rounded-full', getStatusColor(registration.status)]">
                  {{ getStatusLabel(registration.status) }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-600">
                {{ registration.registered_at }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-600">
                {{ registration.notes || '-' }}
              </td>
            </tr>
            <tr v-if="registrations.length === 0">
              <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                No registrations found
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AdminLayout>
</template>
