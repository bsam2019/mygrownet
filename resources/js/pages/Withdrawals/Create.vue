<script setup lang="ts">
import AppLayout from '@/layouts/MemberLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    amount: '',
    wallet_address: '',
    withdrawal_method: ''
});

const withdrawalMethods = [
    { id: 'bitcoin', name: 'Bitcoin' },
    { id: 'ethereum', name: 'Ethereum' },
    { id: 'bank', name: 'Bank Transfer' }
];
</script>

<template>
    <Head title="Request Withdrawal" />

    <AppLayout>
        <div class="max-w-2xl mx-auto p-6">
            <h1 class="text-2xl font-semibold text-gray-900 mb-6">Request Withdrawal</h1>

            <form @submit.prevent="form.post(route('withdrawals.store'))" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Withdrawal Amount</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">K</span>
                        </div>
                        <input type="number"
                               v-model="form.amount"
                               class="block w-full pl-7 rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                               step="0.01">
                    </div>
                    <p v-if="form.errors.amount" class="mt-1 text-sm text-red-600">{{ form.errors.amount }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Withdrawal Method</label>
                    <select v-model="form.withdrawal_method"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select a method</option>
                        <option v-for="method in withdrawalMethods"
                                :key="method.id"
                                :value="method.id">
                            {{ method.name }}
                        </option>
                    </select>
                    <p v-if="form.errors.withdrawal_method" class="mt-1 text-sm text-red-600">
                        {{ form.errors.withdrawal_method }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        {{ form.withdrawal_method === 'bank' ? 'Bank Account Details' : 'Wallet Address' }}
                    </label>
                    <input type="text"
                           v-model="form.wallet_address"
                           class="mt-1 block w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <p v-if="form.errors.wallet_address" class="mt-1 text-sm text-red-600">
                        {{ form.errors.wallet_address }}
                    </p>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                        Submit Withdrawal Request
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
