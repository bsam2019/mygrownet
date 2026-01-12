<template>
    <div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Browser Requirements</h1>
                <p class="text-gray-600">Ensure the best experience with MyGrowNet</p>
            </div>

            <!-- Recommended Browsers -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <CheckCircleIcon class="h-6 w-6 text-green-500" />
                    Recommended Browsers
                </h2>
                <div class="grid md:grid-cols-3 gap-4">
                    <div class="border border-gray-200 rounded-lg p-4 text-center">
                        <div class="text-4xl mb-2">🌐</div>
                        <h3 class="font-semibold mb-1">Google Chrome</h3>
                        <p class="text-sm text-gray-600">Version 90+</p>
                        <a href="https://www.google.com/chrome/" target="_blank" class="text-blue-600 text-sm hover:underline">Download</a>
                    </div>
                    <div class="border border-gray-200 rounded-lg p-4 text-center">
                        <div class="text-4xl mb-2">🦊</div>
                        <h3 class="font-semibold mb-1">Mozilla Firefox</h3>
                        <p class="text-sm text-gray-600">Version 88+</p>
                        <a href="https://www.mozilla.org/firefox/" target="_blank" class="text-blue-600 text-sm hover:underline">Download</a>
                    </div>
                    <div class="border border-gray-200 rounded-lg p-4 text-center">
                        <div class="text-4xl mb-2">🔴</div>
                        <h3 class="font-semibold mb-1">Opera</h3>
                        <p class="text-sm text-gray-600">Version 76+</p>
                        <a href="https://www.opera.com/" target="_blank" class="text-blue-600 text-sm hover:underline">Download</a>
                    </div>
                </div>
            </div>

            <!-- Minimum Requirements -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Minimum Requirements</h2>
                <ul class="space-y-3">
                    <li class="flex items-start gap-3">
                        <CheckCircleIcon class="h-5 w-5 text-green-500 mt-0.5 flex-shrink-0" />
                        <div>
                            <strong>Operating System:</strong> Android 8.0+ or iOS 12+
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <CheckCircleIcon class="h-5 w-5 text-green-500 mt-0.5 flex-shrink-0" />
                        <div>
                            <strong>RAM:</strong> 2GB minimum (4GB recommended)
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <CheckCircleIcon class="h-5 w-5 text-green-500 mt-0.5 flex-shrink-0" />
                        <div>
                            <strong>Internet:</strong> Stable connection (3G minimum, 4G/WiFi recommended)
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <CheckCircleIcon class="h-5 w-5 text-green-500 mt-0.5 flex-shrink-0" />
                        <div>
                            <strong>Browser:</strong> Latest version with JavaScript enabled
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Troubleshooting -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <ExclamationTriangleIcon class="h-6 w-6 text-amber-500" />
                    App Not Loading?
                </h2>
                <div class="space-y-4">
                    <div>
                        <h3 class="font-semibold mb-2">Try These Steps:</h3>
                        <ol class="list-decimal list-inside space-y-2 text-gray-700">
                            <li>Clear your browser cache and cookies</li>
                            <li>Update your browser to the latest version</li>
                            <li>Try a different recommended browser</li>
                            <li>Restart your device</li>
                            <li>Check your internet connection</li>
                        </ol>
                    </div>

                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <h4 class="font-semibold text-amber-900 mb-2">⚠️ Limited Browser Support</h4>
                        <p class="text-sm text-amber-800">
                            The Google app's built-in browser has limited functionality. For full features (especially file uploads), 
                            please use Chrome, Firefox, or Opera.
                        </p>
                    </div>
                </div>
            </div>

            <!-- How to Clear Cache -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">How to Clear Browser Cache</h2>
                
                <div class="space-y-4">
                    <!-- Chrome -->
                    <div>
                        <h3 class="font-semibold mb-2">Chrome (Android)</h3>
                        <ol class="list-decimal list-inside space-y-1 text-sm text-gray-700">
                            <li>Open Chrome → Menu (⋮) → Settings</li>
                            <li>Privacy and security → Clear browsing data</li>
                            <li>Select "Cached images and files"</li>
                            <li>Tap "Clear data"</li>
                        </ol>
                    </div>

                    <!-- Firefox -->
                    <div>
                        <h3 class="font-semibold mb-2">Firefox (Android)</h3>
                        <ol class="list-decimal list-inside space-y-1 text-sm text-gray-700">
                            <li>Open Firefox → Menu (⋮) → Settings</li>
                            <li>Delete browsing data</li>
                            <li>Select "Cache" and tap "Delete browsing data"</li>
                        </ol>
                    </div>

                    <!-- Safari -->
                    <div>
                        <h3 class="font-semibold mb-2">Safari (iOS)</h3>
                        <ol class="list-decimal list-inside space-y-1 text-sm text-gray-700">
                            <li>Settings → Safari</li>
                            <li>Clear History and Website Data</li>
                            <li>Confirm</li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Back Button -->
            <div class="mt-8 text-center">
                <Link :href="route('dashboard')" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700">
                    <ArrowLeftIcon class="h-4 w-4" />
                    Back to Dashboard
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { CheckCircleIcon, ExclamationTriangleIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';
</script>
