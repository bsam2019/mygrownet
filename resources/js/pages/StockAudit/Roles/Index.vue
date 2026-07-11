<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import StockAuditLayout from '@/layouts/StockAuditLayout.vue';
import { ref, computed } from 'vue';
import {
    PlusIcon, ShieldCheckIcon, KeyIcon, PencilIcon, TrashIcon,
    XMarkIcon, CheckIcon, ChevronDownIcon, ChevronRightIcon,
    InformationCircleIcon,
} from '@heroicons/vue/24/outline';

interface Role {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    permissions: string[];
    is_system: boolean;
    created_at: string;
    updated_at: string;
}

interface Props {
    roles: Role[];
    permissions: string[];
}

const props = defineProps<Props>();

const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingRole = ref<Role | null>(null);
const openCompanyDropdown = ref(false);

const createForm = useForm({
    name: '',
    slug: '',
    description: '',
    permissions: [] as string[],
});

const editForm = useForm({
    name: '',
    description: '',
    permissions: [] as string[],
});

// Group permissions by category
const permissionCategories = computed(() => {
    const categories: Record<string, { value: string; label: string }[]> = {};
    const categoryMap: Record<string, string> = {
        'items.': 'Items',
        'sales.': 'Sales',
        'purchases.': 'Purchases',
        'cash.': 'Cash Register',
        'counts.': 'Physical Counts',
        'audits.': 'Audits',
        'movements.': 'Stock Movements',
        'reports.': 'Reports',
        'suppliers.': 'Suppliers',
        'departments.': 'Departments',
        'bins.': 'Bins',
        'employees.': 'Employees',
        'roles.': 'Roles',
        'company.': 'Company Settings',
        'dashboard.': 'Dashboard',
    };

    props.permissions.forEach(perm => {
        let category = 'Other';
        for (const [prefix, cat] of Object.entries(categoryMap)) {
            if (perm.startsWith(prefix)) {
                category = cat;
                break;
            }
        }
        if (!categories[category]) categories[category] = [];
        categories[category].push({ value: perm, label: perm.replace(/\./g, ' ').replace(/\b\w/g, l => l.toUpperCase()) });
    });

    return Object.entries(categories).map(([category, perms]) => ({
        category,
        permissions: perms.sort((a, b) => a.label.localeCompare(b.label)),
    }));
});

const openCreateModal = () => {
    createForm.reset();
    showCreateModal.value = true;
};

const openEditModal = (role: Role) => {
    editingRole.value = role;
    editForm.name = role.name;
    editForm.description = role.description || '';
    editForm.permissions = [...role.permissions];
    showEditModal.value = true;
};

const submitCreate = () => {
    createForm.post(route('stock-audit.roles.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
        },
    });
};

const submitEdit = () => {
    if (!editingRole.value) return;
    editForm.put(route('stock-audit.roles.update', editingRole.value.id), {
        onSuccess: () => {
            showEditModal.value = false;
            editingRole.value = null;
        },
    });
};

const handleDelete = (role: Role) => {
    if (!confirm(`Delete "${role.name}"? This action cannot be undone.`)) return;
    useForm({}).delete(route('stock-audit.roles.destroy', role.id));
};

const generateSlug = (name: string): string => {
    return name.toLowerCase().replace(/\s+/g, '_').replace(/[^a-z0-9_]/g, '');
};

const updateSlug = () => {
    if (!createForm.slug || createForm.name !== createForm.prevName) {
        createForm.slug = generateSlug(createForm.name);
    }
    createForm.prevName = createForm.name;
};
</script>

