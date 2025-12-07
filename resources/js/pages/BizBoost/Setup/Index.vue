<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import {
    BuildingStorefrontIcon, SparklesIcon, CheckCircleIcon, ArrowRightIcon, ArrowLeftIcon,
    PhotoIcon, MapPinIcon, ClockIcon, GlobeAltIcon, DevicePhoneMobileIcon,
    RocketLaunchIcon, UserGroupIcon, ShoppingBagIcon, PaintBrushIcon,
} from '@heroicons/vue/24/outline';

interface Business {
    id: number;
    name: string;
    industry: string;
    description: string | null;
    phone: string | null;
    whatsapp: string | null;
    email: string | null;
    address: string | null;
    city: string | null;
    province: string | null;
    logo_path: string | null;
    business_hours: Record<string, { open: string; close: string; closed: boolean }> | null;
    social_links: Record<string, string> | null;
}

const props = defineProps<{ step: number; business: Business | null; industries: string[] }>();

const currentStep = ref(props.step);
const logoPreview = ref<string | null>(props.business?.logo_path ? `/storage/${props.business.logo_path}` : null);
const isUploading = ref(false);

const businessForm = useForm({
    name: props.business?.name ?? '',
    industry: props.business?.industry ?? '',
    description: props.business?.description ?? '',
    phone: props.business?.phone ?? '',
    whatsapp: props.business?.whatsapp ?? '',
    email: props.business?.email ?? '',
});

const locationForm = useForm({
    address: props.business?.address ?? '',
    city: props.business?.city ?? '',
    province: props.business?.province ?? 'Lusaka',
});

const defaultHours = {
    monday: { open: '08:00', close: '17:00', closed: false },
    tuesday: { open: '08:00', close: '17:00', closed: false },
    wednesday: { open: '08:00', close: '17:00', closed: false },
    thursday: { open: '08:00', close: '17:00', closed: false },
    friday: { open: '08:00', close: '17:00', closed: false },
    saturday: { open: '09:00', close: '13:00', closed: false },
    sunday: { open: '09:00', close: '13:00', closed: true },
};

const hoursForm = useForm({ business_hours: props.business?.business_hours ?? defaultHours });

const socialForm = useForm({
    facebook: props.business?.social_links?.facebook ?? '',
    instagram: props.business?.social_links?.instagram ?? '',
    tiktok: props.business?.social_links?.tiktok ?? '',
    website: props.business?.social_links?.website ?? '',
});

const industryOptions = [
    { value: 'Retail & Shopping', icon: ShoppingBagIcon },
    { value: 'Food & Restaurant', icon: BuildingStorefrontIcon },
    { value: 'Beauty & Salon', icon: SparklesIcon },
    { value: 'Health & Fitness', icon: UserGroupIcon },
    { value: 'Professional Services', icon: BuildingStorefrontIcon },
    { value: 'Home Services', icon: BuildingStorefrontIcon },
    { value: 'Automotive', icon: BuildingStorefrontIcon },
    { value: 'Education & Training', icon: BuildingStorefrontIcon },
    { value: 'Technology', icon: DevicePhoneMobileIcon },
    { value: 'Agriculture', icon: BuildingStorefrontIcon },
    { value: 'Manufacturing', icon: BuildingStorefrontIcon },
    { value: 'Other', icon: BuildingStorefrontIcon },
];

const provinces = ['Lusaka', 'Copperbelt', 'Central', 'Eastern', 'Luapula', 'Muchinga', 'Northern', 'North-Western', 'Southern', 'Western'];

const steps = [
    { id: 1, name: 'Business Info', icon: BuildingStorefrontIcon },
    { id: 2, name: 'Location', icon: MapPinIcon },
    { id: 3, name: 'Hours', icon: ClockIcon },
    { id: 4, name: 'Online', icon: GlobeAltIcon },
    { id: 5, name: 'Branding', icon: PaintBrushIcon },
    { id: 6, name: 'Launch', icon: RocketLaunchIcon },
];

const isStepComplete = (id: number) => id < currentStep.value;
const isCurrentStep = (id: number) => id === currentStep.value;
const canProceed = computed(() => currentStep.value === 1 ? businessForm.name.trim() !== '' && businessForm.industry !== '' : true);
const progressPercentage = computed(() => Math.round((currentStep.value / steps.length) * 100));

