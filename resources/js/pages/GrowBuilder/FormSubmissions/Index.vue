<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ArrowLeftIcon, EyeIcon, TrashIcon, EnvelopeIcon, EnvelopeOpenIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline';

interface Form {
    id: number;
    name: string;
}

interface Submission {
    id: number;
    form_id: number;
    data: Record<string, any>;
    ip_address: string;
    is_read: boolean;
    is_spam: boolean;
    created_at: string;
    form?: Form;
}

interface Site {
    id: number;
    name: string;
    subdomain: string;
}

const props = defineProps<{
    site: Site;
    submissions: {
        data: Submission[];
        links: any[];
        current_page: number;
        last_page: number;
    };
    forms: Form[];
    unreadCount: number;
    filters: {
        form_id?: string;
        status?: string;
    };
}>();

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('en-ZM', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getPreviewData = (data: Record<string, any>): string => {
    const entries = Object.entries(data).slice(0, 2);
    return entries.map(([key, value]) => `${key}: ${value}`).join(' • ');
};

const filterByStatus = (status: string | null) => {
    router.get(route('growbuilder.form-submissions.index', props.site.id), {
        ...props.filters,
        status: status || undefined,
    }, { preserveState: true });
};

const filterByForm = (formId: string | null) => {
    router.get(route('growbuilder.form-submissions.index', props.site.id), {
        ...props.filters,
        form_id: formId || undefined,
    }, { preserveState: true });
};

const toggleRead = (submission: Submission) => {
    router.post(route('growbuilder.form-submissions.toggle-read', {
        siteId: props.site.id,
        submissionId: submission.id,
    }), {}, { preserveScroll: true });
};

const deleteSubmission = (submission: Submission) => {
    if (confirm('Are you sure you want to delete this submission?')) {
        router.delete(route('growbuilder.form-submissions.destroy', {
            siteId: props.site.id,
            submissionId: submission.id,
        }));
    }
};
</script>

<template>
    <AppLayout>
        <Head :title="`Form Submissions - ${site.name}`" />

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        :href="route('growbuilder.index')"
                        class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-4"
                    >
                        <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                        Back to Sites
                    </Link>

                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Form Submissions</h1>
                            <p class="text-sm text-gray-500">{{ site.name }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span v-if="unreadCount > 0" class="px-2.5 py-1 text-sm font-medium bg-blue-100 text-blue-800 rounded-full">
                                {{ unreadCount }} unread
                            </span>
                            <a
                                :href="route('growbuilder.form-submissions.export', site.id)"
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                            >
                                <ArrowDownTrayIcon class="h-4 w-4" aria-hidden="true" />
                                Export CSV
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="flex flex-wrap gap-4 mb-6">
                    <!-- Status Filter -->
                    <div class="flex gap-2">
                        <button
                            type="button"
                            :class="[
                                'px-3 py-1.5 text-sm font-medium rounded-full',
                                !filters.status ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                            ]"
                            @click="filterByStatus(null)"
                        >
                            All
                        </button>
                        <button
                            type="button"
                            :class="[
                                'px-3 py-1.5 text-sm font-medium rounded-full',
                                filters.status === 'unread' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                            ]"
                            @click="filterByStatus('unread')"
                        >
                            Unread
                        </button>
                        <button
                            type="button"
                            :class="[
                                'px-3 py-1.5 text-sm font-medium rounded-full',
                                filters.status === 'read' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                            ]"
                            @click="filterByStatus('read')"
                        >
                            Read
                        </button>
                    </div>

                    <!-- Form Filter -->
                    <select
                        v-if="forms.length > 1"
                        :value="filters.form_id || ''"
                        class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                        @change="filterByForm(($event.target as HTMLSelectElement).value || null)"
                    >
                        <option value="">All Forms</option>
                        <option v-for="form in forms" :key="form.id" :value="form.id">
                            {{ form.name }}
                        </option>
                    </select>
                </div>

                <!-- Submissions List -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div v-if="submissions.data.length === 0" class="text-center py-12">
                        <EnvelopeIcon class="h-12 w-12 mx-auto text-gray-400 mb-4" aria-hidden="true" />
                        <p class="text-gray-500">No form submissions yet</p>
                    </div>

                    <div v-else class="divide-y divide-gray-200">
                        <div
                            v-for="submission in submissions.data"
                            :key="submission.id"
                            :class="[
                                'p-4 hover:bg-gray-50 transition-colors',
                                !submission.is_read ? 'bg-blue-50/50' : ''
                            ]"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <component
                                            :is="submission.is_read ? EnvelopeOpenIcon : EnvelopeIcon"
                                            :class="[
                                                'h-4 w-4',
                                                submission.is_read ? 'text-gray-400' : 'text-blue-600'
                                            ]"
                                            aria-hidden="true"
                                        />
                                        <span v-if="submission.form" class="text-sm font-medium text-gray-900">
                                            {{ submission.form.name }}
                                        </span>
                                        <span class="text-sm text-gray-500">
                                            {{ formatDate(submission.created_at) }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 truncate">
                                        {{ getPreviewData(submission.data) }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        :aria-label="submission.is_read ? 'Mark as unread' : 'Mark as read'"
                                        class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg"
                                        @click="toggleRead(submission)"
                                    >
                                        <component
                                            :is="submission.is_read ? EnvelopeIcon : EnvelopeOpenIcon"
                                            class="h-4 w-4"
                                            aria-hidden="true"
                                        />
                                    </button>
                                    <Link
                                        :href="route('growbuilder.form-submissions.show', { siteId: site.id, submissionId: submission.id })"
                                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg"
                                        aria-label="View submission"
                                    >
                                        <EyeIcon class="h-4 w-4" aria-hidden="true" />
                                    </Link>
                                    <button
                                        type="button"
                                        aria-label="Delete submission"
                                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg"
                                        @click="deleteSubmission(submission)"
                                    >
                                        <TrashIcon class="h-4 w-4" aria-hidden="true" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="submissions.last_page > 1" class="px-4 py-3 border-t border-gray-200 flex justify-center gap-1">
                        <Link
                            v-for="link in submissions.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-1 text-sm rounded',
                                link.active ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100',
                                !link.url ? 'opacity-50 cursor-not-allowed' : ''
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
