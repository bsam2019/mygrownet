<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import MarketplaceLayout from '@/layouts/MarketplaceLayout.vue';
import {
    CubeIcon,
    ShoppingCartIcon,
    CurrencyDollarIcon,
    StarIcon,
    ArrowRightIcon,
    ExclamationTriangleIcon,
    CheckCircleIcon,
    ChartBarIcon,
    UsersIcon,
    BellIcon,
    SparklesIcon,
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
    ClockIcon,
    XCircleIcon,
    MegaphoneIcon,
    ChartPieIcon,
    BanknotesIcon,
    CheckBadgeIcon,
    PlusCircleIcon,
    InformationCircleIcon,
} from '@heroicons/vue/24/outline';

interface Seller {
    id: number;
    business_name: string;
    trust_level: string;
    trust_badge: string;
    trust_label: string;
    kyc_status: string;
    is_active: boolean;
    rating: number;
    total_orders: number;
    effective_commission_rate: number;
    completed_orders: number;
    total_sales_amount: number;
}

interface Stats {
    total_products: number;
    active_products: number;
    pending_products: number;
    rejected_products: number;
    pending_orders: number;
    completed_orders: number;
    total_orders: number;
    rating: number;
    pending_balance: number;
    available_balance: number;
    total_revenue: number;
    today_revenue: number;
    this_week_revenue: number;
    this_month_revenue: number;
    avg_order_value: number;
    low_stock_count: number;
    out_of_stock_count: number;
    total_customers: number;
    repeat_customers: number;
    repeat_rate: number;
}

interface Order {
    id: number;
    order_number: string;
    status: string;
    status_label: string;
    total: number;
    formatted_total: string;
    created_at: string;
    buyer: {
        name: string;
    };
}

interface Product {
    id: number;
    name: string;
    total_sold?: number;
    formatted_price: string;
    stock_quantity: number;
}

interface ChartData {
    date: string;
    revenue: number;
}

interface Review {
    id: number;
    rating: number;
    comment: string;
    created_at: string;
    buyer: {
        name: string;
    };
}

interface TierProgressItem {
    current: number;
    required: number;
    met: boolean;
}

interface TierProgress {
    current_tier: string;
    next_tier: string | null;
    progress: Record<string, TierProgressItem>;
    is_max_tier: boolean;
}

interface TierInfoItem {
    name: string;
    badge: string;
    commission: number;
    description: string;
    color: string;
}

const props = defineProps<{
    seller: Seller;
    stats: Stats;
    recentOrders: Order[];
    topProducts: Product[];
    salesChartData: ChartData[];
    recentReviews: Review[];
    tierProgress: TierProgress;
    tierInfo: Record<string, TierInfoItem>;
}>();

const selectedPeriod = ref('week');

const formatPrice = (amount: number) => {
    return 'K' + (amount / 100).toFixed(2);
};

const getStatusColor = (status: string) => {
    return {
        'pending': 'bg-yellow-100 text-yellow-800',
        'paid': 'bg-blue-100 text-blue-800',
        'shipped': 'bg-purple-100 text-purple-800',
        'delivered': 'bg-teal-100 text-teal-800',
        'completed': 'bg-green-100 text-green-800',
        'cancelled': 'bg-gray-100 text-gray-800',
        'disputed': 'bg-red-100 text-red-800',
    }[status] || 'bg-gray-100 text-gray-800';
};

const maxChartValue = computed(() => {
    return Math.max(...props.salesChartData.map(d => d.revenue), 1);
});

const notifications = computed(() => {
    const alerts = [];
    if (props.stats.pending_orders > 0) {
        alerts.push({ type: 'warning', message: `${props.stats.pending_orders} orders need attention` });
    }
    if (props.stats.low_stock_count > 0) {
        alerts.push({ type: 'warning', message: `${props.stats.low_stock_count} products low on stock` });
    }
    if (props.stats.out_of_stock_count > 0) {
        alerts.push({ type: 'error', message: `${props.stats.out_of_stock_count} products out of stock` });
    }
    if (props.stats.rejected_products > 0) {
        alerts.push({ type: 'error', message: `${props.stats.rejected_products} products rejected` });
    }
    return alerts;
});

