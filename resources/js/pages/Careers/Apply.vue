<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <div class="bg-white shadow-sm">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex items-center justify-between mb-6">
                    <Link
                        :href="route('careers.show', jobPosting.id)"
                        class="inline-flex items-center text-blue-600 hover:text-blue-700"
                    >
                        <ArrowLeftIcon class="w-5 h-5 mr-2" />
                        Back to Job Details
                    </Link>
                </div>

                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Apply for {{ jobPosting.title }}</h1>
                    <p class="text-gray-600">{{ jobPosting.department.name }} • {{ jobPosting.location || 'Lusaka, Zambia' }}</p>
                </div>
            </div>
        </div>

        <!-- Application Form -->
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="bg-white rounded-lg shadow-sm border p-8">
                <form @submit.prevent="submit" enctype="multipart/form-data">
                    <!-- Personal Information -->
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">Personal Information</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    First Name <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.first_name"
                                    type="text"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': form.errors.first_name }"
                                />
                                <div v-if="form.errors.first_name" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.first_name }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Last Name <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.last_name"
                                    type="text"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': form.errors.last_name }"
                                />
                                <div v-if="form.errors.last_name" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.last_name }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Email Address <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': form.errors.email }"
                                />
                                <div v-if="form.errors.email" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.email }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Phone Number <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.phone"
                                    type="tel"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': form.errors.phone }"
                                />
                                <div v-if="form.errors.phone" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.phone }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">National ID</label>
                                <input
                                    v-model="form.national_id"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': form.errors.national_id }"
                                />
                                <div v-if="form.errors.national_id" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.national_id }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Expected Salary (Kwacha)</label>
                                <input
                                    v-model="form.expected_salary"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': form.errors.expected_salary }"
                                />
                                <div v-if="form.errors.expected_salary" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.expected_salary }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                            <textarea
                                v-model="form.address"
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                :class="{ 'border-red-500': form.errors.address }"
                            ></textarea>
                            <div v-if="form.errors.address" class="text-red-600 text-sm mt-1">
                                {{ form.errors.address }}
                            </div>
                        </div>
                    </div>

                    <!-- Resume Upload -->
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">Resume</h2>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Upload Resume <span class="text-red-500">*</span>
                            </label>
                            <input
                                @change="handleFileUpload"
                                type="file"
                                accept=".pdf,.doc,.docx"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                :class="{ 'border-red-500': form.errors.resume }"
                            />
                            <p class="text-sm text-gray-500 mt-1">Accepted formats: PDF, DOC, DOCX (Max 5MB)</p>
                            <div v-if="form.errors.resume" class="text-red-600 text-sm mt-1">
                                {{ form.errors.resume }}
                            </div>
                        </div>
                    </div>

                    <!-- Cover Letter -->
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">Cover Letter</h2>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tell us why you're interested in this position
                            </label>
                            <textarea
                                v-model="form.cover_letter"
                                rows="6"
                                placeholder="Describe your interest in this position, relevant experience, and what you can bring to our team..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                :class="{ 'border-red-500': form.errors.cover_letter }"
                            ></textarea>
                            <div v-if="form.errors.cover_letter" class="text-red-600 text-sm mt-1">
                                {{ form.errors.cover_letter }}
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                        <Link
                            :href="route('careers.show', jobPosting.id)"
                            class="px-6 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Submitting...' : 'Submit Application' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

interface JobPosting {
    id: number;
    title: string;
    location?: string;
    department: {
        name: string;
    };
}

interface Props {
    jobPosting: JobPosting;
}

const props = defineProps<Props>();

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    national_id: '',
    address: '',
    resume: null as File | null,
    cover_letter: '',
    expected_salary: '',
});

const handleFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.resume = target.files[0];
    }
};

const submit = () => {
    form.post(route('careers.store-application', props.jobPosting.id));
};
</script>
