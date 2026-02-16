<script setup lang="ts">
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue'
import { EllipsisVerticalIcon } from '@heroicons/vue/24/outline'

interface Props {
  title: string
  subtitle?: string
  showMoreMenu?: boolean
}

defineProps<Props>()
</script>

<template>
  <div class="bg-white border-b border-gray-200">
    <div class="px-4 sm:px-6 lg:px-8 py-4">
      <div class="flex items-center justify-between min-h-12">
        <!-- Left: Title & Subtitle -->
        <div class="flex items-center gap-3">
          <div>
            <h1 class="text-xl font-bold text-gray-900">{{ title }}</h1>
            <p v-if="subtitle" class="text-sm text-gray-500">{{ subtitle }}</p>
          </div>
        </div>

        <!-- Right: Filters, Actions, Menu -->
        <div class="flex items-center gap-3">
          <!-- Quick Filters Slot -->
          <div class="hidden lg:flex items-center gap-2">
            <slot name="filters" />
          </div>

          <!-- Action Buttons Slot -->
          <div class="flex items-center gap-2">
            <slot name="actions" />
          </div>

          <!-- More Menu Slot -->
          <div v-if="showMoreMenu" class="flex items-center">
            <Menu as="div" class="relative">
              <MenuButton class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                <EllipsisVerticalIcon class="h-5 w-5" aria-hidden="true" />
              </MenuButton>
              <MenuItems class="absolute right-0 mt-2 w-56 origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                <div class="py-1">
                  <slot name="menu" />
                </div>
              </MenuItems>
            </Menu>
          </div>
        </div>
      </div>

      <!-- Mobile Filters (collapsible) -->
      <div class="lg:hidden pb-4">
        <slot name="mobile-filters" />
      </div>
    </div>
  </div>
</template>
