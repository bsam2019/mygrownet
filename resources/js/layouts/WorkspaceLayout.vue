<script setup lang="ts">
import { usePage, Link, router } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted, watch } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import GlobalAppSwitcher from '@/Components/Workspace/GlobalAppSwitcher.vue';
import CommandPalette from '@/Components/Workspace/CommandPalette.vue';
import {
    LayoutDashboard,
    LayoutGrid,
    Building2,
    ChevronDown,
    ChevronRight,
    LogOut,
    Settings,
    Sun,
    Moon,
    PanelLeftClose,
    PanelLeft,
    Menu,
    X,
    Bell,
} from 'lucide-vue-next';

const page = usePage();
const user = computed(() => (page.props.auth as any)?.user);
const workspace = computed(() => (page.props as any).workspace);
const context = computed(() => workspace.value?.context);
const organizations = computed(() => workspace.value?.organizations ?? []);

// ── Mobile sidebar ──
const mobileOpen = ref(false);

function openMobile() { mobileOpen.value = true; }
function closeMobile() { mobileOpen.value = false; }

// ── Sidebar collapse (desktop only) ──
const collapsed = ref(false);

function getStoredCollapse(): boolean | null {
    try { const v = localStorage.getItem('mg-sidebar'); return v === 'collapsed'; } catch { return null; }
}

function toggleCollapse() {
    collapsed.value = !collapsed.value;
    try { localStorage.setItem('mg-sidebar', collapsed.value ? 'collapsed' : 'expanded'); } catch {}
}

const showCollapsed = computed(() => collapsed.value && !mobileOpen.value);

onMounted(() => {
    const stored = getStoredCollapse();
    if (stored !== null) collapsed.value = stored;
});

let keydownHandler: ((e: KeyboardEvent) => void) | null = null;

onMounted(() => {
    keydownHandler = (e: KeyboardEvent) => {
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
            e.preventDefault();
            paletteOpen.value = true;
        }
    };
    document.addEventListener('keydown', keydownHandler);
});

onUnmounted(() => {
    if (keydownHandler) document.removeEventListener('keydown', keydownHandler);
});

// ── Page navigation indicator ──
const navigating = ref(false);

onMounted(() => {
    router.on('start', () => navigating.value = true);
    router.on('finish', () => navigating.value = false);
});

// ── Theme ──
const isDark = ref(false);

function getStoredTheme(): string | null {
    try { return localStorage.getItem('mg-theme'); } catch { return null; }
}

function applyTheme(dark: boolean) {
    isDark.value = dark;
    document.documentElement.classList.toggle('dark', dark);
    try { localStorage.setItem('mg-theme', dark ? 'dark' : 'light'); } catch {}
}

function toggleTheme() {
    applyTheme(!isDark.value);
}

onMounted(() => {
    const stored = getStoredTheme();
    if (stored) {
        applyTheme(stored === 'dark');
    } else {
        applyTheme(false);
    }
});

// ── Context Switcher ──
const ctxOpen = ref(false);
const ctxMenuRef = ref<HTMLElement | null>(null);

const currentLabel = computed(() => {
    if (!context.value) return 'Loading...';
    if (context.value.type === 'organization' && context.value.organization_name) {
        return context.value.organization_name;
    }
    return 'Personal Workspace';
});

const currentType = computed(() => context.value?.type ?? 'guest');

function switchToPersonal() {
    ctxOpen.value = false;
    router.post(route('workspace.switch-context'), { type: 'personal' });
}

function switchToOrganization(org: any) {
    ctxOpen.value = false;
    router.post(route('workspace.switch-context'), {
        type: 'organization',
        organization_id: org.id,
    });
}

function handleClickOutside(e: MouseEvent) {
    if (ctxMenuRef.value && !ctxMenuRef.value.contains(e.target as Node)) {
        ctxOpen.value = false;
    }
}

onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => document.removeEventListener('click', handleClickOutside));

