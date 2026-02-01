<template>
  <div class="h-screen flex overflow-hidden bg-gray-50">
    <!-- Sidebar - Module List -->
    <div :class="[
      'fixed inset-y-0 left-0 z-30 w-80 bg-white border-r border-gray-200 transform transition-transform duration-300 ease-in-out',
      sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
    ]">
      <!-- Sidebar Header -->
      <div class="flex-shrink-0 px-6 py-6 border-b border-gray-200 bg-gradient-to-r from-blue-600 to-indigo-600">
        <Link href="/dashboard" class="flex items-center gap-3 mb-4 text-white hover:text-blue-100 transition-colors">
          <ArrowLeft class="w-5 h-5" aria-hidden="true" />
          <span class="text-sm font-medium">Back to Dashboard</span>
        </Link>
        
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
      <div class="flex-1 overflow-y-auto" style="height: calc(100vh - 280px);">
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
                <CheckCircle 
                  v-if="isCompleted(module.id)"
                  class="w-5 h-5 text-green-600" 
                  aria-hidden="true" 
                />
                <BookOpen 
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

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col overflow-hidden lg:ml-80">
      <!-- Top Bar - Simplified -->
      <div class="flex-shrink-0 bg-white border-b border-gray-200 px-6 py-4 shadow-sm">
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
              <h1 class="text-xl font-bold text-gray-900">{{ selectedModule.title }}</h1>
              <div class="flex items-center gap-3 mt-1">
                <span :class="[
                  'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold',
                  getCategoryStyle(selectedModule.category)
                ]">
                  {{ selectedModule.category }}
                </span>
                <span class="text-xs text-gray-500">{{ selectedModule.estimated_minutes }} min</span>
              </div>
            </div>
          </div>
          
          <div v-if="selectedModule && !isCompleted(selectedModule.id)" class="flex items-center gap-3">
            <div class="text-sm text-gray-600 flex items-center gap-2">
              <Clock class="w-4 h-4" aria-hidden="true" />
              {{ Math.floor(timeSpent / 60) }}:{{ String(timeSpent % 60).padStart(2, '0') }}
            </div>
          </div>
        </div>
      </div>

      <!-- Content Area -->
      <div class="flex-1 overflow-y-auto bg-gradient-to-br from-gray-50 to-blue-50">
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
                <BookOpen class="w-5 h-5" aria-hidden="true" />
                <span>{{ modules.length }} modules</span>
              </div>
              <div class="flex items-center gap-2">
                <CheckCircle class="w-5 h-5" aria-hidden="true" />
                <span>{{ progress.completed_modules }} completed</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Module Content with Pagination -->
        <div v-else class="max-w-4xl mx-auto px-6 py-8">
          <!-- Completion Badge -->
          <div v-if="isCompleted(selectedModule.id)" class="mb-6">
            <div class="bg-green-50 border-2 border-green-200 rounded-xl p-4 flex items-center gap-3">
              <CheckCircle class="w-6 h-6 text-green-600" aria-hidden="true" />
              <div>
                <p class="font-semibold text-green-900">Module Completed!</p>
                <p class="text-sm text-green-700">You've earned your LGR activity credit.</p>
              </div>
            </div>
          </div>

          <!-- Page Progress Indicator -->
          <div class="mb-6">
            <div class="flex items-center justify-between mb-2">
              <span class="text-sm font-medium text-gray-700">
                Page {{ currentPage + 1 }} of {{ totalPages }}
              </span>
              <span class="text-sm text-gray-500">
                {{ Math.round(((currentPage + 1) / totalPages) * 100) }}% complete
              </span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div 
                class="bg-gradient-to-r from-blue-500 to-indigo-600 h-2 rounded-full transition-all duration-300"
                :style="{ width: `${((currentPage + 1) / totalPages) * 100}%` }"
              ></div>
            </div>
          </div>

          <!-- Content Card -->
          <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden mb-6">
            <div class="p-8 sm:p-12">
              <!-- Page Content -->
              <div class="prose prose-lg max-w-none" v-html="renderedContent"></div>
            </div>
          </div>

          <!-- Navigation Buttons -->
          <div class="flex items-center justify-between gap-4 mb-8">
            <button
              v-if="currentPage > 0"
              @click="previousPage"
              class="flex items-center gap-2 px-6 py-3 bg-white text-gray-700 rounded-xl hover:bg-gray-50 transition-all font-medium shadow-md border border-gray-200"
            >
              <ChevronLeft class="w-5 h-5" aria-hidden="true" />
              Previous
            </button>
            <div v-else></div>
            
            <button
              v-if="currentPage < totalPages - 1"
              @click="nextPage"
              class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all font-medium shadow-lg"
            >
              Next
              <ChevronRight class="w-5 h-5" aria-hidden="true" />
            </button>
            <div v-else></div>
          </div>

          <!-- Complete Button (shown on last page) -->
          <div v-if="currentPage === totalPages - 1 && !isCompleted(selectedModule.id)" class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl shadow-xl border-2 border-green-200 p-6">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
              <div class="flex items-start gap-4">
                <div class="p-3 bg-green-500 rounded-xl">
                  <CheckCircle class="w-8 h-8 text-white" aria-hidden="true" />
                </div>
                <div>
                  <h3 class="text-xl font-bold text-gray-900 mb-1">Ready to Complete?</h3>
                  <p class="text-gray-600">Mark as complete to earn your LGR credit</p>
                </div>
              </div>
              <button
                @click="completeModule"
                :disabled="processing"
                class="flex-shrink-0 px-8 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl hover:from-green-700 hover:to-emerald-700 transition-all font-bold shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-105 active:scale-95"
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

    <!-- Mobile Sidebar Overlay -->
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 z-20 bg-gray-600 bg-opacity-75 lg:hidden"
      @click="sidebarOpen = false"
    ></div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { 
  GraduationCap,
  BookOpen,
  CheckCircle,
  Clock,
  Menu,
  ChevronLeft,
  ChevronRight,
  ArrowLeft
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
const currentPage = ref(0);
let intervalId: number | null = null;

const visibleCategories = computed(() => {
  if (selectedCategory.value) {
    return [selectedCategory.value];
  }
  return props.categories;
});

// Split content into pages based on H2 headings
const contentPages = computed(() => {
  if (!selectedModule.value) return [];
  
  const content = selectedModule.value.content;
  const sections = content.split(/(?=^## )/gm).filter(s => s.trim());
  
  // If no H2 headings, split by paragraphs (every 3-4 paragraphs)
  if (sections.length <= 1) {
    const paragraphs = content.split('\n\n').filter(p => p.trim());
    const pages = [];
    for (let i = 0; i < paragraphs.length; i += 4) {
      pages.push(paragraphs.slice(i, i + 4).join('\n\n'));
    }
    return pages.length > 0 ? pages : [content];
  }
  
  return sections;
});

const totalPages = computed(() => contentPages.value.length);

const renderedContent = computed(() => {
  if (contentPages.value.length === 0) return '';
  
  // Configure marked for better rendering
  marked.setOptions({
    breaks: true,
    gfm: true,
    headerIds: true,
    mangle: false,
  });
  
  return marked(contentPages.value[currentPage.value] || '');
});

const isCompleted = (moduleId: number): boolean => {
  return props.completions.some(c => c.learning_module_id === moduleId);
};

const getModulesByCategory = (category: string) => {
  return props.modules.filter(m => m.category === category);
};

const selectModule = (module: Module) => {
  selectedModule.value = module;
  currentPage.value = 0;
  startTime.value = Date.now();
  timeSpent.value = 0;
  
  // Update URL without page reload
  window.history.pushState({}, '', `/learning/${module.slug}`);
};

const nextPage = () => {
  if (currentPage.value < totalPages.value - 1) {
    currentPage.value++;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

const previousPage = () => {
  if (currentPage.value > 0) {
    currentPage.value--;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
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

<style>
/* Enhanced Prose Styling with Better Hierarchy */
.prose {
  color: #374151;
  line-height: 1.75;
  font-size: 1.0625rem;
}

.prose :where(h1):not(:where([class~="not-prose"] *)) {
  color: #111827;
  font-weight: 800;
  font-size: 2.25rem;
  margin-top: 0;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 4px solid #3b82f6;
  line-height: 1.2;
}

.prose :where(h2):not(:where([class~="not-prose"] *)) {
  color: #111827;
  font-weight: 700;
  font-size: 1.875rem;
  margin-top: 3rem;
  margin-bottom: 1.5rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #e5e7eb;
  line-height: 1.3;
}

.prose :where(h3):not(:where([class~="not-prose"] *)) {
  color: #1e3a8a;
  font-weight: 700;
  font-size: 1.5rem;
  margin-top: 2rem;
  margin-bottom: 1rem;
  line-height: 1.4;
}

.prose :where(h4):not(:where([class~="not-prose"] *)) {
  color: #1f2937;
  font-weight: 600;
  font-size: 1.25rem;
  margin-top: 1.5rem;
  margin-bottom: 0.75rem;
}

.prose :where(p):not(:where([class~="not-prose"] *)) {
  margin-top: 0;
  margin-bottom: 1.5rem;
  line-height: 1.8;
}

.prose :where(ul):not(:where([class~="not-prose"] *)),
.prose :where(ol):not(:where([class~="not-prose"] *)) {
  margin-top: 0;
  margin-bottom: 2rem;
  padding-left: 1.5rem;
}

.prose :where(li):not(:where([class~="not-prose"] *)) {
  margin-top: 0.75rem;
  margin-bottom: 0.75rem;
  line-height: 1.75;
}

.prose :where(ul > li):not(:where([class~="not-prose"] *)) {
  position: relative;
  padding-left: 0.5rem;
}

.prose :where(ul > li)::marker {
  color: #2563eb;
  font-size: 1.25rem;
}

.prose :where(ol > li)::marker {
  color: #2563eb;
  font-weight: 700;
}

.prose :where(strong):not(:where([class~="not-prose"] *)) {
  color: #111827;
  font-weight: 700;
  background-color: #fef3c7;
  padding: 0.125rem 0.375rem;
  border-radius: 0.25rem;
}

.prose :where(em):not(:where([class~="not-prose"] *)) {
  color: #374151;
  font-style: italic;
}

.prose :where(a):not(:where([class~="not-prose"] *)) {
  color: #2563eb;
  text-decoration: underline;
  text-decoration-thickness: 2px;
  text-underline-offset: 4px;
  font-weight: 500;
  transition: color 0.2s;
}

.prose :where(a):not(:where([class~="not-prose"] *)):hover {
  color: #1e40af;
}

.prose :where(code):not(:where([class~="not-prose"] *)) {
  color: #be185d;
  background-color: #fdf2f8;
  padding: 0.25rem 0.625rem;
  border-radius: 0.375rem;
  font-size: 0.9375rem;
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
  border: 1px solid #fbcfe8;
}

.prose :where(pre):not(:where([class~="not-prose"] *)) {
  background-color: #111827;
  color: #f3f4f6;
  padding: 1.5rem;
  border-radius: 0.75rem;
  overflow-x: auto;
  margin-top: 0;
  margin-bottom: 2rem;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.prose :where(pre code):not(:where([class~="not-prose"] *)) {
  background-color: transparent;
  color: #f3f4f6;
  padding: 0;
  border: none;
  font-size: 0.9375rem;
}

.prose :where(blockquote):not(:where([class~="not-prose"] *)) {
  border-left: 4px solid #3b82f6;
  background-color: #eff6ff;
  padding: 1.25rem 1.5rem;
  font-style: italic;
  color: #1f2937;
  margin-top: 0;
  margin-bottom: 2rem;
  border-radius: 0 0.75rem 0.75rem 0;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
  font-size: 1.125rem;
  line-height: 1.7;
}

.prose :where(hr):not(:where([class~="not-prose"] *)) {
  border-color: #d1d5db;
  border-top-width: 2px;
  margin-top: 3rem;
  margin-bottom: 3rem;
}

.prose :where(table):not(:where([class~="not-prose"] *)) {
  width: 100%;
  margin-top: 0;
  margin-bottom: 2rem;
  border-collapse: collapse;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  border-radius: 0.5rem;
  overflow: hidden;
}

.prose :where(th):not(:where([class~="not-prose"] *)) {
  background-color: #2563eb;
  color: white;
  font-weight: 700;
  text-align: left;
  padding: 1rem;
  border: 1px solid #1d4ed8;
}

.prose :where(td):not(:where([class~="not-prose"] *)) {
  padding: 1rem;
  border: 1px solid #d1d5db;
  background-color: white;
}

.prose :where(img):not(:where([class~="not-prose"] *)) {
  border-radius: 0.75rem;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  margin-top: 0;
  margin-bottom: 2rem;
  width: 100%;
}

/* Nested lists */
.prose :where(ul ul):not(:where([class~="not-prose"] *)),
.prose :where(ol ul):not(:where([class~="not-prose"] *)),
.prose :where(ul ol):not(:where([class~="not-prose"] *)),
.prose :where(ol ol):not(:where([class~="not-prose"] *)) {
  margin-top: 0.75rem;
  margin-bottom: 0.75rem;
}

/* Smooth scroll */
html {
  scroll-behavior: smooth;
}
</style>
