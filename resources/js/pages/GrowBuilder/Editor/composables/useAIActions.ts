import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { route } from 'ziggy-js';
import type {
  EditorAction,
  AIResponse,
  EditorSessionContext,
  ConversationMessage,
} from '@/types/growbuilder-actions';

/**
 * Composable for AI Actions in GrowBuilder Editor
 * 
 * This composable provides:
 * - Session context management
 * - AI chat with action generation
 * - Action application to editor state
 * - Conversation history
 * 
 * Usage:
 * ```ts
 * const { sendMessage, applyAction, context, conversation } = useAIActions(siteId);
 * await sendMessage('Add a hero section');
 * ```
 */
export function useAIActions(siteId: number) {
  const context = ref<EditorSessionContext | null>(null);
  const conversation = ref<ConversationMessage[]>([]);
  const loading = ref(false);
  const error = ref<string | null>(null);
  const pendingActions = ref<EditorAction[]>([]);
  
  /**
   * Initialize or load session context
   */
  async function loadContext(): Promise<void> {
    try {
      const response = await axios.get(route('growbuilder.ai.context', { siteId }));
      
      if (response.data.success) {
        context.value = response.data.context;
        conversation.value = response.data.context.conversation || [];
      }
    } catch (err: any) {
      console.error('Failed to load AI context:', err);
      error.value = err.response?.data?.message || 'Failed to load context';
    }
  }
  
  /**
   * Update a specific context value
   */
  async function updateContext(key: string, value: any): Promise<void> {
    try {
      const response = await axios.post(route('growbuilder.ai.context.update', { siteId }), {
        key,
        value,
      });
      
      if (response.data.success) {
        context.value = response.data.context;
      }
    } catch (err: any) {
      console.error('Failed to update context:', err);
    }
  }
  
  /**
   * Send a message to AI and get response with actions
   */
  async function sendMessage(
    message: string,
    currentPageId?: number,
    currentSections?: any[]
  ): Promise<AIResponse | null> {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await axios.post(route('growbuilder.ai.chat', { siteId }), {
        message,
        current_page_id: currentPageId,
        current_sections: currentSections,
      });
      
      if (response.data.success) {
        // Update local state
        context.value = response.data.context;
        conversation.value = response.data.context.conversation || [];
        
        // Store pending actions
        if (response.data.actions && response.data.actions.length > 0) {
          pendingActions.value = response.data.actions;
        }
        
        return response.data as AIResponse;
      }
      
      return null;
    } catch (err: any) {
      console.error('AI chat error:', err);
      error.value = err.response?.data?.message || 'Failed to send message';
      return null;
    } finally {
      loading.value = false;
    }
  }
  
  /**
   * Apply an action to the editor
   * 
   * This function:
   * 1. Applies the action to the editor state (reactive)
   * 2. Logs the action to the backend for analytics
   * 3. Updates the undo history
   */
  async function applyAction(
    action: EditorAction,
    editorState: any,
    onApply: (action: EditorAction) => void
  ): Promise<boolean> {
    try {
      // Apply to editor state (frontend)
      onApply(action);
      
      // Log to backend (fire and forget)
      axios.post(route('growbuilder.ai.apply-action', { siteId }), {
        action,
      }).catch(err => {
        console.warn('Failed to log action:', err);
      });
      
      // Remove from pending actions
      pendingActions.value = pendingActions.value.filter(a => a.id !== action.id);
      
      return true;
    } catch (err: any) {
      console.error('Failed to apply action:', err);
      error.value = err.message || 'Failed to apply action';
      return false;
    }
  }
  
  /**
   * Apply all pending actions
   */
  async function applyAllActions(
    editorState: any,
    onApply: (action: EditorAction) => void
  ): Promise<void> {
    for (const action of pendingActions.value) {
      await applyAction(action, editorState, onApply);
    }
  }
  
  /**
   * Dismiss an action without applying
   */
  function dismissAction(actionId: string): void {
    pendingActions.value = pendingActions.value.filter(a => a.id !== actionId);
  }
  
  /**
   * Clear conversation history
   */
  async function clearConversation(): Promise<void> {
    conversation.value = [];
    // TODO: Add backend endpoint to clear conversation
  }
  
  /**
   * Computed: Actions that should auto-apply
   */
  const autoApplyActions = computed(() => {
    return pendingActions.value.filter(a => !a.requires_confirmation);
  });
  
  /**
   * Computed: Actions that require confirmation
   */
  const confirmationActions = computed(() => {
    return pendingActions.value.filter(a => a.requires_confirmation);
  });
  
  /**
   * Computed: Has pending actions
   */
  const hasPendingActions = computed(() => {
    return pendingActions.value.length > 0;
  });
  
  /**
   * Computed: Context is loaded
   */
  const isContextLoaded = computed(() => {
    return context.value !== null;
  });
  
  return {
    // State
    context,
    conversation,
    loading,
    error,
    pendingActions,
    
    // Computed
    autoApplyActions,
    confirmationActions,
    hasPendingActions,
    isContextLoaded,
    
    // Methods
    loadContext,
    updateContext,
    sendMessage,
    applyAction,
    applyAllActions,
    dismissAction,
    clearConversation,
  };
}
