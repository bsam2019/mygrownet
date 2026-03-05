<template>
  <button
    @click="showModal = true"
    type="button"
    class="inline-flex items-center justify-center w-4 h-4 text-gray-400 hover:text-blue-600 transition-colors"
    :aria-label="`Help: ${title}`"
  >
    <QuestionMarkCircleIcon class="h-4 w-4" aria-hidden="true" />
  </button>
  
  <!-- Modal -->
  <Teleport to="body">
    <Transition
      enter-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div 
        v-if="showModal" 
        @click="showModal = false"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
        style="z-index: 99999;"
      >
        <Transition
          enter-active-class="transition-all duration-200"
          enter-from-class="opacity-0 scale-95"
          enter-to-class="opacity-100 scale-100"
          leave-active-class="transition-all duration-200"
          leave-from-class="opacity-100 scale-100"
          leave-to-class="opacity-0 scale-95"
        >
          <div 
            v-if="showModal"
            @click.stop
            class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-2xl"
          >
            <div class="flex items-start gap-3 mb-4">
              <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0">
                <InformationCircleIcon class="h-6 w-6 text-blue-600" />
              </div>
              <div class="flex-1 min-w-0">
                <h3 class="font-bold text-lg text-gray-900">{{ title }}</h3>
              </div>
            </div>
            <p class="text-sm text-gray-600 leading-relaxed mb-6">{{ description }}</p>
            <button 
              @click="showModal = false" 
              class="w-full py-3 px-4 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 active:scale-95 transition-all"
            >
              Got it
            </button>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { QuestionMarkCircleIcon, InformationCircleIcon } from '@heroicons/vue/24/outline';

interface Props {
  title: string;
  description: string;
}

defineProps<Props>();

const showModal = ref(false);
</script>
