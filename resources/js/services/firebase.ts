/**
 * Firebase Configuration for MyGrowNet Life+
 * 
 * This module initializes Firebase services for:
 * - Push Notifications (FCM)
 * - Analytics
 * - Crashlytics (optional)
 */

import { initializeApp, type FirebaseApp } from 'firebase/app';
import { getMessaging, getToken, onMessage, type Messaging } from 'firebase/messaging';
import { getAnalytics, logEvent, type Analytics } from 'firebase/analytics';

// Firebase configuration - Replace with your actual Firebase project config
const firebaseConfig = {
    apiKey: import.meta.env.VITE_FIREBASE_API_KEY || '',
    authDomain: import.meta.env.VITE_FIREBASE_AUTH_DOMAIN || '',
    projectId: import.meta.env.VITE_FIREBASE_PROJECT_ID || '',
    storageBucket: import.meta.env.VITE_FIREBASE_STORAGE_BUCKET || '',
    messagingSenderId: import.meta.env.VITE_FIREBASE_MESSAGING_SENDER_ID || '',
    appId: import.meta.env.VITE_FIREBASE_APP_ID || '',
    measurementId: import.meta.env.VITE_FIREBASE_MEASUREMENT_ID || '',
};

let app: FirebaseApp | null = null;
let messaging: Messaging | null = null;
let analytics: Analytics | null = null;

/**
 * Initialize Firebase app
 */
export function initializeFirebase(): FirebaseApp | null {
    if (app) return app;
    
    // Only initialize if config is present
    if (!firebaseConfig.apiKey || !firebaseConfig.projectId) {
        console.warn('Firebase config not found. Set VITE_FIREBASE_* environment variables.');
        return null;
    }

    try {
        app = initializeApp(firebaseConfig);
        console.log('Firebase initialized successfully');
        return app;
    } catch (error) {
        console.error('Firebase initialization error:', error);
        return null;
    }
}

/**
 * Initialize Firebase Cloud Messaging for web push notifications
 */
export async function initializeMessaging(): Promise<Messaging | null> {
    if (messaging) return messaging;
    
    const firebaseApp = initializeFirebase();
    if (!firebaseApp) return null;

    try {
        messaging = getMessaging(firebaseApp);
        
        // Handle foreground messages
        onMessage(messaging, (payload) => {
            console.log('Foreground message received:', payload);
            
            // Show notification using browser API
            if (Notification.permission === 'granted' && payload.notification) {
                new Notification(payload.notification.title || 'Life+', {
                    body: payload.notification.body,
                    icon: '/icons/icon-192x192.png',
                    badge: '/icons/badge-72x72.png',
                });
            }
        });

        return messaging;
    } catch (error) {
        console.error('Firebase Messaging initialization error:', error);
        return null;
    }
}

/**
 * Request notification permission and get FCM token
 */
export async function requestNotificationPermission(): Promise<string | null> {
    try {
        const permission = await Notification.requestPermission();
        
        if (permission !== 'granted') {
            console.log('Notification permission denied');
            return null;
        }

        const msg = await initializeMessaging();
        if (!msg) return null;

        // Get FCM token - requires VAPID key for web push
        const vapidKey = import.meta.env.VITE_FIREBASE_VAPID_KEY;
        if (!vapidKey) {
            console.warn('VAPID key not configured for web push');
            return null;
        }

        const token = await getToken(msg, { vapidKey });
        console.log('FCM Token:', token);
        
        return token;
    } catch (error) {
        console.error('Error getting FCM token:', error);
        return null;
    }
}

/**
 * Initialize Firebase Analytics
 */
export function initializeAnalytics(): Analytics | null {
    if (analytics) return analytics;
    
    const firebaseApp = initializeFirebase();
    if (!firebaseApp) return null;

    try {
        // Analytics only works in browser environment
        if (typeof window !== 'undefined') {
            analytics = getAnalytics(firebaseApp);
            console.log('Firebase Analytics initialized');
        }
        return analytics;
    } catch (error) {
        console.error('Firebase Analytics initialization error:', error);
        return null;
    }
}

/**
 * Log analytics event
 */
export function trackEvent(eventName: string, params?: Record<string, unknown>): void {
    const analyticsInstance = analytics || initializeAnalytics();
    if (analyticsInstance) {
        logEvent(analyticsInstance, eventName, params);
    }
}

/**
 * Track page view
 */
export function trackPageView(pageName: string, pageTitle?: string): void {
    trackEvent('page_view', {
        page_path: pageName,
        page_title: pageTitle || pageName,
    });
}

/**
 * Track user action
 */
export function trackUserAction(action: string, category: string, label?: string, value?: number): void {
    trackEvent(action, {
        event_category: category,
        event_label: label,
        value: value,
    });
}

// Export types
export type { FirebaseApp, Messaging, Analytics };
