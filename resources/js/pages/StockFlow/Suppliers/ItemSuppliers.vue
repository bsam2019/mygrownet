<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useNotifications } from '@/composables/useNotifications';
import { PlusIcon, TrashIcon, XMarkIcon, CheckCircleIcon } from '@heroicons/vue/24/outline';
import { useStockflowRoute } from '@/composables/useStockflowRoute';

const { route } = useStockflowRoute();

const props = defineProps<{
    itemId: number;
}>();

const page = usePage();
const isSubdomain = computed(() => (page.props as any).routeName?.startsWith('stockflow.sub.'));
const { success, error: notifyError } = useNotifications();

const baseUrl = computed(() => {
    return isSubdomain.value ? `/items/${props.itemId}/suppliers` : `/stockflow/items/${props.itemId}/suppliers`;
});

interface LinkedSupplier {
    id: number;
    name: string;
    pivot: {
        supplier_sku: string | null;
        supplier_price: number | null;
        lead_time_days: number | null;
        is_preferred: boolean;
    };
}

const suppliers = ref<LinkedSupplier[]>([]);
const loading = ref(true);
const showAddForm = ref(false);
const newLink = ref({
    sa_supplier_id: '',
    supplier_sku: '',
    supplier_price: '',
    lead_time_days: '',
    is_preferred: false,
});
const availableSuppliers = ref<any[]>([]);
const errors = ref<Record<string, string>>({});
const submitting = ref(false);

const fetchLinkedSuppliers = async () => {
    loading.value = true;
    try {
        const res = await fetch(baseUrl.value);
        const data = await res.json();
        suppliers.value = data.suppliers ?? [];
    } catch {
        suppliers.value = [];
    } finally {
        loading.value = false;
    }
};

const fetchAvailableSuppliers = async () => {
    try {
        const url = isSubdomain.value ? '/suppliers' : '/stockflow/suppliers';
        const res = await fetch(url);
        const text = await res.text();
        try {
            const data = JSON.parse(text);
            availableSuppliers.value = data.suppliers?.data ?? data.data ?? [];
        } catch {
            availableSuppliers.value = [];
        }
    } catch {
        availableSuppliers.value = [];
    }
};

const openAddForm = async () => {
    showAddForm.value = true;
    errors.value = {};
    await fetchAvailableSuppliers();
};

const addSupplier = async () => {
    if (!newLink.value.sa_supplier_id || submitting.value) return;
    submitting.value = true;
    try {
        const csrf = (page.props as any).csrf_token ?? '';
        const body: any = {
            sa_supplier_id: parseInt(newLink.value.sa_supplier_id),
        };
        if (newLink.value.supplier_sku) body.supplier_sku = newLink.value.supplier_sku;
        if (newLink.value.supplier_price) body.supplier_price = parseFloat(newLink.value.supplier_price);
        if (newLink.value.lead_time_days) body.lead_time_days = parseInt(newLink.value.lead_time_days);
        body.is_preferred = newLink.value.is_preferred;

        const res = await fetch(baseUrl.value, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify(body),
        });
        if (res.ok) {
            success('Supplier linked');
            showAddForm.value = false;
            newLink.value = { sa_supplier_id: '', supplier_sku: '', supplier_price: '', lead_time_days: '', is_preferred: false };
            await fetchLinkedSuppliers();
        } else {
            const errData = await res.json();
            errors.value = errData.errors ?? {};
            notifyError('Failed to link supplier');
        }
    } catch {
        notifyError('Request failed');
    } finally {
        submitting.value = false;
    }
};

