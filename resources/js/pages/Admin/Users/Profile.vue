<script setup lang="ts">
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import Swal from 'sweetalert2'

const page = usePage()
const props = defineProps<{
  user: {
    id: number
    name: string
    email: string
    status: string
    balance: number
    total_earnings: number
    role: string
    created_at: string
    last_login_at: string | null
  }
  profile?: {
    phone_number?: string
    address?: string
    city?: string
    country?: string
    preferred_payment_method?: string
    payment_details?: {
      bank_name?: string
      bank_account?: string
      bank_branch?: string
      mobile_money_number?: string
    }
    avatar?: string
    kyc_status?: string
  } | null
  roles: Array<{ id: number; name: string }>
}>()

const form = ref({
  name: props.user?.name || '',
  email: props.user?.email || '',
  status: props.user?.status || 'active',
  role: props.user?.role || '',
  phone_number: props.profile?.phone_number || '',
  address: props.profile?.address || '',
  city: props.profile?.city || '',
  country: props.profile?.country || '',
  preferred_payment_method: props.profile?.preferred_payment_method || '',
  payment_details: props.profile?.payment_details || {
    bank_name: '',
    bank_account: '',
    bank_branch: '',
    mobile_money_number: ''
  }
})

const passwordForm = ref({
  password: '',
  password_confirmation: ''
})

const showPasswordModal = ref(false)
const showPassword = ref(false)
const showPasswordConfirmation = ref(false)

const openPasswordModal = () => {
  console.log('Opening password modal')
  showPasswordModal.value = true
}

const closePasswordModal = () => {
  showPasswordModal.value = false
  passwordForm.value = {
    password: '',
    password_confirmation: ''
  }
  showPassword.value = false
  showPasswordConfirmation.value = false
}

const updateProfile = () => {
  router.put(route('admin.users.update', props.user.id), form.value, {
    preserveScroll: true,
    onSuccess: () => {
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'User profile updated successfully',
        timer: 2000,
        showConfirmButton: false
      })
    },
    onError: (errors) => {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: Object.values(errors).flat().join(', '),
      })
    }
  })
}

const updatePassword = () => {
  console.log('Updating password...', passwordForm.value)
  
  if (passwordForm.value.password !== passwordForm.value.password_confirmation) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Passwords do not match',
    })
    return
  }

  if (passwordForm.value.password.length < 8) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Password must be at least 8 characters',
    })
    return
  }

  router.post(route('admin.users.update-password', props.user.id), passwordForm.value, {
    preserveScroll: true,
    onSuccess: () => {
      console.log('Password updated successfully')
      closePasswordModal()
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'Password updated successfully',
        timer: 2000,
        showConfirmButton: false
      })
    },
    onError: (errors) => {
      console.error('Password update error:', errors)
      const errorMessage = typeof errors === 'object' 
        ? Object.values(errors).flat().join(', ')
        : 'Failed to update password'
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: errorMessage,
      })
    }
  })
}

const formatDate = (date: string | null) => {
  return date ? new Date(date).toLocaleDateString() : '-'
}

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(value)
}

const statusClass = computed(() => {
  const statusMap: Record<string, string> = {
    'active': 'bg-green-100 text-green-800',
    'pending': 'bg-yellow-100 text-yellow-800',
    'suspended': 'bg-red-100 text-red-800',
    'inactive': 'bg-gray-100 text-gray-800'
  }
  return statusMap[props.user.status] || 'bg-gray-100 text-gray-800'
})

const kycStatusClass = computed(() => {
  const statusMap: Record<string, string> = {
    'verified': 'bg-green-100 text-green-800',
    'pending': 'bg-yellow-100 text-yellow-800',
    'rejected': 'bg-red-100 text-red-800',
    'not_started': 'bg-gray-100 text-gray-800'
  }
  return statusMap[props.profile?.kyc_status || 'not_started'] || 'bg-gray-100 text-gray-800'
})
</script>

