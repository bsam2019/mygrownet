<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { 
    UsersIcon, 
    ShieldCheckIcon, 
    ClockIcon,
    MagnifyingGlassIcon,
    ChevronRightIcon,
    ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';

interface Employee {
    id: number;
    first_name: string;
    last_name: string;
    full_name: string;
    user: { email: string };
    department: { name: string } | null;
    position: { title: string } | null;
    delegations_count: number;
}

interface Props {
    employees: {
        data: Employee[];
        links: any;
        meta: any;
    };
    stats: {
        total_employees: number;
        employees_with_delegations: number;
        total_active_delegations: number;
        pending_approvals: number;
    };
    availablePermissions: Record<string, any>;
    recommendedSets: Record<string, any>;
}

const props = defineProps<Props>();

const searchQuery = ref('');

const filteredEmployees = computed(() => {
    if (!searchQuery.value) return props.employees.data;
    const query = searchQuery.value.toLowerCase();
    return props.employees.data.filter(emp => 
        emp.full_name.toLowerCase().includes(query) ||
        emp.user?.email?.toLowerCase().includes(query) ||
        emp.department?.name?.toLowerCase().includes(query)
    );
});

const getRiskBadgeClass = (count: number) => {
    if (count === 0) return 'bg-gray-100 text-gray-600';
    if (count <= 5) return 'bg-blue-100 text-blue-700';
    if (count <= 10) return 'bg-amber-100 text-amber-700';
    return 'bg-red-100 text-red-700';
};
</script>

<template>
    <Head title="Delegation Management" />
    
    <AdminLayout :breadcrumbs="[
        { title: 'Employees', href: route('admin.employees.index') },
        { title: 'Delegations' }
    ]">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Delegation Management</h1>
                    <p class="text-gray-500 mt-1">Delegate admin functions to employees</p>
                </div>
                <div class="flex gap-3">
                    <Link :href="route('admin.delegations.logs')"
                        class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        View Audit Logs
                    </Link>
                    <Link v-if="stats.pending_approvals > 0" :href="route('admin.delegations.approvals')"
                        class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 flex items-center gap-2">
                        <ExclamationTriangleIcon class="h-5 w-5" />
                        {{ stats.pending_approvals }} Pending Approvals
                    </Link>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Total Employees</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.total_employees }}</p>
                        </div>
                        <div class="p-3 bg-blue-50 rounded-lg">
                            <UsersIcon class="h-6 w-6 text-blue-600" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">With Delegations</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.employees_with_delegations }}</p>
                        </div>
                        <div class="p-3 bg-emerald-50 rounded-lg">
                            <ShieldCheckIcon class="h-6 w-6 text-emerald-600" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Active Delegations</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.total_active_delegations }}</p>
                        </div>
                        <div class="p-3 bg-purple-50 rounded-lg">
                            <ShieldCheckIcon class="h-6 w-6 text-purple-600" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Pending Approvals</p>
                            <p class="text-2xl font-bold" :class="stats.pending_approvals > 0 ? 'text-amber-600' : 'text-gray-900'">
                                {{ stats.pending_approvals }}
                            </p>
                        </div>
                        <div class="p-3 bg-amber-50 rounded-lg">
                            <ClockIcon class="h-6 w-6 text-amber-600" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <div class="relative">
                    <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search employees by name, email, or department..."
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>
            </div>

            <!-- Employee List -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="p-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Employees</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    <Link 
                        v-for="employee in filteredEmployees" 
                        :key="employee.id"
                        :href="route('admin.delegations.show', employee.id)"
                        class="flex items-center justify-between p-4 hover:bg-gray-50 transition-colors"
                    >
                        <div class="flex items-center gap-4">
                            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                                <span class="text-sm font-medium text-white">
                                    {{ employee.first_name[0] }}{{ employee.last_name[0] }}
                                </span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ employee.full_name }}</p>
                                <p class="text-sm text-gray-500">
                                    {{ employee.position?.title || 'No position' }} 
                                    <span v-if="employee.department">• {{ employee.department.name }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <span :class="[
                                'px-3 py-1 text-sm font-medium rounded-full',
                                getRiskBadgeClass(employee.delegations_count)
                            ]">
                                {{ employee.delegations_count }} delegations
                            </span>
                            <ChevronRightIcon class="h-5 w-5 text-gray-400" />
                        </div>
                    </Link>

                    <div v-if="filteredEmployees.length === 0" class="p-8 text-center text-gray-500">
                        No employees found matching your search.
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
