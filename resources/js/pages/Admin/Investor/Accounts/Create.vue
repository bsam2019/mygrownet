<template>
  <AdminLayout>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50 py-8">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
          <Link
            :href="route('admin.investor-accounts.index')"
            class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-blue-600 transition-colors"
          >
            <ArrowLeftIcon class="h-4 w-4 mr-2" aria-hidden="true" />
            Back to Investor Accounts
          </Link>
          <div class="mt-4">
            <h1 class="text-3xl font-bold text-gray-900">Record New Investment</h1>
            <p class="mt-2 text-gray-600">Capture investor details and investment information</p>
          </div>
        </div>

        <form @submit.prevent="submit" class="bg-white rounded-2xl shadow-xl overflow-hidden">
          <!-- Investor Details Section -->
          <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-8 py-6 border-b border-gray-200">
            <div class="flex items-center">
              <div class="flex-shrink-0 w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center">
                <UserIcon class="h-6 w-6 text-white" aria-hidden="true" />
              </div>
              <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-900">Investor Details</h3>
                <p class="text-sm text-gray-600">Personal information and contact details</p>
              </div>
            </div>
          </div>

          <div class="px-8 py-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                  Full Name <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.name"
                  type="text"
                  required
                  placeholder="John Doe"
                  class="block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                />
                <p v-if="form.errors.name" class="mt-2 text-sm text-red-600 flex items-center">
                  <ExclamationCircleIcon class="h-4 w-4 mr-1" aria-hidden="true" />
                  {{ form.errors.name }}
                </p>
              </div>

              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                  Email Address <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.email"
                  type="email"
                  required
                  placeholder="john@example.com"
                  class="block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                />
                <p v-if="form.errors.email" class="mt-2 text-sm text-red-600 flex items-center">
                  <ExclamationCircleIcon class="h-4 w-4 mr-1" aria-hidden="true" />
                  {{ form.errors.email }}
                </p>
              </div>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Link to Existing User Account (Optional)
              </label>
              <div class="relative">
                <input
                  v-model="userSearch"
                  @input="filterUsers"
                  @focus="showUserDropdown = true"
                  type="text"
                  placeholder="Search by name, email, or ID..."
                  class="user-search-input block w-full px-4 py-3 rounded-lg border border-blue-200 bg-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                />
                <MagnifyingGlassIcon class="absolute right-3 top-3.5 h-5 w-5 text-gray-400" aria-hidden="true" />
                
                <!-- Dropdown Results -->
                <div
                  v-if="showUserDropdown && filteredUsers.length > 0"
                  class="user-search-dropdown absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-auto"
                >
                  <button
                    v-for="user in filteredUsers"
                    :key="user.id"
                    type="button"
                    @click="selectUser(user)"
                    class="w-full text-left px-4 py-3 hover:bg-blue-50 transition-colors border-b border-gray-100 last:border-b-0"
                  >
                    <div class="font-medium text-gray-900">{{ user.name }}</div>
                    <div class="text-sm text-gray-600">{{ user.email }} • ID: {{ user.id }}</div>
                  </button>
                </div>

                <!-- Selected User Display -->
                <div v-if="selectedUser" class="mt-3 p-3 bg-white border border-blue-300 rounded-lg flex items-center justify-between">
                  <div>
                    <div class="font-medium text-gray-900">{{ selectedUser.name }}</div>
                    <div class="text-sm text-gray-600">{{ selectedUser.email }} • ID: {{ selectedUser.id }}</div>
                  </div>
                  <button
                    type="button"
                    @click="clearUserSelection"
                    class="text-red-600 hover:text-red-800"
                  >
                    <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                  </button>
                </div>
              </div>
              <p class="mt-2 text-xs text-gray-600 flex items-center">
                <InformationCircleIcon class="h-4 w-4 mr-1 text-blue-500" aria-hidden="true" />
                Search and select if the investor already has a platform account
              </p>
            </div>

            <div v-if="!form.user_id" class="bg-purple-50 border border-purple-200 rounded-lg p-4">
              <div class="flex items-start">
                <InformationCircleIcon class="h-5 w-5 text-purple-600 mr-3 mt-0.5" aria-hidden="true" />
                <div>
                  <h4 class="text-sm font-semibold text-gray-900 mb-1">Access Code Authentication</h4>
                  <p class="text-sm text-gray-700 mb-2">
                    An access code will be automatically generated for this investor after account creation.
                  </p>
                  <p class="text-xs text-gray-600">
                    The access code format: <span class="font-mono bg-white px-2 py-1 rounded">[First 4 letters of email][ID]</span>
                  </p>
                  <p class="text-xs text-gray-600 mt-1">
                    Example: john@example.com → <span class="font-mono bg-white px-2 py-1 rounded">JOHN123</span>
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Investment Details Section -->
          <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-8 py-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <div class="flex-shrink-0 w-12 h-12 bg-green-600 rounded-xl flex items-center justify-center">
                  <BanknotesIcon class="h-6 w-6 text-white" aria-hidden="true" />
                </div>
                <div class="ml-4">
                  <h3 class="text-lg font-semibold text-gray-900">Investment Details</h3>
                  <p class="text-sm text-gray-600">Financial information and investment terms (optional for prospective investors)</p>
                </div>
              </div>
              <label class="flex items-center gap-2 cursor-pointer">
                <input
                  v-model="isProspectiveInvestor"
                  type="checkbox"
                  class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                />
                <span class="text-sm font-medium text-gray-700">Prospective Investor (no investment yet)</span>
              </label>
            </div>
          </div>

          <div class="px-8 py-6 space-y-6">
            <!-- Prospective Investor Notice -->
            <div v-if="isProspectiveInvestor" class="bg-amber-50 border border-amber-200 rounded-lg p-4">
              <div class="flex items-start">
                <InformationCircleIcon class="h-5 w-5 text-amber-600 mr-3 mt-0.5" aria-hidden="true" />
                <div>
                  <h4 class="text-sm font-semibold text-gray-900 mb-1">Adding Prospective Investor</h4>
                  <p class="text-sm text-gray-700">
                    This investor will be added without an investment. They can access the investor portal and you can record their investment later.
                  </p>
                </div>
              </div>
            </div>

            <div v-if="!isProspectiveInvestor">
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Investment Round
              </label>
              <select
                v-model="form.investment_round_id"
                class="block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all bg-white"
              >
                <option value="">Select an active investment round (optional)</option>
                <option v-for="round in rounds" :key="round.id" :value="round.id">
                  {{ round.name }} — K{{ formatNumber(round.raised_amount) }} / K{{ formatNumber(round.goal_amount) }} raised
                </option>
              </select>
              <p v-if="form.errors.investment_round_id" class="mt-2 text-sm text-red-600 flex items-center">
                <ExclamationCircleIcon class="h-4 w-4 mr-1" aria-hidden="true" />
                {{ form.errors.investment_round_id }}
              </p>
            </div>

            <div v-if="!isProspectiveInvestor" class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                  Investment Amount (K)
                </label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <span class="text-gray-500 font-medium">K</span>
                  </div>
                  <input
                    v-model="form.investment_amount"
                    type="number"
                    step="0.01"
                    min="0"
                    placeholder="50,000.00 (optional)"
                    class="block w-full pl-10 pr-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                  />
                </div>
                <p v-if="form.errors.investment_amount" class="mt-2 text-sm text-red-600 flex items-center">
                  <ExclamationCircleIcon class="h-4 w-4 mr-1" aria-hidden="true" />
                  {{ form.errors.investment_amount }}
                </p>
              </div>

              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                  Estimated Equity Percentage (Optional)
                </label>
                <div class="flex gap-2">
                  <div class="relative flex-1">
                    <input
                      v-model="form.equity_percentage"
                      type="number"
                      step="0.0001"
                      min="0"
                      max="100"
                      placeholder="0.0000 (TBD at conversion)"
                      class="block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                    />
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                      <span class="text-gray-500 font-medium">%</span>
                    </div>
                  </div>
                  <button
                    v-if="canAutoCalculate"
                    type="button"
                    @click="autoCalculateEquity"
                    class="px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors whitespace-nowrap text-sm font-medium"
                  >
                    Auto Calculate
                  </button>
                </div>
                <p v-if="calculatedEquity" class="mt-2 text-xs text-blue-600 flex items-center">
                  <InformationCircleIcon class="h-4 w-4 mr-1" aria-hidden="true" />
                  Suggested: {{ calculatedEquity }}% (based on K{{ formatNumber(form.investment_amount) }} / K{{ formatNumber(selectedRoundValuation) }})
                </p>
                <p class="mt-2 text-xs text-gray-600 flex items-center">
                  <InformationCircleIcon class="h-4 w-4 mr-1 text-green-500" aria-hidden="true" />
                  Provisional estimate - Actual equity will be determined at CIU conversion based on valuation
                </p>
                <p v-if="form.errors.equity_percentage" class="mt-2 text-sm text-red-600 flex items-center">
                  <ExclamationCircleIcon class="h-4 w-4 mr-1" aria-hidden="true" />
                  {{ form.errors.equity_percentage }}
                </p>
              </div>
            </div>

            <div v-if="!isProspectiveInvestor">
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Investment Date
              </label>
              <input
                v-model="form.investment_date"
                type="date"
                class="block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
              />
              <p v-if="form.errors.investment_date" class="mt-2 text-sm text-red-600 flex items-center">
                <ExclamationCircleIcon class="h-4 w-4 mr-1" aria-hidden="true" />
                {{ form.errors.investment_date }}
              </p>
            </div>
          </div>

          <!-- Actions -->
          <div class="bg-gray-50 px-8 py-6 flex items-center justify-between border-t border-gray-200">
            <Link
              :href="route('admin.investor-accounts.index')"
              class="inline-flex items-center px-6 py-3 text-sm font-semibold text-gray-700 bg-white border-2 border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-all shadow-sm"
            >
              <XMarkIcon class="h-5 w-5 mr-2" aria-hidden="true" />
              Cancel
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="inline-flex items-center px-8 py-3 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
            >
              <CheckCircleIcon v-if="!form.processing" class="h-5 w-5 mr-2" aria-hidden="true" />
              <svg v-else class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ form.processing ? (isProspectiveInvestor ? 'Adding Investor...' : 'Recording Investment...') : (isProspectiveInvestor ? 'Add Prospective Investor' : 'Record Investment') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
  ArrowLeftIcon, 
  UserIcon, 
  BanknotesIcon,
  InformationCircleIcon,
  ExclamationCircleIcon,
  CheckCircleIcon,
  XMarkIcon,
  MagnifyingGlassIcon,
  EyeIcon,
  EyeSlashIcon
} from '@heroicons/vue/24/outline';
import { ref, computed, onMounted, onUnmounted } from 'vue';

