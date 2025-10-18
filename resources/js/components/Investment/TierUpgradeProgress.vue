<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-100">
    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center">
          <div class="p-2 bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-lg mr-3">
            <StarIcon class="h-6 w-6 text-white" />
          </div>
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Tier Upgrade Progress</h3>
            <p class="text-sm text-gray-500">Track your progress to the next tier</p>
          </div>
        </div>
        <Link 
          :href="route('tiers.compare')" 
          class="text-sm text-primary-600 hover:text-primary-700 font-medium flex items-center"
        >
          Compare Tiers
          <ArrowRightIcon class="h-4 w-4 ml-1" />
        </Link>
      </div>

      <!-- Current Tier Display -->
      <div v-if="currentTier" data-testid="current-tier" class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
        <div class="flex items-center justify-between mb-3">
          <div class="flex items-center">
            <div :class="getTierColorClass(currentTier.name)" class="w-8 h-8 rounded-full flex items-center justify-center mr-3">
              <StarIcon class="h-4 w-4 text-white" />
            </div>
            <div>
              <h4 class="text-lg font-semibold text-gray-900">{{ currentTier.name }} Tier</h4>
              <p class="text-sm text-gray-600">Your current investment tier</p>
            </div>
          </div>
          <div class="text-right">
            <div class="text-sm text-gray-600">Total Investment</div>
            <div class="text-lg font-bold text-gray-900">{{ formatCurrency(currentInvestment) }}</div>
          </div>
        </div>

        <!-- Current Tier Benefits -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
          <div class="text-center p-3 bg-white rounded-lg">
            <div class="text-lg font-bold text-green-600">{{ formatPercentage(currentTier.fixed_profit_rate) }}</div>
            <div class="text-xs text-gray-500">Annual Profit</div>
          </div>
          <div class="text-center p-3 bg-white rounded-lg">
            <div class="text-lg font-bold text-blue-600">{{ formatPercentage(currentTier.direct_referral_rate) }}</div>
            <div class="text-xs text-gray-500">Referral Commission</div>
          </div>
          <div class="text-center p-3 bg-white rounded-lg">
            <div class="text-lg font-bold text-purple-600">{{ formatPercentage(currentTier.level_2_rate || 0) }}</div>
            <div class="text-xs text-gray-500">Level 2 Bonus</div>
          </div>
          <div class="text-center p-3 bg-white rounded-lg">
            <div class="text-lg font-bold text-yellow-600">{{ formatPercentage(currentTier.reinvestment_bonus_rate || 0) }}</div>
            <div class="text-xs text-gray-500">Reinvestment Bonus</div>
          </div>
        </div>
      </div>

      <!-- Upgrade Progress -->
      <div v-if="nextTier" data-testid="upgrade-progress" class="mb-6">
        <div class="flex items-center justify-between mb-3">
          <h4 class="text-md font-semibold text-gray-900">Progress to {{ nextTier.name }} Tier</h4>
          <span class="text-sm font-medium text-blue-600">{{ Math.round(upgradeProgress) }}%</span>
        </div>

        <!-- Progress Bar -->
        <div class="relative">
          <div class="w-full bg-gray-200 rounded-full h-4 mb-3">
            <div 
              data-testid="progress-bar"
              class="bg-gradient-to-r from-blue-500 to-purple-600 h-4 rounded-full transition-all duration-700 relative overflow-hidden"
              :style="{ width: upgradeProgress + '%' }"
            >
              <div class="absolute inset-0 bg-white opacity-20 animate-pulse"></div>
            </div>
          </div>
          
          <!-- Progress Markers -->
          <div class="flex justify-between text-xs text-gray-500 mb-4">
            <span>{{ formatCurrency(currentTier?.minimum_investment || 0) }}</span>
            <span>{{ formatCurrency(nextTier.minimum_investment) }}</span>
          </div>
        </div>

        <!-- Investment Gap -->
        <div class="flex items-center justify-between p-4 bg-yellow-50 rounded-lg border border-yellow-200 mb-4">
          <div class="flex items-center">
            <ExclamationTriangleIcon class="h-5 w-5 text-yellow-600 mr-2" />
            <div>
              <div class="text-sm font-medium text-yellow-800">
                {{ formatCurrency(remainingAmount) }} more needed
              </div>
              <div class="text-xs text-yellow-700">
                to upgrade to {{ nextTier.name }} tier
              </div>
            </div>
          </div>
          <Link 
            :href="route('investments.tier-upgrade')"
            data-testid="upgrade-button"
            class="bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-yellow-700 transition-colors"
          >
            Upgrade Now
          </Link>
        </div>

        <!-- Next Tier Benefits Preview -->
        <div class="p-4 bg-green-50 rounded-lg border border-green-200">
          <h5 class="text-sm font-medium text-green-800 mb-3">{{ nextTier.name }} Tier Benefits:</h5>
          <div class="grid grid-cols-2 gap-4">
            <div class="flex justify-between items-center">
              <span class="text-sm text-green-700">Annual Profit Rate:</span>
              <div class="flex items-center">
                <span class="text-sm font-medium text-green-800">{{ formatPercentage(nextTier.fixed_profit_rate) }}</span>
                <span class="text-xs text-green-600 ml-1">
                  (+{{ formatPercentage(nextTier.fixed_profit_rate - (currentTier?.fixed_profit_rate || 0)) }})
                </span>
              </div>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm text-green-700">Referral Commission:</span>
              <div class="flex items-center">
                <span class="text-sm font-medium text-green-800">{{ formatPercentage(nextTier.direct_referral_rate) }}</span>
                <span class="text-xs text-green-600 ml-1">
                  (+{{ formatPercentage(nextTier.direct_referral_rate - (currentTier?.direct_referral_rate || 0)) }})
                </span>
              </div>
            </div>
            <div v-if="nextTier.level_2_rate" class="flex justify-between items-center">
              <span class="text-sm text-green-700">Level 2 Bonus:</span>
              <div class="flex items-center">
                <span class="text-sm font-medium text-green-800">{{ formatPercentage(nextTier.level_2_rate) }}</span>
                <span class="text-xs text-green-600 ml-1">
                  (+{{ formatPercentage(nextTier.level_2_rate - (currentTier?.level_2_rate || 0)) }})
                </span>
              </div>
            </div>
            <div v-if="nextTier.reinvestment_bonus_rate" class="flex justify-between items-center">
              <span class="text-sm text-green-700">Reinvestment Bonus:</span>
              <div class="flex items-center">
                <span class="text-sm font-medium text-green-800">{{ formatPercentage(nextTier.reinvestment_bonus_rate) }}</span>
                <span class="text-xs text-green-600 ml-1">
                  (+{{ formatPercentage(nextTier.reinvestment_bonus_rate - (currentTier?.reinvestment_bonus_rate || 0)) }})
                </span>
              </div>
            </div>
          </div>

          <!-- Potential Earnings Increase -->
          <div v-if="potentialEarningsIncrease > 0" class="mt-3 pt-3 border-t border-green-200">
            <div class="flex justify-between items-center">
              <span class="text-sm font-medium text-green-800">Potential Annual Increase:</span>
              <span class="text-lg font-bold text-green-600">+{{ formatCurrency(potentialEarningsIncrease) }}</span>
            </div>
            <div class="text-xs text-green-700 mt-1">
              Based on your current investment of {{ formatCurrency(currentInvestment) }}
            </div>
          </div>
        </div>
      </div>

      <!-- Max Tier Reached -->
      <div v-else class="text-center py-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-r from-yellow-400 to-yellow-600 mb-4">
          <StarIcon class="h-8 w-8 text-white" />
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Maximum Tier Reached!</h3>
        <p class="text-gray-500 max-w-md mx-auto">
          Congratulations! You've reached the highest tier and are enjoying maximum benefits.
        </p>
        <div class="mt-4 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
          <div class="text-sm font-medium text-yellow-800 mb-2">Elite Tier Benefits:</div>
          <div class="grid grid-cols-2 gap-4 text-sm text-yellow-700">
            <div>{{ formatPercentage(currentTier?.fixed_profit_rate || 0) }} Annual Profit</div>
            <div>{{ formatPercentage(currentTier?.direct_referral_rate || 0) }} Referral Commission</div>
            <div>{{ formatPercentage(currentTier?.level_2_rate || 0) }} Level 2 Bonus</div>
            <div>{{ formatPercentage(currentTier?.reinvestment_bonus_rate || 0) }} Reinvestment Bonus</div>
          </div>
        </div>
      </div>

      <!-- Tier Comparison Link -->
      <div class="mt-6 pt-6 border-t border-gray-200">
        <Link 
          :href="route('tiers.compare')"
          class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
        >
          <ChartBarIcon class="h-4 w-4 mr-2" />
          Compare All Tiers
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { 
  StarIcon,
  ArrowRightIcon,
  ExclamationTriangleIcon,
  ChartBarIcon
} from '@heroicons/vue/24/outline';
import { formatCurrency, formatPercentage } from '@/utils/formatting';

