<template>
  <nav class="fixed bottom-0 left-0 right-0 bg-white border-t-2 border-blue-500 shadow-2xl" style="z-index: 99999 !important;">
    <div class="flex justify-around items-center h-16 px-2">
      <button
        v-for="item in navItems"
        :key="item.name"
        @click="emit('navigate', item.tab)"
        :aria-label="`Navigate to ${item.name}`"
        :aria-current="activeTab === item.tab ? 'page' : undefined"
        class="flex flex-col items-center justify-center flex-1 h-full transition-all duration-200 rounded-xl relative group"
        :class="activeTab === item.tab ? 'text-blue-600' : 'text-gray-500 hover:text-gray-700 active:scale-95'"
      >
        <!-- Active indicator -->
        <div 
          v-if="activeTab === item.tab" 
          class="absolute top-0 left-1/2 -translate-x-1/2 w-12 h-1 bg-blue-600 rounded-full"
        ></div>
        
        <!-- Icon with background on active -->
        <div 
          class="p-1.5 rounded-xl transition-all duration-200"
          :class="activeTab === item.tab ? 'bg-blue-50' : 'group-hover:bg-gray-50'"
        >
          <component :is="item.icon" class="h-6 w-6 stroke-current stroke-2" aria-hidden="true" />
        </div>
        
        <span 
          class="text-xs mt-0.5 font-medium transition-all duration-200"
          :class="activeTab === item.tab ? 'font-semibold' : ''"
        >
          {{ item.name }}
        </span>
      </button>
    </div>
  </nav>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { 
  HomeIcon, 
  UsersIcon, 
  WalletIcon, 
  WrenchScrewdriverIcon,
  UserCircleIcon,
  EllipsisHorizontalIcon
} from '@heroicons/vue/24/outline';

import { computed } from 'vue';

// Props
interface Props {
  activeTab?: string;
}

const props = withDefaults(defineProps<Props>(), {
  activeTab: 'home'
});

// Emit events instead of navigating for SPA experience
const emit = defineEmits(['navigate']);

const navItems = computed(() => [
  { name: 'Home', tab: 'home', icon: HomeIcon },
  { name: 'Team', tab: 'team', icon: UsersIcon },
  { name: 'Wallet', tab: 'wallet', icon: WalletIcon },
  { name: 'Tools', tab: 'learn', icon: WrenchScrewdriverIcon },
  { name: 'More', tab: 'more', icon: EllipsisHorizontalIcon },
]);
</script>
