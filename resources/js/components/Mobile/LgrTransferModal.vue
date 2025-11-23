<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="show" class="fixed inset-0 z-[100000] overflow-y-auto" @click.self="emit('close')">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
        <div class="flex min-h-full items-end justify-center p-0">
          <div class="relative w-full bg-white rounded-t-3xl shadow-2xl max-h-[90vh] flex flex-col">
            <div class="sticky top-0 bg-gradient-to-r from-yellow-500 to-amber-600 text-white px-6 py-4 rounded-t-3xl">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-lg font-bold">Transfer LGR to Wallet</h3>
                  <p class="text-xs text-yellow-100 mt-0.5">Convert loyalty rewards to cash</p>
                </div>
                <button @click="emit('close')" aria-label="Close LGR transfer modal" class="p-2 hover:bg-white/20 rounded-full">
                  <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                </button>
              </div>
            </div>

            <div class="p-6 space-y-4 overflow-y-auto flex-1">
              <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                <div class="flex items-center justify-between mb-3">
                  <p class="text-xs text-yellow-700 font-medium">LGR Balance</p>
                  <p class="text-2xl font-bold text-yellow-900">K{{ lgrBalance.toFixed(2) }}</p>
                </div>
                <div class="flex items-center justify-between">
                  <p class="text-xs text-yellow-700">Transferable ({{ lgrPercentage }}%)</p>
                  <p class="text-lg font-semibold text-yellow-800">K{{ lgrWithdrawable.toFixed(2) }}</p>
                </div>
              </div>

              <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Transfer Amount</label>
                <div class="relative">
                  <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">K</span>
                  <input
                    v-model.number="amount"
                    type="number"
                    step="0.01"
                    :max="lgrWithdrawable"
                    class="w-full pl-8 pr-4 py-3 border-2 border-gray-300 rounded-xl focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 transition-all"
                    placeholder="0.00"
                  />
                </div>
                <button
                  @click="amount = lgrWithdrawable"
                  class="mt-2 text-xs text-yellow-600 font-medium hover:text-yellow-700"
                >
                  Transfer Maximum (K{{ lgrWithdrawable.toFixed(2) }})
                </button>
              </div>

              <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                <div class="flex gap-3">
                  <InformationCircleIcon class="h-5 w-5 text-blue-600 flex-shrink-0 mt-0.5" />
                  <div class="text-xs text-blue-800">
                    <p class="font-medium mb-1">Transfer Rules</p>
                    <ul class="space-y-1 list-disc list-inside">
                      <li>You can transfer up to {{ lgrPercentage }}% of awarded LGR</li>
                      <li>Transferred amount added to main wallet</li>
                      <li>Remaining LGR can be used on platform</li>
                    </ul>
                  </div>
                </div>
              </div>

              <button
                @click="handleTransfer"
                :disabled="!amount || amount <= 0 || amount > lgrWithdrawable || processing"
                class="w-full bg-gradient-to-r from-yellow-500 to-amber-600 text-white py-4 rounded-xl font-semibold hover:from-yellow-600 hover:to-amber-700 transition-all shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="processing">Processing...</span>
                <span v-else>Transfer K{{ (amount || 0).toFixed(2) }} to Wallet</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { XMarkIcon, InformationCircleIcon } from '@heroicons/vue/24/outline';

interface Props {
  show: boolean;
  lgrBalance: number;
  lgrWithdrawable: number;
  lgrPercentage: number;
}

const props = defineProps<Props>();
const emit = defineEmits(['close', 'success', 'error']);

const amount = ref(0);
const processing = ref(false);

const handleTransfer = () => {
  if (!amount.value || amount.value <= 0) {
    emit('error', 'Please enter a valid amount');
    return;
  }

  if (amount.value > props.lgrWithdrawable) {
    emit('error', `You can only transfer up to K${props.lgrWithdrawable.toFixed(2)}`);
    return;
  }

  processing.value = true;

  router.post(
    route('mygrownet.wallet.lgr-transfer'),
    { amount: amount.value },
    {
      preserveScroll: true,
      preserveState: true,
      onSuccess: () => {
        emit('success', 'LGR transferred to wallet successfully!');
        emit('close');
        amount.value = 0;
      },
      onError: (errors) => {
        const errorMessage = errors.amount || errors.message || 'Transfer failed';
        emit('error', errorMessage);
      },
      onFinish: () => {
        processing.value = false;
      },
    }
  );
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
