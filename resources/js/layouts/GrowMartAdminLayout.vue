<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import GrowMartAdminSidebar from '@/components/GrowMart/GrowMartAdminSidebar.vue';
import ToastContainer from '@/components/GrowMart/ToastContainer.vue';
import { useToast } from '@/composables/useToast';
import type { BreadcrumbItemType } from '@/types';
import { User, LogOut } from 'lucide-vue-next';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), { breadcrumbs: () => [] });

const page = usePage();
const { toast } = useToast();
const isMobile = ref(false);
const savedState = localStorage.getItem('growmart.sidebarCollapsed');
const sidebarCollapsed = ref(savedState === 'true');
const flash = computed(() => (page.props as any).flash);

watch(flash, (f) => {
    if (!f) return;
    if (f.success) toast.success(f.success, 5000);
    if (f.error) toast.error(f.error, 8000);
    if (f.warning) toast.warning(f.warning, 5000);
    if (f.info) toast.info(f.info, 5000);
}, { immediate: true, deep: true });

const userName = computed(() => (page.props as any)?.auth?.user?.name ?? 'Admin');

const checkMobile = () => { isMobile.value = window.innerWidth < 1024; };
const logout = () => router.post('/logout');

onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
});
</script>

<template>
    <div class="flex min-h-screen w-full bg-gray-50">
        <GrowMartAdminSidebar />
        <div class="flex-1 flex flex-col transition-all duration-300" :class="isMobile ? 'ml-0' : (sidebarCollapsed ? 'ml-16' : 'ml-64')">
            <header class="flex h-16 items-center justify-between px-6 bg-white border-b border-gray-200">
                <div class="flex items-center gap-2 text-sm text-gray-500">
                    <span v-if="breadcrumbs.length > 0">
                        <Link href="/admin/growmart" class="hover:text-emerald-600">GrowMart</Link>
                        <span v-for="(crumb, i) in breadcrumbs" :key="i">
                            <span class="mx-2">/</span>
                            <span v-if="crumb.href && !crumb.current" class="text-gray-700">{{ crumb.title }}</span>
                            <span v-else class="text-gray-900 font-medium">{{ crumb.title }}</span>
                        </span>
                    </span>
                    <span v-else class="text-gray-900 font-medium">Dashboard</span>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="logout" class="flex items-center gap-2 px-3 py-2 text-sm text-gray-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                        <LogOut class="h-4 w-4" />
                        <span class="hidden sm:inline">Logout</span>
                    </button>
                    <div class="flex items-center gap-2 px-3 py-1.5 bg-gray-100 rounded-full text-sm">
                        <User class="h-4 w-4 text-gray-500" />
                        <span class="text-gray-700 font-medium">{{ userName }}</span>
                    </div>
                </div>
            </header>
            <main class="flex-1 p-6">
                <slot />
            </main>
        </div>
    </div>
    <ToastContainer />
</template>
