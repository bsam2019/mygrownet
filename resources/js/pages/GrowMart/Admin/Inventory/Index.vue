<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/GrowMartAdminLayout.vue';
import { CubeIcon, FunnelIcon } from '@heroicons/vue/24/outline';
import Pagination from '@/components/Pagination.vue';

interface InventoryItem {
    id: number; product_id: number; product_name: string; product_slug: string;
    category: string; warehouse_id: number; warehouse: string;
    quantity: number; low_stock_threshold: number;
    is_low: boolean; is_out: boolean;
}

interface Warehouse { id: number; name: string; }

interface Props {
    inventory: { data: InventoryItem[]; meta: any };
    warehouses: Warehouse[];
    filters: { warehouse_id?: string; q?: string; low_stock?: string };
}

const props = defineProps<Props>();

const editingId = ref<number | null>(null);
const editForm = useForm({ quantity: 0, low_stock_threshold: 10 });

const startEdit = (item: InventoryItem) => {
    editingId.value = item.id;
    editForm.quantity = item.quantity;
    editForm.low_stock_threshold = item.low_stock_threshold;
};

const saveEdit = (id: number) => {
    editForm.put(route('admin.growmart.inventory.update', id), {
        preserveScroll: true,
        onSuccess: () => editingId.value = null,
    });
};

const cancelEdit = () => { editingId.value = null; };

const applyFilter = (key: string, value: string) => {
    router.get(route('admin.growmart.inventory.index'), { ...props.filters, [key]: value || undefined }, { preserveState: true });
};

const clearFilters = () => {
    router.get(route('admin.growmart.inventory.index'), {}, { preserveState: true });
};

const hasFilters = props.filters?.warehouse_id || props.filters?.q || props.filters?.low_stock;
</script>

<template>
    <Head title="Inventory - GrowMart Admin" />

    <AdminLayout title="Inventory">
        <div class="bg-white rounded-lg border border-gray-200 p-4 mb-4">
            <div class="flex flex-wrap items-center gap-3">
                <input v-model="props.filters.q" @keydown.enter="applyFilter('q', ($event.target as HTMLInputElement).value)" placeholder="Search product..." class="px-3 py-2 border border-gray-300 rounded-lg text-sm min-w-[200px]" />
                <select :value="filters.warehouse_id || ''" @change="applyFilter('warehouse_id', ($event.target as HTMLSelectElement).value)" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">All Warehouses</option>
                    <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                </select>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" :checked="filters.low_stock === '1'" @change="applyFilter('low_stock', filters.low_stock ? '' : '1')" class="rounded" /> Low stock only</label>
                <button v-if="hasFilters" @click="clearFilters" class="text-sm text-red-600 hover:text-red-700 font-medium">Clear</button>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Warehouse</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Low Threshold</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="item in inventory.data" :key="item.id" :class="item.is_low ? 'bg-red-50' : 'hover:bg-gray-50'">
                            <td class="px-6 py-3 text-gray-900 font-medium">{{ item.product_name }}</td>
                            <td class="px-6 py-3 text-gray-600 text-sm">{{ item.category }}</td>
                            <td class="px-6 py-3 text-gray-600 text-sm">{{ item.warehouse }}</td>
                            <td class="px-6 py-3">
                                <div v-if="editingId === item.id" class="flex items-center gap-2 max-w-[120px]">
                                    <input v-model.number="editForm.quantity" type="number" min="0" class="w-20 px-2 py-1 border border-gray-300 rounded text-sm" />
                                </div>
                                <span v-else :class="item.is_out ? 'text-red-600 font-medium' : item.is_low ? 'text-orange-600 font-medium' : ''">{{ item.quantity }}</span>
                            </td>
                            <td class="px-6 py-3">
                                <div v-if="editingId === item.id" class="flex items-center gap-2 max-w-[100px]">
                                    <input v-model.number="editForm.low_stock_threshold" type="number" min="1" class="w-16 px-2 py-1 border border-gray-300 rounded text-sm" />
                                </div>
                                <span v-else class="text-sm text-gray-600">{{ item.low_stock_threshold }}</span>
                            </td>
                            <td class="px-6 py-3">
                                <span v-if="item.is_out" class="text-xs font-medium text-red-700 bg-red-100 px-2 py-0.5 rounded-full">Out of Stock</span>
                                <span v-else-if="item.is_low" class="text-xs font-medium text-orange-700 bg-orange-100 px-2 py-0.5 rounded-full">Low Stock</span>
                                <span v-else class="text-xs font-medium text-green-700 bg-green-100 px-2 py-0.5 rounded-full">In Stock</span>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <div v-if="editingId === item.id" class="flex items-center justify-end gap-1">
                                    <button @click="saveEdit(item.id)" :disabled="editForm.processing" class="text-xs bg-emerald-600 text-white px-2 py-1 rounded hover:bg-emerald-700 disabled:opacity-50">Save</button>
                                    <button @click="cancelEdit" class="text-xs bg-gray-200 text-gray-700 px-2 py-1 rounded hover:bg-gray-300">Cancel</button>
                                </div>
                                <button v-else @click="startEdit(item)" class="text-xs text-emerald-600 hover:text-emerald-700 font-medium">Edit</button>
                            </td>
                        </tr>
                        <tr v-if="inventory.data.length === 0">
                            <td colspan="7" class="px-6 py-12 text-center">
                                <CubeIcon class="mx-auto h-12 w-12 text-gray-400" />
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No inventory records</h3>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Pagination :links="inventory.meta.links" />
    </AdminLayout>
</template>
