<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="show"
        class="fixed inset-0 z-[100000] overflow-y-auto"
        @click.self="handleBackdropClick"
      >
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
        
        <!-- Modal -->
        <div class="flex min-h-full items-end justify-center p-0">
          <div class="relative w-full bg-white rounded-t-3xl shadow-2xl transform transition-all max-h-[90vh] flex flex-col">
            <!-- Header -->
            <div class="sticky top-0 bg-gradient-to-r from-green-600 to-emerald-700 text-white px-6 py-4 rounded-t-3xl flex-shrink-0">
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold">Withdraw Funds</h3>
                <button
                  @click="handleClose"
                  class="p-2 hover:bg-white/20 rounded-full transition-colors"
                >
                  <XMarkIcon class="h-5 w-5" />
                </button>
              </div>
            </div>

            <!-- Content -->
            <div class="p-6 space-y-6 overflow-y-auto flex-1">
              <!-- Available Balance -->
              <div class="bg-green-50 rounded-xl p-4 border border-green-200">
                <p class="text-xs text-green-600 font-medium mb-1">Available Balance</p>
                <p class="text-2xl font-bold text-green-900">K{{ formatCurrency(balance) }}</p>
              </div>

              <!-- Loan Warning (if applicable) -->
              <div v-if="loanSummary?.has_loan && !loanSummary?.can_withdraw" class="bg-red-50 border border-red-200 rounded-lg p-4 flex gap-3">
                <ExclamationTriangleIcon class="h-5 w-5 text-red-600 flex-shrink-0 mt-0.5" />
                <div class="text-sm text-red-800">
                  <p class="font-medium mb-1">Withdrawal Restricted</p>
                  <p>You have an outstanding loan of K{{ formatCurrency(loanSummary.loan_balance) }}. Please repay your loan before making withdrawals.</p>
                </div>
              </div>

              <!-- Withdrawal Form -->
              <form v-if="!loanSummary?.has_loan || loanSummary?.can_withdraw" @submit.prevent="submitWithdrawal" class="space-y-4">
                <!-- Amount Input -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Withdrawal Amount (K)
                  </label>
                  <input
                    v-model="form.amount"
                    type="number"
                    step="0.01"
                    min="50"
                    :max="maxWithdrawal"
                    placeholder="Enter amount (min K50)"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent text-lg"
                    :class="{ 'border-red-500': errors.amount }"
                  />
                  <p v-if="errors.amount" class="mt-1 text-sm text-red-600">{{ errors.amount }}</p>
                  <p class="mt-1 text-xs text-gray-500">
                    Max: K{{ formatCurrency(maxWithdrawal) }}
                  </p>
                </div>

                <!-- Phone Number Input -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Mobile Money Number
                  </label>
                  <input
                    v-model="form.phone_number"
                    type="tel"
                    placeholder="0977123456 or 0967123456"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    :class="{ 'border-red-500': errors.phone_number }"
                  />
                  <p v-if="errors.phone_number" class="mt-1 text-sm text-red-600">{{ errors.phone_number }}</p>
                  <p class="mt-1 text-xs text-gray-500">MTN or Airtel number</p>
                </div>

                <!-- Account Name Input -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Account Name
                  </label>
                  <input
                    v-model="form.account_name"
                    type="text"
                    placeholder="Name on mobile money account"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    :class="{ 'border-red-500': errors.account_name }"
                  />
                  <p v-if="errors.account_name" class="mt-1 text-sm text-red-600">{{ errors.account_name }}</p>
                </div>

                <!-- Withdrawal Limits Info -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                  <h4 class="text-sm font-semibold text-blue-900 mb-3">Your Limits</h4>
                  <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                      <span class="text-blue-700">Remaining Today:</span>
                      <span class="font-medium text-blue-900">K{{ formatCurrency(remainingDailyLimit || 0) }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-blue-700">Single Transaction:</span>
                      <span class="font-medium text-blue-900">K{{ formatCurrency(verificationLimits?.single_transaction || 0) }}</span>
                    </div>
                  </div>
                </div>

                <!-- Processing Info -->
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 flex gap-3">
                  <InformationCircleIcon class="h-5 w-5 text-amber-600 flex-shrink-0 mt-0.5" />
                  <div class="text-sm text-amber-800">
                    <p class="font-medium mb-1">Processing Time</p>
                    <p>Withdrawals are processed within 24-48 hours to your mobile money account.</p>
                  </div>
                </div>

                <!-- Submit Button -->
                <button
                  type="submit"
                  :disabled="processing || !canSubmit"
                  class="w-full bg-gradient-to-r from-green-600 to-emerald-700 text-white py-4 rounded-xl font-semibold hover:from-green-700 hover:to-emerald-800 transition-all shadow-lg disabled:opacity-50 disabled:cursor-not-allowed active:scale-98"
                >
                  <span v-if="processing">Processing...</span>
                  <span v-else>Request Withdrawal</span>
                </button>
              </form>

              <!-- Restricted Message -->
              <div v-else class="text-center py-8">
                <ExclamationTriangleIcon class="h-16 w-16 text-red-500 mx-auto mb-4" />
                <p class="text-lg font-semibold text-gray-900 mb-2">Withdrawal Restricted</p>
                <p class="text-sm text-gray-600">Please clear your outstanding loan first.</p>
              </div>

              <!-- Pending Withdrawals -->
              <div v-if="pendingWithdrawals > 0" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <p class="text-sm text-yellow-800">
                  <span class="font-medium">⏳ Pending:</span> K{{ formatCurrency(pendingWithdrawals) }} in pending withdrawals.
                </p>
              </div>

              <!-- Success Message -->
              <div v-if="successMessage" class="bg-green-50 border border-green-200 rounded-lg p-4 flex gap-3">
                <CheckCircleIcon class="h-5 w-5 text-green-600 flex-shrink-0 mt-0.5" />
                <div class="text-sm text-green-800">
                  <p class="font-medium">{{ successMessage }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { XMarkIcon, InformationCircleIcon, ExclamationTriangleIcon, CheckCircleIcon } from '@heroicons/vue/24/outline';

interface Props {
  show: boolean;
  balance: number;
  verificationLimits?: any;
  remainingDailyLimit?: number;
  pendingWithdrawals?: number;
  loanSummary?: any;
}

const props = withDefaults(defineProps<Props>(), {
  verificationLimits: () => ({ daily_withdrawal: 1000, single_transaction: 500 }),
  remainingDailyLimit: 1000,
  pendingWithdrawals: 0,
  loanSummary: () => ({ has_loan: false, can_withdraw: true }),
});

const emit = defineEmits(['close']);

// Form state
const form = ref({
  amount: '',
  phone_number: '',
  account_name: '',
});

const errors = ref<Record<string, string>>({});
const processing = ref(false);
const successMessage = ref('');

// Calculate maximum withdrawal amount
const maxWithdrawal = computed(() => {
  const limits = [
    props.balance,
    props.verificationLimits?.single_transaction || 500,
    props.remainingDailyLimit || 0,
  ];
  return Math.max(0, Math.min(...limits));
});

// Check if form can be submitted
const canSubmit = computed(() => {
  return form.value.amount && 
         form.value.phone_number && 
         form.value.account_name &&
         !processing.value &&
         parseFloat(form.value.amount) >= 50 &&
         parseFloat(form.value.amount) <= maxWithdrawal.value;
});

// Validate form
const validateForm = (): boolean => {
  errors.value = {};
  
  const amount = parseFloat(form.value.amount);
  
  if (!form.value.amount || amount < 50) {
    errors.value.amount = 'Minimum withdrawal is K50';
    return false;
  }
  
  if (amount > maxWithdrawal.value) {
    errors.value.amount = `Maximum withdrawal is K${formatCurrency(maxWithdrawal.value)}`;
    return false;
  }
  
  if (!form.value.phone_number) {
    errors.value.phone_number = 'Phone number is required';
    return false;
  }
  
  // Validate Zambian phone number format
  const phoneRegex = /^(\+260|0)?[79][0-9]{8}$/;
  if (!phoneRegex.test(form.value.phone_number)) {
    errors.value.phone_number = 'Please enter a valid Zambian mobile number (MTN or Airtel)';
    return false;
  }
  
  if (!form.value.account_name) {
    errors.value.account_name = 'Account name is required';
    return false;
  }
  
  return true;
};

// Submit withdrawal request
const submitWithdrawal = () => {
  if (!validateForm()) {
    return;
  }
  
  processing.value = true;
  errors.value = {};
  
  router.post(route('withdrawals.store'), form.value, {
    preserveScroll: true,
    onSuccess: () => {
      successMessage.value = 'Withdrawal request submitted successfully! You will receive the funds within 24-48 hours.';
      form.value = { amount: '', phone_number: '', account_name: '' };
      
      // Close modal after 2 seconds
      setTimeout(() => {
        handleClose();
      }, 2000);
    },
    onError: (serverErrors) => {
      errors.value = serverErrors;
    },
    onFinish: () => {
      processing.value = false;
    },
  });
};

// Handle close
const handleClose = () => {
  if (!processing.value) {
    form.value = { amount: '', phone_number: '', account_name: '' };
    errors.value = {};
    successMessage.value = '';
    emit('close');
  }
};

// Handle backdrop click
const handleBackdropClick = () => {
  if (!processing.value) {
    handleClose();
  }
};

const formatCurrency = (value: number | undefined | null) => {
  if (value === undefined || value === null || isNaN(value)) return '0.00';
  return Number(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active .relative,
.modal-leave-active .relative {
  transition: transform 0.3s ease;
}

.modal-enter-from .relative,
.modal-leave-to .relative {
  transform: translateY(100%);
}
</style>
