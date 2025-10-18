<template>
    <AdminLayout title="Add Employee">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800">Add New Employee</h2>
                <Link
                    :href="route('admin.employees.index')"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors"
                >
                    <ArrowLeftIcon class="w-4 h-4 mr-2" />
                    Back to Employees
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white rounded-lg shadow-sm border">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <!-- Personal Information -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        First Name <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.first_name"
                                        type="text"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                        :class="{ 'border-red-500': form.errors.first_name }"
                                    />
                                    <div v-if="form.errors.first_name" class="text-red-600 text-sm mt-1">
                                        {{ form.errors.first_name }}
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Last Name <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.last_name"
                                        type="text"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                        :class="{ 'border-red-500': form.errors.last_name }"
                                    />
                                    <div v-if="form.errors.last_name" class="text-red-600 text-sm mt-1">
                                        {{ form.errors.last_name }}
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Email <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.email"
                                        type="email"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                        :class="{ 'border-red-500': form.errors.email }"
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
                            </div>
                        </div>

                        <!-- Employment Information -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Employment Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Department <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.department_id"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                        :class="{ 'border-red-500': form.errors.department_id }"
                                        @change="filterPositions"
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
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Position <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.position_id"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                        :class="{ 'border-red-500': form.errors.position_id }"
                                    >
                                        <option value="">Select Position</option>
                                        <option
                                            v-for="position in filteredPositions"
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
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Hire Date <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.hire_date"
                                        type="date"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                        :class="{ 'border-red-500': form.errors.hire_date }"
                                    />
                                    <div v-if="form.errors.hire_date" class="text-red-600 text-sm mt-1">
                                        {{ form.errors.hire_date }}
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Salary (Kwacha)</label>
                                    <input
                                        v-model="form.salary"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                        :class="{ 'border-red-500': form.errors.salary }"
                                    />
                                    <div v-if="form.errors.salary" class="text-red-600 text-sm mt-1">
                                        {{ form.errors.salary }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                            <Link
                                :href="route('admin.employees.index')"
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
                                <span v-else>Create Employee</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

interface Department {
    id: number;
    name: string;
}

interface Position {
    id: number;
    title: string;
    department_id: number;
}

interface Props {
    departments: Department[];
    positions: Position[];
}

const props = defineProps<Props>();

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    department_id: '',
    position_id: '',
    hire_date: '',
    salary: ''
});

const filteredPositions = computed(() => {
    if (!form.department_id) return [];
    return props.positions.filter(position => position.department_id == form.department_id);
});

const filterPositions = () => {
    // Reset position when department changes
    form.position_id = '';
};

const submit = () => {
    form.post(route('admin.employees.store'));
};
</script>
