<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-900">
        My BGF Applications
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
        <!-- Header Actions -->
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Your Applications</h3>
            <p class="mt-1 text-sm text-gray-600">
              Track your Business Growth Fund applications
            </p>
          </div>
          <Link
            :href="route('mygrownet.bgf.create')"
            class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
          >
            New Application
          </Link>
        </div>

        <!-- Applications List -->
        <div v-if="applications.data.length > 0" class="space-y-4">
          <div
            v-for="application in applications.data"
            :key="application.id"
            class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm transition hover:shadow-md"
          >
            <div class="p-6">
              <div class="flex items-start justify-between">
                <div class="flex-1">
                  <h4 class="text-lg font-semibold text-gray-900">
                    {{ application.business_name }}
                  </h4>
                  <p class="mt-1 text-sm text-gray-600">
                    {{ application.business_type }}
                  </p>
                  <div class="mt-3 flex items-center space-x-4 text-sm text-gray-500">
                    <span>Amount: K{{ application.amount_requested.toLocaleString() }}</span>
                    <span>•</span>
                    <span>Applied: {{ application.created_at }}</span>
                  </div>
                </div>
                <div class="ml-4">
                  <span
                    :class="[
                      'inline-flex rounded-full px-3 py-1 text-xs font-semibold',
                      application.status === 'draft'
                        ? 'bg-gray-100 text-gray-800'
                        : application.status === 'submitted'
                          ? 'bg-blue-100 text-blue-800'
                          : application.status === 'under_review'
                            ? 'bg-yellow-100 text-yellow-800'
                            : application.status === 'approved'
                              ? 'bg-green-100 text-green-800'
                              : 'bg-red-100 text-red-800',
                    ]"
                  >
                    {{ application.status.replace('_', ' ').toUpperCase() }}
                  </span>
                </div>
              </div>

              <div class="mt-4 flex items-center justify-end space-x-3">
                <Link
                  :href="route('mygrownet.bgf.show', application.id)"
                  class="text-sm font-medium text-blue-600 hover:text-blue-800"
                >
                  View Details
                </Link>
                <button
                  v-if="application.status === 'draft'"
                  @click="submitApplication(application.id)"
                  class="text-sm font-medium text-green-600 hover:text-green-800"
                >
                  Submit Application
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div
          v-else
          class="rounded-lg border-2 border-dashed border-gray-300 bg-white p-12 text-center"
        >
          <svg
            class="mx-auto h-12 w-12 text-gray-400"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
            />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No applications</h3>
          <p class="mt-1 text-sm text-gray-500">
            Get started by creating your first BGF application
          </p>
          <div class="mt-6">
            <Link
              :href="route('mygrownet.bgf.create')"
              class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
            >
              <svg
                class="-ml-1 mr-2 h-5 w-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 4v16m8-8H4"
                />
              </svg>
              New Application
            </Link>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="applications.data.length > 0" class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Showing {{ applications.from }} to {{ applications.to }} of {{ applications.total }} applications
          </div>
          <div class="flex space-x-2">
            <Link
              v-for="link in applications.links"
              :key="link.label"
              :href="link.url"
              :class="[
                'rounded-md px-3 py-2 text-sm',
                link.active
                  ? 'bg-blue-600 text-white'
                  : link.url
                    ? 'bg-white text-gray-700 hover:bg-gray-50'
                    : 'bg-gray-100 text-gray-400 cursor-not-allowed',
              ]"
              v-html="link.label"
            />
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { router, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

interface Props {
  applications: {
    data: Array<any>;
    links: Array<any>;
    from: number;
    to: number;
    total: number;
  };
}

defineProps<Props>();

const submitApplication = (id: number) => {
  if (confirm('Are you sure you want to submit this application? You will not be able to edit it after submission.')) {
    router.post(route('mygrownet.bgf.submit', id));
  }
};
</script>

