<template>
    <nav class="bg-slate-800 bg-opacity-95 sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <div class="flex items-center">
            <Link :href="route('home')" class="flex items-center hover:opacity-90 transition-opacity">
              <div class="flex-shrink-0 flex items-center">
                <Logo size="lg" variant="white" />
              </div>
            </Link>
          </div>
          <div class="hidden md:block">
            <div class="ml-10 flex items-center space-x-8">
              <Link
                v-for="item in filteredNavigationItems"
                :key="item.name"
                :href="route(item.route)"
                :class="[
                  'px-3 py-2 text-sm font-medium transition-colors duration-200',
                  (route().has(item.route) && route().current(item.route))
                    ? 'text-blue-400 hover:text-blue-300'
                    : 'text-white hover:text-blue-200'
                ]"
              >
                {{ item.name }}
              </Link>
              <Link
                :href="route('login')"
                class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-6 py-2 rounded-full text-sm font-medium hover:from-blue-600 hover:to-blue-700 hover:scale-105 transition-all duration-300 shadow-md"
              >
                Login
              </Link>
            </div>
          </div>

          <!-- Mobile menu button -->
          <div class="md:hidden">
            <button
              @click="isMobileMenuOpen = !isMobileMenuOpen"
              class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none"
            >
              <span class="sr-only">Open main menu</span>
              <svg
                class="h-6 w-6"
                :class="{'hidden': isMobileMenuOpen, 'block': !isMobileMenuOpen}"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
              <svg
                class="h-6 w-6"
                :class="{'block': isMobileMenuOpen, 'hidden': !isMobileMenuOpen}"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile menu -->
      <div
        class="md:hidden"
        :class="{'block': isMobileMenuOpen, 'hidden': !isMobileMenuOpen}"
      >
        <div class="px-2 pt-2 pb-3 space-y-1">
          <Link
            v-for="item in filteredNavigationItems"
            :key="item.name"
            :href="route(item.route)"
            :class="[
              'block px-3 py-2 rounded-md text-base font-medium',
              (route().has(item.route) && route().current(item.route))
                ? 'text-blue-400 bg-gray-900'
                : 'text-gray-300 hover:text-white hover:bg-gray-700'
            ]"
          >
            {{ item.name }}
          </Link>
          <Link
            :href="route('login')"
            class="block w-full text-center bg-gradient-to-r from-blue-500 to-blue-600 text-white px-6 py-2 rounded-md text-base font-medium hover:from-blue-600 hover:to-blue-700 transition-all duration-300 mt-4"
          >
            Login
          </Link>
        </div>
      </div>
    </nav>
  </template>

  <script>
  import { ref } from 'vue';
  import { Link } from '@inertiajs/vue3';
  import Logo from '../Logo.vue';

  export default {
    components: {
      Link,
      Logo
    },

    setup() {
      const isMobileMenuOpen = ref(false);

      const navigationItems = [
        { name: 'Home', route: 'home' },
        { name: 'About', route: 'about' },
        { name: 'Shop', route: 'shop.index' },
        { name: 'Membership', route: 'membership.index' },
        { name: 'Compliance', route: 'compliance.information' },
        { name: 'Careers', route: 'careers.index' },
        { name: 'Contact', route: 'contact' },
      ];

      // Filter out routes that Ziggy doesn't expose on the current page/session
      const filteredNavigationItems = navigationItems.filter((item) => {
        try {
          return route().has(item.route);
        } catch (e) {
          return false;
        }
      });

      return {
        isMobileMenuOpen,
        navigationItems,
        filteredNavigationItems
      };
    }
  }
  </script>
