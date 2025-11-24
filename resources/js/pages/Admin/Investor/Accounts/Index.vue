<template>
  <AdminLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <div class="flex justify-between items-center">
            <div>
              <h1 class="text-2xl font-bold text-gray-900">Investor Accounts</h1>
              <p class="mt-1 text-sm text-gray-500">Manage investor investments and track conversions</p>
            </div>
            <Link
              :href="route('admin.investor-accounts.create')"
              class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            >
              <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
              Record Investment
            </Link>
          </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                <UsersIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Investors</p>
                <p class="text-2xl font-bold text-gray-900">{{ investorCount }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                <BanknotesIcon class="h-6 w-6 text-green-600" aria-hidden="true" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Invested</p>
                <p class="text-2xl font-bold text-gray-900">K{{ formatNumber(totalInvested) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Accounts Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Investor</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Equity</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="account in accounts" :key="account.id">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div>
                    <div class="text-sm font-medium text-gray-900">{{ account.name }}</div>
                    <div class="text-sm text-gray-500">{{ account.email }}</div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  K{{ formatNumber(account.investment_amount) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ account.equity_percentage }}%
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(account.investment_date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusClass(account.status)">
                    {{ account.status_label }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex justify-end gap-2">
                    <button
                      @click="showAccessCode(account)"
                      class="text-purple-600 hover:text-purple-900"
                      title="View Access Code"
                    >
                      Key
                    </button>
                    <Link
                      :href="route('admin.investor-accounts.edit', account.id)"
                      class="text-blue-600 hover:text-blue-900"
                    >
                      Edit
                    </Link>
                    <button
                      v-if="account.status === 'ciu'"
                      @click="convertToShareholder(account.id)"
                      class="text-green-600 hover:text-green-900"
                    >
                      Convert
                    </button>
                    <button
                      v-if="account.status !== 'exited'"
                      @click="markAsExited(account.id)"
                      class="text-orange-600 hover:text-orange-900"
                    >
                      Exit
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="accounts.length === 0">
                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                  No investor accounts found. Record your first investment to get started.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Access Code Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeModal">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black opacity-50"></div>
        
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
          <button
            @click="closeModal"
            class="absolute top-4 right-4 text-gray-400 hover:text-gray-600"
          >
            <XMarkIcon class="h-6 w-6" aria-hidden="true" />
          </button>

          <div class="text-center mb-6">
            <div class="mx-auto w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mb-4">
              <KeyIcon class="h-8 w-8 text-purple-600" aria-hidden="true" />
            </div>
            <h3 class="text-2xl font-bold text-gray-900">Investor Portal Access</h3>
            <p class="text-sm text-gray-600 mt-2">Share these credentials with the investor</p>
          </div>

          <div class="space-y-4">
            <div class="bg-gray-50 rounded-lg p-4">
              <label class="text-xs font-semibold text-gray-500 uppercase">Investor Name</label>
              <p class="text-lg font-medium text-gray-900 mt-1">{{ selectedAccount?.name }}</p>
            </div>

            <div class="bg-gray-50 rounded-lg p-4">
              <label class="text-xs font-semibold text-gray-500 uppercase">Email</label>
              <p class="text-lg font-medium text-gray-900 mt-1">{{ selectedAccount?.email }}</p>
            </div>

            <div class="bg-purple-50 border-2 border-purple-200 rounded-lg p-4">
              <label class="text-xs font-semibold text-purple-700 uppercase">Access Code</label>
              <p class="text-3xl font-bold text-purple-600 mt-2 font-mono tracking-wider">{{ accessCode }}</p>
            </div>

            <div class="bg-blue-50 rounded-lg p-4">
              <label class="text-xs font-semibold text-gray-500 uppercase">Login URL</label>
              <p class="text-sm text-blue-600 mt-1 break-all">{{ loginUrl }}</p>
            </div>
          </div>

          <div class="mt-6 flex gap-3">
            <button
              @click="copyToClipboard"
              class="flex-1 inline-flex items-center justify-center px-4 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-semibold"
            >
              <ClipboardDocumentIcon v-if="!copied" class="h-5 w-5 mr-2" aria-hidden="true" />
              <CheckCircleIcon v-else class="h-5 w-5 mr-2" aria-hidden="true" />
              {{ copied ? 'Copied!' : 'Copy All Info' }}
            </button>
            <button
              @click="closeModal"
              class="px-4 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-semibold"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
  PlusIcon, 
  UsersIcon, 
  BanknotesIcon, 
  XMarkIcon, 
  KeyIcon, 
  ClipboardDocumentIcon, 
  CheckCircleIcon 
} from '@heroicons/vue/24/outline';

interface InvestorAccount {
  id: number;
  name: string;
  email: string;
  investment_amount: number;
  investment_date: string;
  status: string;
  status_label: string;
  equity_percentage: number;
}

defineProps<{
  accounts: InvestorAccount[];
  totalInvested: number;
  investorCount: number;
}>();

const formatNumber = (value: number): string => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value);
};

const formatDate = (date: string): string => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const getStatusClass = (status: string): string => {
  const classes = {
    ciu: 'px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800',
    shareholder: 'px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800',
    exited: 'px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800',
  };
  return classes[status as keyof typeof classes] || classes.ciu;
};

const convertToShareholder = (id: number) => {
  if (confirm('Convert this investor to shareholder status?')) {
    router.post(route('admin.investor-accounts.convert', id));
  }
};

const markAsExited = (id: number) => {
  if (confirm('Mark this investor as exited?')) {
    router.post(route('admin.investor-accounts.exit', id));
  }
};

const showModal = ref(false);
const selectedAccount = ref<InvestorAccount | null>(null);
const accessCode = ref('');
const copied = ref(false);
const loginUrl = ref('');

const generateAccessCode = (email: string, id: number): string => {
  return email.substring(0, 4).toUpperCase() + id;
};

const showAccessCode = (account: InvestorAccount) => {
  selectedAccount.value = account;
  accessCode.value = generateAccessCode(account.email, account.id);
  loginUrl.value = `${window.location.origin}/investor/login`;
  showModal.value = true;
  copied.value = false;
};

const copyToClipboard = () => {
  const text = `Investor Portal Access\n\nName: ${selectedAccount.value?.name}\nEmail: ${selectedAccount.value?.email}\nAccess Code: ${accessCode.value}\nLogin URL: ${loginUrl.value}`;
  
  navigator.clipboard.writeText(text).then(() => {
    copied.value = true;
    setTimeout(() => {
      copied.value = false;
    }, 2000);
  });
};

const closeModal = () => {
  showModal.value = false;
  selectedAccount.value = null;
  accessCode.value = '';
  copied.value = false;
};
</script>
