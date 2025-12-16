import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
    appId: 'com.mygrownet.lifeplus',
    appName: 'Life+',
    webDir: 'public',
    server: {
        androidScheme: 'https',
        // For development, uncomment and set your local IP
        // url: 'http://192.168.1.100:8000',
        // cleartext: true,
    },
    plugins: {
        SplashScreen: {
            launchShowDuration: 2000,
            launchAutoHide: true,
            backgroundColor: '#10b981',
            androidSplashResourceName: 'splash',
            androidScaleType: 'CENTER_CROP',
            showSpinner: false,
            splashFullScreen: true,
            splashImmersive: true,
        },
        // Firebase Cloud Messaging for push notifications
        FirebaseMessaging: {
            presentationOptions: ['badge', 'sound', 'alert'],
        },
        // Legacy PushNotifications (fallback)
        PushNotifications: {
            presentationOptions: ['badge', 'sound', 'alert'],
        },
        LocalNotifications: {
            smallIcon: 'ic_stat_icon',
            iconColor: '#10b981',
            sound: 'default',
        },
        Geolocation: {
            // Request location permissions
        },
        StatusBar: {
            style: 'LIGHT',
            backgroundColor: '#10b981',
        },
        Keyboard: {
            resize: 'body',
            resizeOnFullScreen: true,
        },
        BackgroundRunner: {
            label: 'com.mygrownet.lifeplus.background',
            src: 'background.js',
            event: 'syncData',
            repeat: true,
            interval: 15, // minutes
            autoStart: true,
        },
    },
    android: {
        allowMixedContent: true,
        captureInput: true,
        webContentsDebuggingEnabled: false,
    },
    ios: {
        contentInset: 'automatic',
        scrollEnabled: true,
    },
};

export default config;
