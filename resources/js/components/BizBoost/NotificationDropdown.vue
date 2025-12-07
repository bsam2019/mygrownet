<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import {
    BellIcon,
    CheckIcon,
    XMarkIcon,
    ShoppingBagIcon,
    UserPlusIcon,
    DocumentTextIcon,
    CurrencyDollarIcon,
    SparklesIcon,
    ExclamationTriangleIcon,
    InformationCircleIcon,
    SignalIcon,
    SignalSlashIcon,
} from '@heroicons/vue/24/outline';
import { BellIcon as BellSolidIcon } from '@heroicons/vue/24/solid';

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
    createdAt: string;
    timeAgo: string;
}

const props = defineProps<{
    businessId?: number;
}>();

const isOpen = ref(false);
const notifications = ref<Notification[]>([]);
const unreadCount = ref(0);
const loading = ref(false);
const isRealtimeConnected = ref(false);
let channel: any = null;

const fetchNotifications = async () => {
    try {
        const response = await fetch('/bizboost/notifications/dropdown', {
            headers: { 'Accept': 'application/json' },
        });
        const data = await response.json();
        notifications.value = data.notifications;
        unreadCount.value = data.unreadCount;
    } catch (error) {
        console.error('Failed to fetch notifications:', error);
    }
};

const fetchUnreadCount = async () => {
    try {
        const response = await fetch('/bizboost/notifications/unread-count', {
            headers: { 'Accept': 'application/json' },
        });
        const data = await response.json();
        unreadCount.value = data.count;
    } catch (error) {
        console.error('Failed to fetch unread count:', error);
    }
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
        const notification = notifications.value.find(n => n.id === id);
        if (notification) {
            notification.read = true;
            unreadCount.value = Math.max(0, unreadCount.value - 1);
        }
    } catch (error) {
        console.error('Failed to mark notification as read:', error);
    }
};

const markAllAsRead = async () => {
    try {
        await fetch('/bizboost/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });
        notifications.value.forEach(n => n.read = true);
        unreadCount.value = 0;
    } catch (error) {
        console.error('Failed to mark all as read:', error);
    }
};

const handleNotificationClick = (notification: Notification) => {
    if (!notification.read) {
        markAsRead(notification.id);
    }
    if (notification.actionUrl) {
        router.visit(notification.actionUrl);
        isOpen.value = false;
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
    if (priority === 'high') return 'text-red-500 bg-red-50';
    switch (type) {
        case 'sale': return 'text-emerald-500 bg-emerald-50';
        case 'customer': return 'text-blue-500 bg-blue-50';
        case 'post': return 'text-violet-500 bg-violet-50';
        case 'product': return 'text-amber-500 bg-amber-50';
        case 'ai': return 'text-purple-500 bg-purple-50';
        case 'warning': return 'text-orange-500 bg-orange-50';
        default: return 'text-slate-500 bg-slate-50';
    }
};

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        fetchNotifications();
    }
};

const closeDropdown = () => {
    isOpen.value = false;
};

const connectRealtime = () => {
    if (!props.businessId || !(window as any).Echo) {
        return;
    }

    try {
        channel = (window as any).Echo.private(`bizboost.${props.businessId}`);
        
        channel
            .subscribed(() => {
                isRealtimeConnected.value = true;
                console.log('[NotificationDropdown] Connected to realtime');
            })
            .error((error: any) => {
                isRealtimeConnected.value = false;
                console.error('[NotificationDropdown] Realtime error:', error);
            });

        // Listen for new notifications
        channel.listen('.notification.received', (data: { notification: any; timestamp: string }) => {
            console.log('[NotificationDropdown] New notification:', data);
            
            // Add to top of list
            const newNotification: Notification = {
                id: data.notification.id,
                type: data.notification.type || 'info',
                category: data.notification.category || 'general',
                title: data.notification.title,
                message: data.notification.message,
                actionUrl: data.notification.action_url,
                actionText: data.notification.action_text,
                priority: data.notification.priority || 'normal',
                read: false,
                createdAt: data.timestamp,
                timeAgo: 'Just now',
            };
            
            notifications.value = [newNotification, ...notifications.value.slice(0, 9)];
            unreadCount.value++;
        });

        // Listen for sales (show as notification)
        channel.listen('.sale.recorded', (data: { sale: any; timestamp: string }) => {
            unreadCount.value++;
        });

        // Listen for new customers
        channel.listen('.customer.added', (data: { customer: any; timestamp: string }) => {
            unreadCount.value++;
        });

    } catch (error) {
        console.error('[NotificationDropdown] Failed to connect:', error);
    }
};

