<template>
  <MemberLayout>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-indigo-50">
      <!-- Hero Section with Gradient -->
      <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
          <div class="flex items-center gap-4 mb-6">
            <div class="p-4 bg-white/20 rounded-2xl backdrop-blur-sm">
              <BookOpenIcon class="w-10 h-10" aria-hidden="true" />
            </div>
            <div>
              <h1 class="text-4xl font-bold tracking-tight">Learning Center</h1>
              <p class="text-blue-100 mt-2 text-lg">Expand your knowledge, earn LGR credits, transform your future</p>
            </div>
          </div>
          
          <!-- Stats Cards -->
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-8">
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-5 border border-white/20 hover:bg-white/15 transition-all">
              <div class="flex items-center justify-between mb-3">
                <div class="p-2 bg-white/20 rounded-lg">
                  <CheckCircleIcon class="w-6 h-6" aria-hidden="true" />
                </div>
                <span class="text-3xl font-bold">{{ progress.completed_modules }}</span>
              </div>
              <p class="text-sm text-blue-100">Modules Completed</p>
            </div>
            
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-5 border border-white/20 hover:bg-white/15 transition-all">
              <div class="flex items-center justify-between mb-3">
                <div class="p-2 bg-white/20 rounded-lg">
                  <BookOpenIcon class="w-6 h-6" aria-hidden="true" />
                </div>
                <span class="text-3xl font-bold">{{ progress.total_modules }}</span>
              </div>
              <p class="text-sm text-blue-100">Total Modules</p>
            </div>
            
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-5 border border-white/20 hover:bg-white/15 transition-all">
              <div class="flex items-center justify-between mb-3">
                <div class="p-2 bg-white/20 rounded-lg">
                  <StarIcon class="w-6 h-6" aria-hidden="true" />
                </div>
                <span class="text-3xl font-bold">{{ progress.completed_today }}</span>
              </div>
              <p class="text-sm text-blue-100">Completed Today</p>
            </div>
            
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-5 border border-white/20 hover:bg-white/15 transition-all">
              <div class="mb-3">
                <div class="flex items-center justify-between mb-2">
                  <span class="text-sm text-blue-100">Progress</span>
                  <span class="text-2xl font-bold">{{ progress.completion_percentage }}%</span>
                </div>
                <div class="w-full bg-white/20 rounded-full h-2.5">
                  <div 
                    class="bg-white h-2.5 rounded-full transition-all duration-500 shadow-lg"
                    :style="{ width: `${progress.completion_percentage}%` }"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Category Filter with Pills -->
        <div v-if="categories.length > 0" class="mb-10">
          <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Filter by Category</h2>
          <div class="flex flex-wrap gap-3">
            <button
              @click="filterCategory(null)"
              :class="[
                'group px-6 py-3 rounded-xl font-medium transition-all duration-200 shadow-sm',
                !selectedCategory 
                  ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg scale-105' 
                  : 'bg-white text-gray-700 hover:bg-gray-50 border-2 border-gray-200 hover:border-blue-300'
              ]"
            >
              <span class="flex items-center gap-2">
                All Modules
                <span v-if="!selectedCategory" class="px-2 py-0.5 bg-white/20 rounded-full text-xs">{{ modules.length }}</span>
              </span>
            </button>
            <button
              v-for="category in categories"
              :key="category"
              @click="filterCategory(category)"
              :class="[
                'group px-6 py-3 rounded-xl font-medium transition-all duration-200 shadow-sm',
                selectedCategory === category 
                  ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg scale-105' 
                  : 'bg-white text-gray-700 hover:bg-gray-50 border-2 border-gray-200 hover:border-blue-300'
              ]"
            >
              <span class="flex items-center gap-2">
                {{ category }}
                <span v-if="selectedCategory === category" class="px-2 py-0.5 bg-white/20 rounded-full text-xs">
                  {{ modules.filter(m => m.category === category).length }}
                </span>
              </span>
            </button>
          </div>
        </div>

        <!-- Modules Grid with Enhanced Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <Link
            v-for="module in modules"
            :key="module.id"
            :href="route('learning.show', module.slug)"
            class="group bg-white rounded-2xl shadow-md hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-100 hover:border-blue-200 hover:-translate-y-2"
          >
            <!-- Gradient Top Bar -->
            <div :class="[
              'h-2',
              getCategoryGradient(module.category)
            ]"></div>
            
            <div class="p-6">
              <!-- Header with Icon and Badge -->
              <div class="flex items-start justify-between mb-4">
                <div :class="[
                  'p-3 rounded-xl',
                  getCategoryBgColor(module.category)
                ]">
                  <component :is="getCategoryIcon(module.category)" class="w-7 h-7 text-white" aria-hidden="true" />
                </div>
                
                <div class="flex flex-col items-end gap-2">
                  <!-- Completion Badge -->
                  <div v-if="isCompleted(module.id)" class="flex items-center gap-1.5 px-3 py-1.5 bg-green-100 text-green-700 rounded-full">
                    <CheckCircleIcon class="w-4 h-4" aria-hidden="true" />
                    <span class="text-xs font-bold">Completed</span>
                  </div>
                  
                  <!-- Required Badge -->
                  <div v-if="module.is_required && !isCompleted(module.id)" class="flex items-center gap-1.5 px-3 py-1.5 bg-amber-100 text-amber-700 rounded-full">
                    <StarIcon class="w-4 h-4" aria-hidden="true" />
                    <span class="text-xs font-bold">Required</span>
                  </div>
                </div>
              </div>

              <!-- Category Tag -->
              <div v-if="module.category" class="mb-3">
                <span :class="[
                  'inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold',
                  getCategoryTextColor(module.category)
                ]">
                  {{ module.category }}
                </span>
              </div>

              <!-- Title -->
              <h3 class="text-xl font-bold text-gray-900 mb-3 group-hover:text-blue-600 transition-colors leading-tight">
                {{ module.title }}
              </h3>

              <!-- Description -->
              <p class="text-sm text-gray-600 mb-5 line-clamp-2 leading-relaxed">
                {{ module.description }}
              </p>

              <!-- Meta Info -->
              <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                <div class="flex items-center gap-2 text-sm text-gray-500">
                  <ClockIcon class="w-4 h-4" aria-hidden="true" />
                  <span class="font-medium">{{ module.estimated_minutes }} min</span>
                </div>
                
                <div class="flex items-center gap-2 text-sm font-semibold text-blue-600 group-hover:gap-3 transition-all">
                  <span>{{ isCompleted(module.id) ? 'Review' : 'Start' }}</span>
                  <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </div>
              </div>
            </div>
          </Link>
        </div>

        <!-- Empty State with Illustration -->
        <div v-if="modules.length === 0" class="text-center py-20">
          <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-6">
            <BookOpenIcon class="w-10 h-10 text-gray-400" aria-hidden="true" />
          </div>
          <h3 class="text-2xl font-bold text-gray-900 mb-2">No modules found</h3>
          <p class="text-gray-500 mb-6 max-w-md mx-auto">
            {{ selectedCategory ? 'Try selecting a different category to explore more learning content' : 'Check back soon for new educational content' }}
          </p>
          <button
            v-if="selectedCategory"
            @click="filterCategory(null)"
            class="px-6 py-3 bg-blue-600 text-white rounded-xl font-medium hover:bg-blue-700 transition-colors shadow-lg"
          >
            View All Modules
          </button>
        </div>
      </div>
    </div>
  </MemberLayout>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';
