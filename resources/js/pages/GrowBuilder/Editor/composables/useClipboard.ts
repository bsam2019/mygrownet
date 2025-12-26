/**
 * Clipboard Composable for Section Copy/Paste
 * Supports copying sections across pages and browser sessions
 */
import { ref, computed } from 'vue';

interface Section {
    id: string;
    type: string;
    content: Record<string, any>;
    style: Record<string, any>;
}

// Storage key for clipboard
const CLIPBOARD_KEY = 'growbuilder_clipboard';

// Reactive clipboard state (shared across components)
const clipboardSection = ref<Section | null>(null);
const clipboardSource = ref<string | null>(null); // Page ID where section was copied from

export function useClipboard() {
    /**
     * Copy a section to clipboard
     */
    const copySection = (section: Section, pageId?: string) => {
        // Deep clone the section
        const sectionCopy = JSON.parse(JSON.stringify(section));
        
        // Store in memory
        clipboardSection.value = sectionCopy;
        clipboardSource.value = pageId || null;
        
        // Also store in localStorage for cross-tab support
        try {
            localStorage.setItem(CLIPBOARD_KEY, JSON.stringify({
                section: sectionCopy,
                source: pageId,
                timestamp: Date.now(),
            }));
        } catch (e) {
            console.warn('Failed to save to localStorage:', e);
        }
        
        return true;
    };
    
    /**
     * Cut a section (copy + mark for deletion)
     */
    const cutSection = (section: Section, pageId?: string) => {
        copySection(section, pageId);
        return section.id; // Return ID so caller can delete it
    };
    
    /**
     * Paste section from clipboard
     * Returns a new section with unique ID
     */
    const pasteSection = (): Section | null => {
        // Try memory first, then localStorage
        let sectionToPaste = clipboardSection.value;
        
        if (!sectionToPaste) {
            // Try to restore from localStorage
            try {
                const stored = localStorage.getItem(CLIPBOARD_KEY);
                if (stored) {
                    const data = JSON.parse(stored);
                    // Only use if less than 24 hours old
                    if (Date.now() - data.timestamp < 24 * 60 * 60 * 1000) {
                        sectionToPaste = data.section;
                        clipboardSection.value = data.section;
                        clipboardSource.value = data.source;
                    }
                }
            } catch (e) {
                console.warn('Failed to read from localStorage:', e);
            }
        }
        
        if (!sectionToPaste) return null;
        
        // Create new section with unique ID
        const newSection: Section = {
            ...JSON.parse(JSON.stringify(sectionToPaste)),
            id: `section-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`,
        };
        
        return newSection;
    };
    
    /**
     * Check if clipboard has content
     */
    const hasClipboard = computed(() => {
        if (clipboardSection.value) return true;
        
        // Check localStorage
        try {
            const stored = localStorage.getItem(CLIPBOARD_KEY);
            if (stored) {
                const data = JSON.parse(stored);
                return Date.now() - data.timestamp < 24 * 60 * 60 * 1000;
            }
        } catch (e) {
            // Ignore
        }
        
        return false;
    });
    
    /**
     * Get clipboard section type (for UI display)
     */
    const clipboardType = computed(() => {
        if (clipboardSection.value) {
            return clipboardSection.value.type;
        }
        
        try {
            const stored = localStorage.getItem(CLIPBOARD_KEY);
            if (stored) {
                const data = JSON.parse(stored);
                return data.section?.type || null;
            }
        } catch (e) {
            // Ignore
        }
        
        return null;
    });
    
    /**
     * Clear clipboard
     */
    const clearClipboard = () => {
        clipboardSection.value = null;
        clipboardSource.value = null;
        try {
            localStorage.removeItem(CLIPBOARD_KEY);
        } catch (e) {
            // Ignore
        }
    };
    
    /**
     * Copy section to system clipboard as JSON (for advanced users)
     */
    const copyToSystemClipboard = async (section: Section): Promise<boolean> => {
        try {
            const json = JSON.stringify(section, null, 2);
            await navigator.clipboard.writeText(json);
            return true;
        } catch (e) {
            console.warn('Failed to copy to system clipboard:', e);
            return false;
        }
    };
    
    /**
     * Paste from system clipboard (if valid JSON section)
     */
    const pasteFromSystemClipboard = async (): Promise<Section | null> => {
        try {
            const text = await navigator.clipboard.readText();
            const data = JSON.parse(text);
            
            // Validate it looks like a section
            if (data && data.type && data.content) {
                return {
                    ...data,
                    id: `section-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`,
                };
            }
        } catch (e) {
            // Not valid JSON or not a section
        }
        
        return null;
    };
    
    return {
        copySection,
        cutSection,
        pasteSection,
        hasClipboard,
        clipboardType,
        clipboardSource,
        clearClipboard,
        copyToSystemClipboard,
        pasteFromSystemClipboard,
    };
}
