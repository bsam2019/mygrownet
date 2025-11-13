<script setup>
import { ref, onMounted, watch } from 'vue'
import { router, usePage, Link, Head, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import Swal from 'sweetalert2'
import { 
    UserIcon, 
    PencilIcon, 
    TrashIcon,
    CheckCircleIcon,
    XCircleIcon,
    ShieldExclamationIcon
} from '@heroicons/vue/24/outline'
import LgrRestrictionModal from '@/components/Admin/LgrRestrictionModal.vue'
import LoanModal from '@/components/Admin/LoanModal.vue'
import LoanLimitModal from '@/components/Admin/LoanLimitModal.vue'

const page = usePage()
const props = defineProps({
  users: Object,
  roles: Array,
  filters: Object,
  professionalLevels: Object
})

// Handle flash messages
const showFlashMessage = () => {
  if (page.props.flash?.success) {
    Swal.fire({
      icon: 'success',
      title: 'Success!',
      text: page.props.flash.success,
      timer: 3000,
      showConfirmButton: false,
    })
  }
  if (page.props.flash?.error) {
    Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: page.props.flash.error,
      timer: 3000,
      showConfirmButton: false,
    })
  }
}

onMounted(() => {
  showFlashMessage()
})

// Watch for flash message changes
watch(() => page.props.flash, (newFlash) => {
  if (newFlash) {
    showFlashMessage()
  }
}, { deep: true })

const filters = ref({
  search: props.filters?.search || '',
  status: props.filters?.status || '',
  role: props.filters?.role || '',
  level: props.filters?.level || '',
  date_from: props.filters?.date_from || '',
  date_to: props.filters?.date_to || ''
})

const showFilters = ref(false)
const showLgrModal = ref(false)
const showLoanModal = ref(false)
const showLoanLimitModal = ref(false)
const showMobileActionsModal = ref(false)
const selectedUser = ref(null)

const applyFilters = () => {
  router.get(route('admin.users.index'), filters.value, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
    only: ['users']
  })
}

const clearFilters = () => {
  filters.value = {
    search: '',
    status: '',
    role: '',
    level: '',
    date_from: '',
    date_to: ''
  }
  applyFilters()
}

// Debounced search - filters as you type
let searchTimeout = null
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 500) // Wait 500ms after user stops typing
}

// Watch for search input changes
const handleSearchInput = () => {
  debouncedSearch()
}

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

const openLgrModal = (user) => {
  console.log('Opening LGR modal for user:', user)
  console.log('LGR fields:', {
    loyalty_points: user.loyalty_points,
    awarded: user.loyalty_points_awarded_total,
    withdrawn: user.loyalty_points_withdrawn_total
  })
  selectedUser.value = user
  showLgrModal.value = true
}

const closeLgrModal = () => {
  showLgrModal.value = false
  selectedUser.value = null
}

const openLoanLimitModal = (user) => {
  router.get(route('admin.users.index'), filters.value, {
    preserveState: true,
    preserveScroll: true,
    only: ['users']
  })
  selectedUser.value = user
  showLoanLimitModal.value = true
}

const closeLoanLimitModal = () => {
  showLoanLimitModal.value = false
  selectedUser.value = null
}

const openLoanModal = (user) => {
  selectedUser.value = user
  showLoanModal.value = true
}

