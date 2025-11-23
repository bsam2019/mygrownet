<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="show"
        class="fixed inset-0 z-[100000] overflow-y-auto"
        @click.self="emit('close')"
      >
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
        
        <!-- Modal -->
        <div class="flex min-h-full items-end justify-center p-0">
          <div class="relative w-full bg-white rounded-t-3xl shadow-2xl transform transition-all">
            <!-- Header -->
            <div class="sticky top-0 bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-4 rounded-t-3xl">
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold">Top Up Wallet</h3>
                <button
                  @click="emit('close')"
                  aria-label="Close deposit modal"
                  class="p-2 hover:bg-white/20 rounded-full transition-colors"
                >
                  <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                </button>
              </div>
            </div>

            <!-- Content -->
            <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
              <!-- Current Balance -->
              <div class="bg-blue-50 rounded-xl p-4 border border-blue-200">
                <p class="text-xs text-blue-600 font-medium mb-1">Current Balance</p>
                <p class="text-2xl font-bold text-blue-900">K{{ formatCurrency(balance) }}</p>
              </div>

              <!-- Amount Input -->
              <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">
                  Top-up Amount
                </label>
                <div class="relative">
                  <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-medium">K</span>
                  <input
                    v-model="amount"
                    type="number"
                    inputmode="decimal"
                    placeholder="0.00"
                    class="w-full pl-10 pr-4 py-4 text-lg font-semibold border-2 border-gray-300 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                    @input="validateAmount"
                  />
                </div>
                <p v-if="amountError" class="text-sm text-red-600 mt-2">{{ amountError }}</p>
              </div>

              <!-- Quick Amount Buttons -->
              <div>
                <p class="text-xs font-medium text-gray-600 mb-2">Quick Select</p>
                <div class="grid grid-cols-4 gap-2">
                  <button
                    v-for="quickAmount in [50, 100, 200, 500]"
                    :key="quickAmount"
                    @click="amount = quickAmount"
                    class="py-3 px-2 bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold rounded-lg transition-colors text-sm active:scale-95"
                  >
                    K{{ quickAmount }}
                  </button>
                </div>
              </div>

              <!-- Payment Method -->
              <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">
                  Payment Method
                </label>
                <div class="space-y-2">
                  <button
                    v-for="method in paymentMethods"
                    :key="method.value"
                    @click="selectedMethod = method.value"
                    class="w-full flex items-center justify-between p-4 border-2 rounded-xl transition-all"
                    :class="selectedMethod === method.value ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'"
                  >
                    <div class="flex items-center gap-3">
                      <div
                        class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                        :class="selectedMethod === method.value ? 'border-blue-500' : 'border-gray-300'"
                      >
                        <div
                          v-if="selectedMethod === method.value"
                          class="w-3 h-3 rounded-full bg-blue-500"
                        ></div>
                      </div>
                      <span class="font-medium text-gray-900">{{ method.label }}</span>
                    </div>
                  </button>
                </div>
              </div>

              <!-- Info -->
              <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 flex gap-3">
                <InformationCircleIcon class="h-5 w-5 text-amber-600 flex-shrink-0 mt-0.5" />
                <div class="text-sm text-amber-800">
                  <p class="font-medium mb-1">Next Steps</p>
                  <p>After clicking "Continue", you'll be redirected to complete your payment via {{ selectedMethodLabel }}.</p>
                </div>
              </div>

              <!-- Phone Number (for Mobile Money) -->
              <div v-if="selectedMethod === 'mobile_money'">
                <label class="block text-sm font-semibold text-gray-900 mb-2">
                  Mobile Money Number
                </label>
                <input
                  v-model="phoneNumber"
                  type="tel"
                  inputmode="tel"
                  placeholder="0977123456"
                  class="w-full px-4 py-4 text-lg border-2 border-gray-300 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                />
                <p class="text-xs text-gray-500 mt-2">Enter your MTN or Airtel number</p>
              </div>

              <!-- Action Button -->
              <button
                v-if="!showInstructions"
                @click="showPaymentInstructions"
                :disabled="!canSubmit"
                class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white py-4 rounded-xl font-semibold text-center hover:from-blue-700 hover:to-blue-800 transition-all shadow-lg active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Show Payment Instructions
              </button>

              <!-- Payment Instructions (Mobile Money) -->
              <div v-if="showInstructions && selectedMethod === 'mobile_money'" class="bg-blue-50 border-2 border-blue-500 rounded-xl p-5 space-y-4">
                <div class="flex items-start gap-3">
                  <svg class="h-6 w-6 text-blue-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                  </svg>
                  <div>
                    <h4 class="font-bold text-blue-900 mb-2">Mobile Money Payment</h4>
                    <p class="text-sm text-blue-800">Follow these steps:</p>
                  </div>
                </div>
                
                <!-- MTN Instructions -->
                <div class="bg-yellow-50 border-2 border-yellow-400 rounded-lg p-4 mb-3">
                  <h5 class="font-bold text-yellow-900 mb-2">MTN Mobile Money (WITHDRAW)</h5>
                  <div class="bg-white rounded px-3 py-2 mb-2">
                    <p class="text-xs text-gray-600">Company Number</p>
                    <p class="text-xl font-bold text-gray-900">0760491206</p>
                    <p class="text-xs text-gray-600 mt-1">Rockshield Investments Ltd</p>
                  </div>
                  <div class="bg-yellow-100 border border-yellow-300 rounded p-2 mb-3">
                    <p class="text-xs font-semibold text-yellow-900">⚠️ IMPORTANT:</p>
                    <p class="text-xs text-yellow-800">You must <strong>WITHDRAW</strong> from this number, not send money to it.</p>
                  </div>
                  <div class="text-xs text-gray-900 space-y-1">
                    <p class="font-semibold mb-2">Steps:</p>
                    <p>1. Dial *115# and call</p>
                    <p>2. Choose option 2 (Withdraw)</p>
                    <p>3. Choose Cash Out</p>
                    <p>4. Enter 1 to choose Agent Number</p>
                    <p>5. Enter Agent Number: <strong>0760491206</strong></p>
                    <p>6. Enter amount: <strong>K{{ amount }}</strong></p>
                    <p>7. Enter your PIN</p>
                  </div>
                </div>

                <!-- Airtel Instructions -->
                <div class="bg-red-50 border-2 border-red-400 rounded-lg p-4">
                  <h5 class="font-bold text-red-900 mb-2">Airtel Money (SEND)</h5>
                  <div class="bg-white rounded px-3 py-2 mb-2">
                    <p class="text-xs text-gray-600">Phone Number</p>
                    <p class="text-xl font-bold text-gray-900">0979230669</p>
                    <p class="text-xs text-gray-600 mt-1">Kafula Mbulo</p>
                  </div>
                  <div class="text-xs text-gray-900 space-y-1">
                    <p class="font-semibold mb-2">Steps:</p>
                    <p>1. Dial *115#</p>
                    <p>2. Select "Send Money"</p>
                    <p>3. Enter: <strong>0979230669</strong></p>
                    <p>4. Enter amount: <strong>K{{ amount }}</strong></p>
                    <p>5. Confirm transaction</p>
                  </div>
                </div>

                <!-- Payment Submission Form -->
                <div class="bg-white rounded-lg p-4 border-2 border-blue-300 mt-4">
                  <h5 class="font-bold text-blue-900 mb-3">Submit Payment Proof</h5>
                  <p class="text-xs text-gray-600 mb-3">After completing the payment, enter your transaction reference:</p>
                  
                  <div class="space-y-3">
                    <div>
                      <label class="block text-xs font-semibold text-gray-700 mb-1">
                        Transaction Reference / ID *
                      </label>
                      <input
                        v-model="paymentReference"
                        type="text"
                        placeholder="e.g., MP240108.1234.A12345"
                        class="w-full px-3 py-2 text-sm border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                      />
                      <p class="text-xs text-gray-500 mt-1">Transaction ID from your mobile money receipt</p>
                    </div>

                    <div>
                      <label class="block text-xs font-semibold text-gray-700 mb-1">
                        Notes (Optional)
                      </label>
                      <textarea
                        v-model="notes"
                        rows="2"
                        placeholder="Any additional information..."
                        class="w-full px-3 py-2 text-sm border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                      ></textarea>
                    </div>
                  </div>
                </div>

                <div class="flex gap-3 mt-4">
                  <button
                    @click="showInstructions = false"
                    class="flex-1 bg-gray-200 text-gray-700 py-3 rounded-xl font-semibold hover:bg-gray-300"
                  >
                    Back
                  </button>
                  <button
                    @click="submitPayment"
                    :disabled="!paymentReference || submitting"
                    class="flex-1 bg-gradient-to-r from-green-600 to-emerald-700 text-white py-3 rounded-xl font-semibold hover:from-green-700 hover:to-emerald-800 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                  >
                    <span v-if="!submitting">Submit Payment</span>
                    <span v-else class="flex items-center gap-2">
                      <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      Submitting...
                    </span>
                  </button>
                </div>

                <!-- Success Message -->
                <div v-if="showSuccess" class="bg-green-50 border-2 border-green-500 rounded-xl p-4 mt-4">
                  <div class="flex gap-3 mb-3">
                    <svg class="h-6 w-6 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <div class="text-sm text-green-800">
                      <p class="font-semibold mb-1">Payment Submitted!</p>
                      <p>Your payment will be verified within 5-10 minutes and your wallet will be credited automatically.</p>
                    </div>
                  </div>
                  <div class="bg-white rounded-lg p-3 border border-green-200">
                    <p class="text-xs text-gray-700">
                      <strong>Track your deposit:</strong> Go to Wallet tab → View All Transactions to see your pending deposit status.
                    </p>
                  </div>
                </div>
              </div>

              <!-- Payment Instructions (Bank Transfer) -->
              <div v-if="showInstructions && selectedMethod === 'bank_transfer'" class="bg-green-50 border-2 border-green-500 rounded-xl p-5 space-y-4">
                <div class="flex items-start gap-3">
                  <svg class="h-6 w-6 text-green-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                  </svg>
                  <div>
                    <h4 class="font-bold text-green-900 mb-2">Bank Transfer Details</h4>
                    <p class="text-sm text-green-800">Transfer K{{ amount }} to:</p>
                  </div>
                </div>
                
                <div class="bg-white rounded-lg p-4 text-center">
                  <svg class="h-16 w-16 text-green-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                  </svg>
                  <h5 class="font-bold text-gray-900 mb-2">Bank Transfer</h5>
                  <p class="text-sm text-gray-700 mb-4">
                    For bank transfer payments, please contact our support team for account details.
                  </p>
                  <div class="space-y-2 text-sm">
                    <div class="bg-green-50 rounded-lg p-3">
                      <p class="text-gray-600 mb-1">WhatsApp:</p>
                      <p class="font-bold text-gray-900">+260 977 123 456</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-3">
                      <p class="text-gray-600 mb-1">Email:</p>
                      <p class="font-bold text-gray-900">payments@mygrownet.com</p>
                    </div>
                  </div>
                  <p class="text-xs text-gray-500 mt-3">
                    Amount to transfer: <span class="font-bold">K{{ amount }}</span>
                  </p>
                </div>

                <!-- Payment Submission Form -->
                <div class="bg-white rounded-lg p-4 border-2 border-green-300 mt-4">
                  <h5 class="font-bold text-green-900 mb-3">Submit Payment Proof</h5>
                  <p class="text-xs text-gray-600 mb-3">After making the transfer, enter your transaction details:</p>
                  
                  <div class="space-y-3">
                    <div>
                      <label class="block text-xs font-semibold text-gray-700 mb-1">
                        Transaction Reference / Receipt Number *
                      </label>
                      <input
                        v-model="paymentReference"
                        type="text"
                        placeholder="e.g., TXN123456789"
                        class="w-full px-3 py-2 text-sm border-2 border-gray-300 rounded-lg focus:border-green-500 focus:ring-2 focus:ring-green-200"
                      />
                      <p class="text-xs text-gray-500 mt-1">Bank transaction reference or receipt number</p>
                    </div>

                    <div>
                      <label class="block text-xs font-semibold text-gray-700 mb-1">
                        Notes (Optional)
                      </label>
                      <textarea
                        v-model="notes"
                        rows="2"
                        placeholder="Any additional information..."
                        class="w-full px-3 py-2 text-sm border-2 border-gray-300 rounded-lg focus:border-green-500 focus:ring-2 focus:ring-green-200"
                      ></textarea>
                    </div>
                  </div>
                  
                  <p class="text-xs text-gray-600 mt-3">
                    <strong>Remember:</strong> Send proof of payment (screenshot/receipt) to WhatsApp: +260 977 123 456
                  </p>
                </div>

                <div class="flex gap-3 mt-4">
                  <button
                    @click="showInstructions = false"
                    class="flex-1 bg-gray-200 text-gray-700 py-3 rounded-xl font-semibold hover:bg-gray-300"
                  >
                    Back
                  </button>
                  <button
                    @click="submitPayment"
                    :disabled="!paymentReference || submitting"
                    class="flex-1 bg-gradient-to-r from-green-600 to-emerald-700 text-white py-3 rounded-xl font-semibold hover:from-green-700 hover:to-emerald-800 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                  >
                    <span v-if="!submitting">Submit Payment</span>
                    <span v-else class="flex items-center gap-2">
                      <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      Submitting...
                    </span>
                  </button>
                </div>

                <!-- Success Message -->
                <div v-if="showSuccess" class="bg-green-50 border-2 border-green-500 rounded-xl p-4 mt-4">
                  <div class="flex gap-3 mb-3">
                    <svg class="h-6 w-6 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <div class="text-sm text-green-800">
                      <p class="font-semibold mb-1">Payment Submitted!</p>
                      <p>Your payment will be verified and your wallet will be credited once confirmed.</p>
                    </div>
                  </div>
                  <div class="bg-white rounded-lg p-3 border border-green-200">
                    <p class="text-xs text-gray-700">
                      <strong>Track your deposit:</strong> Go to Wallet tab → View All Transactions to see your pending deposit status.
                    </p>
                  </div>
                </div>
              </div>

              <!-- Recent Top-ups -->
              <div v-if="recentTopups.length > 0">
                <h4 class="text-sm font-semibold text-gray-900 mb-3">Recent Top-ups</h4>
                <div class="space-y-2">
                  <div
                    v-for="topup in recentTopups.slice(0, 3)"
                    :key="topup.id"
                    class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                  >
                    <div>
                      <p class="text-sm font-medium text-gray-900">K{{ formatCurrency(topup.amount) }}</p>
                      <p class="text-xs text-gray-500">{{ topup.date }}</p>
                    </div>
                    <span
                      class="text-xs px-2 py-1 rounded-full"
                      :class="topup.status === 'verified' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'"
                    >
                      {{ topup.status }}
                    </span>
                  </div>
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
import { XMarkIcon, InformationCircleIcon } from '@heroicons/vue/24/outline';

