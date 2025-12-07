<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    CurrencyDollarIcon,
    UserPlusIcon,
    DocumentTextIcon,
    ShoppingBagIcon,
    ChatBubbleLeftIcon,
    HeartIcon,
    EyeIcon,
    CheckCircleIcon,
} from '@heroicons/vue/24/outline';

interface ActivityItem {
    id: string | number;
    type: 'sale' | 'customer' | 'post' | 'product' | 'engagement' | 'view' | 'message' | 'campaign';
    title: string;
    description?: string;
    amount?: number;
    timestamp: string;
    href?: string;
    actions?: Array<{ label: string; href: string }>;
}

interface Props {
    activities: ActivityItem[];
    loading?: boolean;
    maxItems?: number;
    businessId?: number;
}

const props = withDefaults(defineProps<Props>(), {
    loading: false,
    maxItems: 5,
});

const realtimeActivities = ref<ActivityItem[]>([]);
const isRealtimeConnected = ref(false);
let channel: any = null;

// Merge realtime activities with prop activities
const displayedActivities = computed(() => {
    const merged = [...realtimeActivities.value, ...props.activities];
    // Dedupe by id
    const seen = new Set<string | number>();
    return merged.filter(item => {
        if (seen.has(item.id)) return false;
        seen.add(item.id);
        return true;
    }).slice(0, props.maxItems);
});

const connectRealtime = () => {
    if (!props.businessId || !(window as any).Echo) {
        return;
    }

    try {
        channel = (window as any).Echo.private(`bizboost.${props.businessId}`);
        
        channel
            .subscribed(() => {
                isRealtimeConnected.value = true;
                console.log('[ActivityFeed] Connected to realtime');
            })
            .error((error: any) => {
                isRealtimeConnected.value = false;
                console.error('[ActivityFeed] Realtime error:', error);
            });

        // Listen for sales
        channel.listen('.sale.recorded', (data: { sale: any; timestamp: string }) => {
            const activity: ActivityItem = {
                id: `sale-${data.sale.id}-${Date.now()}`,
                type: 'sale',
                title: 'New Sale',
                description: data.sale.customer_name || 'Walk-in customer',
                amount: data.sale.total_amount || data.sale.amount,
                timestamp: data.timestamp,
                href: `/bizboost/sales/${data.sale.id}`,
                actions: [
                    { label: 'View', href: `/bizboost/sales/${data.sale.id}` },
                ],
            };
            addRealtimeActivity(activity);
        });

        // Listen for new customers
        channel.listen('.customer.added', (data: { customer: any; timestamp: string }) => {
            const activity: ActivityItem = {
                id: `customer-${data.customer.id}-${Date.now()}`,
                type: 'customer',
                title: 'New Customer',
                description: data.customer.name,
                timestamp: data.timestamp,
                href: `/bizboost/customers/${data.customer.id}`,
                actions: [
                    { label: 'View', href: `/bizboost/customers/${data.customer.id}` },
                ],
            };
            addRealtimeActivity(activity);
        });

        // Listen for published posts
        channel.listen('.post.published', (data: { post: any; timestamp: string }) => {
            const activity: ActivityItem = {
                id: `post-${data.post.id}-${Date.now()}`,
                type: 'post',
                title: 'Post Published',
                description: data.post.title || data.post.content?.substring(0, 50),
                timestamp: data.timestamp,
                href: `/bizboost/posts/${data.post.id}`,
                actions: [
                    { label: 'View', href: `/bizboost/posts/${data.post.id}` },
                ],
            };
            addRealtimeActivity(activity);
        });

    } catch (error) {
        console.error('[ActivityFeed] Failed to connect:', error);
    }
};

const disconnectRealtime = () => {
    if (channel && props.businessId) {
        (window as any).Echo?.leave(`bizboost.${props.businessId}`);
        channel = null;
        isRealtimeConnected.value = false;
    }
};

const addRealtimeActivity = (activity: ActivityItem) => {
    realtimeActivities.value = [activity, ...realtimeActivities.value.slice(0, 9)];
};

onMounted(() => {
    connectRealtime();
});

onUnmounted(() => {
    disconnectRealtime();
});

watch(() => props.businessId, (newId, oldId) => {
    if (oldId) disconnectRealtime();
    if (newId) connectRealtime();
});

const getIcon = (type: ActivityItem['type']) => {
    const icons = {
        sale: CurrencyDollarIcon,
        customer: UserPlusIcon,
        post: DocumentTextIcon,
        product: ShoppingBagIcon,
        engagement: HeartIcon,
        view: EyeIcon,
        message: ChatBubbleLeftIcon,
        campaign: CheckCircleIcon,
    };
    return icons[type] || DocumentTextIcon;
};

