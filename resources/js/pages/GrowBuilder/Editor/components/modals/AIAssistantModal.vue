<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 scale-95 translate-y-4"
            enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 scale-100 translate-y-0"
            leave-to-class="opacity-0 scale-95 translate-y-4"
        >
            <div
                v-if="isOpen"
                class="fixed bottom-4 right-4 z-50 w-[400px] max-w-[calc(100vw-2rem)] overflow-hidden rounded-2xl shadow-2xl flex flex-col"
                :class="darkMode ? 'bg-gray-900 border border-gray-800' : 'bg-white border border-gray-200'"
                style="height: 550px; max-height: calc(100vh - 40px);"
            >
                <!-- Header -->
                <AIHeader
                    :dark-mode="darkMode"
                    :is-available="isAvailable"
                    :provider="providerName"
                    :active-view="activeView"
                    :context-summary="contextSummaryText"
                    :ai-usage="aiUsage"
                    :creativity-level="creativityLevel"
                    @close="$emit('close')"
                    @change-view="activeView = $event"
                    @new-chat="clearConversation"
                    @update:creativity-level="updateCreativityLevel"
                />

                <!-- Main Content Area -->
                <div class="flex-1 flex flex-col overflow-hidden">
                    <!-- Chat View -->
                    <div v-if="activeView === 'chat'" class="flex-1 flex flex-col min-h-0">
                        <AIMessageList
                            ref="messageListRef"
                            :messages="messages"
                            :is-typing="isTyping"
                            :dark-mode="darkMode"
                            class="flex-1 min-h-0"
                            @apply-content="handleApplyContent"
                            @dismiss-content="handleDismissContent"
                            @copy="copyToClipboard"
                            @regenerate="regenerateMessage"
                        />

                        <AIQuickActions
                            v-if="messages.length <= 1"
                            :dark-mode="darkMode"
                            :context="quickActionContext"
                            :suggestions="smartSuggestionsList"
                            @action="handleQuickAction"
                        />

                        <AIChatInput
                            v-model="userInput"
                            :dark-mode="darkMode"
                            :is-loading="isLoading"
                            :is-available="isAvailable"
                            :suggestions="inputSuggestions"
                            @send="sendMessage"
                            @suggestion="handleSuggestion"
                        />
                    </div>

                    <!-- Generate View -->
                    <div v-else-if="activeView === 'generate'" class="flex-1 overflow-y-auto">
                        <AIGeneratePanel
                            :dark-mode="darkMode"
                            :site-name="siteName"
                            :business-type="aiContext?.site.businessType"
                            :is-loading="isLoading"
                            :generated-content="generatedContent"
                            @generate="handleGenerate"
                            @apply="handleApplyGenerated"
                        />
                    </div>

                    <!-- Tools View -->
                    <div v-else-if="activeView === 'tools'" class="flex-1 overflow-y-auto">
                        <AIToolsPanel
                            :dark-mode="darkMode"
                            :is-loading="isLoading"
                            :initial-text="initialText"
                            @improve="handleImprove"
                            @translate="handleTranslate"
                            @seo="handleSEO"
                            @colors="handleColors"
                            @apply-colors="$emit('applyColors', $event)"
                        />
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import { useAI } from '../../composables/useAI';
import type { AIContext } from '../../composables/useAIContext';
import AIHeader from './ai/AIHeader.vue';
import AIMessageList from './ai/AIMessageList.vue';
import AIQuickActions from './ai/AIQuickActions.vue';
import AIChatInput from './ai/AIChatInput.vue';
import AIGeneratePanel from './ai/AIGeneratePanel.vue';
import AIToolsPanel from './ai/AIToolsPanel.vue';

interface Message {
    id: string;
    role: 'user' | 'assistant' | 'system';
    content: string;
    timestamp: Date;
    type?: 'text' | 'content' | 'colors' | 'seo' | 'style' | 'section' | 'navigation' | 'footer' | 'page';
    data?: any;
}

interface AIUsage {
    limit: number;
    used: number;
    remaining: number;
    is_unlimited: boolean;
    percentage: number;
    month: string;
    features: string[];
    has_priority: boolean;
}

interface TierRestrictions {
    tier: string;
    tier_name: string;
    sites_limit: number;
    storage_limit: number;
    storage_limit_formatted: string;
    products_limit: number;
    products_unlimited: boolean;
    ai_prompts_limit: number;
    ai_unlimited: boolean;
    features: Record<string, boolean>;
}

const props = defineProps<{
    isOpen: boolean;
    siteId: number;
    siteName: string;
    darkMode?: boolean;
    initialTab?: string;
    initialText?: string;
    aiContext?: AIContext | null;
    contextSummary?: string;
    smartSuggestions?: string[];
    aiUsage?: AIUsage;
    tierRestrictions?: TierRestrictions;
    hasAISectionGenerator?: boolean;
    hasAISEO?: boolean;
}>();

// Compute canUseAI from aiUsage prop (more reactive than receiving it as a prop)
const canUseAI = computed(() => {
    if (!props.aiUsage) return true;
    const result = props.aiUsage.is_unlimited || props.aiUsage.remaining > 0;
    console.log('AIAssistantModal canUseAI:', { 
        aiUsage: props.aiUsage, 
        result 
    });
    return result;
});

const emit = defineEmits<{
    close: [];
    applyContent: [content: any];
    applyColors: [palette: any];
    applyStyle: [style: any];
    addSection: [type: string, content?: any];
    updateNavigation: [changes: any];
    updateFooter: [changes: any];
    createPage: [template: string, title?: string];
    createAIPage: [pageType: string, pageData: any];
    updateUsage: [usage: any];
}>();

const {
    isLoading,
    isAvailable,
    provider,
    checkStatus,
    generateContent,
    generateMeta,
    suggestColors,
    improveText,
    generatePage,
    generatePageDetailed,
    translate,
    classifyIntent,
    smartChat,
    recordFeedback: recordFeedbackToDb,
    getFeedbackStats,
} = useAI(props.siteId);

// State
const activeView = ref<'chat' | 'generate' | 'tools'>('chat');
const messages = ref<Message[]>([]);
const userInput = ref('');
const isTyping = ref(false);
const providerName = ref('');
const generatedContent = ref<any>(null);
const feedbackStats = ref<any>(null);
const messageListRef = ref<any>(null);

// Creativity level state with localStorage persistence
type CreativityLevel = 'guided' | 'balanced' | 'creative';
const creativityLevel = ref<CreativityLevel>(
    (localStorage.getItem('ai_creativity_level') as CreativityLevel) || 'balanced'
);

const updateCreativityLevel = (level: CreativityLevel) => {
    creativityLevel.value = level;
    localStorage.setItem('ai_creativity_level', level);
};

// Conversation persistence key (per site)
const getStorageKey = () => `ai_chat_history_${props.siteId}`;

// Load conversation from localStorage
const loadConversation = () => {
    try {
        const stored = localStorage.getItem(getStorageKey());
        if (stored) {
            const parsed = JSON.parse(stored);
            // Only load if not too old (24 hours)
            const maxAge = 24 * 60 * 60 * 1000;
            if (parsed.timestamp && Date.now() - parsed.timestamp < maxAge) {
                messages.value = parsed.messages.map((m: any) => ({
                    ...m,
                    timestamp: new Date(m.timestamp)
                }));
            }
        }
    } catch (e) {
        console.warn('Failed to load conversation history:', e);
    }
};

// Save conversation to localStorage
const saveConversation = () => {
    try {
        // Only save last 20 messages to avoid storage limits
        const toSave = messages.value.slice(-20).map(m => ({
            ...m,
            timestamp: m.timestamp.toISOString()
        }));
        localStorage.setItem(getStorageKey(), JSON.stringify({
            messages: toSave,
            timestamp: Date.now()
        }));
    } catch (e) {
        console.warn('Failed to save conversation history:', e);
    }
};

// Clear conversation history
const clearConversation = () => {
    messages.value = [];
    localStorage.removeItem(getStorageKey());
};

// Watch messages and save on change
watch(messages, () => {
    if (messages.value.length > 0) {
        saveConversation();
    }
}, { deep: true });

// Context-aware computed properties
const contextSummaryText = computed(() => props.contextSummary || '');
const smartSuggestionsList = computed(() => props.smartSuggestions || []);

const quickActionContext = computed(() => ({
    siteName: props.siteName,
    sectionType: props.aiContext?.selectedSection?.type,
    hasContent: !!props.initialText,
}));

const inputSuggestions = computed(() => {
    const suggestions: string[] = [];
    if (props.aiContext?.selectedSection?.type) {
        suggestions.push(`Improve the ${props.aiContext.selectedSection.type} section`);
    }
    if (props.aiContext?.currentPage) {
        suggestions.push(`Suggest content for ${props.aiContext.currentPage.title}`);
    }
    suggestions.push('Write a compelling headline');
    return suggestions.slice(0, 3);
});

