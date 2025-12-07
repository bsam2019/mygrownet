<script setup lang="ts">
import { ref } from 'vue';
import { useBizBoostTheme } from '@/composables/useBizBoostTheme';
import {
    SunIcon,
    MoonIcon,
    ComputerDesktopIcon,
} from '@heroicons/vue/24/outline';

const { currentTheme, isDark, setTheme } = useBizBoostTheme();

const isOpen = ref(false);

const themes = [
    { value: 'light', label: 'Light', icon: SunIcon },
    { value: 'dark', label: 'Dark', icon: MoonIcon },
    { value: 'system', label: 'System', icon: ComputerDesktopIcon },
] as const;

const selectTheme = (theme: 'light' | 'dark' | 'system') => {
    setTheme(theme);
    isOpen.value = false;
};
</script>

<template>
    <div class="relative">
        <!-- Toggle Button -->
        <button
            @click="isOpen = !isOpen"
            class="p-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800 transition-colors"
            aria-label="Toggle theme"
        >
            <SunIcon v-if="!isDark" class="h-5 w-5" aria-hidden="true" />
            <MoonIcon v-else class="h-5 w-5" aria-hidden="true" />
        </button>

        <!-- Dropdown -->
        <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-if="isOpen"
                class="absolute right-0 mt-2 w-36 rounded-xl bg-white dark:bg-slate-800 shadow-lg ring-1 ring-slate-200 dark:ring-slate-700 py-1 z-50"
            >
                <button
                    v-for="theme in themes"
                    :key="theme.value"
                    @click="selectTheme(theme.value)"
                    :class="[
                        'w-full flex items-center gap-2 px-3 py-2 text-sm transition-colors',
                        currentTheme === theme.value
                            ? 'bg-violet-50 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400'
                            : 'text-slate-700 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-700'
                    ]"
                >
                    <component :is="theme.icon" class="h-4 w-4" aria-hidden="true" />
                    {{ theme.label }}
                    <span 
                        v-if="currentTheme === theme.value" 
                        class="ml-auto h-1.5 w-1.5 rounded-full bg-violet-500"
                    ></span>
                </button>
            </div>
        </Transition>

        <!-- Backdrop -->
        <div 
            v-if="isOpen" 
            class="fixed inset-0 z-40" 
            @click="isOpen = false"
        ></div>
    </div>
</template>
