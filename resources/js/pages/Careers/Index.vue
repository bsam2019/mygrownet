<template>
    <div class="min-h-screen bg-gray-50">
        <Navigation />
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <div class="text-center">
                    <h1 class="text-4xl font-bold mb-4">Start Your Growth Journey</h1>
                    <p class="text-xl text-blue-100 max-w-3xl mx-auto">
                        MyGrowNet isn't about traditional jobs—it's about building your own path to success through learning, earning, and growing with a supportive community.
                    </p>
                </div>
            </div>
        </div>

        <!-- Opportunities Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Why Join MyGrowNet -->
            <div class="mb-12 text-center">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Why Choose MyGrowNet?</h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                    Unlike traditional employment, MyGrowNet gives you the freedom to build your own business while learning valuable skills and earning multiple income streams.
                </p>
            </div>

            <!-- Benefits Grid -->
            <div class="grid md:grid-cols-3 gap-8 mb-16">
                <div class="bg-white rounded-lg shadow-sm border p-6 text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Learn & Grow</h3>
                    <p class="text-gray-600">Access e-books, video lessons, live coaching, and workshops to develop valuable life and business skills.</p>
                </div>

                <div class="bg-white rounded-lg shadow-sm border p-6 text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Multiple Income Streams</h3>
                    <p class="text-gray-600">Earn through referrals, network bonuses, milestone rewards, profit-sharing, and more—6 different ways to earn!</p>
                </div>

                <div class="bg-white rounded-lg shadow-sm border p-6 text-center">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Supportive Community</h3>
                    <p class="text-gray-600">Join a network of mentors, peers, and leaders who support your journey and celebrate your success.</p>
                </div>
            </div>

            <!-- Professional Levels -->
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-8 mb-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">Your Professional Journey: 7 Levels</h2>
                <div class="grid md:grid-cols-7 gap-4">
                    <div v-for="(level, index) in professionalLevels" :key="index" class="bg-white rounded-lg p-4 text-center shadow-sm">
                        <div class="text-2xl font-bold text-blue-600 mb-2">{{ index + 1 }}</div>
                        <div class="font-semibold text-gray-900 text-sm mb-1">{{ level.name }}</div>
                        <div class="text-xs text-gray-600">{{ level.description }}</div>
                    </div>
                </div>
                <p class="text-center text-gray-600 mt-6">
                    Progress through 7 professional levels—from Associate to Ambassador—each unlocking new earning potential and recognition.
                </p>
            </div>

            <div v-if="jobPostings.length === 0" class="text-center py-12">
                <BriefcaseIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                <h3 class="text-lg font-medium text-gray-900 mb-2">Ready to Start Your Journey?</h3>
                <p class="text-gray-600 mb-6">Join MyGrowNet today and begin building your path to success.</p>
                <Link
                    :href="route('register')"
                    class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors"
                >
                    Become a Member
                </Link>
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
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-16">
            <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold mb-4">Ready to Transform Your Future?</h2>
                <p class="text-xl mb-8">
                    Join thousands of members who are learning, earning, and growing with MyGrowNet. Your journey starts with a simple subscription.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <Link
                        :href="route('register')"
                        class="inline-flex items-center justify-center px-8 py-4 bg-white text-blue-600 font-semibold rounded-lg hover:bg-gray-50 transition-colors text-lg"
                    >
                        Join MyGrowNet Today
                    </Link>
                    <Link
                        :href="route('contact')"
                        class="inline-flex items-center justify-center px-8 py-4 bg-blue-700 text-white font-semibold rounded-lg hover:bg-blue-800 transition-colors text-lg border-2 border-white"
                    >
                        <EnvelopeIcon class="w-5 h-5 mr-2" />
                        Have Questions?
                    </Link>
                </div>
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

const professionalLevels = [
    { name: 'Associate', description: 'Learning' },
    { name: 'Professional', description: 'Applying' },
    { name: 'Senior', description: 'Building' },
    { name: 'Manager', description: 'Leading' },
    { name: 'Director', description: 'Strategic' },
    { name: 'Executive', description: 'Top Performer' },
    { name: 'Ambassador', description: 'Community Leader' }
];

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