const closeLoanModal = () => {
  showLoanModal.value = false
  selectedUser.value = null
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

const impersonateUser = (userId) => {
  Swal.fire({
    title: 'Login as User?',
    text: 'You will be logged in as this user. You can return to your admin account anytime.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#f59e0b',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Yes, Login As User'
  }).then((result) => {
    if (result.isConfirmed) {
      router.post(route('admin.impersonate', userId))
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
    
    <div class="py-2 md:py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow">
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-xl font-semibold text-gray-900">User Management</h2>
              <button
                @click="showFilters = !showFilters"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
              >
                {{ showFilters ? 'Hide Filters' : 'Show Filters' }}
              </button>
            </div>

            <!-- Filters -->
            <div v-if="showFilters" class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-gray-50 rounded-lg">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input
                  v-model="filters.search"
                  type="text"
                  placeholder="Name, email, or phone..."
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                  @input="handleSearchInput"
                  @keyup.enter="applyFilters"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select
                  v-model="filters.status"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                  @change="applyFilters"
                >
                  <option value="">All Statuses</option>
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                  <option value="suspended">Suspended</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                <select
                  v-model="filters.role"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                  @change="applyFilters"
                >
                  <option value="">All Roles</option>
                  <option v-for="role in roles" :key="role.id" :value="role.name">
                    {{ role.name }}
                  </option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Professional Level</label>
                <select
                  v-model="filters.level"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                  @change="applyFilters"
                >
                  <option value="">All Levels</option>
                  <option v-for="(label, value) in professionalLevels" :key="value" :value="value">
                    {{ label }}
                  </option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                <input
                  v-model="filters.date_from"
                  type="date"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                  @change="applyFilters"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                <input
                  v-model="filters.date_to"
                  type="date"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                  @change="applyFilters"
                />
              </div>

              <div class="flex items-end space-x-2">
                <button
                  @click="applyFilters"
                  class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700"
                >
                  Apply Filters
                </button>
                <button
                  @click="clearFilters"
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                >
                  Clear
                </button>
              </div>
            </div>
          </div>

          <!-- Mobile Card View -->
          <div class="md:hidden space-y-4">
            <div v-for="user in users.data" :key="user.id" class="bg-white rounded-lg shadow p-4">
              <div class="flex items-center justify-between mb-3">
                <div class="flex-1">
                  <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-500">ID: {{ user.id }}</span>
                    <span :class="[
                      'px-2 py-1 text-xs rounded-full',
                      getStatusColor(user.status)
                    ]">
                      {{ user.status }}
                    </span>
                  </div>
                  <Link
                    :href="route('admin.users.show', user.id)"
                    class="text-base font-semibold text-blue-600 hover:text-blue-800 hover:underline"
                  >
                    {{ user.name }}
                  </Link>
                </div>
                <button
                  @click="selectedUser = user; showMobileActionsModal = true"
                  class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700"
                >
                  Actions
                </button>
              </div>
            </div>
            
            <!-- Pagination for mobile -->
            <div v-if="users.links" class="flex justify-center gap-2 mt-4 flex-wrap">
              <component
                :is="link.url ? Link : 'span'"
                v-for="(link, index) in users.links"
                :key="index"
                :href="link.url || undefined"
                :class="[
                  'px-3 py-2 text-sm rounded',
                  link.active ? 'bg-blue-600 text-white' : link.url ? 'bg-white text-gray-700 hover:bg-gray-50' : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                ]"
                v-html="link.label"
              />
            </div>
          </div>

          <!-- Desktop Table View -->
          <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
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
                      <div class="text-xs text-gray-500">ID: {{ user.id }}</div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm text-gray-900">{{ user.email }}</div>
                    <div class="text-xs text-gray-500">{{ user.phone || 'No phone' }}</div>
                  </td>
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
                    <button 
                      @click="openLgrModal(user)"
                      class="px-3 py-1 text-sm text-white bg-purple-600 rounded hover:bg-purple-700"
                      title="LGR Restrictions">
                      LGR
                    </button>
                    <button 
                      @click="openLoanModal(user)"
                      class="px-3 py-1 text-sm text-white bg-emerald-600 rounded hover:bg-emerald-700"
                      title="Issue Loan">
                      Loan
                    </button>
                    <button 
                      @click="openLoanLimitModal(user)"
                      class="px-3 py-1 text-sm text-white bg-indigo-600 rounded hover:bg-indigo-700"
                      title="Set Loan Limit">
                      Limit
                    </button>
                    <button 
                      v-if="user.role !== 'admin'"
                      @click="impersonateUser(user.id)"
                      class="px-3 py-1 text-sm text-white bg-amber-600 rounded hover:bg-amber-700">
                      Login As
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Mobile Card View -->
          <div class="md:hidden divide-y divide-gray-200">
            <div v-for="user in users.data" :key="user.id" class="p-4 hover:bg-gray-50">
              <div class="flex items-start justify-between mb-3">
                <div class="flex-1">
                  <Link
                    :href="route('admin.users.show', user.id)"
                    class="text-base font-medium text-blue-600 hover:text-blue-800"
                  >
                    {{ user.name }}
                  </Link>
                  <div class="text-xs text-gray-500 mt-1">ID: {{ user.id }}</div>
                </div>
                <span :class="['px-2 py-1 text-xs rounded-full', getStatusColor(user.status)]">
                  {{ user.status }}
                </span>
              </div>
              
              <div class="space-y-2 mb-3">
                <div class="text-sm text-gray-900">{{ user.email }}</div>
                <div class="text-sm text-gray-500">{{ user.phone || 'No phone' }}</div>
                <span class="inline-block px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                  {{ user.role }}
                </span>
              </div>
              
              <div class="flex flex-wrap gap-2">
                <button @click="openEditModal(user)"
                  class="flex-1 px-3 py-2 text-sm text-white bg-indigo-600 rounded hover:bg-indigo-700">
                  Edit
                </button>
                <button @click="toggleStatus(user)"
                  class="flex-1 px-3 py-2 text-sm text-white rounded"
                  :class="user.status === 'active' ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'">
                  {{ user.status === 'active' ? 'Suspend' : 'Activate' }}
                </button>
                <button 
                  v-if="user.role !== 'admin'"
                  @click="impersonateUser(user.id)"
                  class="w-full px-3 py-2 text-sm text-white bg-amber-600 rounded hover:bg-amber-700">
                  Login As
                </button>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="users.links && users.links.length > 3" class="px-6 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
              <div class="text-sm text-gray-700">
                Showing {{ users.from }} to {{ users.to }} of {{ users.total }} users
              </div>
              <div class="flex space-x-1">
                <component
                  :is="link.url ? Link : 'span'"
                  v-for="(link, index) in users.links"
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
    
    <!-- LGR Restriction Modal -->
    <LgrRestrictionModal
      :show="showLgrModal"
      :user="selectedUser"
      :global-percentage="40"
      @close="closeLgrModal"
      @updated="closeLgrModal"
    />
    
    <!-- Loan Modal -->
    <LoanModal
      :is-open="showLoanModal"
      :user="selectedUser"
      @close="closeLoanModal"
    />
    
    <!-- Mobile Actions Modal -->
    <div v-if="showMobileActionsModal && selectedUser" class="fixed inset-0 z-50 md:hidden flex items-end">
      <!-- Background Overlay -->
      <div class="absolute inset-0 bg-gray-500 bg-opacity-75" @click="showMobileActionsModal = false"></div>
      
      <!-- Modal Content -->
      <div class="relative w-full bg-white rounded-t-2xl shadow-xl p-4 max-h-[80vh] overflow-y-auto">
          <!-- Header -->
          <div class="mb-3 pb-3 border-b">
            <div class="flex items-center justify-between">
              <h3 class="text-base font-semibold text-gray-900">{{ selectedUser.name }}</h3>
              <button @click="showMobileActionsModal = false" class="text-gray-400 hover:text-gray-600">
                <XCircleIcon class="h-5 w-5" />
              </button>
            </div>
            <p class="text-xs text-gray-500 mt-1">ID: {{ selectedUser.id }}</p>
          </div>
          
          <!-- User Info -->
          <div class="mb-4 space-y-2">
            <div>
              <p class="text-xs text-gray-500">Email</p>
              <p class="text-sm font-medium">{{ selectedUser.email || 'N/A' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500">Phone</p>
              <p class="text-sm font-medium">{{ selectedUser.phone || 'N/A' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500">Role</p>
              <span class="inline-block px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                {{ selectedUser.role }}
              </span>
            </div>
            <div>
              <p class="text-xs text-gray-500">Status</p>
              <span :class="[
                'inline-block px-2 py-1 text-xs rounded-full',
                getStatusColor(selectedUser.status)
              ]">
                {{ selectedUser.status }}
              </span>
            </div>
          </div>
          
          <!-- Actions -->
          <div class="space-y-2">
            <button
              @click="openEditModal(selectedUser); showMobileActionsModal = false"
              class="w-full px-4 py-3 text-left text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 flex items-center justify-between"
            >
              <span>Edit User</span>
              <PencilIcon class="h-5 w-5" />
            </button>
            
            <button
              @click="toggleStatus(selectedUser); showMobileActionsModal = false"
              :class="[
                'w-full px-4 py-3 text-left text-sm font-medium text-white rounded-lg flex items-center justify-between',
                selectedUser.status === 'active' ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'
              ]"
            >
              <span>{{ selectedUser.status === 'active' ? 'Suspend User' : 'Activate User' }}</span>
              <CheckCircleIcon v-if="selectedUser.status !== 'active'" class="h-5 w-5" />
              <XCircleIcon v-else class="h-5 w-5" />
            </button>
            
            <button
              @click="openLgrModal(selectedUser); showMobileActionsModal = false"
              class="w-full px-4 py-3 text-left text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 flex items-center justify-between"
            >
              <span>LGR Restrictions</span>
              <ShieldExclamationIcon class="h-5 w-5" />
            </button>
            
            <button
              @click="openLoanModal(selectedUser); showMobileActionsModal = false"
              class="w-full px-4 py-3 text-left text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 flex items-center justify-between"
            >
              <span>Issue Loan</span>
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </button>
            
            <button
              v-if="selectedUser.role !== 'admin'"
              @click="impersonateUser(selectedUser.id); showMobileActionsModal = false"
              class="w-full px-4 py-3 text-left text-sm font-medium text-white bg-amber-600 rounded-lg hover:bg-amber-700 flex items-center justify-between"
            >
              <span>Login As User</span>
              <UserIcon class="h-5 w-5" />
            </button>
          </div>
        </div>
    </div>
    
    <!-- Loan Limit Modal -->
    <LoanLimitModal
      :show="showLoanLimitModal"
      :user="selectedUser"
      @close="closeLoanLimitModal"
      @updated="closeLoanLimitModal"
    />
  </AdminLayout>
</template>
