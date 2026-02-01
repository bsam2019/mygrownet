<template>
  <MemberLayout>
    <div class="py-6">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Back Button -->
        <div class="mb-6">
          <Link
            :href="route('learning.index')"
            class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900"
          >
            <ArrowLeftIcon class="h-4 w-4 mr-2" aria-hidden="true" />
            Back to Modules
          </Link>
        </div>

        <!-- Module Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <div v-if="module.category" class="mb-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                  {{ module.category }}
                </span>
              </div>
              <h1 class="text-3xl font-bold text-gray-900 mb-2">
                {{ module.title }}
              </h1>
              <p v-if="module.description" class="text-gray-600 mb-4">
                {{ module.description }}
              </p>
              <div class="flex items-center gap-4 text-sm text-gray-500">
                <div class="flex items-center gap-1">
                  <ClockIcon class="h-4 w-4" aria-hidden="true" />
                  <span>{{ module.estimated_minutes }} minutes</span>
                </div>
                <div v-if="module.is_required" class="flex items-center gap-1">
                  <StarIcon class="h-4 w-4 text-yellow-500" aria-hidden="true" />
                  <span class="text-yellow-600 font-medium">Required</span>
                </div>
              </div>
            </div>
            <div v-if="isCompleted" class="ml-4">
              <div class="flex items-center gap-2 text-green-600 font-medium">
                <CheckCircleIcon class="h-6 w-6" aria-hidden="true" />
                <span>Completed</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Module Content -->
        <div class="bg-white rounded-lg shadow-md p-8 mb-6">
          <div class="prose prose-blue max-w-none" v-html="renderedContent"></div>
        </div>

        <!-- Complete Button -->
        <div v-if="!isCompleted" class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-semibold text-gray-900">Ready to complete?</h3>
              <p class="text-sm text-gray-600 mt-1">
                Mark this module as complete to earn your LGR activity credit
              </p>
            </div>
            <button
              @click="completeModule"
              :disabled="processing"
              class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="processing">Completing...</span>
              <span v-else>Complete Module</span>
            </button>
          </div>
        </div>

        <!-- Already Completed Message -->
        <div v-else class="bg-green-50 border border-green-200 rounded-lg p-6">
          <div class="flex items-center gap-3">
            <CheckCircleIcon class="h-6 w-6 text-green-600" aria-hidden="true" />
            <div>
              <h3 class="text-lg font-semibold text-green-900">Module Completed!</h3>
              <p class="text-sm text-green-700 mt-1">
                You've already completed this module and earned your LGR credit.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </MemberLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';
import { ArrowLeftIcon, ClockIcon, StarIcon, CheckCircleIcon } from 'lucide-vue-next';
import { marked } from 'marked';

interface Module {
  id: number;
  title: string;
  slug: string;
  description: string | null;
  content: string;
  estimated_minutes: number;
  category: string | null;
  is_required: boolean;
}

interface Props {
  module: Module;
  isCompleted: boolean;
}

const props = defineProps<Props>();

const processing = ref(false);
const startTime = ref(Date.now());

const renderedContent = computed(() => {
  return marked(props.module.content);
});

const completeModule = () => {
  processing.value = true;
  
  const timeSpent = Math.floor((Date.now() - startTime.value) / 1000);
  
  router.post(route('learning.complete', props.module.id), {
    time_spent_seconds: timeSpent,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      processing.value = false;
    },
    onError: () => {
      processing.value = false;
    },
  });
};
</script>

<style scoped>
.prose {
  @apply text-gray-700;
}

.prose h1 {
  @apply text-2xl font-bold text-gray-900 mt-8 mb-4;
}

.prose h2 {
  @apply text-xl font-bold text-gray-900 mt-6 mb-3;
}

.prose h3 {
  @apply text-lg font-semibold text-gray-900 mt-4 mb-2;
}

.prose p {
  @apply mb-4 leading-relaxed;
}

.prose ul, .prose ol {
  @apply mb-4 ml-6;
}

.prose li {
  @apply mb-2;
}

.prose strong {
  @apply font-semibold text-gray-900;
}

.prose a {
  @apply text-blue-600 hover:text-blue-700 underline;
}

.prose code {
  @apply bg-gray-100 px-1.5 py-0.5 rounded text-sm font-mono text-gray-800;
}

.prose pre {
  @apply bg-gray-100 p-4 rounded-lg overflow-x-auto mb-4;
}

.prose blockquote {
  @apply border-l-4 border-blue-500 pl-4 italic text-gray-600 my-4;
}
</style>
