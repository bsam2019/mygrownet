/**
 * GrowBuilder Section Content Type Definitions
 * Properly typed content interfaces for all section types
 */

// ============================================
// Shared / Base Content Types
// ============================================

export interface CtaButton {
    text?: string;
    link?: string;
}

export interface LinkItem {
    label: string;
    url: string;
}

export interface StatItem {
    number: string;
    suffix?: string;
    label: string;
    icon?: string;
}

export interface FaqItem {
    question: string;
    answer: string;
}

export interface TeamMember {
    name: string;
    role: string;
    image?: string;
    bio?: string;
}

export interface TimelineItem {
    year: string;
    title: string;
    description: string;
    icon?: string;
}

export interface LogoItem {
    image?: string;
    name: string;
    link?: string;
}

export interface BlogPostItem {
    title: string;
    excerpt?: string;
    date?: string;
    image?: string;
}

export interface ServiceItem {
    title: string;
    description: string;
    icon?: string;
}

export interface FeatureItem {
    title: string;
    description: string;
    icon?: string;
}

export interface TestimonialItem {
    name: string;
    text: string;
    role?: string;
    rating?: number;
    image?: string;
}

export interface PricingPlan {
    name: string;
    price: string;
    currency?: string;
    period?: string;
    description?: string;
    features: string[];
    highlighted?: boolean;
    cta?: CtaButton;
}

export interface GalleryImage {
    src: string;
    alt?: string;
    caption?: string;
}

// ============================================
// Section-specific Content Interfaces
// ============================================

export interface HeroContent {
    title: string;
    subtitle: string;
    buttonText: string;
    buttonLink: string;
    secondaryButtonText?: string;
    secondaryButtonLink?: string;
    textPosition: 'left' | 'center' | 'right';
    backgroundImage?: string;
    layout: 'centered' | 'split-left' | 'split-right' | 'slideshow' | 'video';
    slides?: Array<{ title: string; subtitle: string; image: string }>;
    videoUrl?: string;
    overlay?: string;
}

export interface PageHeaderContent {
    title: string;
    subtitle: string;
    backgroundImage?: string;
    backgroundColor: string;
    textColor: string;
    textPosition: 'left' | 'center' | 'right';
}

export interface AboutContent {
    title: string;
    description: string;
    image?: string;
    imagePosition: 'left' | 'right';
    layout: 'image-right' | 'image-left' | 'centered';
    features?: string[];
    buttonText?: string;
    buttonLink?: string;
}

export interface ServicesContent {
    title: string;
    subtitle?: string;
    layout: 'grid' | 'list' | 'cards';
    columns: number;
    items: ServiceItem[];
}

export interface FeaturesContent {
    title: string;
    subtitle?: string;
    columns?: number;
    items: FeatureItem[];
}

export interface GalleryContent {
    title: string;
    subtitle?: string;
    layout?: 'grid' | 'masonry' | 'slider';
    images: GalleryImage[];
}

export interface TestimonialsContent {
    title: string;
    subtitle?: string;
    layout: 'grid' | 'carousel' | 'list';
    items: TestimonialItem[];
}

export interface PricingContent {
    title: string;
    subtitle?: string;
    plans: PricingPlan[];
}

export interface ProductsContent {
    title: string;
    subtitle?: string;
    showAll: boolean;
    limit: number;
}

export interface MarketplaceProductsContent {
    title: string;
    subtitle: string;
    displayMode: 'all' | 'featured' | 'category';
    limit: number;
    columns: number;
    showPrices: boolean;
    showAddToCart: boolean;
}

export interface ContactContent {
    title: string;
    description: string;
    showForm: boolean;
    email?: string;
    phone?: string;
    address?: string;
    formFields?: Array<{ label: string; type: string; required: boolean }>;
}

export interface CtaContent {
    title: string;
    description: string;
    buttonText: string;
    buttonLink: string;
    secondaryButtonText?: string;
    secondaryButtonLink?: string;
    textAlign?: 'left' | 'center' | 'right';
    backgroundImage?: string;
}

