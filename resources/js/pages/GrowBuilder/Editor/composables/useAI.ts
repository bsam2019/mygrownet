import { ref, computed } from 'vue';
import axios from 'axios';

interface AIStatus {
    available: boolean;
    provider?: string;
}

interface GenerateContentParams {
    sectionType: string;
    businessName?: string;
    businessType?: string;
    businessDescription?: string;
    tone?: 'professional' | 'friendly' | 'casual' | 'formal' | 'playful';
}

interface ImproveTextParams {
    text: string;
    style?: 'professional' | 'friendly' | 'casual' | 'formal' | 'persuasive' | 'concise';
    instruction?: string;
}

interface TranslateParams {
    text: string;
    targetLanguage: 'bem' | 'nya' | 'ton' | 'loz' | 'sw' | 'fr';
}

interface ColorPalette {
    primary: string;
    secondary: string;
    accent: string;
    background: string;
    text: string;
}

interface Testimonial {
    name: string;
    role: string;
    company: string;
    content: string;
    rating: number;
}

interface FAQ {
    question: string;
    answer: string;
}

export function useAI(siteId: number) {
    const isLoading = ref(false);
    const error = ref<string | null>(null);
    const isAvailable = ref(false);
    const provider = ref<string>('');

    const baseUrl = `/growbuilder/sites/${siteId}/ai`;

    // Check AI availability
    const checkStatus = async (): Promise<boolean> => {
        try {
            const response = await axios.get<AIStatus>('/growbuilder/ai/status');
            isAvailable.value = response.data.available;
            provider.value = response.data.provider || '';
            return response.data.available;
        } catch (e) {
            isAvailable.value = false;
            return false;
        }
    };

    // Generic request handler with better error handling
    const makeRequest = async <T>(
        endpoint: string,
        data: Record<string, any>,
        extractKey?: string
    ): Promise<T> => {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await axios.post(`${baseUrl}/${endpoint}`, data);
            return extractKey ? response.data[extractKey] : response.data;
        } catch (e: any) {
            const errorMessage = e.response?.data?.error || e.response?.data?.message || 'Request failed';
            error.value = errorMessage;
            throw new Error(errorMessage);
        } finally {
            isLoading.value = false;
        }
    };

    // Record feedback when user applies or rejects AI suggestions
    // Includes business_type for industry-specific global learning
    const recordFeedback = async (params: {
        actionType: string;
        sectionType?: string;
        businessType?: string;
        applied: boolean;
        userMessage?: string;
        aiResponse?: string;
        context?: Record<string, any>;
    }): Promise<void> => {
        try {
            await axios.post(`${baseUrl}/feedback`, {
                action_type: params.actionType,
                section_type: params.sectionType,
                business_type: params.businessType,
                applied: params.applied,
                user_message: params.userMessage,
                ai_response: params.aiResponse,
                context: params.context,
            });
        } catch (e) {
            // Silently fail - feedback is not critical
            console.warn('Failed to record AI feedback:', e);
        }
    };

    // Get feedback statistics - combines site, industry, and global learning
    const getFeedbackStats = async (businessType?: string): Promise<{
        stats: {
            total: number;
            applied: number;
            rejected: number;
            acceptance_rate: number;
            by_action_type: Record<string, { applied: number; rejected: number }>;
            by_section_type: Record<string, { applied: number; rejected: number }>;
            preferred_actions: string[];
            avoided_actions: string[];
        };
        combined: {
            primary: any;
            source: string;
            confidence: string;
        };
        recent: Array<{ type: string; section: string; applied: boolean; timestamp: string }>;
        topContent: string[];
        aiInsights: string;
    }> => {
        try {
            const url = businessType 
                ? `${baseUrl}/feedback-stats?business_type=${encodeURIComponent(businessType)}`
                : `${baseUrl}/feedback-stats`;
            const response = await axios.get(url);
            return response.data;
        } catch (e) {
            return {
                stats: {
                    total: 0,
                    applied: 0,
                    rejected: 0,
                    acceptance_rate: 0,
                    by_action_type: {},
                    by_section_type: {},
                    preferred_actions: [],
                },
                recent: [],
            };
        }
    };

    // Generate section content
    const generateContent = async (params: GenerateContentParams) => {
        return makeRequest<any>('generate-content', {
            section_type: params.sectionType,
            business_name: params.businessName,
            business_type: params.businessType,
            business_description: params.businessDescription,
            tone: params.tone || 'professional',
        }, 'content');
    };

    // Generate SEO meta
    const generateMeta = async (pageTitle: string, pageContent: string) => {
        const response = await makeRequest<any>('generate-meta', {
            page_title: pageTitle,
            page_content: pageContent,
        });

        return {
            metaDescription: response.meta_description,
            keywords: response.keywords,
        };
    };

    // Suggest color palette
    const suggestColors = async (businessType?: string, mood?: string): Promise<ColorPalette> => {
        return makeRequest<ColorPalette>('suggest-colors', {
            business_type: businessType,
            mood: mood,
        }, 'palette');
    };

    // Improve text
    const improveText = async (params: ImproveTextParams): Promise<string> => {
        return makeRequest<string>('improve-text', {
            text: params.text,
            style: params.style || 'professional',
            instruction: params.instruction,
        }, 'improved_text');
    };

    // Translate content
    const translate = async (params: TranslateParams): Promise<string> => {
        return makeRequest<string>('translate', {
            text: params.text,
            target_language: params.targetLanguage,
        }, 'translated_text');
    };

    // Suggest images
    const suggestImages = async (sectionType: string, content: string, businessType?: string): Promise<string[]> => {
        return makeRequest<string[]>('suggest-images', {
            section_type: sectionType,
            content: content,
            business_type: businessType,
        }, 'keywords');
    };

    // Generate testimonials
    const generateTestimonials = async (businessType?: string, count?: number): Promise<Testimonial[]> => {
        return makeRequest<Testimonial[]>('generate-testimonials', {
            business_type: businessType,
            count: count || 3,
        }, 'testimonials');
    };

    // Generate FAQs
    const generateFAQs = async (businessType?: string, count?: number): Promise<FAQ[]> => {
        return makeRequest<FAQ[]>('generate-faqs', {
            business_type: businessType,
            count: count || 5,
        }, 'faqs');
    };

    // Generate a complete page with AI content
    const generatePage = async (params: {
        pageType: 'about' | 'services' | 'contact' | 'pricing' | 'faq' | 'testimonials' | 'gallery' | 'blog';
        businessName?: string;
        businessType?: string;
        businessDescription?: string;
        existingColors?: Record<string, string>;
    }): Promise<{
        title: string;
        subtitle: string;
        sections: Array<{
            type: string;
            content: Record<string, any>;
            style: Record<string, any>;
        }>;
    }> => {
        return makeRequest('generate-page', {
            page_type: params.pageType,
            business_name: params.businessName,
            business_type: params.businessType,
            business_description: params.businessDescription,
            existing_colors: params.existingColors,
        }, 'page');
    };

    // Generate a page with detailed/comprehensive requirements
    // Handles complex prompts with specific sections, tone, style, and content guidelines
    const generatePageDetailed = async (params: {
        pageType: string;
        businessName?: string;
        businessType?: string;
        existingColors?: Record<string, string>;
        detailedRequirements: {
            sections?: string[];
            tone?: string;
            businessContext?: string;
            targetAudience?: string;
            contentGuidelines?: string[];
            stylePreferences?: string;
        };
    }): Promise<{
        title: string;
        subtitle: string;
        sections: Array<{
            type: string;
            content: Record<string, any>;
            style: Record<string, any>;
        }>;
    }> => {
        return makeRequest('generate-page-detailed', {
            page_type: params.pageType,
            business_name: params.businessName,
            business_type: params.businessType,
            existing_colors: params.existingColors,
            detailed_requirements: params.detailedRequirements,
        }, 'page');
    };

    // Classify user intent using AI with enhanced context (makes assistant smarter)
    // Supports: conversation memory, rich context, multi-step actions, clarification
    const classifyIntent = async (message: string, context?: {
        selectedSection?: string;
        selectedSectionContent?: string;
        currentPage?: string;
        siteName?: string;
        businessType?: string;
        pageSections?: string[];
        sitePages?: string[];
        siteColors?: Record<string, string>;
        lastAction?: string;
        conversationHistory?: Array<{ role: string; content: string }>;
    }): Promise<{
        intent: string;
        confidence: number;
        params: Record<string, any>;
        needsClarification?: boolean;
        originalIntent?: string;
    }> => {
        return makeRequest('classify-intent', {
            message,
            context: context || {},
        }, 'intent');
    };

    // Smart content suggestion based on context
    const smartSuggest = async (context: {
        sectionType?: string;
        currentContent?: string;
        action: 'improve' | 'expand' | 'shorten' | 'rephrase';
    }): Promise<string> => {
        if (!context.currentContent) {
            throw new Error('No content provided');
        }

        const instructions: Record<string, string> = {
            improve: 'Make this text more engaging and professional',
            expand: 'Expand this text with more details',
            shorten: 'Make this text more concise',
            rephrase: 'Rephrase this text in a different way',
        };

        return improveText({
            text: context.currentContent,
            instruction: instructions[context.action],
        });
    };

    // Smart Chat - AI-first approach that handles understanding AND content generation
    // This is the primary method for intelligent conversation
    const smartChat = async (
        message: string,
        context?: {
            selectedSection?: string;
            selectedSectionContent?: string;
            currentPage?: string;
            siteName?: string;
            businessType?: string;
            pageSections?: string[];
            sitePages?: string[];
            siteColors?: Record<string, string>;
            lastAction?: string;
            conversationHistory?: Array<{ role: string; content: string }>;
        }
    ): Promise<{
        action: string;
        message: string;
        data: Record<string, any> | null;
    }> => {
        return makeRequest('smart-chat', {
            message,
            context: context || {},
        }, 'result');
    };

    return {
        isLoading,
        error,
        isAvailable,
        provider,
        checkStatus,
        generateContent,
        generateMeta,
        suggestColors,
        improveText,
        translate,
        suggestImages,
        generateTestimonials,
        generateFAQs,
        generatePage,
        generatePageDetailed,
        classifyIntent,
        smartSuggest,
        smartChat,
        recordFeedback,
        getFeedbackStats,
    };
}

