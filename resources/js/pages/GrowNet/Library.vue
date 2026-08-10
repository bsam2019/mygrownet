<template>
  <GrowNetLayout title="Digital Resource Library & Kit Downloads">
    <div class="py-6 space-y-8">
      <!-- Header Banner -->
      <div class="border-b border-neutral-800 pb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <span class="px-3.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold uppercase tracking-widest inline-flex items-center gap-1.5 mb-2">
            <span class="material-symbols-outlined text-sm">download</span> Member Entitlement Downloads
          </span>
          <h1 class="text-3xl font-bold text-white">Digital Resource Library</h1>
          <p class="text-sm text-neutral-400 mt-1">Download audiobooks, e-pubs, PDF action pamphlets, and starter kit materials.</p>
        </div>
        <div class="text-xs text-neutral-300 font-semibold bg-neutral-900 px-4 py-2.5 rounded-xl border border-neutral-800">
          Your Education Level: <span class="text-emerald-400 font-bold">Level {{ userLevel }}</span>
        </div>
      </div>

      <!-- Resource Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div
          v-for="res in resources"
          :key="res.id"
          :class="[
            'p-6 rounded-2xl border transition-all flex flex-col justify-between',
            res.is_unlocked ? 'bg-neutral-900/80 border-neutral-800 hover:border-emerald-500/50' : 'bg-neutral-900/40 border-neutral-800/40 opacity-75'
          ]"
        >
          <div>
            <div class="flex items-center justify-between mb-3">
              <span :class="[
                'px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider',
                res.type === 'audio' ? 'bg-amber-500/20 text-amber-400' : 'bg-emerald-500/20 text-emerald-400'
              ]">
                {{ res.type.toUpperCase() }}
              </span>
              <span v-if="!res.is_unlocked" class="text-xs font-bold text-amber-400 flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">lock</span> Locked
              </span>
            </div>

            <h2 class="text-base font-bold text-white mb-1">{{ res.title }}</h2>
            <p class="text-xs text-neutral-400 mb-4">{{ res.description }}</p>
          </div>

          <div class="pt-4 border-t border-neutral-800/60 flex items-center justify-between">
            <span class="text-[11px] text-neutral-500 font-semibold">{{ res.difficulty.toUpperCase() }}</span>

            <a
              v-if="res.is_unlocked && res.resource_url"
              :href="res.resource_url"
              download
              class="px-4 py-2 rounded-xl bg-emerald-500 text-black font-bold text-xs hover:bg-emerald-400 transition-colors flex items-center gap-1.5 shadow-lg shadow-emerald-500/20"
            >
              <span class="material-symbols-outlined text-sm">download</span> Download
            </a>
            <span v-else class="text-xs text-neutral-500 font-semibold">
              {{ res.unlock_requirement }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </GrowNetLayout>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import GrowNetLayout from '@/Layouts/GrowNetLayout.vue';

interface Resource {
  id: number;
  title: string;
  description: string;
  type: string;
  category: string;
  resource_url: string | null;
  difficulty: string;
  is_unlocked: boolean;
  unlock_requirement: string;
}

defineProps<{
  resources: Resource[];
  userLevel: number;
}>();
</script>