// Helpers
const generateId = () => `msg_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;

const addMessage = (role: Message['role'], content: string, type?: Message['type'], data?: any) => {
    const message: Message = {
        id: generateId(),
        role,
        content,
        timestamp: new Date(),
        type: type || 'text',
        data,
    };
    messages.value.push(message);
    scrollToBottom();
    return message;
};

const scrollToBottom = async () => {
    await nextTick();
    messageListRef.value?.scrollToBottom();
};

// Send message
const sendMessage = async () => {
    if (!userInput.value.trim() || isLoading.value) return;

    // Check if user can use AI (frontend check for better UX)
    if (canUseAI.value === false) {
        addMessage('assistant', '⚠️ You\'ve reached your AI limit for this month. Please upgrade your plan to continue using AI features.', 'text');
        return;
    }

    const input = userInput.value.trim();
    userInput.value = '';
    lastUserMessage.value = input; // Track for feedback

    addMessage('user', input);
    isTyping.value = true;

    try {
        const response = await processUserMessage(input);
        isTyping.value = false;
        addMessage('assistant', response.content, response.type, response.data);
        lastAiResponse.value = response.content; // Track for feedback
        
        // Update usage if provided
        if (response.usage) {
            console.log('Emitting updateUsage with:', response.usage);
            emit('updateUsage', response.usage);
        } else {
            console.warn('No usage data in response');
        }
        
        // Track last action for context
        if (response.type && response.type !== 'text') {
            lastAction.value = response.type;
        }
    } catch (e: any) {
        isTyping.value = false;
        // Check if it's an upgrade required error
        if (e.response?.data?.upgrade_required) {
            addMessage('assistant', `⚠️ ${e.response.data.message || 'AI limit reached. Please upgrade your plan.'}`, 'text');
        } else {
            addMessage('assistant', 'Sorry, I encountered an error. Please try again.');
        }
    }
};

// Track last action for conversation context
const lastAction = ref<string>('');
const lastUserMessage = ref<string>('');
const lastAiResponse = ref<string>('');

// Build conversation history for AI context
const getConversationHistory = (): Array<{ role: string; content: string }> => {
    return messages.value
        .filter(m => m.role !== 'system')
        .slice(-6) // Last 6 messages (3 exchanges)
        .map(m => ({
            role: m.role,
            content: m.content.substring(0, 200) // Truncate for token efficiency
        }));
};

// Track feedback for learning - now persisted to database
const feedbackHistory = ref<Array<{ type: string; applied: boolean; timestamp: Date }>>([]);

// Record feedback when user applies or rejects suggestions - saves to database
const recordFeedback = async (type: string, applied: boolean, sectionType?: string) => {
    // Update local state for immediate context
    feedbackHistory.value.push({
        type,
        applied,
        timestamp: new Date()
    });
    // Keep only last 20 feedback items locally
    if (feedbackHistory.value.length > 20) {
        feedbackHistory.value = feedbackHistory.value.slice(-20);
    }
    
    // Save to database for persistence across sessions and users (includes business_type for global learning)
    await recordFeedbackToDb({
        actionType: type,
        sectionType: sectionType,
        businessType: props.aiContext?.site?.businessType, // For industry-specific global learning
        applied,
        userMessage: lastUserMessage.value,
        aiResponse: lastAiResponse.value?.substring(0, 500),
    });
};

// AI insights from global learning
const aiInsights = ref<string>('');

// Load feedback stats from database on mount - includes global learning
const loadFeedbackStats = async () => {
    try {
        const businessType = props.aiContext?.site?.businessType;
        const data = await getFeedbackStats(businessType);
        feedbackStats.value = data.stats;
        aiInsights.value = data.aiInsights || '';
        // Convert recent feedback to local format
        feedbackHistory.value = data.recent.map((f: any) => ({
            type: f.type,
            applied: f.applied,
            timestamp: new Date(f.timestamp)
        }));
    } catch (e) {
        console.warn('Failed to load feedback stats:', e);
    }
};

// Build rich context for AI - ENHANCED with section content, visual context, and feedback
const buildRichContext = () => {
    const ctx = props.aiContext;
    
    // Get full section content for analysis
    const allSectionsContent = ctx?.currentPage?.sections?.map((s: any) => ({
        type: s.type,
        content: s.content,
        style: s.style
    })) || [];
    
    // Get selected section's full content and style
    const selectedSectionFull = ctx?.selectedSection;
    
    return {
        // Site ID for database lookups
        siteId: props.siteId,
        
        // Basic context
        selectedSection: selectedSectionFull?.type,
        selectedSectionContent: selectedSectionFull?.content || selectedSectionFull?.contentPreview,
        selectedSectionStyle: selectedSectionFull?.style,
        currentPage: ctx?.currentPage?.title,
        
        // Site context
        siteName: props.siteName,
        businessType: ctx?.site?.businessType,
        
        // Page structure with full content for analysis
        pageSections: ctx?.currentPage?.sections?.map((s: any) => s.type) || [],
        allSectionsContent: allSectionsContent,
        sitePages: ctx?.site?.pages?.map((p: any) => p.title) || [],
        
        // Visual/Style context
        siteColors: ctx?.site?.colors,
        
        // Conversation context
        lastAction: lastAction.value,
        conversationHistory: getConversationHistory(),
        
        // Feedback for learning (local session)
        feedbackHistory: feedbackHistory.value,
        
        // Global learning insights from database
        aiInsights: aiInsights.value,
        
        // User preferences (could be stored in localStorage)
        userPreferences: getUserPreferences(),
        
        // Creativity level for AI flexibility
        creativityLevel: creativityLevel.value,
    };
};

// Get user preferences from localStorage
const getUserPreferences = () => {
    try {
        const stored = localStorage.getItem('ai_assistant_preferences');
        return stored ? JSON.parse(stored) : {};
    } catch {
        return {};
    }
};

// Save user preference
const saveUserPreference = (key: string, value: any) => {
    try {
        const prefs = getUserPreferences();
        prefs[key] = value;
        localStorage.setItem('ai_assistant_preferences', JSON.stringify(prefs));
    } catch {
        // Ignore localStorage errors
    }
};

// Process user message with AI-powered smart chat
const processUserMessage = async (input: string): Promise<{ content: string; type: Message['type']; data?: any; usage?: any }> => {
    const lowerInput = input.toLowerCase();
    const ctx = props.aiContext;

    // ============================================
    // AI-FIRST APPROACH: Smart Chat
    // The AI understands intent AND generates content in one call
    // ============================================
    try {
        const richContext = buildRichContext();
        console.log('Calling smartChat with:', { input, contextKeys: Object.keys(richContext) });
        const result = await smartChat(input, richContext);
        console.log('SmartChat result:', result);
        
        if (result && result.action) {
            // Map AI action to message type and format response
            const response = mapSmartChatResponse(result, ctx);
            // Include usage in response
            return { ...response, usage: result.usage };
        }
    } catch (e: any) {
        console.error('Smart chat failed:', e);
        console.error('Error details:', e.response?.data || e.message);
    }

    // ============================================
    // FALLBACK: Keyword-based matching (only if AI fails)
    // ============================================
    return processWithKeywordMatching(input, lowerInput, ctx);
};

// Map smart chat AI response to the format expected by the UI
const mapSmartChatResponse = (
    result: { action: string; message: string; data: Record<string, any> | null },
    ctx: AIContext | null | undefined
): { content: string; type: Message['type']; data?: any } => {
    let { action, message, data } = result;
    
    // Debug: log what we received
    console.log('mapSmartChatResponse input:', { action, messageType: typeof message, messageLength: message?.length, data });
    
    // Handle case where message might be undefined or empty
    if (!message || message.trim() === '') {
        message = "I've processed your request. Let me know if you need anything else.";
    }
    
    // If message looks like JSON (starts with { or contains "action":), try to extract the actual message
    if (typeof message === 'string' && (message.trim().startsWith('{') || message.includes('"action":'))) {
        try {
            const parsed = JSON.parse(message);
            if (parsed.message) {
                message = parsed.message;
                data = parsed.data || data;
                action = parsed.action || action;
                console.log('Extracted message from JSON:', message.substring(0, 100));
            }
        } catch {
            // Not valid JSON - might be truncated or malformed
            // Try to extract just the message field if it exists
            const messageMatch = message.match(/"message"\s*:\s*"([^"]+)/);
            if (messageMatch && messageMatch[1]) {
                message = messageMatch[1];
                // Clean up any trailing incomplete content
                if (message.endsWith('...') || message.match(/\d+\.\s*\*\*[^*]+$/)) {
                    message += '\n\n_(Response was truncated. Ask me to continue if needed.)_';
                }
            } else {
                // Can't extract, provide a helpful fallback
                message = "I've analyzed your request but the response was incomplete. Could you try asking again?";
            }
        }
    }
    
    switch (action) {
        case 'generate_content':
            return {
                content: message + '\n\nClick "Apply" to add this content.',
                type: 'content',
                data: {
                    sectionType: data?.sectionType,
                    content: data?.content,
                    position: data?.position, // Pass position for smart placement
                    style: data?.style // Pass style for matching page theme
                }
            };
        
        case 'create_page':
            return {
                content: message + '\n\nClick "Apply" to create this page.',
                type: 'page',
                data: {
                    pageRequest: {
                        action: 'create-ai-smart',
                        pageType: data?.pageType,
                        title: data?.title,
                        sections: data?.sections,
                        useAI: false // Already generated
                    }
                }
            };
        
        case 'change_style':
            return {
                content: message + '\n\nClick "Apply" to apply these changes.',
                type: 'style',
                data: {
                    styleChange: {
                        ...data?.styleChanges,
                        sectionType: data?.sectionType || ctx?.selectedSection?.type
                    }
                }
            };
        
        case 'update_navigation':
            return {
                content: message + '\n\nClick "Apply" to update navigation.',
                type: 'navigation',
                data: { navChange: data?.navChanges }
            };
        
        case 'update_footer':
            return {
                content: message + '\n\nClick "Apply" to update footer.',
                type: 'footer',
                data: { footerChange: data?.footerChanges }
            };
        
        case 'generate_seo':
            return {
                content: message,
                type: 'seo',
                data: data?.seo
            };
        
        case 'clarify':
            return {
                content: data?.question || message,
                type: 'text',
                data: { awaitingClarification: true }
            };
        
        case 'analyze_page':
            // For page analysis/suggestions - just show the message, no apply button needed
            return {
                content: message,
                type: 'text',
                data: { suggestions: data?.suggestions }
            };
        
        case 'proactive_suggestion':
            // Format proactive suggestions nicely
            let suggestionContent = message + '\n\n**Suggestions:**\n';
            if (data?.suggestions && Array.isArray(data.suggestions)) {
                data.suggestions.forEach((s: any, i: number) => {
                    const priority = s.priority === 'high' ? '🔴' : s.priority === 'medium' ? '🟡' : '🟢';
                    suggestionContent += `${i + 1}. ${priority} ${s.description}\n`;
                });
            }
            return {
                content: suggestionContent,
                type: 'text',
                data: { suggestions: data?.suggestions }
            };
        
        case 'chat':
        default:
            return {
                content: message,
                type: 'text'
            };
    }
};

// Legacy: Process with keyword matching (fallback only)
const processWithKeywordMatchingLegacy = async (input: string): Promise<{ content: string; type: Message['type']; data?: any }> => {
    const lowerInput = input.toLowerCase();
    const ctx = props.aiContext;
    
    try {
        const richContext = buildRichContext();
        const intentResult = await classifyIntent(input, richContext);
        
        // Handle clarification requests (confidence 0.4-0.7)
        if (intentResult?.needsClarification || intentResult?.intent === 'clarify') {
            const clarificationQuestion = intentResult.params?.clarificationQuestion 
                || generateClarificationQuestion(intentResult.originalIntent || intentResult.intent, input);
            return {
                content: clarificationQuestion,
                type: 'text',
                data: { awaitingClarification: true, originalIntent: intentResult.originalIntent }
            };
        }
        
        // Handle multi-step actions
        if (intentResult?.intent === 'multi_step' && intentResult.params?.actions?.length > 0) {
            return handleMultiStepActions(intentResult.params.actions, ctx);
        }
        
        // Use AI classification if confidence is high enough
        if (intentResult && intentResult.confidence >= 0.7) {
            const result = await handleClassifiedIntent(intentResult, input, ctx);
            if (result) return result;
        }
        
        // Medium confidence (0.4-0.7) - try to proceed but be less certain
        if (intentResult && intentResult.confidence >= 0.4) {
            const result = await handleClassifiedIntent(intentResult, input, ctx);
            if (result) {
                // Add a note about uncertainty
                result.content = result.content + '\n\n_(If this isn\'t what you meant, please clarify your request.)_';
                return result;
            }
        }
    } catch (e) {
        console.warn('AI intent classification failed, falling back to keyword matching:', e);
    }

    // ============================================
    // FALLBACK: KEYWORD-BASED MATCHING
    // ============================================
    return processWithKeywordMatching(input, lowerInput, ctx);
};

// Generate clarification question based on ambiguous intent
const generateClarificationQuestion = (intent: string | undefined, input: string): string => {
    const questions: Record<string, string> = {
        'create_page': `I'm not sure which type of page you want to create. Would you like an **About**, **Services**, **Contact**, **Blog**, **FAQ**, or **Pricing** page?`,
        'add_section': `Which section would you like to add? Options include: **Hero**, **About**, **Services**, **Features**, **Testimonials**, **Pricing**, **FAQ**, **Contact**, **CTA**, **Team**, or **Gallery**.`,
        'change_style': `What style change would you like? I can help with:\n• **Colors** (background, text)\n• **Spacing** (padding, margins)\n• **Alignment** (left, center, right)\n• **Size** (font size, section height)`,
        'generate_content': `What content would you like me to generate? I can create:\n• **Testimonials**\n• **FAQs**\n• **Pricing plans**\n• **Team bios**\n• **Service descriptions**`,
        'unknown': `I'm not quite sure what you'd like to do. Could you tell me more? I can help with:\n• Creating pages\n• Adding sections\n• Generating content\n• Changing styles\n• SEO optimization`,
    };
    
    return questions[intent || 'unknown'] || questions['unknown'];
};

