<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition-opacity duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="show"
        class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm"
        @click="emit('close')"
      />
    </Transition>

    <Transition
      enter-active-class="transition-transform duration-300 ease-out"
      enter-from-class="translate-y-full"
      enter-to-class="translate-y-0"
      leave-active-class="transition-transform duration-200 ease-in"
      leave-from-class="translate-y-0"
      leave-to-class="translate-y-full"
    >
      <div
        v-if="show"
        class="fixed inset-x-0 bottom-0 z-50 bg-white rounded-t-3xl shadow-2xl max-h-[90vh] flex flex-col"
      >
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200 flex-shrink-0">
          <div>
            <h3 class="text-lg font-bold text-gray-900">LGR Packages</h3>
            <p class="text-xs text-gray-600 mt-0.5">Choose your loyalty reward package</p>
          </div>
          <button
            @click="emit('close')"
            aria-label="Close LGR packages modal"
            class="p-2 hover:bg-gray-100 rounded-full transition-colors"
          >
            <XMarkIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
          </button>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-4 space-y-4">
          <!-- Current Package (if user has one) -->
          <div
            v-if="userPackage"
            class="bg-gradient-to-br from-purple-600 to-indigo-600 rounded-2xl p-4 text-white shadow-lg"
          >
            <div class="flex items-center justify-between mb-3">
              <div>
                <p class="text-sm text-purple-100">Your Current Package</p>
                <h3 class="text-xl font-bold">{{ userPackage.name }}</h3>
              </div>
              <div class="bg-white/20 backdrop-blur-sm rounded-full px-3 py-1">
                <p class="text-xs font-semibold">Active</p>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div class="bg-white/10 backdrop-blur-sm rounded-lg p-2">
                <p class="text-xs text-purple-100">Daily Rate</p>
                <p class="text-lg font-bold">K{{ userPackage.daily_lgr_rate }}</p>
              </div>
              <div class="bg-white/10 backdrop-blur-sm rounded-lg p-2">
                <p class="text-xs text-purple-100">Duration</p>
                <p class="text-lg font-bold">{{ userPackage.duration_days }} days</p>
              </div>
            </div>
          </div>

          <!-- No Package Message -->
          <div
            v-else
            class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-2xl p-4"
          >
            <div class="flex items-start gap-3">
              <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                <SparklesIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
              </div>
              <div>
                <h4 class="font-semibold text-gray-900 mb-1">Get Started with LGR</h4>
                <p class="text-sm text-gray-600">
                  Purchase a package to start earning daily loyalty rewards. The more you engage, the more you earn!
                </p>
              </div>
            </div>
          </div>

          <!-- Available Packages -->
          <div class="space-y-3">
            <h4 class="text-sm font-semibold text-gray-900">
              {{ userPackage ? 'Upgrade Options' : 'Available Packages' }}
            </h4>

            <div
              v-for="pkg in availablePackages"
              :key="pkg.id"
              class="bg-white border-2 rounded-2xl p-4 shadow-sm transition-all"
              :class="{
                'border-purple-300 bg-purple-50': pkg.is_popular,
                'border-gray-200': !pkg.is_popular,
                'opacity-50': userPackage?.id === pkg.id
              }"
            >
              <!-- Popular Badge -->
              <div v-if="pkg.is_popular" class="flex justify-end mb-2">
                <span class="bg-purple-600 text-white text-xs font-bold px-3 py-1 rounded-full">
                  POPULAR
                </span>
              </div>

              <!-- Package Info -->
              <div class="mb-3">
                <h5 class="text-lg font-bold text-gray-900">{{ pkg.name }}</h5>
                <p class="text-sm text-gray-600 mt-1">{{ pkg.description }}</p>
              </div>

              <!-- Stats Grid -->
              <div class="grid grid-cols-3 gap-2 mb-4">
                <div class="bg-gray-50 rounded-lg p-2 text-center">
                  <p class="text-xs text-gray-600">Cost</p>
                  <p class="text-sm font-bold text-gray-900">K{{ pkg.cost }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-2 text-center">
                  <p class="text-xs text-gray-600">Daily</p>
                  <p class="text-sm font-bold text-green-600">K{{ pkg.daily_lgr_rate }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-2 text-center">
                  <p class="text-xs text-gray-600">Total</p>
                  <p class="text-sm font-bold text-purple-600">K{{ pkg.total_reward }}</p>
                </div>
              </div>

              <!-- ROI Badge -->
              <div class="flex items-center justify-between mb-3">
                <span class="text-xs text-gray-600">Return on Investment</span>
                <span class="bg-green-100 text-green-700 text-xs font-bold px-2 py-1 rounded-full">
                  {{ calculateROI(pkg) }}% ROI
                </span>
              </div>

              <!-- Action Button -->
              <button
                v-if="userPackage?.id !== pkg.id"
                @click="selectPackage(pkg)"
                class="w-full py-3 rounded-xl font-semibold transition-all active:scale-95"
                :class="{
                  'bg-purple-600 text-white hover:bg-purple-700': pkg.is_popular,
                  'bg-gray-900 text-white hover:bg-gray-800': !pkg.is_popular
                }"
              >
                {{ userPackage ? 'Upgrade to ' : 'Select ' }}{{ pkg.name }}
              </button>
              <div
                v-else
                class="w-full py-3 rounded-xl font-semibold text-center bg-gray-100 text-gray-500"
              >
                Current Package
              </div>
            </div>
          </div>

          <!-- Info Section -->
          <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
            <h5 class="font-semibold text-gray-900 mb-2 flex items-center gap-2">
              <InformationCircleIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
              How LGR Works
            </h5>
            <ul class="space-y-2 text-sm text-gray-700">
              <li class="flex items-start gap-2">
                <span class="text-blue-600 mt-0.5">•</span>
                <span>Earn daily LGR credits automatically</span>
              </li>
              <li class="flex items-start gap-2">
                <span class="text-blue-600 mt-0.5">•</span>
                <span>Complete activities to boost your earnings</span>
              </li>
              <li class="flex items-start gap-2">
                <span class="text-blue-600 mt-0.5">•</span>
                <span>Transfer LGR to your wallet anytime</span>
              </li>
              <li class="flex items-start gap-2">
                <span class="text-blue-600 mt-0.5">•</span>
                <span>Higher packages = better daily rates</span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import {
  XMarkIcon,
  SparklesIcon,
  InformationCircleIcon,
} from '@heroicons/vue/24/outline';

interface LgrPackage {
  id: number;
  name: string;
  description: string;
  cost: number;
  daily_lgr_rate: number;
  duration_days: number;
  total_reward: number;
  is_popular: boolean;
}

const props = defineProps<{
  show: boolean;
  packages: LgrPackage[];
  userPackage?: LgrPackage | null;
}>();

const emit = defineEmits<{
  close: [];
}>();

const availablePackages = computed(() => {
  return props.packages || [];
});

const calculateROI = (pkg: LgrPackage) => {
  if (!pkg.cost || pkg.cost === 0) return 0;
  return Math.round(((pkg.total_reward - pkg.cost) / pkg.cost) * 100);
};

const selectPackage = (pkg: LgrPackage) => {
  // Navigate to package checkout
  router.visit(route('mygrownet.loyalty-reward.packages.show', pkg.id));
};
</script>
