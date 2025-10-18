<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-100">
    <!-- Header -->
    <div class="p-6 border-b border-gray-100">
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <div class="p-2 bg-gradient-to-r from-green-500 to-green-600 rounded-lg mr-3">
            <ClipboardDocumentCheckIcon class="h-6 w-6 text-white" />
          </div>
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Performance Reviews</h3>
            <p class="text-sm text-gray-500">Manage employee performance evaluations</p>
          </div>
        </div>
        <div class="flex items-center space-x-3">
          <button
            v-if="canCreate"
            @click="showCreateModal = true"
            class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors"
          >
            <PlusIcon class="h-4 w-4 mr-2" />
            New Review
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
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm"
            @change="applyFilters"
          >
            <option value="">All Employees</option>
            <option v-for="emp in employees" :key="emp.id" :value="emp.id">
              {{ emp.first_name }} {{ emp.last_name }}
            </option>
          </select>
        </div>

        <!-- Period Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Review Period</label>
          <select
            v-model="filters.period"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm"
            @change="applyFilters"
          >
            <option value="">All Periods</option>
            <option value="Q1">Q1</option>
            <option value="Q2">Q2</option>
            <option value="Q3">Q3</option>
            <option value="Q4">Q4</option>
            <option value="Annual">Annual</option>
          </select>
        </div>

        <!-- Status Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select
            v-model="filters.status"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm"
            @change="applyFilters"
          >
            <option value="">All Statuses</option>
            <option value="draft">Draft</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
            <option value="approved">Approved</option>
          </select>
        </div>

        <!-- Year Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Year</label>
          <select
            v-model="filters.year"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm"
            @change="applyFilters"
          >
            <option value="">All Years</option>
            <option v-for="year in availableYears" :key="year" :value="year">
              {{ year }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <!-- Reviews List -->
    <div class="p-6">
      <div v-if="filteredReviews.length === 0" class="text-center py-12">
        <ClipboardDocumentCheckIcon class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">No reviews found</h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ hasFilters ? 'Try adjusting your search criteria.' : 'Get started by creating your first performance review.' }}
        </p>
        <div v-if="!hasFilters && canCreate" class="mt-6">
          <button
            @click="showCreateModal = true"
            class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors"
          >
            <PlusIcon class="h-4 w-4 mr-2" />
            New Review
          </button>
        </div>
      </div>

      <div v-else class="space-y-4">
        <div
          v-for="review in filteredReviews"
          :key="review.id"
          class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow"
        >
          <!-- Review Header -->
          <div class="flex items-start justify-between mb-4">
            <div class="flex-1">
              <div class="flex items-center mb-2">
                <h4 class="text-lg font-medium text-gray-900 mr-3">
                  {{ review.employee.first_name }} {{ review.employee.last_name }}
                </h4>
                <span
                  class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                  :class="getStatusBadgeClass(review.status)"
                >
                  {{ formatStatus(review.status) }}
                </span>
              </div>
              <p class="text-sm text-gray-600">{{ review.employee.position?.title }}</p>
              <p class="text-sm text-gray-500">{{ review.review_period }} {{ review.review_year }}</p>
            </div>
            <div class="flex items-center space-x-2 ml-4">
              <button
                @click="viewReview(review)"
                class="p-2 text-gray-400 hover:text-blue-600 transition-colors"
                title="View Review"
              >
                <EyeIcon class="h-4 w-4" />
              </button>
              <button
                v-if="canEdit && review.status !== 'approved'"
                @click="editReview(review)"
                class="p-2 text-gray-400 hover:text-indigo-600 transition-colors"
                title="Edit Review"
              >
                <PencilIcon class="h-4 w-4" />
              </button>
              <button
                v-if="canDelete && review.status === 'draft'"
                @click="confirmDelete(review)"
                class="p-2 text-gray-400 hover:text-red-600 transition-colors"
                title="Delete Review"
              >
                <TrashIcon class="h-4 w-4" />
              </button>
            </div>
          </div>

          <!-- Review Metrics -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
            <div class="text-center">
              <div class="text-2xl font-bold text-gray-900">{{ review.overall_score || 'N/A' }}</div>
              <div class="text-xs text-gray-500">Overall Score</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-blue-600">{{ review.goals_completed || 0 }}</div>
              <div class="text-xs text-gray-500">Goals Completed</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-green-600">{{ review.strengths_count || 0 }}</div>
              <div class="text-xs text-gray-500">Strengths</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-amber-600">{{ review.improvement_areas_count || 0 }}</div>
              <div class="text-xs text-gray-500">Improvements</div>
            </div>
          </div>

          <!-- Review Summary -->
          <div v-if="review.summary" class="bg-gray-50 rounded-lg p-4">
            <h5 class="text-sm font-medium text-gray-900 mb-2">Summary</h5>
            <p class="text-sm text-gray-600 line-clamp-3">{{ review.summary }}</p>
          </div>

          <!-- Review Actions -->
          <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-200">
            <div class="text-sm text-gray-500">
              Reviewed by {{ review.reviewer?.first_name }} {{ review.reviewer?.last_name }}
              <span v-if="review.review_date">on {{ formatDate(review.review_date) }}</span>
            </div>
            <div class="flex items-center space-x-2">
              <button
                v-if="review.status === 'completed' && canApprove"
                @click="approveReview(review)"
                class="px-3 py-1 text-sm font-medium text-green-700 bg-green-100 rounded-lg hover:bg-green-200 transition-colors"
              >
                Approve
              </button>
              <button
                v-if="review.status === 'draft' && canEdit"
                @click="startReview(review)"
                class="px-3 py-1 text-sm font-medium text-blue-700 bg-blue-100 rounded-lg hover:bg-blue-200 transition-colors"
              >
                Start Review
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Review Modal -->
    <Modal :show="showCreateModal || showEditModal" @close="closeModal" max-width="4xl">
      <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-6">
          {{ editingReview ? 'Edit Performance Review' : 'Create Performance Review' }}
        </h3>
        
        <form @submit.prevent="submitReview">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Left Column -->
            <div class="space-y-4">
              <!-- Employee -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Employee <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="reviewForm.employee_id"
                  required
                  :disabled="editingReview"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  :class="{ 'border-red-500': errors.employee_id }"
                >
                  <option value="">Select Employee</option>
                  <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                    {{ emp.first_name }} {{ emp.last_name }} - {{ emp.position?.title }}
                  </option>
                </select>
                <p v-if="errors.employee_id" class="mt-1 text-sm text-red-600">{{ errors.employee_id }}</p>
              </div>

              <!-- Review Period -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Review Period <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="reviewForm.review_period"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  :class="{ 'border-red-500': errors.review_period }"
                >
                  <option value="">Select Period</option>
                  <option value="Q1">Q1 (Jan-Mar)</option>
                  <option value="Q2">Q2 (Apr-Jun)</option>
                  <option value="Q3">Q3 (Jul-Sep)</option>
                  <option value="Q4">Q4 (Oct-Dec)</option>
                  <option value="Annual">Annual</option>
                </select>
                <p v-if="errors.review_period" class="mt-1 text-sm text-red-600">{{ errors.review_period }}</p>
              </div>

              <!-- Review Year -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Review Year <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="reviewForm.review_year"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  :class="{ 'border-red-500': errors.review_year }"
                >
                  <option value="">Select Year</option>
                  <option v-for="year in availableYears" :key="year" :value="year">
                    {{ year }}
                  </option>
                </select>
                <p v-if="errors.review_year" class="mt-1 text-sm text-red-600">{{ errors.review_year }}</p>
              </div>

              <!-- Overall Score -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Overall Score (1-10)
                </label>
                <input
                  v-model.number="reviewForm.overall_score"
                  type="number"
                  min="1"
                  max="10"
                  step="0.1"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  :class="{ 'border-red-500': errors.overall_score }"
                  placeholder="Enter score (1-10)"
                />
                <p v-if="errors.overall_score" class="mt-1 text-sm text-red-600">{{ errors.overall_score }}</p>
              </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-4">
              <!-- Summary -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Review Summary
                </label>
                <textarea
                  v-model="reviewForm.summary"
                  rows="4"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  :class="{ 'border-red-500': errors.summary }"
                  placeholder="Enter review summary"
                ></textarea>
                <p v-if="errors.summary" class="mt-1 text-sm text-red-600">{{ errors.summary }}</p>
              </div>

              <!-- Strengths -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Strengths
                </label>
                <div class="space-y-2">
                  <div
                    v-for="(strength, index) in reviewForm.strengths"
                    :key="index"
                    class="flex items-center space-x-2"
                  >
                    <input
                      v-model="reviewForm.strengths[index]"
                      type="text"
                      class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                      placeholder="Enter strength"
                    />
                    <button
                      type="button"
                      @click="removeStrength(index)"
                      class="p-2 text-red-600 hover:text-red-800 transition-colors"
                    >
                      <TrashIcon class="h-4 w-4" />
                    </button>
                  </div>
                  <button
                    type="button"
                    @click="addStrength"
                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-green-600 bg-green-50 rounded-lg hover:bg-green-100 transition-colors"
                  >
                    <PlusIcon class="h-4 w-4 mr-2" />
                    Add Strength
                  </button>
                </div>
              </div>

              <!-- Improvement Areas -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Areas for Improvement
                </label>
                <div class="space-y-2">
                  <div
                    v-for="(area, index) in reviewForm.improvement_areas"
                    :key="index"
                    class="flex items-center space-x-2"
                  >
                    <input
                      v-model="reviewForm.improvement_areas[index]"
                      type="text"
                      class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                      placeholder="Enter improvement area"
                    />
                    <button
                      type="button"
                      @click="removeImprovementArea(index)"
                      class="p-2 text-red-600 hover:text-red-800 transition-colors"
                    >
                      <TrashIcon class="h-4 w-4" />
                    </button>
                  </div>
                  <button
                    type="button"
                    @click="addImprovementArea"
                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-amber-600 bg-amber-50 rounded-lg hover:bg-amber-100 transition-colors"
                  >
                    <PlusIcon class="h-4 w-4 mr-2" />
                    Add Improvement Area
                  </button>
                </div>
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
              class="px-6 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
            >
              <LoadingSpinner v-if="processing" class="w-4 h-4 mr-2" />
              {{ editingReview ? 'Update Review' : 'Create Review' }}
            </button>
          </div>
        </form>
      </div>
    </Modal>

    <!-- View Review Modal -->
    <Modal :show="showViewModal" @close="showViewModal = false" max-width="4xl">
      <div v-if="viewingReview" class="p-6">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-lg font-medium text-gray-900">Performance Review Details</h3>
          <span
            class="inline-flex px-3 py-1 text-sm font-semibold rounded-full"
            :class="getStatusBadgeClass(viewingReview.status)"
          >
            {{ formatStatus(viewingReview.status) }}
          </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Employee Information -->
          <div class="bg-gray-50 rounded-lg p-4">
            <h4 class="text-sm font-medium text-gray-900 mb-3">Employee Information</h4>
            <div class="space-y-2">
              <div class="flex justify-between">
                <span class="text-sm text-gray-500">Name:</span>
                <span class="text-sm font-medium text-gray-900">
                  {{ viewingReview.employee.first_name }} {{ viewingReview.employee.last_name }}
                </span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-500">Position:</span>
                <span class="text-sm font-medium text-gray-900">{{ viewingReview.employee.position?.title }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-500">Department:</span>
                <span class="text-sm font-medium text-gray-900">{{ viewingReview.employee.department?.name }}</span>
              </div>
            </div>
          </div>

          <!-- Review Information -->
          <div class="bg-gray-50 rounded-lg p-4">
            <h4 class="text-sm font-medium text-gray-900 mb-3">Review Information</h4>
            <div class="space-y-2">
              <div class="flex justify-between">
                <span class="text-sm text-gray-500">Period:</span>
                <span class="text-sm font-medium text-gray-900">{{ viewingReview.review_period }} {{ viewingReview.review_year }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-500">Overall Score:</span>
                <span class="text-sm font-medium text-gray-900">{{ viewingReview.overall_score || 'N/A' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-500">Reviewer:</span>
                <span class="text-sm font-medium text-gray-900">
                  {{ viewingReview.reviewer?.first_name }} {{ viewingReview.reviewer?.last_name }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Summary -->
        <div v-if="viewingReview.summary" class="mt-6">
          <h4 class="text-sm font-medium text-gray-900 mb-3">Summary</h4>
          <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-sm text-gray-700">{{ viewingReview.summary }}</p>
          </div>
        </div>

        <!-- Strengths and Improvements -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
          <!-- Strengths -->
          <div v-if="viewingReview.strengths?.length > 0">
            <h4 class="text-sm font-medium text-gray-900 mb-3">Strengths</h4>
            <div class="bg-green-50 rounded-lg p-4">
              <ul class="space-y-2">
                <li
                  v-for="strength in viewingReview.strengths"
                  :key="strength"
                  class="text-sm text-green-800 flex items-center"
                >
                  <CheckCircleIcon class="h-4 w-4 text-green-600 mr-2 flex-shrink-0" />
                  {{ strength }}
                </li>
              </ul>
            </div>
          </div>

          <!-- Improvement Areas -->
          <div v-if="viewingReview.improvement_areas?.length > 0">
            <h4 class="text-sm font-medium text-gray-900 mb-3">Areas for Improvement</h4>
            <div class="bg-amber-50 rounded-lg p-4">
              <ul class="space-y-2">
                <li
                  v-for="area in viewingReview.improvement_areas"
                  :key="area"
                  class="text-sm text-amber-800 flex items-center"
                >
                  <ExclamationTriangleIcon class="h-4 w-4 text-amber-600 mr-2 flex-shrink-0" />
                  {{ area }}
                </li>
              </ul>
            </div>
          </div>
        </div>
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
            <h3 class="text-lg font-medium text-gray-900">Delete Performance Review</h3>
            <div class="mt-2">
              <p class="text-sm text-gray-500">
                Are you sure you want to delete this performance review? This action cannot be undone.
              </p>
            </div>
          </div>
        </div>
        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
          <button
            type="button"
            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
            @click="deleteReview"
          >
            Delete
          </button>
          <button
            type="button"
            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:mt-0 sm:w-auto sm:text-sm"
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
  ClipboardDocumentCheckIcon,
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
interface PerformanceReview {
  id: number
  employee_id: number
  reviewer_id: number
  review_period: string
  review_year: number
  overall_score?: number
  summary?: string
  strengths?: string[]
  improvement_areas?: string[]
  status: 'draft' | 'in_progress' | 'completed' | 'approved'
  review_date?: string
  goals_completed?: number
  strengths_count?: number
  improvement_areas_count?: number
  employee: {
    id: number
    first_name: string
    last_name: string
    position?: {
      id: number
      title: string
    }
    department?: {
      id: number
      name: string
    }
  }
  reviewer?: {
    id: number
    first_name: string
    last_name: string
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
  reviews: PerformanceReview[]
  employees: Employee[]
  canCreate?: boolean
  canEdit?: boolean
  canDelete?: boolean
  canApprove?: boolean
  errors?: Record<string, string>
}

const props = withDefaults(defineProps<Props>(), {
  canCreate: false,
  canEdit: false,
  canDelete: false,
  canApprove: false,
  errors: () => ({})
})

// Reactive data
const filters = reactive({
  employee: '',
  period: '',
  status: '',
  year: ''
})

const showCreateModal = ref(false)
const showEditModal = ref(false)
const showViewModal = ref(false)
const showDeleteModal = ref(false)
const editingReview = ref<PerformanceReview | null>(null)
const viewingReview = ref<PerformanceReview | null>(null)
const reviewToDelete = ref<PerformanceReview | null>(null)
const processing = ref(false)

const reviewForm = reactive({
  employee_id: '',
  review_period: '',
  review_year: '',
  overall_score: null as number | null,
  summary: '',
  strengths: [] as string[],
  improvement_areas: [] as string[]
})

// Computed
const availableYears = computed(() => {
  const currentYear = new Date().getFullYear()
  const years = []
  for (let i = currentYear; i >= currentYear - 5; i--) {
    years.push(i)
  }
  return years
})

const filteredReviews = computed(() => {
  let filtered = [...props.reviews]
  
  if (filters.employee) {
    filtered = filtered.filter(review => review.employee_id.toString() === filters.employee)
  }
  
  if (filters.period) {
    filtered = filtered.filter(review => review.review_period === filters.period)
  }
  
  if (filters.status) {
    filtered = filtered.filter(review => review.status === filters.status)
  }
  
  if (filters.year) {
    filtered = filtered.filter(review => review.review_year.toString() === filters.year)
  }
  
  return filtered
})

const hasFilters = computed(() => {
  return !!(filters.employee || filters.period || filters.status || filters.year)
})

// Methods
const applyFilters = () => {
  // Filters are reactive, so this is just for explicit filter application
}

const viewReview = (review: PerformanceReview) => {
  viewingReview.value = review
  showViewModal.value = true
}

const editReview = (review: PerformanceReview) => {
  editingReview.value = review
  reviewForm.employee_id = review.employee_id.toString()
  reviewForm.review_period = review.review_period
  reviewForm.review_year = review.review_year.toString()
  reviewForm.overall_score = review.overall_score || null
  reviewForm.summary = review.summary || ''
  reviewForm.strengths = [...(review.strengths || [])]
  reviewForm.improvement_areas = [...(review.improvement_areas || [])]
  showEditModal.value = true
}

const confirmDelete = (review: PerformanceReview) => {
  reviewToDelete.value = review
  showDeleteModal.value = true
}

const startReview = (review: PerformanceReview) => {
  router.put(route('performance.reviews.update', review.id), {
    status: 'in_progress'
  })
}

const approveReview = (review: PerformanceReview) => {
  router.put(route('performance.reviews.update', review.id), {
    status: 'approved'
  })
}

const addStrength = () => {
  reviewForm.strengths.push('')
}

const removeStrength = (index: number) => {
  reviewForm.strengths.splice(index, 1)
}

const addImprovementArea = () => {
  reviewForm.improvement_areas.push('')
}

const removeImprovementArea = (index: number) => {
  reviewForm.improvement_areas.splice(index, 1)
}

const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  editingReview.value = null
  reviewForm.employee_id = ''
  reviewForm.review_period = ''
  reviewForm.review_year = ''
  reviewForm.overall_score = null
  reviewForm.summary = ''
  reviewForm.strengths = []
  reviewForm.improvement_areas = []
}

const submitReview = () => {
  processing.value = true
  
  const data = {
    employee_id: reviewForm.employee_id,
    review_period: reviewForm.review_period,
    review_year: parseInt(reviewForm.review_year),
    overall_score: reviewForm.overall_score,
    summary: reviewForm.summary || null,
    strengths: reviewForm.strengths.filter(s => s.trim() !== ''),
    improvement_areas: reviewForm.improvement_areas.filter(a => a.trim() !== '')
  }
  
  if (editingReview.value) {
    router.put(route('performance.reviews.update', editingReview.value.id), data, {
      onSuccess: () => {
        closeModal()
        processing.value = false
      },
      onError: () => {
        processing.value = false
      }
    })
  } else {
    router.post(route('performance.reviews.store'), data, {
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

const deleteReview = () => {
  if (reviewToDelete.value) {
    router.delete(route('performance.reviews.destroy', reviewToDelete.value.id), {
      onSuccess: () => {
        showDeleteModal.value = false
        reviewToDelete.value = null
      }
    })
  }
}

const formatStatus = (status: string): string => {
  const statusMap = {
    draft: 'Draft',
    in_progress: 'In Progress',
    completed: 'Completed',
    approved: 'Approved'
  }
  return statusMap[status as keyof typeof statusMap] || status
}

const getStatusBadgeClass = (status: string): string => {
  const classes = {
    draft: 'bg-gray-100 text-gray-800',
    in_progress: 'bg-blue-100 text-blue-800',
    completed: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800'
  }
  return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-800'
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
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>