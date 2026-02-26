<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import GrowBackupFooter from '@/Components/Storage/GrowBackupFooter.vue';

interface Props {
    title?: string;
}

defineProps<Props>();
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <Head :title="title" />
        
        <!-- GrowBackup Header -->
        <header class="bg-white border-b border-gray-200 sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo & Brand -->
                    <div class="flex items-center gap-2 sm:gap-3">
                        <a href="/dashboard" class="flex items-center gap-1 sm:gap-2 text-gray-600 hover:text-gray-900 transition-colors">
                            <span class="text-sm sm:text-base font-medium">MyGrowNet</span>
                        </a>
                        <span class="text-gray-300 hidden sm:inline">/</span>
                        <div class="flex items-center gap-1 sm:gap-2">
                            <span class="text-xl sm:text-2xl">☁️</span>
                            <div class="hidden sm:block">
                                <h1 class="text-lg font-semibold text-gray-900">GrowBackup</h1>
                                <p class="text-xs text-gray-500">Secure Cloud Storage</p>
                            </div>
                            <h1 class="sm:hidden text-base font-semibold text-gray-900">GrowBackup</h1>
                        </div>
                    </div>
                    
                    <!-- Navigation -->
                    <nav class="flex items-center gap-3 sm:gap-6">
                        <a 
                            href="/growbackup/dashboard"
                            class="text-xs sm:text-sm font-medium transition-colors"
                            :class="$page.url.startsWith('/growbackup/dashboard') 
                                ? 'text-blue-600' 
                                : 'text-gray-600 hover:text-gray-900'"
                        >
                            <span class="hidden sm:inline">My Files</span>
                            <span class="sm:hidden">Files</span>
                        </a>
                        <a 
                            href="/growbackup/subscription"
                            class="text-xs sm:text-sm font-medium transition-colors"
                            :class="$page.url.startsWith('/growbackup/subscription') 
                                ? 'text-blue-600' 
                                : 'text-gray-600 hover:text-gray-900'"
                        >
                            <span class="hidden sm:inline">Plans & Pricing</span>
                            <span class="sm:hidden">Plans</span>
                        </a>
                    </nav>
                </div>
            </div>
        </header>
        
        <!-- Main Content -->
        <main class="flex-1">
            <slot />
        </main>

        <!-- Footer -->
        <GrowBackupFooter />
    </div>
</template>
