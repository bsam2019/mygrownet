<template>
  <div class="relative">
    <!-- Bell Icon Button -->
    <button
      @click="toggleDropdown"
      class="relative p-2 rounded-full hover:bg-white/10 transition-colors flex items-center justify-center"
      :class="{ 'bg-white/10': showDropdown }"
    >
      <BellIcon class="h-5 w-5 text-white" />
      
      <!-- Unread Badge -->
      <span
        v-if="unreadCount > 0"
        class="absolute -top-0.5 -right-0.5 h-4 w-4 flex items-center justify-center bg-red-500 text-white text-[10px] font-bold rounded-full border border-blue-600"
      >
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </button>

    <!-- Dropdown Panel -->
    <Teleport to="body">
      <Transition name="notification">
        <div
          v-if="showDropdown"
          class="fixed inset-0 z-[100000]"
          @click.self="showDropdown = false"
        >
          <!-- Backdrop -->
          <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
          
          <!-- Notification Panel -->
          <div class="fixed top-0 right-0 h-full w-full max-w-md bg-white shadow-2xl transform transition-all flex flex-col">
            <!-- Header -->
            <div class="sticky top-0 bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-4 flex items-center justify-between flex-shrink-0">
              <div>
                <h3 class="text-lg font-bold">Notifications</h3>
                <p class="text-sm opacity-90">{{ unreadCount }} unread</p>
              </div>
              <button
                @click="showDropdown = false"
                class="p-2 hover:bg-white/20 rounded-full transition-colors"
              >
                <XMarkIcon class="h-5 w-5" />
              </button>
            </div>

            <!-- Actions Bar -->
            <div class="px-6 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between flex-shrink-0">
              <button
                v-if="unreadCount > 0"
                @click="markAllAsRead"
                class="text-sm text-blue-600 hover:text-blue-700 font-medium"
              >
                Mark all as read
              </button>
              <div v-else class="text-sm text-gray-500">All caught up!</div>
            </div>

            <!-- Notifications List -->
            <div class="flex-1 overflow-y-auto">
              <div v-if="loading" class="flex items-center justify-center py-12">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
              </div>

              <div v-else-if="notifications.length > 0" class="divide-y divide-gray-100">
                <div
                  v-for="notification in notifications"
                  :key="notification.id"
                  class="p-4 hover:bg-gray-50 transition-colors cursor-pointer"
                  :class="{ 'bg-blue-50': !notification.read_at }"
                  @click="handleNotificationClick(notification)"
                >
                  <div class="flex gap-3">
                    <!-- Icon -->
                    <div
                      class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0"
                      :class="getNotificationIconBg(notification.priority)"
                    >
                      <component
                        :is="getNotificationIcon(notification.priority)"
                        class="h-5 w-5"
                        :class="getNotificationIconColor(notification.priority)"
                      />
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                      <h4 class="text-sm font-semibold text-gray-900 mb-1">
                        {{ notification.title }}
                      </h4>
                      <p class="text-sm text-gray-600 mb-2">
                        {{ notification.message }}
                      </p>
                      <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-400">
                          {{ formatDate(notification.created_at) }}
                        </span>
                        <span
                          v-if="!notification.read_at"
                          class="w-2 h-2 bg-blue-600 rounded-full"
                        ></span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div v-else class="flex flex-col items-center justify-center py-12 px-6 text-center">
                <BellIcon class="h-16 w-16 text-gray-300 mb-4" />
                <p class="text-gray-500 font-medium mb-2">No notifications yet</p>
                <p class="text-sm text-gray-400">We'll notify you when something important happens</p>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { BellIcon, XMarkIcon, ExclamationTriangleIcon, InformationCircleIcon, CheckCircleIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';

interface Notification {
  id: string;
  title: string;
  message: string;
  action_url: string | null;
  action_text: string | null;
  priority: string;
  read_at: string | null;
  created_at: string;
}

const unreadCount = ref(0);
const notifications = ref<Notification[]>([]);
const showDropdown = ref(false);
const loading = ref(false);

