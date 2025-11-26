<template>
  <div
    v-if="announcement"
    :class="[
      'relative rounded-lg p-4 border-l-4 shadow-sm',
      bannerClasses
    ]"
  >
    <!-- Close Button -->
    <button
      @click="markAsRead"
      class="absolute top-2 right-2 p-1 rounded-full hover:bg-white/20 transition-colors"
      :aria-label="`Dismiss ${announcement.title} announcement`"
    >
      <XMarkIcon class="h-4 w-4" aria-hidden="true" />
    </button>

    <!-- Content -->
    <div class="pr-8">
      <!-- Header -->
      <div class="flex items-center gap-2 mb-2 flex-wrap">
        <component
          :is="iconComponent"
          class="h-5 w-5 flex-shrink-0"
          aria-hidden="true"
        />
        <h3 class="font-semibold text-sm">{{ announcement.title }}</h3>
        <span
          v-if="announcement.is_urgent"
          class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"
        >
          Urgent
        </span>
      </div>

      <!-- Message -->
      <p class="text-sm leading-relaxed mb-2 ml-7">{{ announcement.message }}</p>

      <!-- Timestamp -->
      <p class="text-xs opacity-75 ml-7">{{ announcement.created_at_human }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import {
  XMarkIcon,
  InformationCircleIcon,
  ExclamationTriangleIcon,
  CheckCircleIcon,
  ExclamationCircleIcon,
} from '@heroicons/vue/24/outline'

interface Announcement {
  id: number
  title: string
  message: string
  type: 'info' | 'warning' | 'success' | 'urgent'
  is_urgent: boolean
  created_at: string
  created_at_human: string
}

interface Props {
  announcement: Announcement | null
}

const props = defineProps<Props>()

const emit = defineEmits<{
  dismissed: [id: number]
}>()

const bannerClasses = computed(() => {
  if (!props.announcement) return ''
  
  switch (props.announcement.type) {
    case 'info':
      return 'bg-blue-50 border-blue-400 text-blue-800'
    case 'warning':
      return 'bg-amber-50 border-amber-400 text-amber-800'
    case 'success':
      return 'bg-green-50 border-green-400 text-green-800'
    case 'urgent':
      return 'bg-red-50 border-red-400 text-red-800'
    default:
      return 'bg-gray-50 border-gray-400 text-gray-800'
  }
})

const iconComponent = computed(() => {
  if (!props.announcement) return InformationCircleIcon
  
  switch (props.announcement.type) {
    case 'info':
      return InformationCircleIcon
    case 'warning':
      return ExclamationTriangleIcon
    case 'success':
      return CheckCircleIcon
    case 'urgent':
      return ExclamationCircleIcon
    default:
      return InformationCircleIcon
  }
})

const markAsRead = async () => {
  if (!props.announcement) return

  try {
    await router.post(`/investor/announcements/${props.announcement.id}/read`, {}, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        emit('dismissed', props.announcement!.id)
      },
      onError: (errors) => {
        console.error('Failed to mark announcement as read:', errors)
      }
    })
  } catch (error) {
    console.error('Error marking announcement as read:', error)
  }
}
</script>