const disconnectRealtime = () => {
    if (channel && props.businessId) {
        (window as any).Echo?.leave(`bizboost.${props.businessId}`);
        channel = null;
        isRealtimeConnected.value = false;
    }
};

onMounted(() => {
    fetchUnreadCount();
    connectRealtime();
    
    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        const target = e.target as HTMLElement;
        if (!target.closest('.notification-dropdown')) {
            isOpen.value = false;
        }
    });
});

onUnmounted(() => {
    disconnectRealtime();
});

// Reconnect if businessId changes
watch(() => props.businessId, (newId, oldId) => {
    if (oldId) {
        disconnectRealtime();
    }
    if (newId) {
        connectRealtime();
    }
});
</script>

<template>
    <div class="notification-dropdown relative">
        <!-- Trigger Button -->
        <button
            @click.stop="toggleDropdown"
            class="relative p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors"
            aria-label="View notifications"
        >
            <BellIcon v-if="unreadCount === 0" class="h-5 w-5" aria-hidden="true" />
            <BellSolidIcon v-else class="h-5 w-5 text-violet-600" aria-hidden="true" />
            
            <!-- Badge -->
            <span
                v-if="unreadCount > 0"
                class="absolute -top-0.5 -right-0.5 flex items-center justify-center min-w-[18px] h-[18px] px-1 text-xs font-bold text-white bg-red-500 rounded-full"
            >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
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
                class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-xl shadow-xl ring-1 ring-slate-200 z-50 overflow-hidden"
            >
                <!-- Header -->
                <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 bg-slate-50">
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-semibold text-slate-900">Notifications</h3>
                        <!-- Realtime indicator -->
                        <span 
                            v-if="isRealtimeConnected" 
                            class="flex items-center gap-1 text-xs text-emerald-600"
                            title="Live updates enabled"
                        >
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Live
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            v-if="unreadCount > 0"
                            @click="markAllAsRead"
                            class="text-xs text-violet-600 hover:text-violet-700 font-medium"
                        >
                            Mark all read
                        </button>
                        <button
                            @click="closeDropdown"
                            class="p-1 text-slate-400 hover:text-slate-600 rounded"
                            aria-label="Close notifications"
                        >
                            <XMarkIcon class="h-4 w-4" aria-hidden="true" />
                        </button>
                    </div>
                </div>

                <!-- Notifications List -->
                <div class="max-h-96 overflow-y-auto">
                    <div v-if="notifications.length === 0" class="px-4 py-8 text-center">
                        <BellIcon class="h-10 w-10 text-slate-300 mx-auto mb-2" aria-hidden="true" />
                        <p class="text-sm text-slate-500">No notifications yet</p>
                    </div>

                    <button
                        v-for="notification in notifications"
                        :key="notification.id"
                        @click="handleNotificationClick(notification)"
                        :class="[
                            'w-full flex items-start gap-3 px-4 py-3 text-left transition-colors border-b border-slate-50 last:border-0',
                            notification.read ? 'bg-white' : 'bg-violet-50/50'
                        ]"
                    >
                        <!-- Icon -->
                        <div :class="['p-2 rounded-lg shrink-0', getNotificationColor(notification.type, notification.priority)]">
                            <component :is="getNotificationIcon(notification.type)" class="h-4 w-4" aria-hidden="true" />
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <p :class="['text-sm truncate', notification.read ? 'text-slate-700' : 'text-slate-900 font-medium']">
                                {{ notification.title }}
                            </p>
                            <p class="text-xs text-slate-500 truncate mt-0.5">
                                {{ notification.message }}
                            </p>
                            <p class="text-xs text-slate-400 mt-1">
                                {{ notification.timeAgo }}
                            </p>
                        </div>

                        <!-- Unread indicator -->
                        <div v-if="!notification.read" class="w-2 h-2 bg-violet-500 rounded-full shrink-0 mt-2"></div>
                    </button>
                </div>

                <!-- Footer -->
                <div class="px-4 py-2 border-t border-slate-100 bg-slate-50">
                    <button
                        @click="router.visit('/bizboost/notifications'); isOpen = false"
                        class="w-full text-center text-sm text-violet-600 hover:text-violet-700 font-medium py-1"
                    >
                        View all notifications
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>
