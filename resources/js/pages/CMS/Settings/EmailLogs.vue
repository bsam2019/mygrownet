<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import CMSLayout from '@/Layouts/CMSLayout.vue'
import FormInput from '@/components/CMS/FormInput.vue'
import FormSelect from '@/components/CMS/FormSelect.vue'
import { 
  EnvelopeIcon, 
  CheckCircleIcon, 
  XCircleIcon, 
  ClockIcon,
  MagnifyingGlassIcon,
  FunnelIcon
} from '@heroicons/vue/24/outline'

defineOptions({
  layout: CMSLayout
})

interface EmailLog {
  id: number
  email_type: string
  recipient_email: string
  recipient_name: string | null
  subject: string
  reference_type: string | null
  reference_id: number | null
  status: 'queued' | 'sent' | 'failed' | 'bounced'
  provider: 'platform' | 'custom'
  sent_at: string | null
  error_message: string | null
  created_at: string
}

interface Props {
  logs: {
    data: EmailLog[]
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
  filters: {
    type: string | null
    status: string | null
    search: string | null
  }
}

const props = defineProps<Props>()

const search = ref(props.filters.search || '')
const selectedType = ref(props.filters.type || 'all')
const selectedStatus = ref(props.filters.status || 'all')

const applyFilters = () => {
  router.get(route('cms.settings.email.logs'), {
    type: selectedType.value !== 'all' ? selectedType.value : undefined,
    status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
    search: search.value || undefined,
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}

const getStatusColor = (status: string) => {
  switch (status) {
    case 'sent': return 'text-green-600 bg-green-50'
    case 'failed': return 'text-red-600 bg-red-50'
    case 'bounced': return 'text-orange-600 bg-orange-50'
    case 'queued': return 'text-blue-600 bg-blue-50'
    default: return 'text-gray-600 bg-gray-50'
  }
}

const getStatusIcon = (status: string) => {
  switch (status) {
    case 'sent': return CheckCircleIcon
    case 'failed': return XCircleIcon
    case 'bounced': return XCircleIcon
    case 'queued': return ClockIcon
    default: return EnvelopeIcon
  }
}

const getTypeLabel = (type: string) => {
  const labels: Record<string, string> = {
    invoice: 'Invoice',
    payment: 'Payment',
    reminder: 'Reminder',
    overdue: 'Overdue',
    receipt: 'Receipt',
    quotation: 'Quotation',
    other: 'Other',
  }
  return labels[type] || type
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
          <EnvelopeIcon class="h-7 w-7 text-blue-600" aria-hidden="true" />
          Email Logs
        </h1>
        <p class="mt-1 text-sm text-gray-600">
          View all emails sent from your company
        </p>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg border border-gray-200 p-4 mb-6">
        <div class="flex items-center gap-2 mb-3">
          <FunnelIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
          <h2 class="text-sm font-semibold text-gray-900">Filters</h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- Search -->
          <div class="relative">
            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
            <input
              v-model="search"
              type="text"
              placeholder="Search emails..."
              @keyup.enter="applyFilters"
              class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>

          <!-- Type Filter -->
          <FormSelect
            v-model="selectedType"
            label=""
            :options="[
              { value: 'all', label: 'All Types' },
              { value: 'invoice', label: 'Invoice' },
              { value: 'payment', label: 'Payment' },
              { value: 'reminder', label: 'Reminder' },
              { value: 'overdue', label: 'Overdue' },
              { value: 'receipt', label: 'Receipt' },
              { value: 'quotation', label: 'Quotation' },
            ]"
          />

          <!-- Status Filter -->
          <FormSelect
            v-model="selectedStatus"
            label=""
            :options="[
              { value: 'all', label: 'All Statuses' },
              { value: 'sent', label: 'Sent' },
              { value: 'failed', label: 'Failed' },
              { value: 'queued', label: 'Queued' },
              { value: 'bounced', label: 'Bounced' },
            ]"
          />

          <!-- Apply Button -->
          <button
            @click="applyFilters"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition"
          >
            Apply Filters
          </button>
        </div>
      </div>

      <!-- Email Logs Table -->
      <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Type
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Recipient
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Subject
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Date
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Provider
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="log in logs.data" :key="log.id" class="hover:bg-gray-50">
                <!-- Status -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center gap-2">
                    <component 
                      :is="getStatusIcon(log.status)" 
                      class="h-5 w-5"
                      :class="getStatusColor(log.status).split(' ')[0]"
                      aria-hidden="true"
                    />
                    <span 
                      class="px-2 py-1 text-xs font-medium rounded-full"
                      :class="getStatusColor(log.status)"
                    >
                      {{ log.status.toUpperCase() }}
                    </span>
                  </div>
                </td>

                <!-- Type -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm text-gray-900">{{ getTypeLabel(log.email_type) }}</span>
                </td>

                <!-- Recipient -->
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900">{{ log.recipient_name || 'N/A' }}</div>
                  <div class="text-sm text-gray-500">{{ log.recipient_email }}</div>
                </td>

                <!-- Subject -->
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900 max-w-md truncate">{{ log.subject }}</div>
                  <div v-if="log.error_message" class="text-xs text-red-600 mt-1 max-w-md truncate">
                    Error: {{ log.error_message }}
                  </div>
                </td>

                <!-- Date -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ formatDate(log.sent_at || log.created_at) }}</div>
                </td>

                <!-- Provider -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm text-gray-600 capitalize">{{ log.provider }}</span>
                </td>
              </tr>

              <tr v-if="logs.data.length === 0">
                <td colspan="6" class="px-6 py-12 text-center">
                  <EnvelopeIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                  <p class="mt-2 text-sm text-gray-500">No email logs found</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="logs.last_page > 1" class="bg-gray-50 px-6 py-4 border-t border-gray-200">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Showing {{ (logs.current_page - 1) * logs.per_page + 1 }} to 
              {{ Math.min(logs.current_page * logs.per_page, logs.total) }} of 
              {{ logs.total }} results
            </div>
            <div class="flex gap-2">
              <button
                v-for="page in logs.last_page"
                :key="page"
                @click="router.get(route('cms.settings.email.logs', { ...filters, page }))"
                :class="[
                  'px-3 py-1 text-sm rounded',
                  page === logs.current_page
                    ? 'bg-blue-600 text-white'
                    : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300'
                ]"
              >
                {{ page }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
