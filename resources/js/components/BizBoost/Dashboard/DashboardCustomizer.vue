<script setup lang="ts">
import { computed, ref } from 'vue';
import { useBizBoostDashboard, type WidgetConfig } from '@/composables/useBizBoostDashboard';
import {
    XMarkIcon,
    EyeIcon,
    EyeSlashIcon,
    ArrowPathIcon,
    Squares2X2Icon,
    ViewColumnsIcon,
    CheckIcon,
    ChartBarIcon,
    BoltIcon,
    SparklesIcon,
    CalendarIcon,
    LightBulbIcon,
    DocumentTextIcon,
    RocketLaunchIcon,
    ListBulletIcon,
} from '@heroicons/vue/24/outline';

interface Props {
    open: boolean;
}

defineProps<Props>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const { layout, toggleWidget, resetLayout } = useBizBoostDashboard();

// Widget icons mapping
const widgetIcons: Record<string, any> = {
    'stats': ChartBarIcon,
    'activity': ListBulletIcon,
    'quick-actions': BoltIcon,
    'ai-credits': SparklesIcon,
    'upcoming-posts': CalendarIcon,
    'suggestions': LightBulbIcon,
    'recent-posts': DocumentTextIcon,
    'performance-cta': RocketLaunchIcon,
    'chart': ChartBarIcon,
};

// Group widgets by column
const mainWidgets = computed(() => 
    layout.value.widgets.filter(w => w.column === 'main')
);

const sidebarWidgets = computed(() => 
    layout.value.widgets.filter(w => w.column === 'sidebar')
);

const handleClose = () => {
    emit('close');
};

