<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { onMounted, watch, nextTick } from 'vue';
import {
    UsersIcon,
    GlobeAltIcon,
    Cog6ToothIcon,
    ArrowLeftIcon,
    BuildingOfficeIcon,
    UserIcon,
    EnvelopeIcon,
    PhoneIcon,
    MapPinIcon,
    CheckCircleIcon,
} from '@heroicons/vue/24/outline';

// TypeScript declaration for window timeout
declare global {
    interface Window {
        autoSaveTimeout: number;
    }
}

const STORAGE_KEY = 'growbuilder_client_create_form';

// Load saved data from localStorage
const getSavedData = () => {
    try {
        const saved = localStorage.getItem(STORAGE_KEY);
        return saved ? JSON.parse(saved) : null;
    } catch (error) {
        console.warn('Failed to load saved form data:', error);
        return null;
    }
};

// Initialize form with saved data or defaults
const savedData = getSavedData();
const form = useForm({
    client_name: savedData?.client_name || '',
    company_name: savedData?.company_name || '',
    client_type: savedData?.client_type || 'business',
    email: savedData?.email || '',
    phone: savedData?.phone || '',
    alternative_phone: savedData?.alternative_phone || '',
    address: savedData?.address || '',
    country: savedData?.country || 'ZM',
    city: savedData?.city || '',
    notes: savedData?.notes || '',
    tags: savedData?.tags || '',
});

// Auto-save functionality
const saveFormData = () => {
    try {
        const formData = {
            client_name: form.client_name,
            company_name: form.company_name,
            client_type: form.client_type,
            email: form.email,
            phone: form.phone,
            alternative_phone: form.alternative_phone,
            address: form.address,
            country: form.country,
            city: form.city,
            notes: form.notes,
            tags: form.tags,
        };
        localStorage.setItem(STORAGE_KEY, JSON.stringify(formData));
    } catch (error) {
        console.warn('Failed to save form data:', error);
    }
};

// Clear saved data
const clearSavedData = () => {
    try {
        localStorage.removeItem(STORAGE_KEY);
    } catch (error) {
        console.warn('Failed to clear saved form data:', error);
    }
};

// Watch for form changes and auto-save
watch(
    () => ({
        client_name: form.client_name,
        company_name: form.company_name,
        client_type: form.client_type,
        email: form.email,
        phone: form.phone,
        alternative_phone: form.alternative_phone,
        address: form.address,
        country: form.country,
        city: form.city,
        notes: form.notes,
        tags: form.tags,
    }),
    () => {
        // Debounce auto-save to avoid excessive localStorage writes
        clearTimeout(window.autoSaveTimeout);
        window.autoSaveTimeout = setTimeout(saveFormData, 1000);
    },
    { deep: true }
);

const submit = () => {
    form.post(route('growbuilder.clients.store'), {
        onSuccess: () => {
            // Clear saved data on successful submission
            clearSavedData();
        },
    });
};

const clientTypes = [
    { value: 'individual', label: 'Individual' },
    { value: 'business', label: 'Business' },
    { value: 'institution', label: 'Institution' },
    { value: 'church', label: 'Church' },
    { value: 'school', label: 'School' },
    { value: 'ngo', label: 'NGO' },
    { value: 'government', label: 'Government' },
];

// Country options with 2-character codes
const countries = [
    { code: 'ZM', name: 'Zambia' },
    { code: 'ZA', name: 'South Africa' },
    { code: 'KE', name: 'Kenya' },
    { code: 'NG', name: 'Nigeria' },
    { code: 'GH', name: 'Ghana' },
    { code: 'TZ', name: 'Tanzania' },
    { code: 'UG', name: 'Uganda' },
    { code: 'RW', name: 'Rwanda' },
    { code: 'MW', name: 'Malawi' },
    { code: 'ZW', name: 'Zimbabwe' },
    { code: 'BW', name: 'Botswana' },
    { code: 'MZ', name: 'Mozambique' },
    { code: 'AO', name: 'Angola' },
    { code: 'CD', name: 'Democratic Republic of Congo' },
    { code: 'ET', name: 'Ethiopia' },
    { code: 'US', name: 'United States' },
    { code: 'GB', name: 'United Kingdom' },
    { code: 'CA', name: 'Canada' },
    { code: 'AU', name: 'Australia' },
    { code: 'IN', name: 'India' },
    { code: 'CN', name: 'China' },
    { code: 'BR', name: 'Brazil' },
    { code: 'DE', name: 'Germany' },
    { code: 'FR', name: 'France' },
    { code: 'JP', name: 'Japan' },
    { code: 'XX', name: 'Other' },
];

