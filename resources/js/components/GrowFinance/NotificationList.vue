<script setup lang="ts">
import { computed } from 'vue';
import { 
    DocumentTextIcon, 
    CheckCircleIcon, 
    ExclamationTriangleIcon,
    BanknotesIcon,
    CurrencyDollarIcon,
    ChartBarIcon,
    BellIcon
} from '@heroicons/vue/24/outline';

interface Notification {
    id: string;
    type: string;
    data: {
        title: string;
        message: string;
        type: 'info' | 'success' | 'warning' | 'error';
        action_url?: string;
        action_text?: string;
    };
    read_at: string | null;
    created_at: string;
}

const props = defineProps<{
    notifications: Notification[];
    loading?: boolean;
}>();

const emit = defineEmits<{
    (e: 'mark-read', id: string): void;
    (e: 'mark-all-read'): void;
    (e: 'view', notification: Notification): void;
}>();

const getIcon = (type: string) => {
    if (type.includes('Invoice')) return DocumentTextIcon;
    if (type.includes('Paid') || type.includes('Success')) return CheckCircleIcon;
    if (type.includes('Overdue') || type.includes('Alert')) return ExclamationTriangleIcon;
    if (type.includes('Expense')) return BanknotesIcon;
    if (type.includes('Sale')) return CurrencyDollarIcon;
    if (type.includes('Summary')) return ChartBarIcon;
    return BellIcon;
};


const getIconColor = (type: 'info' | 'success' | 'warning' | 'error') => {
    const colors = {
        info: 'text-blue-500',
        success: 'text-emerald-500',
        warning: 'text-amber-500',
        error: 'text-red-500'
    };
    return colors[type] || colors.info;
};

const getBgColor = (type: 'info' | 'success' | 'warning' | 'error') => {
    const colors = {
        info: 'bg-blue-50',
        success: 'bg-emerald-50',
        warning: 'bg-amber-50',
        error: 'bg-red-50'
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
    return date.toLocaleDateString();
};

const hasUnread = computed(() => props.notifications.some(n => !n.read_at));
</script>


<template>
    <div class="bg-white rounded-lg shadow-lg border border-gray-200 max-h-96 overflow-hidden">
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
            <button
                v-if="hasUnread"
                @click="emit('mark-all-read')"
                class="text-xs text-blue-600 hover:text-blue-700 font-medium"
            >
                Mark all read
            </button>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="p-8 text-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
            <p class="mt-2 text-sm text-gray-500">Loading notifications...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="notifications.length === 0" class="p-8 text-center">
            <BellIcon class="h-12 w-12 text-gray-300 mx-auto" aria-hidden="true" />
            <p class="mt-2 text-sm text-gray-500">No notifications yet</p>
        </div>

        <!-- Notification List -->
        <div v-else class="overflow-y-auto max-h-80">
            <div
                v-for="notification in notifications"
                :key="notification.id"
                @click="emit('view', notification)"
                :class="[
                    'px-4 py-3 border-b border-gray-100 cursor-pointer transition-colors',
                    notification.read_at ? 'bg-white hover:bg-gray-50' : 'bg-blue-50/50 hover:bg-blue-50'
                ]"
            >
                <div class="flex items-start gap-3">
                    <!-- Icon -->
                    <div :class="['p-2 rounded-full', getBgColor(notification.data.type)]">
                        <component
                            :is="getIcon(notification.type)"
                            :class="['h-4 w-4', getIconColor(notification.data.type)]"
                            aria-hidden="true"
                        />
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <p :class="[
                            'text-sm',
                            notification.read_at ? 'text-gray-700' : 'text-gray-900 font-medium'
                        ]">
                            {{ notification.data.title }}
                        </p>
                        <p class="text-xs text-gray-500 mt-0.5 truncate">
                            {{ notification.data.message }}
                        </p>
                        <p class="text-xs text-gray-400 mt-1">
                            {{ formatTime(notification.created_at) }}
                        </p>
                    </div>

                    <!-- Unread indicator -->
                    <div
                        v-if="!notification.read_at"
                        class="w-2 h-2 bg-blue-600 rounded-full flex-shrink-0 mt-2"
                    ></div>
                </div>
            </div>
        </div>
    </div>
</template>
