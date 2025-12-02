/**
 * Inertia SPA Enhancements
 * 
 * Provides utilities for better SPA experience:
 * - Global loading state
 * - Navigation events
 * - Optimistic UI helpers
 * - Partial reload utilities
 */

import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

// Global loading state
const isNavigating = ref(false);
const loadingProgress = ref(0);
let listenersInitialized = false;

/**
 * Track navigation state globally
 */
export function useNavigationState() {
    return {
        isNavigating,
        loadingProgress,
    };
}

/**
 * Initialize global navigation listeners
 * Call this once in your app setup
 */
export function initNavigationListeners() {
    // Prevent multiple initializations
    if (listenersInitialized) return;
    listenersInitialized = true;
    
    let progressInterval: ReturnType<typeof setInterval> | null = null;

    router.on('start', () => {
        isNavigating.value = true;
        loadingProgress.value = 0;
        
        // Simulate progress
        progressInterval = setInterval(() => {
            if (loadingProgress.value < 90) {
                loadingProgress.value += Math.random() * 10;
            }
        }, 100);
    });

    router.on('finish', () => {
        if (progressInterval) {
            clearInterval(progressInterval);
            progressInterval = null;
        }
        loadingProgress.value = 100;
        
        // Small delay before hiding
        setTimeout(() => {
            isNavigating.value = false;
            loadingProgress.value = 0;
        }, 200);
    });

    router.on('error', () => {
        if (progressInterval) {
            clearInterval(progressInterval);
            progressInterval = null;
        }
        isNavigating.value = false;
        loadingProgress.value = 0;
    });
}

/**
 * Partial reload helper - only fetch specific props
 */
export function usePartialReload() {
    const reload = (only: string[]) => {
        router.reload({ only });
    };

    return { reload };
}

/**
 * Optimistic UI helper for form submissions
 */
export function useOptimisticSubmit<T>() {
    const isSubmitting = ref(false);
    const optimisticData = ref<T | null>(null);

    const submit = (
        method: 'post' | 'put' | 'patch' | 'delete',
        url: string,
        data: Record<string, any>,
        options?: {
            optimistic?: T;
            onSuccess?: () => void;
            onError?: (errors: Record<string, string>) => void;
            preserveScroll?: boolean;
        }
    ) => {
        isSubmitting.value = true;
        
        // Set optimistic data immediately
        if (options?.optimistic) {
            optimisticData.value = options.optimistic;
        }

        router[method](url, data, {
            preserveScroll: options?.preserveScroll ?? true,
            onSuccess: () => {
                isSubmitting.value = false;
                optimisticData.value = null;
                options?.onSuccess?.();
            },
            onError: (errors) => {
                isSubmitting.value = false;
                // Revert optimistic update on error
                optimisticData.value = null;
                options?.onError?.(errors);
            },
        });
    };

    return {
        isSubmitting,
        optimisticData,
        submit,
    };
}

/**
 * Prefetch links on hover for faster navigation
 */
export function usePrefetch() {
    const prefetchedUrls = new Set<string>();

    const prefetch = (url: string) => {
        if (prefetchedUrls.has(url)) return;
        
        prefetchedUrls.add(url);
        
        // Create a hidden link and trigger prefetch
        const link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = url;
        document.head.appendChild(link);
    };

    return { prefetch };
}

/**
 * Scroll position preservation helper
 */
export function useScrollPreservation() {
    const scrollPositions = new Map<string, number>();

    const saveScrollPosition = (key: string) => {
        scrollPositions.set(key, window.scrollY);
    };

    const restoreScrollPosition = (key: string) => {
        const position = scrollPositions.get(key);
        if (position !== undefined) {
            window.scrollTo(0, position);
        }
    };

    return {
        saveScrollPosition,
        restoreScrollPosition,
    };
}

/**
 * Navigate with loading state
 */
export function useNavigate() {
    const navigate = (
        url: string,
        options?: {
            preserveScroll?: boolean;
            preserveState?: boolean;
            only?: string[];
            replace?: boolean;
        }
    ) => {
        router.visit(url, {
            preserveScroll: options?.preserveScroll ?? false,
            preserveState: options?.preserveState ?? false,
            only: options?.only,
            replace: options?.replace ?? false,
        });
    };

    const back = () => {
        window.history.back();
    };

    return { navigate, back };
}
