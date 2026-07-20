<script setup lang="ts">
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';
import { 
  Cog6ToothIcon,
  ArrowRightOnRectangleIcon,
  UserCircleIcon,
  ClipboardDocumentCheckIcon,
  UsersIcon,
  ShieldCheckIcon,
} from '@heroicons/vue/24/solid';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import AppLauncher from '@/components/AppLauncher.vue';
import GlobalAppSwitcher from '@/Components/Workspace/GlobalAppSwitcher.vue';
import ImpersonationBanner from '@/components/ImpersonationBanner.vue';

interface Props {
    title?: string;
    isAdmin?: boolean;
    isManager?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'MyGrowNet',
    isAdmin: false,
    isManager: false
});

const page = usePage();

// Check if admin is impersonating
const isImpersonating = computed(() => {
  return page.props.impersonate_admin_id !== undefined && page.props.impersonate_admin_id !== null;
});

// Check if user is authenticated
const isAuthenticated = computed(() => !!page.props.auth?.user);

// Check user roles
const isAdmin = computed(() => {
  const user = page.props.auth?.user;
  if (!user) return false;
  
  // Check roles array (case-insensitive)
  const roles = user.roles || [];
  const hasAdminRole = roles.some(role => 
    ['admin', 'administrator', 'superadmin'].includes(role.toLowerCase())
  );
  
  // Also check if user has admin permissions (fallback)
  const permissions = user.permissions || [];
  const hasAdminPermission = permissions.some(p => p.includes('admin'));
  
  return hasAdminRole || hasAdminPermission;
});

const isManager = computed(() => {
  return page.props.auth?.user?.roles?.includes('manager');
});

const isEmployee = computed(() => {
  return page.props.auth?.user?.roles?.includes('employee');
});

const logout = () => {
  router.post('/logout');
};
</script>

<template>
    <Head :title="props.title" />
    
    <div class="min-h-screen bg-gray-50">
        <!-- Impersonation Banner -->
        <ImpersonationBanner v-if="isImpersonating" :userName="page.props.auth?.user?.name || 'User'" />
        
        <!-- Header - Logo Left, Settings/Login Right -->
        <header class="bg-white border-b border-gray-100 sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo - Top Left -->
                    <Link :href="route('workspace')" class="flex items-center hover:opacity-80 transition-opacity">
                        <AppLogoIcon class="h-10 w-10" />
                    </Link>

                    <!-- Authenticated User Menu -->
                    <div v-if="isAuthenticated" class="flex items-center gap-2">
                        <!-- Settings Link -->
                        <Link
                            href="/settings"
                            class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                        >
                            <Cog6ToothIcon class="w-5 h-5" aria-hidden="true" />
                            <span class="hidden sm:inline">Settings</span>
                        </Link>

                        <!-- App Launcher -->
                        <AppLauncher :modules="page.props.modules || []" />
                        <GlobalAppSwitcher />

                        <!-- Profile Menu -->
                        <Menu as="div" class="relative">
                            <MenuButton class="flex items-center gap-2 p-1.5 hover:bg-gray-100 rounded-full transition-colors">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center ring-2 ring-white">
                                    <UserCircleIcon class="w-5 h-5 text-white" />
                                </div>
                            </MenuButton>

                            <MenuItems class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg ring-1 ring-black/5 focus:outline-none z-50 overflow-hidden">
                                <div class="px-4 py-3 border-b border-gray-100">
                                    <p class="text-sm font-medium text-gray-900">{{ page.props.auth?.user?.name || 'User' }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ page.props.auth?.user?.email || '' }}</p>
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
                                            href="/workspace"
                                            :class="[active ? 'bg-emerald-50' : '', 'flex items-center gap-3 px-4 py-2.5 text-sm text-emerald-700']"
                                        >
                                            <ClipboardDocumentCheckIcon class="w-5 h-5 text-emerald-500" />
                                            Workspace
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
                    </div>

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

        <!-- Main Content -->
        <slot />

        <!-- WhatsApp Floating Button -->
        <a
            :href="`https://wa.me/${page.props.whatsappPhone || '260965896512'}`"
            target="_blank"
            rel="noopener noreferrer"
            class="fixed bottom-6 right-6 w-14 h-14 bg-green-500 text-white rounded-full shadow-lg hover:bg-green-600 hover:scale-105 transition-all flex items-center justify-center z-50"
            aria-label="Chat on WhatsApp"
        >
            <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
            </svg>
        </a>
    </div>
</template>