<template>
  <AdminLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-bold text-gray-900">User Profile</h2>
          <p class="mt-1 text-sm text-gray-500">View and edit user details</p>
        </div>
        <a
          :href="route('admin.users.index')"
          class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
        >
          Back to Users
        </a>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- User Overview Card -->
        <div class="mb-6 bg-white rounded-lg shadow">
          <div class="p-6">
            <div class="flex items-center space-x-6">
              <div class="relative">
                <img
                  :src="profile?.avatar || '/images/default-avatar.png'"
                  class="w-24 h-24 rounded-full object-cover border-4 border-white shadow"
                  alt="Profile Avatar"
                >
              </div>
              <div class="flex-1">
                <h2 class="text-2xl font-bold text-gray-900">{{ user.name }}</h2>
                <p class="text-gray-500">{{ user.email }}</p>
                <div class="mt-2 flex items-center space-x-4">
                  <span 
                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                    :class="statusClass"
                  >
                    {{ user.status }}
                  </span>
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                    {{ user.role }}
                  </span>
                  <span class="text-sm text-gray-500">Joined {{ formatDate(user.created_at) }}</span>
                </div>
              </div>
              <div class="text-right">
                <div class="text-sm text-gray-500">Balance</div>
                <div class="text-2xl font-bold text-gray-900">{{ formatCurrency(user.balance) }}</div>
                <div class="text-sm text-gray-500">Total Earnings: {{ formatCurrency(user.total_earnings) }}</div>
              </div>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Profile Form -->
          <div class="lg:col-span-2 bg-white rounded-lg shadow">
            <div class="p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-6">Profile Information</h3>
              <form @submit.prevent="updateProfile" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Full Name</label>
                    <input
                      type="text"
                      v-model="form.name"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:outline-none"
                      required
                    >
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Email Address</label>
                    <input
                      type="email"
                      v-model="form.email"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:outline-none"
                      required
                    >
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <select
                      v-model="form.status"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:outline-none"
                    >
                      <option value="active">Active</option>
                      <option value="inactive">Inactive</option>
                      <option value="suspended">Suspended</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Role</label>
                    <select
                      v-model="form.role"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:outline-none"
                    >
                      <option value="">Select role</option>
                      <option v-for="role in roles" :key="role.id" :value="role.name">
                        {{ role.name }}
                      </option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                    <input
                      type="tel"
                      v-model="form.phone_number"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:outline-none"
                    >
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Address</label>
                    <input
                      type="text"
                      v-model="form.address"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:outline-none"
                    >
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">City</label>
                    <input
                      type="text"
                      v-model="form.city"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:outline-none"
                    >
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Country</label>
                    <input
                      type="text"
                      v-model="form.country"
                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:outline-none"
                    >
                  </div>
                </div>

                <div class="border-t pt-6">
                  <h4 class="text-lg font-medium text-gray-900 mb-4">Payment Information</h4>
                  <div class="space-y-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Preferred Payment Method</label>
                      <select
                        v-model="form.preferred_payment_method"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:outline-none"
                      >
                        <option value="">Select payment method</option>
                        <option value="bank">Bank Transfer</option>
                        <option value="mobile_money">Mobile Money</option>
                      </select>
                    </div>

                    <template v-if="form.preferred_payment_method === 'bank'">
                      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                          <label class="block text-sm font-medium text-gray-700">Bank Name</label>
                          <input
                            type="text"
                            v-model="form.payment_details.bank_name"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:outline-none"
                          >
                        </div>
                        <div>
                          <label class="block text-sm font-medium text-gray-700">Account Number</label>
                          <input
                            type="text"
                            v-model="form.payment_details.bank_account"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:outline-none"
                          >
                        </div>
                        <div class="md:col-span-2">
                          <label class="block text-sm font-medium text-gray-700">Branch</label>
                          <input
                            type="text"
                            v-model="form.payment_details.bank_branch"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:outline-none"
                          >
                        </div>
                      </div>
                    </template>

                    <template v-else-if="form.preferred_payment_method === 'mobile_money'">
                      <div>
                        <label class="block text-sm font-medium text-gray-700">Mobile Money Number</label>
                        <input
                          type="tel"
                          v-model="form.payment_details.mobile_money_number"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:outline-none"
                        >
                      </div>
                    </template>
                  </div>
                </div>

                <div class="flex justify-end">
                  <button
                    type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                  >
                    Save Changes
                  </button>
                </div>
              </form>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="space-y-6">
            <!-- KYC Status -->
            <div class="bg-white rounded-lg shadow">
              <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">KYC Status</h3>
                <div class="flex items-center justify-between mb-4">
                  <span class="text-sm text-gray-500">Verification Status</span>
                  <span 
                    class="px-2 py-1 text-xs rounded-full"
                    :class="kycStatusClass"
                  >
                    {{ profile?.kyc_status || 'Not Started' }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Security -->
            <div class="bg-white rounded-lg shadow">
              <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Security</h3>
                <div class="space-y-4">
                  <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Password</span>
                    <button
                      type="button"
                      @click.prevent="openPasswordModal"
                      class="text-sm text-blue-600 hover:text-blue-800 font-medium cursor-pointer hover:underline"
                    >
                      Change Password
                    </button>
                  </div>
                  <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Last Login</span>
                    <span class="text-sm text-gray-900">{{ formatDate(user.last_login_at) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Password Change Modal -->
    <div v-if="showPasswordModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closePasswordModal"></div>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <form @submit.prevent="updatePassword">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div class="sm:flex sm:items-start">
                <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                  <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                    Change User Password
                  </h3>
                  <div class="mt-4 space-y-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700">New Password</label>
                      <div class="relative mt-1">
                        <input
                          :type="showPassword ? 'text' : 'password'"
                          v-model="passwordForm.password"
                          class="block w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:outline-none"
                          required
                          minlength="8"
                          placeholder="Enter new password"
                        >
                        <button
                          type="button"
                          @click="showPassword = !showPassword"
                          class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                        >
                          <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                          </svg>
                          <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                          </svg>
                        </button>
                      </div>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                      <div class="relative mt-1">
                        <input
                          :type="showPasswordConfirmation ? 'text' : 'password'"
                          v-model="passwordForm.password_confirmation"
                          class="block w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:outline-none"
                          required
                          minlength="8"
                          placeholder="Confirm new password"
                        >
                        <button
                          type="button"
                          @click="showPasswordConfirmation = !showPasswordConfirmation"
                          class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                        >
                          <svg v-if="!showPasswordConfirmation" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                          </svg>
                          <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                          </svg>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
              <button
                type="submit"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
              >
                Update Password
              </button>
              <button
                type="button"
                @click="closePasswordModal"
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
              >
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
