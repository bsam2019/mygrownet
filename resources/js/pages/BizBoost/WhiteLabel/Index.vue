<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import {
    PaintBrushIcon,
    PhotoIcon,
    SwatchIcon,
    GlobeAltIcon,
    LockClosedIcon,
} from '@heroicons/vue/24/outline';

interface WhiteLabelSettings {
    custom_domain: string | null;
    logo_url: string | null;
    favicon_url: string | null;
    primary_color: string;
    secondary_color: string;
    company_name: string | null;
    hide_branding: boolean;
}

interface Props {
    settings: WhiteLabelSettings;
    hasWhiteLabelAccess: boolean;
}

const props = defineProps<Props>();

const form = useForm({
    custom_domain: props.settings.custom_domain || '',
    primary_color: props.settings.primary_color || '#7c3aed',
    secondary_color: props.settings.secondary_color || '#4f46e5',
    company_name: props.settings.company_name || '',
    hide_branding: props.settings.hide_branding || false,
});

const logoFile = ref<File | null>(null);
const logoPreview = ref(props.settings.logo_url);

const handleLogoChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        logoFile.value = target.files[0];
        logoPreview.value = URL.createObjectURL(target.files[0]);
    }
};

const submit = () => {
    form.put('/bizboost/white-label');
};

const uploadLogo = () => {
    if (!logoFile.value) return;
    
    const formData = new FormData();
    formData.append('logo', logoFile.value);
    
    form.post('/bizboost/white-label/logo', {
        data: formData,
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="White-Label Settings - BizBoost" />
    <BizBoostLayout title="White-Label Settings">
        <div class="max-w-3xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center gap-3">
                <div class="rounded-lg bg-violet-100 p-2">
                    <PaintBrushIcon class="h-6 w-6 text-violet-600" aria-hidden="true" />
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">White-Label Settings</h1>
                    <p class="text-sm text-gray-500">Customize the look and feel of your BizBoost experience</p>
                </div>
            </div>

            <!-- Upgrade Notice -->
            <div v-if="!hasWhiteLabelAccess" class="bg-amber-50 border border-amber-200 rounded-xl p-6">
                <div class="flex items-start gap-3">
                    <LockClosedIcon class="h-6 w-6 text-amber-600 flex-shrink-0" aria-hidden="true" />
                    <div>
                        <h3 class="font-semibold text-amber-800">Business Tier Required</h3>
                        <p class="text-sm text-amber-700 mt-1">
                            White-label customization is available on the Business tier. 
                            Upgrade to remove BizBoost branding and use your own domain.
                        </p>
                        <a
                            href="/bizboost/upgrade"
                            class="inline-flex items-center gap-2 mt-3 px-4 py-2 bg-amber-600 text-white rounded-lg text-sm font-medium hover:bg-amber-700"
                        >
                            Upgrade Now
                        </a>
                    </div>
                </div>
            </div>

            <!-- Settings Form -->
            <form @submit.prevent="submit" :class="{ 'opacity-50 pointer-events-none': !hasWhiteLabelAccess }">
                <!-- Branding -->
                <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-6 mb-6">
                    <div class="flex items-center gap-2 mb-4">
                        <PhotoIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                        <h2 class="font-semibold text-gray-900">Branding</h2>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Company Logo
                            </label>
                            <div class="flex items-center gap-4">
                                <div class="w-20 h-20 rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden">
                                    <img
                                        v-if="logoPreview"
                                        :src="logoPreview"
                                        alt="Logo preview"
                                        class="w-full h-full object-contain"
                                    />
                                    <PhotoIcon v-else class="h-8 w-8 text-gray-400" aria-hidden="true" />
                                </div>
                                <div>
                                    <input
                                        type="file"
                                        accept="image/*"
                                        @change="handleLogoChange"
                                        class="hidden"
                                        id="logo-upload"
                                    />
                                    <label
                                        for="logo-upload"
                                        class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 cursor-pointer"
                                    >
                                        Choose File
                                    </label>
                                    <button
                                        v-if="logoFile"
                                        type="button"
                                        @click="uploadLogo"
                                        class="ml-2 px-3 py-2 bg-violet-600 text-white rounded-lg text-sm font-medium hover:bg-violet-700"
                                    >
                                        Upload
                                    </button>
                                    <p class="text-xs text-gray-500 mt-1">PNG, JPG up to 2MB</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="company_name" class="block text-sm font-medium text-gray-700 mb-1">
                                Company Name
                            </label>
                            <input
                                id="company_name"
                                v-model="form.company_name"
                                type="text"
                                class="w-full rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500"
                                placeholder="Your Company Name"
                            />
                            <p class="text-xs text-gray-500 mt-1">Replaces "BizBoost" in the interface</p>
                        </div>

                        <div class="flex items-center gap-3">
                            <input
                                id="hide_branding"
                                v-model="form.hide_branding"
                                type="checkbox"
                                class="rounded border-gray-300 text-violet-600 focus:ring-violet-500"
                            />
                            <label for="hide_branding" class="text-sm text-gray-700">
                                Hide "Powered by BizBoost" branding
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Colors -->
                <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-6 mb-6">
                    <div class="flex items-center gap-2 mb-4">
                        <SwatchIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                        <h2 class="font-semibold text-gray-900">Colors</h2>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="primary_color" class="block text-sm font-medium text-gray-700 mb-1">
                                Primary Color
                            </label>
                            <div class="flex items-center gap-2">
                                <input
                                    id="primary_color"
                                    v-model="form.primary_color"
                                    type="color"
                                    class="w-10 h-10 rounded border-gray-300 cursor-pointer"
                                />
                                <input
                                    v-model="form.primary_color"
                                    type="text"
                                    class="flex-1 rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500 font-mono text-sm"
                                />
                            </div>
                        </div>
                        <div>
                            <label for="secondary_color" class="block text-sm font-medium text-gray-700 mb-1">
                                Secondary Color
                            </label>
                            <div class="flex items-center gap-2">
                                <input
                                    id="secondary_color"
                                    v-model="form.secondary_color"
                                    type="color"
                                    class="w-10 h-10 rounded border-gray-300 cursor-pointer"
                                />
                                <input
                                    v-model="form.secondary_color"
                                    type="text"
                                    class="flex-1 rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500 font-mono text-sm"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Custom Domain -->
                <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-6 mb-6">
                    <div class="flex items-center gap-2 mb-4">
                        <GlobeAltIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                        <h2 class="font-semibold text-gray-900">Custom Domain</h2>
                    </div>

                    <div>
                        <label for="custom_domain" class="block text-sm font-medium text-gray-700 mb-1">
                            Domain Name
                        </label>
                        <input
                            id="custom_domain"
                            v-model="form.custom_domain"
                            type="text"
                            class="w-full rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500"
                            placeholder="app.yourcompany.com"
                        />
                        <p class="text-xs text-gray-500 mt-1">
                            Point your domain's CNAME record to bizboost.mygrownet.com
                        </p>
                    </div>
                </div>

                <!-- Submit -->
                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="form.processing || !hasWhiteLabelAccess"
                        class="px-6 py-2 bg-violet-600 text-white rounded-lg font-medium hover:bg-violet-700 disabled:opacity-50 transition-colors"
                    >
                        {{ form.processing ? 'Saving...' : 'Save Settings' }}
                    </button>
                </div>
            </form>
        </div>
    </BizBoostLayout>
</template>
