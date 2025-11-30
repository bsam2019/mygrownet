<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/Layouts/EmployeePortalLayout.vue';
import { ref, watch } from 'vue';
import { 
    MagnifyingGlassIcon, 
    UserCircleIcon,
    EnvelopeIcon,
    PhoneIcon,
    BuildingOfficeIcon,
    Squares2X2Icon
} from '@heroicons/vue/24/outline';
import { debounce } from 'lodash';

interface Employee {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    department: string | null;
    position: string | null;
    is_self: boolean;
}

interface Department {
    id: number;
    name: string;
}

interface Props {
    employees: {
        data: Employee[];
        links: any;
    };
    departments: Department[];
    filters: {
        search?: string;
        department?: number;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const selectedDepartment = ref(props.filters.department || '');

const performSearch = debounce(() => {
    router.get(route('employee.portal.directory.index'), {
        search: search.value || undefined,
        department: selectedDepartment.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}, 300);

watch([search, selectedDepartment], performSearch);

const getInitials = (name: string) => {
    const parts = name.split(' ');
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
};
</script>

<template>
    <Head title="Company Directory" />
    <EmployeePortalLayout>
        <template #header>Company Directory</template>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Find Colleagues</h2>
                    <p class="text-sm text-gray-500">Search and connect with team members</p>
                </div>
                <Link :href="route('employee.portal.directory.org-chart')"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <Squares2X2Icon class="h-5 w-5" />
                    View Org Chart
                </Link>
            </div>

            <!-- Search & Filters -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1 relative">
                        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                        <input v-model="search" type="text" placeholder="Search by name or email..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <select v-model="selectedDepartment"
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Departments</option>
                        <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                            {{ dept.name }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Employee Grid -->
            <div v-if="employees.data.length === 0" class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                <UserCircleIcon class="h-12 w-12 mx-auto text-gray-300 mb-4" />
                <p class="text-gray-500">No employees found</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="employee in employees.data" :key="employee.id"
                    :class="[
                        'bg-white rounded-xl shadow-sm border p-4 hover:shadow-md transition-shadow',
                        employee.is_self ? 'border-blue-200 bg-blue-50/30' : 'border-gray-100'
                    ]">
                    <div class="flex items-start gap-4">
                        <div class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center flex-shrink-0">
                            <span class="text-white font-medium">{{ getInitials(employee.name) }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="font-semibold text-gray-900 truncate">{{ employee.name }}</h3>
                                <span v-if="employee.is_self" class="text-xs px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full">
                                    You
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 truncate">{{ employee.position || 'No position' }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ employee.department || 'No department' }}</p>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-100 space-y-2">
                        <a :href="`mailto:${employee.email}`" 
                            class="flex items-center gap-2 text-sm text-gray-600 hover:text-blue-600">
                            <EnvelopeIcon class="h-4 w-4" />
                            <span class="truncate">{{ employee.email }}</span>
                        </a>
                        <a v-if="employee.phone" :href="`tel:${employee.phone}`"
                            class="flex items-center gap-2 text-sm text-gray-600 hover:text-blue-600">
                            <PhoneIcon class="h-4 w-4" />
                            {{ employee.phone }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </EmployeePortalLayout>
</template>
