<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50/30 to-indigo-50/50">
    <!-- Sidebar Navigation -->
    <aside 
      :class="[
        'fixed inset-y-0 left-0 bg-white/80 backdrop-blur-xl border-r border-gray-200/50 z-40 hidden lg:flex flex-col transition-all duration-300',
        sidebarCollapsed ? 'w-20' : 'w-72'
      ]"
    >
      <!-- Logo & Brand -->
      <div class="h-16 flex items-center px-4 border-b border-gray-100 flex-shrink-0">
        <div class="flex items-center gap-3 overflow-hidden">
          <img src="/logo.png" alt="MyGrowNet Logo" class="h-9 w-9 object-contain flex-shrink-0" />
          <div v-if="!sidebarCollapsed" class="transition-opacity duration-200">
            <h1 class="text-lg font-bold text-gray-900 whitespace-nowrap">Investor Portal</h1>
            <p class="text-xs text-gray-500">MyGrowNet</p>
          </div>
        </div>
      </div>

      <!-- Navigation Menu -->
      <nav class="flex-1 px-3 py-4 overflow-y-auto">
        <div class="space-y-1">
          <Link
            :href="route('investor.dashboard')"
            :class="getNavClass('dashboard')"
            class="nav-tooltip-trigger"
            :data-tooltip="sidebarCollapsed ? 'Dashboard' : undefined"
          >
            <HomeIcon class="h-5 w-5 flex-shrink-0" :class="isActive('dashboard') ? '' : 'text-gray-400'" aria-hidden="true" />
            <span v-if="!sidebarCollapsed">Dashboard</span>
            <span v-if="sidebarCollapsed" class="nav-tooltip">Dashboard</span>
          </Link>
          
          <Link
            :href="route('investor.documents')"
            :class="getNavClass('documents')"
            class="nav-tooltip-trigger"
          >
            <DocumentTextIcon class="h-5 w-5 flex-shrink-0" :class="isActive('documents') ? '' : 'text-gray-400'" aria-hidden="true" />
            <span v-if="!sidebarCollapsed">Documents</span>
            <span v-if="sidebarCollapsed" class="nav-tooltip">Documents</span>
          </Link>
          
          <Link
            :href="route('investor.reports')"
            :class="getNavClass('reports')"
            class="nav-tooltip-trigger"
          >
            <ChartBarIcon class="h-5 w-5 flex-shrink-0" :class="isActive('reports') ? '' : 'text-gray-400'" aria-hidden="true" />
            <span v-if="!sidebarCollapsed">Reports</span>
            <span v-if="sidebarCollapsed" class="nav-tooltip">Reports</span>
          </Link>
          
          <Link
            :href="route('investor.messages')"
            :class="getNavClass('messages')"
            class="nav-tooltip-trigger"
          >
            <ChatBubbleLeftRightIcon class="h-5 w-5 flex-shrink-0" :class="isActive('messages') ? '' : 'text-gray-400'" aria-hidden="true" />
            <span v-if="!sidebarCollapsed">Messages</span>
            <span v-if="unreadMessagesCount > 0" :class="['w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center', sidebarCollapsed ? 'absolute -top-1 -right-1' : 'ml-auto']">
              {{ unreadMessagesCount > 9 ? '9+' : unreadMessagesCount }}
            </span>
            <span v-if="sidebarCollapsed" class="nav-tooltip">Messages</span>
          </Link>
          
          <Link
            :href="route('investor.announcements')"
            :class="getNavClass('announcements')"
            class="nav-tooltip-trigger"
          >
            <MegaphoneIcon class="h-5 w-5 flex-shrink-0" :class="isActive('announcements') ? '' : 'text-gray-400'" aria-hidden="true" />
            <span v-if="!sidebarCollapsed">Announcements</span>
            <span v-if="sidebarCollapsed" class="nav-tooltip">Announcements</span>
          </Link>
        </div>

        <!-- Phase 2: Analytics & Engagement -->
        <div class="mt-4 pt-4 border-t border-gray-200">
          <p v-if="!sidebarCollapsed" class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Analytics</p>
          <div class="space-y-1">
            <Link :href="route('investor.analytics')" :class="getNavClass('analytics')" class="nav-tooltip-trigger">
              <PresentationChartLineIcon class="h-5 w-5 flex-shrink-0" :class="isActive('analytics') ? '' : 'text-gray-400'" aria-hidden="true" />
              <span v-if="!sidebarCollapsed">Analytics</span>
              <span v-if="sidebarCollapsed" class="nav-tooltip">Analytics</span>
            </Link>
            <Link :href="route('investor.voting')" :class="getNavClass('voting')" class="nav-tooltip-trigger">
              <HandRaisedIcon class="h-5 w-5 flex-shrink-0" :class="isActive('voting') ? '' : 'text-gray-400'" aria-hidden="true" />
              <span v-if="!sidebarCollapsed">Voting</span>
              <span v-if="sidebarCollapsed" class="nav-tooltip">Voting</span>
            </Link>
            <Link :href="route('investor.questions')" :class="getNavClass('questions')" class="nav-tooltip-trigger">
              <QuestionMarkCircleIcon class="h-5 w-5 flex-shrink-0" :class="isActive('questions') ? '' : 'text-gray-400'" aria-hidden="true" />
              <span v-if="!sidebarCollapsed">Q&A</span>
              <span v-if="sidebarCollapsed" class="nav-tooltip">Q&A</span>
            </Link>
            <Link :href="route('investor.feedback')" :class="getNavClass('feedback')" class="nav-tooltip-trigger">
              <ChatBubbleBottomCenterTextIcon class="h-5 w-5 flex-shrink-0" :class="isActive('feedback') ? '' : 'text-gray-400'" aria-hidden="true" />
              <span v-if="!sidebarCollapsed">Feedback</span>
              <span v-if="sidebarCollapsed" class="nav-tooltip">Feedback</span>
            </Link>
          </div>
        </div>

        <!-- Phase 3: Liquidity & Community -->
        <div class="mt-4 pt-4 border-t border-gray-200">
          <p v-if="!sidebarCollapsed" class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Community</p>
          <div class="space-y-1">
            <Link :href="route('investor.share-transfer')" :class="getNavClass('share-transfer')" class="nav-tooltip-trigger">
              <ArrowsRightLeftIcon class="h-5 w-5 flex-shrink-0" :class="isActive('share-transfer') ? '' : 'text-gray-400'" aria-hidden="true" />
              <span v-if="!sidebarCollapsed">Share Transfer</span>
              <span v-if="sidebarCollapsed" class="nav-tooltip">Share Transfer</span>
            </Link>
            <Link :href="route('investor.liquidity-events')" :class="getNavClass('liquidity-events')" class="nav-tooltip-trigger">
              <BanknotesIcon class="h-5 w-5 flex-shrink-0" :class="isActive('liquidity-events') ? '' : 'text-gray-400'" aria-hidden="true" />
              <span v-if="!sidebarCollapsed">Liquidity Events</span>
              <span v-if="sidebarCollapsed" class="nav-tooltip">Liquidity Events</span>
            </Link>
            <Link :href="route('investor.forum')" :class="getNavClass('forum')" class="nav-tooltip-trigger">
              <UserGroupIcon class="h-5 w-5 flex-shrink-0" :class="isActive('forum') ? '' : 'text-gray-400'" aria-hidden="true" />
              <span v-if="!sidebarCollapsed">Forum</span>
              <span v-if="sidebarCollapsed" class="nav-tooltip">Forum</span>
            </Link>
            <Link :href="route('investor.directory')" :class="getNavClass('directory')" class="nav-tooltip-trigger">
              <UsersIcon class="h-5 w-5 flex-shrink-0" :class="isActive('directory') ? '' : 'text-gray-400'" aria-hidden="true" />
              <span v-if="!sidebarCollapsed">Directory</span>
              <span v-if="sidebarCollapsed" class="nav-tooltip">Directory</span>
            </Link>
          </div>
        </div>

        <!-- Settings -->
        <div class="mt-4 pt-4 border-t border-gray-200">
          <Link :href="route('investor.settings')" :class="getNavClass('settings')" class="nav-tooltip-trigger">
            <Cog6ToothIcon class="h-5 w-5 flex-shrink-0" :class="isActive('settings') ? '' : 'text-gray-400'" aria-hidden="true" />
            <span v-if="!sidebarCollapsed">Settings</span>
            <span v-if="sidebarCollapsed" class="nav-tooltip">Settings</span>
          </Link>
        </div>
      </nav>

      <!-- Logout Button -->
      <div class="p-3 border-t border-gray-100 bg-white/50 flex-shrink-0">
        <Link
          :href="route('investor.logout')"
          method="post"
          as="button"
          :class="[
            'w-full flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-gray-600 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all duration-200 nav-tooltip-trigger',
            sidebarCollapsed ? 'justify-center' : ''
          ]"
        >
          <ArrowRightOnRectangleIcon class="h-5 w-5 flex-shrink-0" aria-hidden="true" />
          <span v-if="!sidebarCollapsed">Sign Out</span>
          <span v-if="sidebarCollapsed" class="nav-tooltip">Sign Out</span>
        </Link>
      </div>
    </aside>

    <!-- Desktop Top Bar -->
    <div 
      :class="[
        'hidden lg:block fixed top-0 right-0 h-16 bg-white/80 backdrop-blur-xl border-b border-gray-200/50 z-30 transition-all duration-300',
        sidebarCollapsed ? 'left-20' : 'left-72'
      ]"
    >
      <div class="flex items-center justify-between h-full px-6">
        <div class="flex items-center gap-3">
          <!-- Sidebar Toggle Button -->
          <button
            @click="toggleSidebar"
            class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
            :aria-label="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
          >
            <ChevronLeftIcon v-if="!sidebarCollapsed" class="h-5 w-5" aria-hidden="true" />
            <ChevronRightIcon v-else class="h-5 w-5" aria-hidden="true" />
          </button>
          <h2 class="text-lg font-semibold text-gray-900">{{ pageTitle || 'Dashboard' }}</h2>
        </div>
        <div class="flex items-center gap-4">
          <!-- Notification Bell -->
          <div class="relative">
            <button
              @click="showNotificationDropdown = !showNotificationDropdown"
              class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
              aria-label="View notifications"
            >
              <BellIcon class="h-6 w-6" aria-hidden="true" />
              <span 
                v-if="totalNotifications > 0" 
                class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center"
              >
                {{ totalNotifications > 9 ? '9+' : totalNotifications }}
              </span>
            </button>
            
            <!-- Notification Dropdown -->
            <Transition
              enter-active-class="transition ease-out duration-200"
              enter-from-class="opacity-0 translate-y-1"
              enter-to-class="opacity-100 translate-y-0"
              leave-active-class="transition ease-in duration-150"
              leave-from-class="opacity-100 translate-y-0"
              leave-to-class="opacity-0 translate-y-1"
            >
              <div 
                v-if="showNotificationDropdown" 
                class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden"
              >
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                  <h3 class="font-semibold text-gray-900">Notifications</h3>
                </div>
                <div class="max-h-96 overflow-y-auto">
                  <Link 
                    v-if="unreadMessagesCount > 0"
                    :href="route('investor.messages')"
                    class="flex items-start gap-3 px-4 py-3 hover:bg-blue-50 transition-colors border-b border-gray-100"
                  >
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                      <ChatBubbleLeftRightIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-medium text-gray-900">New Messages</p>
                      <p class="text-xs text-gray-500">You have {{ unreadMessagesCount }} unread message{{ unreadMessagesCount > 1 ? 's' : '' }}</p>
                    </div>
                    <span class="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0 mt-2"></span>
                  </Link>
                  
                  <Link 
                    v-if="unreadAnnouncementsCount > 0"
                    :href="route('investor.announcements')"
                    class="flex items-start gap-3 px-4 py-3 hover:bg-amber-50 transition-colors border-b border-gray-100"
                  >
                    <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0">
                      <MegaphoneIcon class="h-5 w-5 text-amber-600" aria-hidden="true" />
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-medium text-gray-900">New Announcements</p>
                      <p class="text-xs text-gray-500">{{ unreadAnnouncementsCount }} unread announcement{{ unreadAnnouncementsCount > 1 ? 's' : '' }}</p>
                    </div>
                    <span class="w-2 h-2 bg-amber-500 rounded-full flex-shrink-0 mt-2"></span>
                  </Link>
                  
                  <div v-if="totalNotifications === 0" class="px-4 py-8 text-center">
                    <BellIcon class="h-12 w-12 text-gray-300 mx-auto mb-3" aria-hidden="true" />
                    <p class="text-sm text-gray-500">No new notifications</p>
                  </div>
                </div>
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
                  <Link 
                    :href="route('investor.announcements')"
                    class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                  >
                    View all announcements →
                  </Link>
                </div>
              </div>
            </Transition>
          </div>
          
          <!-- User Dropdown -->
          <div class="relative">
            <button
              @click="showUserDropdown = !showUserDropdown"
              class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded-lg transition-colors"
              aria-label="User menu"
            >
              <div class="w-9 h-9 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center text-white font-semibold text-sm">
                {{ getInitials(investor.name) }}
              </div>
              <div class="hidden md:block text-left">
                <p class="text-sm font-medium text-gray-900">{{ investor.name }}</p>
                <p class="text-xs text-gray-500">{{ investor.email }}</p>
              </div>
              <ChevronDownIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
            </button>
            
            <!-- User Dropdown Menu -->
            <Transition
              enter-active-class="transition ease-out duration-200"
              enter-from-class="opacity-0 translate-y-1"
              enter-to-class="opacity-100 translate-y-0"
              leave-active-class="transition ease-in duration-150"
              leave-from-class="opacity-100 translate-y-0"
              leave-to-class="opacity-0 translate-y-1"
            >
              <div 
                v-if="showUserDropdown" 
                class="absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden z-50"
              >
                <div class="px-4 py-3 bg-gradient-to-br from-blue-600 to-indigo-700 text-white">
                  <p class="font-semibold">{{ investor.name }}</p>
                  <p class="text-xs text-blue-200 mt-1">{{ investor.email }}</p>
                </div>
                <div class="py-2">
                  <Link
                    :href="route('investor.settings')"
                    class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                    @click="showUserDropdown = false"
                  >
                    <Cog6ToothIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                    Settings
                  </Link>
                  <Link
                    :href="route('investor.logout')"
                    method="post"
                    as="button"
                    class="w-full flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50"
                  >
                    <ArrowRightOnRectangleIcon class="h-5 w-5" aria-hidden="true" />
                    Sign Out
                  </Link>
                </div>
              </div>
            </Transition>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile Header -->
    <header class="lg:hidden sticky top-0 z-30 bg-white/80 backdrop-blur-xl border-b border-gray-200/50">
      <div class="flex items-center justify-between px-4 h-14">
        <div class="flex items-center gap-3">
          <img src="/logo.png" alt="MyGrowNet Logo" class="h-8 w-8 object-contain" />
          <span class="font-semibold text-gray-900">{{ pageTitle }}</span>
        </div>
        <div class="flex items-center gap-2">
          <!-- Mobile Notification Bell -->
          <button
            @click="showNotificationDropdown = !showNotificationDropdown"
            class="relative p-2 text-gray-600 hover:bg-gray-100 rounded-lg"
            aria-label="View notifications"
          >
            <BellIcon class="h-6 w-6" aria-hidden="true" />
            <span 
              v-if="totalNotifications > 0" 
              class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center"
            >
              {{ totalNotifications > 9 ? '9+' : totalNotifications }}
            </span>
          </button>
          <button
            @click="mobileMenuOpen = !mobileMenuOpen"
            class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg"
            :aria-label="mobileMenuOpen ? 'Close menu' : 'Open menu'"
          >
            <Bars3Icon v-if="!mobileMenuOpen" class="h-6 w-6" aria-hidden="true" />
            <XMarkIcon v-else class="h-6 w-6" aria-hidden="true" />
          </button>
        </div>
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
        <div class="h-14 flex items-center justify-between px-4 border-b border-gray-100">
          <span class="font-semibold text-gray-900">Menu</span>
          <button @click="mobileMenuOpen = false" class="p-2 hover:bg-gray-100 rounded-lg" aria-label="Close menu">
            <XMarkIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
          </button>
        </div>
        <nav class="p-3 space-y-1 overflow-y-auto max-h-[calc(100vh-3.5rem)]">
          <!-- Core -->
          <Link :href="route('investor.dashboard')" :class="getMobileNavClass('dashboard')" @click="mobileMenuOpen = false">
            <HomeIcon class="h-5 w-5" aria-hidden="true" />
            Dashboard
          </Link>
          <Link :href="route('investor.documents')" :class="getMobileNavClass('documents')" @click="mobileMenuOpen = false">
            <DocumentTextIcon class="h-5 w-5" aria-hidden="true" />
            Documents
          </Link>
          <Link :href="route('investor.reports')" :class="getMobileNavClass('reports')" @click="mobileMenuOpen = false">
            <ChartBarIcon class="h-5 w-5" aria-hidden="true" />
            Reports
          </Link>
          <Link :href="route('investor.messages')" :class="getMobileNavClass('messages')" @click="mobileMenuOpen = false">
            <ChatBubbleLeftRightIcon class="h-5 w-5" aria-hidden="true" />
            Messages
            <span v-if="unreadMessagesCount > 0" class="ml-auto w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
              {{ unreadMessagesCount > 9 ? '9+' : unreadMessagesCount }}
            </span>
          </Link>
          <Link :href="route('investor.announcements')" :class="getMobileNavClass('announcements')" @click="mobileMenuOpen = false">
            <MegaphoneIcon class="h-5 w-5" aria-hidden="true" />
            Announcements
          </Link>

          <!-- Analytics & Engagement -->
          <div class="pt-3 mt-3 border-t border-gray-200">
            <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Analytics</p>
            <Link :href="route('investor.analytics')" :class="getMobileNavClass('analytics')" @click="mobileMenuOpen = false">
              <PresentationChartLineIcon class="h-5 w-5" aria-hidden="true" />
              Analytics
            </Link>
            <Link :href="route('investor.voting')" :class="getMobileNavClass('voting')" @click="mobileMenuOpen = false">
              <HandRaisedIcon class="h-5 w-5" aria-hidden="true" />
              Voting
            </Link>
            <Link :href="route('investor.questions')" :class="getMobileNavClass('questions')" @click="mobileMenuOpen = false">
              <QuestionMarkCircleIcon class="h-5 w-5" aria-hidden="true" />
              Q&A
            </Link>
            <Link :href="route('investor.feedback')" :class="getMobileNavClass('feedback')" @click="mobileMenuOpen = false">
              <ChatBubbleBottomCenterTextIcon class="h-5 w-5" aria-hidden="true" />
              Feedback
            </Link>
          </div>

          <!-- Liquidity & Community -->
          <div class="pt-3 mt-3 border-t border-gray-200">
            <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Community</p>
            <Link :href="route('investor.share-transfer')" :class="getMobileNavClass('share-transfer')" @click="mobileMenuOpen = false">
              <ArrowsRightLeftIcon class="h-5 w-5" aria-hidden="true" />
              Share Transfer
            </Link>
            <Link :href="route('investor.liquidity-events')" :class="getMobileNavClass('liquidity-events')" @click="mobileMenuOpen = false">
              <BanknotesIcon class="h-5 w-5" aria-hidden="true" />
              Liquidity Events
            </Link>
            <Link :href="route('investor.forum')" :class="getMobileNavClass('forum')" @click="mobileMenuOpen = false">
              <UserGroupIcon class="h-5 w-5" aria-hidden="true" />
              Forum
            </Link>
            <Link :href="route('investor.directory')" :class="getMobileNavClass('directory')" @click="mobileMenuOpen = false">
              <UsersIcon class="h-5 w-5" aria-hidden="true" />
              Directory
            </Link>
          </div>

          <!-- Settings & Logout -->
          <div class="pt-3 mt-3 border-t border-gray-200">
            <Link :href="route('investor.settings')" :class="getMobileNavClass('settings')" @click="mobileMenuOpen = false">
              <Cog6ToothIcon class="h-5 w-5" aria-hidden="true" />
              Settings
            </Link>
            <Link :href="route('investor.logout')" method="post" as="button" class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-red-600 hover:bg-red-50 rounded-xl">
              <ArrowRightOnRectangleIcon class="h-5 w-5" aria-hidden="true" />
              Sign Out
            </Link>
          </div>
        </nav>
      </aside>
    </Transition>

    <!-- Main Content Area -->
    <main 
      :class="[
        'lg:pt-16 transition-all duration-300',
        sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-72'
      ]"
    >
      <div class="min-h-screen">
        <slot />
      </div>
    </main>
    
    <!-- Click outside overlay for dropdowns -->
    <div 
      v-if="showNotificationDropdown || showUserDropdown" 
      class="fixed inset-0 z-20" 
      @click="showNotificationDropdown = false; showUserDropdown = false"
    ></div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import {
  HomeIcon,
  DocumentTextIcon,
  ChartBarIcon,
  ChatBubbleLeftRightIcon,
  Cog6ToothIcon,
  ArrowRightOnRectangleIcon,
  Bars3Icon,
  XMarkIcon,
  MegaphoneIcon,
  BellIcon,
  PresentationChartLineIcon,
  HandRaisedIcon,
  QuestionMarkCircleIcon,
  ChatBubbleBottomCenterTextIcon,
  ArrowsRightLeftIcon,
  BanknotesIcon,
  UserGroupIcon,
  UsersIcon,
  ChevronDownIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
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
  unreadAnnouncements?: number;
}>();

