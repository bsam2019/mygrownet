<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { ref, computed } from 'vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

interface TierData {
    id?: number;
    name: string;
    price: number;
    currency: string;
    documents_per_month: number;
    features: Record<string, any>;
    is_active: boolean;
}

const props = defineProps<{ tier: TierData | null }>();

const isEditing = computed(() => !!props.tier);

const form = ref({
    name: props.tier?.name ?? '',
    price: props.tier?.price ?? 0,
    currency: props.tier?.currency ?? 'ZMW',
    documents_per_month: props.tier?.documents_per_month ?? 10,
    features: props.tier?.features ?? {
        templates: ['classic', 'modern'],
        sharing: ['pdf_download'],
        watermark: true,
        customization: true,
        design_studio: false,
        custom_branding: false,
        advanced_templates: false,
        api_access: true,
        priority_support: false,
        white_label: false,
        advanced_analytics: false,
        cms_integration: false,
    },
    is_active: props.tier?.is_active ?? true,
});

const processing = ref(false);
const errors = ref<Record<string, string>>({});

const featureKeys = [
    { key: 'templates', label: 'Available Templates', type: 'text' },
    { key: 'sharing', label: 'Sharing Options', type: 'text' },
    { key: 'watermark', label: 'No Watermark', type: 'bool' },
    { key: 'customization', label: 'Customization', type: 'bool' },
    { key: 'design_studio', label: 'Design Studio', type: 'bool' },
    { key: 'custom_branding', label: 'Custom Branding', type: 'bool' },
    { key: 'advanced_templates', label: 'Advanced Templates', type: 'bool' },
    { key: 'api_access', label: 'API Access', type: 'bool' },
    { key: 'priority_support', label: 'Priority Support', type: 'bool' },
    { key: 'white_label', label: 'White Label', type: 'bool' },
    { key: 'advanced_analytics', label: 'Advanced Analytics', type: 'bool' },
    { key: 'cms_integration', label: 'CMS Integration', type: 'bool' },
];

const submit = async () => {
    processing.value = true;
    errors.value = {};

    try {
        if (isEditing.value) {
            await router.put(route('admin.quick-invoice.tiers.update', props.tier!.id), form.value);
        } else {
            await router.post(route('admin.quick-invoice.tiers.store'), form.value);
        }
    } catch (e: any) {
        if (e?.response?.status === 422) {
            errors.value = e.response.data.errors || {};
        }
    } finally {
        processing.value = false;
    }
};
</script>

<template>
    <AdminLayout :title="isEditing ? 'Edit Plan' : 'Create Plan'">
        <Head :title="isEditing ? 'Edit Plan - Admin' : 'Create Plan - Admin'" />

        <div class="py-6">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
                <Link :href="route('admin.quick-invoice.tiers')"
                    class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 mb-6">
                    <ArrowLeftIcon class="h-4 w-4" />
                    Back to Plans
                </Link>

                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h1 class="text-xl font-bold text-gray-900 mb-6">{{ isEditing ? 'Edit Plan' : 'Create Plan' }}</h1>

                    <form @submit.prevent="submit" class="space-y-5">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Plan Name</label>
                                <input v-model="form.name" type="text" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
                                <p v-if="errors.name" class="text-xs text-red-600 mt-1">{{ errors.name[0] }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                                <select v-model="form.currency"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="ZMW">ZMW (K)</option>
                                    <option value="USD">USD ($)</option>
                                    <option value="EUR">EUR (€)</option>
                                    <option value="GBP">GBP (£)</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Price (per month)</label>
                                <input v-model.number="form.price" type="number" min="0" step="0.01" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
                                <p v-if="errors.price" class="text-xs text-red-600 mt-1">{{ errors.price[0] }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Documents Per Month</label>
                                <input v-model.number="form.documents_per_month" type="number" min="-1" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
                                <p class="text-xs text-gray-400 mt-1">Use -1 for unlimited</p>
                                <p v-if="errors.documents_per_month" class="text-xs text-red-600 mt-1">{{ errors.documents_per_month[0] }}</p>
                            </div>
                        </div>

                        <div>
                            <label class="flex items-center gap-2">
                                <input v-model="form.is_active" type="checkbox" class="h-4 w-4 text-blue-600 rounded" />
                                <span class="text-sm font-medium text-gray-700">Active</span>
                            </label>
                        </div>

                        <div class="border-t pt-4">
                            <h3 class="text-sm font-semibold text-gray-900 mb-3">Features</h3>
                            <div class="space-y-3">
                                <div v-for="fk in featureKeys" :key="fk.key" class="flex items-center justify-between">
                                    <label class="text-sm text-gray-700">{{ fk.label }}</label>
                                    <div v-if="fk.type === 'bool'">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" v-model="form.features[fk.key]" class="sr-only peer" />
                                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                                        </label>
                                    </div>
                                    <div v-else>
                                        <input v-model="form.features[fk.key]" type="text"
                                            class="w-48 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t">
                            <Link :href="route('admin.quick-invoice.tiers')"
                                class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 text-sm">
                                Cancel
                            </Link>
                            <button type="submit" :disabled="processing"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 text-sm">
                                {{ processing ? 'Saving...' : isEditing ? 'Update Plan' : 'Create Plan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
