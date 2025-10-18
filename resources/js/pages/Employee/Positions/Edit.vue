<template>
    <AdminLayout title="Edit Position">
        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Page Header -->
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Edit Position</h1>
                        <p class="text-gray-600 mt-1">Update position information</p>
                    </div>
                    <Link
                        :href="route('admin.positions.index')"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors"
                    >
                        <ArrowLeftIcon class="w-4 h-4 mr-2" />
                        Back to Positions
                    </Link>
                </div>

                <!-- Form -->
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <form @submit.prevent="submit">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Position Title</label>
                                <input
                                    v-model="form.title"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': form.errors.title }"
                                    required
                                />
                                <div v-if="form.errors.title" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.title }}
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

                            <div class="md:col-span-2">
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
                                <label class="flex items-center">
                                    <input
                                        v-model="form.commission_eligible"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">Commission Eligible</span>
                                </label>
                            </div>

                            <div>
                                <label class="flex items-center">
                                    <input
                                        v-model="form.is_active"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">Active Position</span>
                                </label>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200 mt-6">
                            <Link
                                :href="route('admin.positions.index')"
                                class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50"
                            >
                                {{ form.processing ? 'Updating...' : 'Update Position' }}
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

interface Position {
    id: number;
    title: string;
    description?: string;
    department_id: number;
    min_salary?: number;
    max_salary?: number;
    commission_eligible: boolean;
    is_active: boolean;
}

interface Department {
    id: number;
    name: string;
}

interface Props {
    position: Position;
    departments: Department[];
}

const props = defineProps<Props>();

const form = useForm({
    title: props.position.title,
    description: props.position.description || '',
    department_id: props.position.department_id,
    min_salary: props.position.min_salary || '',
    max_salary: props.position.max_salary || '',
    commission_eligible: props.position.commission_eligible,
    is_active: props.position.is_active
});

const submit = () => {
    form.put(route('admin.positions.update', props.position.id));
};
</script>
