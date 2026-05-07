<template>
  <CMSLayout page-title="Capacity Forecast">
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Capacity Forecast</h1>
          <p class="mt-1 text-sm text-gray-500">{{ forecastData.weeks_ahead }}-week capacity planning and demand analysis</p>
        </div>
        <div class="flex items-center gap-3">
          <select
            v-model="weeksAhead"
            @change="updateForecast"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option value="4">4 Weeks</option>
            <option value="8">8 Weeks</option>
            <option value="12">12 Weeks</option>
          </select>
          <Link
            :href="route('cms.operations.workload-balance')"
            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
          >
            <ScaleIcon class="h-5 w-5" aria-hidden="true" />
            Workload Balance
          </Link>
        </div>
      </div>

      <!-- Summary Statistics -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center gap-3">
            <div class="p-3 bg-blue-100 rounded-lg">
              <CalendarIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500">Total Weeks</p>
              <p class="mt-1 text-2xl font-bold text-blue-600">{{ forecastData.weeks.length }}</p>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center gap-3">
            <div class="p-3 bg-red-100 rounded-lg">
              <ExclamationTriangleIcon class="h-6 w-6 text-red-600" aria-hidden="true" />
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500">Overbooked</p>
              <p class="mt-1 text-2xl font-bold text-red-600">{{ overbookedWeeks }}</p>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center gap-3">
            <div class="p-3 bg-amber-100 rounded-lg">
              <ClockIcon class="h-6 w-6 text-amber-600" aria-hidden="true" />
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500">High Utilization</p>
              <p class="mt-1 text-2xl font-bold text-amber-600">{{ highUtilizationWeeks }}</p>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center gap-3">
            <div class="p-3 bg-green-100 rounded-lg">
              <CheckCircleIcon class="h-6 w-6 text-green-600" aria-hidden="true" />
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500">Normal</p>
              <p class="mt-1 text-2xl font-bold text-green-600">{{ normalWeeks }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Capacity Chart -->
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-6">Capacity vs Demand</h2>
        <div class="space-y-6">
          <div
            v-for="week in forecastData.weeks"
            :key="week.week_start"
            class="border border-gray-200 rounded-lg p-4"
          >
            <div class="flex items-center justify-between mb-3">
              <div>
                <h3 class="text-sm font-semibold text-gray-900">
                  {{ formatDate(week.week_start) }} - {{ formatDate(week.week_end) }}
                </h3>
                <p class="text-xs text-gray-500 mt-1">{{ week.task_count }} tasks scheduled</p>
              </div>
              <span
                :class="[
                  'px-3 py-1 rounded-full text-xs font-semibold',
                  week.status === 'overbooked' ? 'bg-red-100 text-red-700' :
                  week.status === 'high' ? 'bg-amber-100 text-amber-700' :
                  'bg-green-100 text-green-700'
                ]"
              >
                {{ week.status.charAt(0).toUpperCase() + week.status.slice(1) }}
              </span>
            </div>

            <!-- Capacity Bar -->
            <div class="mb-2">
              <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-medium text-gray-600">Capacity</span>
                <span class="text-xs font-bold text-gray-900">{{ week.available_capacity }}h</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-blue-500 h-full rounded-full" style="width: 100%"></div>
              </div>
            </div>

            <!-- Demand Bar -->
            <div class="mb-2">
              <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-medium text-gray-600">Demand</span>
                <span class="text-xs font-bold text-gray-900">{{ week.demand_hours }}h</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div
                  :class="[
                    'h-full rounded-full',
                    week.status === 'overbooked' ? 'bg-red-500' :
                    week.status === 'high' ? 'bg-amber-500' :
                    'bg-green-500'
                  ]"
                  :style="{ width: Math.min((week.demand_hours / week.available_capacity) * 100, 100) + '%' }"
                ></div>
              </div>
            </div>

            <!-- Utilization -->
            <div class="flex items-center justify-between pt-2 border-t border-gray-100">
              <span class="text-xs text-gray-500">Utilization Rate</span>
              <span
                :class="[
                  'text-sm font-bold',
                  week.status === 'overbooked' ? 'text-red-600' :
                  week.status === 'high' ? 'text-amber-600' :
                  'text-green-600'
                ]"
              >
                {{ week.utilization_rate }}%
              </span>
            </div>

            <!-- Capacity Gap Warning -->
            <div v-if="week.capacity_gap > 0" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
              <div class="flex items-start gap-2">
                <ExclamationTriangleIcon class="h-5 w-5 text-red-600 flex-shrink-0 mt-0.5" aria-hidden="true" />
                <div>
                  <p class="text-sm font-semibold text-red-900">Capacity Shortage</p>
                  <p class="text-xs text-red-700 mt-1">
                    Need {{ week.capacity_gap }} additional hours. Consider reassigning tasks or adding resources.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </CMSLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import CMSLayout from '@/Layouts/CMSLayout.vue'
import {
  CalendarIcon,
  ExclamationTriangleIcon,
  CheckCircleIcon,
  ClockIcon,
  ScaleIcon
} from '@heroicons/vue/24/outline'

interface Week {
  week_start: string
  week_end: string
  available_capacity: number
  demand_hours: number
  utilization_rate: number
  capacity_gap: number
  task_count: number
  status: 'overbooked' | 'high' | 'normal'
}

interface Props {
  forecastData: {
    weeks_ahead: number
    weeks: Week[]
  }
}

const props = defineProps<Props>()

const weeksAhead = ref(props.forecastData.weeks_ahead)

const overbookedWeeks = computed(() => 
  props.forecastData.weeks.filter(w => w.status === 'overbooked').length
)

const highUtilizationWeeks = computed(() => 
  props.forecastData.weeks.filter(w => w.status === 'high').length
)

const normalWeeks = computed(() => 
  props.forecastData.weeks.filter(w => w.status === 'normal').length
)

const formatDate = (dateString: string) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

const updateForecast = () => {
  router.get(route('cms.operations.capacity-forecast'), { weeks: weeksAhead.value }, {
    preserveState: true,
  })
}
</script>
