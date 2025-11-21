<script setup lang="ts">
import { ref, watch } from 'vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';
import AnalyticsView from './AnalyticsView.vue';

interface Props {
  show: boolean;
  userId: number;
}

const props = defineProps<Props>();
const emit = defineEmits(['close']);

const closeModal = () => {
  emit('close');
};

// Prevent body scroll when modal is open
watch(() => props.show, (newVal) => {
  if (newVal) {
    document.body.style.overflow = 'hidden';
  } else {
    document.body.style.overflow = '';
  }
});
</script>

<template>
  <Transition
    enter-active-class="transition-opacity duration-200"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition-opacity duration-200"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-if="show"
      class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[100]"
      @click="closeModal"
    ></div>
  </Transition>

  <Transition
    enter-active-class="transition-transform duration-300 ease-out"
    enter-from-class="translate-y-full"
    enter-to-class="translate-y-0"
    leave-active-class="transition-transform duration-300 ease-in"
    leave-from-class="translate-y-0"
    leave-to-class="translate-y-full"
  >
    <div
      v-if="show"
      class="fixed inset-x-0 bottom-0 top-0 bg-gradient-to-br from-gray-50 to-gray-100 z-[101] flex flex-col"
      @click.stop
    >
      <!-- Header -->
      <div class="bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-600 text-white shadow-xl flex-shrink-0">
        <div class="px-4 py-4 flex items-center justify-between">
          <div>
            <h2 class="text-xl font-bold">Performance Analytics</h2>
            <p class="text-sm opacity-90 mt-0.5">Your insights & recommendations</p>
          </div>
          <button
            @click="closeModal"
            class="p-2 rounded-xl bg-white/15 hover:bg-white/25 backdrop-blur-sm transition-all active:scale-95 border border-white/20"
          >
            <XMarkIcon class="h-6 w-6" />
          </button>
        </div>
      </div>

      <!-- Content - Scrollable with bottom padding for footer -->
      <div class="flex-1 overflow-y-auto px-4 py-6 pb-24">
        <AnalyticsView :user-id="userId" />
      </div>
    </div>
  </Transition>
</template>
