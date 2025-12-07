<script setup lang="ts">
/**
 * BusinessPage - Public-facing mini-website page
 * Uses the same MiniWebsitePreview component as the builder for consistency
 * Includes SEO meta tags for better search engine visibility
 */
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted } from 'vue';
import MiniWebsitePreview from '@/Components/BizBoost/MiniWebsitePreview.vue';

interface TeamMember {
    name: string;
    role: string;
    image?: string;
}

interface Achievement {
    title: string;
    year?: number;
}

interface Service {
    name: string;
    description: string;
    price?: string;
    icon?: string;
}

interface Testimonial {
    name: string;
    content: string;
    rating: number;
}

interface BusinessHours {
    [key: string]: { open: string; close: string; closed?: boolean };
}

interface ThemeSettings {
    primary_color: string;
    secondary_color: string;
    accent_color: string;
    nav_color?: string;
    template: string;
    font_style: 'default' | 'elegant' | 'modern' | 'playful';
    button_style: 'rounded' | 'pill' | 'square';
    hero_style: 'gradient' | 'image' | 'split' | 'wave' | 'diagonal';
    about_layout?: 'story' | 'stats' | 'team' | 'minimal';
}

interface Profile {
    tagline: string | null;
    about: string | null;
    business_story: string | null;
    mission: string | null;
    vision: string | null;
    founding_year: number | null;
    business_hours: BusinessHours | null;
    team_members: TeamMember[] | null;
    achievements: Achievement[] | null;
    services: Service[] | null;
    testimonials: Testimonial[] | null;
    contact_email: string | null;
    hero_image_path: string | null;
    about_image_path: string | null;
    show_products: boolean;
    show_services: boolean;
    show_gallery: boolean;
    show_testimonials: boolean;
    show_business_hours: boolean;
    show_contact_form: boolean;
    show_whatsapp_button: boolean;
    show_social_links: boolean;
    theme_settings: ThemeSettings | null;
}

interface Product {
    id: number;
    name: string;
    description: string | null;
    price: number;
    sale_price: number | null;
    is_featured: boolean;
    primary_image: { path: string } | null;
}

interface Business {
    id: number;
    name: string;
    slug: string;
    industry: string;
    description: string | null;
    logo_path: string | null;
    phone: string | null;
    whatsapp: string | null;
    email: string | null;
    website: string | null;
    address: string | null;
    city: string | null;
    province: string | null;
    social_links: Record<string, string> | null;
    profile: Profile | null;
    products: Product[];
}

interface Props {
    business: Business;
}

const props = defineProps<Props>();

// Default theme settings
const defaultTheme: ThemeSettings = {
    primary_color: '#7c3aed',
    secondary_color: '#4f46e5',
    accent_color: '#10b981',
    nav_color: '#111827',
    template: 'professional',
    font_style: 'default',
    button_style: 'rounded',
    hero_style: 'gradient',
    about_layout: 'story',
};

// Merge with business theme settings
const themeSettings = {
    ...defaultTheme,
    ...(props.business.profile?.theme_settings || {}),
};

// SEO Meta Tags
const seoTitle = computed(() => {
    const tagline = props.business.profile?.tagline;
    return tagline 
        ? `${props.business.name} - ${tagline}`
        : props.business.name;
});

const seoDescription = computed(() => {
    // Use about text, business story, or generate from business info
    const about = props.business.profile?.about;
    const story = props.business.profile?.business_story;
    const description = props.business.description;
    
    const text = about || story || description || 
        `${props.business.name} - ${props.business.industry} business in ${props.business.city || 'Zambia'}`;
    
    // Truncate to ~160 characters for SEO
    return text.length > 160 ? text.substring(0, 157) + '...' : text;
});

const seoImage = computed(() => {
    // Use hero image, logo, or null
    if (props.business.profile?.hero_image_path) {
        return `/storage/${props.business.profile.hero_image_path}`;
    }
    if (props.business.logo_path) {
        return `/storage/${props.business.logo_path}`;
    }
    return null;
});

const canonicalUrl = computed(() => {
    return `${window.location.origin}/biz/${props.business.slug}`;
});

