<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/GrowMartAdminLayout.vue';

const form = useForm({
    code: '',
    type: 'percentage',
    value: '',
    min_order_amount: '',
    max_discount: '',
    buy_quantity: '',
    get_quantity: '',
    usage_limit: '',
    starts_at: '',
    expires_at: '',
    is_active: true,
    description: '',
});

const isBogo = computed(() => form.type === 'bogo');

function submit() {
    form.post(route('admin.growmart.coupons.store'));
}
</script>

<template>
    <Head title="Create Coupon - GrowMart Admin" />

    <AdminLayout>
        <div class="max-w-2xl">
            <div class="mb-6">
                <Link :href="route('admin.growmart.coupons.index')" class="text-sm text-emerald-600 hover:text-emerald-700">
                    &larr; Back to Coupons
                </Link>
                <h1 class="text-2xl font-bold text-gray-900 mt-2">Create Coupon</h1>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Code</label>
                    <input v-model="form.code" type="text" required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                    <p class="text-xs text-gray-400 mt-1">Will be auto-uppercased</p>
                    <p v-if="form.errors.code" class="text-sm text-red-600 mt-1">{{ form.errors.code }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select v-model="form.type"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="percentage">Percentage (%)</option>
                            <option value="fixed">Fixed (K)</option>
                            <option value="bogo">BOGO (Buy X Get Y)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Value</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-gray-400 text-sm">{{ form.type === 'percentage' ? '%' : form.type === 'bogo' ? '% off' : 'K' }}</span>
                            <input v-model="form.value" type="number" min="1" required
                                class="w-full rounded-lg border border-gray-300 pl-8 pr-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                        </div>
                        <p v-if="form.type === 'fixed'" class="text-xs text-gray-400 mt-1">Value in ngwee (e.g. 1000 = K10)</p>
                        <p v-if="form.type === 'bogo'" class="text-xs text-gray-400 mt-1">Percentage discount on free item</p>
                        <p v-if="form.errors.value" class="text-sm text-red-600 mt-1">{{ form.errors.value }}</p>
                    </div>
                </div>

                <div v-if="isBogo" class="grid grid-cols-2 gap-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Buy Quantity</label>
                        <input v-model="form.buy_quantity" type="number" min="1" required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                        <p class="text-xs text-gray-400 mt-1">Customer must buy this many</p>
                        <p v-if="form.errors.buy_quantity" class="text-sm text-red-600 mt-1">{{ form.errors.buy_quantity }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Get Quantity (Free)</label>
                        <input v-model="form.get_quantity" type="number" min="1" required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                        <p class="text-xs text-gray-400 mt-1">How many customer gets free/discounted</p>
                        <p v-if="form.errors.get_quantity" class="text-sm text-red-600 mt-1">{{ form.errors.get_quantity }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Min Order Amount (ngwee)</label>
                        <input v-model="form.min_order_amount" type="number" min="0"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Max Discount (ngwee, % only)</label>
                        <input v-model="form.max_discount" type="number" min="0"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Usage Limit</label>
                        <input v-model="form.usage_limit" type="number" min="1"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                        <p class="text-xs text-gray-400 mt-1">Leave empty for unlimited</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Active</label>
                        <div class="flex gap-4 mt-1.5">
                            <label class="flex items-center gap-2">
                                <input v-model="form.is_active" type="radio" :value="true" />
                                <span class="text-sm">Yes</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input v-model="form.is_active" type="radio" :value="false" />
                                <span class="text-sm">No</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Valid From</label>
                        <input v-model="form.starts_at" type="datetime-local"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Expires At</label>
                        <input v-model="form.expires_at" type="datetime-local"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                        <p v-if="form.errors.expires_at" class="text-sm text-red-600 mt-1">{{ form.errors.expires_at }}</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea v-model="form.description" rows="2"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" :disabled="form.processing"
                        class="px-6 py-2.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 disabled:opacity-50 transition-colors">
                        {{ form.processing ? 'Creating...' : 'Create Coupon' }}
                    </button>
                    <Link :href="route('admin.growmart.coupons.index')"
                        class="px-6 py-2.5 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                        Cancel
                    </Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
