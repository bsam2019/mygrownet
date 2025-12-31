/**
 * Section Templates Configuration
 * Defines render templates and layouts for dynamic section rendering
 * 
 * This enables:
 * 1. AI to understand section structure and generate valid content
 * 2. Dynamic rendering of sections without individual Vue components
 * 3. Easy addition of new section types
 */

export type LayoutType = 
    | 'hero-centered'      // Full-width centered content with background
    | 'hero-split'         // Split layout with image on one side
    | 'content-image'      // Text content with optional image
    | 'grid-cards'         // Grid of cards (services, features, team)
    | 'testimonial-cards'  // Testimonial-specific layout
    | 'pricing-cards'      // Pricing table layout
    | 'faq-accordion'      // Accordion-style FAQ
    | 'contact-form'       // Contact form with info
    | 'gallery-grid'       // Image gallery grid
    | 'stats-row'          // Statistics in a row
    | 'video-embed'        // Video with optional text
    | 'map-embed'          // Map with address
    | 'text-block'         // Simple text content
    | 'divider'            // Visual separator
    | 'cta-banner'         // Call-to-action banner
    | 'product-grid';      // Product cards grid

export interface SectionTemplate {
    type: string;
    name: string;
    description: string;
    layout: LayoutType;
    category: 'structure' | 'content' | 'social-proof' | 'commerce' | 'forms' | 'media';
    
    // Content structure - what fields the section expects
    contentStructure: {
        required: string[];
        optional: string[];
    };
    
    // Default content for new sections
    defaultContent: Record<string, any>;
    
    // Default style
    defaultStyle: {
        backgroundColor: string;
        textColor: string;
        [key: string]: any;
    };
    
    // AI generation hints
    aiHints: {
        contentGuidelines: string;
        examplePrompts: string[];
        itemCount?: { min: number; max: number; default: number };
    };
}

/**
 * Section Templates Registry
 */
