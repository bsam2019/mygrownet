<template>
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8">
                <Link :href="route('growbuilder.clients.show', client.id)"
                      class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 mb-4">
                    <ArrowLeftIcon class="h-4 w-4 mr-2" aria-hidden="true" />
                    Back to Client
                </Link>
                <h1 class="text-3xl font-bold text-gray-900">Edit Client</h1>
                <p class="mt-2 text-sm text-gray-600">Update client information and settings</p>
            </div>

            <!-- Form -->
            <form @submit.prevent="submitForm" class="space-y-6">
                <!-- Basic Information -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Client Type -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Client Type <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <button
                                    v-for="type in clientTypes"
                                    :key="type.value"
                                    type="button"
                                    @click="form.client_type = type.value"
                                    class="flex flex-col items-center p-4 border-2 rounded-lg transition-all"
                                    :class="form.client_type === type.value 
                                        ? 'border-blue-500 bg-blue-50' 
                                        : 'border-gray-200 hover:border-gray-300'"
                                >
                                    <component :is="type.icon" class="h-6 w-6 mb-2" 
                                               :class="form.client_type === type.value ? 'text-blue-600' : 'text-gray-400'"
                                               aria-hidden="true" />
                                    <span class="text-sm font-medium" 
                                          :class="form.client_type === type.value ? 'text-blue-900' : 'text-gray-700'">
                                        {{ type.label }}
                                    </span>
                                </button>
                            </div>
                            <p v-if="form.errors.client_type" class="mt-1 text-sm text-red-600">
                                {{ form.errors.client_type }}
                            </p>
                        </div>

                        <!-- Client Name -->
                        <div class="md:col-span-2">
                            <label for="client_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Client Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="client_name"
                                v-model="form.client_name"
                                type="text"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                :class="{ 'border-red-500': form.errors.client_name }"
                            />
                            <p v-if="form.errors.client_name" class="mt-1 text-sm text-red-600">
                                {{ form.errors.client_name }}
                            </p>
                        </div>

                        <!-- Company Name -->
                        <div class="md:col-span-2">
                            <label for="company_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Company Name
                                <span class="text-gray-500 text-xs">(if different from client name)</span>
                            </label>
                            <input
                                id="company_name"
                                v-model="form.company_name"
                                type="text"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Contact Information</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email Address
                            </label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                :class="{ 'border-red-500': form.errors.email }"
                            />
                            <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                                {{ form.errors.email }}
                            </p>
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
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
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
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                        </div>
                    </div>
                </div>

                <!-- Address Information -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Address Information</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Address -->
                        <div class="md:col-span-2">
                            <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                                Street Address
                            </label>
                            <textarea
                                id="address"
                                v-model="form.address"
                                rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            ></textarea>
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
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                        </div>

                        <!-- Country -->
                        <div>
                            <label for="country" class="block text-sm font-medium text-gray-700 mb-2">
                                Country
                            </label>
                            <input
                                id="country"
                                v-model="form.country"
                                type="text"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                        </div>
                    </div>
                </div>

                <!-- Status & Settings -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Status & Settings</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                Client Status
                            </label>
                            <select
                                id="status"
                                v-model="form.status"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                                <option value="lead">Lead</option>
                                <option value="active">Active</option>
                                <option value="suspended">Suspended</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>

                        <!-- Billing Status -->
                        <div>
                            <label for="billing_status" class="block text-sm font-medium text-gray-700 mb-2">
                                Billing Status
                            </label>
                            <select
                                id="billing_status"
                                v-model="form.billing_status"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                                <option value="active">Active</option>
                                <option value="overdue">Overdue</option>
                                <option value="suspended">Suspended</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>

                        <!-- Onboarding Status -->
                        <div>
                            <label for="onboarding_status" class="block text-sm font-medium text-gray-700 mb-2">
                                Onboarding Status
                            </label>
                            <select
                                id="onboarding_status"
                                v-model="form.onboarding_status"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                                <option value="new">New</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Additional Notes</h2>
                    
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Internal Notes
                        </label>
                        <textarea
                            id="notes"
                            v-model="form.notes"
                            rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        ></textarea>
                        <p class="mt-1 text-xs text-gray-500">
                            These notes are only visible to your agency team
                        </p>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-between">
                    <button
                        type="button"
                        @click="confirmDelete"
                        class="px-6 py-2 text-red-600 hover:text-red-700 transition-colors"
                    >
                        Delete Client
                    </button>
                    
                    <div class="flex gap-4">
                        <Link :href="route('growbuilder.clients.show', client.id)"
                              class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="form.processing">Saving...</span>
                            <span v-else>Save Changes</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link, useForm, router } from '@inertiajs/vue3';
import { 
    ArrowLeftIcon,
    UserIcon,
    BuildingOfficeIcon,
    AcademicCapIcon,
    BuildingLibraryIcon,
    HeartIcon,
    GlobeAltIcon,
    BriefcaseIcon
} from '@heroicons/vue/24/outline';

interface Client {
    id: number;
    client_type: string;
    client_name: string;
    company_name: string | null;
    email: string | null;
    phone: string | null;
    alternative_phone: string | null;
    address: string | null;
    city: string | null;
    country: string | null;
    status: string;
    billing_status: string;
    onboarding_status: string;
    notes: string | null;
}

interface Props {
    client: Client;
}

const props = defineProps<Props>();

const clientTypes = [
    { value: 'individual', label: 'Individual', icon: UserIcon },
    { value: 'business', label: 'Business', icon: BuildingOfficeIcon },
    { value: 'school', label: 'School', icon: AcademicCapIcon },
    { value: 'church', label: 'Church', icon: BuildingLibraryIcon },
    { value: 'ngo', label: 'NGO', icon: HeartIcon },
    { value: 'institution', label: 'Institution', icon: GlobeAltIcon },
    { value: 'government', label: 'Government', icon: BriefcaseIcon },
];

const form = useForm({
    client_type: props.client.client_type,
    client_name: props.client.client_name,
    company_name: props.client.company_name || '',
    email: props.client.email || '',
    phone: props.client.phone || '',
    alternative_phone: props.client.alternative_phone || '',
    address: props.client.address || '',
    city: props.client.city || '',
    country: props.client.country || '',
    status: props.client.status,
    billing_status: props.client.billing_status,
    onboarding_status: props.client.onboarding_status,
    notes: props.client.notes || '',
});

function submitForm() {
    form.put(route('growbuilder.clients.update', props.client.id), {
        onSuccess: () => {
            // Redirect handled by controller
        },
    });
}

function confirmDelete() {
    if (confirm('Are you sure you want to delete this client? This action cannot be undone.')) {
        router.delete(route('growbuilder.clients.destroy', props.client.id));
    }
}
</script>
