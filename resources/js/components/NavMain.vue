<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem, SidebarMenuSub, SidebarMenuSubButton } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';
import { ChevronRightIcon } from 'lucide-vue-next';

interface NavGroup {
    label: string; // required
    items: NavItem[];
    icon?: any; // optional icon component for the group
}

const props = defineProps<{
    groups: NavGroup[];
}>();

const page = usePage();
// Track collapsed state; persisted in localStorage so groups remain open after navigation
const collapsedGroups = ref<Set<string>>(new Set());
const STORAGE_KEY = 'mygrownet.sidebar.collapsedGroups';

// Initialize collapsed state: prefer saved state; otherwise collapse ALL groups by default
const initializeCollapsedGroups = (groups: NavGroup[]) => {
    const saved = localStorage.getItem(STORAGE_KEY);
    if (saved) {
        try {
            const arr: string[] = JSON.parse(saved);
            collapsedGroups.value = new Set(arr);
            return;
        } catch {}
    }

    // Collapse all groups by default
    const newCollapsed = new Set<string>();
    groups.forEach(group => {
        if (group.label) {
            newCollapsed.add(group.label);
        }
    });
    collapsedGroups.value = newCollapsed;
};

// Initialize on mount and when groups change
onMounted(() => {
    if (props.groups?.length) initializeCollapsedGroups(props.groups);
});
watch(
    () => props.groups,
    (val) => {
        if (val?.length) initializeCollapsedGroups(val);
    },
    { deep: true }
);

// Persist collapsed state
watch(
    collapsedGroups,
    (setVal) => {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(Array.from(setVal)));
    },
    { deep: true }
);

const toggleGroup = (groupLabel: string) => {
    if (collapsedGroups.value.has(groupLabel)) {
        collapsedGroups.value.delete(groupLabel);
    } else {
        collapsedGroups.value.add(groupLabel);
    }
};

const isGroupCollapsed = (groupLabel: string) => {
    return collapsedGroups.value.has(groupLabel);
};

// Determine active state robustly (route URLs vs current page URL)
const isActive = (itemHref: string | undefined) => {
    if (!itemHref) return false;
    try {
        const pathname = new URL(itemHref, window.location.origin).pathname;
        // Exact match or section match for nested routes
        return page.url === pathname || page.url.startsWith(pathname + '/');
    } catch {
        // Fallback: compare raw strings
        return page.url === itemHref || page.url.startsWith(itemHref + '/');
    }
};

// Ensure the group containing the active link stays open on route changes
watch(
    () => page.url,
    () => {
        if (!props.groups?.length) return;
        props.groups.forEach((group) => {
            const hasActive = group.items?.some((i) => isActive(i.href));
            if (hasActive) {
                collapsedGroups.value.delete(group.label);
            }
        });
    }
);
</script>

<template>
    <div v-for="group in groups" :key="group.label">
        <SidebarGroup class="px-2 py-0">
            <SidebarGroupLabel 
                class="cursor-pointer flex items-center justify-between hover:bg-sidebar-accent hover:text-sidebar-accent-foreground rounded-md px-2 py-2 transition-colors text-xs font-bold uppercase tracking-wider text-gray-500 hover:text-gray-700"
                @click="toggleGroup(group.label)"
            >
                <span class="flex items-center gap-2">
                    <component v-if="group.icon" :is="group.icon" class="h-3.5 w-3.5" />
                    <span>{{ group.label }}</span>
                </span>
                <ChevronRightIcon 
                    class="h-3.5 w-3.5 transition-transform duration-200" 
                    :class="{ 'rotate-90': !isGroupCollapsed(group.label) }"
                />
            </SidebarGroupLabel>
            <SidebarMenu :class="!isGroupCollapsed(group.label) ? 'mt-1' : 'group-data-[collapsible=icon]:block hidden'">
                <SidebarMenuItem v-for="item in group.items" :key="item.title" :title="item.title">
                    <SidebarMenuButton
                        as-child
                        :is-active="isActive(item.href)"
                        :class="[
                            'ml-2 text-sm',
                            isActive(item.href)
                                ? 'border-l-2 border-[#2563eb] bg-[#eff6ff] text-[#1d4ed8] font-medium'
                                : 'text-gray-700 hover:text-gray-900'
                        ]"
                        :title="item.title"
                    >
                        <Link :href="item.href">
                            <component :is="item.icon" class="h-4 w-4" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarGroup>
    </div>
</template>
