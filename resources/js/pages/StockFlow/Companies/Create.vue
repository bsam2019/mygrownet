<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { useStockflowRoute } from '@/composables/useStockflowRoute';
import { ref } from 'vue';
import { PlusIcon } from '@heroicons/vue/24/outline';

const { route } = useStockflowRoute();

const form = ref({
    name: '',
    email: '',
    phone: '',
    address: '',
    city: '',
    country: '',
    contact_person: '',
    currency: 'MWK',
});

const errors = ref<Record<string, string>>({});
const processing = ref(false);

const submit = () => {
    processing.value = true;
    router.post(route('stockflow.sub.companies.store'), form.value, {
        onSuccess: () => { processing.value = false; },
        onError: (err) => { errors.value = err; processing.value = false; },
    });
};
</script>

<template>
    <StockFlowLayout title="Create Company">
        <Head title="Create Company - StockFlow" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="mb-4">
                    <Link :href="route('stockflow.sub.companies.index')" class="text-sm text-emerald-600 hover:text-emerald-700">&larr; Back to Companies</Link>
                </div>

                <div class="mb-6 overflow-hidden rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-500 px-6 py-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/20">
                            <PlusIcon class="h-6 w-6 text-white" />
                        </div>
                        <div>
                            <h1 class="text-lg font-semibold text-white">Create Company</h1>
                            <p class="text-sm text-emerald-100">Register a new company in the system</p>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 overflow-hidden">
                        <div class="border-b border-gray-100 px-6 py-4">
                            <h2 class="text-base font-semibold text-gray-900">Company Information</h2>
                        </div>
                        <div class="px-6 py-5">
                            <div class="grid gap-6 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Company Name *</label>
                                    <input v-model="form.name" type="text" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600" />
                                    <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Email</label>
                                    <input v-model="form.email" type="email" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Phone</label>
                                    <input v-model="form.phone" type="text" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Contact Person</label>
                                    <input v-model="form.contact_person" type="text" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Currency</label>
                                    <select v-model="form.currency" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600">
                                        <option value="MWK">MWK - Malawi Kwacha</option>
                                        <option value="ZMW">ZMW - Zambian Kwacha</option>
                                        <option value="USD">USD - US Dollar</option>
                                        <option value="EUR">EUR - Euro</option>
                                        <option value="GBP">GBP - British Pound</option>
                                        <option value="ZAR">ZAR - South African Rand</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">City</label>
                                    <input v-model="form.city" type="text" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Country</label>
                                    <input v-model="form.country" type="text" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600" />
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Address</label>
                                    <textarea v-model="form.address" rows="2" class="mt-1 block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 border-t border-gray-100 px-6 py-4">
                            <Link :href="route('stockflow.sub.companies.index')" class="text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">Cancel</Link>
                            <button type="submit" :disabled="processing" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                {{ processing ? 'Creating...' : 'Create Company' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </StockFlowLayout>
</template>
