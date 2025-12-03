<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <!-- Header -->
            <div class="flex items-center gap-3 mb-6">
                <button 
                    @click="router.visit(route('growfinance.expenses.index'))"
                    class="p-2 rounded-xl bg-white shadow-sm active:bg-gray-50"
                    aria-label="Go back"
                >
                    <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                </button>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Add Expense</h1>
                    <p class="text-gray-500 text-sm">Record a business expense</p>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-4">
                <!-- Amount -->
                <div class="bg-white rounded-2xl shadow-sm p-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Amount</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg font-medium">K</span>
                        <input
                            v-model="form.amount"
                            type="number"
                            step="0.01"
                            min="0.01"
                            placeholder="0.00"
                            class="w-full pl-10 pr-4 py-4 text-2xl font-bold rounded-xl border-gray-200 focus:border-red-500 focus:ring-red-500"
                            required
                        />
                    </div>
                    <p v-if="errors.amount" class="text-red-500 text-sm mt-1">{{ errors.amount }}</p>
                </div>

                <!-- Details -->
                <div class="bg-white rounded-2xl shadow-sm p-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <select
                            v-model="form.category"
                            class="w-full px-4 py-3 rounded-xl border-gray-200 focus:border-red-500 focus:ring-red-500"
                        >
                            <option value="">Select category</option>
                            <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <input
                            v-model="form.description"
                            type="text"
                            placeholder="What was this expense for?"
                            class="w-full px-4 py-3 rounded-xl border-gray-200 focus:border-red-500 focus:ring-red-500"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                        <input
                            v-model="form.expense_date"
                            type="date"
                            class="w-full px-4 py-3 rounded-xl border-gray-200 focus:border-red-500 focus:ring-red-500"
                            required
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Expense Account</label>
                        <select
                            v-model="form.account_id"
                            class="w-full px-4 py-3 rounded-xl border-gray-200 focus:border-red-500 focus:ring-red-500"
                            required
                        >
                            <option value="">Select account</option>
                            <option v-for="acc in accounts" :key="acc.id" :value="acc.id">
                                {{ acc.code }} - {{ acc.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Vendor (Optional)</label>
                        <select
                            v-model="form.vendor_id"
                            class="w-full px-4 py-3 rounded-xl border-gray-200 focus:border-red-500 focus:ring-red-500"
                        >
                            <option :value="null">No vendor</option>
                            <option v-for="v in vendors" :key="v.id" :value="v.id">{{ v.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button
                                v-for="method in paymentMethods"
                                :key="method.value"
                                type="button"
                                @click="form.payment_method = method.value"
                                :class="[
                                    'px-4 py-3 rounded-xl border-2 font-medium transition-colors',
                                    form.payment_method === method.value
                                        ? 'border-red-500 bg-red-50 text-red-600'
                                        : 'border-gray-200 text-gray-600'
                                ]"
                            >
                                {{ method.label }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <button
                    type="submit"
                    :disabled="isSubmitting"
                    class="w-full py-4 rounded-2xl bg-gradient-to-r from-red-500 to-red-600 text-white font-semibold shadow-lg shadow-red-500/30 active:scale-[0.98] transition-transform disabled:opacity-50"
                >
                    {{ isSubmitting ? 'Saving...' : 'Save Expense' }}
                </button>
            </form>
        </div>
    </GrowFinanceLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

interface Props {
    accounts: Array<{ id: number; code: string; name: string }>;
    vendors: Array<{ id: number; name: string }>;
    categories: string[];
}

defineProps<Props>();

const page = usePage();
const errors = page.props.errors as Record<string, string>;

const isSubmitting = ref(false);
const form = reactive({
    amount: '',
    category: '',
    description: '',
    expense_date: new Date().toISOString().split('T')[0],
    account_id: '',
    vendor_id: null as number | null,
    payment_method: 'cash',
});

const paymentMethods = [
    { value: 'cash', label: 'Cash' },
    { value: 'bank', label: 'Bank' },
    { value: 'mobile_money', label: 'Mobile Money' },
    { value: 'credit', label: 'On Credit' },
];

const submit = () => {
    isSubmitting.value = true;
    router.post(route('growfinance.expenses.store'), form, {
        onFinish: () => { isSubmitting.value = false; },
    });
};
</script>
