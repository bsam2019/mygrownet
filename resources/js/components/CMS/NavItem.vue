<template>
  <div class="relative group">
    <button
      @click="$emit('click')"
      :class="[
        active ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50',
        'flex items-center w-full px-3 py-2.5 text-sm font-medium rounded-lg transition',
        collapsed ? 'justify-center' : ''
      ]"
    >
      <component
        :is="iconComponent"
        :class="[
          active ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600',
          'flex-shrink-0',
          collapsed ? 'h-6 w-6' : 'mr-3 h-5 w-5'
        ]"
        aria-hidden="true"
      />
      <span v-if="!collapsed">{{ label }}</span>
    </button>
    
    <!-- Tooltip for collapsed state -->
    <div
      v-if="collapsed"
      class="absolute left-full ml-2 px-3 py-2 bg-gray-900 text-white text-sm rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 whitespace-nowrap z-50 top-1/2 -translate-y-1/2"
    >
      {{ label }}
      <div class="absolute right-full top-1/2 -translate-y-1/2 border-4 border-transparent border-r-gray-900"></div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import {
  HomeIcon,
  BriefcaseIcon,
  UsersIcon,
  DocumentTextIcon,
  CreditCardIcon,
  ChartBarIcon,
  Cog6ToothIcon,
} from '@heroicons/vue/24/outline'

interface Props {
  icon: string
  label: string
  routeName: string
  collapsed: boolean
  active: boolean
}

const props = defineProps<Props>()

defineEmits<{
  (e: 'click'): void
}>()

const iconMap: Record<string, any> = {
  HomeIcon,
  BriefcaseIcon,
  UsersIcon,
  DocumentTextIcon,
  CreditCardIcon,
  ChartBarIcon,
  Cog6ToothIcon,
}

const iconComponent = computed(() => iconMap[props.icon] || HomeIcon)
</script>
