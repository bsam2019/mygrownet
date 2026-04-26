<?php

namespace App\Services\GrowBuilder;

use Illuminate\Support\Facades\Log;

/**
 * Editor Action Service
 * 
 * Handles the application of AI-generated actions to the editor state.
 * This service bridges AI responses and editor updates, enabling seamless
 * one-click application of AI suggestions.
 */
class EditorActionService
{
    /**
     * Action types supported by the editor
     */
    public const ACTION_ADD_SECTION = 'add_section';
    public const ACTION_MODIFY_SECTION = 'modify_section';
    public const ACTION_REPLACE_CONTENT = 'replace_content';
    public const ACTION_CHANGE_STYLE = 'change_style';
    public const ACTION_REORDER_SECTIONS = 'reorder_sections';
    public const ACTION_UPDATE_SETTINGS = 'update_settings';
    public const ACTION_DELETE_SECTION = 'delete_section';
    
    /**
     * Create an editor action from AI response
     */
    public function createAction(
        string $type,
        string $target,
        array $payload,
        bool $requiresConfirmation = false,
        ?string $preview = null
    ): array {
        return [
            'id' => uniqid('action_', true),
            'type' => $type,
            'target' => $target,
            'payload' => $payload,
            'requires_confirmation' => $requiresConfirmation,
            'preview' => $preview,
            'reversible' => true,
            'timestamp' => now()->toIso8601String(),
        ];
    }
    
    /**
     * Create an "add section" action
     */
    public function createAddSectionAction(
        array $sectionData,
        ?int $position = null,
        bool $requiresConfirmation = false
    ): array {
        return $this->createAction(
            self::ACTION_ADD_SECTION,
            'page',
            [
                'section' => $sectionData,
                'position' => $position,
            ],
            $requiresConfirmation
        );
    }
    
    /**
     * Create a "modify section" action
     */
    public function createModifySectionAction(
        string $sectionId,
        array $changes,
        bool $requiresConfirmation = false
    ): array {
        return $this->createAction(
            self::ACTION_MODIFY_SECTION,
            $sectionId,
            ['changes' => $changes],
            $requiresConfirmation
        );
    }
    
    /**
     * Create a "replace content" action
     */
    public function createReplaceContentAction(
        string $sectionId,
        string $elementPath,
        string $newContent,
        bool $requiresConfirmation = false
    ): array {
        return $this->createAction(
            self::ACTION_REPLACE_CONTENT,
            $sectionId,
            [
                'element_path' => $elementPath,
                'new_content' => $newContent,
            ],
            $requiresConfirmation
        );
    }
    
    /**
     * Create a "change style" action
     */
    public function createChangeStyleAction(
        string $target,
        array $styleChanges,
        bool $requiresConfirmation = false
    ): array {
        return $this->createAction(
            self::ACTION_CHANGE_STYLE,
            $target,
            ['styles' => $styleChanges],
            $requiresConfirmation
        );
    }
    
    /**
     * Create a "reorder sections" action
     */
    public function createReorderSectionsAction(
        array $newOrder,
        bool $requiresConfirmation = true
    ): array {
        return $this->createAction(
            self::ACTION_REORDER_SECTIONS,
            'page',
            ['order' => $newOrder],
            $requiresConfirmation
        );
    }
    
    /**
     * Create an "update settings" action
     */
    public function createUpdateSettingsAction(
        string $settingsPath,
        mixed $value,
        bool $requiresConfirmation = false
    ): array {
        return $this->createAction(
            self::ACTION_UPDATE_SETTINGS,
            'site',
            [
                'path' => $settingsPath,
                'value' => $value,
            ],
            $requiresConfirmation
        );
    }
    
    /**
     * Create a "delete section" action
     */
    public function createDeleteSectionAction(
        string $sectionId,
        bool $requiresConfirmation = true
    ): array {
        return $this->createAction(
            self::ACTION_DELETE_SECTION,
            $sectionId,
            [],
            $requiresConfirmation
        );
    }
    
    /**
     * Validate an action before execution
     */
    public function validateAction(array $action): bool
    {
        // Check required fields
        if (!isset($action['id'], $action['type'], $action['target'], $action['payload'])) {
            Log::warning('Invalid action structure', ['action' => $action]);
            return false;
        }
        
        // Check action type is valid
        $validTypes = [
            self::ACTION_ADD_SECTION,
            self::ACTION_MODIFY_SECTION,
            self::ACTION_REPLACE_CONTENT,
            self::ACTION_CHANGE_STYLE,
            self::ACTION_REORDER_SECTIONS,
            self::ACTION_UPDATE_SETTINGS,
            self::ACTION_DELETE_SECTION,
        ];
        
        if (!in_array($action['type'], $validTypes)) {
            Log::warning('Invalid action type', ['type' => $action['type']]);
            return false;
        }
        
        return true;
    }
    
    /**
     * Determine if an action should auto-apply or require confirmation
     * 
     * Small changes (text edits, color tweaks) auto-apply
     * Large changes (new sections, deletions) require confirmation
     */
    public function shouldAutoApply(array $action): bool
    {
        // Already has explicit confirmation requirement
        if (isset($action['requires_confirmation'])) {
            return !$action['requires_confirmation'];
        }
        
        // Default rules based on action type
        return match($action['type']) {
            self::ACTION_REPLACE_CONTENT => true,  // Text changes auto-apply
            self::ACTION_CHANGE_STYLE => true,     // Style changes auto-apply
            self::ACTION_MODIFY_SECTION => true,   // Section modifications auto-apply
            self::ACTION_ADD_SECTION => false,     // Adding sections requires confirmation
            self::ACTION_DELETE_SECTION => false,  // Deletions require confirmation
            self::ACTION_REORDER_SECTIONS => false, // Reordering requires confirmation
            self::ACTION_UPDATE_SETTINGS => false, // Settings changes require confirmation
            default => false,
        };
    }
    
    /**
     * Create a batch of actions from AI response
     */
    public function createActionBatch(array $actions): array
    {
        $batch = [
            'id' => uniqid('batch_', true),
            'actions' => [],
            'timestamp' => now()->toIso8601String(),
            'auto_apply_count' => 0,
            'confirmation_required_count' => 0,
        ];
        
        foreach ($actions as $action) {
            if ($this->validateAction($action)) {
                $batch['actions'][] = $action;
                
                if ($this->shouldAutoApply($action)) {
                    $batch['auto_apply_count']++;
                } else {
                    $batch['confirmation_required_count']++;
                }
            }
        }
        
        return $batch;
    }
    
    /**
     * Log action execution for analytics and debugging
     */
    public function logActionExecution(
        array $action,
        int $userId,
        int $siteId,
        bool $success,
        ?string $error = null
    ): void {
        Log::info('Editor action executed', [
            'action_id' => $action['id'],
            'action_type' => $action['type'],
            'user_id' => $userId,
            'site_id' => $siteId,
            'success' => $success,
            'error' => $error,
            'auto_applied' => $this->shouldAutoApply($action),
        ]);
        
        // TODO: Store in database for analytics
        // EditorActionLog::create([...]);
    }
}
