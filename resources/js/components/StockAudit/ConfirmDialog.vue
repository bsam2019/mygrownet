<script setup lang="ts">
import { useConfirmDialog } from '@/composables/useConfirmDialog'
import { ExclamationTriangleIcon, XMarkIcon } from '@heroicons/vue/24/outline'

const { visible, title, message, confirm, cancel } = useConfirmDialog()
</script>

<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="visible" class="fixed inset-0 z-[200] flex items-center justify-center">
        <div class="fixed inset-0 bg-black/40" @click="cancel"></div>
        <div class="relative z-10 w-full max-w-md rounded-xl bg-white p-6 shadow-2xl">
          <div class="flex items-start gap-4">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100">
              <ExclamationTriangleIcon class="h-6 w-6 text-red-600" />
            </div>
            <div class="flex-1">
              <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
              <p class="mt-1 text-sm text-gray-600">{{ message }}</p>
            </div>
            <button @click="cancel" class="shrink-0 p-1 rounded hover:bg-gray-100">
              <XMarkIcon class="h-5 w-5 text-gray-400" />
            </button>
          </div>
          <div class="mt-6 flex justify-end gap-3">
            <button @click="cancel" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
            <button @click="confirm" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Delete</button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-enter-active { transition: all 0.2s ease-out; }
.modal-leave-active { transition: all 0.15s ease-in; }
.modal-enter-from { opacity: 0; }
.modal-leave-to { opacity: 0; }
</style>