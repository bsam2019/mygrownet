<template>
  <div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <!-- Category Header -->
    <div class="bg-gradient-to-r p-4" :class="headerGradient">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
          <span class="text-2xl">{{ icon }}</span>
        </div>
        <div class="flex-1">
          <h3 class="text-white font-bold text-base">{{ title }}</h3>
          <p class="text-white/80 text-xs">{{ subtitle }}</p>
        </div>
        <div v-if="badge" class="px-2 py-1 bg-white/25 backdrop-blur-sm rounded-full">
          <span class="text-white text-xs font-semibold">{{ badge }}</span>
        </div>
      </div>
    </div>

    <!-- Tools Grid -->
    <div class="p-4">
      <div class="grid grid-cols-2 gap-3">
        <button
          v-for="tool in tools"
          :key="tool.id"
          @click="handleToolClick(tool)"
          :disabled="tool.locked"
          class="relative flex flex-col items-center p-4 rounded-xl transition-all"
          :class="tool.locked 
            ? 'bg-gray-50 border-2 border-dashed border-gray-200 opacity-60 cursor-not-allowed' 
            : 'bg-gradient-to-br border-2 hover:shadow-md active:scale-95 ' + tool.bgGradient + ' ' + tool.borderColor"
        >
          <!-- Lock Icon -->
          <div v-if="tool.locked" class="absolute top-2 right-2">
            <LockClosedIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
          </div>

          <!-- Tool Icon -->
          <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-2" :class="tool.iconBg">
            <component v-if="tool.iconComponent" :is="tool.iconComponent" class="h-6 w-6" :class="tool.iconColor" aria-hidden="true" />
            <span v-else class="text-2xl" aria-hidden="true">{{ tool.icon }}</span>
          </div>

          <!-- Tool Name -->
          <span class="text-sm font-semibold text-gray-900 text-center">{{ tool.name }}</span>
          
          <!-- Tool Description -->
          <span v-if="tool.description" class="text-xs text-gray-500 text-center mt-1">{{ tool.description }}</span>

          <!-- Premium Badge -->
          <div v-if="tool.premium && !tool.locked" class="absolute top-2 right-2">
            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-semibold bg-gradient-to-r from-yellow-400 to-orange-400 text-white">
              👑
            </span>
          </div>
        </button>
      </div>

      <!-- Locked Message -->
      <div v-if="hasLockedTools" class="mt-4 bg-gradient-to-r from-purple-50 to-pink-50 border border-purple-200 rounded-lg p-3">
        <p class="text-sm text-purple-800 text-center">
          <LockClosedIcon class="h-4 w-4 inline mr-1" aria-hidden="true" />
          {{ lockedMessage }}
        </p>
        <button
          v-if="upgradeAction"
          @click="$emit('upgrade')"
          class="mt-2 w-full px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg text-sm font-semibold hover:shadow-lg transition-all active:scale-95"
        >
          {{ upgradeButtonText }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { LockClosedIcon } from '@heroicons/vue/24/outline';

interface Tool {
  id: string;
  name: string;
  description?: string;
  icon?: string;
  iconComponent?: any;
  iconBg: string;
  iconColor: string;
  bgGradient: string;
  borderColor: string;
  locked?: boolean;
  premium?: boolean;
  action?: string;
}

interface Props {
  title: string;
  subtitle: string;
  icon: string;
  headerGradient: string;
  tools: Tool[];
  badge?: string;
  lockedMessage?: string;
  upgradeAction?: boolean;
  upgradeButtonText?: string;
}

interface Emits {
  (e: 'tool-click', tool: Tool): void;
  (e: 'upgrade'): void;
}

const props = withDefaults(defineProps<Props>(), {
  lockedMessage: 'Unlock premium tools by upgrading your starter kit',
  upgradeButtonText: 'Upgrade Now'
});

const emit = defineEmits<Emits>();

const hasLockedTools = computed(() => {
  return props.tools.some(tool => tool.locked);
});

const handleToolClick = (tool: Tool) => {
  if (!tool.locked) {
    emit('tool-click', tool);
  }
};
</script>
