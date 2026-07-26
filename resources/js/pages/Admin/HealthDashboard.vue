<template>
  <Head title="Platform Health Dashboard" />
  <AdminLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-900">
          Platform Health Dashboard
        </h2>
        <button
          @click="refresh"
          :disabled="refreshing"
          class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
        >
          <svg v-if="refreshing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
          </svg>
          <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          {{ refreshing ? 'Refreshing...' : 'Refresh' }}
        </button>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Application</th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Last Checked</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="app in applications" :key="app.id" class="hover:bg-gray-50">
                  <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{{ app.name }}</td>
                  <td class="whitespace-nowrap px-6 py-4 text-sm">
                    <span class="inline-flex items-center gap-2">
                      <span :class="statusColor(app.status)" class="h-2.5 w-2.5 inline-block rounded-full" />
                      <span :class="statusTextColor(app.status)">{{ statusLabel(app.status) }}</span>
                    </span>
                  </td>
                  <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ app.last_checked ?? 'Never' }}</td>
                </tr>
                <tr v-if="applications.length === 0">
                  <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500">No applications found.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface ApplicationHealth {
  id: string;
  name: string;
  status: string;
  last_checked: string | null;
}

defineProps<{
  applications: ApplicationHealth[];
}>();

const refreshing = ref(false);

const statusColor = (status: string): string => {
  const colors: Record<string, string> = {
    healthy: 'bg-green-500',
    degraded: 'bg-yellow-500',
    maintenance: 'bg-blue-500',
    unavailable: 'bg-red-500',
    offline: 'bg-gray-400',
  };
  return colors[status] ?? 'bg-gray-400';
};

const statusTextColor = (status: string): string => {
  const colors: Record<string, string> = {
    healthy: 'text-green-700',
    degraded: 'text-yellow-700',
    maintenance: 'text-blue-700',
    unavailable: 'text-red-700',
    offline: 'text-gray-500',
  };
  return colors[status] ?? 'text-gray-500';
};

const statusLabel = (status: string): string => {
  return status.charAt(0).toUpperCase() + status.slice(1);
};

const refresh = () => {
  refreshing.value = true;
  router.get(route('admin.health'), {}, {
    preserveScroll: true,
    preserveState: false,
    onFinish: () => {
      refreshing.value = false;
    },
  });
};
</script>
