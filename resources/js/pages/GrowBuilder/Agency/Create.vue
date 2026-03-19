<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    BuildingOfficeIcon,
    SparklesIcon,
    CheckIcon,
} from '@heroicons/vue/24/outline';

const form = useForm({
    agency_name: '',
    business_email: '',
    phone: '',
    country: 'Zambia',
});

const submit = () => {
    form.post(route('growbuilder.agency.store'), {
        onSuccess: () => {
            // Will redirect to agency dashboard after creation
        },
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Create Your Agency - GrowBuilder" />

        <div class="py-12">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <BuildingOfficeIcon class="h-8 w-8 text-white" aria-hidden="true" />
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Create Your Agency</h1>
                    <p class="text-gray-600">
                        Set up your agency to start managing client websites and projects
                    </p>
                </div>

                <!-- Agency Benefits -->
                <div class="bg-gradient-to-r from-purple-50 to-indigo-50 rounded-xl p-6 mb-8">
                    <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <SparklesIcon class="h-5 w-5 text-purple-600" aria-hidden="true" />
                        Agency Tier Benefits
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-center gap-2 text-sm text-gray-700">
                            <CheckIcon class="h-4 w-4 text-green-600 flex-shrink-0" aria-hidden="true" />
                            <span>Up to 20 websites</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-700">
                            <CheckIcon class="h-4 w-4 text-green-600 flex-shrink-0" aria-hidden="true" />
                            <span>50GB pooled storage</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-700">
                            <CheckIcon class="h-4 w-4 text-green-600 flex-shrink-0" aria-hidden="true" />
                            <span>Client management tools</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-700">
                            <CheckIcon class="h-4 w-4 text-green-600 flex-shrink-0" aria-hidden="true" />
                            <span>Team collaboration</span>
                        </div>
                    </div>
                </div>

                <!-- Create Form -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Agency Name -->
                        <div>
                            <label for="agency_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Agency Name *
                            </label>
                            <input
                                id="agency_name"
                                v-model="form.agency_name"
                                type="text"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                placeholder="e.g., Acme Digital Agency"
                            />
                            <div v-if="form.errors.agency_name" class="mt-1 text-sm text-red-600">
                                {{ form.errors.agency_name }}
                            </div>
                        </div>

                        <!-- Business Email -->
                        <div>
                            <label for="business_email" class="block text-sm font-medium text-gray-700 mb-2">
                                Business Email *
                            </label>
                            <input
                                id="business_email"
                                v-model="form.business_email"
                                type="email"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                placeholder="contact@acmedigital.com"
                            />
                            <div v-if="form.errors.business_email" class="mt-1 text-sm text-red-600">
                                {{ form.errors.business_email }}
                            </div>
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                Phone Number
                            </label>
                            <input
                                id="phone"
                                v-model="form.phone"
                                type="tel"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                placeholder="+260 XXX XXX XXX"
                            />
                            <div v-if="form.errors.phone" class="mt-1 text-sm text-red-600">
                                {{ form.errors.phone }}
                            </div>
                        </div>

                        <!-- Country -->
                        <div>
                            <label for="country" class="block text-sm font-medium text-gray-700 mb-2">
                                Country
                            </label>
                            <select
                                id="country"
                                v-model="form.country"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                            >
                                <option value="Zambia">Zambia</option>
                                <option value="South Africa">South Africa</option>
                                <option value="Kenya">Kenya</option>
                                <option value="Nigeria">Nigeria</option>
                                <option value="Ghana">Ghana</option>
                                <option value="Other">Other</option>
                            </select>
                            <div v-if="form.errors.country" class="mt-1 text-sm text-red-600">
                                {{ form.errors.country }}
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-4">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-semibold py-3 px-4 rounded-lg hover:from-purple-700 hover:to-indigo-700 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                            >
                                <span v-if="form.processing">Creating Agency...</span>
                                <span v-else>Create Agency</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Help Text -->
                <div class="text-center mt-6">
                    <p class="text-sm text-gray-500">
                        Need help? Contact our support team at 
                        <a href="mailto:support@mygrownet.com" class="text-purple-600 hover:text-purple-700">
                            support@mygrownet.com
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>