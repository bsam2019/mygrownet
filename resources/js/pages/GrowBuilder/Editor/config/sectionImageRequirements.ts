/**
 * Section Image Requirements Configuration
 * Defines recommended image dimensions and aspect ratios for each section type
 * Mirrors backend SectionTemplateService image requirements
 */

import type { ImageRequirements } from '@/types/growbuilder';

export interface SectionImageRequirements {
    [sectionType: string]: {
        [fieldName: string]: ImageRequirements;
    };
}

export const sectionImageRequirements: SectionImageRequirements = {
    hero: {
        backgroundImage: {
            width: 1920,
            height: 1080,
            aspectRatio: 1.78, // 16:9 ratio - much better for preserving image content
            minWidth: 1280,
            maxSize: 5242880, // 5MB
            formats: ['jpg', 'jpeg', 'png', 'webp'],
            description: 'Full-width hero background image',
        },
        image: {
            width: 800,
            height: 600,
            aspectRatio: 1.33,
            minWidth: 600,
            formats: ['jpg', 'jpeg', 'png', 'webp'],
            description: 'Split layout side image',
        },
        slides: {
            width: 1920,
            height: 1080,
            aspectRatio: 1.78, // 16:9 ratio for slideshow images too
            minWidth: 1280,
            formats: ['jpg', 'jpeg', 'png', 'webp'],
            description: 'Slideshow images',
        },
    },
    
    'page-header': {
        backgroundImage: {
            width: 1920,
            height: 600,
            aspectRatio: 3.2, // 16:5 ratio - better for page headers with content
            minWidth: 1280,
            formats: ['jpg', 'jpeg', 'png', 'webp'],
            description: 'Page header background image',
        },
    },
    
    about: {
        image: {
            width: 600,
            height: 400,
            aspectRatio: 1.5,
            minWidth: 400,
            formats: ['jpg', 'jpeg', 'png', 'webp'],
            description: 'About section image',
        },
    },
    
    services: {
        'items.image': {
            width: 500,
            height: 400,
            aspectRatio: 1.25, // 5:4 ratio - better for service cards
            minWidth: 400,
            formats: ['jpg', 'jpeg', 'png', 'webp'],
            description: 'Service card image',
        },
    },
    
    team: {
        'items.image': {
            width: 400,
            height: 400,
            aspectRatio: 1.0,
            minWidth: 200,
            formats: ['jpg', 'jpeg', 'png', 'webp'],
            description: 'Team member photo (square)',
        },
    },
    
    testimonials: {
        'items.image': {
            width: 200,
            height: 200,
            aspectRatio: 1.0,
            minWidth: 100,
            formats: ['jpg', 'jpeg', 'png', 'webp'],
            description: 'Customer photo (square)',
        },
    },
    
    cta: {
        image: {
            width: 800,
            height: 600,
            aspectRatio: 1.33,
            minWidth: 600,
            formats: ['jpg', 'jpeg', 'png', 'webp'],
            description: 'CTA split layout image',
        },
    },
    
    gallery: {
        images: {
            width: 1000,
            height: 800,
            aspectRatio: 1.25,
            minWidth: 600,
            formats: ['jpg', 'jpeg', 'png', 'webp'],
            description: 'Gallery image',
        },
    },
};

/**
 * Get image requirements for a specific section type and field
 */
export function getImageRequirements(sectionType: string, field: string): ImageRequirements | null {
    const sectionReqs = sectionImageRequirements[sectionType];
    if (!sectionReqs) return null;
    
    // Direct match
    if (sectionReqs[field]) {
        return sectionReqs[field];
    }
    
    // Pattern match for array fields (e.g., 'items.0.image' matches 'items.image')
    for (const [pattern, requirements] of Object.entries(sectionReqs)) {
        // Simple pattern matching: if field starts with pattern prefix
        if (pattern.includes('.') && field.startsWith(pattern.split('.')[0] + '.')) {
            // Check if the last part matches (e.g., 'image' in 'items.0.image')
            const patternEnd = pattern.split('.').pop();
            const fieldEnd = field.split('.').pop();
            if (patternEnd === fieldEnd) {
                return requirements;
            }
        }
    }
    
    return null;
}

/**
 * Calculate compatibility score between an image and requirements
 * Returns 0-100 score
 */
export function calculateCompatibilityScore(
    imageWidth: number,
    imageHeight: number,
    requirements: ImageRequirements
): number {
    let score = 100;
    
    const imageRatio = imageWidth / imageHeight;
    const requiredRatio = requirements.aspectRatio;
    
    // Aspect ratio match (most important - 50 points)
    const ratioDiff = Math.abs(imageRatio - requiredRatio);
    if (ratioDiff < 0.05) {
        // Perfect match
        score += 0;
    } else if (ratioDiff < 0.1) {
        // Close match
        score -= 10;
    } else if (ratioDiff < 0.2) {
        // Acceptable
        score -= 25;
    } else {
        // Poor match
        score -= 50;
    }
    
    // Dimension match (30 points)
    if (imageWidth === requirements.width && imageHeight === requirements.height) {
        // Perfect dimensions
        score += 0;
    } else if (imageWidth >= requirements.width && imageHeight >= requirements.height) {
        // Larger (can be cropped)
        score -= 5;
    } else if (imageWidth >= (requirements.minWidth || requirements.width * 0.8)) {
        // Acceptable size
        score -= 15;
    } else {
        // Too small
        score -= 30;
    }
    
    // File size check (20 points)
    if (requirements.maxSize) {
        // Would need actual file size here
        // For now, assume OK
    }
    
    return Math.max(0, Math.min(100, score));
}

/**
 * Get compatibility level based on score
 */
export function getCompatibilityLevel(score: number): 'perfect' | 'good' | 'acceptable' | 'poor' {
    if (score >= 95) return 'perfect';
    if (score >= 80) return 'good';
    if (score >= 60) return 'acceptable';
    return 'poor';
}

/**
 * Get compatibility badge color
 */
export function getCompatibilityBadge(level: string): { bg: string; text: string; icon: string } {
    switch (level) {
        case 'perfect':
            return { bg: 'bg-green-100', text: 'text-green-700', icon: '✓' };
        case 'good':
            return { bg: 'bg-blue-100', text: 'text-blue-700', icon: '✓' };
        case 'acceptable':
            return { bg: 'bg-yellow-100', text: 'text-yellow-700', icon: '⚠' };
        case 'poor':
            return { bg: 'bg-red-100', text: 'text-red-700', icon: '✗' };
        default:
            return { bg: 'bg-gray-100', text: 'text-gray-700', icon: '?' };
    }
}
