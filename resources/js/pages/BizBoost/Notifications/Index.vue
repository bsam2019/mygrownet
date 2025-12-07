<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import {
    BellIcon,
    CheckIcon,
    TrashIcon,
    ArchiveBoxIcon,
    FunnelIcon,
    ShoppingBagIcon,
    UserPlusIcon,
    DocumentTextIcon,
    CurrencyDollarIcon,
    SparklesIcon,
    ExclamationTriangleIcon,
    InformationCircleIcon,
} from '@heroicons/vue/24/outline';

interface Notification {
    id: string;
    type: string;
    category: string;
    title: string;
    message: string;
    actionUrl?: string;
    actionText?: string;
    priority: string;
    read: boolean;
    readAt?: string;
    createdAt: string;
    timeAgo: string;
}

interface Props {
    notifications: {
        data: Notification[];
        links: any;
        meta?: any;
    };
    unreadCount: number;
}

const props = defineProps<Props>();

const selectedFilter = ref<'all' | 'unread' | 'read'>('all');
const loading = ref(false);

const filteredNotifications = () => {
    if (selectedFilter.value === 'unread') {
        return props.notifications.data.filter(n => !n.read);
    }
    if (selectedFilter.value === 'read') {
        return props.notifications.data.filter(n => n.read);
    }
    return props.notifications.data;
};

const markAsRead = async (id: string) => {
    try {
        await fetch(`/bizboost/notifications/${id}/read`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });
        router.reload({ only: ['notifications', 'unreadCount'] });
    } catch (error) {
        console.error('Failed to mark as read:', error);
    }
};

const markAllAsRead = async () => {
    loading.value = true;
    try {
        await fetch('/bizboost/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });
        router.reload({ only: ['notifications', 'unreadCount'] });
    } catch (error) {
        console.error('Failed to mark all as read:', error);
    } finally {
        loading.value = false;
    }
};

const archiveNotification = async (id: string) => {
    try {
        await fetch(`/bizboost/notifications/${id}/archive`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });
        router.reload({ only: ['notifications', 'unreadCount'] });
    } catch (error) {
        console.error('Failed to archive:', error);
    }
};

