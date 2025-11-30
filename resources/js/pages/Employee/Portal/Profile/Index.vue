<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/Layouts/EmployeePortalLayout.vue';
import {
    UserCircleIcon,
    BuildingOfficeIcon,
    BriefcaseIcon,
    CalendarDaysIcon,
    EnvelopeIcon,
    PhoneIcon,
    MapPinIcon,
} from '@heroicons/vue/24/outline';

interface Props {
    employee: {
        id: number;
        employee_number: string;
        first_name: string;
        last_name: string;
        full_name: string;
        email: string;
        phone: string;
        address: string;
        hire_date: string;
        emergency_contact_name: string;
        emergency_contact_phone: string;
        department: { name: string };
        position: { title: string };
        manager: { full_name: string };
    };
}

const props = defineProps<Props>();

const form = useForm({
    phone: props.employee.phone || '',
    address: props.employee.address || '',
    emergency_contact_name: props.employee.emergency_contact_name || '',
    emergency_contact_phone: props.employee.emergency_contact_phone || '',
});

const submit = () => {
    form.patch(route('employee.portal.profile.update'));
};

const yearsOfService = () => {
    const hireDate = new Date(props.employee.hire_date);
    const now = new Date();
    const years = Math.floor((now.getTime() - hireDate.getTime()) / (365.25 * 24 * 60 * 60 * 1000));
    return years;
};
</script>

<template>
    <Head title="My Profile" />

    <EmployeePortalLayout>
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold text-gray-900">My Profile</h1>
                <p class="text-gray-500 mt-1">View and update your personal information</p>
            </div>

            <!-- Profile Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Header Section -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-6">
                    <div class="flex items-center gap-4">
                        <div class="h-20 w-20 rounded-full bg-white/20 flex items-center justify-center">
                            <span class="text-3xl font-bold text-white">
                                {{ employee.first_name[0] }}{{ employee.last_name[0] }}
                            </span>
                        </div>
                        <div class="text-white">
                            <h2 class="text-2xl font-bold">{{ employee.full_name }}</h2>
                            <p class="text-blue-100">{{ employee.position?.title }}</p>
                            <p class="text-blue-200 text-sm mt-1">Employee #{{ employee.employee_number }}</p>
                        </div>
                    </div>
                </div>

                <!-- Info Grid -->
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-50 rounded-lg">
                            <BuildingOfficeIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Department</p>
                            <p class="font-medium text-gray-900">{{ employee.department?.name }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-50 rounded-lg">
                            <BriefcaseIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Position</p>
                            <p class="font-medium text-gray-900">{{ employee.position?.title }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-50 rounded-lg">
                            <UserCircleIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Reports To</p>
                            <p class="font-medium text-gray-900">{{ employee.manager?.full_name || 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-50 rounded-lg">
                            <CalendarDaysIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Hire Date</p>
                            <p class="font-medium text-gray-900">
                                {{ new Date(employee.hire_date).toLocaleDateString() }}
                                <span class="text-gray-500 text-sm">({{ yearsOfService() }} years)</span>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-50 rounded-lg">
                            <EnvelopeIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Email</p>
                            <p class="font-medium text-gray-900">{{ employee.email }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Editable Information -->
            <form @submit.prevent="submit" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
                <h3 class="text-lg font-semibold text-gray-900">Contact Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                            Phone Number
                        </label>
                        <div class="relative">
                            <PhoneIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
                            <input type="tel" id="phone" v-model="form.phone"
                                class="w-full pl-10 border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                placeholder="+260 XXX XXX XXX" />
                        </div>
                        <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
                    </div>

                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                            Address
                        </label>
                        <div class="relative">
                            <MapPinIcon class="absolute left-3 top-3 h-5 w-5 text-gray-400" aria-hidden="true" />
                            <textarea id="address" v-model="form.address" rows="2"
                                class="w-full pl-10 border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Your address">
                            </textarea>
                        </div>
                        <p v-if="form.errors.address" class="mt-1 text-sm text-red-600">{{ form.errors.address }}</p>
                    </div>
                </div>

                <h3 class="text-lg font-semibold text-gray-900 pt-4">Emergency Contact</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="emergency_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Contact Name
                        </label>
                        <input type="text" id="emergency_name" v-model="form.emergency_contact_name"
                            class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Emergency contact name" />
                        <p v-if="form.errors.emergency_contact_name" class="mt-1 text-sm text-red-600">
                            {{ form.errors.emergency_contact_name }}
                        </p>
                    </div>

                    <div>
                        <label for="emergency_phone" class="block text-sm font-medium text-gray-700 mb-2">
                            Contact Phone
                        </label>
                        <input type="tel" id="emergency_phone" v-model="form.emergency_contact_phone"
                            class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                            placeholder="+260 XXX XXX XXX" />
                        <p v-if="form.errors.emergency_contact_phone" class="mt-1 text-sm text-red-600">
                            {{ form.errors.emergency_contact_phone }}
                        </p>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-100">
                    <button type="submit"
                        :disabled="form.processing"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors">
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </form>
        </div>
    </EmployeePortalLayout>
</template>
