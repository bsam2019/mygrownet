/**
 * Section Block Definitions
 * Defines all available section types for the page builder
 */

import {
    SparklesIcon,
    Bars3Icon,
    MegaphoneIcon,
    MinusIcon,
    DocumentTextIcon,
    RectangleGroupIcon,
    CheckBadgeIcon,
    UserGroupIcon,
    QuestionMarkCircleIcon,
    ChartBarIcon,
    PhotoIcon,
    PlayCircleIcon,
    MapPinIcon,
    ChatBubbleBottomCenterTextIcon,
    NewspaperIcon,
    CurrencyDollarIcon,
    ShoppingBagIcon,
    EnvelopeIcon,
    UserPlusIcon,
    ClockIcon,
    RocketLaunchIcon,
    BuildingOffice2Icon,
    FilmIcon,
    ChatBubbleLeftRightIcon,
} from '@heroicons/vue/24/outline';
import type { SectionBlock, SectionCategory } from '../types';

/**
 * All available section blocks organized by category
 */
export const sectionBlocks: SectionBlock[] = [
    // Layout
    { type: 'hero', name: 'Hero', icon: SparklesIcon, category: 'Layout', description: 'Eye-catching header section' },
    { type: 'video-hero', name: 'Video Hero', icon: FilmIcon, category: 'Layout', description: 'Hero with video background' },
    { type: 'page-header', name: 'Page Header', icon: Bars3Icon, category: 'Layout', description: 'Title banner for inner pages' },
    { type: 'cta', name: 'Call to Action', icon: MegaphoneIcon, category: 'Layout', description: 'Drive conversions' },
    { type: 'cta-banner', name: 'CTA Banner', icon: RocketLaunchIcon, category: 'Layout', description: 'Full-width conversion banner' },
    { type: 'member-cta', name: 'Member Signup', icon: UserPlusIcon, category: 'Layout', description: 'Promote membership registration' },
    { type: 'divider', name: 'Divider', icon: MinusIcon, category: 'Layout', description: 'Visual separator' },
    
    // Content
    { type: 'about', name: 'About', icon: DocumentTextIcon, category: 'Content', description: 'Tell your story' },
    { type: 'services', name: 'Services', icon: RectangleGroupIcon, category: 'Content', description: 'Showcase what you offer' },
    { type: 'features', name: 'Features', icon: CheckBadgeIcon, category: 'Content', description: 'Highlight key features' },
    { type: 'team', name: 'Team', icon: UserGroupIcon, category: 'Content', description: 'Meet the team' },
    { type: 'faq', name: 'FAQ', icon: QuestionMarkCircleIcon, category: 'Content', description: 'Frequently asked questions' },
    { type: 'stats', name: 'Stats/Counter', icon: ChartBarIcon, category: 'Content', description: 'Show impressive numbers' },
    { type: 'timeline', name: 'Timeline', icon: ClockIcon, category: 'Content', description: 'Company history & milestones' },
    { type: 'text', name: 'Text Block', icon: DocumentTextIcon, category: 'Content', description: 'Rich text content' },
    
    // Media
    { type: 'gallery', name: 'Gallery', icon: PhotoIcon, category: 'Media', description: 'Display images beautifully' },
    { type: 'video', name: 'Video', icon: PlayCircleIcon, category: 'Media', description: 'Embed video content' },
    { type: 'map', name: 'Map', icon: MapPinIcon, category: 'Media', description: 'Show your location' },
    
    // Social Proof
    { type: 'testimonials', name: 'Testimonials', icon: ChatBubbleBottomCenterTextIcon, category: 'Social Proof', description: 'Customer reviews' },
    { type: 'logo-cloud', name: 'Logo Cloud', icon: BuildingOffice2Icon, category: 'Social Proof', description: 'Client & partner logos' },
    { type: 'blog', name: 'Blog Posts', icon: NewspaperIcon, category: 'Social Proof', description: 'Latest articles' },
    
    // Commerce
    { type: 'pricing', name: 'Pricing', icon: CurrencyDollarIcon, category: 'Commerce', description: 'Pricing plans' },
    { type: 'products', name: 'Products', icon: ShoppingBagIcon, category: 'Commerce', description: 'Product catalog' },
    
    // Forms
    { type: 'contact', name: 'Contact', icon: EnvelopeIcon, category: 'Forms', description: 'Contact form' },
    { type: 'whatsapp', name: 'WhatsApp', icon: ChatBubbleLeftRightIcon, category: 'Forms', description: 'WhatsApp click-to-chat button' },
];

/**
 * Section categories in display order
 */
export const sectionCategories: SectionCategory[] = [
    'Layout',
    'Content',
    'Media',
    'Social Proof',
    'Commerce',
    'Forms',
];

/**
 * Get section block by type
 */
export function getSectionBlock(type: string): SectionBlock | undefined {
    return sectionBlocks.find(b => b.type === type);
}

/**
 * Get sections by category
 */
export function getSectionsByCategory(category: SectionCategory): SectionBlock[] {
    return sectionBlocks.filter(b => b.category === category);
}
