<script setup lang="ts">
/**
 * Rich Text Editor Component
 * Simple but powerful text editor for content blocks
 */
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';
import {
    BoldIcon,
    ItalicIcon,
    UnderlineIcon,
    ListBulletIcon,
    LinkIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps<{
    modelValue: string;
    placeholder?: string;
    minHeight?: number;
    maxHeight?: number;
    darkMode?: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
    (e: 'blur'): void;
    (e: 'focus'): void;
}>();

const editorRef = ref<HTMLDivElement | null>(null);
const isFocused = ref(false);
const showLinkModal = ref(false);
const linkUrl = ref('');
const linkText = ref('');
const savedSelection = ref<Range | null>(null);

// Initialize editor content
onMounted(() => {
    if (editorRef.value) {
        editorRef.value.innerHTML = props.modelValue || '';
    }
});

// Watch for external changes
watch(() => props.modelValue, (newValue) => {
    if (editorRef.value && editorRef.value.innerHTML !== newValue) {
        editorRef.value.innerHTML = newValue || '';
    }
});

// Handle input
const handleInput = () => {
    if (editorRef.value) {
        emit('update:modelValue', editorRef.value.innerHTML);
    }
};

// Execute formatting command
const execCommand = (command: string, value?: string) => {
    document.execCommand(command, false, value);
    editorRef.value?.focus();
    handleInput();
};

// Format buttons
const formatBold = () => execCommand('bold');
const formatItalic = () => execCommand('italic');
const formatUnderline = () => execCommand('underline');
const formatList = () => execCommand('insertUnorderedList');
const formatOrderedList = () => execCommand('insertOrderedList');

// Check if format is active
const isFormatActive = (command: string): boolean => {
    return document.queryCommandState(command);
};

// Link handling
const saveSelection = () => {
    const selection = window.getSelection();
    if (selection && selection.rangeCount > 0) {
        savedSelection.value = selection.getRangeAt(0).cloneRange();
        linkText.value = selection.toString();
    }
};

const openLinkModal = () => {
    saveSelection();
    linkUrl.value = '';
    showLinkModal.value = true;
};

const insertLink = () => {
    if (!linkUrl.value) return;
    
    // Restore selection
    if (savedSelection.value) {
        const selection = window.getSelection();
        selection?.removeAllRanges();
        selection?.addRange(savedSelection.value);
    }
    
    // Insert link
    if (linkText.value) {
        execCommand('insertHTML', `<a href="${linkUrl.value}" target="_blank" class="text-blue-600 underline">${linkText.value}</a>`);
    } else {
        execCommand('createLink', linkUrl.value);
    }
    
    showLinkModal.value = false;
    linkUrl.value = '';
    linkText.value = '';
};

const removeLink = () => {
    execCommand('unlink');
};

// Handle paste - strip formatting
const handlePaste = (e: ClipboardEvent) => {
    e.preventDefault();
    const text = e.clipboardData?.getData('text/plain') || '';
    document.execCommand('insertText', false, text);
    handleInput();
};

// Handle focus/blur
const handleFocus = () => {
    isFocused.value = true;
    emit('focus');
};

const handleBlur = () => {
    isFocused.value = false;
    emit('blur');
};

// Keyboard shortcuts
const handleKeydown = (e: KeyboardEvent) => {
    if (e.ctrlKey || e.metaKey) {
        switch (e.key.toLowerCase()) {
            case 'b':
                e.preventDefault();
                formatBold();
                break;
            case 'i':
                e.preventDefault();
                formatItalic();
                break;
            case 'u':
                e.preventDefault();
                formatUnderline();
                break;
            case 'k':
                e.preventDefault();
                openLinkModal();
                break;
        }
    }
};
</script>

<template>
    <div :class="['rich-text-editor rounded-lg border transition-all', 
        darkMode ? 'bg-gray-700 border-gray-600' : 'bg-white border-gray-300',
        isFocused ? (darkMode ? 'ring-2 ring-blue-500 border-blue-500' : 'ring-2 ring-blue-500 border-blue-500') : ''
    ]">
        <!-- Toolbar -->
        <div :class="['flex items-center gap-1 px-2 py-1.5 border-b', darkMode ? 'border-gray-600 bg-gray-800' : 'border-gray-200 bg-gray-50']">
            <button
                type="button"
                @click="formatBold"
                :class="['p-1.5 rounded transition-colors', 
                    isFormatActive('bold') 
                        ? (darkMode ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-700')
                        : (darkMode ? 'text-gray-300 hover:bg-gray-700' : 'text-gray-600 hover:bg-gray-200')
                ]"
                title="Bold (Ctrl+B)"
            >
                <BoldIcon class="w-4 h-4" aria-hidden="true" />
            </button>
            <button
                type="button"
                @click="formatItalic"
                :class="['p-1.5 rounded transition-colors', 
                    isFormatActive('italic') 
                        ? (darkMode ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-700')
                        : (darkMode ? 'text-gray-300 hover:bg-gray-700' : 'text-gray-600 hover:bg-gray-200')
                ]"
                title="Italic (Ctrl+I)"
            >
                <ItalicIcon class="w-4 h-4" aria-hidden="true" />
            </button>
            <button
                type="button"
                @click="formatUnderline"
                :class="['p-1.5 rounded transition-colors', 
                    isFormatActive('underline') 
                        ? (darkMode ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-700')
                        : (darkMode ? 'text-gray-300 hover:bg-gray-700' : 'text-gray-600 hover:bg-gray-200')
                ]"
                title="Underline (Ctrl+U)"
            >
                <UnderlineIcon class="w-4 h-4" aria-hidden="true" />
            </button>
            
            <div :class="['w-px h-5 mx-1', darkMode ? 'bg-gray-600' : 'bg-gray-300']"></div>
            
            <button
                type="button"
                @click="formatList"
                :class="['p-1.5 rounded transition-colors', 
                    isFormatActive('insertUnorderedList') 
                        ? (darkMode ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-700')
                        : (darkMode ? 'text-gray-300 hover:bg-gray-700' : 'text-gray-600 hover:bg-gray-200')
                ]"
                title="Bullet List"
            >
                <ListBulletIcon class="w-4 h-4" aria-hidden="true" />
            </button>
            
            <div :class="['w-px h-5 mx-1', darkMode ? 'bg-gray-600' : 'bg-gray-300']"></div>
            
            <button
                type="button"
                @click="openLinkModal"
                :class="['p-1.5 rounded transition-colors', darkMode ? 'text-gray-300 hover:bg-gray-700' : 'text-gray-600 hover:bg-gray-200']"
                title="Insert Link (Ctrl+K)"
            >
                <LinkIcon class="w-4 h-4" aria-hidden="true" />
            </button>
        </div>
        
        <!-- Editor Area -->
        <div
            ref="editorRef"
            contenteditable="true"
            :class="['px-3 py-2 outline-none prose prose-sm max-w-none', 
                darkMode ? 'text-white prose-invert' : 'text-gray-900'
            ]"
            :style="{ 
                minHeight: (minHeight || 100) + 'px', 
                maxHeight: (maxHeight || 300) + 'px',
                overflowY: 'auto'
            }"
            :data-placeholder="placeholder || 'Enter text...'"
            @input="handleInput"
            @paste="handlePaste"
            @focus="handleFocus"
            @blur="handleBlur"
            @keydown="handleKeydown"
        ></div>
        
        <!-- Link Modal -->
        <Teleport to="body">
            <div v-if="showLinkModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click="showLinkModal = false">
                <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-4" @click.stop>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-900">Insert Link</h3>
                        <button @click="showLinkModal = false" class="p-1 hover:bg-gray-100 rounded" aria-label="Close">
                            <XMarkIcon class="w-5 h-5 text-gray-500" aria-hidden="true" />
                        </button>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">URL</label>
                            <input
                                v-model="linkUrl"
                                type="url"
                                placeholder="https://example.com"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                @keydown.enter="insertLink"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Text (optional)</label>
                            <input
                                v-model="linkText"
                                type="text"
                                placeholder="Link text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                @keydown.enter="insertLink"
                            />
                        </div>
                    </div>
                    <div class="flex gap-2 mt-4">
                        <button
                            type="button"
                            @click="showLinkModal = false"
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            @click="insertLink"
                            :disabled="!linkUrl"
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Insert
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<style scoped>
.rich-text-editor [contenteditable]:empty:before {
    content: attr(data-placeholder);
    color: #9ca3af;
    pointer-events: none;
}

.rich-text-editor [contenteditable] :deep(a) {
    color: #2563eb;
    text-decoration: underline;
}

.rich-text-editor [contenteditable] :deep(ul) {
    list-style-type: disc;
    padding-left: 1.5rem;
    margin: 0.5rem 0;
}

.rich-text-editor [contenteditable] :deep(ol) {
    list-style-type: decimal;
    padding-left: 1.5rem;
    margin: 0.5rem 0;
}
</style>
