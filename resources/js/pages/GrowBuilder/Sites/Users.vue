<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, computed } from 'vue';
import {
    ArrowLeftIcon,
    UserPlusIcon,
    PencilIcon,
    TrashIcon,
    ShieldCheckIcon,
    XMarkIcon,
    CheckIcon,
    MagnifyingGlassIcon,
} from '@heroicons/vue/24/outline';

interface Role {
    id: number;
    name: string;
    slug: string;
    level: number;
    description: string | null;
    type: 'staff' | 'client';
    color: string | null;
    icon: string | null;
    is_system: boolean;
}

interface User {
    id: number;
    name: string;
    email: string;
    status: string;
    site_role_id: number | null;
    role: Role | null;
    created_at: string;
    last_login_at: string | null;
}

interface Site {
    id: number;
    name: string;
    subdomain: string;
}

const props = defineProps<{
    site: Site;
    users: { data: User[]; links: any; meta: any };
    roles: Role[];
}>();

const showCreateModal = ref(false);
const editingUser = ref<User | null>(null);
const deletingUser = ref<User | null>(null);

const createForm = useForm({
    name: '',
    email: '',
    password: '',
    site_role_id: props.roles.find(r => r.slug === 'member')?.id || props.roles[0]?.id,
});

const editForm = useForm({
    site_role_id: 0,
});

const submitCreate = () => {
    createForm.post(route('growbuilder.sites.users.create', props.site.id), {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        },
    });
};

const openEditModal = (user: User) => {
    editingUser.value = user;
    editForm.site_role_id = user.site_role_id || 0;
};

const submitEdit = () => {
    if (!editingUser.value) return;
    editForm.put(route('growbuilder.sites.users.role', [props.site.id, editingUser.value.id]), {
        onSuccess: () => {
            editingUser.value = null;
        },
    });
};

const confirmDelete = (user: User) => {
    deletingUser.value = user;
};

const deleteUser = () => {
    if (!deletingUser.value) return;
    router.delete(route('growbuilder.sites.users.delete', [props.site.id, deletingUser.value.id]), {
        onSuccess: () => {
            deletingUser.value = null;
        },
    });
};

const getRoleBadgeColor = (role: Role | null) => {
    if (!role) return 'bg-gray-100 text-gray-700';
    // Use role color if available
    if (role.color) {
        const colorMap: Record<string, string> = {
            indigo: 'bg-indigo-100 text-indigo-700',
            purple: 'bg-purple-100 text-purple-700',
            blue: 'bg-blue-100 text-blue-700',
            cyan: 'bg-cyan-100 text-cyan-700',
            emerald: 'bg-emerald-100 text-emerald-700',
            yellow: 'bg-yellow-100 text-yellow-700',
            amber: 'bg-amber-100 text-amber-700',
            red: 'bg-red-100 text-red-700',
            gray: 'bg-gray-100 text-gray-700',
        };
        return colorMap[role.color] || 'bg-gray-100 text-gray-700';
    }
    // Fallback to level-based colors
    if (role.level >= 100) return 'bg-indigo-100 text-indigo-700';
    if (role.level >= 50) return 'bg-blue-100 text-blue-700';
    if (role.level >= 20) return 'bg-emerald-100 text-emerald-700';
    return 'bg-gray-100 text-gray-700';
};

const getStatusBadge = (status: string) => {
    const badges: Record<string, string> = {
        active: 'bg-emerald-100 text-emerald-700',
        inactive: 'bg-gray-100 text-gray-700',
        pending: 'bg-amber-100 text-amber-700',
        suspended: 'bg-red-100 text-red-700',
    };
    return badges[status] || 'bg-gray-100 text-gray-700';
};

