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
          <div class="relative w-full bg-white rounded-t-3xl shadow-2xl transform transition-all max-h-[90vh] flex flex-col">
            <!-- Header -->
            <div class="sticky top-0 bg-gradient-to-r from-purple-600 to-purple-700 text-white px-6 py-4 rounded-t-3xl flex-shrink-0">
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold">Help & Support</h3>
                <button
                  @click="emit('close')"
                  aria-label="Close help and support modal"
                  class="p-2 hover:bg-white/20 rounded-full transition-colors"
                >
                  <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                </button>
              </div>
            </div>

            <!-- Content -->
            <div class="p-6 space-y-4 overflow-y-auto flex-1">
              <!-- Contact Support -->
              <div class="bg-gradient-to-r from-purple-50 to-purple-100 border border-purple-200 rounded-xl p-4">
                <h4 class="text-sm font-semibold text-purple-900 mb-3">Contact Support</h4>
                <div class="space-y-3">
                  <a href="tel:+260123456789" class="flex items-center gap-3 text-sm text-purple-700 hover:text-purple-900">
                    <PhoneIcon class="h-5 w-5" />
                    <span>+260 123 456 789</span>
                  </a>
                  <a href="mailto:support@mygrownet.com" class="flex items-center gap-3 text-sm text-purple-700 hover:text-purple-900">
                    <EnvelopeIcon class="h-5 w-5" />
                    <span>support@mygrownet.com</span>
                  </a>
                  <a href="https://wa.me/260123456789" target="_blank" class="flex items-center gap-3 text-sm text-purple-700 hover:text-purple-900">
                    <ChatBubbleLeftRightIcon class="h-5 w-5" />
                    <span>WhatsApp Support</span>
                  </a>
                </div>
              </div>

              <!-- FAQ -->
              <div class="bg-white border border-gray-200 rounded-xl p-4">
                <button
                  @click="toggleFaq(1)"
                  class="w-full flex items-center justify-between text-left"
                >
                  <h4 class="text-sm font-semibold text-gray-900">How do I make a deposit?</h4>
                  <ChevronRightIcon class="h-5 w-5 text-gray-400" :class="{ 'rotate-90': openFaq === 1 }" />
                </button>
                <p v-if="openFaq === 1" class="text-sm text-gray-600 mt-3">
                  Go to the Wallet tab, click "Deposit", enter the amount, and follow the payment instructions for MTN or Airtel Money.
                </p>
              </div>

              <div class="bg-white border border-gray-200 rounded-xl p-4">
                <button
                  @click="toggleFaq(2)"
                  class="w-full flex items-center justify-between text-left"
                >
                  <h4 class="text-sm font-semibold text-gray-900">How do I withdraw funds?</h4>
                  <ChevronRightIcon class="h-5 w-5 text-gray-400" :class="{ 'rotate-90': openFaq === 2 }" />
                </button>
                <p v-if="openFaq === 2" class="text-sm text-gray-600 mt-3">
                  Go to the Wallet tab, click "Withdraw", enter the amount and your mobile money number. Withdrawals are processed within 24-48 hours.
                </p>
              </div>

              <div class="bg-white border border-gray-200 rounded-xl p-4">
                <button
                  @click="toggleFaq(3)"
                  class="w-full flex items-center justify-between text-left"
                >
                  <h4 class="text-sm font-semibold text-gray-900">How do I refer friends?</h4>
                  <ChevronRightIcon class="h-5 w-5 text-gray-400" :class="{ 'rotate-90': openFaq === 3 }" />
                </button>
                <p v-if="openFaq === 3" class="text-sm text-gray-600 mt-3">
                  Go to the Team tab, copy your referral link, and share it with friends. You'll earn commissions when they join and invest.
                </p>
              </div>

              <div class="bg-white border border-gray-200 rounded-xl p-4">
                <button
                  @click="toggleFaq(4)"
                  class="w-full flex items-center justify-between text-left"
                >
                  <h4 class="text-sm font-semibold text-gray-900">What are the commission levels?</h4>
                  <ChevronRightIcon class="h-5 w-5 text-gray-400" :class="{ 'rotate-90': openFaq === 4 }" />
                </button>
                <p v-if="openFaq === 4" class="text-sm text-gray-600 mt-3">
                  MyGrowNet has a 7-level commission structure. You earn from your direct referrals (Level 1) and their referrals down to 7 levels deep.
                </p>
              </div>

              <!-- Quick Links -->
              <div class="bg-white border border-gray-200 rounded-xl p-4">
                <h4 class="text-sm font-semibold text-gray-900 mb-3">Quick Links</h4>
                <div class="space-y-2">
                  <button class="w-full flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg transition-colors">
                    <span class="text-sm text-gray-700">Terms & Conditions</span>
                    <ChevronRightIcon class="h-4 w-4 text-gray-400" />
                  </button>
                  <button class="w-full flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg transition-colors">
                    <span class="text-sm text-gray-700">Privacy Policy</span>
                    <ChevronRightIcon class="h-4 w-4 text-gray-400" />
                  </button>
                  <button class="w-full flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg transition-colors">
                    <span class="text-sm text-gray-700">User Guide</span>
                    <ChevronRightIcon class="h-4 w-4 text-gray-400" />
                  </button>
                </div>
              </div>

              <!-- App Version -->
              <div class="text-center py-4">
                <p class="text-xs text-gray-400">MyGrowNet Mobile v1.0.0</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { XMarkIcon, PhoneIcon, EnvelopeIcon, ChatBubbleLeftRightIcon, ChevronRightIcon } from '@heroicons/vue/24/outline';

interface Props {
  show: boolean;
}

defineProps<Props>();
const emit = defineEmits(['close']);

const openFaq = ref<number | null>(null);

const toggleFaq = (id: number) => {
  openFaq.value = openFaq.value === id ? null : id;
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
