<script setup lang="ts">
// Footer component for authenticated GrowBackup pages
</script>

<template>
    <footer class="mt-auto pt-12 pb-6 border-t border-gray-200 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <!-- About -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">GrowBackup</h3>
                    <p class="text-sm text-gray-600">
                        Secure cloud storage for your important files. Part of the MyGrowNet ecosystem.
                    </p>
                </div>

                <!-- Product -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Product</h3>
                    <ul class="space-y-2 text-sm">
                        <li>
                            <a href="/growbackup/dashboard" class="text-gray-600 hover:text-blue-600 transition-colors">
                                My Files
                            </a>
                        </li>
                        <li>
                            <a href="/growbackup/subscription" class="text-gray-600 hover:text-blue-600 transition-colors">
                                Plans & Pricing
                            </a>
                        </li>
                        <li>
                            <a href="/dashboard" class="text-gray-600 hover:text-blue-600 transition-colors">
                                MyGrowNet Dashboard
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Support -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Support</h3>
                    <ul class="space-y-2 text-sm">
                        <li>
                            <a href="/faq" class="text-gray-600 hover:text-blue-600 transition-colors">
                                FAQ
                            </a>
                        </li>
                        <li>
                            <a href="/contact" class="text-gray-600 hover:text-blue-600 transition-colors">
                                Contact Us
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Legal -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Legal</h3>
                    <ul class="space-y-2 text-sm">
                        <li>
                            <a href="/privacy" class="text-gray-600 hover:text-blue-600 transition-colors">
                                Privacy Policy
                            </a>
                        </li>
                        <li>
                            <a href="/terms" class="text-gray-600 hover:text-blue-600 transition-colors">
                                Terms of Service
                            </a>
                        </li>
                        <li>
                            <a href="/policies" class="text-gray-600 hover:text-blue-600 transition-colors">
                                Policies
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="pt-6 border-t border-gray-200">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <p class="text-sm text-gray-500">
                        © {{ new Date().getFullYear() }} MyGrowNet. All rights reserved.
                    </p>
                    <div class="flex items-center gap-2 text-sm text-gray-500">
                        <span>Powered by</span>
                        <a href="/" class="text-blue-600 hover:text-blue-700 font-medium">
                            MyGrowNet
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</template>
