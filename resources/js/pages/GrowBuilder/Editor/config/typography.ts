/**
 * Typography System
 * Professional font pairings and type scale for world-class templates
 */

export interface FontPairing {
    name: string;
    heading: string;
    body: string;
    description: string;
    category: 'modern' | 'classic' | 'elegant' | 'bold' | 'minimal';
    googleFonts: string[]; // For loading from Google Fonts
}

/**
 * Professional font pairings by industry/style
 */
export const fontPairings: FontPairing[] = [
    // Modern & Tech
    {
        name: 'Modern Tech',
        heading: 'Space Grotesk',
        body: 'Inter',
        description: 'Clean, modern, perfect for tech and startups',
        category: 'modern',
        googleFonts: ['Space+Grotesk:wght@400;500;600;700', 'Inter:wght@300;400;500;600'],
    },
    {
        name: 'Contemporary',
        heading: 'DM Sans',
        body: 'Inter',
        description: 'Geometric and friendly, great for SaaS',
        category: 'modern',
        googleFonts: ['DM+Sans:wght@400;500;700', 'Inter:wght@300;400;500;600'],
    },
    
    // Classic & Professional
    {
        name: 'Professional',
        heading: 'Playfair Display',
        body: 'Inter',
        description: 'Elegant serif with modern sans, perfect for consulting',
        category: 'classic',
        googleFonts: ['Playfair+Display:wght@400;600;700;900', 'Inter:wght@300;400;500;600'],
    },
    {
        name: 'Editorial',
        heading: 'Crimson Text',
        body: 'Source Sans Pro',
        description: 'Traditional and trustworthy, ideal for legal/finance',
        category: 'classic',
        googleFonts: ['Crimson+Text:wght@400;600;700', 'Source+Sans+Pro:wght@300;400;600'],
    },
    
    // Elegant & Luxury
    {
        name: 'Luxury',
        heading: 'Bodoni Moda',
        body: 'Montserrat',
        description: 'High-end and sophisticated, perfect for fashion/beauty',
        category: 'elegant',
        googleFonts: ['Bodoni+Moda:wght@400;600;700;900', 'Montserrat:wght@300;400;500;600'],
    },
    {
        name: 'Refined',
        heading: 'Cormorant Garamond',
        body: 'Lato',
        description: 'Graceful and refined, great for restaurants/hospitality',
        category: 'elegant',
        googleFonts: ['Cormorant+Garamond:wght@400;600;700', 'Lato:wght@300;400;700'],
    },
    
    // Bold & Impactful
    {
        name: 'Bold Impact',
        heading: 'Archivo Black',
        body: 'Work Sans',
        description: 'Strong and attention-grabbing, perfect for creative agencies',
        category: 'bold',
        googleFonts: ['Archivo+Black', 'Work+Sans:wght@300;400;500;600'],
    },
    {
        name: 'Athletic',
        heading: 'Oswald',
        body: 'Roboto',
        description: 'Energetic and powerful, ideal for fitness/sports',
        category: 'bold',
        googleFonts: ['Oswald:wght@400;500;600;700', 'Roboto:wght@300;400;500;700'],
    },
    
    // Minimal & Clean
    {
        name: 'Swiss Minimal',
        heading: 'Inter',
        body: 'Inter',
        description: 'Ultra-clean monochrome, perfect for portfolios',
        category: 'minimal',
        googleFonts: ['Inter:wght@300;400;500;600;700;800;900'],
    },
    {
        name: 'Geometric',
        heading: 'Poppins',
        body: 'Poppins',
        description: 'Friendly geometric, great for education/community',
        category: 'minimal',
        googleFonts: ['Poppins:wght@300;400;500;600;700;800'],
    },
];

/**
 * Type scale (font sizes in pixels)
 * Following a modular scale for visual harmony
 */
export const typeScale = {
    xs: 12,      // Small labels, captions
    sm: 14,      // Body small, secondary text
    base: 16,    // Body text (default)
    lg: 18,      // Large body, lead paragraphs
    xl: 20,      // Small headings
    '2xl': 24,   // H4
    '3xl': 30,   // H3
    '4xl': 36,   // H2
    '5xl': 48,   // H1
    '6xl': 60,   // Display large
    '7xl': 72,   // Display extra large
    '8xl': 96,   // Hero titles
};

/**
 * Line heights for optimal readability
 */
export const lineHeights = {
    tight: 1.2,    // Headings
    snug: 1.375,   // Subheadings
    normal: 1.5,   // Body text
    relaxed: 1.625, // Large body text
    loose: 2,      // Spacious text
};

/**
 * Font weights
 */
export const fontWeights = {
    light: 300,
    normal: 400,
    medium: 500,
    semibold: 600,
    bold: 700,
    extrabold: 800,
    black: 900,
};

/**
 * Letter spacing (tracking)
 */
export const letterSpacing = {
    tighter: '-0.05em',
    tight: '-0.025em',
    normal: '0',
    wide: '0.025em',
    wider: '0.05em',
    widest: '0.1em',
};

/**
 * Get font pairing by category
 */
export function getFontPairingsByCategory(category: FontPairing['category']): FontPairing[] {
    return fontPairings.filter(p => p.category === category);
}

/**
 * Get font pairing by name
 */
export function getFontPairing(name: string): FontPairing | undefined {
    return fontPairings.find(p => p.name === name);
}

/**
 * Generate Google Fonts URL for a pairing
 */
export function getGoogleFontsUrl(pairing: FontPairing): string {
    const fonts = pairing.googleFonts.join('&family=');
    return `https://fonts.googleapis.com/css2?family=${fonts}&display=swap`;
}

/**
 * Industry-specific font pairing recommendations
 */
export const industryFontPairings: Record<string, string> = {
    consulting: 'Professional',
    tech: 'Modern Tech',
    creative: 'Bold Impact',
    legal: 'Editorial',
    finance: 'Editorial',
    fashion: 'Luxury',
    beauty: 'Luxury',
    restaurant: 'Refined',
    hospitality: 'Refined',
    fitness: 'Athletic',
    sports: 'Athletic',
    education: 'Geometric',
    healthcare: 'Professional',
    realestate: 'Professional',
    ecommerce: 'Contemporary',
    portfolio: 'Swiss Minimal',
    agency: 'Bold Impact',
    automotive: 'Athletic',
    construction: 'Bold Impact',
    logistics: 'Modern Tech',
    accounting: 'Editorial',
    events: 'Refined',
    agriculture: 'Contemporary',
    printing: 'Bold Impact',
    barbershop: 'Bold Impact',
};

/**
 * Get recommended font pairing for an industry
 */
export function getIndustryFontPairing(industry: string): FontPairing {
    const pairingName = industryFontPairings[industry] || 'Modern Tech';
    return getFontPairing(pairingName) || fontPairings[0];
}
