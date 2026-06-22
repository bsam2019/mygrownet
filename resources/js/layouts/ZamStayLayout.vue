<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import {
  HomeModernIcon,
  MagnifyingGlassIcon,
  UserCircleIcon,
  CalendarDaysIcon,
  BuildingOffice2Icon,
  ArrowRightOnRectangleIcon,
  Bars3Icon,
  XMarkIcon,
} from '@heroicons/vue/24/outline';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const mobileMenuOpen = ref(false);
const searchQuery = ref('');

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    router.get(route('zamstay.search'), { location: searchQuery.value });
    searchQuery.value = '';
  }
};

const handleLogout = () => {
  router.post(route('logout'));
};

const getInitials = (name: string) => {
  if (!name) return '?';
  const parts = name.split(' ');
  return parts.length >= 2
    ? (parts[0][0] + parts[1][0]).toUpperCase()
    : name.substring(0, 2).toUpperCase();
};
</script>

<template>
  <div class="min-h-screen bg-emerald-50 flex flex-col">
    <!-- Header -->
    <header class="sticky top-0 z-40 bg-white shadow-sm border-b border-emerald-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <!-- Logo -->
          <Link :href="route('zamstay.home')" class="flex items-center gap-2">
            <div class="flex aspect-square size-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-600 to-teal-500 shadow-lg">
              <HomeModernIcon class="size-6 text-white" />
            </div>
            <div class="hidden sm:flex flex-col">
              <span class="text-lg font-bold text-gray-900 leading-tight">ZamStay</span>
              <span class="text-[10px] text-gray-500 leading-tight">Zambia Stays Redefined</span>
            </div>
          </Link>

          <!-- Desktop Search -->
          <div class="hidden md:flex flex-1 max-w-md mx-6">
            <form @submit.prevent="handleSearch" class="w-full">
              <div class="relative">
                <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Search by location..."
                  class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-full focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm"
                />
              </div>
            </form>
          </div>

          <!-- Right Actions -->
          <div class="flex items-center gap-2">
            <!-- Mobile Search Toggle -->
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100">
              <Bars3Icon v-if="!mobileMenuOpen" class="h-6 w-6" />
              <XMarkIcon v-else class="h-6 w-6" />
            </button>

            <!-- Desktop Nav -->
            <div class="hidden md:flex items-center gap-1">
              <Link :href="route('zamstay.home')" class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors">
                Home
              </Link>
              <Link :href="route('zamstay.search')" class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors">
                Browse
              </Link>
              <template v-if="user">
                <Link :href="route('zamstay.host.dashboard')" class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors">
                  Host
                </Link>
                <Link :href="route('zamstay.bookings.index')" class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors">
                  My Bookings
                </Link>
                <Link :href="route('zamstay.agent.dashboard')" class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors">
                  Agent
                </Link>
              </template>
            </div>

            <!-- User Menu -->
            <div v-if="user" class="relative">
              <div class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-gray-100">
                <div class="h-8 w-8 rounded-full bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center">
                  <span class="text-sm font-semibold text-white">{{ getInitials(user.name) }}</span>
                </div>
                <span class="hidden sm:inline text-sm font-medium text-gray-700">{{ user.name }}</span>
              </div>
            </div>

            <template v-else>
              <Link :href="route('login')" class="hidden sm:inline-flex px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                Sign In
              </Link>
              <Link :href="route('register')" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors">
                Join
              </Link>
            </template>
          </div>
        </div>

        <!-- Mobile Menu -->
        <Transition
          enter-active-class="transition ease-out duration-200"
          enter-from-class="opacity-0 -translate-y-2"
          enter-to-class="opacity-100 translate-y-0"
          leave-active-class="transition ease-in duration-150"
          leave-from-class="opacity-100 translate-y-0"
          leave-to-class="opacity-0 -translate-y-2"
        >
          <div v-if="mobileMenuOpen" class="md:hidden pb-4 space-y-2">
            <form @submit.prevent="handleSearch">
              <div class="relative">
                <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Search by location..."
                  class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-full focus:ring-2 focus:ring-emerald-500 text-sm"
                  autofocus
                />
              </div>
            </form>
            <Link :href="route('zamstay.home')" class="block px-4 py-2 text-gray-700 hover:bg-emerald-50 rounded-lg">Home</Link>
            <Link :href="route('zamstay.search')" class="block px-4 py-2 text-gray-700 hover:bg-emerald-50 rounded-lg">Browse Properties</Link>
            <template v-if="user">
              <Link :href="route('zamstay.host.dashboard')" class="block px-4 py-2 text-gray-700 hover:bg-emerald-50 rounded-lg">Host Dashboard</Link>
              <Link :href="route('zamstay.bookings.index')" class="block px-4 py-2 text-gray-700 hover:bg-emerald-50 rounded-lg">My Bookings</Link>
              <Link :href="route('zamstay.agent.dashboard')" class="block px-4 py-2 text-gray-700 hover:bg-emerald-50 rounded-lg">Agent Dashboard</Link>
              <button @click="handleLogout" class="block w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg">Sign Out</button>
            </template>
            <template v-else>
              <Link :href="route('login')" class="block px-4 py-2 text-gray-700 hover:bg-emerald-50 rounded-lg">Sign In</Link>
              <Link :href="route('register')" class="block px-4 py-2 text-emerald-600 font-medium hover:bg-emerald-50 rounded-lg">Join</Link>
            </template>
          </div>
        </Transition>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="bg-emerald-900 text-white mt-auto">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
          <div>
            <h3 class="font-semibold mb-4">Explore</h3>
            <ul class="space-y-2 text-sm text-emerald-300">
              <li><Link :href="route('zamstay.home')" class="hover:text-white">Home</Link></li>
              <li><Link :href="route('zamstay.search')" class="hover:text-white">Browse Stays</Link></li>
              <li><Link :href="route('zamstay.agents.index')" class="hover:text-white">Tour Agents</Link></li>
            </ul>
          </div>
          <div>
            <h3 class="font-semibold mb-4">Host</h3>
            <ul class="space-y-2 text-sm text-emerald-300">
              <li><Link :href="route('zamstay.host.dashboard')" class="hover:text-white">Host Dashboard</Link></li>
              <li><Link :href="route('zamstay.host.properties.create')" class="hover:text-white">List Your Property</Link></li>
              <li><Link :href="route('zamstay.host.properties')" class="hover:text-white">Manage Properties</Link></li>
            </ul>
          </div>
          <div>
            <h3 class="font-semibold mb-4">Support</h3>
            <ul class="space-y-2 text-sm text-emerald-300">
              <li><Link :href="route('zamstay.home')" class="hover:text-white">FAQ</Link></li>
              <li><Link :href="route('zamstay.home')" class="hover:text-white">Cancellation Policy</Link></li>
              <li><Link :href="route('zamstay.agent.register-form')" class="hover:text-white">Become an Agent</Link></li>
            </ul>
          </div>
          <div>
            <h3 class="font-semibold mb-4">About</h3>
            <ul class="space-y-2 text-sm text-emerald-300">
              <li><Link :href="route('zamstay.home')" class="hover:text-white">About ZamStay</Link></li>
              <li><Link :href="route('zamstay.home')" class="hover:text-white">Privacy Policy</Link></li>
              <li><Link :href="route('zamstay.home')" class="hover:text-white">Terms of Service</Link></li>
            </ul>
          </div>
        </div>
        <div class="border-t border-emerald-800 mt-8 pt-8 text-center text-sm text-emerald-400">
          <p>&copy; {{ new Date().getFullYear() }} ZamStay. All rights reserved.</p>
        </div>
      </div>
    </footer>
  </div>
</template>
