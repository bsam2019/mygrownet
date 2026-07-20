<script setup lang="ts">
import { usePage, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLogo from '@/Components/AppLogo.vue';
import ContextSwitcher from '@/Components/Workspace/ContextSwitcher.vue';
import GlobalAppSwitcher from '@/Components/Workspace/GlobalAppSwitcher.vue';
import { HomeIcon } from '@heroicons/vue/24/solid';

const page = usePage();
const user = computed(() => (page.props.auth as any)?.user);
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <header class="bg-white border-b border-gray-200 sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center gap-4">
                        <Link :href="route('workspace')" class="flex items-center gap-2 hover:opacity-80 transition-opacity">
                            <AppLogo class="h-8 w-auto" />
                            <span class="text-lg font-semibold text-gray-900 hidden sm:inline">MyGrowNet</span>
                        </Link>
                    </div>
                    <div class="flex items-center gap-2">
                        <Link
                            :href="route('workspace')"
                            class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors hidden sm:block"
                            title="Home"
                        >
                            <HomeIcon class="w-5 h-5" />
                        </Link>
                        <GlobalAppSwitcher />
                        <ContextSwitcher />
                        <div class="flex items-center gap-2 text-sm text-gray-600 ml-2">
                            <span class="hidden sm:inline">{{ user?.name }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main>
            <slot />
        </main>
    </div>
</template>
