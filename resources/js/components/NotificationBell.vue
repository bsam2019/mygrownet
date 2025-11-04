<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { BellIcon } from 'lucide-vue-next';
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
        
        // Update local state
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
        
        // Update local state
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
    if (!notification.read_at) {
        markAsRead(notification.id);
    }
    if (notification.action_url) {
        router.visit(notification.action_url);
    }
    showDropdown.value = false;
};

const formatTime = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now.getTime() - date.getTime();
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);
    
    if (minutes < 1) return 'Just now';
    if (minutes < 60) return `${minutes}m ago`;
    if (hours < 24) return `${hours}h ago`;
    if (days < 7) return `${days}d ago`;
    return date.toLocaleDateString();
};

// Poll for new notifications every 30 seconds
onMounted(() => {
    fetchCount();
    const interval = setInterval(fetchCount, 30000);
    return () => clearInterval(interval);
});
</script>

<template>
    <div class="relative">
        <!-- Bell Icon -->
        <button
            @click="toggleDropdown"
            class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
        >
            <BellIcon class="h-6 w-6" />
            <span
                v-if="unreadCount > 0"
                class="absolute top-1 right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-red-600 rounded-full min-w-[18px]"
            >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </button>

        <!-- Dropdown -->
        <div
            v-if="showDropdown"
            class="fixed md:absolute right-0 md:right-0 left-0 md:left-auto mt-2 md:w-96 w-screen md:max-w-md bg-white rounded-none md:rounded-lg shadow-lg border-t md:border border-gray-200 z-50 max-h-[80vh] md:max-h-none"
            @click.stop
        >
            <!-- Header -->
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Notifications</h3>
                <button
                    v-if="unreadCount > 0"
                    @click="markAllAsRead"
                    class="text-sm text-blue-600 hover:text-blue-700"
                >
                    Mark all read
                </button>
            </div>

            <!-- Notifications List -->
            <div class="max-h-96 overflow-y-auto">
                <div v-if="loading" class="p-8 text-center text-gray-500">
                    Loading...
                </div>

                <div v-else-if="notifications.length === 0" class="p-8 text-center text-gray-500">
                    No notifications yet
                </div>

                <div v-else>
                    <button
                        v-for="notification in notifications"
                        :key="notification.id"
                        @click="handleNotificationClick(notification)"
                        class="w-full px-4 py-3 hover:bg-gray-50 transition-colors border-b border-gray-100 text-left"
                        :class="{ 'bg-blue-50': !notification.read_at }"
                    >
                        <div class="flex items-start gap-3">
                            <div
                                v-if="!notification.read_at"
                                class="w-2 h-2 bg-blue-600 rounded-full mt-2 flex-shrink-0"
                            ></div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900 text-sm">{{ notification.title }}</p>
                                <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ notification.message }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ formatTime(notification.created_at) }}</p>
                            </div>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">
                <Link
                    :href="route('mygrownet.notifications.center')"
                    class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                    @click="showDropdown = false"
                >
                    View all notifications →
                </Link>
            </div>
        </div>

        <!-- Backdrop -->
        <div
            v-if="showDropdown"
            @click="showDropdown = false"
            class="fixed inset-0 z-40"
        ></div>
    </div>
</template>
