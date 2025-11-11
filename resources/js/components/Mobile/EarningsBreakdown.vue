<template>
  <div class="bg-white rounded-xl shadow-sm p-6">
    <h3 class="text-base font-bold text-gray-900 mb-4">Earnings Breakdown</h3>
    
    <div class="space-y-3">
      <!-- Referral Commissions -->
      <div class="bg-gradient-to-br from-blue-50 to-blue-100/50 rounded-xl p-4 border border-blue-200/50 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm">
              <UsersIcon class="h-5 w-5 text-white" />
            </div>
            <div>
              <p class="text-sm font-semibold text-gray-900">Referral Commissions</p>
              <p class="text-xs text-gray-600">7-level network earnings</p>
            </div>
          </div>
          <div class="text-right">
            <p class="text-lg font-bold text-blue-700">K{{ formatCurrency(earnings.referral_commissions) }}</p>
          </div>
        </div>
      </div>

      <!-- Community Rewards -->
      <div class="bg-gradient-to-br from-emerald-50 to-emerald-100/50 rounded-xl p-4 border border-emerald-200/50 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm">
              <ChartBarIcon class="h-5 w-5 text-white" />
            </div>
            <div>
              <p class="text-sm font-semibold text-gray-900">Community Rewards</p>
              <p class="text-xs text-gray-600">Quarterly member benefits</p>
            </div>
          </div>
          <div class="text-right">
            <p class="text-lg font-bold text-emerald-700">K{{ formatCurrency(earnings.profit_shares) }}</p>
          </div>
        </div>
      </div>

      <!-- Team Performance -->
      <div class="bg-gradient-to-br from-violet-50 to-violet-100/50 rounded-xl p-4 border border-violet-200/50 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-gradient-to-br from-violet-500 to-violet-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm">
              <TrophyIcon class="h-5 w-5 text-white" />
            </div>
            <div>
              <p class="text-sm font-semibold text-gray-900">Team Performance</p>
              <p class="text-xs text-gray-600">Purchases & subscriptions</p>
            </div>
          </div>
          <div class="text-right">
            <p class="text-lg font-bold text-violet-700">K{{ formatCurrency(earnings.team_performance) }}</p>
          </div>
        </div>
      </div>

      <!-- LGR Daily Bonus (Always Show) -->
      <div class="bg-gradient-to-br from-amber-50 to-yellow-100/50 rounded-xl p-4 border border-amber-200/50 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
          <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm">
              <SparklesIcon class="h-5 w-5 text-white" />
            </div>
            <div>
              <p class="text-sm font-semibold text-gray-900">LGR Daily Bonus</p>
              <p class="text-xs text-gray-600">Loyalty rewards balance</p>
            </div>
          </div>
          <div class="text-right">
            <p class="text-lg font-bold text-amber-700">K{{ formatCurrency(lgrBalance || 0) }}</p>
            <p class="text-xs text-amber-600 font-medium">{{ lgrPercentage }}% transferable</p>
          </div>
        </div>
        <button
          v-if="lgrWithdrawable > 0 && !lgrBlocked"
          @click="emit('transfer-lgr')"
          class="w-full bg-gradient-to-r from-amber-500 to-amber-600 text-white py-2.5 rounded-lg text-sm font-semibold hover:from-amber-600 hover:to-amber-700 transition-all shadow-sm hover:shadow active:scale-[0.98]"
        >
          Transfer K{{ formatCurrency(lgrWithdrawable) }} to Wallet
        </button>
        <div v-else class="bg-amber-100/50 rounded-lg py-2 px-3">
          <p v-if="lgrBlocked" class="text-xs text-center text-red-700 font-medium">
            LGR transfers are currently blocked
          </p>
          <p v-else-if="lgrBalance === 0" class="text-xs text-center text-amber-700">
            Earn LGR through daily activities and purchases
          </p>
          <p v-else class="text-xs text-center text-amber-700">
            No transferable LGR available yet
          </p>
        </div>
      </div>

      <!-- Pending Earnings -->
      <div v-if="earnings.pending_earnings > 0" class="bg-gradient-to-br from-orange-50 to-orange-100/50 rounded-xl p-4 border border-orange-200/50 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm">
              <ClockIcon class="h-5 w-5 text-white" />
            </div>
            <div>
              <p class="text-sm font-semibold text-gray-900">Pending Earnings</p>
              <p class="text-xs text-gray-600">Awaiting approval</p>
            </div>
          </div>
          <div class="text-right">
            <p class="text-lg font-bold text-orange-700">K{{ formatCurrency(earnings.pending_earnings) }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Total -->
    <div class="mt-4 pt-4 border-t-2 border-gray-100">
      <div class="bg-gradient-to-br from-slate-50 to-gray-100/50 rounded-xl p-4 border border-gray-200">
        <div class="flex items-center justify-between">
          <p class="text-sm font-semibold text-gray-700">Total Earnings</p>
          <p class="text-2xl font-bold text-gray-900">K{{ formatCurrency(earnings.total_earnings) }}</p>
        </div>
      </div>
    </div>

    <!-- Info Note -->
    <div class="mt-4 bg-blue-50 border border-blue-200 rounded-xl p-3.5">
      <div class="flex gap-2.5">
        <InformationCircleIcon class="h-5 w-5 text-blue-600 flex-shrink-0 mt-0.5" />
        <p class="text-xs text-blue-900 leading-relaxed">
          Earnings are automatically added to your wallet balance when approved.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { UsersIcon, ChartBarIcon, ClockIcon, InformationCircleIcon, SparklesIcon } from '@heroicons/vue/24/outline';
import { TrophyIcon } from '@heroicons/vue/24/solid';

interface Earnings {
  referral_commissions: number;
  profit_shares: number;
  team_performance: number;
  pending_earnings: number;
  total_earnings: number;
}

interface Props {
  earnings: Earnings;
  lgrBalance?: number;
  lgrWithdrawable?: number;
  lgrPercentage?: number;
  lgrBlocked?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  lgrBalance: 0,
  lgrWithdrawable: 0,
  lgrPercentage: 40,
  lgrBlocked: false,
});

const emit = defineEmits(['transfer-lgr']);

const formatCurrency = (value: number): string => {
  return value.toFixed(2);
};
</script>
