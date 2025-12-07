<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import {
    AcademicCapIcon,
    PlayCircleIcon,
    CheckCircleIcon,
    ClockIcon,
    ArrowLeftIcon,
    TrophyIcon,
} from '@heroicons/vue/24/outline';
import { CheckCircleIcon as CheckCircleSolid } from '@heroicons/vue/24/solid';

interface Lesson {
    id: number;
    title: string;
    slug: string;
    duration_minutes: number;
    is_completed: boolean;
}

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
    has_certificate: boolean;
}

interface Progress {
    progress_percent: number;
    completed_at: string | null;
}

interface Props {
    course: Course;
    lessons: Lesson[];
    progress: Progress | null;
    completedLessons: number[];
}

const props = defineProps<Props>();

const progressPercent = computed(() => props.progress?.progress_percent ?? 0);
const isCompleted = computed(() => props.progress?.completed_at !== null);

const nextLesson = computed(() => {
    return props.lessons.find(l => !l.is_completed) || props.lessons[0];
});
</script>

<template>
    <Head :title="`${course.title} - Learning Hub`" />
    <BizBoostLayout title="Learning Hub">
        <div class="space-y-6">
            <!-- Back Link -->
            <Link
                href="/bizboost/learning"
                class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-violet-600"
            >
                <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                Back to Courses
            </Link>

            <!-- Course Header -->
            <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 overflow-hidden">
                <div class="md:flex">
                    <div class="md:w-1/3">
                        <div class="aspect-video bg-gray-100">
                            <img
                                v-if="course.thumbnail"
                                :src="course.thumbnail"
                                :alt="course.title"
                                class="w-full h-full object-cover"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <AcademicCapIcon class="h-16 w-16 text-gray-300" aria-hidden="true" />
                            </div>
                        </div>
                    </div>
                    <div class="p-6 md:w-2/3">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-xs font-medium text-violet-600 bg-violet-50 px-2 py-1 rounded">
                                {{ course.category }}
                            </span>
                            <span class="text-xs text-gray-500 capitalize">{{ course.difficulty }}</span>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ course.title }}</h1>
                        <p class="text-gray-600 mb-4">{{ course.description }}</p>
                        
                        <div class="flex items-center gap-6 text-sm text-gray-500 mb-4">
                            <span class="flex items-center gap-1">
                                <ClockIcon class="h-4 w-4" aria-hidden="true" />
                                {{ course.duration_minutes }} min
                            </span>
                            <span class="flex items-center gap-1">
                                <PlayCircleIcon class="h-4 w-4" aria-hidden="true" />
                                {{ course.lessons_count }} lessons
                            </span>
                            <span v-if="course.has_certificate" class="flex items-center gap-1 text-amber-600">
                                <TrophyIcon class="h-4 w-4" aria-hidden="true" />
                                Certificate
                            </span>
                        </div>

                        <!-- Progress Bar -->
                        <div class="mb-4">
                            <div class="flex items-center justify-between text-sm mb-1">
                                <span class="text-gray-600">Progress</span>
                                <span class="font-medium text-gray-900">{{ progressPercent }}%</span>
                            </div>
                            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div
                                    class="h-full bg-violet-600 rounded-full transition-all"
                                    :style="{ width: `${progressPercent}%` }"
                                ></div>
                            </div>
                        </div>

                        <!-- Completed Badge -->
                        <div v-if="isCompleted" class="flex items-center gap-2 text-emerald-600 mb-4">
                            <CheckCircleSolid class="h-5 w-5" aria-hidden="true" />
                            <span class="font-medium">Course Completed!</span>
                        </div>

                        <!-- Start/Continue Button -->
                        <Link
                            v-if="nextLesson"
                            :href="`/bizboost/learning/${course.slug}/${nextLesson.slug}`"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-violet-600 text-white rounded-lg font-medium hover:bg-violet-700 transition-colors"
                        >
                            <PlayCircleIcon class="h-5 w-5" aria-hidden="true" />
                            {{ progressPercent > 0 ? 'Continue Learning' : 'Start Course' }}
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Lessons List -->
            <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="font-semibold text-gray-900">Course Content</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    <Link
                        v-for="(lesson, index) in lessons"
                        :key="lesson.id"
                        :href="`/bizboost/learning/${course.slug}/${lesson.slug}`"
                        class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50 transition-colors"
                    >
                        <div
                            :class="[
                                'flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium',
                                lesson.is_completed
                                    ? 'bg-emerald-100 text-emerald-600'
                                    : 'bg-gray-100 text-gray-600'
                            ]"
                        >
                            <CheckCircleSolid v-if="lesson.is_completed" class="h-5 w-5" aria-hidden="true" />
                            <span v-else>{{ index + 1 }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900">{{ lesson.title }}</p>
                            <p class="text-sm text-gray-500">{{ lesson.duration_minutes }} min</p>
                        </div>
                        <PlayCircleIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                    </Link>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
