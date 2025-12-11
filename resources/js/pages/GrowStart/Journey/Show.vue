<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    RocketLaunchIcon,
    CalendarIcon,
    MapPinIcon,
    PencilIcon,
    PauseIcon,
    PlayIcon,
    ChartBarIcon,
    ClockIcon
} from '@heroicons/vue/24/outline';
import type { Journey, JourneyProgress, Industry, Country, Stage } from '@/types/growstart';

interface Props {
    journey: Journey;
    progress: JourneyProgress;
    industry: Industry;
    country: Country;
    stages: Stage[];
}

const props = defineProps<Props>();

const isEditing = ref(false);
const form = useForm({
    business_name: props.journey.business_name,
    business_description: props.journey.business_description || '',
    target_launch_date: props.journey.target_launch_date || '',
    province: props.journey.province || '',
    city: props.journey.city || ''
});

const saveChanges = () => {
    form.put(route('growstart.journey.update'), {
        onSuccess: () => {
            isEditing.value = false;
        }
    });
};

const togglePause = () => {
    if (props.journey.status === 'paused') {
        router.post(route('growstart.journey.resume'));
    } else {
        router.post(route('growstart.journey.pause'));
    }
};

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
</script>

<template>
    <Head title="GrowStart - My Journey" />
    
    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">My Journey</h1>
                        <p class="text-gray-600">Manage your startup journey details</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            @click="togglePause"
                            :class="[
                                'inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition',
                                journey.status === 'paused'
                                    ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200'
                                    : 'bg-amber-100 text-amber-700 hover:bg-amber-200'
                            ]"
                        >
                            <PlayIcon v-if="journey.status === 'paused'" class="h-4 w-4" aria-hidden="true" />
                            <PauseIcon v-else class="h-4 w-4" aria-hidden="true" />
                            {{ journey.status === 'paused' ? 'Resume' : 'Pause' }}
                        </button>
                    </div>
                </div>

                <!-- Journey Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                    <div class="p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start gap-4">
                                <div class="w-14 h-14 rounded-xl bg-blue-100 flex items-center justify-center">
                                    <RocketLaunchIcon class="h-7 w-7 text-blue-600" aria-hidden="true" />
                                </div>
                                <div>
                                    <template v-if="!isEditing">
                                        <h2 class="text-xl font-bold text-gray-900">{{ journey.business_name }}</h2>
                                        <p class="text-gray-500">{{ industry.name }} • {{ country.name }}</p>
                                    </template>
                                    <template v-else>
                                        <input
                                            v-model="form.business_name"
                                            type="text"
                                            class="text-xl font-bold border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                        />
                                    </template>
                                </div>
                            </div>
                            <button
                                type="button"
                                @click="isEditing = !isEditing"
                                class="p-2 text-gray-400 hover:text-gray-600 transition"
                                :aria-label="isEditing ? 'Cancel editing' : 'Edit journey'"
                            >
                                <PencilIcon class="h-5 w-5" aria-hidden="true" />
                            </button>
                        </div>

                        <!-- Description -->
                        <div class="mt-4">
                            <template v-if="!isEditing">
                                <p v-if="journey.business_description" class="text-gray-600">
                                    {{ journey.business_description }}
                                </p>
                                <p v-else class="text-gray-400 italic">No description added</p>
                            </template>
                            <textarea
                                v-else
                                v-model="form.business_description"
                                rows="3"
                                placeholder="Describe your business..."
                                class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                            ></textarea>
                        </div>

                        <!-- Details Grid -->
                        <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center gap-2 text-gray-500 text-sm">
                                    <CalendarIcon class="h-4 w-4" aria-hidden="true" />
                                    Started
                                </div>
                                <p class="mt-1 font-medium text-gray-900">
                                    {{ new Date(journey.started_at).toLocaleDateString() }}
                                </p>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center gap-2 text-gray-500 text-sm">
                                    <ClockIcon class="h-4 w-4" aria-hidden="true" />
                                    Days Active
                                </div>
                                <p class="mt-1 font-medium text-gray-900">{{ journey.days_active }}</p>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center gap-2 text-gray-500 text-sm">
                                    <CalendarIcon class="h-4 w-4" aria-hidden="true" />
                                    Target Launch
                                </div>
                                <template v-if="!isEditing">
                                    <p class="mt-1 font-medium text-gray-900">
                                        {{ journey.target_launch_date 
                                            ? new Date(journey.target_launch_date).toLocaleDateString() 
                                            : 'Not set' }}
                                    </p>
                                </template>
                                <input
                                    v-else
                                    v-model="form.target_launch_date"
                                    type="date"
                                    class="mt-1 w-full text-sm border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center gap-2 text-gray-500 text-sm">
                                    <MapPinIcon class="h-4 w-4" aria-hidden="true" />
                                    Location
                                </div>
                                <template v-if="!isEditing">
                                    <p class="mt-1 font-medium text-gray-900">
                                        {{ journey.city || journey.province || 'Not set' }}
                                    </p>
                                </template>
                                <input
                                    v-else
                                    v-model="form.city"
                                    type="text"
                                    placeholder="City"
                                    class="mt-1 w-full text-sm border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>
                        </div>

                        <!-- Edit Actions -->
                        <div v-if="isEditing" class="mt-4 flex items-center gap-2 justify-end">
                            <button
                                type="button"
                                @click="isEditing = false"
                                class="px-4 py-2 text-gray-600 hover:text-gray-900 transition"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                @click="saveChanges"
                                :disabled="form.processing"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition disabled:opacity-50"
                            >
                                Save Changes
                            </button>
                        </div>
                    </div>

                    <!-- Progress Section -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">Overall Progress</span>
                            <span class="text-sm font-bold text-blue-600">{{ progress.overall }}%</span>
                        </div>
                        <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div 
                                class="h-full bg-blue-600 rounded-full transition-all duration-500"
                                :style="{ width: `${progress.overall}%` }"
                            ></div>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">
                            {{ progress.tasks_completed }} of {{ progress.total_tasks }} tasks completed
                        </p>
                    </div>
                </div>

                <!-- Current Stage -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Current Stage</h3>
                    <div class="space-y-3">
                        <template v-for="stage in stages" :key="stage.id">
                            <Link
                                :href="route('growstart.stages.show', stage.slug)"
                                :class="[
                                    'flex items-center gap-4 p-4 rounded-lg transition',
                                    journey.current_stage_id === stage.id 
                                        ? 'bg-blue-50 border-2 border-blue-200' 
                                        : 'hover:bg-gray-50'
                                ]"
                            >
                                <span class="text-2xl">{{ getStageIcon(stage.slug) }}</span>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-medium text-gray-900">{{ stage.name }}</h4>
                                        <span 
                                            v-if="journey.current_stage_id === stage.id"
                                            class="px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-700 rounded-full"
                                        >
                                            Current
                                        </span>
                                    </div>
                                    <div class="mt-1 h-1 bg-gray-100 rounded-full overflow-hidden">
                                        <div 
                                            class="h-full bg-blue-500 rounded-full"
                                            :style="{ width: `${progress.stage_progress?.find(s => s.stage_id === stage.id)?.percentage || 0}%` }"
                                        ></div>
                                    </div>
                                </div>
                                <span class="text-sm text-gray-500">
                                    {{ progress.stage_progress?.find(s => s.stage_id === stage.id)?.percentage || 0 }}%
                                </span>
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
