<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import InventoryLayout from '@/layouts/InventoryLayout.vue';
import {
    ArchiveBoxIcon,
    TagIcon,
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
    ExclamationTriangleIcon,
    PlusIcon,
    Cog6ToothIcon,
} from '@heroicons/vue/24/outline';

interface Props {
    stats: {
        total_items: number;
        total_categories: number;
        low_stock_count: number;
        out_of_stock_count: number;
        total_value: number;
        recent_movements: number;
    };
    lowStockItems: Array<{
        id: number;
        name: string;
        sku: string | null;
        current_stock: number;
        low_stock_threshold: number;
        category: { name: string } | null;
    }>;
    recentMovements: Array<{
        id: number;
        type: string;
        quantity: number;
        created_at: string;
        item: { name: string };
    }>;
}

const props = withDefaults(defineProps<Props>(), {
    stats: () => ({
        total_items: 0,
        total_categories: 0,
        low_stock_count: 0,
        out_of_stock_count: 0,
        total_value: 0,
        recent_movements: 0,
    }),
    lowStockItems: () => [],
    recentMovements: () => [],
});

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 0,
    }).format(amount);
};

const getMovementColor = (type: string) => {
    const colors: Record<string, string> = {
        purchase: 'text-green-600 bg-green-100',
        sale: 'text-red-600 bg-red-100',
        adjustment: 'text-blue-600 bg-blue-100',
        return: 'text-amber-600 bg-amber-100',
        damage: 'text-red-600 bg-red-100',
        initial: 'text-gray-600 bg-gray-100',
    };
    return colors[type] || 'text-gray-600 bg-gray-100';
};
</script>

