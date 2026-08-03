<template>
    <GrowStreamLayout title="Notifications - GrowStream">
        <div class="mx-auto max-w-3xl">
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-[var(--gs-text)]">Notifications</h1>
                    <p v-if="unreadCount > 0" class="mt-2 text-[var(--gs-muted)]">{{ unreadCount }} unread</p>
                </div>
                <button
                    v-if="unreadCount > 0"
                    class="gs-btn gs-btn-outline"
                    @click="markAllAsRead"
                >
                    Mark all read
                </button>
            </div>

            <!-- Empty state -->
            <div
                v-if="notifications.data.length === 0"
                class="gs-card flex flex-col items-center py-16 text-center"
            >
                <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-[var(--gs-primary-soft)]">
                    <svg class="h-8 w-8 text-[var(--gs-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                        />
                    </svg>
                </div>
                <h2 class="mb-2 text-lg font-semibold text-[var(--gs-text)]">No notifications yet</h2>
                <p class="text-sm text-[var(--gs-muted)]">
                    We'll let you know when a creator you follow uploads, your content is approved, or your subscription changes.
                </p>
            </div>

            <!-- List -->
            <div v-else class="space-y-3">
                <div
                    v-for="notification in notifications.data"
                    :key="notification.id"
                    class="gs-card flex items-start gap-3 p-4"
                    :class="{ 'border-[var(--gs-primary)]/40': !notification.read }"
                    @click="markAsRead(notification)"
                >
                    <div
                        class="mt-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-full"
                        :class="categoryIcon(notification.category).bg"
                    >
                        <svg class="h-5 w-5" :class="categoryIcon(notification.category).color" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="categoryIcon(notification.category).icon" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="font-semibold text-[var(--gs-text)]">{{ notification.title }}</h3>
                            <span v-if="!notification.read" class="mt-1 h-2 w-2 shrink-0 rounded-full bg-[var(--gs-primary)]"></span>
                        </div>
                        <p class="mt-0.5 text-sm text-[var(--gs-muted)]">{{ notification.message }}</p>
                        <div class="mt-2 flex items-center gap-3">
                            <span class="text-xs text-[var(--gs-muted)]">{{ notification.timeAgo }}</span>
                            <Link
                                v-if="notification.actionUrl"
                                :href="notification.actionUrl"
                                class="text-xs font-medium text-[var(--gs-accent)] hover:opacity-85"
                                @click.stop
                            >
                                {{ notification.actionText || 'View' }}
                            </Link>
                            <button
                                class="text-xs text-[var(--gs-muted)] hover:text-[var(--gs-text)]"
                                @click.stop="deleteNotification(notification)"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="notifications.data.length > 0" class="flex items-center justify-between pt-2">
                    <p class="text-sm text-[var(--gs-muted)]">
                        Showing {{ notifications.from }} - {{ notifications.to }} of {{ notifications.total }}
                    </p>
                    <div class="flex gap-2">
                        <button
                            :disabled="!notifications.prev_page_url"
                            class="gs-btn gs-btn-outline px-3 py-1 text-sm disabled:opacity-50"
                            @click="page(notifications.current_page - 1)"
                        >
                            Prev
                        </button>
                        <button
                            :disabled="!notifications.next_page_url"
                            class="gs-btn gs-btn-outline px-3 py-1 text-sm disabled:opacity-50"
                            @click="page(notifications.current_page + 1)"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </GrowStreamLayout>
</template>

<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import GrowStreamLayout from '@/Layouts/GrowStreamLayout.vue';

interface NotificationItem {
    id: string;
    type: string;
    category: string;
    title: string;
    message: string;
    actionUrl?: string;
    actionText?: string;
    priority: string;
    read: boolean;
    createdAt: string;
    timeAgo: string;
}

interface Paginated<T> {
    data: T[];
    total: number;
    from: number | null;
    to: number | null;
    current_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
}

interface Props {
    notifications: Paginated<NotificationItem>;
    unreadCount: number;
}

defineProps<Props>();

const categoryIcon = (category: string) => {
    switch (category) {
        case 'subscription':
            return { bg: 'bg-[var(--gs-accent-soft)]', color: 'text-[var(--gs-accent)]', icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z' };
        default:
            return { bg: 'bg-[var(--gs-primary-soft)]', color: 'text-[var(--gs-primary)]', icon: 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z' };
    }
};

const page = (pageNumber: number) => {
    router.get(route('growstream.notifications'), { page: pageNumber }, { preserveState: true });
};

const markAsRead = (notification: NotificationItem) => {
    if (notification.read) return;
    router.post(route('growstream.notifications.mark-read', notification.id), {}, { preserveState: true });
};

const markAllAsRead = () => {
    router.post(route('growstream.notifications.mark-all-read'), {}, { preserveState: true });
};

const deleteNotification = (notification: NotificationItem) => {
    router.delete(route('growstream.notifications.destroy', notification.id), { preserveState: true });
};
</script>
