<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { useCurrency } from '@/composables/useCurrency';
import LoadingSkeleton from '@/components/StockFlow/LoadingSkeleton.vue';
import Pagination from '@/components/StockFlow/Pagination.vue';
import { ref } from 'vue';
import {
    PlusIcon,
    MagnifyingGlassIcon,
    TrashIcon,
    DocumentArrowDownIcon,
    ArrowUpTrayIcon,
} from '@heroicons/vue/24/outline';
import { useNotifications } from '@/composables/useNotifications';
import { useConfirmDialog } from '@/composables/useConfirmDialog';
import { useStockflowRoute } from '@/composables/useStockflowRoute';

const { route } = useStockflowRoute();

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
    is_expirable: boolean;
    expiry_date: string | null;
}

interface Props {
    items: {
        data: Item[];
        links: { url: string | null; label: string; active: boolean }[];
        meta: { current_page: number; last_page: number; total: number; from: number; to: number };
    };
}

defineProps<Props>();
const { success, error: notifyError } = useNotifications();
const confirm = useConfirmDialog();

const search = ref('');
const csvUploading = ref(false);

const { formatCurrency } = useCurrency();

const uploadItemsCsv = async (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (!input.files?.length) return;
    const file = input.files[0];
    csvUploading.value = true;
    const formData = new FormData();
    formData.append('file', file);
    try {
        await router.post(route('stockflow.sub.items.import-csv'), formData, {
            onSuccess: () => { success('Items imported'); input.value = ''; },
            onError: (err) => { notifyError(Object.values(err).join(', ')); input.value = ''; },
        });
    } catch { notifyError('Upload failed'); }
    csvUploading.value = false;
};

const deleteItem = async (item: Item) => {
    const ok = await confirm.show(`Delete "${item.name}"? This cannot be undone.`, 'Delete Item');
    if (ok) {
        router.delete(route('stockflow.sub.items.destroy', item.id), {
            onSuccess: () => success('Item deleted'),
            onError: () => notifyError('Failed to delete item'),
        });
    }
};
</script>

<template>
    <StockFlowLayout title="Items">
        <Head title="Items - StockFlow" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <h1 class="text-2xl font-bold text-gray-900">Inventory Items</h1>
                    <div class="flex gap-2">
                        <a :href="route('stockflow.sub.templates.items')" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-600 hover:text-emerald-600 hover:border-emerald-200">
                            <DocumentArrowDownIcon class="h-4 w-4" />
                            Template
                        </a>
                        <label class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-600 hover:text-emerald-600 hover:border-emerald-200 cursor-pointer">
                            <ArrowUpTrayIcon class="h-4 w-4" />
                            Import CSV
                            <input type="file" accept=".csv,.txt" class="hidden" @change="uploadItemsCsv" />
                        </label>
                        <Link :href="route('stockflow.sub.items.create')" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                            <PlusIcon class="h-5 w-5" aria-hidden="true" />
                            Add Item
                        </Link>
                    </div>
                </div>

                <div class="mb-6 rounded-xl bg-white p-4 shadow-sm">
                    <div class="relative">
                        <MagnifyingGlassIcon class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" aria-hidden="true" />
                        <input v-model="search" @keydown.enter="router.get(route('stockflow.sub.items.index'), { search: search.value }, { preserveState: true, preserveScroll: true })" type="text" placeholder="Search by name, SKU, or category..." class="w-full rounded-lg border-gray-300 pl-10 focus:border-emerald-500 focus:ring-emerald-500" />
                    </div>
                </div>

                <LoadingSkeleton v-if="!items.data?.length" type="table" />
                <template v-else>
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Item</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Category</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Expiry</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Stock</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Unit Price</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="item in items.data" :key="item.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <Link :href="route('stockflow.sub.items.show', item.id)" class="font-medium text-emerald-600 hover:text-emerald-700">
                                            {{ item.name }}
                                        </Link>
                                        <div class="flex items-center gap-2">
                                            <span v-if="item.sku" class="text-xs text-gray-500">SKU: {{ item.sku }}</span>
                                            <span v-if="item.is_expirable && item.expiry_date && new Date(item.expiry_date) < new Date()" class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">Expired</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ item.category || '-' }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span v-if="item.is_expirable && item.expiry_date" :class="[new Date(item.expiry_date) < new Date() ? 'text-red-600 font-semibold' : 'text-amber-600']">
                                            {{ item.expiry_date }}
                                        </span>
                                        <span v-else class="text-gray-400">-</span>
                                    </td>
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
                                <tr v-if="!items.data?.length">
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">No items found</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination :links="items.links" :meta="items.meta" />
                </template>
            </div>
        </div>
    </StockFlowLayout>
</template>
