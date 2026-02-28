<template>
  <div class="min-h-full w-full bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 flex flex-col items-center justify-center p-6 py-8 text-white relative overflow-hidden">
    <!-- Background decorations -->
    <div class="absolute top-0 right-0 w-72 h-72 bg-white/10 rounded-full -mr-36 -mt-36"></div>
    <div class="absolute bottom-0 left-0 w-56 h-56 bg-white/5 rounded-full -ml-28 -mb-28"></div>
    
    <div class="relative z-10 max-w-2xl mx-auto text-center">
      <!-- Section badge -->
      <div class="inline-flex items-center px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full mb-6">
        <span class="text-white text-sm font-semibold">👥 Referral Bonus</span>
      </div>
      
      <h2 class="text-3xl md:text-4xl font-bold mb-3">
        Earn {{ referralRate }}% on Every Referral
      </h2>
      <p class="text-blue-200 mb-8">
        Your direct referrals earn you the highest commission
      </p>
      
      <!-- Visual example -->
      <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20 mb-6">
        <div class="flex items-center justify-center gap-4 mb-6">
          <!-- You -->
          <div class="text-center">
            <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-2 border-4 border-white/30">
              <span class="text-2xl">👤</span>
            </div>
            <span class="text-sm font-medium">You</span>
          </div>
          
          <!-- Arrow -->
          <div class="flex flex-col items-center">
            <div class="text-green-400 font-bold text-lg">K{{ commissionAmount.toFixed(2) }}</div>
            <svg class="w-12 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
            </svg>
            <div class="text-xs text-blue-200">{{ referralRate }}% of base</div>
          </div>
          
          <!-- Referral -->
          <div class="text-center">
            <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-2 border-4 border-white/30">
              <span class="text-2xl">🆕</span>
            </div>
            <span class="text-sm font-medium">New Member</span>
          </div>
        </div>
        
        <!-- Calculation -->
        <div class="bg-white/10 rounded-xl p-4">
          <div class="flex items-center justify-between text-sm mb-2">
            <span class="text-blue-200">Starter Kit Purchase ({{ exampleTier.name }})</span>
            <span class="font-bold">K{{ exampleTier.price }}</span>
          </div>
          <div class="flex items-center justify-between text-sm mb-2">
            <span class="text-blue-200">Commission Base ({{ commissionBase }}%)</span>
            <span class="font-bold">K{{ baseAmount.toFixed(2) }}</span>
          </div>
          <div class="flex items-center justify-between text-sm mb-2">
            <span class="text-blue-200">Your Commission ({{ referralRate }}%)</span>
            <span class="font-bold text-green-400">K{{ commissionAmount.toFixed(2) }}</span>
          </div>
          <div class="border-t border-white/20 pt-2 mt-2">
            <div class="flex items-center justify-between">
              <span class="text-blue-200">Paid to your wallet</span>
              <span class="font-bold text-green-400 text-lg">Within 24hrs!</span>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Multiple referrals example -->
      <div class="grid grid-cols-3 gap-3">
        <div 
          v-for="example in multipleReferrals" 
          :key="example.count"
          class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20"
        >
          <div class="text-2xl mb-1">{{ example.count }}</div>
          <div class="text-xs text-blue-200 mb-2">Referrals</div>
          <div class="text-lg font-bold text-green-400">K{{ example.amount.toFixed(2) }}</div>
        </div>
      </div>
      
      <!-- Note -->
      <p class="mt-6 text-sm text-blue-200">
        <span class="text-xl mr-1">💡</span>
        Plus, you earn from their referrals too (up to 7 levels)!
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { ReferralBonus, StarterKitTier } from '@/composables/usePresentationData';

const props = defineProps<{
  referralBonus?: ReferralBonus;
  tiers?: StarterKitTier[];
}>();

// Use Basic tier (K500) as example
const exampleTier = computed(() => {
  if (props.tiers && props.tiers.length > 0) {
    return props.tiers.find(t => t.key === 'basic') || props.tiers[0];
  }
  return { name: 'Basic', price: 500 };
});

const referralRate = computed(() => props.referralBonus?.rate || 15);
const commissionBase = computed(() => props.referralBonus?.commission_base || 50);

const baseAmount = computed(() => exampleTier.value.price * (commissionBase.value / 100));
const commissionAmount = computed(() => baseAmount.value * (referralRate.value / 100));

// Calculate examples for multiple referrals
const multipleReferrals = computed(() => [
  { count: 3, amount: commissionAmount.value * 3 },
  { count: 10, amount: commissionAmount.value * 10 },
  { count: 30, amount: commissionAmount.value * 30 },
]);
</script>
