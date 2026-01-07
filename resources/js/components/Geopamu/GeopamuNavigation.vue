<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Bars3Icon, XMarkIcon } from '@heroicons/vue/24/outline';

const mobileMenuOpen = ref(false);

const navigation = [
  { name: 'Home', href: '/geopamu' },
  { name: 'Services', href: '/geopamu/services' },
  { name: 'Portfolio', href: '/geopamu/portfolio' },
  { name: 'Blog', href: '/geopamu/blog' },
  { name: 'About', href: '/geopamu/about' },
  { name: 'Contact', href: '/geopamu/contact' }
];
</script>

<template>
  <nav class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-20">
        <!-- Logo -->
        <Link href="/geopamu" class="flex items-center">
          <div class="flex items-center space-x-3">
            <div class="w-12 h-12 rounded-full overflow-hidden flex-shrink-0">
              <img 
                src="/geopamu-assets/logo.png" 
                alt="Geopamu Logo"
                class="w-full h-full object-contain"
              />
            </div>
            <div>
              <div class="text-2xl font-bold text-gray-900">Geopamu</div>
              <div class="text-xs text-gray-600">Printing & Branding</div>
            </div>
          </div>
        </Link>

        <!-- Desktop Navigation -->
        <div class="hidden md:flex items-center space-x-8">
          <Link
            v-for="item in navigation"
            :key="item.name"
            :href="item.href"
            class="text-gray-700 hover:text-blue-600 font-medium transition-colors"
          >
            {{ item.name }}
          </Link>
          
          <a
            href="#quote"
            class="bg-red-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-red-700 transition-colors"
          >
            Get Quote
          </a>
        </div>

        <!-- Mobile menu button -->
        <button
          @click="mobileMenuOpen = !mobileMenuOpen"
          class="md:hidden p-2 rounded-lg hover:bg-gray-100"
          :aria-label="mobileMenuOpen ? 'Close menu' : 'Open menu'"
        >
          <Bars3Icon v-if="!mobileMenuOpen" class="h-6 w-6" aria-hidden="true" />
          <XMarkIcon v-else class="h-6 w-6" aria-hidden="true" />
        </button>
      </div>
    </div>

    <!-- Mobile Navigation -->
    <div v-if="mobileMenuOpen" class="md:hidden border-t border-gray-200">
      <div class="px-4 py-4 space-y-2">
        <Link
          v-for="item in navigation"
          :key="item.name"
          :href="item.href"
          class="block px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg font-medium"
          @click="mobileMenuOpen = false"
        >
          {{ item.name }}
        </Link>
        <a
          href="#quote"
          class="block text-center bg-red-600 text-white px-4 py-3 rounded-lg font-semibold hover:bg-red-700 transition-colors"
        >
          Get Quote
        </a>
      </div>
    </div>
  </nav>
</template>
