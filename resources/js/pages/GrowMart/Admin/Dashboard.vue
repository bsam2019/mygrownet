<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/GrowMartAdminLayout.vue';
import { ShoppingCartIcon, CubeIcon, FolderIcon, BuildingStorefrontIcon, ClipboardDocumentListIcon, CurrencyDollarIcon, ExclamationTriangleIcon, StarIcon, ChartBarIcon } from '@heroicons/vue/24/outline';

interface RecentOrder {
    id: number; order_number: string; customer: string;
    total_formatted: string; status: string; created_at: string;
}

interface RecentProduct {
    id: number; name: string; category: string;
    price: string; status: string; created_at: string;
}

interface Props {
    stats: {
        total_products: number; active_products: number;
        total_categories: number; total_warehouses: number;
        pending_orders: number; processing_orders: number;
        today_orders: number; total_revenue: number;
        total_revenue_formatted: string;
        recent_orders: RecentOrder[];
        recent_products: RecentProduct[];
        revenue_chart: { labels: string[]; revenue: number[]; counts: number[] };
        order_status_breakdown: { status: string; count: number }[];
        top_products: { id: number; name: string; total_sold: number; total_revenue_formatted: string }[];
        aov: { aov_formatted: string; total_orders: number };
        pending_reviews: number;
        low_stock_count: number;
        out_of_stock_count: number;
    };
}

const props = defineProps<Props>();

const statusBadge = (s: string) => ({
    pending: 'bg-yellow-100 text-yellow-800', confirmed: 'bg-blue-100 text-blue-800',
    processing: 'bg-indigo-100 text-indigo-800', out_for_delivery: 'bg-purple-100 text-purple-800',
    delivered: 'bg-green-100 text-green-800', cancelled: 'bg-red-100 text-red-800',
}[s] || 'bg-gray-100 text-gray-800');

const productBadge = (s: string) => s === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';

const maxStatusCount = Math.max(...props.stats.order_status_breakdown.map(s => s.count), 1);
</script>

