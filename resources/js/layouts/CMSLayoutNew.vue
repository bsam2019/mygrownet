<template>
  <div>
    <Head>
      <link rel="manifest" href="/cms-manifest.json" />
      <meta name="theme-color" content="#2563eb" />
      <meta name="apple-mobile-web-app-capable" content="yes" />
      <meta name="apple-mobile-web-app-status-bar-style" content="default" />
      <meta name="apple-mobile-web-app-title" content="CMS" />
    </Head>

    <!-- PWA Install Prompt -->
    <PwaInstallPrompt />

    <div class="flex h-screen overflow-hidden bg-gray-50">
    <!-- Desktop Sidebar -->
    <aside 
      :class="[
        'hidden md:flex md:flex-col transition-all duration-300 ease-in-out z-50 bg-white border-r border-gray-200 shadow-sm',
        sidebarCollapsed ? 'w-20' : 'w-64'
      ]"
    >
      <!-- Logo/Company - Fixed Header -->
      <div class="flex items-center flex-shrink-0 px-4 py-4 border-b border-gray-200 h-16 sticky top-0 bg-white z-10">
        <div class="flex items-center gap-3 flex-1 min-w-0">
          <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
            <BuildingOfficeIcon class="h-6 w-6 text-white" aria-hidden="true" />
          </div>
          <div v-if="!sidebarCollapsed" class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-gray-900 truncate">{{ company?.name }}</p>
            <p class="text-xs text-gray-500 truncate">{{ cmsUser?.role?.name }}</p>
          </div>
        </div>
      </div>

      <!-- Navigation - Scrollable -->
      <div class="flex flex-col flex-grow overflow-y-auto custom-scrollbar">
        <nav class="flex-1 px-3 py-4 space-y-1">
          <NavItem
            icon="HomeIcon"
            label="Dashboard"
            route-name="cms.dashboard"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.dashboard')"
            @click="navigateTo('cms.dashboard')"
          />
          
          <NavItem
            icon="BriefcaseIcon"
            label="Jobs"
            route-name="cms.jobs"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.jobs')"
            @click="navigateTo('cms.jobs.index')"
          />
          
          <NavItem
            icon="UsersIcon"
            label="Customers"
            route-name="cms.customers"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.customers')"
            @click="navigateTo('cms.customers.index')"
          />
          
          <NavItem
            icon="DocumentTextIcon"
            label="Invoices"
            route-name="cms.invoices"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.invoices')"
            @click="navigateTo('cms.invoices.index')"
          />
          
          <NavItem
            icon="CreditCardIcon"
            label="Payments"
            route-name="cms.payments"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.payments')"
            @click="navigateTo('cms.payments.index')"
          />
          
          <NavItem
            icon="ChartBarIcon"
            label="Reports"
            route-name="cms.reports"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.reports')"
            @click="navigateTo('cms.reports.index')"
          />

          <div class="pt-4 pb-2">
            <div class="border-t border-gray-200"></div>
          </div>

          <!-- Analytics Submenu -->
          <div v-if="!sidebarCollapsed" class="px-3">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Analytics</p>
          </div>
          
          <NavItem
            icon="PresentationChartLineIcon"
            label="Operations"
            route-name="cms.analytics.operations"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.analytics.operations')"
            @click="navigateTo('cms.analytics.operations')"
          />
          
          <NavItem
            icon="CurrencyDollarIcon"
            label="Finance"
            route-name="cms.analytics.finance"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.analytics.finance')"
            @click="navigateTo('cms.analytics.finance')"
          />

          <div class="pt-4 pb-2">
            <div class="border-t border-gray-200"></div>
          </div>

          <NavItem
            icon="BanknotesIcon"
            label="Expenses"
            route-name="cms.expenses"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.expenses')"
            @click="navigateTo('cms.expenses.index')"
          />

          <NavItem
            icon="DocumentDuplicateIcon"
            label="Quotations"
            route-name="cms.quotations"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.quotations')"
            @click="navigateTo('cms.quotations.index')"
          />

          <NavItem
            icon="CubeIcon"
            label="Inventory"
            route-name="cms.inventory"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.inventory')"
            @click="navigateTo('cms.inventory.index')"
          />

          <NavItem
            icon="ComputerDesktopIcon"
            label="Assets"
            route-name="cms.assets"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.assets')"
            @click="navigateTo('cms.assets.index')"
          />

          <NavItem
            icon="UserGroupIcon"
            label="Payroll"
            route-name="cms.payroll"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.payroll')"
            @click="navigateTo('cms.payroll.index')"
          />

          <NavItem
            icon="ClockIcon"
            label="Time Tracking"
            route-name="cms.time-tracking"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.time-tracking')"
            @click="navigateTo('cms.time-tracking.index')"
          />

          <NavItem
            icon="ArrowPathIcon"
            label="Recurring Invoices"
            route-name="cms.recurring-invoices"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.recurring-invoices')"
            @click="navigateTo('cms.recurring-invoices.index')"
          />

          <NavItem
            icon="CheckCircleIcon"
            label="Approvals"
            route-name="cms.approvals"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.approvals')"
            @click="navigateTo('cms.approvals.index')"
          />

          <NavItem
            icon="CalculatorIcon"
            label="Chart of Accounts"
            route-name="cms.accounting"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.accounting')"
            @click="navigateTo('cms.accounting.index')"
          />

          <div class="pt-4 pb-2">
            <div class="border-t border-gray-200"></div>
          </div>

          <!-- Settings Submenu -->
          <div v-if="!sidebarCollapsed" class="px-3">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Settings</p>
          </div>

          <NavItem
            icon="Cog6ToothIcon"
            label="Company Settings"
            route-name="cms.settings"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.settings.index')"
            @click="navigateTo('cms.settings.index')"
          />

          <NavItem
            icon="SwatchIcon"
            label="Industry Presets"
            route-name="cms.settings.industry-presets"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.settings.industry-presets')"
            @click="navigateTo('cms.settings.industry-presets.index')"
          />

          <NavItem
            icon="EnvelopeIcon"
            label="Email Settings"
            route-name="cms.settings.email"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.settings.email')"
            @click="navigateTo('cms.settings.email.index')"
          />

          <NavItem
            icon="DevicePhoneMobileIcon"
            label="SMS Settings"
            route-name="cms.settings.sms"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.settings.sms')"
            @click="navigateTo('cms.settings.sms.index')"
          />

          <NavItem
            icon="BanknotesIcon"
            label="Currency"
            route-name="cms.settings.currency"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.settings.currency')"
            @click="navigateTo('cms.settings.currency.index')"
          />

          <NavItem
            icon="ShieldCheckIcon"
            label="Security"
            route-name="cms.security"
            :collapsed="sidebarCollapsed"
            :active="isActive('cms.security')"
            @click="navigateTo('cms.security.settings')"
          />
        </nav>
      </div>

      <!-- User Profile - Fixed at Bottom -->
      <div class="flex-shrink-0 border-t border-gray-200 p-4 bg-white sticky bottom-0 z-10">
        <div v-if="!sidebarCollapsed" class="flex items-center gap-3">
          <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center flex-shrink-0">
            <UserCircleIcon class="h-8 w-8 text-gray-500" aria-hidden="true" />
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 truncate">{{ user?.name }}</p>
            <p class="text-xs text-gray-500 truncate">{{ user?.email }}</p>
          </div>
          <Menu as="div" class="relative">
            <MenuButton class="p-1 rounded-lg hover:bg-gray-100">
              <EllipsisVerticalIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
            </MenuButton>
            <MenuItems class="absolute bottom-full right-0 mb-2 w-48 origin-bottom-right rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
              <div class="py-1">
                <MenuItem v-slot="{ active }">
                  <button
                    @click="navigateTo('profile')"
                    :class="[active ? 'bg-gray-100' : '', 'block w-full text-left px-4 py-2 text-sm text-gray-700']"
                  >
                    Profile Settings
                  </button>
                </MenuItem>
                <MenuItem v-slot="{ active }">
                  <button
                    @click="logout"
                    :class="[active ? 'bg-gray-100' : '', 'block w-full text-left px-4 py-2 text-sm text-gray-700']"
                  >
                    Sign Out
                  </button>
                </MenuItem>
              </div>
            </MenuItems>
          </Menu>
        </div>
        <div v-else class="flex justify-center">
          <Menu as="div" class="relative">
            <MenuButton class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center hover:bg-gray-300">
              <UserCircleIcon class="h-8 w-8 text-gray-500" aria-hidden="true" />
            </MenuButton>
            <MenuItems class="absolute bottom-full left-0 mb-2 w-48 origin-bottom-left rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
              <div class="py-1">
                <MenuItem v-slot="{ active }">
                  <button
                    @click="navigateTo('profile')"
                    :class="[active ? 'bg-gray-100' : '', 'block w-full text-left px-4 py-2 text-sm text-gray-700']"
                  >
                    Profile Settings
                  </button>
                </MenuItem>
                <MenuItem v-slot="{ active }">
                  <button
                    @click="logout"
                    :class="[active ? 'bg-gray-100' : '', 'block w-full text-left px-4 py-2 text-sm text-gray-700']"
                  >
                    Sign Out
                  </button>
                </MenuItem>
              </div>
            </MenuItems>
          </Menu>
        </div>
      </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex flex-col flex-1 overflow-hidden">
      <!-- Top Header -->
      <header class="flex-shrink-0 bg-white border-b border-gray-200 shadow-sm z-30">
        <div class="px-4 sm:px-6 lg:px-8">
          <div class="flex h-16 items-center justify-between">
            <!-- Left side: Hamburger + Breadcrumbs -->
            <div class="flex items-center gap-4">
              <!-- Desktop Sidebar Toggle -->
              <button
                @click="sidebarCollapsed = !sidebarCollapsed"
                class="hidden md:block p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition"
              >
                <Bars3Icon v-if="sidebarCollapsed" class="h-6 w-6" aria-hidden="true" />
                <Bars3BottomLeftIcon v-else class="h-6 w-6" aria-hidden="true" />
              </button>

              <!-- Mobile menu button -->
              <button
                @click="mobileMenuOpen = true"
                class="md:hidden p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100"
              >
                <Bars3Icon class="h-6 w-6" aria-hidden="true" />
              </button>

              <!-- Breadcrumbs -->
              <nav class="hidden md:flex items-center space-x-2 text-sm">
                <button @click="navigateTo('cms.dashboard')" class="text-gray-500 hover:text-gray-700 transition">
                  CMS
                </button>
                <ChevronRightIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
                <span class="text-gray-900 font-medium">{{ pageTitle }}</span>
              </nav>
            </div>

            <!-- Right side actions -->
            <div class="flex items-center gap-3">
              <!-- Search -->
              <div class="hidden lg:block">
                <div class="relative">
                  <MagnifyingGlassIcon
                    class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400"
                    aria-hidden="true"
                  />
                  <input
                    type="text"
                    placeholder="Search..."
                    class="block w-64 px-4 py-2 pl-10 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 placeholder-gray-500 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                  />
                </div>
              </div>

              <!-- Notifications Dropdown -->
              <Menu as="div" class="relative">
                <MenuButton class="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 relative transition">
                  <BellIcon class="h-6 w-6" aria-hidden="true" />
                  <span v-if="unreadNotificationsCount > 0" class="absolute top-1 right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-medium text-white ring-2 ring-white">
                    {{ unreadNotificationsCount > 9 ? '9+' : unreadNotificationsCount }}
                  </span>
                </MenuButton>
                <MenuItems class="absolute right-0 mt-2 w-80 origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                  <div class="p-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                      <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
                      <Link :href="route('cms.notifications.index')" class="text-xs text-blue-600 hover:text-blue-700 font-medium">
                        View all
                      </Link>
                    </div>
                  </div>
                  <div class="max-h-96 overflow-y-auto">
                    <MenuItem v-for="notification in recentNotifications" :key="notification.id" v-slot="{ active }">
                      <button
                        @click="handleNotificationClick(notification)"
                        :class="[
                          active ? 'bg-gray-50' : '',
                          !notification.read_at ? 'bg-blue-50' : '',
                          'block w-full text-left px-4 py-3 border-b border-gray-100 hover:bg-gray-50 transition'
                        ]"
                      >
                        <div class="flex gap-3">
                          <div class="flex-shrink-0">
                            <div :class="[
                              'w-2 h-2 rounded-full mt-2',
                              !notification.read_at ? 'bg-blue-600' : 'bg-gray-300'
                            ]"></div>
                          </div>
                          <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ notification.title }}</p>
                            <p class="text-xs text-gray-600 mt-1 line-clamp-2">{{ notification.message }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ formatNotificationTime(notification.created_at) }}</p>
                          </div>
                        </div>
                      </button>
                    </MenuItem>
                    <div v-if="recentNotifications.length === 0" class="px-4 py-8 text-center">
                      <BellIcon class="h-12 w-12 text-gray-300 mx-auto mb-2" aria-hidden="true" />
                      <p class="text-sm text-gray-500">No notifications</p>
                    </div>
                  </div>
                </MenuItems>
              </Menu>

              <!-- User Profile Dropdown -->
              <Menu as="div" class="relative">
                <MenuButton class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-gray-100 transition">
                  <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                    <UserCircleIcon class="h-7 w-7 text-gray-500" aria-hidden="true" />
                  </div>
                  <ChevronDownIcon class="hidden md:block h-4 w-4 text-gray-400" aria-hidden="true" />
                </MenuButton>
                <MenuItems class="absolute right-0 mt-2 w-56 origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                  <div class="px-4 py-3 border-b border-gray-200">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ user?.name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ user?.email }}</p>
                  </div>
                  <div class="py-1">
                    <MenuItem v-slot="{ active }">
                      <button
                        @click="navigateTo('profile')"
                        :class="[active ? 'bg-gray-100' : '', 'flex items-center gap-2 w-full text-left px-4 py-2 text-sm text-gray-700']"
                      >
                        <UserCircleIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                        Profile Settings
                      </button>
                    </MenuItem>
                    <MenuItem v-slot="{ active }">
                      <Link
                        :href="route('cms.settings.index')"
                        :class="[active ? 'bg-gray-100' : '', 'flex items-center gap-2 w-full text-left px-4 py-2 text-sm text-gray-700']"
                      >
                        <Cog6ToothIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                        Company Settings
                      </Link>
                    </MenuItem>
                    <div class="border-t border-gray-200 my-1"></div>
                    <MenuItem v-slot="{ active }">
                      <button
                        @click="logout"
                        :class="[active ? 'bg-gray-100' : '', 'flex items-center gap-2 w-full text-left px-4 py-2 text-sm text-red-600']"
                      >
                        <ArrowRightOnRectangleIcon class="h-5 w-5 text-red-500" aria-hidden="true" />
                        Sign Out
                      </button>
                    </MenuItem>
                  </div>
                </MenuItems>
              </Menu>
            </div>
          </div>
        </div>
      </header>

      <!-- Page Content (scrollable) -->
      <main class="flex-1 overflow-y-auto custom-scrollbar">
        <slot />
      </main>
    </div>

    <!-- Slide-Over Panel -->
    <SlideOver
      :open="slideOver.isOpen.value"
      :title="slideOver.config.value.title"
      :subtitle="slideOver.config.value.subtitle"
      :size="slideOver.config.value.size"
      @close="slideOver.close()"
    >
      <JobForm
        v-if="slideOver.currentType.value === 'job'"
        :customers="customers"
        @cancel="slideOver.close()"
        @success="handleFormSuccess"
      />
      
      <CustomerForm
        v-else-if="slideOver.currentType.value === 'customer'"
        @cancel="slideOver.close()"
        @success="handleFormSuccess"
      />
      
      <InvoiceForm
        v-else-if="slideOver.currentType.value === 'invoice'"
        :customers="customers"
        @cancel="slideOver.close()"
        @success="handleFormSuccess"
      />
    </SlideOver>

    <!-- Mobile Sidebar (same as before) -->
    <TransitionRoot as="template" :show="mobileMenuOpen">
      <Dialog as="div" class="relative z-50 md:hidden" @close="mobileMenuOpen = false">
        <TransitionChild
          as="template"
          enter="transition-opacity ease-linear duration-300"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="transition-opacity ease-linear duration-300"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-gray-900/80" />
        </TransitionChild>

        <div class="fixed inset-0 flex">
          <TransitionChild
            as="template"
            enter="transition ease-in-out duration-300 transform"
            enter-from="-translate-x-full"
            enter-to="translate-x-0"
            leave="transition ease-in-out duration-300 transform"
            leave-from="translate-x-0"
            leave-to="-translate-x-full"
          >
            <DialogPanel class="relative mr-16 flex w-full max-w-xs flex-1 bg-white">
              <TransitionChild
                as="template"
                enter="ease-in-out duration-300"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="ease-in-out duration-300"
                leave-from="opacity-100"
                leave-to="opacity-0"
              >
                <div class="absolute left-full top-0 flex w-16 justify-center pt-5">
                  <button type="button" class="-m-2.5 p-2.5" @click="mobileMenuOpen = false">
                    <XMarkIcon class="h-6 w-6 text-white" aria-hidden="true" />
                  </button>
                </div>
              </TransitionChild>
              
              <!-- Mobile sidebar content -->
              <div class="flex flex-col flex-grow overflow-y-auto">
                <div class="flex items-center flex-shrink-0 px-4 py-4 border-b border-gray-200">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                      <BuildingOfficeIcon class="h-6 w-6 text-white" aria-hidden="true" />
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-semibold text-gray-900 truncate">{{ company?.name }}</p>
                      <p class="text-xs text-gray-500 truncate">{{ cmsUser?.role?.name }}</p>
                    </div>
                  </div>
                </div>

                <nav class="flex-1 px-3 py-4 space-y-1">
                  <button
                    @click="navigateTo('cms.dashboard'); mobileMenuOpen = false"
                    :class="[
                      isActive('cms.dashboard') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50',
                      'group flex items-center w-full px-3 py-2 text-sm font-medium rounded-lg transition'
                    ]"
                  >
                    <HomeIcon class="mr-3 h-5 w-5 flex-shrink-0" aria-hidden="true" />
                    Dashboard
                  </button>
                  <!-- Add other mobile nav items -->
                </nav>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </Dialog>
    </TransitionRoot>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, provide, computed } from 'vue'
