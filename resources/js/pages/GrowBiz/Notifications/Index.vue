<script setup lang="ts">
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import GrowBizLayout from '@/Layouts/GrowBizLayout.vue';
import {
    BellIcon,
    CheckIcon,
    ClipboardDocumentListIcon,
    UserPlusIcon,
    ChatBubbleLeftIcon,
    ClockIcon,
} from '@heroicons/vue/24/outline';

interface Notification {
    id: string;
    type: string;
    title: string;
    message: string;
    url: string | null;
    read: boolean;
    readAt: string | null;
    createdAt: string;
    timeAgo: string;
}

const props = defineProps<{
    notifications: {
        data: Notification[];
        current_page: number;
        last_page: number;
    };
    unreadCount: number;
}>();

const getIcon = (type: string) => {
    switch (type) {
        case 'growbiz_task_assigned':
        case 'growbiz_task_status_changed':
            return ClipboardDocumentListIcon;
        case 'growbiz_task_comment':
            return ChatBubbleLeftIcon;
        case 'growbiz_task_due_reminder':
            return ClockIcon;
        case 'growbiz_employee_invitation':
            return UserPlusIcon;
        default:
            return BellIcon;
    }
};

const getIconColor = (type: string) => {
    switch (type) {
        case 'growbiz_task_assigned':
            return 'bg-blue-100 text-blue-600';
        case 'growbiz_task_status_changed':
            return 'bg-emerald-100 text-emerald-600';
        case 'growbiz_task_comment':
            return 'bg-purple-100 text-purple-600';
        case 'growbiz_task_due_reminder':
            return 'bg-amber-100 text-amber-600';
        case 'growbiz_employee_invitation':
            return 'bg-indigo-100 text-indigo-600';
        default:
            return 'bg-gray-100 text-gray-600';
    }
};

const markAsRead = (id: string) => {
    router.post(route('growbiz.notifications.read', id), {}, {
        preserveScroll: true,
    });
};

const markAllAsRead = () => {
    router.post(route('growbiz.notifications.mark-all-read'), {}, {
        preserveScroll: true,
    });
};

const handleNotificationClick = (notification: Notification) => {
    if (!notification.read) {
        markAsRead(notification.id);
    }
    if (notification.url) {
        router.visit(notification.url);
    }
};
</script>

<template>
    <GrowBizLayout>
        <div class="p-4">
            <!-- Header -->
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Notifications</h1>
                    <p class="text-sm text-gray-500">
                        {{ unreadCount > 0 ? `${unreadCount} unread` : 'All caught up!' }}
                    </p>
                </div>
                <button
                    v-if="unreadCount > 0"
                    @click="markAllAsRead"
                    class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-emerald-600 bg-emerald-50 rounded-lg hover:bg-emerald-100 transition-colors"
                >
                    <CheckIcon class="h-4 w-4" />
                    Mark all read
                </button>
            </div>

            <!-- Notifications List -->
            <div class="space-y-2">
                <div
                    v-for="notification in notifications.data"
                    :key="notification.id"
                    @click="handleNotificationClick(notification)"
                    :class="[
                        'flex items-start gap-3 p-4 rounded-xl transition-all cursor-pointer',
                        notification.read 
                            ? 'bg-white' 
                            : 'bg-emerald-50/50 border border-emerald-100'
                    ]"
                >
                    <!-- Icon -->
                    <div :class="['flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center', getIconColor(notification.type)]">
                        <component :is="getIcon(notification.type)" class="h-5 w-5" />
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <p :class="['text-sm', notification.read ? 'text-gray-600' : 'text-gray-900 font-medium']">
                                {{ notification.title }}
                            </p>
                            <span class="text-xs text-gray-400 whitespace-nowrap">
                                {{ notification.timeAgo }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 mt-0.5 line-clamp-2">
                            {{ notification.message }}
                        </p>
                    </div>

                    <!-- Unread indicator -->
                    <div v-if="!notification.read" class="flex-shrink-0 w-2 h-2 bg-emerald-500 rounded-full mt-2"></div>
                </div>

                <!-- Empty State -->
                <div v-if="notifications.data.length === 0" class="text-center py-12">
                    <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <BellIcon class="h-8 w-8 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">No notifications</h3>
                    <p class="text-sm text-gray-500">You're all caught up! Check back later.</p>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="notifications.last_page > 1" class="flex justify-center gap-2 mt-6">
                <Link
                    v-for="page in notifications.last_page"
                    :key="page"
                    :href="route('growbiz.notifications.index', { page })"
                    :class="[
                        'w-8 h-8 flex items-center justify-center rounded-lg text-sm font-medium transition-colors',
                        page === notifications.current_page
                            ? 'bg-emerald-600 text-white'
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                    ]"
                >
                    {{ page }}
                </Link>
            </div>
        </div>
    </GrowBizLayout>
</template>
