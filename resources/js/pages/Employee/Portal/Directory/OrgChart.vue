<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/Layouts/EmployeePortalLayout.vue';
import { ArrowLeftIcon, UserCircleIcon, UsersIcon } from '@heroicons/vue/24/outline';

interface Employee {
    id: number;
    name: string;
    position: string | null;
}

interface Department {
    id: number;
    name: string;
    head: Employee | null;
    employees: Employee[];
    employee_count: number;
}

const props = defineProps<{ departments: Department[] }>();

const getInitials = (name: string) => {
    const parts = name.split(' ');
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
};
</script>

<template>
    <Head title="Organization Chart" />
    <EmployeePortalLayout>
        <template #header>Organization Chart</template>
        <div class="space-y-6">
            <!-- Back Link -->
            <Link :href="route('employee.portal.directory.index')"
                class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900">
                <ArrowLeftIcon class="h-5 w-5" />
                Back to Directory
            </Link>

            <!-- Company Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl p-6 text-white text-center">
                <h1 class="text-2xl font-bold">MyGrowNet</h1>
                <p class="text-blue-100 mt-1">Organization Structure</p>
            </div>

            <!-- Departments Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="dept in departments" :key="dept.id"
                    class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <!-- Department Header -->
                    <div class="bg-gray-50 px-4 py-3 border-b">
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-gray-900">{{ dept.name }}</h3>
                            <span class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded-full">
                                {{ dept.employee_count }} members
                            </span>
                        </div>
                    </div>

                    <!-- Department Head -->
                    <div v-if="dept.head" class="p-4 border-b bg-blue-50/50">
                        <p class="text-xs text-gray-500 mb-2">Department Head</p>
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                                <span class="text-white text-sm font-medium">{{ getInitials(dept.head.name) }}</span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ dept.head.name }}</p>
                                <p class="text-xs text-gray-500">{{ dept.head.position || 'Head' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Team Members -->
                    <div class="p-4">
                        <p class="text-xs text-gray-500 mb-3">Team Members</p>
                        <div v-if="dept.employees.length === 0" class="text-center py-4 text-gray-400 text-sm">
                            No team members
                        </div>
                        <div v-else class="space-y-2 max-h-48 overflow-y-auto">
                            <div v-for="emp in dept.employees.slice(0, 5)" :key="emp.id"
                                class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50">
                                <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                                    <span class="text-gray-600 text-xs font-medium">{{ getInitials(emp.name) }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ emp.name }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ emp.position || 'Employee' }}</p>
                                </div>
                            </div>
                            <div v-if="dept.employees.length > 5" class="text-center pt-2">
                                <span class="text-xs text-gray-400">
                                    +{{ dept.employees.length - 5 }} more
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="departments.length === 0" class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                <UsersIcon class="h-12 w-12 mx-auto text-gray-300 mb-4" />
                <p class="text-gray-500">No departments found</p>
            </div>
        </div>
    </EmployeePortalLayout>
</template>
