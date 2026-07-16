<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { useStockflowRoute } from '@/composables/useStockflowRoute';
import { useCurrency } from '@/composables/useCurrency';
import { ref } from 'vue';

const { route } = useStockflowRoute();
const { formatCurrency } = useCurrency();

interface Customer {
    id: number;
    name: string;
    phone: string | null;
    email: string | null;
    address: string | null;
    city: string | null;
    country: string | null;
    credit_limit: number | null;
    payment_terms: string | null;
    notes: string | null;
}

interface Props { customer: Customer; }
const props = defineProps<Props>();

const showEdit = ref(false);
const editForm = ref({
    name: props.customer.name,
    phone: props.customer.phone || '',
    email: props.customer.email || '',
    address: props.customer.address || '',
    city: props.customer.city || '',
    country: props.customer.country || '',
    credit_limit: props.customer.credit_limit || '',
    payment_terms: props.customer.payment_terms || '',
    notes: props.customer.notes || '',
});

const errors = ref<Record<string, string>>({});

const submitEdit = () => {
    router.put(route('stockflow.sub.customers.update', props.customer.id), editForm.value, {
        onSuccess: () => { showEdit.value = false; },
        onError: (err) => { errors.value = err; },
    });
};
</script>

<template>
    <StockFlowLayout :title="customer.name">
        <Head :title="`${customer.name} - StockFlow`" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <Link :href="route('stockflow.sub.customers.index')" class="text-sm text-emerald-600 hover:text-emerald-700">&larr; Back to Customers</Link>
                </div>

                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h1 class="text-2xl font-bold text-gray-900">{{ customer.name }}</h1>
                        <button @click="showEdit = !showEdit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Edit</button>
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        <div><p class="text-xs text-gray-500">Phone</p><p class="text-sm text-gray-900">{{ customer.phone || '-' }}</p></div>
                        <div><p class="text-xs text-gray-500">Email</p><p class="text-sm text-gray-900">{{ customer.email || '-' }}</p></div>
                        <div><p class="text-xs text-gray-500">Address</p><p class="text-sm text-gray-900">{{ customer.address || '-' }}</p></div>
                        <div><p class="text-xs text-gray-500">Location</p><p class="text-sm text-gray-900">{{ [customer.city, customer.country].filter(Boolean).join(', ') || '-' }}</p></div>
                        <div><p class="text-xs text-gray-500">Credit Limit</p><p class="text-sm text-gray-900">{{ customer.credit_limit ? formatCurrency(customer.credit_limit) : '-' }}</p></div>
                        <div><p class="text-xs text-gray-500">Payment Terms</p><p class="text-sm text-gray-900">{{ customer.payment_terms || '-' }}</p></div>
                    </div>
                    <div v-if="customer.notes" class="mt-4"><p class="text-xs text-gray-500">Notes</p><p class="mt-1 text-sm text-gray-700">{{ customer.notes }}</p></div>
                </div>

                <div v-if="showEdit" class="mt-6 rounded-xl bg-white p-6 shadow-sm border border-blue-200">
                    <h2 class="text-lg font-semibold text-gray-900">Edit Customer</h2>
                    <form @submit.prevent="submitEdit" class="mt-4 space-y-4">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div><label class="block text-sm font-medium text-gray-700">Name</label><input v-model="editForm.name" type="text" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" /></div>
                            <div><label class="block text-sm font-medium text-gray-700">Phone</label><input v-model="editForm.phone" type="text" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" /></div>
                            <div><label class="block text-sm font-medium text-gray-700">Email</label><input v-model="editForm.email" type="email" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" /></div>
                            <div><label class="block text-sm font-medium text-gray-700">Credit Limit</label><input v-model.number="editForm.credit_limit" type="number" step="0.01" min="0" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" /></div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Payment Terms</label>
                                <select v-model="editForm.payment_terms" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                    <option value="">N/A</option>
                                    <option value="cash">Cash on Delivery</option>
                                    <option value="7">Net 7</option>
                                    <option value="14">Net 14</option>
                                    <option value="30">Net 30</option>
                                    <option value="60">Net 60</option>
                                </select>
                            </div>
                            <div><label class="block text-sm font-medium text-gray-700">City</label><input v-model="editForm.city" type="text" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" /></div>
                            <div><label class="block text-sm font-medium text-gray-700">Country</label><input v-model="editForm.country" type="text" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" /></div>
                        </div>
                        <div><label class="block text-sm font-medium text-gray-700">Address</label><textarea v-model="editForm.address" rows="2" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"></textarea></div>
                        <div><label class="block text-sm font-medium text-gray-700">Notes</label><textarea v-model="editForm.notes" rows="2" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"></textarea></div>
                        <div class="flex gap-3">
                            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Save Changes</button>
                            <button type="button" @click="showEdit = false" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </StockFlowLayout>
</template>
