<template>
    <AdminLayout title="Permissions Management">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800 leading-tight">
                    Permissions Management
                </h2>
                <button 
                    @click="openCreateModal"
                    class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 flex items-center"
                >
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Create Permission
                </button>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Permissions List -->
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">System Permissions</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            View all permissions in the system. Permissions are assigned to roles.
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Permission
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Description
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Roles
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="permission in permissions" :key="permission.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ permission.name }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ permission.slug }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ permission.description }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ permission.roles_count }} roles</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        <button 
                                            @click="openEditModal(permission)" 
                                            class="text-green-600 hover:text-green-900"
                                        >
                                            Edit
                                        </button>
                                        <button 
                                            @click="deletePermission(permission)" 
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">About Permissions</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p>Permissions define specific actions users can perform. They are assigned to roles, and users inherit permissions from their roles.</p>
                                <p class="mt-2">To modify which permissions a role has, go to the Roles page and click "View Details" on a role.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Permission Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal"></div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="submitForm">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                {{ editingPermission ? 'Edit Permission' : 'Create New Permission' }}
                            </h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Permission Name</label>
                                    <input 
                                        v-model="form.name" 
                                        type="text" 
                                        required
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="e.g., manage_users"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                    <textarea 
                                        v-model="form.description" 
                                        rows="3"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Describe what this permission allows..."
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button 
                                type="submit"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm"
                            >
                                {{ editingPermission ? 'Update' : 'Create' }}
                            </button>
                            <button 
                                type="button"
                                @click="closeModal"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Toast Notification -->
        <div v-if="showToast" 
             :class="toastType === 'success' ? 'bg-emerald-500' : 'bg-red-500'"
             class="fixed bottom-4 right-4 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center space-x-2 animate-fade-in">
            <svg v-if="toastType === 'success'" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <span>{{ toastMessage }}</span>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps<{
    permissions: Array<{
        id: number;
        name: string;
        slug: string;
        description: string;
        roles_count: number;
    }>;
}>();

const page = usePage();
const showModal = ref(false);
const editingPermission = ref<any>(null);
const form = ref({
    name: '',
    description: ''
});

// Toast
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success');

const showToastMessage = (message: string, type = 'success') => {
    toastMessage.value = message;
    toastType.value = type;
    showToast.value = true;
    setTimeout(() => {
        showToast.value = false;
    }, 3000);
};

// Watch for flash messages
if (page.props.flash?.success) {
    showToastMessage(page.props.flash.success as string, 'success');
} else if (page.props.flash?.error) {
    showToastMessage(page.props.flash.error as string, 'error');
}

const openCreateModal = () => {
    editingPermission.value = null;
    form.value = { name: '', description: '' };
    showModal.value = true;
};

const openEditModal = (permission: any) => {
    editingPermission.value = permission;
    form.value = {
        name: permission.name,
        description: permission.description
    };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingPermission.value = null;
    form.value = { name: '', description: '' };
};

const submitForm = () => {
    if (editingPermission.value) {
        router.put(`/admin/role-management/permissions/${editingPermission.value.id}`, form.value, {
            onSuccess: () => {
                closeModal();
                showToastMessage('Permission updated successfully!');
            },
            onError: () => {
                showToastMessage('Failed to update permission', 'error');
            }
        });
    } else {
        router.post('/admin/role-management/permissions', form.value, {
            onSuccess: () => {
                closeModal();
                showToastMessage('Permission created successfully!');
            },
            onError: () => {
                showToastMessage('Failed to create permission', 'error');
            }
        });
    }
};

const deletePermission = (permission: any) => {
    if (!confirm(`Are you sure you want to delete the permission "${permission.name}"? This action cannot be undone.`)) {
        return;
    }

    router.delete(`/admin/role-management/permissions/${permission.id}`, {
        onSuccess: () => {
            showToastMessage('Permission deleted successfully!');
        },
        onError: () => {
            showToastMessage('Failed to delete permission', 'error');
        }
    });
};
</script>
