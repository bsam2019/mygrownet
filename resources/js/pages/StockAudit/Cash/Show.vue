<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import StockAuditLayout from '@/layouts/StockAuditLayout.vue';
import CommentSection from '@/components/StockAudit/CommentSection.vue';
import { useCurrency } from '@/composables/useCurrency';
import { ref } from 'vue';
import { useNotifications } from '@/composables/useNotifications';
import { useConfirmDialog } from '@/composables/useConfirmDialog';

interface CashRegister {
    id: number;
    register_date: string;
    status: string;
    opening_balance: number;
    total_sales: number;
    total_expenses: number;
    total_banking: number;
    expected_closing: number;
    actual_closing: number | null;
    variance: number | null;
    notes: string | null;
    opened_by: string;
    closed_by: string | null;
    created_at: string;
}

interface Props {
    register: CashRegister;
}

const props = defineProps<Props>();
const { success, error: notifyError } = useNotifications();
const confirm = useConfirmDialog();

const showMovementForm = ref(false);
const showCloseForm = ref(false);

const movementForm = ref({
    type: 'expense',
    amount: 0,
    description: '',
});

const closeForm = ref({
    actual_closing: props.register.expected_closing,
    notes: '',
});

const errors = ref<Record<string, string>>({});

const { formatCurrency } = useCurrency();

const statusColors: Record<string, string> = {
    open: 'bg-green-100 text-green-800',
    closed: 'bg-gray-100 text-gray-800',
    verified: 'bg-blue-100 text-blue-800',
};

const addMovement = () => {
    router.post(route('stockflow.sub.cash.movement', props.register.id), movementForm.value, {
        onSuccess: () => { success('Movement recorded'); showMovementForm.value = false; movementForm.value = { type: 'expense', amount: 0, description: '' }; },
        onError: (err) => { errors.value = err; notifyError('Failed to record movement'); },
    });
};

const closeRegister = () => {
    router.post(route('stockflow.sub.cash.close', props.register.id), closeForm.value, {
        onSuccess: () => { success('Register closed'); showCloseForm.value = false; },
        onError: (err) => { errors.value = err; notifyError('Failed to close register'); },
    });
};

const verifyRegister = async () => {
    const ok = await confirm.show('Mark this register as verified?', 'Verify Register');
    if (ok) {
        router.post(route('stockflow.sub.cash.verify', props.register.id), {}, {
            onSuccess: () => success('Register verified'),
            onError: () => notifyError('Failed to verify register'),
        });
    }
};

const isOpen = props.register.status === 'open';
</script>

