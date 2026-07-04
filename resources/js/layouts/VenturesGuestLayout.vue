<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const user = computed(() => usePage().props.auth?.user);
const isSubdomain = computed(() => window.location.hostname === 'venture.mygrownet.com');
const homeUrl = computed(() => isSubdomain.value ? route('venture.sub.welcome') : route('ventures.about'));
const loginUrl = computed(() => isSubdomain.value ? route('venture.sub.login') : '#');
const registerUrl = computed(() => isSubdomain.value ? route('venture.sub.register') : '#');
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <Link :href="homeUrl" class="flex items-center gap-2 shrink-0">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-sm">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605" />
                            </svg>
                        </div>
                        <span class="text-lg font-bold text-gray-900">Venture Builder</span>
                    </Link>

                    <div v-if="isSubdomain" class="flex items-center gap-3">
                        <template v-if="user">
                            <Link href="/my-investments" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">Dashboard</Link>
                        </template>
                        <template v-else>
                            <Link :href="loginUrl" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">Sign in</Link>
                            <Link :href="registerUrl" class="inline-flex items-center px-4 py-2 rounded-lg bg-gradient-to-r from-amber-500 to-orange-600 text-white text-sm font-medium hover:from-amber-600 hover:to-orange-700 transition-all shadow-sm">Get started</Link>
                        </template>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1">
            <slot />
        </main>

        <footer class="bg-gray-900 text-gray-400">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="md:col-span-1">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605" />
                                </svg>
                            </div>
                            <span class="text-white font-bold">Venture Builder</span>
                        </div>
                        <p class="text-sm leading-relaxed">Co-invest in promising ventures and grow your portfolio. Powered by MyGrowNet.</p>
                    </div>
                    <div>
                        <h3 class="text-white text-sm font-semibold uppercase tracking-wider mb-4">Get Started</h3>
                        <ul class="space-y-2">
                            <li v-if="isSubdomain && user"><Link href="/my-investments" class="text-sm hover:text-white transition-colors">Dashboard</Link></li>
                            <li v-if="isSubdomain && !user"><Link :href="registerUrl" class="text-sm hover:text-white transition-colors">Create Account</Link></li>
                            <li v-if="isSubdomain && !user"><Link :href="loginUrl" class="text-sm hover:text-white transition-colors">Sign In</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-white text-sm font-semibold uppercase tracking-wider mb-4">Support</h3>
                        <ul class="space-y-2">
                            <li><Link href="/contact" class="text-sm hover:text-white transition-colors">Contact</Link></li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-gray-800 mt-8 pt-8 text-sm text-center">
                    <p>&copy; {{ new Date().getFullYear() }} Venture Builder, a MyGrowNet product. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>
