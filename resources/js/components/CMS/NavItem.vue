<template>
  <div ref="navItemRef" class="relative">
    <button
      @click="$emit('click')"
      @mouseenter="showTooltip = true"
      @mouseleave="showTooltip = false"
      :class="[
        active 
          ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-md shadow-blue-500/20' 
          : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900',
        'flex items-center w-full px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-200',
        collapsed ? 'justify-center' : '',
        'group-hover:scale-[1.02]'
      ]"
    >
      <component
        :is="iconComponent"
        :class="[
          active ? 'text-white' : 'text-gray-400 group-hover:text-blue-500',
          'flex-shrink-0 transition-colors duration-200',
          collapsed ? 'h-6 w-6' : 'mr-3 h-5 w-5'
        ]"
        aria-hidden="true"
      />
      <span v-if="!collapsed" :class="active ? 'font-semibold' : 'font-medium'">{{ label }}</span>
      
      <!-- Active indicator -->
      <div 
        v-if="active && !collapsed" 
        class="ml-auto w-1.5 h-1.5 rounded-full bg-white animate-pulse"
      ></div>
    </button>
    
    <!-- Tooltip for collapsed state - teleported to body -->
    <Teleport to="body">
      <div
        v-if="collapsed && showTooltip && tooltipPosition"
        :style="{
          position: 'fixed',
          left: `${tooltipPosition.left}px`,
          top: `${tooltipPosition.top}px`,
          transform: 'translateY(-50%)'
        }"
        class="px-3 py-2 bg-gradient-to-r from-gray-900 to-gray-800 text-white text-sm rounded-xl whitespace-nowrap z-[9999] pointer-events-none shadow-xl border border-gray-700/50"
      >
        {{ label }}
        <div class="absolute right-full top-1/2 -translate-y-1/2 border-[6px] border-transparent border-r-gray-900"></div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch, onMounted, onUnmounted } from 'vue'
import { Teleport } from 'vue'
import {
  HomeIcon,
  BriefcaseIcon,
  UsersIcon,
  DocumentTextIcon,
  CreditCardIcon,
  ChartBarIcon,
  Cog6ToothIcon,
  PresentationChartLineIcon,
  CurrencyDollarIcon,
  BanknotesIcon,
  DocumentDuplicateIcon,
  CubeIcon,
  ComputerDesktopIcon,
  UserGroupIcon,
  SwatchIcon,
  ClockIcon,
  ArrowPathIcon,
  CheckCircleIcon,
  CalculatorIcon,
  EnvelopeIcon,
  DevicePhoneMobileIcon,
  ShieldCheckIcon,
  DocumentChartBarIcon,
  BuildingOffice2Icon,
  CalendarDaysIcon,
  ClipboardDocumentCheckIcon,
  AcademicCapIcon,
  MinusCircleIcon,
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
  PresentationChartLineIcon,
  CurrencyDollarIcon,
  BanknotesIcon,
  DocumentDuplicateIcon,
  CubeIcon,
  ComputerDesktopIcon,
  UserGroupIcon,
  SwatchIcon,
  ClockIcon,
  ArrowPathIcon,
  CheckCircleIcon,
  CalculatorIcon,
  EnvelopeIcon,
  DevicePhoneMobileIcon,
  ShieldCheckIcon,
  DocumentChartBarIcon,
  BuildingOffice2Icon,
  CalendarDaysIcon,
  ClipboardDocumentCheckIcon,
  AcademicCapIcon,
  MinusCircleIcon,
}

const iconComponent = computed(() => iconMap[props.icon] || HomeIcon)

const navItemRef = ref<HTMLElement | null>(null)
const showTooltip = ref(false)
const tooltipPosition = ref<{ left: number; top: number } | null>(null)

// Update tooltip position
const updateTooltipPosition = () => {
  if (navItemRef.value && props.collapsed) {
    const rect = navItemRef.value.getBoundingClientRect()
    tooltipPosition.value = {
      left: rect.right + 8, // 8px gap from sidebar
      top: rect.top + rect.height / 2
    }
  }
}

// Watch for tooltip visibility and update position
watch(showTooltip, (visible) => {
  if (visible) {
    updateTooltipPosition()
  }
})

// Scroll into view when active
watch(() => props.active, (isActive) => {
  if (isActive && navItemRef.value) {
    navItemRef.value.scrollIntoView({ 
      behavior: 'smooth', 
      block: 'nearest',
      inline: 'nearest'
    })
  }
})

// Also scroll on mount if already active
onMounted(() => {
  if (props.active && navItemRef.value) {
    navItemRef.value.scrollIntoView({ 
      behavior: 'auto', 
      block: 'nearest',
      inline: 'nearest'
    })
  }
  
  // Update tooltip position on scroll
  window.addEventListener('scroll', updateTooltipPosition, true)
})

onUnmounted(() => {
  window.removeEventListener('scroll', updateTooltipPosition, true)
})
</script>
