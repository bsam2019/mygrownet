<template>
  <MemberLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-gray-900">Loyalty Growth Reward (LGR)</h1>
          <p class="mt-1 text-sm text-gray-600">
            Earn daily credits by staying active on the platform
          </p>
        </div>

        <!-- Current Package (if user has one) -->
        <div v-if="userPackage" class="bg-gradient-to-r from-purple-500 to-indigo-600 rounded-lg shadow-lg p-6 mb-6 text-white">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-lg font-semibold">Your LGR Package</h2>
              <p class="text-3xl font-bold mt-2">{{ userPackage.name }}</p>
              <p class="text-sm text-purple-100 mt-1">K{{ userPackage.daily_lgr_rate }}/day for {{ userPackage.duration_days }} days</p>
            </div>
            <div class="text-right">
              <p class="text-sm text-purple-100">Total Reward</p>
              <p class="text-3xl font-bold">K{{ userPackage.total_reward }}</p>
            </div>
          </div>
        </div>

        <!-- Qualification Status -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Qualification Status</h2>
          
          <div v-if="qualification.is_qualified" class="space-y-4">
            <div class="flex items-center gap-3 text-green-600">
              <CheckCircleIcon class="h-6 w-6" aria-hidden="true" />
              <span class="font-medium">You are qualified for LGR!</span>
            </div>
            
            <div v-if="cycle" class="mt-4">
              <div class="flex items-center justify-between text-sm mb-2">
                <span class="text-gray-600">Cycle Progress</span>
                <span class="font-medium text-gray-900">Day {{ cycle.days_completed }}/{{ cycle.total_days }}</span>
              </div>
              <div class="bg-gray-200 rounded-full h-3">
                <div 
                  class="bg-gradient-to-r from-purple-500 to-indigo-600 rounded-full h-3 transition-all duration-500"
                  :style="{ width: `${(cycle.days_completed / cycle.total_days) * 100}%` }"
                ></div>
              </div>
              <p class="text-xs text-gray-500 mt-2">
                K{{ cycle.total_earned }} earned so far
              </p>
            </div>
          </div>

          <div v-else class="space-y-4">
            <div class="flex items-center gap-3 text-yellow-600">
              <AlertCircleIcon class="h-6 w-6" aria-hidden="true" />
              <span class="font-medium">Not yet qualified</span>
            </div>
            
            <div class="text-sm text-gray-600">
              <p class="mb-2">To qualify for LGR, you need to:</p>
              <ul class="list-disc list-inside space-y-1 ml-4">
                <li>Purchase an LGR package</li>
                <li>Complete daily activities to earn credits</li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Available Packages -->
        <div v-if="packages && packages.length > 0" class="mb-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Available LGR Packages</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div
              v-for="pkg in packages"
              :key="pkg.id"
              class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow overflow-hidden border-2"
              :class="userPackage?.id === pkg.id ? 'border-purple-500' : 'border-transparent'"
            >
              <div class="p-6">
                <!-- Package Name -->
                <h3 class="text-xl font-bold text-gray-900 mb-2">{{ pkg.name }}</h3>
                
                <!-- Package Amount -->
                <div class="mb-4">
                  <p class="text-3xl font-bold text-purple-600">K{{ pkg.package_amount }}</p>
                  <p class="text-sm text-gray-600">One-time payment</p>
                </div>

                <!-- Daily Rate -->
                <div class="mb-4 p-3 bg-purple-50 rounded-lg">
                  <p class="text-sm text-gray-600">Daily LGR Rate</p>
                  <p class="text-2xl font-bold text-purple-700">K{{ pkg.daily_lgr_rate }}</p>
                  <p class="text-xs text-gray-500">for {{ pkg.duration_days }} days</p>
                </div>

                <!-- Total Reward -->
                <div class="mb-4 p-3 bg-green-50 rounded-lg">
                  <p class="text-sm text-gray-600">Total Reward</p>
                  <p class="text-2xl font-bold text-green-700">K{{ pkg.total_reward }}</p>
                  <p class="text-xs text-gray-500">{{ getRoiPercentage(pkg) }}% ROI</p>
                </div>

                <!-- Features -->
                <div v-if="pkg.features && pkg.features.length > 0" class="mb-4">
                  <ul class="space-y-1">
                    <li v-for="(feature, index) in pkg.features" :key="index" class="flex items-start gap-2 text-sm text-gray-600">
                      <CheckIcon class="h-4 w-4 text-green-500 mt-0.5 flex-shrink-0" aria-hidden="true" />
                      <span>{{ feature }}</span>
                    </li>
                  </ul>
                </div>

                <!-- Action Button -->
                <Link
                  v-if="userPackage?.id !== pkg.id"
                  :href="route('mygrownet.loyalty-reward.packages.show', pkg.id)"
                  class="block w-full bg-purple-600 text-white py-2 rounded-lg hover:bg-purple-700 transition-colors font-medium text-center"
                >
                  {{ userPackage ? 'Upgrade' : 'Select Package' }}
                </Link>
                <div v-else class="w-full bg-green-100 text-green-800 py-2 rounded-lg text-center font-medium">
                  Current Package
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Daily Activities -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Earn Daily LGR Credits</h2>
          
          <p class="text-sm text-gray-600 mb-4">
            Complete at least ONE activity each day to earn your LGR credits:
          </p>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <Link
              :href="route('learning.index')"
              class="flex items-center gap-3 p-4 border border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors"
            >
              <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                <BookOpenIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
              </div>
              <div>
                <p class="font-medium text-gray-900">Complete Learning Module</p>
                <p class="text-xs text-gray-500">Earn credits by learning</p>
              </div>
            </Link>

            <Link
              :href="route('events.index')"
              class="flex items-center gap-3 p-4 border border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition-colors"
            >
              <div class="flex-shrink-0 w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                <CalendarIcon class="h-5 w-5 text-purple-600" aria-hidden="true" />
              </div>
              <div>
                <p class="font-medium text-gray-900">Attend Live Event</p>
                <p class="text-xs text-gray-500">Check in to events</p>
              </div>
            </Link>

            <div class="flex items-center gap-3 p-4 border border-gray-200 rounded-lg bg-gray-50">
              <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                <ShoppingBagIcon class="h-5 w-5 text-green-600" aria-hidden="true" />
              </div>
              <div>
                <p class="font-medium text-gray-900">Make Marketplace Purchase</p>
                <p class="text-xs text-gray-500">Buy from marketplace</p>
              </div>
            </div>

            <div class="flex items-center gap-3 p-4 border border-gray-200 rounded-lg bg-gray-50">
              <div class="flex-shrink-0 w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                <UsersIcon class="h-5 w-5 text-yellow-600" aria-hidden="true" />
              </div>
              <div>
                <p class="font-medium text-gray-900">Refer New Member</p>
                <p class="text-xs text-gray-500">Invite someone to join</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </MemberLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';
import { 
  CheckCircleIcon, 
  AlertCircleIcon, 
  CheckIcon,
  BookOpenIcon,
  CalendarIcon,
  ShoppingBagIcon,
  UsersIcon
} from 'lucide-vue-next';

interface Package {
  id: number;
  name: string;
  package_amount: number;
  daily_lgr_rate: number;
  duration_days: number;
  total_reward: number;
  features: string[];
}

interface Qualification {
  is_qualified: boolean;
}

interface Cycle {
  days_completed: number;
  total_days: number;
  total_earned: number;
}

interface Props {
  qualification: Qualification;
  cycle: Cycle | null;
  packages: Package[];
  userPackage: Package | null;
  user: {
    loyalty_points: number;
    bonus_balance: number;
  };
}

const props = defineProps<Props>();

const getRoiPercentage = (pkg: Package): string => {
  const roi = (pkg.total_reward / pkg.package_amount) * 100;
  return roi.toFixed(0);
};
</script>
