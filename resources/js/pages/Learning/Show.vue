<template>
  <MemberLayout>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-indigo-50">
      <!-- Header with Breadcrumb -->
      <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <Link
            :href="route('learning.index')"
            class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-blue-600 transition-colors group mb-4"
          >
            <ArrowLeftIcon class="h-4 w-4 mr-2 group-hover:-translate-x-1 transition-transform" aria-hidden="true" />
            Back to Learning Center
          </Link>
          
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <!-- Category Badge -->
              <div v-if="module.category" class="mb-3">
                <span :class="[
                  'inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold',
                  getCategoryStyle(module.category)
                ]">
                  <component :is="getCategoryIcon(module.category)" class="w-4 h-4 mr-2" aria-hidden="true" />
                  {{ module.category }}
                </span>
              </div>
              
              <!-- Title -->
              <h1 class="text-4xl font-bold text-gray-900 mb-3 leading-tight">
                {{ module.title }}
              </h1>
              
              <!-- Description -->
              <p v-if="module.description" class="text-lg text-gray-600 mb-4 leading-relaxed">
                {{ module.description }}
              </p>
              
              <!-- Meta Info -->
              <div class="flex flex-wrap items-center gap-4 text-sm">
                <div class="flex items-center gap-2 text-gray-600">
                  <div class="p-1.5 bg-blue-100 rounded-lg">
                    <ClockIcon class="h-4 w-4 text-blue-600" aria-hidden="true" />
                  </div>
                  <span class="font-medium">{{ module.estimated_minutes }} minutes</span>
                </div>
                
                <div v-if="module.is_required" class="flex items-center gap-2 text-amber-600">
                  <div class="p-1.5 bg-amber-100 rounded-lg">
                    <StarIcon class="h-4 w-4" aria-hidden="true" />
                  </div>
                  <span class="font-semibold">Required Module</span>
                </div>
                
                <div v-if="isCompleted" class="flex items-center gap-2 text-green-600">
                  <div class="p-1.5 bg-green-100 rounded-lg">
                    <CheckCircleIcon class="h-4 w-4" aria-hidden="true" />
                  </div>
                  <span class="font-semibold">Completed</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Progress Indicator -->
        <div v-if="!isCompleted" class="mb-8">
          <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-2xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-semibold mb-1">You're Learning!</h3>
                <p class="text-blue-100 text-sm">Complete this module to earn your LGR activity credit</p>
              </div>
              <div class="text-right">
                <div class="text-3xl font-bold">{{ Math.floor(timeSpent / 60) }}:{{ String(timeSpent % 60).padStart(2, '0') }}</div>
                <div class="text-blue-100 text-sm">Time spent</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Content Card with Beautiful Typography -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden mb-8">
          <div class="p-8 sm:p-12">
            <article class="prose prose-lg prose-blue max-w-none" v-html="renderedContent"></article>
          </div>
        </div>

        <!-- Completion Section -->
        <div v-if="!isCompleted" class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl shadow-lg border-2 border-green-200 p-8">
          <div class="flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="flex items-start gap-4">
              <div class="p-3 bg-green-500 rounded-xl">
                <CheckCircleIcon class="h-8 w-8 text-white" aria-hidden="true" />
              </div>
              <div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Ready to Complete?</h3>
                <p class="text-gray-600 leading-relaxed">
                  Mark this module as complete to earn your LGR activity credit and track your progress.
                </p>
              </div>
            </div>
            <button
              @click="completeModule"
              :disabled="processing"
              class="flex-shrink-0 px-8 py-4 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl hover:from-green-700 hover:to-emerald-700 transition-all font-bold text-lg shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-105 active:scale-95"
            >
              <span v-if="processing" class="flex items-center gap-2">
                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Completing...
              </span>
              <span v-else class="flex items-center gap-2">
                Complete Module
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
              </span>
            </button>
          </div>
        </div>

        <!-- Already Completed -->
        <div v-else class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl shadow-lg border-2 border-green-300 p-8">
          <div class="flex items-center gap-4">
            <div class="p-4 bg-green-500 rounded-2xl">
              <CheckCircleIcon class="h-10 w-10 text-white" aria-hidden="true" />
            </div>
            <div>
              <h3 class="text-2xl font-bold text-green-900 mb-1">Module Completed!</h3>
              <p class="text-green-700 text-lg">
                Great job! You've completed this module and earned your LGR activity credit.
              </p>
            </div>
          </div>
        </div>

        <!-- Navigation to Next Module -->
        <div class="mt-8 flex justify-center">
          <Link
            :href="route('learning.index')"
            class="inline-flex items-center gap-2 px-6 py-3 bg-white text-gray-700 rounded-xl hover:bg-gray-50 transition-all font-medium shadow-md border border-gray-200"
          >
            <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
            Browse More Modules
          </Link>
        </div>
      </div>
    </div>
  </MemberLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';
