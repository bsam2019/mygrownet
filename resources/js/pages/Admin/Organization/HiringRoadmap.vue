<template>
    <AdminLayout>
        <Head title="Hiring Roadmap" />

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">Hiring Roadmap</h1>
                        <p class="mt-1 text-sm text-gray-600">
                            Plan and track recruitment across 3 phases
                        </p>
                    </div>
                    <button
                        @click="showCreateModal = true"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
                    >
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Position
                    </button>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-6">
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Phase 1 Planned</dt>
                                        <dd class="text-lg font-semibold text-gray-900">{{ stats.phase_1_planned }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">In Progress</dt>
                                        <dd class="text-lg font-semibold text-gray-900">{{ stats.phase_1_in_progress }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Hired</dt>
                                        <dd class="text-lg font-semibold text-gray-900">{{ stats.phase_1_hired }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Overdue</dt>
                                        <dd class="text-lg font-semibold text-gray-900">{{ stats.overdue_hires }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Roadmap by Phase -->
                <div class="space-y-6">
                    <div v-for="(phaseItems, phase) in roadmap" :key="phase" class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900">{{ formatPhase(phase) }}</h3>
                                <span class="text-sm text-gray-500">{{ phaseItems.length }} position(s)</span>
                            </div>
                            
                            <div class="space-y-4">
                                <div v-for="item in phaseItems" :key="item.id" class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center">
                                                <h4 class="text-sm font-semibold text-gray-900">{{ item.position.title }}</h4>
                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" :class="statusBadgeClass(item.status)">
                                                    {{ formatStatus(item.status) }}
                                                </span>
                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" :class="priorityBadgeClass(item.priority)">
                                                    {{ item.priority }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-1">{{ item.position.department.name }}</p>
                                            
                                            <div class="mt-2 flex items-center space-x-4 text-xs text-gray-500">
                                                <span>Headcount: <strong class="text-gray-900">{{ item.headcount }}</strong></span>
                                                <span v-if="item.target_hire_date">Target: <strong class="text-gray-900">{{ formatDate(item.target_hire_date) }}</strong></span>
                                                <span v-if="item.budget_allocated">Budget: <strong class="text-gray-900">K{{ item.budget_allocated }}</strong></span>
                                            </div>
                                            
                                            <p v-if="item.notes" class="text-xs text-gray-600 mt-2">{{ item.notes }}</p>
                                        </div>

                                        <div class="ml-4 flex items-center space-x-2">
                                            <button
                                                @click="editItem(item)"
                                                class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                                            >
                                                Edit
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="Object.keys(roadmap).length === 0" class="bg-white shadow rounded-lg">
                        <div class="px-4 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No hiring plans</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by adding positions to your hiring roadmap.</p>
                            <div class="mt-6">
                                <button
                                    @click="showCreateModal = true"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
                                >
                                    Add Position
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <HiringRoadmapModal
            :show="showCreateModal || showEditModal"
            :item="selectedItem"
            :positions="positions"
            @close="closeModal"
            @saved="handleSaved"
        />
    </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import HiringRoadmapModal from '@/components/Admin/Organization/HiringRoadmapModal.vue';

defineProps({
    roadmap: {
        type: Object,
        required: true
    },
    positions: {
        type: Array,
        required: true
    },
    stats: {
        type: Object,
        required: true
    }
});

const showCreateModal = ref(false);
const showEditModal = ref(false);
const selectedItem = ref(null);

const editItem = (item) => {
    selectedItem.value = item;
    showEditModal.value = true;
};

const closeModal = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    selectedItem.value = null;
};

const handleSaved = () => {
    closeModal();
};

const formatPhase = (phase) => {
    const labels = {
        'phase_1': 'Phase 1 (0-6 months)',
        'phase_2': 'Phase 2 (6-18 months)',
        'phase_3': 'Phase 3 (18+ months)'
    };
    return labels[phase] || phase;
};

const formatStatus = (status) => {
    const labels = {
        'planned': 'Planned',
        'in_progress': 'In Progress',
        'hired': 'Hired',
        'cancelled': 'Cancelled'
    };
    return labels[status] || status;
};

const statusBadgeClass = (status) => {
    const classes = {
        'planned': 'bg-blue-100 text-blue-800',
        'in_progress': 'bg-yellow-100 text-yellow-800',
        'hired': 'bg-green-100 text-green-800',
        'cancelled': 'bg-gray-100 text-gray-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const priorityBadgeClass = (priority) => {
    const classes = {
        'critical': 'bg-red-100 text-red-800',
        'high': 'bg-orange-100 text-orange-800',
        'medium': 'bg-yellow-100 text-yellow-800',
        'low': 'bg-gray-100 text-gray-800'
    };
    return classes[priority] || 'bg-gray-100 text-gray-800';
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};
</script>
