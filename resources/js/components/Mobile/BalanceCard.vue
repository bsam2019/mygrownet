<template>
  <div class="relative overflow-hidden bg-gradient-to-br from-blue-500 via-cyan-500 to-teal-500 rounded-3xl shadow-2xl p-6 text-white">
    <!-- Decorative elements -->
    <div class="absolute top-0 right-0 w-40 h-40 bg-white/10 rounded-full -mr-20 -mt-20"></div>
    <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/10 rounded-full -ml-16 -mb-16"></div>
    <div class="absolute top-1/2 left-1/2 w-64 h-64 bg-white/5 rounded-full -ml-32 -mt-32"></div>
    
    <div class="relative z-10">
      <div class="flex items-start justify-between mb-6">
        <div class="flex-1">
          <div class="flex items-center gap-2 mb-2">
            <div class="w-8 h-8 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center">
              <span class="text-lg">💰</span>
            </div>
            <p class="text-white/90 text-sm font-semibold">Available Balance</p>
          </div>
          <h2 class="text-5xl font-bold tracking-tight drop-shadow-lg">K{{ formatCurrency(balance) }}</h2>
        </div>
        <button
          @click="$emit('refresh')"
          class="p-3 bg-white/20 backdrop-blur-sm rounded-xl hover:bg-white/30 transition-all duration-200 active:scale-95 border border-white/30 shadow-lg"
          :disabled="loading"
        >
          <ArrowPathIcon class="h-5 w-5" :class="{ 'animate-spin': loading }" />
        </button>
      </div>
      
      <div class="flex gap-3">
        <button
          @click="$emit('deposit')"
          class="flex-1 bg-white text-cyan-600 py-3.5 px-4 rounded-xl font-bold text-sm text-center hover:bg-cyan-50 transition-all duration-200 shadow-xl active:scale-95"
        >
          💰 Deposit
        </button>
        <button
          @click="$emit('withdraw')"
          class="flex-1 bg-white/20 backdrop-blur-sm text-white py-3.5 px-4 rounded-xl font-bold text-sm text-center hover:bg-white/30 transition-all duration-200 border-2 border-white/40 active:scale-95 shadow-lg"
        >
          💸 Withdraw
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ArrowPathIcon } from '@heroicons/vue/24/outline';

withDefaults(defineProps<{
  balance?: number;
  loading?: boolean;
}>(), {
  balance: 0,
  loading: false,
});

defineEmits<{
  refresh: [];
  deposit: [];
  withdraw: [];
}>();

const formatCurrency = (value: number | undefined | null) => {
  if (value === undefined || value === null || isNaN(value)) return '0.00';
  return Number(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};
</script>
