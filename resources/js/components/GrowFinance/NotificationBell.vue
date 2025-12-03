<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { BellIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { BellAlertIcon } from '@heroicons/vue/24/solid';

interface NotificationData {
    title?: string;
    message: string;
    type?: string;
    category?: string;
    action_url?: string;
    [key: string]: unknown;
}

interface Notification {
    id: string;
    type: string;
    data: NotificationData;
    read_at: string | null;
    created_at: string;
}

const props = defineProps<{
    notifications?: Notification[];
    unreadCount?: number;
}>();

const emit = defineEmits<{
    (e: 'refresh'): void;
}>();

const page = usePage();
const userId = computed(() => (page.props.auth as { user?: { id: number } })?.user?.id);

const isOpen = ref(false);
const loading = ref(false);
const localNotifications = ref<Notification[]>(props.notifications || []);
const localUnreadCount = ref(props.unreadCount || 0);
const pollInterval = ref<ReturnType<typeof setInterval> | null>(null);
const echoChannel = ref<ReturnType<typeof window.Echo.private> | null>(null);

// Watch for prop changes
watch(() => props.notifications, (newVal) => {
    if (newVal) localNotifications.value = newVal;
}, { deep: true });

watch(() => props.unreadCount, (newVal) => {
    if (newVal !== undefined) localUnreadCount.value = newVal;
});

// Fetch notifications from API
const fetchNotifications = async () => {
    loading.value = true;
    try {
        const response = await fetch('/growfinance/notifications?limit=10', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        if (response.ok) {
            const data = await response.json();
            localNotifications.value = data.notifications || [];
            localUnreadCount.value = data.unread_count || 0;
        }
    } catch (error) {
        console.error('Failed to fetch notifications:', error);
    } finally {
        loading.value = false;
    }
};

const hasUnread = computed(() => localUnreadCount.value > 0);

const formatTime = (dateString: string): string => {
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
    return date.toLocaleDateString();
};

const getNotificationIcon = (notification: Notification): string => {
    const type = notification.type.toLowerCase();
    const category = notification.data.category?.toLowerCase() || '';
    
    if (type.includes('invoice') || category === 'invoices') return '📄';
    if (type.includes('sale') || category === 'sales') return '💰';
    if (type.includes('expense') || category === 'expenses') return '💸';
    if (type.includes('paid') || category === 'payments') return '✅';
    if (type.includes('balance') || category === 'banking') return '⚠️';
    if (type.includes('summary') || category === 'reports') return '📊';
    return '🔔';
};

const getCategoryColor = (notification: Notification): string => {
    const category = notification.data.category?.toLowerCase() || '';
    
    switch (category) {
        case 'invoices':
            return 'bg-blue-100 text-blue-800';
        case 'sales':
        case 'payments':
            return 'bg-green-100 text-green-800';
        case 'expenses':
            return 'bg-red-100 text-red-800';
        case 'banking':
            return 'bg-amber-100 text-amber-800';
        case 'reports':
            return 'bg-purple-100 text-purple-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const getMessage = (notification: Notification): string => {
    return notification.data.message || notification.data.title || 'New notification';
};

const getCategory = (notification: Notification): string => {
    return notification.data.category || 'general';
};

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
};

const closeDropdown = () => {
    isOpen.value = false;
};

const markAsRead = async (notification: Notification) => {
    if (notification.read_at) return;

    try {
        await router.post(`/growfinance/notifications/${notification.id}/read`, {}, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                notification.read_at = new Date().toISOString();
                localUnreadCount.value = Math.max(0, localUnreadCount.value - 1);
            },
        });
    } catch (error) {
        console.error('Failed to mark notification as read:', error);
    }
};

const markAllAsRead = async () => {
    try {
        await router.post('/growfinance/notifications/mark-all-read', {}, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                localNotifications.value.forEach(n => {
                    n.read_at = new Date().toISOString();
                });
                localUnreadCount.value = 0;
            },
        });
    } catch (error) {
        console.error('Failed to mark all notifications as read:', error);
    }
};

const handleNotificationClick = (notification: Notification) => {
    markAsRead(notification);
    const url = notification.data?.action_url || notification.data?.url;
    if (url) {
        router.visit(url as string);
    }
    closeDropdown();
};

// Close dropdown when clicking outside
const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement;
    if (!target.closest('.notification-dropdown')) {
        closeDropdown();
    }
};

// Setup Echo real-time listener
const setupEchoListener = () => {
    if (!window.Echo || !userId.value) return;

    try {
        echoChannel.value = window.Echo.private(`growfinance.user.${userId.value}`);
        
        echoChannel.value.notification((notification: Notification) => {
            // Add new notification to the top of the list
            localNotifications.value.unshift(notification);
            localUnreadCount.value++;
            
            // Keep only the latest 10 notifications in the dropdown
            if (localNotifications.value.length > 10) {
                localNotifications.value = localNotifications.value.slice(0, 10);
            }
        });
    } catch (error) {
        console.warn('Failed to setup Echo listener for GrowFinance notifications:', error);
    }
};

