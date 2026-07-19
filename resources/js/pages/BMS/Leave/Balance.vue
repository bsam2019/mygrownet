<script setup lang="ts">
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { CalendarIcon, ClockIcon, CheckCircleIcon } from '@heroicons/vue/24/outline'
import CMSLayout from '@/Layouts/CMSLayout.vue'

defineOptions({
  layout: CMSLayout
})

interface Props {
  balances: Array<{
    id: number
    leave_type: {
      id: number
      name: string
      code: string
      color: string
    }
    total_days: number
    used_days: number
    pending_days: number
    available_days: number
    year: number
  }>
  worker: {
    id: number
    name: string
    job_title: string
  }
}

const props = defineProps<Props>()

const totalAvailable = computed(() => 
  props.balances.reduce((sum, b) => sum + b.available_days, 0)
)

const totalUsed = computed(() => 
  props.balances.reduce((sum, b) => sum + b.used_days, 0)
)

const totalPending = computed(() => 
  props.balances.reduce((sum, b) => sum + b.pending_days, 0)
)

const getProgressPercentage = (used: number, total: number) => {
  if (total === 0) return 0
  return Math.min((used / total) * 100, 100)
}

const getProgressColor = (percentage: number) => {
  if (percentage >= 80) return 'bg-red-500'
  if (percentage >= 60) return 'bg-amber-500'
  return 'bg-green-500'
}
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="max-w-5xl mx-auto">
      <!-- Header -->
      <div class="mb-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Leave Balance</h1>
            <p class="mt-1 text-sm text-gray-500">{{ worker.name }} - {{ worker.job_title }}</p>
          </div>
          <Link
            :href="route('cms.leave.create')"
            class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
          >
            Request Leave
          </Link>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
          <div class="flex items-center gap-3">
            <div class="p-3 bg-green-100 rounded-lg">
              <CalendarIcon class="h-6 w-6 text-green-600" aria-hidden="true" />
            </div>
            <div>
              <p class="text-sm text-gray-500">Available Days</p>
              <p class="text-2xl font-bold text-gray-900">{{ totalAvailable }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6">
          <div class="flex items-center gap-3">
            <div class="p-3 bg-blue-100 rounded-lg">
              <CheckCircleIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
            </div>
            <div>
              <p class="text-sm text-gray-500">Used Days</p>
              <p class="text-2xl font-bold text-gray-900">{{ totalUsed }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6">
          <div class="flex items-center gap-3">
            <div class="p-3 bg-amber-100 rounded-lg">
              <ClockIcon class="h-6 w-6 text-amber-600" aria-hidden="true" />
            </div>
            <div>
              <p class="text-sm text-gray-500">Pending Days</p>
              <p class="text-2xl font-bold text-gray-900">{{ totalPending }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Leave Type Balances -->
      <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">Leave Type Breakdown</h2>
        </div>
        <div class="divide-y divide-gray-200">
          <div
            v-for="balance in balances"
            :key="balance.id"
            class="p-6 hover:bg-gray-50 transition-colors"
          >
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center gap-3">
                <div
                  class="w-3 h-3 rounded-full"
                  :style="{ backgroundColor: balance.leave_type.color }"
                />
                <div>
                  <h3 class="font-medium text-gray-900">{{ balance.leave_type.name }}</h3>
                  <p class="text-sm text-gray-500">{{ balance.leave_type.code }}</p>
                </div>
              </div>
              <div class="text-right">
                <p class="text-2xl font-bold text-gray-900">{{ balance.available_days }}</p>
                <p class="text-sm text-gray-500">days available</p>
              </div>
            </div>

            <!-- Progress Bar -->
            <div class="mb-3">
              <div class="flex justify-between text-sm text-gray-600 mb-1">
                <span>Usage</span>
                <span>{{ balance.used_days }} / {{ balance.total_days }} days</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div
                  :class="[
                    'h-2 rounded-full transition-all',
                    getProgressColor(getProgressPercentage(balance.used_days, balance.total_days))
                  ]"
                  :style="{ width: `${getProgressPercentage(balance.used_days, balance.total_days)}%` }"
                />
              </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-3 gap-4 text-center">
              <div class="p-3 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-500">Total</p>
                <p class="text-lg font-semibold text-gray-900">{{ balance.total_days }}</p>
              </div>
              <div class="p-3 bg-blue-50 rounded-lg">
                <p class="text-sm text-gray-500">Used</p>
                <p class="text-lg font-semibold text-blue-600">{{ balance.used_days }}</p>
              </div>
              <div class="p-3 bg-amber-50 rounded-lg">
                <p class="text-sm text-gray-500">Pending</p>
                <p class="text-lg font-semibold text-amber-600">{{ balance.pending_days }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
