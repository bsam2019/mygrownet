<template>
  <AdminLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-900">
        LGR Member Qualifications
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
        <!-- Filters -->
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="grid gap-4 sm:grid-cols-3">
              <div>
                <label class="block text-sm font-medium text-gray-700">Search</label>
                <input
                  v-model="filters.search"
                  type="text"
                  placeholder="Name or email..."
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  @input="applyFilters"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <select
                  v-model="filters.qualified"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  @change="applyFilters"
                >
                  <option value="">All Members</option>
                  <option value="1">Qualified</option>
                  <option value="0">Not Qualified</option>
                </select>
              </div>

              <div class="flex items-end">
                <button
                  @click="clearFilters"
                  class="w-full rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                  Clear Filters
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Qualifications Table -->
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6">
            <h3 class="mb-4 text-lg font-semibold text-gray-900">Member Qualifications</h3>
            
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                      Member
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                      Status
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                      Starter Kit
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                      Active Referrals
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                      Last Check
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  <tr v-for="qualification in qualifications.data" :key="qualification.id">
                    <td class="whitespace-nowrap px-4 py-3 text-sm">
                      <div>
                        <div class="font-medium text-gray-900">{{ qualification.user.name }}</div>
                        <div class="text-gray-500">{{ qualification.user.email }}</div>
                      </div>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-sm">
                      <span
                        :class="[
                          'inline-flex rounded-full px-2 py-1 text-xs font-semibold',
                          qualification.is_qualified
                            ? 'bg-green-100 text-green-800'
                            : 'bg-red-100 text-red-800',
                        ]"
                      >
                        {{ qualification.is_qualified ? 'Qualified' : 'Not Qualified' }}
                      </span>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-sm">
                      <span
                        :class="[
                          'inline-flex rounded-full px-2 py-1 text-xs font-semibold',
                          qualification.has_starter_kit
                            ? 'bg-blue-100 text-blue-800'
                            : 'bg-gray-100 text-gray-800',
                        ]"
                      >
                        {{ qualification.has_starter_kit ? 'Yes' : 'No' }}
                      </span>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">
                      {{ qualification.active_referrals_count || 0 }}
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">
                      {{ qualification.last_checked_at || 'Never' }}
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-sm">
                      <Link
                        v-if="qualification.user_id"
                        :href="route('admin.users.show', qualification.user_id)"
                        class="text-blue-600 hover:text-blue-800"
                      >
                        View User
                      </Link>
                      <span v-else class="text-gray-400">N/A</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div v-if="qualifications.links && qualifications.links.length > 3" class="mt-4 flex items-center justify-between">
              <div class="text-sm text-gray-700">
                Showing {{ qualifications.from }} to {{ qualifications.to }} of {{ qualifications.total }} members
              </div>
              <div class="flex space-x-1">
                <component
                  :is="link.url ? Link : 'span'"
                  v-for="(link, index) in qualifications.links"
                  :key="index"
                  :href="link.url || undefined"
                  :class="[
                    'px-3 py-2 text-sm rounded-md',
                    link.active
                      ? 'bg-blue-600 text-white'
                      : link.url
                      ? 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
                      : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                  ]"
                  v-html="link.label"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface Props {
  qualifications: {
    data: Array<any>;
    links: Array<any>;
    from: number;
    to: number;
    total: number;
  };
  filters: {
    search?: string;
    qualified?: string;
  };
}

const props = defineProps<Props>();

const filters = ref({
  search: props.filters?.search || '',
  qualified: props.filters?.qualified || '',
});

let searchTimeout: ReturnType<typeof setTimeout> | null = null;

const applyFilters = () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  
  searchTimeout = setTimeout(() => {
    router.get(route('admin.lgr.qualifications'), filters.value, {
      preserveState: true,
      preserveScroll: true,
    });
  }, 500);
};

const clearFilters = () => {
  filters.value = {
    search: '',
    qualified: '',
  };
  applyFilters();
};
</script>
