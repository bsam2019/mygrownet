<script setup lang="ts">
import { ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { CheckIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import CMSLayout from '@/Layouts/CMSLayout.vue'

defineOptions({
  layout: CMSLayout
})

interface Props {
  worker: any
  attendance: any
  commissions: any
}

const props = defineProps<Props>()

const showAttendanceModal = ref(false)
const attendanceForm = useForm({
  worker_id: props.worker.id,
  job_id: null,
  work_date: new Date().toISOString().split('T')[0],
  hours_worked: 0,
  days_worked: 0,
  work_description: '',
})

const submitAttendance = () => {
  attendanceForm.post(route('cms.payroll.attendance.store'), {
    onSuccess: () => {
      showAttendanceModal.value = false
      attendanceForm.reset()
    }
  })
}

const approveAttendance = (attendanceId: number) => {
  useForm({}).post(route('cms.payroll.attendance.approve', attendanceId))
}

const formatNumber = (value: number) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value)
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const getStatusClass = (status: string) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    paid: 'bg-blue-100 text-blue-800',
  }
  return classes[status as keyof typeof classes] || classes.pending
}
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
      <Link :href="route('cms.payroll.workers.index')" class="text-sm text-blue-600 hover:text-blue-800 mb-2 inline-block">
        ← Back to Workers
      </Link>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">{{ worker.name }}</h1>
          <p class="text-sm text-gray-500">{{ worker.worker_number }}</p>
        </div>
        <button
          @click="showAttendanceModal = true"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium"
        >
          Record Attendance
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 space-y-6">
        <!-- Attendance History -->
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Attendance History</h2>
          <div v-if="attendance.data.length > 0" class="space-y-3">
            <div
              v-for="record in attendance.data"
              :key="record.id"
              class="p-4 bg-gray-50 rounded-lg border border-gray-200"
            >
              <div class="flex justify-between items-start">
                <div class="flex-1">
                  <div class="flex items-center gap-3">
                    <p class="font-medium text-gray-900">{{ formatDate(record.work_date) }}</p>
                    <span :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusClass(record.status)]">
                      {{ record.status.toUpperCase() }}
                    </span>
                  </div>
                  <div class="mt-2 text-sm text-gray-600">
                    <span v-if="record.hours_worked">{{ record.hours_worked }} hours</span>
                    <span v-if="record.days_worked">{{ record.days_worked }} days</span>
                    <span class="ml-2 font-medium">K{{ formatNumber(record.amount_earned) }}</span>
                  </div>
                  <p v-if="record.work_description" class="mt-1 text-sm text-gray-500">
                    {{ record.work_description }}
                  </p>
                  <p v-if="record.job" class="mt-1 text-sm text-blue-600">
                    Job: {{ record.job.job_number }}
                  </p>
                </div>
                <button
                  v-if="record.status === 'pending'"
                  @click="approveAttendance(record.id)"
                  class="ml-4 p-2 text-green-600 hover:bg-green-50 rounded"
                  title="Approve"
                >
                  <CheckIcon class="h-5 w-5" aria-hidden="true" />
                </button>
              </div>
            </div>
          </div>
          <p v-else class="text-sm text-gray-500">No attendance records</p>
        </div>

        <!-- Commissions -->
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Commissions</h2>
          <div v-if="commissions.data.length > 0" class="space-y-3">
            <div
              v-for="commission in commissions.data"
              :key="commission.id"
              class="p-4 bg-gray-50 rounded-lg border border-gray-200"
            >
              <div class="flex justify-between items-start">
                <div>
                  <p class="font-medium text-gray-900 capitalize">{{ commission.commission_type }}</p>
                  <p class="text-sm text-gray-600">
                    {{ commission.commission_rate }}% of K{{ formatNumber(commission.base_amount) }}
                  </p>
                  <p class="text-sm font-medium text-green-600 mt-1">
                    K{{ formatNumber(commission.commission_amount) }}
                  </p>
                  <p v-if="commission.description" class="text-sm text-gray-500 mt-1">
                    {{ commission.description }}
                  </p>
                </div>
                <span :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusClass(commission.status)]">
                  {{ commission.status.toUpperCase() }}
                </span>
              </div>
            </div>
          </div>
          <p v-else class="text-sm text-gray-500">No commission records</p>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Worker Details -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-sm font-semibold text-gray-900 mb-4">Personal Information</h3>
          <div class="space-y-3">
            <div>
              <label class="text-sm font-medium text-gray-500">Full Name</label>
              <p class="text-gray-900">{{ worker.first_name }} {{ worker.middle_name }} {{ worker.last_name }}</p>
            </div>
            <div v-if="worker.date_of_birth">
              <label class="text-sm font-medium text-gray-500">Date of Birth</label>
              <p class="text-gray-900">{{ formatDate(worker.date_of_birth) }}</p>
            </div>
            <div v-if="worker.gender">
              <label class="text-sm font-medium text-gray-500">Gender</label>
              <p class="text-gray-900 capitalize">{{ worker.gender }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Phone</label>
              <p class="text-gray-900">{{ worker.phone }}</p>
            </div>
            <div v-if="worker.email">
              <label class="text-sm font-medium text-gray-500">Email</label>
              <p class="text-gray-900">{{ worker.email }}</p>
            </div>
            <div v-if="worker.address">
              <label class="text-sm font-medium text-gray-500">Address</label>
              <p class="text-gray-900">{{ worker.address }}</p>
              <p v-if="worker.city || worker.province" class="text-sm text-gray-600">
                {{ worker.city }}{{ worker.city && worker.province ? ', ' : '' }}{{ worker.province }}
              </p>
            </div>
          </div>
        </div>

        <!-- Employment Details -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-sm font-semibold text-gray-900 mb-4">Employment Details</h3>
          <div class="space-y-3">
            <div v-if="worker.job_title">
              <label class="text-sm font-medium text-gray-500">Job Title</label>
              <p class="text-gray-900">{{ worker.job_title }}</p>
            </div>
            <div v-if="worker.department">
              <label class="text-sm font-medium text-gray-500">Department</label>
              <p class="text-gray-900">{{ worker.department.department_name }}</p>
            </div>
            <div v-if="worker.hire_date">
              <label class="text-sm font-medium text-gray-500">Hire Date</label>
              <p class="text-gray-900">{{ formatDate(worker.hire_date) }}</p>
            </div>
            <div v-if="worker.employment_type">
              <label class="text-sm font-medium text-gray-500">Employment Type</label>
              <p class="text-gray-900 capitalize">{{ worker.employment_type.replace('_', ' ') }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Worker Type</label>
              <p class="text-gray-900 capitalize">{{ worker.worker_type }}</p>
            </div>
            <div v-if="worker.employment_status">
              <label class="text-sm font-medium text-gray-500">Status</label>
              <p class="text-gray-900 capitalize">{{ worker.employment_status }}</p>
            </div>
          </div>
        </div>

        <!-- Emergency Contact -->
        <div v-if="worker.emergency_contact_name" class="bg-white rounded-lg shadow p-6">
          <h3 class="text-sm font-semibold text-gray-900 mb-4">Emergency Contact</h3>
          <div class="space-y-3">
            <div>
              <label class="text-sm font-medium text-gray-500">Name</label>
              <p class="text-gray-900">{{ worker.emergency_contact_name }}</p>
            </div>
            <div v-if="worker.emergency_contact_phone">
              <label class="text-sm font-medium text-gray-500">Phone</label>
              <p class="text-gray-900">{{ worker.emergency_contact_phone }}</p>
            </div>
            <div v-if="worker.emergency_contact_relationship">
              <label class="text-sm font-medium text-gray-500">Relationship</label>
              <p class="text-gray-900">{{ worker.emergency_contact_relationship }}</p>
            </div>
          </div>
        </div>

        <!-- Rates -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-sm font-semibold text-gray-900 mb-4">Compensation</h3>
          <div class="space-y-3">
            <div v-if="worker.monthly_salary">
              <label class="text-sm font-medium text-gray-500">Monthly Salary</label>
              <p class="text-gray-900 font-semibold">{{ worker.salary_currency || 'K' }}{{ formatNumber(worker.monthly_salary) }}</p>
            </div>
            <div v-if="worker.hourly_rate">
              <label class="text-sm font-medium text-gray-500">Hourly Rate</label>
              <p class="text-gray-900 font-semibold">K{{ formatNumber(worker.hourly_rate) }}</p>
            </div>
            <div v-if="worker.daily_rate">
              <label class="text-sm font-medium text-gray-500">Daily Rate</label>
              <p class="text-gray-900 font-semibold">K{{ formatNumber(worker.daily_rate) }}</p>
            </div>
            <div v-if="worker.commission_rate">
              <label class="text-sm font-medium text-gray-500">Commission Rate</label>
              <p class="text-gray-900 font-semibold">{{ worker.commission_rate }}%</p>
            </div>
          </div>
        </div>

        <!-- Tax & Benefits -->
        <div v-if="worker.tax_number || worker.napsa_number || worker.nhima_number" class="bg-white rounded-lg shadow p-6">
          <h3 class="text-sm font-semibold text-gray-900 mb-4">Tax & Benefits</h3>
          <div class="space-y-3">
            <div v-if="worker.tax_number">
              <label class="text-sm font-medium text-gray-500">TPIN</label>
              <p class="text-gray-900">{{ worker.tax_number }}</p>
            </div>
            <div v-if="worker.napsa_number">
              <label class="text-sm font-medium text-gray-500">NAPSA Number</label>
              <p class="text-gray-900">{{ worker.napsa_number }}</p>
            </div>
            <div v-if="worker.nhima_number">
              <label class="text-sm font-medium text-gray-500">NHIMA Number</label>
              <p class="text-gray-900">{{ worker.nhima_number }}</p>
            </div>
          </div>
        </div>

        <!-- Payment Method -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-sm font-semibold text-gray-900 mb-4">Payment Method</h3>
          <div class="space-y-3">
            <div>
              <label class="text-sm font-medium text-gray-500">Method</label>
              <p class="text-gray-900 capitalize">{{ worker.payment_method.replace('_', ' ') }}</p>
            </div>
            <div v-if="worker.mobile_money_number">
              <label class="text-sm font-medium text-gray-500">Mobile Money</label>
              <p class="text-gray-900">{{ worker.mobile_money_number }}</p>
            </div>
            <div v-if="worker.bank_name">
              <label class="text-sm font-medium text-gray-500">Bank</label>
              <p class="text-gray-900">{{ worker.bank_name }}</p>
              <p class="text-sm text-gray-600">{{ worker.bank_account_number }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Attendance Modal -->
    <div v-if="showAttendanceModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="flex items-center justify-between p-6 border-b">
          <h3 class="text-lg font-semibold text-gray-900">Record Attendance</h3>
          <button @click="showAttendanceModal = false" class="text-gray-400 hover:text-gray-600">
            <XMarkIcon class="h-6 w-6" aria-hidden="true" />
          </button>
        </div>
        <form @submit.prevent="submitAttendance" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Work Date *</label>
            <input
              v-model="attendanceForm.work_date"
              type="date"
              required
              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Hours Worked</label>
            <input
              v-model.number="attendanceForm.hours_worked"
              type="number"
              step="0.5"
              min="0"
              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Days Worked</label>
            <input
              v-model.number="attendanceForm.days_worked"
              type="number"
              step="0.5"
              min="0"
              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Work Description</label>
            <textarea
              v-model="attendanceForm.work_description"
              rows="3"
              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
          </div>

          <div class="flex justify-end gap-3 pt-4">
            <button
              type="button"
              @click="showAttendanceModal = false"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="attendanceForm.processing"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50"
            >
              {{ attendanceForm.processing ? 'Recording...' : 'Record Attendance' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
