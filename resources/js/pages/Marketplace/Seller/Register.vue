<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import MarketplaceLayout from '@/layouts/MarketplaceLayout.vue';
import {
    BuildingStorefrontIcon,
    DocumentTextIcon,
    ShieldCheckIcon,
    ArrowRightIcon,
} from '@heroicons/vue/24/outline';

defineProps<{
    provinces: string[];
}>();

const form = useForm({
    business_name: '',
    business_type: 'individual',
    province: '',
    district: '',
    phone: '',
    email: '',
    description: '',
    nrc_front: null as File | null,
    nrc_back: null as File | null,
    business_cert: null as File | null,
});

const step = ref(1);

const handleFileChange = (field: 'nrc_front' | 'nrc_back' | 'business_cert', event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form[field] = target.files[0];
    }
};

const nextStep = () => {
    if (step.value < 3) step.value++;
};

const prevStep = () => {
    if (step.value > 1) step.value--;
};

const submit = () => {
    form.post(route('marketplace.seller.store'), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Become a Seller - Marketplace" />
    
    <MarketplaceLayout>
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-orange-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <BuildingStorefrontIcon class="h-8 w-8 text-orange-600" aria-hidden="true" />
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Become a Seller</h1>
                <p class="text-gray-500">Join thousands of sellers on MyGrowNet Marketplace</p>
            </div>

            <!-- Progress Steps -->
            <div class="flex items-center justify-center gap-4 mb-8">
                <div 
                    v-for="s in 3" 
                    :key="s"
                    :class="[
                        'flex items-center gap-2',
                        s <= step ? 'text-orange-600' : 'text-gray-400'
                    ]"
                >
                    <div :class="[
                        'w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium',
                        s < step ? 'bg-orange-500 text-white' :
                        s === step ? 'bg-orange-100 text-orange-600 border-2 border-orange-500' :
                        'bg-gray-100 text-gray-400'
                    ]">
                        {{ s }}
                    </div>
                    <span v-if="s < 3" class="hidden sm:block text-sm">
                        {{ s === 1 ? 'Business Info' : s === 2 ? 'Contact' : 'Verification' }}
                    </span>
                    <div v-if="s < 3" class="w-8 h-0.5 bg-gray-200"></div>
                </div>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-xl border border-gray-200 p-6">
                <!-- Step 1: Business Info -->
                <div v-show="step === 1" class="space-y-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Business Information</h2>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Business Name *</label>
                        <input 
                            v-model="form.business_name"
                            type="text"
                            class="w-full border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500"
                            placeholder="Your business or shop name"
                        />
                        <p v-if="form.errors.business_name" class="mt-1 text-sm text-red-600">{{ form.errors.business_name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Business Type *</label>
                        <div class="grid grid-cols-2 gap-4">
                            <label :class="[
                                'flex items-center gap-3 p-4 border rounded-lg cursor-pointer transition-colors',
                                form.business_type === 'individual' ? 'border-orange-500 bg-orange-50' : 'border-gray-200 hover:border-gray-300'
                            ]">
                                <input type="radio" v-model="form.business_type" value="individual" class="text-orange-500" />
                                <div>
                                    <p class="font-medium text-gray-900">Individual</p>
                                    <p class="text-sm text-gray-500">Selling as yourself</p>
                                </div>
                            </label>
                            <label :class="[
                                'flex items-center gap-3 p-4 border rounded-lg cursor-pointer transition-colors',
                                form.business_type === 'registered' ? 'border-orange-500 bg-orange-50' : 'border-gray-200 hover:border-gray-300'
                            ]">
                                <input type="radio" v-model="form.business_type" value="registered" class="text-orange-500" />
                                <div>
                                    <p class="font-medium text-gray-900">Registered</p>
                                    <p class="text-sm text-gray-500">Registered business</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Province *</label>
                            <select 
                                v-model="form.province"
                                class="w-full border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500"
                            >
                                <option value="">Select province</option>
                                <option v-for="province in provinces" :key="province" :value="province">
                                    {{ province }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">District *</label>
                            <input 
                                v-model="form.district"
                                type="text"
                                class="w-full border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500"
                                placeholder="Your district"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea 
                            v-model="form.description"
                            rows="3"
                            class="w-full border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500"
                            placeholder="Tell buyers about your business..."
                        ></textarea>
                    </div>
                </div>

                <!-- Step 2: Contact -->
                <div v-show="step === 2" class="space-y-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Contact Information</h2>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label>
                        <input 
                            v-model="form.phone"
                            type="tel"
                            class="w-full border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500"
                            placeholder="0977123456"
                        />
                        <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email (Optional)</label>
                        <input 
                            v-model="form.email"
                            type="email"
                            class="w-full border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500"
                            placeholder="your@email.com"
                        />
                    </div>
                </div>

                <!-- Step 3: Verification -->
                <div v-show="step === 3" class="space-y-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Verification Documents</h2>
                    
                    <div class="p-4 bg-blue-50 rounded-lg mb-6">
                        <div class="flex items-start gap-3">
                            <ShieldCheckIcon class="h-6 w-6 text-blue-600 flex-shrink-0" aria-hidden="true" />
                            <div>
                                <p class="font-medium text-blue-900">Why we need this</p>
                                <p class="text-sm text-blue-700">Verification helps build trust with buyers and protects everyone on the platform.</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">NRC Front *</label>
                        <input 
                            type="file"
                            accept="image/*"
                            @change="(e) => handleFileChange('nrc_front', e)"
                            class="w-full border border-gray-300 rounded-lg p-2"
                        />
                        <p v-if="form.errors.nrc_front" class="mt-1 text-sm text-red-600">{{ form.errors.nrc_front }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">NRC Back *</label>
                        <input 
                            type="file"
                            accept="image/*"
                            @change="(e) => handleFileChange('nrc_back', e)"
                            class="w-full border border-gray-300 rounded-lg p-2"
                        />
                        <p v-if="form.errors.nrc_back" class="mt-1 text-sm text-red-600">{{ form.errors.nrc_back }}</p>
                    </div>

                    <div v-if="form.business_type === 'registered'">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Business Registration Certificate</label>
                        <input 
                            type="file"
                            accept="image/*,.pdf"
                            @change="(e) => handleFileChange('business_cert', e)"
                            class="w-full border border-gray-300 rounded-lg p-2"
                        />
                    </div>
                </div>

                <!-- Navigation -->
                <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-200">
                    <button 
                        v-if="step > 1"
                        type="button"
                        @click="prevStep"
                        class="px-6 py-2 text-gray-700 hover:text-gray-900"
                    >
                        Back
                    </button>
                    <div v-else></div>

                    <button 
                        v-if="step < 3"
                        type="button"
                        @click="nextStep"
                        class="flex items-center gap-2 px-6 py-2 bg-orange-500 text-white font-medium rounded-lg hover:bg-orange-600"
                    >
                        Continue
                        <ArrowRightIcon class="h-5 w-5" aria-hidden="true" />
                    </button>
                    <button 
                        v-else
                        type="submit"
                        :disabled="form.processing"
                        class="flex items-center gap-2 px-6 py-2 bg-orange-500 text-white font-medium rounded-lg hover:bg-orange-600 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Submitting...' : 'Submit Application' }}
                    </button>
                </div>
            </form>
        </div>
    </MarketplaceLayout>
</template>
