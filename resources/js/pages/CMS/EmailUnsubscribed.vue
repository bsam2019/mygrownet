<script setup lang="ts">
import { CheckCircleIcon } from '@heroicons/vue/24/outline'

interface Props {
  type: 'all' | 'marketing' | 'reminders'
}

defineProps<Props>()

const getTypeLabel = (type: string) => {
  switch (type) {
    case 'all': return 'all emails'
    case 'marketing': return 'marketing emails'
    case 'reminders': return 'payment reminders'
    default: return 'emails'
  }
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
      <CheckCircleIcon class="h-16 w-16 text-green-600 mx-auto mb-4" aria-hidden="true" />
      
      <h1 class="text-2xl font-bold text-gray-900 mb-2">
        Successfully Unsubscribed
      </h1>
      
      <p class="text-gray-600 mb-6">
        You have been unsubscribed from {{ getTypeLabel(type) }}.
      </p>

      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-gray-700">
        <p class="mb-2">
          <strong>Note:</strong> You will still receive important transactional emails such as:
        </p>
        <ul class="text-left list-disc list-inside space-y-1">
          <li>Invoice confirmations</li>
          <li>Payment receipts</li>
          <li>Account notifications</li>
        </ul>
      </div>

      <p class="mt-6 text-sm text-gray-500">
        If you unsubscribed by mistake, please contact the company directly to resubscribe.
      </p>
    </div>
  </div>
</template>
