<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    MagnifyingGlassIcon,
    DocumentTextIcon,
    CurrencyDollarIcon,
    UserPlusIcon,
    ChartBarIcon,
    ShoppingBagIcon,
    CalendarIcon,
    SparklesIcon,
    Cog6ToothIcon,
    RocketLaunchIcon,
    HomeIcon,
    UsersIcon,
    PhotoIcon,
    ClockIcon,
    ArrowRightIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';

const isOpen = ref(false);
const searchQuery = ref('');
const selectedIndex = ref(0);
const searchInputRef = ref<HTMLInputElement | null>(null);

interface CommandItem {
    id: string;
    name: string;
    description?: string;
    icon: any;
    shortcut?: string;
    action: () => void;
    category: 'action' | 'navigation';
}

const commands: CommandItem[] = [
    // Quick Actions
    { id: 'new-post', name: 'Create new post', description: 'Write and schedule a social media post', icon: DocumentTextIcon, shortcut: '⌘⇧P', action: () => router.visit('/bizboost/posts/create'), category: 'action' },
    { id: 'record-sale', name: 'Record a sale', description: 'Log a new sale transaction', icon: CurrencyDollarIcon, shortcut: '⌘⇧S', action: () => router.visit('/bizboost/sales/create'), category: 'action' },
    { id: 'add-customer', name: 'Add customer', description: 'Add a new customer to your list', icon: UserPlusIcon, shortcut: '⌘⇧C', action: () => router.visit('/bizboost/customers/create'), category: 'action' },
    { id: 'add-product', name: 'Add product', description: 'Add a new product to your catalog', icon: ShoppingBagIcon, action: () => router.visit('/bizboost/products/create'), category: 'action' },
    { id: 'ai-content', name: 'Generate AI content', description: 'Create content with AI assistance', icon: SparklesIcon, action: () => router.visit('/bizboost/ai'), category: 'action' },
    { id: 'new-campaign', name: 'Start campaign', description: 'Create a new marketing campaign', icon: RocketLaunchIcon, action: () => router.visit('/bizboost/campaigns/create'), category: 'action' },
    { id: 'new-reminder', name: 'Set reminder', description: 'Create a follow-up reminder', icon: ClockIcon, action: () => router.visit('/bizboost/reminders'), category: 'action' },
    
    // Navigation
    { id: 'nav-dashboard', name: 'Dashboard', description: 'Go to dashboard', icon: HomeIcon, action: () => router.visit('/bizboost'), category: 'navigation' },
    { id: 'nav-products', name: 'Products', description: 'Manage your products', icon: ShoppingBagIcon, action: () => router.visit('/bizboost/products'), category: 'navigation' },
    { id: 'nav-customers', name: 'Customers', description: 'View customer list', icon: UsersIcon, action: () => router.visit('/bizboost/customers'), category: 'navigation' },
    { id: 'nav-posts', name: 'Posts', description: 'Manage social posts', icon: DocumentTextIcon, action: () => router.visit('/bizboost/posts'), category: 'navigation' },
    { id: 'nav-analytics', name: 'Analytics', description: 'View performance metrics', icon: ChartBarIcon, action: () => router.visit('/bizboost/analytics'), category: 'navigation' },
    { id: 'nav-calendar', name: 'Calendar', description: 'View content calendar', icon: CalendarIcon, action: () => router.visit('/bizboost/calendar'), category: 'navigation' },
    { id: 'nav-templates', name: 'Templates', description: 'Browse design templates', icon: PhotoIcon, action: () => router.visit('/bizboost/templates'), category: 'navigation' },
    { id: 'nav-settings', name: 'Settings', description: 'Configure your business', icon: Cog6ToothIcon, action: () => router.visit('/bizboost/business/settings'), category: 'navigation' },
];

const filteredCommands = computed(() => {
    if (!searchQuery.value) return commands;
    const query = searchQuery.value.toLowerCase();
    return commands.filter(cmd => 
        cmd.name.toLowerCase().includes(query) || 
        cmd.description?.toLowerCase().includes(query)
    );
});

const groupedCommands = computed(() => {
    const groups: Record<string, CommandItem[]> = { action: [], navigation: [] };
    filteredCommands.value.forEach(cmd => {
        groups[cmd.category]?.push(cmd);
    });
    return groups;
});

const executeCommand = (cmd: CommandItem) => {
    isOpen.value = false;
    searchQuery.value = '';
    cmd.action();
};

