<script setup lang="ts">
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import ImpersonationBanner from '@/components/ImpersonationBanner.vue';
import UnifiedLiveChatWidget from '@/components/Support/UnifiedLiveChatWidget.vue';
import UpdateNotification from '@/components/UpdateNotification.vue';
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

const currentUserId = computed(() => {
    return page.props.auth?.user?.id || 0;
});

const handleSidebarToggle = (collapsed: boolean) => {
    sidebarCollapsed.value = collapsed;
};

const checkMobile = () => {
    isMobile.value = window.innerWidth < 1024;
};

// Live Chat Widget handler
const handleTicketCreated = (ticketId: number) => {
    console.log('Support ticket created:', ticketId);
};

const liveChatWidgetRef = ref<any>(null);

const handleOpenLiveChat = () => {
    if (liveChatWidgetRef.value) {
        liveChatWidgetRef.value.openChat();
    }
};

onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
});
</script>

<template>
    <div class="flex h-screen w-full overflow-hidden">
        <AppSidebar 
            :footer-nav-items="footerNavItems" 
            @update:collapsed="handleSidebarToggle"
            @open-live-chat="handleOpenLiveChat"
        />
        <div 
            class="flex-1 flex flex-col transition-all duration-300 overflow-hidden" 
            :class="isMobile ? 'ml-0' : (sidebarCollapsed ? 'ml-16' : 'ml-64')"
        >
            <ImpersonationBanner v-if="isImpersonating" :user-name="currentUserName" />
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <main class="flex-1 overflow-y-auto p-6">
                <slot />
            </main>
        </div>

        <!-- Update Notification -->
        <UpdateNotification />

        <!-- Live Chat Support Widget -->
        <UnifiedLiveChatWidget
            ref="liveChatWidgetRef"
            user-type="member"
            :user-id="currentUserId"
            :user-name="currentUserName"
            @ticket-created="handleTicketCreated"
        />
    </div>
</template>
