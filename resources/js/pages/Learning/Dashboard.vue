<template>
  <MemberLayout>
    <div class="h-screen flex overflow-hidden bg-gray-50">
      <!-- Sidebar - Module List -->
      <div class="hidden lg:flex lg:flex-shrink-0">
        <div class="flex flex-col w-80 border-r border-gray-200 bg-white">
          <!-- Sidebar Header -->
          <div class="flex-shrink-0 px-6 py-6 border-b border-gray-200 bg-gradient-to-r from-blue-600 to-indigo-600">
            <div class="flex items-center gap-3 mb-4">
              <div class="p-2 bg-white/20 rounded-lg backdrop-blur-sm">
                <GraduationCap class="w-6 h-6 text-white" aria-hidden="true" />
              </div>
              <div>
                <h2 class="text-xl font-bold text-white">Learning Center</h2>
                <p class="text-xs text-blue-100">{{ progress.completed_modules }}/{{ progress.total_modules }} completed</p>
              </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="w-full bg-white/20 rounded-full h-2">
              <div 
                class="bg-white h-2 rounded-full transition-all duration-500"
                :style="{ width: `${progress.completion_percentage}%` }"
              ></div>
            </div>
          </div>

          <!-- Category Filter -->
          <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
            <select
              v-model="selectedCategory"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option :value="null">All Categories</option>
              <option v-for="category in categories" :key="category" :value="category">
                {{ category }}
              </option>
            </select>
          </div>

          <!-- Module List -->
          <div class="flex-1 overflow-y-auto">
            <div v-for="category in visibleCategories" :key="category" class="py-2">
              <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                {{ category }}
              </div>
              
              <button
                v-for="module in getModulesByCategory(category)"
                :key="module.id"
                @click="selectModule(module)"
                :class="[
                  'w-full text-left px-4 py-3 hover:bg-blue-50 transition-colors border-l-4',
                  selectedModule?.id === module.id 
                    ? 'bg-blue-50 border-blue-600' 
                    : 'border-transparent hover:border-blue-300'
                ]"
              >
                <div class="flex items-start gap-3">
                  <div :class="[
                    'flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center',
                    isCompleted(module.id) ? 'bg-green-100' : 'bg-gray-100'
                  ]">
                    <CheckCircleIcon 
                      v-if="isCompleted(module.id)"
                      class="w-5 h-5 text-green-600" 
                      aria-hidden="true" 
                    />
                    <BookOpenIcon 
                      v-else
                      class="w-5 h-5 text-gray-400" 
                      aria-hidden="true" 
                    />
                  </div>
                  
                  <div class="flex-1 min-w-0">
                    <p :class="[
                      'text-sm font-medium truncate',
                      selectedModule?.id === module.id ? 'text-blue-600' : 'text-gray-900'
                    ]">
                      {{ module.title }}
                    </p>
                    <div class="flex items-center gap-2 mt-1">
                      <span class="text-xs text-gray-500">{{ module.estimated_minutes }} min</span>
                      <span v-if="module.is_required" class="text-xs text-amber-600 font-medium">Required</span>
                    </div>
                  </div>
                </div>
              </button>
            </div>
          </div>

          <!-- Stats Footer -->
          <div class="flex-shrink-0 px-4 py-4 border-t border-gray-200 bg-gray-50">
            <div class="grid grid-cols-2 gap-3 text-center">
              <div class="bg-white rounded-lg p-3 shadow-sm">
                <div class="text-2xl font-bold text-blue-600">{{ progress.completed_today }}</div>
                <div class="text-xs text-gray-600">Today</div>
              </div>
              <div class="bg-white rounded-lg p-3 shadow-sm">
                <div class="text-2xl font-bold text-green-600">{{ progress.completion_percentage }}%</div>
                <div class="text-xs text-gray-600">Complete</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content Area -->
      <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Bar -->
        <div class="flex-shrink-0 bg-white border-b border-gray-200 px-6 py-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
              <!-- Mobile Menu Button -->
              <button
                @click="sidebarOpen = !sidebarOpen"
                class="lg:hidden p-2 rounded-lg hover:bg-gray-100"
              >
                <Menu class="w-6 h-6" aria-hidden="true" />
              </button>
              
              <div v-if="selectedModule">
                <h1 class="text-2xl font-bold text-gray-900">{{ selectedModule.title }}</h1>
                <div class="flex items-center gap-3 mt-1">
                  <span :class="[
                    'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold',
                    getCategoryStyle(selectedModule.category)
                  ]">
                    {{ selectedModule.category }}
                  </span>
                  <span class="text-sm text-gray-500">{{ selectedModule.estimated_minutes }} min read</span>
                </div>
              </div>
            </div>
            
            <div v-if="selectedModule && !isCompleted(selectedModule.id)" class="flex items-center gap-3">
              <div class="text-sm text-gray-600">
                <ClockIcon class="w-4 h-4 inline mr-1" aria-hidden="true" />
                {{ Math.floor(timeSpent / 60) }}:{{ String(timeSpent % 60).padStart(2, '0') }}
              </div>
            </div>
          </div>
        </div>

        <!-- Content Area -->
        <div class="flex-1 overflow-y-auto">
          <!-- Welcome State -->
          <div v-if="!selectedModule" class="flex items-center justify-center h-full p-8">
            <div class="text-center max-w-md">
              <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-100 rounded-full mb-6">
                <GraduationCap class="w-10 h-10 text-blue-600" aria-hidden="true" />
              </div>
              <h2 class="text-3xl font-bold text-gray-900 mb-3">Welcome to Learning Center</h2>
              <p class="text-gray-600 mb-6 leading-relaxed">
                Select a module from the sidebar to start learning and earning LGR credits.
              </p>
              <div class="flex items-center justify-center gap-4 text-sm text-gray-500">
                <div class="flex items-center gap-2">
                  <BookOpenIcon class="w-5 h-5" aria-hidden="true" />
                  <span>{{ modules.length }} modules</span>
                </div>
                <div class="flex items-center gap-2">
                  <CheckCircleIcon class="w-5 h-5" aria-hidden="true" />
                  <span>{{ progress.completed_modules }} completed</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Module Content -->
          <div v-else class="max-w-4xl mx-auto px-6 py-8">
            <!-- Module Header -->
            <div class="mb-8">
              <div v-if="isCompleted(selectedModule.id)" class="mb-6">
                <div class="bg-green-50 border-2 border-green-200 rounded-xl p-4 flex items-center gap-3">
                  <CheckCircleIcon class="w-6 h-6 text-green-600" aria-hidden="true" />
                  <div>
                    <p class="font-semibold text-green-900">Module Completed!</p>
                    <p class="text-sm text-green-700">You've earned your LGR activity credit.</p>
                  </div>
                </div>
              </div>
              
              <p v-if="selectedModule.description" class="text-lg text-gray-600 leading-relaxed">
                {{ selectedModule.description }}
              </p>
            </div>

            <!-- Content -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8 mb-8">
              <article class="prose prose-lg max-w-none" v-html="renderedContent"></article>
            </div>

            <!-- Complete Button -->
            <div v-if="!isCompleted(selectedModule.id)" class="sticky bottom-0 bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl shadow-xl border-2 border-green-200 p-6">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-lg font-bold text-gray-900 mb-1">Ready to complete?</h3>
                  <p class="text-sm text-gray-600">Mark as complete to earn your LGR credit</p>
                </div>
                <button
                  @click="completeModule"
                  :disabled="processing"
                  class="px-8 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl hover:from-green-700 hover:to-emerald-700 transition-all font-bold shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-105 active:scale-95"
                >
                  <span v-if="processing" class="flex items-center gap-2">
                    <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Completing...
                  </span>
                  <span v-else>Complete Module</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 z-40 lg:hidden"
      @click="sidebarOpen = false"
    >
      <div class="absolute inset-0 bg-gray-600 opacity-75"></div>
    </div>
  </MemberLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';
