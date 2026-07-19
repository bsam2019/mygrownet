<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import {
    CheckCircleIcon,
    BuildingOfficeIcon,
    Cog6ToothIcon,
    UsersIcon,
    BriefcaseIcon,
    ArrowRightIcon,
    ArrowLeftIcon,
    SparklesIcon,
    PlayCircleIcon,
    InformationCircleIcon,
} from '@heroicons/vue/24/outline';

interface Props {
    company: any;
    status: {
        completed: boolean;
        progress: Record<number, any>;
        current_step: number;
        steps: Array<{
            id: number;
            name: string;
            description: string;
            required: boolean;
        }>;
    };
    presets: any[];
}

const props = defineProps<Props>();

const currentStep = ref(props.status.current_step);
const isLoading = ref(false);
const showCelebration = ref(false);
const sampleDataGenerated = ref(false);
const showVideoModal = ref(false);
const currentVideoUrl = ref('');

// Auto-save timer
let autoSaveTimer: ReturnType<typeof setTimeout> | null = null;

// Step 1: Company Information
const companyForm = ref({
    name: props.company.name || '',
    business_registration_number: props.company.business_registration_number || '',
    tax_number: props.company.tax_number || '',
    address: props.company.address || '',
    city: props.company.city || '',
    country: props.company.country || 'Zambia',
    phone: props.company.phone || '',
    email: props.company.email || '',
    website: props.company.website || '',
});

// Step 2: Industry Selection
const selectedIndustry = ref(props.company.industry_type || '');

// Step 3: Business Settings
const settingsForm = ref({
    business_hours: {
        monday: { enabled: true, open: '08:00', close: '17:00' },
        tuesday: { enabled: true, open: '08:00', close: '17:00' },
        wednesday: { enabled: true, open: '08:00', close: '17:00' },
        thursday: { enabled: true, open: '08:00', close: '17:00' },
        friday: { enabled: true, open: '08:00', close: '17:00' },
        saturday: { enabled: false, open: '08:00', close: '13:00' },
        sunday: { enabled: false, open: '08:00', close: '13:00' },
    },
    tax: {
        enabled: true,
        default_rate: 16,
        tax_label: 'VAT',
        tax_number: companyForm.value.tax_number || '',
        inclusive: false,
    },
    approval_thresholds: {
        expense_approval_required: true,
        expense_auto_approve_limit: 500,
        quotation_approval_required: true,
        quotation_auto_approve_limit: 5000,
        payment_approval_required: false,
        payment_auto_approve_limit: 10000,
    },
});

const stepIcons = [
    BuildingOfficeIcon,
    BriefcaseIcon,
    Cog6ToothIcon,
    UsersIcon,
    SparklesIcon,
];

// Video tutorial URLs (YouTube embeds)
const videoTutorials: Record<number, { title: string; url: string }> = {
    1: {
        title: 'Setting Up Your Company Profile',
        url: 'https://www.youtube.com/embed/dQw4w9WgXcQ',
    },
    2: {
        title: 'Choosing the Right Industry Preset',
        url: 'https://www.youtube.com/embed/dQw4w9WgXcQ',
    },
    3: {
        title: 'Configuring Business Settings',
        url: 'https://www.youtube.com/embed/dQw4w9WgXcQ',
    },
};

// Tooltips for each step
const tooltips: Record<number, Record<string, string>> = {
    1: {
        name: 'Your official business name as registered',
        tax_number: 'Your TPIN (Taxpayer Identification Number) from ZRA',
        business_registration_number: 'Your PACRA registration number',
    },
    2: {
        industry: 'Select the industry that best matches your business type.',
    },
    3: {
        business_hours: 'Set your operating hours for scheduling',
        tax: 'Configure VAT settings for invoices',
    },
};

const isStepCompleted = (stepId: number) => {
    return props.status.progress[stepId]?.completed || false;
};

const canProceed = computed(() => {
    switch (currentStep.value) {
        case 1:
            return companyForm.value.name.trim().length > 0;
        case 2:
            return selectedIndustry.value.length > 0;
        case 3:
            return true;
        default:
            return true;
    }
});

// Auto-save functionality
const scheduleAutoSave = () => {
    if (autoSaveTimer) {
        clearTimeout(autoSaveTimer);
    }
    autoSaveTimer = setTimeout(() => {
        saveProgressToServer();
    }, 30000);
};

