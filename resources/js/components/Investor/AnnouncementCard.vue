<template>
  <div
    :class="[
      'bg-white rounded-2xl shadow-sm border overflow-hidden transition-all duration-300 hover:shadow-md',
      !isRead && 'ring-2 ring-blue-500/50',
      pinned && 'border-l-4 border-l-blue-600'
    ]"
    class="border-gray-100"
  >
    <div class="p-6">
      <!-- Header -->
      <div class="flex items-start justify-between gap-4 mb-4">
        <div class="flex items-start gap-4">
          <div :class="getTypeIconClass(announcement.type)" class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0">
            <component :is="getTypeIcon(announcement.type)" class="h-6 w-6 text-white" aria-hidden="true" />
          </div>
          <div>
            <div class="flex items-center gap-2 flex-wrap mb-1">
              <h3 class="font-semibold text-gray-900 text-lg">{{ announcement.title }}</h3>
              <span v-if="!isRead" class="px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-700">
                New
              </span>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
              <span :class="getTypeBadgeClass(announcement.type)" class="px-2.5 py-1 text-xs font-semibold rounded-full">
                {{ getTypeLabel(announcement.type) }}
              </span>
              <span v-if="announcement.priority === 'urgent'" class="px-2.5 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700 animate-pulse">
                🚨 Urgent
              </span>
              <span v-else-if="announcement.priority === 'high'" class="px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-700">
                High Priority
              </span>
            </div>
          </div>
        </div>
        <span class="text-sm text-gray-500 whitespace-nowrap">{{ formatDate(announcement.published_at) }}</span>
      </div>

      <!-- Content -->
      <div class="text-gray-600 leading-relaxed">
        <p v-if="!expanded" class="line-clamp-3">
          {{ announcement.summary || announcement.content }}
        </p>
        <div v-else class="whitespace-pre-wrap">
          {{ announcement.content }}
        </div>
      </div>

      <!-- Actions -->
      <div class="flex items-center justify-between mt-5 pt-5 border-t border-gray-100">
        <button
          @click="expanded = !expanded"
          class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-700 text-sm font-medium transition-colors"
        >
          {{ expanded ? 'Show Less' : 'Read More' }}
          <ChevronDownIcon :class="['h-4 w-4 transition-transform', expanded && 'rotate-180']" aria-hidden="true" />
        </button>
        <button
          v-if="!isRead"
          @click="$emit('markRead', announcement.id)"
          class="inline-flex items-center gap-1.5 px-3 py-1.5 text-gray-500 hover:text-gray-700 hover:bg-gray-100 text-sm rounded-lg transition-colors"
        >
          <CheckIcon class="h-4 w-4" aria-hidden="true" />
          Mark as read
        </button>
        <span v-else class="inline-flex items-center gap-1.5 text-sm text-emerald-600">
          <CheckCircleIcon class="h-4 w-4" aria-hidden="true" />
          Read
        </span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import {
  MegaphoneIcon,
  ChartBarIcon,
  BanknotesIcon,
  CalendarIcon,
  ExclamationTriangleIcon,
  TrophyIcon,
  CheckIcon,
  CheckCircleIcon,
  ChevronDownIcon,
} from '@heroicons/vue/24/outline';

interface Announcement {
  id: number;
  title: string;
  content: string;
  summary: string | null;
  type: string;
  priority: string;
  is_pinned: boolean;
  published_at: string;
}

defineProps<{
  announcement: Announcement;
  isRead: boolean;
  pinned?: boolean;
}>();

defineEmits<{
  markRead: [id: number];
}>();

const expanded = ref(false);

const getTypeIcon = (type: string) => {
  const icons: Record<string, any> = {
    general: MegaphoneIcon,
    financial: ChartBarIcon,
    dividend: BanknotesIcon,
    meeting: CalendarIcon,
    urgent: ExclamationTriangleIcon,
    milestone: TrophyIcon,
  };
  return icons[type] || MegaphoneIcon;
};

const getTypeIconClass = (type: string) => {
  const classes: Record<string, string> = {
    general: 'bg-gradient-to-br from-blue-500 to-indigo-600',
    financial: 'bg-gradient-to-br from-emerald-500 to-teal-600',
    dividend: 'bg-gradient-to-br from-green-500 to-emerald-600',
    meeting: 'bg-gradient-to-br from-violet-500 to-purple-600',
    urgent: 'bg-gradient-to-br from-red-500 to-rose-600',
    milestone: 'bg-gradient-to-br from-amber-500 to-orange-600',
  };
  return classes[type] || 'bg-gradient-to-br from-gray-500 to-slate-600';
};

const getTypeBadgeClass = (type: string) => {
  const classes: Record<string, string> = {
    general: 'bg-blue-100 text-blue-700',
    financial: 'bg-emerald-100 text-emerald-700',
    dividend: 'bg-green-100 text-green-700',
    meeting: 'bg-violet-100 text-violet-700',
    urgent: 'bg-red-100 text-red-700',
    milestone: 'bg-amber-100 text-amber-700',
  };
  return classes[type] || 'bg-gray-100 text-gray-700';
};

const getTypeLabel = (type: string) => {
  const labels: Record<string, string> = {
    general: 'General Update',
    financial: 'Financial News',
    dividend: 'Dividend',
    meeting: 'Meeting',
    urgent: 'Urgent',
    milestone: 'Milestone',
  };
  return labels[type] || type;
};

const formatDate = (date: string) => {
  const d = new Date(date);
  const now = new Date();
  const diff = now.getTime() - d.getTime();
  const days = Math.floor(diff / (1000 * 60 * 60 * 24));
  
  if (days === 0) return 'Today';
  if (days === 1) return 'Yesterday';
  if (days < 7) return `${days} days ago`;
  
  return d.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: d.getFullYear() !== now.getFullYear() ? 'numeric' : undefined,
  });
};
</script>
