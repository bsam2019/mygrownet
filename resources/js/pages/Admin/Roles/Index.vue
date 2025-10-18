<template>
    <AdminLayout title="Roles Management">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800 leading-tight">
                    Roles Management
                </h2>
                <button 
                    @click="openCreateModal"
                    class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 flex items-center"
                >
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Create Role
                </button>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Roles List -->
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">System Roles</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Manage roles and their permissions. Roles control what users can access in the system.
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Role
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Description
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Users
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Type
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="role in roles" :key="role.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ role.name }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ role.description }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ role.users_count }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span v-if="role.is_system" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            System
                                        </span>
                                        <span v-else class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            Custom
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        <Link :href="route('admin.role-management.roles.show', role.id)" class="text-blue-600 hover:text-blue-900">
                                            View
                                        </Link>
                                        <button 
                                            v-if="!role.is_system"
                                            @click="openEditModal(role)" 
                                            class="text-green-600 hover:text-green-900"
                                        >
                                            Edit
                                        </button>
                                        <button 
                                            v-if="!role.is_system"
                                            @click="deleteRole(role)" 
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
                <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">About Roles</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p>Roles control access to different parts of the system. System roles (superadmin, admin, employee, member) cannot be deleted.</p>
                                <p class="mt-2"><strong>Note:</strong> Professional Levels (Associate, Professional, Senior, Manager, Director, Executive, Ambassador) are NOT roles - they are progression levels stored in users.professional_level (1-7).</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Role Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal"></div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="submitForm">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                {{ editingRole ? 'Edit Role' : 'Create New Role' }}
                            </h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Role Name</label>
                                    <input 
                                        v-model="form.name" 
                                        type="text" 
                                        required
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="e.g., moderator"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                    <textarea 
                                        v-model="form.description" 
                                        rows="3"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Describe what this role is for..."
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button 
                                type="submit"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm"
                            >
                                {{ editingRole ? 'Update' : 'Create' }}
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
import { Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps<{
    roles: Array<{
        id: number;
        name: string;
        slug: string;
        description: string;
        users_count: number;
        is_system: boolean;
    }>;
}>();

const page = usePage();
const showModal = ref(false);
const editingRole = ref<any>(null);
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
    editingRole.value = null;
    form.value = { name: '', description: '' };
    showModal.value = true;
};

const openEditModal = (role: any) => {
    editingRole.value = role;
    form.value = {
        name: role.name,
        description: role.description
    };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingRole.value = null;
    form.value = { name: '', description: '' };
};

const submitForm = () => {
    if (editingRole.value) {
        // Update role (you'll need to add this route)
        router.put(`/admin/role-management/roles/${editingRole.value.id}`, form.value, {
            onSuccess: () => {
                closeModal();
                showToastMessage('Role updated successfully!');
            },
            onError: () => {
                showToastMessage('Failed to update role', 'error');
            }
        });
    } else {
        // Create role (you'll need to add this route)
        router.post('/admin/role-management/roles', form.value, {
            onSuccess: () => {
                closeModal();
                showToastMessage('Role created successfully!');
            },
            onError: () => {
                showToastMessage('Failed to create role', 'error');
            }
        });
    }
};

const deleteRole = (role: any) => {
    if (!confirm(`Are you sure you want to delete the role "${role.name}"? This action cannot be undone.`)) {
        return;
    }

    router.delete(`/admin/role-management/roles/${role.id}`, {
        onSuccess: () => {
            showToastMessage('Role deleted successfully!');
        },
        onError: () => {
            showToastMessage('Failed to delete role', 'error');
        }
    });
};
</script>
