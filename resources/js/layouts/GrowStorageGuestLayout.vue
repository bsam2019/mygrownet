<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const user = computed(() => usePage().props.auth?.user);
const isSubdomain = computed(() => window.location.hostname === 'growstorage.mygrownet.com');
const homeUrl = computed(() => isSubdomain.value ? route('growstorage.sub.welcome') : route('growbackup.welcome'));
const loginUrl = computed(() => isSubdomain.value ? route('growstorage.sub.login') : '#');
const registerUrl = computed(() => isSubdomain.value ? route('growstorage.sub.register') : '#');
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <Link :href="homeUrl" class="flex items-center gap-2 shrink-0">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center shadow-sm">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                            </svg>
                        </div>
                        <span class="text-lg font-bold text-gray-900">GrowStorage</span>
                    </Link>

                    <div v-if="isSubdomain" class="flex items-center gap-3">
                        <template v-if="user">
                            <Link href="/dashboard" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">Dashboard</Link>
                        </template>
                        <template v-else>
                            <Link :href="loginUrl" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">Sign in</Link>
                            <Link :href="registerUrl" class="inline-flex items-center px-4 py-2 rounded-lg bg-gradient-to-r from-cyan-500 to-blue-600 text-white text-sm font-medium hover:from-cyan-600 hover:to-blue-700 transition-all shadow-sm">Get started</Link>
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
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                                </svg>
                            </div>
                            <span class="text-white font-bold">GrowStorage</span>
                        </div>
                        <p class="text-sm leading-relaxed">Secure cloud storage and file backup for your business. Powered by MyGrowNet.</p>
                    </div>
                    <div>
                        <h3 class="text-white text-sm font-semibold uppercase tracking-wider mb-4">Get Started</h3>
                        <ul class="space-y-2">
                            <li v-if="isSubdomain && user"><Link href="/dashboard" class="text-sm hover:text-white transition-colors">Dashboard</Link></li>
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
                    <p>&copy; {{ new Date().getFullYear() }} GrowStorage, a MyGrowNet product. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>