<template>
    <InventoryLayout title="Dashboard">
        <Head title="Inventory Dashboard" />

        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Inventory Management</h1>
                        <p class="mt-1 text-sm text-gray-500">Track stock levels, manage products, and monitor inventory health</p>
                    </div>
                    <div class="flex gap-3">
                        <Link
                            :href="route('inventory.items.create')"
                            class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700"
                        >
                            <PlusIcon class="h-5 w-5" aria-hidden="true" />
                            Add Item
                        </Link>
                        <Link
                            :href="route('inventory.settings')"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            <Cog6ToothIcon class="h-5 w-5" aria-hidden="true" />
                            Settings
                        </Link>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                    <div class="rounded-xl bg-white p-4 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-teal-100 p-2">
                                <ArchiveBoxIcon class="h-5 w-5 text-teal-600" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Total Items</p>
                                <p class="text-lg font-semibold text-gray-900">{{ stats.total_items }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl bg-white p-4 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-blue-100 p-2">
                                <TagIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Categories</p>
                                <p class="text-lg font-semibold text-gray-900">{{ stats.total_categories }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl bg-white p-4 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-amber-100 p-2">
                                <ExclamationTriangleIcon class="h-5 w-5 text-amber-600" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Low Stock</p>
                                <p class="text-lg font-semibold text-amber-600">{{ stats.low_stock_count }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl bg-white p-4 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-red-100 p-2">
                                <ExclamationTriangleIcon class="h-5 w-5 text-red-600" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Out of Stock</p>
                                <p class="text-lg font-semibold text-red-600">{{ stats.out_of_stock_count }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl bg-white p-4 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-emerald-100 p-2">
                                <ArrowTrendingUpIcon class="h-5 w-5 text-emerald-600" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Total Value</p>
                                <p class="text-lg font-semibold text-gray-900">{{ formatCurrency(stats.total_value) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl bg-white p-4 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-purple-100 p-2">
                                <ArrowTrendingDownIcon class="h-5 w-5 text-purple-600" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Movements (7d)</p>
                                <p class="text-lg font-semibold text-gray-900">{{ stats.recent_movements }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <Link
                        :href="route('inventory.items')"
                        class="flex items-center gap-3 rounded-xl bg-white p-4 shadow-sm transition hover:shadow-md"
                    >
                        <div class="rounded-lg bg-teal-100 p-3">
                            <ArchiveBoxIcon class="h-6 w-6 text-teal-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Items</p>
                            <p class="text-xs text-gray-500">Manage products</p>
                        </div>
                    </Link>

                    <Link
                        :href="route('inventory.categories')"
                        class="flex items-center gap-3 rounded-xl bg-white p-4 shadow-sm transition hover:shadow-md"
                    >
                        <div class="rounded-lg bg-blue-100 p-3">
                            <TagIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Categories</p>
                            <p class="text-xs text-gray-500">Organize items</p>
                        </div>
                    </Link>

                    <Link
                        :href="route('inventory.movements')"
                        class="flex items-center gap-3 rounded-xl bg-white p-4 shadow-sm transition hover:shadow-md"
                    >
                        <div class="rounded-lg bg-purple-100 p-3">
                            <ArrowTrendingUpIcon class="h-6 w-6 text-purple-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Movements</p>
                            <p class="text-xs text-gray-500">Stock history</p>
                        </div>
                    </Link>

                    <Link
                        :href="route('inventory.alerts')"
                        class="flex items-center gap-3 rounded-xl bg-white p-4 shadow-sm transition hover:shadow-md"
                    >
                        <div class="rounded-lg bg-amber-100 p-3">
                            <ExclamationTriangleIcon class="h-6 w-6 text-amber-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Alerts</p>
                            <p class="text-xs text-gray-500">Stock warnings</p>
                        </div>
                    </Link>
                </div>

                <!-- Content Grid -->
                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Low Stock Items -->
                    <div class="rounded-xl bg-white p-6 shadow-sm">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900">Low Stock Items</h2>
                            <Link :href="route('inventory.alerts')" class="text-sm text-teal-600 hover:text-teal-700">
                                View all
                            </Link>
                        </div>

                        <div v-if="lowStockItems.length === 0" class="py-8 text-center text-gray-500">
                            <ExclamationTriangleIcon class="mx-auto h-12 w-12 text-gray-300" aria-hidden="true" />
                            <p class="mt-2">No low stock items</p>
                        </div>

                        <div v-else class="space-y-3">
                            <div
                                v-for="item in lowStockItems.slice(0, 5)"
                                :key="item.id"
                                class="flex items-center justify-between rounded-lg border border-amber-200 bg-amber-50 p-3"
                            >
                                <div>
                                    <p class="font-medium text-gray-900">{{ item.name }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ item.category?.name || 'Uncategorized' }}
                                        <span v-if="item.sku"> · {{ item.sku }}</span>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-amber-600">{{ item.current_stock }}</p>
                                    <p class="text-xs text-gray-500">of {{ item.low_stock_threshold }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Movements -->
                    <div class="rounded-xl bg-white p-6 shadow-sm">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900">Recent Movements</h2>
                            <Link :href="route('inventory.movements')" class="text-sm text-teal-600 hover:text-teal-700">
                                View all
                            </Link>
                        </div>

                        <div v-if="recentMovements.length === 0" class="py-8 text-center text-gray-500">
                            <ArrowTrendingUpIcon class="mx-auto h-12 w-12 text-gray-300" aria-hidden="true" />
                            <p class="mt-2">No recent movements</p>
                        </div>

                        <div v-else class="space-y-3">
                            <div
                                v-for="movement in recentMovements.slice(0, 5)"
                                :key="movement.id"
                                class="flex items-center justify-between rounded-lg bg-gray-50 p-3"
                            >
                                <div class="flex items-center gap-3">
                                    <span
                                        :class="[getMovementColor(movement.type), 'rounded-full px-2 py-1 text-xs font-medium capitalize']"
                                    >
                                        {{ movement.type }}
                                    </span>
                                    <p class="font-medium text-gray-900">{{ movement.item.name }}</p>
                                </div>
                                <div class="text-right">
                                    <p :class="movement.quantity > 0 ? 'text-green-600' : 'text-red-600'" class="font-semibold">
                                        {{ movement.quantity > 0 ? '+' : '' }}{{ movement.quantity }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </InventoryLayout>
</template>
