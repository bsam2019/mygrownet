<template>
  <div
    :class="[
      'bg-white rounded-xl shadow-lg overflow-hidden transition-all',
      !isRead && 'ring-2 ring-blue-500 ring-opacity-50',
      pinned && 'border-l-4 border-blue-600'
    ]"
  >
    <div class="p-6">
      <!-- Header -->
      <div class="flex items-start justify-between mb-3">
        <div class="flex items-center gap-3">
          <div :class="getTypeIconClass(announcement.type)" class="w-10 h-10 rounded-lg flex items-center justify-center">
            <component :is="getTypeIcon(announcement.type)" class="h-5 w-5" aria-hidden="true" />
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h3 class="font-semibold text-gray-900">{{ announcement.title }}</h3>
              <span v-if="!isRead" class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                New
              </span>
            </div>
            <div class="flex items-center gap-2 mt-1">
              <span :class="getTypeBadgeClass(announcement.type)" class="px-2 py-0.5 text-xs font-medium rounded-full">
                {{ getTypeLabel(announcement.type) }}
              </span>
              <span v-if="announcement.priority === 'urgent'" class="px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-800">
                Urgent
              </span>
              <span v-else-if="announcement.priority === 'high'" class="px-2 py-0.5 text-xs font-medium rounded-full bg-amber-100 text-amber-800">
                High Priority
              </span>
            </div>
          </div>
        </div>
        <span class="text-sm text-gray-500">{{ formatDate(announcement.published_at) }}</span>
      </div>

      <!-- Content -->
      <div class="text-gray-700">
        <p v-if="!expanded" class="line-clamp-3">
          {{ announcement.summary || announcement.content }}
        </p>
        <div v-else class="whitespace-pre-wrap">
          {{ announcement.content }}
        </div>
      </div>

      <!-- Actions -->
      <div class="flex items-center justify-between mt-4 pt-4 border-t">
        <button
          @click="expanded = !expanded"
          class="text-blue-600 hover:text-blue-800 text-sm font-medium"
        >
          {{ expanded ? 'Show Less' : 'Read More' }}
        </button>
        <button
          v-if="!isRead"
          @click="$emit('markRead', announcement.id)"
          class="text-gray-500 hover:text-gray-700 text-sm"
        >
          Mark as read
        </button>
        <span v-else class="text-sm text-gray-400 flex items-center gap-1">
          <CheckIcon class="h-4 w-4" aria-hidden="true" />
          Read
        </span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import {
  MegaphoneIcon,
  ChartBarIcon,
  BanknotesIcon,
  CalendarIcon,
  ExclamationTriangleIcon,
  TrophyIcon,
  CheckIcon,
} from '@heroicons/vue/24/outline'

interface Announcement {
  id: number
  title: string
  content: string
  summary: string | null
  type: string
  priority: string
  is_pinned: boolean
  published_at: string
}

const props = defineProps<{
  announcement: Announcement
  isRead: boolean
  pinned?: boolean
}>()

defineEmits<{
  markRead: [id: number]
}>()

const expanded = ref(false)

const getTypeIcon = (type: string) => {
  const icons: Record<string, any> = {
    general: MegaphoneIcon,
    financial: ChartBarIcon,
    dividend: BanknotesIcon,
    meeting: CalendarIcon,
    urgent: ExclamationTriangleIcon,
    milestone: TrophyIcon,
  }
  return icons[type] || MegaphoneIcon
}

const getTypeIconClass = (type: string) => {
  const classes: Record<string, string> = {
    general: 'bg-blue-100 text-blue-600',
    financial: 'bg-green-100 text-green-600',
    dividend: 'bg-emerald-100 text-emerald-600',
    meeting: 'bg-purple-100 text-purple-600',
    urgent: 'bg-red-100 text-red-600',
    milestone: 'bg-amber-100 text-amber-600',
  }
  return classes[type] || 'bg-gray-100 text-gray-600'
}

const getTypeBadgeClass = (type: string) => {
  const classes: Record<string, string> = {
    general: 'bg-blue-100 text-blue-800',
    financial: 'bg-green-100 text-green-800',
    dividend: 'bg-emerald-100 text-emerald-800',
    meeting: 'bg-purple-100 text-purple-800',
    urgent: 'bg-red-100 text-red-800',
    milestone: 'bg-amber-100 text-amber-800',
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}

const getTypeLabel = (type: string) => {
  const labels: Record<string, string> = {
    general: 'General Update',
    financial: 'Financial News',
    dividend: 'Dividend',
    meeting: 'Meeting',
    urgent: 'Urgent',
    milestone: 'Milestone',
  }
  return labels[type] || type
}

const formatDate = (date: string) => {
  const d = new Date(date)
  const now = new Date()
  const diff = now.getTime() - d.getTime()
  const days = Math.floor(diff / (1000 * 60 * 60 * 24))
  
  if (days === 0) {
    return 'Today'
  } else if (days === 1) {
    return 'Yesterday'
  } else if (days < 7) {
    return `${days} days ago`
  } else {
    return d.toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: d.getFullYear() !== now.getFullYear() ? 'numeric' : undefined,
    })
  }
}
</script>
