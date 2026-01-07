<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { UserPlusIcon, TrashIcon } from '@heroicons/vue/24/outline';

const props = defineProps<{
  geopamuAdmins: any[];
  allUsers: any[];
}>();

const form = useForm({
  user_id: ''
});

const assignAdmin = () => {
  form.post('/admin/geopamu-admins/assign', {
    onSuccess: () => form.reset()
  });
};

const revokeAdmin = (userId: number) => {
  if (confirm('Are you sure you want to revoke Geopamu admin access?')) {
    useForm({ user_id: userId }).post('/admin/geopamu-admins/revoke');
  }
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};
</script>

<template>
  <Head title="Geopamu Admin Management" />
  
  <div class="min-h-screen bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Geopamu Admin Management</h1>
        <p class="text-gray-600 mt-2">Manage who can access the Geopamu admin dashboard</p>
      </div>

      <!-- Assign New Admin -->
      <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Assign Geopamu Admin Access</h2>
        
        <form @submit.prevent="assignAdmin" class="flex gap-4">
          <select
            v-model="form.user_id"
            required
            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent"
          >
            <option value="">Select a user...</option>
            <option v-for="user in allUsers" :key="user.id" :value="user.id">
              {{ user.name }} ({{ user.email }})
            </option>
          </select>
          
          <button
            type="submit"
            :disabled="form.processing"
            class="inline-flex items-center px-6 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 disabled:opacity-50"
          >
            <UserPlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
            Assign Access
          </button>
        </form>
      </div>

      <!-- Current Geopamu Admins -->
      <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-xl font-semibold text-gray-900">Current Geopamu Admins</h2>
          <p class="text-sm text-gray-600 mt-1">{{ geopamuAdmins.length }} user(s) with access</p>
        </div>

        <div v-if="geopamuAdmins.length === 0" class="p-8 text-center text-gray-500">
          No Geopamu admins assigned yet.
        </div>

        <table v-else class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Name
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Email
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Granted On
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="admin in geopamuAdmins" :key="admin.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ admin.name }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-500">{{ admin.email }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-500">{{ formatDate(admin.created_at) }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button
                  @click="revokeAdmin(admin.id)"
                  class="text-red-600 hover:text-red-900 inline-flex items-center"
                >
                  <TrashIcon class="h-5 w-5 mr-1" aria-hidden="true" />
                  Revoke
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Info Box -->
      <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-blue-900 mb-2">Important Notes</h3>
        <ul class="text-sm text-blue-800 space-y-2">
          <li>• Geopamu admins can ONLY access /geopamu/admin/blog</li>
          <li>• They CANNOT access MyGrowNet admin dashboard</li>
          <li>• This ensures proper separation between systems</li>
          <li>• You can revoke access at any time</li>
        </ul>
      </div>
    </div>
  </div>
</template>
