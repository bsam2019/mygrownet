<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { 
    BuildingOffice2Icon, 
    UserGroupIcon,
    QrCodeIcon,
    ArrowRightIcon,
    ArrowLeftIcon,
    CheckCircleIcon,
    BriefcaseIcon,
    UserIcon,
    Cog6ToothIcon,
    SparklesIcon
} from '@heroicons/vue/24/outline';

interface Props {
    user: {
        id: number;
        name: string;
        email: string;
    };
}

const props = defineProps<Props>();

// Wizard state
const currentStep = ref(1);
const totalSteps = 4;
const selectedRole = ref<'business' | 'employee' | null>(null);

// Form data
const businessForm = useForm({
    business_name: '',
    industry: '',
    team_size: '',
    owner_title: '',
});

// Industry options
const industries = [
    { value: 'retail', label: 'Retail & Commerce' },
    { value: 'services', label: 'Professional Services' },
    { value: 'technology', label: 'Technology & IT' },
    { value: 'manufacturing', label: 'Manufacturing' },
    { value: 'construction', label: 'Construction' },
    { value: 'hospitality', label: 'Hospitality & Food' },
    { value: 'healthcare', label: 'Healthcare' },
    { value: 'education', label: 'Education & Training' },
    { value: 'agriculture', label: 'Agriculture' },
    { value: 'transport', label: 'Transport & Logistics' },
    { value: 'other', label: 'Other' },
];

// Team size options
const teamSizes = [
    { value: '1-5', label: '1-5 employees' },
    { value: '6-15', label: '6-15 employees' },
    { value: '16-50', label: '16-50 employees' },
    { value: '51-100', label: '51-100 employees' },
    { value: '100+', label: '100+ employees' },
];

// Title options
const titles = [
    { value: 'owner', label: 'Owner' },
    { value: 'ceo', label: 'CEO / Managing Director' },
    { value: 'manager', label: 'Manager' },
    { value: 'director', label: 'Director' },
    { value: 'supervisor', label: 'Supervisor' },
    { value: 'other', label: 'Other' },
];

const progress = computed(() => (currentStep.value / totalSteps) * 100);

