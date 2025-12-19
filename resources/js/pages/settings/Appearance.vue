<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import ClientLayout from '@/layouts/ClientLayout.vue';
import { useAppearance } from '@/composables/useAppearance';
import {
    SunIcon,
    MoonIcon,
    ComputerDesktopIcon,
    ArrowLeftIcon,
    PaintBrushIcon,
    CheckIcon,
} from '@heroicons/vue/24/outline';

const { appearance, updateAppearance } = useAppearance();

const themes = [
    { 
        value: 'light', 
        icon: SunIcon, 
        label: 'Light', 
        description: 'Clean and bright interface',
        gradient: 'from-amber-400 to-orange-500',
        bg: 'bg-gradient-to-br from-amber-50 to-orange-50',
    },
    { 
        value: 'dark', 
        icon: MoonIcon, 
        label: 'Dark', 
        description: 'Easy on the eyes at night',
        gradient: 'from-slate-600 to-slate-800',
        bg: 'bg-gradient-to-br from-slate-100 to-slate-200',
    },
    { 
        value: 'system', 
        icon: ComputerDesktopIcon, 
        label: 'System', 
        description: 'Follows your device settings',
        gradient: 'from-blue-500 to-indigo-600',
        bg: 'bg-gradient-to-br from-blue-50 to-indigo-50',
    },
] as const;
</script>

<template>
    <ClientLayout>
        <Head title="Appearance Settings" />

        <div class="max-w-2xl mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-8">
                <Link
                    href="/settings/profile"
                    class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 mb-4"
                >
                    <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                    Back to Profile
                </Link>
                <h1 class="text-2xl font-bold text-gray-900">Appearance</h1>
                <p class="text-gray-500 mt-1">Customize how the app looks</p>
            </div>

            <!-- Appearance Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-br from-pink-500 to-rose-600 px-6 py-8 text-center">
                    <div class="w-16 h-16 mx-auto rounded-full bg-white/20 flex items-center justify-center mb-4">
                        <PaintBrushIcon class="h-8 w-8 text-white" aria-hidden="true" />
                    </div>
                    <h2 class="text-xl font-bold text-white">Theme Settings</h2>
                    <p class="text-pink-100 text-sm mt-1">Choose your preferred color scheme</p>
                </div>

                <!-- Theme Options -->
                <div class="p-6">
                    <div class="space-y-4">
                        <button
                            v-for="theme in themes"
                            :key="theme.value"
                            @click="updateAppearance(theme.value)"
                            :class="[
                                'w-full p-4 rounded-xl border-2 transition-all text-left flex items-center gap-4',
                                appearance === theme.value
                                    ? 'border-pink-500 bg-pink-50'
                                    : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'
                            ]"
                        >
                            <div :class="['w-12 h-12 rounded-xl flex items-center justify-center bg-gradient-to-br', theme.gradient]">
                                <component :is="theme.icon" class="h-6 w-6 text-white" aria-hidden="true" />
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-gray-900">{{ theme.label }}</p>
                                <p class="text-sm text-gray-500">{{ theme.description }}</p>
                            </div>
                            <div 
                                v-if="appearance === theme.value"
                                class="w-8 h-8 rounded-full bg-pink-500 flex items-center justify-center"
                            >
                                <CheckIcon class="h-5 w-5 text-white" aria-hidden="true" />
                            </div>
                        </button>
                    </div>

                    <!-- Preview -->
                    <div class="mt-8 pt-6 border-t border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-700 mb-4">Preview</h3>
                        <div :class="[
                            'rounded-xl p-4 border transition-colors',
                            appearance === 'dark' ? 'bg-gray-900 border-gray-700' : 'bg-white border-gray-200'
                        ]">
                            <div class="flex items-center gap-3 mb-3">
                                <div :class="['w-10 h-10 rounded-full', appearance === 'dark' ? 'bg-gray-700' : 'bg-gray-200']"></div>
                                <div>
                                    <div :class="['h-3 w-24 rounded', appearance === 'dark' ? 'bg-gray-700' : 'bg-gray-200']"></div>
                                    <div :class="['h-2 w-16 rounded mt-1', appearance === 'dark' ? 'bg-gray-800' : 'bg-gray-100']"></div>
                                </div>
                            </div>
                            <div :class="['h-2 w-full rounded mb-2', appearance === 'dark' ? 'bg-gray-800' : 'bg-gray-100']"></div>
                            <div :class="['h-2 w-3/4 rounded', appearance === 'dark' ? 'bg-gray-800' : 'bg-gray-100']"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info -->
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-2xl p-4">
                <p class="text-sm text-blue-700">
                    <span class="font-semibold">Tip:</span> System theme automatically switches between light and dark based on your device's settings.
                </p>
            </div>
        </div>
    </ClientLayout>
</template>
