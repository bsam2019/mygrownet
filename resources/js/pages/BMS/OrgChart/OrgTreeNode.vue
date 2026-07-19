<script setup lang="ts">
import { ChevronDownIcon, ChevronRightIcon } from '@heroicons/vue/24/outline';

interface WorkerNode {
    id: number;
    name: string;
    job_title: string | null;
    department: string | null;
    photo: string | null;
    children: WorkerNode[];
}

const props = defineProps<{
    node: WorkerNode;
    expanded: Set<number>;
    depth: number;
}>();

const emit = defineEmits<{
    toggle: [id: number];
}>();

const hasChildren = props.node.children.length > 0;
const isExpanded = props.expanded.has(props.node.id);
const levelColors = ['border-l-blue-500', 'border-l-indigo-500', 'border-l-violet-500', 'border-l-purple-500', 'border-l-pink-500'];
const bgColors = ['bg-blue-50', 'bg-indigo-50', 'bg-violet-50', 'bg-purple-50', 'bg-pink-50'];
const avatarColors = ['from-blue-400 to-blue-600', 'from-indigo-400 to-indigo-600', 'from-violet-400 to-violet-600', 'from-purple-400 to-purple-600', 'from-pink-400 to-pink-600'];
</script>

<template>
    <div class="flex flex-col items-center">
        <!-- Node card -->
        <div
            :class="[
                'relative w-72 rounded-xl border-2 p-4 transition-all hover:shadow-lg cursor-pointer',
                levelColors[depth % levelColors.length],
                bgColors[depth % bgColors.length],
                isExpanded ? 'shadow-md' : 'shadow-sm'
            ]"
            @click="emit('toggle', node.id)"
        >
            <div class="flex items-center gap-3">
                <div
                    :class="[
                        'w-10 h-10 rounded-full bg-gradient-to-br flex items-center justify-center text-white font-bold text-sm flex-shrink-0',
                        avatarColors[depth % avatarColors.length]
                    ]"
                >
                    {{ node.name.charAt(0) }}{{ node.name.split(' ').pop()?.charAt(0) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-gray-900 text-sm truncate">{{ node.name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ node.job_title || '—' }}</p>
                    <p v-if="node.department" class="text-xs text-gray-400 truncate">{{ node.department }}</p>
                </div>
                <div v-if="hasChildren" class="flex-shrink-0">
                    <component :is="isExpanded ? ChevronDownIcon : ChevronRightIcon" class="h-4 w-4 text-gray-400" />
                </div>
            </div>
            <div v-if="hasChildren" class="mt-2 text-xs text-gray-400 text-center">
                {{ node.children.length }} {{ node.children.length === 1 ? 'direct report' : 'direct reports' }}
            </div>
        </div>

        <!-- Children with connector line -->
        <div v-if="hasChildren && isExpanded" class="flex flex-col items-center mt-4">
            <div class="w-0.5 h-6 bg-gray-300"></div>
            <div class="flex gap-6 justify-center">
                <div v-for="child in node.children" :key="child.id" class="flex flex-col items-center relative">
                    <!-- Horizontal connector -->
                    <div v-if="node.children.length > 1" class="absolute top-0 left-1/2 w-full h-0.5 bg-gray-300"
                        :style="{ width: node.children.length > 1 ? '100%' : '0' }">
                    </div>
                    <div class="w-0.5 h-4 bg-gray-300"></div>
                    <OrgTreeNode :node="child" :expanded="expanded" :depth="depth + 1" @toggle="emit('toggle', $event)" />
                </div>
            </div>
        </div>
    </div>
</template>