export const sectionTemplates: Record<string, SectionTemplate> = {
    hero: {
        type: 'hero',
        name: 'Hero',
        description: 'Full-screen impact section with headline, subtitle, and call-to-action',
        layout: 'hero-centered',
        category: 'structure',
        contentStructure: {
            required: ['title'],
            optional: ['subtitle', 'buttonText', 'buttonLink', 'secondaryButtonText', 'secondaryButtonLink', 'backgroundImage', 'videoBackground', 'textPosition', 'layout', 'image', 'slides', 'autoPlay', 'slideInterval'],
        },
        defaultContent: {
            layout: 'centered',
            title: 'Welcome to Our Business',
            subtitle: 'We help you grow and succeed with innovative solutions',
            buttonText: 'Get Started',
            buttonLink: '#contact',
            textPosition: 'center',
        },
        defaultStyle: {
            backgroundColor: '#1e40af',
            textColor: '#ffffff',
        },
        aiHints: {
            contentGuidelines: 'Create compelling, action-oriented headline. Subtitle should expand on value proposition. Include clear CTA. Layout options: centered (default), split-right, split-left, video, slideshow.',
            examplePrompts: ['Create a hero for a tech startup', 'Make a welcoming hero for a restaurant'],
        },
    },

    'page-header': {
        type: 'page-header',
        name: 'Page Header',
        description: 'Title banner for inner pages with optional background',
        layout: 'hero-centered',
        category: 'structure',
        contentStructure: {
            required: ['title'],
            optional: ['subtitle', 'backgroundImage', 'textPosition'],
        },
        defaultContent: {
            title: 'Page Title',
            subtitle: '',
            textPosition: 'center',
        },
        defaultStyle: {
            backgroundColor: '#1e40af',
            textColor: '#ffffff',
        },
        aiHints: {
            contentGuidelines: 'Keep title concise. Subtitle is optional but can provide context.',
            examplePrompts: ['Create header for About page', 'Make a Services page header'],
        },
    },

    about: {
        type: 'about',
        name: 'About',
        description: 'Company story section with text and optional image',
        layout: 'content-image',
        category: 'content',
        contentStructure: {
            required: ['title', 'description'],
            optional: ['image', 'layout'],
        },
        defaultContent: {
            layout: 'image-right',
            title: 'About Us',
            description: 'Tell your company story here. Share your mission, values, and what makes you unique.',
        },
        defaultStyle: {
            backgroundColor: '#ffffff',
            textColor: '#111827',
        },
        aiHints: {
            contentGuidelines: 'Write authentic, engaging company story. Focus on mission, values, and unique selling points. Layout options: image-right (default), image-left, image-top.',
            examplePrompts: ['Write about section for a law firm', 'Create about content for a bakery'],
        },
    },

    services: {
        type: 'services',
        name: 'Services',
        description: 'Grid of service cards with icons and descriptions',
        layout: 'grid-cards',
        category: 'content',
        contentStructure: {
            required: ['title', 'items'],
            optional: ['subtitle', 'textPosition', 'layout', 'columns'],
        },
        defaultContent: {
            layout: 'grid',
            title: 'Our Services',
            subtitle: 'What we offer',
            columns: 3,
            items: [
                { icon: 'chart', title: 'Service 1', description: 'Description of service 1' },
                { icon: 'users', title: 'Service 2', description: 'Description of service 2' },
                { icon: 'cog', title: 'Service 3', description: 'Description of service 3' },
            ],
        },
        defaultStyle: {
            backgroundColor: '#f9fafb',
            textColor: '#111827',
        },
        aiHints: {
            contentGuidelines: 'Create 3-6 services with benefit-focused titles. Each should have icon, title, and brief description. Layout options: grid (default), list, cards-images, alternating.',
            examplePrompts: ['List services for a digital agency', 'Create services for a cleaning company'],
            itemCount: { min: 3, max: 6, default: 3 },
        },
    },

    features: {
        type: 'features',
        name: 'Features',
        description: 'List of features or benefits',
        layout: 'grid-cards',
        category: 'content',
        contentStructure: {
            required: ['title', 'items'],
            optional: ['subtitle', 'textPosition', 'layout'],
        },
        defaultContent: {
            layout: 'grid',
            title: 'Why Choose Us',
            items: [
                { icon: 'check', title: 'Feature 1', description: 'Benefit description' },
                { icon: 'check', title: 'Feature 2', description: 'Benefit description' },
                { icon: 'check', title: 'Feature 3', description: 'Benefit description' },
            ],
        },
        defaultStyle: {
            backgroundColor: '#ffffff',
            textColor: '#111827',
        },
        aiHints: {
            contentGuidelines: 'Focus on benefits, not just features. Use action-oriented language. Layout options: grid (default), checklist, steps.',
            examplePrompts: ['List features for a SaaS product', 'Create benefits for a gym membership'],
            itemCount: { min: 3, max: 8, default: 4 },
        },
    },

    testimonials: {
        type: 'testimonials',
        name: 'Testimonials',
        description: 'Customer reviews and testimonials',
        layout: 'testimonial-cards',
        category: 'social-proof',
        contentStructure: {
            required: ['title', 'items'],
            optional: ['subtitle', 'textPosition', 'layout', 'autoPlay'],
        },
        defaultContent: {
            layout: 'grid',
            title: 'What Our Clients Say',
            items: [
                { name: 'John Banda', role: 'CEO, Company', text: 'Great service!', rating: 5 },
                { name: 'Mary Phiri', role: 'Manager', text: 'Highly recommended!', rating: 5 },
            ],
        },
        defaultStyle: {
            backgroundColor: '#f9fafb',
            textColor: '#111827',
        },
        aiHints: {
            contentGuidelines: 'Create realistic testimonials with specific details. Use Zambian names when appropriate. Include role/company. Layout options: grid (default), carousel, single, photos.',
            examplePrompts: ['Generate testimonials for a hotel', 'Create reviews for a software company'],
            itemCount: { min: 2, max: 6, default: 3 },
        },
    },

    pricing: {
        type: 'pricing',
        name: 'Pricing',
        description: 'Pricing plans and packages',
        layout: 'pricing-cards',
        category: 'commerce',
        contentStructure: {
            required: ['title', 'plans'],
            optional: ['subtitle', 'textPosition', 'layout'],
        },
        defaultContent: {
            layout: 'cards',
            title: 'Pricing Plans',
            plans: [
                { name: 'Basic', price: 'K99/mo', features: ['Feature 1', 'Feature 2'], buttonText: 'Get Started' },
                { name: 'Pro', price: 'K199/mo', features: ['All Basic', 'Feature 3', 'Feature 4'], popular: true, buttonText: 'Get Started' },
                { name: 'Enterprise', price: 'Contact Us', features: ['All Pro', 'Custom features'], buttonText: 'Contact' },
            ],
        },
        defaultStyle: {
            backgroundColor: '#ffffff',
            textColor: '#111827',
        },
        aiHints: {
            contentGuidelines: 'Create 2-4 pricing tiers. Use K (Kwacha) for Zambian businesses. Mark one as popular. Layout options: cards (default), table, toggle.',
            examplePrompts: ['Create pricing for a gym', 'Design pricing tiers for a SaaS'],
            itemCount: { min: 2, max: 4, default: 3 },
        },
    },

    contact: {
        type: 'contact',
        name: 'Contact',
        description: 'Contact form with business information',
        layout: 'contact-form',
        category: 'forms',
        contentStructure: {
            required: ['title'],
            optional: ['description', 'email', 'phone', 'address', 'showForm', 'textPosition', 'layout', 'mapEmbedUrl'],
        },
        defaultContent: {
            layout: 'stacked',
            title: 'Contact Us',
            description: 'Get in touch with us',
            email: 'hello@example.com',
            phone: '+260 97 123 4567',
            showForm: true,
        },
        defaultStyle: {
            backgroundColor: '#ffffff',
            textColor: '#111827',
        },
        aiHints: {
            contentGuidelines: 'Include realistic contact details. Use +260 format for Zambian phone numbers. Layout options: stacked (default), side-by-side, with-map.',
            examplePrompts: ['Create contact section for a restaurant', 'Design contact for a law firm'],
        },
    },

    faq: {
        type: 'faq',
        name: 'FAQ',
        description: 'Frequently asked questions accordion',
        layout: 'faq-accordion',
        category: 'content',
        contentStructure: {
            required: ['title', 'items'],
            optional: ['textPosition', 'layout'],
        },
        defaultContent: {
            layout: 'accordion',
            title: 'Frequently Asked Questions',
            items: [
                { question: 'Question 1?', answer: 'Answer to question 1.' },
                { question: 'Question 2?', answer: 'Answer to question 2.' },
            ],
        },
        defaultStyle: {
            backgroundColor: '#ffffff',
            textColor: '#111827',
        },
        aiHints: {
            contentGuidelines: 'Create relevant FAQs for the business type. Answers should be helpful and concise. Layout options: accordion (default), two-column, list.',
            examplePrompts: ['Generate FAQs for an e-commerce store', 'Create FAQs for a dental clinic'],
            itemCount: { min: 3, max: 10, default: 5 },
        },
    },

    team: {
        type: 'team',
        name: 'Team',
        description: 'Team member profiles',
        layout: 'grid-cards',
        category: 'content',
        contentStructure: {
            required: ['title', 'items'],
            optional: ['textPosition', 'layout'],
        },
        defaultContent: {
            layout: 'grid',
            title: 'Meet Our Team',
            items: [
                { name: 'John Banda', role: 'CEO', bio: 'Founder with 10+ years experience' },
                { name: 'Mary Phiri', role: 'CTO', bio: 'Tech expert and innovator' },
            ],
        },
        defaultStyle: {
            backgroundColor: '#f9fafb',
            textColor: '#111827',
        },
        aiHints: {
            contentGuidelines: 'Use realistic Zambian names. Include role and brief bio. Keep bios professional. Layout options: grid (default), social, compact.',
            examplePrompts: ['Create team section for a startup', 'Design team profiles for a law firm'],
            itemCount: { min: 2, max: 8, default: 4 },
        },
    },

    stats: {
        type: 'stats',
        name: 'Stats',
        description: 'Key statistics and numbers',
        layout: 'stats-row',
        category: 'social-proof',
        contentStructure: {
            required: ['items'],
            optional: ['title', 'textPosition', 'layout'],
        },
        defaultContent: {
            layout: 'row',
            title: 'Our Impact',
            items: [
                { value: '500+', label: 'Happy Clients' },
                { value: '10+', label: 'Years Experience' },
                { value: '1000+', label: 'Projects Completed' },
            ],
        },
        defaultStyle: {
            backgroundColor: '#2563eb',
            textColor: '#ffffff',
        },
        aiHints: {
            contentGuidelines: 'Use impressive but realistic numbers. 3-4 stats maximum. Include + or K/M for large numbers. Layout options: row (default), grid, icons.',
            examplePrompts: ['Create stats for a construction company', 'Generate impact numbers for an NGO'],
            itemCount: { min: 3, max: 4, default: 4 },
        },
    },

    gallery: {
        type: 'gallery',
        name: 'Gallery',
        description: 'Image gallery grid',
        layout: 'gallery-grid',
        category: 'media',
        contentStructure: {
            required: ['title'],
            optional: ['images', 'textPosition', 'layout', 'columns'],
        },
        defaultContent: {
            layout: 'grid',
            title: 'Our Gallery',
            columns: 4,
            images: [],
        },
        defaultStyle: {
            backgroundColor: '#ffffff',
            textColor: '#111827',
        },
        aiHints: {
            contentGuidelines: 'Gallery requires user to upload images. AI can suggest title and layout. Layout options: grid (default), masonry, lightbox.',
            examplePrompts: ['Create gallery section for a restaurant', 'Design portfolio gallery'],
        },
    },

    blog: {
        type: 'blog',
        name: 'Blog',
        description: 'Latest blog posts or news',
        layout: 'grid-cards',
        category: 'content',
        contentStructure: {
            required: ['title'],
            optional: ['description', 'layout', 'columns', 'postsCount', 'showImage', 'showDate', 'showExcerpt', 'showViewAll'],
        },
        defaultContent: {
            title: 'Latest News',
            layout: 'grid',
            columns: 3,
            postsCount: 3,
            showImage: true,
            showDate: true,
            showExcerpt: true,
        },
        defaultStyle: {
            backgroundColor: '#ffffff',
            textColor: '#111827',
        },
        aiHints: {
            contentGuidelines: 'Blog section displays posts from the dashboard. AI can configure display options.',
            examplePrompts: ['Set up blog section', 'Configure news display'],
        },
    },

    products: {
        type: 'products',
        name: 'Products',
        description: 'Product catalog grid with cart functionality',
        layout: 'product-grid',
        category: 'commerce',
        contentStructure: {
            required: ['title'],
            optional: ['subtitle', 'columns', 'limit', 'showCategory', 'showViewAll', 'featuredOnly'],
        },
        defaultContent: {
            title: 'Our Products',
            columns: 3,
            limit: 6,
            showCategory: true,
            showViewAll: true,
        },
        defaultStyle: {
            backgroundColor: '#ffffff',
            textColor: '#111827',
        },
        aiHints: {
            contentGuidelines: 'Products section displays items from the dashboard. AI can configure display options.',
            examplePrompts: ['Set up product showcase', 'Configure shop display'],
        },
    },

    'product-search': {
        type: 'product-search',
        name: 'Product Search',
        description: 'Search bar with category filters for products',
        layout: 'text-block',
        category: 'commerce',
        contentStructure: {
            required: [],
            optional: ['placeholder', 'showCategories', 'showSort'],
        },
        defaultContent: {
            placeholder: 'Search products...',
            showCategories: true,
            showSort: true,
        },
        defaultStyle: {
            backgroundColor: '#ffffff',
            textColor: '#111827',
        },
        aiHints: {
            contentGuidelines: 'Product search widget for filtering products. Configure placeholder text and filter options.',
            examplePrompts: ['Add product search', 'Create shop filter bar'],
        },
    },

    cta: {
        type: 'cta',
        name: 'Call to Action',
        description: 'Call-to-action banner',
        layout: 'cta-banner',
        category: 'structure',
        contentStructure: {
            required: ['title', 'buttonText'],
            optional: ['description', 'buttonLink', 'textPosition', 'layout', 'image'],
        },
        defaultContent: {
            layout: 'banner',
            title: 'Ready to Get Started?',
            description: 'Contact us today to learn more',
            buttonText: 'Contact Us',
            buttonLink: '#contact',
        },
        defaultStyle: {
            backgroundColor: '#2563eb',
            textColor: '#ffffff',
        },
        aiHints: {
            contentGuidelines: 'Create urgency with action-oriented language. Clear, compelling CTA button text. Layout options: banner (default), split, minimal.',
            examplePrompts: ['Create CTA for newsletter signup', 'Design CTA for free consultation'],
        },
    },

    'member-cta': {
        type: 'member-cta',
        name: 'Member Signup',
        description: 'Member registration call-to-action',
        layout: 'cta-banner',
        category: 'structure',
        contentStructure: {
            required: ['title'],
            optional: ['subtitle', 'description', 'benefits', 'registerText', 'registerButtonStyle', 'showLoginLink', 'loginText'],
        },
        defaultContent: {
            title: 'Join Our Community',
            subtitle: 'Become a member today',
            benefits: ['Exclusive content', 'Member discounts', 'Early access'],
            registerText: 'Sign Up Now',
            showLoginLink: true,
        },
        defaultStyle: {
            backgroundColor: '#1e40af',
            textColor: '#ffffff',
        },
        aiHints: {
            contentGuidelines: 'Highlight membership benefits. Create compelling reasons to join.',
            examplePrompts: ['Create member signup for a fitness club', 'Design membership CTA'],
        },
    },

    video: {
        type: 'video',
        name: 'Video',
        description: 'Embedded video section',
        layout: 'video-embed',
        category: 'media',
        contentStructure: {
            required: ['title', 'videoUrl'],
            optional: ['description'],
        },
        defaultContent: {
            title: 'Watch Our Story',
            description: '',
            videoUrl: '',
        },
        defaultStyle: {
            backgroundColor: '#ffffff',
            textColor: '#111827',
        },
        aiHints: {
            contentGuidelines: 'Video section requires user to provide YouTube/Vimeo URL. AI can suggest title.',
            examplePrompts: ['Add video section', 'Create video showcase'],
        },
    },

    map: {
        type: 'map',
        name: 'Map',
        description: 'Google Maps embed with address',
        layout: 'map-embed',
        category: 'forms',
        contentStructure: {
            required: ['title'],
            optional: ['embedUrl', 'address', 'showAddress'],
        },
        defaultContent: {
            title: 'Find Us',
            showAddress: true,
        },
        defaultStyle: {
            backgroundColor: '#ffffff',
            textColor: '#111827',
        },
        aiHints: {
            contentGuidelines: 'Map requires Google Maps embed URL. AI can suggest title and address format.',
            examplePrompts: ['Add location map', 'Create find us section'],
        },
    },

    text: {
        type: 'text',
        name: 'Text Block',
        description: 'Rich text content block',
        layout: 'text-block',
        category: 'content',
        contentStructure: {
            required: ['content'],
            optional: [],
        },
        defaultContent: {
            content: '<p>Enter your text content here...</p>',
        },
        defaultStyle: {
            backgroundColor: '#ffffff',
            textColor: '#111827',
        },
        aiHints: {
            contentGuidelines: 'Create well-formatted text content. Can include headings, lists, and paragraphs.',
            examplePrompts: ['Add text block about our history', 'Create terms and conditions section'],
        },
    },

    divider: {
        type: 'divider',
        name: 'Divider',
        description: 'Visual separator between sections',
        layout: 'divider',
        category: 'structure',
        contentStructure: {
            required: [],
            optional: ['style', 'height'],
        },
        defaultContent: {
            style: 'line',
            height: 40,
        },
        defaultStyle: {
            backgroundColor: '#ffffff',
            textColor: '#e5e7eb',
        },
        aiHints: {
            contentGuidelines: 'Simple visual separator. Style can be line, dots, or space.',
            examplePrompts: ['Add divider', 'Insert separator'],
        },
    },
};

