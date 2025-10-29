<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import {
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuLabel,
  DropdownMenuCheckboxItem,
} from '@/components/ui/dropdown-menu';
import { User as UserIcon, Settings as SettingsIcon, Moon as MoonIcon, LogOut as LogOutIcon } from 'lucide-vue-next';
import type { BreadcrumbItemType } from '@/types';

defineProps<{
  breadcrumbs?: BreadcrumbItemType[];
}>();

// Derive current user name from Inertia props if available
const page = usePage();
const userName = (page?.props as any)?.auth?.user?.name ?? 'User';

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

    <!-- Right: Profile dropdown -->
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
