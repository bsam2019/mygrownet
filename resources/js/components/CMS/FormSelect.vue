<script setup lang="ts">
import { computed } from 'vue'
import { ExclamationCircleIcon } from '@heroicons/vue/20/solid'

interface Props {
  label: string
  modelValue: string | number
  options: Array<{ value: string | number; label: string }> | Record<string, string>
  placeholder?: string
  required?: boolean
  error?: string
  helpText?: string
  disabled?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  required: false,
  disabled: false,
})

const emit = defineEmits<{
  'update:modelValue': [value: string | number]
}>()

const optionsArray = computed(() => {
  if (Array.isArray(props.options)) {
    return props.options
  }
  return Object.entries(props.options).map(([value, label]) => ({ value, label }))
})

const selectClasses = computed(() => {
  const base = 'block w-full rounded-lg shadow-sm transition-colors duration-200 sm:text-sm px-4 py-2.5'
  const states = props.error
    ? 'border border-red-300 bg-red-50 text-red-900 focus:border-red-500 focus:ring-2 focus:ring-red-200'
    : 'border border-gray-300 bg-gray-50 text-gray-900 focus:bg-white focus:border-transparent focus:ring-2 focus:ring-blue-500'
  const disabled = props.disabled ? 'bg-gray-100 text-gray-500 cursor-not-allowed border-gray-300' : ''
  
  return `${base} ${states} ${disabled}`
})
</script>

<template>
  <div>
    <label class="block text-sm font-medium text-gray-700 mb-1">
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1">*</span>
    </label>
    
    <div class="relative">
      <select
        :value="modelValue"
        @change="emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
        :required="required"
        :disabled="disabled"
        :class="selectClasses"
      >
        <option v-if="placeholder" value="">{{ placeholder }}</option>
        <option
          v-for="option in optionsArray"
          :key="option.value"
          :value="option.value"
        >
          {{ option.label }}
        </option>
      </select>
      
      <div v-if="error" class="absolute inset-y-0 right-8 flex items-center pointer-events-none">
        <ExclamationCircleIcon class="h-5 w-5 text-red-500" aria-hidden="true" />
      </div>
    </div>
    
    <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
    <p v-else-if="helpText" class="mt-1 text-sm text-gray-500">{{ helpText }}</p>
  </div>
</template>
