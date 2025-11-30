<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/Layouts/EmployeePortalLayout.vue';
import {
    AcademicCapIcon,
    BookOpenIcon,
    CheckBadgeIcon,
    ClockIcon,
    PlayIcon,
    ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';

interface Course {
    id: number;
    title: string;
    category: string;
    type: string;
    duration_hours: number;
    is_mandatory: boolean;
}

interface Enrollment {
    id: number;
    status: string;
    progress: number;
    assigned_date: string;
    due_date: string | null;
    score: number | null;
    course: Course;
}

interface Props {
    enrollments: Enrollment[];
    stats: {
        total_courses: number;
        completed_courses: number;
        in_progress: number;
        overdue: number;
        total_certifications: number;
        valid_certifications: number;
        expiring_soon: number;
        average_score: number | null;
    };
    learningPath: {
        mandatory: Enrollment[];
        recommended: Course[];
    };
    filters: { status?: string };
}

const props = defineProps<Props>();

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        assigned: 'bg-gray-100 text-gray-700',
        in_progress: 'bg-blue-100 text-blue-700',
        completed: 'bg-green-100 text-green-700',
        expired: 'bg-red-100 text-red-700',
    };
    return colors[status] || 'bg-gray-100 text-gray-700';
};

const getTypeIcon = (type: string) => {
    const icons: Record<string, any> = {
        online: PlayIcon,
        classroom: AcademicCapIcon,
        workshop: BookOpenIcon,
        certification: CheckBadgeIcon,
    };
    return icons[type] || BookOpenIcon;
};
</script>

<template>
    <Head title="Training & Learning" />

    <EmployeePortalLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Training & Learning</h1>
                    <p class="text-gray-500 mt-1">Develop your skills and track certifications</p>
                </div>
                <div class="flex gap-3">
                    <Link :href="route('employee.portal.training.courses')"
                        class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        Browse Courses
                    </Link>
                    <Link :href="route('employee.portal.training.certifications')"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        My Certifications
                    </Link>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Completed Courses</p>
                            <p class="text-2xl font-bold text-green-600">{{ stats.completed_courses }}</p>
                            <p class="text-xs text-gray-400 mt-1">of {{ stats.total_courses }} total</p>
                        </div>
                        <div class="p-3 bg-green-50 rounded-lg">
                            <CheckBadgeIcon class="h-6 w-6 text-green-600" aria-hidden="true" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">In Progress</p>
                            <p class="text-2xl font-bold text-blue-600">{{ stats.in_progress }}</p>
                        </div>
                        <div class="p-3 bg-blue-50 rounded-lg">
                            <BookOpenIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Certifications</p>
                            <p class="text-2xl font-bold text-purple-600">{{ stats.valid_certifications }}</p>
                            <p v-if="stats.expiring_soon > 0" class="text-xs text-amber-600 mt-1">
                                {{ stats.expiring_soon }} expiring soon
                            </p>
                        </div>
                        <div class="p-3 bg-purple-50 rounded-lg">
                            <AcademicCapIcon class="h-6 w-6 text-purple-600" aria-hidden="true" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Average Score</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ stats.average_score ? stats.average_score.toFixed(0) + '%' : '-' }}
                            </p>
                        </div>
                        <div class="p-3 bg-amber-50 rounded-lg">
                            <CheckBadgeIcon class="h-6 w-6 text-amber-600" aria-hidden="true" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Overdue Warning -->
            <div v-if="stats.overdue > 0" class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center gap-3">
                <ExclamationTriangleIcon class="h-6 w-6 text-red-500" aria-hidden="true" />
                <div>
                    <p class="font-medium text-red-800">{{ stats.overdue }} overdue course(s)</p>
                    <p class="text-sm text-red-600">Please complete these courses as soon as possible.</p>
                </div>
            </div>

            <!-- My Courses -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="p-5 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">My Courses</h2>
                </div>

                <div class="divide-y divide-gray-100">
                    <div v-for="enrollment in enrollments" :key="enrollment.id" class="p-5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="p-3 bg-gray-100 rounded-lg">
                                    <component :is="getTypeIcon(enrollment.course.type)" class="h-6 w-6 text-gray-600" aria-hidden="true" />
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-medium text-gray-900">{{ enrollment.course.title }}</h3>
                                        <span v-if="enrollment.course.is_mandatory" class="px-2 py-0.5 text-xs bg-red-100 text-red-700 rounded">
                                            Mandatory
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-3 mt-1 text-sm text-gray-500">
                                        <span>{{ enrollment.course.category }}</span>
                                        <span>•</span>
                                        <span>{{ enrollment.course.duration_hours }}h</span>
                                        <span v-if="enrollment.due_date">•</span>
                                        <span v-if="enrollment.due_date">Due: {{ new Date(enrollment.due_date).toLocaleDateString() }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <div class="w-32">
                                    <div class="flex items-center justify-between text-sm mb-1">
                                        <span class="text-gray-500">Progress</span>
                                        <span class="font-medium">{{ enrollment.progress }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full transition-all"
                                            :style="{ width: `${enrollment.progress}%` }">
                                        </div>
                                    </div>
                                </div>

                                <span :class="getStatusColor(enrollment.status)" class="px-3 py-1 text-xs font-medium rounded-full">
                                    {{ enrollment.status.replace('_', ' ') }}
                                </span>

                                <button v-if="enrollment.status !== 'completed'"
                                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    {{ enrollment.status === 'assigned' ? 'Start' : 'Continue' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-if="enrollments.length === 0" class="p-8 text-center text-gray-500">
                        <BookOpenIcon class="h-12 w-12 mx-auto text-gray-300 mb-3" aria-hidden="true" />
                        <p>No courses assigned yet</p>
                    </div>
                </div>
            </div>
        </div>
    </EmployeePortalLayout>
</template>
