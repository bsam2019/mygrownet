<template>
  <Teleport to="body">
    <Transition name="toast">
      <div
        v-if="show"
        class="fixed top-20 left-1/2 -translate-x-1/2 z-[999999] max-w-sm w-full px-4"
      >
        <div
          class="rounded-xl shadow-2xl p-4 flex items-center gap-3 backdrop-blur-sm"
          :class="getToastClass(type)"
        >
          <!-- Icon -->
          <div class="flex-shrink-0">
            <component
              :is="getIcon(type)"
              class="h-6 w-6"
              :class="getIconClass(type)"
            />
          </div>

          <!-- Message -->
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium" :class="getTextClass(type)">
              {{ message }}
            </p>
          </div>

          <!-- Close Button -->
          <button
            @click="close"
            class="flex-shrink-0 p-1 rounded-full hover:bg-black/10 transition-colors"
          >
            <XMarkIcon class="h-4 w-4" :class="getTextClass(type)" />
          </button>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { CheckCircleIcon, ExclamationTriangleIcon, InformationCircleIcon, XCircleIcon, XMarkIcon } from '@heroicons/vue/24/outline';

interface Props {
  show: boolean;
  message: string;
  type?: 'success' | 'error' | 'warning' | 'info';
  duration?: number;
}

const props = withDefaults(defineProps<Props>(), {
  type: 'success',
  duration: 3000,
});

const emit = defineEmits(['close']);

let timeout: NodeJS.Timeout | null = null;

const close = () => {
  emit('close');
};

watch(() => props.show, (newValue) => {
  if (newValue) {
    if (timeout) clearTimeout(timeout);
    timeout = setTimeout(() => {
      close();
    }, props.duration);
  }
});

const getToastClass = (type: string) => {
  return {
    'bg-green-50 border-2 border-green-200': type === 'success',
    'bg-red-50 border-2 border-red-200': type === 'error',
    'bg-yellow-50 border-2 border-yellow-200': type === 'warning',
    'bg-blue-50 border-2 border-blue-200': type === 'info',
  };
};

const getIcon = (type: string) => {
  return type === 'success' ? CheckCircleIcon :
         type === 'error' ? XCircleIcon :
         type === 'warning' ? ExclamationTriangleIcon :
         InformationCircleIcon;
};

const getIconClass = (type: string) => {
  return type === 'success' ? 'text-green-600' :
         type === 'error' ? 'text-red-600' :
         type === 'warning' ? 'text-yellow-600' :
         'text-blue-600';
};

const getTextClass = (type: string) => {
  return type === 'success' ? 'text-green-900' :
         type === 'error' ? 'text-red-900' :
         type === 'warning' ? 'text-yellow-900' :
         'text-blue-900';
};
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translate(-50%, -20px);
}

.toast-leave-to {
  opacity: 0;
  transform: translate(-50%, -20px);
}
</style>