const saveProgressToServer = async () => {
    let formData: any = {};
    switch (currentStep.value) {
        case 1:
            formData = companyForm.value;
            break;
        case 2:
            formData = { industry: selectedIndustry.value };
            break;
        case 3:
            formData = settingsForm.value;
            break;
    }
    try {
        await fetch(route('cms.onboarding.save-progress'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                step_id: currentStep.value,
                form_data: formData,
            }),
        });
    } catch (error) {
        console.error('Auto-save failed:', error);
    }
};

const loadSavedProgress = async () => {
    try {
        const response = await fetch(
            route('cms.onboarding.saved-progress', { step_id: currentStep.value })
        );
        const data = await response.json();
        if (data.success && data.data) {
            switch (currentStep.value) {
                case 1:
                    companyForm.value = { ...companyForm.value, ...data.data };
                    break;
                case 2:
                    if (data.data.industry) selectedIndustry.value = data.data.industry;
                    break;
                case 3:
                    settingsForm.value = { ...settingsForm.value, ...data.data };
                    break;
            }
        }
    } catch (error) {
        console.error('Failed to load saved progress:', error);
    }
};

watch([companyForm, selectedIndustry, settingsForm], () => {
    scheduleAutoSave();
}, { deep: true });

onMounted(() => {
    loadSavedProgress();
});

onUnmounted(() => {
    if (autoSaveTimer) {
        clearTimeout(autoSaveTimer);
    }
});

const saveStep1 = async () => {
    isLoading.value = true;
    try {
        const response = await fetch(route('cms.onboarding.company-info'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify(companyForm.value),
        });
        const data = await response.json();
        if (data.success) {
            currentStep.value = 2;
            loadSavedProgress();
        } else {
            alert('Failed to save company information');
        }
    } catch (error) {
        console.error('Error saving company info:', error);
        alert('An error occurred. Please try again.');
    } finally {
        isLoading.value = false;
    }
};

const saveStep2 = async () => {
    isLoading.value = true;
    try {
        const response = await fetch(route('cms.onboarding.apply-preset'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({ preset_code: selectedIndustry.value }),
        });
        const data = await response.json();
        if (data.success) {
            currentStep.value = 3;
            loadSavedProgress();
        } else {
            alert('Failed to apply industry preset');
        }
    } catch (error) {
        console.error('Error applying preset:', error);
        alert('An error occurred. Please try again.');
    } finally {
        isLoading.value = false;
    }
};

const saveStep3 = async () => {
    isLoading.value = true;
    try {
        const response = await fetch(route('cms.onboarding.configure-settings'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify(settingsForm.value),
        });
        const data = await response.json();
        if (data.success) {
            currentStep.value = 4;
        } else {
            alert('Failed to configure settings');
        }
    } catch (error) {
        console.error('Error configuring settings:', error);
        alert('An error occurred. Please try again.');
    } finally {
        isLoading.value = false;
    }
};

const generateSampleData = async () => {
    isLoading.value = true;
    try {
        const response = await fetch(route('cms.onboarding.generate-sample-data'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });
        const data = await response.json();
        if (data.success) {
            sampleDataGenerated.value = true;
            alert(`Sample data generated!\n${data.data.customers} customers, ${data.data.jobs} jobs, ${data.data.invoices} invoices created.`);
        } else {
            alert('Failed to generate sample data');
        }
    } catch (error) {
        console.error('Error generating sample data:', error);
        alert('An error occurred. Please try again.');
    } finally {
        isLoading.value = false;
    }
};

const clearSampleData = async () => {
    if (!confirm('Are you sure you want to clear all sample data?')) {
        return;
    }
    isLoading.value = true;
    try {
        const response = await fetch(route('cms.onboarding.clear-sample-data'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });
        const data = await response.json();
        if (data.success) {
            sampleDataGenerated.value = false;
            alert('Sample data cleared successfully');
        } else {
            alert('Failed to clear sample data');
        }
    } catch (error) {
        console.error('Error clearing sample data:', error);
        alert('An error occurred. Please try again.');
    } finally {
        isLoading.value = false;
    }
};

