<template>
    <nav class="bg-slate-800 bg-opacity-95 sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <div class="flex items-center">
            <Link :href="route('welcome')" class="flex items-center hover:opacity-90 transition-opacity">
              <div class="flex-shrink-0 flex items-center">
                <Logo size="lg" variant="white" />
              </div>
            </Link>
          </div>
          <div class="hidden md:block">
            <div class="ml-10 flex items-center space-x-6">
              <!-- Simplified Navigation Links -->
              <template v-for="item in filteredNavigationItems" :key="item.name">
                <!-- Regular Link -->
                <Link
                  v-if="!item.dropdown"
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

                <!-- Dropdown Link -->
                <div v-else class="relative" @mouseenter="openDropdown(item.name)" @mouseleave="closeDropdown">
                  <button
                    :class="[
                      'px-3 py-2 text-sm font-medium transition-colors duration-200 flex items-center gap-1',
                      isDropdownActive(item)
                        ? 'text-blue-400 hover:text-blue-300'
                        : 'text-white hover:text-blue-200'
                    ]"
                  >
                    {{ item.name }}
                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': activeDropdown === item.name }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                  </button>
                  
                  <!-- Dropdown Menu -->
                  <transition
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 translate-y-1"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-150"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 translate-y-1"
                  >
                    <div
                      v-show="activeDropdown === item.name"
                      class="absolute left-0 mt-2 w-56 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 overflow-hidden z-50"
                    >
                      <div class="py-1">
                        <Link
                          v-for="subItem in item.dropdown"
                          :key="subItem.name"
                          :href="subItem.url || route(subItem.route)"
                          class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors"
                        >
                          <div class="font-medium">{{ subItem.name }}</div>
                          <div v-if="subItem.description" class="text-xs text-gray-500 mt-0.5">{{ subItem.description }}</div>
                        </Link>
                      </div>
                    </div>
                  </transition>
                </div>
              </template>

              <!-- Show Dashboard if logged in, otherwise Login -->
              <Link
                v-if="isAuthenticated"
                :href="route('dashboard')"
                class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-6 py-2 rounded-full text-sm font-medium hover:from-blue-600 hover:to-blue-700 hover:scale-105 transition-all duration-300 shadow-md"
              >
                Dashboard
              </Link>
              <Link
                v-else
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
            <template v-if="isAuthenticated">
              <Link
                :href="route('dashboard')"
                class="hidden sm:inline-block bg-gradient-to-r from-blue-500 to-blue-600 text-white px-3 py-1.5 rounded-full text-xs font-medium hover:from-blue-600 hover:to-blue-700 transition-all duration-300"
              >
                Dashboard
              </Link>
            </template>
            <template v-else>
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
            </template>
            
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
          <!-- Simplified Mobile Navigation -->
          <template v-for="item in filteredNavigationItems" :key="item.name">
            <!-- Regular Link -->
            <Link
              v-if="!item.dropdown"
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

            <!-- Dropdown Section -->
            <div v-else>
              <button
                @click="toggleMobileDropdown(item.name)"
                :class="[
                  'w-full flex items-center justify-between px-3 py-2 rounded-md text-base font-medium',
                  isDropdownActive(item)
                    ? 'text-blue-400 bg-gray-900'
                    : 'text-gray-300 hover:text-white hover:bg-gray-700'
                ]"
              >
                {{ item.name }}
                <svg class="w-5 h-5 transition-transform" :class="{ 'rotate-180': mobileDropdowns[item.name] }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              
              <!-- Mobile Dropdown Items -->
              <div v-show="mobileDropdowns[item.name]" class="pl-4 space-y-1 mt-1">
                <Link
                  v-for="subItem in item.dropdown"
                  :key="subItem.name"
                  :href="subItem.url || route(subItem.route)"
                  class="block px-3 py-2 rounded-md text-sm text-gray-400 hover:text-white hover:bg-gray-700"
                >
                  {{ subItem.name }}
                </Link>
              </div>
            </div>
          </template>

          <!-- Auth buttons in mobile menu -->
          <div class="flex gap-2 mt-4 pt-4 border-t border-gray-700">
            <template v-if="isAuthenticated">
              <Link
                :href="route('dashboard')"
                class="flex-1 text-center bg-gradient-to-r from-blue-500 to-blue-600 text-white px-4 py-2 rounded-md text-base font-medium hover:from-blue-600 hover:to-blue-700 transition-all duration-300"
              >
                Dashboard
              </Link>
              <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="flex-1 text-center px-4 py-2 rounded-md text-base font-medium text-gray-400 hover:text-white hover:bg-gray-700 transition-all duration-300"
              >
                Logout
              </Link>
            </template>
            <template v-else>
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
            </template>
          </div>
        </div>
      </div>
    </nav>
  </template>

  <script>
  import { ref, computed } from 'vue';
  import { Link, usePage } from '@inertiajs/vue3';
  import Logo from '../Logo.vue';

  export default {
    components: {
      Link,
      Logo
    },

    setup() {
      const page = usePage();
      const isMobileMenuOpen = ref(false);
      const activeDropdown = ref(null);
      const mobileDropdowns = ref({});
      
      // Check if user is authenticated - access props.value for reactivity
      const isAuthenticated = computed(() => {
        const authUser = page.props.auth?.user;
        return !!authUser;
      });
      
      const user = computed(() => {
        return page.props.auth?.user;
      });

      // Simplified navigation - with Services dropdown
      const navigationItems = [
        { name: 'Home', route: 'welcome' },
        { name: 'About', route: 'about' },
        { name: 'Our Apps', route: 'apps.index' },
        { 
          name: 'Services', 
          dropdown: [
            { name: 'GrowMarket', url: '/growmarket', description: 'Browse products & services' },
            { name: 'GrowBuilder', url: '/growbuilder', description: 'Build professional websites' },
            { name: 'Training', route: 'training', description: 'Learn new skills' },
            { name: 'Venture Builder', route: 'ventures.about', description: 'Co-invest in businesses' },
            { name: 'Business Growth Fund', route: 'bgf.about', description: 'Funding for your business' },
          ]
        },
        { name: 'Rewards & Loyalty', route: 'loyalty-reward.index' },
        { name: 'Referral Program', route: 'referral-program' },
        { name: 'Contact', route: 'contact' },
      ];

      // Filter out routes that Ziggy doesn't expose (but keep items with direct URLs)
      const filteredNavigationItems = computed(() => {
        return navigationItems.map(item => {
          if (item.dropdown) {
            // Filter dropdown items - keep items with url OR valid route
            const filteredDropdown = item.dropdown.filter(subItem => {
              if (subItem.url) return true; // Always keep items with direct URLs
              try {
                return route().has(subItem.route);
              } catch (e) {
                return false;
              }
            });
            // Only include dropdown if it has items
            return filteredDropdown.length > 0 ? { ...item, dropdown: filteredDropdown } : null;
          } else {
            // Check if regular route exists
            try {
              return route().has(item.route) ? item : null;
            } catch (e) {
              return null;
            }
          }
        }).filter(item => item !== null);
      });

      const openDropdown = (name) => {
        activeDropdown.value = name;
      };

      const closeDropdown = () => {
        activeDropdown.value = null;
      };

      const toggleMobileDropdown = (name) => {
        mobileDropdowns.value[name] = !mobileDropdowns.value[name];
      };

      const isDropdownActive = (item) => {
        if (!item.dropdown) return false;
        return item.dropdown.some(subItem => {
          try {
            return route().has(subItem.route) && route().current(subItem.route);
          } catch (e) {
            return false;
          }
        });
      };

      return {
        isMobileMenuOpen,
        activeDropdown,
        mobileDropdowns,
        filteredNavigationItems,
        openDropdown,
        closeDropdown,
        toggleMobileDropdown,
        isDropdownActive,
        isAuthenticated,
        user,
      };
    }
  }
  </script>
