<script setup lang="ts">
import { XMarkIcon } from '@heroicons/vue/24/outline';

defineProps<{
    show: boolean;
}>();

const emit = defineEmits<{
    close: [];
}>();
</script>

<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm"
            @click.self="emit('close')"
        >
            <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-md w-full mx-4 animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-semibold text-gray-900">Keyboard Shortcuts</h3>
                    <button @click="emit('close')" class="p-1.5 hover:bg-gray-100 rounded-lg transition-colors">
                        <XMarkIcon class="w-5 h-5 text-gray-500" />
                    </button>
                </div>
                <div class="space-y-1">
                    <div v-for="shortcut in [
                        { label: 'Save', keys: 'Ctrl+S' },
                        { label: 'Undo', keys: 'Ctrl+Z' },
                        { label: 'Redo', keys: 'Ctrl+Shift+Z' },
                        { label: 'Preview Mode', keys: 'Ctrl+P' },
                        { label: 'Open in New Tab', keys: 'Ctrl+Shift+P' },
                        { label: 'Toggle Left Sidebar', keys: 'Ctrl+\\' },
                        { label: 'Toggle Right Sidebar', keys: 'Ctrl+]' },
                        { label: 'Duplicate Section', keys: 'Ctrl+D' },
                        { label: 'Delete Section', keys: 'Delete' },
                        { label: 'Deselect / Exit Preview', keys: 'Esc' },
                        { label: 'Show Shortcuts', keys: 'Ctrl+/' },
                    ]" :key="shortcut.label">
                        <div class="flex justify-between items-center py-2.5 px-3 rounded-lg hover:bg-gray-50 transition-colors">
                            <span class="text-gray-700 text-sm">{{ shortcut.label }}</span>
                            <kbd class="px-2.5 py-1 bg-gray-100 border border-gray-200 rounded-md text-xs font-semibold text-gray-700 shadow-sm">{{ shortcut.keys }}</kbd>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
