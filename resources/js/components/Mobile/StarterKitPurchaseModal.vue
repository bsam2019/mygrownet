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
            <div class="sticky top-0 bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-6 py-4 rounded-t-3xl flex-shrink-0">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-lg font-bold">Starter Kit</h3>
                  <p class="text-xs text-indigo-100 mt-0.5">Begin your journey</p>
                </div>
                <button
                  @click="emit('close')"
                  class="p-2 hover:bg-white/20 rounded-full transition-colors"
                >
                  <XMarkIcon class="h-5 w-5" />
                </button>
              </div>
            </div>

            <!-- Content -->
            <div class="p-6 space-y-4 overflow-y-auto flex-1">
              <!-- Has Starter Kit -->
              <div v-if="hasStarterKit" class="space-y-4">
                <!-- Success Message -->
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl p-4">
                  <div class="flex items-start gap-3">
                    <CheckCircleIcon class="h-6 w-6 text-green-600 flex-shrink-0 mt-0.5" />
                    <div class="flex-1">
                      <h4 class="text-sm font-semibold text-green-900">You have the Starter Kit!</h4>
                      <p class="text-xs text-green-700 mt-1">
                        Purchased on {{ purchaseDate }}
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Shop Credit -->
                <div class="bg-gradient-to-r from-purple-50 to-indigo-50 border border-purple-200 rounded-xl p-4">
                  <div class="flex items-center justify-between">
                    <div>
                      <p class="text-xs text-purple-600 font-medium">Shop Credit</p>
                      <p class="text-2xl font-bold text-purple-900 mt-1">K{{ shopCredit }}</p>
                      <p class="text-xs text-purple-600 mt-1">Expires: {{ creditExpiry }}</p>
                    </div>
                    <ShoppingBagIcon class="h-12 w-12 text-purple-400" />
                  </div>
                </div>

                <!-- Upgrade Option (if basic) -->
                <div v-if="tier === 'basic'" class="bg-gradient-to-r from-amber-50 to-yellow-50 border border-amber-200 rounded-xl p-4">
                  <div class="flex items-start gap-3">
                    <SparklesIcon class="h-6 w-6 text-amber-600 flex-shrink-0 mt-0.5" />
                    <div class="flex-1">
                      <h4 class="text-sm font-semibold text-amber-900">Upgrade to Premium</h4>
                      <p class="text-xs text-amber-700 mt-1">
                        Get LGR access, double shop credit, and more benefits for just K500!
                      </p>
                      <button
                        @click="handleUpgrade"
                        class="mt-3 w-full bg-gradient-to-r from-amber-500 to-yellow-500 text-white py-2 rounded-lg text-sm font-semibold hover:from-amber-600 hover:to-yellow-600 transition-all"
                      >
                        Upgrade Now
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- No Starter Kit - Purchase Options -->
              <div v-else class="space-y-4">
                <!-- Info Banner -->
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                  <div class="flex gap-3">
                    <InformationCircleIcon class="h-5 w-5 text-blue-600 flex-shrink-0 mt-0.5" />
                    <div class="text-sm text-blue-800">
                      <p class="font-medium mb-1">Why Get the Starter Kit?</p>
                      <ul class="text-xs space-y-1 list-disc list-inside">
                        <li>Access exclusive learning content</li>
                        <li>Get shop credit for products</li>
                        <li>Unlock earning opportunities</li>
                        <li>Join the MyGrowNet community</li>
                      </ul>
                    </div>
                  </div>
                </div>

                <!-- Tier Selection -->
                <div class="space-y-3">
                  <h4 class="text-sm font-semibold text-gray-900">Choose Your Tier</h4>
                  
                  <!-- Basic Tier -->
                  <div
                    @click="selectedTier = 'basic'"
                    :class="[
                      'border-2 rounded-xl p-4 cursor-pointer transition-all',
                      selectedTier === 'basic'
                        ? 'border-indigo-600 bg-indigo-50'
                        : 'border-gray-200 bg-white'
                    ]"
                  >
                    <div class="flex items-start justify-between">
                      <div class="flex-1">
                        <div class="flex items-center gap-2">
                          <h5 class="text-base font-bold text-gray-900">Basic</h5>
                          <span class="text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full">Popular</span>
                        </div>
                        <p class="text-2xl font-bold text-indigo-600 mt-1">K500</p>
                        <ul class="text-xs text-gray-600 mt-2 space-y-1">
                          <li>✓ K100 shop credit</li>
                          <li>✓ Learning resources</li>
                          <li>✓ Community access</li>
                        </ul>
                      </div>
                      <div
                        :class="[
                          'w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0',
                          selectedTier === 'basic'
                            ? 'border-indigo-600 bg-indigo-600'
                            : 'border-gray-300'
                        ]"
                      >
                        <div v-if="selectedTier === 'basic'" class="w-2 h-2 bg-white rounded-full"></div>
                      </div>
                    </div>
                  </div>

                  <!-- Premium Tier -->
                  <div
                    @click="selectedTier = 'premium'"
                    :class="[
                      'border-2 rounded-xl p-4 cursor-pointer transition-all relative overflow-hidden',
                      selectedTier === 'premium'
                        ? 'border-purple-600 bg-purple-50'
                        : 'border-gray-200 bg-white'
                    ]"
                  >
                    <div class="absolute top-2 right-2">
                      <span class="text-xs bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-2 py-1 rounded-full font-semibold">
                        ⭐ Best Value
                      </span>
                    </div>
                    <div class="flex items-start justify-between">
                      <div class="flex-1">
                        <h5 class="text-base font-bold text-gray-900">Premium</h5>
                        <p class="text-2xl font-bold text-purple-600 mt-1">K1,000</p>
                        <ul class="text-xs text-gray-600 mt-2 space-y-1">
                          <li>✓ K200 shop credit</li>
                          <li>✓ LGR profit sharing</li>
                          <li>✓ Priority support</li>
                          <li>✓ Enhanced earnings</li>
                        </ul>
                      </div>
                      <div
                        :class="[
                          'w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0',
                          selectedTier === 'premium'
                            ? 'border-purple-600 bg-purple-600'
                            : 'border-gray-300'
                        ]"
                      >
                        <div v-if="selectedTier === 'premium'" class="w-2 h-2 bg-white rounded-full"></div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Wallet Balance Info -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-4">
                  <div class="flex items-center justify-between">
                    <div>
                      <p class="text-xs text-blue-600 font-medium">Your Wallet Balance</p>
                      <p class="text-2xl font-bold text-blue-900 mt-1">K{{ walletBalance }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                      <span class="text-2xl">💳</span>
                    </div>
                  </div>
                  <p class="text-xs text-blue-600 mt-2">
                    Payment will be deducted from your wallet
                  </p>
                </div>

                <!-- Insufficient Balance Warning -->
                <div v-if="!hasSufficientBalance" class="bg-red-50 border border-red-200 rounded-xl p-4">
                  <div class="flex gap-3">
                    <ExclamationTriangleIcon class="h-5 w-5 text-red-600 flex-shrink-0 mt-0.5" />
                    <div class="text-sm text-red-800">
                      <p class="font-medium mb-1">Insufficient Balance</p>
                      <p class="text-xs">
                        You need K{{ selectedTier === 'basic' ? '500' : '1,000' }} to purchase this tier. 
                        Please deposit funds to your wallet first.
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Terms -->
                <label class="flex items-start gap-3 cursor-pointer">
                  <input
                    type="checkbox"
                    v-model="termsAccepted"
                    class="mt-1 w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                  />
                  <span class="text-xs text-gray-600">
                    I agree to the <a href="#" class="text-indigo-600 underline">terms and conditions</a> and understand the starter kit purchase is non-refundable.
                  </span>
                </label>

                <!-- Purchase Button -->
                <button
                  @click="handlePurchase"
                  :disabled="!termsAccepted || purchasing || !hasSufficientBalance"
                  class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-4 rounded-xl font-semibold hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg disabled:opacity-50 disabled:cursor-not-allowed active:scale-98"
                >
                  <span v-if="purchasing">Processing...</span>
                  <span v-else-if="!hasSufficientBalance">Insufficient Balance</span>
                  <span v-else>Purchase {{ selectedTier === 'basic' ? 'K500' : 'K1,000' }} from Wallet</span>
                </button>
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
import { 
  XMarkIcon, 
  CheckCircleIcon, 
  ShoppingBagIcon, 
  SparklesIcon,
  InformationCircleIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline';

interface Props {
  show: boolean;
  hasStarterKit: boolean;
  tier?: string;
  shopCredit?: number;
  creditExpiry?: string;
  purchaseDate?: string;
  walletBalance?: number;
}

const props = withDefaults(defineProps<Props>(), {
  walletBalance: 0,
});

const emit = defineEmits(['close', 'success', 'error']);

const selectedTier = ref('basic');
const termsAccepted = ref(false);
const purchasing = ref(false);

// Check if user has sufficient balance
const hasSufficientBalance = computed(() => {
  const requiredAmount = selectedTier.value === 'basic' ? 500 : 1000;
  return props.walletBalance >= requiredAmount;
});

const handlePurchase = async () => {
  if (!termsAccepted.value) {
    emit('error', 'Please accept the terms and conditions');
    return;
  }

  if (!hasSufficientBalance.value) {
    emit('error', 'Insufficient wallet balance');
    return;
  }

  purchasing.value = true;

  try {
    router.post(
      route('mygrownet.starter-kit.store'),
      {
        tier: selectedTier.value,
        payment_method: 'wallet', // Always use wallet
        terms_accepted: termsAccepted.value,
      },
      {
        preserveScroll: true,
        preserveState: false, // Allow state to update
        onSuccess: (page) => {
          emit('success', '🎉 Starter Kit purchased successfully! Check your announcements for details.');
          emit('close');
          // No page reload - Inertia will update the data automatically
        },
        onError: (errors) => {
          const errorMessage = errors.message || errors[Object.keys(errors)[0]] || 'Purchase failed. Please try again.';
          emit('error', errorMessage);
        },
        onFinish: () => {
          purchasing.value = false;
        },
      }
    );
  } catch (error) {
    console.error('Purchase error:', error);
    emit('error', 'Purchase failed. Please try again.');
    purchasing.value = false;
  }
};

const handleUpgrade = () => {
  router.visit(route('mygrownet.starter-kit.upgrade'));
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
