<template>
    <AdminLayout title="Application Details">
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Page Header -->
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <Link
                            :href="route('admin.applications.index')"
                            class="inline-flex items-center text-blue-600 hover:text-blue-700 mb-4"
                        >
                            <ArrowLeftIcon class="w-5 h-5 mr-2" />
                            Back to Applications
                        </Link>
                        <h1 class="text-2xl font-bold text-gray-900">{{ application.full_name }}</h1>
                        <p class="text-gray-600">Applied for {{ application.job_posting.title }}</p>
                    </div>
                    <div class="flex space-x-3">
                        <a
                            v-if="application.resume_path"
                            :href="route('admin.applications.resume', application.id)"
                            target="_blank"
                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700"
                        >
                            <DocumentArrowDownIcon class="w-4 h-4 mr-2" />
                            Download Resume
                        </a>
                        <button
                            v-if="application.status !== 'hired'"
                            @click="showHireModal = true"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700"
                        >
                            <UserPlusIcon class="w-4 h-4 mr-2" />
                            Hire Applicant
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Application Details -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Personal Information -->
                        <div class="bg-white rounded-lg shadow-sm border p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Full Name</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ application.full_name }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Email</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ application.email }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Phone</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ application.phone }}</p>
                                </div>
                                <div v-if="application.national_id">
                                    <label class="block text-sm font-medium text-gray-700">National ID</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ application.national_id }}</p>
                                </div>
                                <div v-if="application.expected_salary" class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Expected Salary</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ formatKwacha(application.expected_salary) }}</p>
                                </div>
                                <div v-if="application.address" class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Address</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ application.address }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Cover Letter -->
                        <div v-if="application.cover_letter" class="bg-white rounded-lg shadow-sm border p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Cover Letter</h3>
                            <div class="prose max-w-none">
                                <p class="text-gray-700 whitespace-pre-line">{{ application.cover_letter }}</p>
                            </div>
                        </div>

                        <!-- Admin Notes -->
                        <div class="bg-white rounded-lg shadow-sm border p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Admin Notes</h3>
                            <form @submit.prevent="updateNotes">
                                <textarea
                                    v-model="notesForm.notes"
                                    rows="4"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Add notes about this application..."
                                ></textarea>
                                <div class="mt-3 flex justify-end">
                                    <button
                                        type="submit"
                                        :disabled="notesForm.processing"
                                        class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50"
                                    >
                                        {{ notesForm.processing ? 'Saving...' : 'Save Notes' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Status Card -->
                        <div class="bg-white rounded-lg shadow-sm border p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Application Status</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Current Status</label>
                                    <span
                                        :class="[
                                            'inline-flex px-3 py-1 text-sm font-semibold rounded-full',
                                            application.status_color
                                        ]"
                                    >
                                        {{ formatStatus(application.status) }}
                                    </span>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Update Status</label>
                                    <form @submit.prevent="updateStatus">
                                        <select
                                            v-model="statusForm.status"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 mb-3"
                                        >
                                            <option value="submitted">Submitted</option>
                                            <option value="under_review">Under Review</option>
                                            <option value="interviewed">Interviewed</option>
                                            <option value="rejected">Rejected</option>
                                        </select>
                                        <button
                                            type="submit"
                                            :disabled="statusForm.processing"
                                            class="w-full px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50"
                                        >
                                            {{ statusForm.processing ? 'Updating...' : 'Update Status' }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Job Details -->
                        <div class="bg-white rounded-lg shadow-sm border p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Position Details</h3>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Position</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ application.job_posting.title }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Department</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ application.job_posting.department.name }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Applied Date</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ formatDate(application.applied_at) }}</p>
                                </div>
                                <div v-if="application.reviewed_at">
                                    <label class="block text-sm font-medium text-gray-700">Last Reviewed</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ formatDate(application.reviewed_at) }}</p>
                                </div>
                                <div v-if="application.reviewer">
                                    <label class="block text-sm font-medium text-gray-700">Reviewed By</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ application.reviewer.name }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hire Modal -->
        <div v-if="showHireModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Hire {{ application.full_name }}</h3>
                    <form @submit.prevent="hireApplicant">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Starting Salary (Kwacha)</label>
                                <input
                                    v-model="hireForm.salary"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                                <input
                                    v-model="hireForm.start_date"
                                    type="date"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>
                            <div>
                                <label class="flex items-center">
                                    <input
                                        v-model="hireForm.create_user_account"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">Create user account for portal access</span>
                                </label>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                <textarea
                                    v-model="hireForm.notes"
                                    rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                ></textarea>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end space-x-3">
                            <button
                                type="button"
                                @click="showHireModal = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="hireForm.processing"
                                class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 disabled:opacity-50"
                            >
                                {{ hireForm.processing ? 'Hiring...' : 'Hire Employee' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { 
    ArrowLeftIcon, 
    DocumentArrowDownIcon, 
    UserPlusIcon 
} from '@heroicons/vue/24/outline';
import { formatDate, formatKwacha } from '@/utils/formatting';

interface Application {
    id: number;
    full_name: string;
    email: string;
    phone: string;
    national_id?: string;
    address?: string;
    expected_salary?: number;
    cover_letter?: string;
    status: string;
    status_color: string;
    applied_at: string;
    reviewed_at?: string;
    admin_notes?: string;
    resume_path?: string;
    job_posting: {
        title: string;
        department: {
            name: string;
        };
    };
    reviewer?: {
        name: string;
    };
}

interface Props {
    application: Application;
}

const props = defineProps<Props>();

const showHireModal = ref(false);

const statusForm = useForm({
    status: props.application.status,
    notes: '',
});

const notesForm = useForm({
    notes: props.application.admin_notes || '',
});

const hireForm = useForm({
    salary: props.application.expected_salary || '',
    start_date: '',
    create_user_account: true,
    notes: '',
});

const formatStatus = (status: string): string => {
    return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const updateStatus = () => {
    statusForm.patch(route('admin.applications.update-status', props.application.id));
};

const updateNotes = () => {
    notesForm.patch(route('admin.applications.update-status', props.application.id), {
        data: {
            status: props.application.status,
            notes: notesForm.notes,
        }
    });
};

const hireApplicant = () => {
    hireForm.post(route('admin.applications.hire', props.application.id), {
        onSuccess: () => {
            showHireModal.value = false;
        }
    });
};
</script>
