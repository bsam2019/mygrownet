<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import GrowBizLayout from '@/layouts/GrowBizLayout.vue';
import {
    Cog6ToothIcon,
    BuildingStorefrontIcon,
    ReceiptPercentIcon,
    CurrencyDollarIcon,
    PrinterIcon,
    CheckIcon,
} from '@heroicons/vue/24/outline';

interface Settings {
    receipt_header: string | null;
    receipt_footer: string | null;
    business_name: string | null;
    business_address: string | null;
    business_phone: string | null;
    tax_id: string | null;
    default_tax_rate: number;
    enable_tax: boolean;
    require_customer: boolean;
    allow_credit_sales: boolean;
    auto_print_receipt: boolean;
    track_inventory: boolean;
}

interface Props {
    settings: Settings;
}

const props = defineProps<Props>();

const form = ref<Settings>({
    receipt_header: props.settings.receipt_header || '',
    receipt_footer: props.settings.receipt_footer || '',
    business_name: props.settings.business_name || '',
    business_address: props.settings.business_address || '',
    business_phone: props.settings.business_phone || '',
    tax_id: props.settings.tax_id || '',
    default_tax_rate: props.settings.default_tax_rate || 0,
    enable_tax: props.settings.enable_tax || false,
    require_customer: props.settings.require_customer || false,
    allow_credit_sales: props.settings.allow_credit_sales || false,
    auto_print_receipt: props.settings.auto_print_receipt || false,
    track_inventory: props.settings.track_inventory || true,
});

const isSubmitting = ref(false);
const saved = ref(false);

