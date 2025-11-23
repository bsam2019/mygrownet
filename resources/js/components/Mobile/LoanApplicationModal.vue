<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="show" class="fixed inset-0 z-[100000] overflow-y-auto" @click.self="emit('close')">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
        <div class="flex min-h-full items-end justify-center p-0">
          <div class="relative w-full bg-white rounded-t-3xl shadow-2xl max-h-[90vh] flex flex-col">
            <!-- Header -->
            <div class="sticky top-0 bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-4 rounded-t-3xl">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-lg font-bold">Apply for Loan</h3>
                  <p class="text-xs text-blue-100 mt-0.5">Quick funding for your needs</p>
                </div>
                <button @click="emit('close')" aria-label="Close loan application modal" class="p-2 hover:bg-white/20 rounded-full">
                  <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                </button>
              </div>
            </div>

            <div class="p-6 space-y-4 overflow-y-auto flex-1">
              <!-- Eligibility Check -->
              <div v-if="!eligibility.eligible" class="bg-red-50 border border-red-200 rounded-xl p-4">
                <div class="flex gap-3">
                  <ExclamationCircleIcon class="h-5 w-5 text-red-600 flex-shrink-0 mt-0.5" />
                  <div>
                    <p class="text-sm font-semibold text-red-900 mb-1">Not Eligible</p>
                    <p class="text-xs text-red-800">{{ eligibility.reason }}</p>
                  </div>
                </div>
              </div>

              <!-- Available Credit -->
              <div v-else class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-4">
                <div class="flex items-center justify-between mb-2">
                  <p class="text-xs text-blue-700 font-medium">Available Credit</p>
                  <p class="text-2xl font-bold text-blue-900">K{{ formatCurrency(availableCredit) }}</p>
                </div>
                <div class="text-xs text-blue-600">
                  Loan Limit: K{{ formatCurrency(loanLimit) }} | Current Balance: K{{ formatCurrency(loanBalance) }}
                </div>
              </div>

              <!-- Form -->
              <form v-if="eligibility.eligible" @submit.prevent="submitApplication" class="space-y-4">
                <!-- Amount -->
                <div>
                  <label class="block text-sm font-semibold text-gray-900 mb-2">Loan Amount</label>
                  <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">K</span>
                    <input
                      v-model.number="form.amount"
                      type="number"
                      step="50"
                      min="100"
                      :max="availableCredit"
                      class="w-full pl-8 pr-4 py-3 border-2 border-gray-300 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                      placeholder="0.00"
                      required
                    />
                  </div>
                  <p class="text-xs text-gray-500 mt-1">Minimum: K100 | Maximum: K{{ formatCurrency(availableCredit) }}</p>
                </div>

                <!-- Purpose -->
                <div>
                  <label class="block text-sm font-semibold text-gray-900 mb-2">Purpose</label>
                  <textarea
                    v-model="form.purpose"
                    rows="3"
                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all resize-none"
                    placeholder="Describe why you need this loan (minimum 20 characters)"
                    required
                    minlength="20"
                    maxlength="500"
                  ></textarea>
                  <p class="text-xs text-gray-500 mt-1">{{ form.purpose.length }}/500 characters</p>
                </div>

                <!-- Repayment Plan -->
                <div>
                  <label class="block text-sm font-semibold text-gray-900 mb-2">Repayment Plan</label>
                  <div class="grid grid-cols-3 gap-2">
                    <button
                      v-for="plan in repaymentPlans"
                      :key="plan.value"
                      type="button"
                      @click="form.repayment_plan = plan.value"
                      class="p-3 rounded-xl border-2 transition-all text-center"
                      :class="form.repayment_plan === plan.value 
                        ? 'border-blue-500 bg-blue-50 text-blue-900' 
                        : 'border-gray-300 bg-white text-gray-700 hover:border-blue-300'"
                    >
                      <p class="text-xs font-bold">{{ plan.label }}</p>
                      <p class="text-xs text-gray-500 mt-0.5">{{ plan.days }} days</p>
                    </button>
                  </div>
                </div>

                <!-- Terms -->
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
                  <div class="flex gap-3">
                    <InformationCircleIcon class="h-5 w-5 text-gray-600 flex-shrink-0 mt-0.5" />
                    <div class="text-xs text-gray-700 space-y-1">
                      <p class="font-semibold text-gray-900">Loan Terms:</p>
                      <ul class="list-disc list-inside space-y-0.5">
                        <li>Automatic deduction from future earnings</li>
                        <li>No interest charged</li>
                        <li>Approval within 24-48 hours</li>
                        <li>Must maintain active subscription</li>
                      </ul>
                    </div>
                  </div>
                </div>

                <!-- Submit Button -->
                <button
                  type="submit"
                  :disabled="processing || !form.amount || !form.purpose || !form.repayment_plan"
                  class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-4 rounded-xl font-semibold hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg disabled:opacity-50 disabled:cursor-not-allowed active:scale-[0.98]"
                >
                  <span v-if="processing">Processing...</span>
                  <span v-else>Submit Application</span>
                </button>
              </form>

              <!-- Pending Application Notice -->
              <div v-if="hasPendingApplication" class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                <div class="flex gap-3">
                  <ClockIcon class="h-5 w-5 text-amber-600 flex-shrink-0 mt-0.5" />
                  <div>
                    <p class="text-sm font-semibold text-amber-900 mb-1">Application Pending</p>
                    <p class="text-xs text-amber-800">You have a pending loan application under review. Please wait for approval before submitting another.</p>
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
import { XMarkIcon, InformationCircleIcon, ExclamationCircleIcon, ClockIcon } from '@heroicons/vue/24/outline';

interface Props {
  show: boolean;
  loanBalance: number;
  loanLimit: number;
  availableCredit: number;
  eligibility: {
    eligible: boolean;
    reason?: string;
  };
  hasPendingApplication?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  hasPendingApplication: false,
});

const emit = defineEmits(['close', 'success', 'error']);

const form = ref({
  amount: 0,
  purpose: '',
  repayment_plan: '30_days',
});

const processing = ref(false);

const repaymentPlans = [
  { value: '30_days', label: '30 Days', days: 30 },
  { value: '60_days', label: '60 Days', days: 60 },
  { value: '90_days', label: '90 Days', days: 90 },
];

const submitApplication = () => {
  if (!form.value.amount || form.value.amount < 100) {
    emit('error', 'Please enter a valid amount (minimum K100)');
    return;
  }

  if (form.value.amount > props.availableCredit) {
    emit('error', `Amount exceeds available credit of K${props.availableCredit.toFixed(2)}`);
    return;
  }

  if (!form.value.purpose || form.value.purpose.length < 20) {
    emit('error', 'Please provide a detailed purpose (minimum 20 characters)');
    return;
  }

  processing.value = true;

  router.post(
    route('mygrownet.loans.apply'),
    form.value,
    {
      preserveScroll: true,
      preserveState: true,
      onSuccess: () => {
        emit('success', 'Loan application submitted successfully!');
        emit('close');
        form.value = { amount: 0, purpose: '', repayment_plan: '30_days' };
      },
      onError: (errors) => {
        const errorMessage = errors.amount || errors.purpose || errors.error || 'Application failed';
        emit('error', errorMessage);
      },
      onFinish: () => {
        processing.value = false;
      },
    }
  );
};

const formatCurrency = (value: number): string => {
  return value.toFixed(2);
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
