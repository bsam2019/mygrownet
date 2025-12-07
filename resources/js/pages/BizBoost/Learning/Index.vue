<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import {
    AcademicCapIcon,
    PlayCircleIcon,
    CheckCircleIcon,
    LockClosedIcon,
    TrophyIcon,
    ClockIcon,
} from '@heroicons/vue/24/outline';

interface Course {
    id: number;
    title: string;
    slug: string;
    description: string;
    thumbnail: string | null;
    category: string;
    difficulty: string;
    duration_minutes: number;
    lessons_count: number;
    tier_required: string;
    has_certificate: boolean;
    is_locked: boolean;
    progress_percent: number;
    is_completed: boolean;
}

interface Certificate {
    id: number;
    course_title: string;
    certificate_number: string;
    issued_at: string;
}

interface Props {
    courses: Course[];
    categories: string[];
    certificates: Certificate[];
    stats: {
        total_courses: number;
        completed: number;
        in_progress: number;
        certificates: number;
    };
}

const props = defineProps<Props>();
const selectedCategory = ref<string | null>(null);

const filteredCourses = computed(() => {
    if (!selectedCategory.value) return props.courses;
    return props.courses.filter(c => c.category === selectedCategory.value);
});
</script>

<template>
    <Head title="Learning Hub - BizBoost" />
    <BizBoostLayout title="Learning Hub">
        <div class="space-y-6">
            <!-- Stats -->
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <div class="bg-white rounded-xl p-4 shadow-sm ring-1 ring-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-violet-100 p-2">
                            <AcademicCapIcon class="h-5 w-5 text-violet-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Total Courses</p>
                            <p class="text-xl font-semibold text-gray-900">{{ stats.total_courses }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm ring-1 ring-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-blue-100 p-2">
                            <PlayCircleIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">In Progress</p>
                            <p class="text-xl font-semibold text-gray-900">{{ stats.in_progress }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm ring-1 ring-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-emerald-100 p-2">
                            <CheckCircleIcon class="h-5 w-5 text-emerald-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Completed</p>
                            <p class="text-xl font-semibold text-gray-900">{{ stats.completed }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm ring-1 ring-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-amber-100 p-2">
                            <TrophyIcon class="h-5 w-5 text-amber-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Certificates</p>
                            <p class="text-xl font-semibold text-gray-900">{{ stats.certificates }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Category Filter -->
            <div class="flex flex-wrap gap-2">
                <button
                    @click="selectedCategory = null"
                    :class="[
                        'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                        !selectedCategory
                            ? 'bg-violet-600 text-white'
                            : 'bg-white text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50'
                    ]"
                >
                    All Courses
                </button>
                <button
                    v-for="category in categories"
                    :key="category"
                    @click="selectedCategory = category"
                    :class="[
                        'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                        selectedCategory === category
                            ? 'bg-violet-600 text-white'
                            : 'bg-white text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50'
                    ]"
                >
                    {{ category }}
                </button>
            </div>

            <!-- Course Grid -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="course in filteredCourses"
                    :key="course.id"
                    class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 overflow-hidden"
                >
                    <div class="aspect-video bg-gray-100 relative">
                        <img
                            v-if="course.thumbnail"
                            :src="course.thumbnail"
                            :alt="course.title"
                            class="w-full h-full object-cover"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center">
                            <AcademicCapIcon class="h-12 w-12 text-gray-300" aria-hidden="true" />
                        </div>
                        <div v-if="course.is_locked" class="absolute inset-0 bg-black/50 flex items-center justify-center">
                            <LockClosedIcon class="h-8 w-8 text-white" aria-hidden="true" />
                        </div>
                        <div v-if="course.is_completed" class="absolute top-2 right-2 bg-emerald-500 text-white px-2 py-1 rounded text-xs font-medium">
                            Completed
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-xs font-medium text-violet-600 bg-violet-50 px-2 py-1 rounded">
                                {{ course.category }}
                            </span>
                            <span class="text-xs text-gray-500">{{ course.difficulty }}</span>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-1">{{ course.title }}</h3>
                        <p class="text-sm text-gray-500 line-clamp-2 mb-3">{{ course.description }}</p>
                        <div class="flex items-center justify-between text-sm text-gray-500 mb-3">
                            <span class="flex items-center gap-1">
                                <ClockIcon class="h-4 w-4" aria-hidden="true" />
                                {{ course.duration_minutes }} min
                            </span>
                            <span>{{ course.lessons_count }} lessons</span>
                        </div>
                        <div v-if="course.progress_percent > 0 && !course.is_completed" class="mb-3">
                            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div
                                    class="h-full bg-violet-600 rounded-full"
                                    :style="{ width: `${course.progress_percent}%` }"
                                ></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">{{ course.progress_percent }}% complete</p>
                        </div>
                        <Link
                            v-if="!course.is_locked"
                            :href="`/bizboost/learning/${course.slug}`"
                            class="block w-full text-center px-4 py-2 bg-violet-600 text-white rounded-lg text-sm font-medium hover:bg-violet-700 transition-colors"
                        >
                            {{ course.progress_percent > 0 ? 'Continue' : 'Start Course' }}
                        </Link>
                        <button
                            v-else
                            disabled
                            class="block w-full text-center px-4 py-2 bg-gray-100 text-gray-400 rounded-lg text-sm font-medium cursor-not-allowed"
                        >
                            Upgrade to {{ course.tier_required }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Certificates Section -->
            <div v-if="certificates.length > 0" class="bg-white rounded-xl p-6 shadow-sm ring-1 ring-gray-200">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Your Certificates</h2>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="cert in certificates"
                        :key="cert.id"
                        class="flex items-center gap-3 p-3 bg-amber-50 rounded-lg"
                    >
                        <TrophyIcon class="h-8 w-8 text-amber-500" aria-hidden="true" />
                        <div>
                            <p class="font-medium text-gray-900">{{ cert.course_title }}</p>
                            <p class="text-xs text-gray-500">{{ cert.certificate_number }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
