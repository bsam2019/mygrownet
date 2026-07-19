<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const user = computed(() => usePage().props.auth?.user);
const isSubdomain = computed(() => window.location.hostname === 'bizdocs.mygrownet.com');
const homeUrl = computed(() => isSubdomain.value ? route('bizdocs.sub.welcome') : route('bizdocs.welcome'));
const loginUrl = computed(() => isSubdomain.value ? route('bizdocs.sub.login') : '#');
const registerUrl = computed(() => isSubdomain.value ? route('bizdocs.sub.register') : '#');
const termsUrl = computed(() => isSubdomain.value ? route('bizdocs.sub.terms') : '#');
const privacyUrl = computed(() => isSubdomain.value ? route('bizdocs.sub.privacy') : '#');
const aboutUrl = computed(() => isSubdomain.value ? route('bizdocs.sub.about') : '#');
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <Link :href="homeUrl" class="flex items-center gap-2 shrink-0">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-sm">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>
                        <span class="text-lg font-bold text-gray-900">BizDocs</span>
                    </Link>

                    <div v-if="isSubdomain" class="flex items-center gap-3">
                        <template v-if="user">
                            <Link href="/workspace" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">Dashboard</Link>
                        </template>
                        <template v-else>
                            <Link :href="loginUrl" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">Sign in</Link>
                            <Link :href="registerUrl" class="inline-flex items-center px-4 py-2 rounded-lg bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-sm font-medium hover:from-emerald-600 hover:to-teal-700 transition-all shadow-sm">Get started</Link>
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
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                            </div>
                            <span class="text-white font-bold">BizDocs</span>
                        </div>
                        <p class="text-sm leading-relaxed">Professional document generation for Zambian businesses. Powered by MyGrowNet.</p>
                    </div>
                    <div>
                        <h3 class="text-white text-sm font-semibold uppercase tracking-wider mb-4">Company</h3>
                        <ul class="space-y-2">
                            <li v-if="isSubdomain"><Link :href="aboutUrl" class="text-sm hover:text-white transition-colors">About</Link></li>
                            <li v-if="isSubdomain"><Link :href="termsUrl" class="text-sm hover:text-white transition-colors">Terms of Service</Link></li>
                            <li v-if="isSubdomain"><Link :href="privacyUrl" class="text-sm hover:text-white transition-colors">Privacy Policy</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-white text-sm font-semibold uppercase tracking-wider mb-4">Get Started</h3>
                        <ul class="space-y-2">
                            <li v-if="isSubdomain && user"><Link href="/workspace" class="text-sm hover:text-white transition-colors">Dashboard</Link></li>
                            <li v-if="isSubdomain && !user"><Link :href="registerUrl" class="text-sm hover:text-white transition-colors">Create Account</Link></li>
                            <li v-if="isSubdomain && !user"><Link :href="loginUrl" class="text-sm hover:text-white transition-colors">Sign In</Link></li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-gray-800 mt-8 pt-8 text-sm text-center">
                    <p>&copy; {{ new Date().getFullYear() }} BizDocs, a MyGrowNet product. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>
