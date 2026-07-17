<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { useStockflowRoute } from '@/composables/useStockflowRoute';
import LoadingSkeleton from '@/components/StockFlow/LoadingSkeleton.vue';
import Pagination from '@/components/StockFlow/Pagination.vue';
import { ref } from 'vue';
import { PlusIcon, PencilIcon, TrashIcon, DocumentArrowDownIcon, ArrowUpTrayIcon } from '@heroicons/vue/24/outline';
import { useNotifications } from '@/composables/useNotifications';
import { useConfirmDialog } from '@/composables/useConfirmDialog';

const { route } = useStockflowRoute();

interface Supplier {
    id: number;
    name: string;
    contact_person: string | null;
    phone: string | null;
    email: string | null;
    address: string | null;
    payment_terms: string | null;
}

interface Props {
    suppliers: {
        data: Supplier[];
        links: { url: string | null; label: string; active: boolean }[];
        meta: { current_page: number; last_page: number; total: number; from: number; to: number };
    };
}

defineProps<Props>();

const { success, error: notifyError } = useNotifications();
const confirm = useConfirmDialog();

const showCreateForm = ref(false);
const editingId = ref<number | null>(null);
const form = ref({
    name: '',
    contact_person: '',
    phone: '',
    email: '',
    address: '',
    payment_terms: '',
});
const errors = ref<Record<string, string>>({});

const resetForm = () => {
    form.value = { name: '', contact_person: '', phone: '', email: '', address: '', payment_terms: '' };
    editingId.value = null;
    showCreateForm.value = false;
};

const startEdit = (supplier: Supplier) => {
    form.value = {
        name: supplier.name,
        contact_person: supplier.contact_person || '',
        phone: supplier.phone || '',
        email: supplier.email || '',
        address: supplier.address || '',
        payment_terms: supplier.payment_terms || '',
    };
    editingId.value = supplier.id;
    showCreateForm.value = true;
};

const submit = () => {
    if (editingId.value) {
        router.put(route('stockflow.sub.suppliers.update', editingId.value), form.value, {
            onSuccess: () => { success('Supplier updated'); resetForm(); },
            onError: (err) => { errors.value = err; notifyError('Failed to update supplier'); },
        });
    } else {
        router.post(route('stockflow.sub.suppliers.store'), form.value, {
            onSuccess: () => { success('Supplier created'); resetForm(); },
            onError: (err) => { errors.value = err; notifyError('Failed to create supplier'); },
        });
    }
};

const deleteSupplier = async (id: number, name: string) => {
    const ok = await confirm.show(`Delete supplier "${name}"? This cannot be undone.`, 'Delete Supplier');
    if (ok) {
        router.delete(route('stockflow.sub.suppliers.destroy', id), {
            onSuccess: () => success('Supplier deleted'),
            onError: () => notifyError('Failed to delete supplier'),
        });
    }
};

const uploadSuppliersCsv = async (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (!input.files?.length) return;
    const formData = new FormData();
    formData.append('file', input.files[0]);
    await router.post(route('stockflow.sub.suppliers.import-csv'), formData, {
        onSuccess: () => { success('Suppliers imported'); input.value = ''; },
        onError: (err) => { notifyError(Object.values(err).join(', ')); input.value = ''; },
    });
};
</script>

<template>
    <StockFlowLayout title="Suppliers">
        <Head title="Suppliers - StockFlow" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <h1 class="text-2xl font-bold text-gray-900">Suppliers</h1>
                    <div class="flex gap-2">
                        <a :href="route('stockflow.sub.templates.suppliers')" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-600 hover:text-emerald-600 hover:border-emerald-200">
                            <DocumentArrowDownIcon class="h-4 w-4" />
                            Template
                        </a>
                        <label class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-600 hover:text-emerald-600 hover:border-emerald-200 cursor-pointer">
                            <ArrowUpTrayIcon class="h-4 w-4" />
                            Import CSV
                            <input type="file" accept=".csv,.txt" class="hidden" @change="uploadSuppliersCsv" />
                        </label>
                        <button @click="resetForm(); showCreateForm = true" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                            <PlusIcon class="h-5 w-5" aria-hidden="true" />
                            Add Supplier
                        </button>
                    </div>
                </div>

                <div v-if="showCreateForm" class="mb-6 rounded-xl bg-white p-6 shadow-sm border border-emerald-200">
                    <h2 class="text-lg font-semibold text-gray-900">{{ editingId ? 'Edit' : 'New' }} Supplier</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name *</label>
                            <input v-model="form.name" type="text" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                            <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Contact Person</label>
                            <input v-model="form.contact_person" type="text" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Phone</label>
                            <input v-model="form.phone" type="text" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input v-model="form.email" type="email" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Payment Terms</label>
                            <input v-model="form.payment_terms" type="text" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Address</label>
                            <textarea v-model="form.address" rows="1" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                        </div>
                    </div>
                    <div class="mt-4 flex gap-3">
                        <button @click="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">{{ editingId ? 'Update' : 'Save' }}</button>
                        <button @click="resetForm" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    </div>
                </div>

                <LoadingSkeleton v-if="!suppliers.data?.length" type="card" />
                <template v-else>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div v-for="supplier in suppliers.data" :key="supplier.id" class="rounded-xl bg-white p-6 shadow-sm">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ supplier.name }}</h3>
                                    <p v-if="supplier.contact_person" class="text-sm text-gray-500">{{ supplier.contact_person }}</p>
                                </div>
                                <div class="flex gap-1">
                                    <button @click="startEdit(supplier)" class="rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600" title="Edit supplier">
                                        <PencilIcon class="h-5 w-5" aria-hidden="true" />
                                    </button>
                                    <button @click="deleteSupplier(supplier.id, supplier.name)" class="rounded p-1 text-gray-400 hover:bg-red-100 hover:text-red-600" title="Delete supplier">
                                        <TrashIcon class="h-5 w-5" aria-hidden="true" />
                                    </button>
                                </div>
                            </div>
                            <div class="mt-3 space-y-1 text-sm text-gray-600">
                                <p v-if="supplier.phone">Phone: {{ supplier.phone }}</p>
                                <p v-if="supplier.email">Email: {{ supplier.email }}</p>
                                <p v-if="supplier.payment_terms">Terms: {{ supplier.payment_terms }}</p>
                                <p v-if="supplier.address">{{ supplier.address }}</p>
                            </div>
                        </div>
                        <div v-if="!suppliers.data?.length" class="col-span-full py-12 text-center text-gray-500">No suppliers yet</div>
                    </div>
                    <Pagination :links="suppliers.links" :meta="suppliers.meta" />
                </template>
            </div>
        </div>
    </StockFlowLayout>
</template>