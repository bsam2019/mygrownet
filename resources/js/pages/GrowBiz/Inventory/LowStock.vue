<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import GrowBizLayout from '@/layouts/GrowBizLayout.vue';
import {
    ArrowLeftIcon,
    ExclamationTriangleIcon,
    XCircleIcon,
    CubeIcon,
} from '@heroicons/vue/24/outline';

interface InventoryItem {
    id: number;
    name: string;
    sku: string | null;
    unit: string;
    current_stock: number;
    low_stock_threshold: number;
    selling_price: number;
    is_low_stock: boolean;
    is_out_of_stock: boolean;
}

interface Props {
    lowStockItems: InventoryItem[];
    outOfStockItems: InventoryItem[];
    stats: {
        low_stock_count: number;
        out_of_stock_count: number;
    };
}

const props = defineProps<Props>();

defineOptions({ layout: GrowBizLayout });

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', { style: 'currency', currency: 'ZMW' }).format(amount);
};
</script>

<template>
    <div class="p-4 space-y-4">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Link :href="route('growbiz.inventory.index')" class="p-2 -ml-2 rounded-xl hover:bg-gray-100">
                <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
            </Link>
            <div class="flex-1">
                <h1 class="text-xl font-bold text-gray-900">Stock Alerts</h1>
                <p class="text-sm text-gray-500">Items needing attention</p>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-red-50 rounded-xl p-4">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-red-100 rounded-lg">
                        <XCircleIcon class="h-5 w-5 text-red-600" aria-hidden="true" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-red-700">{{ stats.out_of_stock_count }}</p>
                        <p class="text-xs text-red-600">Out of Stock</p>
                    </div>
                </div>
            </div>
            <div class="bg-amber-50 rounded-xl p-4">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-amber-100 rounded-lg">
                        <ExclamationTriangleIcon class="h-5 w-5 text-amber-600" aria-hidden="true" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-amber-700">{{ stats.low_stock_count }}</p>
                        <p class="text-xs text-amber-600">Low Stock</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Out of Stock Section -->
        <div v-if="outOfStockItems.length > 0" class="space-y-2">
            <h2 class="flex items-center gap-2 text-sm font-semibold text-red-600 uppercase">
                <XCircleIcon class="h-4 w-4" aria-hidden="true" />
                Out of Stock ({{ outOfStockItems.length }})
            </h2>
            <div class="space-y-2">
                <Link
                    v-for="item in outOfStockItems"
                    :key="item.id"
                    :href="route('growbiz.inventory.show', item.id)"
                    class="block bg-white rounded-xl p-4 ring-2 ring-red-100 hover:ring-red-200 transition-all"
                >
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
                            <CubeIcon class="h-5 w-5 text-red-600" aria-hidden="true" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900">{{ item.name }}</p>
                            <p v-if="item.sku" class="text-xs text-gray-500">SKU: {{ item.sku }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-red-600">0</p>
                            <p class="text-xs text-gray-500">{{ item.unit }}s</p>
                        </div>
                    </div>
                </Link>
            </div>
        </div>

        <!-- Low Stock Section -->
        <div v-if="lowStockItems.length > 0" class="space-y-2">
            <h2 class="flex items-center gap-2 text-sm font-semibold text-amber-600 uppercase">
                <ExclamationTriangleIcon class="h-4 w-4" aria-hidden="true" />
                Low Stock ({{ lowStockItems.length }})
            </h2>
            <div class="space-y-2">
                <Link
                    v-for="item in lowStockItems"
                    :key="item.id"
                    :href="route('growbiz.inventory.show', item.id)"
                    class="block bg-white rounded-xl p-4 ring-2 ring-amber-100 hover:ring-amber-200 transition-all"
                >
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                            <CubeIcon class="h-5 w-5 text-amber-600" aria-hidden="true" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900">{{ item.name }}</p>
                            <p class="text-xs text-gray-500">Threshold: {{ item.low_stock_threshold }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-amber-600">{{ item.current_stock }}</p>
                            <p class="text-xs text-gray-500">{{ item.unit }}s</p>
                        </div>
                    </div>
                </Link>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="outOfStockItems.length === 0 && lowStockItems.length === 0" class="text-center py-12 bg-white rounded-xl">
            <div class="w-16 h-16 mx-auto bg-emerald-100 rounded-full flex items-center justify-center">
                <CubeIcon class="h-8 w-8 text-emerald-600" aria-hidden="true" />
            </div>
            <p class="mt-4 text-lg font-medium text-gray-900">All stocked up!</p>
            <p class="text-gray-500">No items need restocking right now.</p>
            <Link
                :href="route('growbiz.inventory.index')"
                class="inline-block mt-4 px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium"
            >
                View All Items
            </Link>
        </div>
    </div>
</template>
