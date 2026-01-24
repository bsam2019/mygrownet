<template>
    <UbumiLayout :title="`Check-In History - ${person.name}`">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-6">
                <Link
                    :href="route('ubumi.families.persons.show', { family: familySlug, person: person.slug })"
                    class="inline-flex items-center text-sm text-purple-600 hover:text-purple-700 mb-4"
                >
                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to {{ person.name }}
                </Link>
                
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Check-In History</h1>
                        <p class="mt-1 text-sm text-gray-600">Wellness check-ins for {{ person.name }}</p>
                    </div>
                </div>
            </div>

            <!-- Check-Ins Timeline -->
            <div v-if="checkIns.length > 0" class="space-y-4">
                <div
                    v-for="checkIn in checkIns"
                    :key="checkIn.id"
                    class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
                >
                    <div class="flex items-start gap-4">
                        <!-- Status Emoji -->
                        <div class="flex-shrink-0">
                            <span class="text-4xl">{{ checkIn.status_emoji }}</span>
                        </div>

                        <!-- Content -->
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ checkIn.status_label }}
                                </h3>
                                <span
                                    v-if="checkIn.is_recent"
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800"
                                >
                                    Recent
                                </span>
                            </div>

                            <!-- Note -->
                            <p v-if="checkIn.note" class="text-gray-700 mb-3">
                                {{ checkIn.note }}
                            </p>

                            <!-- Location -->
                            <div v-if="checkIn.location" class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{ checkIn.location }}
                            </div>

                            <!-- Timestamp -->
                            <p class="text-sm text-gray-500">
                                {{ formatDateTime(checkIn.checked_in_at) }}
                            </p>
                        </div>

                        <!-- Status Badge -->
                        <div class="flex-shrink-0">
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                :class="{
                                    'bg-green-100 text-green-800': checkIn.status_color === 'green',
                                    'bg-amber-100 text-amber-800': checkIn.status_color === 'amber',
                                    'bg-red-100 text-red-800': checkIn.status_color === 'red',
                                }"
                            >
                                {{ checkIn.status_label }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">No check-ins yet</h3>
                <p class="mt-2 text-sm text-gray-500">
                    Check-ins will appear here once {{ person.name }} starts recording wellness status.
                </p>
                <div class="mt-6">
                    <Link
                        :href="route('ubumi.families.persons.show', { family: familySlug, person: person.slug })"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-500 to-indigo-600 text-white text-sm font-medium rounded-lg hover:from-purple-600 hover:to-indigo-700"
                    >
                        Go to Profile
                    </Link>
                </div>
            </div>
        </div>
    </UbumiLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import UbumiLayout from '@/layouts/UbumiLayout.vue';

interface Person {
    id: string;
    slug: string;
    name: string;
    photo_url: string | null;
}

interface CheckIn {
    id: string;
    status: string;
    status_label: string;
    status_emoji: string;
    status_color: string;
    note: string | null;
    location: string | null;
    photo_url: string | null;
    checked_in_at: string;
    is_recent: boolean;
}

interface Props {
    person: Person;
    checkIns: CheckIn[];
    familySlug: string;
}

defineProps<Props>();

const formatDateTime = (dateString: string): string => {
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
};
</script>
