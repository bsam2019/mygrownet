<template>
    <AdminLayout title="Edit Department">
        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Page Header -->
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Edit Department</h1>
                        <p class="text-gray-600 mt-1">Update department information</p>
                    </div>
                    <Link
                        :href="route('admin.departments.index')"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors"
                    >
                        <ArrowLeftIcon class="w-4 h-4 mr-2" />
                        Back to Departments
                    </Link>
                </div>

                <!-- Form -->
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <form @submit.prevent="submit">
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Department Name</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': form.errors.name }"
                                    required
                                />
                                <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.name }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea
                                    v-model="form.description"
                                    rows="4"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': form.errors.description }"
                                ></textarea>
                                <div v-if="form.errors.description" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.description }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Parent Department</label>
                                <select
                                    v-model="form.parent_department_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': form.errors.parent_department_id }"
                                >
                                    <option value="">No Parent Department</option>
                                    <option
                                        v-for="department in parent_departments"
                                        :key="department.id"
                                        :value="department.id"
                                    >
                                        {{ department.name }}
                                    </option>
                                </select>
                                <div v-if="form.errors.parent_department_id" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.parent_department_id }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Department Head</label>
                                <select
                                    v-model="form.head_employee_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': form.errors.head_employee_id }"
                                >
                                    <option value="">No Department Head</option>
                                    <option
                                        v-for="employee in employees"
                                        :key="employee.id"
                                        :value="employee.id"
                                    >
                                        {{ employee.first_name }} {{ employee.last_name }}
                                    </option>
                                </select>
                                <div v-if="form.errors.head_employee_id" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.head_employee_id }}
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200 mt-6">
                            <Link
                                :href="route('admin.departments.index')"
                                class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50"
                            >
                                {{ form.processing ? 'Updating...' : 'Update Department' }}
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

interface Department {
    id: number;
    name: string;
    description?: string;
    parent_department_id?: number;
    head_employee_id?: number;
}

interface ParentDepartment {
    id: number;
    name: string;
}

interface Employee {
    id: number;
    first_name: string;
    last_name: string;
}

interface Props {
    department: Department;
    parent_departments: ParentDepartment[];
    employees: Employee[];
}

const props = defineProps<Props>();

const form = useForm({
    name: props.department.name,
    description: props.department.description || '',
    parent_department_id: props.department.parent_department_id || '',
    head_employee_id: props.department.head_employee_id || ''
});

const submit = () => {
    form.put(route('admin.departments.update', props.department.id));
};
</script>
