<template>
  <div class="min-h-full bg-gradient-to-br from-indigo-600 via-purple-600 to-violet-700 flex flex-col p-6 text-white relative overflow-hidden">
    <!-- Background decorations -->
    <div class="absolute top-0 right-0 w-72 h-72 bg-white/10 rounded-full -mr-36 -mt-36"></div>
    <div class="absolute bottom-0 left-0 w-56 h-56 bg-white/5 rounded-full -ml-28 -mb-28"></div>
    
    <div class="relative z-10 flex-1 flex flex-col max-w-2xl mx-auto w-full">
      <!-- Section badge -->
      <div class="text-center mb-4">
        <div class="inline-flex items-center px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full">
          <span class="text-white text-sm font-semibold">🔷 {{ matrixWidth }}×{{ matrixWidth }} Matrix System</span>
        </div>
      </div>
      
      <h2 class="text-2xl md:text-3xl font-bold mb-2 text-center">
        {{ matrixDepth }} Levels Deep Network
      </h2>
      <p class="text-purple-200 mb-4 text-center text-sm">
        Build your team with our powerful {{ matrixWidth }}×{{ matrixWidth }} forced matrix structure
      </p>
      
      <!-- Matrix Visualization -->
      <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 border border-white/20 mb-4">
        <h3 class="font-bold text-sm mb-3 text-center">How the 3×3 Matrix Works</h3>
        
        <!-- Visual Matrix Tree -->
        <div class="flex flex-col items-center space-y-2">
          <!-- Level 0: You -->
          <div class="flex flex-col items-center">
            <div class="w-12 h-12 bg-gradient-to-br from-amber-400 to-orange-500 rounded-full flex items-center justify-center border-2 border-white shadow-lg">
              <span class="text-lg">👤</span>
            </div>
            <span class="text-xs mt-1 font-medium">YOU</span>
          </div>
          
          <!-- Connector lines -->
          <div class="flex items-center justify-center">
            <div class="w-px h-3 bg-white/40"></div>
          </div>
          <div class="flex items-center justify-center gap-8">
            <div class="w-8 h-px bg-white/40"></div>
            <div class="w-px h-3 bg-white/40"></div>
            <div class="w-8 h-px bg-white/40"></div>
          </div>
          
          <!-- Level 1: 3 positions -->
          <div class="flex items-center justify-center gap-4">
            <div class="flex flex-col items-center">
              <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center border-2 border-white/50">
                <span class="text-sm">👤</span>
              </div>
            </div>
            <div class="flex flex-col items-center">
              <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center border-2 border-white/50">
                <span class="text-sm">👤</span>
              </div>
            </div>
            <div class="flex flex-col items-center">
              <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center border-2 border-white/50">
                <span class="text-sm">👤</span>
              </div>
            </div>
          </div>
          <span class="text-xs text-purple-200">Level 1: 3 positions</span>
          
          <!-- Level 2 indicator -->
          <div class="flex items-center gap-1 text-purple-200">
            <div class="flex gap-0.5">
              <div class="w-4 h-4 bg-green-500/60 rounded-full"></div>
              <div class="w-4 h-4 bg-green-500/60 rounded-full"></div>
              <div class="w-4 h-4 bg-green-500/60 rounded-full"></div>
              <span class="mx-1">×</span>
              <div class="w-4 h-4 bg-green-500/60 rounded-full"></div>
              <div class="w-4 h-4 bg-green-500/60 rounded-full"></div>
              <div class="w-4 h-4 bg-green-500/60 rounded-full"></div>
              <span class="mx-1">×</span>
              <div class="w-4 h-4 bg-green-500/60 rounded-full"></div>
              <div class="w-4 h-4 bg-green-500/60 rounded-full"></div>
              <div class="w-4 h-4 bg-green-500/60 rounded-full"></div>
            </div>
          </div>
          <span class="text-xs text-purple-200">Level 2: 9 positions (3×3)</span>
          
          <!-- Dots indicating more levels -->
          <div class="flex items-center gap-1 text-purple-300">
            <span class="text-lg">⋮</span>
          </div>
          <span class="text-xs text-purple-200">...continues to Level 7</span>
        </div>
      </div>
      
      <!-- Level Breakdown Table -->
      <div class="bg-white/10 backdrop-blur-sm rounded-xl overflow-hidden border border-white/20 mb-4">
        <div class="bg-white/10 px-3 py-2 border-b border-white/20">
          <h3 class="font-bold text-sm text-center">Network Capacity by Level</h3>
        </div>
        <div class="divide-y divide-white/10">
          <div 
            v-for="(level, index) in levelPositions" 
            :key="level.level"
            class="flex items-center justify-between px-3 py-2 text-sm"
            :class="level.level === matrixDepth ? 'bg-amber-500/20' : ''"
          >
            <div class="flex items-center gap-2">
              <div 
                class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold"
                :class="[
                  level.level === 1 ? 'bg-blue-500' :
                  level.level === 2 ? 'bg-indigo-500' :
                  level.level === 3 ? 'bg-purple-500' :
                  level.level === 4 ? 'bg-pink-500' :
                  level.level === 5 ? 'bg-rose-500' :
                  level.level === 6 ? 'bg-orange-500' :
                  'bg-gradient-to-br from-amber-400 to-orange-500'
                ]"
              >
                {{ level.level }}
              </div>
              <span :class="level.level === matrixDepth ? 'font-semibold' : ''">
                Level {{ level.level }}
              </span>
            </div>
            <div class="flex items-center gap-3">
              <span :class="level.level === matrixDepth ? 'text-amber-200' : 'text-purple-200'" class="text-xs">
                {{ matrixWidth }}<sup>{{ level.level }}</sup>
              </span>
              <span 
                class="font-bold"
                :class="level.level === matrixDepth ? 'text-amber-300' : 'text-green-400'"
              >
                {{ level.positions.toLocaleString() }}
              </span>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Total & Spillover -->
      <div class="grid grid-cols-2 gap-3 mb-4">
        <div class="bg-gradient-to-br from-green-500/30 to-emerald-500/30 rounded-xl p-3 border border-green-400/30 text-center">
          <div class="text-2xl font-bold text-green-400">{{ totalCapacity.toLocaleString() }}</div>
          <div class="text-xs text-green-200">Total Network Capacity</div>
        </div>
        <div class="bg-gradient-to-br from-blue-500/30 to-cyan-500/30 rounded-xl p-3 border border-blue-400/30 text-center">
          <div class="text-2xl font-bold text-blue-400">∞</div>
          <div class="text-xs text-blue-200">Spillover Benefit</div>
        </div>
      </div>
      
      <!-- Spillover Explanation -->
      <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 border border-white/20">
        <h3 class="font-bold text-sm mb-2 flex items-center gap-2">
          <span class="text-lg">🎁</span> Spillover Advantage
        </h3>
        <p class="text-xs text-purple-200 leading-relaxed">
          When your upline recruits more than 3 people, the extras "spill over" into your network! 
          This means you can benefit from your team's efforts even before you recruit anyone.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { MatrixData, CommissionRate } from '@/composables/usePresentationData';

const props = defineProps<{
  matrix?: MatrixData;
  commissionRates?: CommissionRate[];
}>();

const matrixWidth = computed(() => props.matrix?.width || 3);
const matrixDepth = computed(() => props.matrix?.depth || 7);
const totalCapacity = computed(() => props.matrix?.total_capacity || 3279);

// Calculate positions per level
const levelPositions = computed(() => {
  const positions = [];
  for (let i = 1; i <= matrixDepth.value; i++) {
    positions.push({
      level: i,
      positions: Math.pow(matrixWidth.value, i),
    });
  }
  return positions;
});
</script>
