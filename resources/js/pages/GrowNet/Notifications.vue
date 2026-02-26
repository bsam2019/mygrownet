<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';
import { BellIcon, CheckIcon } from 'lucide-vue-next';
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

const notifications = ref<Notification[]>([]);
const loading = ref(true);
const filter = ref<'all' | 'unread'>('all');

const fetchNotifications = async () => {
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
    } catch (error) {
        console.error('Failed to mark as read:', error);
    }
};

const markAllAsRead = async () => {
    try {
        await axios.post(route('mygrownet.notifications.read-all'));
        
        // Update local state
        notifications.value.forEach(n => {
            n.read_at = new Date().toISOString();
        });
    } catch (error) {
        console.error('Failed to mark all as read:', error);
    }
};

const handleNotificationClick = (notification: Notification) => {
    if (!notification.read_at) {
        markAsRead(notification.id);
    }
    if (notification.action_url) {
        router.visit(notification.action_url);
    }
};

const filteredNotifications = computed(() => {
    if (filter.value === 'unread') {
        return notifications.value.filter(n => !n.read_at);
    }
    return notifications.value;
});

const unreadCount = computed(() => {
    return notifications.value.filter(n => !n.read_at).length;
});

const formatTime = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleString();
};

const getPriorityColor = (priority: string) => {
    switch (priority) {
        case 'urgent': return 'text-red-600';
        case 'high': return 'text-orange-600';
        default: return 'text-gray-600';
    }
};

onMounted(() => {
    fetchNotifications();
});
</script>

<template>
    <MemberLayout>
        <Head title="Notifications" />

        <div class="py-6 sm:py-8">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Notifications</h1>
                    <p class="mt-2 text-sm text-gray-600">
                        Stay updated with your latest activities
                    </p>
                </div>

                <!-- Actions Bar -->
                <div class="mb-6 flex items-center justify-between">
                    <div class="flex gap-2">
                        <button
                            @click="filter = 'all'"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                            :class="filter === 'all' 
                                ? 'bg-blue-600 text-white' 
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                        >
                            All
                        </button>
                        <button
                            @click="filter = 'unread'"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                            :class="filter === 'unread' 
                                ? 'bg-blue-600 text-white' 
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                        >
                            Unread ({{ unreadCount }})
                        </button>
                    </div>

                    <button
                        v-if="unreadCount > 0"
                        @click="markAllAsRead"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-blue-600 hover:text-blue-700"
                    >
                        <CheckIcon class="h-4 w-4" />
                        Mark all as read
                    </button>
                </div>

                <!-- Notifications List -->
                <div class="bg-white rounded-lg shadow">
                    <div v-if="loading" class="p-12 text-center text-gray-500">
                        Loading notifications...
                    </div>

                    <div v-else-if="filteredNotifications.length === 0" class="p-12 text-center">
                        <BellIcon class="h-12 w-12 text-gray-400 mx-auto mb-4" />
                        <p class="text-gray-500">
                            {{ filter === 'unread' ? 'No unread notifications' : 'No notifications yet' }}
                        </p>
                    </div>

                    <div v-else class="divide-y divide-gray-200">
                        <button
                            v-for="notification in filteredNotifications"
                            :key="notification.id"
                            @click="handleNotificationClick(notification)"
                            class="w-full px-6 py-4 hover:bg-gray-50 transition-colors text-left"
                            :class="{ 'bg-blue-50': !notification.read_at }"
                        >
                            <div class="flex items-start gap-4">
                                <div
                                    v-if="!notification.read_at"
                                    class="w-2 h-2 bg-blue-600 rounded-full mt-2 flex-shrink-0"
                                ></div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex-1">
                                            <p 
                                                class="font-semibold text-gray-900"
                                                :class="getPriorityColor(notification.priority)"
                                            >
                                                {{ notification.title }}
                                            </p>
                                            <p class="text-gray-600 mt-1">{{ notification.message }}</p>
                                            <p class="text-sm text-gray-500 mt-2">{{ formatTime(notification.created_at) }}</p>
                                        </div>
                                        <span
                                            v-if="notification.action_text"
                                            class="text-sm text-blue-600 font-medium whitespace-nowrap"
                                        >
                                            {{ notification.action_text }} →
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>
