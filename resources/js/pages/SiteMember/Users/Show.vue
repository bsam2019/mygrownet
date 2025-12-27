<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import SiteMemberLayout from '@/layouts/SiteMemberLayout.vue';
import { ref, computed } from 'vue';
import { ArrowLeftIcon, PencilIcon, KeyIcon, TrashIcon, ShieldCheckIcon, XMarkIcon } from '@heroicons/vue/24/outline';

interface Role {
    id: number;
    name: string;
    slug: string;
    level: number;
    type: 'staff' | 'client';
    color: string | null;
}

interface TargetUser {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    status: string;
    role: Role | null;
    created_at: string;
    last_login_at: string | null;
    login_count: number;
    orders_count: number;
}

interface Props {
    site: { id: number; name: string; subdomain: string; theme: { primaryColor?: string } | null };
    settings: { navigation?: { logo?: string } } | null;
    user: { id: number; name: string; email: string; role: any; permissions: string[] };
    targetUser: TargetUser;
    roles: Role[];
}

const props = defineProps<Props>();
const primaryColor = computed(() => props.site.theme?.primaryColor || '#2563eb');

const showEditModal = ref(false);
const showPasswordModal = ref(false);
const showDeleteModal = ref(false);

const editForm = useForm({
    name: props.targetUser.name,
    email: props.targetUser.email,
    phone: props.targetUser.phone || '',
    status: props.targetUser.status,
});

const roleForm = useForm({
    site_role_id: props.targetUser.role?.id || 0,
});

const passwordForm = useForm({
    password: '',
    password_confirmation: '',
});

const formatDate = (date: string | null) => {
    if (!date) return 'Never';
    return new Date(date).toLocaleDateString('en-ZM', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' });
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

const submitEdit = () => {
    editForm.put(`/sites/${props.site.subdomain}/dashboard/users/${props.targetUser.id}`, {
        onSuccess: () => { showEditModal.value = false; },
    });
};

const submitRole = () => {
    roleForm.put(`/sites/${props.site.subdomain}/dashboard/users/${props.targetUser.id}/role`);
};

const submitPassword = () => {
    passwordForm.post(`/sites/${props.site.subdomain}/dashboard/users/${props.targetUser.id}/reset-password`, {
        onSuccess: () => { showPasswordModal.value = false; passwordForm.reset(); },
    });
};

const deleteUser = () => {
    router.delete(`/sites/${props.site.subdomain}/dashboard/users/${props.targetUser.id}`);
};

const canDelete = computed(() => props.targetUser.id !== props.user.id);
const userInitials = computed(() => props.targetUser.name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2));
</script>

<template>
    <SiteMemberLayout :site="site" :settings="settings" :user="user" title="User Details">
        <Head :title="`${targetUser.name} - ${site.name}`" />

        <div class="max-w-3xl mx-auto">
            <Link :href="`/sites/${site.subdomain}/dashboard/users`" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-4">
                <ArrowLeftIcon class="w-4 h-4" aria-hidden="true" />
                Back to Users
            </Link>

            <!-- User Header -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-full flex items-center justify-center text-white text-xl font-bold" :style="{ backgroundColor: primaryColor }">
                            {{ userInitials }}
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">{{ targetUser.name }}</h1>
                            <p class="text-gray-500">{{ targetUser.email }}</p>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-full" :class="getRoleBadgeColor(targetUser.role)">
                                    <ShieldCheckIcon v-if="targetUser.role?.level >= 100" class="w-3 h-3" aria-hidden="true" />
                                    {{ targetUser.role?.name || 'No Role' }}
                                </span>
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full capitalize" :class="getStatusBadge(targetUser.status)">
                                    {{ targetUser.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <button @click="showEditModal = true" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg" title="Edit">
                        <PencilIcon class="w-5 h-5" aria-hidden="true" />
                    </button>
                </div>
            </div>

            <!-- User Info -->
            <div class="grid md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Account Info</h3>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Phone</dt>
                            <dd class="font-medium text-gray-900">{{ targetUser.phone || 'Not set' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Member Since</dt>
                            <dd class="font-medium text-gray-900">{{ formatDate(targetUser.created_at) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Last Login</dt>
                            <dd class="font-medium text-gray-900">{{ formatDate(targetUser.last_login_at) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Login Count</dt>
                            <dd class="font-medium text-gray-900">{{ targetUser.login_count }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Activity</h3>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Orders</dt>
                            <dd class="font-medium text-gray-900">{{ targetUser.orders_count }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Role Management -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
                <h3 class="font-semibold text-gray-900 mb-4">Role & Permissions</h3>
                <form @submit.prevent="submitRole" class="flex items-end gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Assigned Role</label>
                        <select v-model="roleForm.site_role_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }} ({{ role.type }})</option>
                        </select>
                    </div>
                    <button type="submit" :disabled="roleForm.processing" class="px-4 py-2 text-white font-medium rounded-lg disabled:opacity-50" :style="{ backgroundColor: primaryColor }">
                        Update Role
                    </button>
                </form>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-900 mb-4">Actions</h3>
                <div class="flex flex-wrap gap-3">
                    <button @click="showPasswordModal = true" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50">
                        <KeyIcon class="w-4 h-4" aria-hidden="true" />
                        Reset Password
                    </button>
                    <button v-if="canDelete" @click="showDeleteModal = true" class="inline-flex items-center gap-2 px-4 py-2 border border-red-300 text-red-600 font-medium rounded-lg hover:bg-red-50">
                        <TrashIcon class="w-4 h-4" aria-hidden="true" />
                        Delete User
                    </button>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-900/50" @click="showEditModal = false"></div>
                <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Edit User</h3>
                        <button @click="showEditModal = false" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg">
                            <XMarkIcon class="w-5 h-5" aria-hidden="true" />
                        </button>
                    </div>
                    <form @submit.prevent="submitEdit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input v-model="editForm.name" type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input v-model="editForm.email" type="email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input v-model="editForm.phone" type="tel" class="w-full px-4 py-2 border border-gray-300 rounded-lg" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select v-model="editForm.status" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="pending">Pending</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>
                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" @click="showEditModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg">Cancel</button>
                            <button type="submit" :disabled="editForm.processing" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Password Modal -->
        <div v-if="showPasswordModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-900/50" @click="showPasswordModal = false"></div>
                <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Reset Password</h3>
                        <button @click="showPasswordModal = false" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg">
                            <XMarkIcon class="w-5 h-5" aria-hidden="true" />
                        </button>
                    </div>
                    <form @submit.prevent="submitPassword" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                            <input v-model="passwordForm.password" type="password" required minlength="8" class="w-full px-4 py-2 border border-gray-300 rounded-lg" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                            <input v-model="passwordForm.password_confirmation" type="password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg" />
                        </div>
                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" @click="showPasswordModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg">Cancel</button>
                            <button type="submit" :disabled="passwordForm.processing" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50">Reset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-900/50" @click="showDeleteModal = false"></div>
                <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Delete User</h3>
                    <p class="text-sm text-gray-600 mb-6">Are you sure you want to delete <span class="font-medium">{{ targetUser.name }}</span>? This cannot be undone.</p>
                    <div class="flex justify-end gap-3">
                        <button @click="showDeleteModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg">Cancel</button>
                        <button @click="deleteUser" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </SiteMemberLayout>
</template>