const deleteNotification = async (id: string) => {
    try {
        await fetch(`/bizboost/notifications/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });
        router.reload({ only: ['notifications', 'unreadCount'] });
    } catch (error) {
        console.error('Failed to delete:', error);
    }
};

const handleNotificationClick = (notification: Notification) => {
    if (!notification.read) {
        markAsRead(notification.id);
    }
    if (notification.actionUrl) {
        router.visit(notification.actionUrl);
    }
};

const getNotificationIcon = (type: string) => {
    switch (type) {
        case 'sale': return CurrencyDollarIcon;
        case 'customer': return UserPlusIcon;
        case 'post': return DocumentTextIcon;
        case 'product': return ShoppingBagIcon;
        case 'ai': return SparklesIcon;
        case 'warning': return ExclamationTriangleIcon;
        default: return InformationCircleIcon;
    }
};

const getNotificationColor = (type: string, priority: string) => {
    if (priority === 'high') return 'text-red-500 bg-red-100';
    switch (type) {
        case 'sale': return 'text-emerald-600 bg-emerald-100';
        case 'customer': return 'text-blue-600 bg-blue-100';
        case 'post': return 'text-violet-600 bg-violet-100';
        case 'product': return 'text-amber-600 bg-amber-100';
        case 'ai': return 'text-purple-600 bg-purple-100';
        case 'warning': return 'text-orange-600 bg-orange-100';
        default: return 'text-slate-600 bg-slate-100';
    }
};
</script>

<template>
    <Head title="Notifications - BizBoost" />

    <BizBoostLayout>
        <div class="max-w-4xl mx-auto px-4 py-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Notifications</h1>
                    <p class="text-sm text-slate-500 mt-1">
                        {{ unreadCount }} unread notification{{ unreadCount !== 1 ? 's' : '' }}
                    </p>
                </div>
                <button
                    v-if="unreadCount > 0"
                    @click="markAllAsRead"
                    :disabled="loading"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-violet-600 bg-violet-50 hover:bg-violet-100 rounded-lg transition-colors disabled:opacity-50"
                >
                    <CheckIcon class="h-4 w-4" aria-hidden="true" />
                    Mark all as read
                </button>
            </div>

            <!-- Filters -->
            <div class="flex items-center gap-2 mb-4">
                <button
                    v-for="filter in ['all', 'unread', 'read']"
                    :key="filter"
                    @click="selectedFilter = filter as 'all' | 'unread' | 'read'"
                    :class="[
                        'px-3 py-1.5 text-sm font-medium rounded-lg transition-colors',
                        selectedFilter === filter
                            ? 'bg-violet-100 text-violet-700'
                            : 'text-slate-600 hover:bg-slate-100'
                    ]"
                >
                    {{ filter.charAt(0).toUpperCase() + filter.slice(1) }}
                </button>
            </div>

            <!-- Notifications List -->
            <div class="bg-white rounded-xl shadow-sm ring-1 ring-slate-200 overflow-hidden">
                <div v-if="filteredNotifications().length === 0" class="px-6 py-12 text-center">
                    <BellIcon class="h-12 w-12 text-slate-300 mx-auto mb-3" aria-hidden="true" />
                    <p class="text-slate-500">No notifications to show</p>
                </div>

                <div v-else class="divide-y divide-slate-100">
                    <div
                        v-for="notification in filteredNotifications()"
                        :key="notification.id"
                        :class="[
                            'flex items-start gap-4 p-4 transition-colors',
                            notification.read ? 'bg-white' : 'bg-violet-50/50'
                        ]"
                    >
                        <!-- Icon -->
                        <div :class="['p-2.5 rounded-xl shrink-0', getNotificationColor(notification.type, notification.priority)]">
                            <component :is="getNotificationIcon(notification.type)" class="h-5 w-5" aria-hidden="true" />
                        </div>

                        <!-- Content -->
                        <button
                            @click="handleNotificationClick(notification)"
                            class="flex-1 min-w-0 text-left"
                        >
                            <p :class="['text-sm', notification.read ? 'text-slate-700' : 'text-slate-900 font-semibold']">
                                {{ notification.title }}
                            </p>
                            <p class="text-sm text-slate-500 mt-1">
                                {{ notification.message }}
                            </p>
                            <p class="text-xs text-slate-400 mt-2">
                                {{ notification.timeAgo }}
                            </p>
                        </button>

                        <!-- Actions -->
                        <div class="flex items-center gap-1 shrink-0">
                            <button
                                v-if="!notification.read"
                                @click.stop="markAsRead(notification.id)"
                                class="p-2 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors"
                                title="Mark as read"
                            >
                                <CheckIcon class="h-4 w-4" aria-hidden="true" />
                            </button>
                            <button
                                @click.stop="archiveNotification(notification.id)"
                                class="p-2 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors"
                                title="Archive"
                            >
                                <ArchiveBoxIcon class="h-4 w-4" aria-hidden="true" />
                            </button>
                            <button
                                @click.stop="deleteNotification(notification.id)"
                                class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                title="Delete"
                            >
                                <TrashIcon class="h-4 w-4" aria-hidden="true" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="notifications.links && notifications.links.length > 3" class="mt-6 flex justify-center">
                <nav class="flex items-center gap-1">
                    <template v-for="link in notifications.links" :key="link.label">
                        <button
                            v-if="link.url"
                            @click="router.visit(link.url)"
                            :class="[
                                'px-3 py-1.5 text-sm rounded-lg transition-colors',
                                link.active
                                    ? 'bg-violet-600 text-white'
                                    : 'text-slate-600 hover:bg-slate-100'
                            ]"
                            v-html="link.label"
                        />
                        <span
                            v-else
                            class="px-3 py-1.5 text-sm text-slate-400"
                            v-html="link.label"
                        />
                    </template>
                </nav>
            </div>
        </div>
    </BizBoostLayout>
</template>