// ── Command Palette ──
const paletteOpen = ref(false);

// ── User Dropdown ──
const userOpen = ref(false);
const userMenuRef = ref<HTMLElement | null>(null);

function handleUserClickOutside(e: MouseEvent) {
    if (userMenuRef.value && !userMenuRef.value.contains(e.target as Node)) {
        userOpen.value = false;
    }
}

onMounted(() => document.addEventListener('click', handleUserClickOutside));
onUnmounted(() => document.removeEventListener('click', handleUserClickOutside));

// ── Nav ──
const navItems = [
    { label: 'Workspace', route: 'workspace', icon: LayoutDashboard },
    { label: 'Applications', route: 'apps.catalog', icon: LayoutGrid },
    { label: 'Organizations', route: 'workspace.organization.create', icon: Building2 },
];

function navItemClass(item: typeof navItems[0]): string {
    const active = isActive(item);
    const base = active
        ? 'bg-indigo-50 text-indigo-700 dark:bg-white/[0.06] dark:text-white'
        : 'text-gray-500 hover:text-gray-700 dark:text-white/60 dark:hover:text-white/80 hover:bg-gray-50 dark:hover:bg-white/[0.03]';
    if (showCollapsed.value) {
        return `${base} w-10 h-10 justify-center rounded-lg`;
    }
    const border = active
        ? 'border-l-2 border-indigo-500 dark:border-indigo-400'
        : 'border-l-2 border-transparent';
    return `${base} ${border} px-3 py-2 pl-[11px] w-full`;
}

