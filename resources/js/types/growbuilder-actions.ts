/**
 * GrowBuilder Editor Action Types
 * 
 * These types define the structure of AI-generated actions that can be
 * applied to the editor. Actions bridge AI responses and editor updates.
 */

export type EditorActionType =
  | 'add_section'
  | 'modify_section'
  | 'replace_content'
  | 'change_style'
  | 'reorder_sections'
  | 'update_settings'
  | 'delete_section';

export interface EditorAction {
  id: string;
  type: EditorActionType;
  target: string; // section ID, 'page', or 'site'
  payload: Record<string, any>;
  requires_confirmation: boolean;
  preview?: string;
  reversible: boolean;
  timestamp: string;
}

export interface EditorActionBatch {
  id: string;
  actions: EditorAction[];
  timestamp: string;
  auto_apply_count: number;
  confirmation_required_count: number;
}

export interface AIResponse {
  success: boolean;
  message: string;
  actions: EditorAction[];
  context?: EditorSessionContext;
  usage?: AIUsageStats;
}

export interface EditorSessionContext {
  business_type: string;
  business_name: string;
  target_audience?: string;
  location: string;
  language: string;
  site_id: number;
  subdomain: string;
  current_page?: {
    id: number;
    title: string;
    section_count: number;
  };
  existing_sections: Array<{
    id: string;
    type: string;
    has_content: boolean;
  }>;
  theme: Record<string, any>;
  style_decisions: Record<string, any>;
  conversation: ConversationMessage[];
  last_ai_action?: {
    action: EditorAction;
    timestamp: string;
  };
  user_preferences: {
    preferred_tone: string;
    preferred_language: string;
    design_style: string;
  };
  initialized_at: string;
  last_updated: string;
}

export interface ConversationMessage {
  role: 'user' | 'assistant' | 'system';
  content: string;
  timestamp: string;
}

export interface AIUsageStats {
  limit: number;
  used: number;
  remaining: number;
  is_unlimited: boolean;
  percentage: number;
  month: string;
  features: string[];
  has_priority: boolean;
}

/**
 * Action payload types for specific action types
 */

export interface AddSectionPayload {
  section: {
    id: string;
    type: string;
    content: Record<string, any>;
    styles?: Record<string, any>;
  };
  position?: number;
}

export interface ModifySectionPayload {
  changes: Record<string, any>;
}

export interface ReplaceContentPayload {
  element_path: string;
  new_content: string;
}

export interface ChangeStylePayload {
  styles: Record<string, any>;
}

export interface ReorderSectionsPayload {
  order: string[];
}

export interface UpdateSettingsPayload {
  path: string;
  value: any;
}

/**
 * Helper type guards
 */

export function isAddSectionAction(action: EditorAction): action is EditorAction & { payload: AddSectionPayload } {
  return action.type === 'add_section';
}

export function isModifySectionAction(action: EditorAction): action is EditorAction & { payload: ModifySectionPayload } {
  return action.type === 'modify_section';
}

export function isReplaceContentAction(action: EditorAction): action is EditorAction & { payload: ReplaceContentPayload } {
  return action.type === 'replace_content';
}

export function isChangeStyleAction(action: EditorAction): action is EditorAction & { payload: ChangeStylePayload } {
  return action.type === 'change_style';
}

export function isReorderSectionsAction(action: EditorAction): action is EditorAction & { payload: ReorderSectionsPayload } {
  return action.type === 'reorder_sections';
}

export function isUpdateSettingsAction(action: EditorAction): action is EditorAction & { payload: UpdateSettingsPayload } {
  return action.type === 'update_settings';
}

export function isDeleteSectionAction(action: EditorAction): action is EditorAction {
  return action.type === 'delete_section';
}
