<template>
  <GrowNetLayout title="Predefined Physical Position Rewards Center">
    <div class="py-6 space-y-8">
      <!-- Header -->
      <div class="border-b border-neutral-800 pb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <span class="px-3.5 py-1 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-400 text-xs font-bold uppercase tracking-widest inline-flex items-center gap-1.5 mb-2">
            <span class="material-symbols-outlined text-sm">trophy</span> Position Milestone Entitlements
          </span>
          <h1 class="text-3xl font-bold text-white">Predefined Physical Position Rewards</h1>
          <p class="text-sm text-neutral-400 mt-1">Claim smartphones, motorbikes, vehicles, and property rewards unlocked by your Education Level.</p>
        </div>
      </div>

      <!-- Physical Rewards Catalog Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="reward in rewardsCatalog"
          :key="reward.level"
          :class="[
            'p-6 rounded-2xl border transition-all flex flex-col justify-between',
            reward.is_unlocked ? 'bg-neutral-900 border-amber-500/50 shadow-xl' : 'bg-neutral-900/40 border-neutral-800/60 opacity-80'
          ]"
        >
          <div>
            <div class="flex items-center justify-between mb-3">
              <span class="px-3 py-1 rounded-full bg-amber-500/20 text-amber-400 text-xs font-bold uppercase">
                Level {{ reward.level }} Reward
              </span>
              <span class="font-mono text-xs text-neutral-300 font-bold">Est. Value {{ reward.value }}</span>
            </div>

            <h3 class="font-bold text-lg text-white mb-1">{{ reward.name }}</h3>
            <p class="text-xs text-neutral-400 mb-4">{{ reward.description }}</p>
          </div>

          <div class="pt-4 border-t border-neutral-800/60 flex items-center justify-between">
            <span class="text-xs font-semibold text-neutral-400">Cash Bonus: {{ reward.cashBonus }}</span>

            <button
              v-if="reward.is_unlocked"
              class="px-4 py-2 rounded-xl bg-amber-500 text-black font-bold text-xs hover:bg-amber-400 transition-colors flex items-center gap-1.5 shadow-lg shadow-amber-500/20"
            >
              <span class="material-symbols-outlined text-sm">local_shipping</span> Claim Reward
            </button>
            <span v-else class="text-xs text-neutral-500 font-semibold flex items-center gap-1">
              <span class="material-symbols-outlined text-xs">lock</span> Locked at Level {{ reward.level }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </GrowNetLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import GrowNetLayout from '@/Layouts/GrowNetLayout.vue';

const rewardsCatalog = [
  { level: 1, name: 'MyGrowNet Starter Kit', description: 'Branded t-shirt, business cards, training manual, welcome package', value: 'K500', cashBonus: 'Base Enrolment', is_unlocked: true },
  { level: 2, name: 'Branded Electronics Kit', description: 'Power bank, wireless earbuds, professional planner', value: 'K1,000', cashBonus: 'K500 Cash', is_unlocked: false },
  { level: 3, name: 'Smartphone / Tablet Package', description: 'Choice of Samsung Galaxy A54, iPhone SE, or iPad (2-yr warranty)', value: 'K3,000', cashBonus: 'K1,500 Cash', is_unlocked: false },
  { level: 4, name: 'Motorbike / Office Kit', description: '125cc Honda CB125F motorbike OR Laptop, Printer, Desk & Chair', value: 'K12,000', cashBonus: 'K5,000 Cash', is_unlocked: false },
  { level: 5, name: 'Car Package / Property Assistance', description: 'Toyota Vitz / Honda Fit vehicle OR K25,000 Property Down Payment Assistance', value: 'K35,000', cashBonus: 'K15,000 Cash', is_unlocked: false },
  { level: 6, name: 'Luxury Car Package', description: 'Toyota Camry / BMW 3 Series executive vehicle with 3-yr service plan', value: 'K75,000', cashBonus: 'K50,000 Cash', is_unlocked: false },
  { level: 7, name: 'Property Investment Package', description: 'Multi-unit residential or commercial property in Lusaka / Copperbelt', value: 'K150,000', cashBonus: 'K150,000 Cash', is_unlocked: false },
];
</script>
