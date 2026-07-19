<template>
  <TransitionRoot as="template" :show="open">
    <Dialog as="div" class="relative z-50" @close="handleClose">
      <TransitionChild
        as="template"
        enter="ease-in-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in-out duration-300"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-gray-900/80 transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-hidden">
        <div class="absolute inset-0 overflow-hidden">
          <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10 sm:pl-16">
            <TransitionChild
              as="template"
              enter="transform transition ease-in-out duration-300"
              enter-from="translate-x-full"
              enter-to="translate-x-0"
              leave="transform transition ease-in-out duration-300"
              leave-from="translate-x-0"
              leave-to="translate-x-full"
            >
              <DialogPanel class="pointer-events-auto w-screen" :class="widthClass">
                <div class="flex h-full flex-col bg-white shadow-xl">
                  <!-- Header -->
                  <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-6 shadow-lg">
                    <div class="flex items-start justify-between">
                      <div class="flex-1 min-w-0">
                        <DialogTitle class="text-xl font-bold text-white">
                          {{ title }}
                        </DialogTitle>
                        <p v-if="subtitle" class="mt-1.5 text-sm text-blue-100">
                          {{ subtitle }}
                        </p>
                      </div>
                      <div class="ml-4 flex items-center">
                        <button
                          type="button"
                          class="rounded-lg p-2 text-blue-100 hover:bg-blue-500/30 hover:text-white focus:outline-none focus:ring-2 focus:ring-white/50 transition-colors"
                          aria-label="Close panel"
                          @click="handleClose"
                        >
                          <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                        </button>
                      </div>
                    </div>
                  </div>

                  <!-- Content -->
                  <div class="relative flex-1 overflow-y-auto">
                    <div class="px-6 py-6">
                      <slot />
                    </div>
                  </div>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'

interface Props {
  open: boolean
  title: string
  subtitle?: string
  size?: 'sm' | 'md' | 'lg' | 'xl' | '2xl'
}

const props = withDefaults(defineProps<Props>(), {
  size: 'lg',
})

const emit = defineEmits<{
  (e: 'close'): void
}>()

const widthClass = computed(() => {
  const sizes = {
    sm: 'max-w-md',
    md: 'max-w-lg',
    lg: 'max-w-2xl',
    xl: 'max-w-4xl',
    '2xl': 'max-w-6xl',
  }
  return sizes[props.size]
})

const handleClose = () => {
  emit('close')
}
</script>
