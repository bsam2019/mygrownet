<script setup lang="ts">
import { ref, computed } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import {
  Bars3Icon,
  XMarkIcon,
  HomeIcon,
  DocumentTextIcon,
  CurrencyDollarIcon,
  ArrowRightOnRectangleIcon,
  UserIcon,
  DocumentCheckIcon,
} from '@heroicons/vue/24/outline'

const page = usePage()
const user = computed(() => page.props.auth?.user)
const mobileMenuOpen = ref(false)

const navigation = [
  { name: 'Dashboard', href: 'portal.dashboard', icon: HomeIcon },
  { name: 'Invoices', href: 'portal.invoices', icon: DocumentTextIcon },
  { name: 'Quotes', href: 'portal.quotes', icon: CurrencyDollarIcon },
  { name: 'Payments', href: 'portal.payments', icon: CurrencyDollarIcon },
  { name: 'Contracts', href: 'portal.contracts', icon: DocumentCheckIcon },
]

const isActive = (routeName: string) => {
  return route().current(routeName) || route().current(routeName + '.*')
}

const logout = () => {
  router.post(route('portal.logout'))
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navigation (hidden for login/register) -->
    <nav v-if="user" class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <Link :href="route('portal.dashboard')" class="flex items-center gap-2">
              <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-sm">P</span>
              </div>
              <span class="text-lg font-bold text-gray-900">Customer Portal</span>
            </Link>
            <div class="hidden sm:ml-10 sm:flex sm:items-center sm:gap-1">
              <Link
                v-for="item in navigation"
                :key="item.name"
                :href="route(item.href)"
                :class="[
                  'px-3 py-2 rounded-lg text-sm font-medium transition-colors',
                  isActive(item.href)
                    ? 'bg-blue-50 text-blue-700'
                    : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'
                ]"
              >
                {{ item.name }}
              </Link>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <div class="hidden sm:flex items-center gap-2 text-sm text-gray-600">
              <UserIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
              {{ user?.name }}
            </div>
            <button
              @click="logout"
              class="hidden sm:inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-lg"
            >
              <ArrowRightOnRectangleIcon class="h-5 w-5" aria-hidden="true" />
              Sign Out
            </button>
            <button
              @click="mobileMenuOpen = !mobileMenuOpen"
              class="sm:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-50"
            >
              <Bars3Icon v-if="!mobileMenuOpen" class="h-6 w-6" aria-hidden="true" />
              <XMarkIcon v-else class="h-6 w-6" aria-hidden="true" />
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile menu -->
      <div v-if="mobileMenuOpen" class="sm:hidden border-t border-gray-200">
        <div class="px-4 py-3 space-y-1">
          <Link
            v-for="item in navigation"
            :key="item.name"
            :href="route(item.href)"
            :class="[
              'block px-3 py-2 rounded-lg text-sm font-medium',
              isActive(item.href) ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50'
            ]"
            @click="mobileMenuOpen = false"
          >
            {{ item.name }}
          </Link>
          <hr class="my-2 border-gray-200" />
          <div class="px-3 py-2 text-sm text-gray-500">{{ user?.name }}</div>
          <button @click="logout" class="block w-full text-left px-3 py-2 rounded-lg text-sm font-medium text-red-600 hover:bg-red-50">
            Sign Out
          </button>
        </div>
      </div>
    </nav>

    <!-- Page Content -->
    <main>
      <slot />
    </main>

    <!-- Footer -->
    <footer v-if="user" class="bg-white border-t border-gray-200 mt-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <p class="text-center text-sm text-gray-500">Customer Portal &copy; {{ new Date().getFullYear() }}</p>
      </div>
    </footer>
  </div>
</template>
