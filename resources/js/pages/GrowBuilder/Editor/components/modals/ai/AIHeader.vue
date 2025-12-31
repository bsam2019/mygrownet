<template>
    <div
        class="flex-shrink-0 border-b"
        :class="darkMode ? 'border-gray-800 bg-gray-900' : 'border-gray-100 bg-white'"
    >
        <!-- Top row: Logo, title, close -->
        <div class="flex items-center justify-between px-4 py-3">
            <div class="flex items-center gap-3">
                <!-- AI Logo -->
                <div class="relative">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-violet-500 via-purple-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-purple-500/25">
                        <SparklesIcon class="w-4 h-4 text-white" aria-hidden="true" />
                    </div>
                    <div 
                        class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full border-2"
                        :class="[
                            statusColor,
                            darkMode ? 'border-gray-900' : 'border-white'
                        ]"
                        :title="`AI Status: ${isAvailable ? 'Online' : 'Offline'}`"
                    />
                </div>
                <div class="min-w-0">
                    <h2 class="text-sm font-semibold flex items-center gap-2" :class="darkMode ? 'text-white' : 'text-gray-900'">
                        AI Assistant
                        <span 
                            v-if="provider"
                            class="text-[9px] font-medium px-1.5 py-0.5 rounded-full uppercase tracking-wide"
                            :class="darkMode ? 'bg-gray-800 text-gray-400' : 'bg-gray-100 text-gray-500'"
                        >
                            {{ provider }}
                        </span>
                    </h2>
                    <p class="text-[11px] truncate max-w-[180px]" :class="darkMode ? 'text-gray-500' : 'text-gray-500'">
                        {{ contextSummary || (isAvailable ? 'Ready to help' : 'Not configured') }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-1">
                <!-- New Chat Button -->
                <button
                    @click="$emit('new-chat')"
                    class="p-1.5 rounded-lg transition-colors"
                    :class="darkMode ? 'hover:bg-gray-800 text-gray-400 hover:text-gray-200' : 'hover:bg-gray-100 text-gray-500 hover:text-gray-700'"
                    aria-label="Start new conversation"
                    title="New conversation"
                >
                    <ArrowPathIcon class="w-4 h-4" aria-hidden="true" />
                </button>

                <!-- Close Button -->
                <button
                    @click="$emit('close')"
                    class="p-1.5 rounded-lg transition-colors"
                    :class="darkMode ? 'hover:bg-gray-800 text-gray-400 hover:text-gray-200' : 'hover:bg-gray-100 text-gray-500 hover:text-gray-700'"
                    aria-label="Close AI Assistant"
                >
                    <XMarkIcon class="w-5 h-5" aria-hidden="true" />
                </button>
            </div>
        </div>

        <!-- Limit Warning Banner -->
        <div 
            v-if="aiUsage && !aiUsage.is_unlimited && aiUsage.remaining <= 0"
            class="px-4 py-2 bg-red-50 border-b border-red-100 text-red-700 text-xs flex items-center justify-between"
            :class="darkMode ? 'bg-red-900/20 border-red-900/30 text-red-400' : ''"
        >
            <span>AI limit reached for this month</span>
            <a href="/growbuilder/subscription" class="font-medium underline hover:no-underline">Upgrade</a>
        </div>

        <!-- View Tabs + Controls Row -->
        <div class="flex items-center justify-between px-3 pb-2">
            <!-- View Tabs -->
            <div class="flex items-center gap-1">
                <button
                    v-for="view in views"
                    :key="view.id"
                    @click="$emit('change-view', view.id)"
                    class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg transition-all"
                    :class="[
                        activeView === view.id
                            ? 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-sm' 
                            : darkMode
                                ? 'text-gray-400 hover:text-gray-300 hover:bg-gray-800'
                                : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
                    ]"
                >
                    <component :is="view.icon" class="w-3.5 h-3.5" aria-hidden="true" />
                    {{ view.label }}
                </button>
            </div>

            <!-- Right side: Usage + Creativity -->
            <div class="flex items-center gap-1.5">
                <!-- AI Usage Badge (compact) -->
                <div 
                    v-if="aiUsage && !aiUsage.is_unlimited"
                    class="flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-medium"
                    :class="[
                        darkMode ? 'bg-gray-800' : 'bg-gray-100',
                        aiUsage.remaining <= 0 ? 'text-red-500' : 
                        aiUsage.remaining <= 5 ? 'text-amber-500' : 
                        darkMode ? 'text-gray-400' : 'text-gray-600'
                    ]"
                    :title="`${aiUsage.remaining} of ${aiUsage.limit} prompts remaining`"
                >
                    <SparklesIcon class="w-2.5 h-2.5" aria-hidden="true" />
                    <span>{{ aiUsage.remaining }}/{{ aiUsage.limit }}</span>
                </div>
                
                <!-- Unlimited Badge (compact) -->
                <div 
                    v-else-if="aiUsage?.is_unlimited"
                    class="flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-medium"
                    :class="darkMode ? 'bg-purple-900/30 text-purple-400' : 'bg-purple-100 text-purple-600'"
                    title="Unlimited AI prompts"
                >
                    <SparklesIcon class="w-2.5 h-2.5" aria-hidden="true" />
                    <span>∞</span>
                </div>

                <!-- Creativity Mode Toggle (icon only with dropdown) -->
                <div class="relative" ref="creativityDropdownRef">
                    <button
                        @click="showCreativityDropdown = !showCreativityDropdown"
                        class="flex items-center gap-0.5 p-1.5 rounded-lg text-[10px] font-medium transition-colors"
                        :class="[
                            creativityLevel === 'creative' 
                                ? 'bg-gradient-to-r from-pink-500/20 to-orange-500/20 text-pink-500' 
                                : creativityLevel === 'guided'
                                    ? darkMode ? 'bg-gray-800 text-gray-400' : 'bg-gray-100 text-gray-600'
                                    : darkMode ? 'bg-purple-900/30 text-purple-400' : 'bg-purple-100 text-purple-600'
                        ]"
                        :title="`Creativity: ${creativityLevel}`"
                    >
                        <component :is="creativityIcon" class="w-3.5 h-3.5" aria-hidden="true" />
                        <ChevronDownIcon class="w-2.5 h-2.5" aria-hidden="true" />
                    </button>
                    
                    <!-- Dropdown -->
                    <Transition
                        enter-active-class="transition ease-out duration-100"
                        enter-from-class="transform opacity-0 scale-95"
                        enter-to-class="transform opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-75"
                        leave-from-class="transform opacity-100 scale-100"
                        leave-to-class="transform opacity-0 scale-95"
                    >
                        <div 
                            v-if="showCreativityDropdown"
                            class="absolute right-0 mt-1 w-48 rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-50"
                            :class="darkMode ? 'bg-gray-800' : 'bg-white'"
                        >
                            <div class="py-1">
                                <button
                                    v-for="mode in creativityModes"
                                    :key="mode.value"
                                    @click="selectCreativityMode(mode.value)"
                                    class="w-full px-3 py-2 text-left text-xs flex items-start gap-2 transition-colors"
                                    :class="[
                                        creativityLevel === mode.value
                                            ? darkMode ? 'bg-purple-900/30 text-purple-400' : 'bg-purple-50 text-purple-700'
                                            : darkMode ? 'text-gray-300 hover:bg-gray-700' : 'text-gray-700 hover:bg-gray-50'
                                    ]"
                                >
                                    <component :is="mode.icon" class="w-4 h-4 mt-0.5 flex-shrink-0" aria-hidden="true" />
                                    <div>
                                        <div class="font-medium">{{ mode.label }}</div>
                                        <div class="text-[10px] opacity-70">{{ mode.description }}</div>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { SparklesIcon, XMarkIcon, ChatBubbleLeftRightIcon, DocumentTextIcon, WrenchScrewdriverIcon, ArrowPathIcon, ChevronDownIcon, ShieldCheckIcon, AdjustmentsHorizontalIcon, LightBulbIcon } from '@heroicons/vue/24/outline';