/**
 * Get template for a section type
 */
export function getSectionTemplate(type: string): SectionTemplate | undefined {
    return sectionTemplates[type];
}

/**
 * Get all section types by category
 */
export function getSectionsByCategory(): Record<string, SectionTemplate[]> {
    const byCategory: Record<string, SectionTemplate[]> = {};
    
    Object.values(sectionTemplates).forEach(template => {
        if (!byCategory[template.category]) {
            byCategory[template.category] = [];
        }
        byCategory[template.category].push(template);
    });
    
    return byCategory;
}

/**
 * Generate AI prompt context for section generation
 */
export function getAIPromptForSection(type: string): string {
    const template = sectionTemplates[type];
    if (!template) return '';
    
    return `
Section Type: ${template.name}
Description: ${template.description}
Required Fields: ${template.contentStructure.required.join(', ')}
Optional Fields: ${template.contentStructure.optional.join(', ')}
Guidelines: ${template.aiHints.contentGuidelines}
${template.aiHints.itemCount ? `Item Count: ${template.aiHints.itemCount.min}-${template.aiHints.itemCount.max} (default: ${template.aiHints.itemCount.default})` : ''}
`;
}

/**
 * Get all section types for AI context
 */
export function getAllSectionTypesForAI(): string {
    return Object.values(sectionTemplates)
        .map(t => `- ${t.type}: ${t.description}`)
        .join('\n');
}
