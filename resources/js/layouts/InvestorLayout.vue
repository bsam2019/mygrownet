<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50/30 to-indigo-50/50">
    <!-- Sidebar Navigation -->
    <aside class="fixed inset-y-0 left-0 w-72 bg-white/80 backdrop-blur-xl border-r border-gray-200/50 z-40 hidden lg:block">
      <!-- Logo & Brand -->
      <div class="h-20 flex items-center px-6 border-b border-gray-100">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/25">
            <BuildingOffice2Icon class="h-5 w-5 text-white" aria-hidden="true" />
          </div>
          <div>
            <h1 class="text-lg font-bold text-gray-900">Investor Portal</h1>
            <p class="text-xs text-gray-500">MyGrowNet</p>
          </div>
        </div>
      </div>

      <!-- User Profile Card -->
      <div class="p-4">
        <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl p-4 text-white">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-12 h-12 bg-white/20 backdrop-blur rounded-xl flex items-center justify-center ring-2 ring-white/30">
              <span class="text-lg font-bold">{{ getInitials(investor.name) }}</span>
            </div>
            <div class="flex-1 min-w-0">
              <p class="font-semibold truncate">{{ investor.name }}</p>
              <p class="text-xs text-blue-200 truncate">{{ investor.email }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Navigation Menu -->
      <nav class="px-3 py-2">
        <div class="space-y-1">
          <Link
            :href="route('investor.dashboard')"
            :class="[
              'w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200',
              isActive('dashboard') 
                ? 'text-white bg-gradient-to-r from-blue-600 to-indigo-600 shadow-md shadow-blue-500/25' 
                : 'text-gray-700 hover:bg-gray-100'
            ]"
          >
            <HomeIcon class="h-5 w-5" :class="isActive('dashboard') ? '' : 'text-gray-400'" aria-hidden="true" />
            Dashboard
          </Link>
          
          <Link
            :href="route('investor.documents')"
            :class="[
              'w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200',
              isActive('documents') 
                ? 'text-white bg-gradient-to-r from-blue-600 to-indigo-600 shadow-md shadow-blue-500/25' 
                : 'text-gray-700 hover:bg-gray-100'
            ]"
          >
            <DocumentTextIcon class="h-5 w-5" :class="isActive('documents') ? '' : 'text-gray-400'" aria-hidden="true" />
            Documents
          </Link>
          
          <Link
            :href="route('investor.reports')"
            :class="[
              'w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200',
              isActive('reports') 
                ? 'text-white bg-gradient-to-r from-blue-600 to-indigo-600 shadow-md shadow-blue-500/25' 
                : 'text-gray-700 hover:bg-gray-100'
            ]"
          >
            <ChartBarIcon class="h-5 w-5" :class="isActive('reports') ? '' : 'text-gray-400'" aria-hidden="true" />
            Reports
          </Link>
          
          <Link
            :href="route('investor.messages')"
            :class="[
              'w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 relative',
              isActive('messages') 
                ? 'text-white bg-gradient-to-r from-blue-600 to-indigo-600 shadow-md shadow-blue-500/25' 
                : 'text-gray-700 hover:bg-gray-100'
            ]"
          >
            <ChatBubbleLeftRightIcon class="h-5 w-5" :class="isActive('messages') ? '' : 'text-gray-400'" aria-hidden="true" />
            Messages
            <span v-if="unreadMessages > 0" class="ml-auto w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
              {{ unreadMessages > 9 ? '9+' : unreadMessages }}
            </span>
          </Link>
          
          <Link
            :href="route('investor.announcements')"
            :class="[
              'w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200',
              isActive('announcements') 
                ? 'text-white bg-gradient-to-r from-blue-600 to-indigo-600 shadow-md shadow-blue-500/25' 
                : 'text-gray-700 hover:bg-gray-100'
            ]"
          >
            <MegaphoneIcon class="h-5 w-5" :class="isActive('announcements') ? '' : 'text-gray-400'" aria-hidden="true" />
            Announcements
          </Link>
          
          <Link
            :href="route('investor.settings')"
            :class="[
              'w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200',
              isActive('settings') 
                ? 'text-white bg-gradient-to-r from-blue-600 to-indigo-600 shadow-md shadow-blue-500/25' 
                : 'text-gray-700 hover:bg-gray-100'
            ]"
          >
            <Cog6ToothIcon class="h-5 w-5" :class="isActive('settings') ? '' : 'text-gray-400'" aria-hidden="true" />
            Settings
          </Link>
        </div>
      </nav>

      <!-- Logout Button -->
      <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-100 bg-white/50">
        <Link
          :href="route('investor.logout')"
          method="post"
          as="button"
          class="w-full flex items-center justify-center gap-2 px-4 py-3 text-sm font-medium text-gray-600 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all duration-200"
        >
          <ArrowRightOnRectangleIcon class="h-5 w-5" aria-hidden="true" />
          Sign Out
        </Link>
      </div>
    </aside>

    <!-- Mobile Header -->
    <header class="lg:hidden sticky top-0 z-30 bg-white/80 backdrop-blur-xl border-b border-gray-200/50">
      <div class="flex items-center justify-between px-4 py-3">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center">
            <BuildingOffice2Icon class="h-5 w-5 text-white" aria-hidden="true" />
          </div>
          <span class="font-semibold text-gray-900">{{ pageTitle }}</span>
        </div>
        <button
          @click="mobileMenuOpen = !mobileMenuOpen"
          class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg"
          :aria-label="mobileMenuOpen ? 'Close menu' : 'Open menu'"
        >
          <Bars3Icon v-if="!mobileMenuOpen" class="h-6 w-6" aria-hidden="true" />
          <XMarkIcon v-else class="h-6 w-6" aria-hidden="true" />
        </button>
      </div>
    </header>

    <!-- Mobile Menu Overlay -->
    <Transition
      enter-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="mobileMenuOpen" class="lg:hidden fixed inset-0 z-40 bg-black/50" @click="mobileMenuOpen = false"></div>
    </Transition>

    <!-- Mobile Menu Slide-in -->
    <Transition
      enter-active-class="transition-transform duration-300"
      enter-from-class="-translate-x-full"
      enter-to-class="translate-x-0"
      leave-active-class="transition-transform duration-300"
      leave-from-class="translate-x-0"
      leave-to-class="-translate-x-full"
    >
      <aside v-if="mobileMenuOpen" class="lg:hidden fixed inset-y-0 left-0 w-72 bg-white z-50 shadow-2xl">
        <div class="h-16 flex items-center justify-between px-4 border-b border-gray-100">
          <span class="font-semibold text-gray-900">Menu</span>
          <button @click="mobileMenuOpen = false" class="p-2 hover:bg-gray-100 rounded-lg" aria-label="Close menu">
            <XMarkIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
          </button>
        </div>
        <nav class="p-3 space-y-1">
          <Link :href="route('investor.dashboard')" :class="getMobileNavClass('dashboard')">
            <HomeIcon class="h-5 w-5" aria-hidden="true" />
            Dashboard
          </Link>
          <Link :href="route('investor.documents')" :class="getMobileNavClass('documents')">
            <DocumentTextIcon class="h-5 w-5" aria-hidden="true" />
            Documents
          </Link>
          <Link :href="route('investor.reports')" :class="getMobileNavClass('reports')">
            <ChartBarIcon class="h-5 w-5" aria-hidden="true" />
            Reports
          </Link>
          <Link :href="route('investor.messages')" :class="getMobileNavClass('messages')">
            <ChatBubbleLeftRightIcon class="h-5 w-5" aria-hidden="true" />
            Messages
            <span v-if="unreadMessages > 0" class="ml-auto w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
              {{ unreadMessages > 9 ? '9+' : unreadMessages }}
            </span>
          </Link>
          <Link :href="route('investor.announcements')" :class="getMobileNavClass('announcements')">
            <MegaphoneIcon class="h-5 w-5" aria-hidden="true" />
            Announcements
          </Link>
          <Link :href="route('investor.settings')" :class="getMobileNavClass('settings')">
            <Cog6ToothIcon class="h-5 w-5" aria-hidden="true" />
            Settings
          </Link>
          <Link :href="route('investor.logout')" method="post" as="button" class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-red-600 hover:bg-red-50 rounded-xl">
            <ArrowRightOnRectangleIcon class="h-5 w-5" aria-hidden="true" />
            Sign Out
          </Link>
        </nav>
      </aside>
    </Transition>

    <!-- Main Content Area -->
    <main class="lg:pl-72">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-8">
        <slot />
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import {
  HomeIcon,
  DocumentTextIcon,
  ChartBarIcon,
  ChatBubbleLeftRightIcon,
  Cog6ToothIcon,
  BuildingOffice2Icon,
  ArrowRightOnRectangleIcon,
  Bars3Icon,
  XMarkIcon,
  MegaphoneIcon,
} from '@heroicons/vue/24/outline';

interface Investor {
  id: number;
  name: string;
  email: string;
}

const props = defineProps<{
  investor: Investor;
  pageTitle?: string;
  activePage?: string;
  unreadMessages?: number;
}>();

const mobileMenuOpen = ref(false);

const getInitials = (name: string): string => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
};

const isActive = (page: string): boolean => {
  return props.activePage === page;
};

const getMobileNavClass = (page: string): string => {
  const base = 'flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl';
  return isActive(page)
    ? `${base} text-white bg-gradient-to-r from-blue-600 to-indigo-600`
    : `${base} text-gray-700 hover:bg-gray-100`;
};
</script>
