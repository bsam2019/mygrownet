<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import {
    BuildingStorefrontIcon,
    PhotoIcon,
    CheckIcon,
} from '@heroicons/vue/24/outline';

interface Business {
    id: number;
    name: string;
    description: string | null;
    industry: string;
    phone: string | null;
    whatsapp: string | null;
    email: string | null;
    website: string | null;
    address: string | null;
    city: string | null;
    province: string | null;
    logo_path: string | null;
    social_links: Record<string, string> | null;
    business_hours: Record<string, any> | null;
}

interface Props {
    business: Business;
    industries: Record<string, { name: string; icon: string }>;
}

const props = defineProps<Props>();

const form = useForm({
    name: props.business.name,
    description: props.business.description || '',
    industry: props.business.industry,
    phone: props.business.phone || '',
    whatsapp: props.business.whatsapp || '',
    email: props.business.email || '',
    website: props.business.website || '',
    address: props.business.address || '',
    city: props.business.city || '',
    province: props.business.province || '',
    social_links: props.business.social_links || {},
    business_hours: props.business.business_hours || {},
});

const logoForm = useForm({
    logo: null as File | null,
});

const logoPreview = ref<string | null>(null);

const handleLogoChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
        logoForm.logo = file;
        logoPreview.value = URL.createObjectURL(file);
    }
};

const uploadLogo = () => {
    if (logoForm.logo) {
        logoForm.post(route('bizboost.business.profile.update'), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                logoPreview.value = null;
            },
        });
    }
};

const submit = () => {
    form.put(route('bizboost.business.profile.update'), {
        preserveScroll: true,
    });
};

const provinces = [
    'Central', 'Copperbelt', 'Eastern', 'Luapula', 'Lusaka',
    'Muchinga', 'Northern', 'North-Western', 'Southern', 'Western'
];
</script>

<template>
    <Head title="Business Profile - BizBoost" />
    <BizBoostLayout title="Business Profile">
        <div class="max-w-3xl mx-auto space-y-6">
            <!-- Logo Section -->
            <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Business Logo</h3>
                <div class="flex items-center gap-6">
                    <div class="relative">
                        <div class="h-24 w-24 rounded-xl bg-gray-100 flex items-center justify-center overflow-hidden">
                            <img
                                v-if="logoPreview || business.logo_path"
                                :src="logoPreview || `/storage/${business.logo_path}`"
                                alt="Business logo"
                                class="h-full w-full object-cover"
                            />
                            <BuildingStorefrontIcon v-else class="h-12 w-12 text-gray-400" aria-hidden="true" />
                        </div>
                    </div>
                    <div>
                        <label class="cursor-pointer">
                            <span class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                                <PhotoIcon class="h-4 w-4" aria-hidden="true" />
                                Choose Logo
                            </span>
                            <input
                                type="file"
                                accept="image/*"
                                class="hidden"
                                @change="handleLogoChange"
                            />
                        </label>
                        <p class="text-xs text-gray-500 mt-2">PNG, JPG up to 2MB</p>
                        <button
                            v-if="logoPreview"
                            @click="uploadLogo"
                            :disabled="logoForm.processing"
                            class="mt-2 inline-flex items-center gap-2 px-3 py-1.5 bg-violet-600 text-white rounded-lg text-sm hover:bg-violet-700 disabled:opacity-50"
                        >
                            <CheckIcon class="h-4 w-4" aria-hidden="true" />
                            Save Logo
                        </button>
                    </div>
                </div>
            </div>

            <!-- Profile Form -->
            <form @submit.prevent="submit" class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-6 space-y-6">
                <h3 class="text-lg font-semibold text-gray-900">Business Information</h3>
                
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Business Name</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500"
                            required
                        />
                        <p v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</p>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="w-full rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500"
                            placeholder="Tell customers about your business..."
                        ></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Industry</label>
                        <select
                            v-model="form.industry"
                            class="w-full rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500"
                        >
                            <option v-for="(ind, key) in industries" :key="key" :value="key">
                                {{ ind.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input
                            v-model="form.phone"
                            type="tel"
                            class="w-full rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500"
                            placeholder="+260 97X XXX XXX"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">WhatsApp</label>
                        <input
                            v-model="form.whatsapp"
                            type="tel"
                            class="w-full rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500"
                            placeholder="+260 97X XXX XXX"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input
                            v-model="form.email"
                            type="email"
                            class="w-full rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500"
                        />
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Website</label>
                        <input
                            v-model="form.website"
                            type="url"
                            class="w-full rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500"
                            placeholder="https://..."
                        />
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <input
                            v-model="form.address"
                            type="text"
                            class="w-full rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                        <input
                            v-model="form.city"
                            type="text"
                            class="w-full rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                        <select
                            v-model="form.province"
                            class="w-full rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500"
                        >
                            <option value="">Select province</option>
                            <option v-for="prov in provinces" :key="prov" :value="prov">
                                {{ prov }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-200">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-2 bg-violet-600 text-white rounded-lg font-medium hover:bg-violet-700 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </form>
        </div>
    </BizBoostLayout>
</template>