const submitStep = () => {
    if (currentStep.value === 1) businessForm.post(route('bizboost.setup.business'), { preserveScroll: true, onSuccess: () => currentStep.value = 2 });
    else if (currentStep.value === 2) locationForm.post(route('bizboost.setup.location'), { preserveScroll: true, onSuccess: () => currentStep.value = 3 });
    else if (currentStep.value === 3) hoursForm.post(route('bizboost.setup.hours'), { preserveScroll: true, onSuccess: () => currentStep.value = 4 });
    else if (currentStep.value === 4) socialForm.post(route('bizboost.setup.social'), { preserveScroll: true, onSuccess: () => currentStep.value = 5 });
    else if (currentStep.value === 5) currentStep.value = 6;
};

const skipStep = () => { if (currentStep.value < 6) currentStep.value++; };
const goBack = () => { if (currentStep.value > 1) currentStep.value--; };
const completeSetup = () => router.post(route('bizboost.setup.complete'));

const handleLogoUpload = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (!file) return;
    isUploading.value = true;
    const reader = new FileReader();
    reader.onload = (e) => { logoPreview.value = e.target?.result as string; };
    reader.readAsDataURL(file);
    const formData = new FormData();
    formData.append('logo', file);
    router.post(route('bizboost.setup.logo'), formData, { forceFormData: true, onFinish: () => isUploading.value = false });
};

const toggleDayClosed = (day: string) => { hoursForm.business_hours[day].closed = !hoursForm.business_hours[day].closed; };
watch(() => businessForm.phone, (p) => { if (p && !businessForm.whatsapp) businessForm.whatsapp = p; });
</script>

