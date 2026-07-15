<script setup lang="ts">
import { useNotifications } from '@/composables/useNotifications'
import { XMarkIcon, CheckCircleIcon, ExclamationCircleIcon, ExclamationTriangleIcon, InformationCircleIcon } from '@heroicons/vue/24/outline'

const { notifications, remove } = useNotifications()

const iconMap: Record<string, any> = {
  success: CheckCircleIcon,
  error: ExclamationCircleIcon,
  warning: ExclamationTriangleIcon,
  info: InformationCircleIcon,
}

const colorMap: Record<string, string> = {
  success: 'border-emerald-500 bg-emerald-50 text-emerald-800',
  error: 'border-red-500 bg-red-50 text-red-800',
  warning: 'border-amber-500 bg-amber-50 text-amber-800',
  info: 'border-blue-500 bg-blue-50 text-blue-800',
}
</script>

<template>
  <div class="fixed top-4 right-4 z-[100] flex flex-col gap-2 max-w-sm">
    <transition-group name="toast">
      <div
        v-for="n in notifications"
        :key="n.id"
        :class="['flex items-start gap-3 rounded-lg border-l-4 p-4 shadow-lg', colorMap[n.type]]"
      >
        <component :is="iconMap[n.type]" class="h-5 w-5 shrink-0 mt-0.5" />
        <p class="text-sm flex-1">{{ n.message }}</p>
        <button @click="remove(n.id)" class="shrink-0 p-0.5 rounded hover:bg-black/5">
          <XMarkIcon class="h-4 w-4" />
        </button>
      </div>
    </transition-group>
  </div>
</template>

<style scoped>
.toast-enter-active { transition: all 0.3s ease-out; }
.toast-leave-active { transition: all 0.2s ease-in; }
.toast-enter-from { opacity: 0; transform: translateX(100%); }
.toast-leave-to { opacity: 0; transform: translateX(100%); }
</style>