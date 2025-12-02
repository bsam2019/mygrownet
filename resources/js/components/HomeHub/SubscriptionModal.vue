<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { XMarkIcon } from '@heroicons/vue/24/outline';

interface SubscriptionTier {
  name: string;
  price: number;
  billing_cycle: string;
  features?: Record<string, any>;
  user_limit?: number | string;
}

interface Module {
  id: string;
  name: string;
  slug: string;
  description: string | null;
  color: string | null;
  subscription_tiers: Record<string, SubscriptionTier> | null;
}

interface Props {
  module: Module | null;
  show: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'subscribed'): void;
}>();

const selectedTier = ref<string>('');
const selectedBillingCycle = ref<string>('monthly');

const form = useForm({
  module_id: '',
  tier: '',
  amount: 0,
  currency: 'ZMW',
  billing_cycle: 'monthly',
});

const tiers = computed(() => {
  if (!props.module?.subscription_tiers) return [];
  return Object.entries(props.module.subscription_tiers).map(([key, tier]) => ({
    key,
    ...tier,
  }));
});

const selectedTierData = computed(() => {
  if (!selectedTier.value || !props.module?.subscription_tiers) return null;
  return props.module.subscription_tiers[selectedTier.value];
});

const selectTier = (tierKey: string) => {
  selectedTier.value = tierKey;
  const tier = props.module?.subscription_tiers?.[tierKey];
  if (tier) {
    form.tier = tierKey;
    form.amount = tier.price;
    form.billing_cycle = tier.billing_cycle || 'monthly';
  }
};

const submit = () => {
  if (!props.module || !selectedTier.value) return;
  
  form.module_id = props.module.id;
  form.post(route('subscriptions.store'), {
    onSuccess: () => {
      emit('subscribed');
      emit('close');
    },
  });
};

const close = () => {
  selectedTier.value = '';
  form.reset();
  emit('close');
};

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('en-ZM', {
    style: 'currency',
    currency: 'ZMW',
  }).format(price);
};
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/50" @click="close" />
        
        <!-- Modal -->
        <div class="flex min-h-full items-center justify-center p-4">
          <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
          >
            <div
              v-if="show && module"
              class="relative w-full max-w-2xl bg-white rounded-xl shadow-xl"
            >
              <!-- Header -->
              <div
                class="flex items-center justify-between p-6 border-b"
                :style="{ backgroundColor: module.color || '#6366f1' }"
              >
                <div class="text-white">
                  <h2 class="text-xl font-bold">Subscribe to {{ module.name }}</h2>
                  <p class="text-sm opacity-90">{{ module.description }}</p>
                </div>
                <button
                  @click="close"
                  class="p-2 rounded-full hover:bg-white/20 text-white"
                  aria-label="Close subscription modal"
                >
                  <XMarkIcon class="w-6 h-6" aria-hidden="true" />
                </button>
              </div>

              <!-- Content -->
              <div class="p-6">
                <!-- Tier Selection -->
                <div class="mb-6">
                  <h3 class="text-lg font-semibold text-gray-900 mb-4">Choose a Plan</h3>
                  
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div
                      v-for="tier in tiers"
                      :key="tier.key"
                      @click="selectTier(tier.key)"
                      :class="[
                        'p-4 border-2 rounded-lg cursor-pointer transition-all',
                        selectedTier === tier.key
                          ? 'border-blue-600 bg-blue-50'
                          : 'border-gray-200 hover:border-gray-300'
                      ]"
                    >
                      <div class="flex justify-between items-start mb-2">
                        <h4 class="font-semibold text-gray-900">{{ tier.name }}</h4>
                        <div
                          :class="[
                            'w-5 h-5 rounded-full border-2 flex items-center justify-center',
                            selectedTier === tier.key
                              ? 'border-blue-600 bg-blue-600'
                              : 'border-gray-300'
                          ]"
                        >
                          <svg
                            v-if="selectedTier === tier.key"
                            class="w-3 h-3 text-white"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                          >
                            <path
                              fill-rule="evenodd"
                              d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                              clip-rule="evenodd"
                            />
                          </svg>
                        </div>
                      </div>
                      
                      <div class="text-2xl font-bold text-gray-900 mb-2">
                        {{ tier.price === 0 ? 'Free' : formatPrice(tier.price) }}
                        <span v-if="tier.price > 0" class="text-sm font-normal text-gray-500">
                          /{{ tier.billing_cycle }}
                        </span>
                      </div>

                      <!-- Features -->
                      <ul v-if="tier.features" class="text-sm text-gray-600 space-y-1">
                        <li
                          v-for="(value, feature) in tier.features"
                          :key="feature"
                          class="flex items-center gap-2"
                        >
                          <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path
                              fill-rule="evenodd"
                              d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                              clip-rule="evenodd"
                            />
                          </svg>
                          <span class="capitalize">{{ String(feature).replace(/_/g, ' ') }}: {{ value === true ? '✓' : value }}</span>
                        </li>
                      </ul>

                      <div v-if="tier.user_limit" class="mt-2 text-sm text-gray-500">
                        {{ tier.user_limit === 'unlimited' ? 'Unlimited users' : `Up to ${tier.user_limit} users` }}
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Error Message -->
                <div v-if="form.errors.module_id || form.errors.tier" class="mb-4 p-3 bg-red-50 text-red-700 rounded-lg text-sm">
                  {{ form.errors.module_id || form.errors.tier }}
                </div>
              </div>

              <!-- Footer -->
              <div class="flex items-center justify-end gap-3 p-6 border-t bg-gray-50">
                <button
                  @click="close"
                  class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                >
                  Cancel
                </button>
                <button
                  @click="submit"
                  :disabled="!selectedTier || form.processing"
                  :class="[
                    'px-6 py-2 rounded-lg font-medium transition-colors',
                    selectedTier && !form.processing
                      ? 'bg-blue-600 text-white hover:bg-blue-700'
                      : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                  ]"
                >
                  {{ form.processing ? 'Processing...' : selectedTierData?.price === 0 ? 'Start Free' : 'Subscribe' }}
                </button>
              </div>
            </div>
          </Transition>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
