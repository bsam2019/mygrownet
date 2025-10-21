<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { Activity, Users, TrendingUp, Database, CheckCircle, AlertCircle } from 'lucide-vue-next'

interface Stats {
  total_users: number
  total_transactions: number
  total_commissions: number
  total_subscriptions: number
}

interface PlatformGrowth {
  users_growth: number
  transactions_growth: number
  subscriptions_growth: number
}

interface SystemHealth {
  active_users_percentage: number
  qualified_users_percentage: number
  subscription_conversion: number
}

defineProps<{
  stats: Stats
  platform_growth: PlatformGrowth
  system_health: SystemHealth
}>()

const getHealthColor = (percentage: number) => {
  if (percentage >= 70) return 'text-green-600'
  if (percentage >= 40) return 'text-yellow-600'
  return 'text-red-600'
}

const getHealthBgColor = (percentage: number) => {
  if (percentage >= 70) return 'bg-green-50 border-green-200'
  if (percentage >= 40) return 'bg-yellow-50 border-yellow-200'
  return 'bg-red-50 border-red-200'
}
</script>

<template>
  <Head title="System Analytics" />
  <AdminLayout>
    <div class="p-6 space-y-6">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">System Analytics</h1>
        <div class="text-sm text-gray-500">
          Last updated: {{ new Date().toLocaleString() }}
        </div>
      </div>

      <!-- Platform Overview Stats -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Total Users</p>
              <p class="text-3xl font-bold text-gray-900 mt-2">
                {{ stats.total_users.toLocaleString() }}
              </p>
              <p class="text-sm text-green-600 mt-2">
                +{{ platform_growth.users_growth }} this month
              </p>
            </div>
            <div class="p-3 bg-blue-100 rounded-lg">
              <Users class="w-8 h-8 text-blue-600" />
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Total Transactions</p>
              <p class="text-3xl font-bold text-gray-900 mt-2">
                {{ stats.total_transactions.toLocaleString() }}
              </p>
              <p class="text-sm text-green-600 mt-2">
                +{{ platform_growth.transactions_growth }} this month
              </p>
            </div>
            <div class="p-3 bg-green-100 rounded-lg">
              <Activity class="w-8 h-8 text-green-600" />
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Total Commissions</p>
              <p class="text-3xl font-bold text-gray-900 mt-2">
                {{ stats.total_commissions.toLocaleString() }}
              </p>
              <p class="text-sm text-gray-500 mt-2">
                All-time total
              </p>
            </div>
            <div class="p-3 bg-purple-100 rounded-lg">
              <TrendingUp class="w-8 h-8 text-purple-600" />
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Total Subscriptions</p>
              <p class="text-3xl font-bold text-gray-900 mt-2">
                {{ stats.total_subscriptions.toLocaleString() }}
              </p>
              <p class="text-sm text-green-600 mt-2">
                +{{ platform_growth.subscriptions_growth }} this month
              </p>
            </div>
            <div class="p-3 bg-indigo-100 rounded-lg">
              <Database class="w-8 h-8 text-indigo-600" />
            </div>
          </div>
        </div>
      </div>

      <!-- System Health Metrics -->
      <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">System Health Metrics</h2>
          <p class="text-sm text-gray-500 mt-1">Key performance indicators for platform health</p>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Active Users -->
            <div :class="['border rounded-lg p-6', getHealthBgColor(system_health.active_users_percentage)]">
              <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-900">Active Users</h3>
                <CheckCircle v-if="system_health.active_users_percentage >= 70" class="w-5 h-5 text-green-600" />
                <AlertCircle v-else class="w-5 h-5 text-yellow-600" />
              </div>
              <div class="flex items-end justify-between">
                <div>
                  <p :class="['text-4xl font-bold', getHealthColor(system_health.active_users_percentage)]">
                    {{ system_health.active_users_percentage }}%
                  </p>
                  <p class="text-sm text-gray-600 mt-2">of total users</p>
                </div>
              </div>
              <div class="mt-4 bg-gray-200 rounded-full h-2">
                <div 
                  class="h-2 rounded-full transition-all"
                  :class="system_health.active_users_percentage >= 70 ? 'bg-green-600' : system_health.active_users_percentage >= 40 ? 'bg-yellow-600' : 'bg-red-600'"
                  :style="{ width: system_health.active_users_percentage + '%' }"
                ></div>
              </div>
            </div>

            <!-- Qualified Users -->
            <div :class="['border rounded-lg p-6', getHealthBgColor(system_health.qualified_users_percentage)]">
              <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-900">Qualified Users</h3>
                <CheckCircle v-if="system_health.qualified_users_percentage >= 70" class="w-5 h-5 text-green-600" />
                <AlertCircle v-else class="w-5 h-5 text-yellow-600" />
              </div>
              <div class="flex items-end justify-between">
                <div>
                  <p :class="['text-4xl font-bold', getHealthColor(system_health.qualified_users_percentage)]">
                    {{ system_health.qualified_users_percentage }}%
                  </p>
                  <p class="text-sm text-gray-600 mt-2">meeting MAP threshold</p>
                </div>
              </div>
              <div class="mt-4 bg-gray-200 rounded-full h-2">
                <div 
                  class="h-2 rounded-full transition-all"
                  :class="system_health.qualified_users_percentage >= 70 ? 'bg-green-600' : system_health.qualified_users_percentage >= 40 ? 'bg-yellow-600' : 'bg-red-600'"
                  :style="{ width: system_health.qualified_users_percentage + '%' }"
                ></div>
              </div>
            </div>

            <!-- Subscription Conversion -->
            <div :class="['border rounded-lg p-6', getHealthBgColor(system_health.subscription_conversion)]">
              <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-900">Subscription Rate</h3>
                <CheckCircle v-if="system_health.subscription_conversion >= 70" class="w-5 h-5 text-green-600" />
                <AlertCircle v-else class="w-5 h-5 text-yellow-600" />
              </div>
              <div class="flex items-end justify-between">
                <div>
                  <p :class="['text-4xl font-bold', getHealthColor(system_health.subscription_conversion)]">
                    {{ system_health.subscription_conversion }}%
                  </p>
                  <p class="text-sm text-gray-600 mt-2">users with subscriptions</p>
                </div>
              </div>
              <div class="mt-4 bg-gray-200 rounded-full h-2">
                <div 
                  class="h-2 rounded-full transition-all"
                  :class="system_health.subscription_conversion >= 70 ? 'bg-green-600' : system_health.subscription_conversion >= 40 ? 'bg-yellow-600' : 'bg-red-600'"
                  :style="{ width: system_health.subscription_conversion + '%' }"
                ></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Platform Growth This Month -->
      <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">Platform Growth This Month</h2>
          <p class="text-sm text-gray-500 mt-1">New activity and registrations</p>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="border border-gray-200 rounded-lg p-6">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-gray-600">New Users</p>
                  <p class="text-3xl font-bold text-blue-600 mt-2">
                    {{ platform_growth.users_growth }}
                  </p>
                </div>
                <Users class="w-12 h-12 text-blue-200" />
              </div>
            </div>

            <div class="border border-gray-200 rounded-lg p-6">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-gray-600">New Transactions</p>
                  <p class="text-3xl font-bold text-green-600 mt-2">
                    {{ platform_growth.transactions_growth }}
                  </p>
                </div>
                <Activity class="w-12 h-12 text-green-200" />
              </div>
            </div>

            <div class="border border-gray-200 rounded-lg p-6">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-gray-600">New Subscriptions</p>
                  <p class="text-3xl font-bold text-indigo-600 mt-2">
                    {{ platform_growth.subscriptions_growth }}
                  </p>
                </div>
                <Database class="w-12 h-12 text-indigo-200" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Health Status Legend -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="font-semibold text-gray-900 mb-4">Health Status Guide</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="flex items-center space-x-3">
            <div class="w-4 h-4 bg-green-600 rounded"></div>
            <div>
              <p class="font-medium text-gray-900">Healthy (≥70%)</p>
              <p class="text-sm text-gray-500">System performing well</p>
            </div>
          </div>
          <div class="flex items-center space-x-3">
            <div class="w-4 h-4 bg-yellow-600 rounded"></div>
            <div>
              <p class="font-medium text-gray-900">Warning (40-69%)</p>
              <p class="text-sm text-gray-500">Needs attention</p>
            </div>
          </div>
          <div class="flex items-center space-x-3">
            <div class="w-4 h-4 bg-red-600 rounded"></div>
            <div>
              <p class="font-medium text-gray-900">Critical (<40%)</p>
              <p class="text-sm text-gray-500">Immediate action required</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
