<template>
  <div class="space-y-6">
    <!-- Compact Profile Card -->
    <CompactProfileCard
      :user="user"
      :current-tier="currentTier"
      :membership-progress="membershipProgress"
      @edit="$emit('edit-profile')"
    />

    <!-- Account Section -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
      <div class="px-4 py-3 bg-gray-50 border-b border-gray-100">
        <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
          <UserCircleIcon class="h-4 w-4 text-gray-500" aria-hidden="true" />
          Account
        </h3>
      </div>
      <div class="divide-y divide-gray-100">
        <MenuButton
          label="My Profile"
          :icon="UserCircleIcon"
          @click="$emit('edit-profile')"
        />
        <MenuButton
          label="Change Password"
          :icon="KeyIcon"
          @click="$emit('change-password')"
        />
        <MenuButton
          label="Verification Status"
          :icon="ShieldCheckIcon"
          :subtitle="verificationBadge"
          @click="$emit('verification')"
        />
      </div>
    </div>

    <!-- Support & Help Section -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
      <div class="px-4 py-3 bg-gray-50 border-b border-gray-100">
        <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
          <ChatBubbleLeftRightIcon class="h-4 w-4 text-gray-500" aria-hidden="true" />
          Support & Help
        </h3>
      </div>
      <div class="divide-y divide-gray-100">
        <MenuButton
          label="Live Support"
          :icon="ChatBubbleLeftEllipsisIcon"
          subtitle="Chat with us now"
          @click="$emit('live-support')"
        />
        <MenuButton
          label="Messages"
          :icon="EnvelopeIcon"
          :badge="messagingData?.unread_count"
          @click="$emit('messages')"
        />
        <MenuButton
          label="Support Tickets"
          :icon="TicketIcon"
          @click="$emit('support-tickets')"
        />
        <MenuButton
          label="Help Center"
          :icon="QuestionMarkCircleIcon"
          @click="$emit('help-center')"
        />
        <MenuButton
          label="FAQs"
          :icon="DocumentTextIcon"
          @click="$emit('faqs')"
        />
      </div>
    </div>

    <!-- Settings Section -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
      <div class="px-4 py-3 bg-gray-50 border-b border-gray-100">
        <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
          <CogIcon class="h-4 w-4 text-gray-500" aria-hidden="true" />
          Settings
        </h3>
      </div>
      <div class="divide-y divide-gray-100">
        <MenuButton
          label="Notifications"
          :icon="BellIcon"
          @click="$emit('notifications')"
        />
        <MenuButton
          label="Language"
          :icon="LanguageIcon"
          subtitle="English"
          @click="$emit('language')"
        />
        <MenuButton
          label="Theme"
          :icon="SunIcon"
          subtitle="Light"
          @click="$emit('theme')"
        />
      </div>
    </div>

    <!-- Share & Promote Section -->
    <div class="bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-600 rounded-xl shadow-lg overflow-hidden">
      <button
        @click="$emit('present-mygrownet')"
        class="w-full px-4 py-4 flex items-center gap-4 text-left hover:bg-white/10 transition-colors active:scale-[0.98]"
      >
        <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
          <PresentationChartBarIcon class="h-6 w-6 text-white" aria-hidden="true" />
        </div>
        <div class="flex-1">
          <p class="text-white font-bold">Present MyGrowNet</p>
          <p class="text-blue-100 text-sm">Show others what we're all about</p>
        </div>
        <ChevronRightIcon class="h-5 w-5 text-white/60" aria-hidden="true" />
      </button>
    </div>

    <!-- App & View Section -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
      <div class="px-4 py-3 bg-gray-50 border-b border-gray-100">
        <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
          <DevicePhoneMobileIcon class="h-4 w-4 text-gray-500" aria-hidden="true" />
          App & View
        </h3>
      </div>
      <div class="divide-y divide-gray-100">
        <MenuButton
          v-if="showInstallButton"
          label="Install App"
          :icon="ArrowDownTrayIcon"
          @click="$emit('install-app')"
        />
        <MenuButton
          label="Switch to Classic View"
          :icon="ComputerDesktopIcon"
          @click="$emit('switch-view')"
        />
        <MenuButton
          label="About MyGrowNet"
          :icon="InformationCircleIcon"
          @click="$emit('about')"
        />
        <MenuButton
          label="Terms & Privacy"
          :icon="DocumentTextIcon"
          @click="$emit('terms')"
        />
      </div>
    </div>

    <!-- Logout Button -->
    <button
      @click="$emit('logout')"
      aria-label="Logout from your account"
      class="w-full flex items-center justify-center gap-2 py-3 px-4 bg-red-50 hover:bg-red-100 border border-red-200 rounded-xl text-red-600 font-semibold transition-colors active:scale-95"
    >
      <ArrowRightOnRectangleIcon class="h-5 w-5" aria-hidden="true" />
      Logout
    </button>

    <!-- Version Info -->
    <div class="text-center text-xs text-gray-400 pb-4">
      MyGrowNet v1.0.0
    </div>
  </div>
</template>

<script setup lang="ts">
import { 
  UserCircleIcon,
  KeyIcon,
  ShieldCheckIcon,
  ChatBubbleLeftRightIcon,
  ChatBubbleLeftEllipsisIcon,
  EnvelopeIcon,
  TicketIcon,
  QuestionMarkCircleIcon,
  DocumentTextIcon,
  CogIcon,
  BellIcon,
  LanguageIcon,
  SunIcon,
  DevicePhoneMobileIcon,
  ArrowDownTrayIcon,
  ComputerDesktopIcon,
  InformationCircleIcon,
  ArrowRightOnRectangleIcon,
  PresentationChartBarIcon,
  ChevronRightIcon,
} from '@heroicons/vue/24/outline';

import CompactProfileCard from './CompactProfileCard.vue';
import MenuButton from './MenuButton.vue';

defineProps<{
  user: any;
  currentTier: string;
  membershipProgress: any;
  messagingData: any;
  verificationBadge?: string;
  showInstallButton: boolean;
}>();

defineEmits<{
  'edit-profile': [];
  'change-password': [];
  'verification': [];
  'live-support': [];
  'messages': [];
  'support-tickets': [];
  'help-center': [];
  'faqs': [];
  'notifications': [];
  'language': [];
  'theme': [];
  'install-app': [];
  'switch-view': [];
  'about': [];
  'terms': [];
  'logout': [];
  'present-mygrownet': [];
}>();
</script>
