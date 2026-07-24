<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import ToastContainer from '@/components/Shared/ToastContainer.vue';
import { useToast } from '@/composables/useToast';

const page = usePage();
const isAuthenticated = computed(() => !!page.props.auth?.client);
const showMobileMenu = ref(false);

// Toast flash messages
const { toast } = useToast();
const flash = computed(() => (page.props as any).flash);
watch(flash, (f) => {
    if (!f) return;
    if (f.success) toast.success(f.success);
    if (f.error) toast.error(f.error);
}, { immediate: true, deep: true });

const navLinks = [
    { label: 'Services', route: 'primeedge.public.services' },
    { label: 'About', route: 'primeedge.public.about' },
    { label: 'Contact', route: 'primeedge.public.contact' },
];

const isActive = (routeName: string) => {
    return route().current(routeName);
};
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-emerald-50 via-white to-emerald-50">
        <header class="bg-white/80 backdrop-blur-md border-b border-emerald-100 sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <Link :href="route('primeedge.public.landing')" class="flex items-center gap-2 hover:opacity-80 transition-opacity">
                        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-emerald-600 to-emerald-700 flex items-center justify-center shadow-md">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <span class="text-xl font-bold text-emerald-900">PrimeEdge</span>
                        <span class="hidden sm:inline text-sm text-emerald-600 font-medium">Advisory</span>
                    </Link>

                    <nav class="hidden md:flex items-center gap-6" aria-label="Main navigation">
                        <Link v-for="link in navLinks" :key="link.label" :href="route(link.route)" class="text-sm font-medium transition-colors" :class="isActive(link.route) ? 'text-emerald-700' : 'text-gray-600 hover:text-emerald-700'" :aria-current="isActive(link.route) ? 'page' : undefined">
                            {{ link.label }}
                        </Link>
                    </nav>

                    <div class="flex items-center gap-3">
                        <Link v-if="!isAuthenticated" :href="route('primeedge.login')" class="hidden sm:inline px-4 py-2 text-sm font-medium text-gray-700 hover:text-emerald-700 transition-colors">Sign In</Link>
                        <Link v-if="!isAuthenticated" :href="route('primeedge.register')" class="hidden sm:inline-flex px-4 py-2 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white text-sm font-medium rounded-lg hover:from-emerald-700 hover:to-emerald-800 transition-all shadow-sm">Get Started</Link>
                        <Link v-else :href="route('primeedge.dashboard')" class="hidden sm:inline-flex px-4 py-2 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white text-sm font-medium rounded-lg hover:from-emerald-700 hover:to-emerald-800 transition-all shadow-sm">Dashboard</Link>
                        <button @click="showMobileMenu = !showMobileMenu" class="md:hidden p-2 rounded-lg hover:bg-emerald-50" aria-label="Open menu">
                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <div v-if="showMobileMenu" class="fixed inset-0 z-50 md:hidden" @keydown.escape="showMobileMenu = false" tabindex="0">
            <div class="fixed inset-0 bg-black/30" @click="showMobileMenu = false"></div>
            <div class="fixed right-0 top-0 bottom-0 w-64 bg-white shadow-xl p-4">
                <div class="flex items-center justify-between mb-6">
                    <span class="font-bold text-emerald-900">Menu</span>
                    <button @click="showMobileMenu = false" class="p-2 rounded-lg hover:bg-gray-100" aria-label="Close menu">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <nav class="space-y-1" aria-label="Mobile navigation">
                    <Link v-for="link in navLinks" :key="link.label" :href="route(link.route)" class="block px-3 py-2.5 text-sm font-medium rounded-lg text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors" @click="showMobileMenu = false">
                        {{ link.label }}
                    </Link>
                    <hr class="my-2 border-gray-100" />
                    <Link v-if="!isAuthenticated" :href="route('primeedge.login')" class="block px-3 py-2.5 text-sm font-medium rounded-lg text-gray-700 hover:bg-emerald-50 hover:text-emerald-700" @click="showMobileMenu = false">Sign In</Link>
                    <Link v-if="!isAuthenticated" :href="route('primeedge.register')" class="block px-3 py-2.5 text-sm font-medium rounded-lg bg-emerald-600 text-white hover:bg-emerald-700" @click="showMobileMenu = false">Get Started</Link>
                    <Link v-else :href="route('primeedge.dashboard')" class="block px-3 py-2.5 text-sm font-medium rounded-lg bg-emerald-600 text-white hover:bg-emerald-700" @click="showMobileMenu = false">Dashboard</Link>
                </nav>
            </div>
        </div>

        <main><slot /></main>

        <footer class="bg-emerald-900 text-emerald-200 mt-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-8 h-8 rounded-lg bg-emerald-700 flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <span class="text-lg font-bold text-white">PrimeEdge Advisory</span>
                        </div>
                        <p class="text-sm text-emerald-300">Smart financial solutions for business growth and financial security.</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Services</h3>
                        <ul class="space-y-2 text-sm">
                            <li><Link :href="route('primeedge.public.services')" class="hover:text-white transition-colors">Accounting & Compliance</Link></li>
                            <li><Link :href="route('primeedge.public.services')" class="hover:text-white transition-colors">Business Advisory</Link></li>
                            <li><Link :href="route('primeedge.public.services')" class="hover:text-white transition-colors">Personal Financial Advisory</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Contact</h3>
                        <ul class="space-y-2 text-sm">
                            <li>Lusaka, Zambia</li>
                            <li><a href="mailto:info@primeedgeadvisory.com" class="hover:text-white transition-colors">info@primeedgeadvisory.com</a></li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-emerald-800 mt-8 pt-8 text-center text-sm text-emerald-400">
                    &copy; {{ new Date().getFullYear() }} PrimeEdge Advisory. All rights reserved.
                </div>
            </div>
        </footer>

        <ToastContainer />
    </div>
</template>