// Check if seller is new (no sales yet)
const isNewSeller = computed(() => {
    return props.stats.total_orders === 0 && props.stats.active_products === 0;
});

const hasAnySales = computed(() => {
    return props.stats.total_revenue > 0;
});

// Tier progress helpers
const currentTierInfo = computed(() => {
    return props.tierInfo[props.tierProgress.current_tier] || props.tierInfo['new'];
});

const nextTierInfo = computed(() => {
    if (!props.tierProgress.next_tier) return null;
    return props.tierInfo[props.tierProgress.next_tier];
});

const getTierColor = (tier: string) => {
    const colors: Record<string, string> = {
        'new': 'gray',
        'verified': 'blue',
        'trusted': 'green',
        'top': 'amber',
    };
    return colors[tier] || 'gray';
};

const getProgressPercentage = (item: TierProgressItem) => {
    if (item.met) return 100;
    return Math.min(100, Math.round((item.current / item.required) * 100));
};

const formatProgressLabel = (key: string) => {
    const labels: Record<string, string> = {
        'completed_orders': 'Completed Orders',
        'total_sales': 'Total Sales',
        'rating': 'Rating',
        'dispute_rate': 'Dispute Rate',
        'cancellation_rate': 'Cancellation Rate',
        'account_age': 'Account Age',
    };
    return labels[key] || key;
};

const formatProgressValue = (key: string, value: number) => {
    if (key === 'total_sales') {
        return formatPrice(value);
    }
    if (key === 'rating') {
        return value.toFixed(1);
    }
    if (key.includes('rate')) {
        return value.toFixed(1) + '%';
    }
    if (key === 'account_age') {
        return value + ' days';
    }
    return value.toString();
};

</script>

