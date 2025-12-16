<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
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
import ImpersonationBanner from '@/components/ImpersonationBanner.vue';

const page = usePage();

// Check if admin is impersonating
const isImpersonating = computed(() => {
  return page.props.impersonate_admin_id !== undefined && page.props.impersonate_admin_id !== null;
});

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
  accountType?: string;
  isPublic?: boolean;
  isAdmin?: boolean;
  isManager?: boolean;
  isEmployee?: boolean;
  walletBalance?: number;
  auth?: {
    user: {
      name: string;
      email: string;
      roles?: string[];
    };
  };
}

const props = withDefaults(defineProps<Props>(), {
  walletBalance: 0,
});

// Check if user is authenticated
const isAuthenticated = computed(() => !!props.auth?.user);

// Check user roles
const isAdmin = computed(() => {
  return props.isAdmin || props.auth?.user?.roles?.includes('admin') || props.auth?.user?.roles?.includes('Administrator');
});

const isManager = computed(() => {
  return props.isManager || props.auth?.user?.roles?.includes('manager');
});

const isEmployee = computed(() => {
  return props.isEmployee || props.auth?.user?.roles?.includes('employee');
});

// Subscription modal state
const showSubscriptionModal = ref(false);
const selectedModule = ref<Module | null>(null);

// Define primary modules order (business tools first, then community network, then lifestyle)
const primaryModuleSlugs = ['bizboost', 'growfinance', 'growbiz', 'marketplace', 'grownet', 'lifeplus', 'mlm-dashboard'];

// Computed properties for module organization
const primaryModules = computed(() => {
  const primary = props.modules.filter(m => primaryModuleSlugs.includes(m.slug));
  // Sort by the order defined in primaryModuleSlugs
  return primary.sort((a, b) => {
    return primaryModuleSlugs.indexOf(a.slug) - primaryModuleSlugs.indexOf(b.slug);
  });
});

const additionalModules = computed(() => {
  return props.modules.filter(m => !primaryModuleSlugs.includes(m.slug));
});

// Module descriptions emphasizing business tools
const getModuleDescription = (slug: string): string => {
  const descriptions: Record<string, string> = {
    'bizboost': 'Complete business management & marketing automation',
    'growfinance': 'Accounting & financial management for SMEs',
    'growbiz': 'Team & employee management system',
    'marketplace': 'Shop products & services',
    'grownet': 'Community network & referral rewards',
    'mlm-dashboard': 'Community network & referral rewards',
    'mygrownet-core': 'Community features & rewards',
    'mygrow-save': 'Digital wallet & store credit',
    'learning': 'Business skills & education',
    'rewards': 'Loyalty points & member benefits',
    'lifeplus': 'Health, wellness & lifestyle companion',
  };
  return descriptions[slug] || '';
};

// Get public landing page route for each app
const getPublicLandingRoute = (slug: string): string | null => {
  const publicRoutes: Record<string, string> = {
    'bizboost': '/bizboost/welcome',
    'growfinance': '/growfinance',
    'growbiz': '/growbiz',
    'marketplace': '/marketplace',
    'grownet': '/login', // GrowNet requires login
    'mlm-dashboard': '/login', // MLM Dashboard requires login
    'lifeplus': '/lifeplus/welcome', // LifePlus public landing
  };
  return publicRoutes[slug] || null;
};

const handleModuleClick = (module: Module) => {
  // If user is not authenticated, redirect to public landing page if available
  if (!isAuthenticated.value) {
    const publicRoute = getPublicLandingRoute(module.slug);
    if (publicRoute) {
      router.visit(publicRoute);
    } else {
      // No public landing page, redirect to login
      router.visit('/login', {
        data: { intended: module.primary_route || '/apps' }
      });
    }
    return;
  }

  // GrowNet - Community & referral network dashboard
  if (module.slug === 'grownet') {
    router.visit('/grownet');
    return;
  }

  // GrowBiz is always accessible - it has its own setup flow
  if (module.slug === 'growbiz') {
    router.visit(module.primary_route || '/growbiz/dashboard');
    return;
  }
  
  // GrowFinance is always accessible - it has its own setup flow
  if (module.slug === 'growfinance') {
    router.visit(module.primary_route || '/growfinance/dashboard');
    return;
  }
  
  // BizBoost is always accessible - it has its own setup flow
  if (module.slug === 'bizboost') {
    router.visit(module.primary_route || '/bizboost');
    return;
  }
  
  // LifePlus - Health & Wellness app
  if (module.slug === 'lifeplus') {
    router.visit(module.primary_route || '/lifeplus');
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
    'grownet': UsersIcon,
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
    'lifeplus': HeartIcon, // LifePlus - Health & Wellness
    'health': HeartIcon,
    'wellness': HeartIcon,
  };
  return iconMap[slug] || CubeIcon;
};
</script>

