<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="show" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm" @click="close" />
        </Transition>

        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 translate-y-full"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-full"
        >
            <div v-if="show" class="fixed bottom-0 left-0 right-0 z-50 bg-white rounded-t-3xl shadow-2xl max-h-[90vh] overflow-hidden">
                <!-- Handle bar -->
                <div class="flex justify-center pt-3 pb-2">
                    <div class="w-10 h-1 bg-gray-300 rounded-full"></div>
                </div>

                <!-- Header -->
                <div class="px-5 pb-4 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                                <ClipboardDocumentListIcon class="h-5 w-5 text-red-600" aria-hidden="true" />
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900">Record Expense</h2>
                                <p class="text-xs text-gray-500">Quick expense entry</p>
                            </div>
                        </div>
                        <button @click="close" class="p-2 rounded-full hover:bg-gray-100" aria-label="Close modal">
                            <XMarkIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                        </button>
                    </div>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="px-5 py-4 space-y-4 overflow-y-auto max-h-[55vh]">
                    <!-- Category -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Category *</label>
                        <select
                            v-model="form.category"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-red-500 focus:ring-2 focus:ring-red-500/20 text-sm"
                            required
                        >
                            <option value="">Select category</option>
                            <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                        </select>
                        <p v-if="errors.category" class="mt-1 text-xs text-red-500">{{ errors.category }}</p>
                    </div>

                    <!-- Vendor (Optional) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Vendor (optional)</label>
                        <select
                            v-model="form.vendor_id"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-red-500 focus:ring-2 focus:ring-red-500/20 text-sm"
                        >
                            <option :value="null">No vendor</option>
                            <option v-for="vendor in vendors" :key="vendor.id" :value="vendor.id">
                                {{ vendor.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                        <input
                            v-model="form.description"
                            type="text"
                            placeholder="e.g., Office supplies, Fuel"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-red-500 focus:ring-2 focus:ring-red-500/20 text-sm"
                        />
                    </div>

                    <!-- Amount -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Amount (K) *</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm">K</span>
                            <input
                                v-model.number="form.amount"
                                type="number"
                                step="0.01"
                                min="0.01"
                                placeholder="0.00"
                                class="w-full pl-8 pr-4 py-3 rounded-xl border border-gray-200 focus:border-red-500 focus:ring-2 focus:ring-red-500/20 text-sm"
                                required
                            />
                        </div>
                        <p v-if="errors.amount" class="mt-1 text-xs text-red-500">{{ errors.amount }}</p>
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Payment Method *</label>
                        <div class="grid grid-cols-4 gap-2">
                            <button
                                v-for="method in paymentMethods"
                                :key="method.value"
                                type="button"
                                @click="form.payment_method = method.value"
                                :class="[
                                    'flex flex-col items-center gap-1 p-2.5 rounded-xl border-2 transition-all',
                                    form.payment_method === method.value
                                        ? 'border-red-500 bg-red-50 text-red-700'
                                        : 'border-gray-200 hover:border-gray-300 text-gray-600'
                                ]"
                            >
                                <component :is="method.icon" class="h-4 w-4" aria-hidden="true" />
                                <span class="text-[10px] font-medium">{{ method.label }}</span>
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Footer -->
                <div class="px-5 py-4 border-t border-gray-100 bg-gray-50 safe-area-bottom">
                    <div class="flex gap-3">
                        <button
                            type="button"
                            @click="close"
                            class="flex-1 py-3 rounded-xl border border-gray-200 text-gray-700 font-medium hover:bg-gray-100 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            @click="submit"
                            :disabled="processing || !isValid"
                            :class="[
                                'flex-1 py-3 rounded-xl font-medium transition-all flex items-center justify-center gap-2',
                                processing || !isValid
                                    ? 'bg-gray-200 text-gray-400 cursor-not-allowed'
                                    : 'bg-red-500 text-white hover:bg-red-600 active:scale-[0.98]'
                            ]"
                        >
                            <span v-if="processing" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                            <span>{{ processing ? 'Saving...' : 'Record Expense' }}</span>
                        </button>
                    </div>
                    <button
                        type="button"
                        @click="goToFullPage"
                        class="w-full mt-3 text-center text-sm text-red-600 font-medium hover:text-red-700"
                    >
                        Need more options? Full expense form →
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    ClipboardDocumentListIcon,
    XMarkIcon,
    CurrencyDollarIcon,
    BuildingLibraryIcon,
    DevicePhoneMobileIcon,
    CreditCardIcon,
} from '@heroicons/vue/24/outline';

interface Vendor {
    id: number;
    name: string;
}

interface Account {
    id: number;
    code: string;
    name: string;
}

interface Props {
    show: boolean;
    vendors?: Vendor[];
    accounts?: Account[];
}

const props = withDefaults(defineProps<Props>(), {
    vendors: () => [],
    accounts: () => [],
});

const emit = defineEmits<{
    close: [];
    success: [];
}>();

const categories = [
    'Cost of Goods Sold',
    'Salaries & Wages',
    'Rent',
    'Utilities',
    'Transport & Fuel',
    'Office Supplies',
    'Marketing',
    'Bank Charges',
    'Other',
];

const form = ref({
    vendor_id: null as number | null,
    account_id: null as number | null,
    category: '',
    description: '',
    amount: null as number | null,
    payment_method: 'cash',
    expense_date: new Date().toISOString().split('T')[0],
});

const errors = ref<Record<string, string>>({});
const processing = ref(false);

const paymentMethods = [
    { value: 'cash', label: 'Cash', icon: CurrencyDollarIcon },
    { value: 'bank', label: 'Bank', icon: BuildingLibraryIcon },
    { value: 'mobile_money', label: 'Mobile', icon: DevicePhoneMobileIcon },
    { value: 'credit', label: 'Credit', icon: CreditCardIcon },
];

const isValid = computed(() => {
    return form.value.category !== '' && 
           form.value.amount !== null && 
           form.value.amount > 0 &&
           form.value.payment_method !== '';
});

const close = () => {
    emit('close');
};

const resetForm = () => {
    form.value = {
        vendor_id: null,
        account_id: props.accounts.length > 0 ? props.accounts[0].id : null,
        category: '',
        description: '',
        amount: null,
        payment_method: 'cash',
        expense_date: new Date().toISOString().split('T')[0],
    };
    errors.value = {};
};

const submit = () => {
    if (!isValid.value || processing.value) return;

    processing.value = true;
    errors.value = {};

    // Use first expense account if available
    const submitData = {
        ...form.value,
        account_id: form.value.account_id || (props.accounts.length > 0 ? props.accounts[0].id : null),
    };

    router.post(route('growfinance.expenses.store'), submitData, {
        preserveScroll: true,
        onSuccess: () => {
            resetForm();
            emit('success');
            emit('close');
        },
        onError: (errs) => {
            errors.value = errs;
        },
        onFinish: () => {
            processing.value = false;
        },
    });
};

const goToFullPage = () => {
    close();
    router.visit(route('growfinance.expenses.create'));
};

// Reset form when modal opens
watch(() => props.show, (newVal) => {
    if (newVal) {
        resetForm();
    }
});
</script>

<style scoped>
.safe-area-bottom {
    padding-bottom: max(1rem, env(safe-area-inset-bottom));
}
</style>
