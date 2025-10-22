<template>
    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Package Management</h1>
                        <p class="text-gray-600 mt-1">Manage subscription packages and pricing</p>
                    </div>
                    <button
                        @click="openCreateModal"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
                    >
                        Create Package
                    </button>
                </div>
            </div>

            <!-- Packages Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="pkg in packages"
                    :key="pkg.id"
                    class="bg-white rounded-lg shadow-sm p-6 border-2"
                    :class="pkg.is_active ? 'border-green-200' : 'border-gray-200'"
                >
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">{{ pkg.name }}</h3>
                            <p class="text-sm text-gray-500 mt-1">{{ pkg.billing_cycle }}</p>
                        </div>
                        <span
                            :class="[
                                'px-2 py-1 text-xs font-medium rounded-full',
                                pkg.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                            ]"
                        >
                            {{ pkg.is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <div class="mb-4">
                        <div class="text-3xl font-bold text-blue-600">
                            K{{ pkg.price.toLocaleString() }}
                            <span class="text-sm font-normal text-gray-500">/{{ pkg.billing_cycle }}</span>
                        </div>
                    </div>

                    <div v-if="pkg.description" class="mb-4">
                        <p class="text-sm text-gray-600">{{ pkg.description }}</p>
                    </div>

                    <div v-if="pkg.professional_level" class="mb-4">
                        <span class="text-xs font-medium text-gray-500">Level: </span>
                        <span class="text-sm font-semibold text-gray-900">{{ capitalizeLevel(pkg.professional_level) }}</span>
                    </div>

                    <div v-if="pkg.lp_requirement" class="mb-4">
                        <span class="text-xs font-medium text-gray-500">LP Required: </span>
                        <span class="text-sm font-semibold text-purple-600">{{ pkg.lp_requirement }}</span>
                    </div>

                    <div v-if="pkg.features && pkg.features.length" class="mb-4">
                        <p class="text-xs font-medium text-gray-500 mb-2">Features:</p>
                        <ul class="space-y-1">
                            <li v-for="(feature, index) in pkg.features" :key="index" class="text-sm text-gray-600 flex items-start">
                                <CheckIcon class="h-4 w-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                                <span>{{ feature }}</span>
                            </li>
                        </ul>
                    </div>

                    <div class="flex space-x-2 mt-4 pt-4 border-t border-gray-200">
                        <button
                            @click="openEditModal(pkg)"
                            class="flex-1 px-3 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 transition-colors"
                        >
                            Edit
                        </button>
                        <button
                            @click="togglePackageStatus(pkg)"
                            class="flex-1 px-3 py-2 text-sm rounded-md transition-colors"
                            :class="pkg.is_active ? 'bg-yellow-600 text-white hover:bg-yellow-700' : 'bg-green-600 text-white hover:bg-green-700'"
                        >
                            {{ pkg.is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                        <button
                            @click="deletePackage(pkg)"
                            class="px-3 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700 transition-colors"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>

            <!-- Create/Edit Modal -->
            <Modal :show="showModal" @close="closeModal">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        {{ isEditing ? 'Edit Package' : 'Create Package' }}
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Package Name</label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="e.g., Associate Package"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea
                                v-model="form.description"
                                rows="2"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Package description..."
                            ></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Price (K)</label>
                                <input
                                    v-model.number="form.price"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Billing Cycle</label>
                                <select
                                    v-model="form.billing_cycle"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    <option value="monthly">Monthly</option>
                                    <option value="quarterly">Quarterly</option>
                                    <option value="annual">Annual</option>
                                    <option value="upgrade">Upgrade</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Professional Level</label>
                                <select
                                    v-model="form.professional_level"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    <option value="">None</option>
                                    <option value="associate">Associate</option>
                                    <option value="professional">Professional</option>
                                    <option value="senior">Senior</option>
                                    <option value="manager">Manager</option>
                                    <option value="director">Director</option>
                                    <option value="executive">Executive</option>
                                    <option value="ambassador">Ambassador</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">LP Requirement</label>
                                <input
                                    v-model.number="form.lp_requirement"
                                    type="number"
                                    min="0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Features (one per line)</label>
                            <textarea
                                v-model="featuresText"
                                rows="4"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Feature 1&#10;Feature 2&#10;Feature 3"
                            ></textarea>
                        </div>

                        <div class="flex items-center">
                            <input
                                v-model="form.is_active"
                                type="checkbox"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <label class="ml-2 text-sm text-gray-700">Active</label>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button
                            @click="closeModal"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            @click="savePackage"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
                        >
                            {{ isEditing ? 'Update' : 'Create' }}
                        </button>
                    </div>
                </div>
            </Modal>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Modal from '@/components/Modal.vue';
import { CheckIcon } from '@heroicons/vue/24/outline';

interface Package {
    id: number;
    name: string;
    description: string;
    price: number;
    billing_cycle: string;
    professional_level: string;
    lp_requirement: number;
    features: string[];
    is_active: boolean;
}

interface Props {
    packages: Package[];
}

const props = defineProps<Props>();

const showModal = ref(false);
const isEditing = ref(false);
const editingPackage = ref<Package | null>(null);

const form = ref({
    name: '',
    description: '',
    price: 0,
    billing_cycle: 'monthly',
    professional_level: '',
    lp_requirement: 0,
    features: [] as string[],
    is_active: true,
});

const featuresText = ref('');

const openCreateModal = () => {
    isEditing.value = false;
    editingPackage.value = null;
    form.value = {
        name: '',
        description: '',
        price: 0,
        billing_cycle: 'monthly',
        professional_level: '',
        lp_requirement: 0,
        features: [],
        is_active: true,
    };
    featuresText.value = '';
    showModal.value = true;
};

const openEditModal = (pkg: Package) => {
    isEditing.value = true;
    editingPackage.value = pkg;
    form.value = {
        name: pkg.name,
        description: pkg.description || '',
        price: pkg.price,
        billing_cycle: pkg.billing_cycle,
        professional_level: pkg.professional_level || '',
        lp_requirement: pkg.lp_requirement || 0,
        features: pkg.features || [],
        is_active: pkg.is_active,
    };
    featuresText.value = (pkg.features || []).join('\n');
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const savePackage = () => {
    // Convert features text to array
    form.value.features = featuresText.value
        .split('\n')
        .map(f => f.trim())
        .filter(f => f.length > 0);

    if (isEditing.value && editingPackage.value) {
        router.put(route('admin.packages.update', editingPackage.value.id), form.value, {
            onSuccess: () => closeModal(),
        });
    } else {
        router.post(route('admin.packages.store'), form.value, {
            onSuccess: () => closeModal(),
        });
    }
};

const togglePackageStatus = (pkg: Package) => {
    router.patch(route('admin.packages.toggle-status', pkg.id), {}, {
        preserveScroll: true,
    });
};

const deletePackage = (pkg: Package) => {
    if (confirm(`Are you sure you want to delete "${pkg.name}"? This action cannot be undone.`)) {
        router.delete(route('admin.packages.destroy', pkg.id));
    }
};

const capitalizeLevel = (level: string) => {
    if (!level) return '';
    return level.charAt(0).toUpperCase() + level.slice(1).toLowerCase();
};
</script>
