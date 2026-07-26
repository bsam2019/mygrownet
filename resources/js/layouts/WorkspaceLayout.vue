<script setup lang="ts">
import { computed } from 'vue';
import PlatformShell from '@/components/Platform/PlatformShell.vue';
import { LayoutDashboard, LayoutGrid, Building2 } from 'lucide-vue-next';
import type { NavItem } from '@/components/Platform/PlatformShell.vue';

const workspaceNavItems = computed((): NavItem[] => {
    const cur = route().current() as string;
    return [
        {
            label: 'Workspace',
            href: route('workspace'),
            icon: LayoutDashboard,
            isActive: () => cur === 'workspace' || (cur.startsWith('workspace.') && !cur.startsWith('workspace.organization.')),
        },
        {
            label: 'Applications',
            href: route('apps.catalog'),
            icon: LayoutGrid,
            isActive: () => cur.startsWith('apps.'),
        },
        {
            label: 'Organizations',
            href: route('workspace.organization.create'),
            icon: Building2,
            isActive: () => cur.startsWith('workspace.organization.'),
        },
    ];
});
</script>

<template>
    <PlatformShell :nav-items="workspaceNavItems" title="Workspace">
        <slot />
    </PlatformShell>
</template>
