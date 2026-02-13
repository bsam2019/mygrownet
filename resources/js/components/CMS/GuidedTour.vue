<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { XMarkIcon, ArrowRightIcon, CheckIcon } from '@heroicons/vue/24/outline';

interface TourStep {
    id: string;
    name: string;
    description: string;
    target?: string;
    position?: 'top' | 'bottom' | 'left' | 'right';
}

interface Props {
    steps: TourStep[];
    autoStart?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    autoStart: false,
});

const emit = defineEmits<{
    complete: [];
    skip: [];
    stepComplete: [stepId: string];
}>();

const isActive = ref(false);
const currentStepIndex = ref(0);
const completedSteps = ref<Set<string>>(new Set());

const currentStep = computed(() => props.steps[currentStepIndex.value]);
const isLastStep = computed(() => currentStepIndex.value === props.steps.length - 1);
const progress = computed(() => ((currentStepIndex.value + 1) / props.steps.length) * 100);

const tooltipPosition = ref({ top: 0, left: 0 });
const tooltipStyle = ref<'top' | 'bottom' | 'left' | 'right'>('bottom');

onMounted(() => {
    if (props.autoStart) {
        startTour();
    }
});

watch(currentStepIndex, () => {
    if (isActive.value && currentStep.value.target) {
        positionTooltip();
    }
});

const startTour = () => {
    isActive.value = true;
    currentStepIndex.value = 0;
    if (currentStep.value.target) {
        positionTooltip();
    }
};

const positionTooltip = () => {
    const target = document.querySelector(currentStep.value.target!);
    if (!target) return;

    const rect = target.getBoundingClientRect();
    const tooltipWidth = 400;
    const tooltipHeight = 200;
    const padding = 16;

    // Determine best position
    const position = currentStep.value.position || 'bottom';
    tooltipStyle.value = position;

    switch (position) {
        case 'top':
            tooltipPosition.value = {
                top: rect.top - tooltipHeight - padding,
                left: rect.left + rect.width / 2 - tooltipWidth / 2,
            };
            break;
        case 'bottom':
            tooltipPosition.value = {
                top: rect.bottom + padding,
                left: rect.left + rect.width / 2 - tooltipWidth / 2,
            };
            break;
        case 'left':
            tooltipPosition.value = {
                top: rect.top + rect.height / 2 - tooltipHeight / 2,
                left: rect.left - tooltipWidth - padding,
            };
            break;
        case 'right':
            tooltipPosition.value = {
                top: rect.top + rect.height / 2 - tooltipHeight / 2,
                left: rect.right + padding,
            };
            break;
    }

    // Highlight target element
    target.classList.add('tour-highlight');
    target.scrollIntoView({ behavior: 'smooth', block: 'center' });
};

const nextStep = async () => {
    // Mark current step as completed
    completedSteps.value.add(currentStep.value.id);
    emit('stepComplete', currentStep.value.id);

    // Send to backend
    try {
        await fetch(route('cms.onboarding.tour.complete-step'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({ step_id: currentStep.value.id }),
        });
    } catch (error) {
        console.error('Failed to save tour progress:', error);
    }

    // Remove highlight from current target
    if (currentStep.value.target) {
        const target = document.querySelector(currentStep.value.target);
        target?.classList.remove('tour-highlight');
    }

    if (isLastStep.value) {
        completeTour();
    } else {
        currentStepIndex.value++;
        if (currentStep.value.target) {
            positionTooltip();
        }
    }
};

const previousStep = () => {
    if (currentStepIndex.value > 0) {
        // Remove highlight from current target
        if (currentStep.value.target) {
            const target = document.querySelector(currentStep.value.target);
            target?.classList.remove('tour-highlight');
        }

        currentStepIndex.value--;
        if (currentStep.value.target) {
            positionTooltip();
        }
    }
};

const skipTour = async () => {
    // Remove highlight
    if (currentStep.value.target) {
        const target = document.querySelector(currentStep.value.target);
        target?.classList.remove('tour-highlight');
    }

    isActive.value = false;

    // Send to backend
    try {
        await fetch(route('cms.onboarding.tour.skip'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });
    } catch (error) {
        console.error('Failed to skip tour:', error);
    }

    emit('skip');
};

const completeTour = () => {
    // Remove highlight
    if (currentStep.value.target) {
        const target = document.querySelector(currentStep.value.target);
        target?.classList.remove('tour-highlight');
    }

    isActive.value = false;
    emit('complete');
};

defineExpose({
    startTour,
    skipTour,
});
</script>

<template>
    <!-- Overlay -->
    <Teleport to="body">
        <div v-if="isActive" class="fixed inset-0 z-50">
            <!-- Semi-transparent backdrop -->
            <div class="absolute inset-0 bg-black/50"></div>

            <!-- Tooltip -->
            <div
                v-if="currentStep"
                class="absolute z-50 w-[400px] bg-white rounded-xl shadow-2xl p-6"
                :style="{
                    top: `${tooltipPosition.top}px`,
                    left: `${tooltipPosition.left}px`,
                }"
            >
                <!-- Close button -->
                <button
                    @click="skipTour"
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600"
                    aria-label="Close tour"
                >
                    <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                </button>

                <!-- Progress bar -->
                <div class="mb-4">
                    <div class="h-1 bg-gray-200 rounded-full overflow-hidden">
                        <div
                            class="h-full bg-blue-600 transition-all duration-300"
                            :style="{ width: `${progress}%` }"
                        ></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        Step {{ currentStepIndex + 1 }} of {{ steps.length }}
                    </p>
                </div>

                <!-- Content -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ currentStep.name }}</h3>
                    <p class="text-sm text-gray-600">{{ currentStep.description }}</p>
                </div>

                <!-- Navigation -->
                <div class="flex items-center justify-between">
                    <button
                        v-if="currentStepIndex > 0"
                        @click="previousStep"
                        class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900"
                    >
                        Previous
                    </button>
                    <div v-else></div>

                    <div class="flex items-center gap-2">
                        <button
                            @click="skipTour"
                            class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900"
                        >
                            Skip Tour
                        </button>
                        <button
                            @click="nextStep"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700"
                        >
                            {{ isLastStep ? 'Finish' : 'Next' }}
                            <component
                                :is="isLastStep ? CheckIcon : ArrowRightIcon"
                                class="h-4 w-4"
                                aria-hidden="true"
                            />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
:global(.tour-highlight) {
    position: relative;
    z-index: 51;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.5);
    border-radius: 0.5rem;
}
</style>
