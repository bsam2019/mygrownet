<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import MarketplaceLayout from '@/layouts/MarketplaceLayout.vue';
import {
    CameraIcon,
    PhotoIcon,
    ArrowLeftIcon,
    CheckCircleIcon,
    ExclamationCircleIcon,
} from '@heroicons/vue/24/outline';

interface Seller {
    id: number;
    business_name: string;
    business_type: string;
    description: string | null;
    province: string;
    district: string | null;
    phone: string;
    email: string | null;
    trust_level: string;
    kyc_status: string;
    total_orders: number;
    rating: number | null;
    logo_url: string | null;
    cover_image_url: string | null;
}

interface Props {
    seller: Seller;
    provinces: string[];
}

const props = defineProps<Props>();

const form = useForm({
    business_name: props.seller.business_name,
    description: props.seller.description || '',
    phone: props.seller.phone,
    email: props.seller.email || '',
});

const logoPreview = ref<string | null>(props.seller.logo_url);
const coverPreview = ref<string | null>(props.seller.cover_image_url);
const uploadingLogo = ref(false);
const uploadingCover = ref(false);

const submit = () => {
    form.put(route('marketplace.seller.profile.update'));
};

const handleLogoUpload = async (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (!input.files?.length) return;
    
    const file = input.files[0];
    if (file.size > 2 * 1024 * 1024) {
        alert('Logo must be less than 2MB');
        return;
    }
    
    logoPreview.value = URL.createObjectURL(file);
    uploadingLogo.value = true;
    
    const formData = new FormData();
    formData.append('logo', file);
    
    router.post(route('marketplace.seller.profile.upload-logo'), formData, {
        forceFormData: true,
        preserveScroll: true,
        onFinish: () => {
            uploadingLogo.value = false;
        },
    });
};

const handleCoverUpload = async (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (!input.files?.length) return;
    
    const file = input.files[0];
    if (file.size > 5 * 1024 * 1024) {
        alert('Cover image must be less than 5MB');
        return;
    }
    
    coverPreview.value = URL.createObjectURL(file);
    uploadingCover.value = true;
    
    const formData = new FormData();
    formData.append('cover_image', file);
    
    router.post(route('marketplace.seller.profile.upload-cover'), formData, {
        forceFormData: true,
        preserveScroll: true,
        onFinish: () => {
            uploadingCover.value = false;
        },
    });
};

const getTrustBadge = (level: string) => {
    const badges: Record<string, { icon: string; label: string; color: string }> = {
        'new': { icon: '🆕', label: 'New Seller', color: 'text-gray-600' },
        'verified': { icon: '✓', label: 'Verified Seller', color: 'text-blue-600' },
        'trusted': { icon: '⭐', label: 'Trusted Seller', color: 'text-amber-600' },
        'top': { icon: '👑', label: 'Top Seller', color: 'text-purple-600' },
    };
    return badges[level] || badges['new'];
};

const getKycBadge = (status: string) => {
    const badges: Record<string, { label: string; color: string; bg: string }> = {
        'pending': { label: 'Pending Review', color: 'text-amber-700', bg: 'bg-amber-100' },
        'approved': { label: 'Approved', color: 'text-green-700', bg: 'bg-green-100' },
        'rejected': { label: 'Rejected', color: 'text-red-700', bg: 'bg-red-100' },
    };
    return badges[status] || badges['pending'];
};
</script>

