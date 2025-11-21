<template>
  <div
    @click="!locked && $emit('click')"
    class="border border-gray-200 rounded-lg p-6 hover:border-blue-500 transition-colors cursor-pointer relative"
    :class="locked ? 'opacity-60 cursor-not-allowed' : ''"
  >
    <div v-if="locked" class="absolute top-2 right-2">
      <LockClosedIcon class="w-5 h-5 text-gray-400" />
    </div>
    <div class="flex items-start space-x-4">
      <div class="flex-shrink-0">
        <DocumentIcon v-if="icon === 'document'" class="w-10 h-10 text-blue-600" />
        <DocumentTextIcon v-else-if="icon === 'pdf'" class="w-10 h-10 text-red-600" />
        <DocumentArrowDownIcon v-else-if="icon === 'word'" class="w-10 h-10 text-blue-700" />
      </div>
      <div class="flex-1">
        <div class="flex items-center justify-between mb-1">
          <h4 class="font-semibold text-gray-900">{{ title }}</h4>
          <span 
            class="text-xs px-2 py-1 rounded-full"
            :class="badge === 'FREE' ? 'bg-green-100 text-green-700' : 'bg-purple-100 text-purple-700'"
          >
            {{ badge }}
          </span>
        </div>
        <p class="text-sm text-gray-600">{{ description }}</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { DocumentIcon, DocumentTextIcon, DocumentArrowDownIcon, LockClosedIcon } from '@heroicons/vue/24/outline';

defineProps<{
  title: string;
  description: string;
  icon: 'document' | 'pdf' | 'word';
  badge: string;
  locked?: boolean;
}>();

defineEmits<{
  click: [];
}>();
</script>
