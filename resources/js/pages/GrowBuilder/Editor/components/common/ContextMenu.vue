<script setup lang="ts">
/**
 * Context Menu Component
 * Right-click menu for quick section actions
 */
import { ref, computed, onMounted, onUnmounted } from 'vue';
import {
    DocumentDuplicateIcon,
    ClipboardDocumentIcon,
    ClipboardDocumentCheckIcon,
    ScissorsIcon,
    TrashIcon,
    ArrowUpIcon,
    ArrowDownIcon,
    EyeIcon,
    EyeSlashIcon,
    LockClosedIcon,
    LockOpenIcon,
    PaintBrushIcon,
    Cog6ToothIcon,
} from '@heroicons/vue/24/outline';

interface MenuItem {
    id: string;
    label: string;
    icon?: any;
    shortcut?: string;
    disabled?: boolean;
    danger?: boolean;
    divider?: boolean;
}

const props = defineProps<{
    visible: boolean;
    x: number;
    y: number;
    sectionId: string | null;
    sectionType?: string;
    canMoveUp?: boolean;
    canMoveDown?: boolean;
    hasClipboard?: boolean;
    clipboardType?: string | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'action', action: string, sectionId: string | null): void;
}>();

const menuRef = ref<HTMLElement | null>(null);

// Adjust position to keep menu in viewport
const adjustedPosition = computed(() => {
    let x = props.x;
    let y = props.y;
    
    // Menu dimensions (approximate)
    const menuWidth = 220;
    const menuHeight = 350;
    
    // Viewport dimensions
    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;
    
    // Adjust X if menu would overflow right
    if (x + menuWidth > viewportWidth) {
        x = viewportWidth - menuWidth - 10;
    }
    
    // Adjust Y if menu would overflow bottom
    if (y + menuHeight > viewportHeight) {
        y = viewportHeight - menuHeight - 10;
    }
    
    // Ensure minimum values
    x = Math.max(10, x);
    y = Math.max(10, y);
    
    return { x, y };
});

// Menu items based on context
const menuItems = computed((): MenuItem[] => {
    const items: MenuItem[] = [];
    
    if (props.sectionId) {
        // Section-specific actions
        items.push(
            { id: 'edit', label: 'Edit Section', icon: Cog6ToothIcon, shortcut: 'Enter' },
            { id: 'style', label: 'Edit Style', icon: PaintBrushIcon },
            { id: 'divider1', label: '', divider: true },
            { id: 'copy', label: 'Copy', icon: ClipboardDocumentIcon, shortcut: 'Ctrl+C' },
            { id: 'cut', label: 'Cut', icon: ScissorsIcon, shortcut: 'Ctrl+X' },
            { id: 'paste', label: 'Paste', icon: ClipboardDocumentCheckIcon, shortcut: 'Ctrl+V', disabled: !props.hasClipboard },
            { id: 'duplicate', label: 'Duplicate', icon: DocumentDuplicateIcon, shortcut: 'Ctrl+D' },
            { id: 'divider2', label: '', divider: true },
            { id: 'moveUp', label: 'Move Up', icon: ArrowUpIcon, shortcut: '↑', disabled: !props.canMoveUp },
            { id: 'moveDown', label: 'Move Down', icon: ArrowDownIcon, shortcut: '↓', disabled: !props.canMoveDown },
            { id: 'divider3', label: '', divider: true },
            { id: 'delete', label: 'Delete', icon: TrashIcon, shortcut: 'Del', danger: true },
        );
    } else {
        // Canvas context menu (no section selected)
        items.push(
            { id: 'paste', label: 'Paste Section', icon: ClipboardDocumentCheckIcon, shortcut: 'Ctrl+V', disabled: !props.hasClipboard },
            { id: 'addSection', label: 'Add Section', icon: DocumentDuplicateIcon },
        );
    }
    
    return items;
});

// Handle click outside to close
const handleClickOutside = (e: MouseEvent) => {
    if (menuRef.value && !menuRef.value.contains(e.target as Node)) {
        emit('close');
    }
};

// Handle escape key
const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Escape') {
        emit('close');
    }
};

// Handle menu item click
const handleAction = (item: MenuItem) => {
    if (item.disabled || item.divider) return;
    emit('action', item.id, props.sectionId);
    emit('close');
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    document.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-100 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-75 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="visible"
                ref="menuRef"
                class="fixed z-[9999] min-w-[200px] bg-white rounded-lg shadow-xl border border-gray-200 py-1 overflow-hidden"
                :style="{ left: `${adjustedPosition.x}px`, top: `${adjustedPosition.y}px` }"
                @contextmenu.prevent
            >
                <!-- Section type header -->
                <div v-if="sectionId && sectionType" class="px-3 py-2 border-b border-gray-100">
                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                        {{ sectionType }} Section
                    </span>
                </div>
                
                <!-- Menu items -->
                <template v-for="item in menuItems" :key="item.id">
                    <!-- Divider -->
                    <div v-if="item.divider" class="my-1 border-t border-gray-100"></div>
                    
                    <!-- Menu item -->
                    <button
                        v-else
                        @click="handleAction(item)"
                        :disabled="item.disabled"
                        :class="[
                            'w-full flex items-center gap-3 px-3 py-2 text-sm text-left transition-colors',
                            item.disabled
                                ? 'text-gray-300 cursor-not-allowed'
                                : item.danger
                                    ? 'text-red-600 hover:bg-red-50'
                                    : 'text-gray-700 hover:bg-gray-50'
                        ]"
                    >
                        <component
                            v-if="item.icon"
                            :is="item.icon"
                            class="w-4 h-4 flex-shrink-0"
                            :class="item.disabled ? 'text-gray-300' : item.danger ? 'text-red-500' : 'text-gray-400'"
                            aria-hidden="true"
                        />
                        <span class="flex-1">{{ item.label }}</span>
                        <span v-if="item.shortcut" class="text-xs text-gray-400">{{ item.shortcut }}</span>
                    </button>
                </template>
                
                <!-- Clipboard info -->
                <div v-if="hasClipboard && clipboardType" class="px-3 py-2 border-t border-gray-100 bg-gray-50">
                    <span class="text-xs text-gray-500">
                        <ClipboardDocumentCheckIcon class="w-3 h-3 inline mr-1" aria-hidden="true" />
                        {{ clipboardType }} in clipboard
                    </span>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
