<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import GrowBizLayout from '@/Layouts/GrowBizLayout.vue';
import { ArrowLeftIcon, BuildingOffice2Icon } from '@heroicons/vue/24/outline';

interface Props {
    businessProfile: {
        id?: number;
        business_name: string | null;
        business_type: string | null;
        industry: string | null;
        description: string | null;
        phone: string | null;
        email: string | null;
        address: string | null;
        city: string | null;
        country: string | null;
    } | null;
}

const props = defineProps<Props>();

const form = useForm({
    business_name: props.businessProfile?.business_name || '',
    business_type: props.businessProfile?.business_type || '',
    industry: props.businessProfile?.industry || '',
    description: props.businessProfile?.description || '',
    phone: props.businessProfile?.phone || '',
    email: props.businessProfile?.email || '',
    address: props.businessProfile?.address || '',
    city: props.businessProfile?.city || '',
    country: props.businessProfile?.country || 'Zambia',
});

const businessTypes = [
    { value: '', label: 'Select type...' },
    { value: 'sole_proprietorship', label: 'Sole Proprietorship' },
    { value: 'partnership', label: 'Partnership' },
    { value: 'limited_company', label: 'Limited Company' },
    { value: 'cooperative', label: 'Cooperative' },
    { value: 'ngo', label: 'NGO / Non-Profit' },
    { value: 'other', label: 'Other' },
];

const industries = [
    { value: '', label: 'Select industry...' },
    { value: 'agriculture', label: 'Agriculture & Farming' },
    { value: 'construction', label: 'Construction' },
    { value: 'education', label: 'Education & Training' },
    { value: 'finance', label: 'Finance & Banking' },
    { value: 'food_beverage', label: 'Food & Beverage' },
    { value: 'healthcare', label: 'Healthcare' },
    { value: 'hospitality', label: 'Hospitality & Tourism' },
    { value: 'manufacturing', label: 'Manufacturing' },
    { value: 'retail', label: 'Retail & Wholesale' },
    { value: 'services', label: 'Professional Services' },
    { value: 'technology', label: 'Technology & IT' },
    { value: 'transport', label: 'Transport & Logistics' },
    { value: 'other', label: 'Other' },
];

const submit = () => {
    form.post(route('growbiz.settings.business.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <GrowBizLayout>
        <Head title="Business Profile - GrowBiz" />
        
        <div class="px-4 py-4 pb-6">
            <!-- Page Header -->
            <div class="flex items-center gap-3 mb-6">
                <Link 
                    :href="route('growbiz.settings.index')" 
                    class="p-2 -ml-2 rounded-xl hover:bg-gray-100 active:bg-gray-200 transition-colors"
                    aria-label="Back to settings"
                >
                    <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                </Link>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Business Profile</h1>
                    <p class="text-sm text-gray-500">Manage your business information</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Business Info Card -->
                <div class="bg-white rounded-2xl shadow-sm p-5">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 bg-emerald-100 rounded-xl">
                            <BuildingOffice2Icon class="h-5 w-5 text-emerald-600" />
                        </div>
                        <h2 class="font-semibold text-gray-900">Business Information</h2>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Business Name
                            </label>
                            <input
                                v-model="form.business_name"
                                type="text"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                placeholder="e.g., ABC Trading Ltd"
                            />
                            <p v-if="form.errors.business_name" class="mt-1 text-sm text-red-600">
                                {{ form.errors.business_name }}
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Business Type
                                </label>
                                <select
                                    v-model="form.business_type"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                >
                                    <option v-for="type in businessTypes" :key="type.value" :value="type.value">
                                        {{ type.label }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Industry
                                </label>
                                <select
                                    v-model="form.industry"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                >
                                    <option v-for="ind in industries" :key="ind.value" :value="ind.value">
                                        {{ ind.label }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Description
                            </label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                placeholder="Brief description of your business..."
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Contact Info Card -->
                <div class="bg-white rounded-2xl shadow-sm p-5">
                    <h2 class="font-semibold text-gray-900 mb-4">Contact Information</h2>

                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Phone
                                </label>
                                <input
                                    v-model="form.phone"
                                    type="tel"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    placeholder="+260 XXX XXX XXX"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Email
                                </label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    placeholder="business@example.com"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Address
                            </label>
                            <input
                                v-model="form.address"
                                type="text"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                placeholder="Street address"
                            />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    City
                                </label>
                                <input
                                    v-model="form.city"
                                    type="text"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    placeholder="e.g., Lusaka"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Country
                                </label>
                                <input
                                    v-model="form.country"
                                    type="text"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    placeholder="Zambia"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full py-3.5 px-4 bg-emerald-600 text-white rounded-xl font-semibold hover:bg-emerald-700 disabled:opacity-50 transition-colors"
                >
                    {{ form.processing ? 'Saving...' : 'Save Changes' }}
                </button>
            </form>
        </div>
    </GrowBizLayout>
</template>