import { router, usePage, Link, Head } from '@inertiajs/vue3'
import { Menu, MenuButton, MenuItem, MenuItems, Dialog, DialogPanel, TransitionChild, TransitionRoot } from '@headlessui/vue'
import {
  HomeIcon,
  BriefcaseIcon,
  UsersIcon,
  DocumentTextIcon,
  CreditCardIcon,
  ChartBarIcon,
  Cog6ToothIcon,
  BuildingOfficeIcon,
  UserCircleIcon,
  BellIcon,
  MagnifyingGlassIcon,
  Bars3Icon,
  Bars3BottomLeftIcon,
  XMarkIcon,
  EllipsisVerticalIcon,
  ChevronRightIcon,
  PresentationChartLineIcon,
  CurrencyDollarIcon,
  ChevronDownIcon,
  ArrowRightOnRectangleIcon,
  BanknotesIcon,
  DocumentDuplicateIcon,
  CubeIcon,
  ComputerDesktopIcon,
  UserGroupIcon,
  SwatchIcon,
  ClockIcon,
  ArrowPathIcon,
  CheckCircleIcon,
  CalculatorIcon,
  EnvelopeIcon,
  DevicePhoneMobileIcon,
  ShieldCheckIcon,
} from '@heroicons/vue/24/outline'
import SlideOver from '@/components/CMS/SlideOver.vue'
import JobForm from '@/components/CMS/Forms/JobForm.vue'
import CustomerForm from '@/components/CMS/Forms/CustomerForm.vue'
import InvoiceForm from '@/components/CMS/Forms/InvoiceForm.vue'
import NavItem from '@/components/CMS/NavItem.vue'
import PwaInstallPrompt from '@/components/CMS/PwaInstallPrompt.vue'
import { useCMSSlideOver } from '@/composables/useCMSSlideOver'

