<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import StockAuditLayout from '@/layouts/StockAuditLayout.vue';
import LoadingSkeleton from '@/components/StockAudit/LoadingSkeleton.vue';
import Pagination from '@/components/StockAudit/Pagination.vue';
import { ref } from 'vue';
import { PlusIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline';
import { useNotifications } from '@/composables/useNotifications';
import { useConfirmDialog } from '@/composables/useConfirmDialog';

interface Bin {
    id: number;
    sa_department_id: number;
    name: string;
    label: string | null;
    description: string | null;
    sort_order: number;
}

interface Department {
    id: number;
    name: string;
}

interface Props {
    bins: {
        data: Bin[];
        links: { url: string | null; label: string; active: boolean }[];
        meta: { current_page: number; last_page: number; total: number; from: number; to: number };
    };
    departments: Department[];
}

const props = defineProps<Props>();

const departmentName = (deptId: number): string => {
    const dept = props.departments.find(d => d.id === deptId);
    return dept?.name || `Dept #${deptId}`;
};

const { success, error: notifyError } = useNotifications();
const confirm = useConfirmDialog();

const showCreateForm = ref(false);
const editingId = ref<number | null>(null);
const form = ref({ sa_department_id: '', name: '', label: '', description: '', sort_order: 0 });
const errors = ref<Record<string, string>>({});

const resetForm = () => {
    form.value = { sa_department_id: '', name: '', label: '', description: '', sort_order: 0 };
    editingId.value = null;
    showCreateForm.value = false;
};

const startEdit = (bin: Bin) => {
    form.value = {
        sa_department_id: String(bin.sa_department_id),
        name: bin.name,
        label: bin.label || '',
        description: bin.description || '',
        sort_order: bin.sort_order,
    };
    editingId.value = bin.id;
    showCreateForm.value = true;
};

const submit = () => {
    if (editingId.value) {
        router.put(route('stock-audit.bins.update', editingId.value), form.value, {
            onSuccess: () => { success('Bin updated'); resetForm(); },
            onError: (err) => { errors.value = err; notifyError('Failed to update bin'); },
        });
    } else {
        router.post(route('stock-audit.bins.store'), form.value, {
            onSuccess: () => { success('Bin created'); resetForm(); },
            onError: (err) => { errors.value = err; notifyError('Failed to create bin'); },
        });
    }
};

const deleteBin = async (id: number, name: string) => {
    const ok = await confirm.show(`Delete bin "${name}"?`, 'Delete Bin');
    if (ok) {
        router.delete(route('stock-audit.bins.destroy', id), {
            onSuccess: () => success('Bin deleted'),
            onError: () => notifyError('Failed to delete bin'),
        });
    }
};
</script>

<template>
    <StockAuditLayout title="Bins">
        <Head title="Bins - StockFlow" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <h1 class="text-2xl font-bold text-gray-900">Bins</h1>
                    <button @click="resetForm(); showCreateForm = true" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                        <PlusIcon class="h-5 w-5" aria-hidden="true" />
                        Add Bin
                    </button>
                </div>

                <div v-if="showCreateForm" class="mb-6 rounded-xl bg-white p-6 shadow-sm border border-emerald-200">
                    <h2 class="text-lg font-semibold text-gray-900">{{ editingId ? 'Edit' : 'New' }} Bin</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name *</label>
                            <input v-model="form.name" type="text" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                            <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Label</label>
                            <input v-model="form.label" type="text" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Department *</label>
                            <select v-model="form.sa_department_id" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">Select a department...</option>
                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                            </select>
                            <p v-if="errors.sa_department_id" class="mt-1 text-sm text-red-600">{{ errors.sa_department_id }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Sort Order</label>
                            <input v-model.number="form.sort_order" type="number" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <input v-model="form.description" type="text" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                    </div>
                    <div class="mt-4 flex gap-3">
                        <button @click="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">{{ editingId ? 'Update' : 'Save' }}</button>
                        <button @click="resetForm" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    </div>
                </div>

                <LoadingSkeleton v-if="!bins.data?.length" type="table" />
                <template v-else>
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Label</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Department</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Order</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="bin in bins.data" :key="bin.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ bin.name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ bin.label || '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ departmentName(bin.sa_department_id) }}</td>
                                    <td class="px-6 py-4 text-right text-sm text-gray-700">{{ bin.sort_order }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <button @click="startEdit(bin)" class="rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600" title="Edit">
                                            <PencilIcon class="h-5 w-5" aria-hidden="true" />
                                        </button>
                                        <button @click="deleteBin(bin.id, bin.name)" class="rounded p-1 text-gray-400 hover:bg-red-100 hover:text-red-600" title="Delete">
                                            <TrashIcon class="h-5 w-5" aria-hidden="true" />
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="!bins.data?.length">
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">No bins</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination :links="bins.links" :meta="bins.meta" />
                </template>
            </div>
        </div>
    </StockAuditLayout>
</template>
