<template>
    <AdminLayout>
        <Head :title="`KPIs - ${employee.full_name}`" />
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <Link :href="route('admin.employees.index')" class="text-sm text-blue-600 hover:text-blue-800 mb-2 inline-block">
                        ← Back to Employees
                    </Link>
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-semibold text-gray-900">{{ employee.full_name }}</h1>
                            <p class="mt-1 text-sm text-gray-600">{{ employee.position.title }} • {{ employee.department.name }}</p>
                        </div>
                        <button @click="showRecordModal = true" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                            Record KPI
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-4 mb-6">
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total KPIs</dt>
                                <dd class="text-lg font-semibold text-gray-900">{{ availableKpis.length }}</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Tracked</dt>
                                <dd class="text-lg font-semibold text-gray-900">{{ tracking.length }}</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Avg Achievement</dt>
                                <dd class="text-lg font-semibold text-gray-900">{{ averageAchievement }}%</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Performance</dt>
                                <dd class="text-lg font-semibold" :class="performanceColor">{{ performanceStatus }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">KPI Tracking History</h3>
                        <div v-if="tracking.length > 0" class="space-y-4">
                            <div v-for="record in tracking" :key="record.id" class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="text-sm font-semibold text-gray-900">{{ record.position_kpi.kpi_name }}</h4>
                                        <div class="mt-2 grid grid-cols-2 gap-4 text-sm">
                                            <div>
                                                <span class="text-gray-500">Period:</span>
                                                <span class="ml-1 text-gray-900 font-medium">{{ formatDate(record.period_start) }} - {{ formatDate(record.period_end) }}</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Target:</span>
                                                <span class="ml-1 text-gray-900 font-medium">{{ record.target_value }} {{ record.position_kpi.measurement_unit }}</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Actual:</span>
                                                <span class="ml-1 font-medium" :class="record.achievement_percentage >= 100 ? 'text-green-600' : 'text-red-600'">{{ record.actual_value }} {{ record.position_kpi.measurement_unit }}</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Achievement:</span>
                                                <span class="ml-1 text-gray-900 font-medium">{{ record.achievement_percentage }}%</span>
                                            </div>
                                        </div>
                                        <p v-if="record.notes" class="text-xs text-gray-600 mt-2 italic">Note: {{ record.notes }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-12">
                            <p class="text-sm text-gray-500">No KPI records yet. Click "Record KPI" to get started.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showRecordModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 max-w-md w-full">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Record KPI</h3>
                <p class="text-sm text-gray-500">KPI recording modal coming soon...</p>
                <button @click="showRecordModal = false" class="mt-4 w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Close</button>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    employee: Object,
    tracking: Array,
    availableKpis: Array
});

const showRecordModal = ref(false);

const averageAchievement = computed(() => {
    if (props.tracking.length === 0) return 0;
    const sum = props.tracking.reduce((acc, record) => acc + parseFloat(record.achievement_percentage), 0);
    return Math.round(sum / props.tracking.length);
});

const performanceStatus = computed(() => {
    const avg = averageAchievement.value;
    if (avg >= 100) return 'Excellent';
    if (avg >= 80) return 'Good';
    if (avg >= 60) return 'Fair';
    return 'Needs Improvement';
});

const performanceColor = computed(() => {
    const avg = averageAchievement.value;
    if (avg >= 100) return 'text-green-600';
    if (avg >= 80) return 'text-blue-600';
    if (avg >= 60) return 'text-yellow-600';
    return 'text-red-600';
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};
</script>
