<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import StockAuditLayout from '@/layouts/StockAuditLayout.vue';
import LoadingSkeleton from '@/components/StockAudit/LoadingSkeleton.vue';
import Pagination from '@/components/StockAudit/Pagination.vue';
import { ref, computed } from 'vue';
import {
    PlusIcon, UserCircleIcon, ChevronRightIcon, ChevronDownIcon,
    KeyIcon, ExclamationTriangleIcon, CheckCircleIcon, XCircleIcon, ClockIcon,
    PauseCircleIcon, PlayCircleIcon, ArrowPathIcon, TrashIcon,
    PencilIcon, EyeIcon, EnvelopeIcon, MagnifyingGlassIcon,
} from '@heroicons/vue/24/outline';
import { useNotifications } from '@/composables/useNotifications';
import { useConfirmDialog } from '@/composables/useConfirmDialog';

interface Employee {
    id: number;
    sa_company_id: number;
    user_id: number;
    sa_company_role_id: number | null;
    status: string;
    invited_at: string | null;
    joined_at: string | null;
    removed_at: string | null;
    removal_reason: string | null;
    created_at: string;
    updated_at: string;
    user?: {
        id: number;
        name: string;
        email: string;
    };
    role?: {
        id: number;
        name: string;
        slug: string;
        permissions: string[];
    };
}

interface Role {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    permissions: string[];
    is_system: boolean;
}

interface Props {
    employees: Employee[];
    roles: Role[];
    availableUsers: { id: number; name: string; email: string }[];
    availablePermissions: string[];
}

const props = defineProps<Props>();
const { success, error: notifyError } = useNotifications();
const confirm = useConfirmDialog();

const showInviteModal = ref(false);
const searchQuery = ref('');
const statusFilter = ref('all');

const page = usePage();
const user = computed(() => page.props.auth?.user);

const filteredEmployees = computed(() => {
    return props.employees.filter(emp => {
        const matchesSearch = !searchQuery.value || 
            emp.user?.name?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            emp.user?.email?.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesStatus = statusFilter.value === 'all' || emp.status === statusFilter.value;
        return matchesSearch && matchesStatus;
    });
});

