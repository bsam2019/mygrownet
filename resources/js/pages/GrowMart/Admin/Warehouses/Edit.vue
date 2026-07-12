<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/GrowMartAdminLayout.vue';

interface Warehouse {
    id: number;
    name: string;
    province: string;
    city: string;
    address: string | null;
    is_active: boolean;
}

interface Props {
    warehouse: Warehouse;
}

const props = defineProps<Props>();

const form = useForm({
    name: props.warehouse.name,
    province: props.warehouse.province,
    city: props.warehouse.city,
    address: props.warehouse.address || '',
    is_active: props.warehouse.is_active,
});

const provinces = [
    'Central', 'Copperbelt', 'Eastern', 'Luapula', 'Lusaka',
    'Muchinga', 'Northern', 'North-Western', 'Southern', 'Western',
];

const submit = () => {
    form.put(route('admin.growmart.warehouses.update', props.warehouse.id));
};
</script>

<template>
    <Head :title="'Edit ' + warehouse.name + ' - GrowMart Admin'" />

    <AdminLayout :title="'Edit: ' + warehouse.name">
        <div class="mb-6">
            <Link :href="route('admin.growmart.warehouses.index')" class="text-sm text-emerald-600 hover:text-emerald-700">← Back to Warehouses</Link>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 max-w-2xl">
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                    <input v-model="form.name" type="text" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Province *</label>
                        <select v-model="form.province" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            <option v-for="p in provinces" :key="p" :value="p">{{ p }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">City *</label>
                        <input v-model="form.city" type="text" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <input v-model="form.address" type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Active</label>
                    <select v-model="form.is_active" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <option :value="true">Yes</option>
                        <option :value="false">No</option>
                    </select>
                </div>

                <div class="pt-4 flex gap-3">
                    <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 disabled:opacity-50 font-medium">
                        {{ form.processing ? 'Updating...' : 'Update Warehouse' }}
                    </button>
                    <Link :href="route('admin.growmart.warehouses.index')" class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">
                        Cancel
                    </Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
