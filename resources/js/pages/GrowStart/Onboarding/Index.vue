<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    RocketLaunchIcon, 
    ArrowLeftIcon, 
    ArrowRightIcon,
    CheckIcon
} from '@heroicons/vue/24/outline';
import type { Industry, Country } from '@/types/growstart';

interface Props {
    industries: Industry[];
    countries: Country[];
}

const props = defineProps<Props>();

const currentStep = ref(1);
const totalSteps = 3;

const form = useForm({
    industry_id: null as number | null,
    country_id: null as number | null,
    business_name: '',
    business_description: '',
    target_launch_date: '',
    province: '',
    city: ''
});

const selectedIndustry = computed(() => 
    props.industries.find(i => i.id === form.industry_id)
);

const selectedCountry = computed(() => 
    props.countries.find(c => c.id === form.country_id)
);

const canProceed = computed(() => {
    switch (currentStep.value) {
        case 1: return form.industry_id !== null;
        case 2: return form.country_id !== null && form.business_name.trim().length >= 2;
        case 3: return true;
        default: return false;
    }
});

const nextStep = () => {
    if (currentStep.value < totalSteps && canProceed.value) {
        currentStep.value++;
    }
};

const prevStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
};

const submit = () => {
    form.post(route('growstart.onboarding.start'), {
        onSuccess: () => {
            router.visit(route('growstart.dashboard'));
        }
    });
};

const getIndustryIcon = (slug: string) => {
    const icons: Record<string, string> = {
        retail: '🛒',
        food_beverage: '🍽️',
        technology: '💻',
        agriculture: '🌾',
        manufacturing: '🏭',
        services: '🛠️',
        healthcare: '🏥',
        education: '📚',
        construction: '🏗️',
        transport: '🚚',
        other: '📦'
    };
    return icons[slug] || '📦';
};
</script>

<template>
    <Head title="GrowStart - Begin Your Journey" />
    
    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 py-8">
            <div class="max-w-3xl mx-auto px-4">
                <!-- Header -->
                <div class="text-center mb-8">
                    <RocketLaunchIcon class="mx-auto h-12 w-12 text-blue-600" aria-hidden="true" />
                    <h1 class="mt-4 text-2xl font-bold text-gray-900">Start Your Business Journey</h1>
                    <p class="mt-2 text-gray-600">Let's set up your personalized roadmap</p>
                </div>

                <!-- Progress Steps -->
                <div class="flex items-center justify-center mb-8">
                    <div class="flex items-center gap-2">
                        <template v-for="step in totalSteps" :key="step">
                            <div 
                                :class="[
                                    'w-10 h-10 rounded-full flex items-center justify-center font-medium transition',
                                    step < currentStep ? 'bg-emerald-500 text-white' :
                                    step === currentStep ? 'bg-blue-600 text-white' :
                                    'bg-gray-200 text-gray-500'
                                ]"
                            >
                                <CheckIcon v-if="step < currentStep" class="h-5 w-5" aria-hidden="true" />
                                <span v-else>{{ step }}</span>
                            </div>
                            <div 
                                v-if="step < totalSteps"
                                :class="[
                                    'w-16 h-1 rounded',
                                    step < currentStep ? 'bg-emerald-500' : 'bg-gray-200'
                                ]"
                            ></div>
                        </template>
                    </div>
                </div>

                <!-- Step Content -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <!-- Step 1: Select Industry -->
                    <div v-if="currentStep === 1">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">What type of business are you starting?</h2>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            <button
                                v-for="industry in industries"
                                :key="industry.id"
                                type="button"
                                @click="form.industry_id = industry.id"
                                :class="[
                                    'p-4 rounded-xl border-2 text-left transition',
                                    form.industry_id === industry.id 
                                        ? 'border-blue-500 bg-blue-50' 
                                        : 'border-gray-200 hover:border-gray-300'
                                ]"
                            >
                                <span class="text-2xl">{{ getIndustryIcon(industry.slug) }}</span>
                                <p class="mt-2 font-medium text-gray-900">{{ industry.name }}</p>
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Business Details -->
                    <div v-if="currentStep === 2">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Tell us about your business</h2>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                                <select
                                    v-model="form.country_id"
                                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                >
                                    <option :value="null">Select your country</option>
                                    <option v-for="country in countries" :key="country.id" :value="country.id">
                                        {{ country.name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Business Name</label>
                                <input
                                    v-model="form.business_name"
                                    type="text"
                                    placeholder="Enter your business name"
                                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description (optional)</label>
                                <textarea
                                    v-model="form.business_description"
                                    rows="3"
                                    placeholder="Briefly describe your business idea"
                                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                ></textarea>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Province/State</label>
                                    <input
                                        v-model="form.province"
                                        type="text"
                                        placeholder="e.g., Lusaka"
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">City/Town</label>
                                    <input
                                        v-model="form.city"
                                        type="text"
                                        placeholder="e.g., Lusaka"
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Goals -->
                    <div v-if="currentStep === 3">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Set your launch goal</h2>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Target Launch Date (optional)</label>
                                <input
                                    v-model="form.target_launch_date"
                                    type="date"
                                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                />
                                <p class="mt-1 text-sm text-gray-500">When do you want to launch your business?</p>
                            </div>

                            <!-- Summary -->
                            <div class="mt-6 p-4 bg-gray-50 rounded-xl">
                                <h3 class="font-medium text-gray-900 mb-3">Journey Summary</h3>
                                <dl class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500">Industry:</dt>
                                        <dd class="font-medium text-gray-900">{{ selectedIndustry?.name }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500">Country:</dt>
                                        <dd class="font-medium text-gray-900">{{ selectedCountry?.name }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500">Business:</dt>
                                        <dd class="font-medium text-gray-900">{{ form.business_name }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation -->
                    <div class="mt-8 flex items-center justify-between">
                        <button
                            v-if="currentStep > 1"
                            type="button"
                            @click="prevStep"
                            class="flex items-center gap-2 px-4 py-2 text-gray-600 hover:text-gray-900 transition"
                        >
                            <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                            Back
                        </button>
                        <div v-else></div>

                        <button
                            v-if="currentStep < totalSteps"
                            type="button"
                            @click="nextStep"
                            :disabled="!canProceed"
                            :class="[
                                'flex items-center gap-2 px-6 py-2 rounded-lg font-medium transition',
                                canProceed 
                                    ? 'bg-blue-600 text-white hover:bg-blue-700' 
                                    : 'bg-gray-200 text-gray-400 cursor-not-allowed'
                            ]"
                        >
                            Continue
                            <ArrowRightIcon class="h-4 w-4" aria-hidden="true" />
                        </button>
                        <button
                            v-else
                            type="button"
                            @click="submit"
                            :disabled="form.processing"
                            class="flex items-center gap-2 px-6 py-2 bg-emerald-600 text-white rounded-lg font-medium hover:bg-emerald-700 transition disabled:opacity-50"
                        >
                            <RocketLaunchIcon class="h-5 w-5" aria-hidden="true" />
                            Start My Journey
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
