/**
 * Section Components Registry
 * 
 * This registry pattern makes it easy to add new section types:
 * 1. Create a new component in this folder (e.g., NewSection.vue)
 * 2. Import and add it to the sectionComponents map
 * 3. Add the section type to config/sectionBlocks.ts
 * 4. Add default content to config/sectionDefaults.ts
 */

import { defineAsyncComponent, type Component } from 'vue';
import type { SectionType } from '../../types';

// Eagerly loaded core sections (most commonly used)
import HeroSection from './HeroSection.vue';
import AboutSection from './AboutSection.vue';
import ServicesSection from './ServicesSection.vue';
import CtaSection from './CtaSection.vue';

// Lazy-loaded sections (less common, loaded on demand)
const FeaturesSection = defineAsyncComponent(() => import('./FeaturesSection.vue'));
const TestimonialsSection = defineAsyncComponent(() => import('./TestimonialsSection.vue'));
const PricingSection = defineAsyncComponent(() => import('./PricingSection.vue'));
const ContactSection = defineAsyncComponent(() => import('./ContactSection.vue'));
const GallerySection = defineAsyncComponent(() => import('./GallerySection.vue'));
const FaqSection = defineAsyncComponent(() => import('./FaqSection.vue'));
const TeamSection = defineAsyncComponent(() => import('./TeamSection.vue'));
const BlogSection = defineAsyncComponent(() => import('./BlogSection.vue'));
const StatsSection = defineAsyncComponent(() => import('./StatsSection.vue'));
const MapSection = defineAsyncComponent(() => import('./MapSection.vue'));
const VideoSection = defineAsyncComponent(() => import('./VideoSection.vue'));
const TextSection = defineAsyncComponent(() => import('./TextSection.vue'));
const DividerSection = defineAsyncComponent(() => import('./DividerSection.vue'));
const PageHeaderSection = defineAsyncComponent(() => import('./PageHeaderSection.vue'));
const ProductsSection = defineAsyncComponent(() => import('./ProductsSection.vue'));
const MemberCtaSection = defineAsyncComponent(() => import('./MemberCtaSection.vue'));
const WhatsAppSection = defineAsyncComponent(() => import('./WhatsAppSection.vue'));

// Phase 1 New Sections
const TimelineSection = defineAsyncComponent(() => import('./TimelineSection.vue'));
const CtaBannerSection = defineAsyncComponent(() => import('./CtaBannerSection.vue'));
const LogoCloudSection = defineAsyncComponent(() => import('./LogoCloudSection.vue'));
const VideoHeroSection = defineAsyncComponent(() => import('./VideoHeroSection.vue'));

/**
 * Map of section types to their components
 */
export const sectionComponents: Record<SectionType, Component> = {
    'hero': HeroSection,
    'about': AboutSection,
    'services': ServicesSection,
    'cta': CtaSection,
    'member-cta': MemberCtaSection,
    'features': FeaturesSection,
    'testimonials': TestimonialsSection,
    'pricing': PricingSection,
    'contact': ContactSection,
    'gallery': GallerySection,
    'faq': FaqSection,
    'team': TeamSection,
    'blog': BlogSection,
    'stats': StatsSection,
    'map': MapSection,
    'video': VideoSection,
    'text': TextSection,
    'divider': DividerSection,
    'page-header': PageHeaderSection,
    'products': ProductsSection,
    'timeline': TimelineSection,
    'cta-banner': CtaBannerSection,
    'logo-cloud': LogoCloudSection,
    'video-hero': VideoHeroSection,
    'whatsapp': WhatsAppSection,
};

/**
 * Get component for a section type
 */
export function getSectionComponent(type: SectionType): Component | null {
    return sectionComponents[type] || null;
}

// Re-export wrapper
export { default as SectionWrapper } from './SectionWrapper.vue';
