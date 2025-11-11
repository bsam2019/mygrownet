<template>
  <div v-if="announcement" class="mb-4 rounded-xl overflow-hidden shadow-lg animate-slide-in">
    <div 
      :class="bannerClasses"
      class="p-4 relative"
    >
      <button
        @click="dismiss"
        class="absolute top-2 right-2 p-1 rounded-full hover:bg-black/10 transition-colors"
      >
        <XMarkIcon class="h-5 w-5" />
      </button>
      
      <div class="flex items-start gap-3 pr-8">
        <div class="flex-shrink-0">
          <component :is="iconComponent" class="h-6 w-6" />
        </div>
        <div class="flex-1 min-w-0">
          <h3 class="font-bold text-sm mb-1">{{ announcement.title }}</h3>
          <p class="text-sm opacity-90 leading-relaxed">{{ announcement.message }}</p>
          <p class="text-xs opacity-75 mt-2">{{ formatDate(announcement.created_at) }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { XMarkIcon, InformationCircleIcon, ExclamationTriangleIcon, CheckCircleIcon, BellAlertIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';

interface Props {
  announcement: {
    id: number;
    title: string;
    message: string;
    type: 'info' | 'warning' | 'success' | 'urgent';
    is_urgent: boolean;
    created_at: string;
  } | null;
}

const props = defineProps<Props>();
const emit = defineEmits<{
  dismissed: [];
}>();

const bannerClasses = computed(() => {
  const baseClasses = 'border-l-4';
  
  switch (props.announcement?.type) {
    case 'urgent':
      return `${baseClasses} bg-red-50 border-red-500 text-red-900`;
    case 'warning':
      return `${baseClasses} bg-amber-50 border-amber-500 text-amber-900`;
    case 'success':
      return `${baseClasses} bg-green-50 border-green-500 text-green-900`;
    default:
      return `${baseClasses} bg-blue-50 border-blue-500 text-blue-900`;
  }
});

const iconComponent = computed(() => {
  switch (props.announcement?.type) {
    case 'urgent':
      return BellAlertIcon;
    case 'warning':
      return ExclamationTriangleIcon;
    case 'success':
      return CheckCircleIcon;
    default:
      return InformationCircleIcon;
  }
});

const dismiss = async () => {
  if (!props.announcement) return;
  
  try {
    await axios.post(route('mygrownet.api.announcements.mark-read', props.announcement.id));
    emit('dismissed');
  } catch (error) {
    console.error('Failed to mark announcement as read:', error);
  }
};

const formatDate = (dateString: string) => {
  const date = new Date(dateString);
  const now = new Date();
  const diffMs = now.getTime() - date.getTime();
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMs / 3600000);
  const diffDays = Math.floor(diffMs / 86400000);
  
  if (diffMins < 60) return `${diffMins}m ago`;
  if (diffHours < 24) return `${diffHours}h ago`;
  if (diffDays < 7) return `${diffDays}d ago`;
  return date.toLocaleDateString();
};
</script>
