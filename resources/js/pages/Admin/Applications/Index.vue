<template>
    <AdminLayout title="Job Applications">
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Page Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                    <h1 class="text-2xl font-bold text-gray-900">Job Applications</h1>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-lg shadow-sm border">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <DocumentTextIcon class="w-6 h-6 text-blue-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Applications</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.total }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm border">
                        <div class="flex items-center">
                            <div class="p-2 bg-yellow-100 rounded-lg">
                                <ClockIcon class="w-6 h-6 text-yellow-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Pending Review</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.pending }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm border">
                        <div class="flex items-center">
                            <div class="p-2 bg-purple-100 rounded-lg">
                                <EyeIcon class="w-6 h-6 text-purple-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Under Review</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.under_review }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm border">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <CheckCircleIcon class="w-6 h-6 text-green-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Hired</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.hired }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white p-6 rounded-lg shadow-sm border mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search applicants..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                @input="handleSearch"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select
                                v-model="selectedStatus"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                @change="handleFilter"
                            >
                                <option value="">All Statuses</option>
                                <option value="submitted">Submitted</option>
                                <option value="under_review">Under Review</option>
                                <option value="interviewed">Interviewed</option>
                                <option value="hired">Hired</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button
                                v-if="selectedApplications.length > 0"
                                @click="showBulkActions = true"
                                class="px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700"
                            >
                                Bulk Actions ({{ selectedApplications.length }})
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Applications List -->
                <div class="bg-white rounded-lg shadow-sm border">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Applications</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left">
                                        <input
                                            type="checkbox"
                                            @change="toggleSelectAll"
                                            :checked="allSelected"
                                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                                        />
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Applicant
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Position
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Applied Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="application in applications.data" :key="application.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input
                                            type="checkbox"
                                            :value="application.id"
                                            v-model="selectedApplications"
                                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                                        />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ application.full_name }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ application.email }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ application.job_posting.title }}</div>
                                        <div class="text-sm text-gray-500">{{ application.job_posting.department.name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ formatDate(application.applied_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="[
                                                'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                                application.status_color
                                            ]"
                                        >
                                            {{ formatStatus(application.status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link
                                            :href="route('admin.applications.show', application.id)"
                                            class="text-blue-600 hover:text-blue-900 mr-3"
                                        >
                                            View
                                        </Link>
                                        <a
                                            v-if="application.resume_path"
                                            :href="route('admin.applications.resume', application.id)"
                                            class="text-green-600 hover:text-green-900 mr-3"
                                            target="_blank"
                                        >
                                            Resume
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="applications.links" class="px-6 py-4 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Showing {{ applications.from }} to {{ applications.to }} of {{ applications.total }} results
                            </div>
                            <div class="flex space-x-1">
                                <Link
                                    v-for="link in applications.links"
                                    :key="link.label"
                                    :href="link.url"
                                    :class="[
                                        'px-3 py-2 text-sm rounded-md',
                                        link.active
                                            ? 'bg-blue-600 text-white'
                                            : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
                                    ]"
                                    v-html="link.label"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Actions Modal -->
        <div v-if="showBulkActions" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Bulk Actions</h3>
                    <div class="space-y-3">
                        <button
                            @click="bulkUpdate('under_review')"
                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
                        >
                            Mark as Under Review
                        </button>
                        <button
                            @click="bulkUpdate('rejected')"
                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
                        >
                            Mark as Rejected
                        </button>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            @click="showBulkActions = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { 
    DocumentTextIcon, 
    ClockIcon, 
    EyeIcon, 
    CheckCircleIcon 
} from '@heroicons/vue/24/outline';
import { formatDate } from '@/utils/formatting';

interface Application {
    id: number;
    full_name: string;
    email: string;
    status: string;
    status_color: string;
    applied_at: string;
    resume_path?: string;
    job_posting: {
        title: string;
        department: {
            name: string;
        };
    };
}

interface Props {
    applications: {
        data: Application[];
        links?: any[];
        from?: number;
        to?: number;
        total?: number;
    };
    stats: {
        total: number;
        pending: number;
        under_review: number;
        hired: number;
    };
    filters: {
        search?: string;
        status?: string;
    };
}

const props = defineProps<Props>();

const searchQuery = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || '');
const selectedApplications = ref<number[]>([]);
const showBulkActions = ref(false);

const allSelected = computed(() => {
    return selectedApplications.value.length === props.applications.data.length;
});

const formatStatus = (status: string): string => {
    return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const toggleSelectAll = () => {
    if (allSelected.value) {
        selectedApplications.value = [];
    } else {
        selectedApplications.value = props.applications.data.map(app => app.id);
    }
};

const handleSearch = () => {
    router.get(route('admin.applications.index'), {
        search: searchQuery.value,
        status: selectedStatus.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const handleFilter = () => {
    router.get(route('admin.applications.index'), {
        search: searchQuery.value,
        status: selectedStatus.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const bulkUpdate = (status: string) => {
    router.post(route('admin.applications.bulk-update'), {
        application_ids: selectedApplications.value,
        status: status,
    }, {
        onSuccess: () => {
            selectedApplications.value = [];
            showBulkActions.value = false;
        }
    });
};
</script>