const saveSettings = () => {
    isSubmitting.value = true;
    router.post(route('growbiz.pos.settings.save'), form.value, {
        onSuccess: () => {
            saved.value = true;
            setTimeout(() => saved.value = false, 3000);
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};
</script>

<template>
    <GrowBizLayout>
        <Head title="POS Settings" />

        <div class="max-w-3xl mx-auto px-4 sm:px-6 py-4 sm:py-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">POS Settings</h1>
                    <p class="text-sm text-gray-500 mt-1">Configure your point of sale system</p>
                </div>
                <div v-if="saved" class="flex items-center gap-2 text-green-600">
                    <CheckIcon class="w-5 h-5" />
                    <span class="text-sm">Saved!</span>
                </div>
            </div>

            <form @submit.prevent="saveSettings" class="space-y-6">
                <!-- Business Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                            <BuildingStorefrontIcon class="w-5 h-5 text-blue-600" />
                        </div>
                        <h2 class="font-semibold text-gray-900">Business Information</h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Business Name</label>
                            <input
                                v-model="form.business_name"
                                type="text"
                                class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="Your Business Name"
                            />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <textarea
                                v-model="form.business_address"
                                rows="2"
                                class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="Business address for receipts"
                            ></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input
                                v-model="form.business_phone"
                                type="text"
                                class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="+260 XXX XXX XXX"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tax ID / TPIN</label>
                            <input
                                v-model="form.tax_id"
                                type="text"
                                class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="Tax identification number"
                            />
                        </div>
                    </div>
                </div>

                <!-- Receipt Settings -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                            <ReceiptPercentIcon class="w-5 h-5 text-purple-600" />
                        </div>
                        <h2 class="font-semibold text-gray-900">Receipt Settings</h2>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Receipt Header</label>
                            <input
                                v-model="form.receipt_header"
                                type="text"
                                class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="Text shown at top of receipt"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Receipt Footer</label>
                            <input
                                v-model="form.receipt_footer"
                                type="text"
                                class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="Thank you for your business!"
                            />
                        </div>
                        <div class="flex items-center justify-between py-2">
                            <div>
                                <p class="font-medium text-gray-900">Auto-print Receipt</p>
                                <p class="text-sm text-gray-500">Print receipt after each sale</p>
                            </div>
                            <button
                                type="button"
                                @click="form.auto_print_receipt = !form.auto_print_receipt"
                                :class="[
                                    'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                                    form.auto_print_receipt ? 'bg-blue-600' : 'bg-gray-200'
                                ]"
                            >
                                <span
                                    :class="[
                                        'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                                        form.auto_print_receipt ? 'translate-x-6' : 'translate-x-1'
                                    ]"
                                />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tax Settings -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                            <CurrencyDollarIcon class="w-5 h-5 text-green-600" />
                        </div>
                        <h2 class="font-semibold text-gray-900">Tax Settings</h2>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between py-2">
                            <div>
                                <p class="font-medium text-gray-900">Enable Tax</p>
                                <p class="text-sm text-gray-500">Add tax to sales</p>
                            </div>
                            <button
                                type="button"
                                @click="form.enable_tax = !form.enable_tax"
                                :class="[
                                    'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                                    form.enable_tax ? 'bg-blue-600' : 'bg-gray-200'
                                ]"
                            >
                                <span
                                    :class="[
                                        'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                                        form.enable_tax ? 'translate-x-6' : 'translate-x-1'
                                    ]"
                                />
                            </button>
                        </div>
                        <div v-if="form.enable_tax">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Default Tax Rate (%)</label>
                            <input
                                v-model.number="form.default_tax_rate"
                                type="number"
                                min="0"
                                max="100"
                                step="0.1"
                                class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="16"
                            />
                            <p class="text-xs text-gray-500 mt-1">Standard VAT in Zambia is 16%</p>
                        </div>
                    </div>
                </div>

                <!-- Sales Settings -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                            <Cog6ToothIcon class="w-5 h-5 text-amber-600" />
                        </div>
                        <h2 class="font-semibold text-gray-900">Sales Settings</h2>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between py-2">
                            <div>
                                <p class="font-medium text-gray-900">Require Customer</p>
                                <p class="text-sm text-gray-500">Customer info required for each sale</p>
                            </div>
                            <button
                                type="button"
                                @click="form.require_customer = !form.require_customer"
                                :class="[
                                    'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                                    form.require_customer ? 'bg-blue-600' : 'bg-gray-200'
                                ]"
                            >
                                <span
                                    :class="[
                                        'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                                        form.require_customer ? 'translate-x-6' : 'translate-x-1'
                                    ]"
                                />
                            </button>
                        </div>
                        <div class="flex items-center justify-between py-2">
                            <div>
                                <p class="font-medium text-gray-900">Allow Credit Sales</p>
                                <p class="text-sm text-gray-500">Allow sales on credit</p>
                            </div>
                            <button
                                type="button"
                                @click="form.allow_credit_sales = !form.allow_credit_sales"
                                :class="[
                                    'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                                    form.allow_credit_sales ? 'bg-blue-600' : 'bg-gray-200'
                                ]"
                            >
                                <span
                                    :class="[
                                        'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                                        form.allow_credit_sales ? 'translate-x-6' : 'translate-x-1'
                                    ]"
                                />
                            </button>
                        </div>
                        <div class="flex items-center justify-between py-2">
                            <div>
                                <p class="font-medium text-gray-900">Track Inventory</p>
                                <p class="text-sm text-gray-500">Auto-update stock on sales</p>
                            </div>
                            <button
                                type="button"
                                @click="form.track_inventory = !form.track_inventory"
                                :class="[
                                    'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                                    form.track_inventory ? 'bg-blue-600' : 'bg-gray-200'
                                ]"
                            >
                                <span
                                    :class="[
                                        'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                                        form.track_inventory ? 'translate-x-6' : 'translate-x-1'
                                    ]"
                                />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="isSubmitting"
                        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 flex items-center gap-2"
                    >
                        <CheckIcon v-if="!isSubmitting" class="w-5 h-5" />
                        <span>{{ isSubmitting ? 'Saving...' : 'Save Settings' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </GrowBizLayout>
</template>
