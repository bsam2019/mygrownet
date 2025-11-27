<template>
  <div
    v-if="announcement"
    :class="[
      'relative rounded-2xl p-4 border shadow-sm backdrop-blur-sm transition-all duration-300 hover:shadow-md',
      bannerClasses
    ]"
  >
    <!-- Decorative gradient -->
    <div :class="['absolute inset-0 rounded-2xl opacity-50', gradientClass]"></div>
    
    <!-- Close Button -->
    <button
      @click="markAsRead"
      class="absolute top-3 right-3 p-1.5 rounded-lg hover:bg-white/30 transition-colors z-10"
      :aria-label="`Dismiss ${announcement.title} announcement`"
    >
      <XMarkIcon class="h-4 w-4" aria-hidden="true" />
    </button>

    <!-- Content -->
    <div class="relative pr-10">
      <!-- Header -->
      <div class="flex items-center gap-3 mb-2">
        <div :class="['w-8 h-8 rounded-lg flex items-center justify-center', iconBgClass]">
          <component
            :is="iconComponent"
            class="h-4 w-4"
            aria-hidden="true"
          />
        </div>
        <div class="flex items-center gap-2 flex-wrap">
          <h3 class="font-semibold text-sm">{{ announcement.title }}</h3>
          <span
            v-if="announcement.is_urgent"
            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-500 text-white animate-pulse"
          >
            Urgent
          </span>
        </div>
      </div>

      <!-- Message -->
      <p class="text-sm leading-relaxed mb-2 ml-11 opacity-90">{{ announcement.message }}</p>

      <!-- Timestamp -->
      <p class="text-xs opacity-60 ml-11">{{ announcement.created_at_human }}</p>
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

const props = defineProps<{
  announcement: Announcement | null
}>()

const emit = defineEmits<{
  dismissed: [id: number]
}>()

const bannerClasses = computed(() => {
  if (!props.announcement) return ''
  const classes: Record<string, string> = {
    info: 'bg-blue-50/80 border-blue-200 text-blue-900',
    warning: 'bg-amber-50/80 border-amber-200 text-amber-900',
    success: 'bg-emerald-50/80 border-emerald-200 text-emerald-900',
    urgent: 'bg-red-50/80 border-red-200 text-red-900',
  }
  return classes[props.announcement.type] || classes.info
})

const gradientClass = computed(() => {
  if (!props.announcement) return ''
  const classes: Record<string, string> = {
    info: 'bg-gradient-to-r from-blue-100/50 to-transparent',
    warning: 'bg-gradient-to-r from-amber-100/50 to-transparent',
    success: 'bg-gradient-to-r from-emerald-100/50 to-transparent',
    urgent: 'bg-gradient-to-r from-red-100/50 to-transparent',
  }
  return classes[props.announcement.type] || classes.info
})

const iconBgClass = computed(() => {
  if (!props.announcement) return ''
  const classes: Record<string, string> = {
    info: 'bg-blue-100 text-blue-600',
    warning: 'bg-amber-100 text-amber-600',
    success: 'bg-emerald-100 text-emerald-600',
    urgent: 'bg-red-100 text-red-600',
  }
  return classes[props.announcement.type] || classes.info
})

const iconComponent = computed(() => {
  if (!props.announcement) return InformationCircleIcon
  const icons: Record<string, typeof InformationCircleIcon> = {
    info: InformationCircleIcon,
    warning: ExclamationTriangleIcon,
    success: CheckCircleIcon,
    urgent: ExclamationCircleIcon,
  }
  return icons[props.announcement.type] || InformationCircleIcon
})

const markAsRead = async () => {
  if (!props.announcement) return
  try {
    await router.post(`/investor/announcements/${props.announcement.id}/read`, {}, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => emit('dismissed', props.announcement!.id),
      onError: (errors) => console.error('Failed to mark announcement as read:', errors)
    })
  } catch (error) {
    console.error('Error marking announcement as read:', error)
  }
}
</script>
