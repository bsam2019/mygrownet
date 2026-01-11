<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ArrowLeftIcon, TrashIcon, EnvelopeIcon, EnvelopeOpenIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';

interface Form {
    id: number;
    name: string;
}

interface Submission {
    id: number;
    form_id: number;
    data: Record<string, any>;
    ip_address: string;
    user_agent: string;
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
    submission: Submission;
}>();

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('en-ZM', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatFieldName = (key: string): string => {
    return key
        .replace(/_/g, ' ')
        .replace(/([A-Z])/g, ' $1')
        .replace(/^./, str => str.toUpperCase())
        .trim();
};

const toggleRead = () => {
    router.post(route('growbuilder.form-submissions.toggle-read', {
        siteId: props.site.id,
        submissionId: props.submission.id,
    }), {}, { preserveScroll: true });
};

const markSpam = () => {
    if (confirm('Mark this submission as spam?')) {
        router.post(route('growbuilder.form-submissions.spam', {
            siteId: props.site.id,
            submissionId: props.submission.id,
        }));
    }
};

const deleteSubmission = () => {
    if (confirm('Are you sure you want to delete this submission?')) {
        router.delete(route('growbuilder.form-submissions.destroy', {
            siteId: props.site.id,
            submissionId: props.submission.id,
        }));
    }
};
</script>

<template>
    <AppLayout>
        <Head :title="`Submission - ${site.name}`" />

        <div class="py-6">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        :href="route('growbuilder.form-submissions.index', site.id)"
                        class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-4"
                    >
                        <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                        Back to Submissions
                    </Link>

                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Form Submission</h1>
                            <p class="text-sm text-gray-500">
                                {{ submission.form?.name || 'Unknown Form' }} • {{ formatDate(submission.created_at) }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                :aria-label="submission.is_read ? 'Mark as unread' : 'Mark as read'"
                                class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg"
                                @click="toggleRead"
                            >
                                <component
                                    :is="submission.is_read ? EnvelopeIcon : EnvelopeOpenIcon"
                                    class="h-5 w-5"
                                    aria-hidden="true"
                                />
                            </button>
                            <button
                                type="button"
                                aria-label="Mark as spam"
                                class="p-2 text-orange-600 hover:bg-orange-50 rounded-lg"
                                @click="markSpam"
                            >
                                <ExclamationTriangleIcon class="h-5 w-5" aria-hidden="true" />
                            </button>
                            <button
                                type="button"
                                aria-label="Delete submission"
                                class="p-2 text-red-600 hover:bg-red-50 rounded-lg"
                                @click="deleteSubmission"
                            >
                                <TrashIcon class="h-5 w-5" aria-hidden="true" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Spam Warning -->
                <div v-if="submission.is_spam" class="mb-6 p-4 bg-orange-50 border border-orange-200 rounded-xl">
                    <div class="flex items-center gap-2 text-orange-800">
                        <ExclamationTriangleIcon class="h-5 w-5" aria-hidden="true" />
                        <span class="font-medium">This submission has been marked as spam</span>
                    </div>
                </div>

                <!-- Submission Data -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Submitted Data</h2>
                        <dl class="space-y-4">
                            <div
                                v-for="(value, key) in submission.data"
                                :key="key"
                                class="border-b border-gray-100 pb-4 last:border-0 last:pb-0"
                            >
                                <dt class="text-sm font-medium text-gray-500 mb-1">
                                    {{ formatFieldName(String(key)) }}
                                </dt>
                                <dd class="text-gray-900">
                                    <template v-if="typeof value === 'string' && value.includes('\n')">
                                        <p class="whitespace-pre-wrap">{{ value }}</p>
                                    </template>
                                    <template v-else-if="typeof value === 'boolean'">
                                        {{ value ? 'Yes' : 'No' }}
                                    </template>
                                    <template v-else-if="Array.isArray(value)">
                                        {{ value.join(', ') }}
                                    </template>
                                    <template v-else>
                                        {{ value }}
                                    </template>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Metadata -->
                <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Submission Details</h2>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Submitted At</dt>
                                <dd class="text-gray-900">{{ formatDate(submission.created_at) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">IP Address</dt>
                                <dd class="text-gray-900">{{ submission.ip_address || 'Unknown' }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">User Agent</dt>
                                <dd class="text-gray-900 text-sm break-all">{{ submission.user_agent || 'Unknown' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
