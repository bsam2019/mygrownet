<template>
  <Head title="Loan Management" />
  
  <AdminLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-gray-900">Loan Management</h1>
          <p class="mt-1 text-sm text-gray-600">
            View and manage member loans
          </p>
        </div>

        <!-- Pending Applications -->
        <div v-if="pendingApplications && pendingApplications.length > 0" class="mb-6 bg-white rounded-lg shadow overflow-hidden">
          <div class="bg-amber-50 px-6 py-4 border-b border-amber-200">
            <div class="flex items-center justify-between">
              <div>
                <h2 class="text-lg font-semibold text-amber-900">Pending Loan Applications</h2>
                <p class="text-sm text-amber-700 mt-1">{{ pendingApplications.length }} application(s) awaiting review</p>
              </div>
              <span class="bg-amber-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
                {{ pendingApplications.length }}
              </span>
            </div>
          </div>
          
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Member</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Repayment Plan</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Purpose</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Current Loan</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="app in pendingApplications" :key="app.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatDate(app.created_at) }}
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900">{{ app.user_name }}</div>
                    <div class="text-sm text-gray-500">{{ app.user_email }}</div>
                    <div class="text-xs text-gray-400">{{ app.user_phone }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-bold text-gray-900">K{{ formatNumber(app.amount) }}</div>
                    <div class="text-xs text-gray-500">Limit: K{{ formatNumber(app.loan_limit) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                    {{ app.repayment_plan.replace('_', ' ') }}
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-700 max-w-xs">
                    <div class="truncate" :title="app.purpose">
                      {{ app.purpose }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <span v-if="app.loan_balance > 0" class="text-red-600 font-medium">
                      K{{ formatNumber(app.loan_balance) }}
                    </span>
                    <span v-else class="text-green-600">
                      None
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <div class="flex gap-2">
                      <button
                        @click="approveApplication(app)"
                        class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 font-medium"
                      >
                        Approve
                      </button>
                      <button
                        @click="rejectApplication(app)"
                        class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 font-medium"
                      >
                        Reject
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-emerald-100 rounded-lg p-3">
                <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Active Loans</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.active_loans }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Outstanding</p>
                <p class="text-2xl font-bold text-gray-900">K{{ formatNumber(stats.total_outstanding) }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-amber-100 rounded-lg p-3">
                <svg class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Issued</p>
                <p class="text-2xl font-bold text-gray-900">K{{ formatNumber(stats.total_issued) }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Repaid</p>
                <p class="text-2xl font-bold text-gray-900">K{{ formatNumber(stats.total_repaid) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Loans Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Members with Outstanding Loans</h2>
          </div>
          
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loan Balance</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Issued</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Repaid</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issued Date</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issued By</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="loan in loans" :key="loan.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ loan.name }}</div>
                      <div class="text-sm text-gray-500">{{ loan.email }}</div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-sm font-semibold text-amber-600">K{{ formatNumber(loan.loan_balance) }}</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    K{{ formatNumber(loan.total_issued) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                    K{{ formatNumber(loan.total_repaid) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                        <div 
                          class="bg-emerald-600 h-2 rounded-full"
                          :style="{ width: `${loan.repayment_progress}%` }"
                        ></div>
                      </div>
                      <span class="text-sm text-gray-600">{{ loan.repayment_progress.toFixed(0) }}%</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatDate(loan.issued_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ loan.issued_by }}
                  </td>
                </tr>
                <tr v-if="loans.length === 0">
                  <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                    No outstanding loans
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
import { ref, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({
  pendingApplications: {
    type: Array,
    default: () => []
  }
});

const loans = ref([]);
const stats = ref({
  active_loans: 0,
  total_outstanding: 0,
  total_issued: 0,
  total_repaid: 0,
});

const formatNumber = (value) => {
  return parseFloat(value || 0).toFixed(2);
};

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const loadLoans = async () => {
  try {
    const response = await axios.get(route('admin.loans.members'));
    loans.value = response.data;
    
    // Calculate stats
    stats.value = {
      active_loans: loans.value.length,
      total_outstanding: loans.value.reduce((sum, loan) => sum + parseFloat(loan.loan_balance), 0),
      total_issued: loans.value.reduce((sum, loan) => sum + parseFloat(loan.total_issued), 0),
      total_repaid: loans.value.reduce((sum, loan) => sum + parseFloat(loan.total_repaid), 0),
    };
  } catch (error) {
    console.error('Failed to load loans:', error);
  }
};

const approveApplication = async (application) => {
  const result = await Swal.fire({
    title: 'Approve Loan Application?',
    html: `
      <div class="text-left">
        <p class="mb-2"><strong>Member:</strong> ${application.user_name}</p>
        <p class="mb-2"><strong>Amount:</strong> K${parseFloat(application.amount).toFixed(2)}</p>
        <p class="mb-2"><strong>Purpose:</strong> ${application.purpose}</p>
        <p class="text-sm text-gray-600 mt-3">This will issue the loan immediately and credit the member's wallet.</p>
      </div>
    `,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#10b981',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Yes, Approve',
    cancelButtonText: 'Cancel'
  });

  if (result.isConfirmed) {
    router.post(route('admin.loans.applications.approve', application.id), {}, {
      preserveScroll: true,
      onSuccess: () => {
        Swal.fire('Approved!', 'Loan has been issued to the member.', 'success');
        // Reload both pending applications and loans list
        loadLoans();
        router.reload({ only: ['pendingApplications'] });
      },
      onError: (errors) => {
        Swal.fire('Error', errors.error || 'Failed to approve application', 'error');
      }
    });
  }
};

const rejectApplication = async (application) => {
  const { value: reason } = await Swal.fire({
    title: 'Reject Loan Application',
    html: `
      <div class="text-left mb-3">
        <p class="mb-2"><strong>Member:</strong> ${application.user_name}</p>
        <p class="mb-2"><strong>Amount:</strong> K${parseFloat(application.amount).toFixed(2)}</p>
      </div>
    `,
    input: 'textarea',
    inputLabel: 'Rejection Reason',
    inputPlaceholder: 'Explain why this application is being rejected...',
    inputAttributes: {
      'aria-label': 'Rejection reason',
      'rows': 3
    },
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Reject Application',
    cancelButtonText: 'Cancel',
    inputValidator: (value) => {
      if (!value || value.length < 10) {
        return 'Please provide a detailed reason (minimum 10 characters)';
      }
    }
  });

  if (reason) {
    router.post(route('admin.loans.applications.reject', application.id), {
      rejection_reason: reason
    }, {
      preserveScroll: true,
      onSuccess: () => {
        Swal.fire('Rejected', 'Application has been rejected and member notified.', 'success');
        // Reload pending applications list
        router.reload({ only: ['pendingApplications'] });
      },
      onError: (errors) => {
        Swal.fire('Error', errors.error || 'Failed to reject application', 'error');
      }
    });
  }
};

onMounted(() => {
  loadLoans();
});
</script>
