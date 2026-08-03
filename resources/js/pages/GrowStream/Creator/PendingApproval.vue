<template>
    <AppLayout title="Creator Application - GrowStream">
        <div class="mx-auto max-w-2xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="rounded-lg bg-white p-10 text-center shadow">
                <template v-if="status === 'approved'">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900">You're a creator!</h1>
                    <p class="mt-2 text-gray-600">Your application has been approved. Welcome to the GrowStream creator community.</p>
                    <Link
                        :href="route('growstream.creator.dashboard')"
                        class="mt-6 inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                    >
                        Go to Creator Dashboard
                    </Link>
                </template>

                <template v-else-if="status === 'rejected'">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-red-100">
                        <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900">Application not approved</h1>
                    <p class="mt-2 text-gray-600">
                        Your creator application was not approved at this time.
                    </p>
                    <p v-if="rejectedReason" class="mt-4 rounded-md bg-gray-50 p-4 text-left text-sm text-gray-700">
                        <span class="font-semibold">Reason:</span> {{ rejectedReason }}
                    </p>
                    <Link
                        :href="route('growstream.creator.register')"
                        class="mt-6 inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                    >
                        Apply Again
                    </Link>
                </template>

                <template v-else>
                    <div class="mx-auto mb-4 flex h-16 w-16 animate-pulse items-center justify-center rounded-full bg-blue-100">
                        <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900">Application Under Review</h1>
                    <p class="mt-2 text-gray-600">
                        Thank you for applying! Our team is reviewing your application. This usually
                        takes 1-2 business days. You'll be able to upload content once approved.
                    </p>
                </template>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

interface Props {
    status: string;
    rejectedReason?: string | null;
    profile?: Record<string, any> | null;
}

defineProps<Props>();
</script>
