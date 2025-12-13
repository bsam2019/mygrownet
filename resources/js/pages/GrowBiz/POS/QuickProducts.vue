<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import GrowBizLayout from '@/layouts/GrowBizLayout.vue';
import {
    PlusIcon,
    TrashIcon,
    PencilIcon,
    Squares2X2Icon,
    XCircleIcon,
    LinkIcon,
} from '@heroicons/vue/24/outline';

interface QuickProduct {
    id: number;
    name: string;
    price: number;
    color: string;
    inventory_item_id: number | null;
    sort_order: number;
}

interface InventoryItem {
    id: number;
    name: string;
    selling_price: number;
    current_stock: number;
}

interface Props {
    quickProducts: QuickProduct[];
    inventoryItems: InventoryItem[];
}

const props = defineProps<Props>();

const showModal = ref(false);
const editingProduct = ref<QuickProduct | null>(null);

const form = ref({
    name: '',
    price: 0,
    color: '#3b82f6',
    inventory_item_id: null as number | null,
});

const colors = [
    '#3b82f6', // blue
    '#10b981', // green
    '#f59e0b', // amber
    '#ef4444', // red
    '#8b5cf6', // purple
    '#ec4899', // pink
    '#06b6d4', // cyan
    '#84cc16', // lime
    '#f97316', // orange
    '#6366f1', // indigo
];

const openAddModal = () => {
    editingProduct.value = null;
    form.value = {
        name: '',
        price: 0,
        color: '#3b82f6',
        inventory_item_id: null,
    };
    showModal.value = true;
};

const openEditModal = (product: QuickProduct) => {
    editingProduct.value = product;
    form.value = {
        name: product.name,
        price: product.price,
        color: product.color || '#3b82f6',
        inventory_item_id: product.inventory_item_id,
    };
    showModal.value = true;
};

const linkInventoryItem = (item: InventoryItem) => {
    form.value.name = item.name;
    form.value.price = item.selling_price;
    form.value.inventory_item_id = item.id;
};

const saveProduct = () => {
    router.post(route('growbiz.pos.quick-products.store'), form.value, {
        onSuccess: () => {
            showModal.value = false;
        },
    });
};

const deleteProduct = (product: QuickProduct) => {
    if (confirm(`Remove "${product.name}" from quick products?`)) {
        // Would need a delete endpoint - for now just show confirmation
        alert('Delete functionality would be implemented here');
    }
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', { style: 'currency', currency: 'ZMW' }).format(amount);
};
</script>