<template>
    <Head title="Setup Your Business - BizBoost" />
    <div class="min-h-screen bg-gradient-to-br from-violet-50 via-white to-violet-50/30">
        <!-- Header -->
        <div class="bg-white/80 backdrop-blur-sm shadow-sm sticky top-0 z-10">
            <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-violet-600 to-violet-700 flex items-center justify-center">
                        <SparklesIcon class="h-6 w-6 text-white" aria-hidden="true" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">BizBoost</h1>
                        <p class="text-sm text-gray-500">Setup your business</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-violet-600">{{ progressPercentage }}%</p>
                    <p class="text-xs text-gray-500">Step {{ currentStep }}/{{ steps.length }}</p>
                </div>
            </div>
        </div>

        <div class="max-w-4xl mx-auto px-4 py-6">
            <!-- Progress Bar -->
            <div class="mb-6 h-2 bg-gray-200 rounded-full overflow-hidden">
                <div class="h-full bg-violet-600 rounded-full transition-all duration-500" :style="{ width: `${progressPercentage}%` }"></div>
            </div>

            <!-- Desktop Steps -->
            <nav class="mb-8 hidden lg:block">
                <ol class="flex items-center justify-between">
                    <li v-for="(s, i) in steps" :key="s.id" class="flex items-center">
                        <div class="flex flex-col items-center">
                            <div :class="['w-10 h-10 rounded-full flex items-center justify-center', isCurrentStep(s.id) ? 'bg-violet-600 text-white ring-4 ring-violet-100' : isStepComplete(s.id) ? 'bg-violet-600 text-white' : 'bg-gray-100 text-gray-400']">
                                <CheckCircleIcon v-if="isStepComplete(s.id)" class="h-5 w-5" />
                                <component v-else :is="s.icon" class="h-5 w-5" />
                            </div>
                            <span :class="['mt-2 text-xs font-medium', isCurrentStep(s.id) ? 'text-violet-600' : isStepComplete(s.id) ? 'text-gray-900' : 'text-gray-400']">{{ s.name }}</span>
                        </div>
                        <div v-if="i < steps.length - 1" :class="['w-full h-0.5 mx-2', isStepComplete(s.id + 1) ? 'bg-violet-600' : 'bg-gray-200']"></div>
                    </li>
                </ol>
            </nav>

            <!-- Mobile Steps -->
            <div class="lg:hidden mb-6 flex justify-center gap-2">
                <div v-for="s in steps" :key="s.id" :class="['h-2 rounded-full transition-all', isCurrentStep(s.id) ? 'w-6 bg-violet-600' : isStepComplete(s.id) ? 'w-2 bg-violet-600' : 'w-2 bg-gray-300']"></div>
            </div>

            <!-- Step 1: Business Info -->
            <div v-if="currentStep === 1" class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-violet-600 to-violet-700 px-6 py-8 text-white">
                    <h2 class="text-2xl font-bold">Let's get started! 🚀</h2>
                    <p class="text-violet-100 mt-2">Tell us about your business</p>
                </div>
                <form @submit.prevent="submitStep" class="p-6 space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Business Name *</label>
                        <input id="name" v-model="businessForm.name" type="text" required class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500 text-lg py-3" placeholder="e.g., Mama's Kitchen" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Industry *</label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <button v-for="ind in industryOptions" :key="ind.value" type="button" @click="businessForm.industry = ind.value" :class="['flex flex-col items-center p-4 rounded-xl border-2 transition-all', businessForm.industry === ind.value ? 'border-violet-600 bg-violet-50' : 'border-gray-200 hover:border-violet-300']">
                                <component :is="ind.icon" class="h-6 w-6 text-violet-600 mb-2" />
                                <span class="text-sm font-medium text-gray-900">{{ ind.value.split(' & ')[0] }}</span>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="description" v-model="businessForm.description" rows="3" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500" placeholder="What makes your business special?" />
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input id="phone" v-model="businessForm.phone" type="tel" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500" placeholder="+260 97X XXX XXX" />
                        </div>
                        <div>
                            <label for="whatsapp" class="block text-sm font-medium text-gray-700 mb-1">WhatsApp</label>
                            <input id="whatsapp" v-model="businessForm.whatsapp" type="tel" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500" placeholder="+260 97X XXX XXX" />
                        </div>
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input id="email" v-model="businessForm.email" type="email" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500" placeholder="hello@yourbusiness.com" />
                    </div>
                    <div class="pt-4 flex justify-end">
                        <button type="submit" :disabled="!canProceed || businessForm.processing" class="flex items-center gap-2 rounded-xl bg-violet-600 px-6 py-3 text-white font-medium hover:bg-violet-700 disabled:opacity-50 transition-colors">
                            Continue <ArrowRightIcon class="h-5 w-5" />
                        </button>
                    </div>
                </form>
            </div>

            <!-- Step 2: Location -->
            <div v-else-if="currentStep === 2" class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-violet-600 to-violet-700 px-6 py-8 text-white flex items-center gap-3">
                    <MapPinIcon class="h-8 w-8" />
                    <div>
                        <h2 class="text-2xl font-bold">Where are you located?</h2>
                        <p class="text-violet-100 mt-1">Help customers find you</p>
                    </div>
                </div>
                <form @submit.prevent="submitStep" class="p-6 space-y-6">
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Street Address</label>
                        <input id="address" v-model="locationForm.address" type="text" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500" placeholder="e.g., Plot 123, Cairo Road" />
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700 mb-1">City/Town</label>
                            <input id="city" v-model="locationForm.city" type="text" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500" placeholder="e.g., Lusaka" />
                        </div>
                        <div>
                            <label for="province" class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                            <select id="province" v-model="locationForm.province" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500">
                                <option v-for="p in provinces" :key="p" :value="p">{{ p }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="pt-4 flex justify-between">
                        <button type="button" @click="goBack" class="flex items-center gap-2 rounded-xl border border-gray-300 px-4 py-3 text-gray-700 font-medium hover:bg-gray-50">
                            <ArrowLeftIcon class="h-5 w-5" /> Back
                        </button>
                        <div class="flex gap-3">
                            <button type="button" @click="skipStep" class="px-4 py-3 text-gray-500 font-medium hover:text-gray-700">Skip</button>
                            <button type="submit" :disabled="locationForm.processing" class="flex items-center gap-2 rounded-xl bg-violet-600 px-6 py-3 text-white font-medium hover:bg-violet-700 disabled:opacity-50">
                                Continue <ArrowRightIcon class="h-5 w-5" />
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Step 3: Business Hours -->
            <div v-else-if="currentStep === 3" class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-violet-600 to-violet-700 px-6 py-8 text-white flex items-center gap-3">
                    <ClockIcon class="h-8 w-8" />
                    <div>
                        <h2 class="text-2xl font-bold">When are you open?</h2>
                        <p class="text-violet-100 mt-1">Set your business hours</p>
                    </div>
                </div>
                <form @submit.prevent="submitStep" class="p-6 space-y-4">
                    <div v-for="(hours, day) in hoursForm.business_hours" :key="day" class="flex items-center gap-4 p-3 rounded-xl hover:bg-gray-50">
                        <div class="w-28 font-medium text-gray-900 capitalize">{{ day }}</div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" :checked="!hours.closed" @change="toggleDayClosed(day as string)" class="sr-only peer" />
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-violet-600 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                        </label>
                        <div v-if="!hours.closed" class="flex items-center gap-2 flex-1">
                            <input type="time" v-model="hours.open" class="rounded-lg border-gray-300 text-sm focus:border-violet-500 focus:ring-violet-500" />
                            <span class="text-gray-500">to</span>
                            <input type="time" v-model="hours.close" class="rounded-lg border-gray-300 text-sm focus:border-violet-500 focus:ring-violet-500" />
                        </div>
                        <span v-else class="text-gray-400 text-sm">Closed</span>
                    </div>
                    <div class="pt-4 flex justify-between">
                        <button type="button" @click="goBack" class="flex items-center gap-2 rounded-xl border border-gray-300 px-4 py-3 text-gray-700 font-medium hover:bg-gray-50">
                            <ArrowLeftIcon class="h-5 w-5" /> Back
                        </button>
                        <div class="flex gap-3">
                            <button type="button" @click="skipStep" class="px-4 py-3 text-gray-500 font-medium hover:text-gray-700">Skip</button>
                            <button type="submit" :disabled="hoursForm.processing" class="flex items-center gap-2 rounded-xl bg-violet-600 px-6 py-3 text-white font-medium hover:bg-violet-700 disabled:opacity-50">
                                Continue <ArrowRightIcon class="h-5 w-5" />
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Step 4: Social Links -->
            <div v-else-if="currentStep === 4" class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-violet-600 to-violet-700 px-6 py-8 text-white flex items-center gap-3">
                    <GlobeAltIcon class="h-8 w-8" />
                    <div>
                        <h2 class="text-2xl font-bold">Connect your online presence</h2>
                        <p class="text-violet-100 mt-1">Link your social media</p>
                    </div>
                </div>
                <form @submit.prevent="submitStep" class="p-6 space-y-5">
                    <div>
                        <label for="facebook" class="block text-sm font-medium text-gray-700 mb-1">Facebook Page</label>
                        <input id="facebook" v-model="socialForm.facebook" type="url" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500" placeholder="https://facebook.com/yourbusiness" />
                    </div>
                    <div>
                        <label for="instagram" class="block text-sm font-medium text-gray-700 mb-1">Instagram</label>
                        <input id="instagram" v-model="socialForm.instagram" type="url" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500" placeholder="https://instagram.com/yourbusiness" />
                    </div>
                    <div>
                        <label for="tiktok" class="block text-sm font-medium text-gray-700 mb-1">TikTok</label>
                        <input id="tiktok" v-model="socialForm.tiktok" type="url" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500" placeholder="https://tiktok.com/@yourbusiness" />
                    </div>
                    <div>
                        <label for="website" class="block text-sm font-medium text-gray-700 mb-1">Website</label>
                        <input id="website" v-model="socialForm.website" type="url" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500" placeholder="https://yourbusiness.com" />
                    </div>
                    <div class="pt-4 flex justify-between">
                        <button type="button" @click="goBack" class="flex items-center gap-2 rounded-xl border border-gray-300 px-4 py-3 text-gray-700 font-medium hover:bg-gray-50">
                            <ArrowLeftIcon class="h-5 w-5" /> Back
                        </button>
                        <div class="flex gap-3">
                            <button type="button" @click="skipStep" class="px-4 py-3 text-gray-500 font-medium hover:text-gray-700">Skip</button>
                            <button type="submit" :disabled="socialForm.processing" class="flex items-center gap-2 rounded-xl bg-violet-600 px-6 py-3 text-white font-medium hover:bg-violet-700 disabled:opacity-50">
                                Continue <ArrowRightIcon class="h-5 w-5" />
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Step 5: Branding -->
            <div v-else-if="currentStep === 5" class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-violet-600 to-violet-700 px-6 py-8 text-white flex items-center gap-3">
                    <PaintBrushIcon class="h-8 w-8" />
                    <div>
                        <h2 class="text-2xl font-bold">Add your brand identity</h2>
                        <p class="text-violet-100 mt-1">Upload your logo</p>
                    </div>
                </div>
                <div class="p-6 space-y-6">
                    <div class="flex flex-col items-center">
                        <div :class="['relative h-40 w-40 rounded-2xl flex items-center justify-center overflow-hidden', logoPreview ? 'ring-4 ring-violet-200' : 'bg-gray-100 ring-2 ring-dashed ring-gray-300']">
                            <img v-if="logoPreview" :src="logoPreview" alt="Logo" class="h-full w-full object-cover" />
                            <div v-else class="text-center p-4">
                                <PhotoIcon class="h-12 w-12 text-gray-400 mx-auto" />
                                <p class="text-sm text-gray-500 mt-2">No logo</p>
                            </div>
                            <div v-if="isUploading" class="absolute inset-0 bg-white/80 flex items-center justify-center">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-violet-600"></div>
                            </div>
                        </div>
                        <label for="logo-upload" class="mt-4 cursor-pointer rounded-xl bg-violet-100 px-6 py-3 text-sm font-medium text-violet-700 hover:bg-violet-200">
                            {{ logoPreview ? 'Change Logo' : 'Upload Logo' }}
                        </label>
                        <input id="logo-upload" type="file" accept="image/*" class="hidden" @change="handleLogoUpload" />
                        <p class="mt-2 text-xs text-gray-500">PNG, JPG up to 2MB</p>
                    </div>
                    <div class="bg-violet-50 rounded-xl p-4">
                        <h4 class="font-semibold text-violet-900 mb-2">Tips</h4>
                        <ul class="text-sm text-violet-700 space-y-1">
                            <li>• Use a square image (1:1)</li>
                            <li>• High contrast works best</li>
                            <li>• You can change this later</li>
                        </ul>
                    </div>
                    <div class="pt-4 flex justify-between">
                        <button type="button" @click="goBack" class="flex items-center gap-2 rounded-xl border border-gray-300 px-4 py-3 text-gray-700 font-medium hover:bg-gray-50">
                            <ArrowLeftIcon class="h-5 w-5" /> Back
                        </button>
                        <div class="flex gap-3">
                            <button type="button" @click="skipStep" class="px-4 py-3 text-gray-500 font-medium hover:text-gray-700">Skip</button>
                            <button type="button" @click="submitStep" class="flex items-center gap-2 rounded-xl bg-violet-600 px-6 py-3 text-white font-medium hover:bg-violet-700">
                                Continue <ArrowRightIcon class="h-5 w-5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 6: Launch -->
            <div v-else class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-violet-600 via-purple-600 to-violet-700 px-6 py-12 text-white text-center">
                    <div class="mx-auto h-20 w-20 rounded-full bg-white/20 flex items-center justify-center mb-6 animate-bounce">
                        <RocketLaunchIcon class="h-10 w-10" />
                    </div>
                    <h2 class="text-3xl font-bold">You're all set! 🎉</h2>
                    <p class="text-violet-100 mt-3 text-lg">Your business is ready to go</p>
                </div>
                <div class="p-6 space-y-6">
                    <div class="bg-gray-50 rounded-xl p-5">
                        <h3 class="font-semibold text-gray-900 mb-4">What's next?</h3>
                        <div class="space-y-3">
                            <div class="flex items-start gap-3">
                                <div class="h-8 w-8 rounded-lg bg-violet-100 flex items-center justify-center shrink-0">
                                    <ShoppingBagIcon class="h-4 w-4 text-violet-600" />
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Add your products</p>
                                    <p class="text-sm text-gray-500">Showcase what you sell</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="h-8 w-8 rounded-lg bg-violet-100 flex items-center justify-center shrink-0">
                                    <SparklesIcon class="h-4 w-4 text-violet-600" />
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Create your first post</p>
                                    <p class="text-sm text-gray-500">Use AI to generate content</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="h-8 w-8 rounded-lg bg-violet-100 flex items-center justify-center shrink-0">
                                    <GlobeAltIcon class="h-4 w-4 text-violet-600" />
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Publish your mini-website</p>
                                    <p class="text-sm text-gray-500">Get a free online presence</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <button @click="completeSetup" class="w-full flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-violet-600 to-purple-600 px-6 py-4 text-white font-semibold hover:from-violet-700 hover:to-purple-700 shadow-lg shadow-violet-500/25">
                            <RocketLaunchIcon class="h-5 w-5" /> Launch My Dashboard
                        </button>
                        <div class="grid grid-cols-2 gap-3">
                            <a :href="route('bizboost.products.create')" class="flex items-center justify-center gap-2 rounded-xl border border-violet-300 px-4 py-3 text-violet-600 font-medium hover:bg-violet-50">
                                <ShoppingBagIcon class="h-5 w-5" /> Add Product
                            </a>
                            <a :href="route('bizboost.posts.create')" class="flex items-center justify-center gap-2 rounded-xl border border-violet-300 px-4 py-3 text-violet-600 font-medium hover:bg-violet-50">
                                <SparklesIcon class="h-5 w-5" /> Create Post
                            </a>
                        </div>
                    </div>
                    <div class="text-center pt-4">
                        <button type="button" @click="goBack" class="text-sm text-gray-500 hover:text-gray-700">← Go back and edit</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
