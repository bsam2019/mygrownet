<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const user = computed(() => usePage().props.auth?.user);
const isSubdomain = computed(() => window.location.hostname === 'grownet.mygrownet.com');
const mobileMenuOpen = ref(false);

const homeUrl = computed(() => isSubdomain.value ? route('grownet.sub.welcome') : route('grownet.welcome'));
const loginUrl = computed(() => isSubdomain.value ? route('grownet.sub.login') : '/login');
const registerUrl = computed(() => isSubdomain.value ? route('grownet.sub.register') : '/register');
const aboutUrl = computed(() => isSubdomain.value ? route('grownet.sub.about') : '/grownet/about');
const termsUrl = computed(() => isSubdomain.value ? route('grownet.sub.terms') : '/grownet/terms');
const privacyUrl = computed(() => isSubdomain.value ? route('grownet.sub.privacy') : '/grownet/privacy');

const navLinks = [
    { label: 'Features', href: '#features' },
    { label: 'About', href: aboutUrl.value },
    { label: 'Plans', href: '#plans' },
    { label: 'Contact', href: '/contact' },
];
</script>

<template>
    <div class="min-h-screen bg-gradient-to-b from-violet-50 via-white to-white flex flex-col">
        <header class="sticky top-0 z-50 bg-white/90 backdrop-blur-lg border-b border-violet-100/60">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center gap-8">
                        <Link :href="homeUrl" class="flex items-center gap-2.5 shrink-0 group">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-violet-600 to-fuchsia-600 flex items-center justify-center shadow-lg shadow-violet-500/25 group-hover:shadow-violet-500/40 transition-shadow">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
                                </svg>
                            </div>
                            <span class="text-lg font-bold text-violet-900">GrowNet</span>
                        </Link>
                        <nav class="hidden md:flex items-center gap-1">
                            <a v-for="link in navLinks" :key="link.label"
                                :href="link.href"
                                class="px-3 py-2 text-sm font-medium text-violet-700/70 hover:text-violet-900 rounded-lg hover:bg-violet-50 transition-colors">
                                {{ link.label }}
                            </a>
                        </nav>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <template v-if="user">
                            <Link href="/workspace" class="px-4 py-2 text-sm font-medium text-violet-700 bg-violet-100 hover:bg-violet-200 rounded-lg transition-colors">Dashboard</Link>
                        </template>
                        <template v-else>
                            <Link :href="loginUrl" class="hidden sm:inline-flex px-4 py-2 text-sm font-medium text-violet-700 hover:text-violet-900 hover:bg-violet-50 rounded-lg transition-colors">Log in</Link>
                            <Link :href="registerUrl" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-violet-600 to-fuchsia-600 hover:from-violet-700 hover:to-fuchsia-700 rounded-lg shadow-lg shadow-violet-500/25 hover:shadow-violet-500/40 transition-all">Get Started</Link>
                        </template>
                        <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden ml-1 p-2 rounded-lg text-violet-600 hover:bg-violet-50 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div v-if="mobileMenuOpen" class="md:hidden pb-3 border-t border-violet-100 mt-2 pt-3">
                    <a v-for="link in navLinks" :key="link.label"
                        :href="link.href"
                        @click="mobileMenuOpen = false"
                        class="block px-3 py-2.5 text-sm font-medium text-violet-700 hover:text-violet-900 rounded-lg hover:bg-violet-50 transition-colors">
                        {{ link.label }}
                    </a>
                    <template v-if="!user">
                        <Link :href="loginUrl" @click="mobileMenuOpen = false" class="block px-3 py-2.5 text-sm font-medium text-violet-700 rounded-lg">Log in</Link>
                        <Link :href="registerUrl" @click="mobileMenuOpen = false" class="block px-3 py-2.5 mt-1 text-sm font-semibold text-white bg-gradient-to-r from-violet-600 to-fuchsia-600 rounded-lg text-center">Get Started</Link>
                    </template>
                </div>
            </div>
        </header>

        <main class="flex-1">
            <slot />
        </main>

        <footer class="bg-violet-950 text-violet-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                    <div class="col-span-2 md:col-span-1">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-fuchsia-600 flex items-center justify-center shadow-lg">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
                                </svg>
                            </div>
                            <span class="text-white font-bold">GrowNet</span>
                        </div>
                        <p class="text-sm leading-relaxed text-violet-300/70">Access online lessons, video streaming, e-books, and cloud storage. A digital content platform by MyGrowNet.</p>
                    </div>
                    <div>
                        <h3 class="text-white text-sm font-semibold uppercase tracking-wider mb-4">Platform</h3>
                        <ul class="space-y-2">
                            <li><a href="#features" class="text-sm text-violet-300/70 hover:text-violet-200 transition-colors">Features</a></li>
                            <li><Link :href="aboutUrl" class="text-sm text-violet-300/70 hover:text-violet-200 transition-colors">About</Link></li>
                            <li><a href="#plans" class="text-sm text-violet-300/70 hover:text-violet-200 transition-colors">Plans</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-white text-sm font-semibold uppercase tracking-wider mb-4">Support</h3>
                        <ul class="space-y-2">
                            <li><Link href="/contact" class="text-sm text-violet-300/70 hover:text-violet-200 transition-colors">Contact</Link></li>
                            <li><Link :href="termsUrl" class="text-sm text-violet-300/70 hover:text-violet-200 transition-colors">Terms</Link></li>
                            <li><Link :href="privacyUrl" class="text-sm text-violet-300/70 hover:text-violet-200 transition-colors">Privacy</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-white text-sm font-semibold uppercase tracking-wider mb-4">Account</h3>
                        <ul class="space-y-2">
                            <li v-if="!user"><Link :href="registerUrl" class="text-sm text-violet-300/70 hover:text-violet-200 transition-colors">Create Account</Link></li>
                            <li v-if="!user"><Link :href="loginUrl" class="text-sm text-violet-300/70 hover:text-violet-200 transition-colors">Sign In</Link></li>
                            <li v-if="user"><Link href="/workspace" class="text-sm text-violet-300/70 hover:text-violet-200 transition-colors">Dashboard</Link></li>
                            <li><Link :href="route('grownet.welcome')" class="text-sm text-violet-300/70 hover:text-violet-200 transition-colors">Home</Link></li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-violet-800/50 mt-8 pt-8 text-sm text-center">
                    <p>&copy; {{ new Date().getFullYear() }} GrowNet, a MyGrowNet product. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>
