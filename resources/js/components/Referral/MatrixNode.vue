<template>
    <div 
        :class="nodeClasses"
        @click="handleClick"
        class="matrix-node relative cursor-pointer transition-all duration-200 hover:scale-105"
    >
        <!-- Node Content -->
        <div class="node-content relative">
            <!-- Avatar/Icon -->
            <div :class="avatarClasses">
                <img 
                    v-if="node?.avatar && !node?.is_empty" 
                    :src="node.avatar" 
                    :alt="node?.name"
                    class="w-full h-full object-cover"
                />
                <UserIcon 
                    v-else-if="!node?.is_empty" 
                    :class="iconClasses"
                />
                <PlusIcon 
                    v-else 
                    :class="iconClasses"
                />
            </div>
            
            <!-- Node Info -->
            <div v-if="!node?.is_empty" class="node-info mt-2 text-center">
                <div :class="nameClasses">
                    {{ truncatedName }}
                </div>
                <div v-if="node?.tier" :class="tierClasses">
                    {{ node?.tier }}
                </div>
                <div v-if="node?.investment_amount" :class="amountClasses">
                    {{ formatCurrency(node?.investment_amount) }}
                </div>
            </div>
            
            <!-- Empty Node Info -->
            <div v-else class="node-info mt-2 text-center">
                <div :class="nameClasses">
                    Available
                </div>
                <div :class="tierClasses">
                    Position {{ position }}
                </div>
            </div>
            
            <!-- Status Indicators -->
            <div class="status-indicators absolute -top-1 -right-1 flex space-x-1">
                <!-- Direct Referral Badge -->
                <div 
                    v-if="node?.is_direct && !node?.is_empty"
                    class="w-4 h-4 bg-emerald-500 rounded-full border-2 border-white flex items-center justify-center"
                    title="Direct Referral"
                >
                    <CheckIcon class="w-2 h-2 text-white" />
                </div>
                
                <!-- Spillover Badge -->
                <div 
                    v-if="node?.is_spillover && !node?.is_empty"
                    class="w-4 h-4 bg-amber-500 rounded-full border-2 border-white flex items-center justify-center"
                    title="Spillover Referral"
                >
                    <ArrowDownIcon class="w-2 h-2 text-white" />
                </div>
                
                <!-- Root Badge -->
                <div 
                    v-if="isRoot"
                    class="w-4 h-4 bg-blue-500 rounded-full border-2 border-white flex items-center justify-center"
                    title="You"
                >
                    <CrownIcon class="w-2 h-2 text-white" />
                </div>
            </div>
            
            <!-- Activity Status -->
            <div 
                v-if="!node?.is_empty"
                :class="statusDotClasses"
                :title="node?.status === 'active' ? 'Active Investor' : 'Inactive'"
            ></div>
        </div>
        
        <!-- Hover Tooltip -->
        <div 
            v-if="showTooltip && !node?.is_empty"
            class="tooltip absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 z-10"
        >
            <div class="bg-gray-900 text-white text-xs rounded-lg py-2 px-3 whitespace-nowrap">
                <div class="font-medium">{{ node?.name }}</div>
                <div class="text-gray-300">{{ node?.email }}</div>
                <div v-if="node?.joined_at" class="text-gray-400">
                    Joined {{ formatDate(node?.joined_at) }}
                </div>
                <div v-if="node?.investment_amount" class="text-emerald-400">
                    Total Investment: {{ formatCurrency(node?.investment_amount) }}
                </div>
                <!-- Tooltip Arrow -->
                <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900"></div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { 
    UserIcon, 
    PlusIcon, 
    CheckIcon, 
    ArrowDownIcon, 
    CrownIcon 
} from 'lucide-vue-next';
import { formatCurrency } from '@/utils/formatting';

interface MatrixNodeData {
    id?: number;
    name?: string;
    email?: string;
    tier?: string;
    investment_amount?: number;
    position?: number;
    level?: number;
    is_direct?: boolean;
    is_spillover?: boolean;
    is_empty?: boolean;
    avatar?: string;
    joined_at?: string;
    status?: 'active' | 'inactive';
}

interface Props {
    node: MatrixNodeData;
    position?: number;
    level?: number;
    isRoot?: boolean;
    isSmall?: boolean;
}

interface Emits {
    (e: 'node-click', node: MatrixNodeData): void;
}

const props = withDefaults(defineProps<Props>(), {
    position: 1,
    level: 1,
    isRoot: false,
    isSmall: false
});

const emit = defineEmits<Emits>();

const showTooltip = ref(false);