const skipStep = async (stepId: number) => {
    isLoading.value = true;
    try {
        const response = await fetch(route('cms.onboarding.skip-step'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({ step_id: stepId }),
        });
        const data = await response.json();
        if (data.success) {
            if (stepId < 5) {
                currentStep.value = stepId + 1;
            } else {
                completeOnboarding();
            }
        }
    } catch (error) {
        console.error('Error skipping step:', error);
    } finally {
        isLoading.value = false;
    }
};

const completeOnboarding = async () => {
    isLoading.value = true;
    try {
        const response = await fetch(route('cms.onboarding.complete'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });
        const data = await response.json();
        if (data.success) {
            showCelebration.value = true;
            setTimeout(() => {
                router.visit(route('cms.dashboard'));
            }, 3000);
        }
    } catch (error) {
        console.error('Error completing onboarding:', error);
    } finally {
        isLoading.value = false;
    }
};

const nextStep = () => {
    switch (currentStep.value) {
        case 1:
            saveStep1();
            break;
        case 2:
            saveStep2();
            break;
        case 3:
            saveStep3();
            break;
        case 4:
        case 5:
            skipStep(currentStep.value);
            break;
    }
};

const previousStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
        loadSavedProgress();
    }
};

const openVideoTutorial = (stepId: number) => {
    if (videoTutorials[stepId]) {
        currentVideoUrl.value = videoTutorials[stepId].url;
        showVideoModal.value = true;
    }
};

const closeVideoModal = () => {
    showVideoModal.value = false;
    currentVideoUrl.value = '';
};

const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
const dayLabels: Record<string, string> = {
    monday: 'Monday',
    tuesday: 'Tuesday',
    wednesday: 'Wednesday',
    thursday: 'Thursday',
    friday: 'Friday',
    saturday: 'Saturday',
    sunday: 'Sunday',
};
</script>


