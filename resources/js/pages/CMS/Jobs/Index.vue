<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <!-- Page Header -->
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Jobs</h1>
        <p class="mt-1 text-sm text-gray-500">Manage customer jobs</p>
      </div>
      <button
        @click="slideOver?.open('job')"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium flex items-center gap-2"
      >
        <PlusIcon class="h-5 w-5" aria-hidden="true" />
        <span>Create Job</span>
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-4">
      <div class="grid grid-cols-1 gap-3">
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Filter by Status</label>
          <select
            v-model="filters.status"
            @change="applyFilters"
            class="w-full rounded-lg border border-gray-300 bg-gray-50 text-gray-900 px-4 py-2.5 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm"
          >
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Mobile: Card List -->
    <div class="md:hidden space-y-3">
      <Link
        v-for="job in jobs.data"
        :key="job.id"
        :href="route('cms.jobs.show', job.id)"
        class="block bg-white rounded-xl shadow-sm border border-gray-100 p-4 active:bg-gray-50 transition"
      >
        <div class="flex items-start justify-between gap-3 mb-2">
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
              <p class="text-sm font-semibold text-gray-900">{{ job.job_number }}</p>
              <span :class="getStatusClass(job.status)">
                {{ formatStatus(job.status) }}
              </span>
            </div>
            <p class="text-sm text-gray-600 truncate">{{ job.customer?.name }}</p>
          </div>
          <ChevronRightIcon class="h-5 w-5 text-gray-400 flex-shrink-0 mt-1" aria-hidden="true" />
        </div>
        
        <div class="grid grid-cols-2 gap-2 text-xs text-gray-500 mt-3 pt-3 border-t border-gray-100">
          <div>
            <span class="font-medium">Type:</span> {{ job.job_type }}
          </div>
          <div class="text-right">
            <span class="font-medium">Value:</span> K{{ formatNumber(job.quoted_value || 0) }}
          </div>
          <div v-if="job.assigned_to?.user?.name">
            <span class="font-medium">Assigned:</span> {{ job.assigned_to.user.name }}
          </div>
          <div v-if="job.deadline" class="text-right">
            <span class="font-medium">Due:</span> {{ formatDate(job.deadline) }}
          </div>
        </div>
      </Link>

      <div v-if="jobs.data.length === 0" class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
        <BriefcaseIcon class="h-12 w-12 text-gray-300 mx-auto mb-2" aria-hidden="true" />
        <p class="text-sm text-gray-500 mb-3">No jobs found</p>
        <Link
          :href="route('cms.jobs.create')"
          class="inline-block text-sm text-blue-600 font-medium"
        >
          Create your first job
        </Link>
      </div>
    </div>

    <!-- Desktop: Table -->
    <div class="hidden md:block bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Job #</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Assigned To</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Value</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deadline</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="job in jobs.data" :key="job.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
              {{ job.job_number }}
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
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ job.assigned_to?.user?.name || '-' }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              K{{ formatNumber(job.quoted_value || 0) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ job.deadline ? formatDate(job.deadline) : '-' }}
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
          <tr v-if="jobs.data.length === 0">
            <td colspan="8" class="px-6 py-8 text-center text-gray-500">
              No jobs found. Create your first job to get started.
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="jobs.data.length > 0" class="px-6 py-4 border-t border-gray-200">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-500">
            Showing {{ jobs.from }} to {{ jobs.to }} of {{ jobs.total }} jobs
          </div>
          <div class="flex gap-2">
            <Link
              v-for="link in jobs.links"
              :key="link.label"
              :href="link.url"
              :class="[
                'px-3 py-1 rounded',
                link.active
                  ? 'bg-blue-600 text-white'
                  : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300',
                !link.url && 'opacity-50 cursor-not-allowed',
              ]"
              v-html="link.label"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile Pagination -->
    <div v-if="jobs.data.length > 0" class="md:hidden mt-4 flex items-center justify-between text-sm">
      <div class="text-gray-500">
        {{ jobs.from }}-{{ jobs.to }} of {{ jobs.total }}
      </div>
      <div class="flex gap-2">
        <Link
          v-for="link in jobs.links.filter((l: any) => l.label.includes('Previous') || l.label.includes('Next'))"
          :key="link.label"
          :href="link.url"
          :class="[
            'px-3 py-1 rounded-lg text-sm font-medium',
            link.url
              ? 'bg-white text-gray-700 border border-gray-300'
              : 'bg-gray-100 text-gray-400 cursor-not-allowed',
          ]"
        >
          {{ link.label.includes('Previous') ? '← Prev' : 'Next →' }}
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, inject } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import CMSLayoutNew from '@/Layouts/CMSLayoutNew.vue'
import {
  BriefcaseIcon,
  ChevronRightIcon,
  PlusIcon,
} from '@heroicons/vue/24/outline'

defineOptions({
  layout: CMSLayoutNew
})

interface Props {
  jobs: any
  filters: {
    status?: string
    assigned_to?: number
  }
}

const props = defineProps<Props>()

// Get slideOver from layout
const slideOver: any = inject('slideOver')

const filters = ref({ ...props.filters })

const applyFilters = () => {
  router.get(route('cms.jobs.index'), filters.value, {
    preserveState: true,
    preserveScroll: true,
  })
}

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
    pending: 'px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-700',
    in_progress: 'px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-700',
    completed: 'px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-700',
    cancelled: 'px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-700',
  }
  return classes[status as keyof typeof classes] || classes.pending
}
</script>
