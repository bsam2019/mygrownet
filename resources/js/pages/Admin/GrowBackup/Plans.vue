<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';
import { CheckIcon, XMarkIcon } from '@heroicons/vue/24/outline';

interface Plan {
    id: string;
    name: string;
    slug: string;
    quota_gb: number;
    max_file_size_mb: number;
    price_monthly: number;
    price_annual: number;
    features: string[];
    is_popular: boolean;
    is_active: boolean;
    allow_sharing: boolean;
    allow_public_profile_files: boolean;
    subscribers_count: number;
}

interface Props {
    plans: Plan[];
}

const props = defineProps<Props>();
const editingPlan = ref<Plan | null>(null);

const form = useForm({
    name: '',
    quota_gb: 0,
    max_file_size_mb: 0,
    price_monthly: 0,
    price_annual: 0,
    features: [] as string[],
    is_popular: false,
    is_active: true,
    allow_sharing: false,
    allow_public_profile_files: false,
});

const editPlan = (plan: Plan) => {
    editingPlan.value = plan;
    form.name = plan.name;
    form.quota_gb = plan.quota_gb;
    form.max_file_size_mb = plan.max_file_size_mb;
    form.price_monthly = plan.price_monthly;
    form.price_annual = plan.price_annual;
    form.features = [...plan.features];
    form.is_popular = plan.is_popular;
    form.is_active = plan.is_active;
    form.allow_sharing = plan.allow_sharing;
    form.allow_public_profile_files = plan.allow_public_profile_files;
};

const cancelEdit = () => {
    editingPlan.value = null;
    form.reset();
};

const savePlan = () => {
    if (!editingPlan.value) return;
    
    form.put(route('admin.growbackup.plans.update', editingPlan.value.id), {
        onSuccess: () => {
            cancelEdit();
        },
    });
};

const addFeature = () => {
    form.features.push('');
};

const removeFeature = (index: number) => {
    form.features.splice(index, 1);
};
</script>

<template>
    <Head title="Manage Storage Plans" />

    <AdminLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-8">Storage Plans Management</h1>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div v-for="plan in plans" :key="plan.id" class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">{{ plan.name }}</h3>
                                <p class="text-sm text-gray-600">{{ plan.subscribers_count }} subscribers</p>
                            </div>
                            <button
                                @click="editPlan(plan)"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm"
                            >
                                Edit
                            </button>
                        </div>

                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Storage:</span>
                                <span class="font-medium">{{ plan.quota_gb }} GB</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Max File Size:</span>
                                <span class="font-medium">{{ plan.max_file_size_mb }} MB</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Monthly Price:</span>
                                <span class="font-medium">K{{ plan.price_monthly }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Annual Price:</span>
                                <span class="font-medium">K{{ plan.price_annual }}</span>
                            </div>
                            <div class="flex gap-2 pt-2">
                                <span v-if="plan.is_popular" class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Popular</span>
                                <span v-if="plan.is_active" class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">Active</span>
                                <span v-if="plan.allow_sharing" class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-xs">Sharing</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Modal -->
                <div v-if="editingPlan" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                    <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Edit {{ editingPlan.name }}</h2>

                        <form @submit.prevent="savePlan" class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Plan Name</label>
                                    <input v-model="form.name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Storage (GB)</label>
                                    <input v-model.number="form.quota_gb" type="number" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Max File Size (MB)</label>
                                    <input v-model.number="form.max_file_size_mb" type="number" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Monthly Price (K)</label>
                                    <input v-model.number="form.price_monthly" type="number" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Annual Price (K)</label>
                                    <input v-model.number="form.price_annual" type="number" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="flex items-center gap-2">
                                    <input v-model="form.is_popular" type="checkbox" class="rounded" />
                                    <span class="text-sm">Mark as Popular</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <input v-model="form.is_active" type="checkbox" class="rounded" />
                                    <span class="text-sm">Active</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <input v-model="form.allow_sharing" type="checkbox" class="rounded" />
                                    <span class="text-sm">Allow File Sharing</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <input v-model="form.allow_public_profile_files" type="checkbox" class="rounded" />
                                    <span class="text-sm">Allow Public Profile Files</span>
                                </label>
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-sm font-medium text-gray-700">Features</label>
                                    <button type="button" @click="addFeature" class="text-sm text-blue-600 hover:text-blue-700">+ Add Feature</button>
                                </div>
                                <div class="space-y-2">
                                    <div v-for="(feature, index) in form.features" :key="index" class="flex gap-2">
                                        <input v-model="form.features[index]" type="text" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg" placeholder="Feature description" />
                                        <button type="button" @click="removeFeature(index)" class="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg">
                                            <XMarkIcon class="h-5 w-5" />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="flex gap-3 pt-4">
                                <button type="submit" :disabled="form.processing" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                    {{ form.processing ? 'Saving...' : 'Save Changes' }}
                                </button>
                                <button type="button" @click="cancelEdit" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
