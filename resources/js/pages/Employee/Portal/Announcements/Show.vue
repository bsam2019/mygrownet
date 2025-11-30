<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/Layouts/EmployeePortalLayout.vue';
import { ArrowLeftIcon, CalendarIcon, UserIcon, BuildingOfficeIcon } from '@heroicons/vue/24/outline';

interface Announcement {
    id: number;
    title: string;
    content: string;
    type: string;
    priority: string;
    publish_date: string;
    is_pinned: boolean;
    department: { name: string } | null;
    author: { full_name: string } | null;
}

const props = defineProps<{ announcement: Announcement }>();

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-ZM', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const priorityColors: Record<string, string> = {
    low: 'bg-gray-100 text-gray-700',
    normal: 'bg-blue-100 text-blue-700',
    high: 'bg-red-100 text-red-700',
};
</script>

<template>
    <Head :title="announcement.title" />
    <EmployeePortalLayout>
        <template #header>Announcement</template>
        <div class="space-y-6">
            <!-- Back Link -->
            <Link :href="route('employee.portal.announcements.index')"
                class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900">
                <ArrowLeftIcon class="h-5 w-5" />
                Back to Announcements
            </Link>

            <!-- Announcement Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-6 text-white">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-xs px-2 py-1 bg-white/20 rounded-full capitalize">
                            {{ announcement.type }}
                        </span>
                        <span :class="[priorityColors[announcement.priority], 'text-xs px-2 py-1 rounded-full capitalize']">
                            {{ announcement.priority }} priority
                        </span>
                    </div>
                    <h1 class="text-2xl font-bold">{{ announcement.title }}</h1>
                </div>

                <!-- Meta Info -->
                <div class="px-6 py-4 bg-gray-50 border-b flex flex-wrap gap-6 text-sm text-gray-600">
                    <div class="flex items-center gap-2">
                        <CalendarIcon class="h-5 w-5 text-gray-400" />
                        {{ formatDate(announcement.publish_date) }}
                    </div>
                    <div v-if="announcement.author" class="flex items-center gap-2">
                        <UserIcon class="h-5 w-5 text-gray-400" />
                        {{ announcement.author.full_name }}
                    </div>
                    <div v-if="announcement.department" class="flex items-center gap-2">
                        <BuildingOfficeIcon class="h-5 w-5 text-gray-400" />
                        {{ announcement.department.name }}
                    </div>
                </div>

                <!-- Content -->
                <div class="p-6">
                    <div class="prose max-w-none" v-html="announcement.content.replace(/\n/g, '<br>')"></div>
                </div>
            </div>
        </div>
    </EmployeePortalLayout>
</template>
