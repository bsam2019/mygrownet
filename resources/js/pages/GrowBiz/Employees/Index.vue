<template>
    <GrowBizLayout>
        <PullToRefresh ref="pullToRefreshRef" @refresh="handleRefresh">
            <div class="px-4 py-4 pb-6">
                <!-- Floating Action Button -->
                <FloatingActionButton 
                    @click="router.visit(route('growbiz.employees.create'))"
                />
                
                <!-- Page Header -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">Team</h1>
                        <p class="text-sm text-gray-500">Manage your employees</p>
                    </div>
                    <Link 
                        :href="route('growbiz.employees.create')" 
                        class="hidden sm:inline-flex items-center px-4 py-2 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 active:bg-emerald-800 transition-colors shadow-sm"
                    >
                        <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                        Add Employee
                    </Link>
                </div>

                <!-- Stats Cards - Animated -->
                <SkeletonLoader v-if="isLoading" type="stats" />
                <div v-else class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
                    <div class="stat-card bg-white rounded-2xl shadow-sm p-4 text-center active:scale-95 transition-transform">
                        <p class="text-2xl font-bold text-gray-900">{{ stats.total }}</p>
                        <p class="text-xs text-gray-500 mt-1">Total</p>
                    </div>
                    <div class="stat-card bg-white rounded-2xl shadow-sm p-4 text-center active:scale-95 transition-transform">
                        <p class="text-2xl font-bold text-emerald-600">{{ stats.active }}</p>
                        <p class="text-xs text-gray-500 mt-1">Active</p>
                    </div>
                    <div class="stat-card bg-white rounded-2xl shadow-sm p-4 text-center active:scale-95 transition-transform">
                        <p class="text-2xl font-bold text-gray-400">{{ stats.inactive }}</p>
                        <p class="text-xs text-gray-500 mt-1">Inactive</p>
                    </div>
                    <div class="stat-card bg-white rounded-2xl shadow-sm p-4 text-center active:scale-95 transition-transform">
                        <p class="text-2xl font-bold text-amber-500">{{ stats.on_leave }}</p>
                        <p class="text-xs text-gray-500 mt-1">On Leave</p>
                    </div>
                </div>

                <!-- Filter Button -->
                <button 
                    @click="showFilters = true"
                    class="w-full mb-4 flex items-center justify-between p-4 bg-white rounded-2xl shadow-sm active:bg-gray-50 transition-colors"
                >
                    <div class="flex items-center gap-3">
                        <FunnelIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                        <span class="text-gray-700">Filters & Search</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span v-if="activeFilterCount > 0" class="bg-emerald-100 text-emerald-700 text-xs font-medium px-2 py-0.5 rounded-full">
                            {{ activeFilterCount }}
                        </span>
                        <ChevronRightIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                    </div>
                </button>

                <!-- Employees List - Swipeable on Mobile -->
                <SkeletonLoader v-if="isLoading" type="list" :count="5" />
                <div v-else-if="employees.length > 0" class="bg-white rounded-2xl shadow-sm overflow-hidden">
                    <TransitionGroup name="list" tag="ul" class="divide-y divide-gray-100">
                        <SwipeableListItem 
                            v-for="employee in employees" 
                            :key="`employee-${employee.id}`"
                            @view="router.visit(route('growbiz.employees.show', employee.id))"
                            @edit="router.visit(route('growbiz.employees.edit', employee.id))"
                            @delete="confirmDelete(employee)"
                        >
                            <div class="p-4 bg-white">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center flex-shrink-0">
                                        <span class="text-sm font-medium text-white">{{ getInitials(employee.name) }}</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-900 truncate">{{ employee.name }}</p>
                                        <p class="text-sm text-gray-500 truncate">{{ employee.position || 'No position' }} {{ employee.department ? '• ' + employee.department : '' }}</p>
                                    </div>
                                    <!-- Account status indicator -->
                                    <div v-if="employee.user_id" class="p-1.5 rounded-lg bg-green-50" title="Account linked">
                                        <CheckBadgeIcon class="h-4 w-4 text-green-600" aria-hidden="true" />
                                    </div>
                                    <button 
                                        v-else
                                        @click.stop="openInviteModal(employee)"
                                        class="p-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 active:bg-blue-200 transition-colors"
                                        :aria-label="`Invite ${employee.name}`"
                                        title="Send invite"
                                    >
                                        <UserPlusIcon class="h-4 w-4 text-blue-600" aria-hidden="true" />
                                    </button>
                                    <StatusBadge v-if="employee.status" :status="employee.status" size="sm" />
                                </div>
                            </div>
                        </SwipeableListItem>
                    </TransitionGroup>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white rounded-2xl shadow-sm p-12 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                        <UsersIcon class="h-8 w-8 text-gray-400" aria-hidden="true" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No employees found</h3>
                    <p class="text-gray-500 mb-6">Get started by adding your first team member.</p>
                    <HapticButton @click="router.visit(route('growbiz.employees.create'))">
                        <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                        Add Employee
                    </HapticButton>
                </div>
            </div>
        </PullToRefresh>

        <!-- Invite Employee Modal -->
        <InviteEmployeeModal
            v-if="selectedEmployee"
            :show="showInviteModal"
            :employee-id="selectedEmployee.id"
            :employee-name="selectedEmployee.name"
            :employee-email="selectedEmployee.email"
            @close="closeInviteModal"
            @invited="closeInviteModal"
        />

        <!-- Mobile Filter Bottom Sheet -->
        <BottomSheet v-model="showFilters" title="Filters">
            <div class="p-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input 
                        v-model="localFilters.search" 
                        type="text" 
                        placeholder="Search employees..."
                        class="w-full rounded-xl border-gray-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <div class="flex flex-wrap gap-2">
                        <button 
                            v-for="status in [{ value: '', label: 'All' }, ...statuses]" 
                            :key="status.value"
                            @click="localFilters.status = status.value"
                            :class="[
                                'px-4 py-2 rounded-full text-sm font-medium transition-colors',
                                localFilters.status === status.value 
                                    ? 'bg-emerald-600 text-white' 
                                    : 'bg-gray-100 text-gray-700 active:bg-gray-200'
                            ]"
                        >
                            {{ status.label }}
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                    <div class="flex flex-wrap gap-2">
                        <button 
                            @click="localFilters.department = ''"
                            :class="[
                                'px-4 py-2 rounded-full text-sm font-medium transition-colors',
                                localFilters.department === '' 
                                    ? 'bg-emerald-600 text-white' 
                                    : 'bg-gray-100 text-gray-700 active:bg-gray-200'
                            ]"
                        >
                            All
                        </button>
                        <button 
                            v-for="dept in departments" 
                            :key="dept"
                            @click="localFilters.department = dept"
                            :class="[
                                'px-4 py-2 rounded-full text-sm font-medium transition-colors',
                                localFilters.department === dept 
                                    ? 'bg-emerald-600 text-white' 
                                    : 'bg-gray-100 text-gray-700 active:bg-gray-200'
                            ]"
                        >
                            {{ dept }}
                        </button>
                    </div>
                </div>
                <div class="pt-4 flex justify-between gap-3">
                    <button 
                        @click="clearFilters(); showFilters = false"
                        class="flex-1 px-4 py-3 text-gray-700 bg-gray-100 rounded-xl font-medium active:bg-gray-200 transition-colors"
                    >
                        Clear All
                    </button>
                    <button 
                        @click="applyFilters(); showFilters = false"
                        class="flex-1 px-4 py-3 text-white bg-emerald-600 rounded-xl font-medium active:bg-emerald-700 transition-colors"
                    >
                        Apply Filters
                    </button>
                </div>
            </div>
        </BottomSheet>
    </GrowBizLayout>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import GrowBizLayout from '@/Layouts/GrowBizLayout.vue';