interface InvestmentRound {
  id: number;
  name: string;
  goal_amount: number;
  raised_amount: number;
}

interface User {
  id: number;
  name: string;
  email: string;
}

const props = defineProps<{
  rounds: InvestmentRound[];
  users: User[];
}>();

const form = useForm({
  user_id: null as number | null,
  name: '',
  email: '',
  investment_amount: '',
  investment_date: new Date().toISOString().split('T')[0],
  investment_round_id: '',
  equity_percentage: '',
});

const userSearch = ref('');
const showUserDropdown = ref(false);
const selectedUser = ref<User | null>(null);
const filteredUsers = ref<User[]>([]);
const showPassword = ref(false);
const isProspectiveInvestor = ref(false);

const filterUsers = () => {
  const search = userSearch.value.toLowerCase();
  if (!search) {
    filteredUsers.value = props.users.slice(0, 10); // Show first 10 if no search
  } else {
    filteredUsers.value = props.users.filter(user => 
      user.name.toLowerCase().includes(search) ||
      user.email.toLowerCase().includes(search) ||
      user.id.toString().includes(search)
    ).slice(0, 10); // Limit to 10 results
  }
};

const selectUser = (user: User) => {
  selectedUser.value = user;
  form.user_id = user.id;
  form.name = user.name;
  form.email = user.email;
  userSearch.value = '';
  showUserDropdown.value = false;
};

const clearUserSelection = () => {
  selectedUser.value = null;
  form.user_id = null;
  form.name = '';
  form.email = '';
  userSearch.value = '';
};

// Close dropdown when clicking outside
const handleClickOutside = (event: MouseEvent) => {
  const target = event.target as HTMLElement;
  const dropdown = document.querySelector('.user-search-dropdown');
  const input = document.querySelector('.user-search-input');
  
  if (dropdown && !dropdown.contains(target) && !input?.contains(target)) {
    showUserDropdown.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
  filterUsers();
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});

const submit = () => {
  // Clear investment fields if prospective investor
  if (isProspectiveInvestor.value) {
    form.investment_amount = '';
    form.investment_round_id = '';
    form.investment_date = '';
    form.equity_percentage = '';
  }
  
  form.post(route('admin.investor-accounts.store'), {
    preserveScroll: true,
    preserveState: true,
  });
};

const formatNumber = (value: number): string => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value);
};
</script>