const mobileMenuOpen = ref(false);
const showNotificationDropdown = ref(false);
const showUserDropdown = ref(false);
const sidebarCollapsed = ref(false);

// Load sidebar state from localStorage
onMounted(() => {
  const saved = localStorage.getItem('investor-sidebar-collapsed');
  if (saved !== null) {
    sidebarCollapsed.value = saved === 'true';
  }
  
  // Close dropdowns on click outside
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});

const handleClickOutside = (event: MouseEvent) => {
  const target = event.target as HTMLElement;
  if (!target.closest('.notification-dropdown') && !target.closest('[aria-label="View notifications"]')) {
    showNotificationDropdown.value = false;
  }
  if (!target.closest('[aria-label="User menu"]') && !target.closest('.user-dropdown')) {
    showUserDropdown.value = false;
  }
};

const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value;
  localStorage.setItem('investor-sidebar-collapsed', String(sidebarCollapsed.value));
};

const unreadMessagesCount = computed(() => props.unreadMessages || 0);
const unreadAnnouncementsCount = computed(() => props.unreadAnnouncements || 0);
const totalNotifications = computed(() => unreadMessagesCount.value + unreadAnnouncementsCount.value);

const isActive = (page: string) => props.activePage === page;

const getNavClass = (page: string) => {
  const base = 'w-full flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-xl transition-all duration-200 relative';
  const active = 'text-white bg-gradient-to-r from-blue-600 to-indigo-600 shadow-md shadow-blue-500/25';
  const inactive = 'text-gray-700 hover:bg-gray-100';
  const collapsed = sidebarCollapsed.value ? 'justify-center' : '';
  
  return `${base} ${isActive(page) ? active : inactive} ${collapsed}`;
};

const getMobileNavClass = (page: string) => {
  const base = 'w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200';
  const active = 'text-white bg-gradient-to-r from-blue-600 to-indigo-600 shadow-md shadow-blue-500/25';
  const inactive = 'text-gray-700 hover:bg-gray-100';
  
  return `${base} ${isActive(page) ? active : inactive}`;
};

const getInitials = (name: string) => {
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2);
};
</script>

<style scoped>
/* Tooltip styles for collapsed sidebar */
.nav-tooltip-trigger {
  position: relative;
}

.nav-tooltip {
  position: absolute;
  left: calc(100% + 12px);
  top: 50%;
  transform: translateY(-50%);
  background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
  color: white;
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  white-space: nowrap;
  opacity: 0;
  visibility: hidden;
  transition: all 0.2s ease;
  pointer-events: none;
  z-index: 100;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.nav-tooltip::before {
  content: '';
  position: absolute;
  left: -6px;
  top: 50%;
  transform: translateY(-50%);
  border: 6px solid transparent;
  border-right-color: #1e293b;
}

.nav-tooltip-trigger:hover .nav-tooltip {
  opacity: 1;
  visibility: visible;
}
</style>