const formatDate = (date: string | null) => {
    if (!date) return 'Never';
    return new Date(date).toLocaleDateString('en-ZM', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <AppLayout>
        <Head :title="`Users - ${site.name}`" />

        <div class="py-6">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        :href="route('growbuilder.sites.settings', site.id)"
                        class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-4"
                    >
                        <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                        Back to Settings
                    </Link>

                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Site Users</h1>
                            <p class="text-sm text-gray-500">Manage users who can access {{ site.name }}'s dashboard</p>
                        </div>
                        <button
                            @click="showCreateModal = true"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
                        >
                            <UserPlusIcon class="h-5 w-5" aria-hidden="true" />
                            Add User
                        </button>
                    </div>
                </div>

                <!-- Roles Legend -->
                <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Available Roles</h3>
                    <div class="flex flex-wrap gap-3">
                        <div v-for="role in roles" :key="role.id" class="flex items-center gap-2">
                            <span 
                                class="px-2 py-1 text-xs font-medium rounded-full"
                                :class="getRoleBadgeColor(role)"
                            >
                                {{ role.name }}
                            </span>
                            <span class="text-xs text-gray-500">{{ role.description || `Level ${role.level}` }}</span>
                        </div>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Login</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-semibold">
                                            {{ user.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ user.name }}</p>
                                            <p class="text-sm text-gray-500">{{ user.email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span 
                                        class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full"
                                        :class="getRoleBadgeColor(user.role)"
                                    >
                                        <ShieldCheckIcon v-if="user.role?.level >= 100" class="w-3 h-3" aria-hidden="true" />
                                        {{ user.role?.name || 'No Role' }}
                                    </span>
                                    <span v-if="user.role?.type" class="ml-1 text-xs text-gray-400">
                                        ({{ user.role.type }})
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span 
                                        class="px-2 py-1 text-xs font-medium rounded-full capitalize"
                                        :class="getStatusBadge(user.status)"
                                    >
                                        {{ user.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ formatDate(user.last_login_at) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            @click="openEditModal(user)"
                                            class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                            title="Change Role"
                                        >
                                            <PencilIcon class="w-4 h-4" aria-hidden="true" />
                                        </button>
                                        <button
                                            @click="confirmDelete(user)"
                                            class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                            title="Delete User"
                                        >
                                            <TrashIcon class="w-4 h-4" aria-hidden="true" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="users.data.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    No users yet. Click "Add User" to create the first one.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Create User Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-900/50" @click="showCreateModal = false"></div>
                <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Add New User</h3>
                        <button @click="showCreateModal = false" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg">
                            <XMarkIcon class="w-5 h-5" aria-hidden="true" />
                        </button>
                    </div>

                    <form @submit.prevent="submitCreate" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input
                                v-model="createForm.name"
                                type="text"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            />
                            <p v-if="createForm.errors.name" class="mt-1 text-sm text-red-600">{{ createForm.errors.name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input
                                v-model="createForm.email"
                                type="email"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            />
                            <p v-if="createForm.errors.email" class="mt-1 text-sm text-red-600">{{ createForm.errors.email }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <input
                                v-model="createForm.password"
                                type="password"
                                required
                                minlength="8"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            />
                            <p v-if="createForm.errors.password" class="mt-1 text-sm text-red-600">{{ createForm.errors.password }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                            <select
                                v-model="createForm.site_role_id"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option v-for="role in roles" :key="role.id" :value="role.id">
                                    {{ role.name }} (Level {{ role.level }})
                                </option>
                            </select>
                            <p v-if="createForm.errors.site_role_id" class="mt-1 text-sm text-red-600">{{ createForm.errors.site_role_id }}</p>
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <button
                                type="button"
                                @click="showCreateModal = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="createForm.processing"
                                class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors"
                            >
                                {{ createForm.processing ? 'Creating...' : 'Create User' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Role Modal -->
        <div v-if="editingUser" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-900/50" @click="editingUser = null"></div>
                <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Change Role</h3>
                        <button @click="editingUser = null" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg">
                            <XMarkIcon class="w-5 h-5" aria-hidden="true" />
                        </button>
                    </div>

                    <p class="text-sm text-gray-600 mb-4">
                        Change role for <span class="font-medium">{{ editingUser.name }}</span>
                    </p>

                    <form @submit.prevent="submitEdit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">New Role</label>
                            <select
                                v-model="editForm.site_role_id"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option v-for="role in roles" :key="role.id" :value="role.id">
                                    {{ role.name }} (Level {{ role.level }})
                                </option>
                            </select>
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <button
                                type="button"
                                @click="editingUser = null"
                                class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="editForm.processing"
                                class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors"
                            >
                                {{ editForm.processing ? 'Saving...' : 'Save Changes' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="deletingUser" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-900/50" @click="deletingUser = null"></div>
                <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Delete User</h3>
                    <p class="text-sm text-gray-600 mb-6">
                        Are you sure you want to delete <span class="font-medium">{{ deletingUser.name }}</span>? 
                        This action cannot be undone.
                    </p>

                    <div class="flex justify-end gap-3">
                        <button
                            @click="deletingUser = null"
                            class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            @click="deleteUser"
                            class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors"
                        >
                            Delete User
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
