<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import {
    BellIcon,
    CheckIcon,
    TrashIcon,
    FunnelIcon,
    DocumentTextIcon,
    BanknotesIcon,
    ExclamationTriangleIcon,
    ChartBarIcon,
    CurrencyDollarIcon,
    BuildingLibraryIcon,
} from '@heroicons/vue/24/outline';

interface Notification {
    id: string;
    type: string;
    data: {
        title: string;
        message: string;
        type: 'info' | 'success' | 'warning' | 'error';
        category: string;
        action_url?: string;
        action_text?: string;
        [key: string]: unknown;
    };
    read_at: string | null;
    created_at: string;
}

const props = defineProps<{
    notifications: Notification[];
    unreadCount: number;
}>();

const selectedFilter = ref<string>('all');
const selectedNotifications = ref<Set<string>>(new Set());

const filters = [
    { value: 'all', label: 'All' },
    { value: 'unread', label: 'Unread' },
    { value: 'invoices', label: 'Invoices' },
    { value: 'sales', label: 'Sales' },
    { value: 'expenses', label: 'Expenses' },
    { value: 'banking', label: 'Banking' },
    { value: 'reports', label: 'Reports' },
];

const filteredNotifications = computed(() => {
    if (selectedFilter.value === 'all') return props.notifications;
    if (selectedFilter.value === 'unread') return props.notifications.filter(n => !n.read_at);
    return props.notifications.filter(n => n.data.category === selectedFilter.value);
});

const getIcon = (category: string) => {
    const icons: Record<string, typeof DocumentTextIcon> = {
        invoices: DocumentTextIcon,
        sales: CurrencyDollarIcon,
        payments: BanknotesIcon,
        expenses: BanknotesIcon,
        banking: BuildingLibraryIcon,
        reports: ChartBarIcon,
    };
    return icons[category] || BellIcon;
};

const getIconColor = (type: string) => {
    const colors: Record<string, string> = {
        info: 'text-blue-500 bg-blue-50',
        success: 'text-emerald-500 bg-emerald-50',
        warning: 'text-amber-500 bg-amber-50',
        error: 'text-red-500 bg-red-50',
    };
    return colors[type] || colors.info;
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
    return date.toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });
};

const formatFullDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-GB', {
        weekday: 'short',
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const markAsRead = (id: string) => {
    router.post(route('growfinance.notifications.mark-read', id), {}, {
        preserveScroll: true,
        preserveState: true,
    });
};

const markAllAsRead = () => {
    router.post(route('growfinance.notifications.mark-all-read'), {}, {
        preserveScroll: true,
    });
};

const deleteNotification = (id: string) => {
    router.delete(route('growfinance.notifications.destroy', id), {
        preserveScroll: true,
    });
};

const handleNotificationClick = (notification: Notification) => {
    if (!notification.read_at) {
        markAsRead(notification.id);
    }
    if (notification.data.action_url) {
        router.visit(notification.data.action_url);
    }
};

const toggleSelection = (id: string) => {
    if (selectedNotifications.value.has(id)) {
        selectedNotifications.value.delete(id);
    } else {
        selectedNotifications.value.add(id);
    }
};

const selectAll = () => {
    if (selectedNotifications.value.size === filteredNotifications.value.length) {
        selectedNotifications.value.clear();
    } else {
        filteredNotifications.value.forEach(n => selectedNotifications.value.add(n.id));
    }
};

const deleteSelected = () => {
    selectedNotifications.value.forEach(id => {
        router.delete(route('growfinance.notifications.destroy', id), {
            preserveScroll: true,
        });
    });
    selectedNotifications.value.clear();
};
</script>

