import { ref } from 'vue';

type HapticPattern = 'light' | 'medium' | 'heavy' | 'success' | 'warning' | 'error' | 'selection';

interface HapticPatterns {
    light: number[];
    medium: number[];
    heavy: number[];
    success: number[];
    warning: number[];
    error: number[];
    selection: number[];
}

const patterns: HapticPatterns = {
    light: [10],
    medium: [20],
    heavy: [30],
    success: [10, 50, 10],
    warning: [30, 50, 30],
    error: [50, 100, 50, 100, 50],
    selection: [5]
};

const isSupported = ref(false);
const isEnabled = ref(true);

export function useHaptics() {
    // Check if vibration API is supported
    const checkSupport = (): boolean => {
        if (typeof navigator === 'undefined') return false;
        isSupported.value = 'vibrate' in navigator;
        return isSupported.value;
    };

    // Initialize on first use
    checkSupport();

    /**
     * Trigger haptic feedback with a specific pattern
     */
    const trigger = (pattern: HapticPattern = 'light'): boolean => {
        if (!isSupported.value || !isEnabled.value) return false;

        try {
            const vibrationPattern = patterns[pattern] || patterns.light;
            return navigator.vibrate(vibrationPattern);
        } catch (error) {
            console.warn('[Haptics] Vibration failed:', error);
            return false;
        }
    };

    /**
     * Light tap feedback - for button presses
     */
    const light = () => trigger('light');

    /**
     * Medium feedback - for swipe actions
     */
    const medium = () => trigger('medium');

    /**
     * Heavy feedback - for important actions
     */
    const heavy = () => trigger('heavy');

    /**
     * Success feedback - for completed actions
     */
    const success = () => trigger('success');

    /**
     * Warning feedback - for warnings
     */
    const warning = () => trigger('warning');

    /**
     * Error feedback - for errors
     */
    const error = () => trigger('error');

    /**
     * Selection feedback - for selections/toggles
     */
    const selection = () => trigger('selection');

    /**
     * Custom vibration pattern
     */
    const custom = (pattern: number[]): boolean => {
        if (!isSupported.value || !isEnabled.value) return false;

        try {
            return navigator.vibrate(pattern);
        } catch (err) {
            console.warn('[Haptics] Custom vibration failed:', err);
            return false;
        }
    };

    /**
     * Stop any ongoing vibration
     */
    const stop = (): boolean => {
        if (!isSupported.value) return false;

        try {
            return navigator.vibrate(0);
        } catch (err) {
            return false;
        }
    };

    /**
     * Enable/disable haptic feedback
     */
    const setEnabled = (enabled: boolean) => {
        isEnabled.value = enabled;
        // Persist preference
        localStorage.setItem('bizboost-haptics-enabled', enabled.toString());
    };

    /**
     * Load saved preference
     */
    const loadPreference = () => {
        const saved = localStorage.getItem('bizboost-haptics-enabled');
        if (saved !== null) {
            isEnabled.value = saved === 'true';
        }
    };

    // Load preference on init
    loadPreference();

    return {
        // State
        isSupported,
        isEnabled,

        // Pattern triggers
        trigger,
        light,
        medium,
        heavy,
        success,
        warning,
        error,
        selection,

        // Custom
        custom,
        stop,

        // Settings
        setEnabled
    };
}