// Show auto-save indicator
const showAutoSaveIndicator = () => {
    const indicator = document.getElementById('auto-save-indicator');
    if (indicator) {
        indicator.classList.remove('opacity-0');
        indicator.classList.add('opacity-100');
        setTimeout(() => {
            indicator.classList.remove('opacity-100');
            indicator.classList.add('opacity-0');
        }, 2000);
    }
};

// Enhanced auto-save with visual feedback
const saveFormDataWithFeedback = () => {
    saveFormData();
    showAutoSaveIndicator();
};

// Update the watch to use the enhanced save function
watch(
    () => ({
        client_name: form.client_name,
        company_name: form.company_name,
        client_type: form.client_type,
        email: form.email,
        phone: form.phone,
        alternative_phone: form.alternative_phone,
        address: form.address,
        country: form.country,
        city: form.city,
        notes: form.notes,
        tags: form.tags,
    }),
    () => {
        // Only auto-save if there's actual content
        const hasContent = Object.values(form.data()).some(value => 
            typeof value === 'string' && value.trim().length > 0
        );
        
        if (hasContent) {
            clearTimeout(window.autoSaveTimeout);
            window.autoSaveTimeout = setTimeout(saveFormDataWithFeedback, 1500);
        }
    },
    { deep: true }
);

onMounted(() => {
    // Show notification if data was restored
    if (savedData) {
        nextTick(() => {
            const notification = document.getElementById('restored-data-notification');
            if (notification) {
                notification.classList.remove('opacity-0', 'translate-y-2');
                notification.classList.add('opacity-100', 'translate-y-0');
                setTimeout(() => {
                    notification.classList.remove('opacity-100', 'translate-y-0');
                    notification.classList.add('opacity-0', 'translate-y-2');
                }, 4000);
            }
        });
    }
});
</script>