const canProceed = computed(() => {
    if (currentStep.value === 1) return selectedRole.value !== null;
    if (currentStep.value === 2) return businessForm.business_name.trim() !== '';
    if (currentStep.value === 3) return true; // Optional fields
    return true;
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

const completeSetup = () => {
    businessForm.post(route('growbiz.setup.business'), {
        onSuccess: () => {
            // Redirect handled by controller
        },
    });
};

const goToCodeEntry = () => {
    router.visit(route('growbiz.invitation.code'));
};
</script>

<template>
    <Head title="GrowBiz Setup" />
    
    <div class="min-h-screen bg-gradient-to-br from-emerald-50 via-white to-teal-50">
        <!-- Progress Bar -->
        <div class="fixed top-0 left-0 right-0 h-1 bg-gray-200 z-50">
            <div 
                class="h-full bg-gradient-to-r from-emerald-500 to-teal-500 transition-all duration-500 ease-out"
                :style="{ width: `${progress}%` }"
            />
        </div>

        <div class="min-h-screen flex items-center justify-center p-4 pt-8">
            <div class="w-full max-w-2xl">
                <!-- Logo & Header -->
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-emerald-500/30">
                        <BriefcaseIcon class="w-8 h-8 text-white" />
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900">Welcome to GrowBiz</h1>
                    <p class="text-gray-500 mt-1">Let's set up your workspace</p>
                </div>

                <!-- Step Indicators -->
                <div v-if="selectedRole === 'business'" class="flex justify-center gap-2 mb-8">
                    <div 
                        v-for="step in totalSteps" 
                        :key="step"
                        class="flex items-center"
                    >
                        <div 
                            :class="[
                                'w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium transition-all duration-300',
                                currentStep >= step 
                                    ? 'bg-emerald-500 text-white' 
                                    : 'bg-gray-200 text-gray-500'
                            ]"
                        >
                            <CheckCircleIcon v-if="currentStep > step" class="w-5 h-5" />
                            <span v-else>{{ step }}</span>
                        </div>
                        <div 
                            v-if="step < totalSteps"
                            :class="[
                                'w-12 h-0.5 mx-1 transition-all duration-300',
                                currentStep > step ? 'bg-emerald-500' : 'bg-gray-200'
                            ]"
                        />
                    </div>
                </div>

                <!-- Step 1: Role Selection -->
                <Transition
                    enter-active-class="transition-all duration-300 ease-out"
                    enter-from-class="opacity-0 translate-x-8"
                    enter-to-class="opacity-100 translate-x-0"
                    leave-active-class="transition-all duration-200 ease-in"
                    leave-from-class="opacity-100 translate-x-0"
                    leave-to-class="opacity-0 -translate-x-8"
                    mode="out-in"
                >
                    <div v-if="currentStep === 1 && !selectedRole" key="step1" class="space-y-4">
                        <p class="text-center text-gray-700 mb-6 text-lg">How will you be using GrowBiz?</p>
                        
                        <!-- Business Owner Option -->
                        <button
                            @click="selectedRole = 'business'; nextStep()"
                            class="w-full bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 text-left group border-2 border-transparent hover:border-emerald-200"
                        >
                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-xl group-hover:from-emerald-200 group-hover:to-teal-200 transition-colors">
                                    <BuildingOffice2Icon class="w-8 h-8 text-emerald-600" />
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-1">I'm a Business Owner / Manager</h3>
                                    <p class="text-gray-600 text-sm mb-3">
                                        Set up your business, add team members, and manage tasks
                                    </p>
                                    <div class="flex flex-wrap gap-2">
                                        <span class="px-2 py-1 bg-emerald-50 text-emerald-700 text-xs rounded-full">Create Tasks</span>
                                        <span class="px-2 py-1 bg-emerald-50 text-emerald-700 text-xs rounded-full">Manage Team</span>
                                        <span class="px-2 py-1 bg-emerald-50 text-emerald-700 text-xs rounded-full">Track Progress</span>
                                    </div>
                                </div>
                                <ArrowRightIcon class="w-5 h-5 text-gray-400 group-hover:text-emerald-600 transition-colors mt-2" />
                            </div>
                        </button>

                        <!-- Employee Option -->
                        <button
                            @click="selectedRole = 'employee'"
                            class="w-full bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 text-left group border-2 border-transparent hover:border-blue-200"
                        >
                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-xl group-hover:from-blue-200 group-hover:to-indigo-200 transition-colors">
                                    <UserGroupIcon class="w-8 h-8 text-blue-600" />
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-1">I'm Joining a Team</h3>
                                    <p class="text-gray-600 text-sm mb-3">
                                        I have an invitation code from my employer
                                    </p>
                                    <div class="flex flex-wrap gap-2">
                                        <span class="px-2 py-1 bg-blue-50 text-blue-700 text-xs rounded-full">View Tasks</span>
                                        <span class="px-2 py-1 bg-blue-50 text-blue-700 text-xs rounded-full">Update Progress</span>
                                        <span class="px-2 py-1 bg-blue-50 text-blue-700 text-xs rounded-full">Collaborate</span>
                                    </div>
                                </div>
                                <ArrowRightIcon class="w-5 h-5 text-gray-400 group-hover:text-blue-600 transition-colors mt-2" />
                            </div>
                        </button>
                    </div>

                    <!-- Employee Flow - Code Entry -->
                    <div v-else-if="selectedRole === 'employee'" key="employee" class="bg-white rounded-2xl p-8 shadow-xl">
                        <button 
                            @click="selectedRole = null"
                            class="flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 mb-6"
                        >
                            <ArrowLeftIcon class="w-4 h-4" />
                            Back
                        </button>
                        
                        <div class="text-center">
                            <div class="w-16 h-16 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <QrCodeIcon class="w-8 h-8 text-blue-600" />
                            </div>
                            <h2 class="text-xl font-semibold text-gray-900 mb-2">Join Your Team</h2>
                            <p class="text-gray-600 mb-8">
                                Enter the 6-character invitation code provided by your employer
                            </p>
                            
                            <button
                                @click="goToCodeEntry"
                                class="w-full py-4 px-6 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-semibold hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg shadow-blue-500/30 flex items-center justify-center gap-2"
                            >
                                <QrCodeIcon class="w-5 h-5" />
                                Enter Invitation Code
                            </button>
                        </div>

                        <div class="mt-8 p-4 bg-gray-50 rounded-xl">
                            <p class="text-sm text-gray-600 text-center">
                                <span class="font-medium">Don't have a code?</span><br>
                                Ask your employer to send you an invitation from their GrowBiz dashboard.
                            </p>
                        </div>
                    </div>

                    <!-- Step 2: Business Name -->
                    <div v-else-if="currentStep === 2" key="step2" class="bg-white rounded-2xl p-8 shadow-xl">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2 bg-emerald-100 rounded-xl">
                                <BuildingOffice2Icon class="w-6 h-6 text-emerald-600" />
                            </div>
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900">Your Business</h2>
                                <p class="text-sm text-gray-500">Tell us about your business</p>
                            </div>
                        </div>

                        <div class="space-y-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Business Name <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="businessForm.business_name"
                                    type="text"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all"
                                    placeholder="e.g., Acme Solutions Ltd"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Industry
                                </label>
                                <select
                                    v-model="businessForm.industry"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all"
                                >
                                    <option value="">Select your industry</option>
                                    <option v-for="ind in industries" :key="ind.value" :value="ind.value">
                                        {{ ind.label }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Team Size & Role -->
                    <div v-else-if="currentStep === 3" key="step3" class="bg-white rounded-2xl p-8 shadow-xl">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2 bg-emerald-100 rounded-xl">
                                <UserGroupIcon class="w-6 h-6 text-emerald-600" />
                            </div>
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900">Your Team</h2>
                                <p class="text-sm text-gray-500">Help us customize your experience</p>
                            </div>
                        </div>

                        <div class="space-y-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Team Size
                                </label>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                    <button
                                        v-for="size in teamSizes"
                                        :key="size.value"
                                        @click="businessForm.team_size = size.value"
                                        :class="[
                                            'px-4 py-3 rounded-xl text-sm font-medium transition-all border-2',
                                            businessForm.team_size === size.value
                                                ? 'border-emerald-500 bg-emerald-50 text-emerald-700'
                                                : 'border-gray-200 hover:border-gray-300 text-gray-700'
                                        ]"
                                    >
                                        {{ size.label }}
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Your Role
                                </label>
                                <select
                                    v-model="businessForm.owner_title"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all"
                                >
                                    <option value="">Select your role</option>
                                    <option v-for="title in titles" :key="title.value" :value="title.value">
                                        {{ title.label }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Ready to Go -->
                    <div v-else-if="currentStep === 4" key="step4" class="bg-white rounded-2xl p-8 shadow-xl text-center">
                        <div class="w-20 h-20 bg-gradient-to-br from-emerald-400 to-teal-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg shadow-emerald-500/30">
                            <SparklesIcon class="w-10 h-10 text-white" />
                        </div>
                        
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">You're All Set!</h2>
                        <p class="text-gray-600 mb-8">
                            Your GrowBiz workspace is ready. Start by adding your first team member or creating a task.
                        </p>

                        <div class="bg-gradient-to-r from-emerald-50 to-teal-50 rounded-xl p-6 mb-8">
                            <div class="flex items-center justify-center gap-4 text-left">
                                <div class="flex-1 border-r border-emerald-200 pr-4">
                                    <p class="text-2xl font-bold text-emerald-600">{{ businessForm.business_name || 'Your Business' }}</p>
                                    <p class="text-sm text-gray-500">Business Name</p>
                                </div>
                                <div class="flex-1 pl-4">
                                    <p class="text-2xl font-bold text-emerald-600">{{ businessForm.team_size || 'Any' }}</p>
                                    <p class="text-sm text-gray-500">Team Size</p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <button
                                @click="completeSetup"
                                :disabled="businessForm.processing"
                                class="w-full py-4 px-6 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl font-semibold hover:from-emerald-700 hover:to-teal-700 transition-all shadow-lg shadow-emerald-500/30 disabled:opacity-50 flex items-center justify-center gap-2"
                            >
                                {{ businessForm.processing ? 'Setting up...' : 'Launch GrowBiz' }}
                                <ArrowRightIcon class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                </Transition>

                <!-- Navigation Buttons (for business flow) -->
                <div v-if="selectedRole === 'business' && currentStep > 1 && currentStep < 4" class="flex justify-between mt-6">
                    <button
                        @click="prevStep"
                        class="flex items-center gap-2 px-6 py-3 text-gray-600 hover:text-gray-900 transition-colors"
                    >
                        <ArrowLeftIcon class="w-5 h-5" />
                        Back
                    </button>
                    <button
                        @click="nextStep"
                        :disabled="!canProceed"
                        class="flex items-center gap-2 px-6 py-3 bg-emerald-600 text-white rounded-xl font-medium hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                    >
                        Continue
                        <ArrowRightIcon class="w-5 h-5" />
                    </button>
                </div>

                <!-- Skip for now (on optional steps) -->
                <div v-if="selectedRole === 'business' && currentStep === 3" class="text-center mt-4">
                    <button
                        @click="nextStep"
                        class="text-sm text-gray-500 hover:text-gray-700 underline"
                    >
                        Skip for now
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