// Handle multi-step actions (e.g., "create about page and add testimonials")
const handleMultiStepActions = async (
    actions: Array<{ intent: string; params: Record<string, any> }>,
    ctx: AIContext | null | undefined
): Promise<{ content: string; type: Message['type']; data?: any }> => {
    const actionDescriptions: string[] = [];
    const combinedData: any = { multiStep: true, actions: [] };
    
    for (const action of actions) {
        switch (action.intent) {
            case 'create_page':
                actionDescriptions.push(`Create a ${action.params.pageType || 'new'} page`);
                combinedData.actions.push({
                    type: 'createPage',
                    pageType: action.params.pageType,
                    useAI: true
                });
                break;
            case 'add_section':
                actionDescriptions.push(`Add a ${action.params.sectionType} section`);
                combinedData.actions.push({
                    type: 'addSection',
                    sectionType: action.params.sectionType
                });
                break;
            case 'change_style':
                actionDescriptions.push(`Apply style changes`);
                combinedData.actions.push({
                    type: 'changeStyle',
                    styleChanges: action.params.styleChanges
                });
                break;
            case 'generate_content':
                actionDescriptions.push(`Generate ${action.params.contentType || 'content'}`);
                combinedData.actions.push({
                    type: 'generateContent',
                    contentType: action.params.contentType,
                    sectionType: action.params.sectionType
                });
                break;
        }
    }
    
    const actionList = actionDescriptions.map((a, i) => `${i + 1}. ${a}`).join('\n');
    
    return {
        content: `I'll perform these actions for you:\n\n${actionList}\n\nClick "Apply" to execute all steps.`,
        type: 'page', // Use page type to trigger multi-step handler
        data: combinedData
    };
};

// Handle intent based on AI classification
const handleClassifiedIntent = async (
    intentResult: { intent: string; confidence: number; params: Record<string, any> },
    input: string,
    ctx: AIContext | null | undefined
): Promise<{ content: string; type: Message['type']; data?: any } | null> => {
    const { intent, params } = intentResult;
    
    switch (intent) {
        case 'create_page': {
            const pageType = params.pageType || detectPageType(input);
            if (pageType) {
                return {
                    content: `I'll create a ${pageType} page with AI-generated content tailored to your ${ctx?.site.businessType || 'business'}. This includes professional copy, appropriate sections, and styling. Click "Apply" to generate it.`,
                    type: 'page',
                    data: { 
                        pageRequest: {
                            action: 'create-ai',
                            pageType,
                            title: pageType.charAt(0).toUpperCase() + pageType.slice(1),
                            useAI: true
                        }
                    }
                };
            }
            break;
        }
        
        case 'create_page_detailed': {
            // Handle detailed/comprehensive page generation requests
            const pageType = params.pageType || detectPageType(input) || 'about';
            const detailedRequirements = params.detailedRequirements || {};
            
            // Build a summary of what will be created
            const sections = detailedRequirements.sections || [];
            const tone = detailedRequirements.tone || 'professional';
            const sectionList = sections.length > 0 
                ? sections.slice(0, 5).join(', ') + (sections.length > 5 ? '...' : '')
                : 'hero, about, team, values, CTA';
            
            return {
                content: `I'll create a comprehensive **${pageType}** page with your specific requirements:\n\n` +
                    `• **Tone:** ${tone}\n` +
                    `• **Sections:** ${sectionList}\n` +
                    `• **Content:** Original, no filler text\n\n` +
                    `This will generate professional, tailored content based on your detailed specifications. Click "Apply" to generate it.`,
                type: 'page',
                data: {
                    pageRequest: {
                        action: 'create-detailed',
                        pageType,
                        title: pageType.charAt(0).toUpperCase() + pageType.slice(1),
                        useAI: true,
                        detailedRequirements
                    }
                }
            };
        }
        
        case 'add_section': {
            const sectionType = params.sectionType || detectSectionType(input.toLowerCase());
            if (sectionType) {
                return {
                    content: `I'll add a ${sectionType} section for you. Click "Apply" to add it.`,
                    type: 'section',
                    data: { action: 'add', sectionType }
                };
            }
            break;
        }
        
        case 'edit_content':
        case 'generate_content': {
            const sectionType = params.sectionType || ctx?.selectedSection?.type || 'hero';
            const lowerInput = input.toLowerCase();
            
            // Check for specific content types
            if (lowerInput.includes('testimonial')) {
                const testimonials = await generateTestimonials(ctx?.site.businessType, 3);
                return {
                    content: formatTestimonialsResponse(testimonials),
                    type: 'content',
                    data: { sectionType: 'testimonials', content: { items: testimonials } }
                };
            }
            
            if (lowerInput.includes('faq') || lowerInput.includes('question')) {
                const faqs = await generateFAQs(ctx?.site.businessType, 5);
                return {
                    content: formatFAQsResponse(faqs),
                    type: 'content',
                    data: { sectionType: 'faq', content: { items: faqs } }
                };
            }
            
            if (lowerInput.includes('pricing') || lowerInput.includes('plan')) {
                const pricing = generatePricingContent(ctx?.site.businessType);
                return {
                    content: formatPricingResponse(pricing),
                    type: 'content',
                    data: { sectionType: 'pricing', content: pricing }
                };
            }
            
            if (lowerInput.includes('stat') || lowerInput.includes('number') || lowerInput.includes('counter') || sectionType === 'stats') {
                const stats = generateStatsContent(ctx?.site.businessType);
                return {
                    content: formatStatsResponse(stats),
                    type: 'content',
                    data: { sectionType: 'stats', content: stats }
                };
            }
            
            if (lowerInput.includes('team') || lowerInput.includes('member') || sectionType === 'team') {
                const team = generateTeamContent();
                return {
                    content: formatTeamResponse(team),
                    type: 'content',
                    data: { sectionType: 'team', content: team }
                };
            }
            
            if (lowerInput.includes('service') || sectionType === 'services') {
                const services = generateServicesContent(ctx?.site.businessType);
                return {
                    content: formatServicesResponse(services),
                    type: 'content',
                    data: { sectionType: 'services', content: services }
                };
            }
            
            if (lowerInput.includes('feature') || sectionType === 'features') {
                const features = generateFeaturesContent(ctx?.site.businessType);
                return {
                    content: formatFeaturesResponse(features),
                    type: 'content',
                    data: { sectionType: 'features', content: features }
                };
            }
            
            if (lowerInput.includes('cta') || lowerInput.includes('call to action') || sectionType === 'cta') {
                const cta = generateCtaContent(ctx?.site.businessType);
                return {
                    content: formatCtaResponse(cta),
                    type: 'content',
                    data: { sectionType: 'cta', content: cta }
                };
            }
            
            // Generic content generation via AI
            const content = await generateContent({
                sectionType,
                businessName: props.siteName,
                businessType: ctx?.site.businessType,
            });
            return { 
                content: formatContentResponse(content, sectionType), 
                type: 'content', 
                data: { sectionType, content } 
            };
        }
        
        case 'change_style': {
            const styleChange = params.styleChanges || parseStyleRequest(input.toLowerCase());
            if (styleChange) {
                if (!ctx?.selectedSection) {
                    return {
                        content: `Please select a section first, then I can apply the style change for you.`,
                        type: 'text'
                    };
                }
                return {
                    content: formatStyleResponse(styleChange),
                    type: 'style',
                    data: { styleChange }
                };
            }
            break;
        }
        
        case 'navigation': {
            const navChange = parseNavigationRequest(input.toLowerCase());
            if (navChange) {
                return {
                    content: formatNavigationResponse(navChange),
                    type: 'navigation',
                    data: { navChange }
                };
            }
            break;
        }
        
        case 'footer': {
            const footerChange = parseFooterRequest(input.toLowerCase());
            if (footerChange) {
                return {
                    content: formatFooterResponse(footerChange),
                    type: 'footer',
                    data: { footerChange }
                };
            }
            break;
        }
        
        case 'seo': {
            const pageTitle = ctx?.currentPage?.title || props.siteName;
            const pageContent = ctx?.selectedSection?.contentPreview || `${props.siteName} website`;
            const seo = await generateMeta(pageTitle, pageContent);
            return { content: formatSEOResponse(seo), type: 'seo', data: seo };
        }
        
        case 'colors': {
            let mood = 'professional';
            const lowerInput = input.toLowerCase();
            if (lowerInput.includes('vibrant') || lowerInput.includes('bright')) mood = 'vibrant';
            if (lowerInput.includes('calm') || lowerInput.includes('soft')) mood = 'calm';
            if (lowerInput.includes('bold') || lowerInput.includes('strong')) mood = 'bold';
            if (lowerInput.includes('elegant') || lowerInput.includes('luxury')) mood = 'elegant';
            
            const palette = await suggestColors(ctx?.site.businessType || 'business', mood);
            return { content: formatColorResponse(palette), type: 'colors', data: { palette } };
        }
        
        case 'help':
            return buildContextAwareHelp();
    }
    
    return null; // Fall through to keyword matching
};