<template>
    <AppLayout>
        <Head title="Create Client - GrowBuilder" />

        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <div class="flex items-center gap-4 mb-4">
                        <Link
                            :href="route('growbuilder.clients.index')"
                            class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition"
                        >
                            <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
                        </Link>
                        <div class="flex-1">
                            <div class="flex items-center gap-3">
                                <div>
                                    <h1 class="text-2xl font-bold text-gray-900">Create New Client</h1>
                                    <p class="text-gray-600">Add a new client to manage their websites and projects</p>
                                </div>
                                
                                <!-- Auto-save indicator -->
                                <div 
                                    id="auto-save-indicator"
                                    class="flex items-center gap-2 px-3 py-1.5 bg-green-50 text-green-700 rounded-lg text-sm opacity-0 transition-opacity duration-300"
                                >
                                    <CheckCircleIcon class="h-4 w-4" aria-hidden="true" />
                                    <span>Auto-saved</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Restored data notification -->
                    <div 
                        id="restored-data-notification"
                        class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg opacity-0 translate-y-2 transition-all duration-300"
                    >
                        <div class="flex items-center gap-2 text-blue-800">
                            <CheckCircleIcon class="h-4 w-4" aria-hidden="true" />
                            <span class="text-sm font-medium">Form data restored from previous session</span>
                        </div>
                    </div>

                    <!-- Navigation Tabs -->
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-8">
                            <Link 
                                :href="route('growbuilder.dashboard')"
                                class="py-2 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm"
                            >
                                <GlobeAltIcon class="h-5 w-5 inline mr-2" aria-hidden="true" />
                                Sites
                            </Link>
                            <Link 
                                :href="route('growbuilder.clients.index')"
                                class="py-2 px-1 border-b-2 border-blue-500 text-blue-600 font-medium text-sm"
                            >
                                <UsersIcon class="h-5 w-5 inline mr-2" aria-hidden="true" />
                                Clients
                            </Link>
                            <Link 
                                :href="route('growbuilder.agency.dashboard')"
                                class="py-2 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm"
                            >
                                <Cog6ToothIcon class="h-5 w-5 inline mr-2" aria-hidden="true" />
                                Agency
                            </Link>
                        </nav>
                    </div>
                </div>

                <!-- Create Form -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <!-- Basic Information -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <UserIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                                Basic Information
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Client Name -->
                                <div>
                                    <label for="client_name" class="block text-sm font-medium text-gray-700 mb-2">
                                        Client Name *
                                    </label>
                                    <input
                                        id="client_name"
                                        v-model="form.client_name"
                                        type="text"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="John Doe"
                                    />
                                    <div v-if="form.errors.client_name" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.client_name }}
                                    </div>
                                </div>

                                <!-- Company Name -->
                                <div>
                                    <label for="company_name" class="block text-sm font-medium text-gray-700 mb-2">
                                        Company Name
                                    </label>
                                    <input
                                        id="company_name"
                                        v-model="form.company_name"
                                        type="text"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Acme Corporation"
                                    />
                                    <div v-if="form.errors.company_name" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.company_name }}
                                    </div>
                                </div>

                                <!-- Client Type -->
                                <div>
                                    <label for="client_type" class="block text-sm font-medium text-gray-700 mb-2">
                                        Client Type *
                                    </label>
                                    <select
                                        id="client_type"
                                        v-model="form.client_type"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option v-for="type in clientTypes" :key="type.value" :value="type.value">
                                            {{ type.label }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.client_type" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.client_type }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <EnvelopeIcon class="h-5 w-5 text-green-600" aria-hidden="true" />
                                Contact Information
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Email -->
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                        Email Address *
                                    </label>
                                    <input
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="john@acmecorp.com"
                                    />
                                    <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.email }}
                                    </div>
                                </div>

                                <!-- Phone -->
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                        Phone Number *
                                    </label>
                                    <input
                                        id="phone"
                                        v-model="form.phone"
                                        type="tel"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="+260 XXX XXX XXX"
                                    />
                                    <div v-if="form.errors.phone" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.phone }}
                                    </div>
                                </div>

                                <!-- Alternative Phone -->
                                <div>
                                    <label for="alternative_phone" class="block text-sm font-medium text-gray-700 mb-2">
                                        Alternative Phone
                                    </label>
                                    <input
                                        id="alternative_phone"
                                        v-model="form.alternative_phone"
                                        type="tel"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="+260 XXX XXX XXX"
                                    />
                                    <div v-if="form.errors.alternative_phone" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.alternative_phone }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Location Information -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <MapPinIcon class="h-5 w-5 text-purple-600" aria-hidden="true" />
                                Location Information
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Country -->
                                <div>
                                    <label for="country" class="block text-sm font-medium text-gray-700 mb-2">
                                        Country
                                    </label>
                                    <select
                                        id="country"
                                        v-model="form.country"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option v-for="country in countries" :key="country.code" :value="country.code">
                                            {{ country.name }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.country" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.country }}
                                    </div>
                                </div>

                                <!-- City -->
                                <div>
                                    <label for="city" class="block text-sm font-medium text-gray-700 mb-2">
                                        City
                                    </label>
                                    <input
                                        id="city"
                                        v-model="form.city"
                                        type="text"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Lusaka"
                                    />
                                    <div v-if="form.errors.city" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.city }}
                                    </div>
                                </div>

                                <!-- Address -->
                                <div class="md:col-span-2">
                                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                                        Address
                                    </label>
                                    <textarea
                                        id="address"
                                        v-model="form.address"
                                        rows="3"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Street address, building, etc."
                                    ></textarea>
                                    <div v-if="form.errors.address" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.address }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <BuildingOfficeIcon class="h-5 w-5 text-indigo-600" aria-hidden="true" />
                                Additional Information
                            </h3>
                            <div class="space-y-6">
                                <!-- Tags -->
                                <div>
                                    <label for="tags" class="block text-sm font-medium text-gray-700 mb-2">
                                        Tags
                                    </label>
                                    <input
                                        id="tags"
                                        v-model="form.tags"
                                        type="text"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="e.g., premium, priority, local (comma-separated)"
                                    />
                                    <p class="mt-1 text-sm text-gray-500">
                                        Separate multiple tags with commas
                                    </p>
                                    <div v-if="form.errors.tags" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.tags }}
                                    </div>
                                </div>

                                <!-- Notes -->
                                <div>
                                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                        Notes
                                    </label>
                                    <textarea
                                        id="notes"
                                        v-model="form.notes"
                                        rows="4"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Any additional notes about this client..."
                                    ></textarea>
                                    <div v-if="form.errors.notes" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.notes }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                            <div class="flex items-center gap-3">
                                <Link
                                    :href="route('growbuilder.clients.index')"
                                    class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition"
                                >
                                    Cancel
                                </Link>
                                <button
                                    v-if="savedData"
                                    type="button"
                                    @click="clearSavedData"
                                    class="px-3 py-2 text-sm text-gray-600 hover:text-gray-800 underline"
                                >
                                    Clear saved data
                                </button>
                            </div>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition"
                            >
                                <span v-if="form.processing">Creating Client...</span>
                                <span v-else>Create Client</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>