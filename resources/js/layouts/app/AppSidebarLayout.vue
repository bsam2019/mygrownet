<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import ImpersonationBanner from '@/components/ImpersonationBanner.vue';
import type { BreadcrumbItemType, NavItem } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
    footerNavItems?: NavItem[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    footerNavItems: () => []
});

const page = usePage();

// Check if admin is impersonating
const isImpersonating = computed(() => {
    return page.props.impersonate_admin_id !== undefined && page.props.impersonate_admin_id !== null;
});

const currentUserName = computed(() => {
    return page.props.auth?.user?.name || '';
});
</script>

<template>
    <ImpersonationBanner v-if="isImpersonating" :user-name="currentUserName" />
    <AppShell variant="sidebar">
        <AppSidebar :footer-nav-items="footerNavItems" />
        <AppContent variant="sidebar">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
        </AppContent>
    </AppShell>
</template>
