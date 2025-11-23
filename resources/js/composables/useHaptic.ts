/**
 * Haptic Feedback Composable
 * Provides haptic feedback for mobile devices
 */

export type HapticType = 'light' | 'medium' | 'heavy' | 'success' | 'warning' | 'error';

export function useHaptic() {
  const isSupported = 'vibrate' in navigator;

  const patterns: Record<HapticType, number[]> = {
    light: [10],
    medium: [20],
    heavy: [30],
    success: [10, 50, 10],
    warning: [20, 100, 20],
    error: [30, 100, 30, 100, 30]
  };

  const trigger = (type: HapticType = 'light') => {
    if (!isSupported) return;
    
    try {
      navigator.vibrate(patterns[type]);
    } catch (error) {
      console.warn('Haptic feedback failed:', error);
    }
  };

  return {
    trigger,
    isSupported
  };
}
