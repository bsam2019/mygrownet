<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import GrowBizLayout from '@/layouts/GrowBizLayout.vue';
import {
    PlusIcon,
    MagnifyingGlassIcon,
    FunnelIcon,
    CubeIcon,
    ExclamationTriangleIcon,
    ArrowTrendingDownIcon,
    CurrencyDollarIcon,
    PencilIcon,
    TrashIcon,
    AdjustmentsHorizontalIcon,
} from '@heroicons/vue/24/outline';

interface InventoryItem {
    id: number;
    name: string;
    sku: string | null;
    description: string | null;
    category_id: number | null;
    unit: string;
    cost_price: number;
    selling_price: number;
    current_stock: number;
    low_stock_threshold: number;
    location: string | null;
    is_low_stock: boolean;
    is_out_of_stock: boolean;
    stock_value: number;
    profit_margin: number;
}

interface Category {
    id: number;
    name: string;
    color: string;
    items_count: number;
}

interface Props {
    items: InventoryItem[];
    stats: {
        total_items: number;
        total_stock: number;
        total_value: number;
        potential_revenue: number;
        low_stock_count: number;
        out_of_stock_count: number;
    };
    categories: Category[];
    filters: {
        category_id: number | null;
        search: string | null;
        stock_status: string | null;
    };
    movementTypes: { value: string; label: string; direction: string }[];
}

const props = defineProps<Props>();

defineOptions({ layout: GrowBizLayout });

const showAddModal = ref(false);
const showFilters = ref(false);
const showAdjustModal = ref(false);
const selectedItem = ref<InventoryItem | null>(null);
const searchQuery = ref(props.filters.search || '');

// Form data
const newItem = ref({
    name: '',
    sku: '',
    description: '',
    category_id: null as number | null,
    unit: 'piece',
    cost_price: 0,
    selling_price: 0,
    initial_stock: 0,
    low_stock_threshold: 10,
    location: '',
    barcode: '',
});

const adjustmentForm = ref({
    type: 'purchase',
    quantity: 1,
    unit_cost: 0,
    notes: '',
});

const resetForm = () => {
    newItem.value = {
        name: '',
        sku: '',
        description: '',
        category_id: null,
        unit: 'piece',
        cost_price: 0,
        selling_price: 0,
        initial_stock: 0,
        low_stock_threshold: 10,
        location: '',
        barcode: '',
    };
    selectedItem.value = null;
};

const createItem = () => {
    if (!newItem.value.name.trim()) return;
    
    router.post(route('growbiz.inventory.store'), newItem.value, {
        preserveScroll: true,
        onSuccess: () => {
            showAddModal.value = false;
            resetForm();
        },
    });
};

const editItem = (item: InventoryItem) => {
    selectedItem.value = item;
    newItem.value = {
        name: item.name,
        sku: item.sku || '',
        description: item.description || '',
        category_id: item.category_id,
        unit: item.unit,
        cost_price: item.cost_price,
        selling_price: item.selling_price,
        initial_stock: item.current_stock,
        low_stock_threshold: item.low_stock_threshold,
        location: item.location || '',
        barcode: '',
    };
    showAddModal.value = true;
};

const updateItem = () => {
    if (!selectedItem.value || !newItem.value.name.trim()) return;
    
    router.put(route('growbiz.inventory.update', selectedItem.value.id), newItem.value, {
        preserveScroll: true,
        onSuccess: () => {
            showAddModal.value = false;
            resetForm();
        },
    });
};

const deleteItem = (item: InventoryItem) => {
    if (!confirm(`Delete "${item.name}"?`)) return;
    router.delete(route('growbiz.inventory.destroy', item.id), { preserveScroll: true });
};

const openAdjustModal = (item: InventoryItem) => {
    selectedItem.value = item;
    adjustmentForm.value = {
        type: 'purchase',
        quantity: 1,
        unit_cost: item.cost_price,
        notes: '',
    };
    showAdjustModal.value = true;
};

const adjustStock = async () => {
    if (!selectedItem.value) return;
    
    try {
        await fetch(route('growbiz.inventory.adjust', selectedItem.value.id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify(adjustmentForm.value),
        });
        
        showAdjustModal.value = false;
        router.reload({ only: ['items', 'stats'] });
    } catch (e) {
        console.error('Failed to adjust stock', e);
    }
};

const applyFilter = (key: string, value: any) => {
    router.get(route('growbiz.inventory.index'), {
        ...props.filters,
        [key]: value,
    }, { preserveState: true, preserveScroll: true });
};

const search = () => {
    applyFilter('search', searchQuery.value || null);
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', { style: 'currency', currency: 'ZMW' }).format(amount);
};

const getCategoryName = (categoryId: number | null) => {
    if (!categoryId) return 'Uncategorized';
    const cat = props.categories.find(c => c.id === categoryId);
    return cat?.name || 'Uncategorized';
};
</script>