interface Notification {
  id: number
  title: string
  message: string
  type: string
  read_at: string | null
  created_at: string
  data?: any
}

interface Props {
  company?: any
  user?: any
  cmsUser?: any
  pageTitle?: string
  customers?: any[]
  notifications?: Notification[]
}

const props = defineProps<Props>()

const page = usePage()
const mobileMenuOpen = ref(false)
const sidebarCollapsed = ref(false)
const slideOver = useCMSSlideOver()

// Provide slideOver to child components
provide('slideOver', slideOver)

// Notifications from backend (no hardcoded data)
const recentNotifications = ref<Notification[]>(props.notifications || [])

const unreadNotificationsCount = computed(() => {
  return recentNotifications.value.filter(n => !n.read_at).length
})

const handleNotificationClick = (notification: Notification) => {
  // Mark as read
  if (!notification.read_at) {
    router.post(route('cms.notifications.mark-read', notification.id), {}, {
      preserveScroll: true,
      onSuccess: () => {
        notification.read_at = new Date().toISOString()
      }
    })
  }
  
  // Navigate to relevant page based on notification type
  if (notification.data?.url) {
    router.visit(notification.data.url)
  }
}

const formatNotificationTime = (timestamp: string) => {
  const date = new Date(timestamp)
  const now = new Date()
  const diffMs = now.getTime() - date.getTime()
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMs / 3600000)
  const diffDays = Math.floor(diffMs / 86400000)
  
  if (diffMins < 1) return 'Just now'
  if (diffMins < 60) return `${diffMins}m ago`
  if (diffHours < 24) return `${diffHours}h ago`
  if (diffDays < 7) return `${diffDays}d ago`
  
  return date.toLocaleDateString()
}