interface Props {
  show: boolean;
  balance: number;
  recentTopups?: any[];
}

withDefaults(defineProps<Props>(), {
  recentTopups: () => [],
});

const emit = defineEmits(['close', 'error']);

const amount = ref<number | null>(null);
const amountError = ref('');
const selectedMethod = ref('mobile_money');
const phoneNumber = ref('');
const showInstructions = ref(false);
const paymentReference = ref('');
const notes = ref('');
const submitting = ref(false);
const showSuccess = ref(false);

const paymentMethods = [
  { value: 'mobile_money', label: 'Mobile Money (MTN/Airtel)' },
  { value: 'bank_transfer', label: 'Bank Transfer' },
];

const selectedMethodLabel = computed(() => {
  const method = paymentMethods.find(m => m.value === selectedMethod.value);
  return method?.label || 'Mobile Money';
});

const canSubmit = computed(() => {
  if (!amount.value || amount.value < 10 || amountError.value) {
    return false;
  }
  
  if (selectedMethod.value === 'mobile_money' && !phoneNumber.value) {
    return false;
  }
  
  return true;
});

const validateAmount = () => {
  amountError.value = '';
  
  if (!amount.value) {
    return;
  }
  
  if (amount.value < 10) {
    amountError.value = 'Minimum top-up amount is K10';
  } else if (amount.value > 100000) {
    amountError.value = 'Maximum top-up amount is K100,000';
  }
};

