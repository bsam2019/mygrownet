<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <!-- Header -->
            <div class="flex items-center gap-3 mb-6">
                <button 
                    @click="router.visit(route('growfinance.customers.index'))"
                    class="p-2 rounded-xl bg-white shadow-sm active:bg-gray-50"
                    aria-label="Go back"
                >
                    <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                </button>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Add Customer</h1>
                    <p class="text-gray-500 text-sm">Create a new customer record</p>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-4">
                <div class="bg-white rounded-2xl shadow-sm p-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="Customer name"
                            class="w-full px-4 py-3 rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500"
                            required
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                        <input
                            v-model="form.phone"
                            type="tel"
                            placeholder="Phone number"
                            class="w-full px-4 py-3 rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input
                            v-model="form.email"
                            type="email"
                            placeholder="Email address"
                            class="w-full px-4 py-3 rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                        <textarea
                            v-model="form.address"
                            rows="2"
                            placeholder="Physical address"
                            class="w-full px-4 py-3 rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Credit Limit</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">K</span>
                            <input
                                v-model="form.credit_limit"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                class="w-full pl-10 pr-4 py-3 rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                        <textarea
                            v-model="form.notes"
                            rows="2"
                            placeholder="Additional notes"
                            class="w-full px-4 py-3 rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500"
                        />
                    </div>
                </div>

                <button
                    type="submit"
                    :disabled="isSubmitting"
                    class="w-full py-4 rounded-2xl bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold shadow-lg shadow-blue-500/30 active:scale-[0.98] transition-transform disabled:opacity-50"
                >
                    {{ isSubmitting ? 'Saving...' : 'Save Customer' }}
                </button>
            </form>
        </div>
    </GrowFinanceLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

const isSubmitting = ref(false);
const form = reactive({
    name: '',
    phone: '',
    email: '',
    address: '',
    credit_limit: '',
    notes: '',
});

const submit = () => {
    isSubmitting.value = true;
    router.post(route('growfinance.customers.store'), form, {
        onFinish: () => { isSubmitting.value = false; },
    });
};
</script>
