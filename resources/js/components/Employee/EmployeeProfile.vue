<template>
  <div class="space-y-6">
    <!-- Profile Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="bg-gradient-to-r from-blue-500 to-purple-600 px-6 py-8">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="h-20 w-20 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center text-white font-bold text-2xl">
              {{ getInitials(employee.first_name, employee.last_name) }}
            </div>
          </div>
          <div class="ml-6 text-white">
            <h1 class="text-2xl font-bold">{{ employee.first_name }} {{ employee.last_name }}</h1>
            <p class="text-lg opacity-90">{{ employee.position?.title || 'No Position' }}</p>
            <p class="text-sm opacity-75">{{ employee.department?.name || 'No Department' }}</p>
            <div class="mt-2">
              <span :class="getStatusBadgeClass(employee.employment_status)" class="inline-flex px-3 py-1 text-xs font-semibold rounded-full">
                {{ formatStatus(employee.employment_status) }}
              </span>
            </div>
          </div>
        </div>
      </div>
      
      <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-600">
            Employee #{{ employee.employee_number }}
          </div>
          <div class="flex items-center space-x-4">
            <Link 
              v-if="canEdit"
              :href="route('employees.edit', employee.id)"
              class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors"
            >
              <PencilIcon class="h-4 w-4 mr-2" />
              Edit Profile
            </Link>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Personal Information -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Contact Information -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
          <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
              <UserIcon class="h-5 w-5 mr-2 text-blue-600" />
              Contact Information
            </h3>
          </div>
          <div class="p-6">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <dt class="text-sm font-medium text-gray-500">Email Address</dt>
                <dd class="mt-1 text-sm text-gray-900 flex items-center">
                  <EnvelopeIcon class="h-4 w-4 mr-2 text-gray-400" />
                  <a :href="`mailto:${employee.email}`" class="text-blue-600 hover:text-blue-800">
                    {{ employee.email }}
                  </a>
                </dd>
              </div>
              
              <div v-if="employee.phone">
                <dt class="text-sm font-medium text-gray-500">Phone Number</dt>
                <dd class="mt-1 text-sm text-gray-900 flex items-center">
                  <PhoneIcon class="h-4 w-4 mr-2 text-gray-400" />
                  <a :href="`tel:${employee.phone}`" class="text-blue-600 hover:text-blue-800">
                    {{ employee.phone }}
                  </a>
                </dd>
              </div>
              
              <div v-if="employee.address" class="md:col-span-2">
                <dt class="text-sm font-medium text-gray-500">Address</dt>
                <dd class="mt-1 text-sm text-gray-900 flex items-start">
                  <MapPinIcon class="h-4 w-4 mr-2 text-gray-400 mt-0.5" />
                  {{ employee.address }}
                </dd>
              </div>
            </dl>
          </div>
        </div>

        <!-- Employment Details -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
          <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
              <BriefcaseIcon class="h-5 w-5 mr-2 text-blue-600" />
              Employment Details
            </h3>
          </div>
          <div class="p-6">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <dt class="text-sm font-medium text-gray-500">Department</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ employee.department?.name || 'Not assigned' }}</dd>
              </div>
              
              <div>
                <dt class="text-sm font-medium text-gray-500">Position</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ employee.position?.title || 'Not assigned' }}</dd>
              </div>
              
              <div>
                <dt class="text-sm font-medium text-gray-500">Hire Date</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ formatDate(employee.hire_date) }}</dd>
              </div>
              
              <div>
                <dt class="text-sm font-medium text-gray-500">Years of Service</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ calculateYearsOfService(employee.hire_date) }}</dd>
              </div>
              
              <div v-if="employee.manager">
                <dt class="text-sm font-medium text-gray-500">Reports To</dt>
                <dd class="mt-1 text-sm text-gray-900">
                  <Link 
                    :href="route('employees.show', employee.manager.id)"
                    class="text-blue-600 hover:text-blue-800"
                  >
                    {{ employee.manager.first_name }} {{ employee.manager.last_name }}
                  </Link>
                </dd>
              </div>
              
              <div>
                <dt class="text-sm font-medium text-gray-500">Base Salary</dt>
                <dd class="mt-1 text-sm text-gray-900 font-medium">
                  {{ formatCurrency(employee.base_salary) }}
                </dd>
              </div>
            </dl>
          </div>
        </div>

        <!-- Recent Performance -->
        <div v-if="recentPerformance?.length > 0" class="bg-white rounded-xl shadow-sm border border-gray-100">
          <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
              <ChartBarIcon class="h-5 w-5 mr-2 text-blue-600" />
              Recent Performance Reviews
            </h3>
          </div>
          <div class="p-6">
            <div class="space-y-4">
              <div v-for="review in recentPerformance" :key="review.id" class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                  <h4 class="text-sm font-medium text-gray-900">{{ review.review_period }}</h4>
                  <span :class="getPerformanceScoreClass(review.overall_score)" class="px-2 py-1 text-xs font-semibold rounded-full">
                    {{ review.overall_score }}/5
                  </span>
                </div>
                <p class="text-sm text-gray-600">{{ review.summary || 'No summary provided' }}</p>
                <div class="mt-2 text-xs text-gray-500">
                  Reviewed on {{ formatDate(review.review_date) }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Quick Stats -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
          <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">Quick Stats</h3>
          </div>
          <div class="p-6 space-y-4">
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-600">Total Commissions</span>
              <span class="text-sm font-medium text-gray-900">
                {{ formatCurrency(stats.total_commissions || 0) }}
              </span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-600">This Month</span>
              <span class="text-sm font-medium text-green-600">
                {{ formatCurrency(stats.monthly_commissions || 0) }}
              </span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-600">Performance Score</span>
              <span class="text-sm font-medium text-gray-900">
                {{ stats.avg_performance_score || 'N/A' }}/5
              </span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-600">Direct Reports</span>
              <span class="text-sm font-medium text-gray-900">
                {{ stats.direct_reports_count || 0 }}
              </span>
            </div>
          </div>
        </div>

        <!-- Direct Reports -->
        <div v-if="directReports?.length > 0" class="bg-white rounded-xl shadow-sm border border-gray-100">
          <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">Direct Reports</h3>
          </div>
          <div class="p-6">
            <div class="space-y-3">
              <div v-for="report in directReports" :key="report.id" class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="h-8 w-8 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center text-white font-medium text-xs">
                    {{ getInitials(report.first_name, report.last_name) }}
                  </div>
                </div>
                <div class="ml-3 flex-1">
                  <Link 
                    :href="route('employees.show', report.id)"
                    class="text-sm font-medium text-gray-900 hover:text-blue-600"
                  >
                    {{ report.first_name }} {{ report.last_name }}
                  </Link>
                  <p class="text-xs text-gray-500">{{ report.position?.title || 'No Position' }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Activity -->
        <div v-if="recentActivity?.length > 0" class="bg-white rounded-xl shadow-sm border border-gray-100">
          <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">Recent Activity</h3>
          </div>
          <div class="p-6">
            <div class="space-y-3">
              <div v-for="activity in recentActivity" :key="activity.id" class="flex items-start">
                <div class="flex-shrink-0">
                  <div class="h-2 w-2 bg-blue-600 rounded-full mt-2"></div>
                </div>
                <div class="ml-3">
                  <p class="text-sm text-gray-900">{{ activity.description }}</p>
                  <p class="text-xs text-gray-500">{{ formatDate(activity.created_at) }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import {
  UserIcon,
  BriefcaseIcon,
  ChartBarIcon,
  PencilIcon,
  EnvelopeIcon,
  PhoneIcon,
  MapPinIcon
} from '@heroicons/vue/24/outline'
import { formatCurrency } from '@/utils/formatting'

// Types
interface Employee {
  id: number
  employee_number: string
  first_name: string
  last_name: string
  email: string
  phone?: string
  address?: string
  hire_date: string
  base_salary: number
  employment_status: 'active' | 'inactive' | 'terminated' | 'suspended'
  department?: {
    id: number
    name: string
  }
  position?: {
    id: number
    title: string
  }
  manager?: {
    id: number
    first_name: string
    last_name: string
  }
}

interface PerformanceReview {
  id: number
  review_period: string
  review_date: string
  overall_score: number
  summary?: string
}

interface DirectReport {
  id: number
  first_name: string
  last_name: string
  position?: {
    title: string
  }
}

interface Activity {
  id: number
  description: string
  created_at: string
}

interface Stats {
  total_commissions: number
  monthly_commissions: number
  avg_performance_score: number
  direct_reports_count: number
}

interface Props {
  employee: Employee
  recentPerformance?: PerformanceReview[]
  directReports?: DirectReport[]
  recentActivity?: Activity[]
  stats: Stats
  canEdit?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  recentPerformance: () => [],
  directReports: () => [],
  recentActivity: () => [],
  canEdit: false
})

// Methods
const getInitials = (firstName: string, lastName: string): string => {
  return `${firstName.charAt(0)}${lastName.charAt(0)}`.toUpperCase()
}

const formatDate = (date: string): string => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatStatus = (status: string): string => {
  return status.charAt(0).toUpperCase() + status.slice(1)
}

const getStatusBadgeClass = (status: string): string => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    inactive: 'bg-gray-100 text-gray-800',
    terminated: 'bg-red-100 text-red-800',
    suspended: 'bg-yellow-100 text-yellow-800'
  }
  return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-800'
}

const getPerformanceScoreClass = (score: number): string => {
  if (score >= 4.5) return 'bg-green-100 text-green-800'
  if (score >= 3.5) return 'bg-blue-100 text-blue-800'
  if (score >= 2.5) return 'bg-yellow-100 text-yellow-800'
  return 'bg-red-100 text-red-800'
}

const calculateYearsOfService = (hireDate: string): string => {
  const hire = new Date(hireDate)
  const now = new Date()
  const years = now.getFullYear() - hire.getFullYear()
  const months = now.getMonth() - hire.getMonth()
  
  if (years === 0) {
    return months === 1 ? '1 month' : `${months} months`
  }
  
  if (months < 0) {
    return years === 1 ? '1 year' : `${years - 1} years`
  }
  
  return years === 1 ? '1 year' : `${years} years`
}
</script>