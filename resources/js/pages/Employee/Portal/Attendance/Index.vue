<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/Layouts/EmployeePortalLayout.vue';
import { ref, watch } from 'vue';
import {
    ClockIcon,
    PlayIcon,
    StopIcon,
    PauseIcon,
    ArrowPathIcon,
} from '@heroicons/vue/24/outline';

interface AttendanceRecord {
    id: number;
    date: string;
    clock_in: string;
    clock_out: string;
    hours_worked: number;
    overtime_hours: number;
    status: string;
}

interface Props {
    summary: {
        today: {
            clocked_in: boolean;
            clocked_out: boolean;
            on_break: boolean;
            clock_in_time: string | null;
            clock_out_time: string | null;
            hours_worked: number | null;
        };
        weekly_hours: number | null;
        status: string;
    };
    monthlyStats: {
        total_days: number;
        working_days: number;
        present_days: number;
        late_days: number;
        absent_days: number;
        total_hours: number | null;
        total_overtime: number | null;
        average_hours_per_day: number | null;
        attendance_rate: number | null;
    };
    history: AttendanceRecord[];
    filters: {
        year: string;
        month: string;
    };
}

const props = withDefaults(defineProps<Props>(), {
    summary: () => ({
        today: {
            clocked_in: false,
            clocked_out: false,
            on_break: false,
            clock_in_time: null,
            clock_out_time: null,
            hours_worked: 0,
        },
        weekly_hours: 0,
        status: 'not_clocked_in',
    }),
    monthlyStats: () => ({
        total_days: 0,
        working_days: 0,
        present_days: 0,
        late_days: 0,
        absent_days: 0,
        total_hours: 0,
        total_overtime: 0,
        average_hours_per_day: 0,
        attendance_rate: 0,
    }),
    history: () => [],
    filters: () => ({ year: new Date().getFullYear().toString(), month: (new Date().getMonth() + 1).toString().padStart(2, '0') }),
});

const selectedYear = ref(props.filters.year);
const selectedMonth = ref(props.filters.month);
const isLoading = ref(false);

const applyFilters = () => {
    router.get(route('employee.portal.attendance.index'), {
        year: selectedYear.value,
        month: selectedMonth.value,
    }, { preserveState: true });
};

watch([selectedYear, selectedMonth], applyFilters);

const clockIn = () => {
    isLoading.value = true;
    router.post(route('employee.portal.attendance.clock-in'), {}, {
        onFinish: () => isLoading.value = false,
    });
};

const clockOut = () => {
    isLoading.value = true;
    router.post(route('employee.portal.attendance.clock-out'), {}, {
        onFinish: () => isLoading.value = false,
    });
};

const startBreak = () => {
    isLoading.value = true;
    router.post(route('employee.portal.attendance.break-start'), {}, {
        onFinish: () => isLoading.value = false,
    });
};

const endBreak = () => {
    isLoading.value = true;
    router.post(route('employee.portal.attendance.break-end'), {}, {
        onFinish: () => isLoading.value = false,
    });
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        'present': 'bg-green-100 text-green-700',
        'late': 'bg-amber-100 text-amber-700',
        'absent': 'bg-red-100 text-red-700',
        'half_day': 'bg-blue-100 text-blue-700',
    };
    return colors[status] || 'bg-gray-100 text-gray-700';
};

const months = [
    { value: '01', label: 'January' },
    { value: '02', label: 'February' },
    { value: '03', label: 'March' },
    { value: '04', label: 'April' },
    { value: '05', label: 'May' },
    { value: '06', label: 'June' },
    { value: '07', label: 'July' },
    { value: '08', label: 'August' },
    { value: '09', label: 'September' },
    { value: '10', label: 'October' },
    { value: '11', label: 'November' },
    { value: '12', label: 'December' },
];

const years = Array.from({ length: 3 }, (_, i) => new Date().getFullYear() - i);
</script>

