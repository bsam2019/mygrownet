<template>
  <button
    @click="handleClick"
    class="flex items-center gap-4 p-4 bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-blue-200 transition-all duration-200 active:scale-98 w-full text-left"
  >
    <div :class="[iconBgClass, 'p-3 rounded-xl']">
      <component :is="icon" :class="[iconColorClass, 'h-6 w-6']" />
    </div>
    <div class="flex-1 min-w-0">
      <h3 class="text-sm font-semibold text-gray-900">{{ title }}</h3>
      <p class="text-xs text-gray-500 mt-0.5">{{ subtitle }}</p>
    </div>
    <div class="flex items-center gap-2 flex-shrink-0">
      <span
        v-if="badge && badge > 0"
        class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-bold bg-blue-600 text-white min-w-[20px]"
      >
        {{ badge }}
      </span>
      <ChevronRightIcon class="h-5 w-5 text-gray-400" />
    </div>
  </button>
</template>

<script setup lang="ts">
import { ChevronRightIcon } from '@heroicons/vue/24/outline';

interface Props {
  title: string;
  subtitle: string;
  icon: any;
  iconBgClass?: string;
  iconColorClass?: string;
  href?: string;
  badge?: number;
}

const props = withDefaults(defineProps<Props>(), {
  iconBgClass: 'bg-blue-50',
  iconColorClass: 'text-blue-600',
});

const emit = defineEmits(['click']);

const handleClick = () => {
  if (props.href) {
    window.location.href = props.href;
  } else {
    emit('click');
  }
};
</script>
