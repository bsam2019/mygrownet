<script setup>
import { watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  maxWidth: {
    type: String,
    default: '2xl'
  },
  closeable: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['close']);

watch(() => props.show, (show) => {
  if (show) {
    document.body.style.overflow = 'hidden';
  } else {
    document.body.style.overflow = null;
  }
});

const close = () => {
  if (props.closeable) {
    emit('close');
  }
};

const closeOnEscape = (e) => {
  if (e.key === 'Escape' && props.show) {
    close();
  }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));
onUnmounted(() => document.removeEventListener('keydown', closeOnEscape));

const maxWidthClass = {
  'sm': 'sm:max-w-sm',
  'md': 'sm:max-w-md',
  'lg': 'sm:max-w-lg',
  'xl': 'sm:max-w-xl',
  '2xl': 'sm:max-w-2xl',
}[props.maxWidth];
</script>

<template>
  <teleport to="body">
    <div v-if="show" class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50">
      <div class="fixed inset-0 transform transition-all" @click="close">
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
      </div>

      <div class="mb-6 bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:mx-auto"
        :class="maxWidthClass">
        <slot></slot>
      </div>
    </div>
  </teleport>
</template>