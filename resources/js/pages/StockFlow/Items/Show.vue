<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import CommentSection from '@/components/StockFlow/CommentSection.vue';
import { useCurrency } from '@/composables/useCurrency';
import { ref } from 'vue';

interface Item {
    id: number;
    name: string;
    sku: string | null;
    description: string | null;
    unit_price: number;
    unit: string;
    system_quantity: number;
    category: string | null;
    sa_bin_id: number | null;
    is_expirable: boolean;
    expiry_date: string | null;
    notes: string | null;
    created_at: string;
    updated_at: string;
}

interface Props {
    item: Item;
}

const props = defineProps<Props>();

const showAdjust = ref(false);
const adjustForm = ref({
    new_quantity: props.item.system_quantity,
    reason: '',
    type: 'adjustment_in' as string,
});

const errors = ref<Record<string, string>>({});

const { formatCurrency } = useCurrency();

const submitAdjust = () => {
    router.post(route('stockflow.sub.items.adjust', props.item.id), adjustForm.value, {
        onSuccess: () => {
            showAdjust.value = false;
        },
        onError: (err) => {
            errors.value = err;
        },
    });
};
</script>

<template>
    <StockFlowLayout :title="item.name">
        <Head :title="`${item.name} - StockFlow`" />
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <Link :href="route('stockflow.sub.items.index')" class="text-sm text-emerald-600 hover:text-emerald-700">&larr; Back to Items</Link>
                </div>

                <div class="grid gap-6 lg:grid-cols-3">
                    <!-- Item Details -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="rounded-xl bg-white p-6 shadow-sm">
                            <div class="flex items-center justify-between">
                                <h1 class="text-2xl font-bold text-gray-900">{{ item.name }}</h1>
                                <button @click="showAdjust = !showAdjust" class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white hover:bg-amber-700">
                                    Adjust Stock
                                </button>
                            </div>

                            <div class="mt-6 grid gap-6 sm:grid-cols-3">
                                <div>
                                    <p class="text-xs text-gray-500">Current Stock</p>
                                    <p :class="['text-2xl font-bold', item.system_quantity <= 0 ? 'text-red-600' : 'text-emerald-600']">
                                        {{ item.system_quantity }} {{ item.unit }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Unit Price</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(item.unit_price) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Stock Value</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(item.unit_price * item.system_quantity) }}</p>
                                </div>
                            </div>

                            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                                <div v-if="item.sku">
                                    <p class="text-xs text-gray-500">SKU</p>
                                    <p class="text-sm text-gray-900">{{ item.sku }}</p>
                                </div>
                                <div v-if="item.category">
                                    <p class="text-xs text-gray-500">Category</p>
                                    <p class="text-sm text-gray-900">{{ item.category }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Unit</p>
                                    <p class="text-sm text-gray-900">{{ item.unit }}</p>
                                </div>
                                <div v-if="item.is_expirable">
                                    <p class="text-xs text-gray-500">Expiry Date</p>
                                    <p class="text-sm text-gray-900">{{ item.expiry_date || 'N/A' }}</p>
                                </div>
                            </div>

                            <div v-if="item.description" class="mt-4">
                                <p class="text-xs text-gray-500">Description</p>
                                <p class="mt-1 text-sm text-gray-700">{{ item.description }}</p>
                            </div>

                            <div v-if="item.notes" class="mt-4">
                                <p class="text-xs text-gray-500">Notes</p>
                                <p class="mt-1 text-sm text-gray-700">{{ item.notes }}</p>
                            </div>
                        </div>

                        <!-- Stock Adjustment Form -->
                        <div v-if="showAdjust" class="rounded-xl bg-white p-6 shadow-sm border border-amber-200">
                            <h2 class="text-lg font-semibold text-gray-900">Stock Adjustment</h2>
                            <form @submit.prevent="submitAdjust" class="mt-4 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Adjustment Type</label>
                                    <select v-model="adjustForm.type" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                        <option value="adjustment_in">Adjustment In</option>
                                        <option value="adjustment_out">Adjustment Out</option>
                                        <option value="damage_out">Damage</option>
                                        <option value="expired_out">Expired</option>
                                        <option value="return_in">Return</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">New Quantity</label>
                                    <input v-model.number="adjustForm.new_quantity" type="number" step="0.01" min="0" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                                    <p v-if="errors.new_quantity" class="mt-1 text-sm text-red-600">{{ errors.new_quantity }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Reason *</label>
                                    <input v-model="adjustForm.reason" type="text" class="mt-1 w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500" />
                                    <p v-if="errors.reason" class="mt-1 text-sm text-red-600">{{ errors.reason }}</p>
                                </div>
                                <div class="flex gap-3">
                                    <button type="submit" class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white hover:bg-amber-700">Apply Adjustment</button>
                                    <button type="button" @click="showAdjust = false" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Sidebar info -->
                    <div class="space-y-6">
                        <div class="rounded-xl bg-white p-6 shadow-sm">
                            <h3 class="text-sm font-semibold text-gray-900">Item Info</h3>
                            <dl class="mt-3 space-y-3">
                                <div>
                                    <dt class="text-xs text-gray-500">Created</dt>
                                    <dd class="text-sm text-gray-900">{{ item.created_at }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-gray-500">Last Updated</dt>
                                    <dd class="text-sm text-gray-900">{{ item.updated_at }}</dd>
                                </div>
                                <div v-if="!item.is_expirable">
                                    <dt class="text-xs text-gray-500">Expiry Tracking</dt>
                                    <dd class="text-sm text-gray-500">Not tracked</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Comments -->
                    <div class="lg:col-span-2">
                        <CommentSection type="item" :id="item.id" />
                    </div>
                </div>
            </div>
        </div>
    </StockFlowLayout>
</template>
