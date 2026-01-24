<template>
  <div class="family-tree-container h-full flex flex-col bg-gradient-to-br from-purple-50 via-indigo-50 to-blue-50">
    <!-- Mobile-optimized header -->
    <div class="controls flex items-center justify-between px-4 py-3 bg-gradient-to-r from-purple-500 to-indigo-600 text-white shadow-lg">
      <h2 class="text-lg font-semibold">Family Tree</h2>
      <div class="flex gap-2">
        <button 
          @click="zoomOut" 
          class="p-2 bg-white/20 hover:bg-white/30 rounded-full transition-colors"
          aria-label="Zoom out"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
          </svg>
        </button>
        <button 
          @click="resetZoom" 
          class="p-2 bg-white/20 hover:bg-white/30 rounded-full transition-colors"
          aria-label="Reset zoom"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
        </button>
        <button 
          @click="zoomIn" 
          class="p-2 bg-white/20 hover:bg-white/30 rounded-full transition-colors"
          aria-label="Zoom in"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
        </button>
      </div>
    </div>
    
    <!-- Tree canvas -->
    <div 
      ref="treeContainer" 
      class="tree-canvas flex-1 overflow-auto pb-20"
    >
      <div
        :style="{ transform: `scale(${zoom})`, transformOrigin: 'top center' }"
        class="inline-block min-w-full p-6 transition-transform"
      >
        <div v-if="persons.length === 0" class="text-center py-12">
          <div class="w-20 h-20 mx-auto bg-gradient-to-br from-purple-400 to-indigo-500 rounded-full flex items-center justify-center mb-4">
            <UserGroupIcon class="h-10 w-10 text-white" aria-hidden="true" />
          </div>
          <h3 class="text-lg font-semibold text-gray-900">No family members yet</h3>
          <p class="mt-2 text-sm text-gray-600">
            Add family members to see the family tree visualization.
          </p>
        </div>

        <!-- Hierarchical Tree Layout -->
        <div v-else class="flex flex-col items-center gap-8">
          <PersonNode
            v-for="person in rootPersons"
            :key="person.id"
            :person="person"
            :relationships="relationships"
            :all-persons="personsMap"
            @select="handlePersonSelect"
          />
        </div>
      </div>
    </div>

    <!-- Legend -->
    <div class="px-4 py-3 bg-white/80 backdrop-blur-sm border-t border-gray-200 flex items-center justify-center gap-6 text-xs font-medium">
      <div class="flex items-center gap-2">
        <div class="w-3 h-3 rounded-full bg-blue-500 shadow-sm"></div>
        <span class="text-gray-700">Male</span>
      </div>
      <div class="flex items-center gap-2">
        <div class="w-3 h-3 rounded-full bg-pink-500 shadow-sm"></div>
        <span class="text-gray-700">Female</span>
      </div>
      <div class="flex items-center gap-2">
        <div class="w-3 h-3 rounded-full bg-gray-400 shadow-sm"></div>
        <span class="text-gray-700">Deceased</span>
      </div>
    </div>

    <!-- Profile Modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div 
          v-if="selectedPerson" 
          class="fixed inset-0 z-50 flex items-end sm:items-center justify-center"
          @click.self="closeProfile"
        >
          <!-- Backdrop -->
          <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
          
          <!-- Modal Content -->
          <div class="relative w-full sm:max-w-lg mx-auto bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl transform transition-all max-h-[85vh] overflow-y-auto">
            <!-- Header with gradient -->
            <div class="sticky top-0 bg-gradient-to-r from-purple-500 to-indigo-600 text-white px-6 py-4 rounded-t-3xl sm:rounded-t-2xl z-10">
              <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold">Profile</h3>
                <button 
                  @click="closeProfile"
                  class="p-2 hover:bg-white/20 rounded-full transition-colors"
                  aria-label="Close profile"
                >
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Profile Content -->
            <div class="p-6">
              <!-- Avatar -->
              <div class="flex justify-center mb-6">
                <div class="relative">
                  <div 
                    v-if="selectedPerson.photo_url"
                    class="w-24 h-24 rounded-full overflow-hidden shadow-lg ring-4 ring-white"
                  >
                    <img :src="selectedPerson.photo_url" :alt="selectedPerson.name" class="w-full h-full object-cover" />
                  </div>
                  <div 
                    v-else
                    class="w-24 h-24 rounded-full bg-gradient-to-br from-purple-400 to-indigo-500 flex items-center justify-center text-white text-3xl font-bold shadow-lg ring-4 ring-white"
                  >
                    {{ getInitials(selectedPerson.name) }}
                  </div>
                  <div 
                    class="absolute bottom-0 right-0 w-8 h-8 rounded-full border-4 border-white shadow-lg flex items-center justify-center"
                    :class="selectedPerson.gender === 'male' ? 'bg-blue-500' : 'bg-pink-500'"
                  >
                    <svg v-if="selectedPerson.gender === 'male'" class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M9 9l10-10m0 0v7m0-7h-7m-2 17a5 5 0 110-10 5 5 0 010 10z"/>
                    </svg>
                    <svg v-else class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12 2a5 5 0 100 10 5 5 0 000-10zm0 12c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg>
                  </div>
                  <div 
                    v-if="selectedPerson.is_deceased"
                    class="absolute -top-1 -right-1 w-6 h-6 rounded-full bg-gray-500 border-2 border-white shadow-lg flex items-center justify-center"
                  >
                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/>
                    </svg>
                  </div>
                </div>
              </div>

              <!-- Name -->
              <h4 class="text-2xl font-bold text-center text-gray-900 mb-2">
                {{ selectedPerson.name }}
              </h4>
              <p v-if="selectedPerson.age" class="text-center text-gray-600 mb-6">
                {{ selectedPerson.age }} years old
              </p>

              <!-- Info Cards -->
              <div class="space-y-3 mt-6">
                <!-- Gender -->
                <div class="flex items-center gap-3 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl">
                  <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                  </div>
                  <div class="flex-1">
                    <p class="text-xs text-gray-500 font-medium">Gender</p>
                    <p class="text-sm font-semibold text-gray-900 capitalize">{{ selectedPerson.gender || 'Not specified' }}</p>
                  </div>
                </div>

                <!-- Status -->
                <div v-if="selectedPerson.is_deceased" class="flex items-center gap-3 p-4 bg-gradient-to-r from-gray-50 to-slate-50 rounded-xl">
                  <div class="w-10 h-10 rounded-full bg-gray-500 flex items-center justify-center text-white flex-shrink-0">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/>
                    </svg>
                  </div>
                  <div class="flex-1">
                    <p class="text-xs text-gray-500 font-medium">Status</p>
                    <p class="text-sm font-semibold text-gray-900">Deceased</p>
                  </div>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="flex gap-3 mt-6">
                <button
                  @click="viewDetails"
                  class="flex-1 py-3 bg-gradient-to-r from-purple-500 to-indigo-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all active:scale-95"
                >
                  View Details
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { UserGroupIcon } from '@heroicons/vue/24/outline';
import { router } from '@inertiajs/vue3';
import PersonNode from './PersonNode.vue';

