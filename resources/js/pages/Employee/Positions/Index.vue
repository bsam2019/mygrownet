<template>
    <AdminLayout title="Position Management">
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Page Header with Add Button -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                    <h1 class="text-2xl font-bold text-gray-900">Position Management</h1>
                    <Link
                        :href="route('admin.positions.create')"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        <PlusIcon class="w-4 h-4 mr-2" />
                        Add Position
                    </Link>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-lg shadow-sm border">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <BriefcaseIcon class="w-6 h-6 text-blue-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Positions</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ positions.total || positions.length }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm border">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <CheckCircleIcon class="w-6 h-6 text-green-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Active Positions</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ activePositionsCount }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white p-6 rounded-lg shadow-sm border mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search positions..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                @input="handleSearch"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                            <select
                                v-model="selectedDepartment"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                @change="handleFilter"
                            >
                                <option value="">All Departments</option>
                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                    {{ dept.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select
                                v-model="activeOnly"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                @change="handleFilter"
                            >
                                <option :value="false">All Positions</option>
                                <option :value="true">Active Only</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Positions List -->
                <div class="bg-white rounded-lg shadow-sm border">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Positions</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Position
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Department
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Salary Range
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Commission Eligible
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="position in positions.data || positions" :key="position.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ position.title }}
                                                </div>
                                                <div v-if="position.description" class="text-sm text-gray-500">
                                                    {{ position.description }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div v-if="position.department" class="text-sm text-gray-900">
                                            {{ position.department.name }}
                                        </div>
                                        <div v-else class="text-sm text-gray-500">No department</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div v-if="position.min_salary && position.max_salary">
                                            {{ formatKwacha(position.min_salary) }} - {{ formatKwacha(position.max_salary) }}
                                        </div>
                                        <div v-else class="text-gray-500">Not specified</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="[
                                                'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                                position.is_commission_eligible
                                                    ? 'bg-green-100 text-green-800'
                                                    : 'bg-gray-100 text-gray-800'
                                            ]"
                                        >
                                            {{ position.is_commission_eligible ? 'Yes' : 'No' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="[
                                                'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                                position.is_active
                                                    ? 'bg-green-100 text-green-800'
                                                    : 'bg-red-100 text-red-800'
                                            ]"
                                        >
                                            {{ position.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link
                                            :href="route('admin.positions.show', position.id)"
                                            class="text-blue-600 hover:text-blue-900 mr-3"
                                        >
                                            View
                                        </Link>
                                        <Link
                                            v-if="$page.props.auth.user.permissions?.includes('edit-positions')"
                                            :href="route('admin.positions.edit', position.id)"
                                            class="text-indigo-600 hover:text-indigo-900"
                                        >
                                            Edit
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Link } from '@inertiajs/vue3';
import { Briefcase, CheckCircle} from 'lucide-vue-next';
import { formatKwacha } from '@/utils/formatting.ts';
import { Plus} from '@heroicons/vue/24/outline';

interface Position {
    id: number;
    title: string;
    description?: string;
    is_active: boolean;
    is_commission_eligible: boolean;
    min_salary?: number;
    max_salary?: number;
    department?: {
        name: string;
    };
}

interface Department {
    id: number;
    name: string;
}

interface Props {
    positions: {
        data: Position[];
        total?: number;
    } | Position[];
    departments: Department[];
    filters: {
        search?: string;
        department_id?: number;
        active_only: boolean;
    };
}

const props = defineProps<Props>();

const searchQuery = ref(props.filters.search || '');
const selectedDepartment = ref(props.filters.department_id || '');
const activeOnly = ref(props.filters.active_only);

const activePositionsCount = computed(() => {
    const positionsList = Array.isArray(props.positions) ? props.positions : props.positions.data;
    return positionsList.filter(p => p.is_active).length;
});

// Using shared currency formatter to ensure Kwacha formatting across the app
const formatNumber = (num: number) => new Intl.NumberFormat().format(num);

const handleSearch = () => {
    router.get(route('admin.positions.index'), {
        search: searchQuery.value,
        department_id: selectedDepartment.value,
        active_only: activeOnly.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const handleFilter = () => {
    router.get(route('admin.positions.index'), {
        search: searchQuery.value,
        department_id: selectedDepartment.value,
        active_only: activeOnly.value,
    }, {
        preserveState: true,
        replace: true,
    });
};
</script>
