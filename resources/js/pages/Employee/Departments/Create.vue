<template>
    <AdminLayout title="Add Department">
        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Page Header -->
                <div class="flex items-center justify-between mb-8">
                    <h1 class="text-2xl font-bold text-gray-900">Add New Department</h1>
                    <Link
                        :href="route('admin.departments.index')"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors"
                    >
                        <ArrowLeftIcon class="w-4 h-4 mr-2" />
                        Back to Departments
                    </Link>
                </div>

                <div class="bg-white rounded-lg shadow-sm border">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <!-- Department Information -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Department Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Department Name <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                        :class="{ 'border-red-500': form.errors.name }"
                                    />
                                    <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">
                                        {{ form.errors.name }}
                                    </div>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                    <textarea
                                        v-model="form.description"
                                        rows="3"
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
                                        <!-- Will be populated with existing departments -->
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
                                        <!-- Will be populated with existing employees -->
                                    </select>
                                    <div v-if="form.errors.head_employee_id" class="text-red-600 text-sm mt-1">
                                        {{ form.errors.head_employee_id }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                            <Link
                                :href="route('admin.departments.index')"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                            >
                                <span v-if="form.processing">Creating...</span>
                                <span v-else>Create Department</span>
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
}

interface Employee {
    id: number;
    first_name: string;
    last_name: string;
}

interface Props {
    parent_departments: Department[];
    employees: Employee[];
}

const props = defineProps<Props>();

const form = useForm({
    name: '',
    description: '',
    parent_department_id: '',
    head_employee_id: ''
});

const submit = () => {
    form.post(route('admin.departments.store'));
};
</script>
