<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import { CogIcon } from '@heroicons/vue/24/outline';

interface Business {
    id: number;
    name: string;
    timezone: string | null;
    locale: string | null;
    currency: string | null;
    settings: Record<string, any> | null;
}

interface Props {
    business: Business;
}

const props = defineProps<Props>();

const form = useForm({
    timezone: props.business.timezone || 'Africa/Lusaka',
    locale: props.business.locale || 'en',
    currency: props.business.currency || 'ZMW',
    settings: props.business.settings || {
        notifications_email: true,
        notifications_sms: false,
        auto_publish: false,
        show_prices: true,
    },
});

const submit = () => {
    form.put(route('bizboost.business.settings.update'), {
        preserveScroll: true,
    });
};

const timezones = [
    { value: 'Africa/Lusaka', label: 'Lusaka (CAT)' },
    { value: 'Africa/Johannesburg', label: 'Johannesburg (SAST)' },
    { value: 'Africa/Nairobi', label: 'Nairobi (EAT)' },
    { value: 'UTC', label: 'UTC' },
];

const currencies = [
    { value: 'ZMW', label: 'Zambian Kwacha (K)' },
    { value: 'USD', label: 'US Dollar ($)' },
    { value: 'ZAR', label: 'South African Rand (R)' },
];

const locales = [
    { value: 'en', label: 'English' },
];
</script>

<template>
    <Head title="Settings - BizBoost" />
    <BizBoostLayout title="Settings">
        <div class="max-w-2xl mx-auto">
            <form @submit.prevent="submit" class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-6 space-y-6">
                <div class="flex items-center gap-3 pb-4 border-b border-gray-200">
                    <div class="p-2 bg-violet-100 rounded-lg">
                        <CogIcon class="h-6 w-6 text-violet-600" aria-hidden="true" />
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Business Settings</h2>
                        <p class="text-sm text-gray-500">Configure your business preferences</p>
                    </div>
                </div>

                <!-- Regional Settings -->
                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-gray-900">Regional Settings</h3>
                    
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Timezone</label>
                            <select
                                v-model="form.timezone"
                                class="w-full rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500"
                            >
                                <option v-for="tz in timezones" :key="tz.value" :value="tz.value">
                                    {{ tz.label }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                            <select
                                v-model="form.currency"
                                class="w-full rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500"
                            >
                                <option v-for="curr in currencies" :key="curr.value" :value="curr.value">
                                    {{ curr.label }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Language</label>
                            <select
                                v-model="form.locale"
                                class="w-full rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500"
                            >
                                <option v-for="loc in locales" :key="loc.value" :value="loc.value">
                                    {{ loc.label }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Notification Settings -->
                <div class="space-y-4 pt-4 border-t border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
                    
                    <div class="space-y-3">
                        <label class="flex items-center gap-3">
                            <input
                                v-model="form.settings.notifications_email"
                                type="checkbox"
                                class="rounded border-gray-300 text-violet-600 focus:ring-violet-500"
                            />
                            <span class="text-sm text-gray-700">Email notifications</span>
                        </label>

                        <label class="flex items-center gap-3">
                            <input
                                v-model="form.settings.notifications_sms"
                                type="checkbox"
                                class="rounded border-gray-300 text-violet-600 focus:ring-violet-500"
                            />
                            <span class="text-sm text-gray-700">SMS notifications</span>
                        </label>
                    </div>
                </div>

                <!-- Publishing Settings -->
                <div class="space-y-4 pt-4 border-t border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Publishing</h3>
                    
                    <div class="space-y-3">
                        <label class="flex items-center gap-3">
                            <input
                                v-model="form.settings.auto_publish"
                                type="checkbox"
                                class="rounded border-gray-300 text-violet-600 focus:ring-violet-500"
                            />
                            <div>
                                <span class="text-sm text-gray-700">Auto-publish posts</span>
                                <p class="text-xs text-gray-500">Automatically publish scheduled posts to connected platforms</p>
                            </div>
                        </label>

                        <label class="flex items-center gap-3">
                            <input
                                v-model="form.settings.show_prices"
                                type="checkbox"
                                class="rounded border-gray-300 text-violet-600 focus:ring-violet-500"
                            />
                            <div>
                                <span class="text-sm text-gray-700">Show prices on mini-website</span>
                                <p class="text-xs text-gray-500">Display product prices publicly</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-200">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-2 bg-violet-600 text-white rounded-lg font-medium hover:bg-violet-700 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Saving...' : 'Save Settings' }}
                    </button>
                </div>
            </form>
        </div>
    </BizBoostLayout>
</template>
