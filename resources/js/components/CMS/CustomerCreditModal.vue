<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { XMarkIcon, CreditCardIcon, DocumentTextIcon } from '@heroicons/vue/24/outline';

const props = defineProps<{
    open: boolean;
    customerId: number;
    customerName: string;
    availableInvoices?: Array<{
        id: number;
        invoice_number: string;
        total_amount: number;
        amount_paid: number;
        balance_due: number;
    }>;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'applied'): void;
}>();

const creditSummary = ref<any>(null);
const loading = ref(false);

const form = useForm({
    invoice_id: null as number | null,
    amount: 0,
});

const selectedInvoice = computed(() => {
    if (!form.invoice_id || !props.availableInvoices) return null;
    return props.availableInvoices.find(inv => inv.id === form.invoice_id);
});

const maxAmount = computed(() => {
    if (!selectedInvoice.value || !creditSummary.value) return 0;
    return Math.min(selectedInvoice.value.balance_due, creditSummary.value.total_credit);
});

const fetchCreditSummary = async () => {
    loading.value = true;
    try {
        const response = await fetch(`/cms/customers/${props.customerId}/credit`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        creditSummary.value = await response.json();
    } catch (error) {
        console.error('Failed to fetch credit summary:', error);
    } finally {
        loading.value = false;
    }
};

const applyCredit = () => {
    form.post(`/cms/customers/${props.customerId}/credit/apply`, {
        preserveScroll: true,
        onSuccess: () => {
            emit('applied');
            emit('close');
            form.reset();
        },
    });
};

const useMaxAmount = () => {
    form.amount = maxAmount.value;
};

onMounted(() => {
    if (props.open) {
        fetchCreditSummary();
    }
});
</script>

<template>
    <TransitionRoot as="template" :show="open">
        <Dialog as="div" class="relative z-50" @close="emit('close')">
            <TransitionChild
                as="template"
                enter="ease-out duration-300"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="ease-in duration-200"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
            </TransitionChild>

            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <TransitionChild
                        as="template"
                        enter="ease-out duration-300"
                        enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        enter-to="opacity-100 translate-y-0 sm:scale-100"
                        leave="ease-in duration-200"
                        leave-from="opacity-100 translate-y-0 sm:scale-100"
                        leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    >
                        <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:p-6">
                            <div class="absolute right-0 top-0 pr-4 pt-4">
                                <button
                                    type="button"
                                    class="rounded-md bg-white text-gray-400 hover:text-gray-500"
                                    @click="emit('close')"
                                >
                                    <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                                </button>
                            </div>

                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <CreditCardIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
                                </div>
                                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left flex-1">
                                    <DialogTitle as="h3" class="text-lg font-semibold leading-6 text-gray-900">
                                        Apply Customer Credit
                                    </DialogTitle>
                                    <p class="mt-1 text-sm text-gray-500">
                                        {{ customerName }}
                                    </p>
                                </div>
                            </div>

                            <div v-if="loading" class="mt-6 text-center py-8">
                                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                            </div>

                            <div v-else-if="creditSummary" class="mt-6 space-y-6">
                                <!-- Credit Summary -->
                                <div class="bg-green-50 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="text-sm text-green-600 font-medium">Available Credit</div>
                                            <div class="text-2xl font-bold text-green-900">K {{ creditSummary.total_credit.toFixed(2) }}</div>
                                        </div>
                                        <CreditCardIcon class="h-10 w-10 text-green-600" />
                                    </div>
                                </div>

                                <!-- Unallocated Payments -->
                                <div v-if="creditSummary.unallocated_payments.length > 0">
                                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Credit Sources</h4>
                                    <div class="space-y-2 max-h-40 overflow-y-auto">
                                        <div
                                            v-for="payment in creditSummary.unallocated_payments"
                                            :key="payment.payment_id"
                                            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg text-sm"
                                        >
                                            <div>
                                                <div class="font-medium text-gray-900">{{ payment.payment_number }}</div>
                                                <div class="text-xs text-gray-500">{{ payment.payment_date }}</div>
                                            </div>
                                            <div class="text-right">
                                                <div class="font-semibold text-gray-900">K {{ payment.unallocated_amount.toFixed(2) }}</div>
                                                <div class="text-xs text-gray-500 capitalize">{{ payment.payment_method.replace('_', ' ') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Apply Credit Form -->
                                <form @submit.prevent="applyCredit" class="space-y-4">
                                    <!-- Select Invoice -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Select Invoice
                                        </label>
                                        <select
                                            v-model="form.invoice_id"
                                            required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        >
                                            <option :value="null">Choose an invoice...</option>
                                            <option
                                                v-for="invoice in availableInvoices"
                                                :key="invoice.id"
                                                :value="invoice.id"
                                            >
                                                {{ invoice.invoice_number }} - K {{ invoice.balance_due.toFixed(2) }} due
                                            </option>
                                        </select>
                                        <p v-if="form.errors.invoice_id" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.invoice_id }}
                                        </p>
                                    </div>

                                    <!-- Amount -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Amount to Apply
                                        </label>
                                        <div class="flex gap-2">
                                            <input
                                                v-model.number="form.amount"
                                                type="number"
                                                step="0.01"
                                                min="0.01"
                                                :max="maxAmount"
                                                required
                                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                placeholder="0.00"
                                            />
                                            <button
                                                v-if="maxAmount > 0"
                                                type="button"
                                                @click="useMaxAmount"
                                                class="px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100"
                                            >
                                                Max: K {{ maxAmount.toFixed(2) }}
                                            </button>
                                        </div>
                                        <p v-if="form.errors.amount" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.amount }}
                                        </p>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex gap-3 justify-end pt-4">
                                        <button
                                            type="button"
                                            @click="emit('close')"
                                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                                        >
                                            Cancel
                                        </button>
                                        <button
                                            type="submit"
                                            :disabled="form.processing || !form.invoice_id || form.amount <= 0"
                                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            <span v-if="form.processing">Applying...</span>
                                            <span v-else>Apply Credit</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
