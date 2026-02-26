<script setup lang="ts">
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

interface Company {
  id: number
  name: string
  email: string
  phone: string
  status: string
  subscription_type: string
  sponsor_reference: string | null
  subscription_notes: string | null
  complimentary_until: string | null
}

interface Props {
  company: Company
}

const props = defineProps<Props>()

const form = useForm({
  subscription_type: props.company.subscription_type,
  sponsor_reference: props.company.sponsor_reference || '',
  subscription_notes: props.company.subscription_notes || '',
  complimentary_until: props.company.complimentary_until || '',
  status: props.company.status,
})

const submit = () => {
  form.put(route('admin.cms-companies.update', props.company.id))
}
</script>

<template>
  <Head :title="`Edit ${company.name} Access`" />

  <AdminLayout>
    <div class="py-6">
      <!-- Header -->
      <div class="mb-6">
        <Link
          :href="route('admin.cms-companies.index')"
          class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-4"
        >
          <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
          Back to Companies
        </Link>
        <h1 class="text-2xl font-semibold text-gray-900">Edit Company Access</h1>
        <p class="mt-1 text-sm text-gray-600">
          Manage subscription type and access for {{ company.name }}
        </p>
      </div>

      <!-- Company Info Card -->
      <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Company Information</h2>
        <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
          <div>
            <dt class="text-sm font-medium text-gray-500">Company Name</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ company.name }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-500">Email</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ company.email }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-500">Phone</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ company.phone || '—' }}</dd>
          </div>
        </dl>
      </div>

      <!-- Subscription Form -->
      <form @submit.prevent="submit" class="bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-6">Subscription Settings</h2>

        <div class="space-y-6">
          <!-- Subscription Type -->
          <div>
            <label for="subscription_type" class="block text-sm font-medium text-gray-700">
              Subscription Type <span class="text-red-500">*</span>
            </label>
            <select
              id="subscription_type"
              v-model="form.subscription_type"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              :class="{ 'border-red-300': form.errors.subscription_type }"
            >
              <option value="paid">Paid - Standard subscription</option>
              <option value="sponsored">Sponsored - Sponsored by MyGrowNet or partner</option>
              <option value="complimentary">Complimentary - Free access (beta, promo, etc.)</option>
              <option value="partner">Partner - Special partner access</option>
            </select>
            <p v-if="form.errors.subscription_type" class="mt-1 text-sm text-red-600">
              {{ form.errors.subscription_type }}
            </p>
            <p class="mt-1 text-sm text-gray-500">
              Select the type of access this company should have
            </p>
          </div>

          <!-- Sponsor Reference -->
          <div v-if="['sponsored', 'complimentary', 'partner'].includes(form.subscription_type)">
            <label for="sponsor_reference" class="block text-sm font-medium text-gray-700">
              Sponsor/Reference
            </label>
            <input
              id="sponsor_reference"
              v-model="form.sponsor_reference"
              type="text"
              placeholder="e.g., MyGrowNet Beta Program, Partnership Agreement #2026-001"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              :class="{ 'border-red-300': form.errors.sponsor_reference }"
            />
            <p v-if="form.errors.sponsor_reference" class="mt-1 text-sm text-red-600">
              {{ form.errors.sponsor_reference }}
            </p>
            <p class="mt-1 text-sm text-gray-500">
              Who is sponsoring or what is the reference for this arrangement
            </p>
          </div>

          <!-- Complimentary Until -->
          <div v-if="form.subscription_type === 'complimentary'">
            <label for="complimentary_until" class="block text-sm font-medium text-gray-700">
              Expiration Date <span class="text-red-500">*</span>
            </label>
            <input
              id="complimentary_until"
              v-model="form.complimentary_until"
              type="datetime-local"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              :class="{ 'border-red-300': form.errors.complimentary_until }"
            />
            <p v-if="form.errors.complimentary_until" class="mt-1 text-sm text-red-600">
              {{ form.errors.complimentary_until }}
            </p>
            <p class="mt-1 text-sm text-gray-500">
              When should this complimentary access expire? Leave empty for unlimited access.
            </p>
          </div>

          <!-- Subscription Notes -->
          <div>
            <label for="subscription_notes" class="block text-sm font-medium text-gray-700">
              Internal Notes
            </label>
            <textarea
              id="subscription_notes"
              v-model="form.subscription_notes"
              rows="4"
              placeholder="Internal notes about this subscription arrangement..."
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              :class="{ 'border-red-300': form.errors.subscription_notes }"
            />
            <p v-if="form.errors.subscription_notes" class="mt-1 text-sm text-red-600">
              {{ form.errors.subscription_notes }}
            </p>
            <p class="mt-1 text-sm text-gray-500">
              Document why this access was granted and any special conditions
            </p>
          </div>

          <!-- Status -->
          <div>
            <label for="status" class="block text-sm font-medium text-gray-700">
              Account Status <span class="text-red-500">*</span>
            </label>
            <select
              id="status"
              v-model="form.status"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
              :class="{ 'border-red-300': form.errors.status }"
            >
              <option value="active">Active - Company can access GrowSuite</option>
              <option value="suspended">Suspended - Access blocked</option>
            </select>
            <p v-if="form.errors.status" class="mt-1 text-sm text-red-600">
              {{ form.errors.status }}
            </p>
          </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex items-center justify-end gap-3">
          <Link
            :href="route('admin.cms-companies.index')"
            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Cancel
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
          >
            {{ form.processing ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>
