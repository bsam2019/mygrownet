<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';
import { 
  Cog6ToothIcon,
  ArrowRightOnRectangleIcon,
  UserCircleIcon,
  ClipboardDocumentCheckIcon,
  ChartBarIcon,
  ChatBubbleLeftRightIcon,
  ShoppingCartIcon,
  CubeIcon,
  WalletIcon,
  BuildingOfficeIcon,
  HeartIcon,
  AcademicCapIcon,
  BanknotesIcon,
  HomeIcon,
  UsersIcon,
  CalendarIcon,
  DocumentTextIcon,
  GiftIcon,
  TruckIcon,
  ShieldCheckIcon,
  SparklesIcon,
  RocketLaunchIcon
} from '@heroicons/vue/24/solid';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import SubscriptionModal from '@/components/HomeHub/SubscriptionModal.vue';

interface Module {
  id: string;
  name: string;
  slug: string;
  category: string;
  description: string | null;
  icon: string | null;
  color: string | null;
  thumbnail: string | null;
  has_access: boolean;
  is_subscribed: boolean;
  subscription_status: string | null;
  subscription_tier: string | null;
  requires_subscription: boolean;
  subscription_tiers: Record<string, any> | null;
  primary_route: string;
  is_pwa: boolean;
  status: string;
}

interface Props {
  modules: Module[];
  accountType: string;
  auth?: {
    user: {
      name: string;
      email: string;
    };
  };
}

const props = defineProps<Props>();

// Subscription modal state
const showSubscriptionModal = ref(false);
const selectedModule = ref<Module | null>(null);

const handleModuleClick = (module: Module) => {
  // GrowBiz is always accessible - it has its own setup flow
  if (module.slug === 'growbiz') {
    router.visit(module.primary_route || '/growbiz');
    return;
  }
  
  // BizBoost is always accessible - it has its own setup flow
  if (module.slug === 'bizboost') {
    router.visit(module.primary_route || '/bizboost');
    return;
  }
  
  if (module.has_access) {
    router.visit(module.primary_route);
  } else if (module.requires_subscription && module.subscription_tiers) {
    // Open subscription modal
    selectedModule.value = module;
    showSubscriptionModal.value = true;
  } else {
    // Free module without access - might need account type upgrade
    alert('This module is not available for your account type.');
  }
};

const handleSubscribed = () => {
  // Refresh the page to update module access
  router.reload();
};

const logout = () => {
  router.post('/logout');
};

// Icon mapping for modules - using solid icons for better visibility
const getModuleIcon = (slug: string) => {
  const iconMap: Record<string, any> = {
    'mlm-dashboard': UsersIcon,
    'mygrownet-core': HomeIcon,
    'dashboard': HomeIcon,
    'growbiz': ClipboardDocumentCheckIcon,
    'task-management': ClipboardDocumentCheckIcon,
    'smart-accounting': ChartBarIcon,
    'sme-accounting': ChartBarIcon,
    'accounting': ChartBarIcon,
    'growfinance': BanknotesIcon,
    'bizboost': SparklesIcon,
    'messaging': ChatBubbleLeftRightIcon,
    'marketplace': ShoppingCartIcon,
    'shop': ShoppingCartIcon,
    'settings': Cog6ToothIcon,
    'mygrow-save': WalletIcon,
    'wallet': WalletIcon,
    'personal-finance': BanknotesIcon,
    'finance': BanknotesIcon,
    'wedding-planner': HeartIcon,
    'wedding': HeartIcon,
    'learning': AcademicCapIcon,
    'education': AcademicCapIcon,
    'enterprise': BuildingOfficeIcon,
    'business': BuildingOfficeIcon,
    'calendar': CalendarIcon,
    'documents': DocumentTextIcon,
    'rewards': GiftIcon,
    'loyalty': GiftIcon,
    'delivery': TruckIcon,
    'logistics': TruckIcon,
    'security': ShieldCheckIcon,
    'premium': SparklesIcon,
  };
  return iconMap[slug] || CubeIcon;
};
</script>

