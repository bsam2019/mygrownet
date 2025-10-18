<template>
    <AdminLayout :title="`Role: ${role.name}`">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800 leading-tight">
                    Role: {{ role.name }}
                </h2>
                <Link :href="route('admin.role-management.roles.index')" class="text-sm text-blue-600 hover:text-blue-800">
                    ← Back to Roles
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Role Details -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Role Details</h3>
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Name</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ role.name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Slug</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ role.slug }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Description</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ role.description }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Type</dt>
                            <dd class="mt-1">
                                <span v-if="role.is_system" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    System Role
                                </span>
                                <span v-else class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    Custom Role
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Users with this role</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ role.users_count }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Permissions Management -->
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Permissions</h3>
                        <button 
                            v-if="!role.is_system || role.name !== 'superadmin'"
                            @click="editMode = !editMode" 
                            class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700"
                        >
                            {{ editMode ? 'Cancel' : 'Edit Permissions' }}
                        </button>
                    </div>

                    <div v-if="role.is_system && role.name === 'superadmin'" class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-4">
                        <p class="text-sm text-yellow-800">
                            Superadmin role permissions cannot be modified. This role always has all permissions.
                        </p>
                    </div>

                    <!-- Edit Mode -->
                    <form v-if="editMode" @submit.prevent="updatePermissions" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div v-for="permission in all_permissions" :key="permission.id" class="flex items-start">
                                <input 
                                    :id="`permission-${permission.id}`"
                                    v-model="selectedPermissions" 
                                    :value="permission.id"
                                    type="checkbox" 
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mt-1"
                                />
                                <label :for="`permission-${permission.id}`" class="ml-2 block text-sm text-gray-900">
                                    <div class="font-medium">{{ permission.name }}</div>
                                    <div class="text-xs text-gray-500">{{ permission.description }}</div>
                                </label>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 pt-4 border-t">
                            <button 
                                type="button" 
                                @click="editMode = false"
                                class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit"
                                class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700"
                            >
                                Save Permissions
                            </button>
                        </div>
                    </form>

                    <!-- View Mode -->
                    <div v-else>
                        <div v-if="role.permissions.length === 0" class="text-sm text-gray-500">
                            No permissions assigned to this role.
                        </div>
                        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            <div v-for="permission in role.permissions" :key="permission.id" class="flex items-center p-3 bg-gray-50 rounded-md">
                                <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ permission.name }}</div>
                                    <div class="text-xs text-gray-500">{{ permission.description }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps<{
    role: {
        id: number;
        name: string;
        slug: string;
        description: string;
        is_system: boolean;
        permissions: Array<{
            id: number;
            name: string;
            description: string;
        }>;
        users_count: number;
    };
    all_permissions: Array<{
        id: number;
        name: string;
        description: string;
    }>;
}>();

const editMode = ref(false);
const selectedPermissions = ref<number[]>(props.role.permissions.map(p => p.id));

const updatePermissions = () => {
    router.post(route('admin.role-management.roles.permissions.update', props.role.id), {
        permissions: selectedPermissions.value
    }, {
        onSuccess: () => {
            editMode.value = false;
        }
    });
};
</script>
