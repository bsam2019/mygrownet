<template>
  <MemberLayout>
    <Head title="Points Dashboard" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- Page Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-gradient-to-r from-blue-600 to-indigo-600 text-white">
            <h1 class="text-3xl font-bold">Your Points Dashboard</h1>
            <p class="mt-2 text-blue-100">Track your progress and earn rewards</p>
          </div>
        </div>

        <!-- Points Overview Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Lifetime Points -->
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-gray-600">Lifetime Points</p>
                  <p class="text-3xl font-bold text-gray-900 mt-2">
                    {{ userPoints?.lifetime_points?.toLocaleString() || 0 }} LP
                  </p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                  <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                  </svg>
                </div>
              </div>
              <p class="text-xs text-gray-500 mt-2">Never expires • For level progression</p>
            </div>
          </div>

          <!-- Monthly Points -->
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-gray-600">Monthly Points</p>
                  <p class="text-3xl font-bold text-gray-900 mt-2">
                    {{ userPoints?.monthly_points?.toLocaleString() || 0 }} MAP
                  </p>
                </div>
                <div class="p-3 bg-emerald-100 rounded-full">
                  <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
              </div>
              <p class="text-xs text-gray-500 mt-2">Resets monthly • For earnings qualification</p>
            </div>
          </div>

          <!-- Streak & Multiplier -->
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-gray-600">Active Streak</p>
                  <p class="text-3xl font-bold text-gray-900 mt-2">
                    {{ userPoints?.current_streak_months || 0 }} months 🔥
                  </p>
                </div>
                <div class="p-3 bg-amber-100 rounded-full">
                  <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                  </svg>
                </div>
              </div>
              <p class="text-xs text-gray-500 mt-2">{{ userPoints?.active_multiplier }}x multiplier active</p>
            </div>
          </div>
        </div>

        <!-- Monthly Qualification Status -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-xl font-semibold text-gray-900">Monthly Qualification</h2>
              <span v-if="isQualified" class="px-3 py-1 bg-emerald-100 text-emerald-800 text-sm font-medium rounded-full">
                ✓ Qualified
              </span>
              <span v-else class="px-3 py-1 bg-amber-100 text-amber-800 text-sm font-medium rounded-full">
                ⚠ {{ daysLeftInMonth }} days left
              </span>
            </div>

            <div class="space-y-3">
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Progress</span>
                <span class="font-medium text-gray-900">
                  {{ userPoints?.monthly_points || 0 }} / {{ currentMonthActivity?.map_required || 100 }} MAP
                </span>
              </div>
              
              <div class="w-full bg-gray-200 rounded-full h-3">
                <div 
                  class="h-3 rounded-full transition-all duration-500"
                  :class="isQualified ? 'bg-emerald-500' : 'bg-amber-500'"
                  :style="{ width: qualificationProgress + '%' }"
                ></div>
              </div>

              <div v-if="!isQualified" class="mt-4 p-4 bg-amber-50 rounded-lg">
                <p class="text-sm font-medium text-amber-900 mb-2">
                  You need {{ (currentMonthActivity?.map_required || 100) - (userPoints?.monthly_points || 0) }} more MAP to qualify
                </p>
                <p class="text-xs text-amber-700">Quick actions: Complete a course (+30 MAP), Make a sale (+10 MAP/K100), Login daily (+5 MAP)</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Level Progress -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Level Progress</h2>
            
            <div class="flex items-center justify-between mb-6">
              <div>
                <p class="text-sm text-gray-600">Current Level</p>
                <p class="text-2xl font-bold text-blue-600 capitalize">{{ levelProgress?.current_level }}</p>
              </div>
              <div class="text-center">
                <svg class="w-8 h-8 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
              </div>
              <div>
                <p class="text-sm text-gray-600">Next Level</p>
                <p class="text-2xl font-bold text-emerald-600 capitalize">{{ levelProgress?.next_level || 'Max Level' }}</p>
              </div>
            </div>

            <div v-if="levelProgress?.next_level" class="space-y-4">
              <div class="flex justify-between text-sm mb-2">
                <span class="text-gray-600">Overall Progress</span>
                <span class="font-medium text-gray-900">{{ Math.round(levelProgress?.progress || 0) }}%</span>
              </div>
              
              <div class="w-full bg-gray-200 rounded-full h-3">
                <div 
                  class="bg-blue-600 h-3 rounded-full transition-all duration-500"
                  :style="{ width: (levelProgress?.progress || 0) + '%' }"
                ></div>
              </div>

              <!-- Requirements Checklist -->
              <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div v-for="(req, key) in levelProgress?.met" :key="key" class="flex items-center space-x-3">
                  <div class="flex-shrink-0">
                    <svg v-if="req.met" class="w-6 h-6 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <svg v-else class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                  </div>
                  <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900 capitalize">{{ key.replace('_', ' ') }}</p>
                    <p class="text-xs text-gray-500">{{ req.current }} / {{ req.required }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Recent Activity</h2>
            
            <div class="space-y-3">
              <div 
                v-for="transaction in recentTransactions" 
                :key="transaction.id"
                class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition"
              >
                <div class="flex-1">
                  <p class="text-sm font-medium text-gray-900">{{ transaction.description }}</p>
                  <p class="text-xs text-gray-500">{{ formatDate(transaction.created_at) }}</p>
                </div>
                <div class="text-right">
                  <p v-if="transaction.lp_amount > 0" class="text-sm font-semibold text-blue-600">
                    +{{ transaction.lp_amount }} LP
                  </p>
                  <p v-if="transaction.map_amount > 0" class="text-sm font-semibold text-emerald-600">
                    +{{ transaction.map_amount }} MAP
                  </p>
                </div>
              </div>

              <div v-if="!recentTransactions || recentTransactions.length === 0" class="text-center py-8 text-gray-500">
                No transactions yet. Start earning points!
              </div>
            </div>
          </div>
        </div>

        <!-- Badges -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Your Badges</h2>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div 
                v-for="badge in badges" 
                :key="badge.id"
                class="text-center p-4 bg-gradient-to-br from-amber-50 to-yellow-50 rounded-lg border-2 border-amber-200"
              >
                <div class="text-4xl mb-2">🏆</div>
                <p class="text-sm font-semibold text-gray-900">{{ badge.badge_name }}</p>
                <p class="text-xs text-gray-600 mt-1">{{ badge.lp_reward }} LP</p>
                <p class="text-xs text-gray-500 mt-1">{{ formatDate(badge.earned_at) }}</p>
              </div>

              <div v-if="!badges || badges.length === 0" class="col-span-full text-center py-8 text-gray-500">
                No badges earned yet. Keep going!
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </MemberLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';

const props = defineProps({
  userPoints: Object,
  levelProgress: Object,
  recentTransactions: Array,
  monthlyHistory: Array,
  badges: Array,
  currentMonthActivity: Object,
  isQualified: Boolean,
  daysLeftInMonth: Number,
});

const qualificationProgress = computed(() => {
  const current = props.userPoints?.monthly_points || 0;
  const required = props.currentMonthActivity?.map_required || 100;
  return Math.min(100, (current / required) * 100);
});

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};
</script>
