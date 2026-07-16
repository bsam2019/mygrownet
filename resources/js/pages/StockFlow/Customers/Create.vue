<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { useStockflowRoute } from '@/composables/useStockflowRoute';
import { ref } from 'vue';

const { route } = useStockflowRoute();

const form = ref({
    name: '',
    phone: '',
    email: '',
    address: '',
    city: '',
    country: '',
    credit_limit: '',
    payment_terms: '',
    notes: '',
});

const errors = ref<Record<string, string>>({});
const processing = ref(false);

const submit = () => {
    processing.value = true;
    router.post(route('stockflow.sub.customers.store'), form.value, {
        onSuccess: () => { processing.value = false; },
        onError: (err) => { errors.value = err; processing.value = false; },
    });
};
</script>

<template>
    <StockFlowLayout title="Create Customer">
        <Head title="Create Customer - StockFlow" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <Link :href="route('stockflow.sub.customers.index')" class="text-sm text-emerald-600 hover:text-emerald-700">&larr; Back to Customers</Link>
                    <h1 class="mt-2 text-2xl font-bold text-gray-900">Create New Customer</h1>
                </div>

                <form @submit.prevent="submit" class="space-y-6 rounded-xl bg-white p-6 shadow-sm">
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name *</label>
                            <input v-model="form.name" type="text" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                            <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Phone</label>
                            <input v-model="form.phone" type="text" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input v-model="form.email" type="email" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                            <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Credit Limit</label>
                            <input v-model.number="form.credit_limit" type="number" step="0.01" min="0" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Payment Terms</label>
                            <select v-model="form.payment_terms" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">N/A</option>
                                <option value="cash">Cash on Delivery</option>
                                <option value="7">Net 7</option>
                                <option value="14">Net 14</option>
                                <option value="30">Net 30</option>
                                <option value="60">Net 60</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">City</label>
                            <input v-model="form.city" type="text" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Country</label>
                            <input v-model="form.country" type="text" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Address</label>
                        <textarea v-model="form.address" rows="2" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea v-model="form.notes" rows="2" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                    </div>

                    <div class="flex items-center gap-4">
                        <button type="submit" :disabled="processing" class="rounded-lg bg-emerald-600 px-6 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50">
                            {{ processing ? 'Creating...' : 'Create Customer' }}
                        </button>
                        <Link :href="route('stockflow.sub.customers.index')" class="text-sm text-gray-600 hover:text-gray-800">Cancel</Link>
                    </div>
                </form>
            </div>
        </div>
    </StockFlowLayout>
</template>
