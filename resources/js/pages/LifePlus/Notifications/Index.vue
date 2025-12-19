<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import LifePlusLayout from '@/layouts/LifePlusLayout.vue';
import {
    BellIcon,
    CheckCircleIcon,
    ExclamationCircleIcon,
    InformationCircleIcon,
    SparklesIcon,
    ClockIcon,
    TrashIcon,
} from '@heroicons/vue/24/outline';
import { CheckCircleIcon as CheckCircleIconSolid } from '@heroicons/vue/24/solid';

defineOptions({ layout: LifePlusLayout });

interface Notification {
    id: string | number;
    type: 'success' | 'info' | 'warning' | 'reminder';
    title: string;
    message: string;
    time: string;
    read: boolean;
    action_url?: string;
}

const props = defineProps<{
    notifications: Notification[];
}>();

// Local state to track read/deleted notifications (since backend is mock)
const localNotifications = ref([...props.notifications]);

const unreadCount = computed(() => 
    localNotifications.value.filter(n => !n.read).length
);

const getIcon = (type: string) => {
    switch (type) {
        case 'success':
            return CheckCircleIcon;
        case 'warning':
            return ExclamationCircleIcon;
        case 'reminder':
            return ClockIcon;
        default:
            return InformationCircleIcon;
    }
};

const getIconColor = (type: string) => {
    switch (type) {
        case 'success':
            return 'text-emerald-600 bg-emerald-50';
        case 'warning':
            return 'text-amber-600 bg-amber-50';
        case 'reminder':
            return 'text-purple-600 bg-purple-50';
        default:
            return 'text-blue-600 bg-blue-50';
    }
};

const formatTime = (time: string) => {
    const date = new Date(time);
    const now = new Date();
    const diff = now.getTime() - date.getTime();
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);

    if (minutes < 1) return 'Just now';
    if (minutes < 60) return `${minutes}m ago`;
    if (hours < 24) return `${hours}h ago`;
    if (days < 7) return `${days}d ago`;
    return date.toLocaleDateString();
};

const markAsRead = (id: string | number) => {
    // Update local state for immediate UI feedback
    const notification = localNotifications.value.find(n => n.id === id);
    if (notification) {
        notification.read = true;
    }
    // Call backend to persist
    router.post(route('lifeplus.notifications.read', id), {}, { preserveScroll: true });
};

const deleteNotification = (id: string | number) => {
    // Update local state for immediate UI feedback
    localNotifications.value = localNotifications.value.filter(n => n.id !== id);
    // Call backend to persist
    router.delete(route('lifeplus.notifications.destroy', id), { preserveScroll: true });
};

const markAllAsRead = () => {
    // Update local state (mock implementation)
    localNotifications.value.forEach(n => n.read = true);
    // Also call backend (for when real implementation is ready)
    router.post(route('lifeplus.notifications.read-all'), {}, { preserveScroll: true });
};
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-pink-50 via-rose-50 to-purple-50 p-4 space-y-4">
        <!-- Header with Gradient -->
        <div class="bg-gradient-to-br from-pink-500 via-rose-500 to-purple-600 rounded-3xl p-6 shadow-lg text-white">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-md">
                            <BellIcon class="h-6 w-6" aria-hidden="true" />
                        </div>
                        <h1 class="text-2xl font-bold">Notifications</h1>
                    </div>
                    <p v-if="unreadCount > 0" class="text-sm text-pink-100">
                        {{ unreadCount }} unread notification{{ unreadCount !== 1 ? 's' : '' }}
                    </p>
                    <p v-else class="text-sm text-pink-100">
                        You're all caught up! ✨
                    </p>
                </div>
                <button
                    v-if="unreadCount > 0"
                    @click="markAllAsRead"
                    class="px-4 py-2 bg-white/20 backdrop-blur-sm rounded-xl text-sm font-semibold hover:bg-white/30 transition-all shadow-md"
                >
                    Mark all read
                </button>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="localNotifications.length === 0" class="text-center py-12 bg-white rounded-3xl shadow-lg">
            <div class="w-20 h-20 rounded-full bg-gradient-to-br from-pink-100 to-purple-100 flex items-center justify-center mx-auto mb-4 shadow-md">
                <BellIcon class="h-10 w-10 text-pink-500" aria-hidden="true" />
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-1">No notifications</h3>
            <p class="text-sm text-gray-500">You're all caught up! ✨</p>
        </div>

        <!-- Notifications List -->
        <div v-else class="space-y-3">
            <div
                v-for="notification in localNotifications"
                :key="notification.id"
                :class="[
                    'bg-white rounded-3xl p-5 shadow-lg border-2 transition-all hover:scale-[1.02]',
                    notification.read ? 'border-gray-100' : 'border-pink-200 bg-gradient-to-br from-white to-pink-50'
                ]"
            >
                <div class="flex gap-3">
                    <!-- Icon -->
                    <div :class="['w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0', getIconColor(notification.type)]">
                        <component :is="getIcon(notification.type)" class="h-5 w-5" aria-hidden="true" />
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2 mb-1">
                            <h3 :class="['font-semibold text-sm', notification.read ? 'text-gray-700' : 'text-gray-900']">
                                {{ notification.title }}
                            </h3>
                            <span class="text-xs text-gray-500 whitespace-nowrap">
                                {{ formatTime(notification.time) }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 mb-2">{{ notification.message }}</p>

                        <!-- Actions -->
                        <div class="flex items-center gap-3">
                            <button
                                v-if="!notification.read"
                                @click="markAsRead(notification.id)"
                                class="text-xs font-medium text-emerald-600 hover:text-emerald-700 flex items-center gap-1"
                            >
                                <CheckCircleIconSolid class="h-4 w-4" aria-hidden="true" />
                                Mark as read
                            </button>
                            <button
                                @click="deleteNotification(notification.id)"
                                class="text-xs font-medium text-gray-500 hover:text-red-600 flex items-center gap-1"
                            >
                                <TrashIcon class="h-4 w-4" aria-hidden="true" />
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
