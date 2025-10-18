<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-100">
    <!-- Header -->
    <div class="p-6 border-b border-gray-100">
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <div class="p-2 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg mr-3">
            <TargetIcon class="h-6 w-6 text-white" />
          </div>
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Goal Tracker</h3>
            <p class="text-sm text-gray-500">Track and manage employee goals and objectives</p>
          </div>
        </div>
        <div class="flex items-center space-x-3">
          <button
            v-if="canCreate"
            @click="showCreateModal = true"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
          >
            <PlusIcon class="h-4 w-4 mr-2" />
            Add Goal
          </button>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="p-6 border-b border-gray-100 bg-gray-50">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Employee Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
          <select
            v-model="filters.employee"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
            @change="applyFilters"
          >
            <option value="">All Employees</option>
            <option v-for="emp in employees" :key="emp.id" :value="emp.id">
              {{ emp.first_name }} {{ emp.last_name }}
            </option>
          </select>
        </div>

        <!-- Status Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select
            v-model="filters.status"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
            @change="applyFilters"
          >
            <option value="">All Statuses</option>
            <option value="not_started">Not Started</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
            <option value="overdue">Overdue</option>
          </select>
        </div>

        <!-- Priority Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
          <select
            v-model="filters.priority"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
            @change="applyFilters"
          >
            <option value="">All Priorities</option>
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
            <option value="critical">Critical</option>
          </select>
        </div>

        <!-- Due Date Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
          <select
            v-model="filters.dueDate"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
            @change="applyFilters"
          >
            <option value="">All Dates</option>
            <option value="overdue">Overdue</option>
            <option value="this_week">This Week</option>
            <option value="this_month">This Month</option>
            <option value="next_month">Next Month</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Goals Summary -->
    <div class="p-6 border-b border-gray-100">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <div class="text-center">
          <div class="text-3xl font-bold text-gray-900">{{ goalStats.total }}</div>
          <div class="text-sm text-gray-500">Total Goals</div>
        </div>
        <div class="text-center">
          <div class="text-3xl font-bold text-green-600">{{ goalStats.completed }}</div>
          <div class="text-sm text-gray-500">Completed</div>
        </div>
        <div class="text-center">
          <div class="text-3xl font-bold text-blue-600">{{ goalStats.inProgress }}</div>
          <div class="text-sm text-gray-500">In Progress</div>
        </div>
        <div class="text-center">
          <div class="text-3xl font-bold text-red-600">{{ goalStats.overdue }}</div>
          <div class="text-sm text-gray-500">Overdue</div>
        </div>
      </div>
    </div>

    <!-- Goals List -->
    <div class="p-6">
      <div v-if="filteredGoals.length === 0" class="text-center py-12">
        <TargetIcon class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">No goals found</h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ hasFilters ? 'Try adjusting your search criteria.' : 'Get started by creating your first goal.' }}
        </p>
        <div v-if="!hasFilters && canCreate" class="mt-6">
          <button
            @click="showCreateModal = true"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
          >
            <PlusIcon class="h-4 w-4 mr-2" />
            Add Goal
          </button>
        </div>
      </div>

      <div v-else class="space-y-4">
        <div
          v-for="goal in filteredGoals"
          :key="goal.id"
          class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow"
        >
          <!-- Goal Header -->
          <div class="flex items-start justify-between mb-4">
            <div class="flex-1">
              <div class="flex items-center mb-2">
                <h4 class="text-lg font-medium text-gray-900 mr-3">{{ goal.title }}</h4>
                <span
                  class="inline-flex px-2 py-1 text-xs font-semibold rounded-full mr-2"
                  :class="getStatusBadgeClass(goal.status)"
                >
                  {{ formatStatus(goal.status) }}
                </span>
                <span
                  class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                  :class="getPriorityBadgeClass(goal.priority)"
                >
                  {{ formatPriority(goal.priority) }}
                </span>
              </div>
              <p class="text-sm text-gray-600 mb-2">{{ goal.employee.first_name }} {{ goal.employee.last_name }}</p>
              <p class="text-sm text-gray-500">Due: {{ formatDate(goal.due_date) }}</p>
            </div>
            <div class="flex items-center space-x-2 ml-4">
              <button
                @click="viewGoal(goal)"
                class="p-2 text-gray-400 hover:text-blue-600 transition-colors"
                title="View Goal"
              >
                <EyeIcon class="h-4 w-4" />
              </button>
              <button
                v-if="canEdit"
                @click="editGoal(goal)"
                class="p-2 text-gray-400 hover:text-indigo-600 transition-colors"
                title="Edit Goal"
              >
                <PencilIcon class="h-4 w-4" />
              </button>
              <button
                v-if="canDelete && goal.status === 'not_started'"
                @click="confirmDelete(goal)"
                class="p-2 text-gray-400 hover:text-red-600 transition-colors"
                title="Delete Goal"
              >
                <TrashIcon class="h-4 w-4" />
              </button>
            </div>
          </div>

          <!-- Progress Bar -->
          <div class="mb-4">
            <div class="flex items-center justify-between mb-2">
              <span class="text-sm font-medium text-gray-700">Progress</span>
              <span class="text-sm text-gray-500">{{ goal.progress }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div
                class="h-2 rounded-full transition-all duration-300"
                :class="getProgressBarClass(goal.progress)"
                :style="{ width: `${goal.progress}%` }"
              ></div>
            </div>
          </div>

          <!-- Goal Description -->
          <div v-if="goal.description" class="mb-4">
            <p class="text-sm text-gray-600 line-clamp-2">{{ goal.description }}</p>
          </div>

          <!-- Goal Actions -->
          <div class="flex items-center justify-between pt-4 border-t border-gray-200">
            <div class="text-sm text-gray-500">
              Created {{ formatDate(goal.created_at) }}
            </div>
            <div class="flex items-center space-x-2">
              <button
                v-if="goal.status === 'not_started' && canEdit"
                @click="startGoal(goal)"
                class="px-3 py-1 text-sm font-medium text-blue-700 bg-blue-100 rounded-lg hover:bg-blue-200 transition-colors"
              >
                Start Goal
              </button>
              <button
                v-if="goal.status === 'in_progress' && canEdit"
                @click="updateProgress(goal)"
                class="px-3 py-1 text-sm font-medium text-indigo-700 bg-indigo-100 rounded-lg hover:bg-indigo-200 transition-colors"
              >
                Update Progress
              </button>
              <button
                v-if="goal.status === 'in_progress' && goal.progress >= 100 && canEdit"
                @click="completeGoal(goal)"
                class="px-3 py-1 text-sm font-medium text-green-700 bg-green-100 rounded-lg hover:bg-green-200 transition-colors"
              >
                Mark Complete
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Goal Modal -->
    <Modal :show="showCreateModal || showEditModal" @close="closeModal" max-width="3xl">
      <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-6">
          {{ editingGoal ? 'Edit Goal' : 'Create Goal' }}
        </h3>
        
        <form @submit.prevent="submitGoal">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Left Column -->
            <div class="space-y-4">
              <!-- Employee -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Employee <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="goalForm.employee_id"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  :class="{ 'border-red-500': errors.employee_id }"
                >
                  <option value="">Select Employee</option>
                  <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                    {{ emp.first_name }} {{ emp.last_name }} - {{ emp.position?.title }}
                  </option>
                </select>
                <p v-if="errors.employee_id" class="mt-1 text-sm text-red-600">{{ errors.employee_id }}</p>
              </div>

              <!-- Goal Title -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Goal Title <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="goalForm.title"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  :class="{ 'border-red-500': errors.title }"
                  placeholder="Enter goal title"
                />
                <p v-if="errors.title" class="mt-1 text-sm text-red-600">{{ errors.title }}</p>
              </div>

              <!-- Priority -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Priority <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="goalForm.priority"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  :class="{ 'border-red-500': errors.priority }"
                >
                  <option value="">Select Priority</option>
                  <option value="low">Low</option>
                  <option value="medium">Medium</option>
                  <option value="high">High</option>
                  <option value="critical">Critical</option>
                </select>
                <p v-if="errors.priority" class="mt-1 text-sm text-red-600">{{ errors.priority }}</p>
              </div>

              <!-- Due Date -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Due Date <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="goalForm.due_date"
                  type="date"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  :class="{ 'border-red-500': errors.due_date }"
                />
                <p v-if="errors.due_date" class="mt-1 text-sm text-red-600">{{ errors.due_date }}</p>
              </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-4">
              <!-- Description -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Description
                </label>
                <textarea
                  v-model="goalForm.description"
                  rows="4"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  :class="{ 'border-red-500': errors.description }"
                  placeholder="Enter goal description"
                ></textarea>
                <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
              </div>

              <!-- Success Criteria -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Success Criteria
                </label>
                <div class="space-y-2">
                  <div
                    v-for="(criteria, index) in goalForm.success_criteria"
                    :key="index"
                    class="flex items-center space-x-2"
                  >
                    <input
                      v-model="goalForm.success_criteria[index]"
                      type="text"
                      class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="Enter success criteria"
                    />
                    <button
                      type="button"
                      @click="removeCriteria(index)"
                      class="p-2 text-red-600 hover:text-red-800 transition-colors"
                    >
                      <TrashIcon class="h-4 w-4" />
                    </button>
                  </div>
                  <button
                    type="button"
                    @click="addCriteria"
                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors"
                  >
                    <PlusIcon class="h-4 w-4 mr-2" />
                    Add Criteria
                  </button>
                </div>
              </div>

              <!-- Progress (for editing) -->
              <div v-if="editingGoal">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Progress (%)
                </label>
                <input
                  v-model.number="goalForm.progress"
                  type="number"
                  min="0"
                  max="100"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  :class="{ 'border-red-500': errors.progress }"
                  placeholder="Enter progress percentage"
                />
                <p v-if="errors.progress" class="mt-1 text-sm text-red-600">{{ errors.progress }}</p>
              </div>
            </div>
          </div>

          <div class="flex items-center justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
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
              {{ editingGoal ? 'Update Goal' : 'Create Goal' }}
            </button>
          </div>
        </form>
      </div>
    </Modal>

    <!-- View Goal Modal -->
    <Modal :show="showViewModal" @close="showViewModal = false" max-width="3xl">
      <div v-if="viewingGoal" class="p-6">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-lg font-medium text-gray-900">Goal Details</h3>
          <div class="flex items-center space-x-2">
            <span
              class="inline-flex px-3 py-1 text-sm font-semibold rounded-full"
              :class="getStatusBadgeClass(viewingGoal.status)"
            >
              {{ formatStatus(viewingGoal.status) }}
            </span>
            <span
              class="inline-flex px-3 py-1 text-sm font-semibold rounded-full"
              :class="getPriorityBadgeClass(viewingGoal.priority)"
            >
              {{ formatPriority(viewingGoal.priority) }}
            </span>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Goal Information -->
          <div class="bg-gray-50 rounded-lg p-4">
            <h4 class="text-sm font-medium text-gray-900 mb-3">Goal Information</h4>
            <div class="space-y-2">
              <div class="flex justify-between">
                <span class="text-sm text-gray-500">Title:</span>
                <span class="text-sm font-medium text-gray-900">{{ viewingGoal.title }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-500">Employee:</span>
                <span class="text-sm font-medium text-gray-900">
                  {{ viewingGoal.employee.first_name }} {{ viewingGoal.employee.last_name }}
                </span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-500">Due Date:</span>
                <span class="text-sm font-medium text-gray-900">{{ formatDate(viewingGoal.due_date) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-500">Progress:</span>
                <span class="text-sm font-medium text-gray-900">{{ viewingGoal.progress }}%</span>
              </div>
            </div>
          </div>

          <!-- Progress Visualization -->
          <div class="bg-gray-50 rounded-lg p-4">
            <h4 class="text-sm font-medium text-gray-900 mb-3">Progress</h4>
            <div class="w-full bg-gray-200 rounded-full h-4 mb-2">
              <div
                class="h-4 rounded-full transition-all duration-300"
                :class="getProgressBarClass(viewingGoal.progress)"
                :style="{ width: `${viewingGoal.progress}%` }"
              ></div>
            </div>
            <div class="text-center text-2xl font-bold text-gray-900">{{ viewingGoal.progress }}%</div>
          </div>
        </div>

        <!-- Description -->
        <div v-if="viewingGoal.description" class="mt-6">
          <h4 class="text-sm font-medium text-gray-900 mb-3">Description</h4>
          <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-sm text-gray-700">{{ viewingGoal.description }}</p>
          </div>
        </div>

        <!-- Success Criteria -->
        <div v-if="viewingGoal.success_criteria?.length > 0" class="mt-6">
          <h4 class="text-sm font-medium text-gray-900 mb-3">Success Criteria</h4>
          <div class="bg-gray-50 rounded-lg p-4">
            <ul class="space-y-2">
              <li
                v-for="criteria in viewingGoal.success_criteria"
                :key="criteria"
                class="text-sm text-gray-700 flex items-center"
              >
                <CheckCircleIcon class="h-4 w-4 text-green-600 mr-2 flex-shrink-0" />
                {{ criteria }}
              </li>
            </ul>
          </div>
        </div>
      </div>
    </Modal>

    <!-- Update Progress Modal -->
    <Modal :show="showProgressModal" @close="showProgressModal = false">
      <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Update Progress</h3>
        
        <form @submit.prevent="submitProgressUpdate">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Progress (%)
              </label>
              <input
                v-model.number="progressForm.progress"
                type="number"
                min="0"
                max="100"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Enter progress percentage"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Progress Notes
              </label>
              <textarea
                v-model="progressForm.notes"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Add notes about the progress update"
              ></textarea>
            </div>
          </div>

          <div class="flex items-center justify-end space-x-3 mt-6 pt-6 border-t border-gray-200">
            <button
              type="button"
              @click="showProgressModal = false"
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
              Update Progress
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
            <h3 class="text-lg font-medium text-gray-900">Delete Goal</h3>
            <div class="mt-2">
              <p class="text-sm text-gray-500">
                Are you sure you want to delete "{{ goalToDelete?.title }}"? This action cannot be undone.
              </p>
            </div>
          </div>
        </div>
        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
          <button
            type="button"
            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
            @click="deleteGoal"
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
  TargetIcon,
  PlusIcon,
  EyeIcon,
  PencilIcon,
  TrashIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'
import Modal from '@/components/Modal.vue'
import LoadingSpinner from '@/components/LoadingSpinner.vue'

// Types
interface Goal {
  id: number
  employee_id: number
  title: string
  description?: string
  priority: 'low' | 'medium' | 'high' | 'critical'
  status: 'not_started' | 'in_progress' | 'completed' | 'overdue'
  progress: number
  due_date: string
  success_criteria?: string[]
  created_at: string
  employee: {
    id: number
    first_name: string
    last_name: string
    position?: {
      id: number
      title: string
    }
  }
}

interface Employee {
  id: number
  first_name: string
  last_name: string
  position?: {
    id: number
    title: string
  }
}

interface Props {
  goals: Goal[]
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
const filters = reactive({
  employee: '',
  status: '',
  priority: '',
  dueDate: ''
})

const showCreateModal = ref(false)
const showEditModal = ref(false)
const showViewModal = ref(false)
const showProgressModal = ref(false)
const showDeleteModal = ref(false)
const editingGoal = ref<Goal | null>(null)
const viewingGoal = ref<Goal | null>(null)
const goalToDelete = ref<Goal | null>(null)
const updatingGoal = ref<Goal | null>(null)
const processing = ref(false)

const goalForm = reactive({
  employee_id: '',
  title: '',
  description: '',
  priority: '',
  due_date: '',
  success_criteria: [] as string[],
  progress: 0
})

const progressForm = reactive({
  progress: 0,
  notes: ''
})

// Computed
const filteredGoals = computed(() => {
  let filtered = [...props.goals]
  
  if (filters.employee) {
    filtered = filtered.filter(goal => goal.employee_id.toString() === filters.employee)
  }
  
  if (filters.status) {
    filtered = filtered.filter(goal => goal.status === filters.status)
  }
  
  if (filters.priority) {
    filtered = filtered.filter(goal => goal.priority === filters.priority)
  }
  
  if (filters.dueDate) {
    const now = new Date()
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate())
    
    filtered = filtered.filter(goal => {
      const dueDate = new Date(goal.due_date)
      
      switch (filters.dueDate) {
        case 'overdue':
          return dueDate < today && goal.status !== 'completed'
        case 'this_week':
          const weekFromNow = new Date(today.getTime() + 7 * 24 * 60 * 60 * 1000)
          return dueDate >= today && dueDate <= weekFromNow
        case 'this_month':
          const monthFromNow = new Date(today.getFullYear(), today.getMonth() + 1, today.getDate())
          return dueDate >= today && dueDate <= monthFromNow
        case 'next_month':
          const nextMonth = new Date(today.getFullYear(), today.getMonth() + 1, today.getDate())
          const monthAfter = new Date(today.getFullYear(), today.getMonth() + 2, today.getDate())
          return dueDate >= nextMonth && dueDate <= monthAfter
        default:
          return true
      }
    })
  }
  
  return filtered
})

const goalStats = computed(() => {
  const total = props.goals.length
  const completed = props.goals.filter(g => g.status === 'completed').length
  const inProgress = props.goals.filter(g => g.status === 'in_progress').length
  const overdue = props.goals.filter(g => g.status === 'overdue').length
  
  return { total, completed, inProgress, overdue }
})

const hasFilters = computed(() => {
  return !!(filters.employee || filters.status || filters.priority || filters.dueDate)
})

// Methods
const applyFilters = () => {
  // Filters are reactive, so this is just for explicit filter application
}

const viewGoal = (goal: Goal) => {
  viewingGoal.value = goal
  showViewModal.value = true
}

const editGoal = (goal: Goal) => {
  editingGoal.value = goal
  goalForm.employee_id = goal.employee_id.toString()
  goalForm.title = goal.title
  goalForm.description = goal.description || ''
  goalForm.priority = goal.priority
  goalForm.due_date = goal.due_date
  goalForm.success_criteria = [...(goal.success_criteria || [])]
  goalForm.progress = goal.progress
  showEditModal.value = true
}

const confirmDelete = (goal: Goal) => {
  goalToDelete.value = goal
  showDeleteModal.value = true
}

const startGoal = (goal: Goal) => {
  router.put(route('goals.update', goal.id), {
    status: 'in_progress'
  })
}

const updateProgress = (goal: Goal) => {
  updatingGoal.value = goal
  progressForm.progress = goal.progress
  progressForm.notes = ''
  showProgressModal.value = true
}

const completeGoal = (goal: Goal) => {
  router.put(route('goals.update', goal.id), {
    status: 'completed',
    progress: 100
  })
}

const addCriteria = () => {
  goalForm.success_criteria.push('')
}

const removeCriteria = (index: number) => {
  goalForm.success_criteria.splice(index, 1)
}

const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  editingGoal.value = null
  goalForm.employee_id = ''
  goalForm.title = ''
  goalForm.description = ''
  goalForm.priority = ''
  goalForm.due_date = ''
  goalForm.success_criteria = []
  goalForm.progress = 0
}

const submitGoal = () => {
  processing.value = true
  
  const data = {
    employee_id: goalForm.employee_id,
    title: goalForm.title,
    description: goalForm.description || null,
    priority: goalForm.priority,
    due_date: goalForm.due_date,
    success_criteria: goalForm.success_criteria.filter(c => c.trim() !== ''),
    progress: goalForm.progress
  }
  
  if (editingGoal.value) {
    router.put(route('goals.update', editingGoal.value.id), data, {
      onSuccess: () => {
        closeModal()
        processing.value = false
      },
      onError: () => {
        processing.value = false
      }
    })
  } else {
    router.post(route('goals.store'), data, {
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

const submitProgressUpdate = () => {
  if (!updatingGoal.value) return
  
  processing.value = true
  
  router.put(route('goals.update', updatingGoal.value.id), {
    progress: progressForm.progress,
    notes: progressForm.notes
  }, {
    onSuccess: () => {
      showProgressModal.value = false
      updatingGoal.value = null
      progressForm.progress = 0
      progressForm.notes = ''
      processing.value = false
    },
    onError: () => {
      processing.value = false
    }
  })
}

const deleteGoal = () => {
  if (goalToDelete.value) {
    router.delete(route('goals.destroy', goalToDelete.value.id), {
      onSuccess: () => {
        showDeleteModal.value = false
        goalToDelete.value = null
      }
    })
  }
}

const formatStatus = (status: string): string => {
  const statusMap = {
    not_started: 'Not Started',
    in_progress: 'In Progress',
    completed: 'Completed',
    overdue: 'Overdue'
  }
  return statusMap[status as keyof typeof statusMap] || status
}

const formatPriority = (priority: string): string => {
  const priorityMap = {
    low: 'Low',
    medium: 'Medium',
    high: 'High',
    critical: 'Critical'
  }
  return priorityMap[priority as keyof typeof priorityMap] || priority
}

const getStatusBadgeClass = (status: string): string => {
  const classes = {
    not_started: 'bg-gray-100 text-gray-800',
    in_progress: 'bg-blue-100 text-blue-800',
    completed: 'bg-green-100 text-green-800',
    overdue: 'bg-red-100 text-red-800'
  }
  return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-800'
}

const getPriorityBadgeClass = (priority: string): string => {
  const classes = {
    low: 'bg-gray-100 text-gray-800',
    medium: 'bg-yellow-100 text-yellow-800',
    high: 'bg-orange-100 text-orange-800',
    critical: 'bg-red-100 text-red-800'
  }
  return classes[priority as keyof typeof classes] || 'bg-gray-100 text-gray-800'
}

const getProgressBarClass = (progress: number): string => {
  if (progress >= 100) return 'bg-green-500'
  if (progress >= 75) return 'bg-blue-500'
  if (progress >= 50) return 'bg-yellow-500'
  if (progress >= 25) return 'bg-orange-500'
  return 'bg-red-500'
}

const formatDate = (date: string): string => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>