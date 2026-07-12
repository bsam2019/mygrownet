<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    CheckCircleIcon, 
    ClockIcon,
    ArrowLeftIcon,
    PlayIcon,
    DocumentTextIcon,
    LinkIcon
} from '@heroicons/vue/24/outline';
import { CheckCircleIcon as CheckCircleSolidIcon } from '@heroicons/vue/24/solid';
import type { Journey, Stage, Task } from '@/types/growstart';

interface Props {
    journey: Journey;
    stage: Stage;
    tasks: Task[];
    progress: number;
    prevStage: Stage | null;
    nextStage: Stage | null;
}

const props = defineProps<Props>();

const completingTask = ref<number | null>(null);

const getStageIcon = (slug: string) => {
    const icons: Record<string, string> = {
        idea: '💡',
        validation: '✅',
        planning: '📋',
        registration: '📝',
        launch: '🚀',
        accounting: '💰',
        marketing: '📢',
        growth: '📈'
    };
    return icons[slug] || '📌';
};

const getTaskStatus = (task: Task) => {
    return task.user_task?.status || 'pending';
};

const completeTask = (taskId: number) => {
    completingTask.value = taskId;
    router.post(route('growstart.tasks.complete', taskId), {}, {
        preserveScroll: true,
        onFinish: () => {
            completingTask.value = null;
        }
    });
};

const startTask = (taskId: number) => {
    router.post(route('growstart.tasks.start', taskId), {}, {
        preserveScroll: true
    });
};

const skipTask = (taskId: number) => {
    router.post(route('growstart.tasks.skip', taskId), {}, {
        preserveScroll: true
    });
};
</script>

<template>
    <Head :title="`GrowStart - ${stage.name}`" />
    
    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Back Link -->
                <Link 
                    :href="route('growstart.stages.index')"
                    class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 mb-6"
                >
                    <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                    Back to Stages
                </Link>

                <!-- Stage Header -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 rounded-xl bg-blue-100 flex items-center justify-center text-3xl">
                            {{ getStageIcon(stage.slug) }}
                        </div>
                        <div class="flex-1">
                            <h1 class="text-2xl font-bold text-gray-900">{{ stage.name }}</h1>
                            <p class="mt-1 text-gray-600">{{ stage.description }}</p>
                            
                            <!-- Progress -->
                            <div class="mt-4">
                                <div class="flex items-center justify-between text-sm mb-2">
                                    <span class="text-gray-500">Stage Progress</span>
                                    <span class="font-bold text-blue-600">{{ progress }}%</span>
                                </div>
                                <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div 
                                        class="h-full bg-blue-600 rounded-full transition-all duration-500"
                                        :style="{ width: `${progress}%` }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stage Meta -->
                    <div class="mt-4 pt-4 border-t border-gray-100 flex items-center gap-6 text-sm text-gray-500">
                        <div class="flex items-center gap-1">
                            <ClockIcon class="h-4 w-4" aria-hidden="true" />
                            <span>{{ stage.estimated_days }} days estimated</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <DocumentTextIcon class="h-4 w-4" aria-hidden="true" />
                            <span>{{ tasks.length }} tasks</span>
                        </div>
                    </div>
                </div>

                <!-- Tasks List -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-5 border-b border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900">Tasks</h2>
                    </div>
                    
                    <div class="divide-y divide-gray-100">
                        <div 
                            v-for="task in tasks" 
                            :key="task.id"
                            class="p-5 hover:bg-gray-50 transition"
                        >
                            <div class="flex items-start gap-4">
                                <!-- Checkbox -->
                                <button
                                    type="button"
                                    @click="getTaskStatus(task) !== 'completed' ? completeTask(task.id) : null"
                                    :disabled="completingTask === task.id"
                                    :class="[
                                        'mt-0.5 w-6 h-6 rounded-full border-2 flex items-center justify-center transition',
                                        getTaskStatus(task) === 'completed' 
                                            ? 'border-emerald-500 bg-emerald-500 cursor-default' 
                                            : 'border-gray-300 hover:border-blue-500 cursor-pointer'
                                    ]"
                                    :aria-label="getTaskStatus(task) === 'completed' ? 'Task completed' : 'Mark task as complete'"
                                >
                                    <CheckCircleSolidIcon 
                                        v-if="getTaskStatus(task) === 'completed'"
                                        class="h-4 w-4 text-white" 
                                        aria-hidden="true" 
                                    />
                                    <span 
                                        v-else-if="completingTask === task.id"
                                        class="w-3 h-3 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"
                                    ></span>
                                </button>

                                <!-- Task Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h3 
                                            :class="[
                                                'font-medium',
                                                getTaskStatus(task) === 'completed' 
                                                    ? 'text-gray-400 line-through' 
                                                    : 'text-gray-900'
                                            ]"
                                        >
                                            {{ task.title }}
                                        </h3>
                                        <span 
                                            v-if="task.is_required"
                                            class="px-1.5 py-0.5 text-xs font-medium bg-red-100 text-red-700 rounded"
                                        >
                                            Required
                                        </span>
                                        <span 
                                            v-if="getTaskStatus(task) === 'in_progress'"
                                            class="px-1.5 py-0.5 text-xs font-medium bg-blue-100 text-blue-700 rounded"
                                        >
                                            In Progress
                                        </span>
                                    </div>
                                    <p 
                                        v-if="task.description"
                                        class="mt-1 text-sm text-gray-500"
                                    >
                                        {{ task.description }}
                                    </p>

                                    <!-- Task Meta & Actions -->
                                    <div class="mt-3 flex items-center gap-4">
                                        <span class="text-xs text-gray-400">
                                            {{ task.estimated_hours }}h estimated
                                        </span>
                                        
                                        <a 
                                            v-if="task.external_link"
                                            :href="task.external_link"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-700"
                                        >
                                            <LinkIcon class="h-3 w-3" aria-hidden="true" />
                                            Resource Link
                                        </a>

                                        <template v-if="getTaskStatus(task) === 'pending'">
                                            <button
                                                type="button"
                                                @click="startTask(task.id)"
                                                class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-700"
                                            >
                                                <PlayIcon class="h-3 w-3" aria-hidden="true" />
                                                Start Task
                                            </button>
                                            <button
                                                v-if="!task.is_required"
                                                type="button"
                                                @click="skipTask(task.id)"
                                                class="text-xs text-gray-400 hover:text-gray-600"
                                            >
                                                Skip
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="tasks.length === 0" class="p-8 text-center text-gray-500">
                            <CheckCircleIcon class="mx-auto h-10 w-10 text-gray-300" aria-hidden="true" />
                            <p class="mt-2">No tasks for this stage yet</p>
                        </div>
                    </div>
                </div>

                <!-- Stage Navigation -->
                <div class="mt-6 flex items-center justify-between">
                    <Link
                        v-if="prevStage"
                        :href="route('growstart.stages.show', prevStage.slug)"
                        class="inline-flex items-center gap-2 px-4 py-2 text-gray-600 hover:text-gray-900 transition"
                    >
                        <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                        {{ prevStage.name }}
                    </Link>
                    <div v-else></div>

                    <Link
                        v-if="nextStage"
                        :href="route('growstart.stages.show', nextStage.slug)"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                    >
                        {{ nextStage.name }}
                        <ArrowLeftIcon class="h-4 w-4 rotate-180" aria-hidden="true" />
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
