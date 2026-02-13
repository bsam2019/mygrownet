<script setup lang="ts">
import { inject } from 'vue'
import { Link } from '@inertiajs/vue3'
import CMSLayoutNew from '@/Layouts/CMSLayoutNew.vue'
import {
  BriefcaseIcon,
  UsersIcon,
  DocumentTextIcon,
  CurrencyDollarIcon,
  PlusCircleIcon,
  UserPlusIcon,
  DocumentPlusIcon,
  ChartBarIcon,
} from '@heroicons/vue/24/outline'

defineOptions({
  layout: CMSLayoutNew
})

interface Props {
  stats: {
    activeJobs: number
    totalCustomers: number
    pendingInvoices: number
    monthlyRevenue: number
  }
  recentJobs: any[]
}

defineProps<Props>()

// Get slideOver from layout
const slideOver: any = inject('slideOver')

const formatNumber = (value: number) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value)
}

const formatStatus = (status: string) => {
  return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const getStatusClass = (status: string) => {
  const classes = {
    pending: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800',
    in_progress: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800',
    completed: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800',
    cancelled: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800',
  }
  return classes[status as keyof typeof classes] || classes.pending
}
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
      <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <BriefcaseIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Active Jobs</dt>
                <dd class="flex items-baseline">
                  <div class="text-2xl font-semibold text-gray-900">{{ stats.activeJobs }}</div>
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <UsersIcon class="h-6 w-6 text-green-600" aria-hidden="true" />
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Total Customers</dt>
                <dd class="flex items-baseline">
                  <div class="text-2xl font-semibold text-gray-900">{{ stats.totalCustomers }}</div>
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center">
                <DocumentTextIcon class="h-6 w-6 text-amber-600" aria-hidden="true" />
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Pending Invoices</dt>
                <dd class="flex items-baseline">
                  <div class="text-2xl font-semibold text-gray-900">{{ stats.pendingInvoices }}</div>
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center">
                <CurrencyDollarIcon class="h-6 w-6 text-emerald-600" aria-hidden="true" />
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Monthly Revenue</dt>
                <dd class="flex items-baseline">
                  <div class="text-2xl font-semibold text-gray-900">K{{ formatNumber(stats.monthlyRevenue) }}</div>
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="mb-8">
      <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <button
          @click="slideOver?.open('job')"
          class="relative group bg-white p-6 rounded-lg border-2 border-gray-200 hover:border-blue-500 transition-all shadow-sm hover:shadow-md text-left"
        >
          <div class="flex items-center gap-4">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-600 transition">
                <PlusCircleIcon class="h-6 w-6 text-blue-600 group-hover:text-white transition" aria-hidden="true" />
              </div>
            </div>
            <div class="flex-1">
              <h3 class="text-sm font-semibold text-gray-900 group-hover:text-blue-600 transition">Create Job</h3>
              <p class="text-sm text-gray-500">Start a new job</p>
            </div>
          </div>
        </button>

        <button
          @click="slideOver?.open('customer')"
          class="relative group bg-white p-6 rounded-lg border-2 border-gray-200 hover:border-green-500 transition-all shadow-sm hover:shadow-md text-left"
        >
          <div class="flex items-center gap-4">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-600 transition">
                <UserPlusIcon class="h-6 w-6 text-green-600 group-hover:text-white transition" aria-hidden="true" />
              </div>
            </div>
            <div class="flex-1">
              <h3 class="text-sm font-semibold text-gray-900 group-hover:text-green-600 transition">Add Customer</h3>
              <p class="text-sm text-gray-500">Register new customer</p>
            </div>
          </div>
        </button>

        <button
          @click="slideOver?.open('invoice')"
          class="relative group bg-white p-6 rounded-lg border-2 border-gray-200 hover:border-purple-500 transition-all shadow-sm hover:shadow-md text-left"
        >
          <div class="flex items-center gap-4">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-600 transition">
                <DocumentPlusIcon class="h-6 w-6 text-purple-600 group-hover:text-white transition" aria-hidden="true" />
              </div>
            </div>
            <div class="flex-1">
              <h3 class="text-sm font-semibold text-gray-900 group-hover:text-purple-600 transition">Create Invoice</h3>
              <p class="text-sm text-gray-500">Bill a customer</p>
            </div>
          </div>
        </button>

        <Link
          :href="route('cms.reports.index')"
          class="relative group bg-white p-6 rounded-lg border-2 border-gray-200 hover:border-indigo-500 transition-all shadow-sm hover:shadow-md text-left"
        >
          <div class="flex items-center gap-4">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center group-hover:bg-indigo-600 transition">
                <ChartBarIcon class="h-6 w-6 text-indigo-600 group-hover:text-white transition" aria-hidden="true" />
              </div>
            </div>
            <div class="flex-1">
              <h3 class="text-sm font-semibold text-gray-900 group-hover:text-indigo-600 transition">View Reports</h3>
              <p class="text-sm text-gray-500">Financial insights</p>
            </div>
          </div>
        </Link>
      </div>
    </div>

    <!-- Recent Jobs -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
      <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900">Recent Jobs</h2>
        <Link
          :href="route('cms.jobs.index')"
          class="text-sm font-medium text-blue-600 hover:text-blue-700"
        >
          View All →
        </Link>
      </div>
      
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job #</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="job in recentJobs.slice(0, 5)" :key="job.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <Link
                  :href="route('cms.jobs.show', job.id)"
                  class="text-sm font-medium text-blue-600 hover:text-blue-800"
                >
                  {{ job.job_number }}
                </Link>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ job.customer?.name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ job.job_type }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(job.status)">
                  {{ formatStatus(job.status) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                K{{ formatNumber(job.quoted_value || 0) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                <Link
                  :href="route('cms.jobs.show', job.id)"
                  class="text-blue-600 hover:text-blue-800 font-medium"
                >
                  View
                </Link>
              </td>
            </tr>
            <tr v-if="recentJobs.length === 0">
              <td colspan="6" class="px-6 py-12 text-center">
                <BriefcaseIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                <p class="mt-2 text-sm text-gray-500">No jobs yet</p>
                <button
                  @click="slideOver?.open('job')"
                  class="mt-3 inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-700"
                >
                  Create your first job →
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
