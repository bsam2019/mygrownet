<template>
    <UbumiLayout title="Family Wellness Dashboard">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="mb-6">
                <Link
                    :href="route('ubumi.families.show', { family: family.slug })"
                    class="inline-flex items-center text-sm text-purple-600 hover:text-purple-700 mb-4"
                >
                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Family Tree
                </Link>
                
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Family Wellness Dashboard</h1>
                        <p class="mt-1 text-sm text-gray-600">{{ family.name }} - Check-in overview</p>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <div class="flex items-center gap-3">
                        <div class="h-12 w-12 rounded-full bg-purple-100 flex items-center justify-center">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Total Members</p>
                            <p class="text-2xl font-bold text-gray-900">{{ summary.total_members }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <div class="flex items-center gap-3">
                        <div class="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center">
                            <span class="text-2xl">😊</span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Feeling Well</p>
                            <p class="text-2xl font-bold text-green-600">{{ summary.well_count }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <div class="flex items-center gap-3">
                        <div class="h-12 w-12 rounded-full bg-amber-100 flex items-center justify-center">
                            <span class="text-2xl">😐</span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Not Well</p>
                            <p class="text-2xl font-bold text-amber-600">{{ summary.unwell_count }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <div class="flex items-center gap-3">
                        <div class="h-12 w-12 rounded-full bg-red-100 flex items-center justify-center">
                            <span class="text-2xl">🆘</span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Need Help</p>
                            <p class="text-2xl font-bold text-red-600">{{ summary.need_assistance_count }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Alerts -->
            <div v-if="alerts.length > 0" class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Active Alerts</h2>
                <div class="space-y-3">
                    <div
                        v-for="alert in alerts"
                        :key="alert.id"
                        class="bg-red-50 border border-red-200 rounded-lg p-4"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex items-start gap-3">
                                <span class="text-2xl">🚨</span>
                                <div>
                                    <p class="font-semibold text-red-900">{{ alert.message }}</p>
                                    <p class="text-sm text-red-700 mt-1">{{ formatDateTime(alert.created_at) }}</p>
                                </div>
                            </div>
                            <button
                                @click="acknowledgeAlert(alert.id)"
                                class="px-3 py-1 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700"
                            >
                                Acknowledge
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Members List -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Family Members</h2>
                </div>
                <div class="divide-y divide-gray-200">
                    <div
                        v-for="member in members"
                        :key="member.person_id"
                        class="px-6 py-4 hover:bg-gray-50 transition-colors"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <!-- Photo -->
                                <div class="flex-shrink-0">
                                    <div
                                        v-if="member.photo_url"
                                        class="h-12 w-12 rounded-full overflow-hidden"
                                    >
                                        <img
                                            :src="member.photo_url"
                                            :alt="member.name"
                                            class="h-full w-full object-cover"
                                        />
                                    </div>
                                    <div
                                        v-else
                                        class="h-12 w-12 rounded-full bg-gradient-to-br from-purple-400 to-indigo-500 flex items-center justify-center"
                                    >
                                        <span class="text-white font-bold text-lg">
                                            {{ member.name.charAt(0) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Info -->
                                <div>
                                    <Link
                                        :href="route('ubumi.families.persons.show', { family: family.slug, person: member.person_slug })"
                                        class="font-semibold text-gray-900 hover:text-purple-600"
                                    >
                                        {{ member.name }}
                                    </Link>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span v-if="member.age" class="text-sm text-gray-500">
                                            {{ member.age }} years old
                                        </span>
                                        <span
                                            v-if="member.is_deceased"
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-200 text-gray-700"
                                        >
                                            Deceased
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Latest Check-in Status -->
                            <div v-if="member.latest_checkin && !member.is_deceased" class="flex items-center gap-3">
                                <div class="text-right">
                                    <div class="flex items-center gap-2">
                                        <span class="text-2xl">{{ member.latest_checkin.status_emoji }}</span>
                                        <span class="text-sm font-medium text-gray-900">
                                            {{ member.latest_checkin.status_label }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ formatRelativeTime(member.latest_checkin.checked_in_at) }}
                                    </p>
                                </div>
                            </div>
                            <div v-else-if="!member.is_deceased" class="text-sm text-gray-500">
                                No check-ins yet
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </UbumiLayout>
</template>

<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import UbumiLayout from '@/layouts/UbumiLayout.vue';

interface Family {
    id: string;
    slug: string;
    name: string;
}

interface Summary {
    total_members: number;
    well_count: number;
    unwell_count: number;
    need_assistance_count: number;
}

interface Alert {
    id: string;
    message: string;
    created_at: string;
}

interface CheckIn {
    status_emoji: string;
    status_label: string;
    checked_in_at: string;
}

interface Member {
    person_id: string;
    person_slug: string;
    name: string;
    photo_url: string | null;
    age: number | null;
    is_deceased: boolean;
    latest_checkin: CheckIn | null;
}

interface Props {
    family: Family;
    summary: Summary;
    alerts: Alert[];
    members: Member[];
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

const formatRelativeTime = (dateString: string): string => {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
    const diffDays = Math.floor(diffHours / 24);

    if (diffHours < 1) return 'Just now';
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays < 7) return `${diffDays}d ago`;
    return formatDateTime(dateString);
};

const acknowledgeAlert = (alertId: string) => {
    router.post(
        route('ubumi.alerts.acknowledge', { alert: alertId }),
        {},
        {
            preserveScroll: true,
        }
    );
};
</script>
