<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline';

interface Props {
    message: string;
}

defineProps<Props>();
</script>

<template>
    <Head title="Invalid Invitation" />
    
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden text-center p-8">
            <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <ExclamationTriangleIcon class="w-8 h-8 text-amber-600" aria-hidden="true" />
            </div>
            
            <h1 class="text-xl font-bold text-gray-900 mb-2">Invalid Invitation</h1>
            <p class="text-gray-600 mb-6">{{ message }}</p>
            
            <div class="space-y-3">
                <Link
                    :href="route('growbiz.invitation.code')"
                    class="block w-full py-3 px-4 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700"
                >
                    Enter Invitation Code
                </Link>
                <Link
                    href="/"
                    class="block w-full py-3 px-4 border border-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-50"
                >
                    Go to Homepage
                </Link>
            </div>
        </div>
    </div>
</template>
