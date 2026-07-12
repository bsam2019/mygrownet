<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/GrowMartAdminLayout.vue';
import { BuildingStorefrontIcon, PlusIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline';

interface Warehouse {
    id: number;
    name: string;
    province: string;
    city: string;
    address: string | null;
    is_active: boolean;
    inventory_count: number;
}

interface Props {
    warehouses: {
        data: Warehouse[];
        meta: any;
    };
}

const props = defineProps<Props>();

const deleteWarehouse = (warehouse: Warehouse) => {
    if (!confirm(`Delete warehouse "${warehouse.name}"?`)) return;
    router.delete(route('admin.growmart.warehouses.destroy', warehouse.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="GrowMart Warehouses - Admin" />

    <AdminLayout title="GrowMart Warehouses">
        <div class="flex justify-between items-center mb-6">
            <p class="text-gray-600">Manage storage warehouses</p>
            <Link :href="route('admin.growmart.warehouses.create')" class="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                <PlusIcon class="h-5 w-5" />
                Add Warehouse
            </Link>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="warehouse in warehouses.data" :key="warehouse.id" class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div :class="warehouse.is_active ? 'bg-emerald-100' : 'bg-gray-100'" class="p-2 rounded-lg">
                            <BuildingStorefrontIcon :class="warehouse.is_active ? 'text-emerald-600' : 'text-gray-400'" class="h-6 w-6" />
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ warehouse.name }}</h3>
                            <p class="text-sm text-gray-600">{{ warehouse.city }}, {{ warehouse.province }}</p>
                        </div>
                    </div>
                </div>
                <div v-if="warehouse.address" class="text-sm text-gray-500 mb-3">{{ warehouse.address }}</div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">{{ warehouse.inventory_count }} products</span>
                    <span :class="warehouse.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'" class="px-2.5 py-0.5 rounded-full text-xs font-medium">
                        {{ warehouse.is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-100 flex justify-end gap-2">
                    <Link :href="route('admin.growmart.warehouses.edit', warehouse.id)" class="text-emerald-600 hover:text-emerald-700 text-sm font-medium">Edit</Link>
                    <button @click="deleteWarehouse(warehouse)" class="text-red-600 hover:text-red-700 text-sm font-medium">Delete</button>
                </div>
            </div>
        </div>

        <div v-if="warehouses.data.length === 0" class="text-center py-12">
            <BuildingStorefrontIcon class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900">No warehouses</h3>
            <p class="mt-1 text-sm text-gray-500">Add a warehouse to start tracking inventory</p>
            <Link :href="route('admin.growmart.warehouses.create')" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
                <PlusIcon class="h-5 w-5" />
                Add Warehouse
            </Link>
        </div>
    </AdminLayout>
</template>
