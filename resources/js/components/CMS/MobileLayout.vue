<template>
  <div class="min-h-screen bg-gray-50 pb-20 md:pb-0">
    <!-- Mobile Header -->
    <header class="sticky top-0 z-40 bg-white border-b border-gray-200 shadow-sm">
      <div class="px-4 py-3">
        <div class="flex items-center justify-between">
          <div class="flex-1">
            <Link
              :href="route('cms.dashboard')"
              class="text-xs text-blue-600 hover:text-blue-800 mb-1 inline-block md:hidden"
            >
              ← Dashboard
            </Link>
            <h1 class="text-lg font-bold text-gray-900 truncate">
              {{ title }}
            </h1>
            <p v-if="subtitle" class="text-xs text-gray-500">{{ subtitle }}</p>
          </div>
          <slot name="header-actions">
            <button
              @click="showMenu = !showMenu"
              class="p-2 rounded-lg hover:bg-gray-100 md:hidden"
              aria-label="Toggle menu"
            >
              <Bars3Icon v-if="!showMenu" class="h-6 w-6 text-gray-600" aria-hidden="true" />
              <XMarkIcon v-else class="h-6 w-6 text-gray-600" aria-hidden="true" />
            </button>
          </slot>
        </div>
      </div>

      <!-- Mobile Menu Dropdown -->
      <div v-if="showMenu" class="border-t border-gray-200 bg-white md:hidden">
        <div class="px-4 py-3 space-y-1">
          <Link
            :href="route('cms.dashboard')"
            class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100"
            @click="showMenu = false"
          >
            Dashboard
          </Link>
          <Link
            :href="route('cms.jobs.index')"
            class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100"
            @click="showMenu = false"
          >
            Jobs
          </Link>
          <Link
            :href="route('cms.customers.index')"
            class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100"
            @click="showMenu = false"
          >
            Customers
          </Link>
          <Link
            :href="route('cms.invoices.index')"
            class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100"
            @click="showMenu = false"
          >
            Invoices
          </Link>
          <Link
            :href="route('cms.payments.index')"
            class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100"
            @click="showMenu = false"
          >
            Payments
          </Link>
          <Link
            :href="route('cms.reports.index')"
            class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100"
            @click="showMenu = false"
          >
            Reports
          </Link>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="px-4 py-4 md:max-w-7xl md:mx-auto md:px-6 lg:px-8 md:py-8">
      <slot />
    </main>

    <!-- Mobile Bottom Navigation -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 md:hidden z-50">
      <div class="grid grid-cols-5 h-16">
        <Link
          :href="route('cms.dashboard')"
          :class="[
            'flex flex-col items-center justify-center gap-1',
            currentRoute === 'cms.dashboard' ? 'text-blue-600' : 'text-gray-600'
          ]"
        >
          <HomeIcon class="h-6 w-6" aria-hidden="true" />
          <span class="text-xs font-medium">Home</span>
        </Link>
        
        <Link
          :href="route('cms.jobs.index')"
          :class="[
            'flex flex-col items-center justify-center gap-1',
            currentRoute === 'cms.jobs.index' ? 'text-blue-600' : 'text-gray-600'
          ]"
        >
          <BriefcaseIcon class="h-6 w-6" aria-hidden="true" />
          <span class="text-xs font-medium">Jobs</span>
        </Link>
        
        <Link
          :href="route('cms.invoices.index')"
          :class="[
            'flex flex-col items-center justify-center gap-1',
            currentRoute === 'cms.invoices.index' ? 'text-blue-600' : 'text-gray-600'
          ]"
        >
          <DocumentTextIcon class="h-6 w-6" aria-hidden="true" />
          <span class="text-xs font-medium">Invoices</span>
        </Link>
        
        <Link
          :href="route('cms.customers.index')"
          :class="[
            'flex flex-col items-center justify-center gap-1',
            currentRoute === 'cms.customers.index' ? 'text-blue-600' : 'text-gray-600'
          ]"
        >
          <UsersIcon class="h-6 w-6" aria-hidden="true" />
          <span class="text-xs font-medium">Customers</span>
        </Link>
        
        <Link
          :href="route('cms.reports.index')"
          :class="[
            'flex flex-col items-center justify-center gap-1',
            currentRoute === 'cms.reports.index' ? 'text-blue-600' : 'text-gray-600'
          ]"
        >
          <ChartBarIcon class="h-6 w-6" aria-hidden="true" />
          <span class="text-xs font-medium">Reports</span>
        </Link>
      </div>
    </nav>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import {
  BriefcaseIcon,
  UsersIcon,
  DocumentTextIcon,
  Bars3Icon,
  XMarkIcon,
  HomeIcon,
  ChartBarIcon,
} from '@heroicons/vue/24/outline'

interface Props {
  title: string
  subtitle?: string
}

defineProps<Props>()

const showMenu = ref(false)
const page = usePage()

const currentRoute = computed(() => {
  return page.component.value
})
</script>