// Language options for translation
export const languageOptions = [
    { value: 'bem', label: 'Bemba' },
    { value: 'nya', label: 'Nyanja' },
    { value: 'ton', label: 'Tonga' },
    { value: 'loz', label: 'Lozi' },
    { value: 'sw', label: 'Swahili' },
    { value: 'fr', label: 'French' },
];

// Tone options
export const toneOptions = [
    { value: 'professional', label: 'Professional' },
    { value: 'friendly', label: 'Friendly' },
    { value: 'casual', label: 'Casual' },
    { value: 'formal', label: 'Formal' },
    { value: 'playful', label: 'Playful' },
];

// Style options for text improvement
export const styleOptions = [
    { value: 'professional', label: 'Professional' },
    { value: 'friendly', label: 'Friendly' },
    { value: 'casual', label: 'Casual' },
    { value: 'formal', label: 'Formal' },
    { value: 'persuasive', label: 'Persuasive' },
    { value: 'concise', label: 'Concise' },
];

// Business type options
export const businessTypeOptions = [
    { value: 'restaurant', label: 'Restaurant/Food' },
    { value: 'church', label: 'Church/Ministry' },
    { value: 'tutor', label: 'Tutor/Education' },
    { value: 'portfolio', label: 'Portfolio/Creative' },
    { value: 'retail', label: 'Retail/Shop' },
    { value: 'services', label: 'Professional Services' },
    { value: 'healthcare', label: 'Healthcare' },
    { value: 'technology', label: 'Technology' },
    { value: 'real-estate', label: 'Real Estate' },
    { value: 'agriculture', label: 'Agriculture' },
    { value: 'transport', label: 'Transport/Logistics' },
    { value: 'beauty', label: 'Beauty/Salon' },
    { value: 'fitness', label: 'Fitness/Gym' },
    { value: 'events', label: 'Events/Planning' },
];