const structuredData = computed(() => {
    const data: Record<string, any> = {
        '@context': 'https://schema.org',
        '@type': 'LocalBusiness',
        name: props.business.name,
        description: seoDescription.value,
        url: canonicalUrl.value,
    };
    
    if (props.business.industry) {
        data.industry = props.business.industry;
    }
    
    if (props.business.phone) {
        data.telephone = props.business.phone;
    }
    
    if (props.business.email) {
        data.email = props.business.email;
    }
    
    if (props.business.address || props.business.city) {
        data.address = {
            '@type': 'PostalAddress',
            streetAddress: props.business.address || '',
            addressLocality: props.business.city || '',
            addressRegion: props.business.province || '',
            addressCountry: 'ZM',
        };
    }
    
    if (seoImage.value) {
        data.image = seoImage.value;
    }
    
    // Add business hours if available
    if (props.business.profile?.business_hours) {
        const hours = props.business.profile.business_hours;
        const openingHours: string[] = [];
        const dayMap: Record<string, string> = {
            monday: 'Mo',
            tuesday: 'Tu',
            wednesday: 'We',
            thursday: 'Th',
            friday: 'Fr',
            saturday: 'Sa',
            sunday: 'Su',
        };
        
        Object.entries(hours).forEach(([day, schedule]) => {
            if (schedule && !schedule.closed && schedule.open && schedule.close) {
                openingHours.push(`${dayMap[day]} ${schedule.open}-${schedule.close}`);
            }
        });
        
        if (openingHours.length > 0) {
            data.openingHours = openingHours;
        }
    }
    
    return JSON.stringify(data);
});

// Inject structured data script into document head
let structuredDataScript: HTMLScriptElement | null = null;

onMounted(() => {
    structuredDataScript = document.createElement('script');
    structuredDataScript.type = 'application/ld+json';
    structuredDataScript.textContent = structuredData.value;
    structuredDataScript.id = 'structured-data-jsonld';
    document.head.appendChild(structuredDataScript);
});

onUnmounted(() => {
    if (structuredDataScript && structuredDataScript.parentNode) {
        structuredDataScript.parentNode.removeChild(structuredDataScript);
    }
});
</script>

<template>
    <Head :title="seoTitle">
        <!-- Primary Meta Tags -->
        <meta name="description" :content="seoDescription" />
        <meta name="keywords" :content="`${business.name}, ${business.industry}, ${business.city || 'Zambia'}, business`" />
        
        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="website" />
        <meta property="og:url" :content="canonicalUrl" />
        <meta property="og:title" :content="seoTitle" />
        <meta property="og:description" :content="seoDescription" />
        <meta v-if="seoImage" property="og:image" :content="seoImage" />
        <meta property="og:site_name" content="MyGrowNet BizBoost" />
        
        <!-- Twitter -->
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:url" :content="canonicalUrl" />
        <meta name="twitter:title" :content="seoTitle" />
        <meta name="twitter:description" :content="seoDescription" />
        <meta v-if="seoImage" name="twitter:image" :content="seoImage" />
        
        <!-- Canonical URL -->
        <link rel="canonical" :href="canonicalUrl" />
        
        <!-- Structured Data (JSON-LD) is injected via onMounted -->
    </Head>
    
    <!-- 
        Public mini-website page
        Uses the same MiniWebsitePreview component as the builder
        This ensures WYSIWYG - what users see in the builder is exactly what visitors see
    -->
    <MiniWebsitePreview
        :business-name="business.name"
        :business-slug="business.slug"
        :industry="business.industry"
        :logo-path="business.logo_path"
        :phone="business.phone"
        :whatsapp="business.whatsapp"
        :email="business.email"
        :address="business.address"
        :city="business.city"
        :tagline="business.profile?.tagline || ''"
        :about="business.profile?.about || ''"
        :business-story="business.profile?.business_story || ''"
        :mission="business.profile?.mission || ''"
        :vision="business.profile?.vision || ''"
        :founding-year="business.profile?.founding_year || null"
        :business-hours="business.profile?.business_hours || null"
        :team-members="business.profile?.team_members || []"
        :achievements="business.profile?.achievements || []"
        :services="business.profile?.services || []"
        :testimonials="business.profile?.testimonials || []"
        :contact-email="business.profile?.contact_email || business.email || ''"
        :hero-image-path="business.profile?.hero_image_path || null"
        :about-image-path="business.profile?.about_image_path || null"
        :show-products="business.profile?.show_products ?? true"
        :show-services="business.profile?.show_services ?? true"
        :show-business-hours="business.profile?.show_business_hours ?? true"
        :show-testimonials="business.profile?.show_testimonials ?? false"
        :show-contact-form="business.profile?.show_contact_form ?? true"
        :show-whatsapp-button="business.profile?.show_whatsapp_button ?? true"
        :show-social-links="business.profile?.show_social_links ?? true"
        :theme-settings="themeSettings"
        :products="business.products || []"
        :is-preview="false"
        :enable-links="true"
    />
</template>
