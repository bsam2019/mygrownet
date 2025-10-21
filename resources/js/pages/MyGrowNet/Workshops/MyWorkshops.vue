<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import MemberLayout from '@/layouts/MemberLayout.vue'
import { Calendar, Award, CheckCircle, Clock, XCircle } from 'lucide-vue-next'

interface Registration {
  id: number
  workshop: {
    id: number
    title: string
    slug: string
    start_date: string
    category: string
    delivery_format: string
  }
  status: string
  registered_at: string
  completed_at: string | null
  certificate_issued: boolean
  points_awarded: boolean
}

defineProps<{
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

const getStatusIcon = (status: string) => {
  switch (status) {
    case 'completed':
      return CheckCircle
    case 'pending_payment':
      return Clock
    case 'cancelled':
      return XCircle
    default:
      return Calendar
  }
}

const getCategoryLabel = (category: string) => {
  const labels = {
    financial_literacy: 'Financial Literacy',
    business_skills: 'Business Skills',
    leadership: 'Leadership',
    marketing: 'Marketing',
    technology: 'Technology',
    personal_development: 'Personal Development',
  }
  return labels[category as keyof typeof labels] || category
}
</script>

<template>
  <Head title="My Workshops" />
  <MemberLayout>
    <div class="p-6">
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">My Workshops</h1>
          <p class="text-gray-600 mt-1">Track your workshop registrations and progress</p>
        </div>
        <Link
          :href="route('mygrownet.workshops.index')"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          Browse Workshops
        </Link>
      </div>

      <div v-if="registrations.length === 0" class="bg-white rounded-lg shadow p-8 text-center">
        <Award class="w-16 h-16 text-gray-400 mx-auto mb-4" />
        <p class="text-gray-500 mb-4">You haven't registered for any workshops yet.</p>
        <Link
          :href="route('mygrownet.workshops.index')"
          class="inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          Explore Workshops
        </Link>
      </div>

      <div v-else class="space-y-4">
        <div
          v-for="registration in registrations"
          :key="registration.id"
          class="bg-white rounded-lg shadow hover:shadow-md transition-shadow p-6"
        >
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <div class="flex items-center gap-3 mb-2">
                <component
                  :is="getStatusIcon(registration.status)"
                  class="w-5 h-5"
                  :class="registration.status === 'completed' ? 'text-green-600' : 'text-gray-400'"
                />
                <Link
                  :href="route('mygrownet.workshops.show', registration.workshop.slug)"
                  class="text-lg font-semibold text-gray-900 hover:text-blue-600"
                >
                  {{ registration.workshop.title }}
                </Link>
              </div>

              <div class="flex flex-wrap gap-4 text-sm text-gray-600 mb-3">
                <div class="flex items-center">
                  <Calendar class="w-4 h-4 mr-1" />
                  {{ registration.workshop.start_date }}
                </div>
                <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs">
                  {{ getCategoryLabel(registration.workshop.category) }}
                </span>
                <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs capitalize">
                  {{ registration.workshop.delivery_format }}
                </span>
              </div>

              <div class="flex items-center gap-4">
                <span :class="['px-3 py-1 text-xs font-semibold rounded-full', getStatusColor(registration.status)]">
                  {{ registration.status.replace('_', ' ').toUpperCase() }}
                </span>
                <span class="text-sm text-gray-500">
                  Registered: {{ registration.registered_at }}
                </span>
                <span v-if="registration.completed_at" class="text-sm text-gray-500">
                  Completed: {{ registration.completed_at }}
                </span>
              </div>
            </div>

            <div class="flex flex-col items-end gap-2 ml-4">
              <div v-if="registration.certificate_issued" class="flex items-center text-green-600 text-sm">
                <CheckCircle class="w-4 h-4 mr-1" />
                Certificate Issued
              </div>
              <div v-if="registration.points_awarded" class="flex items-center text-blue-600 text-sm">
                <Award class="w-4 h-4 mr-1" />
                Points Awarded
              </div>
              <Link
                :href="route('mygrownet.workshops.show', registration.workshop.slug)"
                class="text-sm text-blue-600 hover:text-blue-700 font-medium"
              >
                View Details →
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Summary Stats -->
      <div v-if="registrations.length > 0" class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
        <div class="bg-white rounded-lg shadow p-4">
          <p class="text-sm text-gray-600">Total Registered</p>
          <p class="text-2xl font-bold text-gray-900">{{ registrations.length }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
          <p class="text-sm text-gray-600">Completed</p>
          <p class="text-2xl font-bold text-green-600">
            {{ registrations.filter(r => r.status === 'completed').length }}
          </p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
          <p class="text-sm text-gray-600">Certificates</p>
          <p class="text-2xl font-bold text-purple-600">
            {{ registrations.filter(r => r.certificate_issued).length }}
          </p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
          <p class="text-sm text-gray-600">Pending Payment</p>
          <p class="text-2xl font-bold text-yellow-600">
            {{ registrations.filter(r => r.status === 'pending_payment').length }}
          </p>
        </div>
      </div>
    </div>
  </MemberLayout>
</template>
