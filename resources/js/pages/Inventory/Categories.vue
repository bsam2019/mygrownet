<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import InventoryLayout from '@/layouts/InventoryLayout.vue';
import { ref } from 'vue';
import { PlusIcon, PencilIcon, TrashIcon, XMarkIcon } from '@heroicons/vue/24/outline';

interface Category { id: number; name: string; description: string | null; color: string; }
const props = defineProps<{ categories: Category[] }>();

const showModal = ref(false);
const editingCategory = ref<Category | null>(null);
const form = useForm({ name: '', description: '', color: '#6b7280' });

const openCreate = () => { editingCategory.value = null; form.reset(); showModal.value = true; };
const openEdit = (cat: Category) => { editingCategory.value = cat; form.name = cat.name; form.description = cat.description || ''; form.color = cat.color; showModal.value = true; };
const closeModal = () => { showModal.value = false; form.reset(); };

const submit = () => {
    if (editingCategory.value) {
        form.put(route('inventory.categories.update', editingCategory.value.id), { onSuccess: closeModal });
    } else {
        form.post(route('inventory.categories.store'), { onSuccess: closeModal });
    }
};
const deleteCategory = (cat: Category) => { if (confirm(`Delete "${cat.name}"?`)) router.delete(route('inventory.categories.destroy', cat.id)); };
</script>

<template>
    <InventoryLayout title="Categories">
        <Head title="Inventory Categories" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-gray-900">Categories</h1>
                    <button @click="openCreate" class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">
                        <PlusIcon class="h-5 w-5" aria-hidden="true" /> Add Category
                    </button>
                </div>
                <div class="space-y-3">
                    <div v-for="cat in categories" :key="cat.id" class="flex items-center justify-between rounded-xl bg-white p-4 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div :style="{ backgroundColor: cat.color }" class="h-4 w-4 rounded-full"></div>
                            <div>
                                <p class="font-medium text-gray-900">{{ cat.name }}</p>
                                <p v-if="cat.description" class="text-sm text-gray-500">{{ cat.description }}</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button @click="openEdit(cat)" class="rounded p-1 text-gray-400 hover:bg-gray-100"><PencilIcon class="h-5 w-5" /></button>
                            <button @click="deleteCategory(cat)" class="rounded p-1 text-gray-400 hover:bg-red-100 hover:text-red-600"><TrashIcon class="h-5 w-5" /></button>
                        </div>
                    </div>
                    <div v-if="categories.length === 0" class="rounded-xl bg-white p-12 text-center text-gray-500 shadow-sm">No categories yet</div>
                </div>
            </div>
        </div>
        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md rounded-xl bg-white p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold">{{ editingCategory ? 'Edit' : 'Add' }} Category</h2>
                    <button @click="closeModal" class="rounded p-1 hover:bg-gray-100"><XMarkIcon class="h-5 w-5" /></button>
                </div>
                <form @submit.prevent="submit" class="space-y-4">
                    <div><label class="block text-sm font-medium text-gray-700">Name</label><input v-model="form.name" type="text" required class="mt-1 w-full rounded-lg border-gray-300" /></div>
                    <div><label class="block text-sm font-medium text-gray-700">Description</label><input v-model="form.description" type="text" class="mt-1 w-full rounded-lg border-gray-300" /></div>
                    <div><label class="block text-sm font-medium text-gray-700">Color</label><input v-model="form.color" type="color" class="mt-1 h-10 w-20 rounded border-gray-300" /></div>
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="closeModal" class="rounded-lg border px-4 py-2 text-sm">Cancel</button>
                        <button type="submit" :disabled="form.processing" class="rounded-lg bg-teal-600 px-4 py-2 text-sm text-white hover:bg-teal-700">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </InventoryLayout>
</template>
