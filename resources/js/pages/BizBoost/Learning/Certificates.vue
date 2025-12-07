<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import {
    TrophyIcon,
    ArrowDownTrayIcon,
    ArrowLeftIcon,
    AcademicCapIcon,
} from '@heroicons/vue/24/outline';

interface Certificate {
    id: number;
    course_title: string;
    certificate_number: string;
    recipient_name: string;
    issued_at: string;
    thumbnail: string | null;
    pdf_path: string | null;
}

interface Props {
    certificates: Certificate[];
}

defineProps<Props>();

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="My Certificates - BizBoost" />
    <BizBoostLayout title="My Certificates">
        <div class="space-y-6">
            <!-- Back Link -->
            <Link
                href="/bizboost/learning"
                class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-violet-600"
            >
                <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                Back to Learning Hub
            </Link>

            <!-- Header -->
            <div class="flex items-center gap-3">
                <div class="rounded-lg bg-amber-100 p-2">
                    <TrophyIcon class="h-6 w-6 text-amber-600" aria-hidden="true" />
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">My Certificates</h1>
                    <p class="text-sm text-gray-500">{{ certificates.length }} certificate{{ certificates.length !== 1 ? 's' : '' }} earned</p>
                </div>
            </div>

            <!-- Certificates Grid -->
            <div v-if="certificates.length" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="cert in certificates"
                    :key="cert.id"
                    class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 overflow-hidden"
                >
                    <!-- Certificate Preview -->
                    <div class="aspect-[4/3] bg-gradient-to-br from-amber-50 to-amber-100 flex items-center justify-center p-6">
                        <div class="text-center">
                            <TrophyIcon class="h-12 w-12 text-amber-500 mx-auto mb-2" aria-hidden="true" />
                            <p class="text-xs text-amber-600 font-medium uppercase tracking-wide">Certificate of Completion</p>
                            <p class="text-lg font-bold text-gray-900 mt-2">{{ cert.course_title }}</p>
                            <p class="text-sm text-gray-600 mt-1">{{ cert.recipient_name }}</p>
                        </div>
                    </div>

                    <div class="p-4">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs text-gray-500">{{ cert.certificate_number }}</span>
                            <span class="text-xs text-gray-500">{{ formatDate(cert.issued_at) }}</span>
                        </div>
                        <a
                            v-if="cert.pdf_path"
                            :href="`/bizboost/learning/certificates/${cert.id}/download`"
                            class="flex items-center justify-center gap-2 w-full px-4 py-2 bg-violet-600 text-white rounded-lg text-sm font-medium hover:bg-violet-700 transition-colors"
                        >
                            <ArrowDownTrayIcon class="h-4 w-4" aria-hidden="true" />
                            Download PDF
                        </a>
                        <p v-else class="text-center text-sm text-gray-500 py-2">
                            PDF generating...
                        </p>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12 bg-white rounded-xl ring-1 ring-gray-200">
                <AcademicCapIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                <h3 class="mt-4 text-lg font-medium text-gray-900">No certificates yet</h3>
                <p class="mt-2 text-sm text-gray-500">Complete courses to earn certificates.</p>
                <Link
                    href="/bizboost/learning"
                    class="mt-4 inline-flex items-center gap-2 rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-700"
                >
                    Browse Courses
                </Link>
            </div>
        </div>
    </BizBoostLayout>
</template>
