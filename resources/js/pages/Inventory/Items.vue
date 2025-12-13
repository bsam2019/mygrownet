<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import InventoryLayout from '@/layouts/InventoryLayout.vue';
import { ref, computed } from 'vue';
import {
    PlusIcon,
    MagnifyingGlassIcon,
    PencilIcon,
    TrashIcon,
    FunnelIcon,
} from '@heroicons/vue/24/outline';

interface Category {
    id: number;
    name: string;
}

interface Item {
    id: number;
    name: string;
    sku: string | null;
    description: string | null;
    category: Category | null;
    unit: string;
    cost_price: number;
    selling_price: number;
    current_stock: number;
    low_stock_threshold: number;
    is_active: boolean;
}

interface Props {
    items: { data: Item[]; links: any; meta: any };
    categories: Category[];
    filters: Record<string, any>;
}

const props = defineProps<Props>();
const search = ref(props.filters.search || '');
const categoryFilter = ref(props.filters.category_id || '');

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', { style: 'currency', currency: 'ZMW', minimumFractionDigits: 0 }).format(amount);
};

const applyFilters = () => {
    router.get(route('inventory.items'), { search: search.value, category_id: categoryFilter.value }, { preserveState: true });
};

const deleteItem = (item: Item) => {
    if (confirm(`Delete "${item.name}"?`)) {
        router.delete(route('inventory.items.destroy', item.id));
    }
};
</script>

<template>
    <InventoryLayout title="Items">
        <Head title="Inventory Items" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <h1 class="text-2xl font-bold text-gray-900">Inventory Items</h1>
                    <Link :href="route('inventory.items.create')" class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">
                        <PlusIcon class="h-5 w-5" aria-hidden="true" />
                        Add Item
                    </Link>
                </div>

                <!-- Filters -->
                <div class="mb-6 flex flex-col gap-4 rounded-xl bg-white p-4 shadow-sm sm:flex-row">
                    <div class="relative flex-1">
                        <MagnifyingGlassIcon class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" aria-hidden="true" />
                        <input v-model="search" @keyup.enter="applyFilters" type="text" placeholder="Search items..." class="w-full rounded-lg border-gray-300 pl-10 focus:border-teal-500 focus:ring-teal-500" />
                    </div>
                    <select v-model="categoryFilter" @change="applyFilters" class="rounded-lg border-gray-300 focus:border-teal-500 focus:ring-teal-500">
                        <option value="">All Categories</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                    <button @click="applyFilters" class="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                        <FunnelIcon class="h-5 w-5" aria-hidden="true" />
                        Filter
                    </button>
                </div>

                <!-- Items Table -->
                <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Category</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Stock</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Price</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="item in items.data" :key="item.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">{{ item.name }}</div>
                                    <div v-if="item.sku" class="text-xs text-gray-500">SKU: {{ item.sku }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ item.category?.name || '-' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <span :class="item.current_stock <= item.low_stock_threshold ? 'text-amber-600 font-semibold' : 'text-gray-900'">
                                        {{ item.current_stock }} {{ item.unit }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium text-gray-900">{{ formatCurrency(item.selling_price) }}</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <Link :href="route('inventory.items.edit', item.id)" class="rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                                            <PencilIcon class="h-5 w-5" aria-hidden="true" />
                                        </Link>
                                        <button @click="deleteItem(item)" class="rounded p-1 text-gray-400 hover:bg-red-100 hover:text-red-600">
                                            <TrashIcon class="h-5 w-5" aria-hidden="true" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="items.data.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">No items found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </InventoryLayout>
</template>
