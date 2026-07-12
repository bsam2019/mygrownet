<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import GrowMartLayout from '@/layouts/GrowMartLayout.vue';
import { BellIcon, BellAlertIcon, ShoppingBagIcon, CheckCircleIcon, XCircleIcon, CurrencyDollarIcon, ArrowLeftIcon, CheckIcon } from '@heroicons/vue/24/outline';

interface Notification {
    id: string;
    type: string;
    title: string;
    message: string;
    category: string;
    action_url: string | null;
    action_text: string | null;
    data: Record<string, unknown>;
    read_at: string | null;
    created_at: string;
}

interface PaginatedData {
    data: Notification[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Props {
    notifications: PaginatedData;
    unreadCount: number;
    cartCount: number;
}

const props = defineProps<Props>();

const localNotifications = ref<Notification[]>(props.notifications?.data || []);
const localUnreadCount = ref(props.unreadCount || 0);

const getIcon = (n: Notification) => {
    if (n.type.includes('paid') || n.category === 'payments') return CurrencyDollarIcon;
    if (n.type.includes('cancelled')) return XCircleIcon;
    if (n.type.includes('placed') || n.type.includes('status')) return ShoppingBagIcon;
    return CheckCircleIcon;
};

const getIconClass = (n: Notification) => {
    if (n.type.includes('paid') || n.category === 'payments') return 'text-emerald-600 bg-emerald-100';
    if (n.type.includes('cancelled')) return 'text-red-600 bg-red-100';
    return 'text-emerald-600 bg-emerald-100';
};

const getCategoryClass = (n: Notification) => {
    if (n.category === 'payments') return 'bg-emerald-100 text-emerald-800';
    if (n.category === 'orders') return 'bg-blue-100 text-blue-800';
    return 'bg-gray-100 text-gray-800';
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

const markAsRead = async (n: Notification) => {
    if (n.read_at) return;
    try {
        await router.post(`/growmart/notifications/${n.id}/read`, {}, { preserveState: true, preserveScroll: true });
        n.read_at = new Date().toISOString();
        localUnreadCount.value = Math.max(0, localUnreadCount.value - 1);
    } catch { /* silent */ }
};

const markAllAsRead = async () => {
    try {
        await router.post('/growmart/notifications/mark-all-read', {}, { preserveState: true, preserveScroll: true });
        localNotifications.value.forEach(n => { n.read_at = new Date().toISOString(); });
        localUnreadCount.value = 0;
    } catch { /* silent */ }
};

const handleClick = (n: Notification) => {
    markAsRead(n);
    if (n.action_url) router.visit(n.action_url);
};
</script>

<template>
    <Head title="Notifications - GrowMart" />

    <GrowMartLayout>
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="mb-6">
                <Link :href="route('growmart.home')" class="text-sm text-emerald-600 hover:text-emerald-700 inline-flex items-center gap-1">
                    <ArrowLeftIcon class="w-4 h-4" /> Back to GrowMart
                </Link>
            </div>

            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-emerald-100 rounded-lg">
                        <BellIcon v-if="localUnreadCount === 0" class="h-6 w-6 text-emerald-600" />
                        <BellAlertIcon v-else class="h-6 w-6 text-emerald-600" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">Notifications</h1>
                        <p v-if="localUnreadCount > 0" class="text-sm text-gray-500">{{ localUnreadCount }} unread</p>
                    </div>
                </div>
                <button v-if="localUnreadCount > 0" @click="markAllAsRead"
                    class="text-sm text-emerald-600 hover:text-emerald-800 font-medium flex items-center gap-1">
                    <CheckIcon class="w-4 h-4" /> Mark all read
                </button>
            </div>

            <div v-if="localNotifications.length === 0" class="text-center py-16 bg-white rounded-xl border border-gray-200">
                <BellIcon class="h-16 w-16 mx-auto text-gray-300 mb-4" />
                <h2 class="text-lg font-medium text-gray-900 mb-1">No notifications yet</h2>
                <p class="text-sm text-gray-500">You'll see order updates and payment notifications here.</p>
                <Link :href="route('growmart.home')" class="mt-4 inline-flex items-center px-6 py-2.5 bg-emerald-600 text-white rounded-lg font-medium hover:bg-emerald-700">
                    Start Shopping
                </Link>
            </div>

            <div v-else class="space-y-3">
                <div v-for="n in localNotifications" :key="n.id"
                    @click="handleClick(n)"
                    class="bg-white rounded-xl border border-gray-200 p-4 cursor-pointer hover:border-emerald-300 hover:shadow-sm transition-all"
                    :class="{ 'ring-1 ring-emerald-500/20 bg-emerald-50/30': !n.read_at }"
                >
                    <div class="flex gap-4">
                        <component :is="getIcon(n)" :class="['h-10 w-10 p-2 shrink-0 rounded-full', getIconClass(n), n.read_at ? 'opacity-60' : '']" />
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-sm" :class="n.read_at ? 'text-gray-600' : 'text-gray-900 font-medium'">{{ n.message }}</p>
                                <span v-if="!n.read_at" class="shrink-0 w-2.5 h-2.5 bg-emerald-500 rounded-full mt-1.5"></span>
                            </div>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" :class="getCategoryClass(n)">{{ n.category }}</span>
                                <span class="text-xs text-gray-400">{{ formatTime(n.created_at) }}</span>
                                <span v-if="n.action_text" class="text-xs text-emerald-600 ml-auto font-medium">{{ n.action_text }} →</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GrowMartLayout>
</template>
