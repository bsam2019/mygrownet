<script setup>
import { ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import Swal from 'sweetalert2'

const page = usePage()
const props = defineProps({
  user: {
    type: Object,
    required: true
  },
  profile: Object
})

const form = ref({
  name: props.user?.name || '',
  email: props.user?.email || '',
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
  current_password: '',
  password: '',
  password_confirmation: ''
})

const kycForm = ref({
  document_type: '',
  document: null
})

const showPasswordModal = ref(false)
const showKycModal = ref(false)

const updateProfile = () => {
  router.post(route('admin.profile.update'), form.value, {
    preserveScroll: true,
    onSuccess: () => {
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'Profile updated successfully',
        timer: 2000,
        showConfirmButton: false
      })
    }
  })
}

const updatePassword = () => {
  router.post(route('admin.profile.password.update'), passwordForm.value, {
    preserveScroll: true,
    onSuccess: () => {
      showPasswordModal.value = false
      passwordForm.value = {
        current_password: '',
        password: '',
        password_confirmation: ''
      }
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'Password updated successfully',
        timer: 2000,
        showConfirmButton: false
      })
    }
  })
}

const handleKycUpload = () => {
  if (!kycForm.value.document) return

  const formData = new FormData()
  formData.append('document_type', kycForm.value.document_type)
  formData.append('document', kycForm.value.document)

  router.post(route('admin.profile.kyc.upload'), formData, {
    preserveScroll: true,
    onSuccess: () => {
      showKycModal.value = false
      kycForm.value = {
        document_type: '',
        document: null
      }
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'KYC document uploaded successfully',
        timer: 2000,
        showConfirmButton: false
      })
    }
  })
}

const handleAvatarChange = (e) => {
  const file = e.target.files[0]
  if (file) {
    const formData = new FormData()
    formData.append('avatar', file)

    router.post(route('admin.profile.avatar.upload'), formData, {
      preserveScroll: true,
      onSuccess: () => {
        Swal.fire({
          icon: 'success',
          title: 'Success',
          text: 'Avatar uploaded successfully',
          timer: 2000,
          showConfirmButton: false
        })
      }
    })
  }
}

const formatDate = (date) => {
  return date ? new Date(date).toLocaleDateString() : '-'
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(value)
}
</script>

<template>
  <AdminLayout :breadcrumbs="[
    { name: 'Dashboard', href: route('admin.dashboard') },
    { name: 'Users', href: route('admin.users.index') },
    { name: 'Profile', href: '#' }
  ]">
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
                <label class="absolute bottom-0 right-0 bg-white rounded-full p-2 shadow-lg cursor-pointer hover:bg-gray-50">
                  <input
                    type="file"
                    class="hidden"
                    accept="image/*"
                    @change="handleAvatarChange"
                  >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                  </svg>
                </label>
              </div>
              <div class="flex-1">
                <h2 class="text-2xl font-bold text-gray-900">{{ user.name }}</h2>
                <p class="text-gray-500">{{ user.email }}</p>
                <div class="mt-2 flex items-center space-x-4">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                    :class="{
                      'bg-green-100 text-green-800': user.status === 'active',
                      'bg-yellow-100 text-yellow-800': user.status === 'pending',
                      'bg-red-100 text-red-800': user.status === 'suspended'
                    }"
                  >
                    {{ user.status }}
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
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Email Address</label>
                    <input
                      type="email"
                      v-model="form.email"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                    <input
                      type="tel"
                      v-model="form.phone_number"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Address</label>
                    <input
                      type="text"
                      v-model="form.address"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">City</label>
                    <input
                      type="text"
                      v-model="form.city"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Country</label>
                    <input
                      type="text"
                      v-model="form.country"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
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
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
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
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                          >
                        </div>
                        <div>
                          <label class="block text-sm font-medium text-gray-700">Account Number</label>
                          <input
                            type="text"
                            v-model="form.payment_details.bank_account"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                          >
                        </div>
                        <div>
                          <label class="block text-sm font-medium text-gray-700">Branch</label>
                          <input
                            type="text"
                            v-model="form.payment_details.bank_branch"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
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
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
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
                  <span :class="[
                    'px-2 py-1 text-xs rounded-full',
                    {
                      'bg-green-100 text-green-800': profile?.kyc_status === 'verified',
                      'bg-yellow-100 text-yellow-800': profile?.kyc_status === 'pending',
                      'bg-red-100 text-red-800': profile?.kyc_status === 'rejected'
                    }
                  ]">
                    {{ profile?.kyc_status || 'Not Started' }}
                  </span>
                </div>
                <button
                  @click="showKycModal = true"
                  class="w-full px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                  {{ profile?.kyc_status === 'verified' ? 'View Documents' : 'Upload Documents' }}
                </button>
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
                      @click="showPasswordModal = true"
                      class="text-sm text-blue-600 hover:text-blue-800"
                    >
                      Change
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
      <!-- Modal implementation -->
    </div>

    <!-- KYC Modal -->
    <div v-if="showKycModal" class="fixed inset-0 z-50 overflow-y-auto">
      <!-- Modal implementation -->
    </div>
  </AdminLayout>
</template>
