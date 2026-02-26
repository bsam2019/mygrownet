<script setup lang="ts">
import { TrashIcon, XMarkIcon } from '@heroicons/vue/24/outline';

interface Props {
    selectionCount: number;
    hasSelection: boolean;
}

defineProps<Props>();

const emit = defineEmits<{
    delete: [];
    selectAll: [];
    cancel: [];
}>();
</script>

<template>
    <Transition
        enter-active-class="transition-all duration-200"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition-all duration-200"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-2"
    >
        <div class="bg-blue-600 text-white rounded-lg shadow-lg px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button
                    @click="emit('cancel')"
                    class="p-1 hover:bg-blue-700 rounded transition-colors"
                    aria-label="Exit selection mode"
                >
                    <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                </button>
                
                <span class="font-medium">
                    <template v-if="hasSelection">
                        {{ selectionCount }} {{ selectionCount === 1 ? 'item' : 'items' }} selected
                    </template>
                    <template v-else>
                        Selection mode - Click checkboxes to select items
                    </template>
                </span>
            </div>
            
            <div class="flex items-center gap-2">
                <button
                    v-if="!hasSelection"
                    @click="emit('selectAll')"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-700 hover:bg-blue-800 rounded-lg transition-colors"
                >
                    <span>Select All</span>
                </button>
                
                <button
                    v-if="hasSelection"
                    @click="emit('delete')"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 rounded-lg transition-colors"
                >
                    <TrashIcon class="h-5 w-5" aria-hidden="true" />
                    <span>Delete</span>
                </button>
            </div>
        </div>
    </Transition>
</template>
