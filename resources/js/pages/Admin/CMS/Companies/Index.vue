<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import {
  MagnifyingGlassIcon,
  FunnelIcon,
  BuildingOfficeIcon,
  CheckCircleIcon,
  XCircleIcon,
  ClockIcon,
  ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline'

interface Company {
  id: number
  name: string
  email: string
  phone: string
  industry_type: string
  status: string
  subscription_type: string
  sponsor_reference: string | null
  subscription_notes: string | null
  complimentary_until: string | null
  users_count: number
  created_at: string
  has_valid_access: boolean
  is_expiring_soon: boolean
  days_until_expires: number | null
}

interface Props {
  companies: {
    data: Company[]
    links: any[]
    meta: any
  }
  filters: {
    search?: string
    subscription_type?: string
    status?: string
    expiring_soon?: boolean
  }
  stats: {
    total: number
    active: number
    paid: number
    complimentary: number
    sponsored: number
    partner: number
    expiring_soon: number
  }
}

const props = defineProps<Props>()

const search = ref(props.filters.search || '')
const subscriptionType = ref(props.filters.subscription_type || '')
const status = ref(props.filters.status || '')
const expiringSoon = ref(props.filters.expiring_soon || false)

const applyFilters = () => {
  router.get(
    route('admin.cms-companies.index'),
    {
      search: search.value,
      subscription_type: subscriptionType.value,
      status: status.value,
      expiring_soon: expiringSoon.value ? '1' : '',
    },
    {
      preserveState: true,
      preserveScroll: true,
    }
  )
}

const clearFilters = () => {
  search.value = ''
  subscriptionType.value = ''
  status.value = ''
  expiringSoon.value = false
  applyFilters()
}

const getSubscriptionTypeBadge = (type: string) => {
  const badges = {
    paid: 'bg-blue-100 text-blue-800',
    sponsored: 'bg-purple-100 text-purple-800',
    complimentary: 'bg-green-100 text-green-800',
    partner: 'bg-indigo-100 text-indigo-800',
  }
  return badges[type as keyof typeof badges] || 'bg-gray-100 text-gray-800'
}

const getStatusBadge = (status: string) => {
  return status === 'active'
    ? 'bg-green-100 text-green-800'
    : 'bg-red-100 text-red-800'
}
</script>

<template>
  <Head title="GrowSuite Companies" />

  <AdminLayout>
    <div class="py-6">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">GrowSuite Companies</h1>
        <p class="mt-1 text-sm text-gray-600">
          Manage GrowSuite company access and subscriptions
        </p>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-6">
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <BuildingOfficeIcon class="h-6 w-6 text-gray-400" aria-hidden="true" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Total Companies</dt>
                  <dd class="text-lg font-semibold text-gray-900">{{ stats.total }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <CheckCircleIcon class="h-6 w-6 text-green-400" aria-hidden="true" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Active</dt>
                  <dd class="text-lg font-semibold text-gray-900">{{ stats.active }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <ClockIcon class="h-6 w-6 text-amber-400" aria-hidden="true" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Complimentary</dt>
                  <dd class="text-lg font-semibold text-gray-900">{{ stats.complimentary }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <ExclamationTriangleIcon class="h-6 w-6 text-red-400" aria-hidden="true" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Expiring Soon</dt>
                  <dd class="text-lg font-semibold text-gray-900">{{ stats.expiring_soon }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white shadow rounded-lg p-4 mb-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <div class="relative">
              <input
                v-model="search"
                type="text"
                placeholder="Company name, email..."
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                @keyup.enter="applyFilters"
              />
              <MagnifyingGlassIcon
                class="absolute right-3 top-2.5 h-5 w-5 text-gray-400"
                aria-hidden="true"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Subscription Type
            </label>
            <select
              v-model="subscriptionType"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              @change="applyFilters"
            >
              <option value="">All Types</option>
              <option value="paid">Paid</option>
              <option value="sponsored">Sponsored</option>
              <option value="complimentary">Complimentary</option>
              <option value="partner">Partner</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              v-model="status"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              @change="applyFilters"
            >
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="suspended">Suspended</option>
            </select>
          </div>

          <div class="flex items-end">
            <label class="flex items-center">
              <input
                v-model="expiringSoon"
                type="checkbox"
                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                @change="applyFilters"
              />
              <span class="ml-2 text-sm text-gray-700">Expiring Soon</span>
            </label>
          </div>

          <div class="flex items-end gap-2">
            <button
              type="button"
              @click="applyFilters"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <FunnelIcon class="h-4 w-4 mr-2" aria-hidden="true" />
              Filter
            </button>
            <button
              type="button"
              @click="clearFilters"
              class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              Clear
            </button>
          </div>
        </div>
      </div>

      <!-- Companies Table -->
      <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Company
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Subscription
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Expiration
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Users
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="company in companies.data" :key="company.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div>
                    <div class="text-sm font-medium text-gray-900">{{ company.name }}</div>
                    <div class="text-sm text-gray-500">{{ company.email }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="getSubscriptionTypeBadge(company.subscription_type)"
                  class="inline-flex px-2 text-xs font-semibold leading-5 rounded-full capitalize"
                >
                  {{ company.subscription_type }}
                </span>
                <div v-if="company.sponsor_reference" class="text-xs text-gray-500 mt-1">
                  {{ company.sponsor_reference }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="getStatusBadge(company.status)"
                  class="inline-flex px-2 text-xs font-semibold leading-5 rounded-full capitalize"
                >
                  {{ company.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <div v-if="company.complimentary_until">
                  <div>{{ new Date(company.complimentary_until).toLocaleDateString() }}</div>
                  <div
                    v-if="company.is_expiring_soon"
                    class="text-xs text-red-600 font-medium"
                  >
                    {{ company.days_until_expires }} days left
                  </div>
                </div>
                <span v-else class="text-gray-400">—</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ company.users_count }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <Link
                  :href="route('admin.cms-companies.edit', company.id)"
                  class="text-blue-600 hover:text-blue-900"
                >
                  Edit Access
                </Link>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div v-if="companies.links.length > 3" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
          <div class="flex items-center justify-between">
            <div class="flex-1 flex justify-between sm:hidden">
              <Link
                v-if="companies.links[0].url"
                :href="companies.links[0].url"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
              >
                Previous
              </Link>
              <Link
                v-if="companies.links[companies.links.length - 1].url"
                :href="companies.links[companies.links.length - 1].url"
                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
              >
                Next
              </Link>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-gray-700">
                  Showing
                  <span class="font-medium">{{ companies.meta.from }}</span>
                  to
                  <span class="font-medium">{{ companies.meta.to }}</span>
                  of
                  <span class="font-medium">{{ companies.meta.total }}</span>
                  results
                </p>
              </div>
              <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                  <Link
                    v-for="(link, index) in companies.links"
                    :key="index"
                    :href="link.url"
                    :class="[
                      link.active
                        ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                      'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                    ]"
                    v-html="link.label"
                  />
                </nav>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
