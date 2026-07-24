<script setup lang="ts">
import { Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import ToastContainer from '@/components/Shared/ToastContainer.vue';
import { useToast } from '@/composables/useToast';

const page = usePage();
const showMobileMenu = ref(false);

const client = computed(() => (page.props as any).auth?.client || null);

// Toast flash messages
const { toast } = useToast();
const flash = computed(() => (page.props as any).flash);
watch(flash, (f) => {
    if (!f) return;
    if (f.success) toast.success(f.success);
    if (f.error) toast.error(f.error);
}, { immediate: true, deep: true });

const logout = () => {
    router.post(route('primeedge.logout'));
};

const navItems = [
    { label: 'Dashboard', route: 'primeedge.dashboard', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    { label: 'Inquiries', route: 'primeedge.inquiries.index', icon: 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z' },
    { label: 'Engagements', route: 'primeedge.engagements.index', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
    { label: 'Documents', route: 'primeedge.documents.index', icon: 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z' },
    { label: 'Compliance', route: 'primeedge.compliance.index', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
    { label: 'Invoices', route: 'primeedge.invoices.index', icon: 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z' },
    { label: 'Appointments', route: 'primeedge.appointments.index', icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' },
];

const isActive = (routeName: string) => {
    return route().current(routeName) || route().current(routeName + '.*');
};
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <header class="bg-white border-b border-gray-200 sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center gap-4">
                        <button @click="showMobileMenu = !showMobileMenu" class="lg:hidden p-2 rounded-lg hover:bg-gray-100" aria-label="Open menu">
                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <Link :href="route('primeedge.dashboard')" class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-600 to-emerald-700 flex items-center justify-center shadow-sm">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <span class="text-lg font-bold text-emerald-900">PrimeEdge</span>
                        </Link>
                    </div>

                    <nav class="hidden lg:flex items-center gap-1" aria-label="Main navigation">
                        <Link v-for="item in navItems" :key="item.label" :href="route(item.route)" class="px-3 py-2 text-sm font-medium rounded-lg transition-colors" :class="isActive(item.route) ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'" :aria-current="isActive(item.route) ? 'page' : undefined">
                            {{ item.label }}
                        </Link>
                    </nav>

                    <div class="flex items-center gap-3">
                        <Link :href="route('primeedge.profile.edit')" class="text-sm text-gray-600 hover:text-gray-900">{{ client?.name }}</Link>
                        <button @click="logout" class="px-3 py-1.5 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors">Logout</button>
                    </div>
                </div>
            </div>
        </header>

        <div v-if="showMobileMenu" class="fixed inset-0 z-50 lg:hidden" @keydown.escape="showMobileMenu = false" tabindex="0">
            <div class="fixed inset-0 bg-black/30" @click="showMobileMenu = false"></div>
            <div class="fixed left-0 top-0 bottom-0 w-64 bg-white shadow-xl p-4">
                <div class="flex items-center justify-between mb-6">
                    <span class="font-bold text-emerald-900">Menu</span>
                    <button @click="showMobileMenu = false" class="p-2 rounded-lg hover:bg-gray-100" aria-label="Close menu">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <nav class="space-y-1" aria-label="Mobile navigation">
                    <Link v-for="item in navItems" :key="item.label" :href="route(item.route)" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-colors" :class="isActive(item.route) ? 'bg-emerald-50 text-emerald-700' : 'text-gray-700 hover:bg-emerald-50 hover:text-emerald-700'" @click="showMobileMenu = false">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" /></svg>
                        {{ item.label }}
                    </Link>
                </nav>
            </div>
        </div>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <slot />
        </main>

        <ToastContainer />
    </div>
</template>
