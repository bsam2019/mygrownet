<template>
  <div class="flex flex-col items-center">
    <!-- Person Card -->
    <button
      @click="$emit('select', person.id)"
      class="flex flex-col items-center p-4 bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all transform hover:scale-105 active:scale-95 min-w-[140px]"
      :class="[
        person.is_deceased ? 'opacity-75' : '',
        'border-2',
        borderColor
      ]"
    >
      <!-- Photo with gradient ring -->
      <div class="relative mb-3">
        <div
          class="absolute inset-0 rounded-full blur-md opacity-50"
          :class="gradientBg"
        ></div>
        <div
          v-if="person.photo_url"
          class="relative h-20 w-20 rounded-full overflow-hidden ring-4 ring-white shadow-lg"
        >
          <img
            :src="person.photo_url"
            :alt="person.name"
            class="h-full w-full object-cover"
          />
        </div>
        <div
          v-else
          class="relative h-20 w-20 rounded-full flex items-center justify-center ring-4 ring-white shadow-lg text-white text-xl font-bold"
          :class="gradientBg"
        >
          {{ getInitials(person.name) }}
        </div>

        <!-- Gender badge -->
        <div
          class="absolute -bottom-1 -right-1 rounded-full p-1.5 shadow-lg ring-2 ring-white"
          :class="genderBadgeColor"
        >
          <svg v-if="person.gender === 'male'" class="h-3 w-3 text-white" fill="currentColor" viewBox="0 0 24 24">
            <path d="M9 9l10-10m0 0v7m0-7h-7m-2 17a5 5 0 110-10 5 5 0 010 10z"/>
          </svg>
          <svg v-else-if="person.gender === 'female'" class="h-3 w-3 text-white" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2a5 5 0 100 10 5 5 0 000-10zm0 12c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
          </svg>
          <svg v-else class="h-3 w-3 text-white" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
          </svg>
        </div>

        <!-- Deceased indicator -->
        <div
          v-if="person.is_deceased"
          class="absolute -top-1 -left-1 bg-gray-600 text-white rounded-full p-1.5 shadow-lg ring-2 ring-white"
          title="Deceased"
        >
          <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/>
          </svg>
        </div>
      </div>

      <!-- Name and Age -->
      <div class="text-center">
        <p class="text-sm font-bold text-gray-900 max-w-[120px] truncate mb-1">
          {{ person.name }}
        </p>
        <p v-if="person.age" class="text-xs font-medium px-2 py-0.5 rounded-full inline-block" :class="ageBadgeColor">
          {{ person.age }} years
        </p>
      </div>
    </button>

    <!-- Children -->
    <div v-if="childrenGroups.length > 0" class="mt-4">
      <!-- Connector Line with gradient -->
      <div class="flex justify-center">
        <div class="w-1 h-8 rounded-full" :class="connectorColor"></div>
      </div>

      <!-- Children Groups (each group is a generation/level) -->
      <div class="flex flex-col gap-8">
        <div v-for="(group, groupIndex) in childrenGroups" :key="groupIndex" class="flex gap-6 justify-center relative">
          <!-- Horizontal connector for siblings -->
          <div 
            v-if="group.length > 1"
            class="absolute top-0 left-1/2 -translate-x-1/2 h-1 rounded-full"
            :class="connectorColor"
            :style="{ width: `calc(100% - ${140 / group.length}px)` }"
          ></div>
          
          <PersonNode
            v-for="child in group"
            :key="child.id"
            :person="child"
            :relationships="relationships"
            :all-persons="allPersons"
            @select="$emit('select', $event)"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Person {
  id: string;
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
  person: Person;
  relationships: Relationship[];
  allPersons: Map<string, Person>;
}

const props = defineProps<Props>();

defineEmits<{
  (e: 'select', personId: string): void;
}>();

// Gender-based styling with vibrant gradients
const gradientBg = computed(() => {
  if (props.person.is_deceased) return 'bg-gradient-to-br from-gray-400 to-gray-500';
  if (props.person.gender === 'male') return 'bg-gradient-to-br from-blue-400 to-blue-600';
  if (props.person.gender === 'female') return 'bg-gradient-to-br from-pink-400 to-pink-600';
  return 'bg-gradient-to-br from-purple-400 to-indigo-500';
});

const borderColor = computed(() => {
  if (props.person.is_deceased) return 'border-gray-300';
  if (props.person.gender === 'male') return 'border-blue-200';
  if (props.person.gender === 'female') return 'border-pink-200';
  return 'border-purple-200';
});

const genderBadgeColor = computed(() => {
  if (props.person.gender === 'male') return 'bg-blue-500';
  if (props.person.gender === 'female') return 'bg-pink-500';
  return 'bg-purple-500';
});

const ageBadgeColor = computed(() => {
  if (props.person.is_deceased) return 'bg-gray-100 text-gray-700';
  if (props.person.gender === 'male') return 'bg-blue-100 text-blue-700';
  if (props.person.gender === 'female') return 'bg-pink-100 text-pink-700';
  return 'bg-purple-100 text-purple-700';
});

const connectorColor = computed(() => {
  if (props.person.is_deceased) return 'bg-gradient-to-b from-gray-300 to-gray-400';
  if (props.person.gender === 'male') return 'bg-gradient-to-b from-blue-300 to-blue-400';
  if (props.person.gender === 'female') return 'bg-gradient-to-b from-pink-300 to-pink-400';
  return 'bg-gradient-to-b from-purple-300 to-indigo-400';
});

// Find children of this person
const children = computed(() => {
  const childIds = props.relationships
    .filter(rel => 
      rel.related_person_id === props.person.id && 
      (rel.relationship_type === 'parent' || rel.relationship_type === 'father' || rel.relationship_type === 'mother')
    )
    .map(rel => rel.person_id);

  return childIds
    .map(id => props.allPersons.get(id))
    .filter((p): p is Person => p !== undefined);
});

// Group children who are siblings together
const childrenGroups = computed(() => {
  if (children.value.length === 0) return [];
  
  const processed = new Set<string>();
  const groups: Person[][] = [];
  
  children.value.forEach(child => {
    if (processed.has(child.id)) return;
    
    // Start a new sibling group
    const siblingGroup = [child];
    processed.add(child.id);
    
    // Find siblings of this child
    children.value.forEach(otherChild => {
      if (otherChild.id === child.id || processed.has(otherChild.id)) return;
      
      // Check if they are siblings
      const areSiblings = props.relationships.some(rel =>
        (rel.person_id === child.id && rel.related_person_id === otherChild.id && rel.relationship_type === 'sibling') ||
        (rel.person_id === otherChild.id && rel.related_person_id === child.id && rel.relationship_type === 'sibling')
      );
      
      if (areSiblings) {
        siblingGroup.push(otherChild);
        processed.add(otherChild.id);
      }
    });
    
    groups.push(siblingGroup);
  });
  
  return groups;
});

const getInitials = (name: string): string => {
  return name
    .split(' ')
    .map(part => part[0])
    .join('')
    .toUpperCase()
    .slice(0, 2);
};
</script>
