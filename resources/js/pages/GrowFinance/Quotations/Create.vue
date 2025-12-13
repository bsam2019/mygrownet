<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <!-- Header -->
            <div class="flex items-center gap-3 mb-6">
                <button 
                    @click="router.visit(route('growfinance.quotations.index'))"
                    class="p-2 rounded-xl bg-white shadow-sm active:bg-gray-50"
                    aria-label="Go back"
                >
                    <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                </button>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">New Quotation</h1>
                    <p class="text-gray-500 text-sm">Create a quote for your customer</p>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-4">
                <!-- Customer & Dates -->
                <div class="bg-white rounded-2xl shadow-sm p-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Customer</label>
                        <select
                            v-model="form.customer_id"
                            class="w-full px-4 py-3 rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500"
                        >
                            <option :value="null">Walk-in Customer</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Subject / Title</label>
                        <input
                            v-model="form.subject"
                            type="text"
                            placeholder="e.g., Website Development Quote"
                            class="w-full px-4 py-3 rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Quote Date</label>
                            <input
                                v-model="form.quotation_date"
                                type="date"
                                class="w-full px-4 py-3 rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500"
                                required
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Valid Until</label>
                            <input
                                v-model="form.valid_until"
                                type="date"
                                class="w-full px-4 py-3 rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500"
                            />
                        </div>
                    </div>
                </div>

                <!-- Line Items -->
                <div class="bg-white rounded-2xl shadow-sm p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-900">Items</h3>
                        <button
                            type="button"
                            @click="addItem"
                            class="text-sm text-emerald-600 font-medium"
                        >
                            + Add Item
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div v-for="(item, index) in form.items" :key="index" class="p-3 bg-gray-50 rounded-xl">
                            <input
                                v-model="item.description"
                                type="text"
                                placeholder="Description"
                                class="w-full px-3 py-2 rounded-lg border-gray-200 text-sm mb-2"
                                required
                            />
                            <div class="flex gap-2">
                                <input
                                    v-model="item.quantity"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    placeholder="Qty"
                                    class="w-20 px-3 py-2 rounded-lg border-gray-200 text-sm"
                                    required
                                />
                                <div class="relative flex-1">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">K</span>
                                    <input
                                        v-model="item.unit_price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        placeholder="Price"
                                        class="w-full pl-8 pr-3 py-2 rounded-lg border-gray-200 text-sm"
                                        required
                                    />
                                </div>
                                <button
                                    v-if="form.items.length > 1"
                                    type="button"
                                    @click="removeItem(index)"
                                    class="p-2 text-red-500"
                                    aria-label="Remove item"
                                >
                                    <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                                </button>
                            </div>
                            <p class="text-right text-sm font-medium text-gray-600 mt-2">
                                {{ formatMoney(item.quantity * item.unit_price || 0) }}
                            </p>
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="mt-4 pt-4 border-t border-gray-200 flex justify-between items-center">
                        <span class="font-semibold text-gray-900">Total</span>
                        <span class="text-xl font-bold text-emerald-600">{{ formatMoney(total) }}</span>
                    </div>
                </div>

                <!-- Notes & Terms -->
                <div class="bg-white rounded-2xl shadow-sm p-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                        <textarea
                            v-model="form.notes"
                            rows="2"
                            placeholder="Additional notes for the customer"
                            class="w-full px-4 py-3 rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Terms & Conditions</label>
                        <textarea
                            v-model="form.terms"
                            rows="2"
                            placeholder="Payment terms, delivery conditions, etc."
                            class="w-full px-4 py-3 rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500"
                        />
                    </div>
                </div>

                <!-- Submit -->
                <button
                    type="submit"
                    :disabled="isSubmitting"
                    class="w-full py-4 rounded-2xl bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-semibold shadow-lg shadow-emerald-500/30 active:scale-[0.98] transition-transform disabled:opacity-50"
                >
                    {{ isSubmitting ? 'Creating...' : 'Create Quotation' }}
                </button>
            </form>
        </div>
    </GrowFinanceLayout>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import { ArrowLeftIcon, XMarkIcon } from '@heroicons/vue/24/outline';

interface Props {
    customers: Array<{ id: number; name: string }>;
}

defineProps<Props>();

const isSubmitting = ref(false);

// Default valid_until to 30 days from now
const defaultValidUntil = new Date();
defaultValidUntil.setDate(defaultValidUntil.getDate() + 30);

const form = reactive({
    customer_id: null as number | null,
    quotation_date: new Date().toISOString().split('T')[0],
    valid_until: defaultValidUntil.toISOString().split('T')[0],
    subject: '',
    notes: '',
    terms: '',
    items: [{ description: '', quantity: 1, unit_price: 0 }],
});

const total = computed(() => {
    return form.items.reduce((sum, item) => sum + (item.quantity * item.unit_price || 0), 0);
});

const addItem = () => {
    form.items.push({ description: '', quantity: 1, unit_price: 0 });
};

const removeItem = (index: number) => {
    form.items.splice(index, 1);
};

const formatMoney = (amount: number) => {
    return 'K' + amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const submit = () => {
    isSubmitting.value = true;
    router.post(route('growfinance.quotations.store'), form, {
        onFinish: () => { isSubmitting.value = false; },
    });
};
</script>
