<template>
  <Head title="Home" />
  
  <div class="min-h-screen bg-white">
    <!-- Header -->
    <header class="bg-white px-4 py-4 flex items-center justify-between">
      <!-- Logo -->
      <div class="flex items-center">
        <img 
          src="/logo.png" 
          alt="MyGrowNet" 
          class="h-10 w-auto object-contain"
        />
      </div>
      
      <!-- Right side - Profile icon and three dots -->
      <div class="flex items-center gap-2">
        <!-- Profile button -->
        <button
          @click="navigateToProfile"
          aria-label="Profile"
          class="w-10 h-10 rounded-full bg-teal-500 flex items-center justify-center text-white"
        >
          <UserIcon class="h-5 w-5" aria-hidden="true" />
        </button>
        
        <!-- Three dots menu -->
        <div class="relative">
          <button
            @click="toggleMenu"
            aria-label="Menu"
            class="text-gray-400 hover:text-gray-600 p-1"
          >
            <EllipsisVerticalIcon class="h-6 w-6" aria-hidden="true" />
          </button>
          
          <!-- Dropdown Menu -->
          <div
            v-if="showMenu"
            class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-50"
          >
            <Link
              :href="route('profile.edit')"
              class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50"
            >
              <Cog8ToothIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
              Settings
            </Link>
            <button
              @click="logout"
              class="w-full flex items-center gap-3 px-4 py-3 text-sm text-red-600 hover:bg-red-50"
            >
              <ArrowRightOnRectangleIcon class="h-5 w-5" aria-hidden="true" />
              Logout
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Click outside to close menu -->
    <div v-if="showMenu" class="fixed inset-0 z-40" @click="showMenu = false"></div>

    <!-- Main Content -->
    <main class="px-6 py-6 max-w-2xl mx-auto">
      <!-- Page Title -->
      <h1 class="text-3xl font-bold text-gray-900 mb-8">Home</h1>

      <!-- Module Grid - 3 columns -->
      <div class="grid grid-cols-3 gap-4">
        <!-- MLM Dashboard - Teal/Green gradient -->
        <Link
          :href="route('dashboard')"
          class="aspect-square rounded-2xl p-4 flex flex-col items-center justify-center text-white hover:opacity-90 transition-opacity active:scale-95"
          style="background: linear-gradient(135deg, #4fd1c5 0%, #38b2ac 100%);"
        >
          <div class="w-12 h-12 rounded-full border-2 border-white/80 flex items-center justify-center mb-3">
            <UserCircleIcon class="h-7 w-7" aria-hidden="true" />
          </div>
          <span class="text-sm font-semibold text-center leading-tight">MLM<br>Dashboard</span>
        </Link>

        <!-- Task & Staff Management - Blue gradient -->
        <Link
          :href="taskRoute"
          class="aspect-square rounded-2xl p-4 flex flex-col items-center justify-center text-white hover:opacity-90 transition-opacity active:scale-95"
          style="background: linear-gradient(135deg, #63b3ed 0%, #4299e1 100%);"
        >
          <div class="w-12 h-12 flex items-center justify-center mb-3">
            <ClipboardDocumentCheckIcon class="h-9 w-9" aria-hidden="true" />
          </div>
          <span class="text-sm font-semibold text-center leading-tight">Task & Staff<br>Management</span>
        </Link>

        <!-- Smart Accounting - Green gradient -->
        <Link
          :href="accountingRoute"
          class="aspect-square rounded-2xl p-4 flex flex-col items-center justify-center text-white hover:opacity-90 transition-opacity active:scale-95"
          style="background: linear-gradient(135deg, #68d391 0%, #48bb78 100%);"
        >
          <div class="w-12 h-12 flex items-center justify-center mb-3">
            <ChartBarIcon class="h-9 w-9" aria-hidden="true" />
          </div>
          <span class="text-sm font-semibold text-center leading-tight">Smart<br>Accounting</span>
        </Link>

        <!-- Messaging - Purple gradient -->
        <Link
          :href="messagingRoute"
          class="aspect-square rounded-2xl p-4 flex flex-col items-center justify-center text-white hover:opacity-90 transition-opacity active:scale-95"
          style="background: linear-gradient(135deg, #a78bfa 0%, #8b5cf6 100%);"
        >
          <div class="w-12 h-12 flex items-center justify-center mb-3">
            <ChatBubbleLeftRightIcon class="h-9 w-9" aria-hidden="true" />
          </div>
          <span class="text-sm font-semibold text-center leading-tight">Messaging</span>
        </Link>

        <!-- Marketplace - Orange gradient -->
        <Link
          :href="marketplaceRoute"
          class="aspect-square rounded-2xl p-4 flex flex-col items-center justify-center text-white hover:opacity-90 transition-opacity active:scale-95"
          style="background: linear-gradient(135deg, #fbb040 0%, #f6921e 100%);"
        >
          <div class="w-12 h-12 flex items-center justify-center mb-3">
            <ShoppingCartIcon class="h-9 w-9" aria-hidden="true" />
          </div>
          <span class="text-sm font-semibold text-center leading-tight">Marketplace</span>
        </Link>

        <!-- Profile & Settings - Gray/Slate gradient -->
        <Link
          :href="route('profile.edit')"
          class="aspect-square rounded-2xl p-4 flex flex-col items-center justify-center text-white hover:opacity-90 transition-opacity active:scale-95"
          style="background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%);"
        >
          <div class="w-12 h-12 flex items-center justify-center mb-3">
            <Cog8ToothIcon class="h-9 w-9" aria-hidden="true" />
          </div>
          <span class="text-sm font-semibold text-center leading-tight">Profile &<br>Settings</span>
        </Link>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import {
  UserIcon,
  UserCircleIcon,
  ClipboardDocumentCheckIcon,
  ChartBarIcon,
  ChatBubbleLeftRightIcon,
  ShoppingCartIcon,
  Cog8ToothIcon,
  EllipsisVerticalIcon,
  ArrowRightOnRectangleIcon,
} from '@heroicons/vue/24/outline';

interface Props {
  user: {
    id: number;
    name: string;
    email: string;
  };
}

const props = defineProps<Props>();

// Menu state
const showMenu = ref(false);

const toggleMenu = () => {
  showMenu.value = !showMenu.value;
};

// Navigate to profile
const navigateToProfile = () => {
  router.visit(route('profile.edit'));
};

// Logout
const logout = () => {
  router.post(route('logout'));
};

// Task management route
const taskRoute = computed(() => {
  try {
    return route('employee.portal.dashboard');
  } catch {
    return route('home');
  }
});

// Accounting route (placeholder)
const accountingRoute = computed(() => {
  return route('home'); // Placeholder - coming soon
});

// Messaging route
const messagingRoute = computed(() => {
  try {
    return route('mygrownet.messages.index');
  } catch {
    return route('home');
  }
});

// Marketplace route
const marketplaceRoute = computed(() => {
  try {
    return route('shop.index');
  } catch {
    return route('home');
  }
});
</script>
