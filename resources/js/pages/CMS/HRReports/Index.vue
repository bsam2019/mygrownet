<template>
  <CMSLayout>
    <div class="p-6 space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">HR Reports & Analytics</h1>
          <p class="text-sm text-gray-500 mt-1">Generate comprehensive HR reports and insights</p>
        </div>
      </div>

      <!-- Report Templates Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="template in templates"
          :key="template.id"
          class="bg-white rounded-lg border border-gray-200 p-6 hover:shadow-md transition cursor-pointer"
          @click="selectTemplate(template)"
        >
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
              <div :class="[
                'w-12 h-12 rounded-lg flex items-center justify-center',
                getCategoryColor(template.category)
              ]">
                <component :is="getCategoryIcon(template.category)" class="h-6 w-6 text-white" aria-hidden="true" />
              </div>
              <div>
                <h3 class="font-semibold text-gray-900">{{ template.name }}</h3>
                <p class="text-xs text-gray-500 capitalize">{{ template.category }}</p>
              </div>
            </div>
          </div>
          <p class="text-sm text-gray-600 mb-4">{{ template.description }}</p>
          <button
            @click.stop="selectTemplate(template)"
            class="w-full px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition"
          >
            Generate Report
          </button>
        </div>
      </div>

      <!-- Recent Reports -->
      <div v-if="savedReports.length > 0" class="bg-white rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">Recent Reports</h2>
        </div>
        <div class="divide-y divide-gray-200">
          <div
            v-for="report in savedReports"
            :key="report.id"
            class="px-6 py-4 hover:bg-gray-50 transition"
          >
            <div class="flex items-center justify-between">
              <div class="flex-1">
                <h4 class="font-medium text-gray-900">{{ report.report_name }}</h4>
                <p class="text-sm text-gray-500 mt-1">
                  Generated {{ formatDate(report.created_at) }}
                  <span v-if="report.date_from && report.date_to">
                    • Period: {{ formatDate(report.date_from) }} to {{ formatDate(report.date_to) }}
                  </span>
                </p>
              </div>
              <div class="flex items-center gap-2">
                <button
                  @click="viewReport(report)"
                  class="px-3 py-1.5 text-sm text-blue-600 hover:bg-blue-50 rounded-lg transition"
                >
                  View
                </button>
                <button
                  @click="deleteReport(report.id)"
                  class="px-3 py-1.5 text-sm text-red-600 hover:bg-red-50 rounded-lg transition"
                >
                  Delete
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Generate Report Modal -->
      <TransitionRoot as="template" :show="showGenerateModal">
        <Dialog as="div" class="relative z-50" @close="showGenerateModal = false">
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0"
            enter-to="opacity-100"
            leave="ease-in duration-200"
            leave-from="opacity-100"
            leave-to="opacity-0"
          >
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
          </TransitionChild>

          <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
              <TransitionChild
                as="template"
                enter="ease-out duration-300"
                enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                enter-to="opacity-100 translate-y-0 sm:scale-100"
                leave="ease-in duration-200"
                leave-from="opacity-100 translate-y-0 sm:scale-100"
                leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
              >
                <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                  <div>
                    <div class="mb-4">
                      <DialogTitle as="h3" class="text-lg font-semibold text-gray-900">
                        Generate {{ selectedTemplate?.name }}
                      </DialogTitle>
                      <p class="text-sm text-gray-500 mt-1">{{ selectedTemplate?.description }}</p>
                    </div>

                    <form @submit.prevent="generateReport" class="space-y-4">
                      <!-- Date Range -->
                      <div class="grid grid-cols-2 gap-4">
                        <div>
                          <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                          <input
                            v-model="reportFilters.date_from"
                            type="date"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                          />
                        </div>
                        <div>
                          <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                          <input
                            v-model="reportFilters.date_to"
                            type="date"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                          />
                        </div>
                      </div>

                      <!-- Department Filter -->
                      <div v-if="['headcount', 'attendance', 'leave', 'performance'].includes(selectedTemplate?.category)">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Department (Optional)</label>
                        <select
                          v-model="reportFilters.filters.department_id"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                          <option :value="null">All Departments</option>
                          <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                            {{ dept.name }}
                          </option>
                        </select>
                      </div>

                      <!-- Worker Filter -->
                      <div v-if="['attendance', 'leave', 'performance', 'training'].includes(selectedTemplate?.category)">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Employee (Optional)</label>
                        <select
                          v-model="reportFilters.filters.worker_id"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                          <option :value="null">All Employees</option>
                          <option v-for="worker in workers" :key="worker.id" :value="worker.id">
                            {{ worker.name }}
                          </option>
                        </select>
                      </div>

                      <!-- Leave Type Filter -->
                      <div v-if="selectedTemplate?.category === 'leave'">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Leave Type (Optional)</label>
                        <select
                          v-model="reportFilters.filters.leave_type_id"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                          <option :value="null">All Leave Types</option>
                          <option v-for="type in leaveTypes" :key="type.id" :value="type.id">
                            {{ type.name }}
                          </option>
                        </select>
                      </div>

                      <div class="flex gap-3 mt-6">
                        <button
                          type="button"
                          @click="showGenerateModal = false"
                          class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
                        >
                          Cancel
                        </button>
                        <button
                          type="submit"
                          :disabled="generating"
                          class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition disabled:opacity-50"
                        >
                          {{ generating ? 'Generating...' : 'Generate' }}
                        </button>
                      </div>
                    </form>
                  </div>
                </DialogPanel>
              </TransitionChild>
            </div>
          </div>
        </Dialog>
      </TransitionRoot>

      <!-- Report Results Modal -->
      <TransitionRoot as="template" :show="showResultsModal">
        <Dialog as="div" class="relative z-50" @close="showResultsModal = false">
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0"
            enter-to="opacity-100"
            leave="ease-in duration-200"
            leave-from="opacity-100"
            leave-to="opacity-0"
          >
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
          </TransitionChild>

          <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
              <TransitionChild
                as="template"
                enter="ease-out duration-300"
                enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                enter-to="opacity-100 translate-y-0 sm:scale-100"
                leave="ease-in duration-200"
                leave-from="opacity-100 translate-y-0 sm:scale-100"
                leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
              >
                <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl sm:p-6">
                  <div>
                    <div class="mb-6">
                      <DialogTitle as="h3" class="text-lg font-semibold text-gray-900">
                        Report Results
                      </DialogTitle>
                    </div>

                    <div v-if="reportResults" class="space-y-6">
                      <!-- Statistics Cards -->
                      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div
                          v-for="(value, key) in reportResults.stats"
                          :key="key"
                          class="bg-gray-50 rounded-lg p-4"
                        >
                          <p class="text-sm text-gray-600 capitalize">{{ formatStatKey(key) }}</p>
                          <p class="text-2xl font-bold text-gray-900 mt-1">{{ formatStatValue(value) }}</p>
                        </div>
                      </div>

                      <!-- Data Table -->
                      <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <div class="overflow-x-auto max-h-96">
                          <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                              <tr>
                                <th
                                  v-for="(value, key) in getFirstRecord()"
                                  :key="key"
                                  class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                  {{ formatStatKey(key) }}
                                </th>
                              </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                              <tr v-for="(record, index) in getRecords()" :key="index">
                                <td
                                  v-for="(value, key) in record"
                                  :key="key"
                                  class="px-4 py-3 text-sm text-gray-900"
                                >
                                  {{ value }}
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                      <button
                        @click="showResultsModal = false"
                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
                      >
                        Close
                      </button>
                    </div>
                  </div>
                </DialogPanel>
              </TransitionChild>
            </div>
          </div>
        </Dialog>
      </TransitionRoot>
    </div>
  </CMSLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import CMSLayout from '@/Layouts/CMSLayout.vue'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import {
  UsersIcon,
  ClipboardDocumentCheckIcon,
  CalendarDaysIcon,
  BanknotesIcon,
  ChartBarIcon,
  AcademicCapIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps<{
  templates: any[]
  savedReports: any[]
  departments: any[]
  workers: any[]
  leaveTypes: any[]
}>()

const showGenerateModal = ref(false)
const showResultsModal = ref(false)
const selectedTemplate = ref<any>(null)
const generating = ref(false)
const reportResults = ref<any>(null)

const reportFilters = ref({
  date_from: '',
  date_to: '',
  filters: {
    department_id: null,
    worker_id: null,
    leave_type_id: null,
  },
})

const selectTemplate = (template: any) => {
  selectedTemplate.value = template
  reportFilters.value = {
    date_from: '',
    date_to: '',
    filters: {
      department_id: null,
      worker_id: null,
      leave_type_id: null,
    },
  }
  showGenerateModal.value = true
}

const generateReport = async () => {
  generating.value = true
  
  try {
    const response = await fetch(route('cms.hr-reports.generate'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify({
        template_id: selectedTemplate.value.id,
        date_from: reportFilters.value.date_from || null,
        date_to: reportFilters.value.date_to || null,
        filters: reportFilters.value.filters,
      }),
    })

    const data = await response.json()
    
    if (data.success) {
      reportResults.value = data.report
      showGenerateModal.value = false
      showResultsModal.value = true
      router.reload({ only: ['savedReports'] })
    }
  } catch (error) {
    console.error('Error generating report:', error)
  } finally {
    generating.value = false
  }
}

const viewReport = (report: any) => {
  // Implement view logic
  console.log('View report:', report)
}

const deleteReport = async (id: number) => {
  if (!confirm('Are you sure you want to delete this report?')) return

  try {
    await fetch(route('cms.hr-reports.destroy', id), {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
    })
    router.reload({ only: ['savedReports'] })
  } catch (error) {
    console.error('Error deleting report:', error)
  }
}

const getCategoryIcon = (category: string) => {
  const icons: Record<string, any> = {
    headcount: UsersIcon,
    attendance: ClipboardDocumentCheckIcon,
    leave: CalendarDaysIcon,
    payroll: BanknotesIcon,
    performance: ChartBarIcon,
    training: AcademicCapIcon,
  }
  return icons[category] || ChartBarIcon
}

const getCategoryColor = (category: string) => {
  const colors: Record<string, string> = {
    headcount: 'bg-blue-600',
    attendance: 'bg-green-600',
    leave: 'bg-purple-600',
    payroll: 'bg-emerald-600',
    performance: 'bg-orange-600',
    training: 'bg-indigo-600',
  }
  return colors[category] || 'bg-gray-600'
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const formatStatKey = (key: string) => {
  return key.replace(/_/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase())
}

const formatStatValue = (value: any) => {
  if (typeof value === 'number') {
    return value.toLocaleString()
  }
  if (typeof value === 'object') {
    return JSON.stringify(value)
  }
  return value
}

const getFirstRecord = () => {
  const records = getRecords()
  return records.length > 0 ? records[0] : {}
}

const getRecords = () => {
  if (!reportResults.value) return []
  
  // Find the array property in the report results
  const keys = Object.keys(reportResults.value).filter(k => Array.isArray(reportResults.value[k]))
  return keys.length > 0 ? reportResults.value[keys[0]] : []
}
</script>
