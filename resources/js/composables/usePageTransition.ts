import { ref, computed, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

export type TransitionType = 'slide-left' | 'slide-right' | 'fade' | 'scale' | 'none';

// Track navigation history for direction detection
const navigationHistory = ref<string[]>([]);
const currentTransition = ref<TransitionType>('fade');
const prefersReducedMotion = ref(false);

// Check for reduced motion preference
if (typeof window !== 'undefined') {
    const mediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    prefersReducedMotion.value = mediaQuery.matches;
    
    mediaQuery.addEventListener('change', (e) => {
        prefersReducedMotion.value = e.matches;
    });
}

export function usePageTransition() {
    const page = usePage();

    // Determine transition based on navigation direction
    const determineTransition = (newUrl: string): TransitionType => {
        if (prefersReducedMotion.value) return 'none';

        const history = navigationHistory.value;
        const lastUrl = history[history.length - 1];

        // Check if going back
        if (history.length > 1 && history[history.length - 2] === newUrl) {
            return 'slide-right';
        }

        // Check URL depth for forward/back detection
        const currentDepth = (lastUrl?.match(/\//g) || []).length;
        const newDepth = (newUrl.match(/\//g) || []).length;

        if (newDepth > currentDepth) {
            return 'slide-left'; // Going deeper
        } else if (newDepth < currentDepth) {
            return 'slide-right'; // Going back
        }

        return 'fade'; // Same level
    };

    // Update history on navigation
    const updateHistory = (url: string) => {
        const history = navigationHistory.value;
        
        // If going back, pop from history
        if (history.length > 1 && history[history.length - 2] === url) {
            history.pop();
        } else {
            // Add to history (limit to 50 entries)
            history.push(url);
            if (history.length > 50) {
                history.shift();
            }
        }
    };

    // Watch for page changes
    watch(
        () => page.url,
        (newUrl, oldUrl) => {
            if (oldUrl && newUrl !== oldUrl) {
                currentTransition.value = determineTransition(newUrl);
                updateHistory(newUrl);
            }
        },
        { immediate: true }
    );

    // Initialize history with current URL
    if (navigationHistory.value.length === 0 && page.url) {
        navigationHistory.value.push(page.url);
    }

    // Transition classes for Vue transitions
    const transitionClasses = computed(() => {
        const type = currentTransition.value;

        if (type === 'none') {
            return {
                enterActiveClass: '',
                leaveActiveClass: '',
                enterFromClass: '',
                leaveToClass: '',
            };
        }

        if (type === 'slide-left') {
            return {
                enterActiveClass: 'transition-transform duration-300 ease-out',
                leaveActiveClass: 'transition-transform duration-300 ease-out',
                enterFromClass: 'translate-x-full',
                leaveToClass: '-translate-x-full',
            };
        }

        if (type === 'slide-right') {
            return {
                enterActiveClass: 'transition-transform duration-300 ease-out',
                leaveActiveClass: 'transition-transform duration-300 ease-out',
                enterFromClass: '-translate-x-full',
                leaveToClass: 'translate-x-full',
            };
        }

        if (type === 'scale') {
            return {
                enterActiveClass: 'transition-all duration-200 ease-out',
                leaveActiveClass: 'transition-all duration-200 ease-out',
                enterFromClass: 'opacity-0 scale-95',
                leaveToClass: 'opacity-0 scale-95',
            };
        }

        // Default fade
        return {
            enterActiveClass: 'transition-opacity duration-200 ease-out',
            leaveActiveClass: 'transition-opacity duration-200 ease-out',
            enterFromClass: 'opacity-0',
            leaveToClass: 'opacity-0',
        };
    });

    // Set transition manually
    const setTransition = (type: TransitionType) => {
        currentTransition.value = prefersReducedMotion.value ? 'none' : type;
    };

    // Navigate with specific transition
    const navigateWithTransition = (url: string, transition: TransitionType = 'slide-left') => {
        setTransition(transition);
        router.visit(url);
    };

    // Go back with slide-right transition
    const goBack = (fallbackUrl?: string) => {
        setTransition('slide-right');
        
        if (navigationHistory.value.length > 1) {
            window.history.back();
        } else if (fallbackUrl) {
            router.visit(fallbackUrl);
        }
    };

    return {
        // State
        currentTransition: computed(() => currentTransition.value),
        prefersReducedMotion: computed(() => prefersReducedMotion.value),
        transitionClasses,

        // Actions
        setTransition,
        navigateWithTransition,
        goBack,
    };
}
