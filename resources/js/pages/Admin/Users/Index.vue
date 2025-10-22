<script setup>
import { ref, onMounted } from 'vue'
import { router, usePage, Link, Head, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import Swal from 'sweetalert2'
import { 
    UserIcon, 
    PencilIcon, 
    TrashIcon,
    CheckCircleIcon,
    XCircleIcon
} from '@heroicons/vue/24/outline'

const page = usePage()
const props = defineProps({
  users: Object,
  roles: Array
})

const editingUser = ref(null)
const showEditModal = ref(false)

const form = useForm({
  name: '',
  email: '',
  role: '',
  status: ''
})

const formatDate = (date) => {
  return date ? new Date(date).toLocaleDateString() : '-'
}

const getStatusColor = (status) => {
  return {
    'active': 'bg-green-100 text-green-800',
    'inactive': 'bg-gray-100 text-gray-800',
    'suspended': 'bg-red-100 text-red-800'
  }[status] || 'bg-gray-100 text-gray-800'
}

const openEditModal = (user) => {
  editingUser.value = user
  form.name = user.name
  form.email = user.email
  form.role = user.role
  form.status = user.status
  showEditModal.value = true
}

const closeEditModal = () => {
  showEditModal.value = false
  editingUser.value = null
  form.reset()
}

const submitEdit = () => {
  form.put(route('admin.users.update', editingUser.value.id), {
    onSuccess: () => {
      closeEditModal()
    }
  })
}

const toggleStatus = (user) => {
  Swal.fire({
    title: 'Are you sure?',
    text: `Do you want to ${user.status === 'active' ? 'suspend' : 'activate'} this user?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes'
  }).then((result) => {
    if (result.isConfirmed) {
      router.patch(route('admin.users.toggle-status', user.id), {}, {
        preserveScroll: true
      })
    }
  })
}
</script>

<template>
  <AdminLayout :breadcrumbs="[
    { name: 'Dashboard', href: route('admin.dashboard') },
    { name: 'Users', href: route('admin.users.index') }
  ]">
    <Head title="User Management" />
    
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow">
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h2 class="text-xl font-semibold text-gray-900">User Management</h2>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Login</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                    <div>
                        <Link
                        :href="route('admin.users.show', user.id)"
                        class="text-sm font-medium text-blue-600 hover:text-blue-800 hover:underline"
                        >
                        {{ user.name }}
                        </Link>
                        <div class="text-xs text-gray-500">Joined {{ formatDate(user.created_at) }}</div>
                    </div>
                    </td>
                  <td class="px-6 py-4 text-sm text-gray-500">{{ user.email }}</td>
                  <td class="px-6 py-4">
                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                      {{ user.role }}
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <span :class="[
                      'px-2 py-1 text-xs rounded-full',
                      getStatusColor(user.status)
                    ]">
                      {{ user.status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-500">
                    {{ formatDate(user.last_login_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap space-x-2">
                    <button @click="openEditModal(user)"
                      class="px-3 py-1 text-sm text-white bg-indigo-600 rounded hover:bg-indigo-700">
                      Edit
                    </button>
                    <button @click="toggleStatus(user)"
                      class="px-3 py-1 text-sm text-white rounded"
                      :class="user.status === 'active' ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'">
                      {{ user.status === 'active' ? 'Suspend' : 'Activate' }}
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit User Modal -->
    <div v-if="showEditModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-end sm:items-center justify-center min-h-screen p-4 text-center sm:p-0">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="closeEditModal"></div>

        <div class="relative bg-white rounded-lg w-full max-w-md transform transition-all">
          <div class="flex justify-between items-center p-4 border-b">
            <h3 class="text-lg font-semibold">Edit User</h3>
            <button @click="closeEditModal" class="text-gray-500 hover:text-gray-700">
              <span class="text-2xl">&times;</span>
            </button>
          </div>

          <form @submit.prevent="submitEdit" class="p-4 space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
              <input type="text" v-model="form.name" required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
              <input type="email" v-model="form.email" required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
              <select v-model="form.role"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                <option v-for="role in roles" :key="role.id" :value="role.name">
                  {{ role.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select v-model="form.status"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>

            <div class="flex justify-end space-x-2 pt-4">
              <button type="button" @click="closeEditModal"
                class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                Cancel
              </button>
              <button type="submit"
                class="px-4 py-2 text-sm text-white bg-blue-600 rounded-md hover:bg-blue-700">
                Save Changes
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
