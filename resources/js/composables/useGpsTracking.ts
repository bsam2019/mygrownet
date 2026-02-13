import { ref } from 'vue';

interface Position {
    latitude: number;
    longitude: number;
    accuracy: number;
    timestamp: number;
}

export function useGpsTracking() {
    const currentPosition = ref<Position | null>(null);
    const isTracking = ref(false);
    const error = ref<string | null>(null);
    const watchId = ref<number | null>(null);

    // Check if geolocation is available
    const isGpsAvailable = () => {
        return !!navigator.geolocation;
    };

    // Get current position once
    const getCurrentPosition = (): Promise<Position> => {
        return new Promise((resolve, reject) => {
            if (!isGpsAvailable()) {
                reject(new Error('GPS not available on this device'));
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const pos: Position = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                        accuracy: position.coords.accuracy,
                        timestamp: position.timestamp,
                    };
                    currentPosition.value = pos;
                    error.value = null;
                    resolve(pos);
                },
                (err) => {
                    error.value = getErrorMessage(err);
                    reject(new Error(error.value));
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0,
                }
            );
        });
    };

    // Start continuous tracking
    const startTracking = (callback?: (position: Position) => void) => {
        if (!isGpsAvailable()) {
            error.value = 'GPS not available on this device';
            return false;
        }

        if (isTracking.value) {
            return true;
        }

        watchId.value = navigator.geolocation.watchPosition(
            (position) => {
                const pos: Position = {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    accuracy: position.coords.accuracy,
                    timestamp: position.timestamp,
                };
                currentPosition.value = pos;
                error.value = null;
                isTracking.value = true;

                if (callback) {
                    callback(pos);
                }
            },
            (err) => {
                error.value = getErrorMessage(err);
                isTracking.value = false;
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 5000,
            }
        );

        return true;
    };

    // Stop tracking
    const stopTracking = () => {
        if (watchId.value !== null) {
            navigator.geolocation.clearWatch(watchId.value);
            watchId.value = null;
            isTracking.value = false;
        }
    };

    // Calculate distance between two positions (in meters)
    const calculateDistance = (
        lat1: number,
        lon1: number,
        lat2: number,
        lon2: number
    ): number => {
        const R = 6371e3; // Earth's radius in meters
        const φ1 = (lat1 * Math.PI) / 180;
        const φ2 = (lat2 * Math.PI) / 180;
        const Δφ = ((lat2 - lat1) * Math.PI) / 180;
        const Δλ = ((lon2 - lon1) * Math.PI) / 180;

        const a =
            Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
            Math.cos(φ1) * Math.cos(φ2) * Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

        return R * c;
    };

    // Get Google Maps URL
    const getGoogleMapsUrl = (position: Position): string => {
        return `https://www.google.com/maps?q=${position.latitude},${position.longitude}`;
    };

    // Get error message
    const getErrorMessage = (err: GeolocationPositionError): string => {
        switch (err.code) {
            case err.PERMISSION_DENIED:
                return 'Location permission denied. Please enable location access.';
            case err.POSITION_UNAVAILABLE:
                return 'Location information unavailable.';
            case err.TIMEOUT:
                return 'Location request timed out.';
            default:
                return 'An unknown error occurred.';
        }
    };

    return {
        currentPosition,
        isTracking,
        error,
        isGpsAvailable,
        getCurrentPosition,
        startTracking,
        stopTracking,
        calculateDistance,
        getGoogleMapsUrl,
    };
}
