/**
 * Design System
 * Modern color palettes, effects, and design tokens
 */

export interface ColorPalette {
    name: string;
    primary: string;
    secondary: string;
    accent: string;
    background: string;
    text: string;
    description: string;
    category: 'professional' | 'vibrant' | 'elegant' | 'bold' | 'minimal';
}

/**
 * Professional color palettes
 */
export const colorPalettes: ColorPalette[] = [
    // Professional
    {
        name: 'Corporate Blue',
        primary: '#2563eb',
        secondary: '#64748b',
        accent: '#059669',
        background: '#ffffff',
        text: '#1f2937',
        description: 'Trust and professionalism',
        category: 'professional',
    },
    {
        name: 'Executive Navy',
        primary: '#1e40af',
        secondary: '#475569',
        accent: '#0891b2',
        background: '#f8fafc',
        text: '#0f172a',
        description: 'Authority and confidence',
        category: 'professional',
    },
    
    // Vibrant
    {
        name: 'Energetic Orange',
        primary: '#ea580c',
        secondary: '#f97316',
        accent: '#fbbf24',
        background: '#ffffff',
        text: '#1f2937',
        description: 'Energy and enthusiasm',
        category: 'vibrant',
    },
    {
        name: 'Creative Purple',
        primary: '#7c3aed',
        secondary: '#a78bfa',
        accent: '#ec4899',
        background: '#faf5ff',
        text: '#1f2937',
        description: 'Innovation and creativity',
        category: 'vibrant',
    },
    
    // Elegant
    {
        name: 'Luxury Gold',
        primary: '#92400e',
        secondary: '#78350f',
        accent: '#d97706',
        background: '#fffbeb',
        text: '#1c1917',
        description: 'Sophistication and luxury',
        category: 'elegant',
    },
    {
        name: 'Royal Purple',
        primary: '#581c87',
        secondary: '#6b21a8',
        accent: '#c026d3',
        background: '#faf5ff',
        text: '#1f2937',
        description: 'Elegance and prestige',
        category: 'elegant',
    },
    
    // Bold
    {
        name: 'Bold Red',
        primary: '#dc2626',
        secondary: '#991b1b',
        accent: '#f59e0b',
        background: '#ffffff',
        text: '#1f2937',
        description: 'Power and passion',
        category: 'bold',
    },
    {
        name: 'Electric Cyan',
        primary: '#0891b2',
        secondary: '#06b6d4',
        accent: '#8b5cf6',
        background: '#f0fdfa',
        text: '#1f2937',
        description: 'Modern and dynamic',
        category: 'bold',
    },
    
    // Minimal
    {
        name: 'Monochrome',
        primary: '#18181b',
        secondary: '#52525b',
        accent: '#71717a',
        background: '#ffffff',
        text: '#09090b',
        description: 'Clean and minimal',
        category: 'minimal',
    },
    {
        name: 'Soft Gray',
        primary: '#475569',
        secondary: '#64748b',
        accent: '#94a3b8',
        background: '#f8fafc',
        text: '#1e293b',
        description: 'Subtle and refined',
        category: 'minimal',
    },
];

/**
 * Gradient presets for modern designs
 */
export const gradients = {
    // Blue gradients
    oceanBlue: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
    skyBlue: 'linear-gradient(135deg, #2563eb 0%, #7c3aed 100%)',
    deepBlue: 'linear-gradient(135deg, #1e3a8a 0%, #312e81 100%)',
    
    // Purple gradients
    royalPurple: 'linear-gradient(135deg, #7c3aed 0%, #c026d3 100%)',
    twilight: 'linear-gradient(135deg, #4c1d95 0%, #7c2d12 100%)',
    
    // Green gradients
    forestGreen: 'linear-gradient(135deg, #059669 0%, #047857 100%)',
    mintFresh: 'linear-gradient(135deg, #10b981 0%, #06b6d4 100%)',
    
    // Warm gradients
    sunset: 'linear-gradient(135deg, #f59e0b 0%, #dc2626 100%)',
    fire: 'linear-gradient(135deg, #dc2626 0%, #7c2d12 100%)',
    
    // Cool gradients
    arctic: 'linear-gradient(135deg, #0891b2 0%, #0e7490 100%)',
    midnight: 'linear-gradient(135deg, #1e293b 0%, #0f172a 100%)',
};

