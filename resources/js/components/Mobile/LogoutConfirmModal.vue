<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="show"
        class="fixed inset-0 z-[100000] flex items-center justify-center p-4"
        @click.self="emit('cancel')"
      >
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
        
        <!-- Modal -->
        <div class="relative w-full max-w-sm bg-white rounded-2xl shadow-2xl transform transition-all">
          <!-- Icon -->
          <div class="pt-6 px-6 flex justify-center">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center">
              <ArrowRightOnRectangleIcon class="h-8 w-8 text-red-600" />
            </div>
          </div>

          <!-- Content -->
          <div class="p-6 text-center">
            <h3 class="text-lg font-bold text-gray-900 mb-2">Logout Confirmation</h3>
            <p class="text-sm text-gray-600">
              Are you sure you want to logout? You'll need to login again to access your account.
            </p>
          </div>

          <!-- Actions -->
          <div class="px-6 pb-6 flex gap-3">
            <button
              @click="emit('cancel')"
              class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition-colors active:scale-98"
            >
              Cancel
            </button>
            <button
              @click="emit('confirm')"
              :disabled="loading"
              class="flex-1 px-4 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl font-semibold hover:from-red-700 hover:to-red-800 transition-all shadow-lg disabled:opacity-50 disabled:cursor-not-allowed active:scale-98"
            >
              <span v-if="loading">Logging out...</span>
              <span v-else>Logout</span>
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { ArrowRightOnRectangleIcon } from '@heroicons/vue/24/outline';

interface Props {
  show: boolean;
}

defineProps<Props>();
const emit = defineEmits(['confirm', 'cancel']);

const loading = ref(false);
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active .relative,
.modal-leave-active .relative {
  transition: transform 0.3s ease, opacity 0.3s ease;
}

.modal-enter-from .relative,
.modal-leave-to .relative {
  transform: scale(0.95);
  opacity: 0;
}
</style>
