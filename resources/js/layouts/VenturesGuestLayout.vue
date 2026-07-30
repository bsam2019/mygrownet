<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const user = computed(() => usePage().props.auth?.user);
const isSubdomain = computed(() => window.location.hostname === 'venture.mygrownet.com');

const homeUrl = computed(() => isSubdomain.value ? route('venture.sub.welcome') : route('venturebuilder.welcome'));
const aboutUrl = computed(() => isSubdomain.value ? route('venture.sub.about') : route('ventures.about'));
const policyUrl = computed(() => isSubdomain.value ? route('venture.sub.policy') : route('ventures.policy'));
const termsUrl = computed(() => isSubdomain.value ? route('venture.sub.terms') : route('ventures.terms'));
const privacyUrl = computed(() => isSubdomain.value ? route('venture.sub.privacy') : route('ventures.privacy'));
const loginUrl = computed(() => isSubdomain.value ? route('venture.sub.login') : '/login');
const registerUrl = computed(() => isSubdomain.value ? route('venture.sub.register') : '/register');
const dashboardUrl = computed(() => isSubdomain.value ? '/my-investments' : route('workspace'));

const navLinks = computed(() => [
    { name: 'Home', href: homeUrl.value },
    { name: 'About', href: aboutUrl.value },
    { name: 'Investment Policy', href: policyUrl.value },
]);

const isMobileOpen = ref(false);
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <header class="bg-gradient-to-r from-slate-900 via-slate-800 to-orange-900 sticky top-0 z-50 shadow-lg shadow-black/20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center gap-8">
                        <Link :href="homeUrl" class="flex items-center gap-3 shrink-0 group">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-lg shadow-orange-500/40 group-hover:shadow-orange-400/60 transition-shadow">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605" />
                                </svg>
                            </div>
                            <div class="flex flex-col leading-tight">
                                <span class="text-lg font-bold text-white">Venture Builder</span>
                                <span class="text-[10px] text-amber-300/70 font-medium tracking-wider uppercase">by MyGrowNet</span>
                            </div>
                        </Link>

                        <nav class="hidden md:flex items-center gap-1">
                            <Link v-for="link in navLinks" :key="link.name" :href="link.href" class="px-4 py-2 text-sm font-medium text-amber-100/80 hover:text-white hover:bg-white/10 rounded-lg transition-all">{{ link.name }}</Link>
                        </nav>
                    </div>

                    <div class="flex items-center gap-3">
                        <template v-if="user">
                            <Link :href="dashboardUrl" class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-amber-500 to-orange-600 rounded-lg hover:from-amber-400 hover:to-orange-500 transition-all shadow-lg shadow-orange-500/30">Dashboard</Link>
                        </template>
                        <template v-else>
                            <Link :href="loginUrl" class="text-sm font-medium text-amber-100/70 hover:text-white transition-colors">Sign in</Link>
                            <Link :href="registerUrl" class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-amber-500 to-orange-600 rounded-lg hover:from-amber-400 hover:to-orange-500 transition-all shadow-lg shadow-orange-500/30">Get started</Link>
                        </template>
                        <button @click="isMobileOpen = !isMobileOpen" class="md:hidden p-2 rounded-lg text-amber-100/70 hover:text-white hover:bg-white/10">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                    </div>
                </div>
                <div v-if="isMobileOpen" class="md:hidden pb-4 space-y-1">
                    <Link v-for="link in navLinks" :key="link.name" :href="link.href" class="block px-3 py-2 text-sm font-medium text-amber-100/80 hover:text-white hover:bg-white/10 rounded-lg transition-all">{{ link.name }}</Link>
                    <hr class="border-white/10 my-2">
                    <Link v-if="user" :href="dashboardUrl" class="block px-3 py-2 text-sm font-medium text-amber-100/80 hover:text-white hover:bg-white/10 rounded-lg">Dashboard</Link>
                    <template v-else>
                        <Link :href="loginUrl" class="block px-3 py-2 text-sm font-medium text-amber-100/80 hover:text-white hover:bg-white/10 rounded-lg">Sign in</Link>
                        <Link :href="registerUrl" class="block px-3 py-2 text-sm font-medium text-amber-100/80 hover:text-white hover:bg-white/10 rounded-lg">Get started</Link>
                    </template>
                </div>
            </div>
        </header>

        <main class="flex-1">
            <slot />
        </main>

        <footer class="bg-slate-900 border-t border-slate-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-white font-bold text-sm">Venture Builder</span>
                            <span class="text-slate-500 text-xs ml-1">by MyGrowNet</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-5 text-xs text-slate-400">
                        <Link v-for="link in navLinks" :key="link.name" :href="link.href" class="hover:text-white transition-colors">{{ link.name }}</Link>
                        <span class="text-slate-700">|</span>
                        <Link :href="termsUrl" class="hover:text-white transition-colors">Terms</Link>
                        <Link :href="privacyUrl" class="hover:text-white transition-colors">Privacy</Link>
                    </div>
                </div>
                <div class="mt-6 pt-6 border-t border-slate-800 text-xs text-center text-slate-600">
                    &copy; {{ new Date().getFullYear() }} Venture Builder, a MyGrowNet product. All rights reserved.
                </div>
            </div>
        </footer>
    </div>
</template>
