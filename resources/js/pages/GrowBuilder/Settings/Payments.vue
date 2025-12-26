<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ArrowLeftIcon, DevicePhoneMobileIcon, BanknotesIcon, ChatBubbleLeftRightIcon } from '@heroicons/vue/24/outline';

interface Site {
    id: number;
    name: string;
    subdomain: string;
}

interface PaymentSettings {
    momo_enabled: boolean;
    momo_phone: string | null;
    momo_sandbox: boolean;
    airtel_enabled: boolean;
    airtel_phone: string | null;
    airtel_sandbox: boolean;
    cod_enabled: boolean;
    whatsapp_enabled: boolean;
    whatsapp_number: string | null;
    bank_enabled: boolean;
    bank_name: string | null;
    bank_account_name: string | null;
    bank_account_number: string | null;
    bank_branch: string | null;
}

const props = defineProps<{
    site: Site;
    settings: PaymentSettings;
}>();

const form = useForm({
    momo_enabled: props.settings.momo_enabled,
    momo_phone: props.settings.momo_phone || '',
    momo_sandbox: props.settings.momo_sandbox,
    airtel_enabled: props.settings.airtel_enabled,
    airtel_phone: props.settings.airtel_phone || '',
    airtel_sandbox: props.settings.airtel_sandbox,
    cod_enabled: props.settings.cod_enabled,
    whatsapp_enabled: props.settings.whatsapp_enabled,
    whatsapp_number: props.settings.whatsapp_number || '',
    bank_enabled: props.settings.bank_enabled,
    bank_name: props.settings.bank_name || '',
    bank_account_name: props.settings.bank_account_name || '',
    bank_account_number: props.settings.bank_account_number || '',
    bank_branch: props.settings.bank_branch || '',
});

const submit = () => {
    form.put(route('growbuilder.payments.update', props.site.id));
};
</script>

<template>
    <AppLayout>
        <Head :title="`Payment Settings - ${site.name}`" />

        <div class="py-6">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        :href="route('growbuilder.sites.show', site.id)"
                        class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-4"
                    >
                        <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                        Back to Site
                    </Link>

                    <h1 class="text-2xl font-bold text-gray-900">Payment Settings</h1>
                    <p class="text-sm text-gray-500">{{ site.name }}</p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- MTN MoMo -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="p-2 bg-yellow-100 rounded-lg">
                                <DevicePhoneMobileIcon class="h-6 w-6 text-yellow-600" aria-hidden="true" />
                            </div>
                            <div class="flex-1">
                                <h2 class="text-lg font-semibold text-gray-900">MTN MoMo</h2>
                                <p class="text-sm text-gray-500">Accept payments via MTN Mobile Money</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="form.momo_enabled" class="sr-only peer" />
                                <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-500"></div>
                            </label>
                        </div>

                        <div v-if="form.momo_enabled" class="space-y-4 pt-4 border-t border-gray-200">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">MoMo Phone Number</label>
                                <input
                                    v-model="form.momo_phone"
                                    type="tel"
                                    placeholder="0977123456"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                />
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="checkbox" v-model="form.momo_sandbox" id="momo_sandbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded" />
                                <label for="momo_sandbox" class="text-sm text-gray-600">Sandbox/Test Mode</label>
                            </div>
                        </div>
                    </div>

                    <!-- Airtel Money -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="p-2 bg-red-100 rounded-lg">
                                <DevicePhoneMobileIcon class="h-6 w-6 text-red-600" aria-hidden="true" />
                            </div>
                            <div class="flex-1">
                                <h2 class="text-lg font-semibold text-gray-900">Airtel Money</h2>
                                <p class="text-sm text-gray-500">Accept payments via Airtel Money</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="form.airtel_enabled" class="sr-only peer" />
                                <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-500"></div>
                            </label>
                        </div>

                        <div v-if="form.airtel_enabled" class="space-y-4 pt-4 border-t border-gray-200">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Airtel Phone Number</label>
                                <input
                                    v-model="form.airtel_phone"
                                    type="tel"
                                    placeholder="0975123456"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                />
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="checkbox" v-model="form.airtel_sandbox" id="airtel_sandbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded" />
                                <label for="airtel_sandbox" class="text-sm text-gray-600">Sandbox/Test Mode</label>
                            </div>
                        </div>
                    </div>

                    <!-- WhatsApp Orders -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <ChatBubbleLeftRightIcon class="h-6 w-6 text-green-600" aria-hidden="true" />
                            </div>
                            <div class="flex-1">
                                <h2 class="text-lg font-semibold text-gray-900">WhatsApp Orders</h2>
                                <p class="text-sm text-gray-500">Let customers order via WhatsApp</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="form.whatsapp_enabled" class="sr-only peer" />
                                <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                            </label>
                        </div>

                        <div v-if="form.whatsapp_enabled" class="pt-4 border-t border-gray-200">
                            <label class="block text-sm font-medium text-gray-700 mb-1">WhatsApp Number</label>
                            <input
                                v-model="form.whatsapp_number"
                                type="tel"
                                placeholder="+260977123456"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            />
                        </div>
                    </div>

                    <!-- Cash on Delivery -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-gray-100 rounded-lg">
                                <BanknotesIcon class="h-6 w-6 text-gray-600" aria-hidden="true" />
                            </div>
                            <div class="flex-1">
                                <h2 class="text-lg font-semibold text-gray-900">Cash on Delivery</h2>
                                <p class="text-sm text-gray-500">Accept cash payments on delivery</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="form.cod_enabled" class="sr-only peer" />
                                <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                    </div>

                    <!-- Bank Transfer -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <BanknotesIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
                            </div>
                            <div class="flex-1">
                                <h2 class="text-lg font-semibold text-gray-900">Bank Transfer</h2>
                                <p class="text-sm text-gray-500">Accept direct bank transfers</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="form.bank_enabled" class="sr-only peer" />
                                <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <div v-if="form.bank_enabled" class="space-y-4 pt-4 border-t border-gray-200">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Bank Name</label>
                                    <input v-model="form.bank_name" type="text" placeholder="e.g., Zanaco" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                                    <input v-model="form.bank_branch" type="text" placeholder="e.g., Cairo Road" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Account Name</label>
                                <input v-model="form.bank_account_name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Account Number</label>
                                <input v-model="form.bank_account_number" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-end">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Saving...' : 'Save Settings' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
