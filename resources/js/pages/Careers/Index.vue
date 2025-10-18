<template>
    <div class="min-h-screen bg-gray-50">
        <Navigation />
        <!-- Header -->
        <div class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="text-center">
                    <h1 class="text-4xl font-bold text-gray-900 mb-4">Join Our Team</h1>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                        Build your career with VBIF. We're looking for talented individuals to help us grow and succeed together.
                    </p>
                </div>
            </div>
        </div>

        <!-- Job Listings -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div v-if="jobPostings.length === 0" class="text-center py-12">
                <BriefcaseIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Open Positions</h3>
                <p class="text-gray-600">We don't have any open positions at the moment. Check back soon!</p>
            </div>

            <div v-else class="grid gap-6 lg:grid-cols-2">
                <div
                    v-for="job in jobPostings"
                    :key="job.id"
                    class="bg-white rounded-lg shadow-sm border hover:shadow-md transition-shadow"
                >
                    <div class="p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="text-xl font-semibold text-gray-900 mb-2">
                                    {{ job.title }}
                                </h3>
                                <div class="flex items-center text-sm text-gray-600 mb-3">
                                    <BuildingOfficeIcon class="w-4 h-4 mr-1" />
                                    <span>{{ job.department.name }}</span>
                                    <span class="mx-2">•</span>
                                    <MapPinIcon class="w-4 h-4 mr-1" />
                                    <span>{{ job.location || 'Lusaka, Zambia' }}</span>
                                </div>
                                <p class="text-gray-700 mb-4 line-clamp-3">
                                    {{ job.description }}
                                </p>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ formatEmploymentType(job.employment_type) }}
                                        </span>
                                        <span class="text-sm font-medium text-green-600">
                                            {{ job.salary_range }}
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        Posted {{ formatDate(job.posted_at, 'relative') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-6 flex space-x-3">
                            <Link
                                :href="route('careers.show', job.id)"
                                class="flex-1 bg-blue-600 text-white text-center py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors"
                            >
                                View Details
                            </Link>
                            <Link
                                :href="route('careers.apply', job.id)"
                                class="flex-1 bg-green-600 text-white text-center py-2 px-4 rounded-lg hover:bg-green-700 transition-colors"
                            >
                                Apply Now
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer CTA -->
        <div class="bg-blue-600 text-white py-16">
            <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold mb-4">Don't See the Right Position?</h2>
                <p class="text-xl mb-8">
                    We're always looking for talented individuals. Send us your resume and we'll keep you in mind for future opportunities.
                </p>
                <a
                    href="mailto:careers@vbif.com"
                    class="inline-flex items-center px-6 py-3 bg-white text-blue-600 font-semibold rounded-lg hover:bg-gray-50 transition-colors"
                >
                    <EnvelopeIcon class="w-5 h-5 mr-2" />
                    Send Your Resume
                </a>
            </div>
        </div>
        <Footer />
    </div>
</template>

<script setup lang="ts">
import Navigation from '@/components/custom/Navigation.vue';
import Footer from '@/components/custom/Footer.vue';
import { Link } from '@inertiajs/vue3';
import { BriefcaseIcon, BuildingOfficeIcon, MapPinIcon, EnvelopeIcon } from '@heroicons/vue/24/outline';
import { formatDate } from '@/utils/formatting';

interface JobPosting {
    id: number;
    title: string;
    description: string;
    employment_type: string;
    location?: string;
    salary_range: string;
    posted_at: string;
    department: {
        name: string;
    };
}

interface Props {
    jobPostings: JobPosting[];
}

defineProps<Props>();

const formatEmploymentType = (type: string): string => {
    return type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
};
</script>

<style scoped>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
