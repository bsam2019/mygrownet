<template>
  <AppLayout title="My Dashboard">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Welcome Section -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-6 text-white mb-6">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-2xl font-bold">Welcome back, {{ employee.firstName }}!</h1>
              <p class="text-blue-100 mt-1">{{ employee.position?.title }} • {{ employee.department?.name }}</p>
            </div>
            <div class="text-right">
              <div class="text-sm text-blue-100">Years of Service</div>
              <div class="text-2xl font-bold">{{ employee.yearsOfService }}</div>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Main Content -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Quick Stats -->
            <div class="bg-white rounded-lg shadow p-6">
              <h2 class="text-lg font-semibold text-gray-900 mb-4">My Performance</h2>
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center p-3 bg-green-50 rounded-lg">
                  <div class="text-2xl font-bold text-green-600">{{ performance.currentScore }}/5</div>
                  <div class="text-xs text-gray-600">Current Score</div>
                </div>
                <div class="text-center p-3 bg-blue-50 rounded-lg">
                  <div class="text-2xl font-bold text-blue-600">{{ performance.goalsCompleted }}</div>
                  <div class="text-xs text-gray-600">Goals Completed</div>
                </div>
                <div class="text-center p-3 bg-purple-50 rounded-lg">
                  <div class="text-2xl font-bold text-purple-600">{{ formatCurrency(commissions.totalEarned) }}</div>
                  <div class="text-xs text-gray-600">Total Earned</div>
                </div>
                <div class="text-center p-3 bg-yellow-50 rounded-lg">
                  <div class="text-2xl font-bold text-yellow-600">{{ commissions.pendingCount }}</div>
                  <div class="text-xs text-gray-600">Pending</div>
                </div>
              </div>
            </div>

            <!-- Recent Activities -->
            <div class="bg-white rounded-lg shadow p-6">
              <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Recent Activities</h2>
                <Link :href="route('employee.activities')" class="text-sm text-blue-600 hover:text-blue-800">
                  View All
                </Link>
              </div>
              <div class="space-y-3">
                <div
                  v-for="activity in recentActivities"
                  :key="activity.id"
                  class="flex items-center p-3 bg-gray-50 rounded-lg"
                >
                  <div class="flex-shrink-0">
                    <div :class="getActivityIconClass(activity.type)" class="w-8 h-8 rounded-full flex items-center justify-center">
                      <component :is="getActivityIcon(activity.type)" class="w-4 h-4" />
                    </div>
                  </div>
                  <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-gray-900">{{ activity.title }}</p>
                    <p class="text-xs text-gray-500">{{ activity.description }}</p>
                  </div>
                  <div class="text-xs text-gray-400">
                    {{ formatDate(activity.createdAt, 'relative') }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Goals & Objectives -->
            <div class="bg-white rounded-lg shadow p-6">
              <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">My Goals</h2>
                <button
                  @click="showGoalModal = true"
                  class="text-sm text-blue-600 hover:text-blue-800"
                >
                  Add Goal
                </button>
              </div>
              <div class="space-y-4">
                <div
                  v-for="goal in goals"
                  :key="goal.id"
                  class="border rounded-lg p-4"
                >
                  <div class="flex items-center justify-between mb-2">
                    <h3 class="font-medium text-gray-900">{{ goal.title }}</h3>
                    <span :class="getGoalStatusClass(goal.status)" class="px-2 py-1 text-xs font-medium rounded-full">
                      {{ goal.status }}
                    </span>
                  </div>
                  <p class="text-sm text-gray-600 mb-3">{{ goal.description }}</p>
                  <div class="flex items-center justify-between">
                    <div class="flex-1 mr-4">
                      <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                        <span>Progress</span>
                        <span>{{ goal.progress }}%</span>
                      </div>
                      <div class="w-full bg-gray-200 rounded-full h-2">
                        <div
                          class="bg-blue-500 h-2 rounded-full transition-all duration-300"
                          :style="{ width: goal.progress + '%' }"
                        ></div>
                      </div>
                    </div>
                    <div class="text-xs text-gray-500">
                      Due: {{ formatDate(goal.dueDate) }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="space-y-6">
            <!-- Profile Summary -->
            <div class="bg-white rounded-lg shadow p-6">
              <h2 class="text-lg font-semibold text-gray-900 mb-4">Profile</h2>
              <div class="text-center mb-4">
                <EmployeeAvatar :name="employee.fullName" :status="employee.employmentStatus" size="xl" />
                <h3 class="mt-2 font-medium text-gray-900">{{ employee.fullName }}</h3>
                <p class="text-sm text-gray-500">{{ employee.employeeNumber }}</p>
              </div>
              <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                  <span class="text-gray-500">Department:</span>
                  <span class="font-medium">{{ employee.department?.name }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-500">Position:</span>
                  <span class="font-medium">{{ employee.position?.title }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-500">Hire Date:</span>
                  <span class="font-medium">{{ formatDate(employee.hireDate) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-500">Manager:</span>
                  <span class="font-medium">{{ employee.manager?.fullName || 'N/A' }}</span>
                </div>
              </div>
              <div class="mt-4 pt-4 border-t">
                <Link
                  :href="route('employee.profile.edit')"
                  class="w-full bg-blue-600 text-white text-sm font-medium py-2 px-4 rounded-md hover:bg-blue-700 text-center block"
                >
                  Edit Profile
                </Link>
              </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow p-6">
              <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
              <div class="space-y-2">
                <Link
                  :href="route('employee.time-off.request')"
                  class="w-full text-left p-3 text-sm text-gray-700 hover:bg-gray-50 rounded-lg flex items-center"
                >
                  <CalendarIcon class="w-4 h-4 mr-3 text-gray-400" />
                  Request Time Off
                </Link>
                <Link
                  :href="route('employee.expenses.create')"
                  class="w-full text-left p-3 text-sm text-gray-700 hover:bg-gray-50 rounded-lg flex items-center"
                >
                  <CurrencyDollarIcon class="w-4 h-4 mr-3 text-gray-400" />
                  Submit Expense
                </Link>
                <Link
                  :href="route('employee.documents')"
                  class="w-full text-left p-3 text-sm text-gray-700 hover:bg-gray-50 rounded-lg flex items-center"
                >
                  <DocumentIcon class="w-4 h-4 mr-3 text-gray-400" />
                  My Documents
                </Link>
                <Link
                  :href="route('employee.feedback.create')"
                  class="w-full text-left p-3 text-sm text-gray-700 hover:bg-gray-50 rounded-lg flex items-center"
                >
                  <ChatBubbleLeftIcon class="w-4 h-4 mr-3 text-gray-400" />
                  Give Feedback
                </Link>
              </div>
            </div>

            <!-- Upcoming Events -->
            <div class="bg-white rounded-lg shadow p-6">
              <h2 class="text-lg font-semibold text-gray-900 mb-4">Upcoming</h2>
              <div class="space-y-3">
                <div
                  v-for="event in upcomingEvents"
                  :key="event.id"
                  class="flex items-center p-3 bg-gray-50 rounded-lg"
                >
                  <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                      <CalendarIcon class="w-4 h-4 text-blue-600" />
                    </div>
                  </div>
                  <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-gray-900">{{ event.title }}</p>
                    <p class="text-xs text-gray-500">{{ formatDate(event.date) }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Goal Modal -->
    <Modal :show="showGoalModal" @close="showGoalModal = false">
      <div class="p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Add New Goal</h2>
        <form @submit.prevent="submitGoal">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Goal Title</label>
              <input
                v-model="newGoal.title"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                required
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea
                v-model="newGoal.description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              ></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
              <input
                v-model="newGoal.dueDate"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                required
              />
            </div>
          </div>
          <div class="mt-6 flex justify-end space-x-3">
            <button
              type="button"
              @click="showGoalModal = false"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700"
            >
              Add Goal
            </button>
          </div>
        </form>
      </div>
    </Modal>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import {
  CalendarIcon,
  CurrencyDollarIcon,
  DocumentIcon,
  ChatBubbleLeftIcon,
  ChartBarIcon,
  TrophyIcon,
  ClockIcon
} from '@heroicons/vue/24/outline';
import { formatDate, formatCurrency } from '@/utils/formatting';
import AppLayout from '@/layouts/AppLayout.vue';
import EmployeeAvatar from '@/components/Employee/EmployeeAvatar.vue';
import Modal from '@/components/Modal.vue';

interface Props {
  employee: any;
  performance: any;
  commissions: any;
  recentActivities: any[];
  goals: any[];
  upcomingEvents: any[];
}

const props = defineProps<Props>();

const showGoalModal = ref(false);
const newGoal = ref({
  title: '',
  description: '',
  dueDate: ''
});

const getActivityIconClass = (type: string) => {
  const classes = {
    performance: 'bg-green-100 text-green-600',
    commission: 'bg-blue-100 text-blue-600',
    goal: 'bg-purple-100 text-purple-600',
    training: 'bg-yellow-100 text-yellow-600',
    default: 'bg-gray-100 text-gray-600'
  };
  return classes[type] || classes.default;
};

const getActivityIcon = (type: string) => {
  const icons = {
    performance: ChartBarIcon,
    commission: CurrencyDollarIcon,
    goal: TrophyIcon,
    training: DocumentIcon,
    default: ClockIcon
  };
  return icons[type] || icons.default;
};

const getGoalStatusClass = (status: string) => {
  const classes = {
    'in-progress': 'bg-blue-100 text-blue-800',
    'completed': 'bg-green-100 text-green-800',
    'overdue': 'bg-red-100 text-red-800',
    'pending': 'bg-yellow-100 text-yellow-800'
  };
  return classes[status] || classes.pending;
};

const submitGoal = () => {
  router.post(route('employee.goals.store'), newGoal.value, {
    onSuccess: () => {
      showGoalModal.value = false;
      newGoal.value = { title: '', description: '', dueDate: '' };
    }
  });
};
</script>