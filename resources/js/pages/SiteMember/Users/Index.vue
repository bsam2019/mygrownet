<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import SiteMemberLayout from '@/layouts/SiteMemberLayout.vue';
import { ref, computed } from 'vue';
import { PencilIcon, TrashIcon, UsersIcon, XMarkIcon, ShieldCheckIcon } from '@heroicons/vue/24/outline';

interface Role {
    id: number;
    name: string;
    slug: string;
    level: number;
    type: 'staff' | 'client';
    color: string | null;
}

interface User {
    id: number;
    name: string;
    email: string;
    status: string;
    role: Role | null;
    created_at: string;
    last_login_at: string | null;
}

interface Props {
    site: { id: number; name: string; subdomain: string; theme: { primaryColor?: string } | null };
    settings: { navigation?: { logo?: string } } | null;
    user: { id: number; name: string; email: string; role: any; permissions: string[] };
    users: { data: User[]; links: any; meta: any };
    roles: Role[];
}

const props = defineProps<Props>();
const primaryColor = computed(() => props.site.theme?.primaryColor || '#2563eb');

const editingUser = ref<User | null>(null);
const deletingUser = ref<User | null>(null);

const editForm = useForm({ site_role_id: 0 });

const formatDate = (date: string | null) => {
    if (!date) return 'Never';
    return new Date(date).toLocaleDateString('en-ZM', { year: 'numeric', month: 'short', day: 'numeric' });
};

const getRoleBadgeColor = (role: Role | null) => {
    if (!role) return 'bg-gray-100 text-gray-700';
    const colorMap: Record<string, string> = {
        indigo: 'bg-indigo-100 text-indigo-700',
        purple: 'bg-purple-100 text-purple-700',
        blue: 'bg-blue-100 text-blue-700',
        emerald: 'bg-emerald-100 text-emerald-700',
        yellow: 'bg-yellow-100 text-yellow-700',
        amber: 'bg-amber-100 text-amber-700',
        gray: 'bg-gray-100 text-gray-700',
    };
    return colorMap[role.color || 'gray'] || 'bg-gray-100 text-gray-700';
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

const openEditModal = (user: User) => {
    editingUser.value = user;
    editForm.site_role_id = user.role?.id || 0;
};

const submitEdit = () => {
    if (!editingUser.value) return;
    editForm.put(`/sites/${props.site.subdomain}/dashboard/users/${editingUser.value.id}/role`, {
        onSuccess: () => { editingUser.value = null; },
    });
};

const deleteUser = () => {
    if (!deletingUser.value) return;
    router.delete(`/sites/${props.site.subdomain}/dashboard/users/${deletingUser.value.id}`, {
        onSuccess: () => { deletingUser.value = null; },
    });
};
</script>

<template>
    <SiteMemberLayout :site="site" :settings="settings" :user="user" title="Users">
        <Head :title="`Users - ${site.name}`" />

        <div class="max-w-5xl mx-auto">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Site Users</h1>
                <p class="text-gray-500">Manage users and their roles</p>
            </div>

            <!-- Users Table -->
            <div v-if="users.data.length > 0" class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Login</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="u in users.data" :key="u.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-semibold" :style="{ backgroundColor: primaryColor }">
                                        {{ u.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ u.name }}</p>
                                        <p class="text-sm text-gray-500">{{ u.email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-full" :class="getRoleBadgeColor(u.role)">
                                    <ShieldCheckIcon v-if="u.role?.level >= 100" class="w-3 h-3" aria-hidden="true" />
                                    {{ u.role?.name || 'No Role' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full capitalize" :class="getStatusBadge(u.status)">{{ u.status }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ formatDate(u.last_login_at) }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button @click="openEditModal(u)" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg" title="Change Role">
                                        <PencilIcon class="w-4 h-4" aria-hidden="true" />
                                    </button>
                                    <button v-if="u.id !== user.id" @click="deletingUser = u" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg" title="Delete">
                                        <TrashIcon class="w-4 h-4" aria-hidden="true" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-white rounded-xl border border-gray-200 p-12 text-center">
                <UsersIcon class="w-16 h-16 mx-auto text-gray-300 mb-4" aria-hidden="true" />
                <h3 class="text-lg font-medium text-gray-900 mb-2">No users yet</h3>
                <p class="text-gray-500">Users will appear here when they register.</p>
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
                    <p class="text-sm text-gray-600 mb-4">Change role for <span class="font-medium">{{ editingUser.name }}</span></p>
                    <form @submit.prevent="submitEdit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">New Role</label>
                            <select v-model="editForm.site_role_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }} ({{ role.type }})</option>
                            </select>
                        </div>
                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" @click="editingUser = null" class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg">Cancel</button>
                            <button type="submit" :disabled="editForm.processing" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                {{ editForm.processing ? 'Saving...' : 'Save' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div v-if="deletingUser" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-900/50" @click="deletingUser = null"></div>
                <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Delete User</h3>
                    <p class="text-sm text-gray-600 mb-6">Are you sure you want to delete <span class="font-medium">{{ deletingUser.name }}</span>?</p>
                    <div class="flex justify-end gap-3">
                        <button @click="deletingUser = null" class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg">Cancel</button>
                        <button @click="deleteUser" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </SiteMemberLayout>
</template>
