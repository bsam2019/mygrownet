<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <div class="bg-white shadow-sm">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex items-center justify-between mb-6">
                    <Link
                        :href="route('careers.index')"
                        class="inline-flex items-center text-blue-600 hover:text-blue-700"
                    >
                        <ArrowLeftIcon class="w-5 h-5 mr-2" />
                        Back to Jobs
                    </Link>
                    <Link
                        :href="route('careers.apply', jobPosting.id)"
                        class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors"
                    >
                        Apply for this Position
                    </Link>
                </div>

                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ jobPosting.title }}</h1>
                    <div class="flex items-center text-gray-600 space-x-6">
                        <div class="flex items-center">
                            <BuildingOfficeIcon class="w-5 h-5 mr-2" />
                            <span>{{ jobPosting.department.name }}</span>
                        </div>
                        <div class="flex items-center">
                            <MapPinIcon class="w-5 h-5 mr-2" />
                            <span>{{ jobPosting.location || 'Lusaka, Zambia' }}</span>
                        </div>
                        <div class="flex items-center">
                            <ClockIcon class="w-5 h-5 mr-2" />
                            <span>{{ formatEmploymentType(jobPosting.employment_type) }}</span>
                        </div>
                        <div class="flex items-center">
                            <CurrencyDollarIcon class="w-5 h-5 mr-2" />
                            <span class="font-medium text-green-600">{{ jobPosting.salary_range }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Job Details -->
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="bg-white rounded-lg shadow-sm border p-8">
                <div class="prose max-w-none">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Job Description</h2>
                    <div class="text-gray-700 mb-8 whitespace-pre-line">{{ jobPosting.description }}</div>

                    <h2 v-if="jobPosting.requirements" class="text-xl font-semibold text-gray-900 mb-4">Requirements</h2>
                    <div v-if="jobPosting.requirements" class="text-gray-700 mb-8 whitespace-pre-line">{{ jobPosting.requirements }}</div>

                    <h2 v-if="jobPosting.benefits" class="text-xl font-semibold text-gray-900 mb-4">Benefits</h2>
                    <div v-if="jobPosting.benefits" class="text-gray-700 mb-8 whitespace-pre-line">{{ jobPosting.benefits }}</div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mt-8">
                        <h3 class="text-lg font-semibold text-blue-900 mb-2">Ready to Apply?</h3>
                        <p class="text-blue-800 mb-4">
                            Join our team and be part of something great. We offer competitive compensation, 
                            professional development opportunities, and a collaborative work environment.
                        </p>
                        <Link
                            :href="route('careers.apply', jobPosting.id)"
                            class="inline-flex items-center bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors"
                        >
                            <DocumentTextIcon class="w-5 h-5 mr-2" />
                            Apply Now
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { 
    ArrowLeftIcon, 
    BuildingOfficeIcon, 
    MapPinIcon, 
    ClockIcon, 
    CurrencyDollarIcon,
    DocumentTextIcon 
} from '@heroicons/vue/24/outline';

interface JobPosting {
    id: number;
    title: string;
    description: string;
    requirements?: string;
    benefits?: string;
    employment_type: string;
    location?: string;
    salary_range: string;
    posted_at: string;
    department: {
        name: string;
    };
}

interface Props {
    jobPosting: JobPosting;
}

defineProps<Props>();

const formatEmploymentType = (type: string): string => {
    return type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
};
</script>
