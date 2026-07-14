<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import StockAuditLayout from '@/layouts/StockAuditLayout.vue';
import { ref } from 'vue';
import {
    PlusIcon,
    MagnifyingGlassIcon,
    TrashIcon,
} from '@heroicons/vue/24/outline';
import { useNotifications } from '@/composables/useNotifications';
import { useConfirmDialog } from '@/composables/useConfirmDialog';

interface Item {
    id: number;
    name: string;
    sku: string | null;
    description: string | null;
    unit_price: number;
    unit: string;
    system_quantity: number;
    category: string | null;
    sa_bin_id: number | null;
}

interface Props {
    items: Item[];
}

const props = defineProps<Props>();
const { success, error: notifyError } = useNotifications();
const confirm = useConfirmDialog();

const search = ref('');

const filteredItems = ref(props.items);

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', { style: 'currency', currency: 'ZMW', minimumFractionDigits: 2 }).format(amount);
};

const applySearch = () => {
    const q = search.value.toLowerCase();
    filteredItems.value = props.items.filter(i =>
        i.name.toLowerCase().includes(q) ||
        (i.sku && i.sku.toLowerCase().includes(q)) ||
        (i.category && i.category.toLowerCase().includes(q))
    );
};

const deleteItem = async (item: Item) => {
    const ok = await confirm.show(`Delete "${item.name}"? This cannot be undone.`, 'Delete Item');
    if (ok) {
        router.delete(route('stock-audit.items.destroy', item.id), {
            onSuccess: () => success('Item deleted'),
            onError: () => notifyError('Failed to delete item'),
        });
    }
};
</script>

<template>
    <StockAuditLayout title="Items">
        <Head title="Items - StockFlow" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <h1 class="text-2xl font-bold text-gray-900">Inventory Items</h1>
                    <div class="flex gap-3">
                        <Link :href="route('stock-audit.items.create')" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                            <PlusIcon class="h-5 w-5" aria-hidden="true" />
                            Add Item
                        </Link>
                    </div>
                </div>

                <div class="mb-6 rounded-xl bg-white p-4 shadow-sm">
                    <div class="relative">
                        <MagnifyingGlassIcon class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" aria-hidden="true" />
                        <input v-model="search" @input="applySearch" type="text" placeholder="Search by name, SKU, or category..." class="w-full rounded-lg border-gray-300 pl-10 focus:border-emerald-500 focus:ring-emerald-500" />
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Category</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Stock</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Unit Price</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="item in filteredItems" :key="item.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <Link :href="route('stock-audit.items.show', item.id)" class="font-medium text-emerald-600 hover:text-emerald-700">
                                        {{ item.name }}
                                    </Link>
                                    <div v-if="item.sku" class="text-xs text-gray-500">SKU: {{ item.sku }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ item.category || '-' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <span :class="[item.system_quantity <= 0 ? 'text-red-600 font-semibold' : 'text-gray-900']">
                                        {{ item.system_quantity }} {{ item.unit }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm text-gray-900">{{ formatCurrency(item.unit_price) }}</td>
                                <td class="px-6 py-4 text-right">
                                    <button @click="deleteItem(item)" class="rounded p-1 text-gray-400 hover:bg-red-100 hover:text-red-600" title="Delete item">
                                        <TrashIcon class="h-5 w-5" aria-hidden="true" />
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="filteredItems.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">No items found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </StockAuditLayout>
</template>
