<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { usePage } from '@inertiajs/vue3';
import {
    XMarkIcon,
    ArrowRightIcon,
    ArrowLeftIcon,
    CheckCircleIcon,
    HomeIcon,
    BanknotesIcon,
    DocumentTextIcon,
    ChartBarIcon,
    UsersIcon,
    ClipboardDocumentListIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps<{
    autoStart?: boolean;
}>();

const emit = defineEmits<{
    (e: 'complete'): void;
    (e: 'skip'): void;
}>();

const page = usePage();
const isVisible = ref(false);
const currentStep = ref(0);
const tourInstanceId = ref<string | null>(null);

// Draggable state
const isDragging = ref(false);
const dragOffset = ref({ x: 0, y: 0 });
const cardPosition = ref({ x: 0, y: 0 });
const hasBeenDragged = ref(false);
const tourCardRef = ref<HTMLElement | null>(null);

const TOUR_STORAGE_KEY = 'growfinance-tour-completed';
const TOUR_INSTANCE_KEY = 'growfinance-tour-instance';

// Check if tour was already completed
const isTourCompleted = (): boolean => {
    try {
        return localStorage.getItem(TOUR_STORAGE_KEY) === 'true';
    } catch {
        return false;
    }
};

// Prevent multiple tour instances from running
const acquireTourLock = (): boolean => {
    try {
        const existingInstance = sessionStorage.getItem(TOUR_INSTANCE_KEY);
        if (existingInstance) {
            return false; // Another instance is running
        }
        const instanceId = `tour-${Date.now()}`;
        sessionStorage.setItem(TOUR_INSTANCE_KEY, instanceId);
        tourInstanceId.value = instanceId;
        return true;
    } catch {
        return true; // If sessionStorage fails, allow the tour
    }
};

const releaseTourLock = () => {
    try {
        if (tourInstanceId.value) {
            const currentInstance = sessionStorage.getItem(TOUR_INSTANCE_KEY);
            if (currentInstance === tourInstanceId.value) {
                sessionStorage.removeItem(TOUR_INSTANCE_KEY);
            }
        }
    } catch {
        // Ignore errors
    }
};

const steps = [
    {
        id: 'welcome',
        title: 'Welcome to GrowFinance! 🎉',
        description: 'Your complete business finance management solution. Let me show you around in just a few steps.',
        icon: HomeIcon,
        position: 'center',
        highlight: null,
    },
    {
        id: 'dashboard',
        title: 'Your Dashboard',
        description: 'Get a quick overview of your business finances - income, expenses, and cash flow at a glance.',
        icon: HomeIcon,
        position: 'right',
        highlight: 'a[data-tour="dashboard"]',
    },
    {
        id: 'sales',
        title: 'Record Sales',
        description: 'Quickly record sales and income. Track every kwacha coming into your business.',
        icon: BanknotesIcon,
        position: 'right',
        highlight: 'a[data-tour="sales"]',
    },
    {
        id: 'expenses',
        title: 'Track Expenses',
        description: 'Keep track of all your business expenses. Categorize and monitor where your money goes.',
        icon: ClipboardDocumentListIcon,
        position: 'right',
        highlight: 'a[data-tour="expenses"]',
    },
    {
        id: 'invoices',
        title: 'Create Invoices',
        description: 'Send professional invoices to your customers. Track payments and follow up on overdue invoices.',
        icon: DocumentTextIcon,
        position: 'right',
        highlight: 'a[data-tour="invoices"]',
    },
    {
        id: 'reports',
        title: 'Financial Reports',
        description: 'Generate profit & loss statements, balance sheets, and cash flow reports to understand your business health.',
        icon: ChartBarIcon,
        position: 'right',
        highlight: 'a[data-tour="reports"]',
    },
    {
        id: 'customers',
        title: 'Manage Customers',
        description: 'Keep track of your customers, their contact info, and transaction history all in one place.',
        icon: UsersIcon,
        position: 'right',
        highlight: 'a[data-tour="customers"]',
    },
    {
        id: 'complete',
        title: "You're All Set! ✨",
        description: "You're ready to start managing your finances like a pro. Need help? Click the support icon anytime.",
        icon: CheckCircleIcon,
        position: 'center',
        highlight: null,
    },
];

const currentStepData = computed(() => steps[currentStep.value]);
const isFirstStep = computed(() => currentStep.value === 0);
const isLastStep = computed(() => currentStep.value === steps.length - 1);
const progress = computed(() => ((currentStep.value + 1) / steps.length) * 100);

const highlightedElement = ref<Element | null>(null);
const tooltipPosition = ref({ top: 0, left: 0 });

const startTour = () => {
    if (isTourCompleted()) {
        return;
    }
    if (!acquireTourLock()) {
        return; // Another tour instance is already running
    }
    isVisible.value = true;
    currentStep.value = 0;
    hasBeenDragged.value = false;
    cardPosition.value = { x: 0, y: 0 };
    nextTick(() => highlightElement());
};

// Draggable functionality
const startDrag = (e: MouseEvent | TouchEvent) => {
    if (!tourCardRef.value) return;
    
    isDragging.value = true;
    const clientX = 'touches' in e ? e.touches[0].clientX : e.clientX;
    const clientY = 'touches' in e ? e.touches[0].clientY : e.clientY;
    
    const rect = tourCardRef.value.getBoundingClientRect();
    dragOffset.value = {
        x: clientX - rect.left,
        y: clientY - rect.top,
    };
    
    document.addEventListener('mousemove', onDrag);
    document.addEventListener('mouseup', stopDrag);
    document.addEventListener('touchmove', onDrag);
    document.addEventListener('touchend', stopDrag);
};

const onDrag = (e: MouseEvent | TouchEvent) => {
    if (!isDragging.value) return;
    
    e.preventDefault();
    const clientX = 'touches' in e ? e.touches[0].clientX : e.clientX;
    const clientY = 'touches' in e ? e.touches[0].clientY : e.clientY;
    
    const newX = clientX - dragOffset.value.x;
    const newY = clientY - dragOffset.value.y;
    
    // Keep within viewport bounds
    const maxX = window.innerWidth - 400; // card width ~400px
    const maxY = window.innerHeight - 300; // card height ~300px
    
    cardPosition.value = {
        x: Math.max(0, Math.min(newX, maxX)),
        y: Math.max(0, Math.min(newY, maxY)),
    };
    hasBeenDragged.value = true;
};

const stopDrag = () => {
    isDragging.value = false;
    document.removeEventListener('mousemove', onDrag);
    document.removeEventListener('mouseup', stopDrag);
    document.removeEventListener('touchmove', onDrag);
    document.removeEventListener('touchend', stopDrag);
};

const nextStep = () => {
    if (isLastStep.value) {
        completeTour();
    } else {
        currentStep.value++;
        nextTick(() => highlightElement());
    }
};

const prevStep = () => {
    if (!isFirstStep.value) {
        currentStep.value--;
        nextTick(() => highlightElement());
    }
};

const skipTour = () => {
    markTourComplete();
    closeTour();
    emit('skip');
};

const completeTour = () => {
    markTourComplete();
    closeTour();
    emit('complete');
};

const markTourComplete = () => {
    try {
        localStorage.setItem(TOUR_STORAGE_KEY, 'true');
    } catch {
        // Ignore localStorage errors
    }
};

const closeTour = () => {
    isVisible.value = false;
    removeHighlight();
    releaseTourLock();
};

const highlightElement = () => {
    removeHighlight();
    const selector = currentStepData.value.highlight;
    
    // Reset drag position when moving to a new step (unless user has dragged)
    if (!hasBeenDragged.value) {
        cardPosition.value = { x: 0, y: 0 };
    }
    
    if (selector) {
        const element = document.querySelector(selector);
        if (element) {
            highlightedElement.value = element;
            element.classList.add('tour-highlight');
            
            // Scroll element into view
            element.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Calculate tooltip position based on element
            const rect = element.getBoundingClientRect();
            const viewportHeight = window.innerHeight;
            const cardHeight = 380; // Approximate card height
            
            // Calculate optimal top position - ensure card stays within viewport
            let topPos = rect.top + window.scrollY - 50;
            
            // If the card would go below the viewport, position it higher
            if (topPos + cardHeight > viewportHeight + window.scrollY - 20) {
                topPos = Math.max(80, viewportHeight + window.scrollY - cardHeight - 20);
            }
            
            // Ensure it doesn't go above the viewport
            topPos = Math.max(80, topPos);
            
            tooltipPosition.value = {
                top: topPos,
                left: rect.right + 20, // Position to the right of the element
            };
        }
    } else {
        highlightedElement.value = null;
    }
};

const removeHighlight = () => {
    document.querySelectorAll('.tour-highlight').forEach(el => {
        el.classList.remove('tour-highlight');
    });
    highlightedElement.value = null;
};

const getPositionClasses = () => {
    // If user has dragged, don't apply centering classes
    if (hasBeenDragged.value) {
        return '';
    }
    
    const position = currentStepData.value.position;
    
    if (position === 'center' || !highlightedElement.value) {
        return 'top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2';
    }
    
    // For sidebar items, position to the right
    return '';
};

const getPositionStyle = () => {
    // If user has dragged, use the drag position
    if (hasBeenDragged.value) {
        return {
            top: `${cardPosition.value.y}px`,
            left: `${cardPosition.value.x}px`,
            transform: 'none',
        };
    }
    
    const position = currentStepData.value.position;
    
    if (position === 'center' || !highlightedElement.value) {
        return {};
    }
    
    // Position next to the highlighted element with smart positioning
    return {
        top: `${tooltipPosition.value.top}px`,
        left: `${tooltipPosition.value.left}px`,
    };
};

// Reset tour (for testing or user request)
const resetTour = () => {
    try {
        localStorage.removeItem(TOUR_STORAGE_KEY);
        sessionStorage.removeItem(TOUR_INSTANCE_KEY);
    } catch {
        // Ignore errors
    }
};

onMounted(() => {
    if (props.autoStart && !isTourCompleted()) {
        // Delay to ensure DOM is ready
        setTimeout(() => {
            startTour();
        }, 1500);
    }
});

onUnmounted(() => {
    removeHighlight();
    releaseTourLock();
    stopDrag(); // Clean up drag listeners
});

// Expose methods for external use
defineExpose({ startTour, resetTour });
</script>

<template>
    <!-- Tour Overlay -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="isVisible" class="fixed inset-0 z-[100]">
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-black/60" />

                <!-- Tour Card -->
                <div
                    ref="tourCardRef"
                    :class="[
                        'absolute z-10 w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden',
                        getPositionClasses(),
                        isDragging ? 'cursor-grabbing' : ''
                    ]"
                    :style="getPositionStyle()"
                >
                    <!-- Drag Handle + Progress Bar -->
                    <div 
                        class="h-6 bg-gray-50 border-b border-gray-100 cursor-grab active:cursor-grabbing flex items-center justify-center gap-1 select-none"
                        @mousedown="startDrag"
                        @touchstart="startDrag"
                    >
                        <div class="w-8 h-1 bg-gray-300 rounded-full"></div>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div class="h-1 bg-gray-100">
                        <div
                            class="h-full bg-emerald-500 transition-all duration-300"
                            :style="{ width: `${progress}%` }"
                        />
                    </div>

                    <!-- Content -->
                    <div class="p-6">
                        <!-- Icon -->
                        <div class="flex justify-center mb-4">
                            <div class="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center">
                                <component
                                    :is="currentStepData.icon"
                                    class="h-8 w-8 text-emerald-600"
                                    aria-hidden="true"
                                />
                            </div>
                        </div>

                        <!-- Title -->
                        <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">
                            {{ currentStepData.title }}
                        </h3>

                        <!-- Description -->
                        <p class="text-gray-600 text-center mb-6">
                            {{ currentStepData.description }}
                        </p>

                        <!-- Step Indicator -->
                        <div class="flex justify-center gap-1.5 mb-6">
                            <div
                                v-for="(step, index) in steps"
                                :key="step.id"
                                :class="[
                                    'w-2 h-2 rounded-full transition-colors',
                                    index === currentStep ? 'bg-emerald-500' : 'bg-gray-200'
                                ]"
                            />
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-between">
                            <button
                                v-if="!isFirstStep"
                                @click="prevStep"
                                class="flex items-center gap-1 px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors"
                            >
                                <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                                Back
                            </button>
                            <button
                                v-else
                                @click="skipTour"
                                class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors"
                            >
                                Skip Tour
                            </button>

                            <button
                                @click="nextStep"
                                class="flex items-center gap-1 px-6 py-2.5 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors"
                            >
                                {{ isLastStep ? 'Get Started' : 'Next' }}
                                <ArrowRightIcon v-if="!isLastStep" class="h-4 w-4" aria-hidden="true" />
                                <CheckCircleIcon v-else class="h-4 w-4" aria-hidden="true" />
                            </button>
                        </div>

                        <!-- Don't show again checkbox (only on first step) -->
                        <div v-if="isFirstStep" class="mt-4 pt-4 border-t border-gray-100">
                            <p class="text-xs text-gray-400 text-center">
                                You can restart this tour anytime from the Help menu
                            </p>
                        </div>
                    </div>

                    <!-- Close Button -->
                    <button
                        @click="skipTour"
                        class="absolute top-8 right-3 p-1.5 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100 transition-colors"
                        aria-label="Close tour"
                    >
                        <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style>
.tour-highlight {
    position: relative;
    z-index: 101 !important;
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.5), 0 0 0 8px rgba(16, 185, 129, 0.25) !important;
    border-radius: 8px;
    background-color: white !important;
    animation: tour-pulse 2s ease-in-out infinite;
}

@keyframes tour-pulse {
    0%, 100% {
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.5), 0 0 0 8px rgba(16, 185, 129, 0.25);
    }
    50% {
        box-shadow: 0 0 0 6px rgba(16, 185, 129, 0.6), 0 0 0 12px rgba(16, 185, 129, 0.3);
    }
}

/* Prevent text selection while dragging */
.cursor-grabbing * {
    user-select: none;
}
</style>
