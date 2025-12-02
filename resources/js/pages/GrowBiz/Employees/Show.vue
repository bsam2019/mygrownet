<template>
    <GrowBizLayout>
        <div class="px-4 py-4 pb-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <Link 
                        :href="route('growbiz.employees.index')" 
                        class="p-2 -ml-2 rounded-xl hover:bg-gray-100 active:bg-gray-200 transition-colors"
                        aria-label="Back to team"
                    >
                        <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                    </Link>
                    <h1 class="text-xl font-bold text-gray-900">Employee</h1>
                </div>
                <div class="flex items-center gap-2">
                    <button 
                        v-if="!isLinked"
                        @click="showInviteModal = true" 
                        class="p-2 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-100 active:bg-blue-200 transition-colors"
                        aria-label="Invite employee"
                    >
                        <UserPlusIcon class="h-5 w-5" aria-hidden="true" />
                    </button>
                    <div v-else class="p-2 rounded-xl bg-green-50 text-green-600" title="Account linked">
                        <CheckBadgeIcon class="h-5 w-5" aria-hidden="true" />
                    </div>
                    <Link :href="route('growbiz.employees.edit', employee.id)" class="p-2 rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 active:bg-gray-300 transition-colors">
                        <PencilIcon class="h-5 w-5" aria-hidden="true" />
                    </Link>
                    <button @click="confirmDelete" class="p-2 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 active:bg-red-200 transition-colors">
                        <TrashIcon class="h-5 w-5" aria-hidden="true" />
                    </button>
                </div>
            </div>

            <div class="max-w-2xl mx-auto">
                <div class="bg-white rounded-2xl shadow-sm">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-start justify-between">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">{{ employee.name }}</h1>
                                <p class="text-gray-500 mt-1">{{ employee.position || 'No position' }}</p>
                                <div class="mt-2">
                                    <StatusBadge :status="employee.status" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Email</h3>
                                <p class="mt-1 text-gray-900">{{ employee.email || 'Not set' }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Phone</h3>
                                <p class="mt-1 text-gray-900">{{ employee.phone || 'Not set' }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Department</h3>
                                <p class="mt-1 text-gray-900">{{ employee.department || 'Not set' }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Hire Date</h3>
                                <p class="mt-1 text-gray-900">{{ employee.hire_date || 'Not set' }}</p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">Hourly Rate</h3>
                                <p class="mt-1 text-gray-900">{{ employee.hourly_rate ? `K${employee.hourly_rate}` : 'Not set' }}</p>
                            </div>
                        </div>

                        <div v-if="employee.notes">
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Notes</h3>
                            <p class="text-gray-900 whitespace-pre-wrap">{{ employee.notes }}</p>
                        </div>

                        <!-- Account Status -->
                        <div class="bg-gray-50 rounded-xl p-4">
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Platform Account</h3>
                            <div v-if="isLinked" class="flex items-center gap-2 text-green-600">
                                <CheckBadgeIcon class="h-5 w-5" aria-hidden="true" />
                                <span class="font-medium">Account linked</span>
                            </div>
                            <div v-else class="flex items-center justify-between">
                                <span class="text-gray-600">Not linked to platform account</span>
                                <button 
                                    @click="showInviteModal = true"
                                    class="text-sm text-blue-600 font-medium hover:text-blue-700"
                                >
                                    Send Invite
                                </button>
                            </div>
                        </div>

                        <!-- Task Stats -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-3">Task Statistics</h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div class="bg-gray-50 rounded-lg p-3 text-center">
                                    <p class="text-xl font-bold text-gray-900">{{ taskStats.total }}</p>
                                    <p class="text-xs text-gray-500">Total Tasks</p>
                                </div>
                                <div class="bg-blue-50 rounded-lg p-3 text-center">
                                    <p class="text-xl font-bold text-blue-600">{{ taskStats.in_progress }}</p>
                                    <p class="text-xs text-gray-500">In Progress</p>
                                </div>
                                <div class="bg-green-50 rounded-lg p-3 text-center">
                                    <p class="text-xl font-bold text-green-600">{{ taskStats.completed }}</p>
                                    <p class="text-xs text-gray-500">Completed</p>
                                </div>
                                <div class="bg-purple-50 rounded-lg p-3 text-center">
                                    <p class="text-xl font-bold text-purple-600">{{ taskStats.completion_rate }}%</p>
                                    <p class="text-xs text-gray-500">Completion Rate</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Invite Modal -->
        <InviteEmployeeModal
            :show="showInviteModal"
            :employee-id="employee.id"
            :employee-name="employee.name"
            :employee-email="employee.email"
            @close="showInviteModal = false"
        />
    </GrowBizLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import GrowBizLayout from '@/Layouts/GrowBizLayout.vue';
import StatusBadge from '@/Components/GrowBiz/StatusBadge.vue';
import InviteEmployeeModal from '@/Components/GrowBiz/InviteEmployeeModal.vue';
import { ArrowLeftIcon, PencilIcon, TrashIcon, UserPlusIcon, CheckBadgeIcon } from '@heroicons/vue/24/outline';

interface Props {
    employee: {
        id: number;
        name: string;
        email: string | null;
        phone: string | null;
        position: string | null;
        department: string | null;
        status: string;
        hire_date: string | null;
        hourly_rate: number | null;
        notes: string | null;
        user_id: number | null;
    };
    taskStats: {
        total: number;
        completed: number;
        in_progress: number;
        pending: number;
        completion_rate: number;
    };
}

const props = defineProps<Props>();

const showInviteModal = ref(false);

const confirmDelete = () => {
    if (confirm('Are you sure you want to remove this employee?')) {
        router.delete(route('growbiz.employees.destroy', props.employee.id));
    }
};

const isLinked = props.employee.user_id !== null;
</script>