<template>
    <Head title="Seller Profile - Marketplace" />
    
    <MarketplaceLayout>
        <div class="bg-gray-50 min-h-screen py-8">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <Link
                        :href="route('marketplace.seller.dashboard')"
                        class="text-orange-600 hover:text-orange-700 text-sm font-medium flex items-center gap-1 mb-4"
                    >
                        <ArrowLeftIcon class="w-4 h-4" aria-hidden="true" />
                        Back to Dashboard
                    </Link>
                    <h1 class="text-2xl font-bold text-gray-900">Store Settings</h1>
                    <p class="text-gray-500 mt-1">Customize your shop appearance and business information</p>
                </div>

                <!-- Shop Appearance Section -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Shop Appearance</h2>
                        <p class="text-sm text-gray-500 mt-1">Customize how your shop looks to customers</p>
                    </div>
                    
                    <!-- Cover Image -->
                    <div class="relative">
                        <div class="h-48 bg-gradient-to-r from-orange-500 to-amber-500 relative overflow-hidden">
                            <img 
                                v-if="coverPreview"
                                :src="coverPreview"
                                alt="Cover image"
                                class="w-full h-full object-cover"
                            />
                            <div v-else class="absolute inset-0 flex items-center justify-center">
                                <div class="text-center text-white/80">
                                    <PhotoIcon class="h-12 w-12 mx-auto mb-2" aria-hidden="true" />
                                    <p class="text-sm">Add a cover image to make your shop stand out</p>
                                </div>
                            </div>
                            
                            <!-- Cover Upload Button -->
                            <label class="absolute bottom-4 right-4 cursor-pointer">
                                <input 
                                    type="file" 
                                    accept="image/*" 
                                    class="hidden" 
                                    @change="handleCoverUpload"
                                    :disabled="uploadingCover"
                                />
                                <span class="inline-flex items-center gap-2 px-4 py-2 bg-white/90 backdrop-blur-sm text-gray-700 rounded-lg text-sm font-medium hover:bg-white transition-colors shadow-lg">
                                    <CameraIcon class="h-4 w-4" aria-hidden="true" />
                                    {{ uploadingCover ? 'Uploading...' : 'Change Cover' }}
                                </span>
                            </label>
                        </div>
                        
                        <!-- Logo -->
                        <div class="absolute -bottom-12 left-6">
                            <div class="relative">
                                <div class="w-24 h-24 bg-white rounded-xl shadow-lg border-4 border-white overflow-hidden flex items-center justify-center">
                                    <img 
                                        v-if="logoPreview"
                                        :src="logoPreview"
                                        alt="Shop logo"
                                        class="w-full h-full object-cover"
                                    />
                                    <span v-else class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-br from-orange-500 to-amber-600">
                                        {{ seller.business_name.charAt(0) }}
                                    </span>
                                </div>
                                
                                <!-- Logo Upload Button -->
                                <label class="absolute -bottom-1 -right-1 cursor-pointer">
                                    <input 
                                        type="file" 
                                        accept="image/*" 
                                        class="hidden" 
                                        @change="handleLogoUpload"
                                        :disabled="uploadingLogo"
                                    />
                                    <span class="flex items-center justify-center w-8 h-8 bg-orange-500 text-white rounded-full shadow-lg hover:bg-orange-600 transition-colors">
                                        <CameraIcon class="h-4 w-4" aria-hidden="true" />
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="pt-16 pb-6 px-6">
                        <p class="text-sm text-gray-500">
                            <strong>Logo:</strong> Square image, max 2MB. <strong>Cover:</strong> 1200x400px recommended, max 5MB.
                        </p>
                    </div>
                </div>

                <!-- Status Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                    <!-- Trust Level -->
                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <div class="text-sm text-gray-500 mb-1">Trust Level</div>
                        <div class="flex items-center gap-2">
                            <span :class="getTrustBadge(seller.trust_level).color" class="text-xl">
                                {{ getTrustBadge(seller.trust_level).icon }}
                            </span>
                            <span class="font-semibold text-gray-900">
                                {{ getTrustBadge(seller.trust_level).label }}
                            </span>
                        </div>
                    </div>

                    <!-- KYC Status -->
                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <div class="text-sm text-gray-500 mb-1">KYC Status</div>
                        <span
                            :class="[getKycBadge(seller.kyc_status).bg, getKycBadge(seller.kyc_status).color]"
                            class="inline-flex px-3 py-1 rounded-full text-sm font-medium"
                        >
                            {{ getKycBadge(seller.kyc_status).label }}
                        </span>
                    </div>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-2 gap-4 mb-8">
                    <div class="bg-white rounded-lg shadow-sm p-4 text-center">
                        <div class="text-2xl font-bold text-amber-600">{{ seller.total_orders }}</div>
                        <div class="text-sm text-gray-500">Total Orders</div>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-4 text-center">
                        <div class="text-2xl font-bold text-amber-600">
                            {{ seller.rating ? `⭐ ${seller.rating.toFixed(1)}` : 'N/A' }}
                        </div>
                        <div class="text-sm text-gray-500">Rating</div>
                    </div>
                </div>

                <!-- Profile Form -->
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Business Info -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Business Information</h2>
                        
                        <div class="space-y-4">
                            <!-- Business Name -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Business Name *
                                </label>
                                <input
                                    v-model="form.business_name"
                                    type="text"
                                    required
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                                />
                                <p v-if="form.errors.business_name" class="mt-1 text-sm text-red-600">{{ form.errors.business_name }}</p>
                            </div>

                            <!-- Description -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Business Description
                                </label>
                                <textarea
                                    v-model="form.description"
                                    rows="3"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                                    placeholder="Tell buyers about your business..."
                                ></textarea>
                                <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Location Info (Read-only) -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Location</h2>
                        <p class="text-sm text-gray-500 mb-4">Location cannot be changed. Contact support if you need to update.</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                                <div class="px-3 py-2 bg-gray-100 rounded-lg text-gray-700">{{ seller.province }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">District</label>
                                <div class="px-3 py-2 bg-gray-100 rounded-lg text-gray-700">{{ seller.district || 'Not specified' }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Contact Information</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Phone -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Phone Number *
                                </label>
                                <input
                                    v-model="form.phone"
                                    type="tel"
                                    required
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                                    placeholder="+260 97X XXX XXX"
                                />
                                <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Email Address
                                </label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                                    placeholder="business@example.com"
                                />
                                <p class="mt-1 text-xs text-gray-500">For order notifications</p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="flex items-center justify-end gap-4">
                        <Link
                            :href="route('marketplace.seller.dashboard')"
                            class="px-6 py-2 text-gray-700 hover:text-gray-900"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                        >
                            <svg v-if="form.processing" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </MarketplaceLayout>
</template>
