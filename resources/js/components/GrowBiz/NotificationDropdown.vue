<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
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
    timeAgo: string;
}

const props = defineProps<{
    unreadCount: number;
}>();

const page = usePage();
const isOpen = ref(false);
const loading = ref(false);
const notifications = ref<Notification[]>([]);
const dropdownRef = ref<HTMLElement | null>(null);

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

const toggleDropdown = async () => {
    isOpen.value = !isOpen.value;
    
    if (isOpen.value && notifications.value.length === 0) {
        await fetchNotifications();
    }
};

const fetchNotifications = async () => {
    loading.value = true;
    try {
        const response = await fetch(route('growbiz.notifications.index'), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        
        if (response.ok) {
            const data = await response.json();
            // Handle JSON response format
            if (data.notifications?.data) {
                notifications.value = data.notifications.data.slice(0, 5);
            } else if (Array.isArray(data.notifications)) {
                notifications.value = data.notifications.slice(0, 5);
            }
        }
    } catch (error) {
        console.error('Failed to fetch notifications:', error);
    } finally {
        loading.value = false;
    }
};

const markAsRead = (id: string, event: Event) => {
    event.stopPropagation();
    router.post(route('growbiz.notifications.read', id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            const notification = notifications.value.find(n => n.id === id);
            if (notification) {
                notification.read = true;
            }
        },
    });
};

const handleNotificationClick = (notification: Notification) => {
    if (!notification.read) {
        router.post(route('growbiz.notifications.read', notification.id), {}, {
            preserveScroll: true,
        });
    }
    isOpen.value = false;
    if (notification.url) {
        router.visit(notification.url);
    }
};

const markAllAsRead = () => {
    router.post(route('growbiz.notifications.mark-all-read'), {}, {
        preserveScroll: true,
        onSuccess: () => {
            notifications.value.forEach(n => n.read = true);
        },
    });
};

const closeDropdown = (e: MouseEvent) => {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target as Node)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
    document.removeEventListener('click', closeDropdown);
});
</script>

<template>
    <div ref="dropdownRef" class="relative">
        <!-- Bell Button -->
        <button 
            @click="toggleDropdown"
            class="p-2.5 rounded-xl text-gray-500 hover:bg-gray-100 active:bg-gray-200 transition-colors touch-manipulation relative"
            aria-label="View notifications"
        >
            <BellIcon class="h-5 w-5" aria-hidden="true" />
            <!-- Notification badge -->
            <span 
                v-if="unreadCount > 0" 
                class="absolute top-1.5 right-1.5 min-w-[18px] h-[18px] px-1 bg-red-500 rounded-full flex items-center justify-center"
            >
                <span class="text-[10px] font-bold text-white">
                    {{ unreadCount > 99 ? '99+' : unreadCount }}
                </span>
            </span>
        </button>

        <!-- Dropdown -->
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
                class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden z-50"
            >
                <!-- Header -->
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 bg-gray-50">
                    <h3 class="font-semibold text-gray-900">Notifications</h3>
                    <button
                        v-if="unreadCount > 0"
                        @click="markAllAsRead"
                        class="text-xs font-medium text-emerald-600 hover:text-emerald-700"
                    >
                        Mark all read
                    </button>
                </div>

                <!-- Loading State -->
                <div v-if="loading" class="p-4 text-center">
                    <div class="animate-spin h-6 w-6 border-2 border-emerald-500 border-t-transparent rounded-full mx-auto"></div>
                    <p class="text-sm text-gray-500 mt-2">Loading...</p>
                </div>

                <!-- Notifications List -->
                <div v-else-if="notifications.length > 0" class="max-h-80 overflow-y-auto">
                    <div
                        v-for="notification in notifications"
                        :key="notification.id"
                        @click="handleNotificationClick(notification)"
                        :class="[
                            'flex items-start gap-3 px-4 py-3 cursor-pointer transition-colors border-b border-gray-50 last:border-0',
                            notification.read 
                                ? 'bg-white hover:bg-gray-50' 
                                : 'bg-emerald-50/50 hover:bg-emerald-50'
                        ]"
                    >
                        <!-- Icon -->
                        <div :class="['flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center', getIconColor(notification.type)]">
                            <component :is="getIcon(notification.type)" class="h-4 w-4" />
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <p :class="['text-sm line-clamp-1', notification.read ? 'text-gray-600' : 'text-gray-900 font-medium']">
                                {{ notification.title }}
                            </p>
                            <p class="text-xs text-gray-500 line-clamp-1 mt-0.5">
                                {{ notification.message }}
                            </p>
                            <span class="text-xs text-gray-400 mt-1 block">
                                {{ notification.timeAgo }}
                            </span>
                        </div>

                        <!-- Unread indicator -->
                        <div v-if="!notification.read" class="flex-shrink-0 w-2 h-2 bg-emerald-500 rounded-full mt-1"></div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="p-6 text-center">
                    <BellIcon class="h-8 w-8 text-gray-300 mx-auto mb-2" />
                    <p class="text-sm text-gray-500">No notifications yet</p>
                </div>

                <!-- Footer -->
                <Link
                    :href="route('growbiz.notifications.index')"
                    class="block px-4 py-3 text-center text-sm font-medium text-emerald-600 bg-gray-50 hover:bg-gray-100 transition-colors border-t border-gray-100"
                    @click="isOpen = false"
                >
                    View all notifications
                </Link>
            </div>
        </Transition>
    </div>
</template>
