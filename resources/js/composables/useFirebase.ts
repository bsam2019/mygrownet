/**
 * Firebase Composable for Vue Components
 * 
 * Unified interface for Firebase services that works on both
 * web and native (Capacitor) platforms
 */

import { ref, onMounted, onUnmounted } from 'vue';
import { Capacitor } from '@capacitor/core';
import {
    initializeFirebase,
    requestNotificationPermission,
    trackEvent,
    trackPageView,
    trackUserAction,
    initializeAnalytics,
} from '@/services/firebase';
import {
    isNativePlatform,
    requestNativeNotificationPermission,
    getNativeFCMToken,
    addNativeNotificationListeners,
    logNativeEvent,
    setNativeUserId,
    trackNativeScreen,
    initializeNativeAnalytics,
} from '@/services/capacitor-firebase';

export function useFirebase() {
    const fcmToken = ref<string | null>(null);
    const notificationPermission = ref<boolean>(false);
    const isInitialized = ref(false);
    const isNative = ref(Capacitor.isNativePlatform());

    let cleanupListeners: (() => void) | null = null;

    /**
     * Initialize Firebase based on platform
     */
    async function initialize(): Promise<void> {
        if (isInitialized.value) return;

        if (isNative.value) {
            await initializeNativeAnalytics();
        } else {
            initializeFirebase();
            initializeAnalytics();
        }

        isInitialized.value = true;
    }

    /**
     * Request notification permission and get FCM token
     */
    async function requestPermission(): Promise<string | null> {
        if (isNative.value) {
            const granted = await requestNativeNotificationPermission();
            notificationPermission.value = granted;
            
            if (granted) {
                fcmToken.value = await getNativeFCMToken();
            }
        } else {
            fcmToken.value = await requestNotificationPermission();
            notificationPermission.value = !!fcmToken.value;
        }

        return fcmToken.value;
    }

    /**
     * Register notification listeners
     */
    async function registerNotificationListeners(
        onReceived?: (notification: unknown) => void,
        onTapped?: (notification: unknown) => void
    ): Promise<void> {
        if (isNative.value && onReceived && onTapped) {
            cleanupListeners = await addNativeNotificationListeners(onReceived, onTapped);
        }
    }

    /**
     * Log analytics event (works on both platforms)
     */
    async function logEvent(name: string, params?: Record<string, unknown>): Promise<void> {
        if (isNative.value) {
            await logNativeEvent(name, params);
        } else {
            trackEvent(name, params);
        }
    }

    /**
     * Track page/screen view
     */
    async function trackScreen(screenName: string, screenClass?: string): Promise<void> {
        if (isNative.value) {
            await trackNativeScreen(screenName, screenClass);
        } else {
            trackPageView(screenName, screenClass);
        }
    }

    /**
     * Track user action
     */
    async function trackAction(
        action: string,
        category: string,
        label?: string,
        value?: number
    ): Promise<void> {
        if (isNative.value) {
            await logNativeEvent(action, {
                event_category: category,
                event_label: label,
                value,
            });
        } else {
            trackUserAction(action, category, label, value);
        }
    }

    /**
     * Set user ID for analytics
     */
    async function setUserId(userId: string | null): Promise<void> {
        if (isNative.value) {
            await setNativeUserId(userId);
        }
        // Web analytics user ID is typically set via gtag
    }

    /**
     * Send FCM token to backend for storage
     */
    async function registerTokenWithBackend(token: string): Promise<boolean> {
        try {
            const response = await fetch('/api/notifications/register-device', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({
                    token,
                    platform: isNative.value ? Capacitor.getPlatform() : 'web',
                }),
            });

            return response.ok;
        } catch (error) {
            console.error('Failed to register FCM token with backend:', error);
            return false;
        }
    }

    // Cleanup on unmount
    onUnmounted(() => {
        if (cleanupListeners) {
            cleanupListeners();
        }
    });

    return {
        // State
        fcmToken,
        notificationPermission,
        isInitialized,
        isNative,

        // Methods
        initialize,
        requestPermission,
        registerNotificationListeners,
        logEvent,
        trackScreen,
        trackAction,
        setUserId,
        registerTokenWithBackend,
    };
}

/**
 * Auto-initialize Firebase on app mount
 */
export function useFirebaseAutoInit() {
    const firebase = useFirebase();

    onMounted(async () => {
        await firebase.initialize();
    });

    return firebase;
}
