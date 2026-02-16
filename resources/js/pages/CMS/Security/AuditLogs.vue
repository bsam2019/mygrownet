<template>
  <CMSLayout title="Security Audit Logs">
    <div class="space-y-6">
      <!-- Header with Filters -->
      <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-2xl font-semibold text-gray-900 mb-4">Security Audit Logs</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Event Type</label>
            <select
              v-model="filters.event_type"
              @change="applyFilters"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="">All Events</option>
              <option value="login_successful">Login Successful</option>
              <option value="login_failed">Login Failed</option>
              <option value="account_locked">Account Locked</option>
              <option value="password_changed">Password Changed</option>
              <option value="2fa_enabled">2FA Enabled</option>
              <option value="2fa_disabled">2FA Disabled</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">User</label>
            <select
              v-model="filters.user_id"
              @change="applyFilters"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="">All Users</option>
              <option v-for="user in users" :key="user.id" :value="user.id">
                {{ user.name }} ({{ user.email }})
              </option>
            </select>
          </div>

          <div class="flex items-end">
            <button
              @click="clearFilters"
              class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              Clear Filters
            </button>
          </div>
        </div>
      </div>

      <!-- Logs Table -->
      <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Timestamp</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Event</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP Address</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Severity</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="log in logs.data" :key="log.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatDate(log.created_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ log.user?.name || 'System' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <span :class="getEventClass(log.event_type)">
                  {{ formatEventType(log.event_type) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ log.ip_address }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <span :class="getSeverityClass(log.severity)">
                  {{ log.severity }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ log.description }}
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div v-if="logs.data.length > 0" class="px-6 py-4 border-t border-gray-200">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Showing {{ logs.from }} to {{ logs.to }} of {{ logs.total }} results
            </div>
            <div class="flex gap-2">
              <button
                v-for="link in logs.links"
                :key="link.label"
                @click="changePage(link.url)"
                :disabled="!link.url"
                v-html="link.label"
                :class="[
                  'px-3 py-1 text-sm rounded',
                  link.active ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50',
                  !link.url && 'opacity-50 cursor-not-allowed'
                ]"
              />
            </div>
          </div>
        </div>

        <div v-else class="px-6 py-12 text-center text-gray-500">
          No audit logs found
        </div>
      </div>
    </div>
  </CMSLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';

const props = defineProps<{
  logs: any;
  users: any[];
  filters: {
    event_type?: string;
    user_id?: string;
  };
}>();

const filters = reactive({ ...props.filters });

const applyFilters = () => {
  router.get(route('cms.security.audit-logs'), filters, {
    preserveState: true,
    preserveScroll: true,
  });
};

const clearFilters = () => {
  filters.event_type = '';
  filters.user_id = '';
  applyFilters();
};

const changePage = (url: string | null) => {
  if (!url) return;
  router.get(url, {}, {
    preserveState: true,
    preserveScroll: true,
  });
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleString();
};

const formatEventType = (type: string) => {
  return type.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const getEventClass = (type: string) => {
  const classes: Record<string, string> = {
    login_successful: 'px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800',
    login_failed: 'px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800',
    account_locked: 'px-2 py-1 text-xs font-medium rounded-full bg-orange-100 text-orange-800',
    password_changed: 'px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800',
  };
  return classes[type] || 'px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800';
};

const getSeverityClass = (severity: string) => {
  const classes: Record<string, string> = {
    info: 'px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800',
    warning: 'px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800',
    critical: 'px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800',
  };
  return classes[severity] || 'px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800';
};
</script>