import { ref, computed, onMounted, onUnmounted } from 'vue';

type CreativityLevel = 'guided' | 'balanced' | 'creative';

interface AIUsage {
    limit: number;
    used: number;
    remaining: number;
    is_unlimited: boolean;
    percentage: number;
    month: string;
    features: string[];
    has_priority: boolean;
}

const props = defineProps<{
    darkMode?: boolean;
    isAvailable: boolean;
    provider?: string;
    activeView: 'chat' | 'generate' | 'tools';
    contextSummary?: string;
    aiUsage?: AIUsage;
    creativityLevel?: CreativityLevel;
}>();

const emit = defineEmits<{
    close: [];
    'change-view': [view: 'chat' | 'generate' | 'tools'];
    'new-chat': [];
    'update:creativityLevel': [level: CreativityLevel];
}>();

// Creativity mode state
const showCreativityDropdown = ref(false);
const creativityDropdownRef = ref<HTMLElement | null>(null);
const creativityLevel = computed(() => props.creativityLevel || 'balanced');

const creativityModes = [
    { 
        value: 'guided' as CreativityLevel, 
        label: 'Guided', 
        icon: ShieldCheckIcon,
        description: 'Follows best practices strictly'
    },
    { 
        value: 'balanced' as CreativityLevel, 
        label: 'Balanced', 
        icon: AdjustmentsHorizontalIcon,
        description: 'Smart defaults, respects your choices'
    },
    { 
        value: 'creative' as CreativityLevel, 
        label: 'Creative', 
        icon: LightBulbIcon,
        description: 'Maximum freedom, experimental'
    },
];

const creativityIcon = computed(() => {
    const mode = creativityModes.find(m => m.value === creativityLevel.value);
    return mode?.icon || AdjustmentsHorizontalIcon;
});

const selectCreativityMode = (mode: CreativityLevel) => {
    emit('update:creativityLevel', mode);
    showCreativityDropdown.value = false;
    // Save preference to localStorage
    localStorage.setItem('ai_creativity_level', mode);
};

// Close dropdown when clicking outside
const handleClickOutside = (event: MouseEvent) => {
    if (creativityDropdownRef.value && !creativityDropdownRef.value.contains(event.target as Node)) {
        showCreativityDropdown.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

// Compute canUseAI from aiUsage (don't receive as prop for better reactivity)
const canUseAI = computed(() => {
    if (!props.aiUsage) return true;
    return props.aiUsage.is_unlimited || props.aiUsage.remaining > 0;
});

// Computed property for status color
const statusColor = computed(() => {
    const isOnline = props.isAvailable && canUseAI.value !== false;
    return isOnline ? 'bg-emerald-500' : 'bg-amber-500';
});

const views = [
    { id: 'chat', label: 'Chat', icon: ChatBubbleLeftRightIcon },
    { id: 'generate', label: 'Generate', icon: DocumentTextIcon },
    { id: 'tools', label: 'Tools', icon: WrenchScrewdriverIcon },
];
</script>