const isActive = (routeName: string) => {
  const currentRoute = page.component.value
  if (!currentRoute) return false
  return currentRoute.startsWith(routeName.replace('.index', ''))
}

const navigateTo = (routeName: string) => {
  router.visit(route(routeName), {
    preserveState: true,
    preserveScroll: false,
    only: ['pageContent', 'stats', 'recentJobs', 'recentInvoices'], // Only fetch what changed
  })
}

const handleFormSuccess = () => {
  slideOver.close()
  // Reload only necessary data
  router.reload({
    only: ['stats', 'recentJobs', 'recentInvoices', 'customers'],
    preserveScroll: true,
  })
}

const logout = () => {
  router.post(route('logout'))
}
</script>

<style scoped>
/* Custom Scrollbar Styling */
.custom-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: #CBD5E1 #F8FAFC;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: #F8FAFC;
  border-radius: 4px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #CBD5E1;
  border-radius: 4px;
  transition: background 0.2s ease;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94A3B8;
}

.custom-scrollbar::-webkit-scrollbar-thumb:active {
  background: #64748B;
}

/* Firefox scrollbar styling */
@supports (scrollbar-color: auto) {
  .custom-scrollbar {
    scrollbar-color: #CBD5E1 #F8FAFC;
  }
  
  .custom-scrollbar:hover {
    scrollbar-color: #94A3B8 #F8FAFC;
  }
}
</style>