import StatusBadge from '@/Components/GrowBiz/StatusBadge.vue';
import PullToRefresh from '@/Components/GrowBiz/PullToRefresh.vue';
import BottomSheet from '@/Components/GrowBiz/BottomSheet.vue';
import SwipeableListItem from '@/Components/GrowBiz/SwipeableListItem.vue';
import SkeletonLoader from '@/Components/GrowBiz/SkeletonLoader.vue';
import FloatingActionButton from '@/Components/GrowBiz/FloatingActionButton.vue';
import HapticButton from '@/Components/GrowBiz/HapticButton.vue';
import InviteEmployeeModal from '@/Components/GrowBiz/InviteEmployeeModal.vue';
import { PlusIcon, ChevronRightIcon, UsersIcon, FunnelIcon, UserPlusIcon, CheckBadgeIcon } from '@heroicons/vue/24/outline';
import { useDebounceFn } from '@vueuse/core';

interface Employee {
    id: number;
    name: string;
    email: string | null;
    position: string | null;
    department: string | null;
    status: string;
    user_id: number | null;
}

interface Props {
    employees: Employee[];
    stats: { total: number; active: number; inactive: number; on_leave: number; };
    departments: string[];
    filters: { status: string | null; department: string | null; search: string | null; };
    statuses: Array<{ value: string; label: string }>;
}

const props = defineProps<Props>();

const showFilters = ref(false);
const isLoading = ref(false);
const pullToRefreshRef = ref<InstanceType<typeof PullToRefresh> | null>(null);

// Invite modal state
const showInviteModal = ref(false);
const selectedEmployee = ref<Employee | null>(null);

const openInviteModal = (employee: Employee) => {
    selectedEmployee.value = employee;
    showInviteModal.value = true;
};

const closeInviteModal = () => {
    showInviteModal.value = false;
    selectedEmployee.value = null;
};

const localFilters = reactive({
    status: props.filters.status || '',
    department: props.filters.department || '',
    search: props.filters.search || '',
});

const activeFilterCount = computed(() => {
    let count = 0;
    if (localFilters.status) count++;
    if (localFilters.department) count++;
    if (localFilters.search) count++;
    return count;
});

const getInitials = (name: string) => {
    if (!name) return '?';
    const parts = name.split(' ');
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
};

const applyFilters = () => {
    router.get(route('growbiz.employees.index'), {
        status: localFilters.status || undefined,
        department: localFilters.department || undefined,
        search: localFilters.search || undefined,
    }, { preserveState: true, preserveScroll: true });
};

const debouncedFilter = useDebounceFn(applyFilters, 300);

const clearFilters = () => {
    localFilters.status = '';
    localFilters.department = '';
    localFilters.search = '';
    router.get(route('growbiz.employees.index'));
};

const handleRefresh = () => {
    router.reload({
        onFinish: () => {
            pullToRefreshRef.value?.finishRefresh();
        }
    });
};

const confirmDelete = (employee: Employee) => {
    if (confirm(`Are you sure you want to delete ${employee.name}?`)) {
        router.delete(route('growbiz.employees.destroy', employee.id));
    }
};
</script>

<style scoped>
/* List animations */
.list-enter-active,
.list-leave-active {
    transition: all 0.3s ease;
}

.list-enter-from {
    opacity: 0;
    transform: translateX(-20px);
}

.list-leave-to {
    opacity: 0;
    transform: translateX(20px);
}

.list-move {
    transition: transform 0.3s ease;
}

/* Stat card hover effect */
.stat-card {
    touch-action: manipulation;
    -webkit-tap-highlight-color: transparent;
}
</style>
