<template>
    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-900">Create Investment</h2>
                <Link :href="route('investments.index')"
                      class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Back to Investments
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Amount (ZMW)</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">K</span>
                                    </div>
                                    <input type="number"
                                           v-model="form.amount"
                                           class="block w-full pl-7 rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                           min="5000"
                                           step="100">
                                </div>
                                <div v-if="form.errors.amount" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.amount }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Category</label>
                                <select v-model="form.category_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select a category</option>
                                    <option v-for="category in categories"
                                            :key="category.id"
                                            :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                                <div v-if="form.errors.category_id" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.category_id }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Payment Proof</label>
                                <input type="file"
                                       @input="form.payment_proof = $event.target.files[0]"
                                       accept="image/*"
                                       class="mt-1 block w-full text-sm text-gray-500
                                              file:mr-4 file:py-2 file:px-4
                                              file:rounded-md file:border-0
                                              file:text-sm file:font-semibold
                                              file:bg-blue-50 file:text-blue-700
                                              hover:file:bg-blue-100">
                                <div v-if="form.errors.payment_proof" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.payment_proof }}
                                </div>
                            </div>

                            <div class="bg-gray-50 p-4 rounded-md">
                                <h3 class="text-sm font-medium text-gray-700 mb-2">Investment Tiers</h3>
                                <div class="space-y-2 text-sm text-gray-600">
                                    <p>Elite: K10,000+ (15% Annual Returns)</p>
                                    <p>Leader: K5,000 - K9,999 (10% Annual Returns)</p>
                                    <p>Builder: K2,500 - K4,999 (7% Annual Returns)</p>
                                    <p>Starter: K1,000 - K2,499 (5% Annual Returns)</p>
                                    <p>Basic: Below K1,000 (3% Annual Returns)</p>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit"
                                        :disabled="form.processing"
                                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                    Create Investment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/layouts/InvestorLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    categories: Array,
});

const form = useForm({
    amount: '',
    category_id: '',
    payment_proof: null
});

const submit = () => {
    form.post(route('investments.store'));
};
</script>