const handleKeydown = (e: KeyboardEvent) => {
    // Open with Cmd+K or Ctrl+K
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault();
        isOpen.value = !isOpen.value;
        if (isOpen.value) {
            nextTick(() => {
                searchInputRef.value?.focus();
            });
        }
        return;
    }

    if (!isOpen.value) return;

    // Navigate with arrows
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        selectedIndex.value = Math.min(selectedIndex.value + 1, filteredCommands.value.length - 1);
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        selectedIndex.value = Math.max(selectedIndex.value - 1, 0);
    } else if (e.key === 'Enter') {
        e.preventDefault();
        const cmd = filteredCommands.value[selectedIndex.value];
        if (cmd) executeCommand(cmd);
    } else if (e.key === 'Escape') {
        isOpen.value = false;
        searchQuery.value = '';
    }
};

watch(searchQuery, () => {
    selectedIndex.value = 0;
});

watch(isOpen, (newVal) => {
    if (newVal) {
        nextTick(() => {
            searchInputRef.value?.focus();
        });
    }
});

onMounted(() => {
    document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <Teleport to="body">
        <!-- Backdrop -->
        <Transition
            enter-active-class="duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div 
                v-if="isOpen" 
                class="fixed inset-0 z-[60] bg-slate-900/60 backdrop-blur-sm" 
                @click="isOpen = false; searchQuery = ''" 
            />
        </Transition>

        <!-- Command Palette Modal -->
        <Transition
            enter-active-class="duration-200 ease-out"
            enter-from-class="opacity-0 scale-95 translate-y-4"
            enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="duration-150 ease-in"
            leave-from-class="opacity-100 scale-100 translate-y-0"
            leave-to-class="opacity-0 scale-95 translate-y-4"
        >
            <div v-if="isOpen" class="fixed inset-0 z-[70] flex items-start justify-center pt-[12vh] sm:pt-[15vh] px-4">
                <div class="w-full max-w-2xl bg-white dark:bg-slate-800 rounded-2xl shadow-2xl ring-1 ring-slate-900/10 dark:ring-slate-700 overflow-hidden">
                    <!-- Search Header -->
                    <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-200 dark:border-slate-700 bg-gradient-to-r from-violet-50 to-purple-50 dark:from-slate-800 dark:to-slate-800">
                        <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-violet-100 dark:bg-violet-900/50 shrink-0">
                            <MagnifyingGlassIcon class="h-5 w-5 text-violet-600 dark:text-violet-400" aria-hidden="true" />
                        </div>
                        <input
                            ref="searchInputRef"
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search commands, pages, or actions..."
                            class="flex-1 px-4 py-2.5 border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-violet-500 text-sm"
                        />
                        <kbd class="hidden sm:inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 shadow-sm shrink-0">
                            ESC
                        </kbd>
                        <!-- Close button for mobile -->
                        <button
                            @click="isOpen = false; searchQuery = ''"
                            class="sm:hidden flex items-center justify-center w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors shrink-0"
                            aria-label="Close search"
                        >
                            <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                        </button>
                    </div>

                    <!-- Results -->
                    <div class="max-h-[60vh] overflow-y-auto">
                        <!-- Quick Actions -->
                        <div v-if="groupedCommands.action.length" class="p-2">
                            <div class="px-3 py-2 text-xs font-bold text-violet-600 dark:text-violet-400 uppercase tracking-wider flex items-center gap-2">
                                <SparklesIcon class="h-3.5 w-3.5" aria-hidden="true" />
                                Quick Actions
                            </div>
                            <div class="space-y-1">
                                <button
                                    v-for="cmd in groupedCommands.action"
                                    :key="cmd.id"
                                    @click="executeCommand(cmd)"
                                    @mouseenter="selectedIndex = filteredCommands.indexOf(cmd)"
                                    :class="[
                                        'w-full flex items-center gap-4 px-3 py-3 rounded-xl text-left transition-all duration-150',
                                        filteredCommands.indexOf(cmd) === selectedIndex
                                            ? 'bg-violet-100 dark:bg-violet-900/40 ring-1 ring-violet-200 dark:ring-violet-700'
                                            : 'hover:bg-slate-50 dark:hover:bg-slate-700/50'
                                    ]"
                                >
                                    <div :class="[
                                        'flex items-center justify-center w-10 h-10 rounded-xl transition-colors',
                                        filteredCommands.indexOf(cmd) === selectedIndex 
                                            ? 'bg-violet-600 text-white shadow-lg shadow-violet-500/30' 
                                            : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400'
                                    ]">
                                        <component :is="cmd.icon" class="h-5 w-5" aria-hidden="true" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p :class="[
                                            'text-sm font-semibold truncate',
                                            filteredCommands.indexOf(cmd) === selectedIndex 
                                                ? 'text-violet-900 dark:text-violet-100' 
                                                : 'text-slate-900 dark:text-slate-100'
                                        ]">
                                            {{ cmd.name }}
                                        </p>
                                        <p v-if="cmd.description" class="text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5">
                                            {{ cmd.description }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <kbd v-if="cmd.shortcut" :class="[
                                            'hidden sm:inline-flex text-xs px-2 py-1 rounded-md font-mono',
                                            filteredCommands.indexOf(cmd) === selectedIndex
                                                ? 'bg-violet-200 dark:bg-violet-800 text-violet-700 dark:text-violet-300'
                                                : 'bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400'
                                        ]">
                                            {{ cmd.shortcut }}
                                        </kbd>
                                        <ArrowRightIcon v-if="filteredCommands.indexOf(cmd) === selectedIndex" class="h-4 w-4 text-violet-600 dark:text-violet-400" aria-hidden="true" />
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Navigation -->
                        <div v-if="groupedCommands.navigation.length" class="p-2 border-t border-slate-100 dark:border-slate-700">
                            <div class="px-3 py-2 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider flex items-center gap-2">
                                <HomeIcon class="h-3.5 w-3.5" aria-hidden="true" />
                                Go to Page
                            </div>
                            <div class="grid grid-cols-2 gap-1">
                                <button
                                    v-for="cmd in groupedCommands.navigation"
                                    :key="cmd.id"
                                    @click="executeCommand(cmd)"
                                    @mouseenter="selectedIndex = filteredCommands.indexOf(cmd)"
                                    :class="[
                                        'flex items-center gap-3 px-3 py-2.5 rounded-xl text-left transition-all duration-150',
                                        filteredCommands.indexOf(cmd) === selectedIndex
                                            ? 'bg-violet-100 dark:bg-violet-900/40 ring-1 ring-violet-200 dark:ring-violet-700'
                                            : 'hover:bg-slate-50 dark:hover:bg-slate-700/50'
                                    ]"
                                >
                                    <div :class="[
                                        'flex items-center justify-center w-8 h-8 rounded-lg transition-colors',
                                        filteredCommands.indexOf(cmd) === selectedIndex 
                                            ? 'bg-violet-600 text-white' 
                                            : 'bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400'
                                    ]">
                                        <component :is="cmd.icon" class="h-4 w-4" aria-hidden="true" />
                                    </div>
                                    <span :class="[
                                        'text-sm font-medium truncate',
                                        filteredCommands.indexOf(cmd) === selectedIndex 
                                            ? 'text-violet-900 dark:text-violet-100' 
                                            : 'text-slate-700 dark:text-slate-300'
                                    ]">
                                        {{ cmd.name }}
                                    </span>
                                </button>
                            </div>
                        </div>

                        <!-- No Results -->
                        <div v-if="!filteredCommands.length" class="px-6 py-12 text-center">
                            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center">
                                <MagnifyingGlassIcon class="h-8 w-8 text-slate-400" aria-hidden="true" />
                            </div>
                            <p class="text-sm font-medium text-slate-900 dark:text-slate-100">No results found</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Try searching for something else</p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-between px-5 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                        <div class="flex items-center gap-4 text-xs text-slate-500 dark:text-slate-400">
                            <span class="flex items-center gap-1.5">
                                <kbd class="px-1.5 py-1 bg-white dark:bg-slate-700 rounded border border-slate-200 dark:border-slate-600 font-mono text-[10px]">↑</kbd>
                                <kbd class="px-1.5 py-1 bg-white dark:bg-slate-700 rounded border border-slate-200 dark:border-slate-600 font-mono text-[10px]">↓</kbd>
                                <span class="hidden sm:inline">navigate</span>
                            </span>
                            <span class="flex items-center gap-1.5">
                                <kbd class="px-1.5 py-1 bg-white dark:bg-slate-700 rounded border border-slate-200 dark:border-slate-600 font-mono text-[10px]">↵</kbd>
                                <span class="hidden sm:inline">select</span>
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-slate-400 dark:text-slate-500">Powered by</span>
                            <span class="text-xs font-semibold text-violet-600 dark:text-violet-400">BizBoost</span>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
