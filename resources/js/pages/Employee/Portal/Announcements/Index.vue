<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/Layouts/EmployeePortalLayout.vue';
import { 
    MegaphoneIcon, 
    CheckCircleIcon,
    ExclamationTriangleIcon,
    InformationCircleIcon,
    CalendarIcon,
    BuildingOfficeIcon
} from '@heroicons/vue/24/outline';
import { StarIcon } from '@heroicons/vue/24/solid';

interface Announcement {
    id: number;
    title: string;
    content: string;
    type: 'general' | 'policy' | 'event' | 'urgent' | 'hr';
    priority: 'low' | 'normal' | 'high';
    publish_date: string;
    is_pinned: boolean;
    is_read: boolean;
    department: { name: string } | null;
    author: { full_name: string } | null;
}

interface Props {
    announcements: {
        data: Announcement[];
        links: any;
    };
    unreadCount: number;
    filters: { type?: string };
}

const props = defineProps<Props>();

const typeConfig: Record<string, { icon: any; color: string; label: string }> = {
    general: { icon: InformationCircleIcon, color: 'blue', label: 'General' },
    policy: { icon: MegaphoneIcon, color: 'purple', label: 'Policy Update' },
    event: { icon: CalendarIcon, color: 'emerald', label: 'Event' },
    urgent: { icon: ExclamationTriangleIcon, color: 'red', label: 'Urgent' },
    hr: { icon: BuildingOfficeIcon, color: 'amber', label: 'HR' },
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-ZM', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const markAsRead = (id: number) => {
    router.post(route('employee.portal.announcements.mark-read', id), {}, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Announcements" />
    <EmployeePortalLayout>
        <template #header>Announcements</template>
        <div class="space-y-6">
            <!-- Header Stats -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold">Company Announcements</h2>
                        <p class="text-blue-100 mt-1">Stay updated with the latest news</p>
                    </div>
                    <div v-if="unreadCount > 0" class="bg-white/20 rounded-lg px-4 py-2">
                        <span class="text-2xl font-bold">{{ unreadCount }}</span>
                        <span class="text-blue-100 text-sm ml-1">unread</span>
                    </div>
                </div>
            </div>

            <!-- Announcements List -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div v-if="announcements.data.length === 0" class="p-12 text-center">
                    <MegaphoneIcon class="h-12 w-12 mx-auto text-gray-300 mb-4" />
                    <p class="text-gray-500">No announcements at this time</p>
                </div>

                <div v-else class="divide-y divide-gray-100">
                    <Link v-for="announcement in announcements.data" :key="announcement.id"
                        :href="route('employee.portal.announcements.show', announcement.id)"
                        class="block p-4 hover:bg-gray-50 transition-colors"
                        :class="{ 'bg-blue-50/50': !announcement.is_read }">
                        <div class="flex items-start gap-4">
                            <!-- Icon -->
                            <div :class="[
                                'p-3 rounded-lg flex-shrink-0',
                                `bg-${typeConfig[announcement.type]?.color || 'gray'}-100`
                            ]">
                                <component :is="typeConfig[announcement.type]?.icon || InformationCircleIcon"
                                    :class="[
                                        'h-6 w-6',
                                        `text-${typeConfig[announcement.type]?.color || 'gray'}-600`
                                    ]" />
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <StarIcon v-if="announcement.is_pinned" class="h-4 w-4 text-amber-500" />
                                    <span :class="[
                                        'text-xs px-2 py-0.5 rounded-full',
                                        `bg-${typeConfig[announcement.type]?.color || 'gray'}-100`,
                                        `text-${typeConfig[announcement.type]?.color || 'gray'}-700`
                                    ]">
                                        {{ typeConfig[announcement.type]?.label || announcement.type }}
                                    </span>
                                    <span v-if="!announcement.is_read" 
                                        class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">
                                        New
                                    </span>
                                </div>
                                <h3 class="font-semibold text-gray-900 truncate">{{ announcement.title }}</h3>
                                <p class="text-sm text-gray-500 line-clamp-2 mt-1">
                                    {{ announcement.content.substring(0, 150) }}...
                                </p>
                                <div class="flex items-center gap-4 mt-2 text-xs text-gray-400">
                                    <span>{{ formatDate(announcement.publish_date) }}</span>
                                    <span v-if="announcement.author">by {{ announcement.author.full_name }}</span>
                                    <span v-if="announcement.department">• {{ announcement.department.name }}</span>
                                </div>
                            </div>

                            <!-- Read indicator -->
                            <div v-if="announcement.is_read" class="flex-shrink-0">
                                <CheckCircleIcon class="h-5 w-5 text-emerald-500" />
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </EmployeePortalLayout>
</template>
