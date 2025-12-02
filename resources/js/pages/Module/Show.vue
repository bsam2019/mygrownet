<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';
import { 
  ArrowLeftIcon,
  EllipsisVerticalIcon,
  UserCircleIcon,
  Cog6ToothIcon,
  CubeIcon,
  WalletIcon,
  ChartBarIcon,
  HeartIcon,
  AcademicCapIcon,
  BuildingOfficeIcon,
  BanknotesIcon
} from '@heroicons/vue/24/outline';
import AppLogo from '@/components/AppLogo.vue';

interface Module {
  id: string;
  name: string;
  slug: string;
  category: string;
  description: string | null;
  icon: string | null;
  color: string | null;
  thumbnail: string | null;
  status: string;
  version: string;
}

interface Access {
  has_access: boolean;
  access_type: string;
  reason: string | null;
  subscription: any | null;
}

interface Props {
  module: Module;
  access: Access;
}

const props = defineProps<Props>();

const logout = () => {
  router.post('/logout');
};

const goBack = () => {
  router.visit('/home-hub');
};

// Icon mapping for modules
const iconMap: Record<string, any> = {
  'mlm-dashboard': UserCircleIcon,
  'mygrownet-core': UserCircleIcon,
  'sme-accounting': ChartBarIcon,
  'mygrow-save': WalletIcon,
  'personal-finance': BanknotesIcon,
  'wedding-planner': HeartIcon,
  'learning': AcademicCapIcon,
  'enterprise': BuildingOfficeIcon,
};

const getModuleIcon = () => iconMap[props.module.slug] || CubeIcon;
</script>

<template>
  <Head :title="module.name" />

  <div class="min-h-screen bg-white">
    <!-- Header -->
    <header 
      class="text-white"
      :style="{ backgroundColor: module.color || '#3B82F6' }"
    >
      <div class="max-w-lg mx-auto px-4 py-4">
        <div class="flex items-center justify-between">
          <!-- Back Button -->
          <button 
            @click="goBack"
            class="p-2 hover:bg-white/10 rounded-lg transition-colors"
          >
            <ArrowLeftIcon class="w-6 h-6" />
          </button>

          <!-- Module Name -->
          <h1 class="text-lg font-semibold">{{ module.name }}</h1>

          <!-- Menu -->
          <Menu as="div" class="relative">
            <MenuButton class="p-2 hover:bg-white/10 rounded-lg transition-colors">
              <EllipsisVerticalIcon class="w-6 h-6" />
            </MenuButton>

            <MenuItems class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
              <div class="py-1">
                <MenuItem v-slot="{ active }">
                  <Link
                    href="/settings/profile"
                    :class="[active ? 'bg-gray-50' : '', 'block px-4 py-2 text-sm text-gray-700']"
                  >
                    Settings
                  </Link>
                </MenuItem>
                <MenuItem v-slot="{ active }">
                  <button
                    @click="logout"
                    :class="[active ? 'bg-gray-50' : '', 'block w-full text-left px-4 py-2 text-sm text-red-600']"
                  >
                    Logout
                  </button>
                </MenuItem>
              </div>
            </MenuItems>
          </Menu>
        </div>
      </div>

      <!-- Module Hero -->
      <div class="max-w-lg mx-auto px-4 py-8 text-center">
        <div class="w-20 h-20 mx-auto mb-4 bg-white/20 rounded-2xl flex items-center justify-center">
          <component :is="getModuleIcon()" class="w-12 h-12" />
        </div>
        <p class="text-white/80 text-sm">{{ module.description }}</p>
        
        <!-- Subscription Badge -->
        <div v-if="access.subscription" class="mt-4">
          <span class="px-3 py-1 bg-white/20 rounded-full text-sm">
            {{ access.subscription.tier }} Plan
          </span>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-lg mx-auto px-4 py-6">
      <!-- Module Content Placeholder -->
      <div class="bg-gray-50 rounded-2xl p-6 text-center">
        <div class="w-16 h-16 mx-auto mb-4 bg-gray-200 rounded-xl flex items-center justify-center">
          <Cog6ToothIcon class="w-8 h-8 text-gray-400" />
        </div>
        <h2 class="text-lg font-semibold text-gray-900 mb-2">Module Content</h2>
        <p class="text-gray-500 text-sm">
          This is a placeholder for module-specific content. Each module will have its own
          unique interface and functionality here.
        </p>
      </div>

      <!-- Module Info -->
      <div class="mt-6 space-y-4">
        <div class="flex justify-between items-center py-3 border-b border-gray-100">
          <span class="text-gray-500">Version</span>
          <span class="text-gray-900 font-medium">{{ module.version }}</span>
        </div>
        <div class="flex justify-between items-center py-3 border-b border-gray-100">
          <span class="text-gray-500">Category</span>
          <span class="text-gray-900 font-medium capitalize">{{ module.category }}</span>
        </div>
        <div class="flex justify-between items-center py-3 border-b border-gray-100">
          <span class="text-gray-500">Status</span>
          <span 
            class="px-2 py-1 text-xs font-medium rounded-full"
            :class="module.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'"
          >
            {{ module.status }}
          </span>
        </div>
        <div v-if="access.access_type" class="flex justify-between items-center py-3 border-b border-gray-100">
          <span class="text-gray-500">Access Type</span>
          <span class="text-gray-900 font-medium capitalize">{{ access.access_type }}</span>
        </div>
      </div>
    </main>
  </div>
</template>