const removeSupplier = async (supplierId: number) => {
    try {
        const csrf = (page.props as any).csrf_token ?? '';
        const res = await fetch(`${baseUrl.value}/${supplierId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf },
        });
        if (res.ok) {
            success('Supplier removed');
            await fetchLinkedSuppliers();
        } else {
            notifyError('Failed to remove supplier');
        }
    } catch {
        notifyError('Request failed');
    }
};

import { computed } from 'vue';

onMounted(fetchLinkedSuppliers);
</script>

<template>
    <div class="rounded-xl bg-white shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900">Linked Suppliers</h3>
            <button @click="openAddForm" class="inline-flex items-center gap-1 text-sm font-medium text-emerald-600 hover:text-emerald-700">
                <PlusIcon class="h-4 w-4" />
                Add Supplier
            </button>
        </div>

        <div v-if="showAddForm" class="px-5 py-4 border-b border-gray-100 bg-emerald-50/50">
            <h4 class="text-sm font-medium text-gray-700 mb-3">Link a Supplier</h4>
            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-medium text-gray-600">Supplier *</label>
                    <select v-model="newLink.sa_supplier_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Select supplier...</option>
                        <option v-for="s in availableSuppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                    <p v-if="errors.sa_supplier_id" class="mt-1 text-xs text-red-600">{{ errors.sa_supplier_id }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">Supplier SKU</label>
                    <input v-model="newLink.supplier_sku" type="text" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:ring-emerald-500 focus:border-emerald-500" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">Supplier Price</label>
                    <input v-model.number="newLink.supplier_price" type="number" step="0.01" min="0" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:ring-emerald-500 focus:border-emerald-500" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">Lead Time (days)</label>
                    <input v-model.number="newLink.lead_time_days" type="number" min="0" class="mt-1 w-full rounded-lg border-gray-300 text-sm focus:ring-emerald-500 focus:border-emerald-500" />
                </div>
                <div class="flex items-end">
                    <label class="flex items-center gap-2">
                        <input v-model="newLink.is_preferred" type="checkbox" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" />
                        <span class="text-sm text-gray-700">Preferred supplier</span>
                    </label>
                </div>
            </div>
            <div class="mt-3 flex gap-2">
                <button @click="addSupplier" :disabled="submitting" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50">
                    {{ submitting ? 'Saving...' : 'Save' }}
                </button>
                <button @click="showAddForm = false" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
            </div>
        </div>

        <div class="px-5 py-4">
            <div v-if="loading" class="text-center py-4">
                <div class="animate-spin h-5 w-5 border-2 border-emerald-600 border-t-transparent rounded-full mx-auto"></div>
            </div>
            <div v-else-if="suppliers.length === 0" class="text-center py-6">
                <p class="text-sm text-gray-500">No suppliers linked to this item</p>
            </div>
            <table v-else class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 uppercase">
                        <th class="pb-2 pr-4">Supplier</th>
                        <th class="pb-2 pr-4">SKU</th>
                        <th class="pb-2 pr-4">Price</th>
                        <th class="pb-2 pr-4">Lead Time</th>
                        <th class="pb-2 pr-2">Status</th>
                        <th class="pb-2 w-10"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="s in suppliers" :key="s.id" class="hover:bg-gray-50">
                        <td class="py-2 pr-4 font-medium text-gray-900">{{ s.name }}</td>
                        <td class="py-2 pr-4 text-gray-600">{{ s.pivot.supplier_sku || '-' }}</td>
                        <td class="py-2 pr-4 text-gray-900">{{ s.pivot.supplier_price ? Number(s.pivot.supplier_price).toFixed(2) : '-' }}</td>
                        <td class="py-2 pr-4 text-gray-600">{{ s.pivot.lead_time_days ? `${s.pivot.lead_time_days}d` : '-' }}</td>
                        <td class="py-2 pr-2">
                            <span v-if="s.pivot.is_preferred" class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">
                                <CheckCircleIcon class="h-3 w-3" />
                                Preferred
                            </span>
                        </td>
                        <td class="py-2">
                            <button @click="removeSupplier(s.id)" class="rounded p-1 text-gray-400 hover:bg-red-100 hover:text-red-600" title="Remove supplier">
                                <TrashIcon class="h-4 w-4" />
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