import { 
  GraduationCap,
  BookOpenIcon,
  CheckCircleIcon,
  ClockIcon,
  Menu,
  Sparkles,
  Trophy
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

interface Completion {
  learning_module_id: number;
}

interface Progress {
  total_modules: number;
  completed_modules: number;
  completed_today: number;
  completion_percentage: number;
}

interface Props {
  modules: Module[];
  categories: string[];
  progress: Progress;
  completions: Completion[];
  initialModuleSlug?: string;
}

const props = defineProps<Props>();

const selectedModule = ref<Module | null>(null);
const selectedCategory = ref<string | null>(null);
const sidebarOpen = ref(false);
const processing = ref(false);
const startTime = ref(Date.now());
const timeSpent = ref(0);
let intervalId: number | null = null;

const visibleCategories = computed(() => {
  if (selectedCategory.value) {
    return [selectedCategory.value];
  }
  return props.categories;
});

const renderedContent = computed(() => {
  if (!selectedModule.value) return '';
  return marked(selectedModule.value.content);
});

const isCompleted = (moduleId: number): boolean => {
  return props.completions.some(c => c.learning_module_id === moduleId);
};

const getModulesByCategory = (category: string) => {
  return props.modules.filter(m => m.category === category);
};

const selectModule = (module: Module) => {
  selectedModule.value = module;
  startTime.value = Date.now();
  timeSpent.value = 0;
  
  // Update URL without page reload
  window.history.pushState({}, '', `/learning/${module.slug}`);
};

const completeModule = () => {
  if (!selectedModule.value) return;
  
  processing.value = true;
  const timeSpentSeconds = Math.floor((Date.now() - startTime.value) / 1000);
  
  router.post(route('learning.complete', selectedModule.value.id), {
    time_spent_seconds: timeSpentSeconds,
  }, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      processing.value = false;
      // Reload to update completions
      router.reload({ only: ['completions', 'progress'] });
    },
    onError: () => {
      processing.value = false;
    },
  });
};

