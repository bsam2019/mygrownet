<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <!-- Page Header -->
    <div class="mb-6">
      <Link :href="route('cms.customers.index')" class="text-sm text-blue-600 hover:text-blue-800 mb-2 inline-block">
        ← Back to Customers
      </Link>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">{{ customer.name }}</h1>
          <p class="text-sm text-gray-500">{{ customer.customer_number }}</p>
        </div>
        <span :class="customer.status === 'active' 
          ? 'px-3 py-1 text-sm font-medium rounded-full bg-green-100 text-green-800'
          : 'px-3 py-1 text-sm font-medium rounded-full bg-gray-100 text-gray-800'
        ">
          {{ customer.status }}
        </span>
      </div>
    </div>

    <!-- Content -->
    <div>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Customer Details -->
          <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
              <h2 class="text-lg font-semibold text-gray-900">Customer Information</h2>
              <Link
                :href="route('cms.customers.edit', customer.id)"
                class="text-sm text-blue-600 hover:text-blue-800"
              >
                Edit
              </Link>
            </div>
            <div class="p-6 space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="text-sm font-medium text-gray-500">Phone</label>
                  <p class="text-gray-900">{{ customer.phone }}</p>
                </div>
                <div v-if="customer.email">
                  <label class="text-sm font-medium text-gray-500">Email</label>
                  <p class="text-gray-900">{{ customer.email }}</p>
                </div>
              </div>

              <div v-if="customer.address">
                <label class="text-sm font-medium text-gray-500">Address</label>
                <p class="text-gray-900 whitespace-pre-wrap">{{ customer.address }}</p>
              </div>

              <div v-if="customer.notes">
                <label class="text-sm font-medium text-gray-500">Internal Notes</label>
                <p class="text-gray-900 whitespace-pre-wrap">{{ customer.notes }}</p>
              </div>
            </div>
          </div>

          <!-- Recent Jobs -->
          <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Recent Jobs</h2>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Job #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Value</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="job in customer.jobs" :key="job.id">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {{ job.job_number }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ job.job_type }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="getStatusClass(job.status)">
                        {{ formatStatus(job.status) }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      K{{ formatNumber(job.quoted_value || 0) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                      <Link
                        :href="route('cms.jobs.show', job.id)"
                        class="text-blue-600 hover:text-blue-800"
                      >
                        View
                      </Link>
                    </td>
                  </tr>
                  <tr v-if="!customer.jobs || customer.jobs.length === 0">
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                      No jobs yet
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Contact Persons -->
          <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
              <h2 class="text-lg font-semibold text-gray-900">Contact Persons</h2>
              <button
                @click="showContactsModal = true"
                class="text-sm text-blue-600 hover:text-blue-800 font-medium"
              >
                Manage
              </button>
            </div>
            <div class="p-6">
              <div v-if="customer.contacts && customer.contacts.length > 0" class="space-y-3">
                <div
                  v-for="contact in customer.contacts"
                  :key="contact.id"
                  class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200"
                >
                  <UserCircleIcon class="h-6 w-6 text-gray-600 flex-shrink-0 mt-0.5" aria-hidden="true" />
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                      <p class="text-sm font-medium text-gray-900">
                        {{ contact.name }}
                      </p>
                      <span
                        v-if="contact.is_primary"
                        class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-800"
                      >
                        Primary
                      </span>
                    </div>
                    <p v-if="contact.title" class="text-xs text-gray-500">
                      {{ contact.title }}
                    </p>
                    <div class="mt-1 space-y-0.5">
                      <p v-if="contact.email" class="text-xs text-gray-600">
                        {{ contact.email }}
                      </p>
                      <p v-if="contact.phone || contact.mobile" class="text-xs text-gray-600">
                        {{ contact.phone || contact.mobile }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
              <p v-else class="text-sm text-gray-500">No contact persons yet</p>
            </div>
          </div>

          <!-- Documents -->
          <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Documents</h2>
            </div>
            <div class="p-6">
              <div v-if="customer.documents && customer.documents.length > 0" class="space-y-3 mb-4">
                <div
                  v-for="document in customer.documents"
                  :key="document.id"
                  class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200"
                >
                  <div class="flex items-center gap-3 flex-1 min-w-0">
                    <DocumentIcon class="h-6 w-6 text-gray-600 flex-shrink-0" aria-hidden="true" />
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-medium text-gray-900 truncate">
                        {{ document.file_name }}
                      </p>
                      <p class="text-xs text-gray-500">
                        {{ document.document_type }} • {{ formatDate(document.created_at) }}
                      </p>
                    </div>
                  </div>
                  <a
                    :href="document.file_path"
                    target="_blank"
                    class="flex-shrink-0 ml-3 text-blue-600 hover:text-blue-800 text-sm font-medium"
                  >
                    View
                  </a>
                </div>
              </div>
              <p v-else class="text-sm text-gray-500 mb-4">No documents yet</p>
              
              <button
                @click="showDocumentModal = true"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium"
              >
                <PaperClipIcon class="h-4 w-4" aria-hidden="true" />
                Upload Document
              </button>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Financial Summary -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Financial Summary</h3>
            <div class="space-y-3">
              <div>
                <label class="text-sm font-medium text-gray-500">Outstanding Balance</label>
                <p :class="[
                  'text-2xl font-bold',
                  customer.outstanding_balance > 0 ? 'text-red-600' : 'text-gray-900'
                ]">
                  K{{ formatNumber(customer.outstanding_balance || 0) }}
                </p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">Credit Limit</label>
                <p class="text-gray-900">K{{ formatNumber(customer.credit_limit || 0) }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-500">Available Credit</label>
                <p class="text-gray-900">
                  K{{ formatNumber((customer.credit_limit || 0) - (customer.outstanding_balance || 0)) }}
                </p>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Quick Actions</h3>
            <div class="space-y-3">
              <Link
                :href="route('cms.jobs.create', { customer_id: customer.id })"
                class="block w-full px-4 py-2 bg-blue-600 text-white text-center rounded-lg hover:bg-blue-700"
              >
                Create Job
              </Link>
            </div>
          </div>

          <!-- Metadata -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Metadata</h3>
            <div class="space-y-3 text-sm">
              <div>
                <label class="text-gray-500">Created By</label>
                <p class="text-gray-900">{{ customer.created_by?.user?.name }}</p>
              </div>
              <div>
                <label class="text-gray-500">Created</label>
                <p class="text-gray-900">{{ formatDate(customer.created_at) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Document Upload Modal -->
    <DocumentUploadModal
      :show="showDocumentModal"
      :customer-id="customer.id"
      @close="showDocumentModal = false"
    />

    <!-- Customer Contacts Modal -->
    <CustomerContactsModal
      :show="showContactsModal"
      :customer-id="customer.id"
      :contacts="customer.contacts || []"
      @close="showContactsModal = false"
    />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { DocumentIcon, PaperClipIcon, UserCircleIcon } from '@heroicons/vue/24/outline'
import CMSLayoutNew from '@/Layouts/CMSLayoutNew.vue'
import DocumentUploadModal from '@/components/CMS/DocumentUploadModal.vue'
import CustomerContactsModal from '@/components/CMS/CustomerContactsModal.vue'

defineOptions({
  layout: CMSLayoutNew
})

interface Props {
  customer: any
}

defineProps<Props>()

const showDocumentModal = ref(false)
const showContactsModal = ref(false)

const formatNumber = (value: number) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value)
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const formatStatus = (status: string) => {
  return status.replace('_', ' ').replace(/\b\w/g, (l) => l.toUpperCase())
}

const getStatusClass = (status: string) => {
  const classes = {
    pending: 'px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800',
    in_progress: 'px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800',
    completed: 'px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800',
    cancelled: 'px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800',
  }
  return classes[status as keyof typeof classes] || classes.pending
}
</script>
