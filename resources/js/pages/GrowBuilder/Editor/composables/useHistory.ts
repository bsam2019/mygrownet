/**
 * History Management Composable (Undo/Redo)
 * Implements command pattern for tracking changes
 */
import { ref, computed } from 'vue';
import type { Section, NavigationSettings, FooterSettings } from '../types';

interface HistoryState {
    sections: Section[];
    navigation: NavigationSettings;
    footer: FooterSettings;
    timestamp: number;
}

interface UseHistoryOptions {
    maxHistory?: number;
}

export function useHistory(options: UseHistoryOptions = {}) {
    const { maxHistory = 50 } = options;
    
    // History stacks
    const undoStack = ref<HistoryState[]>([]);
    const redoStack = ref<HistoryState[]>([]);
    
    // Current state reference (will be set by the editor)
    const currentState = ref<HistoryState | null>(null);
    
    // Track if we're in the middle of an undo/redo operation
    const isUndoRedoOperation = ref(false);
    
    // Computed properties
    const canUndo = computed(() => undoStack.value.length > 0);
    const canRedo = computed(() => redoStack.value.length > 0);
    const undoCount = computed(() => undoStack.value.length);
    const redoCount = computed(() => redoStack.value.length);
    
    /**
     * Deep clone a state object
     */
    const cloneState = (state: HistoryState): HistoryState => {
        return JSON.parse(JSON.stringify(state));
    };
    
    /**
     * Create a state snapshot from current data
     */
    const createSnapshot = (
        sections: Section[],
        navigation: NavigationSettings,
        footer: FooterSettings
    ): HistoryState => {
        return {
            sections: JSON.parse(JSON.stringify(sections)),
            navigation: JSON.parse(JSON.stringify(navigation)),
            footer: JSON.parse(JSON.stringify(footer)),
            timestamp: Date.now(),
        };
    };
    
    /**
     * Initialize history with initial state
     */
    const initHistory = (
        sections: Section[],
        navigation: NavigationSettings,
        footer: FooterSettings
    ) => {
        currentState.value = createSnapshot(sections, navigation, footer);
        undoStack.value = [];
        redoStack.value = [];
    };
    
    /**
     * Push current state to history before making changes
     * Call this BEFORE modifying the state
     */
    const pushState = (
        sections: Section[],
        navigation: NavigationSettings,
        footer: FooterSettings
    ) => {
        // Don't push if we're in an undo/redo operation
        if (isUndoRedoOperation.value) return;
        
        // Save current state to undo stack
        if (currentState.value) {
            undoStack.value.push(cloneState(currentState.value));
            
            // Limit history size
            if (undoStack.value.length > maxHistory) {
                undoStack.value.shift();
            }
        }
        
        // Update current state
        currentState.value = createSnapshot(sections, navigation, footer);
        
        // Clear redo stack when new changes are made
        redoStack.value = [];
    };
    
    /**
     * Undo the last change
     * Returns the state to restore, or null if nothing to undo
     */
    const undo = (): HistoryState | null => {
        if (!canUndo.value || !currentState.value) return null;
        
        isUndoRedoOperation.value = true;
        
        // Push current state to redo stack
        redoStack.value.push(cloneState(currentState.value));
        
        // Pop from undo stack
        const previousState = undoStack.value.pop()!;
        currentState.value = cloneState(previousState);
        
        isUndoRedoOperation.value = false;
        
        return previousState;
    };
    
    /**
     * Redo the last undone change
     * Returns the state to restore, or null if nothing to redo
     */
    const redo = (): HistoryState | null => {
        if (!canRedo.value || !currentState.value) return null;
        
        isUndoRedoOperation.value = true;
        
        // Push current state to undo stack
        undoStack.value.push(cloneState(currentState.value));
        
        // Pop from redo stack
        const nextState = redoStack.value.pop()!;
        currentState.value = cloneState(nextState);
        
        isUndoRedoOperation.value = false;
        
        return nextState;
    };
    
    /**
     * Clear all history
     */
    const clearHistory = () => {
        undoStack.value = [];
        redoStack.value = [];
    };
    
    /**
     * Check if state has changed from initial
     */
    const hasChanges = computed(() => {
        return undoStack.value.length > 0 || redoStack.value.length > 0;
    });
    
    return {
        // State
        canUndo,
        canRedo,
        undoCount,
        redoCount,
        hasChanges,
        isUndoRedoOperation,
        
        // Methods
        initHistory,
        pushState,
        undo,
        redo,
        clearHistory,
        createSnapshot,
    };
}
