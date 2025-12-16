/**
 * Firebase Messaging Service Worker
 * 
 * Handles background push notifications for web browsers
 */

// Import Firebase scripts
importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging-compat.js');

// Firebase configuration - will be populated from environment
// For production, replace these with your actual Firebase config
const firebaseConfig = {
    apiKey: '',
    authDomain: '',
    projectId: '',
    storageBucket: '',
    messagingSenderId: '',
    appId: '',
    measurementId: '',
};

// Initialize Firebase only if config is present
if (firebaseConfig.apiKey && firebaseConfig.projectId) {
    firebase.initializeApp(firebaseConfig);
    const messaging = firebase.messaging();

    // Handle background messages
    messaging.onBackgroundMessage((payload) => {
        console.log('[firebase-messaging-sw.js] Received background message:', payload);

        const notificationTitle = payload.notification?.title || 'Life+';
        const notificationOptions = {
            body: payload.notification?.body || '',
            icon: '/icons/icon-192x192.png',
            badge: '/icons/badge-72x72.png',
            tag: payload.data?.tag || 'default',
            data: payload.data,
            actions: [
                {
                    action: 'open',
                    title: 'Open',
                },
                {
                    action: 'dismiss',
                    title: 'Dismiss',
                },
            ],
        };

        self.registration.showNotification(notificationTitle, notificationOptions);
    });

    // Handle notification click
    self.addEventListener('notificationclick', (event) => {
        console.log('[firebase-messaging-sw.js] Notification clicked:', event);

        event.notification.close();

        if (event.action === 'dismiss') {
            return;
        }

        // Open the app or focus existing window
        const urlToOpen = event.notification.data?.url || '/lifeplus';

        event.waitUntil(
            clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
                // Check if there's already a window open
                for (const client of windowClients) {
                    if (client.url.includes(self.location.origin) && 'focus' in client) {
                        client.focus();
                        if (urlToOpen) {
                            client.navigate(urlToOpen);
                        }
                        return;
                    }
                }

                // Open new window if none exists
                if (clients.openWindow) {
                    return clients.openWindow(urlToOpen);
                }
            })
        );
    });
} else {
    console.log('[firebase-messaging-sw.js] Firebase config not set, skipping initialization');
}
