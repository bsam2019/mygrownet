import { ref, onMounted, onUnmounted, computed } from 'vue';
import type { Ref } from 'vue';

interface Sale {
    id: number;
    amount: number;
    customer_name: string;
    product_name?: string;
    created_at: string;
}

interface Customer {
    id: number;
    name: string;
    email?: string;
    phone?: string;
    created_at: string;
}

interface Post {
    id: number;
    title: string;
    status: string;
    platform?: string;
    published_at?: string;
}

interface Notification {
    id: string;
    type: string;
    title: string;
    message: string;
    read: boolean;
    created_at: string;
}

interface DashboardStats {
    todaySales: number;
    todayRevenue: number;
    totalCustomers: number;
    totalProducts: number;
    pendingPosts: number;
}

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

interface RealtimeCallbacks {
    onSale?: (sale: Sale) => void;
    onCustomer?: (customer: Customer) => void;
    onPost?: (post: Post) => void;
    onNotification?: (notification: Notification) => void;
    onStatsUpdate?: (stats: DashboardStats) => void;
    onActivity?: (activity: ActivityItem) => void;
}

export function useBizBoostRealtime(businessId: Ref<number | null> | number, callbacks?: RealtimeCallbacks) {
    const isConnected = ref(false);
    const connectionError = ref<string | null>(null);
    const recentActivities = ref<ActivityItem[]>([]);
    const unreadNotifications = ref(0);
    
    let channel: any = null;
    
    const resolvedBusinessId = computed(() => {
        return typeof businessId === 'number' ? businessId : businessId.value;
    });

    const connect = () => {
        const id = resolvedBusinessId.value;
        if (!id || !(window as any).Echo) {
            connectionError.value = 'Echo not available or no business ID';
            return;
        }

        try {
            channel = (window as any).Echo.private(`bizboost.${id}`);
            
            channel
                .subscribed(() => {
                    isConnected.value = true;
                    connectionError.value = null;
                    console.log(`[BizBoost] Connected to channel bizboost.${id}`);
                })
                .error((error: any) => {
                    isConnected.value = false;
                    connectionError.value = error?.message || 'Connection failed';
                    console.error('[BizBoost] Channel error:', error);
                });

            // Listen for sales
            channel.listen('.sale.recorded', (data: { sale: Sale; timestamp: string }) => {
                console.log('[BizBoost] Sale recorded:', data);
                
                const activity: ActivityItem = {
                    id: `sale-${data.sale.id}`,
                    type: 'sale',
                    title: 'New Sale',
                    description: data.sale.customer_name,
                    amount: data.sale.amount,
                    timestamp: data.timestamp,
                    href: `/bizboost/sales/${data.sale.id}`,
                    actions: [
                        { label: 'View', href: `/bizboost/sales/${data.sale.id}` },
                        { label: 'Receipt', href: `/bizboost/sales/${data.sale.id}/receipt` },
                    ],
                };
                
                addActivity(activity);
                callbacks?.onSale?.(data.sale);
                callbacks?.onActivity?.(activity);
            });

            // Listen for new customers
            channel.listen('.customer.added', (data: { customer: Customer; timestamp: string }) => {
                console.log('[BizBoost] Customer added:', data);
                
                const activity: ActivityItem = {
                    id: `customer-${data.customer.id}`,
                    type: 'customer',
                    title: 'New Customer',
                    description: data.customer.name,
                    timestamp: data.timestamp,
                    href: `/bizboost/customers/${data.customer.id}`,
                    actions: [
                        { label: 'View', href: `/bizboost/customers/${data.customer.id}` },
                    ],
                };
                
                addActivity(activity);
                callbacks?.onCustomer?.(data.customer);
                callbacks?.onActivity?.(activity);
            });

            // Listen for published posts
            channel.listen('.post.published', (data: { post: Post; timestamp: string }) => {
                console.log('[BizBoost] Post published:', data);
                
                const activity: ActivityItem = {
                    id: `post-${data.post.id}`,
                    type: 'post',
                    title: 'Post Published',
                    description: data.post.title,
                    timestamp: data.timestamp,
                    href: `/bizboost/posts/${data.post.id}`,
                    actions: [
                        { label: 'View', href: `/bizboost/posts/${data.post.id}` },
                    ],
                };
                
                addActivity(activity);
                callbacks?.onPost?.(data.post);
                callbacks?.onActivity?.(activity);
            });

            // Listen for notifications
            channel.listen('.notification.received', (data: { notification: Notification; timestamp: string }) => {
                console.log('[BizBoost] Notification received:', data);
                unreadNotifications.value++;
                callbacks?.onNotification?.(data.notification);
            });

            // Listen for stats updates
            channel.listen('.stats.updated', (data: { stats: DashboardStats; timestamp: string }) => {
                console.log('[BizBoost] Stats updated:', data);
                callbacks?.onStatsUpdate?.(data.stats);
            });

        } catch (error) {
            connectionError.value = 'Failed to connect to channel';
            console.error('[BizBoost] Connection error:', error);
        }
    };

    const disconnect = () => {
        const id = resolvedBusinessId.value;
        if (channel && id) {
            (window as any).Echo?.leave(`bizboost.${id}`);
            channel = null;
            isConnected.value = false;
            console.log(`[BizBoost] Disconnected from channel bizboost.${id}`);
        }
    };

    const addActivity = (activity: ActivityItem) => {
        // Add to front, keep max 20 items
        recentActivities.value = [activity, ...recentActivities.value.slice(0, 19)];
    };

    const clearActivities = () => {
        recentActivities.value = [];
    };

    const resetUnreadCount = () => {
        unreadNotifications.value = 0;
    };

    onMounted(() => {
        connect();
    });

    onUnmounted(() => {
        disconnect();
    });

    return {
        isConnected,
        connectionError,
        recentActivities,
        unreadNotifications,
        connect,
        disconnect,
        clearActivities,
        resetUnreadCount,
    };
}

// Singleton for global access across components
let globalInstance: ReturnType<typeof useBizBoostRealtime> | null = null;

export function useBizBoostRealtimeGlobal(businessId?: number) {
    if (!globalInstance && businessId) {
        globalInstance = useBizBoostRealtime(businessId);
    }
    return globalInstance;
}