const getCategoryStyle = (category: string | null) => {
  if (category === 'Life Skills') return 'bg-emerald-100 text-emerald-700';
  if (category === 'GrowNet') return 'bg-purple-100 text-purple-700';
  return 'bg-blue-100 text-blue-700';
};

// Timer for time spent
watch(selectedModule, (newModule) => {
  if (intervalId) {
    clearInterval(intervalId);
  }
  
  if (newModule && !isCompleted(newModule.id)) {
    intervalId = window.setInterval(() => {
      timeSpent.value = Math.floor((Date.now() - startTime.value) / 1000);
    }, 1000);
  }
});

onMounted(() => {
  // Load initial module if provided
  if (props.initialModuleSlug) {
    const module = props.modules.find(m => m.slug === props.initialModuleSlug);
    if (module) {
      selectedModule.value = module;
    }
  }
});

onUnmounted(() => {
  if (intervalId) {
    clearInterval(intervalId);
  }
});
</script>

<style scoped>
/* Enhanced Prose Styling */
.prose {
  @apply text-gray-800;
}

.prose h1 {
  @apply text-3xl font-bold text-gray-900 mt-8 mb-4 pb-3 border-b-2 border-blue-200;
}

.prose h2 {
  @apply text-2xl font-bold text-gray-900 mt-6 mb-3;
}

.prose h3 {
  @apply text-xl font-semibold text-gray-900 mt-5 mb-2;
}

.prose p {
  @apply mb-4 leading-relaxed;
}

.prose ul, .prose ol {
  @apply mb-4 ml-6 space-y-2;
}

.prose li {
  @apply leading-relaxed;
}

.prose strong {
  @apply font-bold text-gray-900;
}

.prose a {
  @apply text-blue-600 hover:text-blue-700 underline;
}

.prose code {
  @apply bg-gray-100 px-2 py-1 rounded text-sm font-mono text-pink-600;
}

.prose pre {
  @apply bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto mb-4;
}

.prose blockquote {
  @apply border-l-4 border-blue-500 bg-blue-50 pl-4 pr-4 py-3 italic text-gray-700 my-4 rounded-r-lg;
}
</style>
