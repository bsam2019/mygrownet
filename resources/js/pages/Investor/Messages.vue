<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex justify-between items-center">
          <div>
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-blue-700 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-lg">{{ getInitials(investor.name) }}</span>
              </div>
              <div>
                <h1 class="text-xl font-bold text-gray-900">{{ investor.name }}</h1>
                <p class="text-sm text-gray-600">Investor Portal - Messages</p>
              </div>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <Link
              :href="route('investor.dashboard')"
              class="inline-flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
            >
              Dashboard
            </Link>
            <Link
              :href="route('investor.documents')"
              class="inline-flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
            >
              Documents
            </Link>
            <Link
              :href="route('investor.logout')"
              method="post"
              as="button"
              class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
            >
              Logout
            </Link>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="bg-white rounded-xl shadow-lg">
        <!-- Messages Header -->
        <div class="border-b border-gray-200 px-6 py-4">
          <h2 class="text-xl font-bold text-gray-900">Messages</h2>
          <p class="text-gray-600 text-sm">Communication with MyGrowNet team</p>
        </div>

        <!-- Messages Content -->
        <div class="p-6">
          <div class="text-center py-12">
            <ChatBubbleLeftRightIcon class="h-16 w-16 text-gray-400 mx-auto mb-4" aria-hidden="true" />
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Messages Yet</h3>
            <p class="text-gray-600 mb-6">
              You don't have any messages at the moment. Our team will reach out when there are important updates or if we need to discuss your investment.
            </p>
            
            <!-- Contact Information -->
            <div class="bg-blue-50 rounded-lg p-6 max-w-md mx-auto">
              <h4 class="font-semibold text-blue-900 mb-3">Need to Contact Us?</h4>
              <div class="space-y-2 text-sm text-blue-800">
                <p>
                  <strong>Email:</strong> investors@mygrownet.com
                </p>
                <p>
                  <strong>Phone:</strong> +260 XXX XXX XXX
                </p>
                <p class="text-xs text-blue-600 mt-3">
                  We typically respond within 24 hours during business days.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { ChatBubbleLeftRightIcon } from '@heroicons/vue/24/outline'

interface Investor {
  id: number
  name: string
  email: string
}

interface Props {
  investor: Investor
  messages: any[]
}

const props = defineProps<Props>()

const getInitials = (name: string): string => {
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .substring(0, 2)
}
</script>