/**
 * Shadow presets for depth
 */
export const shadows = {
    sm: '0 1px 2px 0 rgb(0 0 0 / 0.05)',
    base: '0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1)',
    md: '0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)',
    lg: '0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1)',
    xl: '0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1)',
    '2xl': '0 25px 50px -12px rgb(0 0 0 / 0.25)',
    inner: 'inset 0 2px 4px 0 rgb(0 0 0 / 0.05)',
    glow: '0 0 20px rgb(59 130 246 / 0.5)',
};

/**
 * Border radius presets
 */
export const borderRadius = {
    none: '0',
    sm: '0.125rem',    // 2px
    base: '0.25rem',   // 4px
    md: '0.375rem',    // 6px
    lg: '0.5rem',      // 8px
    xl: '0.75rem',     // 12px
    '2xl': '1rem',     // 16px
    '3xl': '1.5rem',   // 24px
    full: '9999px',
};

/**
 * Spacing scale (in pixels)
 */
export const spacing = {
    0: 0,
    1: 4,
    2: 8,
    3: 12,
    4: 16,
    5: 20,
    6: 24,
    8: 32,
    10: 40,
    12: 48,
    16: 64,
    20: 80,
    24: 96,
    32: 128,
    40: 160,
    48: 192,
    56: 224,
    64: 256,
};

/**
 * Animation durations (in milliseconds)
 */
export const durations = {
    fast: 150,
    base: 300,
    slow: 500,
    slower: 700,
};

/**
 * Easing functions
 */
export const easings = {
    linear: 'linear',
    easeIn: 'cubic-bezier(0.4, 0, 1, 1)',
    easeOut: 'cubic-bezier(0, 0, 0.2, 1)',
    easeInOut: 'cubic-bezier(0.4, 0, 0.2, 1)',
    bounce: 'cubic-bezier(0.68, -0.55, 0.265, 1.55)',
};

/**
 * Hover effects configuration
 */
export const hoverEffects = {
    lift: {
        transform: 'translateY(-4px)',
        boxShadow: shadows.xl,
        transition: `all ${durations.base}ms ${easings.easeOut}`,
    },
    scale: {
        transform: 'scale(1.05)',
        transition: `transform ${durations.base}ms ${easings.easeOut}`,
    },
    glow: {
        boxShadow: shadows.glow,
        transition: `box-shadow ${durations.base}ms ${easings.easeOut}`,
    },
    brighten: {
        filter: 'brightness(1.1)',
        transition: `filter ${durations.base}ms ${easings.easeOut}`,
    },
};

/**
 * Industry-specific color palette recommendations
 */
export const industryColorPalettes: Record<string, string> = {
    consulting: 'Corporate Blue',
    tech: 'Electric Cyan',
    creative: 'Creative Purple',
    legal: 'Executive Navy',
    finance: 'Executive Navy',
    fashion: 'Luxury Gold',
    beauty: 'Royal Purple',
    restaurant: 'Energetic Orange',
    hospitality: 'Luxury Gold',
    fitness: 'Bold Red',
    sports: 'Bold Red',
    education: 'Corporate Blue',
    healthcare: 'Corporate Blue',
    realestate: 'Executive Navy',
    ecommerce: 'Creative Purple',
    portfolio: 'Monochrome',
    agency: 'Creative Purple',
    automotive: 'Bold Red',
    construction: 'Energetic Orange',
    logistics: 'Electric Cyan',
    accounting: 'Executive Navy',
    events: 'Royal Purple',
    agriculture: 'forestGreen',
    printing: 'Bold Red',
    barbershop: 'Monochrome',
};

/**
 * Get color palette by name
 */
export function getColorPalette(name: string): ColorPalette | undefined {
    return colorPalettes.find(p => p.name === name);
}

/**
 * Get recommended palette for industry
 */
export function getIndustryColorPalette(industry: string): ColorPalette {
    const paletteName = industryColorPalettes[industry] || 'Corporate Blue';
    return getColorPalette(paletteName) || colorPalettes[0];
}

/**
 * Get palettes by category
 */
export function getPalettesByCategory(category: ColorPalette['category']): ColorPalette[] {
    return colorPalettes.filter(p => p.category === category);
}
