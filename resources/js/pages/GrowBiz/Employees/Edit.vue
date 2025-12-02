<template>
    <GrowBizLayout>
        <div class="px-4 py-4 pb-6">
            <!-- Page Header -->
            <div class="flex items-center gap-3 mb-6">
                <Link 
                    :href="route('growbiz.employees.show', employee.id)" 
                    class="p-2 -ml-2 rounded-xl hover:bg-gray-100 active:bg-gray-200 transition-colors"
                    aria-label="Back to employee"
                >
                    <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                </Link>
                <h1 class="text-xl font-bold text-gray-900">Edit Employee</h1>
            </div>

            <div class="max-w-2xl mx-auto">
                <form @submit.prevent="submit" class="bg-white rounded-2xl shadow-sm p-5">
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700">First Name *</label>
                                <input id="first_name" v-model="form.first_name" type="text" required
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name *</label>
                                <input id="last_name" v-model="form.last_name" type="text" required
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                                <input id="email" v-model="form.email" type="email" required
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                                <input id="phone" v-model="form.phone" type="tel"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="position" class="block text-sm font-medium text-gray-700">Position</label>
                                <input id="position" v-model="form.position" type="text"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label for="department" class="block text-sm font-medium text-gray-700">Department</label>
                                <input id="department" v-model="form.department" type="text" list="departments"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                <datalist id="departments">
                                    <option v-for="dept in departments" :key="dept" :value="dept" />
                                </datalist>
                            </div>
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status *</label>
                            <select id="status" v-model="form.status" required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option v-for="status in statuses" :key="status.value" :value="status.value">
                                    {{ status.label }}
                                </option>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="hire_date" class="block text-sm font-medium text-gray-700">Hire Date</label>
                                <input id="hire_date" v-model="form.hire_date" type="date"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label for="hourly_rate" class="block text-sm font-medium text-gray-700">Hourly Rate</label>
                                <input id="hourly_rate" v-model="form.hourly_rate" type="number" min="0" step="0.01"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea id="notes" v-model="form.notes" rows="3"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-col sm:flex-row items-center justify-end gap-3">
                        <Link :href="route('growbiz.employees.show', employee.id)" class="w-full sm:w-auto px-6 py-3 text-center text-gray-600 bg-gray-100 rounded-xl font-medium hover:bg-gray-200 active:bg-gray-300 transition-colors">
                            Cancel
                        </Link>
                        <button type="submit" :disabled="form.processing"
                            class="w-full sm:w-auto px-6 py-3 bg-emerald-600 text-white rounded-xl font-medium hover:bg-emerald-700 active:bg-emerald-800 disabled:opacity-50 transition-colors">
                            {{ form.processing ? 'Saving...' : 'Save Changes' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </GrowBizLayout>
</template>

<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import GrowBizLayout from '@/Layouts/GrowBizLayout.vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

interface Props {
    employee: {
        id: number;
        first_name: string;
        last_name: string;
        email: string | null;
        phone: string | null;
        position: string | null;
        department: string | null;
        status: string;
        hire_date: string | null;
        hourly_rate: number | null;
        notes: string | null;
    };
    departments: string[];
    statuses: Array<{ value: string; label: string }>;
}

const props = defineProps<Props>();

const form = useForm({
    first_name: props.employee.first_name,
    last_name: props.employee.last_name,
    email: props.employee.email || '',
    phone: props.employee.phone || '',
    position: props.employee.position || '',
    department: props.employee.department || '',
    status: props.employee.status,
    hire_date: props.employee.hire_date || '',
    hourly_rate: props.employee.hourly_rate,
    notes: props.employee.notes || '',
});

const submit = () => {
    form.put(route('growbiz.employees.update', props.employee.id));
};
</script>
