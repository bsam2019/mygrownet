<template>
  <AdminLayout :breadcrumbs="[
    { name: 'Dashboard', href: route('admin.dashboard') },
    { name: 'Withdrawals', href: route('admin.withdrawals.index') }
  ]">
    <div class="p-4">
      <div class="bg-white rounded-lg shadow">
        <div class="flex justify-between items-center p-4 border-b">
          <h3 class="text-2xl font-semibold text-gray-900">Withdrawal Management</h3>
        </div>
        <div class="p-4">
          <!-- Statistics Cards -->
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
              <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                  </svg>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-500">Total Withdrawals</p>
                  <p class="text-lg font-semibold text-gray-900">{{ formatKwacha(totalWithdrawals) }}</p>
                  <p class="text-xs text-gray-500">{{ props.withdrawals.data.length }} requests</p>
                </div>
              </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
              <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-500">Pending Withdrawals</p>
                  <p class="text-lg font-semibold text-gray-900">{{ formatKwacha(pendingWithdrawals) }}</p>
                  <p class="text-xs text-gray-500">{{ pendingCount }} requests</p>
                </div>
              </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
              <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-500">Completed Today</p>
                  <p class="text-lg font-semibold text-gray-900">{{ formatKwacha(completedToday) }}</p>
                  <p class="text-xs text-gray-500">{{ completedTodayCount }} requests</p>
                </div>
              </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
              <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-500">Rejected Today</p>
                  <p class="text-lg font-semibold text-gray-900">{{ formatKwacha(rejectedToday) }}</p>
                  <p class="text-xs text-gray-500">{{ rejectedTodayCount }} requests</p>
                </div>
              </div>
            </div>
          </div>

          <div class="mb-4 space-y-4 sm:space-y-0 sm:flex sm:items-center sm:justify-between">
            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4">
              <select v-model="statusFilter" class="w-full sm:w-auto rounded-md border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="completed">Completed</option>
                <option value="rejected">Rejected</option>
              </select>
              
              <div class="flex space-x-2">
                <input 
                  type="date" 
                  v-model="dateFrom"
                  class="rounded-md border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500"
                  placeholder="From Date"
                >
                <input 
                  type="date" 
                  v-model="dateTo"
                  class="rounded-md border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500"
                  placeholder="To Date"
                >
              </div>
            </div>
            
            <div class="relative">
              <input
                type="text"
                v-model="searchQuery"
                placeholder="Search by user name..."
                class="w-full sm:w-64 rounded-md border-gray-300 pl-10 text-sm focus:ring-blue-500 focus:border-blue-500"
              >
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
              </div>
            </div>
          </div>

          <!-- Mobile View -->
          <div class="block lg:hidden space-y-4">
            <div v-for="withdrawal in filteredWithdrawals" :key="withdrawal.id" class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
              <div class="flex items-start justify-between mb-3">
                <div class="flex items-center">
                  <img class="h-10 w-10 rounded-full" :src="withdrawal.user.profile_photo_url" :alt="withdrawal.user.name">
                  <div class="ml-3">
                    <div class="text-sm font-medium text-gray-900">{{ withdrawal.user.name }}</div>
                    <div class="text-xs text-gray-500">{{ withdrawal.user.email }}</div>
                  </div>
                </div>
                <span :class="[
                  'px-2 py-1 text-xs font-medium rounded-full',
                  withdrawal.status === 'approved' || withdrawal.status === 'completed' ? 'bg-green-100 text-green-800' : 
                  withdrawal.status === 'rejected' ? 'bg-red-100 text-red-800' : 
                  'bg-yellow-100 text-yellow-800'
                ]">
                  {{ withdrawal.status === 'completed' || withdrawal.status === 'approved' ? 'Approved' : withdrawal.status.charAt(0).toUpperCase() + withdrawal.status.slice(1) }}
                </span>
              </div>
              
              <div class="space-y-2 mb-3">
                <div class="flex justify-between">
                  <span class="text-xs text-gray-500">Amount:</span>
                  <span class="text-sm font-medium text-gray-900">{{ formatKwacha(withdrawal.amount) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-xs text-gray-500">Balance:</span>
                  <span class="text-sm text-gray-700">{{ formatKwacha(withdrawal.user.balance) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-xs text-gray-500">Mobile Money:</span>
                  <span class="text-sm text-gray-700">{{ withdrawal.wallet_address || 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-xs text-gray-500">Date:</span>
                  <span class="text-sm text-gray-700">{{ formatDate(withdrawal.created_at) }}</span>
                </div>
              </div>
              
              <div v-if="withdrawal.reason" class="mb-3 p-2 bg-red-50 rounded text-xs text-red-700">
                <span class="font-medium">Reason:</span> {{ withdrawal.reason }}
              </div>
              
              <div class="flex flex-wrap gap-2">
                <button
                  v-if="withdrawal.status === 'pending'"
                  @click="approveWithdrawal(withdrawal.id)"
                  class="flex-1 inline-flex items-center justify-center px-3 py-2 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                  Approve
                </button>
                <button
                  v-if="withdrawal.status === 'pending'"
                  @click="rejectWithdrawal(withdrawal.id)"
                  class="flex-1 inline-flex items-center justify-center px-3 py-2 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                      Approve
                    </button>
                    <button
                      v-if="withdrawal.status === 'pending'"
                      @click="rejectWithdrawal(withdrawal.id)"
                      class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                      </svg>
                      Reject
                    </button>
                    <button
                      @click="viewDetails(withdrawal.id)"
                      class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                      </svg>
                      View
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import Swal from 'sweetalert2'
import { formatKwacha, formatDate, formatTime } from '@/utils/format'

const page = usePage()

const props = defineProps({
  withdrawals: Object
})

const statusFilter = ref('')
const dateFrom = ref('')
const dateTo = ref('')
const searchQuery = ref('')

// Computed properties for statistics
const totalWithdrawals = computed(() => {
  return props.withdrawals.data.reduce((sum, w) => sum + parseFloat(w.amount), 0)
})

const pendingWithdrawals = computed(() => {
  return props.withdrawals.data
    .filter(w => w.status === 'pending')
    .reduce((sum, w) => sum + parseFloat(w.amount), 0)
})

const pendingCount = computed(() => {
  return props.withdrawals.data.filter(w => w.status === 'pending').length
})

const completedToday = computed(() => {
  const today = new Date().toISOString().split('T')[0]
  return props.withdrawals.data
    .filter(w => w.status === 'completed' && w.created_at.startsWith(today))
    .reduce((sum, w) => sum + parseFloat(w.amount), 0)
})

const completedTodayCount = computed(() => {
  const today = new Date().toISOString().split('T')[0]
  return props.withdrawals.data
    .filter(w => w.status === 'completed' && w.created_at.startsWith(today))
    .length
})

const rejectedToday = computed(() => {
  const today = new Date().toISOString().split('T')[0]
  return props.withdrawals.data
    .filter(w => w.status === 'rejected' && w.created_at.startsWith(today))
    .reduce((sum, w) => sum + parseFloat(w.amount), 0)
})

const rejectedTodayCount = computed(() => {
  const today = new Date().toISOString().split('T')[0]
  return props.withdrawals.data
    .filter(w => w.status === 'rejected' && w.created_at.startsWith(today))
    .length
})

const filteredWithdrawals = computed(() => {
  let filtered = props.withdrawals.data

  if (statusFilter.value) {
    filtered = filtered.filter(w => w.status === statusFilter.value)
  }

  if (dateFrom.value) {
    filtered = filtered.filter(w => w.created_at >= dateFrom.value)
  }

  if (dateTo.value) {
    filtered = filtered.filter(w => w.created_at <= dateTo.value + ' 23:59:59')
  }

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(w => 
      w.user.name.toLowerCase().includes(query) ||
      w.user.email.toLowerCase().includes(query)
    )
  }

  return filtered
})

const viewDetails = (id) => {
  const withdrawal = props.withdrawals.data.find(w => w.id === id)
  Swal.fire({
    title: 'Withdrawal Details',
    html: `
      <div class="text-left">
        <div class="mb-4">
          <h3 class="text-lg font-medium text-gray-900 mb-2">User Information</h3>
          <p class="text-sm text-gray-600">Name: ${withdrawal.user.name}</p>
          <p class="text-sm text-gray-600">Email: ${withdrawal.user.email}</p>
          <p class="text-sm text-gray-600">Investment Tier: ${withdrawal.user.investment_tier}</p>
          <p class="text-sm text-gray-600">Investment Amount: ${formatKwacha(withdrawal.user.investment_amount)}</p>
        </div>
        <div class="mb-4">
          <h3 class="text-lg font-medium text-gray-900 mb-2">Withdrawal Information</h3>
          <p class="text-sm text-gray-600">Amount: ${formatKwacha(withdrawal.amount)}</p>
          <p class="text-sm text-gray-600">Method: Mobile Money</p>
          <p class="text-sm text-gray-600">Account: ${withdrawal.wallet_address || 'N/A'}</p>
          <p class="text-sm text-gray-600">Status: ${withdrawal.status.charAt(0).toUpperCase() + withdrawal.status.slice(1)}</p>
          <p class="text-sm text-gray-600">Requested: ${formatDate(withdrawal.created_at)} ${formatTime(withdrawal.created_at)}</p>
          ${withdrawal.reason ? `<p class="text-sm text-gray-600">Rejection Reason: ${withdrawal.reason}</p>` : ''}
        </div>
        <div class="mb-4">
          <h3 class="text-lg font-medium text-gray-900 mb-2">Account Balance</h3>
          <p class="text-sm text-gray-600">Current Balance: ${formatKwacha(withdrawal.user.balance)}</p>
          <p class="text-sm text-gray-600">After Withdrawal: ${formatKwacha(withdrawal.user.balance - withdrawal.amount)}</p>
        </div>
      </div>
    `,
    width: '600px',
    showCloseButton: true,
    showConfirmButton: false,
    customClass: {
      container: 'withdrawal-details-modal'
    }
  })
}

const approveWithdrawal = (id) => {
  const withdrawal = props.withdrawals.data.find(w => w.id === id)
  Swal.fire({
    title: 'Approve Withdrawal',
    html: `
      <div class="text-left">
        <p class="mb-4">Are you sure you want to approve this withdrawal request?</p>
        <div class="bg-gray-50 p-4 rounded-lg">
          <p class="text-sm text-gray-600">User: ${withdrawal.user.name}</p>
          <p class="text-sm text-gray-600">Amount: ${formatKwacha(withdrawal.amount)}</p>
          <p class="text-sm text-gray-600">Current Balance: ${formatKwacha(withdrawal.user.balance)}</p>
          <p class="text-sm text-gray-600">After Withdrawal: ${formatKwacha(withdrawal.user.balance - withdrawal.amount)}</p>
        </div>
      </div>
    `,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, approve it!',
    customClass: {
      container: 'approve-withdrawal-modal'
    }
  }).then((result) => {
    if (result.isConfirmed) {
      router.patch(route('admin.withdrawals.approve', id), {}, {
        preserveScroll: true,
        onSuccess: () => {
          if (page.props.flash.success) {
            Swal.fire({
              icon: 'success',
              title: 'Success',
              text: page.props.flash.success,
              timer: 2000,
              showConfirmButton: false
            });
          }
        },
        onError: (errors) => {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: page.props.flash.error || 'An error occurred while processing the withdrawal'
          });
        }
      });
    }
  });
}

const rejectWithdrawal = (id) => {
  const withdrawal = props.withdrawals.data.find(w => w.id === id)
  Swal.fire({
    title: 'Reject Withdrawal',
    html: `
      <div class="text-left mb-4">
        <p class="mb-4">Please provide a reason for rejecting this withdrawal request:</p>
        <div class="bg-gray-50 p-4 rounded-lg mb-4">
          <p class="text-sm text-gray-600">User: ${withdrawal.user.name}</p>
          <p class="text-sm text-gray-600">Amount: ${formatKwacha(withdrawal.amount)}</p>
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Reason for Rejection</label>
          <textarea id="rejection-reason" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500" rows="3" placeholder="Enter reason for rejection"></textarea>
        </div>
      </div>
    `,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, reject it!',
    preConfirm: () => {
      const reason = document.getElementById('rejection-reason').value;
      if (!reason) {
        Swal.showValidationMessage('Please provide a reason for rejection');
        return false;
      }
      return reason;
    },
    customClass: {
      container: 'reject-withdrawal-modal'
    }
  }).then((result) => {
    if (result.isConfirmed) {
      router.patch(route('admin.withdrawals.reject', id), {
        reason: result.value
      }, {
        preserveScroll: true,
        onSuccess: () => {
          if (page.props.flash.success) {
            Swal.fire({
              icon: 'success',
              title: 'Success',
              text: page.props.flash.success,
              timer: 2000,
              showConfirmButton: false
            });
          }
        },
        onError: (errors) => {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: page.props.flash.error || 'An error occurred while processing the withdrawal'
          });
        }
      });
    }
  });
}

// Watch for flash messages
watch(() => page.props.flash, (flash) => {
  if (flash?.success) {
    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: flash.success,
      timer: 2000,
      showConfirmButton: false
    })
  }
  if (flash?.error) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: flash.error
    })
  }
}, { deep: true })
</script>
