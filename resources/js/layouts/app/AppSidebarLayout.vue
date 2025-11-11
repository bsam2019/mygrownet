<script setup lang="ts">
import AppSidebar from '@/components/MyGrowNetSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import ImpersonationBanner from '@/components/ImpersonationBanner.vue';
import InstallPrompt from '@/Components/Mobile/InstallPrompt.vue';
import type { BreadcrumbItemType, NavItem } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
    footerNavItems?: NavItem[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    footerNavItems: () => []
});

const page = usePage();
const isMobile = ref(false);

// Initialize from localStorage to match sidebar's initial state
const savedState = localStorage.getItem('mygrownet.sidebarCollapsed');
const sidebarCollapsed = ref(savedState === 'true');

// Check if admin is impersonating
const isImpersonating = computed(() => {
    return page.props.impersonate_admin_id !== undefined && page.props.impersonate_admin_id !== null;
});

const currentUserName = computed(() => {
    return page.props.auth?.user?.name || '';
});

const handleSidebarToggle = (collapsed: boolean) => {
    sidebarCollapsed.value = collapsed;
};

const checkMobile = () => {
    isMobile.value = window.innerWidth < 1024;
};

onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
});
</script>

<template>
    <div class="flex min-h-screen w-full">
        <AppSidebar 
            :footer-nav-items="footerNavItems" 
            @update:collapsed="handleSidebarToggle"
        />
        <div 
            class="flex-1 flex flex-col transition-all duration-300" 
            :class="isMobile ? 'ml-0' : (sidebarCollapsed ? 'ml-16' : 'ml-64')"
        >
            <ImpersonationBanner v-if="isImpersonating" :user-name="currentUserName" />
            <div class="flex h-full w-full flex-col">
                <AppSidebarHeader :breadcrumbs="breadcrumbs" />
                <main class="flex-1 p-6">
                    <slot />
                </main>
            </div>
        </div>
        
        <!-- PWA Install Prompt -->
        <InstallPrompt />
    </div>
</template>