<template>
    <Head title="Seller Dashboard - Marketplace" />
    
    <MarketplaceLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Seller Dashboard</h1>
                    <p class="text-sm text-gray-600">Manage your store, track sales, and grow your business on MyGrowNet</p>
                </div>
                <div class="flex items-center gap-3">
                    <span :class="[
                        'px-3 py-1.5 text-xs font-semibold rounded-full flex items-center gap-1.5',
                        seller.kyc_status === 'approved' ? 'bg-green-100 text-green-800' : 
                        seller.kyc_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'
                    ]">
                        <CheckBadgeIcon v-if="seller.kyc_status === 'approved'" class="h-4 w-4" aria-hidden="true" />
                        <ClockIcon v-else-if="seller.kyc_status === 'pending'" class="h-4 w-4" aria-hidden="true" />
                        {{ seller.trust_badge }} {{ seller.trust_label }}
                    </span>
                </div>
            </div>

            <!-- New Seller Onboarding -->
            <div v-if="isNewSeller" class="mb-6 bg-gradient-to-r from-orange-50 to-amber-50 border border-orange-200 rounded-xl p-6">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center flex-shrink-0">
                        <SparklesIcon class="h-6 w-6 text-white" aria-hidden="true" />
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Welcome to MyGrowNet Marketplace! 🎉</h3>
                        <p class="text-sm text-gray-700 mb-4">Complete these steps to start selling:</p>
                        <div class="space-y-2 mb-4">
                            <div class="flex items-center gap-2 text-sm">
                                <CheckCircleIcon v-if="seller.kyc_status === 'approved'" class="h-5 w-5 text-green-600" aria-hidden="true" />
                                <ClockIcon v-else class="h-5 w-5 text-yellow-600" aria-hidden="true" />
                                <span :class="seller.kyc_status === 'approved' ? 'text-green-700 font-medium' : 'text-gray-700'">
                                    Complete Profile Verification {{ seller.kyc_status === 'pending' ? '(Pending Review)' : '' }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2 text-sm">
                                <CheckCircleIcon v-if="stats.active_products > 0" class="h-5 w-5 text-green-600" aria-hidden="true" />
                                <div v-else class="h-5 w-5 rounded-full border-2 border-gray-300"></div>
                                <span :class="stats.active_products > 0 ? 'text-green-700 font-medium' : 'text-gray-700'">
                                    Add Your First Product
                                </span>
                            </div>
                            <div class="flex items-center gap-2 text-sm">
                                <div class="h-5 w-5 rounded-full border-2 border-gray-300"></div>
                                <span class="text-gray-700">Set Up Payout Details</span>
                            </div>
                        </div>
                        <Link 
                            v-if="seller.kyc_status === 'approved' && stats.active_products === 0"
                            :href="route('marketplace.seller.products.create')"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors font-medium text-sm"
                        >
                            <PlusCircleIcon class="h-5 w-5" aria-hidden="true" />
                            Add Your First Product
                        </Link>
                        <button
                            v-else-if="seller.kyc_status !== 'approved' && stats.active_products === 0"
                            disabled
                            class="inline-flex items-center gap-2 px-4 py-2 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed font-medium text-sm"
                            :title="seller.kyc_status === 'pending' ? 'Your account is under review' : 'Complete profile verification to add products'"
                        >
                            <PlusCircleIcon class="h-5 w-5" aria-hidden="true" />
                            Add Your First Product
                        </button>
                    </div>
                </div>
            </div>

            <!-- Notifications/Alerts -->
            <div v-if="notifications.length > 0 && !isNewSeller" class="mb-4 space-y-2">
                <div 
                    v-for="(alert, index) in notifications" 
                    :key="index"
                    :class="[
                        'p-3 rounded-lg flex items-start gap-3',
                        alert.type === 'warning' ? 'bg-yellow-50 border border-yellow-200' : 'bg-red-50 border border-red-200'
                    ]"
                >
                    <ExclamationTriangleIcon 
                        :class="[
                            'h-5 w-5 flex-shrink-0',
                            alert.type === 'warning' ? 'text-yellow-600' : 'text-red-600'
                        ]" 
                        aria-hidden="true" 
                    />
                    <p :class="[
                        'text-sm font-medium',
                        alert.type === 'warning' ? 'text-yellow-800' : 'text-red-800'
                    ]">{{ alert.message }}</p>
                </div>
            </div>

            <!-- KYC Pending Alert -->
            <div v-if="seller.kyc_status === 'pending' && !isNewSeller" class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg flex items-start gap-3">
                <ClockIcon class="h-5 w-5 text-yellow-600 flex-shrink-0" aria-hidden="true" />
                <div>
                    <p class="text-sm font-medium text-yellow-800">Verification Pending - Your account is under review.</p>
                </div>
            </div>

            <!-- Financial Overview - Business Health -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
                <!-- Available Balance -->
                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-5 text-white shadow-sm hover:shadow-md transition-shadow group relative">
                    <button 
                        class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity"
                        title="Funds ready to withdraw to your account"
                    >
                        <InformationCircleIcon class="h-4 w-4" aria-hidden="true" />
                    </button>
                    <div class="flex items-center gap-2 mb-2">
                        <CurrencyDollarIcon class="h-7 w-7 opacity-90" aria-hidden="true" />
                    </div>
                    <p class="text-xs opacity-90 mb-1 uppercase tracking-wide">Available Balance</p>
                    <p class="text-3xl font-bold mb-1">{{ formatPrice(stats.available_balance) }}</p>
                    <p class="text-xs opacity-80">Ready to withdraw</p>
                </div>

                <!-- Pending Balance -->
                <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl p-5 text-white shadow-sm hover:shadow-md transition-shadow group relative">
                    <button 
                        class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity"
                        title="Funds held in escrow until orders are completed"
                    >
                        <InformationCircleIcon class="h-4 w-4" aria-hidden="true" />
                    </button>
                    <div class="flex items-center gap-2 mb-2">
                        <ClockIcon class="h-7 w-7 opacity-90" aria-hidden="true" />
                    </div>
                    <p class="text-xs opacity-90 mb-1 uppercase tracking-wide">Pending Balance</p>
                    <p class="text-3xl font-bold mb-1">{{ formatPrice(stats.pending_balance) }}</p>
                    <p class="text-xs opacity-80">In escrow</p>
                </div>

                <!-- This Month Revenue -->
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-5 text-white shadow-sm hover:shadow-md transition-shadow group relative">
                    <button 
                        class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity"
                        title="Total sales for current month"
                    >
                        <InformationCircleIcon class="h-4 w-4" aria-hidden="true" />
                    </button>
                    <div class="flex items-center gap-2 mb-2">
                        <ChartBarIcon class="h-7 w-7 opacity-90" aria-hidden="true" />
                    </div>
                    <p class="text-xs opacity-90 mb-1 uppercase tracking-wide">This Month</p>
                    <p class="text-3xl font-bold mb-1">{{ formatPrice(stats.this_month_revenue) }}</p>
                    <p class="text-xs opacity-80">{{ stats.completed_orders }} orders</p>
                </div>

                <!-- Total Revenue -->
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-5 text-white shadow-sm hover:shadow-md transition-shadow group relative">
                    <button 
                        class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity"
                        title="All-time earnings from completed orders"
                    >
                        <InformationCircleIcon class="h-4 w-4" aria-hidden="true" />
                    </button>
                    <div class="flex items-center gap-2 mb-2">
                        <SparklesIcon class="h-7 w-7 opacity-90" aria-hidden="true" />
                    </div>
                    <p class="text-xs opacity-90 mb-1 uppercase tracking-wide">Total Revenue</p>
                    <p class="text-3xl font-bold mb-1">{{ formatPrice(stats.total_revenue) }}</p>
                    <p class="text-xs opacity-80">All-time</p>
                </div>
            </div>

            <!-- Store Snapshot - Compact Stats -->
            <div class="bg-white rounded-xl border border-gray-200 p-4 mb-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Store Snapshot</h3>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <CubeIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-xl font-bold text-gray-900">{{ stats.active_products }}</p>
                            <p class="text-xs text-gray-500">Active Products</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <ShoppingCartIcon class="h-5 w-5 text-orange-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-xl font-bold text-gray-900">{{ stats.pending_orders }}</p>
                            <p class="text-xs text-gray-500">Pending Orders</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <StarIcon class="h-5 w-5 text-amber-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-xl font-bold text-gray-900">{{ stats.rating.toFixed(1) }}</p>
                            <p class="text-xs text-gray-500">Rating</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <UsersIcon class="h-5 w-5 text-green-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-xl font-bold text-gray-900">{{ stats.total_customers }}</p>
                            <p class="text-xs text-gray-500">Customers</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seller Tier & Commission Card -->
            <div class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
                <div class="flex flex-col lg:flex-row lg:items-start gap-5">
                    <!-- Current Tier Info -->
                    <div class="flex-shrink-0">
                        <div class="flex items-center gap-3 mb-2">
                            <div :class="[
                                'w-12 h-12 rounded-full flex items-center justify-center',
                                tierProgress.current_tier === 'top' ? 'bg-amber-100' :
                                tierProgress.current_tier === 'trusted' ? 'bg-green-100' :
                                tierProgress.current_tier === 'verified' ? 'bg-blue-100' : 'bg-gray-100'
                            ]">
                                <span class="text-2xl">{{ currentTierInfo.badge }}</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900">{{ currentTierInfo.name }}</h3>
                                <p class="text-sm text-gray-600">{{ currentTierInfo.description }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 mt-3">
                            <div class="text-center">
                                <p class="text-2xl font-bold text-orange-600">{{ seller.effective_commission_rate }}%</p>
                                <p class="text-xs text-gray-500">Commission Rate</p>
                            </div>
                            <div class="text-center border-l border-gray-200 pl-4">
                                <p class="text-2xl font-bold text-gray-900">{{ seller.completed_orders }}</p>
                                <p class="text-xs text-gray-500">Completed Orders</p>
                            </div>
                            <div class="text-center border-l border-gray-200 pl-4">
                                <p class="text-2xl font-bold text-gray-900">{{ formatPrice(seller.total_sales_amount || 0) }}</p>
                                <p class="text-xs text-gray-500">Total Sales</p>
                            </div>
                        </div>
                    </div>

                    <!-- Progress to Next Tier -->
                    <div v-if="!tierProgress.is_max_tier && nextTierInfo" class="flex-1 lg:border-l lg:border-gray-200 lg:pl-5">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-semibold text-gray-700">Progress to {{ nextTierInfo.name }}</h4>
                            <span :class="[
                                'text-xs px-2 py-1 rounded-full font-medium',
                                tierProgress.next_tier === 'top' ? 'bg-amber-100 text-amber-700' :
                                tierProgress.next_tier === 'trusted' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'
                            ]">
                                {{ nextTierInfo.badge }} {{ nextTierInfo.commission }}% commission
                            </span>
                        </div>
                        <div class="space-y-3">
                            <div 
                                v-for="(item, key) in tierProgress.progress" 
                                :key="key"
                                class="flex items-center gap-3"
                            >
                                <div class="flex-shrink-0 w-5">
                                    <CheckCircleIcon 
                                        v-if="item.met" 
                                        class="h-5 w-5 text-green-500" 
                                        aria-hidden="true" 
                                    />
                                    <div 
                                        v-else 
                                        class="h-5 w-5 rounded-full border-2 border-gray-300"
                                    ></div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-xs text-gray-600">{{ formatProgressLabel(key as string) }}</span>
                                        <span class="text-xs font-medium" :class="item.met ? 'text-green-600' : 'text-gray-900'">
                                            {{ formatProgressValue(key as string, item.current) }} / {{ formatProgressValue(key as string, item.required) }}
                                        </span>
                                    </div>
                                    <div class="h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                        <div 
                                            :class="[
                                                'h-full rounded-full transition-all',
                                                item.met ? 'bg-green-500' : 'bg-orange-500'
                                            ]"
                                            :style="{ width: getProgressPercentage(item) + '%' }"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Max Tier Achieved -->
                    <div v-else-if="tierProgress.is_max_tier" class="flex-1 lg:border-l lg:border-gray-200 lg:pl-5">
                        <div class="text-center py-4">
                            <SparklesIcon class="h-10 w-10 text-amber-500 mx-auto mb-2" aria-hidden="true" />
                            <h4 class="font-bold text-gray-900 mb-1">You've reached the top tier!</h4>
                            <p class="text-sm text-gray-600">Enjoy the lowest commission rate of {{ currentTierInfo.commission }}%</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sales Chart & Quick Actions -->
            <div class="grid lg:grid-cols-3 gap-5 mb-5">
                <!-- Sales Chart -->
                <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-semibold text-gray-900">Sales Overview</h2>
                        <div class="flex gap-2">
                            <button class="px-3 py-1 text-xs rounded-lg bg-orange-100 text-orange-700 font-medium">
                                Last 7 Days
                            </button>
                        </div>
                    </div>
                    
                    <!-- Empty State for No Sales -->
                    <div v-if="!hasAnySales" class="py-12 text-center">
                        <ChartBarIcon class="h-16 w-16 text-gray-300 mx-auto mb-3" aria-hidden="true" />
                        <p class="text-gray-900 font-medium mb-1">No sales data yet</p>
                        <p class="text-sm text-gray-500 mb-4">Add products and promote your store to start selling</p>
                        <Link 
                            v-if="seller.kyc_status === 'approved'"
                            :href="route('marketplace.seller.products.create')"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors text-sm font-medium"
                        >
                            <PlusCircleIcon class="h-4 w-4" aria-hidden="true" />
                            Add Your First Product
                        </Link>
                        <button
                            v-else
                            disabled
                            class="inline-flex items-center gap-2 px-4 py-2 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed text-sm font-medium"
                            :title="seller.kyc_status === 'pending' ? 'Your account is under review' : 'Complete profile verification to add products'"
                        >
                            <PlusCircleIcon class="h-4 w-4" aria-hidden="true" />
                            Add Your First Product
                        </button>
                    </div>
                    
                    <!-- Chart with Data -->
                    <div v-else>
                        <!-- Simple Bar Chart - Reduced Height -->
                        <div class="flex items-end justify-between gap-2 h-32">
                            <div 
                                v-for="(data, index) in salesChartData" 
                                :key="index"
                                class="flex-1 flex flex-col items-center gap-2"
                            >
                                <div class="w-full flex items-end justify-center" style="height: 100px;">
                                    <div 
                                        class="w-full bg-gradient-to-t from-orange-500 to-amber-400 rounded-t-lg transition-all hover:from-orange-600 hover:to-amber-500 cursor-pointer"
                                        :style="{ height: `${(data.revenue / maxChartValue) * 100}%` }"
                                        :title="`K${data.revenue.toFixed(2)}`"
                                    ></div>
                                </div>
                                <span class="text-xs text-gray-500">{{ data.date }}</span>
                            </div>
                        </div>
                        
                        <div class="mt-4 pt-4 border-t border-gray-200 grid grid-cols-3 gap-4 text-center">
                            <div>
                                <p class="text-xs text-gray-500">Today</p>
                                <p class="text-base font-bold text-gray-900">{{ formatPrice(stats.today_revenue) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">This Week</p>
                                <p class="text-base font-bold text-gray-900">{{ formatPrice(stats.this_week_revenue) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Avg Order</p>
                                <p class="text-base font-bold text-gray-900">{{ formatPrice(stats.avg_order_value) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions - Context-Aware -->
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h2 class="font-semibold text-gray-900 mb-4">Quick Actions</h2>
                    <div class="space-y-2">
                        <!-- Add Product Button -->
                        <Link 
                            v-if="seller.kyc_status === 'approved'"
                            :href="route('marketplace.seller.products.create')"
                            class="flex items-center gap-3 p-3 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors"
                        >
                            <PlusCircleIcon class="h-5 w-5" aria-hidden="true" />
                            <span class="font-medium text-sm">{{ isNewSeller ? 'Add First Product' : 'Add Product' }}</span>
                        </Link>
                        <button
                            v-else
                            disabled
                            class="flex items-center gap-3 p-3 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed"
                            :title="seller.kyc_status === 'pending' ? 'Your account is under review' : 'Complete profile verification to add products'"
                        >
                            <PlusCircleIcon class="h-5 w-5" aria-hidden="true" />
                            <span class="font-medium text-sm">{{ isNewSeller ? 'Add First Product' : 'Add Product' }}</span>
                        </button>

                        <!-- My Products - Always visible -->
                        <Link 
                            :href="route('marketplace.seller.products.index')"
                            class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            <CubeIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                            <span class="font-medium text-gray-900 text-sm">My Products</span>
                            <span v-if="stats.total_products > 0" class="ml-auto text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">
                                {{ stats.total_products }}
                            </span>
                        </Link>

                        <!-- New Seller specific -->
                        <template v-if="isNewSeller">
                            <Link 
                                :href="route('marketplace.seller.profile')"
                                class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                            >
                                <CheckBadgeIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                                <span class="font-medium text-gray-900 text-sm">Complete Profile</span>
                            </Link>
                        </template>
                        
                        <!-- Active Seller Actions -->
                        <template v-else>
                            <Link 
                                :href="route('marketplace.seller.orders.index')"
                                class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                            >
                                <ShoppingCartIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                                <span class="font-medium text-gray-900 text-sm">View Orders</span>
                                <span v-if="stats.pending_orders > 0" class="ml-auto text-xs bg-orange-100 text-orange-600 px-2 py-0.5 rounded-full">
                                    {{ stats.pending_orders }} pending
                                </span>
                            </Link>
                            <button
                                class="w-full flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                                title="Coming soon"
                            >
                                <BanknotesIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                                <span class="font-medium text-gray-900 text-sm">Withdraw Funds</span>
                            </button>
                            <Link 
                                :href="route('marketplace.seller.profile')"
                                class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                            >
                                <CheckBadgeIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                                <span class="font-medium text-gray-900 text-sm">Store Settings</span>
                            </Link>
                        </template>
                    </div>

                    <!-- Help & Resources -->
                    <div class="mt-5 pt-5 border-t border-gray-200">
                        <h3 class="text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Help & Resources</h3>
                        <div class="space-y-1.5">
                            <Link 
                                :href="route('marketplace.seller-guide')"
                                class="block text-sm text-orange-600 hover:text-orange-700"
                            >
                                📚 Seller Guide
                            </Link>
                            <Link 
                                :href="route('marketplace.help')"
                                class="block text-sm text-orange-600 hover:text-orange-700"
                            >
                                💬 Help Center
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Performance & Customer Insights -->
            <div class="grid lg:grid-cols-2 gap-5 mb-5">
                <!-- Top Products -->
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-semibold text-gray-900">Top Selling Products</h2>
                        <Link 
                            :href="route('marketplace.seller.products.index')"
                            class="text-xs text-orange-600 hover:text-orange-700 font-medium"
                        >
                            View All
                        </Link>
                    </div>
                    
                    <div v-if="topProducts.length === 0" class="py-8 text-center">
                        <CubeIcon class="h-12 w-12 text-gray-300 mx-auto mb-2" aria-hidden="true" />
                        <p class="text-sm text-gray-900 font-medium mb-1">No sales yet</p>
                        <p class="text-xs text-gray-500 mb-3">Add products and promote your store to start selling</p>
                        <Link 
                            v-if="seller.kyc_status === 'approved'"
                            :href="route('marketplace.seller.products.create')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors text-xs font-medium"
                        >
                            <PlusCircleIcon class="h-4 w-4" aria-hidden="true" />
                            Add Product
                        </Link>
                        <button
                            v-else
                            disabled
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed text-xs font-medium"
                            :title="seller.kyc_status === 'pending' ? 'Your account is under review' : 'Complete profile verification to add products'"
                        >
                            <PlusCircleIcon class="h-4 w-4" aria-hidden="true" />
                            Add Product
                        </button>
                    </div>
                    
                    <div v-else class="space-y-2">
                        <div 
                            v-for="product in topProducts" 
                            :key="product.id"
                            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
                        >
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-sm text-gray-900 truncate">{{ product.name }}</p>
                                <p class="text-xs text-gray-500">{{ product.formatted_price }}</p>
                            </div>
                            <div class="text-right ml-3">
                                <p class="text-base font-bold text-orange-600">{{ product.total_sold || 0 }}</p>
                                <p class="text-xs text-gray-500">sold</p>
                            </div>
                        </div>
                    </div>

                    <!-- Product Alerts -->
                    <div v-if="stats.low_stock_count > 0 || stats.out_of_stock_count > 0" class="mt-3 pt-3 border-t border-gray-200 space-y-1.5">
                        <div v-if="stats.low_stock_count > 0" class="flex items-center gap-2 text-xs text-yellow-700">
                            <ExclamationTriangleIcon class="h-4 w-4" aria-hidden="true" />
                            <span>{{ stats.low_stock_count }} products low on stock</span>
                        </div>
                        <div v-if="stats.out_of_stock_count > 0" class="flex items-center gap-2 text-xs text-red-700">
                            <XCircleIcon class="h-4 w-4" aria-hidden="true" />
                            <span>{{ stats.out_of_stock_count }} products out of stock</span>
                        </div>
                    </div>
                </div>

                <!-- Customer Insights -->
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h2 class="font-semibold text-gray-900 mb-4">Customer Insights</h2>
                    
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                            <div>
                                <p class="text-xs text-gray-600">Total Customers</p>
                                <p class="text-xl font-bold text-gray-900">{{ stats.total_customers }}</p>
                            </div>
                            <UsersIcon class="h-8 w-8 text-blue-600" aria-hidden="true" />
                        </div>
                        
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <div>
                                <p class="text-xs text-gray-600">Repeat Customers</p>
                                <p class="text-xl font-bold text-gray-900">{{ stats.repeat_customers }}</p>
                                <p class="text-xs text-green-600 mt-0.5">{{ stats.repeat_rate.toFixed(1) }}% repeat rate</p>
                            </div>
                            <ArrowTrendingUpIcon class="h-8 w-8 text-green-600" aria-hidden="true" />
                        </div>

                        <div class="flex items-center justify-between p-3 bg-amber-50 rounded-lg">
                            <div>
                                <p class="text-xs text-gray-600">Average Order Value</p>
                                <p class="text-xl font-bold text-gray-900">{{ formatPrice(stats.avg_order_value) }}</p>
                            </div>
                            <CurrencyDollarIcon class="h-8 w-8 text-amber-600" aria-hidden="true" />
                        </div>
                    </div>

                    <!-- Recent Reviews -->
                    <div v-if="recentReviews && recentReviews.length > 0" class="mt-5 pt-5 border-t border-gray-200">
                        <h3 class="text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Recent Reviews</h3>
                        <div class="space-y-2">
                            <div 
                                v-for="review in recentReviews.slice(0, 2)" 
                                :key="review.id"
                                class="p-2.5 bg-gray-50 rounded-lg"
                            >
                                <div class="flex items-center gap-2 mb-1">
                                    <div class="flex">
                                        <StarIcon 
                                            v-for="i in 5" 
                                            :key="i"
                                            :class="[
                                                'h-3 w-3',
                                                i <= review.rating ? 'text-amber-400 fill-amber-400' : 'text-gray-300'
                                            ]"
                                            aria-hidden="true"
                                        />
                                    </div>
                                    <span class="text-xs text-gray-500">{{ review.buyer.name }}</span>
                                </div>
                                <p class="text-xs text-gray-700 line-clamp-2">{{ review.comment }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">Recent Orders</h2>
                    <Link 
                        :href="route('marketplace.seller.orders.index')"
                        class="text-xs text-orange-600 hover:text-orange-700 flex items-center gap-1 font-medium"
                    >
                        View All
                        <ArrowRightIcon class="h-3 w-3" aria-hidden="true" />
                    </Link>
                </div>

                <div v-if="recentOrders.length === 0" class="p-8 text-center">
                    <ShoppingCartIcon class="h-12 w-12 text-gray-300 mx-auto mb-2" aria-hidden="true" />
                    <p class="text-sm text-gray-900 font-medium mb-1">No orders yet</p>
                    <p class="text-xs text-gray-500">Orders will appear here once customers buy your products</p>
                </div>

                <div v-else class="divide-y divide-gray-200">
                    <Link
                        v-for="order in recentOrders"
                        :key="order.id"
                        :href="route('marketplace.seller.orders.show', order.id)"
                        class="flex items-center justify-between p-4 hover:bg-gray-50 transition-colors"
                    >
                        <div>
                            <p class="font-medium text-sm text-gray-900">{{ order.order_number }}</p>
                            <p class="text-xs text-gray-500">{{ order.buyer.name }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-sm text-gray-900">{{ order.formatted_total }}</p>
                            <span :class="['text-xs px-2 py-0.5 rounded-full', getStatusColor(order.status)]">
                                {{ order.status_label }}
                            </span>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </MarketplaceLayout>
</template>
