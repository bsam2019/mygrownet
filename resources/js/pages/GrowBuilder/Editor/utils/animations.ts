/**
 * Animation Utilities
 * Reusable animation classes and utilities for components
 */

/**
 * Hover effect classes for buttons
 */
export const buttonHoverClasses = {
    lift: 'transition-all duration-300 hover:-translate-y-1 hover:shadow-xl',
    scale: 'transition-transform duration-300 hover:scale-105',
    glow: 'transition-shadow duration-300 hover:shadow-2xl',
    brighten: 'transition-all duration-300 hover:brightness-110',
    slideRight: 'transition-all duration-300 hover:translate-x-1',
    
    // Combined effects
    liftAndGlow: 'transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl',
    scaleAndBrighten: 'transition-all duration-300 hover:scale-105 hover:brightness-110',
};

/**
 * Hover effect classes for cards
 */
export const cardHoverClasses = {
    lift: 'transition-all duration-300 hover:-translate-y-2 hover:shadow-xl',
    scale: 'transition-transform duration-300 hover:scale-[1.02]',
    glow: 'transition-shadow duration-300 hover:shadow-2xl',
    border: 'transition-all duration-300 hover:border-blue-500',
    
    // Combined effects
    liftAndGlow: 'transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl',
    scaleAndGlow: 'transition-all duration-300 hover:scale-[1.02] hover:shadow-xl',
};

/**
 * Hover effect classes for images
 */
export const imageHoverClasses = {
    zoom: 'transition-transform duration-500 hover:scale-110',
    zoomSlow: 'transition-transform duration-700 hover:scale-105',
    brighten: 'transition-all duration-300 hover:brightness-110',
    grayscaleToColor: 'grayscale hover:grayscale-0 transition-all duration-300',
    blur: 'transition-all duration-300 hover:blur-sm',
};

/**
 * Loading/skeleton animation classes
 */
export const loadingClasses = {
    pulse: 'animate-pulse',
    spin: 'animate-spin',
    bounce: 'animate-bounce',
    shimmer: 'animate-shimmer bg-gradient-to-r from-gray-200 via-gray-100 to-gray-200 bg-[length:200%_100%]',
};

/**
 * Entrance animation classes (works with AOS)
 */
export const entranceAnimations = {
    fadeIn: 'data-aos="fade-in"',
    fadeUp: 'data-aos="fade-up"',
    fadeDown: 'data-aos="fade-down"',
    fadeLeft: 'data-aos="fade-left"',
    fadeRight: 'data-aos="fade-right"',
    zoomIn: 'data-aos="zoom-in"',
    zoomOut: 'data-aos="zoom-out"',
    slideUp: 'data-aos="slide-up"',
    slideDown: 'data-aos="slide-down"',
    flipLeft: 'data-aos="flip-left"',
    flipRight: 'data-aos="flip-right"',
};

/**
 * Micro-animation classes
 */
export const microAnimations = {
    wiggle: 'hover:animate-wiggle',
    heartbeat: 'hover:animate-heartbeat',
    shake: 'hover:animate-shake',
    swing: 'hover:animate-swing',
    rubberBand: 'hover:animate-rubberBand',
};

/**
 * Transition classes
 */
export const transitions = {
    fast: 'transition-all duration-150',
    base: 'transition-all duration-300',
    slow: 'transition-all duration-500',
    slower: 'transition-all duration-700',
};

/**
 * Get combined hover effect class
 */
export function getHoverEffect(type: 'button' | 'card' | 'image', effect: string): string {
    switch (type) {
        case 'button':
            return buttonHoverClasses[effect as keyof typeof buttonHoverClasses] || buttonHoverClasses.lift;
        case 'card':
            return cardHoverClasses[effect as keyof typeof cardHoverClasses] || cardHoverClasses.lift;
        case 'image':
            return imageHoverClasses[effect as keyof typeof imageHoverClasses] || imageHoverClasses.zoom;
        default:
            return transitions.base;
    }
}

/**
 * Generate staggered animation delays for lists
 */
export function getStaggerDelay(index: number, baseDelay: number = 100): number {
    return index * baseDelay;
}

/**
 * Animation configuration presets
 */
export const animationPresets = {
    // Subtle and professional
    subtle: {
        duration: 300,
        easing: 'ease-out',
        hover: 'lift',
    },
    
    // Playful and engaging
    playful: {
        duration: 500,
        easing: 'cubic-bezier(0.68, -0.55, 0.265, 1.55)', // bounce
        hover: 'scale',
    },
    
    // Fast and snappy
    snappy: {
        duration: 150,
        easing: 'ease-in-out',
        hover: 'brighten',
    },
    
    // Smooth and elegant
    elegant: {
        duration: 700,
        easing: 'ease-in-out',
        hover: 'liftAndGlow',
    },
};

/**
 * Get animation preset
 */
export function getAnimationPreset(preset: keyof typeof animationPresets) {
    return animationPresets[preset] || animationPresets.subtle;
}
