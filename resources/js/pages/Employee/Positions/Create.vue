<template>
    <AdminLayout title="Add Position">
        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Page Header -->
                <div class="flex items-center justify-between mb-8">
                    <h1 class="text-2xl font-bold text-gray-900">Add New Position</h1>
                    <Link
                        :href="route('admin.positions.index')"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors"
                    >
                        <ArrowLeftIcon class="w-4 h-4 mr-2" />
                        Back to Positions
                    </Link>
                </div>

                <div class="bg-white rounded-lg shadow-sm border">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <!-- Position Information -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Position Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Position Title <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.title"
                                        type="text"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                        :class="{ 'border-red-500': form.errors.title }"
                                    />
                                    <div v-if="form.errors.title" class="text-red-600 text-sm mt-1">
                                        {{ form.errors.title }}
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
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Department <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.department_id"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                        :class="{ 'border-red-500': form.errors.department_id }"
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
                                    <label class="flex items-center">
                                        <input
                                            v-model="form.is_commission_eligible"
                                            type="checkbox"
                                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                        />
                                        <span class="ml-2 text-sm text-gray-700">Commission Eligible</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Salary Information -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Salary Range (Kwacha)</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Salary</label>
                                    <input
                                        v-model="form.min_salary"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                        :class="{ 'border-red-500': form.errors.min_salary }"
                                    />
                                    <div v-if="form.errors.min_salary" class="text-red-600 text-sm mt-1">
                                        {{ form.errors.min_salary }}
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Maximum Salary</label>
                                    <input
                                        v-model="form.max_salary"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                        :class="{ 'border-red-500': form.errors.max_salary }"
                                    />
                                    <div v-if="form.errors.max_salary" class="text-red-600 text-sm mt-1">
                                        {{ form.errors.max_salary }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                            <Link
                                :href="route('admin.positions.index')"
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
                                <span v-else>Create Position</span>
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

interface Props {
    departments: Department[];
}

const props = defineProps<Props>();

const form = useForm({
    title: '',
    description: '',
    department_id: '',
    min_salary: '',
    max_salary: '',
    is_commission_eligible: false
});

const submit = () => {
    form.post(route('admin.positions.store'));
};
</script>