<template>
    <Head title="Welcome to CMS" />

    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-gray-900 mb-2">Welcome to Your CMS</h1>
                <p class="text-lg text-gray-600">Let's get your business set up in just a few steps</p>
                <p class="text-sm text-gray-500 mt-2">
                    <InformationCircleIcon class="h-4 w-4 inline mr-1" aria-hidden="true" />
                    Your progress is automatically saved every 30 seconds
                </p>
            </div>

            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div
                        v-for="(step, index) in status.steps"
                        :key="step.id"
                        class="flex items-center"
                        :class="{ 'flex-1': index < status.steps.length - 1 }"
                    >
                        <div class="flex flex-col items-center">
                            <div
                                :class="[
                                    'w-12 h-12 rounded-full flex items-center justify-center border-2 transition-all',
                                    isStepCompleted(step.id)
                                        ? 'bg-green-500 border-green-500 text-white'
                                        : currentStep === step.id
                                        ? 'bg-blue-600 border-blue-600 text-white'
                                        : 'bg-white border-gray-300 text-gray-400'
                                ]"
                            >
                                <CheckCircleIcon v-if="isStepCompleted(step.id)" class="h-6 w-6" aria-hidden="true" />
                                <component v-else :is="stepIcons[index]" class="h-6 w-6" aria-hidden="true" />
                            </div>
                            <span
                                class="mt-2 text-xs font-medium text-center max-w-[100px]"
                                :class="currentStep === step.id ? 'text-blue-600' : 'text-gray-500'"
                            >
                                {{ step.name }}
                            </span>
                        </div>
                        <div
                            v-if="index < status.steps.length - 1"
                            class="flex-1 h-0.5 mx-4"
                            :class="isStepCompleted(step.id) ? 'bg-green-500' : 'bg-gray-300'"
                        ></div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-8">
                <div v-if="videoTutorials[currentStep]" class="mb-6 flex justify-end">
                    <button
                        @click="openVideoTutorial(currentStep)"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors"
                    >
                        <PlayCircleIcon class="h-5 w-5" aria-hidden="true" />
                        Watch Tutorial
                    </button>
                </div>

                <div v-if="currentStep === 1" class="space-y-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Company Information</h2>
                        <p class="text-gray-600">Tell us about your business</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Company Name <span class="text-red-500">*</span>
                                <span v-if="tooltips[1]?.name" class="ml-1 text-gray-400 cursor-help" :title="tooltips[1].name">
                                    <InformationCircleIcon class="h-4 w-4 inline" aria-hidden="true" />
                                </span>
                            </label>
                            <input
                                v-model="companyForm.name"
                                type="text"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Your Company Name"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Business Registration Number
                                <span v-if="tooltips[1]?.business_registration_number" class="ml-1 text-gray-400 cursor-help" :title="tooltips[1].business_registration_number">
                                    <InformationCircleIcon class="h-4 w-4 inline" aria-hidden="true" />
                                </span>
                            </label>
                            <input
                                v-model="companyForm.business_registration_number"
                                type="text"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="123456789"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tax Number / TPIN
                                <span v-if="tooltips[1]?.tax_number" class="ml-1 text-gray-400 cursor-help" :title="tooltips[1].tax_number">
                                    <InformationCircleIcon class="h-4 w-4 inline" aria-hidden="true" />
                                </span>
                            </label>
                            <input
                                v-model="companyForm.tax_number"
                                type="text"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="1234567890"
                            />
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                            <input v-model="companyForm.address" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="123 Main Street" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                            <input v-model="companyForm.city" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Lusaka" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                            <input v-model="companyForm.country" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Zambia" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                            <input v-model="companyForm.phone" type="tel" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="+260 XXX XXX XXX" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input v-model="companyForm.email" type="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="info@company.com" />
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Website</label>
                            <input v-model="companyForm.website" type="url" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="https://www.company.com" />
                        </div>
                    </div>
                </div>

                <div v-if="currentStep === 2" class="space-y-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Choose Your Industry</h2>
                        <p class="text-gray-600">
                            Select the industry that best matches your business
                            <span v-if="tooltips[2]?.industry" class="ml-1 text-gray-400 cursor-help" :title="tooltips[2].industry">
                                <InformationCircleIcon class="h-4 w-4 inline" aria-hidden="true" />
                            </span>
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <button
                            v-for="preset in presets"
                            :key="preset.id"
                            @click="selectedIndustry = preset.code"
                            :class="[
                                'p-6 border-2 rounded-xl text-left transition-all',
                                selectedIndustry === preset.code ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'
                            ]"
                        >
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ preset.name }}</h3>
                            <p class="text-sm text-gray-600">{{ preset.description }}</p>
                            <div class="mt-4 flex gap-4 text-xs text-gray-500">
                                <span>{{ preset.roles?.length || 0 }} roles</span>
                                <span>{{ preset.expense_categories?.length || 0 }} categories</span>
                            </div>
                        </button>
                    </div>
                </div>

                <div v-if="currentStep === 3" class="space-y-8">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Business Settings</h2>
                        <p class="text-gray-600">Configure your business hours and tax settings</p>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            Business Hours
                            <span v-if="tooltips[3]?.business_hours" class="ml-1 text-gray-400 cursor-help" :title="tooltips[3].business_hours">
                                <InformationCircleIcon class="h-4 w-4 inline" aria-hidden="true" />
                            </span>
                        </h3>
                        <div class="space-y-3">
                            <div v-for="day in days" :key="day" class="flex items-center gap-4">
                                <div class="w-28">
                                    <label class="text-sm font-medium text-gray-700">{{ dayLabels[day] }}</label>
                                </div>
                                <input type="checkbox" v-model="settingsForm.business_hours[day].enabled" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" />
                                <div v-if="settingsForm.business_hours[day].enabled" class="flex items-center gap-2">
                                    <input type="time" v-model="settingsForm.business_hours[day].open" class="px-3 py-1 border border-gray-300 rounded-lg text-sm" />
                                    <span class="text-sm text-gray-600">to</span>
                                    <input type="time" v-model="settingsForm.business_hours[day].close" class="px-3 py-1 border border-gray-300 rounded-lg text-sm" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            Tax Settings
                            <span v-if="tooltips[3]?.tax" class="ml-1 text-gray-400 cursor-help" :title="tooltips[3].tax">
                                <InformationCircleIcon class="h-4 w-4 inline" aria-hidden="true" />
                            </span>
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <input type="checkbox" v-model="settingsForm.tax.enabled" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" />
                                <label class="ml-2 text-sm text-gray-900">Enable tax on invoices</label>
                            </div>

                            <div v-if="settingsForm.tax.enabled" class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tax Label</label>
                                    <input v-model="settingsForm.tax.tax_label" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="VAT, GST, etc." />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Default Rate (%)</label>
                                    <input v-model.number="settingsForm.tax.default_rate" type="number" step="0.01" class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="16.00" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="currentStep === 4" class="space-y-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Add Team Members</h2>
                        <p class="text-gray-600">Invite your team to collaborate (you can do this later)</p>
                    </div>

                    <div class="text-center py-12">
                        <UsersIcon class="h-16 w-16 text-gray-400 mx-auto mb-4" aria-hidden="true" />
                        <p class="text-gray-600 mb-4">You can add team members from the settings page after setup</p>
                    </div>
                </div>

                <div v-if="currentStep === 5" class="space-y-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Try It Out with Sample Data</h2>
                        <p class="text-gray-600">Generate demo customers, jobs, and invoices to explore the system</p>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                        <div class="flex items-start gap-4">
                            <SparklesIcon class="h-8 w-8 text-blue-600 flex-shrink-0" aria-hidden="true" />
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Sample Data Includes:</h3>
                                <ul class="space-y-2 text-sm text-gray-700">
                                    <li>• 3 demo customers with realistic Zambian business names</li>
                                    <li>• 3 sample jobs with different statuses</li>
                                    <li>• 3 invoices showing various payment states</li>
                                </ul>
                                <p class="mt-4 text-sm text-gray-600">
                                    This helps you understand how the system works. You can delete this data anytime.
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 flex gap-3">
                            <button
                                v-if="!sampleDataGenerated"
                                @click="generateSampleData"
                                :disabled="isLoading"
                                class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50"
                            >
                                <SparklesIcon class="h-5 w-5" aria-hidden="true" />
                                Generate Sample Data
                            </button>
                            <button
                                v-else
                                @click="clearSampleData"
                                :disabled="isLoading"
                                class="inline-flex items-center gap-2 px-6 py-3 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 disabled:opacity-50"
                            >
                                Clear Sample Data
                            </button>
                        </div>
                    </div>

                    <div class="text-center py-8">
                        <BriefcaseIcon class="h-16 w-16 text-gray-400 mx-auto mb-4" aria-hidden="true" />
                        <p class="text-gray-600">Or skip this step and add your own data from the dashboard</p>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-200">
                    <button
                        v-if="currentStep > 1"
                        @click="previousStep"
                        :disabled="isLoading"
                        class="inline-flex items-center gap-2 px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                    >
                        <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
                        Previous
                    </button>
                    <div v-else></div>

                    <div class="flex items-center gap-3">
                        <button
                            v-if="currentStep >= 4"
                            @click="skipStep(currentStep)"
                            :disabled="isLoading"
                            class="px-6 py-3 text-sm font-medium text-gray-600 hover:text-gray-900 disabled:opacity-50"
                        >
                            Skip
                        </button>
                        <button
                            @click="nextStep"
                            :disabled="!canProceed || isLoading"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ currentStep === 5 ? 'Complete Setup' : 'Continue' }}
                            <ArrowRightIcon class="h-5 w-5" aria-hidden="true" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showVideoModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75 p-4" @click="closeVideoModal">
            <div class="bg-white rounded-xl max-w-4xl w-full p-6" @click.stop>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-900">
                        {{ videoTutorials[currentStep]?.title }}
                    </h3>
                    <button @click="closeVideoModal" class="text-gray-400 hover:text-gray-600" aria-label="Close video">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="aspect-video">
                    <iframe
                        :src="currentVideoUrl"
                        class="w-full h-full rounded-lg"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                    ></iframe>
                </div>
            </div>
        </div>

        <div v-if="showCelebration" class="fixed inset-0 z-50 flex items-center justify-center bg-gradient-to-br from-blue-500 to-purple-600 p-4">
            <div class="text-center text-white">
                <div class="mb-8 animate-bounce">
                    <CheckCircleIcon class="h-32 w-32 mx-auto" aria-hidden="true" />
                </div>
                <h2 class="text-5xl font-bold mb-4">Congratulations!</h2>
                <p class="text-2xl mb-8">Your CMS is ready to use</p>
                <div class="flex items-center justify-center gap-2 text-lg">
                    <svg class="animate-spin h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Redirecting to dashboard...</span>
                </div>
            </div>
        </div>
    </div>
</template>
