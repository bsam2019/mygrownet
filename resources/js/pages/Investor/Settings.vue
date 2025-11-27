<template>
  <InvestorLayout :investor="investor" page-title="Settings" active-page="settings">
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Settings</h1>
      <p class="mt-1 text-gray-600">Manage your account preferences and notifications</p>
    </div>

    <!-- Profile Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
      <div class="p-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
            <UserCircleIcon class="h-5 w-5 text-white" aria-hidden="true" />
          </div>
          <div>
            <h2 class="font-semibold text-gray-900">Profile Information</h2>
            <p class="text-sm text-gray-500">Your account details</p>
          </div>
        </div>
      </div>
      <div class="p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div class="bg-gray-50 rounded-xl p-4">
            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Name</label>
            <p class="text-gray-900 font-medium">{{ investor.name }}</p>
          </div>
          <div class="bg-gray-50 rounded-xl p-4">
            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Email</label>
            <p class="text-gray-900 font-medium">{{ investor.email }}</p>
          </div>
        </div>
        <p class="mt-4 text-sm text-gray-500 flex items-center gap-2">
          <InformationCircleIcon class="h-4 w-4" aria-hidden="true" />
          Contact investor relations to update your profile information.
        </p>
      </div>
    </div>

    <!-- Notification Preferences -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="p-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center">
              <BellIcon class="h-5 w-5 text-white" aria-hidden="true" />
            </div>
            <div>
              <h2 class="font-semibold text-gray-900">Email Notifications</h2>
              <p class="text-sm text-gray-500">Choose what updates you receive</p>
            </div>
          </div>
          <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
          >
            <div v-if="saved" class="flex items-center gap-1.5 text-emerald-600 text-sm font-medium bg-emerald-50 px-3 py-1.5 rounded-full">
              <CheckIcon class="h-4 w-4" aria-hidden="true" />
              Saved
            </div>
          </Transition>
        </div>
      </div>

      <div class="p-6 space-y-6">
        <!-- Digest Frequency -->
        <div>
          <label class="block text-sm font-semibold text-gray-900 mb-3">Email Frequency</label>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <label
              v-for="option in digestOptions"
              :key="option.value"
              class="relative flex items-start p-4 border-2 rounded-xl cursor-pointer transition-all hover:border-gray-300"
              :class="form.digest_frequency === option.value ? 'border-blue-500 bg-blue-50/50' : 'border-gray-200'"
            >
              <input type="radio" v-model="form.digest_frequency" :value="option.value" class="sr-only" />
              <div class="flex-1">
                <span class="block text-sm font-medium text-gray-900">{{ option.label }}</span>
                <span class="block text-xs text-gray-500 mt-0.5">{{ option.description }}</span>
              </div>
              <div v-if="form.digest_frequency === option.value" class="absolute top-3 right-3">
                <div class="w-5 h-5 bg-blue-600 rounded-full flex items-center justify-center">
                  <CheckIcon class="h-3 w-3 text-white" aria-hidden="true" />
                </div>
              </div>
            </label>
          </div>
        </div>

        <!-- Email Types -->
        <div v-if="form.digest_frequency !== 'none'">
          <label class="block text-sm font-semibold text-gray-900 mb-3">Notification Types</label>
          <div class="space-y-3">
            <label
              v-for="pref in emailPreferences"
              :key="pref.key"
              class="flex items-center justify-between p-4 bg-gray-50 border border-gray-100 rounded-xl hover:bg-gray-100 cursor-pointer transition-colors"
            >
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center border border-gray-200">
                  <EnvelopeIcon class="h-4 w-4 text-gray-500" aria-hidden="true" />
                </div>
                <div>
                  <span class="block text-sm font-medium text-gray-900">{{ pref.label }}</span>
                  <span class="block text-xs text-gray-500">{{ pref.description }}</span>
                </div>
              </div>
              <div class="relative">
                <input
                  type="checkbox"
                  v-model="form[pref.key as keyof NotificationPreferences]"
                  class="sr-only peer"
                />
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
              </div>
            </label>
          </div>
        </div>

        <!-- Urgent Only Mode -->
        <div v-if="form.digest_frequency !== 'none'" class="pt-4 border-t border-gray-100">
          <label class="flex items-center justify-between p-4 bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl cursor-pointer">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center">
                <ShieldCheckIcon class="h-5 w-5 text-amber-600" aria-hidden="true" />
              </div>
              <div>
                <span class="block text-sm font-medium text-gray-900">Urgent Only Mode</span>
                <span class="block text-xs text-gray-500">Only receive emails for urgent announcements</span>
              </div>
            </div>
            <div class="relative">
              <input type="checkbox" v-model="form.email_urgent_only" class="sr-only peer" />
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-amber-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
            </div>
          </label>
        </div>

        <!-- Save Button -->
        <div class="pt-4">
          <button
            @click="savePreferences"
            :disabled="saving"
            class="w-full sm:w-auto px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium rounded-xl hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg shadow-blue-500/25"
          >
            {{ saving ? 'Saving...' : 'Save Preferences' }}
          </button>
        </div>
      </div>
    </div>
  </InvestorLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import InvestorLayout from '@/layouts/InvestorLayout.vue';
import {
  UserCircleIcon,
  BellIcon,
  EnvelopeIcon,
  ShieldCheckIcon,
  CheckIcon,
  InformationCircleIcon,
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

interface Investor {
  id: number;
  name: string;
  email: string;
}

const props = defineProps<{
  investor: Investor;
  preferences: NotificationPreferences;
}>();

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

const savePreferences = () => {
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
};
</script>