interface Tier {
  name: string;
  minimum_investment: number;
  fixed_profit_rate: number;
  direct_referral_rate: number;
  level_2_rate?: number;
  level_3_rate?: number;
  reinvestment_bonus_rate?: number;
}

interface Props {
  currentTier?: Tier;
  nextTier?: Tier;
  currentInvestment: number;
  upgradeProgress: number;
  remainingAmount: number;
}

const props = withDefaults(defineProps<Props>(), {
  currentInvestment: 0,
  upgradeProgress: 0,
  remainingAmount: 0,
});

const potentialEarningsIncrease = computed(() => {
  if (!props.currentTier || !props.nextTier || props.currentInvestment === 0) {
    return 0;
  }
  
  const currentRate = props.currentTier.fixed_profit_rate / 100;
  const nextRate = props.nextTier.fixed_profit_rate / 100;
  const rateDifference = nextRate - currentRate;
  
  return props.currentInvestment * rateDifference;
});

const getTierColorClass = (tierName: string): string => {
  const classes: Record<string, string> = {
    'Basic': 'bg-gray-500',
    'Starter': 'bg-blue-500',
    'Builder': 'bg-blue-600',
    'Leader': 'bg-blue-700',
    'Elite': 'bg-indigo-600',
  };
  return classes[tierName] || 'bg-gray-500';
};
</script>