<template>
  <div class="bg-white rounded-lg shadow border">
    <!-- Search Header -->
    <div class="px-6 py-4 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-medium text-gray-900">Advanced Employee Search</h3>
        <button
          @click="resetFilters"
          class="text-sm text-gray-500 hover:text-gray-700"
        >
          Reset All
        </button>
      </div>
    </div>

    <!-- Search Form -->
    <div class="p-6">
      <form @submit.prevent="performSearch" class="space-y-6">
        <!-- Basic Search -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Search Term</label>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Name, email, employee number..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Employment Status</label>
            <select
              v-model="filters.status"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">All Statuses</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="terminated">Terminated</option>
              <option value="suspended">Suspended</option>
            </select>
          </div>
        </div>

        <!-- Department & Position -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
            <select
              v-model="filters.department"
              @change="onDepartmentChange"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">All Departments</option>
              <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                {{ dept.name }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Position</label>
            <select
              v-model="filters.position"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">All Positions</option>
              <option v-for="pos in filteredPositions" :key="pos.id" :value="pos.id">
                {{ pos.title }}
              </option>
            </select>
          </div>
        </div>

        <!-- Date Ranges -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Hire Date Range</label>
            <div class="grid grid-cols-2 gap-2">
              <input
                v-model="filters.hireDateFrom"
                type="date"
                placeholder="From"
                class="px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              />
              <input
                v-model="filters.hireDateTo"
                type="date"
                placeholder="To"
                class="px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Salary Range (K)</label>
            <div class="grid grid-cols-2 gap-2">
              <input
                v-model.number="filters.salaryMin"
                type="number"
                placeholder="Min"
                class="px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              />
              <input
                v-model.number="filters.salaryMax"
                type="number"
                placeholder="Max"
                class="px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
          </div>
        </div>

        <!-- Advanced Filters -->
        <div class="border-t pt-6">
          <button
            type="button"
            @click="showAdvanced = !showAdvanced"
            class="flex items-center text-sm font-medium text-blue-600 hover:text-blue-800"
          >
            <ChevronDownIcon 
              :class="{ 'rotate-180': showAdvanced }" 
              class="w-4 h-4 mr-1 transform transition-transform"
            />
            Advanced Filters
          </button>

          <div v-show="showAdvanced" class="mt-4 space-y-4">
            <!-- Performance & Commission Filters -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Performance Score</label>
                <select
                  v-model="filters.performanceScore"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                >
                  <option value="">Any Score</option>
                  <option value="excellent">Excellent (4.5+)</option>
                  <option value="good">Good (3.5-4.4)</option>
                  <option value="satisfactory">Satisfactory (2.5-3.4)</option>
                  <option value="needs-improvement">Needs Improvement (1.5-2.4)</option>
                  <option value="poor">Poor (< 1.5)</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Commission Eligible</label>
                <select
                  v-model="filters.commissionEligible"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                >
                  <option value="">All Employees</option>
                  <option value="yes">Commission Eligible</option>
                  <option value="no">Not Commission Eligible</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Years of Service</label>
                <select
                  v-model="filters.yearsOfService"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                >
                  <option value="">Any Duration</option>
                  <option value="0-1">0-1 years</option>
                  <option value="1-3">1-3 years</option>
                  <option value="3-5">3-5 years</option>
                  <option value="5-10">5-10 years</option>
                  <option value="10+">10+ years</option>
                </select>
              </div>
            </div>

            <!-- Skills & Qualifications -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Skills</label>
                <div class="flex flex-wrap gap-2">
                  <button
                    v-for="skill in availableSkills"
                    :key="skill"
                    type="button"
                    @click="toggleSkill(skill)"
                    :class="[
                      'px-3 py-1 text-xs font-medium rounded-full border transition-colors',
                      filters.skills.includes(skill)
                        ? 'bg-blue-100 text-blue-800 border-blue-200'
                        : 'bg-gray-100 text-gray-700 border-gray-200 hover:bg-gray-200'
                    ]"
                  >
                    {{ skill }}
                  </button>
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Manager</label>
                <select
                  v-model="filters.manager"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                >
                  <option value="">Any Manager</option>
                  <option v-for="manager in managers" :key="manager.id" :value="manager.id">
                    {{ manager.name }}
                  </option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Search Actions -->
        <div class="flex items-center justify-between pt-6 border-t">
          <div class="text-sm text-gray-500">
            {{ resultCount }} employees found
          </div>
          <div class="flex space-x-3">
            <button
              type="button"
              @click="saveSearch"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200"
            >
              Save Search
            </button>
            <button
              type="submit"
              :disabled="isSearching"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50"
            >
              <span v-if="isSearching">Searching...</span>
              <span v-else>Search</span>
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- Saved Searches -->
    <div v-if="savedSearches.length > 0" class="px-6 py-4 border-t bg-gray-50">
      <h4 class="text-sm font-medium text-gray-900 mb-2">Saved Searches</h4>
      <div class="flex flex-wrap gap-2">
        <button
          v-for="search in savedSearches"
          :key="search.id"
          @click="loadSavedSearch(search)"
          class="px-3 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded-full hover:bg-blue-200"
        >
          {{ search.name }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { ChevronDownIcon } from '@heroicons/vue/24/outline';

interface Props {
  departments: any[];
  positions: any[];
  managers: any[];
  availableSkills: string[];
  savedSearches: any[];
  resultCount: number;
}

const props = defineProps<Props>();

const emit = defineEmits(['search', 'save-search']);

const showAdvanced = ref(false);
const isSearching = ref(false);

const filters = ref({
  search: '',
  status: '',
  department: '',
  position: '',
  hireDateFrom: '',
  hireDateTo: '',
  salaryMin: null,
  salaryMax: null,
  performanceScore: '',
  commissionEligible: '',
  yearsOfService: '',
  skills: [],
  manager: ''
});

const filteredPositions = computed(() => {
  if (!filters.value.department) return props.positions;
  return props.positions.filter(pos => pos.department_id == filters.value.department);
});

const onDepartmentChange = () => {
  filters.value.position = '';
};

const toggleSkill = (skill: string) => {
  const index = filters.value.skills.indexOf(skill);
  if (index > -1) {
    filters.value.skills.splice(index, 1);
  } else {
    filters.value.skills.push(skill);
  }
};

const performSearch = () => {
  isSearching.value = true;
  emit('search', { ...filters.value });
  setTimeout(() => {
    isSearching.value = false;
  }, 1000);
};

const resetFilters = () => {
  filters.value = {
    search: '',
    status: '',
    department: '',
    position: '',
    hireDateFrom: '',
    hireDateTo: '',
    salaryMin: null,
    salaryMax: null,
    performanceScore: '',
    commissionEligible: '',
    yearsOfService: '',
    skills: [],
    manager: ''
  };
  performSearch();
};

const saveSearch = () => {
  const name = prompt('Enter a name for this search:');
  if (name) {
    emit('save-search', { name, filters: { ...filters.value } });
  }
};

const loadSavedSearch = (search: any) => {
  filters.value = { ...search.filters };
  performSearch();
};

// Auto-search on filter changes (debounced)
let searchTimeout: NodeJS.Timeout;
watch(filters, () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    performSearch();
  }, 500);
}, { deep: true });
</script>