<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-100">
    <!-- Header -->
    <div class="p-6 border-b border-gray-100">
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <div class="p-2 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg mr-3">
            <BuildingOfficeIcon class="h-6 w-6 text-white" />
          </div>
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Department Structure</h3>
            <p class="text-sm text-gray-500">Organizational hierarchy and department management</p>
          </div>
        </div>
        <div class="flex items-center space-x-3">
          <button
            @click="toggleExpandAll"
            class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
          >
            {{ allExpanded ? 'Collapse All' : 'Expand All' }}
          </button>
          <button
            v-if="canCreate"
            @click="showCreateModal = true"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
          >
            <PlusIcon class="h-4 w-4 mr-2" />
            Add Department
          </button>
        </div>
      </div>
    </div>

    <!-- Department Tree -->
    <div class="p-6">
      <div v-if="departments.length === 0" class="text-center py-12">
        <BuildingOfficeIcon class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">No departments</h3>
        <p class="mt-1 text-sm text-gray-500">Get started by creating your first department.</p>
        <div v-if="canCreate" class="mt-6">
          <button
            @click="showCreateModal = true"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
          >
            <PlusIcon class="h-4 w-4 mr-2" />
            Add Department
          </button>
        </div>
      </div>

      <div v-else class="space-y-2">
        <DepartmentNode
          v-for="department in rootDepartments"
          :key="department.id"
          :department="department"
          :expanded="expandedDepartments.has(department.id)"
          :can-edit="canEdit"
          :can-delete="canDelete"
          :can-create="canCreate"
          @toggle="toggleDepartment"
          @edit="editDepartment"
          @delete="confirmDelete"
          @add-child="addChildDepartment"
          @drag-start="onDragStart"
          @drag-over="onDragOver"
          @drop="onDrop"
        />
      </div>
    </div>

    <!-- Create/Edit Department Modal -->
    <Modal :show="showCreateModal || showEditModal" @close="closeModal">
      <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">
          {{ editingDepartment ? 'Edit Department' : 'Create Department' }}
        </h3>
        
        <form @submit.prevent="submitDepartment">
          <div class="space-y-4">
            <!-- Department Name -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Department Name <span class="text-red-500">*</span>
              </label>
              <input
                v-model="departmentForm.name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': errors.name }"
                placeholder="Enter department name"
              />
              <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
            </div>

            <!-- Parent Department -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Parent Department
              </label>
              <select
                v-model="departmentForm.parent_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': errors.parent_id }"
              >
                <option value="">No Parent (Root Department)</option>
                <option
                  v-for="dept in availableParents"
                  :key="dept.id"
                  :value="dept.id"
                  :disabled="dept.id === editingDepartment?.id"
                >
                  {{ dept.name }}
                </option>
              </select>
              <p v-if="errors.parent_id" class="mt-1 text-sm text-red-600">{{ errors.parent_id }}</p>
            </div>

            <!-- Description -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Description
              </label>
              <textarea
                v-model="departmentForm.description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': errors.description }"
                placeholder="Enter department description"
              ></textarea>
              <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
            </div>

            <!-- Department Head -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Department Head
              </label>
              <select
                v-model="departmentForm.head_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': errors.head_id }"
              >
                <option value="">No Department Head</option>
                <option v-for="employee in employees" :key="employee.id" :value="employee.id">
                  {{ employee.first_name }} {{ employee.last_name }} ({{ employee.employee_number }})
                </option>
              </select>
              <p v-if="errors.head_id" class="mt-1 text-sm text-red-600">{{ errors.head_id }}</p>
            </div>

            <!-- Status -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Status
              </label>
              <select
                v-model="departmentForm.is_active"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option :value="true">Active</option>
                <option :value="false">Inactive</option>
              </select>
            </div>
          </div>

          <div class="flex items-center justify-end space-x-3 mt-6 pt-6 border-t border-gray-200">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="processing"
              class="px-6 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
            >
              <LoadingSpinner v-if="processing" class="w-4 h-4 mr-2" />
              {{ editingDepartment ? 'Update Department' : 'Create Department' }}
            </button>
          </div>
        </form>
      </div>
    </Modal>

    <!-- Delete Confirmation Modal -->
    <Modal :show="showDeleteModal" @close="showDeleteModal = false">
      <div class="p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <ExclamationTriangleIcon class="h-6 w-6 text-red-600" />
          </div>
          <div class="ml-3">
            <h3 class="text-lg font-medium text-gray-900">Delete Department</h3>
            <div class="mt-2">
              <p class="text-sm text-gray-500">
                Are you sure you want to delete "{{ departmentToDelete?.name }}"? 
                This action cannot be undone and will affect all employees in this department.
              </p>
              <div v-if="departmentToDelete?.children?.length > 0" class="mt-2 p-3 bg-yellow-50 rounded-lg">
                <p class="text-sm text-yellow-800">
                  <strong>Warning:</strong> This department has {{ departmentToDelete.children.length }} 
                  subdepartment(s). They will be moved to the parent department or become root departments.
                </p>
              </div>
            </div>
          </div>
        </div>
        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
          <button
            type="button"
            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
            @click="deleteDepartment"
          >
            Delete
          </button>
          <button
            type="button"
            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm"
            @click="showDeleteModal = false"
          >
            Cancel
          </button>
        </div>
      </div>
    </Modal>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import {
  BuildingOfficeIcon,
  PlusIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'
import Modal from '@/components/Modal.vue'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import DepartmentNode from './DepartmentNode.vue'

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

interface Employee {
  id: number
  first_name: string
  last_name: string
  employee_number: string
}

interface Props {
  departments: Department[]
  employees: Employee[]
  canCreate?: boolean
  canEdit?: boolean
  canDelete?: boolean
  errors?: Record<string, string>
}

const props = withDefaults(defineProps<Props>(), {
  canCreate: false,
  canEdit: false,
  canDelete: false,
  errors: () => ({})
})

// Reactive data
const expandedDepartments = ref(new Set<number>())
const showCreateModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const editingDepartment = ref<Department | null>(null)
const departmentToDelete = ref<Department | null>(null)
const processing = ref(false)
const draggedDepartment = ref<Department | null>(null)

const departmentForm = reactive({
  name: '',
  description: '',
  parent_id: '',
  head_id: '',
  is_active: true
})

// Computed
const rootDepartments = computed(() => {
  return buildDepartmentTree(props.departments)
})

const allExpanded = computed(() => {
  return expandedDepartments.value.size === props.departments.length
})

const availableParents = computed(() => {
  if (!editingDepartment.value) return props.departments
  
  // Exclude the department being edited and its descendants
  const excludeIds = new Set([editingDepartment.value.id])
  const addDescendants = (dept: Department) => {
    dept.children?.forEach(child => {
      excludeIds.add(child.id)
      addDescendants(child)
    })
  }
  addDescendants(editingDepartment.value)
  
  return props.departments.filter(dept => !excludeIds.has(dept.id))
})

// Methods
const buildDepartmentTree = (departments: Department[]): Department[] => {
  const departmentMap = new Map<number, Department>()
  const rootDepartments: Department[] = []
  
  // Create a map of all departments
  departments.forEach(dept => {
    departmentMap.set(dept.id, { ...dept, children: [] })
  })
  
  // Build the tree structure
  departments.forEach(dept => {
    const department = departmentMap.get(dept.id)!
    
    if (dept.parent_id && departmentMap.has(dept.parent_id)) {
      const parent = departmentMap.get(dept.parent_id)!
      parent.children!.push(department)
    } else {
      rootDepartments.push(department)
    }
  })
  
  return rootDepartments
}

const toggleDepartment = (departmentId: number) => {
  if (expandedDepartments.value.has(departmentId)) {
    expandedDepartments.value.delete(departmentId)
  } else {
    expandedDepartments.value.add(departmentId)
  }
}

const toggleExpandAll = () => {
  if (allExpanded.value) {
    expandedDepartments.value.clear()
  } else {
    props.departments.forEach(dept => {
      expandedDepartments.value.add(dept.id)
    })
  }
}

const editDepartment = (department: Department) => {
  editingDepartment.value = department
  departmentForm.name = department.name
  departmentForm.description = department.description || ''
  departmentForm.parent_id = department.parent_id?.toString() || ''
  departmentForm.head_id = department.head_id?.toString() || ''
  departmentForm.is_active = department.is_active
  showEditModal.value = true
}

const addChildDepartment = (parentDepartment: Department) => {
  editingDepartment.value = null
  departmentForm.name = ''
  departmentForm.description = ''
  departmentForm.parent_id = parentDepartment.id.toString()
  departmentForm.head_id = ''
  departmentForm.is_active = true
  showCreateModal.value = true
}

const confirmDelete = (department: Department) => {
  departmentToDelete.value = department
  showDeleteModal.value = true
}

const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  editingDepartment.value = null
  departmentForm.name = ''
  departmentForm.description = ''
  departmentForm.parent_id = ''
  departmentForm.head_id = ''
  departmentForm.is_active = true
}

