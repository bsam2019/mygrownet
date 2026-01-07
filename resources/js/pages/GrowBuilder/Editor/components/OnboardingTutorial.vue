<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import {
    XMarkIcon,
    ArrowRightIcon,
    ArrowLeftIcon,
    CheckIcon,
    Squares2X2Icon,
    DocumentIcon,
    PencilSquareIcon,
    EyeIcon,
    SparklesIcon,
    RocketLaunchIcon,
} from '@heroicons/vue/24/outline';

interface TutorialStep {
    id: string;
    title: string;
    description: string;
    icon: any;
    highlight?: string;
    position: 'center' | 'left' | 'right' | 'bottom';
}

const props = defineProps<{
    show: boolean;
    siteName: string;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'complete'): void;
}>();

const currentStep = ref(0);
const spotlightRect = ref<{ top: number; left: number; width: number; height: number } | null>(null);

const steps: TutorialStep[] = [
    {
        id: 'welcome',
        title: 'Welcome to GrowBuilder! 🎉',
        description: `Let's take a quick tour to help you build "${props.siteName}" in minutes. This will only take 30 seconds.`,
        icon: RocketLaunchIcon,
        position: 'center',
    },
    {
        id: 'add-sections',
        title: 'Add Sections',
        description: 'Click the "Add" tab to browse section blocks. Drag and drop Hero, About, Services, and more to build your page.',
        icon: Squares2X2Icon,
        highlight: '[data-tour="add-tab"]',
        position: 'right',
    },
    {
        id: 'pages',
        title: 'Manage Pages',
        description: 'Use the "Pages" tab to create new pages like About, Services, or Contact. Each page can have its own sections.',
        icon: DocumentIcon,
        highlight: '[data-tour="pages-tab"]',
        position: 'right',
    },
    {
        id: 'edit-sections',
        title: 'Edit Content',
        description: 'Click any section on the canvas to select it. The "Edit" tab will show options to customize text, images, and styles.',
        icon: PencilSquareIcon,
        highlight: '[data-tour="edit-tab"]',
        position: 'right',
    },
    {
        id: 'preview',
        title: 'Preview Your Site',
        description: 'Use the preview buttons to see how your site looks on desktop, tablet, and mobile devices.',
        icon: EyeIcon,
        highlight: '[data-tour="preview-buttons"]',
        position: 'bottom',
    },
    {
        id: 'ai-assistant',
        title: 'AI Assistant',
        description: 'Need help? Click the AI button to generate content, get suggestions, or translate text into local languages.',
        icon: SparklesIcon,
        highlight: '[data-tour="ai-button"]',
        position: 'left',
    },
    {
        id: 'done',
        title: "You're Ready! 🚀",
        description: "That's it! Start by adding a Hero section, then customize it with your business info. Don't forget to Save and Publish when you're done!",
        icon: CheckIcon,
        position: 'center',
    },
];

const currentStepData = computed(() => steps[currentStep.value]);
const isFirstStep = computed(() => currentStep.value === 0);
const isLastStep = computed(() => currentStep.value === steps.length - 1);
const progress = computed(() => ((currentStep.value + 1) / steps.length) * 100);

// Calculate card position based on spotlight
const cardStyle = computed(() => {
    const stepData = currentStepData.value;
    
    if (!spotlightRect.value || stepData.position === 'center') {
        return {
            top: '50%',
            left: '50%',
            transform: 'translate(-50%, -50%)',
        };
    }
    
    const rect = spotlightRect.value;
    const padding = 20;
    const cardWidth = 400;
    
    if (stepData.position === 'right') {
        return {
            top: `${rect.top}px`,
            left: `${rect.left + rect.width + padding}px`,
            transform: 'translateY(0)',
        };
    }
    
    if (stepData.position === 'left') {
        return {
            top: `${rect.top}px`,
            right: `${window.innerWidth - rect.left + padding}px`,
            transform: 'translateY(0)',
        };
    }
    
    if (stepData.position === 'bottom') {
        return {
            top: `${rect.top + rect.height + padding}px`,
            left: `${rect.left + rect.width / 2}px`,
            transform: 'translateX(-50%)',
        };
    }
    
    return {};
});

const nextStep = () => {
    if (currentStep.value < steps.length - 1) {
        currentStep.value++;
    } else {
        complete();
    }
};

const prevStep = () => {
    if (currentStep.value > 0) {
        currentStep.value--;
    }
};

const skip = () => {
    cleanup();
    emit('close');
};

const complete = () => {
    cleanup();
    emit('complete');
};

const cleanup = () => {
    document.querySelectorAll('.tour-highlight').forEach(el => {
        el.classList.remove('tour-highlight');
    });
    spotlightRect.value = null;
};

