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
                            <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
                                <BanknotesIcon class="h-5 w-5 text-emerald-600" aria-hidden="true" />
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900">Record Sale</h2>
                                <p class="text-xs text-gray-500">Quick cash sale entry</p>
                            </div>
                        </div>
                        <button @click="close" class="p-2 rounded-full hover:bg-gray-100" aria-label="Close modal">
                            <XMarkIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                        </button>
                    </div>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="px-5 py-4 space-y-4 overflow-y-auto max-h-[60vh]">
                    <!-- Customer (Optional) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Customer (optional)</label>
                        <select
                            v-model="form.customer_id"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-sm"
                        >
                            <option :value="null">Walk-in Customer</option>
                            <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                                {{ customer.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Description *</label>
                        <input
                            v-model="form.description"
                            type="text"
                            placeholder="e.g., Product sale, Service rendered"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-sm"
                            required
                        />
                        <p v-if="errors.description" class="mt-1 text-xs text-red-500">{{ errors.description }}</p>
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
                                class="w-full pl-8 pr-4 py-3 rounded-xl border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-sm"
                                required
                            />
                        </div>
                        <p v-if="errors.amount" class="mt-1 text-xs text-red-500">{{ errors.amount }}</p>
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Payment Method *</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button
                                v-for="method in paymentMethods"
                                :key="method.value"
                                type="button"
                                @click="form.payment_method = method.value"
                                :class="[
                                    'flex flex-col items-center gap-1 p-3 rounded-xl border-2 transition-all',
                                    form.payment_method === method.value
                                        ? 'border-emerald-500 bg-emerald-50 text-emerald-700'
                                        : 'border-gray-200 hover:border-gray-300 text-gray-600'
                                ]"
                            >
                                <component :is="method.icon" class="h-5 w-5" aria-hidden="true" />
                                <span class="text-xs font-medium">{{ method.label }}</span>
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
                                    : 'bg-emerald-500 text-white hover:bg-emerald-600 active:scale-[0.98]'
                            ]"
                        >
                            <span v-if="processing" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                            <span>{{ processing ? 'Saving...' : 'Record Sale' }}</span>
                        </button>
                    </div>
                    <button
                        type="button"
                        @click="goToFullPage"
                        class="w-full mt-3 text-center text-sm text-emerald-600 font-medium hover:text-emerald-700"
                    >
                        Need more options? Create full invoice →
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
    BanknotesIcon,
    XMarkIcon,
    CurrencyDollarIcon,
    BuildingLibraryIcon,
    DevicePhoneMobileIcon,
} from '@heroicons/vue/24/outline';

interface Customer {
    id: number;
    name: string;
}

interface Props {
    show: boolean;
    customers?: Customer[];
}

const props = withDefaults(defineProps<Props>(), {
    customers: () => [],
});

const emit = defineEmits<{
    close: [];
    success: [];
}>();

const form = ref({
    customer_id: null as number | null,
    description: '',
    amount: null as number | null,
    payment_method: 'cash',
});

const errors = ref<Record<string, string>>({});
const processing = ref(false);

const paymentMethods = [
    { value: 'cash', label: 'Cash', icon: CurrencyDollarIcon },
    { value: 'bank', label: 'Bank', icon: BuildingLibraryIcon },
    { value: 'mobile_money', label: 'Mobile', icon: DevicePhoneMobileIcon },
];

const isValid = computed(() => {
    return form.value.description.trim() !== '' && 
           form.value.amount !== null && 
           form.value.amount > 0 &&
           form.value.payment_method !== '';
});

const close = () => {
    emit('close');
};

const resetForm = () => {
    form.value = {
        customer_id: null,
        description: '',
        amount: null,
        payment_method: 'cash',
    };
    errors.value = {};
};

const submit = () => {
    if (!isValid.value || processing.value) return;

    processing.value = true;
    errors.value = {};

    router.post(route('growfinance.sales.store'), form.value, {
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
    router.visit(route('growfinance.invoices.create'));
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
