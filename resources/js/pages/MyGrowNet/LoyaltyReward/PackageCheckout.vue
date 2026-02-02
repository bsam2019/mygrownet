<template>
  <MemberLayout>
    <div class="py-6">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <Link :href="route('mygrownet.loyalty-reward.index')" class="text-purple-600 hover:text-purple-700 text-sm mb-2 inline-flex items-center">
            <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
            Back to LGR Dashboard
          </Link>
          <h1 class="text-2xl font-bold text-gray-900 mt-2">
            {{ isUpgrade ? 'Upgrade Package' : 'Purchase Package' }}
          </h1>
          <p class="mt-1 text-sm text-gray-600">
            {{ isUpgrade ? 'Upgrade your LGR package to earn more' : 'Select your payment method to complete purchase' }}
          </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Package Details -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Package Summary -->
            <div class="bg-white rounded-lg shadow-md p-6">
              <h2 class="text-lg font-semibold text-gray-900 mb-4">Package Details</h2>
              
              <div class="space-y-4">
                <div class="flex items-center justify-between pb-4 border-b">
                  <div>
                    <h3 class="text-xl font-bold text-gray-900">{{ package.name }}</h3>
                    <p class="text-sm text-gray-600">{{ package.description }}</p>
                  </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <div class="p-3 bg-purple-50 rounded-lg">
                    <p class="text-sm text-gray-600">Daily LGR Rate</p>
                    <p class="text-xl font-bold text-purple-700">K{{ package.daily_lgr_rate }}</p>
                  </div>
                  <div class="p-3 bg-green-50 rounded-lg">
                    <p class="text-sm text-gray-600">Total Reward</p>
                    <p class="text-xl font-bold text-green-700">K{{ package.total_reward }}</p>
                  </div>
                  <div class="p-3 bg-blue-50 rounded-lg">
                    <p class="text-sm text-gray-600">Duration</p>
                    <p class="text-xl font-bold text-blue-700">{{ package.duration_days }} days</p>
                  </div>
                  <div class="p-3 bg-yellow-50 rounded-lg">
                    <p class="text-sm text-gray-600">ROI</p>
                    <p class="text-xl font-bold text-yellow-700">{{ getRoiPercentage() }}%</p>
                  </div>
                </div>

                <div v-if="package.features && package.features.length > 0" class="pt-4 border-t">
                  <p class="text-sm font-medium text-gray-900 mb-2">Features:</p>
                  <ul class="space-y-1">
                    <li v-for="(feature, index) in package.features" :key="index" class="flex items-start gap-2 text-sm text-gray-600">
                      <CheckIcon class="h-4 w-4 text-green-500 mt-0.5 flex-shrink-0" aria-hidden="true" />
                      <span>{{ feature }}</span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>

            <!-- Payment Method -->
            <div class="bg-white rounded-lg shadow-md p-6">
              <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Method</h2>
              
              <form @submit.prevent="submitPurchase">
                <div class="space-y-4">
                  <!-- Wallet Payment -->
                  <label class="flex items-start gap-3 p-4 border-2 rounded-lg cursor-pointer transition-colors"
                    :class="form.payment_method === 'wallet' ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-purple-300'">
                    <input
                      type="radio"
                      v-model="form.payment_method"
                      value="wallet"
                      class="mt-1"
                    />
                    <div class="flex-1">
                      <div class="flex items-center justify-between">
                        <p class="font-medium text-gray-900">MyGrow Wallet</p>
                        <p class="text-sm font-semibold text-purple-600">K{{ user.wallet_balance }}</p>
                      </div>
                      <p class="text-sm text-gray-600 mt-1">Pay instantly from your wallet balance</p>
                      <p v-if="user.wallet_balance < finalAmount" class="text-sm text-red-600 mt-1">
                        Insufficient balance. Please top up your wallet first.
                      </p>
                    </div>
                  </label>

                  <!-- Mobile Money -->
                  <label class="flex items-start gap-3 p-4 border-2 rounded-lg cursor-pointer transition-colors"
                    :class="form.payment_method === 'mobile_money' ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-purple-300'">
                    <input
                      type="radio"
                      v-model="form.payment_method"
                      value="mobile_money"
                      class="mt-1"
                    />
                    <div class="flex-1">
                      <p class="font-medium text-gray-900">Mobile Money</p>
                      <p class="text-sm text-gray-600 mt-1">MTN MoMo or Airtel Money</p>
                      
                      <div v-if="form.payment_method === 'mobile_money'" class="mt-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                        <input
                          type="tel"
                          v-model="form.phone_number"
                          placeholder="0977123456"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                          required
                        />
                      </div>
                    </div>
                  </label>

                  <!-- Bank Transfer -->
                  <label class="flex items-start gap-3 p-4 border-2 rounded-lg cursor-pointer transition-colors"
                    :class="form.payment_method === 'bank_transfer' ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-purple-300'">
                    <input
                      type="radio"
                      v-model="form.payment_method"
                      value="bank_transfer"
                      class="mt-1"
                    />
                    <div class="flex-1">
                      <p class="font-medium text-gray-900">Bank Transfer</p>
                      <p class="text-sm text-gray-600 mt-1">Transfer to our bank account</p>
                    </div>
                  </label>
                </div>

                <div v-if="form.errors.payment_method" class="mt-2 text-sm text-red-600">
                  {{ form.errors.payment_method }}
                </div>
                <div v-if="form.errors.phone_number" class="mt-2 text-sm text-red-600">
                  {{ form.errors.phone_number }}
                </div>

                <button
                  type="submit"
                  :disabled="form.processing || (form.payment_method === 'wallet' && user.wallet_balance < finalAmount)"
                  class="w-full mt-6 bg-purple-600 text-white py-3 rounded-lg hover:bg-purple-700 transition-colors font-medium disabled:bg-gray-400 disabled:cursor-not-allowed"
                >
                  <span v-if="form.processing">Processing...</span>
                  <span v-else>{{ isUpgrade ? 'Upgrade Now' : 'Complete Purchase' }}</span>
                </button>
              </form>
            </div>
          </div>

          <!-- Order Summary -->
          <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-md p-6 sticky top-6">
              <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h2>
              
              <div class="space-y-3">
                <div v-if="isUpgrade && currentPackage" class="pb-3 border-b">
                  <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Current Package</span>
                    <span class="font-medium text-gray-900">{{ currentPackage.name }}</span>
                  </div>
                  <div class="flex justify-between text-sm mt-1">
                    <span class="text-gray-600">Current Value</span>
                    <span class="font-medium text-gray-900">K{{ currentPackage.package_amount }}</span>
                  </div>
                </div>

                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">{{ isUpgrade ? 'New Package' : 'Package' }}</span>
                  <span class="font-medium text-gray-900">{{ package.name }}</span>
                </div>
                
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Package Value</span>
                  <span class="font-medium text-gray-900">K{{ package.package_amount }}</span>
                </div>

                <div v-if="isUpgrade" class="flex justify-between text-sm text-green-600">
                  <span>Credit Applied</span>
                  <span class="font-medium">-K{{ currentPackage.package_amount }}</span>
                </div>

                <div class="pt-3 border-t">
                  <div class="flex justify-between">
                    <span class="font-semibold text-gray-900">{{ isUpgrade ? 'Upgrade Cost' : 'Total' }}</span>
                    <span class="text-2xl font-bold text-purple-600">K{{ finalAmount }}</span>
                  </div>
                </div>

                <div class="pt-3 border-t text-sm text-gray-600">
                  <p class="mb-2">You will earn:</p>
                  <ul class="space-y-1 ml-4">
                    <li>• K{{ package.daily_lgr_rate }}/day for {{ package.duration_days }} days</li>
                    <li>• Total: K{{ package.total_reward }}</li>
                    <li>• ROI: {{ getRoiPercentage() }}%</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </MemberLayout>
</template>

<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';
import { ArrowLeftIcon, CheckIcon } from 'lucide-vue-next';

interface Package {
  id: number;
  name: string;
  description: string;
  package_amount: number;
  daily_lgr_rate: number;
  duration_days: number;
  total_reward: number;
  features: string[];
}

interface Props {
  package: Package;
  currentPackage: Package | null;
  isUpgrade: boolean;
  upgradeCost: number;
  finalAmount: number;
  user: {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    wallet_balance: number;
  };
}

const props = defineProps<Props>();

const form = useForm({
  payment_method: 'wallet',
  phone_number: props.user.phone || '',
});

const getRoiPercentage = (): string => {
  const roi = (props.package.total_reward / props.package.package_amount) * 100;
  return roi.toFixed(0);
};

const submitPurchase = () => {
  form.post(route('mygrownet.loyalty-reward.packages.purchase', props.package.id), {
    preserveScroll: true,
  });
};
</script>

