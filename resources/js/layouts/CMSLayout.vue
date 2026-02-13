<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Desktop Sidebar -->
    <aside 
      :class="[
        'hidden md:fixed md:inset-y-0 md:flex md:flex-col transition-all duration-300 ease-in-out z-50',
        sidebarCollapsed ? 'md:w-20' : 'md:w-64'
      ]"
    >
      <div class="flex flex-col flex-grow bg-white border-r border-gray-200 overflow-y-auto shadow-sm">
        <!-- Logo/Company -->
        <div class="flex items-center flex-shrink-0 px-4 py-4 border-b border-gray-200 h-16">
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

        <!-- Navigation -->
        <nav class="flex-1 px-3 py-4 space-y-1">
          <!-- Dashboard -->
          <div class="relative group">
            <button
              @click="navigateTo('cms.dashboard')"
              :class="[
                isActive('cms.dashboard')
                  ? 'bg-blue-50 text-blue-600'
                  : 'text-gray-700 hover:bg-gray-50',
                'flex items-center w-full px-3 py-2.5 text-sm font-medium rounded-lg transition',
                sidebarCollapsed ? 'justify-center' : ''
              ]"
            >
              <HomeIcon
                :class="[
                  isActive('cms.dashboard') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600',
                  'flex-shrink-0',
                  sidebarCollapsed ? 'h-6 w-6' : 'mr-3 h-5 w-5'
                ]"
                aria-hidden="true"
              />
              <span v-if="!sidebarCollapsed">Dashboard</span>
            </button>
            <!-- Tooltip -->
            <div
              v-if="sidebarCollapsed"
              class="absolute left-full ml-2 px-3 py-2 bg-gray-900 text-white text-sm rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 whitespace-nowrap z-50 top-1/2 -translate-y-1/2"
            >
              Dashboard
              <div class="absolute right-full top-1/2 -translate-y-1/2 border-4 border-transparent border-r-gray-900"></div>
            </div>
          </div>

          <!-- Jobs -->
          <div class="relative group">
            <button
              @click="navigateTo('cms.jobs.index')"
              :class="[
                isActive('cms.jobs')
                  ? 'bg-blue-50 text-blue-600'
                  : 'text-gray-700 hover:bg-gray-50',
                'flex items-center w-full px-3 py-2.5 text-sm font-medium rounded-lg transition',
                sidebarCollapsed ? 'justify-center' : ''
              ]"
            >
              <BriefcaseIcon
                :class="[
                  isActive('cms.jobs') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600',
                  'flex-shrink-0',
                  sidebarCollapsed ? 'h-6 w-6' : 'mr-3 h-5 w-5'
                ]"
                aria-hidden="true"
              />
              <span v-if="!sidebarCollapsed">Jobs</span>
            </button>
            <div
              v-if="sidebarCollapsed"
              class="absolute left-full ml-2 px-3 py-2 bg-gray-900 text-white text-sm rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 whitespace-nowrap z-50 top-1/2 -translate-y-1/2"
            >
              Jobs
              <div class="absolute right-full top-1/2 -translate-y-1/2 border-4 border-transparent border-r-gray-900"></div>
            </div>
          </div>

          <!-- Customers -->
          <div class="relative group">
            <button
              @click="navigateTo('cms.customers.index')"
              :class="[
                isActive('cms.customers')
                  ? 'bg-blue-50 text-blue-600'
                  : 'text-gray-700 hover:bg-gray-50',
                'flex items-center w-full px-3 py-2.5 text-sm font-medium rounded-lg transition',
                sidebarCollapsed ? 'justify-center' : ''
              ]"
            >
              <UsersIcon
                :class="[
                  isActive('cms.customers') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600',
                  'flex-shrink-0',
                  sidebarCollapsed ? 'h-6 w-6' : 'mr-3 h-5 w-5'
                ]"
                aria-hidden="true"
              />
              <span v-if="!sidebarCollapsed">Customers</span>
            </button>
            <div
              v-if="sidebarCollapsed"
              class="absolute left-full ml-2 px-3 py-2 bg-gray-900 text-white text-sm rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 whitespace-nowrap z-50 top-1/2 -translate-y-1/2"
            >
              Customers
              <div class="absolute right-full top-1/2 -translate-y-1/2 border-4 border-transparent border-r-gray-900"></div>
            </div>
          </div>

          <!-- Invoices -->
          <div class="relative group">
            <button
              @click="navigateTo('cms.invoices.index')"
              :class="[
                isActive('cms.invoices')
                  ? 'bg-blue-50 text-blue-600'
                  : 'text-gray-700 hover:bg-gray-50',
                'flex items-center w-full px-3 py-2.5 text-sm font-medium rounded-lg transition',
                sidebarCollapsed ? 'justify-center' : ''
              ]"
            >
              <DocumentTextIcon
                :class="[
                  isActive('cms.invoices') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600',
                  'flex-shrink-0',
                  sidebarCollapsed ? 'h-6 w-6' : 'mr-3 h-5 w-5'
                ]"
                aria-hidden="true"
              />
              <span v-if="!sidebarCollapsed">Invoices</span>
            </button>
            <div
              v-if="sidebarCollapsed"
              class="absolute left-full ml-2 px-3 py-2 bg-gray-900 text-white text-sm rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 whitespace-nowrap z-50 top-1/2 -translate-y-1/2"
            >
              Invoices
              <div class="absolute right-full top-1/2 -translate-y-1/2 border-4 border-transparent border-r-gray-900"></div>
            </div>
          </div>

          <!-- Payments -->
          <div class="relative group">
            <button
              @click="navigateTo('cms.payments.index')"
              :class="[
                isActive('cms.payments')
                  ? 'bg-blue-50 text-blue-600'
                  : 'text-gray-700 hover:bg-gray-50',
                'flex items-center w-full px-3 py-2.5 text-sm font-medium rounded-lg transition',
                sidebarCollapsed ? 'justify-center' : ''
              ]"
            >
              <CreditCardIcon
                :class="[
                  isActive('cms.payments') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600',
                  'flex-shrink-0',
                  sidebarCollapsed ? 'h-6 w-6' : 'mr-3 h-5 w-5'
                ]"
                aria-hidden="true"
              />
              <span v-if="!sidebarCollapsed">Payments</span>
            </button>
            <div
              v-if="sidebarCollapsed"
              class="absolute left-full ml-2 px-3 py-2 bg-gray-900 text-white text-sm rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 whitespace-nowrap z-50 top-1/2 -translate-y-1/2"
            >
              Payments
              <div class="absolute right-full top-1/2 -translate-y-1/2 border-4 border-transparent border-r-gray-900"></div>
            </div>
          </div>

          <!-- Reports -->
          <div class="relative group">
            <button
              @click="navigateTo('cms.reports.index')"
              :class="[
                isActive('cms.reports')
                  ? 'bg-blue-50 text-blue-600'
                  : 'text-gray-700 hover:bg-gray-50',
                'flex items-center w-full px-3 py-2.5 text-sm font-medium rounded-lg transition',
                sidebarCollapsed ? 'justify-center' : ''
              ]"
            >
              <ChartBarIcon
                :class="[
                  isActive('cms.reports') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600',
                  'flex-shrink-0',
                  sidebarCollapsed ? 'h-6 w-6' : 'mr-3 h-5 w-5'
                ]"
                aria-hidden="true"
              />
              <span v-if="!sidebarCollapsed">Reports</span>
            </button>
            <div
              v-if="sidebarCollapsed"
              class="absolute left-full ml-2 px-3 py-2 bg-gray-900 text-white text-sm rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 whitespace-nowrap z-50 top-1/2 -translate-y-1/2"
            >
              Reports
              <div class="absolute right-full top-1/2 -translate-y-1/2 border-4 border-transparent border-r-gray-900"></div>
            </div>
          </div>

          <!-- Divider -->
          <div class="pt-4 pb-2">
            <div class="border-t border-gray-200"></div>
          </div>

          <!-- Settings -->
          <div class="relative group">
            <button
              @click="navigateTo('cms.settings')"
              :class="[
                'flex items-center w-full px-3 py-2.5 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition',
                sidebarCollapsed ? 'justify-center' : ''
              ]"
            >
              <Cog6ToothIcon
                :class="[
                  'text-gray-400 group-hover:text-gray-600 flex-shrink-0',
                  sidebarCollapsed ? 'h-6 w-6' : 'mr-3 h-5 w-5'
                ]"
                aria-hidden="true"
              />
              <span v-if="!sidebarCollapsed">Settings</span>
            </button>
            <div
              v-if="sidebarCollapsed"
              class="absolute left-full ml-2 px-3 py-2 bg-gray-900 text-white text-sm rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 whitespace-nowrap z-50 top-1/2 -translate-y-1/2"
            >
              Settings
              <div class="absolute right-full top-1/2 -translate-y-1/2 border-4 border-transparent border-r-gray-900"></div>
            </div>
          </div>
        </nav>

        <!-- User Profile -->
        <div class="flex-shrink-0 border-t border-gray-200 p-4">
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
      </div>
    </aside>

    <!-- Main Content Area -->
    <div 
      :class="[
        'flex flex-col flex-1 transition-all duration-300 ease-in-out',
        sidebarCollapsed ? 'md:pl-20' : 'md:pl-64'
      ]"
    >
      <!-- Top Header -->
      <header class="sticky top-0 z-30 bg-white border-b border-gray-200 shadow-sm">
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
                    class="block w-64 rounded-lg border-gray-300 pl-10 pr-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500"
                  />
                </div>
              </div>

              <!-- Notifications -->
              <button class="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 relative">
                <BellIcon class="h-6 w-6" aria-hidden="true" />
                <span class="absolute top-1 right-1 block h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
              </button>

              <!-- User menu (mobile) -->
              <Menu as="div" class="relative md:hidden">
                <MenuButton class="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                  <UserCircleIcon class="h-6 w-6" aria-hidden="true" />
                </MenuButton>
                <MenuItems class="absolute right-0 mt-2 w-48 origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                  <div class="py-1">
                    <MenuItem v-slot="{ active }">
                      <Link
                        href="/profile"
                        :class="[active ? 'bg-gray-100' : '', 'block px-4 py-2 text-sm text-gray-700']"
                      >
                        Profile Settings
                      </Link>
                    </MenuItem>
                    <MenuItem v-slot="{ active }">
                      <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        :class="[active ? 'bg-gray-100' : '', 'block w-full text-left px-4 py-2 text-sm text-gray-700']"
                      >
                        Sign Out
                      </Link>
                    </MenuItem>
                  </div>
                </MenuItems>
              </Menu>
            </div>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="flex-1">
        <slot />
      </main>
    </div>

    <!-- Mobile Sidebar -->
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
            <DialogPanel class="relative mr-16 flex w-full max-w-xs flex-1">
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
              
              <!-- Mobile sidebar content (same as desktop) -->
              <div class="flex flex-col flex-grow bg-white overflow-y-auto">
                <!-- Same content as desktop sidebar -->
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
                  <!-- Same navigation items -->
                  <Link
                    :href="route('cms.dashboard')"
                    @click="mobileMenuOpen = false"
                    :class="[
                      isActive('cms.dashboard')
                        ? 'bg-blue-50 text-blue-600'
                        : 'text-gray-700 hover:bg-gray-50',
                      'group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition'
                    ]"
                  >
                    <HomeIcon class="mr-3 h-5 w-5 flex-shrink-0" aria-hidden="true" />
                    Dashboard
                  </Link>
                  <!-- Add other nav items -->
                </nav>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </Dialog>
    </TransitionRoot>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { router, Link, usePage } from '@inertiajs/vue3'
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
} from '@heroicons/vue/24/outline'

interface Props {
  company?: any
  user?: any
  cmsUser?: any
  pageTitle?: string
}

defineProps<Props>()

const page = usePage()
const mobileMenuOpen = ref(false)
const sidebarCollapsed = ref(false)

const isActive = (routeName: string) => {
  const currentRoute = page.component.value
  if (!currentRoute) return false
  return currentRoute.startsWith(routeName)
}

const navigateTo = (routeName: string) => {
  router.visit(route(routeName), {
    preserveState: true,
    preserveScroll: false,
  })
}

const logout = () => {
  router.post(route('logout'))
}
</script>
