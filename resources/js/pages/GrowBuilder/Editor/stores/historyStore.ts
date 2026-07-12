import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import type { Section, NavigationSettings, FooterSettings } from '../types';

interface HistoryState {
    sections: Section[];
    navigation: NavigationSettings;
    footer: FooterSettings;
    timestamp: number;
}

export const useHistoryStore = defineStore('editor-history', () => {
    const maxHistory = 50;
    const undoStack = ref<HistoryState[]>([]);
    const redoStack = ref<HistoryState[]>([]);
    const currentState = ref<HistoryState | null>(null);
    const isUndoRedoOperation = ref(false);

    const canUndo = computed(() => undoStack.value.length > 0);
    const canRedo = computed(() => redoStack.value.length > 0);
    const undoCount = computed(() => undoStack.value.length);
    const redoCount = computed(() => redoStack.value.length);
    const hasChanges = computed(() => undoStack.value.length > 0 || redoStack.value.length > 0);

    function cloneState(state: HistoryState): HistoryState {
        return JSON.parse(JSON.stringify(state));
    }

    function createSnapshot(
        sections: Section[],
        navigation: NavigationSettings,
        footer: FooterSettings
    ): HistoryState {
        return {
            sections: JSON.parse(JSON.stringify(sections)),
            navigation: JSON.parse(JSON.stringify(navigation)),
            footer: JSON.parse(JSON.stringify(footer)),
            timestamp: Date.now(),
        };
    }

    function initHistory(
        sections: Section[],
        navigation: NavigationSettings,
        footer: FooterSettings
    ) {
        currentState.value = createSnapshot(sections, navigation, footer);
        undoStack.value = [];
        redoStack.value = [];
    }

    function pushState(
        sections: Section[],
        navigation: NavigationSettings,
        footer: FooterSettings
    ) {
        if (isUndoRedoOperation.value) return;

        if (currentState.value) {
            undoStack.value.push(cloneState(currentState.value));
            if (undoStack.value.length > maxHistory) {
                undoStack.value.shift();
            }
        }
        currentState.value = createSnapshot(sections, navigation, footer);
        redoStack.value = [];
    }

    function undo(): HistoryState | null {
        if (!canUndo.value || !currentState.value) return null;
        isUndoRedoOperation.value = true;
        redoStack.value.push(cloneState(currentState.value));
        const previousState = undoStack.value.pop()!;
        currentState.value = cloneState(previousState);
        isUndoRedoOperation.value = false;
        return previousState;
    }

    function redo(): HistoryState | null {
        if (!canRedo.value || !currentState.value) return null;
        isUndoRedoOperation.value = true;
        undoStack.value.push(cloneState(currentState.value));
        const nextState = redoStack.value.pop()!;
        currentState.value = cloneState(nextState);
        isUndoRedoOperation.value = false;
        return nextState;
    }

    function clearHistory() {
        undoStack.value = [];
        redoStack.value = [];
    }

    return {
        canUndo, canRedo, undoCount, redoCount, hasChanges, isUndoRedoOperation,
        initHistory, pushState, undo, redo, clearHistory, createSnapshot,
    };
});
