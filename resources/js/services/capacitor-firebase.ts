/**
 * Capacitor Firebase Integration for Native Mobile Apps
 * 
 * Uses @capacitor-firebase plugins for native push notifications
 * and analytics on iOS/Android
 */

import { Capacitor } from '@capacitor/core';

// Types for Capacitor Firebase plugins
interface FirebaseMessagingPlugin {
    requestPermissions(): Promise<{ receive: string }>;
    getToken(): Promise<{ token: string }>;
    addListener(event: string, callback: (data: unknown) => void): Promise<{ remove: () => void }>;
    removeAllListeners(): Promise<void>;
}

interface FirebaseAnalyticsPlugin {
    setEnabled(options: { enabled: boolean }): Promise<void>;
    logEvent(options: { name: string; params?: Record<string, unknown> }): Promise<void>;
    setUserId(options: { userId: string | null }): Promise<void>;
    setUserProperty(options: { key: string; value: string | null }): Promise<void>;
    setCurrentScreen(options: { screenName: string; screenClass?: string }): Promise<void>;
}

let FirebaseMessaging: FirebaseMessagingPlugin | null = null;
let FirebaseAnalytics: FirebaseAnalyticsPlugin | null = null;

/**
 * Check if running on native platform
 */
export function isNativePlatform(): boolean {
    return Capacitor.isNativePlatform();
}

/**
 * Initialize Capacitor Firebase Messaging (for native apps)
 */
export async function initializeNativeMessaging(): Promise<void> {
    if (!isNativePlatform()) {
        console.log('Not a native platform, skipping Capacitor Firebase Messaging');
        return;
    }

    try {
        const module = await import('@capacitor-firebase/messaging');
        FirebaseMessaging = module.FirebaseMessaging as unknown as FirebaseMessagingPlugin;
        console.log('Capacitor Firebase Messaging loaded');
    } catch (error) {
        console.error('Failed to load Capacitor Firebase Messaging:', error);
    }
}

/**
 * Request push notification permissions on native
 */
export async function requestNativeNotificationPermission(): Promise<boolean> {
    if (!FirebaseMessaging) {
        await initializeNativeMessaging();
    }

    if (!FirebaseMessaging) {
        return false;
    }

    try {
        const result = await FirebaseMessaging.requestPermissions();
        return result.receive === 'granted';
    } catch (error) {
        console.error('Error requesting native notification permission:', error);
        return false;
    }
}

/**
 * Get FCM token on native platform
 */
export async function getNativeFCMToken(): Promise<string | null> {
    if (!FirebaseMessaging) {
        await initializeNativeMessaging();
    }

    if (!FirebaseMessaging) {
        return null;
    }

    try {
        const result = await FirebaseMessaging.getToken();
        console.log('Native FCM Token:', result.token);
        return result.token;
    } catch (error) {
        console.error('Error getting native FCM token:', error);
        return null;
    }
}

/**
 * Listen for push notifications on native
 */
export async function addNativeNotificationListeners(
    onNotificationReceived: (notification: unknown) => void,
    onNotificationTapped: (notification: unknown) => void
): Promise<() => void> {
    if (!FirebaseMessaging) {
        await initializeNativeMessaging();
    }

    if (!FirebaseMessaging) {
        return () => {};
    }

    const listeners: Array<{ remove: () => void }> = [];

    try {
        // Notification received while app is in foreground
        const foregroundListener = await FirebaseMessaging.addListener(
            'notificationReceived',
            onNotificationReceived
        );
        listeners.push(foregroundListener);

        // Notification tapped (app opened from notification)
        const tapListener = await FirebaseMessaging.addListener(
            'notificationActionPerformed',
            onNotificationTapped
        );
        listeners.push(tapListener);

        // Return cleanup function
        return () => {
            listeners.forEach(l => l.remove());
        };
    } catch (error) {
        console.error('Error adding notification listeners:', error);
        return () => {};
    }
}

/**
 * Initialize Capacitor Firebase Analytics (for native apps)
 */
export async function initializeNativeAnalytics(): Promise<void> {
    if (!isNativePlatform()) {
        console.log('Not a native platform, skipping Capacitor Firebase Analytics');
        return;
    }

    try {
        const module = await import('@capacitor-firebase/analytics');
        FirebaseAnalytics = module.FirebaseAnalytics as unknown as FirebaseAnalyticsPlugin;
        await FirebaseAnalytics.setEnabled({ enabled: true });
        console.log('Capacitor Firebase Analytics loaded and enabled');
    } catch (error) {
        console.error('Failed to load Capacitor Firebase Analytics:', error);
    }
}

/**
 * Log event on native analytics
 */
export async function logNativeEvent(name: string, params?: Record<string, unknown>): Promise<void> {
    if (!FirebaseAnalytics) {
        await initializeNativeAnalytics();
    }

    if (!FirebaseAnalytics) {
        return;
    }

    try {
        await FirebaseAnalytics.logEvent({ name, params });
    } catch (error) {
        console.error('Error logging native event:', error);
    }
}

/**
 * Set user ID for native analytics
 */
export async function setNativeUserId(userId: string | null): Promise<void> {
    if (!FirebaseAnalytics) {
        await initializeNativeAnalytics();
    }

    if (!FirebaseAnalytics) {
        return;
    }

    try {
        await FirebaseAnalytics.setUserId({ userId });
    } catch (error) {
        console.error('Error setting native user ID:', error);
    }
}

/**
 * Track screen view on native
 */
export async function trackNativeScreen(screenName: string, screenClass?: string): Promise<void> {
    if (!FirebaseAnalytics) {
        await initializeNativeAnalytics();
    }

    if (!FirebaseAnalytics) {
        return;
    }

    try {
        await FirebaseAnalytics.setCurrentScreen({ screenName, screenClass });
    } catch (error) {
        console.error('Error tracking native screen:', error);
    }
}