const statusConfig: Record<string, { label: string; icon: any; class: string; dot: string }> = {
    active: { label: 'Active', icon: CheckCircleIcon, class: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20', dot: 'bg-emerald-500' },
    pending: { label: 'Pending', icon: ClockIcon, class: 'bg-amber-50 text-amber-700 ring-amber-600/20', dot: 'bg-amber-500' },
    suspended: { label: 'Suspended', icon: PauseCircleIcon, class: 'bg-red-50 text-red-700 ring-red-600/20', dot: 'bg-red-500' },
    removed: { label: 'Removed', icon: XCircleIcon, class: 'bg-gray-50 text-gray-700 ring-gray-600/20', dot: 'bg-gray-400' },
};

const statusOrder = { active: 0, pending: 1, suspended: 2, removed: 3 };

const sortedEmployees = computed(() => {
    return [...filteredEmployees.value].sort((a, b) => {
        return (statusOrder[a.status as keyof typeof statusOrder] ?? 99) - (statusOrder[b.status as keyof typeof statusOrder] ?? 99);
    });
});

const inviteForm = useForm({
    user_id: '',
    role_id: '',
});

const openInviteModal = () => {
    inviteForm.reset();
    showInviteModal.value = true;
};

const submitInvite = () => {
    inviteForm.post(route('stock-audit.employees.invite'), {
        onSuccess: () => {
            success('Invitation sent');
            showInviteModal.value = false;
        },
        onError: () => notifyError('Failed to send invitation'),
    });
};

const handleAction = async (employee: Employee, action: 'suspend' | 'reactivate' | 'remove') => {
    if (action === 'suspend') {
        const ok = await confirm.show(`Suspend ${employee.user?.name}? They will lose access until reactivated.`, 'Suspend Employee');
        if (!ok) return;
        useForm({}).post(route('stock-audit.employees.suspend', employee.user_id), {
            onSuccess: () => success('Employee suspended'),
            onError: () => notifyError('Failed to suspend employee'),
        });
    } else if (action === 'reactivate') {
        useForm({}).post(route('stock-audit.employees.reactivate', employee.user_id), {
            onSuccess: () => success('Employee reactivated'),
            onError: () => notifyError('Failed to reactivate employee'),
        });
    } else if (action === 'remove') {
        const ok = await confirm.show(`Remove ${employee.user?.name}? This action cannot be undone.`, 'Remove Employee');
        if (!ok) return;
        useForm({ reason: '' }).post(route('stock-audit.employees.remove', employee.user_id), {
            onSuccess: () => success('Employee removed'),
            onError: () => notifyError('Failed to remove employee'),
        });
    }
};

const resendInvite = (employee: Employee) => {
    useForm({}).post(route('stock-audit.employees.invite', employee.user_id), {
        onSuccess: () => success('Invitation resent'),
        onError: () => notifyError('Failed to resend invitation'),
    });
};

const formatDate = (dateStr: string | null) => {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('en-ZM', { month: 'short', day: 'numeric', year: 'numeric' });
};

const getRoleDisplay = (employee: Employee) => {
    if (!employee.role) return { name: 'No Role', color: 'gray' };
    return { name: employee.role.name, color: employee.role.is_system ? 'blue' : 'emerald' };
};
</script>

<template>
    <StockAuditLayout title="Employees">
        <Head title="Employees" />

        <div class="min-h-screen bg-gray-50">
            <!-- Header -->
            <div class="px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Employees</h1>
                        <p class="mt-1 text-sm text-gray-500">Manage team members and their access</p>
                    </div>
                    <button @click="openInviteModal" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                        <PlusIcon class="h-5 w-5" />
                        Invite Employee
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="px-4 sm:px-6 lg:px-8 pb-4">
                <div class="flex flex-col gap-4 sm:flex-row">
                    <div class="relative flex-1">
                        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search by name or email..."
                            class="w-full rounded-lg border border-gray-200 bg-white pl-10 pr-4 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                        />
                    </div>
                    <select
                        v-model="statusFilter"
                        class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm text-gray-900 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    >
                        <option value="all">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="pending">Pending</option>
                        <option value="suspended">Suspended</option>
                        <option value="removed">Removed</option>
                    </select>
                </div>
            </div>

            <!-- Employees Table -->
            <div class="px-4 sm:px-6 lg:px-8 pb-8">
                <LoadingSkeleton v-if="!employees.length && !(employees as any).data" type="table" />
                <template v-else>
                    <div class="rounded-xl border border-gray-100 bg-white shadow-sm overflow-hidden">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-100">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Employee</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 hidden md:table-cell">Role</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 hidden lg:table-cell">Invited</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 hidden lg:table-cell">Joined</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="emp in sortedEmployees" :key="emp.id" class="hover:bg-gray-50 transition-colors">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 font-medium text-sm">
                                                {{ emp.user?.name?.charAt(0).toUpperCase() }}
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate">{{ emp.user?.name }}</p>
                                                <p class="text-xs text-gray-500 truncate">{{ emp.user?.email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 hidden md:table-cell">
                                        <span
                                            v-if="emp.role"
                                            :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset', emp.role.is_system ? 'bg-blue-50 text-blue-700 ring-blue-600/20' : 'bg-emerald-50 text-emerald-700 ring-emerald-600/20']"
                                        >
                                            {{ emp.role.name }}
                                        </span>
                                        <span v-else class="inline-flex items-center rounded-full bg-gray-50 px-2.5 py-0.5 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-600/20">
                                            No Role
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span :class="['inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset', statusConfig[emp.status].class]">
                                            <component :is="statusConfig[emp.status].icon" class="h-3.5 w-3.5" />
                                            {{ statusConfig[emp.status].label }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 hidden lg:table-cell text-sm text-gray-500">
                                        {{ formatDate(emp.invited_at) }}
                                    </td>
                                    <td class="px-5 py-4 hidden lg:table-cell text-sm text-gray-500">
                                        {{ formatDate(emp.joined_at) }}
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <!-- Role Dropdown -->
                                            <div class="relative" v-if="emp.status === 'active'">
                                                <button
                                                    @click="emp.showRoleDropdown = !emp.showRoleDropdown"
                                                    class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50"
                                                >
                                                    <ChevronDownIcon class="inline h-4 w-4" />
                                                </button>
                                                <div v-if="emp.showRoleDropdown" class="absolute right-0 z-10 mt-1 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black/5 focus:outline-none animate-in fade-in-0 zoom-in-95" role="menu" aria-orientation="vertical">
                                                    <button
                                                        v-for="role in props.roles"
                                                        :key="role.id"
                                                        @click="inviteForm.role_id = role.id; inviteForm.user_id = emp.user_id; submitInvite()"
                                                        class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                                                        role="menuitem"
                                                    >
                                                        {{ role.name }}
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="flex items-center gap-1">
                                                <button
                                                    v-if="emp.status === 'pending'"
                                                    @click="resendInvite(emp)"
                                                    class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                                                    title="Resend invitation"
                                                >
                                                    <EnvelopeIcon class="h-4 w-4" />
                                                </button>

                                                <button
                                                    v-if="emp.status === 'active'"
                                                    @click="handleAction(emp, 'suspend')"
                                                    class="rounded-lg p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600"
                                                    title="Suspend"
                                                >
                                                    <PauseCircleIcon class="h-4 w-4" />
                                                </button>

                                                <button
                                                    v-if="emp.status === 'suspended'"
                                                    @click="handleAction(emp, 'reactivate')"
                                                    class="rounded-lg p-1.5 text-gray-400 hover:bg-emerald-50 hover:text-emerald-600"
                                                    title="Reactivate"
                                                >
                                                    <PlayCircleIcon class="h-4 w-4" />
                                                </button>

                                                <button
                                                    v-if="emp.status !== 'removed'"
                                                    @click="handleAction(emp, 'remove')"
                                                    class="rounded-lg p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600"
                                                    title="Remove"
                                                >
                                                    <TrashIcon class="h-4 w-4" />
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div v-if="sortedEmployees.length === 0" class="px-5 py-12 text-center">
                            <UserCircleIcon class="mx-auto h-12 w-12 text-gray-300" />
                            <h3 class="mt-2 text-sm font-semibold text-gray-900">No employees found</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by inviting your first team member</p>
                            <button @click="openInviteModal" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                                <PlusIcon class="h-5 w-5" />
                                Invite Employee
                            </button>
                        </div>
                    </div>
                    <Pagination v-if="(employees as any).links" :links="(employees as any).links" :meta="(employees as any).meta" />
                </template>
            </div>
        </div>

        <!-- Invite Modal -->
        <div v-if="showInviteModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="fixed inset-0 bg-gray-900/80" @click="showInviteModal = false"></div>
            <div class="relative mx-auto mt-10 max-w-md px-4">
                <div class="rounded-xl bg-white px-6 py-6 shadow-xl ring-1 ring-gray-900/10">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Invite Employee</h3>
                        <button @click="showInviteModal = false" class="text-gray-400 hover:text-gray-500">
                            <XMarkIcon class="h-6 w-6" />
                        </button>
                    </div>

                    <form @submit.prevent="submitInvite">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">User</label>
                            <select
                                v-model="inviteForm.user_id"
                                required
                                class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            >
                                <option value="">Select a user...</option>
                                <option v-for="u in availableUsers" :key="u.id" :value="u.id">
                                    {{ u.name }} ({{ u.email }})
                                </option>
                            </select>
                            <div v-if="inviteForm.errors.user_id" class="mt-1 text-sm text-red-600">{{ inviteForm.errors.user_id }}</div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Role (optional)</label>
                            <select
                                v-model="inviteForm.role_id"
                                class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            >
                                <option value="">No role assigned</option>
                                <option v-for="role in props.roles" :key="role.id" :value="role.id">
                                    {{ role.name }}
                                </option>
                            </select>
                        </div>

                        <div class="flex justify-end gap-3">
                            <button
                                type="button"
                                @click="showInviteModal = false"
                                class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="inviteForm.processing"
                                class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50"
                            >
                                <span v-if="inviteForm.processing">Inviting...</span>
                                <span v-else>Send Invitation</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </StockAuditLayout>
</template>