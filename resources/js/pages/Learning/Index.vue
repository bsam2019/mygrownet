<template>
  <MemberLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-gray-900">Learning Modules</h1>
          <p class="mt-1 text-sm text-gray-600">
            Complete modules to earn LGR activity credits and advance your skills
          </p>
        </div>

        <!-- Progress Card -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 mb-6 text-white">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-lg font-semibold">Your Progress</h2>
              <p class="text-sm text-blue-100 mt-1">Keep learning to earn daily LGR credits</p>
            </div>
            <div class="text-right">
              <p class="text-3xl font-bold">{{ progress.completed_modules }}/{{ progress.total_modules }}</p>
              <p class="text-sm text-blue-100">Modules Completed</p>
            </div>
          </div>
          <div class="mt-4">
            <div class="bg-blue-400/30 rounded-full h-2">
              <div 
                class="bg-white rounded-full h-2 transition-all duration-300"
                :style="{ width: `${progress.completion_percentage}%` }"
              ></div>
            </div>
            <p class="text-xs text-blue-100 mt-2">{{ progress.completed_today }} completed today</p>
          </div>
        </div>

        <!-- Category Filter -->
        <div v-if="categories.length > 0" class="mb-6">
          <div class="flex flex-wrap gap-2">
            <button
              @click="filterCategory(null)"
              :class="[
                'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                !selectedCategory 
                  ? 'bg-blue-600 text-white' 
                  : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
              ]"
            >
              All Modules
            </button>
            <button
              v-for="category in categories"
              :key="category"
              @click="filterCategory(category)"
              :class="[
                'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                selectedCategory === category 
                  ? 'bg-blue-600 text-white' 
                  : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
              ]"
            >
              {{ category }}
            </button>
          </div>
        </div>

        <!-- Modules Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="module in modules"
            :key="module.id"
            class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow overflow-hidden"
          >
            <div class="p-6">
              <!-- Category Badge -->
              <div v-if="module.category" class="mb-3">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                  {{ module.category }}
                </span>
              </div>

              <!-- Title -->
              <h3 class="text-lg font-semibold text-gray-900 mb-2">
                {{ module.title }}
              </h3>

              <!-- Description -->
              <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                {{ module.description }}
              </p>

              <!-- Meta Info -->
              <div class="flex items-center gap-4 text-xs text-gray-500 mb-4">
                <div class="flex items-center gap-1">
                  <ClockIcon class="h-4 w-4" aria-hidden="true" />
                  <span>{{ module.estimated_minutes }} min</span>
                </div>
                <div v-if="module.is_required" class="flex items-center gap-1">
                  <StarIcon class="h-4 w-4 text-yellow-500" aria-hidden="true" />
                  <span class="text-yellow-600 font-medium">Required</span>
                </div>
              </div>

              <!-- Completion Status -->
              <div v-if="isCompleted(module.id)" class="mb-4">
                <div class="flex items-center gap-2 text-green-600 text-sm font-medium">
                  <CheckCircleIcon class="h-5 w-5" aria-hidden="true" />
                  <span>Completed</span>
                </div>
              </div>

              <!-- Action Button -->
              <Link
                :href="route('learning.show', module.slug)"
                class="block w-full text-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm"
              >
                {{ isCompleted(module.id) ? 'Review Module' : 'Start Learning' }}
              </Link>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="modules.length === 0" class="text-center py-12">
          <BookOpenIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">No modules found</h3>
          <p class="mt-1 text-sm text-gray-500">
            {{ selectedCategory ? 'Try selecting a different category' : 'Check back soon for new content' }}
          </p>
        </div>
      </div>
    </div>
  </MemberLayout>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';
import { ClockIcon, StarIcon, CheckCircleIcon, BookOpenIcon } from 'lucide-vue-next';

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
</script>