<template>
    <div class="p-4 space-y-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Inventory</h1>
                <p class="text-sm text-gray-500">{{ stats.total_items }} items in stock</p>
            </div>
            <button
                @click="showAddModal = true; resetForm()"
                class="flex items-center gap-2 px-4 py-2.5 bg-emerald-600 text-white rounded-xl font-medium shadow-lg shadow-emerald-200 active:scale-95 transition-transform"
            >
                <PlusIcon class="h-5 w-5" aria-hidden="true" />
                <span>Add Item</span>
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-white rounded-xl p-4">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-emerald-100 rounded-lg">
                        <CurrencyDollarIcon class="h-5 w-5 text-emerald-600" aria-hidden="true" />
                    </div>
                    <div>
                        <p class="text-lg font-bold text-gray-900">{{ formatCurrency(stats.total_value) }}</p>
                        <p class="text-xs text-gray-500">Stock Value</p>
                    </div>
                </div>
            </div>
            <Link
                :href="route('growbiz.inventory.low-stock')"
                class="bg-white rounded-xl p-4 hover:bg-gray-50 transition-colors"
            >
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-amber-100 rounded-lg">
                        <ExclamationTriangleIcon class="h-5 w-5 text-amber-600" aria-hidden="true" />
                    </div>
                    <div>
                        <p class="text-lg font-bold text-gray-900">{{ stats.low_stock_count }}</p>
                        <p class="text-xs text-gray-500">Low Stock</p>
                    </div>
                </div>
            </Link>
        </div>

        <!-- Search & Filter -->
        <div class="flex gap-2">
            <div class="flex-1 relative">
                <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
                <input
                    v-model="searchQuery"
                    @keyup.enter="search"
                    type="text"
                    placeholder="Search items..."
                    class="w-full pl-10 pr-4 py-2.5 bg-white border-0 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500"
                />
            </div>
            <button
                @click="showFilters = !showFilters"
                :class="[
                    'p-2.5 rounded-xl transition-colors',
                    showFilters ? 'bg-emerald-100 text-emerald-600' : 'bg-white text-gray-600'
                ]"
            >
                <FunnelIcon class="h-5 w-5" aria-hidden="true" />
            </button>
        </div>

        <!-- Filters -->
        <div v-if="showFilters" class="bg-white rounded-xl p-4 space-y-3">
            <div>
                <label class="text-xs font-medium text-gray-500 uppercase">Category</label>
                <div class="flex flex-wrap gap-2 mt-1">
                    <button
                        @click="applyFilter('category_id', null)"
                        :class="[
                            'px-3 py-1.5 rounded-lg text-sm font-medium transition-colors',
                            !props.filters.category_id ? 'bg-emerald-100 text-emerald-700 ring-2 ring-emerald-500' : 'bg-gray-100 text-gray-600'
                        ]"
                    >
                        All
                    </button>
                    <button
                        v-for="cat in categories"
                        :key="cat.id"
                        @click="applyFilter('category_id', props.filters.category_id === cat.id ? null : cat.id)"
                        :class="[
                            'px-3 py-1.5 rounded-lg text-sm font-medium transition-colors',
                            props.filters.category_id === cat.id ? 'bg-emerald-100 text-emerald-700 ring-2 ring-emerald-500' : 'bg-gray-100 text-gray-600'
                        ]"
                    >
                        {{ cat.name }} ({{ cat.items_count }})
                    </button>
                </div>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500 uppercase">Stock Status</label>
                <div class="flex gap-2 mt-1">
                    <button
                        @click="applyFilter('stock_status', props.filters.stock_status === 'low' ? null : 'low')"
                        :class="[
                            'px-3 py-1.5 rounded-lg text-sm font-medium transition-colors',
                            props.filters.stock_status === 'low' ? 'bg-amber-100 text-amber-700 ring-2 ring-amber-500' : 'bg-gray-100 text-gray-600'
                        ]"
                    >
                        Low Stock
                    </button>
                    <button
                        @click="applyFilter('stock_status', props.filters.stock_status === 'out' ? null : 'out')"
                        :class="[
                            'px-3 py-1.5 rounded-lg text-sm font-medium transition-colors',
                            props.filters.stock_status === 'out' ? 'bg-red-100 text-red-700 ring-2 ring-red-500' : 'bg-gray-100 text-gray-600'
                        ]"
                    >
                        Out of Stock
                    </button>
                </div>
            </div>
        </div>

        <!-- Items List -->
        <div class="space-y-2">
            <div v-if="items.length === 0" class="text-center py-12 bg-white rounded-xl">
                <CubeIcon class="h-12 w-12 mx-auto text-gray-300" aria-hidden="true" />
                <p class="mt-2 text-gray-500">No inventory items</p>
                <button
                    @click="showAddModal = true; resetForm()"
                    class="mt-4 px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium"
                >
                    Add your first item
                </button>
            </div>

            <Link
                v-for="item in items"
                :key="item.id"
                :href="route('growbiz.inventory.show', item.id)"
                :class="[
                    'block bg-white rounded-xl p-4 transition-all hover:shadow-md',
                    item.is_out_of_stock ? 'ring-2 ring-red-200' : item.is_low_stock ? 'ring-2 ring-amber-200' : ''
                ]"
            >
                <div class="flex items-start gap-3">
                    <div :class="[
                        'flex-shrink-0 w-12 h-12 rounded-lg flex items-center justify-center',
                        item.is_out_of_stock ? 'bg-red-100' : item.is_low_stock ? 'bg-amber-100' : 'bg-emerald-100'
                    ]">
                        <CubeIcon :class="[
                            'h-6 w-6',
                            item.is_out_of_stock ? 'text-red-600' : item.is_low_stock ? 'text-amber-600' : 'text-emerald-600'
                        ]" aria-hidden="true" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="font-medium text-gray-900">{{ item.name }}</p>
                                <p v-if="item.sku" class="text-xs text-gray-500">SKU: {{ item.sku }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-gray-900">{{ item.current_stock }}</p>
                                <p class="text-xs text-gray-500">{{ item.unit }}s</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="text-sm text-gray-600">{{ formatCurrency(item.selling_price) }}</span>
                            <span v-if="item.is_out_of_stock" class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                Out of Stock
                            </span>
                            <span v-else-if="item.is_low_stock" class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                Low Stock
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                {{ getCategoryName(item.category_id) }}
                            </span>
                        </div>
                    </div>
                </div>
            </Link>
        </div>
    </div>

    <!-- Add/Edit Item Modal -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            leave-active-class="transition-opacity duration-150"
            leave-from-class="opacity-100"
        >
            <div v-if="showAddModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm" @click="showAddModal = false" />
        </Transition>

        <Transition
            enter-active-class="transition-transform duration-300 ease-out"
            enter-from-class="translate-y-full"
            leave-active-class="transition-transform duration-200 ease-in"
            leave-from-class="translate-y-0"
        >
            <div
                v-if="showAddModal"
                class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-md z-50 bg-white rounded-t-3xl shadow-2xl max-h-[90vh] overflow-y-auto"
            >
                <div class="flex justify-center pt-3 pb-2">
                    <div class="w-10 h-1 bg-gray-300 rounded-full"></div>
                </div>

                <div class="px-6 pb-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">
                        {{ selectedItem ? 'Edit Item' : 'New Item' }}
                    </h2>

                    <form @submit.prevent="selectedItem ? updateItem() : createItem()" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                            <input v-model="newItem.name" type="text" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500" />
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                                <input v-model="newItem.sku" type="text" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                                <select v-model="newItem.unit" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                                    <option value="piece">Piece</option>
                                    <option value="kg">Kilogram</option>
                                    <option value="liter">Liter</option>
                                    <option value="box">Box</option>
                                    <option value="pack">Pack</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cost Price *</label>
                                <input v-model.number="newItem.cost_price" type="number" step="0.01" min="0" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Selling Price *</label>
                                <input v-model.number="newItem.selling_price" type="number" step="0.01" min="0" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Initial Stock</label>
                                <input v-model.number="newItem.initial_stock" type="number" min="0" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Low Stock Alert</label>
                                <input v-model.number="newItem.low_stock_threshold" type="number" min="0" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select v-model="newItem.category_id" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                                <option :value="null">No Category</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                            <input v-model="newItem.location" type="text" placeholder="e.g., Shelf A, Warehouse 1" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500" />
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="button" @click="showAddModal = false; resetForm()" class="flex-1 py-3 bg-gray-100 text-gray-700 rounded-xl font-medium">
                                Cancel
                            </button>
                            <button type="submit" class="flex-1 py-3 bg-emerald-600 text-white rounded-xl font-medium shadow-lg shadow-emerald-200">
                                {{ selectedItem ? 'Update' : 'Create' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </Teleport>

    <!-- Stock Adjustment Modal -->
    <Teleport to="body">
        <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0" leave-active-class="transition-opacity duration-150">
            <div v-if="showAdjustModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm" @click="showAdjustModal = false" />
        </Transition>

        <Transition enter-active-class="transition-transform duration-300 ease-out" enter-from-class="translate-y-full" leave-active-class="transition-transform duration-200 ease-in">
            <div v-if="showAdjustModal" class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-md z-50 bg-white rounded-t-3xl shadow-2xl">
                <div class="flex justify-center pt-3 pb-2">
                    <div class="w-10 h-1 bg-gray-300 rounded-full"></div>
                </div>

                <div class="px-6 pb-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Adjust Stock</h2>
                    <p class="text-sm text-gray-500 mb-4">{{ selectedItem?.name }} - Current: {{ selectedItem?.current_stock }}</p>

                    <form @submit.prevent="adjustStock" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                            <select v-model="adjustmentForm.type" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                                <option v-for="t in movementTypes.filter(m => m.value !== 'initial')" :key="t.value" :value="t.value">
                                    {{ t.label }} ({{ t.direction === 'in' ? '+' : t.direction === 'out' ? '-' : '±' }})
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                            <input v-model.number="adjustmentForm.quantity" type="number" min="1" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea v-model="adjustmentForm.notes" rows="2" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 resize-none"></textarea>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="button" @click="showAdjustModal = false" class="flex-1 py-3 bg-gray-100 text-gray-700 rounded-xl font-medium">Cancel</button>
                            <button type="submit" class="flex-1 py-3 bg-emerald-600 text-white rounded-xl font-medium">Adjust</button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
