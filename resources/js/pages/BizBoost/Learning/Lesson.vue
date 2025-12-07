<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import {
    ArrowLeftIcon,
    ArrowRightIcon,
    CheckCircleIcon,
    PlayCircleIcon,
    ListBulletIcon,
} from '@heroicons/vue/24/outline';
import { CheckCircleIcon as CheckCircleSolid } from '@heroicons/vue/24/solid';

interface Lesson {
    id: number;
    title: string;
    slug: string;
    content: string | null;
    video_url: string | null;
    duration_minutes: number;
    sort_order: number;
}

interface Course {
    id: number;
    title: string;
    slug: string;
}

interface Props {
    course: Course;
    lesson: Lesson;
    lessons: Lesson[];
    completedLessons: number[];
    nextLesson: Lesson | null;
    prevLesson: Lesson | null;
}

const props = defineProps<Props>();
const showSidebar = ref(false);
const completing = ref(false);

const isCompleted = computed(() => props.completedLessons.includes(props.lesson.id));

const completeLesson = () => {
    if (completing.value) return;
    completing.value = true;
    router.post(`/bizboost/learning/${props.course.slug}/${props.lesson.slug}/complete`, {}, {
        preserveScroll: true,
        onFinish: () => {
            completing.value = false;
        },
    });
};

const goToNext = () => {
    if (props.nextLesson) {
        router.visit(`/bizboost/learning/${props.course.slug}/${props.nextLesson.slug}`);
    } else {
        router.visit(`/bizboost/learning/${props.course.slug}`);
    }
};
</script>

<template>
    <Head :title="`${lesson.title} - ${course.title}`" />
    <BizBoostLayout :title="course.title">
        <div class="flex gap-6">
            <!-- Main Content -->
            <div class="flex-1 space-y-6">
                <!-- Navigation -->
                <div class="flex items-center justify-between">
                    <Link
                        :href="`/bizboost/learning/${course.slug}`"
                        class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-violet-600"
                    >
                        <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                        Back to Course
                    </Link>
                    <button
                        @click="showSidebar = !showSidebar"
                        class="lg:hidden inline-flex items-center gap-2 text-sm text-gray-600 hover:text-violet-600"
                        aria-label="Toggle lesson list"
                    >
                        <ListBulletIcon class="h-5 w-5" aria-hidden="true" />
                        Lessons
                    </button>
                </div>

                <!-- Lesson Content -->
                <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 overflow-hidden">
                    <!-- Video Player -->
                    <div v-if="lesson.video_url" class="aspect-video bg-black">
                        <iframe
                            :src="lesson.video_url"
                            class="w-full h-full"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                        ></iframe>
                    </div>

                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h1 class="text-xl font-bold text-gray-900">{{ lesson.title }}</h1>
                                <p class="text-sm text-gray-500 mt-1">{{ lesson.duration_minutes }} min</p>
                            </div>
                            <div v-if="isCompleted" class="flex items-center gap-1 text-emerald-600">
                                <CheckCircleSolid class="h-5 w-5" aria-hidden="true" />
                                <span class="text-sm font-medium">Completed</span>
                            </div>
                        </div>

                        <!-- Lesson Content -->
                        <div
                            v-if="lesson.content"
                            class="prose prose-violet max-w-none"
                            v-html="lesson.content"
                        ></div>

                        <!-- Complete & Navigate -->
                        <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-200">
                            <Link
                                v-if="prevLesson"
                                :href="`/bizboost/learning/${course.slug}/${prevLesson.slug}`"
                                class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-violet-600"
                            >
                                <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                                Previous
                            </Link>
                            <div v-else></div>

                            <div class="flex items-center gap-3">
                                <button
                                    v-if="!isCompleted"
                                    @click="completeLesson"
                                    :disabled="completing"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 disabled:opacity-50 transition-colors"
                                >
                                    <CheckCircleIcon class="h-4 w-4" aria-hidden="true" />
                                    {{ completing ? 'Saving...' : 'Mark Complete' }}
                                </button>

                                <button
                                    @click="goToNext"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-violet-600 text-white rounded-lg text-sm font-medium hover:bg-violet-700 transition-colors"
                                >
                                    {{ nextLesson ? 'Next Lesson' : 'Finish Course' }}
                                    <ArrowRightIcon class="h-4 w-4" aria-hidden="true" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar - Lesson List -->
            <div
                :class="[
                    'w-80 flex-shrink-0',
                    showSidebar ? 'fixed inset-0 z-50 bg-black/50 lg:relative lg:bg-transparent' : 'hidden lg:block'
                ]"
                @click.self="showSidebar = false"
            >
                <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 h-fit max-h-[calc(100vh-8rem)] overflow-y-auto">
                    <div class="px-4 py-3 border-b border-gray-200 sticky top-0 bg-white">
                        <h3 class="font-semibold text-gray-900">Course Content</h3>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <Link
                            v-for="(l, index) in lessons"
                            :key="l.id"
                            :href="`/bizboost/learning/${course.slug}/${l.slug}`"
                            :class="[
                                'flex items-center gap-3 px-4 py-3 text-sm transition-colors',
                                l.id === lesson.id
                                    ? 'bg-violet-50 text-violet-700'
                                    : 'hover:bg-gray-50 text-gray-700'
                            ]"
                        >
                            <div
                                :class="[
                                    'flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-xs font-medium',
                                    completedLessons.includes(l.id)
                                        ? 'bg-emerald-100 text-emerald-600'
                                        : l.id === lesson.id
                                            ? 'bg-violet-600 text-white'
                                            : 'bg-gray-100 text-gray-600'
                                ]"
                            >
                                <CheckCircleSolid v-if="completedLessons.includes(l.id)" class="h-4 w-4" aria-hidden="true" />
                                <span v-else>{{ index + 1 }}</span>
                            </div>
                            <span class="truncate">{{ l.title }}</span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
