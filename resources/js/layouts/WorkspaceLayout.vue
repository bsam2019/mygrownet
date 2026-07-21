<script setup lang="ts">
import { usePage, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import ContextSwitcher from '@/Components/Workspace/ContextSwitcher.vue';
import GlobalAppSwitcher from '@/Components/Workspace/GlobalAppSwitcher.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

const page = usePage();
const user = computed(() => (page.props.auth as any)?.user);
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <header class="bg-white border-b border-gray-200 sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <Link :href="route('workspace')" class="flex items-center hover:opacity-80 transition-opacity">
                        <AppLogoIcon class="h-9 w-auto" />
                    </Link>
                    <div class="flex items-center gap-2">
                        <GlobalAppSwitcher />
                        <ContextSwitcher />
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button class="flex items-center gap-2 text-sm font-medium text-gray-700 hover:text-gray-900 transition-colors px-2 py-1 rounded-lg hover:bg-gray-100">
                                    <span class="hidden sm:inline">{{ user?.name }}</span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            </template>
                            <div class="px-4 py-2 text-xs text-gray-400 border-b border-gray-100">{{ user?.email }}</div>
                            <DropdownLink :href="route('logout')" method="post" as="button">Sign Out</DropdownLink>
                        </Dropdown>
                    </div>
                </div>
            </div>
        </header>

        <main>
            <slot />
        </main>
    </div>
</template>
