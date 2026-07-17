<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { useCurrency } from '@/composables/useCurrency';
import LoadingSkeleton from '@/components/StockFlow/LoadingSkeleton.vue';
import Pagination from '@/components/StockFlow/Pagination.vue';
import { ref, computed } from 'vue';
import {
    PlusIcon,
    MagnifyingGlassIcon,
    TrashIcon,
    DocumentArrowDownIcon,
    ArrowUpTrayIcon,
    XMarkIcon,
    AdjustmentsHorizontalIcon,
    CurrencyDollarIcon,
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
    image_url: string | null;
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
const selectedIds = ref<number[]>([]);
const showBulkStock = ref(false);
const showBulkPrice = ref(false);
const bulkStockChange = ref(0);
const bulkPriceValue = ref(0);
const bulkReason = ref('bulk adjustment');
const bulkProcessing = ref(false);

const allSelected = computed(() => {
    if (!items.data?.length) return false;
    return selectedIds.value.length === items.data.length;
});

const toggleAll = () => {
    if (allSelected.value) {
        selectedIds.value = [];
    } else {
        selectedIds.value = items.data.map(i => i.id);
    }
};

const toggleItem = (id: number) => {
    const idx = selectedIds.value.indexOf(id);
    if (idx > -1) {
        selectedIds.value.splice(idx, 1);
    } else {
        selectedIds.value.push(id);
    }
};

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

const executeBulkDelete = async () => {
    if (selectedIds.value.length === 0) return;
    const ok = await confirm.show(`Delete ${selectedIds.value.length} selected items? This cannot be undone.`, 'Delete Items');
    if (!ok) return;
    bulkProcessing.value = true;
    router.post(route('stockflow.sub.items.bulk-delete'), { ids: selectedIds.value }, {
        onSuccess: () => { success(`${selectedIds.value.length} items deleted`); selectedIds.value = []; },
        onError: () => notifyError('Failed to delete items'),
        preserveScroll: true,
    });
    bulkProcessing.value = false;
};

const executeBulkStock = async () => {
    if (selectedIds.value.length === 0 || bulkProcessing.value) return;
    bulkProcessing.value = true;
    router.post(route('stockflow.sub.items.bulk-adjust-stock'), {
        ids: selectedIds.value,
        quantity_change: bulkStockChange.value,
        reason: bulkReason.value,
    }, {
        onSuccess: () => { success(`Stock adjusted for ${selectedIds.value.length} items`); showBulkStock.value = false; selectedIds.value = []; },
        onError: () => notifyError('Failed to adjust stock'),
        preserveScroll: true,
    });
    bulkProcessing.value = false;
};

const executeBulkPrice = async () => {
    if (selectedIds.value.length === 0 || bulkProcessing.value) return;
    bulkProcessing.value = true;
    router.post(route('stockflow.sub.items.bulk-update-price'), {
        ids: selectedIds.value,
        unit_price: bulkPriceValue.value,
    }, {
        onSuccess: () => { success(`Price updated for ${selectedIds.value.length} items`); showBulkPrice.value = false; selectedIds.value = []; },
        onError: () => notifyError('Failed to update price'),
        preserveScroll: true,
    });
    bulkProcessing.value = false;
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

                <!-- Floating action bar -->
                <Transition name="slide">
                    <div v-if="selectedIds.length > 0" class="mb-4 flex items-center justify-between rounded-xl bg-emerald-600 px-6 py-3 shadow-lg">
                        <span class="text-sm font-medium text-white">{{ selectedIds.length }} item{{ selectedIds.length !== 1 ? 's' : '' }} selected</span>
                        <div class="flex items-center gap-2">
                            <button @click="showBulkStock = true" class="inline-flex items-center gap-1 rounded-lg bg-white/20 px-3 py-1.5 text-sm font-medium text-white hover:bg-white/30">
                                <AdjustmentsHorizontalIcon class="h-4 w-4" />
                                Adjust Stock
                            </button>
                            <button @click="showBulkPrice = true" class="inline-flex items-center gap-1 rounded-lg bg-white/20 px-3 py-1.5 text-sm font-medium text-white hover:bg-white/30">
                                <CurrencyDollarIcon class="h-4 w-4" />
                                Change Price
                            </button>
                            <button @click="executeBulkDelete" class="inline-flex items-center gap-1 rounded-lg bg-red-500 px-3 py-1.5 text-sm font-medium text-white hover:bg-red-600">
                                <TrashIcon class="h-4 w-4" />
                                Delete
                            </button>
                            <button @click="selectedIds = []" class="rounded-lg p-1.5 text-white/70 hover:bg-white/20 hover:text-white">
                                <XMarkIcon class="h-5 w-5" />
                            </button>
                        </div>
                    </div>
                </Transition>

                <!-- Bulk stock modal -->
                <Teleport to="body">
                    <Transition name="modal">
                        <div v-if="showBulkStock" class="fixed inset-0 z-[200] flex items-center justify-center bg-black/40 p-4">
                            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-2xl">
                                <h3 class="text-lg font-semibold text-gray-900">Adjust Stock for {{ selectedIds.length }} Items</h3>
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Quantity Change</label>
                                        <input v-model.number="bulkStockChange" type="number" step="0.01" class="mt-1 w-full rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500" />
                                        <p class="mt-1 text-xs text-gray-500">Use positive values to add stock, negative to remove</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Reason</label>
                                        <input v-model="bulkReason" type="text" class="mt-1 w-full rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500" />
                                    </div>
                                </div>
                                <div class="mt-6 flex justify-end gap-3">
                                    <button @click="showBulkStock = false" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                                    <button @click="executeBulkStock" :disabled="bulkProcessing" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50">Apply</button>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </Teleport>

                <!-- Bulk price modal -->
                <Teleport to="body">
                    <Transition name="modal">
                        <div v-if="showBulkPrice" class="fixed inset-0 z-[200] flex items-center justify-center bg-black/40 p-4">
                            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-2xl">
                                <h3 class="text-lg font-semibold text-gray-900">Update Price for {{ selectedIds.length }} Items</h3>
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700">New Unit Price</label>
                                    <input v-model.number="bulkPriceValue" type="number" step="0.01" min="0" class="mt-1 w-full rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500" />
                                </div>
                                <div class="mt-6 flex justify-end gap-3">
                                    <button @click="showBulkPrice = false" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                                    <button @click="executeBulkPrice" :disabled="bulkProcessing" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50">Update</button>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </Teleport>

                <LoadingSkeleton v-if="!items.data?.length" type="table" />
                <template v-else>
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left">
                                            <input type="checkbox" :checked="allSelected" @change="toggleAll" class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" />
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Image</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Item</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Category</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Expiry</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Stock</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Unit Price</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr v-for="item in items.data" :key="item.id" class="hover:bg-gray-50" :class="{ 'bg-emerald-50': selectedIds.includes(item.id) }">
                                        <td class="px-4 py-4">
                                            <input type="checkbox" :checked="selectedIds.includes(item.id)" @change="toggleItem(item.id)" class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" />
                                        </td>
                                        <td class="px-6 py-4">
                                            <img v-if="item.image_url" :src="'/storage/' + item.image_url" class="h-10 w-10 rounded-lg object-cover border border-gray-200" />
                                            <div v-else class="h-10 w-10 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center">
                                                <span class="text-xs text-gray-400">-</span>
                                            </div>
                                        </td>
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
                                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">No items found</td>
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

<style scoped>
.slide-enter-active, .slide-leave-active { transition: all 0.2s ease-out; }
.slide-enter-from, .slide-leave-to { opacity: 0; transform: translateY(-10px); }
.modal-enter-active { transition: all 0.2s ease-out; }
.modal-leave-active { transition: all 0.15s ease-in; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>
