<script setup lang="ts">
import { onMounted, ref, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import NotificationBell from '@/components/NotificationBell.vue';
import {
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuLabel,
  DropdownMenuCheckboxItem,
} from '@/components/ui/dropdown-menu';
import { User as UserIcon, Settings as SettingsIcon, Moon as MoonIcon, LogOut as LogOutIcon, Smartphone as SmartphoneIcon, Download as DownloadIcon } from 'lucide-vue-next';
import type { BreadcrumbItemType } from '@/types';
import axios from 'axios';
import { usePWA } from '@/composables/usePWA';

defineProps<{
  breadcrumbs?: BreadcrumbItemType[];
}>();

// PWA install functionality
const { isInstallable, isInstalled, isStandalone, promptInstall } = usePWA();
const canInstall = computed(() => isInstallable.value && !isInstalled.value && !isStandalone.value);

// Derive current user name from Inertia props if available
const page = usePage();
const userName = (page?.props as any)?.auth?.user?.name ?? 'User';
const userDashboardPreference = ref((page?.props as any)?.auth?.user?.dashboard_preference ?? 'auto');

// Simple dark mode toggle with persistence
const isDark = ref(false);
const applyTheme = (dark: boolean) => {
  const root = document.documentElement;
  if (dark) {
    root.classList.add('dark');
    localStorage.setItem('theme', 'dark');
  } else {
    root.classList.remove('dark');
    localStorage.setItem('theme', 'light');
  }
  isDark.value = dark;
};

onMounted(() => {
  const pref = localStorage.getItem('theme');
  applyTheme(pref ? pref === 'dark' : document.documentElement.classList.contains('dark'));
});

const goto = (href: string) => router.visit(href);
const logout = () => router.post('/logout');

// Dashboard preference toggle - now just refreshes the dashboard
const toggleMobileDashboard = async () => {
  try {
    const isCurrentlyMobile = userDashboardPreference.value === 'mobile';
    const newPreference = isCurrentlyMobile ? 'desktop' : 'mobile';
    
    console.log('Toggling dashboard preference:', { from: userDashboardPreference.value, to: newPreference });
    
    await axios.post(route('mygrownet.api.user.dashboard-preference'), { preference: newPreference });
    userDashboardPreference.value = newPreference;
    
    // Redirect to dashboard (single unified dashboard)
    console.log('Redirecting to dashboard...');
    window.location.href = '/dashboard';
  } catch (error) {
    console.error('Failed to update dashboard preference:', error);
  }
};
</script>

<template>
  <header
    class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-[[data-collapsible=icon]]/sidebar-wrapper:h-12 md:px-4"
  >
    <!-- Left: breadcrumbs -->
    <div class="flex items-center gap-2">
      <template v-if="breadcrumbs && breadcrumbs.length > 0">
        <Breadcrumbs :items="breadcrumbs" />
      </template>
    </div>

    <!-- Spacer -->
    <div class="flex-1" />

    <!-- Right: Notification Bell & Profile dropdown -->
    <div class="flex items-center gap-2">
      <NotificationBell />
    </div>
    <DropdownMenu>
      <DropdownMenuTrigger as-child>
        <button
          class="inline-flex items-center rounded-full border border-border/60 bg-background p-1 hover:bg-muted focus:outline-none transition-colors"
        >
          <span
            class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 text-primary font-semibold"
            aria-hidden="true"
          >{{ userName?.charAt(0) ?? 'U' }}</span>
        </button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end" class="w-64 p-0 overflow-hidden">
        <!-- Elegant header -->
        <div class="flex items-center gap-3 px-3 py-3 bg-muted/50">
          <div class="flex h-9 w-9 items-center justify-center rounded-full bg-primary/10 text-primary font-semibold">
            {{ userName?.charAt(0) ?? 'U' }}
          </div>
          <div class="min-w-0">
            <div class="font-medium truncate">{{ userName }}</div>
            <div class="text-xs text-muted-foreground truncate">Account</div>
          </div>
        </div>
        <DropdownMenuSeparator />

        <!-- Actions -->
        <div class="py-1">
          <DropdownMenuItem @click="goto('/profile')">
            <UserIcon class="mr-2 h-4 w-4" />
            <span>Profile</span>
          </DropdownMenuItem>
          <DropdownMenuItem @click="goto(route('password.edit'))">
            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
            </svg>
            <span>Change Password</span>
          </DropdownMenuItem>
          <DropdownMenuItem @click="goto('/settings')">
            <SettingsIcon class="mr-2 h-4 w-4" />
            <span>Settings</span>
          </DropdownMenuItem>
        </div>
        <DropdownMenuSeparator />
        <div class="py-1">
          <DropdownMenuCheckboxItem :checked="isDark" @update:checked="applyTheme">
            <MoonIcon class="mr-2 h-4 w-4" />
            <span>Dark mode</span>
          </DropdownMenuCheckboxItem>
          <DropdownMenuItem as-child>
            <button 
              type="button"
              class="w-full"
              @click.prevent="toggleMobileDashboard"
            >
              <SmartphoneIcon class="mr-2 h-4 w-4" />
              <span>Mobile Dashboard</span>
              <span v-if="userDashboardPreference === 'mobile'" class="ml-auto text-primary">✓</span>
            </button>
          </DropdownMenuItem>
          <DropdownMenuItem v-if="canInstall" as-child>
            <button 
              type="button"
              class="w-full"
              @click.prevent="promptInstall"
            >
              <DownloadIcon class="mr-2 h-4 w-4" />
              <span>Install App</span>
            </button>
          </DropdownMenuItem>
        </div>
        <DropdownMenuSeparator />
        <div class="py-1">
          <DropdownMenuItem class="text-destructive" @click="logout">
            <LogOutIcon class="mr-2 h-4 w-4" />
            <span>Logout</span>
          </DropdownMenuItem>
        </div>
      </DropdownMenuContent>
    </DropdownMenu>
  </header>
  
</template>
