<template>
  <TransitionRoot appear :show="isOpen" as="template">
    <Dialog as="div" @close="closeModal" class="relative z-50">
      <TransitionChild
        as="template"
        enter="duration-300 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-200 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center">
          <TransitionChild
            as="template"
            enter="duration-300 ease-out"
            enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100"
            leave="duration-200 ease-in"
            leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-95"
          >
            <DialogPanel
              class="w-full max-w-lg transform overflow-hidden rounded-2xl bg-white shadow-2xl transition-all"
            >
              <!-- Header with gradient -->
              <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 px-6 py-5">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20 backdrop-blur-sm">
                      <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </div>
                    <div>
                      <DialogTitle as="h3" class="text-lg font-semibold text-white">
                        Issue Loan
                      </DialogTitle>
                      <p class="text-sm text-emerald-100">{{ user?.name }}</p>
                    </div>
                  </div>
                  <button
                    @click="closeModal"
                    class="rounded-lg p-1 text-white/80 hover:bg-white/10 hover:text-white transition-colors"
                  >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>
              
              <!-- Form Content -->
              <form @submit.prevent="submitLoan" class="p-6">
                <div class="space-y-5">
                  <!-- Amount Input -->
                  <div>
                    <label for="amount" class="block text-sm font-semibold text-gray-700 mb-2">
                      Loan Amount (K)
                    </label>
                    <div class="relative">
                      <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <span class="text-gray-500 text-sm font-medium">K</span>
                      </div>
                      <input
                        id="amount"
                        v-model="form.amount"
                        type="number"
                        step="0.01"
                        min="1"
                        max="10000"
                        required
                        class="block w-full rounded-lg border-gray-300 pl-8 pr-4 py-3 text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors"
                        placeholder="500.00"
                      />
                    </div>
                    <p class="mt-2 text-xs text-gray-500 flex items-center gap-1">
                      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      Funds will be credited to member's wallet immediately
                    </p>
                  </div>

                  <!-- Notes Input -->
                  <div>
                    <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">
                      Notes (Optional)
                    </label>
                    <textarea
                      id="notes"
                      v-model="form.notes"
                      rows="3"
                      class="block w-full rounded-lg border-gray-300 px-4 py-3 text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors resize-none"
                      placeholder="Reason for loan (e.g., Starter kit purchase, Emergency assistance)..."
                    ></textarea>
                  </div>

                  <!-- Info Box -->
                  <div class="rounded-xl bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-200 p-4">
                    <div class="flex gap-3">
                      <div class="flex-shrink-0">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100">
                          <svg class="h-5 w-5 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                          </svg>
                        </div>
                      </div>
                      <div class="flex-1">
                        <h4 class="text-sm font-semibold text-emerald-900 mb-2">
                          Automatic Repayment Terms
                        </h4>
                        <ul class="space-y-1.5 text-sm text-emerald-800">
                          <li class="flex items-start gap-2">
                            <svg class="h-5 w-5 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>100% of future earnings automatically go to loan repayment</span>
                          </li>
                          <li class="flex items-start gap-2">
                            <svg class="h-5 w-5 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <span>Member cannot withdraw until loan is fully repaid</span>
                          </li>
                          <li class="flex items-start gap-2">
                            <svg class="h-5 w-5 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <span>Loan balance and progress shown on member's dashboard</span>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 flex gap-3">
                  <button
                    type="button"
                    @click="closeModal"
                    class="flex-1 rounded-lg border-2 border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    :disabled="processing"
                    class="flex-1 rounded-lg bg-gradient-to-r from-emerald-500 to-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:from-emerald-600 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg shadow-emerald-500/30"
                  >
                    <span v-if="processing" class="flex items-center justify-center gap-2">
                      <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      Processing...
                    </span>
                    <span v-else class="flex items-center justify-center gap-2">
                      <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      Issue Loan
                    </span>
                  </button>
                </div>
              </form>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import {
  TransitionRoot,
  TransitionChild,
  Dialog,
  DialogPanel,
  DialogTitle,
} from '@headlessui/vue';

const props = defineProps({
  isOpen: Boolean,
  user: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(['close']);

const form = ref({
  amount: '',
  notes: '',
});

const processing = ref(false);

const closeModal = () => {
  emit('close');
  form.value = { amount: '', notes: '' };
};

const submitLoan = () => {
  if (!props.user) {
    console.error('No user selected');
    return;
  }
  
  console.log('Submitting loan:', {
    user: props.user,
    form: form.value,
    route: route('admin.loans.issue', props.user.id)
  });
  
  processing.value = true;
  
  router.post(
    route('admin.loans.issue', props.user.id),
    form.value,
    {
      onSuccess: (page) => {
        console.log('Loan issued successfully', page);
        closeModal();
      },
      onError: (errors) => {
        console.error('Loan issuance failed:', errors);
        alert('Error: ' + JSON.stringify(errors));
      },
      onFinish: () => {
        processing.value = false;
      },
      preserveScroll: true,
    }
  );
};
</script>
