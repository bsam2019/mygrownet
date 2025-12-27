import { computed, Ref } from 'vue';

export interface AIContext {
    // Site info
    site: {
        id: number;
        name: string;
        description?: string;
        businessType?: string;
        subdomain: string;
    };
    // Current page
    currentPage: {
        id: number;
        title: string;
        slug: string;
        sectionsCount: number;
        sectionTypes: string[];
    } | null;
    // All pages summary
    pages: Array<{
        id: number;
        title: string;
        slug: string;
        isHome: boolean;
    }>;
    // Currently selected section
    selectedSection: {
        id: string;
        type: string;
        hasContent: boolean;
        contentPreview?: string;
    } | null;
    // Theme info
    theme: {
        primaryColor?: string;
        secondaryColor?: string;
        fontFamily?: string;
    };
    // What the user is currently doing
    userIntent?: 'editing_section' | 'adding_section' | 'browsing' | 'styling';
}

export interface Section {
    id: string;
    type: string;
    content?: any;
    style?: any;
}

export interface Page {
    id: number;
    title: string;
    slug: string;
    is_home?: boolean;
}

export interface Site {
    id: number;
    name: string;
    description?: string;
    subdomain: string;
    settings?: {
        businessType?: string;
        theme?: {
            primaryColor?: string;
            secondaryColor?: string;
            fontFamily?: string;
        };
    };
}

export function useAIContext(
    site: Ref<Site>,
    pages: Ref<Page[]>,
    currentPage: Ref<Page | null>,
    sections: Ref<Section[]>,
    selectedSectionId: Ref<string | null>
) {
    // Build the full context object
    const context = computed<AIContext>(() => {
        const selectedSection = selectedSectionId.value
            ? sections.value.find(s => s.id === selectedSectionId.value)
            : null;

        return {
            site: {
                id: site.value.id,
                name: site.value.name,
                description: site.value.description,
                businessType: site.value.settings?.businessType,
                subdomain: site.value.subdomain,
            },
            currentPage: currentPage.value ? {
                id: currentPage.value.id,
                title: currentPage.value.title,
                slug: currentPage.value.slug,
                sectionsCount: sections.value.length,
                sectionTypes: sections.value.map(s => s.type),
            } : null,
            pages: pages.value.map(p => ({
                id: p.id,
                title: p.title,
                slug: p.slug,
                isHome: p.is_home || false,
            })),
            selectedSection: selectedSection ? {
                id: selectedSection.id,
                type: selectedSection.type,
                hasContent: !!selectedSection.content,
                contentPreview: getContentPreview(selectedSection),
            } : null,
            theme: {
                primaryColor: site.value.settings?.theme?.primaryColor,
                secondaryColor: site.value.settings?.theme?.secondaryColor,
                fontFamily: site.value.settings?.theme?.fontFamily,
            },
            userIntent: determineUserIntent(selectedSectionId.value, sections.value),
        };
    });

    // Generate a natural language summary for the AI
    const contextSummary = computed(() => {
        const ctx = context.value;
        const parts: string[] = [];

        parts.push(`Site: "${ctx.site.name}"`);
        if (ctx.site.businessType) {
            parts.push(`(${ctx.site.businessType} business)`);
        }

        if (ctx.currentPage) {
            parts.push(`Currently editing "${ctx.currentPage.title}" page`);
            parts.push(`with ${ctx.currentPage.sectionsCount} sections`);
            if (ctx.currentPage.sectionTypes.length > 0) {
                parts.push(`(${ctx.currentPage.sectionTypes.join(', ')})`);
            }
        }

        if (ctx.selectedSection) {
            parts.push(`Selected: ${ctx.selectedSection.type} section`);
            if (ctx.selectedSection.contentPreview) {
                parts.push(`Content: "${ctx.selectedSection.contentPreview}"`);
            }
        }

        if (ctx.pages.length > 1) {
            parts.push(`Site has ${ctx.pages.length} pages: ${ctx.pages.map(p => p.title).join(', ')}`);
        }

        return parts.join('. ');
    });

    // Generate smart suggestions based on context
    const smartSuggestions = computed(() => {
        const ctx = context.value;
        const suggestions: string[] = [];

        // Suggestions based on what's missing
        if (ctx.currentPage) {
            const types = ctx.currentPage.sectionTypes;
            
            if (!types.includes('hero')) {
                suggestions.push('Add a hero section to grab attention');
            }
            if (!types.includes('about') && !types.includes('features')) {
                suggestions.push('Tell visitors about your business');
            }
            if (!types.includes('cta') && !types.includes('contact')) {
                suggestions.push('Add a call-to-action or contact section');
            }
            if (!types.includes('testimonials') && types.length > 2) {
                suggestions.push('Add testimonials to build trust');
            }
        }

        // Suggestions based on selected section
        if (ctx.selectedSection) {
            suggestions.push(`Improve the ${ctx.selectedSection.type} content`);
            suggestions.push(`Suggest better copy for this section`);
        }

        // General suggestions
        if (!ctx.theme.primaryColor) {
            suggestions.push('Suggest a color palette for your brand');
        }

        return suggestions.slice(0, 4);
    });

    // Build system prompt context for AI
    const systemPromptContext = computed(() => {
        const ctx = context.value;
        
        return `
You are helping build a website for "${ctx.site.name}"${ctx.site.businessType ? `, a ${ctx.site.businessType} business` : ''}.
${ctx.site.description ? `Business description: ${ctx.site.description}` : ''}

Current state:
- Editing page: "${ctx.currentPage?.title || 'None selected'}"
- Page has ${ctx.currentPage?.sectionsCount || 0} sections: ${ctx.currentPage?.sectionTypes.join(', ') || 'none'}
- ${ctx.selectedSection ? `Currently selected: ${ctx.selectedSection.type} section` : 'No section selected'}
- Site has ${ctx.pages.length} pages total: ${ctx.pages.map(p => p.title).join(', ')}

When generating content:
- Use the business name "${ctx.site.name}" naturally
- Match the existing tone and style
- Consider what sections already exist to avoid repetition
- Suggest content that complements the existing page structure
`.trim();
    });

    return {
        context,
        contextSummary,
        smartSuggestions,
        systemPromptContext,
    };
}

// Helper: Get a preview of section content
function getContentPreview(section: Section): string | undefined {
    if (!section.content) return undefined;
    
    const content = section.content;
    if (content.title) return content.title.substring(0, 50);
    if (content.heading) return content.heading.substring(0, 50);
    if (content.text) return content.text.substring(0, 50);
    if (typeof content === 'string') return content.substring(0, 50);
    
    return undefined;
}

// Helper: Determine what the user is likely trying to do
function determineUserIntent(
    selectedSectionId: string | null,
    sections: Section[]
): AIContext['userIntent'] {
    if (selectedSectionId) {
        return 'editing_section';
    }
    if (sections.length === 0) {
        return 'adding_section';
    }
    return 'browsing';
}