interface Person {
  id: string;
  slug: string;
  name: string;
  photo_url: string | null;
  age: number | null;
  gender: 'male' | 'female' | null;
  is_deceased: boolean;
}

interface Relationship {
  id: number;
  person_id: string;
  related_person_id: string;
  relationship_type: string;
}

interface Props {
  persons: Person[];
  relationships: Relationship[];
  familyId: string;
  familySlug: string;
}

const props = defineProps<Props>();

// Import route helper
const route = (window as any).route;

const emit = defineEmits<{
  (e: 'selectPerson', personId: string): void;
}>();

const treeContainer = ref<HTMLElement | null>(null);
const zoom = ref(1);
const selectedPerson = ref<Person | null>(null);

// Create a map for quick person lookup
const personsMap = computed(() => {
  const map = new Map<string, Person>();
  props.persons.forEach(person => map.set(person.id, person));
  return map;
});

// Find root persons (those without parents in the tree)
const rootPersons = computed(() => {
  const childIds = new Set<string>();
  
  // Find all persons who are children
  props.relationships.forEach(rel => {
    if (rel.relationship_type === 'parent' || rel.relationship_type === 'child') {
      // If relationship_type is 'parent', then person_id is the child
      // If relationship_type is 'child', then person_id is the parent
      if (rel.relationship_type === 'parent') {
        childIds.add(rel.person_id);
      }
    }
  });

  // Root persons are those who are not children
  const roots = props.persons.filter(person => !childIds.has(person.id));
  
  // Group root persons who are siblings
  const grouped = groupSiblings(roots);
  
  return grouped;
});

// Group persons who are siblings together
const groupSiblings = (persons: Person[]) => {
  if (persons.length <= 1) return persons;
  
  const processed = new Set<string>();
  const groups: Person[][] = [];
  
  persons.forEach(person => {
    if (processed.has(person.id)) return;
    
    // Find siblings of this person
    const siblingGroup = [person];
    processed.add(person.id);
    
    // Check if any other person shares a sibling relationship
    persons.forEach(otherPerson => {
      if (otherPerson.id === person.id || processed.has(otherPerson.id)) return;
      
      const areSiblings = props.relationships.some(rel =>
        (rel.person_id === person.id && rel.related_person_id === otherPerson.id && rel.relationship_type === 'sibling') ||
        (rel.person_id === otherPerson.id && rel.related_person_id === person.id && rel.relationship_type === 'sibling')
      );
      
      if (areSiblings) {
        siblingGroup.push(otherPerson);
        processed.add(otherPerson.id);
      }
    });
    
    groups.push(siblingGroup);
  });
  
  // Flatten groups - for now just return first person of each group
  // In a more complex implementation, we'd render sibling groups horizontally
  return groups.flat();
};

const zoomIn = () => {
  zoom.value = Math.min(zoom.value + 0.1, 2);
};

const zoomOut = () => {
  zoom.value = Math.max(zoom.value - 0.1, 0.5);
};

const resetZoom = () => {
  zoom.value = 1;
};

const handlePersonSelect = (personId: string) => {
  const person = personsMap.value.get(personId);
  if (person) {
    selectedPerson.value = person;
  }
  emit('selectPerson', personId);
};

const closeProfile = () => {
  selectedPerson.value = null;
};

const viewDetails = () => {
  if (selectedPerson.value) {
    router.visit(route('ubumi.families.persons.show', {
      family: props.familySlug,
      person: selectedPerson.value.slug
    }));
  }
};

const getInitials = (name: string): string => {
  return name
    .split(' ')
    .map(part => part[0])
    .join('')
    .toUpperCase()
    .slice(0, 2);
};
</script>

<style scoped>
.family-tree-container {
  user-select: none;
}

/* Modal transitions */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-active > div:last-child,
.modal-leave-active > div:last-child {
  transition: transform 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from > div:last-child {
  transform: translateY(100%);
}

.modal-leave-to > div:last-child {
  transform: translateY(100%);
}

@media (min-width: 640px) {
  .modal-enter-from > div:last-child,
  .modal-leave-to > div:last-child {
    transform: translateY(0) scale(0.95);
  }
}
</style>
