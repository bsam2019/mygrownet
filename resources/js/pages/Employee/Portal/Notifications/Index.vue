<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/Layouts/EmployeePortalLayout.vue';
import {
    BellIcon,
    CheckIcon,
    ClipboardDocumentListIcon,
    CalendarDaysIcon,
    FlagIcon,
    InformationCircleIcon,
} from '@heroicons/vue/24/outline';

interface Notification {
    id: number;
    type: string;
    title: string;
    message: string;
    action_url: string;
    read_at: string;
    created_at: string;
}

interface Props {
    notifications: {
        data: Notification[];
        links: any;
    };
}

const props = defineProps<Props>();

const getTypeIcon = (type: string) => {
    const icons: Record<string, any> = {
        'task': ClipboardDocumentListIcon,
        'time_off': CalendarDaysIcon,
        'goal': FlagIcon,
        'info': InformationCircleIcon,
    };
    return icons[type] || BellIcon;
};

const getTypeColor = (type: string) => {
    const colors: Record<string, string> = {
        'task': 'bg-blue-100 text-blue-600',
        'time_off': 'bg-purple-100 text-purple-600',
        'goal': 'bg-emerald-100 text-emerald-600',
        'info': 'bg-gray-100 text-gray-600',
    };
    return colors[type] || 'bg-gray-100 text-gray-600';
};

const markAsRead = (notificationId: number) => {
    router.patch(route('employee.portal.notifications.mark-read', notificationId), {}, {
        preserveScroll: true,
    });
};

const markAllAsRead = () => {
    router.post(route('employee.portal.notifications.mark-all-read'), {}, {
        preserveScroll: true,
    });
};

const formatTime = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now.getTime() - date.getTime();
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);

    if (minutes < 60) return `${minutes}m ago`;
    if (hours < 24) return `${hours}h ago`;
    if (days < 7) return `${days}d ago`;
    return date.toLocaleDateString();
};

const unreadCount = props.notifications.data.filter(n => !n.read_at).length;
</script>

<template>
    <Head title="Notifications" />

    <EmployeePortalLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
                    <p class="text-gray-500 mt-1">
                        {{ unreadCount > 0 ? `${unreadCount} unread notifications` : 'All caught up!' }}
                    </p>
                </div>
                <button v-if="unreadCount > 0"
                    @click="markAllAsRead"
                    class="inline-flex items-center px-4 py-2 text-sm text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors">
                    <CheckIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                    Mark all as read
                </button>
            </div>

            <!-- Notifications List -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="divide-y divide-gray-100">
                    <div v-for="notification in notifications.data" :key="notification.id"
                        :class="[
                            'p-4 transition-colors',
                            notification.read_at ? 'bg-white' : 'bg-blue-50/50'
                        ]">
                        <div class="flex items-start gap-4">
                            <div :class="[getTypeColor(notification.type), 'p-2 rounded-lg']">
                                <component :is="getTypeIcon(notification.type)" class="h-5 w-5" aria-hidden="true" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 :class="[
                                            'font-medium',
                                            notification.read_at ? 'text-gray-700' : 'text-gray-900'
                                        ]">
                                            {{ notification.title }}
                                        </h3>
                                        <p class="text-sm text-gray-500 mt-1">{{ notification.message }}</p>
                                    </div>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <span class="text-xs text-gray-400">
                                            {{ formatTime(notification.created_at) }}
                                        </span>
                                        <button v-if="!notification.read_at"
                                            @click="markAsRead(notification.id)"
                                            class="p-1 hover:bg-gray-100 rounded transition-colors"
                                            title="Mark as read"
                                            aria-label="Mark as read">
                                            <CheckIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
                                        </button>
                                    </div>
                                </div>
                                <a v-if="notification.action_url"
                                    :href="notification.action_url"
                                    class="inline-block mt-2 text-sm text-blue-600 hover:text-blue-700">
                                    View details →
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="notifications.data.length === 0" class="p-12 text-center">
                        <BellIcon class="h-12 w-12 mx-auto text-gray-300 mb-4" aria-hidden="true" />
                        <h3 class="text-lg font-medium text-gray-900">No notifications</h3>
                        <p class="text-gray-500 mt-1">You're all caught up!</p>
                    </div>
                </div>
            </div>
        </div>
    </EmployeePortalLayout>
</template>
