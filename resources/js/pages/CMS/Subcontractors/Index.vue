<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { MagnifyingGlassIcon, PlusIcon, StarIcon } from '@heroicons/vue/24/outline';
import { StarIcon as StarIconSolid } from '@heroicons/vue/24/solid';

interface Subcontractor {
  id: number;
  name: string;
  company_name: string;
  trade: string;
  phone: string;
  email: string;
  average_rating: number;
  total_assignments: number;
  active_assignments: number;
}

const props = defineProps<{
  subcontractors: {
    data: Subcontractor[];
    current_page: number;
    last_page: number;
  };
  filters: {
    search?: string;
    trade?: string;
  };
}>();

const search = ref(props.filters.search || '');
const tradeFilter = ref(props.filters.trade || '');

const applyFilters = () => {
  router.get(route('cms.subcontractors.index'), {
    search: search.value,
    trade: tradeFilter.value,
  }, { preserveState: true });
};
</script>

<template>
  <Head title="Subcontractors" />
  
  <CMSLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Subcontractors</h1>
          <p class="mt-1 text-sm text-gray-500">Manage subcontractors and track performance</p>
        </div>
        <Link
          :href="route('cms.subcontractors.create')"
          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          <PlusIcon class="h-5 w-5" aria-hidden="true" />
          Add Subcontractor
        </Link>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <div class="relative">
              <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
              <input
                v-model="search"
                type="text"
                placeholder="Search subcontractors..."
                class="pl-10 w-full rounded-lg border-gray-300"
                @keyup.enter="applyFilters"
              />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Trade</label>
            <input
              v-model="tradeFilter"
              type="text"
              placeholder="e.g., Plumbing"
              class="w-full rounded-lg border-gray-300"
              @keyup.enter="applyFilters"
            />
          </div>
        </div>
      </div>

      <!-- Subcontractors Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <Link
          v-for="subcontractor in subcontractors.data"
          :key="subcontractor.id"
          :href="route('cms.subcontractors.show', subcontractor.id)"
          class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow"
        >
          <div class="flex items-start justify-between mb-4">
            <div class="flex-1">
              <h3 class="font-semibold text-gray-900">{{ subcontractor.name }}</h3>
              <p class="text-sm text-gray-500">{{ subcontractor.company_name }}</p>
            </div>
            <div class="flex items-center gap-1">
              <StarIconSolid
                v-for="i in 5"
                :key="i"
                :class="[
                  'h-4 w-4',
                  i <= subcontractor.average_rating ? 'text-yellow-400' : 'text-gray-300'
                ]"
                aria-hidden="true"
              />
            </div>
          </div>

          <div class="space-y-2">
            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-500">Trade</span>
              <span class="font-medium text-gray-900">{{ subcontractor.trade }}</span>
            </div>
            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-500">Active Jobs</span>
              <span class="font-medium text-gray-900">{{ subcontractor.active_assignments }}</span>
            </div>
            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-500">Total Jobs</span>
              <span class="font-medium text-gray-900">{{ subcontractor.total_assignments }}</span>
            </div>
          </div>

          <div class="mt-4 pt-4 border-t border-gray-200">
            <div class="text-sm text-gray-500">{{ subcontractor.phone }}</div>
            <div class="text-sm text-gray-500">{{ subcontractor.email }}</div>
          </div>
        </Link>
      </div>

      <!-- Empty State -->
      <div v-if="subcontractors.data.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
        <p class="text-gray-500">No subcontractors found</p>
        <Link
          :href="route('cms.subcontractors.create')"
          class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          <PlusIcon class="h-5 w-5" aria-hidden="true" />
          Add First Subcontractor
        </Link>
      </div>
    </div>
  </CMSLayout>
</template>
