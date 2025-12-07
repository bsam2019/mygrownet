import { ref, computed, onMounted, onUnmounted } from 'vue';

const MOBILE_BREAKPOINT = 768;
const TABLET_BREAKPOINT = 1024;

const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1024);
const windowHeight = ref(typeof window !== 'undefined' ? window.innerHeight : 768);
const orientation = ref<'portrait' | 'landscape'>('portrait');

let resizeHandler: (() => void) | null = null;
let orientationHandler: (() => void) | null = null;

export function useMobileDetect() {
    const isMobile = computed(() => windowWidth.value < MOBILE_BREAKPOINT);
    const isTablet = computed(() => windowWidth.value >= MOBILE_BREAKPOINT && windowWidth.value < TABLET_BREAKPOINT);
    const isDesktop = computed(() => windowWidth.value >= TABLET_BREAKPOINT);
    const isMobileOrTablet = computed(() => windowWidth.value < TABLET_BREAKPOINT);
    
    const isTouch = computed(() => {
        if (typeof window === 'undefined') return false;
        return 'ontouchstart' in window || navigator.maxTouchPoints > 0;
    });

    const isPortrait = computed(() => orientation.value === 'portrait');
    const isLandscape = computed(() => orientation.value === 'landscape');

    const safeAreaInsets = ref({
        top: 0,
        right: 0,
        bottom: 0,
        left: 0
    });

    const updateDimensions = () => {
        windowWidth.value = window.innerWidth;
        windowHeight.value = window.innerHeight;
        orientation.value = window.innerHeight > window.innerWidth ? 'portrait' : 'landscape';
        
        // Get safe area insets (for notched devices)
        const computedStyle = getComputedStyle(document.documentElement);
        safeAreaInsets.value = {
            top: parseInt(computedStyle.getPropertyValue('--sat') || '0', 10),
            right: parseInt(computedStyle.getPropertyValue('--sar') || '0', 10),
            bottom: parseInt(computedStyle.getPropertyValue('--sab') || '0', 10),
            left: parseInt(computedStyle.getPropertyValue('--sal') || '0', 10)
        };
    };

    const handleOrientationChange = () => {
        // Small delay to let the browser update dimensions
        setTimeout(updateDimensions, 100);
    };

    onMounted(() => {
        if (typeof window === 'undefined') return;

        updateDimensions();

        resizeHandler = () => {
            requestAnimationFrame(updateDimensions);
        };

        orientationHandler = handleOrientationChange;

        window.addEventListener('resize', resizeHandler);
        window.addEventListener('orientationchange', orientationHandler);

        // Set CSS custom properties for safe areas
        document.documentElement.style.setProperty('--sat', 'env(safe-area-inset-top)');
        document.documentElement.style.setProperty('--sar', 'env(safe-area-inset-right)');
        document.documentElement.style.setProperty('--sab', 'env(safe-area-inset-bottom)');
        document.documentElement.style.setProperty('--sal', 'env(safe-area-inset-left)');
    });

    onUnmounted(() => {
        if (resizeHandler) {
            window.removeEventListener('resize', resizeHandler);
        }
        if (orientationHandler) {
            window.removeEventListener('orientationchange', orientationHandler);
        }
    });

    return {
        // Breakpoint states
        isMobile,
        isTablet,
        isDesktop,
        isMobileOrTablet,
        
        // Touch detection
        isTouch,
        
        // Orientation
        isPortrait,
        isLandscape,
        orientation,
        
        // Dimensions
        windowWidth,
        windowHeight,
        
        // Safe areas (for notched devices)
        safeAreaInsets
    };
}
