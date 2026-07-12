<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    RocketLaunchIcon, 
    CheckCircleIcon, 
    ClockIcon,
    ChartBarIcon,
    DocumentTextIcon,
    TrophyIcon,
    ArrowRightIcon,
    PlayIcon
} from '@heroicons/vue/24/outline';
import type { Journey, JourneyProgress, Stage, Task, Badge, Timeline, WeeklyGoal } from '@/types/growstart';

interface Props {
    journey: Journey | null;
    progress: JourneyProgress | null;
    timeline: Timeline | null;
    nextTasks: Task[];
    stages: Stage[];
    recentBadges: Badge[];
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

const getStatusColor = (status: string) => {
    switch (status) {
        case 'completed': return 'text-emerald-600 bg-emerald-50';
        case 'in_progress': return 'text-blue-600 bg-blue-50';
        case 'pending': return 'text-gray-500 bg-gray-50';
        default: return 'text-gray-400 bg-gray-50';
    }
};
</script>

<template>
    <Head title="GrowStart - Dashboard" />
    
    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- No Journey State -->
                <div v-if="!journey" class="text-center py-16">
                    <RocketLaunchIcon class="mx-auto h-16 w-16 text-blue-500" aria-hidden="true" />
                    <h2 class="mt-4 text-2xl font-bold text-gray-900">Start Your Business Journey</h2>
                    <p class="mt-2 text-gray-600 max-w-md mx-auto">
                        GrowStart guides you through every step of launching your business, from idea to growth.
                    </p>
                    <Link
                        :href="route('growstart.onboarding')"
                        class="mt-6 inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                    >
                        <PlayIcon class="h-5 w-5" aria-hidden="true" />
                        Begin Your Journey
                    </Link>
                </div>

                <!-- Active Journey Dashboard -->
                <div v-else class="space-y-6">
                    <!-- Header -->
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ journey.business_name }}</h1>
                            <p class="text-gray-600">Your startup journey dashboard</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span 
                                :class="[
                                    'px-3 py-1 rounded-full text-sm font-medium',
                                    journey.status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600'
                                ]"
                            >
                                {{ journey.status === 'active' ? 'Active' : journey.status }}
                            </span>
                        </div>
                    </div>

                    <!-- Progress Overview -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-blue-50 rounded-lg">
                                    <ChartBarIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Overall Progress</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ progress?.overall || 0 }}%</p>
                                </div>
                            </div>
                            <div class="mt-3 h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div 
                                    class="h-full bg-blue-600 rounded-full transition-all duration-500"
                                    :style="{ width: `${progress?.overall || 0}%` }"
                                ></div>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-emerald-50 rounded-lg">
                                    <CheckCircleIcon class="h-6 w-6 text-emerald-600" aria-hidden="true" />
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Tasks Completed</p>
                                    <p class="text-2xl font-bold text-gray-900">
                                        {{ progress?.tasks_completed || 0 }}/{{ progress?.total_tasks || 0 }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-amber-50 rounded-lg">
                                    <ClockIcon class="h-6 w-6 text-amber-600" aria-hidden="true" />
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Days Active</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ journey.days_active || 0 }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-purple-50 rounded-lg">
                                    <TrophyIcon class="h-6 w-6 text-purple-600" aria-hidden="true" />
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Badges Earned</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ recentBadges.length }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Content Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Stages Progress -->
                        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100">
                            <div class="p-5 border-b border-gray-100">
                                <h2 class="text-lg font-semibold text-gray-900">Journey Stages</h2>
                            </div>
                            <div class="p-5">
                                <div class="space-y-3">
                                    <Link
                                        v-for="stage in stages"
                                        :key="stage.id"
                                        :href="route('growstart.stages.show', stage.slug)"
                                        class="flex items-center gap-4 p-4 rounded-lg hover:bg-gray-50 transition group"
                                    >
                                        <span class="text-2xl">{{ getStageIcon(stage.slug) }}</span>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between">
                                                <h3 class="font-medium text-gray-900">{{ stage.name }}</h3>
                                                <span class="text-sm text-gray-500">
                                                    {{ progress?.stage_progress?.find(s => s.stage_id === stage.id)?.percentage || 0 }}%
                                                </span>
                                            </div>
                                            <div class="mt-2 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                                <div 
                                                    class="h-full bg-blue-500 rounded-full"
                                                    :style="{ width: `${progress?.stage_progress?.find(s => s.stage_id === stage.id)?.percentage || 0}%` }"
                                                ></div>
                                            </div>
                                        </div>
                                        <ArrowRightIcon class="h-5 w-5 text-gray-400 group-hover:text-blue-600 transition" aria-hidden="true" />
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- Next Tasks -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                            <div class="p-5 border-b border-gray-100">
                                <h2 class="text-lg font-semibold text-gray-900">Next Tasks</h2>
                            </div>
                            <div class="p-5">
                                <div v-if="nextTasks.length === 0" class="text-center py-8 text-gray-500">
                                    <CheckCircleIcon class="mx-auto h-10 w-10 text-emerald-400" aria-hidden="true" />
                                    <p class="mt-2">All caught up!</p>
                                </div>
                                <div v-else class="space-y-3">
                                    <Link
                                        v-for="task in nextTasks.slice(0, 5)"
                                        :key="task.id"
                                        :href="route('growstart.tasks.show', task.id)"
                                        class="block p-3 rounded-lg hover:bg-gray-50 transition"
                                    >
                                        <div class="flex items-start gap-3">
                                            <div 
                                                :class="[
                                                    'mt-0.5 w-5 h-5 rounded-full border-2 flex items-center justify-center',
                                                    task.user_task?.status === 'completed' 
                                                        ? 'border-emerald-500 bg-emerald-500' 
                                                        : 'border-gray-300'
                                                ]"
                                            >
                                                <CheckCircleIcon 
                                                    v-if="task.user_task?.status === 'completed'"
                                                    class="h-3 w-3 text-white" 
                                                    aria-hidden="true" 
                                                />
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate">{{ task.title }}</p>
                                                <p class="text-xs text-gray-500">{{ task.estimated_hours }}h estimated</p>
                                            </div>
                                        </div>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <Link
                            :href="route('growstart.templates.index')"
                            class="flex items-center gap-3 p-4 bg-white rounded-xl shadow-sm border border-gray-100 hover:border-blue-200 transition"
                        >
                            <DocumentTextIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
                            <span class="font-medium text-gray-900">Templates</span>
                        </Link>
                        <Link
                            :href="route('growstart.providers.index')"
                            class="flex items-center gap-3 p-4 bg-white rounded-xl shadow-sm border border-gray-100 hover:border-blue-200 transition"
                        >
                            <span class="text-xl">🤝</span>
                            <span class="font-medium text-gray-900">Providers</span>
                        </Link>
                        <Link
                            :href="route('growstart.badges.index')"
                            class="flex items-center gap-3 p-4 bg-white rounded-xl shadow-sm border border-gray-100 hover:border-blue-200 transition"
                        >
                            <TrophyIcon class="h-6 w-6 text-purple-600" aria-hidden="true" />
                            <span class="font-medium text-gray-900">Badges</span>
                        </Link>
                        <Link
                            :href="route('growstart.journey.show')"
                            class="flex items-center gap-3 p-4 bg-white rounded-xl shadow-sm border border-gray-100 hover:border-blue-200 transition"
                        >
                            <RocketLaunchIcon class="h-6 w-6 text-emerald-600" aria-hidden="true" />
                            <span class="font-medium text-gray-900">My Journey</span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