<template>
  <Head title="Home" />

  <div class="min-h-screen bg-gray-50">
    <!-- Header - Logo Left, Settings Right -->
    <header class="bg-white shadow-sm sticky top-0 z-40">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <!-- Logo - Top Left -->
          <Link href="/" class="flex items-center gap-2">
            <AppLogoIcon class="h-9 w-9" />
          </Link>

          <!-- Settings Dropdown - Top Right -->
          <Menu as="div" class="relative">
            <MenuButton class="flex items-center gap-2 p-2 hover:bg-gray-100 rounded-full transition-colors">
              <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                <UserCircleIcon class="w-6 h-6 text-white" />
              </div>
            </MenuButton>

            <MenuItems class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg ring-1 ring-black/5 focus:outline-none z-50 overflow-hidden">
              <div class="px-4 py-3 border-b border-gray-100">
                <p class="text-sm font-medium text-gray-900">{{ auth?.user?.name || 'User' }}</p>
                <p class="text-xs text-gray-500 truncate">{{ auth?.user?.email || '' }}</p>
              </div>
              <div class="py-1">
                <MenuItem v-slot="{ active }">
                  <Link
                    href="/settings/profile"
                    :class="[active ? 'bg-gray-50' : '', 'flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700']"
                  >
                    <Cog6ToothIcon class="w-5 h-5 text-gray-400" />
                    Settings
                  </Link>
                </MenuItem>
                <MenuItem v-slot="{ active }">
                  <button
                    @click="logout"
                    :class="[active ? 'bg-red-50' : '', 'flex items-center gap-3 w-full text-left px-4 py-2.5 text-sm text-red-600']"
                  >
                    <ArrowRightOnRectangleIcon class="w-5 h-5" />
                    Logout
                  </button>
                </MenuItem>
              </div>
            </MenuItems>
          </Menu>
        </div>
      </div>
    </header>

    <!-- Main Content - Centered with larger area -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Page Title -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Home</h1>
        <p class="text-gray-500 mt-1">Access your apps and services</p>
      </div>

      <!-- Module Grid - Larger Cards -->
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
        <button
          v-for="module in modules"
          :key="module.id"
          @click="handleModuleClick(module)"
          class="group relative bg-white rounded-2xl p-5 sm:p-6 flex flex-col items-center justify-center gap-3 sm:gap-4 transition-all duration-300 hover:shadow-xl hover:-translate-y-1 active:scale-95 shadow-md border border-gray-100"
        >
          <!-- Icon Container with gradient background -->
          <div 
            class="w-14 h-14 sm:w-16 sm:h-16 flex items-center justify-center rounded-2xl transition-transform duration-300 group-hover:scale-110"
            :style="{ backgroundColor: module.color || '#3B82F6' }"
          >
            <component 
              :is="getModuleIcon(module.slug)" 
              class="w-7 h-7 sm:w-8 sm:h-8 text-white"
            />
          </div>
          
          <!-- Module Name -->
          <span class="text-gray-800 text-center font-semibold text-sm leading-tight">
            {{ module.name }}
          </span>

          <!-- Status Badge -->
          <span 
            v-if="module.status === 'beta'" 
            class="absolute top-2 right-2 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide bg-amber-100 text-amber-700 rounded-full"
          >
            Beta
          </span>
          
          <span 
            v-if="module.status === 'coming_soon'" 
            class="absolute top-2 right-2 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide bg-gray-100 text-gray-600 rounded-full"
          >
            Soon
          </span>

          <!-- Access indicator -->
          <span 
            v-if="!module.has_access && module.requires_subscription" 
            class="absolute bottom-2 right-2 w-2 h-2 bg-orange-400 rounded-full"
            title="Subscription required"
          />
        </button>
      </div>

      <!-- Empty State -->
      <div v-if="modules.length === 0" class="text-center py-20">
        <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-gray-100 flex items-center justify-center">
          <CubeIcon class="w-12 h-12 text-gray-400" />
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">No modules available</h3>
        <p class="text-gray-500 max-w-sm mx-auto">
          There are no modules available for your account type. Contact support for assistance.
        </p>
      </div>
    </main>

    <!-- Footer -->
    <footer class="mt-auto py-6 text-center text-sm text-gray-400">
      <p>&copy; {{ new Date().getFullYear() }} MyGrowNet. All rights reserved.</p>
    </footer>

    <!-- Subscription Modal -->
    <SubscriptionModal
      :module="selectedModule"
      :show="showSubscriptionModal"
      @close="showSubscriptionModal = false"
      @subscribed="handleSubscribed"
    />
  </div>
</template>
