<script setup lang="ts">
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    XMarkIcon,
    ArrowRightIcon,
    ArrowLeftIcon,
    CheckIcon,
    SparklesIcon,
    ShoppingBagIcon,
    UsersIcon,
    DocumentTextIcon,
    ChartBarIcon,
} from '@heroicons/vue/24/outline';

interface TourStep {
    id: string;
    title: string;
    description: string;
    target?: string; // CSS selector for element to highlight
    icon: any;
    action?: { label: string; href: string };
    position?: 'top' | 'bottom' | 'left' | 'right';
}

interface Props {
    show?: boolean;
    onComplete?: () => void;
}

const props = withDefaults(defineProps<Props>(), {
    show: false,
});

const emit = defineEmits<{
    complete: [];
    skip: [];
    close: [];
}>();

const STORAGE_KEY = 'bizboost-onboarding-completed';

const isVisible = ref(false);
const currentStep = ref(0);
const highlightRect = ref<DOMRect | null>(null);

const steps: TourStep[] = [
    {
        id: 'welcome',
        title: 'Welcome to BizBoost! 🎉',
        description: 'Your all-in-one marketing toolkit for small businesses. Let me show you around.',
        icon: SparklesIcon,
    },
    {
        id: 'products',
        title: 'Manage Your Products',
        description: 'Add your products with photos, prices, and descriptions. Track inventory and sales.',
        target: '[data-tour="products"]',
        icon: ShoppingBagIcon,
        action: { label: 'Add Product', href: '/bizboost/products/create' },
        position: 'right',
    },
    {
        id: 'customers',
        title: 'Build Your Customer Base',
        description: 'Keep track of your customers, their purchases, and send them targeted messages.',
        target: '[data-tour="customers"]',
        icon: UsersIcon,
        action: { label: 'Add Customer', href: '/bizboost/customers/create' },
        position: 'right',
    },
    {
        id: 'posts',
        title: 'Create Social Content',
        description: 'Design beautiful posts, schedule them, and publish to multiple platforms at once.',
        target: '[data-tour="posts"]',
        icon: DocumentTextIcon,
        action: { label: 'Create Post', href: '/bizboost/posts/create' },
        position: 'right',
    },
    {
        id: 'analytics',
        title: 'Track Your Growth',
        description: 'See how your business is performing with detailed analytics and insights.',
        target: '[data-tour="analytics"]',
        icon: ChartBarIcon,
        action: { label: 'View Analytics', href: '/bizboost/analytics' },
        position: 'right',
    },
    {
        id: 'command',
        title: 'Quick Actions with ⌘K',
        description: 'Press Cmd+K (or Ctrl+K) anytime to quickly navigate, search, or take actions.',
        icon: SparklesIcon,
    },
];

const currentStepData = computed(() => steps[currentStep.value]);
const isFirstStep = computed(() => currentStep.value === 0);
const isLastStep = computed(() => currentStep.value === steps.length - 1);
const progress = computed(() => ((currentStep.value + 1) / steps.length) * 100);

