<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const user = computed(() => usePage().props.auth?.user);
const isSubdomain = computed(() => window.location.hostname === 'bizboost.mygrownet.com');
const homeUrl = computed(() => isSubdomain.value ? route('bizboost.sub.welcome') : route('bizboost.welcome'));
const loginUrl = computed(() => isSubdomain.value ? route('bizboost.sub.login') : '#');
const registerUrl = computed(() => isSubdomain.value ? route('bizboost.sub.register') : '#');
const termsUrl = computed(() => isSubdomain.value ? route('bizboost.sub.terms') : '#');
const privacyUrl = computed(() => isSubdomain.value ? route('bizboost.sub.privacy') : '#');
const aboutUrl = computed(() => isSubdomain.value ? route('bizboost.sub.about') : '#');
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <!-- Navigation -->
        <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo -->
                    <Link :href="homeUrl" class="flex items-center gap-2 shrink-0">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-purple-600 to-purple-700 flex items-center justify-center shadow-sm">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                            </svg>
                        </div>
                        <span class="text-lg font-bold text-gray-900">BizBoost</span>
                    </Link>

                    <!-- Right section -->
                    <div v-if="isSubdomain" class="flex items-center gap-3">
                        <template v-if="user">
                            <Link href="/dashboard" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
                                Dashboard
                            </Link>
                        </template>
                        <template v-else>
                            <Link :href="loginUrl" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
                                Sign in
                            </Link>
                            <Link :href="registerUrl" class="inline-flex items-center px-4 py-2 rounded-lg bg-gradient-to-r from-purple-600 to-purple-700 text-white text-sm font-medium hover:from-purple-700 hover:to-purple-800 transition-all shadow-sm">
                                Get started
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-gray-900 text-gray-400">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Brand -->
                    <div class="md:col-span-1">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                                </svg>
                            </div>
                            <span class="text-white font-bold">BizBoost</span>
                        </div>
                        <p class="text-sm leading-relaxed">
                            Marketing & Growth Assistant for small and medium enterprises. Powered by MyGrowNet.
                        </p>
                    </div>

                    <!-- Links -->
                    <div>
                        <h3 class="text-white text-sm font-semibold uppercase tracking-wider mb-4">Company</h3>
                        <ul class="space-y-2">
                            <li v-if="isSubdomain">
                                <Link :href="aboutUrl" class="text-sm hover:text-white transition-colors">About</Link>
                            </li>
                            <li v-if="isSubdomain">
                                <Link :href="termsUrl" class="text-sm hover:text-white transition-colors">Terms of Service</Link>
                            </li>
                            <li v-if="isSubdomain">
                                <Link :href="privacyUrl" class="text-sm hover:text-white transition-colors">Privacy Policy</Link>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-white text-sm font-semibold uppercase tracking-wider mb-4">Get Started</h3>
                        <ul class="space-y-2">
                            <li v-if="isSubdomain && user">
                                <Link href="/dashboard" class="text-sm hover:text-white transition-colors">Dashboard</Link>
                            </li>
                            <li v-if="isSubdomain && !user">
                                <Link :href="registerUrl" class="text-sm hover:text-white transition-colors">Create Account</Link>
                            </li>
                            <li v-if="isSubdomain && !user">
                                <Link :href="loginUrl" class="text-sm hover:text-white transition-colors">Sign In</Link>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="border-t border-gray-800 mt-8 pt-8 text-sm text-center">
                    <p>&copy; {{ new Date().getFullYear() }} BizBoost, a MyGrowNet product. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>
