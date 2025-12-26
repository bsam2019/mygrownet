/**
 * Composable for computing background styles (solid or gradient)
 * Used by section components to support gradient backgrounds
 */
import { computed, type ComputedRef } from 'vue';

interface SectionStyle {
    backgroundColor?: string;
    backgroundType?: 'solid' | 'gradient';
    gradientFrom?: string;
    gradientTo?: string;
    gradientDirection?: string;
    textColor?: string;
    minHeight?: number;
    [key: string]: any;
}

interface BackgroundStyleOptions {
    style: ComputedRef<SectionStyle | undefined> | SectionStyle | undefined;
    defaultBgColor?: string;
    defaultTextColor?: string;
    minHeight?: string;
}

export function useBackgroundStyle(options: BackgroundStyleOptions) {
    const backgroundStyle = computed(() => {
        const s = 'value' in (options.style || {}) 
            ? (options.style as ComputedRef<SectionStyle | undefined>).value 
            : options.style as SectionStyle | undefined;
        
        const result: Record<string, string> = {};
        
        // Handle gradient background
        if (s?.backgroundType === 'gradient' && s?.gradientFrom && s?.gradientTo) {
            const degMap: Record<string, string> = {
                'to-r': '90deg',
                'to-b': '180deg',
                'to-br': '135deg',
                'to-tr': '45deg',
            };
            const direction = degMap[s.gradientDirection || 'to-r'] || '90deg';
            result.background = `linear-gradient(${direction}, ${s.gradientFrom}, ${s.gradientTo})`;
        } else {
            // Solid background
            result.backgroundColor = s?.backgroundColor || options.defaultBgColor || '#ffffff';
        }
        
        // Add text color
        if (options.defaultTextColor) {
            result.color = s?.textColor || options.defaultTextColor;
        }
        
        // Add min height if provided
        if (options.minHeight) {
            result.minHeight = options.minHeight;
        }
        
        return result;
    });
    
    return { backgroundStyle };
}

/**
 * Simple function version for use in computed properties
 */
export function getBackgroundStyle(
    style: SectionStyle | undefined,
    defaultBgColor = '#ffffff',
    defaultTextColor?: string,
    minHeight?: string
): Record<string, string> {
    const result: Record<string, string> = {};
    
    // Handle gradient background
    if (style?.backgroundType === 'gradient' && style?.gradientFrom && style?.gradientTo) {
        const degMap: Record<string, string> = {
            'to-r': '90deg',
            'to-b': '180deg',
            'to-br': '135deg',
            'to-tr': '45deg',
        };
        const direction = degMap[style.gradientDirection || 'to-r'] || '90deg';
        result.background = `linear-gradient(${direction}, ${style.gradientFrom}, ${style.gradientTo})`;
    } else {
        // Solid background
        result.backgroundColor = style?.backgroundColor || defaultBgColor;
    }
    
    // Add text color
    if (defaultTextColor) {
        result.color = style?.textColor || defaultTextColor;
    }
    
    // Add min height if provided
    if (minHeight) {
        result.minHeight = minHeight;
    }
    
    return result;
}