const fetchCount = async () => {
  try {
    const { data } = await axios.get(route('mygrownet.notifications.count'));
    unreadCount.value = data.count;
  } catch (error) {
    console.error('Failed to fetch notification count:', error);
  }
};

const fetchNotifications = async () => {
  if (loading.value) return;
  
  loading.value = true;
  try {
    const { data } = await axios.get(route('mygrownet.notifications.index'));
    notifications.value = data.notifications;
  } catch (error) {
    console.error('Failed to fetch notifications:', error);
  } finally {
    loading.value = false;
  }
};

const markAsRead = async (id: string) => {
  try {
    await axios.post(route('mygrownet.notifications.read', { id }));
    
    const notification = notifications.value.find(n => n.id === id);
    if (notification) {
      notification.read_at = new Date().toISOString();
    }
    unreadCount.value = Math.max(0, unreadCount.value - 1);
  } catch (error) {
    console.error('Failed to mark notification as read:', error);
  }
};

const markAllAsRead = async () => {
  try {
    await axios.post(route('mygrownet.notifications.read-all'));
    
    notifications.value.forEach(n => {
      n.read_at = new Date().toISOString();
    });
    unreadCount.value = 0;
  } catch (error) {
    console.error('Failed to mark all as read:', error);
  }
};

const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value;
  if (showDropdown.value && notifications.value.length === 0) {
    fetchNotifications();
  }
};

const handleNotificationClick = (notification: Notification) => {
  // Mark as read
  if (!notification.read_at) {
    markAsRead(notification.id);
  }
  
  // Close dropdown
  showDropdown.value = false;
  
  // Check if we're on mobile
  const isMobile = window.location.pathname.includes('/mobile');
  
  if (isMobile) {
    // Handle message notifications specially - open message modal
    if (notification.type === 'messages.received' && notification.data?.message_id) {
      // Emit event to open message modal
      window.dispatchEvent(new CustomEvent('open-message-modal', {
        detail: { messageId: notification.data.message_id }
      }));
    }
    // For other notifications: just close the panel and mark as read
    // User stays on current page - better mobile UX
  } else {
    // Desktop: navigate to action URL if provided
    if (notification.action_url) {
      router.visit(notification.action_url);
    }
  }
};

const getNotificationIcon = (priority: string) => {
  return priority === 'high' ? ExclamationTriangleIcon :
         priority === 'medium' ? InformationCircleIcon :
         CheckCircleIcon;
};

const getNotificationIconBg = (priority: string) => {
  return priority === 'high' ? 'bg-red-100' :
         priority === 'medium' ? 'bg-yellow-100' :
         'bg-green-100';
};

const getNotificationIconColor = (priority: string) => {
  return priority === 'high' ? 'text-red-600' :
         priority === 'medium' ? 'text-yellow-600' :
         'text-green-600';
};

const formatDate = (dateString: string) => {
  const date = new Date(dateString);
  const now = new Date();
  const diffMs = now.getTime() - date.getTime();
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMs / 3600000);
  const diffDays = Math.floor(diffMs / 86400000);
  
  if (diffMins < 1) return 'Just now';
  if (diffMins < 60) return `${diffMins}m ago`;
  if (diffHours < 24) return `${diffHours}h ago`;
  if (diffDays < 7) return `${diffDays}d ago`;
  
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

onMounted(() => {
  fetchCount();
  
  // Poll for new notifications every 30 seconds
  setInterval(fetchCount, 30000);
});
</script>

<style scoped>
.notification-enter-active,
.notification-leave-active {
  transition: opacity 0.3s ease;
}

.notification-enter-from,
.notification-leave-to {
  opacity: 0;
}

.notification-enter-active .fixed.top-0,
.notification-leave-active .fixed.top-0 {
  transition: transform 0.3s ease;
}

.notification-enter-from .fixed.top-0,
.notification-leave-to .fixed.top-0 {
  transform: translateX(100%);
}
</style>