<template>
    <StockAuditLayout :title="`Register - ${register.register_date}`">
        <Head :title="`Cash Register - ${register.register_date} - StockFlow`" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <Link :href="route('stockflow.sub.cash.index')" class="text-sm text-emerald-600 hover:text-emerald-700">&larr; Back to Register</Link>
                </div>

                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between border-b border-gray-200 pb-4">
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">Cash Register</h1>
                            <p class="text-sm text-gray-500">{{ register.register_date }} - Opened by {{ register.opened_by }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span :class="[statusColors[register.status] || 'bg-gray-100 text-gray-800', 'rounded-full px-3 py-1 text-sm font-medium capitalize']">
                                {{ register.status }}
                            </span>
                            <button v-if="isOpen" @click="showMovementForm = !showMovementForm" class="rounded-lg bg-amber-600 px-3 py-2 text-sm font-medium text-white hover:bg-amber-700">
                                Add Movement
                            </button>
                            <button v-if="isOpen" @click="showCloseForm = !showCloseForm" class="rounded-lg bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700">
                                Close Register
                            </button>
                            <button v-if="register.status === 'closed'" @click="verifyRegister" class="rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700">
                                Verify
                            </button>
                        </div>
                    </div>

                    <!-- Movement Form -->
                    <div v-if="showMovementForm" class="mt-6 rounded-lg border border-amber-200 bg-amber-50 p-4">
                        <h3 class="font-semibold text-amber-800">Add Cash Movement</h3>
                        <div class="mt-3 grid gap-4 sm:grid-cols-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Type</label>
                                <select v-model="movementForm.type" class="mt-1 w-full rounded border-amber-300 text-sm focus:border-amber-500 focus:ring-amber-500">
                                    <option value="expense">Expense</option>
                                    <option value="banking">Banking</option>
                                    <option value="float_top_up">Float Top Up</option>
                                    <option value="float_withdrawal">Float Withdrawal</option>
                                    <option value="petty_cash">Petty Cash</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Amount *</label>
                                <input v-model.number="movementForm.amount" type="number" step="0.01" min="0.01" class="mt-1 w-full rounded border-amber-300 text-sm focus:border-amber-500 focus:ring-amber-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Description</label>
                                <input v-model="movementForm.description" type="text" class="mt-1 w-full rounded border-amber-300 text-sm focus:border-amber-500 focus:ring-amber-500" />
                            </div>
                        </div>
                        <div class="mt-3 flex gap-3">
                            <button @click="addMovement" class="rounded-lg bg-amber-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-amber-700">Add</button>
                            <button @click="showMovementForm = false" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                        </div>
                    </div>

                    <!-- Close Form -->
                    <div v-if="showCloseForm" class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4">
                        <h3 class="font-semibold text-red-800">Close Register</h3>
                        <div class="mt-3 grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Actual Closing Balance *</label>
                                <input v-model.number="closeForm.actual_closing" type="number" step="0.01" min="0" class="mt-1 w-full rounded border-red-300 text-sm focus:border-red-500 focus:ring-red-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Notes</label>
                                <input v-model="closeForm.notes" type="text" class="mt-1 w-full rounded border-red-300 text-sm focus:border-red-500 focus:ring-red-500" />
                            </div>
                        </div>
                        <div class="mt-3 flex gap-3">
                            <button @click="closeRegister" class="rounded-lg bg-red-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-red-700">Close</button>
                            <button @click="showCloseForm = false" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
                        <div class="rounded-lg bg-gray-50 p-4">
                            <p class="text-xs text-gray-500">Opening Balance</p>
                            <p class="text-lg font-bold text-gray-900">{{ formatCurrency(register.opening_balance) }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4">
                            <p class="text-xs text-gray-500">Sales</p>
                            <p class="text-lg font-bold text-emerald-600">{{ formatCurrency(register.total_sales) }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4">
                            <p class="text-xs text-gray-500">Expenses</p>
                            <p class="text-lg font-bold text-red-600">{{ formatCurrency(register.total_expenses) }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4">
                            <p class="text-xs text-gray-500">Banking</p>
                            <p class="text-lg font-bold text-blue-600">{{ formatCurrency(register.total_banking) }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4">
                            <p class="text-xs text-gray-500">Expected Closing</p>
                            <p class="text-lg font-bold text-gray-900">{{ formatCurrency(register.expected_closing) }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4">
                            <p class="text-xs text-gray-500">Actual Closing</p>
                            <p class="text-lg font-bold text-gray-900">{{ register.actual_closing !== null ? formatCurrency(register.actual_closing) : '-' }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4">
                            <p class="text-xs text-gray-500">Variance</p>
                            <p :class="['text-lg font-bold', register.variance !== null && register.variance !== 0 ? (register.variance < 0 ? 'text-red-600' : 'text-emerald-600') : 'text-gray-900']">
                                {{ register.variance !== null ? formatCurrency(register.variance) : '-' }}
                            </p>
                        </div>
                    </div>

                    <div v-if="register.notes" class="mt-4 border-t border-gray-200 pt-4">
                        <p class="text-xs text-gray-500">Notes</p>
                        <p class="mt-1 text-sm text-gray-700">{{ register.notes }}</p>
                    </div>
                </div>

                <!-- Comments -->
                <div class="mt-6">
                    <CommentSection type="cash_register" :id="register.id" />
                </div>
            </div>
        </div>
    </StockAuditLayout>
</template>