<template>
  <Head :title="isPublic ? 'Our Apps' : 'Home'" />

  <div class="min-h-screen bg-gray-50">
    <!-- Impersonation Banner -->
    <ImpersonationBanner v-if="isImpersonating" />
    
    <!-- Header - Logo Left, Settings/Login Right -->
    <header class="bg-white shadow-sm sticky top-0 z-40">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <!-- Logo - Top Left -->
          <Link href="/" class="flex items-center gap-2">
            <AppLogoIcon class="h-9 w-9" />
          </Link>

          <!-- Authenticated User Menu -->
          <Menu v-if="isAuthenticated" as="div" class="relative">
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
              <!-- Role-specific links -->
              <div v-if="isAdmin || isManager || isEmployee" class="py-1 border-b border-gray-100">
                <MenuItem v-if="isAdmin" v-slot="{ active }">
                  <Link
                    href="/admin/dashboard"
                    :class="[active ? 'bg-indigo-50' : '', 'flex items-center gap-3 px-4 py-2.5 text-sm text-indigo-700']"
                  >
                    <ShieldCheckIcon class="w-5 h-5 text-indigo-500" />
                    Admin Panel
                  </Link>
                </MenuItem>
                <MenuItem v-if="isManager" v-slot="{ active }">
                  <Link
                    href="/manager/dashboard"
                    :class="[active ? 'bg-purple-50' : '', 'flex items-center gap-3 px-4 py-2.5 text-sm text-purple-700']"
                  >
                    <UsersIcon class="w-5 h-5 text-purple-500" />
                    Manager Dashboard
                  </Link>
                </MenuItem>
                <MenuItem v-if="isEmployee" v-slot="{ active }">
                  <Link
                    href="/employee/portal"
                    :class="[active ? 'bg-emerald-50' : '', 'flex items-center gap-3 px-4 py-2.5 text-sm text-emerald-700']"
                  >
                    <ClipboardDocumentCheckIcon class="w-5 h-5 text-emerald-500" />
                    Employee Portal
                  </Link>
                </MenuItem>
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

          <!-- Public User - Login/Register buttons -->
          <div v-else class="flex items-center gap-3">
            <Link
              href="/login"
              class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 transition-colors"
            >
              Login
            </Link>
            <Link
              href="/register"
              class="px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white text-sm font-medium rounded-full hover:from-blue-600 hover:to-blue-700 transition-all shadow-sm"
            >
              Get Started
            </Link>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content - Centered with larger area -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Welcome Banner - Different for public vs authenticated -->
      <div class="mb-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-6 sm:p-8 text-white shadow-lg">
        <div class="flex items-start justify-between">
          <div>
            <h1 v-if="isAuthenticated" class="text-2xl sm:text-3xl font-bold mb-2">Welcome back, {{ auth?.user?.name || 'Member' }}!</h1>
            <h1 v-else class="text-2xl sm:text-3xl font-bold mb-2">Discover Our Business Apps</h1>
            <p v-if="isAuthenticated" class="text-blue-50 text-sm sm:text-base">Your business empowerment platform - Access your tools and services</p>
            <p v-else class="text-blue-50 text-sm sm:text-base">Powerful tools to help you manage, grow, and scale your business</p>
          </div>
          <RocketLaunchIcon class="w-12 h-12 sm:w-16 sm:h-16 text-blue-200 opacity-50 hidden sm:block" aria-hidden="true" />
        </div>
      </div>

      <!-- Quick Stats - Product-focused (only for authenticated users) -->
      <div v-if="isAuthenticated" class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-8">
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
          <div class="flex items-center gap-2 mb-1">
            <CubeIcon class="w-4 h-4 text-blue-500" aria-hidden="true" />
            <span class="text-xs text-gray-500">Active Subscriptions</span>
          </div>
          <p class="text-2xl font-bold text-gray-900">{{ modules.filter(m => m.is_subscribed).length }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
          <div class="flex items-center gap-2 mb-1">
            <AcademicCapIcon class="w-4 h-4 text-emerald-500" aria-hidden="true" />
            <span class="text-xs text-gray-500">Training Completed</span>
          </div>
          <p class="text-2xl font-bold text-gray-900">0</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
          <div class="flex items-center gap-2 mb-1">
            <GiftIcon class="w-4 h-4 text-indigo-500" aria-hidden="true" />
            <span class="text-xs text-gray-500">Loyalty Points</span>
          </div>
          <p class="text-2xl font-bold text-gray-900">0</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
          <div class="flex items-center gap-2 mb-1">
            <ShoppingCartIcon class="w-4 h-4 text-purple-500" aria-hidden="true" />
            <span class="text-xs text-gray-500">Orders</span>
          </div>
          <p class="text-2xl font-bold text-gray-900">0</p>
        </div>
      </div>

      <!-- Quick Actions (only for authenticated users) -->
      <div v-if="isAuthenticated" class="mb-8">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Quick Actions</h3>
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
          <Link
            href="/wallet"
            class="flex flex-col items-center gap-2 p-4 bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl shadow-sm border border-green-100 hover:shadow-md hover:border-green-300 transition-all"
          >
            <WalletIcon class="w-6 h-6 text-green-600" aria-hidden="true" />
            <span class="text-xs font-medium text-gray-700">My Wallet</span>
            <span class="text-sm font-bold text-green-700">K{{ walletBalance.toLocaleString('en-ZM', { minimumFractionDigits: 2 }) }}</span>
          </Link>
          <Link
            href="/marketplace"
            class="flex flex-col items-center gap-2 p-4 bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-blue-200 transition-all"
          >
            <ShoppingCartIcon class="w-6 h-6 text-blue-600" aria-hidden="true" />
            <span class="text-xs font-medium text-gray-700">Marketplace</span>
          </Link>
          <Link
            href="/training"
            class="flex flex-col items-center gap-2 p-4 bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-emerald-200 transition-all"
          >
            <AcademicCapIcon class="w-6 h-6 text-emerald-600" aria-hidden="true" />
            <span class="text-xs font-medium text-gray-700">Learning</span>
          </Link>
          <Link
            href="/grownet/orders"
            class="flex flex-col items-center gap-2 p-4 bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-purple-200 transition-all"
          >
            <ClipboardDocumentCheckIcon class="w-6 h-6 text-purple-600" aria-hidden="true" />
            <span class="text-xs font-medium text-gray-700">Orders</span>
          </Link>
          <Link
            href="/rewards"
            class="flex flex-col items-center gap-2 p-4 bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-indigo-200 transition-all"
          >
            <GiftIcon class="w-6 h-6 text-indigo-600" aria-hidden="true" />
            <span class="text-xs font-medium text-gray-700">Rewards</span>
          </Link>
        </div>
      </div>

      <!-- Primary Apps Section -->
      <div class="mb-8">
        <div class="flex items-center gap-2 mb-4">
          <SparklesIcon class="w-5 h-5 text-blue-600" aria-hidden="true" />
          <h2 class="text-xl font-bold text-gray-900">{{ isAuthenticated ? 'Your Business Tools' : 'Business Tools' }}</h2>
        </div>
        <p class="text-sm text-gray-600 mb-4">Essential apps for managing and growing your business</p>
        
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 sm:gap-6">
          <button
            v-for="module in primaryModules"
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
                aria-hidden="true"
              />
            </div>
            
            <!-- Module Name -->
            <span class="text-gray-800 text-center font-semibold text-sm leading-tight">
              {{ module.name }}
            </span>

            <!-- Module Description -->
            <span v-if="getModuleDescription(module.slug)" class="text-xs text-gray-500 text-center leading-tight">
              {{ getModuleDescription(module.slug) }}
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
      </div>

      <!-- Additional Services Section -->
      <div v-if="additionalModules.length > 0" class="mb-8">
        <div class="flex items-center gap-2 mb-4">
          <CubeIcon class="w-5 h-5 text-gray-600" aria-hidden="true" />
          <h2 class="text-xl font-bold text-gray-900">Additional Services</h2>
        </div>
        <p class="text-sm text-gray-600 mb-4">More tools and features to enhance your experience</p>
        
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
          <button
            v-for="module in additionalModules"
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
                aria-hidden="true"
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
      </div>

      <!-- Empty State -->
      <div v-if="modules.length === 0" class="text-center py-20">
        <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-gray-100 flex items-center justify-center">
          <CubeIcon class="w-12 h-12 text-gray-400" aria-hidden="true" />
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
