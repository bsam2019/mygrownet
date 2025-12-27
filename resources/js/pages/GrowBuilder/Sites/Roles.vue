<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, computed } from 'vue';
import {
    ArrowLeftIcon,
    PlusIcon,
    PencilIcon,
    TrashIcon,
    ShieldCheckIcon,
    XMarkIcon,
    UserGroupIcon,
    BriefcaseIcon,
    UserIcon,
} from '@heroicons/vue/24/outline';

interface Permission {
    id: number;
    name: string;
    slug: string;
    description: string;
}

interface Role {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    level: number;
    type: 'staff' | 'client';
    icon: string | null;
    color: string | null;
    is_system: boolean;
    users_count: number;
    permissions: string[];
}

interface Site {
    id: number;
    name: string;
    subdomain: string;
}

const props = defineProps<{
    site: Site;
    roles: Role[];
    permissions: Record<string, Permission[]>;
}>();

const showCreateModal = ref(false);
const editingRole = ref<Role | null>(null);
const deletingRole = ref<Role | null>(null);

const colorOptions = [
    { value: 'indigo', label: 'Indigo', class: 'bg-indigo-500' },
    { value: 'purple', label: 'Purple', class: 'bg-purple-500' },
    { value: 'blue', label: 'Blue', class: 'bg-blue-500' },
    { value: 'cyan', label: 'Cyan', class: 'bg-cyan-500' },
    { value: 'emerald', label: 'Emerald', class: 'bg-emerald-500' },
    { value: 'yellow', label: 'Yellow', class: 'bg-yellow-500' },
    { value: 'amber', label: 'Amber', class: 'bg-amber-500' },
    { value: 'red', label: 'Red', class: 'bg-red-500' },
    { value: 'gray', label: 'Gray', class: 'bg-gray-500' },
];

const createForm = useForm({
    name: '',
    slug: '',
    description: '',
    level: 15,
    type: 'client' as 'staff' | 'client',
    icon: '',
    color: 'gray',
    permissions: [] as string[],
});

const editForm = useForm({
    name: '',
    description: '',
    level: 15,
    type: 'client' as 'staff' | 'client',
    icon: '',
    color: 'gray',
    permissions: [] as string[],
});

const generateSlug = () => {
    createForm.slug = createForm.name
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-|-$/g, '');
};

const submitCreate = () => {
    createForm.post(route('growbuilder.sites.roles.create', props.site.id), {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        },
    });
};

const openEditModal = (role: Role) => {
    editingRole.value = role;
    editForm.name = role.name;
    editForm.description = role.description || '';
    editForm.level = role.level;
    editForm.type = role.type;
    editForm.icon = role.icon || '';
    editForm.color = role.color || 'gray';
    editForm.permissions = [...role.permissions];
};

const submitEdit = () => {
    if (!editingRole.value) return;
    editForm.put(route('growbuilder.sites.roles.update', [props.site.id, editingRole.value.id]), {
        onSuccess: () => {
            editingRole.value = null;
        },
    });
};

const confirmDelete = (role: Role) => {
    deletingRole.value = role;
};

const deleteRole = () => {
    if (!deletingRole.value) return;
    router.delete(route('growbuilder.sites.roles.delete', [props.site.id, deletingRole.value.id]), {
        onSuccess: () => {
            deletingRole.value = null;
        },
    });
};