// Detect page type from input
const detectPageType = (input: string): string | null => {
    const lowerInput = input.toLowerCase();
    const pageTypes = ['about', 'services', 'contact', 'faq', 'pricing', 'blog', 'gallery', 'testimonials'];
    for (const pageType of pageTypes) {
        if (lowerInput.includes(pageType)) {
            return pageType;
        }
    }
    return null;
};

// Fallback keyword-based processing
const processWithKeywordMatching = async (
    input: string, 
    lowerInput: string, 
    ctx: AIContext | null | undefined
): Promise<{ content: string; type: Message['type']; data?: any }> => {

    // ============================================
    // 0. COMPLEX REQUESTS - "Work on", "Fix", "Update", "Empty" with multiple actions
    // ============================================
    const isComplexRequest = lowerInput.includes('work on') || lowerInput.includes('fix') || lowerInput.includes('update') || lowerInput.includes('improve') || lowerInput.includes('empty') || lowerInput.includes('fill');
    const allSectionTypes = ['hero', 'about', 'section', 'services', 'features', 'testimonials', 'contact', 'pricing', 'faq', 'stats', 'team', 'gallery', 'cta'];
    const mentionsSection = allSectionTypes.some(s => lowerInput.includes(s));
    
    if (isComplexRequest && mentionsSection) {
        // Parse all style changes from the request
        const styleChange = parseStyleRequest(lowerInput);
        
        // Check if they want content improvement too
        const wantsContentImprovement = lowerInput.includes('word') || lowerInput.includes('text') || lowerInput.includes('content') || lowerInput.includes('correct') || lowerInput.includes('better') || lowerInput.includes('empty') || lowerInput.includes('fill');
        
        // Determine target section type - expanded list
        let targetSection = ctx?.selectedSection?.type;
        if (lowerInput.includes('hero')) targetSection = 'hero';
        else if (lowerInput.includes('about')) targetSection = 'about';
        else if (lowerInput.includes('services')) targetSection = 'services';
        else if (lowerInput.includes('features')) targetSection = 'features';
        else if (lowerInput.includes('testimonials')) targetSection = 'testimonials';
        else if (lowerInput.includes('contact')) targetSection = 'contact';
        else if (lowerInput.includes('pricing')) targetSection = 'pricing';
        else if (lowerInput.includes('faq')) targetSection = 'faq';
        else if (lowerInput.includes('stats')) targetSection = 'stats';
        else if (lowerInput.includes('team')) targetSection = 'team';
        else if (lowerInput.includes('gallery')) targetSection = 'gallery';
        else if (lowerInput.includes('cta')) targetSection = 'cta';
        
        // Build combined response
        const actions: string[] = [];
        const combinedData: any = { sectionType: targetSection };
        
        if (styleChange) {
            // Include sectionType in styleChange so handler can find the right section
            combinedData.styleChange = { ...styleChange, sectionType: targetSection };
            actions.push(styleChange.action || 'style changes');
        }
        
        if (wantsContentImprovement) {
            // Generate improved content for the section based on type
            try {
                let content;
                // Use local generators for specific section types
                switch (targetSection) {
                    case 'stats':
                        content = generateStatsContent(ctx?.site.businessType);
                        break;
                    case 'team':
                        content = generateTeamContent();
                        break;
                    case 'pricing':
                        content = generatePricingContent(ctx?.site.businessType);
                        break;
                    case 'services':
                        content = generateServicesContent(ctx?.site.businessType);
                        break;
                    case 'features':
                        content = generateFeaturesContent(ctx?.site.businessType);
                        break;
                    case 'cta':
                        content = generateCtaContent(ctx?.site.businessType);
                        break;
                    default:
                        // Use AI for other section types
                        content = await generateContent({
                            sectionType: targetSection || 'hero',
                            businessName: props.siteName,
                            businessType: ctx?.site.businessType,
                            tone: 'professional',
                        });
                }
                combinedData.content = content;
                actions.push('generated content');
            } catch (e) {
                console.error('Content generation failed:', e);
            }
        }
        
        if (actions.length > 0) {
            const actionList = actions.join(' and ');
            return {
                content: `I'll apply ${actionList} to your ${targetSection || 'selected'} section. Click "Apply" to confirm the changes.`,
                type: styleChange ? 'style' : 'content',
                data: combinedData
            };
        }
    }

    // ============================================
    // 1. PAGE CREATION - Check for page creation requests FIRST
    // ============================================
    // Must check before section management since "create blog page" could match "add blog section"
    const pageRequest = parsePageRequest(lowerInput);
    if (pageRequest) {
        return {
            content: formatPageResponse(pageRequest),
            type: 'page',
            data: { pageRequest }
        };
    }

    // ============================================
    // 2. SECTION MANAGEMENT - Add new sections
    // ============================================
    const addSectionMatch = parseAddSectionRequest(lowerInput);
    if (addSectionMatch) {
        return {
            content: `I'll add a ${addSectionMatch.type} section for you. Click "Apply" to add it.`,
            type: 'section',
            data: { action: 'add', sectionType: addSectionMatch.type, content: addSectionMatch.content }
        };
    }

    // ============================================
    // 3. CONTENT GENERATION - Generate content for sections
    // ============================================
    if (lowerInput.includes('generate') || lowerInput.includes('create') || lowerInput.includes('write')) {
        // Check for specific content types
        if (lowerInput.includes('testimonial')) {
            const testimonials = await generateTestimonials(ctx?.site.businessType, 3);
            return {
                content: formatTestimonialsResponse(testimonials),
                type: 'content',
                data: { sectionType: 'testimonials', content: { items: testimonials } }
            };
        }
        
        if (lowerInput.includes('faq') || lowerInput.includes('question')) {
            const faqs = await generateFAQs(ctx?.site.businessType, 5);
            return {
                content: formatFAQsResponse(faqs),
                type: 'content',
                data: { sectionType: 'faq', content: { items: faqs } }
            };
        }
        
        if (lowerInput.includes('pricing') || lowerInput.includes('plan')) {
            const pricing = generatePricingContent(ctx?.site.businessType);
            return {
                content: formatPricingResponse(pricing),
                type: 'content',
                data: { sectionType: 'pricing', content: pricing }
            };
        }
        
        if (lowerInput.includes('team') || lowerInput.includes('member')) {
            const team = generateTeamContent();
            return {
                content: formatTeamResponse(team),
                type: 'content',
                data: { sectionType: 'team', content: team }
            };
        }
        
        if (lowerInput.includes('service')) {
            const services = generateServicesContent(ctx?.site.businessType);
            return {
                content: formatServicesResponse(services),
                type: 'content',
                data: { sectionType: 'services', content: services }
            };
        }
        
        if (lowerInput.includes('feature')) {
            const features = generateFeaturesContent(ctx?.site.businessType);
            return {
                content: formatFeaturesResponse(features),
                type: 'content',
                data: { sectionType: 'features', content: features }
            };
        }
        
        if (lowerInput.includes('stat') || lowerInput.includes('number') || lowerInput.includes('counter')) {
            const stats = generateStatsContent(ctx?.site.businessType);
            return {
                content: formatStatsResponse(stats),
                type: 'content',
                data: { sectionType: 'stats', content: stats }
            };
        }
        
        if (lowerInput.includes('cta') || lowerInput.includes('call to action')) {
            const cta = generateCtaContent(ctx?.site.businessType);
            return {
                content: formatCtaResponse(cta),
                type: 'content',
                data: { sectionType: 'cta', content: cta }
            };
        }

        // Generic content generation
        const sectionType = detectSectionType(lowerInput) || ctx?.selectedSection?.type || 'hero';
        const content = await generateContent({
            sectionType,
            businessName: props.siteName,
            businessType: ctx?.site.businessType,
        });
        return { content: formatContentResponse(content, sectionType), type: 'content', data: { sectionType, content } };
    }

    // ============================================
    // 3. NAVIGATION CHANGES
    // ============================================
    const navChange = parseNavigationRequest(lowerInput);
    if (navChange) {
        return {
            content: formatNavigationResponse(navChange),
            type: 'navigation',
            data: { navChange }
        };
    }

    // ============================================
    // 4. FOOTER CHANGES
    // ============================================
    const footerChange = parseFooterRequest(lowerInput);
    if (footerChange) {
        return {
            content: formatFooterResponse(footerChange),
            type: 'footer',
            data: { footerChange }
        };
    }

    // ============================================
    // 5. (Moved to section 2 - Page creation now checked earlier)
    // ============================================

    // ============================================
    // 6. COLOR SUGGESTIONS
    // ============================================
    if (lowerInput.includes('color') || lowerInput.includes('palette') || lowerInput.includes('theme')) {
        // Check for specific mood/style
        let mood = 'professional';
        if (lowerInput.includes('vibrant') || lowerInput.includes('bright')) mood = 'vibrant';
        if (lowerInput.includes('calm') || lowerInput.includes('soft')) mood = 'calm';
        if (lowerInput.includes('bold') || lowerInput.includes('strong')) mood = 'bold';
        if (lowerInput.includes('elegant') || lowerInput.includes('luxury')) mood = 'elegant';
        
        const palette = await suggestColors(ctx?.site.businessType || 'business', mood);
        return { content: formatColorResponse(palette), type: 'colors', data: { palette } };
    }

    // ============================================
    // 7. SEO ASSISTANCE
    // ============================================
    if (lowerInput.includes('seo') || lowerInput.includes('meta') || lowerInput.includes('keyword')) {
        const pageTitle = ctx?.currentPage?.title || props.siteName;
        const pageContent = ctx?.selectedSection?.contentPreview || `${props.siteName} website`;
        const seo = await generateMeta(pageTitle, pageContent);
        return { content: formatSEOResponse(seo), type: 'seo', data: seo };
    }

    // ============================================
    // 8. STYLE CHANGES (colors, spacing, alignment, height)
    // ============================================
    const styleChange = parseStyleRequest(lowerInput);
    if (styleChange) {
        if (!ctx?.selectedSection) {
            return {
                content: `Please select a section first, then I can apply the style change for you.`,
                type: 'text'
            };
        }
        return {
            content: formatStyleResponse(styleChange),
            type: 'style',
            data: { styleChange }
        };
    }

    // ============================================
    // 9. TEXT IMPROVEMENT
    // ============================================
    const textToImprove = props.initialText || ctx?.selectedSection?.contentPreview;
    if (textToImprove && (lowerInput.includes('improve') || lowerInput.includes('rewrite') || lowerInput.includes('better') || lowerInput.includes('change'))) {
        try {
            const improved = await improveText({ 
                text: textToImprove, 
                style: 'professional',
                instruction: input
            });
            
            if (improved) {
                return { 
                    content: `Here's an improved version:\n\n"${improved}"`, 
                    type: 'content', 
                    data: { content: { title: improved }, sectionType: ctx?.selectedSection?.type } 
                };
            }
        } catch (e) {
            console.error('AI request failed:', e);
        }
    }

    // ============================================
    // 10. FALLBACK - Help message
    // ============================================
    return buildContextAwareHelp();
};