<template>
    <Head title="GrowMart - Admin" />

    <AdminLayout title="GrowMart">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <p class="text-gray-600">Manage your online grocery supermarket</p>
            </div>
            <div class="flex gap-2">
                <Link :href="route('admin.growmart.orders.index')" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors text-sm font-medium">
                    View Orders
                </Link>
                <Link :href="route('admin.growmart.products.create')" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                    + Add Product
                </Link>
                <Link :href="route('admin.growmart.inventory.index')" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors text-sm font-medium">
                    Inventory
                </Link>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <div class="flex items-center gap-3"><div class="p-2 bg-blue-500 rounded-lg"><CubeIcon class="h-6 w-6 text-white" /></div><div><p class="text-2xl font-bold text-gray-900">{{ stats.total_products }}</p><p class="text-sm text-gray-600">Total Products</p><p class="text-xs text-green-600">{{ stats.active_products }} active</p></div></div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <div class="flex items-center gap-3"><div class="p-2 bg-yellow-500 rounded-lg"><ClipboardDocumentListIcon class="h-6 w-6 text-white" /></div><div><p class="text-2xl font-bold text-gray-900">{{ stats.pending_orders }}</p><p class="text-sm text-gray-600">Pending Orders</p><p class="text-xs text-orange-600">{{ stats.processing_orders }} processing</p></div></div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <div class="flex items-center gap-3"><div class="p-2 bg-green-500 rounded-lg"><ShoppingCartIcon class="h-6 w-6 text-white" /></div><div><p class="text-2xl font-bold text-gray-900">{{ stats.today_orders }}</p><p class="text-sm text-gray-600">Today's Orders</p><p class="text-xs text-gray-500">AOV: {{ stats.aov.aov_formatted }}</p></div></div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <div class="flex items-center gap-3"><div class="p-2 bg-purple-500 rounded-lg"><CurrencyDollarIcon class="h-6 w-6 text-white" /></div><div><p class="text-2xl font-bold text-gray-900">{{ stats.total_revenue_formatted }}</p><p class="text-sm text-gray-600">Total Revenue</p><p class="text-xs text-gray-500">{{ stats.aov.total_orders }} delivered orders</p></div></div>
            </div>
        </div>

        <!-- Alert Cards -->
        <div v-if="stats.pending_reviews > 0 || stats.low_stock_count > 0 || stats.out_of_stock_count > 0" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <Link v-if="stats.pending_reviews > 0" :href="route('admin.growmart.reviews.index')" class="flex items-center gap-3 bg-amber-50 border border-amber-200 rounded-lg p-3 hover:bg-amber-100 transition-colors">
                <StarIcon class="h-6 w-6 text-amber-500" />
                <div><p class="text-sm font-medium text-amber-800">{{ stats.pending_reviews }} pending review{{ stats.pending_reviews !== 1 ? 's' : '' }}</p><p class="text-xs text-amber-600">Click to moderate</p></div>
            </Link>
            <Link v-if="stats.low_stock_count > 0" :href="route('admin.growmart.inventory.index') + '?low_stock=1'" class="flex items-center gap-3 bg-orange-50 border border-orange-200 rounded-lg p-3 hover:bg-orange-100 transition-colors">
                <ExclamationTriangleIcon class="h-6 w-6 text-orange-500" />
                <div><p class="text-sm font-medium text-orange-800">{{ stats.low_stock_count }} low stock item{{ stats.low_stock_count !== 1 ? 's' : '' }}</p><p class="text-xs text-orange-600">{{ stats.out_of_stock_count }} out of stock</p></div>
            </Link>
        </div>

        <!-- Charts Row -->
        <div class="grid md:grid-cols-2 gap-4 mb-6">
            <!-- Revenue Chart (bar) -->
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <h3 class="font-semibold text-gray-900 mb-3">Revenue (Last 14 Days)</h3>
                <div v-if="stats.revenue_chart.labels.length" class="flex items-end gap-1 h-32">
                    <div v-for="(rev, i) in stats.revenue_chart.revenue" :key="i" class="flex-1 flex flex-col items-center gap-1">
                        <span class="text-[10px] text-gray-400">{{ (rev / 100).toFixed(0) }}</span>
                        <div class="w-full bg-emerald-500 rounded-t" :style="{ height: Math.max((rev / Math.max(...stats.revenue_chart.revenue, 1)) * 100, 4) + '%' }"></div>
                        <span class="text-[10px] text-gray-400 truncate w-full text-center">{{ stats.revenue_chart.labels[i].slice(-5) }}</span>
                    </div>
                </div>
                <p v-else class="text-sm text-gray-500 text-center py-8">No revenue data yet.</p>
            </div>

            <!-- Order Status Breakdown -->
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <h3 class="font-semibold text-gray-900 mb-3">Orders by Status</h3>
                <div class="space-y-2">
                    <div v-for="s in stats.order_status_breakdown" :key="s.status" class="flex items-center gap-3">
                        <span class="text-sm text-gray-700 w-32 capitalize">{{ s.status }}</span>
                        <div class="flex-1 bg-gray-100 rounded-full h-5 overflow-hidden">
                            <div class="h-full bg-emerald-500 rounded-full transition-all" :style="{ width: (s.count / maxStatusCount) * 100 + '%' }"></div>
                        </div>
                        <span class="text-sm font-medium text-gray-900 w-8 text-right">{{ s.count }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Row: Top Products + Recent Orders -->
        <div class="grid md:grid-cols-2 gap-4 mb-6">
            <!-- Top Selling Products -->
            <div class="bg-white rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-900">Top Selling Products</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    <div v-for="p in stats.top_products" :key="p.id" class="px-6 py-3 flex items-center justify-between hover:bg-gray-50">
                        <div><p class="font-medium text-gray-900 text-sm">{{ p.name }}</p><p class="text-xs text-gray-500">{{ p.total_sold }} sold</p></div>
                        <p class="text-sm font-semibold text-gray-900">{{ p.total_revenue_formatted }}</p>
                    </div>
                    <div v-if="stats.top_products.length === 0" class="px-6 py-8 text-center text-gray-500 text-sm">No sales data yet.</div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="bg-white rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-900">Recent Orders</h3>
                    <Link :href="route('admin.growmart.orders.index')" class="text-sm text-emerald-600 hover:text-emerald-700">View All</Link>
                </div>
                <div class="divide-y divide-gray-100">
                    <div v-for="o in stats.recent_orders" :key="o.id" class="px-6 py-3 flex items-center justify-between hover:bg-gray-50">
                        <div><Link :href="route('admin.growmart.orders.show', o.id)" class="font-mono text-sm font-medium text-gray-900 hover:text-emerald-600">{{ o.order_number }}</Link><p class="text-xs text-gray-500">{{ o.customer }} • {{ o.created_at }}</p></div>
                        <div class="text-right"><span :class="statusBadge(o.status)" class="px-2 py-0.5 rounded-full text-xs font-medium capitalize">{{ o.status.replace('_', ' ') }}</span><p class="text-sm font-semibold text-gray-900 mt-0.5">{{ o.total_formatted }}</p></div>
                    </div>
                    <div v-if="stats.recent_orders.length === 0" class="px-6 py-8 text-center text-gray-500 text-sm">No orders yet.</div>
                </div>
            </div>
        </div>

        <!-- Recent Products -->
        <div class="bg-white rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="font-semibold text-gray-900">Recent Products</h3>
                <Link :href="route('admin.growmart.products.index')" class="text-sm text-emerald-600 hover:text-emerald-700">View All</Link>
            </div>
            <div class="divide-y divide-gray-100">
                <div v-for="p in stats.recent_products" :key="p.id" class="px-6 py-3 flex items-center justify-between hover:bg-gray-50">
                    <div><p class="font-medium text-gray-900 text-sm">{{ p.name }}</p><p class="text-xs text-gray-500">{{ p.category || '—' }}</p></div>
                    <div class="text-right"><span :class="productBadge(p.status)" class="px-2 py-0.5 rounded-full text-xs font-medium">{{ p.status }}</span><p class="text-sm font-semibold text-gray-900 mt-0.5">{{ p.price }}</p></div>
                </div>
                <div v-if="stats.recent_products.length === 0" class="px-6 py-8 text-center text-gray-500 text-sm">No products yet.</div>
            </div>
        </div>
    </AdminLayout>
</template>
