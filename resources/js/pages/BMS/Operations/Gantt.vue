<template>
  <BMSLayout page-title="Gantt Chart">
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Gantt Chart</h1>
          <p class="mt-1 text-sm text-gray-500">Visual timeline of tasks and dependencies</p>
        </div>
        <div class="flex items-center gap-3">
          <select
            v-model="selectedWorkflow"
            @change="updateWorkflow"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option value="">All Workflows</option>
            <option
              v-for="workflow in workflows"
              :key="workflow.id"
              :value="workflow.id"
            >
              {{ workflow.name }}
            </option>
          </select>
          <Link
            :href="route('bms.operations.analytics')"
            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
          >
            <ChartBarIcon class="h-5 w-5" aria-hidden="true" />
            Analytics
          </Link>
        </div>
      </div>

      <!-- Legend -->
      <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center gap-6 flex-wrap">
          <div class="flex items-center gap-2">
            <div class="w-4 h-4 bg-blue-500 rounded"></div>
            <span class="text-sm text-gray-700">Not Started</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-4 h-4 bg-yellow-500 rounded"></div>
            <span class="text-sm text-gray-700">In Progress</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-4 h-4 bg-green-500 rounded"></div>
            <span class="text-sm text-gray-700">Completed</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-4 h-4 bg-red-500 rounded"></div>
            <span class="text-sm text-gray-700">Overdue</span>
          </div>
          <div class="flex items-center gap-2">
            <ArrowRightIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
            <span class="text-sm text-gray-700">Dependency</span>
          </div>
        </div>
      </div>

      <!-- Gantt Chart Container -->
      <div class="bg-white rounded-lg shadow p-6">
        <div v-if="ganttData.tasks.length === 0" class="text-center py-12">
          <CalendarIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">No tasks to display</h3>
          <p class="mt-1 text-sm text-gray-500">Create tasks with due dates to see them on the Gantt chart.</p>
        </div>

        <!-- Simple Gantt Chart (Custom Implementation) -->
        <div v-else class="overflow-x-auto">
          <!-- Timeline Header -->
          <div class="flex border-b border-gray-200 pb-2 mb-4">
            <div class="w-64 flex-shrink-0 font-semibold text-sm text-gray-700">Task</div>
            <div class="flex-1 flex gap-1">
              <div
                v-for="day in timelineDays"
                :key="day"
                class="flex-1 text-center text-xs text-gray-500"
              >
                {{ formatDay(day) }}
              </div>
            </div>
          </div>

          <!-- Task Rows -->
          <div class="space-y-2">
            <div
              v-for="task in ganttData.tasks"
              :key="task.id"
              class="flex items-center"
            >
              <!-- Task Info -->
              <div class="w-64 flex-shrink-0 pr-4">
                <Link
                  :href="route('bms.operations.tasks.show', task.id)"
                  class="text-sm font-medium text-blue-600 hover:text-blue-800 truncate block"
                >
                  {{ task.title }}
                </Link>
                <p class="text-xs text-gray-500">{{ task.assignee_name }}</p>
              </div>

              <!-- Timeline Bar -->
              <div class="flex-1 relative h-8">
                <div
                  :style="{
                    left: calculatePosition(task.start_date) + '%',
                    width: calculateWidth(task.start_date, task.end_date) + '%'
                  }"
                  :class="[
                    'absolute top-1 h-6 rounded flex items-center justify-center text-xs text-white font-medium',
                    task.status === 'completed' ? 'bg-green-500' :
                    task.status === 'in_progress' ? 'bg-yellow-500' :
                    task.is_overdue ? 'bg-red-500' :
                    'bg-blue-500'
                  ]"
                >
                  <span class="truncate px-2">{{ task.progress }}%</span>
                </div>

                <!-- Dependencies -->
                <div
                  v-for="dep in task.dependencies"
                  :key="dep"
                  class="absolute top-0 left-0 w-full h-full pointer-events-none"
                >
                  <!-- Dependency arrow would be drawn here with SVG -->
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Note about Gantt Library -->
        <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
          <div class="flex items-start gap-3">
            <InformationCircleIcon class="h-5 w-5 text-blue-600 flex-shrink-0 mt-0.5" aria-hidden="true" />
            <div>
              <p class="text-sm font-semibold text-blue-900">Enhanced Gantt Chart Coming Soon</p>
              <p class="text-xs text-blue-700 mt-1">
                This is a simplified Gantt view. A full-featured Gantt chart with drag-and-drop scheduling, 
                dependency visualization, and critical path analysis will be integrated using a specialized library.
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Task Statistics -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
          <p class="text-sm font-medium text-gray-500">Total Tasks</p>
          <p class="mt-2 text-3xl font-bold text-gray-900">{{ ganttData.tasks.length }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <p class="text-sm font-medium text-gray-500">In Progress</p>
          <p class="mt-2 text-3xl font-bold text-yellow-600">
            {{ ganttData.tasks.filter(t => t.status === 'in_progress').length }}
          </p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <p class="text-sm font-medium text-gray-500">Completed</p>
          <p class="mt-2 text-3xl font-bold text-green-600">
            {{ ganttData.tasks.filter(t => t.status === 'completed').length }}
          </p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <p class="text-sm font-medium text-gray-500">Overdue</p>
          <p class="mt-2 text-3xl font-bold text-red-600">
            {{ ganttData.tasks.filter(t => t.is_overdue).length }}
          </p>
        </div>
      </div>
    </div>
  </BMSLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import BMSLayout from '@/Layouts/BMSLayout.vue'
import {
  ChartBarIcon,
  CalendarIcon,
  ArrowRightIcon,
  InformationCircleIcon
} from '@heroicons/vue/24/outline'

interface Task {
  id: number
  title: string
  start_date: string
  end_date: string
  status: string
  progress: number
  assignee_name: string
  is_overdue: boolean
  dependencies: number[]
}

interface Workflow {
  id: number
  name: string
}

interface Props {
  ganttData: {
    tasks: Task[]
  }
  workflows: Workflow[]
}

const props = defineProps<Props>()

const selectedWorkflow = ref('')

// Calculate timeline days (30 days from earliest to latest task)
const timelineDays = computed(() => {
  if (props.ganttData.tasks.length === 0) return []
  
  const dates = props.ganttData.tasks.flatMap(t => [new Date(t.start_date), new Date(t.end_date)])
  const minDate = new Date(Math.min(...dates.map(d => d.getTime())))
  const maxDate = new Date(Math.max(...dates.map(d => d.getTime())))
  
  const days: Date[] = []
  const current = new Date(minDate)
  
  while (current <= maxDate) {
    days.push(new Date(current))
    current.setDate(current.getDate() + 1)
  }
  
  return days
})

const formatDay = (date: Date) => {
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

const calculatePosition = (startDate: string) => {
  if (timelineDays.value.length === 0) return 0
  
  const start = new Date(startDate)
  const timelineStart = timelineDays.value[0]
  const daysDiff = Math.floor((start.getTime() - timelineStart.getTime()) / (1000 * 60 * 60 * 24))
  
  return (daysDiff / timelineDays.value.length) * 100
}

const calculateWidth = (startDate: string, endDate: string) => {
  if (timelineDays.value.length === 0) return 0
  
  const start = new Date(startDate)
  const end = new Date(endDate)
  const duration = Math.floor((end.getTime() - start.getTime()) / (1000 * 60 * 60 * 24)) + 1
  
  return (duration / timelineDays.value.length) * 100
}

const updateWorkflow = () => {
  router.get(route('bms.operations.gantt'), { workflow: selectedWorkflow.value }, {
    preserveState: true,
  })
}
</script>