<template>
    <GrowBizLayout>
        <Head title="Quick Products - POS" />

        <div class="max-w-4xl mx-auto px-4 sm:px-6 py-4 sm:py-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Quick Products</h1>
                    <p class="text-sm text-gray-500 mt-1">Manage quick-access product buttons for POS</p>
                </div>
                <button
                    @click="openAddModal"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                >
                    <PlusIcon class="w-5 h-5" aria-hidden="true" />
                    <span>Add Product</span>
                </button>
            </div>

            <!-- Quick Products Grid -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
                <div v-if="quickProducts.length === 0" class="text-center py-12">
                    <Squares2X2Icon class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                    <p class="text-gray-500 mb-4">No quick products yet</p>
                    <button
                        @click="openAddModal"
                        class="text-blue-600 hover:text-blue-700 font-medium"
                    >
                        Add your first quick product
                    </button>
                </div>

                <div v-else class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
                    <div
                        v-for="product in quickProducts"
                        :key="product.id"
                        class="relative group"
                    >
                        <button
                            :style="{ backgroundColor: product.color }"
                            class="w-full aspect-square rounded-xl text-white font-medium text-sm p-2 flex flex-col items-center justify-center hover:opacity-90 transition-opacity"
                        >
                            <span class="text-center line-clamp-2">{{ product.name }}</span>
                            <span class="text-xs opacity-80 mt-1">{{ formatCurrency(product.price) }}</span>
                        </button>
                        <div class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity flex gap-1">
                            <button
                                @click="openEditModal(product)"
                                class="p-1 bg-white/90 rounded-full hover:bg-white"
                                aria-label="Edit product"
                            >
                                <PencilIcon class="w-3 h-3 text-gray-600" />
                            </button>
                            <button
                                @click="deleteProduct(product)"
                                class="p-1 bg-white/90 rounded-full hover:bg-white"
                                aria-label="Delete product"
                            >
                                <TrashIcon class="w-3 h-3 text-red-600" />
                            </button>
                        </div>
                        <div v-if="product.inventory_item_id" class="absolute bottom-1 left-1">
                            <LinkIcon class="w-3 h-3 text-white/70" />
                        </div>
                    </div>

                    <!-- Add Button -->
                    <button
                        @click="openAddModal"
                        class="aspect-square rounded-xl border-2 border-dashed border-gray-200 flex flex-col items-center justify-center text-gray-400 hover:border-blue-400 hover:text-blue-500 transition-colors"
                    >
                        <PlusIcon class="w-6 h-6" />
                        <span class="text-xs mt-1">Add</span>
                    </button>
                </div>
            </div>

            <!-- Tips -->
            <div class="bg-blue-50 rounded-xl p-4">
                <h3 class="font-medium text-blue-900 mb-2">Tips</h3>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li>• Quick products appear as buttons on the POS terminal for fast checkout</li>
                    <li>• Link to inventory items to automatically track stock</li>
                    <li>• Use different colors to organize by category</li>
                    <li>• Drag to reorder products (coming soon)</li>
                </ul>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-end sm:items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/50" @click="showModal = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md">
                    <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-lg font-semibold">
                            {{ editingProduct ? 'Edit Quick Product' : 'Add Quick Product' }}
                        </h3>
                        <button @click="showModal = false" class="p-1 hover:bg-gray-100 rounded-full">
                            <XCircleIcon class="w-6 h-6 text-gray-400" />
                        </button>
                    </div>
                    <div class="p-4 space-y-4">
                        <!-- Link from Inventory -->
                        <div v-if="inventoryItems.length > 0 && !editingProduct">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Link from Inventory</label>
                            <select
                                @change="(e) => {
                                    const item = inventoryItems.find(i => i.id === Number((e.target as HTMLSelectElement).value));
                                    if (item) linkInventoryItem(item);
                                }"
                                class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="">Select inventory item...</option>
                                <option v-for="item in inventoryItems" :key="item.id" :value="item.id">
                                    {{ item.name }} - {{ formatCurrency(item.selling_price) }}
                                </option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Or enter details manually below</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="e.g., Coffee"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Price (ZMW)</label>
                            <input
                                v-model.number="form.price"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="0.00"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Button Color</label>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="color in colors"
                                    :key="color"
                                    @click="form.color = color"
                                    :style="{ backgroundColor: color }"
                                    :class="[
                                        'w-8 h-8 rounded-lg transition-transform',
                                        form.color === color ? 'ring-2 ring-offset-2 ring-gray-400 scale-110' : ''
                                    ]"
                                    :aria-label="`Select color ${color}`"
                                />
                            </div>
                        </div>

                        <!-- Preview -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Preview</label>
                            <div class="flex justify-center">
                                <button
                                    :style="{ backgroundColor: form.color }"
                                    class="w-24 h-24 rounded-xl text-white font-medium text-sm p-2 flex flex-col items-center justify-center"
                                >
                                    <span class="text-center line-clamp-2">{{ form.name || 'Product' }}</span>
                                    <span class="text-xs opacity-80 mt-1">{{ formatCurrency(form.price) }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 border-t border-gray-100 flex gap-2">
                        <button
                            @click="showModal = false"
                            class="flex-1 px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button
                            @click="saveProduct"
                            :disabled="!form.name || form.price <= 0"
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                        >
                            {{ editingProduct ? 'Update' : 'Add Product' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </GrowBizLayout>
</template>
