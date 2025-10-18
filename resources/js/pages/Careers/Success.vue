<template>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center">
        <div class="max-w-md w-full bg-white rounded-lg shadow-sm border p-8 text-center">
            <div class="mb-6">
                <CheckCircleIcon class="w-16 h-16 text-green-500 mx-auto mb-4" />
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Application Submitted!</h1>
                <p class="text-gray-600">
                    Thank you for your interest in joining our team. We've received your application and will review it shortly.
                </p>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h3 class="text-sm font-semibold text-blue-900 mb-2">What happens next?</h3>
                <ul class="text-sm text-blue-800 text-left space-y-1">
                    <li>• Our HR team will review your application</li>
                    <li>• If you're a good fit, we'll contact you within 5-7 business days</li>
                    <li>• We may schedule a phone or video interview</li>
                </ul>
            </div>

            <div class="space-y-3">
                <Link
                    :href="route('careers.index')"
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors inline-block"
                >
                    View Other Positions
                </Link>
                <Link
                    :href="route('home')"
                    class="w-full bg-gray-100 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-200 transition-colors inline-block"
                >
                    Back to Home
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { CheckCircleIcon } from '@heroicons/vue/24/outline';
</script>
