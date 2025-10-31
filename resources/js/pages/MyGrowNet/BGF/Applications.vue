<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-900">
          My BGF Applications
        </h2>
        <Link
          :href="route('mygrownet.bgf.create')"
          class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
        >
          New Application
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div v-if="applications.data.length === 0" class="text-center py-12">
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
                Get started by creating a new BGF application.
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

            <div v-else class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                    >
                      Reference
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                    >
                      Business Name
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                    >
                      Amount
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                    >
                      Status
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                    >
                      Date
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                  <tr v-for="application in applications.data" :key="application.id">
                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                      {{ application.reference_number }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                      {{ application.business_name }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                      K{{ application.amount_requested.toLocaleString() }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                      <span
                        :class="[
                          'inline-flex rounded-full px-2 py-1 text-xs font-semibold',
                          getStatusClass(application.status),
                        ]"
                      >
                        {{ application.status }}
                      </span>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      {{ formatDate(application.created_at) }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                      <Link
                        :href="route('mygrownet.bgf.show', application.id)"
                        class="text-blue-600 hover:text-blue-900"
                      >
                        View
                      </Link>
                    </td>
                  </tr>
                </tbody>
              </table>

              <!-- Pagination -->
              <div v-if="applications.links.length > 3" class="mt-4 flex items-center justify-between border-t border-gray-200 px-4 py-3 sm:px-6">
                <div class="flex flex-1 justify-between sm:hidden">
                  <Link
                    v-if="applications.prev_page_url"
                    :href="applications.prev_page_url"
                    class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                  >
                    Previous
                  </Link>
                  <Link
                    v-if="applications.next_page_url"
                    :href="applications.next_page_url"
                    class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                  >
                    Next
                  </Link>
                </div>
                <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                  <div>
                    <p class="text-sm text-gray-700">
                      Showing
                      <span class="font-medium">{{ applications.from }}</span>
                      to
                      <span class="font-medium">{{ applications.to }}</span>
                      of
                      <span class="font-medium">{{ applications.total }}</span>
                      results
                    </p>
                  </div>
                  <div>
                    <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm">
                      <Link
                        v-for="(link, index) in applications.links"
                        :key="index"
                        :href="link.url"
                        :class="[
                          'relative inline-flex items-center px-4 py-2 text-sm font-medium',
                          link.active
                            ? 'z-10 bg-blue-600 text-white'
                            : 'bg-white text-gray-700 hover:bg-gray-50',
                          index === 0 ? 'rounded-l-md' : '',
                          index === applications.links.length - 1 ? 'rounded-r-md' : '',
                          !link.url ? 'cursor-not-allowed opacity-50' : '',
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
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

interface Application {
  id: number;
  reference_number: string;
  business_name: string;
  amount_requested: number;
  status: string;
  created_at: string;
}

interface Props {
  applications: {
    data: Application[];
    links: any[];
    from: number;
    to: number;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
  };
}

defineProps<Props>();

const getStatusClass = (status: string) => {
  const classes: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-800',
    submitted: 'bg-blue-100 text-blue-800',
    under_review: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
    withdrawn: 'bg-gray-100 text-gray-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};
</script>

