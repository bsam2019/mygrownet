<template>
  <div class="min-h-full w-full bg-gradient-to-br from-rose-600 via-pink-600 to-fuchsia-700 flex flex-col items-center justify-center p-6 py-8 text-white relative overflow-hidden">
    <!-- Background decorations -->
    <div class="absolute top-0 right-0 w-72 h-72 bg-white/10 rounded-full -mr-36 -mt-36"></div>
    <div class="absolute bottom-0 left-0 w-56 h-56 bg-white/5 rounded-full -ml-28 -mb-28"></div>
    
    <div class="relative z-10 max-w-2xl mx-auto text-center">
      <!-- Section badge -->
      <div class="inline-flex items-center px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full mb-6">
        <span class="text-white text-sm font-semibold">🎁 LGR - Loyalty Gift Rewards</span>
      </div>
      
      <h2 class="text-3xl md:text-4xl font-bold mb-3">
        Loyalty Pays Off!
      </h2>
      <p class="text-pink-200 mb-8">
        Exclusive rewards for committed members
      </p>
      
      <!-- What is LGR -->
      <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20 mb-6 text-left">
        <h3 class="font-bold text-lg mb-4 text-center">What is LGR?</h3>
        <p class="text-pink-100 mb-4">
          LGR (Loyalty Gift Rewards) is our exclusive program that rewards members who demonstrate 
          commitment through their <span class="font-bold text-white">Starter Kit</span> purchase 
          and active participation. All tiers qualify!
        </p>
        
        <div class="space-y-3">
          <div class="flex items-start gap-3">
            <div class="w-8 h-8 bg-green-500/30 rounded-lg flex items-center justify-center flex-shrink-0">
              <span class="text-lg">✓</span>
            </div>
            <div>
              <div class="font-semibold">Daily LGR Earnings</div>
              <div class="text-sm text-pink-200">
                Earn daily rewards based on your tier (K12.50 - K62.50/day)
              </div>
            </div>
          </div>
          
          <div class="flex items-start gap-3">
            <div class="w-8 h-8 bg-green-500/30 rounded-lg flex items-center justify-center flex-shrink-0">
              <span class="text-lg">✓</span>
            </div>
            <div>
              <div class="font-semibold">Transferable Balance</div>
              <div class="text-sm text-pink-200">Transfer LGR rewards to your main wallet anytime</div>
            </div>
          </div>
          
          <div class="flex items-start gap-3">
            <div class="w-8 h-8 bg-green-500/30 rounded-lg flex items-center justify-center flex-shrink-0">
              <span class="text-lg">✓</span>
            </div>
            <div>
              <div class="font-semibold">Withdrawal Options</div>
              <div class="text-sm text-pink-200">Withdraw to mobile money or bank account</div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- LGR Rates by Tier -->
      <div class="bg-gradient-to-r from-amber-500/20 to-orange-500/20 backdrop-blur-sm rounded-xl p-4 border border-amber-400/30">
        <h3 class="font-bold mb-3">LGR Daily Rates by Tier</h3>
        <div v-if="hasTiers" class="grid grid-cols-2 gap-3 text-sm">
          <div 
            v-for="tier in validTiers" 
            :key="`tier-${tier.id}`"
            class="flex items-center gap-2"
          >
            <span :class="getTierIconColor(tier.name)">{{ getTierIcon(tier.name) }}</span>
            <span>{{ tier.name }}: K{{ formatTierRate(tier.lgr_daily_rate) }}/day</span>
          </div>
        </div>
        <div v-else class="grid grid-cols-2 gap-3 text-sm">
          <div class="flex items-center gap-2">
            <span class="text-gray-300">📦</span>
            <span>Lite: K12.50/day</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="text-blue-400">🎁</span>
            <span>Basic: K25/day</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="text-emerald-400">🚀</span>
            <span>Growth Plus: K37.50/day</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="text-amber-400">✨</span>
            <span>Pro: K62.50/day</span>
          </div>
        </div>
      </div>
      
      <!-- CTA -->
      <p class="mt-6 text-sm text-pink-200">
        <span class="text-xl mr-1">💡</span>
        All starter kit members qualify for LGR - higher tiers earn more!
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Tier {
  id: number;
  name: string;
  price: number;
  lgr_daily_rate: number;
}

const props = defineProps<{
  tiers?: Tier[];
}>();

// Check if we have valid tiers
const hasTiers = computed(() => {
  return props.tiers && Array.isArray(props.tiers) && props.tiers.length > 0;
});

// Get valid tiers with lgr_daily_rate
const validTiers = computed(() => {
  if (!hasTiers.value) return [];
  return props.tiers!.filter(t => t.lgr_daily_rate !== undefined && t.lgr_daily_rate !== null);
});

// Format tier rate safely
const formatTierRate = (rate: number | undefined | null): string => {
  if (rate === undefined || rate === null || isNaN(rate)) {
    return '0.00';
  }
  return Number(rate).toFixed(2);
};

// Get tier icon based on name
const getTierIcon = (name: string): string => {
  const icons: Record<string, string> = {
    'Lite': '📦',
    'Basic': '🎁',
    'Growth Plus': '🚀',
    'Pro': '✨',
  };
  return icons[name] || '🎁';
};

// Get tier icon color based on name
const getTierIconColor = (name: string): string => {
  const colors: Record<string, string> = {
    'Lite': 'text-gray-300',
    'Basic': 'text-blue-400',
    'Growth Plus': 'text-emerald-400',
    'Pro': 'text-amber-400',
  };
  return colors[name] || 'text-white';
};
</script>
