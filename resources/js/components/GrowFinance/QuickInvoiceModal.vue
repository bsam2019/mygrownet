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
                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                <DocumentTextIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900">Quick Invoice</h2>
                                <p class="text-xs text-gray-500">
                                    <span v-if="autoSaveStatus === 'saving'" class="text-amber-600">Saving draft...</span>
                                    <span v-else-if="autoSaveStatus === 'saved'" class="text-emerald-600">✓ Draft saved</span>
                                    <span v-else>Simple single-item invoice</span>
                                </p>
                            </div>
                        </div>
                        <button @click="close" class="p-2 rounded-full hover:bg-gray-100" aria-label="Close modal">
                            <XMarkIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                        </button>
                    </div>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="px-5 py-4 space-y-4 overflow-y-auto max-h-[55vh]">
                    <!-- Customer -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Customer *</label>
                        <select
                            v-model="form.customer_id"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 text-sm"
                            required
                        >
                            <option :value="null">Select customer</option>
                            <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                                {{ customer.name }}
                            </option>
                        </select>
                        <p v-if="errors.customer_id" class="mt-1 text-xs text-red-500">{{ errors.customer_id }}</p>
                    </div>

                    <!-- Item Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Item/Service *</label>
                        <input
                            v-model="form.item_description"
                            type="text"
                            placeholder="e.g., Consulting services, Product sale"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 text-sm"
                            required
                        />
                        <p v-if="errors['items.0.description']" class="mt-1 text-xs text-red-500">{{ errors['items.0.description'] }}</p>
                    </div>

                    <!-- Quantity and Price Row -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Quantity</label>
                            <input
                                v-model.number="form.quantity"
                                type="number"
                                step="0.01"
                                min="0.01"
                                placeholder="1"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 text-sm"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Unit Price (K)</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm">K</span>
                                <input
                                    v-model.number="form.unit_price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="0.00"
                                    class="w-full pl-8 pr-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 text-sm"
                                    required
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Total Display -->
                    <div class="bg-blue-50 rounded-xl p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-blue-700 font-medium">Total Amount</span>
                            <span class="text-xl font-bold text-blue-700">K{{ totalAmount.toFixed(2) }}</span>
                        </div>
                    </div>

                    <!-- Due Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Due Date</label>
                        <input
                            v-model="form.due_date"
                            type="date"
                            :min="form.invoice_date"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 text-sm"
                        />
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes (optional)</label>
                        <textarea
                            v-model="form.notes"
                            rows="2"
                            placeholder="Any additional notes..."
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 text-sm resize-none"
                        ></textarea>
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
                                    : 'bg-blue-500 text-white hover:bg-blue-600 active:scale-[0.98]'
                            ]"
                        >
                            <span v-if="processing" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                            <span>{{ processing ? 'Creating...' : 'Create Invoice' }}</span>
                        </button>
                    </div>
                    <button
                        type="button"
                        @click="goToFullPage"
                        class="w-full mt-3 text-center text-sm text-blue-600 font-medium hover:text-blue-700"
                    >
                        Need multiple items? Full invoice form →
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    DocumentTextIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';
import { useAutoSave } from '@/composables/useAutoSave';

interface Customer {
    id: number;
    name: string;
    email?: string;
    phone?: string;
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

const today = new Date().toISOString().split('T')[0];
const defaultDueDate = new Date(Date.now() + 14 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]; // 14 days from now

const form = ref({
    customer_id: null as number | null,
    item_description: '',
    quantity: 1,
    unit_price: null as number | null,
    invoice_date: today,
    due_date: defaultDueDate,
    notes: '',
});

const errors = ref<Record<string, string>>({});
const processing = ref(false);

// Auto-save to localStorage
const AUTOSAVE_KEY = 'quick_invoice_draft';
const { autoSaveStatus, loadDraft, hasDraft, clearDraft } = useAutoSave(AUTOSAVE_KEY, form.value, { debounce: 1500 });

const totalAmount = computed(() => {
    const qty = form.value.quantity || 0;
    const price = form.value.unit_price || 0;
    return qty * price;
});

const isValid = computed(() => {
    return form.value.customer_id !== null && 
           form.value.item_description.trim() !== '' && 
           form.value.unit_price !== null && 
           form.value.unit_price >= 0 &&
           form.value.quantity > 0;
});

const close = () => {
    emit('close');
};

const resetForm = () => {
    form.value = {
        customer_id: null,
        item_description: '',
        quantity: 1,
        unit_price: null,
        invoice_date: today,
        due_date: defaultDueDate,
        notes: '',
    };
    errors.value = {};
};

const submit = () => {
    if (!isValid.value || processing.value) return;

    processing.value = true;
    errors.value = {};

    // Transform to the format expected by the controller
    const submitData = {
        customer_id: form.value.customer_id,
        invoice_date: form.value.invoice_date,
        due_date: form.value.due_date,
        notes: form.value.notes,
        items: [
            {
                description: form.value.item_description,
                quantity: form.value.quantity,
                unit_price: form.value.unit_price,
            }
        ],
    };

    router.post(route('growfinance.invoices.store'), submitData, {
        preserveScroll: true,
        onSuccess: () => {
            clearDraft(); // Clear saved draft on success
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

// Load draft when modal opens
onMounted(() => {
    if (hasDraft()) {
        const draft = loadDraft();
        if (draft) {
            Object.assign(form.value, draft);
        }
    }
});

// Reset form when modal opens/closes
watch(() => props.show, (newVal) => {
    if (newVal) {
        // Load draft if exists, otherwise reset
        if (hasDraft()) {
            const draft = loadDraft();
            if (draft) {
                Object.assign(form.value, draft);
            }
        }
    } else {
        // Don't reset on close - keep the draft
        errors.value = {};
    }
});
</script>

<style scoped>
.safe-area-bottom {
    padding-bottom: max(1rem, env(safe-area-inset-bottom));
}
</style>