function isActive(item: typeof navItems[0]): boolean {
    try {
        const current = route().current() as string;
        if (item.route === 'workspace') return current === 'workspace' || (current.startsWith('workspace.') && !current.startsWith('workspace.organization.'));
        if (item.route === 'apps.catalog') return current.startsWith('apps.');
        if (item.route === 'workspace.organization.create') return current.startsWith('workspace.organization.');
        return false;
    } catch {
        return false;
    }
}
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-950 flex transition-colors duration-200">
        <!-- Navigation progress bar -->
        <div
            v-if="navigating"
            class="fixed top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-indigo-500 to-purple-500 z-[200] animate-pulse"
        ></div>

        <!-- Mobile Top Bar -->
        <div class="fixed top-0 left-0 right-0 h-14 flex items-center gap-3 px-4 bg-white dark:bg-[#0a0e17] border-b border-gray-200 dark:border-white/[0.04] z-30 lg:hidden">
            <button @click="openMobile" class="flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 dark:text-white/50 dark:hover:text-white/70 hover:bg-gray-100 dark:hover:bg-white/[0.06] transition-all">
                <Menu class="w-[18px] h-[18px]" stroke-width="1.5" />
            </button>
            <Link :href="route('workspace')" class="flex items-center hover:opacity-80 transition-opacity">
                <AppLogoIcon class="h-7 w-auto dark:brightness-0 dark:invert" />
            </Link>
        </div>

        <!-- Mobile Backdrop -->
        <div
            v-if="mobileOpen"
            class="fixed inset-0 bg-black/50 z-40 lg:hidden transition-opacity duration-200"
            @click="closeMobile"
        ></div>

        <!-- Sidebar -->
        <aside
            class="flex flex-col z-40 select-none bg-white dark:bg-[#0a0e17] border-r border-gray-200 dark:border-white/[0.04] transition-all duration-200 ease-in-out"
            :class="[
                collapsed ? 'lg:w-14 w-60' : 'lg:w-60 w-60',
                'fixed left-0 top-0 bottom-0 z-40',
                'max-lg:z-50 max-lg:transition-transform max-lg:duration-200 max-lg:ease-in-out',
                mobileOpen ? 'max-lg:translate-x-0' : 'max-lg:-translate-x-full',
            ]"
        >
            <!-- Logo + Toggle (desktop) -->
            <div class="h-14 hidden lg:flex items-center border-b border-gray-100 dark:border-white/[0.04]" :class="collapsed ? 'justify-center px-0' : 'justify-between px-5'">
                <Link v-if="!collapsed" :href="route('workspace')" class="flex items-center hover:opacity-80 transition-opacity flex-shrink-0">
                    <AppLogoIcon class="h-8 w-auto dark:brightness-0 dark:invert" />
                </Link>
                <button
                    @click="toggleCollapse"
                    :title="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                    class="flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 dark:text-white/50 dark:hover:text-white/70 hover:bg-gray-100 dark:hover:bg-white/[0.06] transition-all flex-shrink-0"
                >
                    <component :is="collapsed ? PanelLeft : PanelLeftClose" class="w-[18px] h-[18px]" stroke-width="1.5" />
                </button>
            </div>

            <!-- Logo + Close (mobile) -->
            <div class="h-14 flex lg:hidden items-center justify-between px-4 border-b border-gray-100 dark:border-white/[0.04]">
                <Link :href="route('workspace')" class="flex items-center hover:opacity-80 transition-opacity" @click="closeMobile">
                    <AppLogoIcon class="h-7 w-auto dark:brightness-0 dark:invert" />
                </Link>
                <button
                    @click="closeMobile"
                    class="flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 dark:text-white/50 dark:hover:text-white/70 hover:bg-gray-100 dark:hover:bg-white/[0.06] transition-all flex-shrink-0"
                >
                    <X class="w-[18px] h-[18px]" stroke-width="1.5" />
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 flex flex-col py-4 overflow-y-auto overflow-x-hidden" :class="showCollapsed ? 'items-center px-1 space-y-1' : 'px-3 space-y-0.5'">
                <div v-if="!showCollapsed" class="px-3 pb-3 text-[10px] font-semibold text-gray-400 dark:text-white/25 uppercase tracking-[0.15em]">Navigation</div>

                <Link
                    v-for="item in navItems"
                    :key="item.label"
                    :href="route(item.route)"
                    :title="showCollapsed ? item.label : undefined"
                    class="group flex items-center gap-3 rounded-lg text-sm transition-all duration-150 flex-shrink-0"
                    :class="navItemClass(item)"
                    @click="closeMobile"
                >
                    <component :is="item.icon" class="w-[18px] h-[18px] flex-shrink-0" stroke-width="1.5" />
                    <span v-if="!showCollapsed" class="font-medium text-xs whitespace-nowrap">{{ item.label }}</span>
                </Link>
            </nav>

            <!-- Bottom Section -->
            <div :class="showCollapsed ? 'px-1.5 pb-3 space-y-2 flex flex-col items-center' : 'px-3 pb-3 space-y-1'">
                <div :class="showCollapsed ? '' : 'flex items-center justify-between px-3 py-2 w-full'">
                    <span v-if="!showCollapsed" class="text-[10px] font-semibold text-gray-400 dark:text-white/25 uppercase tracking-[0.15em]">Quick Access</span>
                    <GlobalAppSwitcher align="right-side" />
                </div>

                <div class="h-px bg-gray-100 dark:bg-white/[0.04]" :class="showCollapsed ? 'w-8' : 'mx-2 w-auto'"></div>

                <button
                    title="No new notifications"
                    class="flex items-center rounded-lg text-xs text-gray-500 hover:text-gray-700 dark:text-white/60 dark:hover:text-white/80 hover:bg-gray-50 dark:hover:bg-white/[0.03] transition-all flex-shrink-0 relative"
                    :class="showCollapsed ? 'w-10 h-10 justify-center' : 'w-full gap-3 px-3 py-2'"
                >
                    <Bell class="w-[18px] h-[18px] flex-shrink-0" stroke-width="1.5" />
                    <span v-if="!showCollapsed" class="font-medium">Notifications</span>
                </button>

                <div class="h-px bg-gray-100 dark:bg-white/[0.04]" :class="showCollapsed ? 'w-8' : 'mx-2 w-auto'"></div>

                <div ref="ctxMenuRef" class="relative" :class="showCollapsed ? '' : 'w-full'">
                    <button
                        @click="ctxOpen = !ctxOpen"
                        :title="showCollapsed ? currentLabel : undefined"
                        class="flex items-center rounded-lg text-xs text-gray-500 hover:text-gray-700 dark:text-white/60 dark:hover:text-white/80 hover:bg-gray-50 dark:hover:bg-white/[0.03] transition-all flex-shrink-0"
                        :class="showCollapsed ? 'w-10 h-10 justify-center' : 'w-full gap-3 px-3 py-2'"
                    >
                        <div class="w-5 h-5 rounded bg-gray-100 dark:bg-white/[0.06] flex items-center justify-center flex-shrink-0">
                            <span class="text-[8px] font-bold text-gray-500 dark:text-white/50">{{ currentLabel.charAt(0) }}</span>
                        </div>
                        <span v-if="!showCollapsed" class="flex-1 truncate text-left font-medium">{{ currentLabel }}</span>
                        <ChevronDown v-if="!showCollapsed" class="w-3 h-3 text-gray-400 dark:text-white/30 flex-shrink-0" stroke-width="1.5" />
                    </button>

                    <div
                        v-if="ctxOpen"
                        :class="[
                            'absolute bg-white dark:bg-[#121926] border border-gray-200 dark:border-white/[0.06] rounded-xl shadow-2xl py-1.5 z-50',
                            showCollapsed ? 'bottom-0 left-full ml-2 min-w-[220px]' : 'bottom-full left-0 right-0 mb-1'
                        ]"
                    >
                        <button
                            @click="switchToPersonal"
                            class="w-full text-left px-3 py-2 text-xs hover:bg-gray-50 dark:hover:bg-white/[0.04] flex items-center gap-2.5 transition-colors"
                            :class="{ 'text-gray-900 dark:text-white font-medium': currentType === 'personal', 'text-gray-500 dark:text-white/50': currentType !== 'personal' }"
                        >
                            <div class="w-5 h-5 rounded bg-gray-100 dark:bg-white/[0.06] flex items-center justify-center flex-shrink-0">
                                <span class="text-[8px] font-bold text-gray-400 dark:text-white/50">P</span>
                            </div>
                            Personal Workspace
                        </button>
                        <div v-if="organizations.length > 0" class="border-t border-gray-100 dark:border-white/[0.04] mt-1 pt-1">
                            <div class="px-3 py-1 text-[9px] font-semibold text-gray-500 dark:text-white/30 uppercase tracking-wider">Organizations</div>
                            <button
                                v-for="org in organizations"
                                :key="org.id"
                                @click="switchToOrganization(org)"
                                class="w-full text-left px-3 py-2 text-xs hover:bg-gray-50 dark:hover:bg-white/[0.04] flex items-center gap-2.5 transition-colors"
                                :class="{
                                    'text-gray-900 dark:text-white font-medium': currentType === 'organization' && context?.organization_id === org.id,
                                    'text-gray-500 dark:text-white/50': !(currentType === 'organization' && context?.organization_id === org.id),
                                }"
                            >
                                <div class="w-5 h-5 rounded bg-gray-100 dark:bg-white/[0.06] flex items-center justify-center flex-shrink-0">
                                    <span class="text-[8px] font-bold text-gray-400 dark:text-white/50">{{ org.name.charAt(0) }}</span>
                                </div>
                                {{ org.name }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="h-px bg-gray-100 dark:bg-white/[0.04]" :class="showCollapsed ? 'w-8' : 'mx-2 w-auto'"></div>

                <button
                    @click="toggleTheme"
                    :title="isDark ? 'Light Mode' : 'Dark Mode'"
                    class="flex items-center rounded-lg text-xs text-gray-500 hover:text-gray-700 dark:text-white/60 dark:hover:text-white/80 hover:bg-gray-50 dark:hover:bg-white/[0.03] transition-all flex-shrink-0"
                    :class="showCollapsed ? 'w-10 h-10 justify-center' : 'w-full gap-3 px-3 py-2'"
                >
                    <Sun v-if="isDark" class="w-[18px] h-[18px] flex-shrink-0" stroke-width="1.5" />
                    <Moon v-else class="w-[18px] h-[18px] flex-shrink-0" stroke-width="1.5" />
                    <span v-if="!showCollapsed" class="font-medium">{{ isDark ? 'Light Mode' : 'Dark Mode' }}</span>
                </button>

                <div class="h-px bg-gray-100 dark:bg-white/[0.04]" :class="showCollapsed ? 'w-8' : 'mx-2 w-auto'"></div>

                <div ref="userMenuRef" class="relative" :class="showCollapsed ? '' : 'w-full'">
                    <button
                        @click="userOpen = !userOpen"
                        :title="user?.name || 'User'"
                        class="flex items-center rounded-lg text-xs text-gray-500 hover:text-gray-700 dark:text-white/60 dark:hover:text-white/80 hover:bg-gray-50 dark:hover:bg-white/[0.03] transition-all flex-shrink-0"
                        :class="showCollapsed ? 'w-10 h-10 justify-center' : 'w-full gap-3 px-3 py-2'"
                    >
                        <div class="w-5 h-5 rounded-full bg-gray-100 dark:bg-white/[0.06] flex items-center justify-center flex-shrink-0">
                            <span class="text-[9px] font-bold text-gray-500 dark:text-white/50">{{ user?.name?.charAt(0) || '?' }}</span>
                        </div>
                        <div v-if="!showCollapsed" class="flex-1 text-left min-w-0">
                            <p class="text-xs font-medium truncate leading-tight text-gray-700 dark:text-white/70">{{ user?.name || 'User' }}</p>
                            <p class="text-[10px] text-gray-500 dark:text-white/40 truncate leading-tight mt-0.5">{{ user?.email }}</p>
                        </div>
                        <ChevronRight v-if="!showCollapsed" class="w-3 h-3 text-gray-400 dark:text-white/30 flex-shrink-0" stroke-width="1.5" />
                    </button>

                    <div
                        v-if="userOpen"
                        :class="[
                            'absolute bg-white dark:bg-[#121926] border border-gray-200 dark:border-white/[0.06] rounded-xl shadow-2xl py-1.5 z-50',
                            showCollapsed ? 'bottom-0 left-full ml-2 min-w-[220px]' : 'bottom-full left-0 right-0 mb-1'
                        ]"
                    >
                        <div class="px-4 py-2.5 text-xs border-b border-gray-100 dark:border-white/[0.04]">
                            <p class="font-medium text-gray-700 dark:text-white">{{ user?.name }}</p>
                            <p class="text-gray-500 dark:text-white/50 mt-0.5">{{ user?.email }}</p>
                        </div>
                        <Link
                            :href="route('profile.edit')"
                            class="flex items-center gap-2 px-4 py-2 text-xs text-gray-600 dark:text-white/60 hover:bg-gray-50 dark:hover:bg-white/[0.04] transition-colors"
                        >
                            <Settings class="w-3.5 h-3.5" stroke-width="1.5" />
                            Profile Settings
                        </Link>
                        <button
                            @click="router.post(route('logout'))"
                            class="w-full flex items-center gap-2 px-4 py-2 text-xs text-gray-600 dark:text-white/60 hover:bg-gray-50 dark:hover:bg-white/[0.04] transition-colors"
                        >
                            <LogOut class="w-3.5 h-3.5" stroke-width="1.5" />
                            Sign Out
                        </button>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main
            class="flex-1 min-h-screen transition-all duration-200 ease-in-out max-lg:pt-14"
            :class="collapsed ? 'lg:ml-14' : 'lg:ml-60'"
        >
            <slot />
        </main>

        <CommandPalette :open="paletteOpen" @close="paletteOpen = false" />
    </div>
</template>
