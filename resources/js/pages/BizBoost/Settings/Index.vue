<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import {
    Cog6ToothIcon,
    BellIcon,
    GlobeAltIcon,
    CreditCardIcon,
    ShieldCheckIcon,
} from '@heroicons/vue/24/outline';

interface Props {
    settings: {
        notifications: {
            email_new_sale: boolean;
            email_new_customer: boolean;
            email_low_stock: boolean;
            push_enabled: boolean;
        };
        preferences: {
            currency: string;
            timezone: string;
            language: string;
            date_format: string;
        };
    };
    currencies: string[];
    timezones: string[];
}

const props = defineProps<Props>();
const activeTab = ref('notifications');

const notificationForm = useForm({
    email_new_sale: props.settings.notifications.email_new_sale,
    email_new_customer: props.settings.notifications.email_new_customer,
    email_low_stock: props.settings.notifications.email_low_stock,
    push_enabled: props.settings.notifications.push_enabled,
});

const preferencesForm = useForm({
    currency: props.settings.preferences.currency,
    timezone: props.settings.preferences.timezone,
    language: props.settings.preferences.language,
    date_format: props.settings.preferences.date_format,
});

const saveNotifications = () => {
    notificationForm.put('/bizboost/settings/notifications', { preserveScroll: true });
};

const savePreferences = () => {
    preferencesForm.put('/bizboost/settings/preferences', { preserveScroll: true });
};
</script>

<template>
    <Head title="Settings - BizBoost" />
    <BizBoostLayout title="Settings">
        <div class="flex gap-6">
            <!-- Sidebar -->
            <nav class="w-48 flex-shrink-0">
                <ul class="space-y-1">
                    <li>
                        <button
                            @click="activeTab = 'notifications'"
                            :class="[
                                'w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium',
                                activeTab === 'notifications'
                                    ? 'bg-violet-50 text-violet-700'
                                    : 'text-gray-600 hover:bg-gray-50'
                            ]"
                        >
                            <BellIcon class="h-5 w-5" aria-hidden="true" />
                            Notifications
                        </button>
                    </li>
                    <li>
                        <button
                            @click="activeTab = 'preferences'"
                            :class="[
                                'w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium',
                                activeTab === 'preferences'
                                    ? 'bg-violet-50 text-violet-700'
                                    : 'text-gray-600 hover:bg-gray-50'
                            ]"
                        >
                            <GlobeAltIcon class="h-5 w-5" aria-hidden="true" />
                            Preferences
                        </button>
                    </li>
                    <li>
                        <button
                            @click="activeTab = 'billing'"
                            :class="[
                                'w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium',
                                activeTab === 'billing'
                                    ? 'bg-violet-50 text-violet-700'
                                    : 'text-gray-600 hover:bg-gray-50'
                            ]"
                        >
                            <CreditCardIcon class="h-5 w-5" aria-hidden="true" />
                            Billing
                        </button>
                    </li>
                    <li>
                        <button
                            @click="activeTab = 'security'"
                            :class="[
                                'w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium',
                                activeTab === 'security'
                                    ? 'bg-violet-50 text-violet-700'
                                    : 'text-gray-600 hover:bg-gray-50'
                            ]"
                        >
                            <ShieldCheckIcon class="h-5 w-5" aria-hidden="true" />
                            Security
                        </button>
                    </li>
                </ul>
            </nav>

            <!-- Content -->
            <div class="flex-1">
                <!-- Notifications -->
                <div v-if="activeTab === 'notifications'" class="bg-white rounded-xl p-6 shadow-sm ring-1 ring-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Notification Settings</h2>
                    <form @submit.prevent="saveNotifications" class="space-y-4">
                        <label class="flex items-center justify-between">
                            <span class="text-sm text-gray-700">Email on new sale</span>
                            <input type="checkbox" v-model="notificationForm.email_new_sale" class="rounded border-gray-300 text-violet-600" />
                        </label>
                        <label class="flex items-center justify-between">
                            <span class="text-sm text-gray-700">Email on new customer</span>
                            <input type="checkbox" v-model="notificationForm.email_new_customer" class="rounded border-gray-300 text-violet-600" />
                        </label>
                        <label class="flex items-center justify-between">
                            <span class="text-sm text-gray-700">Email on low stock</span>
                            <input type="checkbox" v-model="notificationForm.email_low_stock" class="rounded border-gray-300 text-violet-600" />
                        </label>
                        <label class="flex items-center justify-between">
                            <span class="text-sm text-gray-700">Push notifications</span>
                            <input type="checkbox" v-model="notificationForm.push_enabled" class="rounded border-gray-300 text-violet-600" />
                        </label>
                        <button type="submit" :disabled="notificationForm.processing" class="px-4 py-2 bg-violet-600 text-white rounded-lg text-sm font-medium hover:bg-violet-700 disabled:opacity-50">
                            Save Changes
                        </button>
                    </form>
                </div>

                <!-- Preferences -->
                <div v-if="activeTab === 'preferences'" class="bg-white rounded-xl p-6 shadow-sm ring-1 ring-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Preferences</h2>
                    <form @submit.prevent="savePreferences" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                            <select v-model="preferencesForm.currency" class="w-full rounded-lg border-gray-300">
                                <option v-for="c in currencies" :key="c" :value="c">{{ c }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Timezone</label>
                            <select v-model="preferencesForm.timezone" class="w-full rounded-lg border-gray-300">
                                <option v-for="tz in timezones" :key="tz" :value="tz">{{ tz }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date Format</label>
                            <select v-model="preferencesForm.date_format" class="w-full rounded-lg border-gray-300">
                                <option value="Y-m-d">2025-12-04</option>
                                <option value="d/m/Y">04/12/2025</option>
                                <option value="m/d/Y">12/04/2025</option>
                            </select>
                        </div>
                        <button type="submit" :disabled="preferencesForm.processing" class="px-4 py-2 bg-violet-600 text-white rounded-lg text-sm font-medium hover:bg-violet-700 disabled:opacity-50">
                            Save Changes
                        </button>
                    </form>
                </div>

                <!-- Billing -->
                <div v-if="activeTab === 'billing'" class="bg-white rounded-xl p-6 shadow-sm ring-1 ring-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Billing & Subscription</h2>
                    <p class="text-gray-500">Manage your subscription and payment methods in the subscription page.</p>
                </div>

                <!-- Security -->
                <div v-if="activeTab === 'security'" class="bg-white rounded-xl p-6 shadow-sm ring-1 ring-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Security</h2>
                    <p class="text-gray-500">Two-factor authentication and security settings coming soon.</p>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
