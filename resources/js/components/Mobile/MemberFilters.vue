<template>
  <div class="bg-white rounded-xl shadow-sm p-4 space-y-3">
    <!-- Filter Tabs -->
    <div class="flex items-center gap-2">
      <button
        v-for="filter in filters"
        :key="filter.value"
        @click="$emit('update:filter', filter.value)"
        class="flex-1 px-3 py-3 rounded-lg text-sm font-medium transition-all min-h-[44px]"
        :class="modelValue === filter.value
          ? 'bg-blue-600 text-white shadow-sm'
          : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
      >
        {{ filter.label }}
        <span v-if="filter.count !== undefined" class="ml-1 opacity-75">
          ({{ filter.count }})
        </span>
      </button>
    </div>

    <!-- Sort and Search Row -->
    <div class="flex items-center gap-2">
      <!-- Sort Dropdown -->
      <div class="relative flex-1">
        <select
          :value="sort"
          @change="$emit('update:sort', ($event.target as HTMLSelectElement).value)"
          class="w-full px-3 py-2 pr-8 text-sm border border-gray-200 rounded-lg bg-white appearance-none focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="recent">Recent First</option>
          <option value="name">Name (A-Z)</option>
          <option value="earnings">Highest Earnings</option>
          <option value="oldest">Oldest First</option>
        </select>
        <ChevronDownIcon class="absolute right-2 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" aria-hidden="true" />
      </div>

      <!-- Search Toggle -->
      <button
        @click="showSearch = !showSearch"
        class="p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors min-w-[44px] min-h-[44px] flex items-center justify-center"
        :class="{ 'bg-blue-50 border-blue-200': showSearch }"
        aria-label="Toggle search"
      >
        <MagnifyingGlassIcon class="h-5 w-5" :class="showSearch ? 'text-blue-600' : 'text-gray-600'" aria-hidden="true" />
      </button>
    </div>

    <!-- Search Input (Expandable) -->
    <div v-if="showSearch" class="animate-slide-down">
      <div class="relative">
        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" aria-hidden="true" />
        <input
          type="text"
          :value="search"
          @input="$emit('update:search', ($event.target as HTMLInputElement).value)"
          placeholder="Search by name or email..."
          class="w-full pl-9 pr-9 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        <button
          v-if="search"
          @click="$emit('update:search', '')"
          aria-label="Clear search"
          class="absolute right-2 top-1/2 -translate-y-1/2 p-1 hover:bg-gray-100 rounded"
        >
          <XMarkIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
        </button>
      </div>
    </div>

    <!-- Active Filters Summary -->
    <div v-if="hasActiveFilters" class="flex items-center justify-between text-xs text-gray-500 pt-1">
      <span>{{ filteredCount }} member{{ filteredCount !== 1 ? 's' : '' }} found</span>
      <button
        @click="clearFilters"
        class="text-blue-600 hover:text-blue-700 font-medium"
      >
        Clear filters
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { ChevronDownIcon, MagnifyingGlassIcon, XMarkIcon } from '@heroicons/vue/24/outline';

interface Filter {
  label: string;
  value: string;
  count?: number;
}

interface Props {
  modelValue: string;
  sort: string;
  search: string;
  filters: Filter[];
  filteredCount?: number;
  totalCount?: number;
}

interface Emits {
  (e: 'update:filter', value: string): void;
  (e: 'update:sort', value: string): void;
  (e: 'update:search', value: string): void;
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: 'all',
  sort: 'recent',
  search: '',
  filteredCount: 0,
  totalCount: 0
});

const emit = defineEmits<Emits>();

const showSearch = ref(false);

const hasActiveFilters = computed(() => {
  return props.modelValue !== 'all' || props.search !== '' || props.sort !== 'recent';
});

const clearFilters = () => {
  emit('update:filter', 'all');
  emit('update:sort', 'recent');
  emit('update:search', '');
  showSearch.value = false;
};
</script>

<style scoped>
@keyframes slide-down {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-slide-down {
  animation: slide-down 0.2s ease-out;
}
</style>
