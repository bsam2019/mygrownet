<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import StockAuditLayout from '@/layouts/StockAuditLayout.vue';
import LoadingSkeleton from '@/components/StockAudit/LoadingSkeleton.vue';
import Pagination from '@/components/StockAudit/Pagination.vue';
import { ref } from 'vue';
import { PlusIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline';
import { useNotifications } from '@/composables/useNotifications';
import { useConfirmDialog } from '@/composables/useConfirmDialog';

interface Department {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    sort_order: number;
}

interface Props {
    departments: {
        data: Department[];
        links: { url: string | null; label: string; active: boolean }[];
        meta: { current_page: number; last_page: number; total: number; from: number; to: number };
    };
}

defineProps<Props>();

const { success, error: notifyError } = useNotifications();
const confirm = useConfirmDialog();

const showCreateForm = ref(false);
const editingId = ref<number | null>(null);
const form = ref({ name: '', description: '', sort_order: 0 });
const errors = ref<Record<string, string>>({});

const resetForm = () => {
    form.value = { name: '', description: '', sort_order: 0 };
    editingId.value = null;
    showCreateForm.value = false;
};

const startEdit = (dept: Department) => {
    form.value = { name: dept.name, description: dept.description || '', sort_order: dept.sort_order };
    editingId.value = dept.id;
    showCreateForm.value = true;
};

const submit = () => {
    if (editingId.value) {
        router.put(route('stock-audit.departments.update', editingId.value), form.value, {
            onSuccess: () => { success('Department updated'); resetForm(); },
            onError: (err) => { errors.value = err; notifyError('Failed to update department'); },
        });
    } else {
        router.post(route('stock-audit.departments.store'), form.value, {
            onSuccess: () => { success('Department created'); resetForm(); },
            onError: (err) => { errors.value = err; notifyError('Failed to create department'); },
        });
    }
};

const deleteDept = async (id: number, name: string) => {
    const ok = await confirm.show(`Delete department "${name}"?`, 'Delete Department');
    if (ok) {
        router.delete(route('stock-audit.departments.destroy', id), {
            onSuccess: () => success('Department deleted'),
            onError: () => notifyError('Failed to delete department'),
        });
    }
};
</script>

<template>
    <StockAuditLayout title="Departments">
        <Head title="Departments - StockFlow" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <h1 class="text-2xl font-bold text-gray-900">Departments</h1>
                    <button @click="resetForm(); showCreateForm = true" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                        <PlusIcon class="h-5 w-5" aria-hidden="true" />
                        Add Department
                    </button>
                </div>

                <div v-if="showCreateForm" class="mb-6 rounded-xl bg-white p-6 shadow-sm border border-emerald-200">
                    <h2 class="text-lg font-semibold text-gray-900">{{ editingId ? 'Edit' : 'New' }} Department</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name *</label>
                            <input v-model="form.name" type="text" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                            <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Sort Order</label>
                            <input v-model.number="form.sort_order" type="number" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <input v-model="form.description" type="text" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                    </div>
                    <div class="mt-4 flex gap-3">
                        <button @click="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">{{ editingId ? 'Update' : 'Save' }}</button>
                        <button @click="resetForm" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    </div>
                </div>

                <LoadingSkeleton v-if="!departments.data?.length" type="table" />
                <template v-else>
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Slug</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Description</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Order</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="dept in departments.data" :key="dept.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ dept.name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ dept.slug }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ dept.description || '-' }}</td>
                                    <td class="px-6 py-4 text-right text-sm text-gray-700">{{ dept.sort_order }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <button @click="startEdit(dept)" class="rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600" title="Edit">
                                            <PencilIcon class="h-5 w-5" aria-hidden="true" />
                                        </button>
                                        <button @click="deleteDept(dept.id, dept.name)" class="rounded p-1 text-gray-400 hover:bg-red-100 hover:text-red-600" title="Delete">
                                            <TrashIcon class="h-5 w-5" aria-hidden="true" />
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="!departments.data?.length">
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">No departments</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination :links="departments.links" :meta="departments.meta" />
                </template>
            </div>
        </div>
    </StockAuditLayout>
</template>