// Update spotlight when step changes
const updateSpotlight = () => {
    // Remove previous highlights
    document.querySelectorAll('.tour-highlight').forEach(el => {
        el.classList.remove('tour-highlight');
    });
    
    const stepData = steps[currentStep.value];
    
    if (stepData.highlight) {
        const el = document.querySelector(stepData.highlight) as HTMLElement;
        if (el) {
            el.classList.add('tour-highlight');
            
            // Get element position for spotlight
            const rect = el.getBoundingClientRect();
            const padding = 8;
            spotlightRect.value = {
                top: rect.top - padding,
                left: rect.left - padding,
                width: rect.width + padding * 2,
                height: rect.height + padding * 2,
            };
            
            // Scroll into view if needed
            el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else {
            spotlightRect.value = null;
        }
    } else {
        spotlightRect.value = null;
    }
};

watch(currentStep, () => {
    nextTick(updateSpotlight);
}, { immediate: true });

watch(() => props.show, (show) => {
    if (show) {
        nextTick(updateSpotlight);
    } else {
        cleanup();
    }
});

// Keyboard navigation
const handleKeydown = (e: KeyboardEvent) => {
    if (!props.show) return;
    
    if (e.key === 'Escape') {
        skip();
    } else if (e.key === 'ArrowRight' || e.key === 'Enter') {
        nextStep();
    } else if (e.key === 'ArrowLeft') {
        prevStep();
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
    cleanup();
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="show" class="fixed inset-0 z-[9999]">
                <!-- SVG Backdrop with spotlight cutout -->
                <svg class="absolute inset-0 w-full h-full">
                    <defs>
                        <mask id="spotlight-mask">
                            <!-- White = visible (backdrop shows) -->
                            <rect x="0" y="0" width="100%" height="100%" fill="white" />
                            <!-- Black = hidden (spotlight hole) -->
                            <rect 
                                v-if="spotlightRect"
                                :x="spotlightRect.left"
                                :y="spotlightRect.top"
                                :width="spotlightRect.width"
                                :height="spotlightRect.height"
                                rx="12"
                                fill="black"
                            />
                        </mask>
                    </defs>
                    <!-- Dark backdrop with mask -->
                    <rect 
                        x="0" y="0" 
                        width="100%" height="100%" 
                        fill="rgba(0, 0, 0, 0.75)"
                        mask="url(#spotlight-mask)"
                    />
                </svg>
                
                <!-- Spotlight border/glow effect -->
                <div 
                    v-if="spotlightRect"
                    class="absolute pointer-events-none rounded-xl transition-all duration-300"
                    :style="{
                        top: spotlightRect.top + 'px',
                        left: spotlightRect.left + 'px',
                        width: spotlightRect.width + 'px',
                        height: spotlightRect.height + 'px',
                        boxShadow: '0 0 0 3px rgba(59, 130, 246, 0.8), 0 0 20px rgba(59, 130, 246, 0.4)',
                    }"
                >
                    <!-- Pulsing ring -->
                    <div class="absolute inset-0 rounded-xl animate-ping-slow border-2 border-blue-400 opacity-50"></div>
                </div>
                
                <!-- Tutorial Card -->
                <div 
                    class="absolute bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 transition-all duration-300"
                    :style="cardStyle"
                >
                    <!-- Progress Bar -->
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gray-100 rounded-t-2xl overflow-hidden">
                        <div 
                            class="h-full bg-blue-500 transition-all duration-300"
                            :style="{ width: progress + '%' }"
                        ></div>
                    </div>
                    
                    <!-- Close Button -->
                    <button
                        @click="skip"
                        class="absolute top-4 right-4 p-1 text-gray-400 hover:text-gray-600 transition"
                        aria-label="Skip tutorial"
                    >
                        <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                    </button>
                    
                    <!-- Content -->
                    <div class="pt-2">
                        <!-- Icon -->
                        <div class="w-14 h-14 rounded-2xl bg-blue-100 flex items-center justify-center mb-4">
                            <component :is="currentStepData.icon" class="h-7 w-7 text-blue-600" aria-hidden="true" />
                        </div>
                        
                        <!-- Step Counter -->
                        <p class="text-xs text-gray-400 mb-1">Step {{ currentStep + 1 }} of {{ steps.length }}</p>
                        
                        <!-- Title -->
                        <h3 class="text-xl font-bold text-gray-900 mb-2">{{ currentStepData.title }}</h3>
                        
                        <!-- Description -->
                        <p class="text-gray-600 mb-6 leading-relaxed">{{ currentStepData.description }}</p>
                        
                        <!-- Actions -->
                        <div class="flex items-center justify-between">
                            <button
                                v-if="!isFirstStep"
                                @click="prevStep"
                                class="flex items-center gap-1 px-3 py-2 text-gray-600 hover:text-gray-900 transition"
                            >
                                <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                                Back
                            </button>
                            <button
                                v-else
                                @click="skip"
                                class="px-3 py-2 text-gray-400 hover:text-gray-600 transition"
                            >
                                Skip tour
                            </button>
                            
                            <button
                                @click="nextStep"
                                class="flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 transition"
                            >
                                {{ isLastStep ? "Let's Go!" : 'Next' }}
                                <ArrowRightIcon v-if="!isLastStep" class="h-4 w-4" aria-hidden="true" />
                                <CheckIcon v-else class="h-4 w-4" aria-hidden="true" />
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Step Dots -->
                <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex items-center gap-2">
                    <button
                        v-for="(step, index) in steps"
                        :key="step.id"
                        @click="currentStep = index"
                        :class="[
                            'w-2 h-2 rounded-full transition-all',
                            index === currentStep ? 'bg-white w-6' : 'bg-white/40 hover:bg-white/60'
                        ]"
                        :aria-label="`Go to step ${index + 1}`"
                    ></button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style>
/* Highlight class for tour elements */
.tour-highlight {
    position: relative;
    z-index: 10000 !important;
}

/* Slow ping animation for spotlight */
@keyframes ping-slow {
    0% {
        transform: scale(1);
        opacity: 0.5;
    }
    50% {
        transform: scale(1.05);
        opacity: 0.3;
    }
    100% {
        transform: scale(1);
        opacity: 0.5;
    }
}

.animate-ping-slow {
    animation: ping-slow 2s ease-in-out infinite;
}
</style>
