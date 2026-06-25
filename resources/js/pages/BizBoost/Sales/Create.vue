<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import Card from '@/Components/BizBoost/UI/Card.vue';
import FormInput from '@/Components/BizBoost/Form/FormInput.vue';
import FormSelect from '@/Components/BizBoost/Form/FormSelect.vue';
import FormTextarea from '@/Components/BizBoost/Form/FormTextarea.vue';
import FormSection from '@/Components/BizBoost/Form/FormSection.vue';
import FormActions from '@/Components/BizBoost/Form/FormActions.vue';
import { formatBizBoostPrice } from '@/composables/useBizBoostCurrency';
import { ArrowLeftIcon, PlusIcon } from '@heroicons/vue/24/outline';

interface Product {
    id: number;
    name: string;
    price: number;
    sale_price?: number;
}

interface Customer {
    id: number;
    name: string;
    phone?: string;
}

interface Props {
    products: Product[];
    customers: Customer[];
}

const props = defineProps<Props>();

const today = new Date().toISOString().split('T')[0];

const form = useForm({
    product_id: null as number | null,
    product_name: '',
    customer_id: null as number | null,
    quantity: 1,
    unit_price: 0,
    sale_date: today,
    payment_method: 'cash',
    notes: '',
});

const useCustomProduct = ref(false);

const productOptions = computed(() =>
    props.products.map(p => ({
        value: p.id,
        label: `${p.name} - ${formatBizBoostPrice(p.sale_price || p.price)}`,
    }))
);

const customerOptions = computed(() => [
    { value: null, label: 'Walk-in Customer' },
    ...props.customers.map(c => ({
        value: c.id,
        label: c.name + (c.phone ? ` - ${c.phone}` : ''),
    })),
]);

const paymentOptions = [
    { value: 'cash', label: 'Cash' },
    { value: 'mobile_money', label: 'Mobile Money' },
    { value: 'card', label: 'Card' },
    { value: 'bank_transfer', label: 'Bank Transfer' },
];

const onProductChange = (productId: number | null) => {
    if (productId) {
        const product = props.products.find(p => p.id === productId);
        if (product) {
            form.product_name = product.name;
            form.unit_price = product.sale_price || product.price;
        }
        useCustomProduct.value = false;
    }
};

const toggleCustomProduct = () => {
    useCustomProduct.value = !useCustomProduct.value;
    if (useCustomProduct.value) {
        form.product_id = null;
        form.product_name = '';
        form.unit_price = 0;
    }
};

const total = computed(() => form.quantity * form.unit_price);

const canSubmit = computed(() =>
    form.product_name.trim() !== '' &&
    form.quantity > 0 &&
    form.unit_price >= 0 &&
    form.sale_date !== ''
);

const submit = () => {
    if (!canSubmit.value) return;
    form.post('/bizboost/sales');
};
</script>

<template>
    <Head title="Record Sale - BizBoost" />
    <BizBoostLayout title="Record Sale">
        <div class="max-w-2xl mx-auto">
            <Link
                href="/bizboost/sales"
                class="inline-flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 mb-6 transition-colors"
            >
                <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                Back to Sales
            </Link>

            <form @submit.prevent="submit" class="space-y-6">
                <Card>
                    <FormSection title="Product" description="Select or enter the product being sold" :icon="PlusIcon">
                        <div class="flex items-center justify-between mb-2">
                            <button
                                type="button"
                                @click="toggleCustomProduct"
                                class="text-sm text-violet-600 hover:text-violet-700 dark:text-violet-400 dark:hover:text-violet-300 transition-colors"
                            >
                                {{ useCustomProduct ? 'Select from catalog' : 'Enter custom product' }}
                            </button>
                        </div>

                        <template v-if="!useCustomProduct">
                            <FormSelect
                                v-model="form.product_id"
                                label="Select Product"
                                :options="productOptions"
                                placeholder="Choose a product"
                                :error="form.errors.product_id"
                                @update:model-value="onProductChange"
                            />
                        </template>
                        <template v-else>
                            <FormInput
                                v-model="form.product_name"
                                label="Product Name"
                                placeholder="Enter product or service name"
                                :error="form.errors.product_name"
                            />
                        </template>

                        <div class="grid gap-4 sm:grid-cols-3">
                            <FormInput
                                v-model.number="form.quantity"
                                label="Quantity"
                                type="number"
                                min="1"
                                :error="form.errors.quantity"
                            />
                            <FormInput
                                v-model.number="form.unit_price"
                                label="Unit Price (K)"
                                type="number"
                                step="0.01"
                                min="0"
                                :error="form.errors.unit_price"
                            />
                            <div class="flex items-end">
                                <div class="w-full p-3 bg-violet-50 dark:bg-violet-900/30 rounded-xl text-center ring-1 ring-violet-200/50 dark:ring-violet-700/30">
                                    <p class="text-xs text-violet-600 dark:text-violet-400 font-medium">Total</p>
                                    <p class="text-lg font-bold text-violet-700 dark:text-violet-300">{{ formatBizBoostPrice(total) }}</p>
                                </div>
                            </div>
                        </div>
                    </FormSection>
                </Card>

                <Card>
                    <FormSection title="Sale Details" description="Date, payment method, and customer">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <FormInput
                                v-model="form.sale_date"
                                label="Sale Date"
                                type="date"
                                :error="form.errors.sale_date"
                            />
                            <FormSelect
                                v-model="form.payment_method"
                                label="Payment Method"
                                :options="paymentOptions"
                            />
                            <div class="sm:col-span-2">
                                <FormSelect
                                    v-model="form.customer_id"
                                    label="Customer (Optional)"
                                    :options="customerOptions"
                                />
                            </div>
                        </div>
                    </FormSection>
                </Card>

                <Card>
                    <FormTextarea
                        v-model="form.notes"
                        label="Notes (Optional)"
                        placeholder="Any additional notes about this sale..."
                        :rows="2"
                    />
                </Card>

                <FormActions
                    :submit-label="form.processing ? 'Recording...' : `Record Sale - ${formatBizBoostPrice(total)}`"
                    cancel-href="/bizboost/sales"
                    :processing="form.processing"
                />
            </form>
        </div>
    </BizBoostLayout>
</template>