// ============================================
// SECTION PARSING
// ============================================
const parseAddSectionRequest = (input: string): { type: string; content?: any } | null => {
    const sectionTypes: Record<string, string[]> = {
        'hero': ['hero', 'header', 'banner'],
        'about': ['about', 'about us', 'our story'],
        'services': ['service', 'services', 'what we do'],
        'features': ['feature', 'features', 'benefits'],
        'testimonials': ['testimonial', 'testimonials', 'review', 'reviews'],
        'pricing': ['pricing', 'price', 'plans'],
        'faq': ['faq', 'faqs', 'questions'],
        'contact': ['contact', 'contact us', 'get in touch'],
        'cta': ['cta', 'call to action'],
        'team': ['team', 'our team', 'staff'],
        'gallery': ['gallery', 'photos', 'images'],
        'stats': ['stats', 'statistics', 'numbers', 'counter'],
        'video': ['video'],
        'map': ['map', 'location'],
        'blog': ['blog', 'news', 'articles'],
    };

    if (input.includes('add') || input.includes('insert') || input.includes('create a') || input.includes('new section')) {
        for (const [type, keywords] of Object.entries(sectionTypes)) {
            if (keywords.some(k => input.includes(k))) {
                return { type };
            }
        }
    }
    return null;
};

// ============================================
// NAVIGATION PARSING
// ============================================
const parseNavigationRequest = (input: string): Record<string, any> | null => {
    const changes: Record<string, any> = {};
    
    if (!input.includes('nav') && !input.includes('menu') && !input.includes('header')) {
        return null;
    }
    
    // Navigation style changes
    const navStyles = ['default', 'centered', 'split', 'floating', 'transparent', 'dark', 'sidebar', 'mega'];
    for (const style of navStyles) {
        if (input.includes(style)) {
            changes.style = style;
            changes.action = `change navigation to ${style} style`;
            break;
        }
    }
    
    // Sticky navigation
    if (input.includes('sticky')) {
        changes.sticky = !input.includes('not') && !input.includes('remove');
        changes.action = changes.sticky ? 'make navigation sticky' : 'remove sticky navigation';
    }
    
    // CTA button
    if (input.includes('cta') || input.includes('button')) {
        if (input.includes('hide') || input.includes('remove')) {
            changes.showCta = false;
            changes.action = 'hide CTA button';
        } else if (input.includes('show') || input.includes('add')) {
            changes.showCta = true;
            changes.action = 'show CTA button';
        }
    }
    
    // Auth buttons
    if (input.includes('login') || input.includes('register') || input.includes('auth')) {
        if (input.includes('show') || input.includes('add') || input.includes('enable')) {
            changes.showAuthButtons = true;
            changes.action = 'enable login/register buttons';
        } else if (input.includes('hide') || input.includes('remove')) {
            changes.showAuthButtons = false;
            changes.action = 'hide login/register buttons';
        }
    }
    
    return Object.keys(changes).length > 0 ? changes : null;
};

const formatNavigationResponse = (navChange: Record<string, any>): string => {
    return `I'll ${navChange.action || 'update the navigation'}. Click "Apply" to confirm.`;
};

// ============================================
// FOOTER PARSING
// ============================================
const parseFooterRequest = (input: string): Record<string, any> | null => {
    const changes: Record<string, any> = {};
    
    if (!input.includes('footer')) {
        return null;
    }
    
    // Footer layout changes
    const footerLayouts = ['columns', 'centered', 'split', 'minimal', 'stacked', 'newsletter', 'social', 'contact'];
    for (const layout of footerLayouts) {
        if (input.includes(layout)) {
            changes.layout = layout;
            changes.action = `change footer to ${layout} layout`;
            break;
        }
    }
    
    // Footer colors
    const colorMap: Record<string, string> = {
        'dark': '#1f2937', 'black': '#111827', 'white': '#ffffff', 'light': '#f9fafb',
        'blue': '#1e40af', 'gray': '#4b5563', 'grey': '#4b5563'
    };
    
    if (input.includes('background') || input.includes('color')) {
        for (const [colorName, colorValue] of Object.entries(colorMap)) {
            if (input.includes(colorName)) {
                changes.backgroundColor = colorValue;
                const isDark = ['dark', 'black', 'blue', 'gray', 'grey'].includes(colorName);
                changes.textColor = isDark ? '#ffffff' : '#111827';
                changes.action = `change footer to ${colorName} background`;
                break;
            }
        }
    }
    
    // Social links
    if (input.includes('social')) {
        if (input.includes('show') || input.includes('add') || input.includes('enable')) {
            changes.showSocialLinks = true;
            changes.action = 'show social links in footer';
        } else if (input.includes('hide') || input.includes('remove')) {
            changes.showSocialLinks = false;
            changes.action = 'hide social links in footer';
        }
    }
    
    return Object.keys(changes).length > 0 ? changes : null;
};

const formatFooterResponse = (footerChange: Record<string, any>): string => {
    return `I'll ${footerChange.action || 'update the footer'}. Click "Apply" to confirm.`;
};

// ============================================
// PAGE PARSING - Now with AI-powered generation
// ============================================
const parsePageRequest = (input: string): Record<string, any> | null => {
    const changes: Record<string, any> = {};
    
    if (!input.includes('page') && !input.includes('template')) {
        return null;
    }
    
    // Create new page with AI
    if (input.includes('create') || input.includes('new') || input.includes('add') || input.includes('generate')) {
        const pageTypes = ['about', 'services', 'contact', 'faq', 'pricing', 'blog', 'gallery', 'testimonials'];
        for (const pageType of pageTypes) {
            if (input.includes(pageType)) {
                changes.action = 'create-ai';
                changes.pageType = pageType;
                changes.title = pageType.charAt(0).toUpperCase() + pageType.slice(1);
                changes.useAI = true; // Flag to use AI generation
                return changes;
            }
        }
        // Generic page creation (blank)
        if (input.includes('blank')) {
            changes.action = 'create';
            changes.template = 'blank';
            changes.title = 'New Page';
            changes.useAI = false;
            return changes;
        }
    }
    
    // Apply template (non-AI)
    if (input.includes('apply') || input.includes('use')) {
        const templates = ['home', 'home-minimal', 'home-business', 'about', 'services', 'contact', 'faq', 'pricing', 'blog', 'gallery', 'testimonials'];
        for (const template of templates) {
            if (input.includes(template.replace('-', ' '))) {
                changes.action = 'apply-template';
                changes.template = template;
                return changes;
            }
        }
    }
    
    return null;
};