<template>
    <GrowFinanceLayout>
        <Head title="Notifications - GrowFinance" />

        <div class="p-4 lg:p-6 max-w-4xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-xl lg:text-2xl font-bold text-gray-900">Notifications</h1>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ unreadCount }} unread notification{{ unreadCount !== 1 ? 's' : '' }}
                    </p>
                </div>
                <button
                    v-if="unreadCount > 0"
                    @click="markAllAsRead"
                    class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors"
                >
                    <CheckIcon class="h-4 w-4" aria-hidden="true" />
                    Mark all read
                </button>
            </div>

            <!-- Filters -->
            <div class="flex items-center gap-2 mb-4 overflow-x-auto pb-2 hide-scrollbar">
                <FunnelIcon class="h-4 w-4 text-gray-400 flex-shrink-0" aria-hidden="true" />
                <button
                    v-for="filter in filters"
                    :key="filter.value"
                    @click="selectedFilter = filter.value"
                    :class="[
                        'px-3 py-1.5 text-sm font-medium rounded-full whitespace-nowrap transition-colors',
                        selectedFilter === filter.value
                            ? 'bg-emerald-100 text-emerald-700'
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                    ]"
                >
                    {{ filter.label }}
                </button>
            </div>

            <!-- Bulk Actions -->
            <div v-if="selectedNotifications.size > 0" class="flex items-center gap-3 mb-4 p-3 bg-gray-50 rounded-lg">
                <span class="text-sm text-gray-600">{{ selectedNotifications.size }} selected</span>
                <button
                    @click="deleteSelected"
                    class="flex items-center gap-1 px-2 py-1 text-sm text-red-600 hover:bg-red-50 rounded transition-colors"
                >
                    <TrashIcon class="h-4 w-4" aria-hidden="true" />
                    Delete
                </button>
            </div>

            <!-- Notification List -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div v-if="filteredNotifications.length === 0" class="p-8 text-center">
                    <BellIcon class="h-12 w-12 text-gray-300 mx-auto mb-3" aria-hidden="true" />
                    <p class="text-gray-500">No notifications found</p>
                </div>

                <div v-else class="divide-y divide-gray-100">
                    <div
                        v-for="notification in filteredNotifications"
                        :key="notification.id"
                        :class="[
                            'flex items-start gap-3 p-4 transition-colors cursor-pointer',
                            notification.read_at ? 'bg-white hover:bg-gray-50' : 'bg-blue-50/30 hover:bg-blue-50/50'
                        ]"
                        @click="handleNotificationClick(notification)"
                    >
                        <!-- Checkbox -->
                        <input
                            type="checkbox"
                            :checked="selectedNotifications.has(notification.id)"
                            @click.stop="toggleSelection(notification.id)"
                            class="mt-1 h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                        />

                        <!-- Icon -->
                        <div :class="['p-2 rounded-full flex-shrink-0', getIconColor(notification.data.type)]">
                            <component
                                :is="getIcon(notification.data.category)"
                                class="h-5 w-5"
                                aria-hidden="true"
                            />
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <p :class="[
                                    'text-sm',
                                    notification.read_at ? 'text-gray-700' : 'text-gray-900 font-medium'
                                ]">
                                    {{ notification.data.title }}
                                </p>
                                <span class="text-xs text-gray-400 whitespace-nowrap">
                                    {{ formatTime(notification.created_at) }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 mt-0.5 line-clamp-2">
                                {{ notification.data.message }}
                            </p>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                    {{ notification.data.category }}
                                </span>
                                <span class="text-xs text-gray-400" :title="formatFullDate(notification.created_at)">
                                    {{ formatFullDate(notification.created_at) }}
                                </span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-1 flex-shrink-0">
                            <button
                                v-if="!notification.read_at"
                                @click.stop="markAsRead(notification.id)"
                                class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded transition-colors"
                                title="Mark as read"
                            >
                                <CheckIcon class="h-4 w-4" aria-hidden="true" />
                            </button>
                            <button
                                @click.stop="deleteNotification(notification.id)"
                                class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors"
                                title="Delete"
                            >
                                <TrashIcon class="h-4 w-4" aria-hidden="true" />
                            </button>
                        </div>

                        <!-- Unread indicator -->
                        <div
                            v-if="!notification.read_at"
                            class="w-2 h-2 bg-blue-600 rounded-full flex-shrink-0 mt-2"
                        />
                    </div>
                </div>
            </div>
        </div>
    </GrowFinanceLayout>
</template>