const handleReset = () => {
    if (confirm('Reset dashboard to default layout? This cannot be undone.')) {
        resetLayout();
    }
};
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="open"
                class="fixed inset-0 z-50 overflow-y-auto"
            >
                <!-- Backdrop -->
                <div
                    class="fixed inset-0 bg-black/50 backdrop-blur-sm"
                    @click="handleClose"
                />

                <!-- Modal -->
                <div class="flex min-h-full items-center justify-center p-4">
                    <Transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95"
                        enter-to-class="opacity-100 scale-100"
                        leave-active-class="transition duration-150 ease-in"
                        leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-95"
                    >
                        <div
                            v-if="open"
                            class="relative w-full max-w-2xl rounded-2xl bg-white dark:bg-slate-900 shadow-2xl"
                        >
                            <!-- Header -->
                            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-700 px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded-xl bg-violet-100 dark:bg-violet-900/30">
                                        <Squares2X2Icon class="h-5 w-5 text-violet-600 dark:text-violet-400" aria-hidden="true" />
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                                            Customize Dashboard
                                        </h2>
                                        <p class="text-sm text-slate-500 dark:text-slate-400">
                                            Show, hide, or rearrange widgets
                                        </p>
                                    </div>
                                </div>
                                <button
                                    @click="handleClose"
                                    class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors"
                                    aria-label="Close customizer"
                                >
                                    <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                                </button>
                            </div>

                            <!-- Content -->
                            <div class="p-6 space-y-6 max-h-[60vh] overflow-y-auto">
                                <!-- Main Column Widgets -->
                                <div>
                                    <div class="flex items-center gap-2 mb-3">
                                        <ViewColumnsIcon class="h-4 w-4 text-slate-400" aria-hidden="true" />
                                        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                            Main Area
                                        </h3>
                                    </div>
                                    <div class="space-y-2">
                                        <button
                                            v-for="widget in mainWidgets"
                                            :key="widget.id"
                                            @click="toggleWidget(widget.id)"
                                            :class="[
                                                'w-full flex items-center gap-3 p-3 rounded-xl border transition-all',
                                                widget.visible
                                                    ? 'bg-violet-50 dark:bg-violet-900/20 border-violet-200 dark:border-violet-700'
                                                    : 'bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 opacity-60',
                                            ]"
                                        >
                                            <div
                                                :class="[
                                                    'p-2 rounded-lg',
                                                    widget.visible
                                                        ? 'bg-violet-100 dark:bg-violet-800/50 text-violet-600 dark:text-violet-400'
                                                        : 'bg-slate-200 dark:bg-slate-700 text-slate-400 dark:text-slate-500',
                                                ]"
                                            >
                                                <component
                                                    :is="widgetIcons[widget.type] || Squares2X2Icon"
                                                    class="h-5 w-5"
                                                    aria-hidden="true"
                                                />
                                            </div>
                                            <span
                                                :class="[
                                                    'flex-1 text-left font-medium',
                                                    widget.visible
                                                        ? 'text-slate-900 dark:text-white'
                                                        : 'text-slate-500 dark:text-slate-400',
                                                ]"
                                            >
                                                {{ widget.title }}
                                            </span>
                                            <div
                                                :class="[
                                                    'p-1.5 rounded-lg transition-colors',
                                                    widget.visible
                                                        ? 'bg-violet-200 dark:bg-violet-700 text-violet-700 dark:text-violet-300'
                                                        : 'bg-slate-200 dark:bg-slate-600 text-slate-400 dark:text-slate-500',
                                                ]"
                                            >
                                                <EyeIcon v-if="widget.visible" class="h-4 w-4" aria-hidden="true" />
                                                <EyeSlashIcon v-else class="h-4 w-4" aria-hidden="true" />
                                            </div>
                                        </button>
                                    </div>
                                </div>

                                <!-- Sidebar Widgets -->
                                <div>
                                    <div class="flex items-center gap-2 mb-3">
                                        <ViewColumnsIcon class="h-4 w-4 text-slate-400" aria-hidden="true" />
                                        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                            Sidebar
                                        </h3>
                                    </div>
                                    <div class="space-y-2">
                                        <button
                                            v-for="widget in sidebarWidgets"
                                            :key="widget.id"
                                            @click="toggleWidget(widget.id)"
                                            :class="[
                                                'w-full flex items-center gap-3 p-3 rounded-xl border transition-all',
                                                widget.visible
                                                    ? 'bg-violet-50 dark:bg-violet-900/20 border-violet-200 dark:border-violet-700'
                                                    : 'bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 opacity-60',
                                            ]"
                                        >
                                            <div
                                                :class="[
                                                    'p-2 rounded-lg',
                                                    widget.visible
                                                        ? 'bg-violet-100 dark:bg-violet-800/50 text-violet-600 dark:text-violet-400'
                                                        : 'bg-slate-200 dark:bg-slate-700 text-slate-400 dark:text-slate-500',
                                                ]"
                                            >
                                                <component
                                                    :is="widgetIcons[widget.type] || Squares2X2Icon"
                                                    class="h-5 w-5"
                                                    aria-hidden="true"
                                                />
                                            </div>
                                            <span
                                                :class="[
                                                    'flex-1 text-left font-medium',
                                                    widget.visible
                                                        ? 'text-slate-900 dark:text-white'
                                                        : 'text-slate-500 dark:text-slate-400',
                                                ]"
                                            >
                                                {{ widget.title }}
                                            </span>
                                            <div
                                                :class="[
                                                    'p-1.5 rounded-lg transition-colors',
                                                    widget.visible
                                                        ? 'bg-violet-200 dark:bg-violet-700 text-violet-700 dark:text-violet-300'
                                                        : 'bg-slate-200 dark:bg-slate-600 text-slate-400 dark:text-slate-500',
                                                ]"
                                            >
                                                <EyeIcon v-if="widget.visible" class="h-4 w-4" aria-hidden="true" />
                                                <EyeSlashIcon v-else class="h-4 w-4" aria-hidden="true" />
                                            </div>
                                        </button>
                                    </div>
                                </div>

                                <!-- Tip -->
                                <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700">
                                    <p class="text-sm text-amber-800 dark:text-amber-300">
                                        <strong>Tip:</strong> You can also drag and drop widgets directly on the dashboard when in edit mode.
                                    </p>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="flex items-center justify-between border-t border-slate-200 dark:border-slate-700 px-6 py-4">
                                <button
                                    @click="handleReset"
                                    class="flex items-center gap-2 px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                                >
                                    <ArrowPathIcon class="h-4 w-4" aria-hidden="true" />
                                    Reset to Default
                                </button>
                                <button
                                    @click="handleClose"
                                    class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-violet-600 text-white font-medium hover:bg-violet-700 transition-colors"
                                >
                                    <CheckIcon class="h-4 w-4" aria-hidden="true" />
                                    Done
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