const formatPageResponse = (pageRequest: Record<string, any>): string => {
    if (pageRequest.action === 'create-ai') {
        return `I'll create a ${pageRequest.title} page with AI-generated content tailored to your ${props.aiContext?.site.businessType || 'business'}. This includes professional copy, appropriate sections, and styling. Click "Apply" to generate it.`;
    }
    if (pageRequest.action === 'create') {
        return `I'll create a new ${pageRequest.title} page using the ${pageRequest.template} template. Click "Apply" to create it.`;
    }
    if (pageRequest.action === 'apply-template') {
        return `I'll apply the ${pageRequest.template} template to this page. Click "Apply" to confirm.`;
    }
    return 'I can help you manage pages. Click "Apply" to proceed.';
};

// ============================================
// BUSINESS-TYPE AWARE CONTENT GENERATORS
// ============================================

// Business-specific testimonial templates
const businessTestimonials: Record<string, Array<{ name: string; text: string; role: string; rating: number }>> = {
    restaurant: [
        { name: 'Mwila Chanda', text: 'The food here is absolutely amazing! Best nshima and village chicken in town. My family loves coming here every weekend.', role: 'Regular Customer', rating: 5 },
        { name: 'Grace Tembo', text: 'Perfect venue for our company lunch. The service was excellent and the traditional dishes were authentic and delicious.', role: 'Event Organizer', rating: 5 },
        { name: 'John Banda', text: 'Finally found a restaurant that serves real Zambian cuisine! The kapenta and ifisashi are just like my grandmother used to make.', role: 'Food Enthusiast', rating: 5 },
    ],
    church: [
        { name: 'Pastor David Mulenga', text: 'This church has transformed our community. The worship is powerful and the teachings are life-changing.', role: 'Visiting Pastor', rating: 5 },
        { name: 'Mary Phiri', text: 'I found my spiritual home here. The fellowship is warm and the youth programs have been a blessing for my children.', role: 'Church Member', rating: 5 },
        { name: 'Emmanuel Zulu', text: 'The outreach programs have made a real difference in our neighborhood. This is a church that truly serves.', role: 'Community Leader', rating: 5 },
    ],
    tutor: [
        { name: 'Mrs. Banda', text: 'My son\'s grades improved from C to A in just 3 months! The tutors here really understand how to teach.', role: 'Parent', rating: 5 },
        { name: 'Chipo Mwansa', text: 'I passed my Grade 12 exams with flying colors thanks to the dedicated teachers here. Highly recommended!', role: 'Student', rating: 5 },
        { name: 'Mr. Tembo', text: 'Professional tutoring service with qualified teachers. My daughter is now confident in mathematics.', role: 'Parent', rating: 5 },
    ],
    healthcare: [
        { name: 'Sarah Mwila', text: 'The doctors here are compassionate and thorough. I finally got the diagnosis I needed after years of uncertainty.', role: 'Patient', rating: 5 },
        { name: 'Dr. Phiri', text: 'Excellent medical facility with modern equipment. I confidently refer my patients here for specialized care.', role: 'Referring Physician', rating: 5 },
        { name: 'Grace Zulu', text: 'The maternity ward staff were incredible during my delivery. Professional, caring, and supportive throughout.', role: 'New Mother', rating: 5 },
    ],
    beauty: [
        { name: 'Natasha Mwape', text: 'Best salon in Lusaka! My braids always look perfect and last for weeks. The stylists are true artists.', role: 'Regular Client', rating: 5 },
        { name: 'Chanda Tembo', text: 'I got my wedding makeup done here and looked absolutely stunning. Everyone asked who did my makeup!', role: 'Bride', rating: 5 },
        { name: 'Mwila Banda', text: 'Finally found a salon that understands African hair. The treatments have transformed my natural hair.', role: 'Natural Hair Client', rating: 5 },
    ],
    fitness: [
        { name: 'Joseph Mulenga', text: 'Lost 15kg in 4 months with their personalized training program. The trainers really push you to achieve your goals.', role: 'Member', rating: 5 },
        { name: 'Chipo Zulu', text: 'The group fitness classes are energetic and fun. I actually look forward to working out now!', role: 'Fitness Enthusiast', rating: 5 },
        { name: 'David Phiri', text: 'Clean facilities, modern equipment, and professional staff. Best gym investment I\'ve made.', role: 'Member', rating: 5 },
    ],
};

const generateFAQs = async (businessType?: string, count: number = 5) => {
    try {
        const { generateFAQs: apiGenerate } = useAI(props.siteId);
        return await apiGenerate(businessType, count);
    } catch {
        return [
            { question: 'What services do you offer?', answer: 'We offer a comprehensive range of services tailored to meet your specific needs.' },
            { question: 'How can I get started?', answer: 'Simply contact us through our form or give us a call, and we\'ll guide you through the process.' },
            { question: 'What are your business hours?', answer: 'We\'re available Monday to Friday, 8am to 5pm. Weekend appointments available on request.' },
            { question: 'Do you offer free consultations?', answer: 'Yes! We offer a free initial consultation to understand your needs and provide recommendations.' },
            { question: 'What payment methods do you accept?', answer: 'We accept MTN Mobile Money, Airtel Money, bank transfers, and major credit cards.' },
        ].slice(0, count);
    }
};

const generatePricingContent = (businessType?: string) => ({
    title: 'Our Pricing Plans',
    plans: [
        { name: 'Starter', price: 'K500/mo', features: ['Basic features', 'Email support', '5 users included', 'Monthly reports'], highlighted: false },
        { name: 'Professional', price: 'K1,500/mo', features: ['All Starter features', 'Priority support', '25 users included', 'Weekly reports', 'Custom integrations'], highlighted: true },
        { name: 'Enterprise', price: 'Custom', features: ['All Professional features', '24/7 support', 'Unlimited users', 'Dedicated manager', 'Custom development'], highlighted: false },
    ]
});

const generateTeamContent = () => ({
    title: 'Meet Our Team',
    items: [
        { name: 'David Mulenga', role: 'Founder & CEO', image: '', bio: 'Visionary leader with 15+ years of industry experience.' },
        { name: 'Chipo Banda', role: 'Operations Director', image: '', bio: 'Expert in streamlining processes and delivering excellence.' },
        { name: 'Emmanuel Phiri', role: 'Technical Lead', image: '', bio: 'Passionate about innovation and cutting-edge solutions.' },
    ]
});

const generateServicesContent = (businessType?: string) => ({
    title: 'Our Services',
    subtitle: 'Comprehensive solutions tailored to your needs',
    items: [
        { title: 'Consulting', description: 'Expert guidance to help you achieve your business goals.' },
        { title: 'Implementation', description: 'End-to-end project delivery with proven methodologies.' },
        { title: 'Support', description: 'Ongoing assistance to ensure your continued success.' },
    ]
});

const generateFeaturesContent = (businessType?: string) => ({
    title: 'Why Choose Us',
    subtitle: 'What sets us apart from the competition',
    items: [
        { title: 'Experience', description: 'Over a decade of proven results in the industry.' },
        { title: 'Quality', description: 'We never compromise on the quality of our work.' },
        { title: 'Support', description: '24/7 customer support to address your needs.' },
        { title: 'Value', description: 'Competitive pricing without sacrificing quality.' },
    ]
});

const generateStatsContent = (businessType?: string) => ({
    title: 'Our Impact',
    subtitle: 'Numbers that speak for themselves',
    items: [
        { value: '500+', label: 'Happy Clients' },
        { value: '98%', label: 'Satisfaction Rate' },
        { value: '10+', label: 'Years Experience' },
        { value: '24/7', label: 'Support Available' },
    ]
});

const generateCtaContent = (businessType?: string) => ({
    title: 'Ready to Get Started?',
    subtitle: 'Contact us today and let\'s discuss how we can help you achieve your goals.',
    buttonText: 'Contact Us',
    buttonLink: '#contact',
});

// ============================================
// RESPONSE FORMATTERS
// ============================================
const formatTestimonialsResponse = (testimonials: any[]) => {
    let response = `Here are ${testimonials.length} testimonials:\n\n`;
    testimonials.forEach((t, i) => {
        response += `**${i + 1}. ${t.name}** (${t.role})\n"${t.text}"\n\n`;
    });
    response += 'Click "Apply" to add these to your section.';
    return response;
};

const formatFAQsResponse = (faqs: any[]) => {
    let response = `Here are ${faqs.length} FAQ items:\n\n`;
    faqs.forEach((f, i) => {
        response += `**Q${i + 1}: ${f.question}**\n${f.answer}\n\n`;
    });
    response += 'Click "Apply" to add these to your section.';
    return response;
};

const formatPricingResponse = (pricing: any) => {
    let response = `Here's a pricing structure:\n\n`;
    pricing.plans.forEach((p: any) => {
        response += `**${p.name}** - ${p.price}\n`;
        response += p.features.map((f: string) => `• ${f}`).join('\n') + '\n\n';
    });
    response += 'Click "Apply" to add this pricing section.';
    return response;
};

const formatTeamResponse = (team: any) => {
    let response = `Here's a team section:\n\n`;
    team.items.forEach((m: any) => {
        response += `**${m.name}** - ${m.role}\n${m.bio}\n\n`;
    });
    response += 'Click "Apply" to add this team section.';
    return response;
};

const formatServicesResponse = (services: any) => {
    let response = `Here are service descriptions:\n\n`;
    services.items.forEach((s: any) => {
        response += `**${s.title}**\n${s.description}\n\n`;
    });
    response += 'Click "Apply" to add these services.';
    return response;
};