const getIconColor = (type: ActivityItem['type']) => {
    const colors = {
        sale: 'bg-emerald-100 text-emerald-600',
        customer: 'bg-blue-100 text-blue-600',
        post: 'bg-violet-100 text-violet-600',
        product: 'bg-amber-100 text-amber-600',
        engagement: 'bg-pink-100 text-pink-600',
        view: 'bg-slate-100 text-slate-600',
        message: 'bg-green-100 text-green-600',
        campaign: 'bg-indigo-100 text-indigo-600',
    };
    return colors[type] || 'bg-slate-100 text-slate-600';
};

const formatTime = (timestamp: string) => {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = now.getTime() - date.getTime();
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);

    if (minutes < 1) return 'Just now';
    if (minutes < 60) return `${minutes}m ago`;
    if (hours < 24) return `${hours}h ago`;
    if (days < 7) return `${days}d ago`;
    return date.toLocaleDateString('en-ZM', { month: 'short', day: 'numeric' });
};
</script>

<template>
    <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm ring-1 ring-slate-200 dark:ring-slate-700 overflow-hidden">
        <!-- Header -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-700">
            <div class="flex items-center gap-2">
                <div 
                    :class="[
                        'h-2 w-2 rounded-full',
                        isRealtimeConnected ? 'bg-emerald-500 animate-pulse' : 'bg-slate-300 dark:bg-slate-600'
                    ]"
                    :title="isRealtimeConnected ? 'Live updates active' : 'Connecting...'"
                ></div>
                <h3 class="text-base font-semibold text-slate-900 dark:text-white">Live Activity</h3>
                <span v-if="isRealtimeConnected" class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">Live</span>
            </div>
            <Link href="/bizboost/analytics" class="text-sm text-violet-600 dark:text-violet-400 hover:text-violet-700 dark:hover:text-violet-300 font-medium">
                View all
            </Link>
        </div>

        <!-- Loading state -->
        <div v-if="loading" class="divide-y divide-slate-100">
            <div v-for="i in 4" :key="i" class="px-5 py-4 animate-pulse">
                <div class="flex items-start gap-3">
                    <div class="h-9 w-9 rounded-xl bg-slate-200"></div>
                    <div class="flex-1">
                        <div class="h-4 w-32 bg-slate-200 rounded mb-2"></div>
                        <div class="h-3 w-48 bg-slate-100 rounded"></div>
                    </div>
                    <div class="h-3 w-12 bg-slate-100 rounded"></div>
                </div>
            </div>
        </div>

        <!-- Activity list -->
        <div v-else-if="displayedActivities.length" class="divide-y divide-slate-100 dark:divide-slate-700">
            <TransitionGroup name="activity">
                <div
                    v-for="activity in displayedActivities"
                    :key="activity.id"
                    class="px-5 py-4 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors group"
                >
                    <div class="flex items-start gap-3">
                        <!-- Icon -->
                        <div :class="[getIconColor(activity.type), 'p-2 rounded-xl shrink-0 group-hover:scale-105 transition-transform']">
                            <component :is="getIcon(activity.type)" class="h-5 w-5" aria-hidden="true" />
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-medium text-slate-900 dark:text-white truncate">{{ activity.title }}</p>
                                <span v-if="activity.amount" class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                                    K{{ activity.amount.toLocaleString() }}
                                </span>
                            </div>
                            <p v-if="activity.description" class="text-sm text-slate-500 dark:text-slate-400 truncate mt-0.5">
                                {{ activity.description }}
                            </p>

                            <!-- Quick actions -->
                            <div v-if="activity.actions?.length" class="flex items-center gap-2 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <Link
                                    v-for="action in activity.actions"
                                    :key="action.label"
                                    :href="action.href"
                                    class="text-xs font-medium text-violet-600 dark:text-violet-400 hover:text-violet-700 dark:hover:text-violet-300 px-2 py-1 rounded-md hover:bg-violet-50 dark:hover:bg-violet-900/30 transition-colors"
                                >
                                    {{ action.label }}
                                </Link>
                            </div>
                        </div>

                        <!-- Timestamp -->
                        <span class="text-xs text-slate-400 dark:text-slate-500 shrink-0">{{ formatTime(activity.timestamp) }}</span>
                    </div>
                </div>
            </TransitionGroup>
        </div>

        <!-- Empty state -->
        <div v-else class="px-5 py-12 text-center">
            <div class="mx-auto h-12 w-12 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center mb-3">
                <EyeIcon class="h-6 w-6 text-slate-400 dark:text-slate-500" aria-hidden="true" />
            </div>
            <p class="text-sm font-medium text-slate-900 dark:text-white">No recent activity</p>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Activity will appear here as it happens</p>
        </div>
    </div>
</template>

<style scoped>
.activity-enter-active {
    transition: all 0.3s ease-out;
}
.activity-leave-active {
    transition: all 0.2s ease-in;
}
.activity-enter-from {
    opacity: 0;
    transform: translateX(-20px);
}
.activity-leave-to {
    opacity: 0;
    transform: translateX(20px);
}
</style>