const getRoleBadgeColor = (color: string | null) => {
    const colors: Record<string, string> = {
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
    return colors[color || 'gray'] || colors.gray;
};

const staffRoles = computed(() => props.roles.filter(r => r.type === 'staff'));
const clientRoles = computed(() => props.roles.filter(r => r.type === 'client'));

const togglePermission = (form: typeof createForm | typeof editForm, slug: string) => {
    const idx = form.permissions.indexOf(slug);
    if (idx > -1) {
        form.permissions.splice(idx, 1);
    } else {
        form.permissions.push(slug);
    }
};

const toggleGroupPermissions = (form: typeof createForm | typeof editForm, group: Permission[]) => {
    const groupSlugs = group.map(p => p.slug);
    const allSelected = groupSlugs.every(s => form.permissions.includes(s));
    
    if (allSelected) {
        form.permissions = form.permissions.filter(p => !groupSlugs.includes(p));
    } else {
        groupSlugs.forEach(s => {
            if (!form.permissions.includes(s)) {
                form.permissions.push(s);
            }
        });
    }
};
</script>

<template>
    <AppLayout>
        <Head :title="`Roles - ${site.name}`" />

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
                            <h1 class="text-2xl font-bold text-gray-900">Roles & Permissions</h1>
                            <p class="text-sm text-gray-500">Manage roles and their permissions for {{ site.name }}</p>
                        </div>
                        <button
                            @click="showCreateModal = true"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
                        >
                            <PlusIcon class="h-5 w-5" aria-hidden="true" />
                            Create Role
                        </button>
                    </div>
                </div>

                <!-- Staff Roles -->
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-4">
                        <BriefcaseIcon class="h-5 w-5 text-purple-600" aria-hidden="true" />
                        <h2 class="text-lg font-semibold text-gray-900">Staff Roles</h2>
                        <span class="text-sm text-gray-500">(Employees who manage the site)</span>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div
                            v-for="role in staffRoles"
                            :key="role.id"
                            class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md transition-shadow"
                        >
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="px-2.5 py-1 text-xs font-medium rounded-full"
                                        :class="getRoleBadgeColor(role.color)"
                                    >
                                        {{ role.name }}
                                    </span>
                                    <span v-if="role.is_system" class="text-xs text-gray-400">System</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button
                                        @click="openEditModal(role)"
                                        class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg"
                                        title="Edit"
                                    >
                                        <PencilIcon class="w-4 h-4" aria-hidden="true" />
                                    </button>
                                    <button
                                        v-if="!role.is_system"
                                        @click="confirmDelete(role)"
                                        class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg"
                                        title="Delete"
                                    >
                                        <TrashIcon class="w-4 h-4" aria-hidden="true" />
                                    </button>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 mb-3">{{ role.description || 'No description' }}</p>
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span>Level {{ role.level }}</span>
                                <span class="flex items-center gap-1">
                                    <UserGroupIcon class="w-3.5 h-3.5" aria-hidden="true" />
                                    {{ role.users_count }} users
                                </span>
                            </div>
                            <div class="mt-3 pt-3 border-t border-gray-100">
                                <p class="text-xs text-gray-500">
                                    {{ role.slug === 'admin' ? 'All permissions' : `${role.permissions.length} permissions` }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Client Roles -->
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <UserIcon class="h-5 w-5 text-emerald-600" aria-hidden="true" />
                        <h2 class="text-lg font-semibold text-gray-900">Client Roles</h2>
                        <span class="text-sm text-gray-500">(Customers and members)</span>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div
                            v-for="role in clientRoles"
                            :key="role.id"
                            class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md transition-shadow"
                        >
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="px-2.5 py-1 text-xs font-medium rounded-full"
                                        :class="getRoleBadgeColor(role.color)"
                                    >
                                        {{ role.name }}
                                    </span>
                                    <span v-if="role.is_system" class="text-xs text-gray-400">System</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button
                                        @click="openEditModal(role)"
                                        class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg"
                                        title="Edit"
                                    >
                                        <PencilIcon class="w-4 h-4" aria-hidden="true" />
                                    </button>
                                    <button
                                        v-if="!role.is_system"
                                        @click="confirmDelete(role)"
                                        class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg"
                                        title="Delete"
                                    >
                                        <TrashIcon class="w-4 h-4" aria-hidden="true" />
                                    </button>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 mb-3">{{ role.description || 'No description' }}</p>
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span>Level {{ role.level }}</span>
                                <span class="flex items-center gap-1">
                                    <UserGroupIcon class="w-3.5 h-3.5" aria-hidden="true" />
                                    {{ role.users_count }} users
                                </span>
                            </div>
                            <div class="mt-3 pt-3 border-t border-gray-100">
                                <p class="text-xs text-gray-500">{{ role.permissions.length }} permissions</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Role Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-900/50" @click="showCreateModal = false"></div>
                <div class="relative bg-white rounded-2xl shadow-xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Create New Role</h3>
                        <button @click="showCreateModal = false" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg">
                            <XMarkIcon class="w-5 h-5" aria-hidden="true" />
                        </button>
                    </div>

                    <form @submit.prevent="submitCreate" class="space-y-5">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                <input
                                    v-model="createForm.name"
                                    @input="generateSlug"
                                    type="text"
                                    required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    placeholder="e.g., Sales Manager"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                                <input
                                    v-model="createForm.slug"
                                    type="text"
                                    required
                                    pattern="[a-z0-9-]+"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    placeholder="sales-manager"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <input
                                v-model="createForm.description"
                                type="text"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="Brief description of this role"
                            />
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                                <select v-model="createForm.type" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                    <option value="staff">Staff (Employee)</option>
                                    <option value="client">Client (Customer)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Level (1-99)</label>
                                <input
                                    v-model.number="createForm.level"
                                    type="number"
                                    min="1"
                                    max="99"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                                <select v-model="createForm.color" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                    <option v-for="c in colorOptions" :key="c.value" :value="c.value">{{ c.label }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Permissions -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Permissions</label>
                            <div class="space-y-4 max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-4">
                                <div v-for="(perms, group) in permissions" :key="group">
                                    <div class="flex items-center gap-2 mb-2">
                                        <button
                                            type="button"
                                            @click="toggleGroupPermissions(createForm, perms)"
                                            class="text-xs text-blue-600 hover:text-blue-800"
                                        >
                                            Toggle all
                                        </button>
                                        <span class="text-sm font-medium text-gray-700 capitalize">{{ group }}</span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 ml-4">
                                        <label
                                            v-for="perm in perms"
                                            :key="perm.slug"
                                            class="flex items-center gap-2 text-sm cursor-pointer"
                                        >
                                            <input
                                                type="checkbox"
                                                :checked="createForm.permissions.includes(perm.slug)"
                                                @change="togglePermission(createForm, perm.slug)"
                                                class="rounded border-gray-300 text-blue-600"
                                            />
                                            <span class="text-gray-600">{{ perm.name }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t">
                            <button type="button" @click="showCreateModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg">
                                Cancel
                            </button>
                            <button type="submit" :disabled="createForm.processing" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                {{ createForm.processing ? 'Creating...' : 'Create Role' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Role Modal -->
        <div v-if="editingRole" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-900/50" @click="editingRole = null"></div>
                <div class="relative bg-white rounded-2xl shadow-xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Edit Role: {{ editingRole.name }}</h3>
                        <button @click="editingRole = null" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg">
                            <XMarkIcon class="w-5 h-5" aria-hidden="true" />
                        </button>
                    </div>

                    <div v-if="editingRole.is_system" class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                        <p class="text-sm text-amber-700">This is a system role. You can edit its name and permissions, but cannot delete it.</p>
                    </div>

                    <form @submit.prevent="submitEdit" class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input v-model="editForm.name" type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <input v-model="editForm.description" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg" />
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                                <select v-model="editForm.type" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                    <option value="staff">Staff</option>
                                    <option value="client">Client</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Level</label>
                                <input
                                    v-model.number="editForm.level"
                                    type="number"
                                    min="1"
                                    :max="editingRole.slug === 'admin' ? 100 : 99"
                                    :disabled="editingRole.slug === 'admin'"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg disabled:bg-gray-100"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                                <select v-model="editForm.color" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                    <option v-for="c in colorOptions" :key="c.value" :value="c.value">{{ c.label }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Permissions (not for admin) -->
                        <div v-if="editingRole.slug !== 'admin'">
                            <label class="block text-sm font-medium text-gray-700 mb-3">Permissions</label>
                            <div class="space-y-4 max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-4">
                                <div v-for="(perms, group) in permissions" :key="group">
                                    <div class="flex items-center gap-2 mb-2">
                                        <button type="button" @click="toggleGroupPermissions(editForm, perms)" class="text-xs text-blue-600 hover:text-blue-800">
                                            Toggle all
                                        </button>
                                        <span class="text-sm font-medium text-gray-700 capitalize">{{ group }}</span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 ml-4">
                                        <label v-for="perm in perms" :key="perm.slug" class="flex items-center gap-2 text-sm cursor-pointer">
                                            <input
                                                type="checkbox"
                                                :checked="editForm.permissions.includes(perm.slug)"
                                                @change="togglePermission(editForm, perm.slug)"
                                                class="rounded border-gray-300 text-blue-600"
                                            />
                                            <span class="text-gray-600">{{ perm.name }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="p-3 bg-indigo-50 border border-indigo-200 rounded-lg">
                            <p class="text-sm text-indigo-700">Admin role has all permissions by default.</p>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t">
                            <button type="button" @click="editingRole = null" class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg">
                                Cancel
                            </button>
                            <button type="submit" :disabled="editForm.processing" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                {{ editForm.processing ? 'Saving...' : 'Save Changes' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="deletingRole" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-900/50" @click="deletingRole = null"></div>
                <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Delete Role</h3>
                    <p class="text-sm text-gray-600 mb-6">
                        Are you sure you want to delete the <span class="font-medium">{{ deletingRole.name }}</span> role?
                        <span v-if="deletingRole.users_count > 0" class="text-red-600 block mt-2">
                            Warning: {{ deletingRole.users_count }} user(s) are assigned to this role.
                        </span>
                    </p>
                    <div class="flex justify-end gap-3">
                        <button @click="deletingRole = null" class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg">
                            Cancel
                        </button>
                        <button @click="deleteRole" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">
                            Delete Role
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
