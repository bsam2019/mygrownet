<script setup lang="ts">
// Footer component for public share pages
</script>

<template>
    <footer class="mt-12 pt-8 border-t border-gray-200">
        <div class="max-w-4xl mx-auto px-4">
            <!-- Main Footer Content -->
            <div class="text-center space-y-4">
                <!-- Branding -->
                <div class="flex items-center justify-center gap-2 text-gray-600">
                    <span class="text-2xl">☁️</span>
                    <span class="font-semibold">GrowBackup</span>
                    <span class="text-gray-400">•</span>
                    <span class="text-sm">Secure Cloud Storage</span>
                </div>

                <!-- Policy Links -->
                <div class="flex flex-wrap items-center justify-center gap-4 text-sm">
                    <a 
                        href="/privacy" 
                        class="text-gray-600 hover:text-blue-600 transition-colors"
                    >
                        Privacy Policy
                    </a>
                    <span class="text-gray-300">•</span>
                    <a 
                        href="/terms" 
                        class="text-gray-600 hover:text-blue-600 transition-colors"
                    >
                        Terms of Service
                    </a>
                    <span class="text-gray-300">•</span>
                    <a 
                        href="/policies" 
                        class="text-gray-600 hover:text-blue-600 transition-colors"
                    >
                        Policies
                    </a>
                </div>

                <!-- CTA -->
                <div class="pt-4">
                    <a 
                        href="/growbackup" 
                        class="inline-flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 font-medium"
                    >
                        Get your own secure cloud storage
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>

                <!-- Copyright -->
                <div class="text-xs text-gray-500 pt-4">
                    © {{ new Date().getFullYear() }} MyGrowNet. All rights reserved.
                </div>
            </div>
        </div>
    </footer>
</template>
