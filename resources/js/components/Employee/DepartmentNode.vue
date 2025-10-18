<template>
  <div class="department-node">
    <!-- Department Item -->
    <div
      class="flex items-center p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors"
      :class="{
        'bg-blue-50 border-blue-200': expanded,
        'bg-gray-50': isDragOver
      }"
      draggable="true"
      @dragstart="handleDragStart"
      @dragover="handleDragOver"
      @dragleave="handleDragLeave"
      @drop="handleDrop"
    >
      <!-- Expand/Collapse Button -->
      <button
        v-if="department.children && department.children.length > 0"
        @click="$emit('toggle', department.id)"
        class="flex-shrink-0 p-1 rounded hover:bg-gray-200 transition-colors mr-2"
      >
        <ChevronRightIcon
          class="h-4 w-4 text-gray-500 transition-transform"
          :class="{ 'rotate-90': expanded }"
        />
      </button>
      <div v-else class="w-6 mr-2"></div>

      <!-- Department Icon -->
      <div class="flex-shrink-0 mr-3">
        <div class="p-2 rounded-lg" :class="getIconClass(department)">
          <BuildingOfficeIcon class="h-5 w-5" />
        </div>
      </div>

      <!-- Department Info -->
      <div class="flex-1 min-w-0">
        <div class="flex items-center justify-between">
          <div class="flex-1">
            <h4 class="text-sm font-medium text-gray-900 truncate">
              {{ department.name }}
            </h4>
            <div class="flex items-center space-x-4 mt-1">
              <span class="text-xs text-gray-500">
                {{ department.employee_count }} {{ department.employee_count === 1 ? 'employee' : 'employees' }}
              </span>
              <span v-if="department.head" class="text-xs text-gray-500">
                Head: {{ department.head.first_name }} {{ department.head.last_name }}
              </span>
              <span
                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                :class="department.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
              >
                {{ department.is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center space-x-2 ml-4">
            <button
              v-if="canCreate"
              @click="$emit('add-child', department)"
              class="p-1 text-gray-400 hover:text-blue-600 transition-colors"
              title="Add Subdepartment"
            >
              <PlusIcon class="h-4 w-4" />
            </button>
            <button
              v-if="canEdit"
              @click="$emit('edit', department)"
              class="p-1 text-gray-400 hover:text-indigo-600 transition-colors"
              title="Edit Department"
            >
              <PencilIcon class="h-4 w-4" />
            </button>
            <button
              v-if="canDelete && department.employee_count === 0"
              @click="$emit('delete', department)"
              class="p-1 text-gray-400 hover:text-red-600 transition-colors"
              title="Delete Department"
            >
              <TrashIcon class="h-4 w-4" />
            </button>
            <div
              v-else-if="canDelete"
              class="p-1 text-gray-300 cursor-not-allowed"
              title="Cannot delete department with employees"
            >
              <TrashIcon class="h-4 w-4" />
            </div>
          </div>
        </div>

        <!-- Description -->
        <p v-if="department.description" class="text-xs text-gray-600 mt-2 line-clamp-2">
          {{ department.description }}
        </p>
      </div>
    </div>

    <!-- Children -->
    <div
      v-if="expanded && department.children && department.children.length > 0"
      class="ml-8 mt-2 space-y-2 border-l-2 border-gray-200 pl-4"
    >
      <DepartmentNode
        v-for="child in department.children"
        :key="child.id"
        :department="child"
        :expanded="childExpanded(child.id)"
        :can-edit="canEdit"
        :can-delete="canDelete"
        :can-create="canCreate"
        @toggle="$emit('toggle', $event)"
        @edit="$emit('edit', $event)"
        @delete="$emit('delete', $event)"
        @add-child="$emit('add-child', $event)"
        @drag-start="$emit('drag-start', $event)"
        @drag-over="$emit('drag-over', $event)"
        @drop="$emit('drop', $event)"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import {
  BuildingOfficeIcon,
  ChevronRightIcon,
  PlusIcon,
  PencilIcon,
  TrashIcon
} from '@heroicons/vue/24/outline'

// Types
interface Department {
  id: number
  name: string
  description?: string
  parent_id?: number
  head_id?: number
  is_active: boolean
  employee_count: number
  children?: Department[]
  head?: {
    id: number
    first_name: string
    last_name: string
    employee_number: string
  }
}

interface Props {
  department: Department
  expanded: boolean
  canEdit: boolean
  canDelete: boolean
  canCreate: boolean
}

const props = defineProps<Props>()

// Emits
const emit = defineEmits<{
  toggle: [departmentId: number]
  edit: [department: Department]
  delete: [department: Department]
  'add-child': [department: Department]
  'drag-start': [department: Department]
  'drag-over': [event: DragEvent]
  drop: [department: Department]
}>()

// Reactive data
const isDragOver = ref(false)

// Methods
const getIconClass = (department: Department): string => {
  if (!department.is_active) {
    return 'bg-gray-100 text-gray-500'
  }
  
  if (department.children && department.children.length > 0) {
    return 'bg-blue-100 text-blue-600'
  }
  
  return 'bg-green-100 text-green-600'
}

const childExpanded = (childId: number): boolean => {
  // This will be passed down from parent component
  return false
}

// Drag and Drop handlers
const handleDragStart = (event: DragEvent) => {
  event.dataTransfer?.setData('text/plain', '')
  emit('drag-start', props.department)
}

const handleDragOver = (event: DragEvent) => {
  event.preventDefault()
  isDragOver.value = true
  emit('drag-over', event)
}

const handleDragLeave = () => {
  isDragOver.value = false
}

const handleDrop = (event: DragEvent) => {
  event.preventDefault()
  isDragOver.value = false
  emit('drop', props.department)
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.department-node {
  position: relative;
}

.department-node:hover .actions {
  opacity: 1;
}
</style>