const showPaymentInstructions = () => {
  if (canSubmit.value) {
    showInstructions.value = true;
  }
};

const submitPayment = () => {
  if (!paymentReference.value || submitting.value) return;
  
  submitting.value = true;
  
  router.post(route('mygrownet.payments.store'), {
    amount: amount.value,
    payment_method: selectedMethod.value === 'mobile_money' ? 'mtn_momo' : 'bank_transfer',
    payment_reference: paymentReference.value,
    phone_number: phoneNumber.value,
    payment_type: 'wallet_topup',
    notes: notes.value,
    _mobile: true,
  }, {
    preserveScroll: true,
    preserveState: true,
    replace: true,
    onBefore: () => {
      // Prevent navigation by returning false if we detect a redirect
      return true;
    },
    onSuccess: (page) => {
      submitting.value = false;
      showSuccess.value = true;
      
      // Close modal and reload after 3 seconds
      setTimeout(() => {
        showSuccess.value = false;
        showInstructions.value = false;
        paymentReference.value = '';
        notes.value = '';
        amount.value = null;
        emit('close');
        
        // Reload only the mobile dashboard data
        router.reload({ 
          only: ['walletBalance', 'recentTopups'],
          preserveScroll: true,
          preserveState: true,
        });
      }, 3000);
    },
    onError: (errors) => {
      submitting.value = false;
      console.error('Payment submission error:', errors);
      const errorMessage = Object.values(errors).flat().join(', ');
      // Emit error to parent to show toast
      emit('error', `Payment submission failed: ${errorMessage}`);
    },
    onFinish: () => {
      submitting.value = false;
    },
  });
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
