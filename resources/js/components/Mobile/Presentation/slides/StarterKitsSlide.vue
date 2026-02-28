<template>
  <div class="min-h-full w-full bg-gradient-to-br from-violet-600 via-purple-600 to-fuchsia-700 flex flex-col p-4 py-6 text-white relative overflow-hidden">
    <!-- Background decorations -->
    <div class="absolute top-0 right-0 w-72 h-72 bg-white/10 rounded-full -mr-36 -mt-36"></div>
    <div class="absolute bottom-0 left-0 w-56 h-56 bg-white/5 rounded-full -ml-28 -mb-28"></div>
    
    <div class="relative z-10 max-w-4xl mx-auto text-center">
      <!-- Section badge -->
      <div class="inline-flex items-center px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full mb-4">
        <span class="text-white text-sm font-semibold">🎁 Starter Kits</span>
      </div>
      
      <h2 class="text-2xl md:text-3xl font-bold mb-2">
        Choose Your Starting Point
      </h2>
      <p class="text-purple-200 text-sm mb-4">
        Everything you need to begin your journey
      </p>
      
      <!-- Starter Kit Cards - Dynamic Tiers -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div 
          v-for="(tier, index) in displayTiers" 
          :key="tier.key"
          class="rounded-xl p-3 border text-left"
          :class="getTierClasses(index)"
        >
          <div v-if="tier.badge" class="absolute -top-2 left-1/2 -translate-x-1/2 px-2 py-0.5 rounded-full text-[10px] font-bold text-white"
               :class="tier.badgeClass">
            {{ tier.badge }}
          </div>
          
          <div class="mb-2" :class="tier.badge ? 'mt-1' : ''">
            <span class="text-sm font-bold">{{ tier.name }}</span>
          </div>
          
          <div class="mb-2">
            <span class="text-2xl font-bold">K{{ tier.price.toLocaleString() }}</span>
          </div>
          
          <ul class="space-y-1 text-xs mb-3">
            <li class="flex items-center gap-1">
              <span :class="tier.checkColor">✓</span>
              <span>{{ tier.storage_gb }}GB Storage</span>
            </li>
            <li class="flex items-center gap-1">
              <span :class="tier.checkColor">✓</span>
              <span>{{ tier.lifetimePoints }} Lifetime Points</span>
            </li>
            <li class="flex items-center gap-1">
              <span :class="tier.checkColor">{{ tier.specialIcon }}</span>
              <span :class="tier.specialTextClass">{{ tier.specialFeature }}</span>
            </li>
          </ul>
          
          <div class="text-xs" :class="tier.descColor">
            {{ tier.tagline }}
          </div>
        </div>
      </div>
      
      <!-- Value highlight -->
      <div class="mt-4 p-3 bg-white/10 backdrop-blur-sm rounded-xl">
        <p class="text-purple-100 text-sm">
          <span class="text-lg mr-1">💡</span>
          All kits include <span class="font-bold text-white">7-level commission eligibility</span> and 
          <span class="font-bold text-white">progressive content unlocks</span>!
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { StarterKitTier } from '@/composables/usePresentationData';

const props = defineProps<{
  tiers?: StarterKitTier[];
}>();

// Map tier keys to lifetime points
const tierPoints: Record<string, number> = {
  'lite': 15,
  'basic': 25,
  'growth_plus': 50,
  'pro': 100,
};

// Map tier keys to display properties
const tierConfig: Record<string, any> = {
  'lite': {
    bgClass: 'bg-white/10 backdrop-blur-sm border-white/20',
    checkColor: 'text-green-400',
    descColor: 'text-purple-200',
    specialIcon: '✓',
    specialFeature: 'Community Access',
    specialTextClass: '',
    tagline: 'Entry level',
  },
  'basic': {
    bgClass: 'bg-blue-500/20 backdrop-blur-sm border-blue-400/30',
    checkColor: 'text-blue-400',
    descColor: 'text-blue-200',
    specialIcon: '✓',
    specialFeature: 'Full Platform',
    specialTextClass: '',
    tagline: 'Great start',
  },
  'growth_plus': {
    bgClass: 'bg-gradient-to-br from-emerald-500/30 to-green-500/30 backdrop-blur-sm border-emerald-400/50 relative',
    checkColor: 'text-emerald-400',
    descColor: 'text-emerald-200',
    specialIcon: '⭐',
    specialFeature: 'Priority Support',
    specialTextClass: 'text-emerald-200',
    tagline: 'Best choice',
    badge: 'POPULAR',
    badgeClass: 'bg-emerald-500',
  },
  'pro': {
    bgClass: 'bg-gradient-to-br from-amber-500/30 to-orange-500/30 backdrop-blur-sm border-2 border-amber-400/50 relative',
    checkColor: 'text-amber-400',
    descColor: 'text-amber-200',
    specialIcon: '⭐',
    specialFeature: 'Premium Benefits',
    specialTextClass: 'text-amber-200',
    tagline: 'Maximum value',
    badge: 'BEST VALUE',
    badgeClass: 'bg-gradient-to-r from-amber-400 to-orange-400 text-black',
  },
};

const displayTiers = computed(() => {
  if (!props.tiers || props.tiers.length === 0) {
    // Fallback data
    return [
      { key: 'lite', name: 'Lite', price: 300, storage_gb: 5, lifetimePoints: 15 },
      { key: 'basic', name: 'Basic', price: 500, storage_gb: 10, lifetimePoints: 25 },
      { key: 'growth_plus', name: 'Growth Plus', price: 1000, storage_gb: 25, lifetimePoints: 50 },
      { key: 'pro', name: 'Pro', price: 2000, storage_gb: 50, lifetimePoints: 100 },
    ].map(tier => ({
      ...tier,
      ...tierConfig[tier.key],
    }));
  }

  return props.tiers.map(tier => ({
    ...tier,
    lifetimePoints: tierPoints[tier.key] || 0,
    ...tierConfig[tier.key],
  }));
});

const getTierClasses = (index: number) => {
  const tier = displayTiers.value[index];
  return tier.bgClass || 'bg-white/10 backdrop-blur-sm border-white/20';
};
</script>
