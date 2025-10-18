<template>
    <AdminLayout title="Edit Employee">
        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Page Header -->
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Edit Employee</h1>
                        <p class="text-gray-600 mt-1">Update employee information</p>
                    </div>
                    <Link
                        :href="route('admin.employees.index')"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors"
                    >
                        <ArrowLeftIcon class="w-4 h-4 mr-2" />
                        Back to Employees
                    </Link>
                </div>

                <!-- Form -->
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <form @submit.prevent="submit">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                                <input
                                    v-model="form.first_name"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': form.errors.first_name }"
                                    required
                                />
                                <div v-if="form.errors.first_name" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.first_name }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                                <input
                                    v-model="form.last_name"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': form.errors.last_name }"
                                    required
                                />
                                <div v-if="form.errors.last_name" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.last_name }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': form.errors.email }"
                                    required
                                />
                                <div v-if="form.errors.email" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.email }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                                <input
                                    v-model="form.phone"
                                    type="tel"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': form.errors.phone }"
                                />
                                <div v-if="form.errors.phone" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.phone }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                                <select
                                    v-model="form.department_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': form.errors.department_id }"
                                    required
                                >
                                    <option value="">Select Department</option>
                                    <option
                                        v-for="department in departments"
                                        :key="department.id"
                                        :value="department.id"
                                    >
                                        {{ department.name }}
                                    </option>
                                </select>
                                <div v-if="form.errors.department_id" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.department_id }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Position</label>
                                <select
                                    v-model="form.position_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': form.errors.position_id }"
                                    required
                                >
                                    <option value="">Select Position</option>
                                    <option
                                        v-for="position in positions"
                                        :key="position.id"
                                        :value="position.id"
                                    >
                                        {{ position.title }}
                                    </option>
                                </select>
                                <div v-if="form.errors.position_id" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.position_id }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Current Salary</label>
                                <input
                                    v-model="form.current_salary"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': form.errors.current_salary }"
                                />
                                <div v-if="form.errors.current_salary" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.current_salary }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Employment Status</label>
                                <select
                                    v-model="form.employment_status"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': form.errors.employment_status }"
                                    required
                                >
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="terminated">Terminated</option>
                                </select>
                                <div v-if="form.errors.employment_status" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.employment_status }}
                                </div>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                                <textarea
                                    v-model="form.address"
                                    rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': form.errors.address }"
                                ></textarea>
                                <div v-if="form.errors.address" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.address }}
                                </div>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                <textarea
                                    v-model="form.notes"
                                    rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': form.errors.notes }"
                                ></textarea>
                                <div v-if="form.errors.notes" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.notes }}
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200 mt-6">
                            <Link
                                :href="route('admin.employees.index')"
                                class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50"
                            >
                                {{ form.processing ? 'Updating...' : 'Update Employee' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

interface Employee {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    phone?: string;
    address?: string;
    department_id: number;
    position_id: number;
    current_salary?: number;
    employment_status: string;
    notes?: string;
}

interface Department {
    id: number;
    name: string;
}

interface Position {
    id: number;
    title: string;
}

interface Props {
    employee: Employee;
    departments: Department[];
    positions: Position[];
}

const props = defineProps<Props>();

const form = useForm({
    first_name: props.employee.first_name,
    last_name: props.employee.last_name,
    email: props.employee.email,
    phone: props.employee.phone || '',
    address: props.employee.address || '',
    department_id: props.employee.department_id,
    position_id: props.employee.position_id,
    current_salary: props.employee.current_salary || '',
    employment_status: props.employee.employment_status,
    notes: props.employee.notes || ''
});

const submit = () => {
    form.put(route('admin.employees.update', props.employee.id));
};
</script>