const submitDepartment = () => {
  processing.value = true
  
  const data = {
    name: departmentForm.name,
    description: departmentForm.description || null,
    parent_id: departmentForm.parent_id || null,
    head_id: departmentForm.head_id || null,
    is_active: departmentForm.is_active
  }
  
  if (editingDepartment.value) {
    router.put(route('departments.update', editingDepartment.value.id), data, {
      onSuccess: () => {
        closeModal()
        processing.value = false
      },
      onError: () => {
        processing.value = false
      }
    })
  } else {
    router.post(route('departments.store'), data, {
      onSuccess: () => {
        closeModal()
        processing.value = false
      },
      onError: () => {
        processing.value = false
      }
    })
  }
}

const deleteDepartment = () => {
  if (departmentToDelete.value) {
    router.delete(route('departments.destroy', departmentToDelete.value.id), {
      onSuccess: () => {
        showDeleteModal.value = false
        departmentToDelete.value = null
      }
    })
  }
}

// Drag and Drop functionality
const onDragStart = (department: Department) => {
  draggedDepartment.value = department
}

const onDragOver = (event: DragEvent) => {
  event.preventDefault()
}

const onDrop = (targetDepartment: Department) => {
  if (!draggedDepartment.value || draggedDepartment.value.id === targetDepartment.id) {
    return
  }
  
  // Prevent dropping a parent onto its child
  const isDescendant = (parent: Department, child: Department): boolean => {
    if (parent.id === child.id) return true
    return parent.children?.some(c => isDescendant(c, child)) || false
  }
  
  if (isDescendant(draggedDepartment.value, targetDepartment)) {
    return
  }
  
  // Update the department's parent
  router.put(route('departments.update', draggedDepartment.value.id), {
    ...draggedDepartment.value,
    parent_id: targetDepartment.id
  })
  
  draggedDepartment.value = null
}
</script>