<template>
    <StockAuditLayout title="Roles & Permissions">
        <Head title="Roles & Permissions" />

        <div class="min-h-screen bg-gray-50">
            <div class="px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Roles & Permissions</h1>
                        <p class="mt-1 text-sm text-gray-500">Define custom roles and assign granular permissions</p>
                    </div>
                    <button @click="openCreateModal" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                        <PlusIcon class="h-5 w-5" />
                        Create Role
                    </button>
                </div>
            </div>

            <div class="px-4 sm:px-6 lg:px-8 pb-8 space-y-8">
                <div v-for="role in props.roles" :key="role.id" class="rounded-xl border border-gray-100 bg-white shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3">
                            <div :class="['flex h-10 w-10 items-center justify-center rounded-lg', role.is_system ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700']">
                                <component :is="role.is_system ? ShieldCheckIcon : KeyIcon" class="h-5 w-5" />
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-lg font-semibold text-gray-900">{{ role.name }}</h3>
                                    <span v-if="role.is_system" class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-600/20">
                                        System Role
                                    </span>
                                </div>
                                <p v-if="role.description" class="mt-0.5 text-sm text-gray-500">{{ role.description }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                @click="openEditModal(role)"
                                :disabled="role.is_system"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <PencilIcon class="h-4 w-4" />
                                Edit
                            </button>
                            <button
                                v-if="!role.is_system"
                                @click="handleDelete(role)"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50 hover:border-red-300"
                            >
                                <TrashIcon class="h-4 w-4" />
                                Delete
                            </button>
                        </div>
                    </div>

                    <div class="px-5 py-4">
                        <div class="flex items-center gap-2 mb-3">
                            <InformationCircleIcon class="h-4 w-4 text-gray-400" />
                            <span class="text-xs text-gray-500">{{ role.permissions.length }} permissions assigned</span>
                        </div>
                        <div class="flex flex-wrap gap-1.5">
                            <span
                                v-for="perm in role.permissions"
                                :key="perm"
                                class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20"
                            >
                                {{ perm.replace(/\./g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Create Role Modal -->
            <div v-if="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="fixed inset-0 bg-gray-900/80" @click="showCreateModal = false"></div>
                <div class="relative mx-auto mt-10 max-w-2xl px-4">
                    <div class="rounded-xl bg-white px-6 py-6 shadow-xl ring-1 ring-gray-900/10 max-h-[90vh] overflow-y-auto">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-gray-900">Create New Role</h3>
                            <button @click="showCreateModal = false" class="text-gray-400 hover:text-gray-500">
                                <XMarkIcon class="h-6 w-6" />
                            </button>
                        </div>

                        <form @submit.prevent="submitCreate">
                            <div class="grid gap-4 sm:grid-cols-2 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Role Name *</label>
                                    <input
                                        v-model="createForm.name"
                                        @input="updateSlug"
                                        required
                                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                    />
                                    <div v-if="createForm.errors.name" class="mt-1 text-sm text-red-600">{{ createForm.errors.name }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Slug *</label>
                                    <input
                                        v-model="createForm.slug"
                                        required
                                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                    />
                                    <div v-if="createForm.errors.slug" class="mt-1 text-sm text-red-600">{{ createForm.errors.slug }}</div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea
                                    v-model="createForm.description"
                                    rows="2"
                                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                ></textarea>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Permissions</label>
                                <div v-for="cat in permissionCategories" :key="cat.category" class="mb-4">
                                    <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">{{ cat.category }}</h4>
                                    <div class="flex flex-wrap gap-2">
                                        <label v-for="perm in cat.permissions" :key="perm.value" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm text-gray-700 hover:bg-emerald-50 hover:border-emerald-300 cursor-pointer transition-colors">
                                            <input
                                                type="checkbox"
                                                :value="perm.value"
                                                v-model="createForm.permissions"
                                                class="h-4 w-4 rounded border-gray-200 text-emerald-600 focus:ring-emerald-500"
                                            />
                                            {{ perm.label }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                                <button
                                    type="button"
                                    @click="showCreateModal = false"
                                    class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="createForm.processing"
                                    class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50"
                                >
                                    <span v-if="createForm.processing">Creating...</span>
                                    <span v-else>Create Role</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit Role Modal -->
            <div v-if="showEditModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="fixed inset-0 bg-gray-900/80" @click="showEditModal = false"></div>
                <div class="relative mx-auto mt-10 max-w-2xl px-4">
                    <div class="rounded-xl bg-white px-6 py-6 shadow-xl ring-1 ring-gray-900/10 max-h-[90vh] overflow-y-auto">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-gray-900">Edit Role</h3>
                            <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-500">
                                <XMarkIcon class="h-6 w-6" />
                            </button>
                        </div>

                        <form @submit.prevent="submitEdit">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Role Name *</label>
                                <input
                                    v-model="editForm.name"
                                    required
                                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                />
                                <div v-if="editForm.errors.name" class="mt-1 text-sm text-red-600">{{ editForm.errors.name }}</div>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea
                                    v-model="editForm.description"
                                    rows="2"
                                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                ></textarea>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Permissions</label>
                                <div v-for="cat in permissionCategories" :key="cat.category" class="mb-4">
                                    <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">{{ cat.category }}</h4>
                                    <div class="flex flex-wrap gap-2">
                                        <label v-for="perm in cat.permissions" :key="perm.value" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm text-gray-700 hover:bg-emerald-50 hover:border-emerald-300 cursor-pointer transition-colors">
                                            <input
                                                type="checkbox"
                                                :value="perm.value"
                                                v-model="editForm.permissions"
                                                class="h-4 w-4 rounded border-gray-200 text-emerald-600 focus:ring-emerald-500"
                                            />
                                            {{ perm.label }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                                <button
                                    type="button"
                                    @click="showEditModal = false"
                                    class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="editForm.processing"
                                    class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50"
                                >
                                    <span v-if="editForm.processing">Saving...</span>
                                    <span v-else>Save Changes</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </StockAuditLayout>
</template>