export interface CtaBannerContent {
    title: string;
    subtitle: string;
    layout: 'centered' | 'split' | 'overlay';
    buttonText: string;
    buttonLink: string;
    secondaryButtonText?: string;
    secondaryButtonLink?: string;
    image?: string;
}

export interface MemberCtaContent {
    title: string;
    subtitle: string;
    description: string;
    benefits: string[];
    loginText?: string;
    registerText: string;
    registerButtonStyle: 'solid' | 'outline';
    showLoginLink: boolean;
    backgroundColor: string;
    textColor: string;
}

export interface TextContent {
    content: string;
}

export interface FaqContent {
    title: string;
    subtitle?: string;
    layout?: 'accordion' | 'grid' | 'list';
    items: FaqItem[];
}

export interface TeamContent {
    title: string;
    subtitle?: string;
    items: TeamMember[];
}

export interface BlogContent {
    title: string;
    subtitle?: string;
    showLatest: boolean;
    limit: number;
    category?: string;
    posts: BlogPostItem[];
}

export interface StatsContent {
    title: string;
    subtitle?: string;
    layout: 'horizontal' | 'grid';
    animated: boolean;
    items: StatItem[];
}

export interface MapContent {
    title: string;
    address: string;
    embedUrl?: string;
    showAddress: boolean;
    latitude?: number;
    longitude?: number;
    zoom?: number;
}

export interface VideoContent {
    title: string;
    videoUrl: string;
    videoType: 'youtube' | 'vimeo' | 'custom';
    autoplay: boolean;
    description?: string;
    posterImage?: string;
}

export interface DividerContent {
    style: 'line' | 'dots' | 'icon';
    height: number;
    color: string;
    icon?: string;
}

export interface TimelineContent {
    title: string;
    subtitle?: string;
    textAlign: 'left' | 'center' | 'right';
    layout: 'vertical' | 'horizontal';
    items: TimelineItem[];
}

export interface LogoCloudContent {
    title: string;
    subtitle?: string;
    textAlign: 'left' | 'center' | 'right';
    layout: 'grid' | 'carousel' | 'list';
    grayscale: boolean;
    items: LogoItem[];
}

export interface VideoHeroContent {
    title: string;
    subtitle: string;
    layout: 'fullscreen' | 'banner';
    videoUrl: string;
    posterImage?: string;
    autoPlay: boolean;
    muted: boolean;
    loop: boolean;
    buttonText: string;
    buttonLink: string;
    overlay?: string;
}

export interface WhatsAppContent {
    phoneNumber: string;
    message: string;
    buttonText: string;
    buttonStyle: 'solid' | 'outline';
    buttonSize: 'sm' | 'md' | 'lg';
    alignment: 'left' | 'center' | 'right';
    showIcon: boolean;
    backgroundColor: string;
    textColor: string;
}

// ============================================
// Section Type → Content Mapper
// ============================================

export interface SectionContentMap {
    'hero': HeroContent;
    'page-header': PageHeaderContent;
    'about': AboutContent;
    'services': ServicesContent;
    'features': FeaturesContent;
    'gallery': GalleryContent;
    'testimonials': TestimonialsContent;
    'pricing': PricingContent;
    'products': ProductsContent;
    'marketplaceProducts': MarketplaceProductsContent;
    'contact': ContactContent;
    'cta': CtaContent;
    'cta-banner': CtaBannerContent;
    'member-cta': MemberCtaContent;
    'text': TextContent;
    'faq': FaqContent;
    'team': TeamContent;
    'blog': BlogContent;
    'stats': StatsContent;
    'map': MapContent;
    'video': VideoContent;
    'divider': DividerContent;
    'timeline': TimelineContent;
    'logo-cloud': LogoCloudContent;
    'video-hero': VideoHeroContent;
    'whatsapp': WhatsAppContent;
}
