<template>
    <AdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Investment Categories</h2>
                <button @click="showCreateModal = true" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                    Add Category
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Min Investment</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Interest Rate</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="category in categories" :key="category.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ category.name }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ category.description }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatCurrency(category.min_investment) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ category.interest_rate }}%
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="category.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                              class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                            {{ category.active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button @click="editCategory(category)" class="text-blue-600 hover:text-blue-900 mr-3">
                                            Edit
                                        </button>
                                        <button @click="toggleCategoryStatus(category)"
                                                :class="category.active ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900'">
                                            {{ category.active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Modal :show="showCreateModal" @close="closeModal">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    {{ form.id ? 'Edit Category' : 'Create New Category' }}
                </h3>
                <form @submit.prevent="submitForm">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <input type="text" v-model="form.name" class="mt-1 block w-full rounded-md border-gray-300" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea v-model="form.description" rows="3" class="mt-1 block w-full rounded-md border-gray-300"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Minimum Investment (ZMW)</label>
                            <input type="number" v-model="form.min_investment" class="mt-1 block w-full rounded-md border-gray-300" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Interest Rate (%)</label>
                            <input type="number" v-model="form.interest_rate" step="0.01" class="mt-1 block w-full rounded-md border-gray-300" required>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" @click="closeModal" class="bg-white px-4 py-2 border rounded-md hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                            {{ form.id ? 'Update' : 'Create' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Modal from '@/components/Modal.vue';

const props = defineProps({
    categories: Array
});

const showCreateModal = ref(false);
const form = ref({
    id: null,
    name: '',
    description: '',
    min_investment: 0,
    interest_rate: 0
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW'
    }).format(value);
};

const editCategory = (category) => {
    form.value = { ...category };
    showCreateModal.value = true;
};

const closeModal = () => {
    form.value = {
        id: null,
        name: '',
        description: '',
        min_investment: 0,
        interest_rate: 0
    };
    showCreateModal.value = false;
};

const submitForm = () => {
    if (form.value.id) {
        router.put(route('admin.investment-categories.update', form.value.id), form.value);
    } else {
        router.post(route('admin.investment-categories.store'), form.value);
    }
    closeModal();
};

const toggleCategoryStatus = (category) => {
    router.patch(route('admin.investment-categories.toggle-status', category.id));
};
</script>
