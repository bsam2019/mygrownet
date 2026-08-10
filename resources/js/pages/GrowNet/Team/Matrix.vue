<template>
  <GrowNetLayout title="Team Management Suite & Downline Matrix">
    <div class="py-6 space-y-8">
      <!-- Header -->
      <div class="border-b border-neutral-800 pb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <span class="px-3.5 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/30 text-indigo-400 text-xs font-bold uppercase tracking-widest inline-flex items-center gap-1.5 mb-2">
            <span class="material-symbols-outlined text-sm">hub</span> Team Management Suite
          </span>
          <h1 class="text-3xl font-bold text-white">Interactive Team Matrix &amp; Downline</h1>
          <p class="text-sm text-neutral-400 mt-1">Manage your downline matrix, track team learning progress, and monitor team LP volume.</p>
        </div>
      </div>

      <!-- Team Overview Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="p-6 rounded-2xl bg-neutral-900 border border-neutral-800">
          <p class="text-xs text-neutral-400 font-semibold">Direct Referrals</p>
          <p class="text-3xl font-black text-white mt-2">{{ directCount }}</p>
          <p class="text-xs text-emerald-400 mt-1">Level 1 Direct Team</p>
        </div>

        <div class="p-6 rounded-2xl bg-neutral-900 border border-neutral-800">
          <p class="text-xs text-neutral-400 font-semibold">Total Downline Network</p>
          <p class="text-3xl font-black text-indigo-400 mt-2">{{ totalNetwork }}</p>
          <p class="text-xs text-indigo-300 mt-1">Active Team Matrix Members</p>
        </div>

        <div class="p-6 rounded-2xl bg-neutral-900 border border-neutral-800">
          <p class="text-xs text-neutral-400 font-semibold">Team LP Volume</p>
          <p class="text-3xl font-black text-amber-400 mt-2">{{ teamVolume.toLocaleString() }} LP</p>
          <p class="text-xs text-amber-300 mt-1">Cumulative Team Life Points</p>
        </div>
      </div>

      <!-- Tree View Placeholder -->
      <div class="p-8 rounded-2xl bg-neutral-900/60 border border-neutral-800 text-center space-y-4">
        <div class="w-14 h-14 mx-auto rounded-2xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center">
          <span class="material-symbols-outlined text-3xl">account_tree</span>
        </div>
        <h3 class="text-base font-bold text-white">7-Level Downline Matrix Visualizer</h3>
        <p class="text-xs text-neutral-400 max-w-md mx-auto">
          Interactive matrix tree visualization showing downline placements, active learning subscriptions, and level progress.
        </p>
      </div>
    </div>
  </GrowNetLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import GrowNetLayout from '@/Layouts/GrowNetLayout.vue';

withDefaults(defineProps<{
  directCount?: number;
  totalNetwork?: number;
  teamVolume?: number;
}>(), {
  directCount: 3,
  totalNetwork: 12,
  teamVolume: 4250,
});
</script>
