/**
 * GrowBuilder Editor Type Definitions
 * Central type definitions for the page builder
 */

export type { SectionContentMap } from './content';
export type {
    HeroContent,
    AboutContent,
    ServicesContent,
    FeaturesContent,
    GalleryContent,
    TestimonialsContent,
    PricingContent,
    ProductsContent,
    MarketplaceProductsContent,
    ContactContent,
    CtaContent,
    CtaBannerContent,
    MemberCtaContent,
    TextContent,
    FaqContent,
    TeamContent,
    BlogContent,
    StatsContent,
    MapContent,
    VideoContent,
    DividerContent,
    TimelineContent,
    LogoCloudContent,
    VideoHeroContent,
    WhatsAppContent,
    PageHeaderContent,
} from './content';

// ============================================
// Core Types
// ============================================

export interface NavItem {
    id: string;
    label: string;
    url: string;
    pageId?: number;
    isExternal: boolean;
    children: NavItem[];
}

export interface SectionStyle {
    backgroundColor?: string;
    textColor?: string;
    backgroundType?: 'solid' | 'gradient' | 'image';
    gradientFrom?: string;
    gradientTo?: string;
    gradientDirection?: string;
    backgroundImage?: string;
    backgroundSize?: 'cover' | 'contain' | 'repeat';
    backgroundPosition?: string;
    overlayColor?: string;
    overlayOpacity?: number;
    minHeight?: number;
    paddingTop?: number;
    paddingBottom?: number;
    borderRadius?: string;
    shadow?: string;
    textAlign?: 'left' | 'center' | 'right';
    maxWidth?: number;
    marginTop?: number;
    marginBottom?: number;
    borderWidth?: number;
    borderColor?: string;
    borderStyle?: 'solid' | 'dashed' | 'dotted' | 'none';
}

export interface Section {
    id: string;
    type: SectionType;
    content: Record<string, any>;
    style: SectionStyle;
}

export interface TypedSection<S extends SectionType> {
    id: string;
    type: S;
    content: SectionContentMap[S];
    style: SectionStyle;
}

export interface Page {
    id: number;
    title: string;
    slug: string;
    content: { sections: Section[] };
    isHomepage: boolean;
    isPublished: boolean;
    showInNav: boolean;
    navOrder: number;
    metaTitle?: string;
    metaDescription?: string;
    ogImage?: string;
}

export interface Site {
    id: number;
    name: string;
    subdomain: string;
    theme: Record<string, unknown> | null;
    settings: SiteSettings | null;
    status: string;
    url: string;
    logo?: string;
    favicon?: string;
}

export interface SiteSettings {
    navigation?: NavigationSettings;
    footer?: FooterSettings;
}

// ============================================
// Section Types
// ============================================

export type SectionType =
    | 'hero'
    | 'page-header'
    | 'about'
    | 'services'
    | 'features'
    | 'gallery'
    | 'testimonials'
    | 'pricing'
    | 'products'
    | 'contact'
    | 'cta'
    | 'member-cta'
    | 'text'
    | 'faq'
    | 'team'
    | 'blog'
    | 'stats'
    | 'map'
    | 'video'
    | 'divider'
    | 'timeline'
    | 'cta-banner'
    | 'logo-cloud'
    | 'video-hero'
    | 'whatsapp'
    | 'marketplaceProducts';

export interface SectionBlock {
    type: SectionType;
    name: string;
    icon: unknown;
    category: SectionCategory;
    description: string;
}

export type SectionCategory =
    | 'Layout'
    | 'Content'
    | 'Media'
    | 'Social Proof'
    | 'Commerce'
    | 'Forms';

// ============================================
// Navigation Types
// ============================================

export type NavStyle =
    | 'default'
    | 'centered'
    | 'split'
    | 'floating'
    | 'transparent'
    | 'dark'
    | 'sidebar'
    | 'mega';

export interface NavigationSettings {
    logoText: string;
    logo: string;
    navItems: NavItem[];
    showCta: boolean;
    ctaText: string;
    ctaLink: string;
    sticky: boolean;
    style: NavStyle;
    showAuthButtons?: boolean;
    showLoginButton?: boolean;
    showRegisterButton?: boolean;
    loginText?: string;
    registerText?: string;
    loginStyle?: 'link' | 'outline' | 'solid';
    registerStyle?: 'link' | 'outline' | 'solid';
}

export interface NavStyleOption {
    id: NavStyle;
    name: string;
    description: string;
}

// ============================================
// Footer Types
// ============================================

export type FooterLayout =
    | 'columns'
    | 'centered'
    | 'split'
    | 'minimal'
    | 'stacked'
    | 'newsletter'
    | 'social'
    | 'contact';

export interface FooterLink {
    id: string;
    label: string;
    url: string;
}

export interface FooterColumn {
    id: string;
    title: string;
    links: FooterLink[];
}

export type SocialPlatform =
    | 'facebook'
    | 'twitter'
    | 'instagram'
    | 'linkedin'
    | 'youtube'
    | 'tiktok';

export interface SocialLink {
    id: string;
    platform: SocialPlatform;
    url: string;
}

export interface FooterSettings {
    logo: string;
    copyrightText: string;
    showSocialLinks: boolean;
    socialLinks: SocialLink[];
    columns: FooterColumn[];
    showNewsletter: boolean;
    newsletterTitle: string;
    backgroundColor: string;
    textColor: string;
    layout: FooterLayout;
}

// ============================================
// Page Template Types
// ============================================

export interface PageTemplate {
    id: string;
    name: string;
    description: string;
    iconType: string;
    isHomepage?: boolean;
    sections: Array<{
        type: SectionType;
        content: Record<string, unknown>;
        style: Record<string, unknown>;
    }>;
}

// ============================================
// Editor State Types
// ============================================

export type PreviewMode = 'desktop' | 'tablet' | 'mobile';
export type InspectorTab = 'content' | 'style' | 'advanced';
export type LeftSidebarTab = 'pages' | 'widgets';

export interface EditingField {
    sectionId: string;
    field: string;
    itemIndex?: number;
}

export interface DraggingElement {
    sectionId: string;
    elementKey: string;
}

export interface MediaLibraryTarget {
    sectionId?: string;
    field: string;
    itemIndex?: number;
    target?: 'navigation' | 'footer' | 'section';
}

export interface NewPageForm {
    title: string;
    slug: string;
    showInNav: boolean;
    templateId: string;
}

// ============================================
// Event Types
// ============================================

export interface SectionDropEvent {
    added?: { element: Section; newIndex: number };
    removed?: { element: Section; oldIndex: number };
    moved?: { element: Section; oldIndex: number; newIndex: number };
}

// ============================================
// Utility Types
// ============================================

export interface ThemeColor {
    name: string;
    value: string;
}

export interface SocialPlatformOption {
    value: SocialPlatform;
    label: string;
}
