<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { BellIcon, XMarkIcon, ShoppingBagIcon, CheckCircleIcon, XCircleIcon, CurrencyDollarIcon } from '@heroicons/vue/24/outline';
import { BellAlertIcon } from '@heroicons/vue/24/solid';

interface NotificationData {
    title?: string;
    message: string;
    [key: string]: unknown;
}

interface Notification {
    id: string;
    type: string;
    title: string;
    message: string;
    category: string;
    action_url: string | null;
    action_text: string | null;
    data: NotificationData;
    read_at: string | null;
    created_at: string;
}

const isOpen = ref(false);
const loading = ref(false);
const notifications = ref<Notification[]>([]);
const unreadCount = ref(0);
const pollInterval = ref<ReturnType<typeof setInterval> | null>(null);

const hasUnread = computed(() => unreadCount.value > 0);

const fetchNotifications = async () => {
    loading.value = true;
    try {
        const res = await fetch('/growmart/notifications/list?limit=10', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (res.ok) {
            const data = await res.json();
            notifications.value = data.notifications || [];
            unreadCount.value = data.unread_count || 0;
        }
    } catch {
        // silent
    } finally {
        loading.value = false;
    }
};

const fetchUnreadCount = async () => {
    try {
        const res = await fetch('/growmart/notifications/unread-count', {
            headers: { 'Accept': 'application/json' },
        });
        if (res.ok) {
            const data = await res.json();
            unreadCount.value = data.count || 0;
        }
    } catch {
        // silent
    }
};

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

const getIcon = (n: Notification) => {
    if (n.type.includes('paid') || n.category === 'payments') return CurrencyDollarIcon;
    if (n.type.includes('cancelled')) return XCircleIcon;
    if (n.type.includes('placed') || n.type.includes('status')) return ShoppingBagIcon;
    return CheckCircleIcon;
};

const getIconClass = (n: Notification) => {
    if (n.type.includes('paid') || n.category === 'payments') return 'text-emerald-600';
    if (n.type.includes('cancelled')) return 'text-red-600';
    return 'text-emerald-600';
};

const getCategoryClass = (n: Notification) => {
    if (n.category === 'payments') return 'bg-emerald-100 text-emerald-800';
    if (n.category === 'orders') return 'bg-blue-100 text-blue-800';
    return 'bg-gray-100 text-gray-800';
};

const toggle = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value && notifications.value.length === 0) {
        fetchNotifications();
    }
};

const close = () => { isOpen.value = false; };

const markAsRead = async (n: Notification) => {
    if (n.read_at) return;
    try {
        await router.post(`/growmart/notifications/${n.id}/read`, {}, { preserveState: true, preserveScroll: true });
        n.read_at = new Date().toISOString();
        unreadCount.value = Math.max(0, unreadCount.value - 1);
    } catch {
        // silent
    }
};

const markAllAsRead = async () => {
    try {
        await router.post('/growmart/notifications/mark-all-read', {}, { preserveState: true, preserveScroll: true });
        notifications.value.forEach(n => { n.read_at = new Date().toISOString(); });
        unreadCount.value = 0;
    } catch {
        // silent
    }
};

const handleClick = (n: Notification) => {
    markAsRead(n);
    if (n.action_url) {
        router.visit(n.action_url);
    }
    close();
};

const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement;
    if (!target.closest('.gm-notification-dropdown')) close();
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    fetchUnreadCount();
    pollInterval.value = setInterval(fetchUnreadCount, 30000);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    if (pollInterval.value) clearInterval(pollInterval.value);
});
</script>

<template>
    <div class="gm-notification-dropdown relative">
        <button @click.stop="toggle" class="relative p-2 text-gray-600 hover:text-emerald-600 transition-colors" :aria-label="`Notifications${hasUnread ? ` (${unreadCount} unread)` : ''}`">
            <component :is="hasUnread ? BellAlertIcon : BellIcon" class="h-6 w-6" :class="hasUnread ? 'text-emerald-600' : ''" />
            <span v-if="hasUnread" class="absolute -top-0.5 -right-0.5 flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-red-500 rounded-full">
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </button>

        <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 translate-y-1" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-1">
            <div v-if="isOpen" class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-xl shadow-lg ring-1 ring-black ring-opacity-5 z-50 overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 bg-gray-50 border-b">
                    <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
                    <div class="flex items-center gap-2">
                        <button v-if="hasUnread" @click="markAllAsRead" class="text-xs text-emerald-600 hover:text-emerald-800 font-medium">Mark all read</button>
                        <button @click="close" class="p-1 rounded hover:bg-gray-200"><XMarkIcon class="h-4 w-4 text-gray-500" /></button>
                    </div>
                </div>

                <div class="max-h-96 overflow-y-auto">
                    <div v-if="notifications.length === 0" class="px-4 py-8 text-center">
                        <BellIcon class="h-12 w-12 mx-auto text-gray-300 mb-2" />
                        <p class="text-sm text-gray-500">No notifications yet</p>
                    </div>
                    <div v-else-if="loading" class="px-4 py-8 text-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-600 mx-auto"></div>
                        <p class="mt-2 text-sm text-gray-500">Loading...</p>
                    </div>
                    <div v-else class="divide-y divide-gray-100">
                        <button v-for="n in notifications" :key="n.id" @click="handleClick(n)" class="w-full px-4 py-3 text-left hover:bg-gray-50 transition-colors" :class="{ 'bg-emerald-50/50': !n.read_at }">
                            <div class="flex gap-3">
                                <component :is="getIcon(n)" :class="['h-8 w-8 p-1.5 shrink-0 rounded-full', getIconClass(n), n.read_at ? 'opacity-60' : '']" />
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm truncate" :class="n.read_at ? 'text-gray-600' : 'text-gray-900 font-medium'">{{ n.message }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium" :class="getCategoryClass(n)">{{ n.category }}</span>
                                        <span class="text-[10px] text-gray-400">{{ formatTime(n.created_at) }}</span>
                                    </div>
                                </div>
                                <span v-if="!n.read_at" class="shrink-0 w-2 h-2 bg-emerald-500 rounded-full mt-2"></span>
                            </div>
                        </button>
                    </div>
                </div>

                <div class="px-4 py-3 bg-gray-50 border-t">
                    <button @click="router.visit(route('growmart.notifications.page'))" class="w-full text-center text-sm text-emerald-600 hover:text-emerald-800 font-medium">
                        View all notifications
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>
