<script setup lang="ts">
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { BellIcon, CheckIcon, TrashIcon, FunnelIcon } from '@heroicons/vue/24/outline'
import CMSLayout from '@/Layouts/CMSLayout.vue'

defineOptions({
  layout: CMSLayout
})

interface Notification {
  id: number
  title: string
  message: string
  type: string
  read_at: string | null
  created_at: string
  data?: any
}

interface Props {
  notifications: Notification[]
  stats: {
    total: number
    unread: number
  }
}

const props = defineProps<Props>()

const filterType = ref<string>('all')
const filterRead = ref<string>('all')

const filteredNotifications = computed(() => {
  let filtered = props.notifications

  if (filterType.value !== 'all') {
    filtered = filtered.filter(n => n.type === filterType.value)
  }

  if (filterRead.value === 'unread') {
    filtered = filtered.filter(n => !n.read_at)
  } else if (filterRead.value === 'read') {
    filtered = filtered.filter(n => n.read_at)
  }

  return filtered
})

const markAsRead = (notificationId: number) => {
  router.post(route('cms.notifications.mark-read', notificationId), {}, {
    preserveScroll: true,
  })
}

const markAllAsRead = () => {
  router.post(route('cms.notifications.mark-all-read'), {}, {
    preserveScroll: true,
  })
}

const deleteNotification = (notificationId: number) => {
  if (confirm('Are you sure you want to delete this notification?')) {
    router.delete(route('cms.notifications.destroy', notificationId), {
      preserveScroll: true,
    })
  }
}

const formatTime = (timestamp: string) => {
  const date = new Date(timestamp)
  const now = new Date()
  const diffMs = now.getTime() - date.getTime()
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMs / 3600000)
  const diffDays = Math.floor(diffMs / 86400000)
  
  if (diffMins < 1) return 'Just now'
  if (diffMins < 60) return `${diffMins} minutes ago`
  if (diffHours < 24) return `${diffHours} hours ago`
  if (diffDays < 7) return `${diffDays} days ago`
  
  return date.toLocaleDateString('en-US', { 
    month: 'short', 
    day: 'numeric',
    year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined
  })
}

const getNotificationIcon = (type: string) => {
  const icons: Record<string, string> = {
    payment: '💰',
    invoice: '📄',
    job: '🔨',
    inventory: '📦',
    expense: '💸',
    customer: '👤',
    system: '⚙️',
  }
  return icons[type] || '🔔'
}

const getNotificationColor = (type: string) => {
  const colors: Record<string, string> = {
    payment: 'bg-green-100 text-green-600',
    invoice: 'bg-blue-100 text-blue-600',
    job: 'bg-purple-100 text-purple-600',
    inventory: 'bg-orange-100 text-orange-600',
    expense: 'bg-red-100 text-red-600',
    customer: 'bg-indigo-100 text-indigo-600',
    system: 'bg-gray-100 text-gray-600',
  }
  return colors[type] || 'bg-gray-100 text-gray-600'
}
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="max-w-5xl mx-auto">
      <!-- Header -->
      <div class="mb-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
            <p class="mt-1 text-sm text-gray-500">
              {{ stats.unread }} unread of {{ stats.total }} total notifications
            </p>
          </div>
          <button
            v-if="stats.unread > 0"
            @click="markAllAsRead"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition"
          >
            <CheckIcon class="h-4 w-4" aria-hidden="true" />
            Mark all as read
          </button>
        </div>
      </div>

      <!-- Filters -->
      <div class="mb-6 bg-white rounded-lg border border-gray-200 p-4">
        <div class="flex items-center gap-4">
          <FunnelIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
          
          <select
            v-model="filterType"
            class="px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
          >
            <option value="all">All Types</option>
            <option value="payment">Payments</option>
            <option value="invoice">Invoices</option>
            <option value="job">Jobs</option>
            <option value="inventory">Inventory</option>
            <option value="expense">Expenses</option>
            <option value="customer">Customers</option>
            <option value="system">System</option>
          </select>

          <select
            v-model="filterRead"
            class="px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
          >
            <option value="all">All Status</option>
            <option value="unread">Unread Only</option>
            <option value="read">Read Only</option>
          </select>
        </div>
      </div>

      <!-- Notifications List -->
      <div class="space-y-3">
        <div
          v-for="notification in filteredNotifications"
          :key="notification.id"
          :class="[
            'bg-white rounded-lg border transition-all',
            !notification.read_at ? 'border-blue-200 bg-blue-50/30' : 'border-gray-200'
          ]"
        >
          <div class="p-4">
            <div class="flex gap-4">
              <!-- Icon -->
              <div :class="[
                'flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center text-lg',
                getNotificationColor(notification.type)
              ]">
                {{ getNotificationIcon(notification.type) }}
              </div>

              <!-- Content -->
              <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-4">
                  <div class="flex-1">
                    <div class="flex items-center gap-2">
                      <h3 class="text-sm font-semibold text-gray-900">
                        {{ notification.title }}
                      </h3>
                      <span
                        v-if="!notification.read_at"
                        class="flex-shrink-0 w-2 h-2 bg-blue-600 rounded-full"
                      ></span>
                    </div>
                    <p class="mt-1 text-sm text-gray-600">
                      {{ notification.message }}
                    </p>
                    <p class="mt-2 text-xs text-gray-400">
                      {{ formatTime(notification.created_at) }}
                    </p>
                  </div>

                  <!-- Actions -->
                  <div class="flex items-center gap-2">
                    <button
                      v-if="!notification.read_at"
                      @click="markAsRead(notification.id)"
                      class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"
                      title="Mark as read"
                    >
                      <CheckIcon class="h-4 w-4" aria-hidden="true" />
                    </button>
                    <button
                      @click="deleteNotification(notification.id)"
                      class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                      title="Delete"
                    >
                      <TrashIcon class="h-4 w-4" aria-hidden="true" />
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div
          v-if="filteredNotifications.length === 0"
          class="bg-white rounded-lg border border-gray-200 p-12 text-center"
        >
          <BellIcon class="h-12 w-12 text-gray-300 mx-auto mb-4" aria-hidden="true" />
          <h3 class="text-sm font-medium text-gray-900 mb-1">No notifications</h3>
          <p class="text-sm text-gray-500">
            {{ filterRead.value === 'unread' ? 'You\'re all caught up!' : 'No notifications to display' }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
