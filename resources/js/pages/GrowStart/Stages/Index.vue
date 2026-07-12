<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    CheckCircleIcon, 
    LockClosedIcon,
    ArrowRightIcon 
} from '@heroicons/vue/24/outline';
import type { Journey, Stage, JourneyProgress } from '@/types/growstart';

interface Props {
    journey: Journey;
    stages: Stage[];
    progress: JourneyProgress;
}

const props = defineProps<Props>();

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

const getStageProgress = (stageId: number) => {
    return props.progress.stage_progress?.find(s => s.stage_id === stageId)?.percentage || 0;
};

const isStageComplete = (stageId: number) => {
    return getStageProgress(stageId) === 100;
};

const isCurrentStage = (stageId: number) => {
    return props.journey.current_stage_id === stageId;
};
</script>

<template>
    <Head title="GrowStart - Journey Stages" />
    
    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-gray-900">Your Journey Stages</h1>
                    <p class="text-gray-600">Complete each stage to launch your business</p>
                </div>

                <!-- Overall Progress -->
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">Overall Progress</span>
                        <span class="text-sm font-bold text-blue-600">{{ progress.overall }}%</span>
                    </div>
                    <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                        <div 
                            class="h-full bg-gradient-to-r from-blue-500 to-blue-600 rounded-full transition-all duration-500"
                            :style="{ width: `${progress.overall}%` }"
                        ></div>
                    </div>
                    <p class="mt-2 text-sm text-gray-500">
                        {{ progress.tasks_completed }} of {{ progress.total_tasks }} tasks completed
                    </p>
                </div>

                <!-- Stages List -->
                <div class="space-y-4">
                    <Link
                        v-for="(stage, index) in stages"
                        :key="stage.id"
                        :href="route('growstart.stages.show', stage.slug)"
                        class="block bg-white rounded-xl shadow-sm border border-gray-100 hover:border-blue-200 transition overflow-hidden"
                    >
                        <div class="p-5">
                            <div class="flex items-start gap-4">
                                <!-- Stage Number/Icon -->
                                <div 
                                    :class="[
                                        'w-12 h-12 rounded-xl flex items-center justify-center text-2xl',
                                        isStageComplete(stage.id) ? 'bg-emerald-100' :
                                        isCurrentStage(stage.id) ? 'bg-blue-100' : 'bg-gray-100'
                                    ]"
                                >
                                    <CheckCircleIcon 
                                        v-if="isStageComplete(stage.id)" 
                                        class="h-6 w-6 text-emerald-600" 
                                        aria-hidden="true" 
                                    />
                                    <span v-else>{{ getStageIcon(stage.slug) }}</span>
                                </div>

                                <!-- Stage Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-semibold text-gray-900">
                                            Stage {{ index + 1 }}: {{ stage.name }}
                                        </h3>
                                        <span 
                                            v-if="isCurrentStage(stage.id)"
                                            class="px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-700 rounded-full"
                                        >
                                            Current
                                        </span>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500 line-clamp-2">{{ stage.description }}</p>
                                    
                                    <!-- Progress Bar -->
                                    <div class="mt-3">
                                        <div class="flex items-center justify-between text-xs mb-1">
                                            <span class="text-gray-500">Progress</span>
                                            <span class="font-medium text-gray-700">{{ getStageProgress(stage.id) }}%</span>
                                        </div>
                                        <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                            <div 
                                                :class="[
                                                    'h-full rounded-full transition-all',
                                                    isStageComplete(stage.id) ? 'bg-emerald-500' : 'bg-blue-500'
                                                ]"
                                                :style="{ width: `${getStageProgress(stage.id)}%` }"
                                            ></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Arrow -->
                                <ArrowRightIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                            </div>
                        </div>

                        <!-- Estimated Time -->
                        <div class="px-5 py-3 bg-gray-50 border-t border-gray-100">
                            <span class="text-xs text-gray-500">
                                Estimated: {{ stage.estimated_days }} days
                            </span>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