// Computed classes
const nodeClasses = computed(() => [
    'matrix-node',
    {
        'node-root': props.isRoot,
        'node-filled': !props.node?.is_empty,
        'node-empty': props.node?.is_empty,
        'node-direct': props.node?.is_direct,
        'node-spillover': props.node?.is_spillover,
        'node-small': props.isSmall,
        'node-inactive': props.node?.status === 'inactive'
    }
]);

const avatarClasses = computed(() => [
    'avatar rounded-full border-4 flex items-center justify-center overflow-hidden',
    {
        // Size classes
        'w-16 h-16': !props.isSmall,
        'w-12 h-12': props.isSmall,
        
        // Border colors based on node type
        'border-blue-500 bg-blue-100': props.isRoot,
        'border-emerald-500 bg-emerald-100': props.node?.is_direct && !props.isRoot,
        'border-amber-500 bg-amber-100': props.node?.is_spillover,
        'border-gray-300 bg-gray-100': props.node?.is_empty,
        'border-gray-400 bg-gray-200': !props.node?.is_empty && !props.node?.is_direct && !props.node?.is_spillover && !props.isRoot,
        
        // Status-based styling
        'opacity-60': props.node?.status === 'inactive'
    }
]);

const iconClasses = computed(() => [
    {
        'h-8 w-8': !props.isSmall,
        'h-6 w-6': props.isSmall,
        'text-blue-600': props.isRoot,
        'text-emerald-600': props.node?.is_direct && !props.isRoot,
        'text-amber-600': props.node?.is_spillover,
        'text-gray-400': props.node?.is_empty,
        'text-gray-600': !props.node?.is_empty && !props.node?.is_direct && !props.node?.is_spillover && !props.isRoot
    }
]);

const nameClasses = computed(() => [
    'font-medium truncate',
    {
        'text-sm': !props.isSmall,
        'text-xs': props.isSmall,
        'text-blue-900': props.isRoot,
        'text-emerald-900': props.node?.is_direct && !props.isRoot,
        'text-amber-900': props.node?.is_spillover,
        'text-gray-500': props.node?.is_empty,
        'text-gray-900': !props.node?.is_empty && !props.node?.is_direct && !props.node?.is_spillover && !props.isRoot
    }
]);

const tierClasses = computed(() => [
    'text-xs font-medium',
    {
        'text-blue-600': props.isRoot,
        'text-emerald-600': props.node?.is_direct && !props.isRoot,
        'text-amber-600': props.node?.is_spillover,
        'text-gray-400': props.node?.is_empty,
        'text-gray-600': !props.node?.is_empty && !props.node?.is_direct && !props.node?.is_spillover && !props.isRoot
    }
]);

const amountClasses = computed(() => [
    'text-xs font-bold',
    {
        'text-blue-700': props.isRoot,
        'text-emerald-700': props.node?.is_direct && !props.isRoot,
        'text-amber-700': props.node?.is_spillover,
        'text-gray-700': !props.node?.is_empty && !props.node?.is_direct && !props.node?.is_spillover && !props.isRoot
    }
]);

const statusDotClasses = computed(() => [
    'absolute -bottom-1 -right-1 w-3 h-3 rounded-full border-2 border-white',
    {
        'bg-emerald-500': props.node?.status === 'active',
        'bg-gray-400': props.node?.status === 'inactive'
    }
]);

// Computed properties
const truncatedName = computed(() => {
    if (!props.node?.name) return '';
    const maxLength = props.isSmall ? 8 : 12;
    return props.node.name.length > maxLength 
        ? props.node.name.substring(0, maxLength) + '...'
        : props.node.name;
});

// Event handlers
const handleClick = () => {
    emit('node-click', props.node);
};



const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
};
</script>

<style scoped>
.matrix-node {
    @apply relative;
    min-width: 80px;
}

.matrix-node.node-small {
    min-width: 60px;
}

.matrix-node:hover .tooltip {
    @apply block;
}

.tooltip {
    @apply hidden;
}

.node-content {
    @apply flex flex-col items-center;
}

.node-info {
    max-width: 100px;
}

.node-small .node-info {
    max-width: 80px;
}

/* Hover effects */
.matrix-node:hover {
    @apply transform scale-105;
}

.matrix-node.node-empty:hover .avatar {
    @apply border-blue-400 bg-blue-50;
}

.matrix-node.node-empty:hover .icon {
    @apply text-blue-500;
}

/* Animation for new nodes */
@keyframes nodeAppear {
    from {
        opacity: 0;
        transform: scale(0.8);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.matrix-node.node-filled {
    animation: nodeAppear 0.3s ease-out;
}

/* Pulse animation for available positions */
.matrix-node.node-empty .avatar {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
}
</style>