const updateHighlight = async () => {
    await nextTick();
    const target = currentStepData.value.target;
    
    if (target) {
        const element = document.querySelector(target);
        if (element) {
            highlightRect.value = element.getBoundingClientRect();
            element.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else {
            highlightRect.value = null;
        }
    } else {
        highlightRect.value = null;
    }
};

const nextStep = () => {
    if (isLastStep.value) {
        completeTour();
    } else {
        currentStep.value++;
        updateHighlight();
    }
};

const prevStep = () => {
    if (!isFirstStep.value) {
        currentStep.value--;
        updateHighlight();
    }
};

const skipTour = () => {
    localStorage.setItem(STORAGE_KEY, 'skipped');
    isVisible.value = false;
    emit('skip');
    emit('close');
};

const completeTour = () => {
    localStorage.setItem(STORAGE_KEY, 'completed');
    isVisible.value = false;
    emit('complete');
    emit('close');
};

const startTour = () => {
    currentStep.value = 0;
    isVisible.value = true;
    updateHighlight();
};

const shouldShowTour = () => {
    const status = localStorage.getItem(STORAGE_KEY);
    return !status;
};

watch(() => props.show, (newVal) => {
    if (newVal) {
        startTour();
    }
});

watch(currentStep, () => {
    updateHighlight();
});

onMounted(() => {
    if (shouldShowTour()) {
        // Delay to let page render
        setTimeout(() => {
            isVisible.value = true;
            updateHighlight();
        }, 1000);
    }
});

// Expose for manual trigger
defineExpose({ startTour });
</script>

<template>
    <Teleport to="body">
        <!-- Backdrop -->
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div 
                v-if="isVisible" 
                class="fixed inset-0 z-[100] bg-slate-900/70 backdrop-blur-sm"
            >
                <!-- Highlight cutout -->
                <div
                    v-if="highlightRect"
                    class="absolute bg-transparent ring-4 ring-violet-500 ring-offset-4 ring-offset-transparent rounded-xl transition-all duration-300 pointer-events-none"
                    :style="{
                        top: `${highlightRect.top - 8}px`,
                        left: `${highlightRect.left - 8}px`,
                        width: `${highlightRect.width + 16}px`,
                        height: `${highlightRect.height + 16}px`,
                    }"
                ></div>
            </div>
        </Transition>

        <!-- Tour Modal -->
        <Transition
            enter-active-class="transition-all duration-300"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition-all duration-200"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="isVisible"
                class="fixed z-[101] w-full max-w-md bg-white dark:bg-slate-800 rounded-2xl shadow-2xl overflow-hidden"
                :style="highlightRect ? {
                    top: `${highlightRect.bottom + 20}px`,
                    left: `${Math.max(16, Math.min(highlightRect.left, window.innerWidth - 420))}px`,
                } : {
                    top: '50%',
                    left: '50%',
                    transform: 'translate(-50%, -50%)',
                }"
            >
                <!-- Progress bar -->
                <div class="h-1 bg-slate-100 dark:bg-slate-700">
                    <div 
                        class="h-full bg-gradient-to-r from-violet-500 to-purple-500 transition-all duration-300"
                        :style="{ width: `${progress}%` }"
                    ></div>
                </div>

                <!-- Content -->
                <div class="p-6">
                    <!-- Header -->
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-xl bg-violet-100 text-violet-600">
                                <component :is="currentStepData.icon" class="h-6 w-6" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Step {{ currentStep + 1 }} of {{ steps.length }}</p>
                                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ currentStepData.title }}</h3>
                            </div>
                        </div>
                        <button
                            @click="skipTour"
                            class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors"
                            aria-label="Close tour"
                        >
                            <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                        </button>
                    </div>

                    <!-- Description -->
                    <p class="text-slate-600 dark:text-slate-300 mb-6">{{ currentStepData.description }}</p>

                    <!-- Action button -->
                    <button
                        v-if="currentStepData.action"
                        @click="router.visit(currentStepData.action.href); skipTour()"
                        class="w-full mb-4 px-4 py-2.5 rounded-xl bg-violet-50 text-violet-700 font-medium hover:bg-violet-100 transition-colors text-sm"
                    >
                        {{ currentStepData.action.label }} →
                    </button>

                    <!-- Navigation -->
                    <div class="flex items-center justify-between">
                        <button
                            v-if="!isFirstStep"
                            @click="prevStep"
                            class="flex items-center gap-1 px-3 py-2 text-sm text-slate-600 hover:text-slate-900 transition-colors"
                        >
                            <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                            Back
                        </button>
                        <button
                            v-else
                            @click="skipTour"
                            class="px-3 py-2 text-sm text-slate-500 hover:text-slate-700 transition-colors"
                        >
                            Skip tour
                        </button>

                        <button
                            @click="nextStep"
                            class="flex items-center gap-2 px-4 py-2 rounded-xl bg-violet-600 text-white font-medium hover:bg-violet-700 transition-colors text-sm"
                        >
                            {{ isLastStep ? 'Get Started' : 'Next' }}
                            <CheckIcon v-if="isLastStep" class="h-4 w-4" aria-hidden="true" />
                            <ArrowRightIcon v-else class="h-4 w-4" aria-hidden="true" />
                        </button>
                    </div>
                </div>

                <!-- Step indicators -->
                <div class="flex justify-center gap-1.5 pb-4">
                    <button
                        v-for="(step, index) in steps"
                        :key="step.id"
                        @click="currentStep = index; updateHighlight()"
                        :class="[
                            'h-1.5 rounded-full transition-all',
                            index === currentStep 
                                ? 'w-6 bg-violet-500' 
                                : index < currentStep 
                                    ? 'w-1.5 bg-violet-300' 
                                    : 'w-1.5 bg-slate-200'
                        ]"
                        :aria-label="`Go to step ${index + 1}`"
                    ></button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