const formatFeaturesResponse = (features: any) => {
    let response = `Here are feature highlights:\n\n`;
    features.items.forEach((f: any) => {
        response += `**${f.title}**\n${f.description}\n\n`;
    });
    response += 'Click "Apply" to add these features.';
    return response;
};

const formatStatsResponse = (stats: any) => {
    let response = `Here are statistics for your section:\n\n`;
    stats.items.forEach((s: any) => {
        response += `**${s.value}** - ${s.label}\n`;
    });
    response += '\nClick "Apply" to add these stats.';
    return response;
};

const formatCtaResponse = (cta: any) => {
    let response = `Here's a call-to-action:\n\n`;
    response += `**${cta.title}**\n${cta.subtitle}\n\nButton: "${cta.buttonText}"\n\n`;
    response += 'Click "Apply" to add this CTA.';
    return response;
};

// Parse style change requests from user input
const parseStyleRequest = (input: string): Record<string, any> | null => {
    const changes: Record<string, any> = {};
    const actions: string[] = [];
    
    // Font size changes
    if (input.includes('font') || input.includes('text size')) {
        if (input.includes('bigger') || input.includes('larger') || input.includes('increase')) {
            changes.titleSize = 'text-5xl';
            actions.push('increase font size');
        } else if (input.includes('smaller') || input.includes('decrease') || input.includes('reduce')) {
            changes.titleSize = 'text-3xl';
            actions.push('decrease font size');
        }
    }
    
    // Spacing/padding changes - expanded patterns
    if (input.includes('spacing') || input.includes('padding') || input.includes('space') || input.includes('gap')) {
        if (input.includes('more') || input.includes('increase') || input.includes('bigger') || input.includes('good') || input.includes('better') || input.includes('add')) {
            changes.paddingY = '80';
            actions.push('increase spacing');
        } else if (input.includes('less') || input.includes('decrease') || input.includes('smaller') || input.includes('reduce')) {
            changes.paddingY = '40';
            actions.push('decrease spacing');
        }
    }
    
    // Text alignment - expanded patterns to catch more natural language
    if (input.includes('center')) {
        // Check if it's about centering (not just mentioning "center")
        if (input.includes('align') || input.includes('text') || input.includes('item') || input.includes('content') || input.includes('the')) {
            changes.textPosition = 'center';
            actions.push('center align');
        }
    } else if (input.includes('left') && (input.includes('align') || input.includes('text') || input.includes('move'))) {
        changes.textPosition = 'left';
        actions.push('left align');
    } else if (input.includes('right') && (input.includes('align') || input.includes('text') || input.includes('move'))) {
        changes.textPosition = 'right';
        actions.push('right align');
    }
    
    // Color changes - expanded support
    const colorMap: Record<string, string> = {
        'red': '#dc2626',
        'blue': '#2563eb',
        'green': '#16a34a',
        'yellow': '#ca8a04',
        'orange': '#ea580c',
        'purple': '#9333ea',
        'pink': '#db2777',
        'indigo': '#4f46e5',
        'teal': '#0d9488',
        'cyan': '#0891b2',
        'gray': '#6b7280',
        'grey': '#6b7280',
        'black': '#111827',
        'white': '#ffffff',
        'dark': '#1f2937',
        'light': '#f9fafb',
    };
    
    // Background color changes
    if (input.includes('background')) {
        for (const [colorName, colorValue] of Object.entries(colorMap)) {
            if (input.includes(colorName)) {
                changes.backgroundColor = colorValue;
                // Auto-adjust text color for contrast
                const isDark = ['black', 'dark', 'blue', 'purple', 'indigo', 'red', 'green', 'teal'].includes(colorName);
                changes.textColor = isDark ? '#ffffff' : '#111827';
                actions.push(`${colorName} background`);
                break;
            }
        }
    }
    
    // Text color changes
    if ((input.includes('text color') || input.includes('text to') || input.includes('font color')) && !changes.textColor) {
        for (const [colorName, colorValue] of Object.entries(colorMap)) {
            if (input.includes(colorName)) {
                changes.textColor = colorValue;
                actions.push(`${colorName} text`);
                break;
            }
        }
    }
    
    // General color change (could be background or text)
    if (!changes.backgroundColor && !changes.textColor && (input.includes('color') || input.includes('make it'))) {
        for (const [colorName, colorValue] of Object.entries(colorMap)) {
            if (input.includes(colorName)) {
                // Default to background color change
                changes.backgroundColor = colorValue;
                const isDark = ['black', 'dark', 'blue', 'purple', 'indigo', 'red', 'green', 'teal'].includes(colorName);
                changes.textColor = isDark ? '#ffffff' : '#111827';
                actions.push(`${colorName} color scheme`);
                break;
            }
        }
    }
    
    // Bold text
    if (input.includes('bold')) {
        changes.fontWeight = 'bold';
        actions.push('make text bold');
    }
    
    // Section height
    if (input.includes('height') || input.includes('taller') || input.includes('shorter')) {
        if (input.includes('taller') || input.includes('increase') || input.includes('bigger')) {
            changes.minHeight = 600;
            actions.push('increase section height');
        } else if (input.includes('shorter') || input.includes('decrease') || input.includes('smaller')) {
            changes.minHeight = 300;
            actions.push('decrease section height');
        }
    }
    
    if (Object.keys(changes).length > 0) {
        changes.action = actions.join(', ');
        return changes;
    }
    return null;
};

// Format style change response
const formatStyleResponse = (styleChange: Record<string, any>): string => {
    const action = styleChange.action || 'style change';
    return `I'll apply the ${action} to your section. Click "Apply" to confirm.`;
};

const detectSectionType = (input: string): string | null => {
    const types: Record<string, string[]> = {
        hero: ['hero', 'header', 'banner', 'landing'],
        about: ['about', 'who we are', 'our story'],
        services: ['service', 'what we do', 'offerings'],
        features: ['feature', 'benefits', 'why choose'],
        cta: ['cta', 'call to action', 'get started'],
        contact: ['contact', 'reach us', 'get in touch'],
        testimonials: ['testimonial', 'review', 'feedback'],
        faq: ['faq', 'question', 'help'],
    };

    for (const [type, keywords] of Object.entries(types)) {
        if (keywords.some(k => input.includes(k))) return type;
    }
    return null;
};

const buildContextAwareHelp = (): { content: string; type: Message['type'] } => {
    const ctx = props.aiContext;
    let help = `I can help you with:\n\n`;

    // Context-specific suggestions
    if (ctx?.selectedSection) {
        help += `**For your ${ctx.selectedSection.type} section:**\n`;
        help += `• "Make the background blue"\n`;
        help += `• "Improve the headline"\n`;
        help += `• "Make it taller"\n\n`;
    }

    help += `**Content Generation:**\n`;
    help += `• "Generate testimonials"\n`;
    help += `• "Write FAQ questions"\n`;
    help += `• "Create pricing plans"\n\n`;

    help += `**Add Sections:**\n`;
    help += `• "Add a hero section"\n`;
    help += `• "Add testimonials"\n`;
    help += `• "Add a contact form"\n\n`;

    help += `**Style Changes:**\n`;
    help += `• "Make it darker/lighter"\n`;
    help += `• "Center the text"\n`;
    help += `• "Add more spacing"\n\n`;

    help += `**Navigation & Footer:**\n`;
    help += `• "Make nav sticky"\n`;
    help += `• "Change footer to dark"\n\n`;

    help += `**SEO & Colors:**\n`;
    help += `• "Generate meta description"\n`;
    help += `• "Suggest a color palette"\n`;

    return { content: help, type: 'text' };
};

const formatContentResponse = (content: any, type: string) => {
    if (!content) return 'I generated some content for you. Click "Apply" to use it.';
    let response = `Here's your ${type} content:\n\n`;
    if (content.title) response += `**Title:** ${content.title}\n`;
    if (content.subtitle) response += `**Subtitle:** ${content.subtitle}\n`;
    if (content.content) response += `\n${content.content}\n`;
    if (content.buttonText) response += `\n**Button:** ${content.buttonText}`;
    return response;
};

const formatColorResponse = (palette: any) => {
    if (!palette) return 'I created a color palette for you.';
    return `Here's a professional color palette:\n\n• **Primary:** ${palette.primary}\n• **Secondary:** ${palette.secondary}\n• **Accent:** ${palette.accent}\n• **Background:** ${palette.background}\n• **Text:** ${palette.text}\n\nClick "Apply" to use these colors.`;
};

const formatSEOResponse = (seo: any) => {
    if (!seo) return 'I generated SEO suggestions for you.';
    let response = `**Meta Description:**\n${seo.metaDescription}\n\n**Keywords:**\n`;
    if (seo.keywords) response += seo.keywords.join(', ');
    return response;
};

// Action handlers
const handleQuickAction = async (action: string) => {
    userInput.value = action;
    await sendMessage();
};

const handleSuggestion = (suggestion: string) => {
    userInput.value = suggestion;
};

// Handle when user dismisses/rejects a suggestion - records for learning
const handleDismissContent = (data: { type: string; sectionType?: string }) => {
    recordFeedback(data.type, false, data.sectionType);
    // Add a subtle message acknowledging the dismissal
    addMessage('assistant', "Got it! I'll try a different approach. What would you prefer?", 'text');
};

