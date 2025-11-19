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
            <div class="ml-10 flex items-center space-x-6">
              <!-- Regular Links -->
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
              
              <!-- Growth Opportunities Dropdown -->
              <div class="relative" @mouseenter="handleGrowthEnter" @mouseleave="handleGrowthLeave">
                <button
                  :class="[
                    'px-3 py-2 text-sm font-medium transition-colors duration-200 flex items-center gap-1',
                    isGrowthActive ? 'text-blue-400' : 'text-white hover:text-blue-200'
                  ]"
                >
                  Opportunities
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </button>
                <div
                  v-show="showGrowth"
                  @mouseenter="handleGrowthEnter"
                  @mouseleave="handleGrowthLeave"
                  class="absolute left-0 mt-0 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50"
                >
                  <div class="py-1">
                    <Link
                      v-for="item in growthItems"
                      :key="item.name"
                      :href="route(item.route)"
                      class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors"
                    >
                      {{ item.name }}
                    </Link>
                  </div>
                </div>
              </div>
              
              <!-- Marketplace Dropdown -->
              <div class="relative" @mouseenter="handleMarketplaceEnter" @mouseleave="handleMarketplaceLeave">
                <button
                  :class="[
                    'px-3 py-2 text-sm font-medium transition-colors duration-200 flex items-center gap-1',
                    isMarketplaceActive ? 'text-blue-400' : 'text-white hover:text-blue-200'
                  ]"
                >
                  Marketplace
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </button>
                <div
                  v-show="showMarketplace"
                  @mouseenter="handleMarketplaceEnter"
                  @mouseleave="handleMarketplaceLeave"
                  class="absolute left-0 mt-0 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50"
                >
                  <div class="py-1">
                    <Link
                      v-for="item in marketplaceItems"
                      :key="item.name"
                      :href="route(item.route)"
                      class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors"
                    >
                      {{ item.name }}
                    </Link>
                  </div>
                </div>
              </div>

              <Link
                :href="route('login')"
                class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-6 py-2 rounded-full text-sm font-medium hover:from-blue-600 hover:to-blue-700 hover:scale-105 transition-all duration-300 shadow-md"
              >
                Login
              </Link>
            </div>
          </div>

          <!-- Mobile menu button and auth links -->
          <div class="md:hidden flex items-center gap-2">
            <!-- Mobile Auth Links (visible on mobile) -->
            <Link
              :href="route('register')"
              class="hidden sm:inline-block px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 transition-colors"
            >
              Register
            </Link>
            <Link
              :href="route('login')"
              class="hidden sm:inline-block bg-gradient-to-r from-blue-500 to-blue-600 text-white px-3 py-1.5 rounded-full text-xs font-medium hover:from-blue-600 hover:to-blue-700 transition-all duration-300"
            >
              Login
            </Link>
            
            <!-- Menu button -->
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
          
          <!-- Mobile Growth Opportunities Section -->
          <div class="pt-2">
            <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">
              Growth Opportunities
            </div>
            <Link
              v-for="item in growthItems"
              :key="item.name"
              :href="route(item.route)"
              class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:text-white hover:bg-gray-700"
            >
              {{ item.name }}
            </Link>
          </div>
          
          <!-- Mobile Marketplace Section -->
          <div class="pt-2">
            <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">
              Marketplace
            </div>
            <Link
              v-for="item in marketplaceItems"
              :key="item.name"
              :href="route(item.route)"
              class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:text-white hover:bg-gray-700"
            >
              {{ item.name }}
            </Link>
          </div>

          <!-- Auth buttons in mobile menu -->
          <div class="flex gap-2 mt-4 pt-4 border-t border-gray-700">
            <Link
              :href="route('register')"
              class="flex-1 text-center px-4 py-2 rounded-md text-base font-medium text-blue-400 hover:text-blue-300 hover:bg-gray-700 transition-all duration-300"
            >
              Register
            </Link>
            <Link
              :href="route('login')"
              class="flex-1 text-center bg-gradient-to-r from-blue-500 to-blue-600 text-white px-4 py-2 rounded-md text-base font-medium hover:from-blue-600 hover:to-blue-700 transition-all duration-300"
            >
              Login
            </Link>
          </div>
        </div>
      </div>
    </nav>
  </template>

  <script>
  import { ref, computed } from 'vue';
  import { Link } from '@inertiajs/vue3';
  import Logo from '../Logo.vue';

  export default {
    components: {
      Link,
      Logo
    },

    setup() {
      const isMobileMenuOpen = ref(false);
      const showGrowth = ref(false);
      const showMarketplace = ref(false);
      let growthTimeout = null;
      let marketplaceTimeout = null;

      const navigationItems = [
        { name: 'Home', route: 'home' },
        { name: 'About', route: 'about' },
        { name: 'Membership', route: 'membership.index' },
      ];

      const growthItems = [
        { name: 'Venture Builder', route: 'ventures.about' },
        { name: 'Business Growth Fund', route: 'bgf.about' },
      ];

      const marketplaceItems = [
        { name: 'Venture Investments', route: 'ventures.index' },
        { name: 'MyGrow Shop', route: 'shop.index' },
      ];

      // Filter out routes that Ziggy doesn't expose
      const filteredNavigationItems = navigationItems.filter((item) => {
        try {
          return route().has(item.route);
        } catch (e) {
          return false;
        }
      });

      // Dropdown handlers with delay
      const handleGrowthEnter = () => {
        clearTimeout(growthTimeout);
        showGrowth.value = true;
      };

      const handleGrowthLeave = () => {
        growthTimeout = setTimeout(() => {
          showGrowth.value = false;
        }, 200);
      };

      const handleMarketplaceEnter = () => {
        clearTimeout(marketplaceTimeout);
        showMarketplace.value = true;
      };

      const handleMarketplaceLeave = () => {
        marketplaceTimeout = setTimeout(() => {
          showMarketplace.value = false;
        }, 200);
      };

      // Check if we're on a growth opportunities page
      const isGrowthActive = computed(() => {
        try {
          return route().current('ventures.about') || route().current('bgf.*');
        } catch (e) {
          return false;
        }
      });

      // Check if we're on a marketplace page
      const isMarketplaceActive = computed(() => {
        try {
          return route().current('ventures.index') || route().current('shop.*');
        } catch (e) {
          return false;
        }
      });

      return {
        isMobileMenuOpen,
        showGrowth,
        showMarketplace,
        navigationItems,
        growthItems,
        marketplaceItems,
        filteredNavigationItems,
        isGrowthActive,
        isMarketplaceActive,
        handleGrowthEnter,
        handleGrowthLeave,
        handleMarketplaceEnter,
        handleMarketplaceLeave
      };
    }
  }
  </script>
