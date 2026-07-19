<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const user = computed(() => usePage().props.auth?.user);
const isSubdomain = computed(() => window.location.hostname === 'growbuilder.mygrownet.com');
const homeUrl = computed(() => isSubdomain.value ? route('growbuilder.sub.welcome') : route('growbuilder.welcome'));
const loginUrl = computed(() => isSubdomain.value ? route('growbuilder.sub.login') : '#');
const registerUrl = computed(() => isSubdomain.value ? route('growbuilder.sub.register') : '#');
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <Link :href="homeUrl" class="flex items-center gap-2 shrink-0">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center shadow-sm">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6V4.5A1.5 1.5 0 017.5 3h9A1.5 1.5 0 0118 4.5V6M6 6v12a3 3 0 003 3h6a3 3 0 003-3V6M6 6H3m15 0h3" />
                            </svg>
                        </div>
                        <span class="text-lg font-bold text-gray-900">GrowBuilder</span>
                    </Link>

                    <div v-if="isSubdomain" class="flex items-center gap-3">
                        <template v-if="user">
                            <Link href="/workspace" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">Dashboard</Link>
                        </template>
                        <template v-else>
                            <Link :href="loginUrl" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">Sign in</Link>
                            <Link :href="registerUrl" class="inline-flex items-center px-4 py-2 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-700 text-white text-sm font-medium hover:from-blue-700 hover:to-indigo-800 transition-all shadow-sm">Get started</Link>
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
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6V4.5A1.5 1.5 0 017.5 3h9A1.5 1.5 0 0118 4.5V6M6 6v12a3 3 0 003 3h6a3 3 0 003-3V6M6 6H3m15 0h3" />
                                </svg>
                            </div>
                            <span class="text-white font-bold">GrowBuilder</span>
                        </div>
                        <p class="text-sm leading-relaxed">Build professional websites with drag-and-drop simplicity. Powered by MyGrowNet.</p>
                    </div>
                    <div>
                        <h3 class="text-white text-sm font-semibold uppercase tracking-wider mb-4">Get Started</h3>
                        <ul class="space-y-2">
                            <li v-if="isSubdomain && user"><Link href="/workspace" class="text-sm hover:text-white transition-colors">Dashboard</Link></li>
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
                    <p>&copy; {{ new Date().getFullYear() }} GrowBuilder, a MyGrowNet product. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>
