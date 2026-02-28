<template>
  <div class="min-h-full w-full bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-600 flex flex-col p-6 py-8 text-white relative overflow-hidden">
    <!-- Background decorations -->
    <div class="absolute top-0 left-0 w-64 h-64 bg-white/10 rounded-full -ml-32 -mt-32"></div>
    <div class="absolute bottom-0 right-0 w-48 h-48 bg-white/5 rounded-full -mr-24 -mb-24"></div>
    
    <div class="relative z-10 max-w-2xl mx-auto text-center">
      <!-- Section badge -->
      <div class="inline-flex items-center px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full mb-4">
        <span class="text-white text-sm font-semibold">📊 7-Level Commissions</span>
      </div>
      
      <h2 class="text-2xl md:text-3xl font-bold mb-2">
        Earn From 7 Levels Deep
      </h2>
      <p class="text-purple-200 mb-6 text-sm">
        Build once, earn forever from your entire network
      </p>
      
      <!-- Commission table -->
      <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 border border-white/20 mb-4">
        <div class="space-y-2">
          <!-- Top 3 Levels - Detailed -->
          <div 
            v-for="(rate, index) in topLevels" 
            :key="rate.level"
            class="flex items-center justify-between p-3 rounded-xl bg-gradient-to-r"
            :class="levelColors[index]?.bg || 'from-gray-500/30 to-gray-600/30'"
          >
            <div class="flex items-center gap-3">
              <div 
                class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm"
                :class="levelColors[index]?.badge || 'bg-gray-500'"
              >
                {{ rate.level }}
              </div>
              <div class="text-left">
                <div class="font-semibold text-sm">{{ rate.name }}</div>
                <div class="text-xs text-purple-200">{{ rate.positions }} positions</div>
              </div>
            </div>
            <div class="text-right">
              <div class="font-bold text-green-400">{{ rate.rate }}%</div>
              <div class="text-xs text-purple-200">K{{ calculatePerReferral(rate.rate) }}/referral</div>
            </div>
          </div>
          
          <!-- Levels 4-7 compact -->
          <div class="grid grid-cols-2 gap-2 pt-2">
            <div 
              v-for="(rate, index) in remainingLevels" 
              :key="rate.level"
              class="flex items-center justify-between p-2 bg-white/10 rounded-lg"
            >
              <div class="flex items-center gap-2">
                <div 
                  class="w-6 h-6 rounded-full flex items-center justify-center font-bold text-xs"
                  :class="levelColors[index + 3]?.badge || 'bg-gray-500'"
                >
                  {{ rate.level }}
                </div>
                <span class="text-xs">{{ rate.name }}</span>
              </div>
              <span class="text-green-400 font-bold text-sm">{{ rate.rate }}%</span>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Total potential -->
      <div class="bg-gradient-to-r from-green-500/20 to-emerald-500/20 backdrop-blur-sm rounded-xl p-4 border border-green-400/30">
        <div class="text-sm text-green-200 mb-1">Full Network Potential ({{ totalCapacity.toLocaleString() }} members)</div>
        <div class="text-3xl font-bold text-green-400">K{{ totalPotential.toLocaleString() }}+</div>
        <div class="text-xs text-green-200">per month from commissions alone!</div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { CommissionRate, MatrixData } from '@/composables/usePresentationData';

const props = defineProps<{
  commissionRates?: CommissionRate[];
  matrix?: MatrixData;
}>();

const rates = computed(() => props.commissionRates || []);
const totalCapacity = computed(() => props.matrix?.total_capacity || 3279);

// Calculate per-referral amount (based on K120 average purchase/subscription)
const calculatePerReferral = (rate: number) => {
  const baseAmount = 120; // K120 average purchase/subscription
  return (baseAmount * rate / 100).toFixed(2);
};

// Level colors
const levelColors = [
  { bg: 'from-blue-500/30 to-blue-600/30', badge: 'bg-blue-500' },
  { bg: 'from-indigo-500/30 to-indigo-600/30', badge: 'bg-indigo-500' },
  { bg: 'from-purple-500/30 to-purple-600/30', badge: 'bg-purple-500' },
  { bg: 'from-pink-500/30 to-pink-600/30', badge: 'bg-pink-500' },
  { bg: 'from-rose-500/30 to-rose-600/30', badge: 'bg-rose-500' },
  { bg: 'from-orange-500/30 to-orange-600/30', badge: 'bg-orange-500' },
  { bg: 'from-amber-500/30 to-amber-600/30', badge: 'bg-amber-500' },
];

// Calculate total potential monthly earnings
const totalPotential = computed(() => {
  if (!props.commissionRates || props.commissionRates.length === 0) return 4017;
  
  return props.commissionRates.reduce((total, rate) => {
    const perReferral = parseFloat(calculatePerReferral(rate.rate));
    return total + (perReferral * rate.positions);
  }, 0);
});

// Get first 3 levels for detailed display
const topLevels = computed(() => rates.value.slice(0, 3));
// Get remaining levels for compact display
const remainingLevels = computed(() => rates.value.slice(3));
</script>