<template>
    <Head title="Attendance" />

    <EmployeePortalLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Attendance</h1>
                <p class="text-gray-500 mt-1">Track your work hours and attendance</p>
            </div>

            <!-- Today's Status Card -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100">Today's Status</p>
                        <p class="text-3xl font-bold mt-1">
                            {{ summary.status === 'not_clocked_in' ? 'Not Clocked In' :
                               summary.status === 'working' ? 'Working' :
                               summary.status === 'on_break' ? 'On Break' : 'Clocked Out' }}
                        </p>
                        <div v-if="summary.today.clocked_in" class="mt-2 text-blue-100">
                            <span>In: {{ summary.today.clock_in_time }}</span>
                            <span v-if="summary.today.clock_out_time" class="ml-4">Out: {{ summary.today.clock_out_time }}</span>
                            <span class="ml-4">{{ Number(summary.today.hours_worked ?? 0).toFixed(1) }}h worked</span>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <!-- Clock In -->
                        <button v-if="!summary.today.clocked_in"
                            @click="clockIn"
                            :disabled="isLoading"
                            class="flex items-center gap-2 px-4 py-2 bg-white text-blue-600 rounded-lg hover:bg-blue-50 disabled:opacity-50 transition-colors">
                            <PlayIcon class="h-5 w-5" aria-hidden="true" />
                            Clock In
                        </button>

                        <!-- Break Controls -->
                        <template v-else-if="!summary.today.clocked_out">
                            <button v-if="!summary.today.on_break"
                                @click="startBreak"
                                :disabled="isLoading"
                                class="flex items-center gap-2 px-4 py-2 bg-white/20 text-white rounded-lg hover:bg-white/30 disabled:opacity-50 transition-colors">
                                <PauseIcon class="h-5 w-5" aria-hidden="true" />
                                Start Break
                            </button>
                            <button v-else
                                @click="endBreak"
                                :disabled="isLoading"
                                class="flex items-center gap-2 px-4 py-2 bg-white/20 text-white rounded-lg hover:bg-white/30 disabled:opacity-50 transition-colors">
                                <ArrowPathIcon class="h-5 w-5" aria-hidden="true" />
                                End Break
                            </button>

                            <!-- Clock Out -->
                            <button @click="clockOut"
                                :disabled="isLoading || summary.today.on_break"
                                class="flex items-center gap-2 px-4 py-2 bg-white text-blue-600 rounded-lg hover:bg-blue-50 disabled:opacity-50 transition-colors">
                                <StopIcon class="h-5 w-5" aria-hidden="true" />
                                Clock Out
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500">This Week</p>
                    <p class="text-2xl font-bold text-gray-900">{{ Number(summary.weekly_hours ?? 0).toFixed(1) }}h</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500">Monthly Hours</p>
                    <p class="text-2xl font-bold text-gray-900">{{ Number(monthlyStats.total_hours ?? 0).toFixed(1) }}h</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500">Overtime</p>
                    <p class="text-2xl font-bold text-gray-900">{{ Number(monthlyStats.total_overtime ?? 0).toFixed(1) }}h</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500">Attendance Rate</p>
                    <p class="text-2xl font-bold text-gray-900">{{ Number(monthlyStats.attendance_rate ?? 0) }}%</p>
                </div>
            </div>

            <!-- History -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">Attendance History</h2>
                    <div class="flex gap-2">
                        <select v-model="selectedMonth"
                            class="text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            <option v-for="month in months" :key="month.value" :value="month.value">
                                {{ month.label }}
                            </option>
                        </select>
                        <select v-model="selectedYear"
                            class="text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            <option v-for="year in years" :key="year" :value="year.toString()">{{ year }}</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Clock In</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Clock Out</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hours</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Overtime</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="record in history" :key="record.id" class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    {{ new Date(record.date).toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' }) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ record.clock_in || '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ record.clock_out || '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 font-medium">{{ record.hours_worked ? Number(record.hours_worked).toFixed(1) : '-' }}h</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ Number(record.overtime_hours ?? 0).toFixed(1) }}h</td>
                                <td class="px-4 py-3">
                                    <span :class="getStatusColor(record.status)"
                                        class="px-2 py-1 text-xs font-medium rounded-full">
                                        {{ record.status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="history.length === 0" class="p-12 text-center">
                    <ClockIcon class="h-12 w-12 mx-auto text-gray-300 mb-4" aria-hidden="true" />
                    <p class="text-gray-500">No attendance records for this period</p>
                </div>
            </div>
        </div>
    </EmployeePortalLayout>
</template>
