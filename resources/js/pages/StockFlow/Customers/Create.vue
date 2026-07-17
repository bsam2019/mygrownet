<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { useStockflowRoute } from '@/composables/useStockflowRoute';
import { ref } from 'vue';
import { PlusIcon } from '@heroicons/vue/24/outline';

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
                <div class="mb-4">
                    <Link :href="route('stockflow.sub.customers.index')" class="text-sm text-emerald-600 hover:text-emerald-700">&larr; Back to Customers</Link>
                </div>

                <div class="mb-6 overflow-hidden rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-500 px-6 py-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/20">
                            <PlusIcon class="h-6 w-6 text-white" />
                        </div>
                        <div>
                            <h1 class="text-lg font-semibold text-white">Create Customer</h1>
                            <p class="text-sm text-emerald-100">Add a new customer to your records</p>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 overflow-hidden">
                        <div class="border-b border-gray-100 px-6 py-4">
                            <h2 class="text-base font-semibold text-gray-900">Customer Information</h2>
                        </div>
                        <div class="px-6 py-5">
                            <div class="grid gap-6 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Name *</label>
                                    <input v-model="form.name" type="text" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600" />
                                    <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Phone</label>
                                    <input v-model="form.phone" type="text" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Email</label>
                                    <input v-model="form.email" type="email" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600" />
                                    <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Credit Limit</label>
                                    <input v-model.number="form.credit_limit" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Payment Terms</label>
                                    <select v-model="form.payment_terms" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600">
                                        <option value="">N/A</option>
                                        <option value="cash">Cash on Delivery</option>
                                        <option value="7">Net 7</option>
                                        <option value="14">Net 14</option>
                                        <option value="30">Net 30</option>
                                        <option value="60">Net 60</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-6 grid gap-6 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">City</label>
                                    <input v-model="form.city" type="text" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Country</label>
                                    <input v-model="form.country" type="text" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600" />
                                </div>
                            </div>
                            <div class="mt-6">
                                <label class="block text-sm font-medium text-gray-700">Address</label>
                                <textarea v-model="form.address" rows="2" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600"></textarea>
                            </div>
                            <div class="mt-6">
                                <label class="block text-sm font-medium text-gray-700">Notes</label>
                                <textarea v-model="form.notes" rows="2" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600"></textarea>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 border-t border-gray-100 px-6 py-4">
                            <Link :href="route('stockflow.sub.customers.index')" class="text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">Cancel</Link>
                            <button type="submit" :disabled="processing" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                {{ processing ? 'Creating...' : 'Create Customer' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </StockFlowLayout>
</template>
