<template>
  <AdminLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <h1 class="text-3xl font-bold text-gray-900">User Points Management</h1>
          <p class="mt-2 text-gray-600">View and manage points for all users</p>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow mb-6 p-6">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
              <input
                v-model="filters.search"
                type="text"
                placeholder="Name or email..."
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                @input="applyFilters"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Level</label>
              <select
                v-model="filters.level"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                @change="applyFilters"
              >
                <option value="">All Levels</option>
                <option value="associate">Associate</option>
                <option value="professional">Professional</option>
                <option value="senior">Senior</option>
                <option value="manager">Manager</option>
                <option value="director">Director</option>
                <option value="executive">Executive</option>
                <option value="ambassador">Ambassador</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Qualification</label>
              <select
                v-model="filters.qualified"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                @change="applyFilters"
              >
                <option value="">All Users</option>
                <option value="true">Qualified</option>
                <option value="false">Not Qualified</option>
              </select>
            </div>

            <div class="flex items-end">
              <button
                @click="resetFilters"
                class="w-full px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition"
              >
                Reset Filters
              </button>
            </div>
          </div>
        </div>

        <!-- Bulk Actions Bar -->
        <div v-if="selectedUsers.length > 0" class="bg-blue-50 border border-blue-200 rounded-lg shadow mb-6 p-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
              <span class="text-sm font-medium text-blue-900">
                {{ selectedUsers.length }} user(s) selected
              </span>
              <button
                @click="selectedUsers = []"
                class="text-sm text-blue-600 hover:text-blue-800"
              >
                Clear selection
              </button>
            </div>
            <button
              @click="showBulkAwardModal = true"
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition flex items-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              Bulk Award Points
            </button>
          </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left">
                    <input
                      type="checkbox"
                      :checked="selectedUsers.length === users.data.length && users.data.length > 0"
                      @change="toggleSelectAll"
                      class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    />
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Level</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lifetime Points</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monthly Points</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Multiplier</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <input
                      type="checkbox"
                      :checked="selectedUsers.includes(user.id)"
                      @change="toggleUserSelection(user.id)"
                      class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    />
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div>
                        <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                        <div class="text-sm text-gray-500">{{ user.email }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full capitalize" :class="getLevelBadgeClass(user.current_professional_level)">
                      {{ user.current_professional_level }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ user.points?.lifetime_points || 0 }} LP
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ user.points?.monthly_points || 0 }} MAP
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span v-if="user.current_month_activity?.qualified" class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                      ✓ Qualified
                    </span>
                    <span v-else class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                      ✗ Not Qualified
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ user.points?.active_multiplier || 1.00 }}x
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <Link
                      :href="route('admin.points.show', user.id)"
                      class="text-blue-600 hover:text-blue-900 mr-3"
                    >
                      View Details
                    </Link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            <div class="flex items-center justify-between">
              <div class="text-sm text-gray-700">
                Showing {{ users.from || 0 }} to {{ users.to || 0 }} of {{ users.total || 0 }} users
              </div>
              <div class="flex gap-2">
                <component
                  v-for="(link, index) in users.links"
                  :key="index"
                  :is="link.url ? Link : 'span'"
                  :href="link.url"
                  :class="[
                    'px-3 py-2 text-sm rounded-md',
                    link.active
                      ? 'bg-blue-600 text-white'
                      : link.url
                      ? 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300 cursor-pointer'
                      : 'bg-gray-100 text-gray-400 border border-gray-200 cursor-not-allowed'
                  ]"
                  v-html="link.label"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Bulk Award Modal -->
    <div v-if="showBulkAwardModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="showBulkAwardModal = false">
      <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-medium text-gray-900">Bulk Award Points to {{ selectedUsers.length }} User(s)</h3>
          <button @click="showBulkAwardModal = false" class="text-gray-400 hover:text-gray-500">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <form @submit.prevent="submitBulkAward">
          <div class="space-y-4">
            <div class="bg-blue-50 border border-blue-200 rounded-md p-3">
              <p class="text-sm text-blue-800">
                You are about to award points to <strong>{{ selectedUsers.length }}</strong> selected user(s).
              </p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Lifetime Points (LP)</label>
              <input
                v-model.number="bulkAwardForm.lp_amount"
                type="number"
                min="0"
                max="10000"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                required
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Monthly Activity Points (MAP)</label>
              <input
                v-model.number="bulkAwardForm.map_amount"
                type="number"
                min="0"
                max="10000"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                required
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Reason</label>
              <textarea
                v-model="bulkAwardForm.reason"
                rows="3"
                maxlength="500"
                placeholder="e.g., Monthly bonus, Special promotion, etc."
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                required
              ></textarea>
            </div>
          </div>
          
          <div class="mt-6 flex justify-end gap-3">
            <button
              type="button"
              @click="showBulkAwardModal = false"
              class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition"
            >
              Award Points to {{ selectedUsers.length }} User(s)
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  users: {
    type: Object,
    default: () => ({ data: [], links: [], from: 0, to: 0, total: 0 })
  },
  filters: {
    type: Object,
    default: () => ({})
  },
});

const filters = reactive({
  search: props.filters?.search || '',
  level: props.filters?.level || '',
  qualified: props.filters?.qualified || '',
});

const selectedUsers = ref([]);
const showBulkAwardModal = ref(false);

const bulkAwardForm = reactive({
  lp_amount: 0,
  map_amount: 0,
  reason: '',
});

const toggleUserSelection = (userId) => {
  const index = selectedUsers.value.indexOf(userId);
  if (index > -1) {
    selectedUsers.value.splice(index, 1);
  } else {
    selectedUsers.value.push(userId);
  }
};

const toggleSelectAll = () => {
  if (selectedUsers.value.length === props.users.data.length) {
    selectedUsers.value = [];
  } else {
    selectedUsers.value = props.users.data.map(u => u.id);
  }
};

const submitBulkAward = () => {
  if (selectedUsers.value.length === 0) {
    alert('Please select at least one user');
    return;
  }

  router.post(route('admin.points.bulk-award'), {
    user_ids: selectedUsers.value,
    lp_amount: bulkAwardForm.lp_amount,
    map_amount: bulkAwardForm.map_amount,
    reason: bulkAwardForm.reason,
  }, {
    onSuccess: () => {
      showBulkAwardModal.value = false;
      selectedUsers.value = [];
      bulkAwardForm.lp_amount = 0;
      bulkAwardForm.map_amount = 0;
      bulkAwardForm.reason = '';
    },
  });
};

const applyFilters = () => {
  router.get(route('admin.points.users'), filters, {
    preserveState: true,
    preserveScroll: true,
  });
};

const resetFilters = () => {
  filters.search = '';
  filters.level = '';
  filters.qualified = '';
  applyFilters();
};

const getLevelBadgeClass = (level) => {
  const classes = {
    associate: 'bg-gray-100 text-gray-800',
    professional: 'bg-blue-100 text-blue-800',
    senior: 'bg-emerald-100 text-emerald-800',
    manager: 'bg-purple-100 text-purple-800',
    director: 'bg-indigo-100 text-indigo-800',
    executive: 'bg-pink-100 text-pink-800',
    ambassador: 'bg-amber-100 text-amber-800',
  };
  return classes[level] || 'bg-gray-100 text-gray-800';
};
</script>
