<?php

namespace App\Services\GrowBuilder;

use App\Models\User;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Editor Session Context
 * 
 * Maintains persistent context throughout an editing session.
 * This allows AI to remember previous decisions, understand the site's
 * purpose, and provide more relevant suggestions.
 * 
 * Context is stored in cache with a 2-hour TTL and includes:
 * - Business information
 * - Current page state
 * - Conversation history
 * - User preferences
 * - Style decisions
 */
class EditorSessionContext
{
    private string $sessionKey;
    private array $context = [];
    private const CACHE_TTL = 7200; // 2 hours
    private const MAX_CONVERSATION_HISTORY = 20; // Last 10 exchanges (20 messages)
    
    public function __construct(
        private int $siteId,
        private int $userId
    ) {
        $this->sessionKey = "editor_context:{$this->siteId}:{$this->userId}";
        $this->load();
    }
    
    /**
     * Initialize context from site and user data
     */
    public function initialize(GrowBuilderSite $site, User $user): void
    {
        $this->context = [
            // Business context
            'business_type' => $site->business_type ?? 'business',
            'business_name' => $site->name,
            'target_audience' => $site->target_audience ?? null,
            'location' => $site->location ?? 'Zambia',
            'language' => $site->primary_language ?? 'en',
            
            // Site state
            'site_id' => $site->id,
            'subdomain' => $site->subdomain,
            'current_page' => null,
            'existing_sections' => [],
            'page_count' => $site->pages()->count(),
            
            // Theme and style
            'theme' => $site->theme ?? [],
            'style_decisions' => [],
            
            // Conversation
            'conversation' => [],
            'last_ai_action' => null,
            
            // User preferences
            'user_preferences' => $this->loadUserPreferences($user),
            
            // Metadata
            'initialized_at' => now()->toIso8601String(),
            'last_updated' => now()->toIso8601String(),
        ];
        
        $this->save();
    }
    
    /**
     * Load context from cache
     */
    private function load(): void
    {
        $cached = Cache::get($this->sessionKey);
        
        if ($cached && is_array($cached)) {
            $this->context = $cached;
        }
    }
    
    /**
     * Save context to cache
     */
    public function save(): void
    {
        Cache::put($this->sessionKey, $this->context, self::CACHE_TTL);
    }
    
    /**
     * Update a specific context value
     */
    public function update(string $key, mixed $value): void
    {
        $this->context[$key] = $value;
        $this->context['last_updated'] = now()->toIso8601String();
        $this->save();
    }
    
    /**
     * Get a context value
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->context[$key] ?? $default;
    }
    
    /**
     * Get all context
     */
    public function all(): array
    {
        return $this->context;
    }
    
    /**
     * Add a message to conversation history
     */
    public function addToConversation(string $role, string $content): void
    {
        if (!isset($this->context['conversation'])) {
            $this->context['conversation'] = [];
        }
        
        $this->context['conversation'][] = [
            'role' => $role,
            'content' => $content,
            'timestamp' => now()->toIso8601String(),
        ];
        
        // Trim to last N messages to manage token costs
        if (count($this->context['conversation']) > self::MAX_CONVERSATION_HISTORY) {
            $this->context['conversation'] = array_slice(
                $this->context['conversation'],
                -self::MAX_CONVERSATION_HISTORY
            );
        }
        
        $this->save();
    }
    
    /**
     * Get conversation history
     */
    public function getConversation(): array
    {
        return $this->context['conversation'] ?? [];
    }
    
    /**
     * Clear conversation history (useful for starting fresh)
     */
    public function clearConversation(): void
    {
        $this->context['conversation'] = [];
        $this->save();
    }
    
    /**
     * Update current page context
     */
    public function setCurrentPage(int $pageId, string $pageTitle, array $sections): void
    {
        $this->context['current_page'] = [
            'id' => $pageId,
            'title' => $pageTitle,
            'section_count' => count($sections),
        ];
        
        $this->context['existing_sections'] = array_map(function($section) {
            return [
                'id' => $section['id'] ?? null,
                'type' => $section['type'] ?? 'unknown',
                'has_content' => !empty($section['content'] ?? []),
            ];
        }, $sections);
        
        $this->save();
    }
    
    /**
     * Record a style decision (for learning user preferences)
     */
    public function recordStyleDecision(string $decision, mixed $value): void
    {
        if (!isset($this->context['style_decisions'])) {
            $this->context['style_decisions'] = [];
        }
        
        $this->context['style_decisions'][$decision] = [
            'value' => $value,
            'timestamp' => now()->toIso8601String(),
        ];
        
        $this->save();
    }
    
    /**
     * Record last AI action (for undo context)
     */
    public function recordAIAction(array $action): void
    {
        $this->context['last_ai_action'] = [
            'action' => $action,
            'timestamp' => now()->toIso8601String(),
        ];
        
        $this->save();
    }
    
    /**
     * Convert context to a prompt-friendly format for AI
     */
    public function toPromptContext(): string
    {
        $promptContext = [
            'business' => [
                'name' => $this->context['business_name'] ?? 'Unknown',
                'type' => $this->context['business_type'] ?? 'business',
                'location' => $this->context['location'] ?? 'Zambia',
                'language' => $this->context['language'] ?? 'en',
            ],
        ];
        
        if (!empty($this->context['current_page'])) {
            $promptContext['current_page'] = $this->context['current_page'];
        }
        
        if (!empty($this->context['existing_sections'])) {
            $promptContext['existing_sections'] = array_map(
                fn($s) => $s['type'],
                $this->context['existing_sections']
            );
        }
        
        if (!empty($this->context['style_decisions'])) {
            $promptContext['style_preferences'] = array_map(
                fn($d) => $d['value'],
                $this->context['style_decisions']
            );
        }
        
        return json_encode($promptContext, JSON_PRETTY_PRINT);
    }
    
    /**
     * Get conversation history formatted for AI prompt
     */
    public function getConversationForPrompt(): array
    {
        return array_map(function($message) {
            return [
                'role' => $message['role'],
                'content' => $message['content'],
            ];
        }, $this->context['conversation'] ?? []);
    }
    
    /**
     * Load user preferences from database or cache
     */
    private function loadUserPreferences(User $user): array
    {
        // TODO: Load from user settings table
        return [
            'preferred_tone' => 'professional',
            'preferred_language' => $user->language ?? 'en',
            'design_style' => 'modern',
        ];
    }
    
    /**
     * Check if context is initialized
     */
    public function isInitialized(): bool
    {
        return !empty($this->context) && isset($this->context['initialized_at']);
    }
    
    /**
     * Clear all context (useful for testing or resetting)
     */
    public function clear(): void
    {
        Cache::forget($this->sessionKey);
        $this->context = [];
    }
    
    /**
     * Get context age in minutes
     */
    public function getAgeInMinutes(): int
    {
        if (!isset($this->context['initialized_at'])) {
            return 0;
        }
        
        $initialized = \Carbon\Carbon::parse($this->context['initialized_at']);
        return $initialized->diffInMinutes(now());
    }
    
    /**
     * Check if context is stale (older than 2 hours)
     */
    public function isStale(): bool
    {
        return $this->getAgeInMinutes() > 120;
    }
}