const handleApplyContent = async (data: any) => {
    if (!data) return;
    
    // Handle multi-step actions
    if (data.multiStep && data.actions?.length > 0) {
        await handleMultiStepApply(data.actions);
        recordFeedback('multi_step', true);
        return;
    }
    
    let appliedSomething = false;
    const appliedTypes: string[] = [];
    
    // Debug log to see what data we're receiving
    console.log('handleApplyContent data:', JSON.stringify(data, null, 2));
    
    // Handle style changes (don't return early - may have content too)
    if (data.styleChange) {
        emit('applyStyle', data.styleChange);
        appliedSomething = true;
        appliedTypes.push('style');
    }
    
    // Handle content changes (can be combined with style)
    if (data.content && Object.keys(data.content).length > 0) {
        const contentToApply = {
            ...data.content,
            sectionType: data.sectionType,
            position: data.position, // Pass position for smart placement
            style: data.style, // Pass style for theme matching
        };
        console.log('Emitting applyContent:', contentToApply);
        emit('applyContent', contentToApply);
        appliedSomething = true;
        appliedTypes.push(data.sectionType || 'content');
    }
    
    // If we applied style and/or content, show success and return
    if (appliedSomething) {
        const actions = [];
        if (data.styleChange) actions.push('style');
        if (data.content) actions.push('content');
        toast.success(`Applied ${actions.join(' and ')} to section`);
        // Record feedback for learning
        appliedTypes.forEach(type => recordFeedback(type, true));
        return;
    }
    
    // Handle navigation changes
    if (data.navChange) {
        emit('updateNavigation', data.navChange);
        toast.success('Navigation updated');
        recordFeedback('navigation', true);
        return;
    }
    
    // Handle footer changes
    if (data.footerChange) {
        emit('updateFooter', data.footerChange);
        toast.success('Footer updated');
        recordFeedback('footer', true);
        return;
    }
    
    // Handle page requests
    if (data.pageRequest) {
        // Smart AI page creation (sections already generated by smartChat)
        if (data.pageRequest.action === 'create-ai-smart') {
            // Sections are already generated, just emit to create the page
            const pageData = {
                title: data.pageRequest.title,
                sections: data.pageRequest.sections || [],
            };
            
            emit('createAIPage', data.pageRequest.pageType, pageData);
            toast.success(`Created ${data.pageRequest.title || data.pageRequest.pageType} page`);
            recordFeedback('page', true, data.pageRequest.pageType);
            
            const sectionCount = pageData.sections?.length || 0;
            addMessage('assistant', `✅ Created your ${data.pageRequest.title || data.pageRequest.pageType} page with ${sectionCount} sections!`);
            return;
        }
        
        // Detailed AI-powered page creation (comprehensive requirements)
        if (data.pageRequest.action === 'create-detailed' && data.pageRequest.useAI) {
            isTyping.value = true;
            addMessage('assistant', `Generating comprehensive ${data.pageRequest.title} page with your detailed requirements... This may take a moment.`);
            
            try {
                const pageData = await generatePageDetailed({
                    pageType: data.pageRequest.pageType,
                    businessName: props.siteName,
                    businessType: props.aiContext?.site.businessType,
                    detailedRequirements: data.pageRequest.detailedRequirements || {},
                });
                
                isTyping.value = false;
                emit('createAIPage', data.pageRequest.pageType, pageData);
                toast.success(`Created ${data.pageRequest.title} page with detailed AI content`);
                recordFeedback('page_detailed', true);
                
                // Show success message with details
                const sectionCount = pageData.sections?.length || 0;
                addMessage('assistant', `✅ Created your comprehensive ${data.pageRequest.title} page!\n\n` +
                    `• **${sectionCount} sections** generated\n` +
                    `• Original, professional content\n` +
                    `• Tailored to your specifications\n\n` +
                    `You can now edit any section to fine-tune the content.`);
            } catch (e: any) {
                isTyping.value = false;
                toast.error('Failed to generate detailed page. Trying standard generation...');
                recordFeedback('page_detailed', false);
                // Fallback to standard AI generation
                try {
                    const pageData = await generatePage({
                        pageType: data.pageRequest.pageType,
                        businessName: props.siteName,
                        businessType: props.aiContext?.site.businessType,
                    });
                    emit('createAIPage', data.pageRequest.pageType, pageData);
                    addMessage('assistant', `✅ Created ${data.pageRequest.title} page (standard generation).`);
                    recordFeedback('page', true);
                } catch {
                    emit('createPage', data.pageRequest.pageType, data.pageRequest.title);
                    recordFeedback('page', false);
                }
            }
            return;
        }
        
        // AI-powered page creation
        if (data.pageRequest.action === 'create-ai' && data.pageRequest.useAI) {
            isTyping.value = true;
            addMessage('assistant', `Generating ${data.pageRequest.title} page with AI... This may take a moment.`);
            
            try {
                const pageData = await generatePage({
                    pageType: data.pageRequest.pageType,
                    businessName: props.siteName,
                    businessType: props.aiContext?.site.businessType,
                    businessDescription: props.aiContext?.site.description,
                });
                
                isTyping.value = false;
                emit('createAIPage', data.pageRequest.pageType, pageData);
                toast.success(`Created ${data.pageRequest.title} page with AI content`);
                
                // Show success message
                addMessage('assistant', `✅ Created your ${data.pageRequest.title} page with ${pageData.sections?.length || 0} sections tailored to your ${props.aiContext?.site.businessType || 'business'}!`);
            } catch (e: any) {
                isTyping.value = false;
                toast.error('Failed to generate page. Using template instead.');
                // Fallback to template
                emit('createPage', data.pageRequest.pageType, data.pageRequest.title);
            }
            return;
        }
        
        // Standard template-based page creation
        if (data.pageRequest.action === 'create') {
            emit('createPage', data.pageRequest.template, data.pageRequest.title);
            toast.success(`Creating ${data.pageRequest.title} page`);
        }
        return;
    }
    
    // Handle add section
    if (data.action === 'add' && data.sectionType) {
        emit('addSection', data.sectionType, data.content);
        toast.success(`Added ${data.sectionType} section`);
        return;
    }
    
    // Handle different data structures from AI responses
    // data.content contains the actual content (title, subtitle, etc.)
    // data.sectionType tells us what type of section it's for
    const contentToApply = {
        ...data.content,
        sectionType: data.sectionType,
    };
    
    emit('applyContent', contentToApply);
    toast.success('Content applied to section');
    // Don't close - let user continue chatting
};

// Handle multi-step action execution
const handleMultiStepApply = async (actions: Array<{ type: string; [key: string]: any }>) => {
    isTyping.value = true;
    const results: string[] = [];
    
    for (let i = 0; i < actions.length; i++) {
        const action = actions[i];
        addMessage('assistant', `Executing step ${i + 1}/${actions.length}: ${action.type}...`);
        
        try {
            switch (action.type) {
                case 'createPage':
                    if (action.useAI) {
                        const pageData = await generatePage({
                            pageType: action.pageType,
                            businessName: props.siteName,
                            businessType: props.aiContext?.site.businessType,
                        });
                        emit('createAIPage', action.pageType, pageData);
                        results.push(`✅ Created ${action.pageType} page`);
                    } else {
                        emit('createPage', action.pageType, action.pageType);
                        results.push(`✅ Created ${action.pageType} page`);
                    }
                    break;
                    
                case 'addSection':
                    emit('addSection', action.sectionType, action.content);
                    results.push(`✅ Added ${action.sectionType} section`);
                    break;
                    
                case 'changeStyle':
                    emit('applyStyle', action.styleChanges);
                    results.push(`✅ Applied style changes`);
                    break;
                    
                case 'generateContent':
                    const content = await generateContent({
                        sectionType: action.sectionType || 'hero',
                        businessName: props.siteName,
                        businessType: props.aiContext?.site.businessType,
                    });
                    emit('applyContent', { ...content, sectionType: action.sectionType });
                    results.push(`✅ Generated ${action.contentType || 'content'}`);
                    break;
            }
            
            // Small delay between actions for UI feedback
            await new Promise(resolve => setTimeout(resolve, 500));
        } catch (e) {
            results.push(`❌ Failed: ${action.type}`);
        }
    }
    
    isTyping.value = false;
    addMessage('assistant', `Completed all steps:\n\n${results.join('\n')}`);
    toast.success('Multi-step actions completed');
};

// Simple toast for feedback
const toast = {
    success: (msg: string) => console.log('✓', msg),
};

const copyToClipboard = async (text: string) => {
    await navigator.clipboard.writeText(text);
};

const regenerateMessage = async (messageId: string, data?: { type: string; sectionType?: string }) => {
    // Record that user wanted to retry (implicit rejection of previous response)
    if (data?.type) {
        recordFeedback(data.type, false, data.sectionType);
    }
    
    const index = messages.value.findIndex(m => m.id === messageId);
    if (index > 0) {
        const userMessage = messages.value[index - 1];
        if (userMessage.role === 'user') {
            messages.value = messages.value.slice(0, index);
            userInput.value = userMessage.content;
            await sendMessage();
        }
    }
};

const handleGenerate = async (params: any) => {
    try {
        generatedContent.value = await generateContent(params);
    } catch (e) {
        console.error('Generation failed:', e);
    }
};

const handleApplyGenerated = () => {
    if (generatedContent.value) {
        emit('applyContent', generatedContent.value);
        emit('close');
    }
};

const handleImprove = async (params: any) => {
    return await improveText(params);
};

const handleTranslate = async (params: any) => {
    return await translate(params);
};

const handleSEO = async (title: string, content: string) => {
    return await generateMeta(title, content);
};

const handleColors = async (businessType: string, mood: string) => {
    return await suggestColors(businessType, mood);
};

// Initialize
onMounted(async () => {
    // Load conversation history from localStorage
    loadConversation();
    
    await checkStatus();
    providerName.value = provider.value;
    console.log('After checkStatus:', { isAvailable: isAvailable.value, provider: provider.value, providerName: providerName.value });
    // Load feedback stats from database
    await loadFeedbackStats();
});

// Debug watcher
watch([isAvailable, provider], ([available, prov]) => {
    console.log('AI Status Changed:', { isAvailable: available, provider: prov });
});

watch(() => props.isOpen, (isOpen) => {
    if (isOpen && messages.value.length === 0) {
        const greeting = props.aiContext?.selectedSection
            ? `Hi! I see you're working on the ${props.aiContext.selectedSection.type} section. How can I help?`
            : `Hi! I'm your AI assistant for ${props.siteName}. What would you like to create today?`;
        addMessage('assistant', greeting);
    }
});
</script>
