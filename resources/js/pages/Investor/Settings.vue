<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    Cog6ToothIcon,
    BellIcon,
    EnvelopeIcon,
    ShieldCheckIcon,
    UserCircleIcon,
    CheckIcon,
} from '@heroicons/vue/24/outline';

interface NotificationPreferences {
    email_announcements: boolean;
    email_financial_reports: boolean;
    email_dividends: boolean;
    email_meetings: boolean;
    email_messages: boolean;
    email_urgent_only: boolean;
    digest_frequency: string;
}

interface Props {
    investor: {
        id: number;
        name: string;
        email: string;
    };
    preferences: NotificationPreferences;
}

const props = defineProps<Props>();

const form = ref<NotificationPreferences>({
    email_announcements: props.preferences?.email_announcements ?? true,
    email_financial_reports: props.preferences?.email_financial_reports ?? true,
    email_dividends: props.preferences?.email_dividends ?? true,
    email_meetings: props.preferences?.email_meetings ?? true,
    email_messages: props.preferences?.email_messages ?? true,
    email_urgent_only: props.preferences?.email_urgent_only ?? false,
    digest_frequency: props.preferences?.digest_frequency ?? 'immediate',
});

const saving = ref(false);
const saved = ref(false);

const digestOptions = [
    { value: 'immediate', label: 'Immediately', description: 'Get notified as soon as something happens' },
    { value: 'daily', label: 'Daily Digest', description: 'Receive a summary once per day' },
    { value: 'weekly', label: 'Weekly Digest', description: 'Receive a summary once per week' },
    { value: 'none', label: 'No Emails', description: 'Only view updates in the portal' },
];

const emailPreferences = [
    { key: 'email_announcements', label: 'Announcements', description: 'Company news and updates' },
    { key: 'email_financial_reports', label: 'Financial Reports', description: 'Monthly, quarterly, and annual reports' },
    { key: 'email_dividends', label: 'Dividend Notifications', description: 'Dividend declarations and payments' },
    { key: 'email_meetings', label: 'Meeting Notices', description: 'Shareholder meetings and events' },
    { key: 'email_messages', label: 'Direct Messages', description: 'Messages from the investor relations team' },
];

async function savePreferences() {
    saving.value = true;
    saved.value = false;

    router.post('/investor/settings/notifications', form.value, {
        preserveScroll: true,
        onSuccess: () => {
            saved.value = true;
            setTimeout(() => { saved.value = false; }, 3000);
        },
        onFinish: () => {
            saving.value = false;
        },
    });
}
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-white border-b border-gray-200">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <Cog6ToothIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Settings</h1>
                        <p class="text-sm text-gray-500">Manage your account preferences</p>
                    </div>
                </div>
            </div>
        </header>

        <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Profile Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <UserCircleIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                        <h2 class="text-lg font-semibold text-gray-900">Profile Information</h2>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Name</label>
                            <p class="mt-1 text-gray-900">{{ investor.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Email</label>
                            <p class="mt-1 text-gray-900">{{ investor.email }}</p>
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-gray-500">
                        Contact investor relations to update your profile information.
                    </p>
                </div>
            </div>

            <!-- Notification Preferences -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <BellIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                            <h2 class="text-lg font-semibold text-gray-900">Email Notifications</h2>
                        </div>
                        <div v-if="saved" class="flex items-center gap-1 text-green-600 text-sm">
                            <CheckIcon class="h-4 w-4" aria-hidden="true" />
                            Saved
                        </div>
                    </div>
                </div>

                <div class="p-6 space-y-6">
                    <!-- Digest Frequency -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            Email Frequency
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label
                                v-for="option in digestOptions"
                                :key="option.value"
                                class="relative flex items-start p-4 border rounded-lg cursor-pointer transition-colors"
                                :class="form.digest_frequency === option.value 
                                    ? 'border-blue-500 bg-blue-50' 
                                    : 'border-gray-200 hover:border-gray-300'"
                            >
                                <input
                                    type="radio"
                                    v-model="form.digest_frequency"
                                    :value="option.value"
                                    class="sr-only"
                                />
                                <div>
                                    <span class="block text-sm font-medium text-gray-900">
                                        {{ option.label }}
                                    </span>
                                    <span class="block text-xs text-gray-500 mt-0.5">
                                        {{ option.description }}
                                    </span>
                                </div>
                                <div
                                    v-if="form.digest_frequency === option.value"
                                    class="absolute top-2 right-2"
                                >
                                    <CheckIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Email Types -->
                    <div v-if="form.digest_frequency !== 'none'">
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            Notification Types
                        </label>
                        <div class="space-y-3">
                            <label
                                v-for="pref in emailPreferences"
                                :key="pref.key"
                                class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer"
                            >
                                <div class="flex items-center gap-3">
                                    <EnvelopeIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                                    <div>
                                        <span class="block text-sm font-medium text-gray-900">
                                            {{ pref.label }}
                                        </span>
                                        <span class="block text-xs text-gray-500">
                                            {{ pref.description }}
                                        </span>
                                    </div>
                                </div>
                                <input
                                    type="checkbox"
                                    v-model="form[pref.key as keyof NotificationPreferences]"
                                    class="h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                />
                            </label>
                        </div>
                    </div>

                    <!-- Urgent Only Mode -->
                    <div v-if="form.digest_frequency !== 'none'" class="pt-4 border-t border-gray-200">
                        <label class="flex items-center justify-between p-4 bg-amber-50 border border-amber-200 rounded-lg cursor-pointer">
                            <div class="flex items-center gap-3">
                                <ShieldCheckIcon class="h-5 w-5 text-amber-600" aria-hidden="true" />
                                <div>
                                    <span class="block text-sm font-medium text-gray-900">
                                        Urgent Only Mode
                                    </span>
                                    <span class="block text-xs text-gray-500">
                                        Only receive emails for urgent announcements
                                    </span>
                                </div>
                            </div>
                            <input
                                type="checkbox"
                                v-model="form.email_urgent_only"
                                class="h-5 w-5 text-amber-600 border-gray-300 rounded focus:ring-amber-500"
                            />
                        </label>
                    </div>

                    <!-- Save Button -->
                    <div class="pt-4">
                        <button
                            @click="savePreferences"
                            :disabled="saving"
                            class="w-full sm:w-auto px-6 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            {{ saving ? 'Saving...' : 'Save Preferences' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Back Link -->
            <div class="mt-6 text-center">
                <a
                    href="/investor/dashboard"
                    class="text-sm text-blue-600 hover:text-blue-700"
                >
                    ← Back to Dashboard
                </a>
            </div>
        </main>
    </div>
</template>
