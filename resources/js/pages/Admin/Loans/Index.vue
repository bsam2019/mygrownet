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
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import axios from 'axios';

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

onMounted(() => {
  loadLoans();
});
</script>