import { 
  ClockIcon, 
  StarIcon, 
  CheckCircleIcon, 
  BookOpenIcon,
  Sparkles,
  Trophy,
  GraduationCap
} from 'lucide-vue-next';

interface Module {
  id: number;
  title: string;
  slug: string;
  description: string;
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
  selectedCategory: string | null;
}

const props = defineProps<Props>();

const isCompleted = (moduleId: number): boolean => {
  return props.completions.some(c => c.learning_module_id === moduleId);
};

const filterCategory = (category: string | null) => {
  router.get(route('learning.index'), { category }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const getCategoryIcon = (category: string | null) => {
  if (category === 'Life Skills') return Sparkles;
  if (category === 'GrowNet') return Trophy;
  return GraduationCap;
};

const getCategoryGradient = (category: string | null) => {
  if (category === 'Life Skills') return 'bg-gradient-to-r from-emerald-500 to-teal-600';
  if (category === 'GrowNet') return 'bg-gradient-to-r from-purple-500 to-indigo-600';
  return 'bg-gradient-to-r from-blue-500 to-blue-600';
};

const getCategoryBgColor = (category: string | null) => {
  if (category === 'Life Skills') return 'bg-gradient-to-br from-emerald-500 to-teal-600';
  if (category === 'GrowNet') return 'bg-gradient-to-br from-purple-500 to-indigo-600';
  return 'bg-gradient-to-br from-blue-500 to-blue-600';
};

const getCategoryTextColor = (category: string | null) => {
  if (category === 'Life Skills') return 'bg-emerald-100 text-emerald-700';
  if (category === 'GrowNet') return 'bg-purple-100 text-purple-700';
  return 'bg-blue-100 text-blue-700';
};
</script>