// Cleanup Echo listener
const cleanupEchoListener = () => {
    if (echoChannel.value && userId.value) {
        try {
            window.Echo.leave(`growfinance.user.${userId.value}`);
        } catch (error) {
            console.warn('Failed to leave Echo channel:', error);
        }
        echoChannel.value = null;
    }
};

// Setup polling fallback (every 60 seconds)
const setupPolling = () => {
    // Clear any existing interval
    if (pollInterval.value) {
        clearInterval(pollInterval.value);
    }
    
    // Poll every 60 seconds as fallback for connection drops
    pollInterval.value = setInterval(fetchNotifications, 60000);
};

// Cleanup polling
const cleanupPolling = () => {
    if (pollInterval.value) {
        clearInterval(pollInterval.value);
        pollInterval.value = null;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    
    // Fetch notifications on mount if not provided via props
    if (!props.notifications || props.notifications.length === 0) {
        fetchNotifications();
    }
    
    // Setup real-time updates via Echo (Reverb)
    setupEchoListener();
    
    // Setup polling fallback for reliability
    setupPolling();
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    cleanupEchoListener();
    cleanupPolling();
});
</script>

<template>
    <div class="notification-dropdown relative">
        <!-- Bell Button -->
        <button
            @click.stop="toggleDropdown"
            class="relative p-2 rounded-lg hover:bg-gray-100 transition-colors"
            :aria-label="`Notifications${hasUnread ? ` (${localUnreadCount} unread)` : ''}`"
        >
            <component
                :is="hasUnread ? BellAlertIcon : BellIcon"
                class="h-6 w-6"
                :class="hasUnread ? 'text-blue-600' : 'text-gray-600'"
                aria-hidden="true"
            />
            <!-- Unread Badge -->
            <span
                v-if="hasUnread"
                class="absolute -top-1 -right-1 flex items-center justify-center min-w-[20px] h-5 px-1 text-xs font-bold text-white bg-red-500 rounded-full"
            >
                {{ localUnreadCount > 99 ? '99+' : localUnreadCount }}
            </span>
        </button>

        <!-- Dropdown Panel -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-1"
        >
            <div
                v-if="isOpen"
                class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-xl shadow-lg ring-1 ring-black ring-opacity-5 z-50 overflow-hidden"
            >
                <!-- Header -->
                <div class="flex items-center justify-between px-4 py-3 bg-gray-50 border-b">
                    <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
                    <div class="flex items-center gap-2">
                        <button
                            v-if="hasUnread"
                            @click="markAllAsRead"
                            class="text-xs text-blue-600 hover:text-blue-800 font-medium"
                        >
                            Mark all read
                        </button>
                        <button
                            @click="closeDropdown"
                            class="p-1 rounded hover:bg-gray-200"
                            aria-label="Close notifications"
                        >
                            <XMarkIcon class="h-4 w-4 text-gray-500" aria-hidden="true" />
                        </button>
                    </div>
                </div>

                <!-- Notification List -->
                <div class="max-h-96 overflow-y-auto">
                    <div v-if="localNotifications.length === 0" class="px-4 py-8 text-center">
                        <BellIcon class="h-12 w-12 mx-auto text-gray-300 mb-2" aria-hidden="true" />
                        <p class="text-sm text-gray-500">No notifications yet</p>
                    </div>

                    <!-- Loading State -->
                    <div v-else-if="loading" class="px-4 py-8 text-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-600 mx-auto"></div>
                        <p class="mt-2 text-sm text-gray-500">Loading...</p>
                    </div>

                    <div v-else class="divide-y divide-gray-100">
                        <button
                            v-for="notification in localNotifications"
                            :key="notification.id"
                            @click="handleNotificationClick(notification)"
                            class="w-full px-4 py-3 text-left hover:bg-gray-50 transition-colors"
                            :class="{ 'bg-blue-50/50': !notification.read_at }"
                        >
                            <div class="flex gap-3">
                                <!-- Icon -->
                                <span class="text-xl flex-shrink-0">
                                    {{ getNotificationIcon(notification) }}
                                </span>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <p
                                        class="text-sm"
                                        :class="notification.read_at ? 'text-gray-600' : 'text-gray-900 font-medium'"
                                    >
                                        {{ getMessage(notification) }}
                                    </p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                            :class="getCategoryColor(notification)"
                                        >
                                            {{ getCategory(notification) }}
                                        </span>
                                        <span class="text-xs text-gray-400">
                                            {{ formatTime(notification.created_at) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Unread Indicator -->
                                <span
                                    v-if="!notification.read_at"
                                    class="flex-shrink-0 w-2 h-2 bg-blue-600 rounded-full mt-2"
                                ></span>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-4 py-3 bg-gray-50 border-t">
                    <button
                        @click="router.visit('/growfinance/notifications')"
                        class="w-full text-center text-sm text-blue-600 hover:text-blue-800 font-medium"
                    >
                        View all notifications
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>
