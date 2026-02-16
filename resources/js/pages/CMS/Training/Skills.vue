<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { SparklesIcon, PlusIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

const props = defineProps<{
  skills: any;
  filters: any;
}>();

const search = ref(props.filters.search || '');
const categoryFilter = ref(props.filters.category || '');

const applyFilters = () => {
  router.get(route('cms.training.skills'), {
    search: search.value,
    category: categoryFilter.value,
  }, { preserveState: true });
};

const getCategoryColor = (category: string) => {
  const colors: Record<string, string> = {
    technical: 'bg-blue-100 text-blue-800',
    soft_skills: 'bg-green-100 text-green-800',
    leadership: 'bg-purple-100 text-purple-800',
    language: 'bg-orange-100 text-orange-800',
    certification: 'bg-indigo-100 text-indigo-800',
    other: 'bg-gray-100 text-gray-800',
  };
  return colors[category] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
  <Head title="Skills Catalog" />
  
  <CMSLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900">Skills Catalog</h1>
          <p class="mt-1 text-sm text-gray-500">Manage skills and competencies</p>
        </div>
        <button class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
          <PlusIcon class="h-5 w-5" aria-hidden="true" />
          Add Skill
        </button>
      </div>

      <!-- Filters -->
      <div class="bg-white p-4 rounded-lg border border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="relative">
            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
            <input
              v-model="search"
              @keyup.enter="applyFilters"
              type="text"
              placeholder="Search skills..."
              class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg"
            />
          </div>
          <select v-model="categoryFilter" @change="applyFilters" class="px-4 py-2 border border-gray-300 rounded-lg">
            <option value="">All Categories</option>
            <option value="technical">Technical</option>
            <option value="soft_skills">Soft Skills</option>
            <option value="leadership">Leadership</option>
            <option value="language">Language</option>
            <option value="certification">Certification</option>
            <option value="other">Other</option>
          </select>
          <button @click="search = ''; categoryFilter = ''; applyFilters()" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
            Clear Filters
          </button>
        </div>
      </div>

      <!-- Skills Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="skill in skills.data"
          :key="skill.id"
          class="bg-white p-6 rounded-lg border border-gray-200 hover:shadow-md transition-shadow"
        >
          <div class="flex items-start justify-between">
            <div class="flex items-center gap-3">
              <SparklesIcon class="h-8 w-8 text-blue-600" aria-hidden="true" />
              <div>
                <h3 class="font-medium text-gray-900">{{ skill.name }}</h3>
                <p class="text-sm text-gray-500 mt-1">{{ skill.description }}</p>
              </div>
            </div>
            <span v-if="skill.is_core" class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
              Core
            </span>
          </div>
          <div class="mt-4 flex items-center gap-2">
            <span :class="getCategoryColor(skill.category)" class="px-2 py-1 text-xs font-medium rounded-full">
              {{ skill.category }}
            </span>
            <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">
              {{ skill.level_required }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </CMSLayout>
</template>