import { 
  ArrowLeftIcon, 
  ClockIcon, 
  StarIcon, 
  CheckCircleIcon,
  Sparkles,
  Trophy,
  GraduationCap
} from 'lucide-vue-next';
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
const timeSpent = ref(0);
let intervalId: number | null = null;

onMounted(() => {
  // Update time spent every second
  intervalId = window.setInterval(() => {
    timeSpent.value = Math.floor((Date.now() - startTime.value) / 1000);
  }, 1000);
});

onUnmounted(() => {
  if (intervalId) {
    clearInterval(intervalId);
  }
});

const renderedContent = computed(() => {
  return marked(props.module.content);
});

const completeModule = () => {
  processing.value = true;
  
  const timeSpentSeconds = Math.floor((Date.now() - startTime.value) / 1000);
  
  router.post(route('learning.complete', props.module.id), {
    time_spent_seconds: timeSpentSeconds,
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

const getCategoryIcon = (category: string | null) => {
  if (category === 'Life Skills') return Sparkles;
  if (category === 'GrowNet') return Trophy;
  return GraduationCap;
};

const getCategoryStyle = (category: string | null) => {
  if (category === 'Life Skills') return 'bg-emerald-100 text-emerald-700';
  if (category === 'GrowNet') return 'bg-purple-100 text-purple-700';
  return 'bg-blue-100 text-blue-700';
};
</script>

<style scoped>
/* Enhanced Prose Styling */
.prose {
  @apply text-gray-800;
}

.prose h1 {
  @apply text-3xl font-bold text-gray-900 mt-10 mb-6 pb-3 border-b-2 border-blue-200;
}

.prose h2 {
  @apply text-2xl font-bold text-gray-900 mt-8 mb-4;
}

.prose h3 {
  @apply text-xl font-semibold text-gray-900 mt-6 mb-3;
}

.prose h4 {
  @apply text-lg font-semibold text-gray-800 mt-4 mb-2;
}

.prose p {
  @apply mb-5 leading-relaxed text-base;
}

.prose ul, .prose ol {
  @apply mb-6 ml-6 space-y-2;
}

.prose li {
  @apply leading-relaxed;
}

.prose ul > li {
  @apply relative pl-2;
}

.prose ul > li::before {
  content: "•";
  @apply absolute left-0 text-blue-600 font-bold;
  left: -1.25rem;
}

.prose ol {
  @apply list-decimal;
}

.prose strong {
  @apply font-bold text-gray-900;
}

.prose em {
  @apply italic text-gray-700;
}

.prose a {
  @apply text-blue-600 hover:text-blue-700 underline decoration-2 underline-offset-2 transition-colors;
}

.prose code {
  @apply bg-gray-100 px-2 py-1 rounded text-sm font-mono text-pink-600 border border-gray-200;
}

.prose pre {
  @apply bg-gray-900 text-gray-100 p-6 rounded-xl overflow-x-auto mb-6 shadow-lg;
}

.prose pre code {
  @apply bg-transparent text-gray-100 p-0 border-0;
}

.prose blockquote {
  @apply border-l-4 border-blue-500 bg-blue-50 pl-6 pr-4 py-4 italic text-gray-700 my-6 rounded-r-lg;
}

.prose hr {
  @apply my-8 border-t-2 border-gray-200;
}

.prose table {
  @apply w-full my-6 border-collapse;
}

.prose th {
  @apply bg-gray-100 font-semibold text-left p-3 border border-gray-300;
}

.prose td {
  @apply p-3 border border-gray-300;
}

.prose img {
  @apply rounded-lg shadow-md my-6;
}

/* Smooth scroll behavior */
html {
  scroll-behavior: smooth;
}
</style>
