<template>
    <AdminLayout title="User Role Assignment">
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800 leading-tight">
                User Role Assignment
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Users List -->
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Assign Roles to Users</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Manage user roles. Each user can have one or more roles that determine their access level.
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        User
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Email
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Professional Level
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Current Roles
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="user in users.data" :key="user.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ user.name }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ user.email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                            Level {{ user.professional_level || 1 }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            <span v-for="role in user.roles" :key="role" class="group relative px-2 inline-flex items-center text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                {{ role }}
                                                <button 
                                                    @click.stop="removeRole(user, role)"
                                                    class="ml-1 text-blue-600 hover:text-blue-900"
                                                    title="Remove role"
                                                >
                                                    ×
                                                </button>
                                            </span>
                                            <span v-if="!user.roles || user.roles.length === 0" class="text-sm text-gray-500">
                                                No roles assigned
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button @click="openAssignModal(user)" class="px-3 py-1 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700">
                                            Assign Role
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 flex justify-between sm:hidden">
                                <Link v-if="users.prev_page_url" :href="users.prev_page_url" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Previous
                                </Link>
                                <Link v-if="users.next_page_url" :href="users.next_page_url" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Next
                                </Link>
                            </div>
                            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-gray-700">
                                        Showing
                                        <span class="font-medium">{{ users.from }}</span>
                                        to
                                        <span class="font-medium">{{ users.to }}</span>
                                        of
                                        <span class="font-medium">{{ users.total }}</span>
                                        results
                                    </p>
                                </div>
                                <div>
                                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                        <Link v-if="users.prev_page_url" :href="users.prev_page_url" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                            Previous
                                        </Link>
                                        <Link v-if="users.next_page_url" :href="users.next_page_url" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                            Next
                                        </Link>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="mt-6 bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800">Roles vs Professional Levels</h3>
                            <div class="mt-2 text-sm text-green-700">
                                <p><strong>Roles</strong> (superadmin, admin, employee, member) control what users can ACCESS in the system.</p>
                                <p class="mt-1"><strong>Professional Levels</strong> (1-7: Associate → Ambassador) track user PROGRESSION and determine commission rates.</p>
                                <p class="mt-1">They are separate systems - don't confuse them!</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assign Role Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showModal = false"></div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            Assign Role to {{ selectedUser?.name }}
                        </h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Select Role</label>
                                <select v-model="selectedRole" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">-- Select a role --</option>
                                    <option v-for="role in roles" :key="role.id" :value="role.name">
                                        {{ role.name }} - {{ role.description }}
                                    </option>
                                </select>
                            </div>

                            <div v-if="selectedUser?.roles?.length > 0" class="bg-gray-50 p-3 rounded">
                                <p class="text-sm font-medium text-gray-700 mb-2">Current Roles:</p>
                                <div class="flex flex-wrap gap-1">
                                    <span v-for="role in selectedUser.roles" :key="role" class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ role }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button 
                            @click="assignRole"
                            :disabled="!selectedRole"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Assign Role
                        </button>
                        <button 
                            @click="showModal = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps<{
    users: {
        data: Array<{
            id: number;
            name: string;
            email: string;
            professional_level: number;
            roles: string[];
        }>;
        from: number;
        to: number;
        total: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    roles: Array<{
        id: number;
        name: string;
        description: string;
    }>;
}>();

import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const showModal = ref(false);
const selectedUser = ref<any>(null);
const selectedRole = ref('');

const openAssignModal = (user: any) => {
    selectedUser.value = user;
    selectedRole.value = '';
    showModal.value = true;
};

const assignRole = () => {
    if (!selectedRole.value || !selectedUser.value) return;
    
    router.post(route('admin.role-management.users.assign-role', selectedUser.value.id), {
        role: selectedRole.value
    }, {
        onSuccess: () => {
            showModal.value = false;
            selectedUser.value = null;
            selectedRole.value = '';
        }
    });
};

const removeRole = (user: any, role: string) => {
    if (!confirm(`Remove role "${role}" from ${user.name}?`)) return;
    
    router.post(route('admin.role-management.users.remove-role', user.id), {
        role: role
    });
};
</script>
