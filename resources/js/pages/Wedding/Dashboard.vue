<template>
  <div class="min-h-screen bg-gradient-to-br from-pink-50 to-purple-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Wedding Dashboard</h1>
            <p class="text-gray-600 mt-1">Plan your perfect wedding day</p>
          </div>
          <div class="flex space-x-4">
            <Link
              :href="route('wedding.create')"
              v-if="!hasActiveEvent"
              class="bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700 transition-colors"
            >
              <PlusIcon class="h-5 w-5 inline mr-2" aria-hidden="true" />
              Create Wedding Event
            </Link>
            <Link
              :href="route('wedding.vendors')"
              class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition-colors"
            >
              <BuildingStorefrontIcon class="h-5 w-5 inline mr-2" aria-hidden="true" />
              Browse Vendors
            </Link>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Active Wedding Event -->
      <div v-if="activeEvent" class="mb-8">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
          <div class="bg-gradient-to-r from-pink-500 to-purple-600 px-6 py-4">
            <h2 class="text-xl font-semibold text-white">Your Wedding</h2>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div>
                <h3 class="font-semibold text-gray-900 mb-2">Wedding Details</h3>
                <p class="text-gray-600">Partner: {{ activeEvent.partnerName }}</p>
                <p class="text-gray-600">Date: {{ formatDate(activeEvent.weddingDate) }}</p>
                <p class="text-gray-600">Guests: {{ activeEvent.guestCount }}</p>
              </div>
              <div>
                <h3 class="font-semibold text-gray-900 mb-2">Budget</h3>
                <p class="text-2xl font-bold text-green-600">{{ formatCurrency(activeEvent.budget.amount) }}</p>
                <p class="text-gray-600">Total Budget</p>
              </div>
              <div>
                <h3 class="font-semibold text-gray-900 mb-2">Status</h3>
                <span :class="getStatusClass(activeEvent.status.value)" class="px-3 py-1 rounded-full text-sm font-medium">
                  {{ activeEvent.status.label }}
                </span>
                <p class="text-gray-600 mt-2">{{ getDaysUntilWedding(activeEvent.weddingDate) }}</p>
              </div>
            </div>
            <div class="mt-6 flex space-x-4">
              <Link
                :href="route('wedding.show', activeEvent.id)"
                class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700 transition-colors"
              >
                View Details
              </Link>
              <Link
                :href="route('wedding.planning')"
                class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors"
              >
                Planning Tools
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <Link
          :href="route('wedding.planning')"
          class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow"
        >
          <div class="flex items-center">
            <ClipboardDocumentListIcon class="h-8 w-8 text-pink-600" aria-hidden="true" />
            <div class="ml-4">
              <h3 class="font-semibold text-gray-900">Planning Tools</h3>
              <p class="text-gray-600 text-sm">Timeline & checklists</p>
            </div>
          </div>
        </Link>

        <Link
          :href="route('wedding.vendors')"
          class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow"
        >
          <div class="flex items-center">
            <BuildingStorefrontIcon class="h-8 w-8 text-purple-600" aria-hidden="true" />
            <div class="ml-4">
              <h3 class="font-semibold text-gray-900">Find Vendors</h3>
              <p class="text-gray-600 text-sm">Browse & book services</p>
            </div>
          </div>
        </Link>

        <Link
          :href="route('wedding.budget-calculator')"
          class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow"
        >
          <div class="flex items-center">
            <CalculatorIcon class="h-8 w-8 text-green-600" aria-hidden="true" />
            <div class="ml-4">
              <h3 class="font-semibold text-gray-900">Budget Calculator</h3>
              <p class="text-gray-600 text-sm">Plan your expenses</p>
            </div>
          </div>
        </Link>

        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center">
            <HeartIcon class="h-8 w-8 text-red-500" aria-hidden="true" />
            <div class="ml-4">
              <h3 class="font-semibold text-gray-900">Guest List</h3>
              <p class="text-gray-600 text-sm">Manage invitations</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Events -->
      <div v-if="userEvents.length > 0" class="bg-white rounded-xl shadow-lg">
        <div class="px-6 py-4 border-b">
          <h2 class="text-xl font-semibold text-gray-900">Your Wedding Events</h2>
        </div>
        <div class="p-6">
          <div class="space-y-4">
            <div
              v-for="event in userEvents"
              :key="event.id"
              class="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50"
            >
              <div>
                <h3 class="font-semibold text-gray-900">{{ event.partnerName }}</h3>
                <p class="text-gray-600">{{ formatDate(event.weddingDate) }}</p>
                <span :class="getStatusClass(event.status.value)" class="px-2 py-1 rounded-full text-xs font-medium">
                  {{ event.status.label }}
                </span>
              </div>
              <div class="text-right">
                <p class="font-semibold text-gray-900">{{ formatCurrency(event.budget.amount) }}</p>
                <p class="text-gray-600 text-sm">{{ event.guestCount }} guests</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Welcome Message for New Users -->
      <div v-else class="text-center py-12">
        <HeartIcon class="h-16 w-16 text-pink-400 mx-auto mb-4" aria-hidden="true" />
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Welcome to Your Wedding Journey!</h2>
        <p class="text-gray-600 mb-6">Start planning your perfect wedding day with our comprehensive tools and vendor marketplace.</p>
        <Link
          :href="route('wedding.create')"
          class="bg-pink-600 text-white px-8 py-3 rounded-lg hover:bg-pink-700 transition-colors inline-flex items-center"
        >
          <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
          Create Your First Wedding Event
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import {
  PlusIcon,
  BuildingStorefrontIcon,
  ClipboardDocumentListIcon,
  CalculatorIcon,
  HeartIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  userEvents: Array,
  activeEvent: Object,
  hasActiveEvent: Boolean
})

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatCurrency = (amount) => {
  return `K${parseFloat(amount).toLocaleString()}`
}

const getStatusClass = (status) => {
  const classes = {
    planning: 'bg-blue-100 text-blue-800',
    confirmed: 'bg-green-100 text-green-800',
    completed: 'bg-gray-100 text-gray-800',
    cancelled: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getDaysUntilWedding = (weddingDate) => {
  const today = new Date()
  const wedding = new Date(weddingDate)
  const diffTime = wedding - today
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  
  if (diffDays > 0) {
    return `${diffDays} days to go`
  } else if (diffDays === 0) {
    return 'Today!'
  } else {
    return 'Wedding completed'
  }
}
</script>