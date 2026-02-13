import { ref } from 'vue';

export function useCameraCapture() {
    const stream = ref<MediaStream | null>(null);
    const isCapturing = ref(false);
    const error = ref<string | null>(null);

    // Check if camera is available
    const isCameraAvailable = () => {
        return !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
    };

    // Start camera
    const startCamera = async (videoElement: HTMLVideoElement) => {
        if (!isCameraAvailable()) {
            error.value = 'Camera not available on this device';
            return false;
        }

        try {
            stream.value = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'environment', // Use back camera on mobile
                    width: { ideal: 1920 },
                    height: { ideal: 1080 },
                },
            });

            videoElement.srcObject = stream.value;
            isCapturing.value = true;
            error.value = null;
            return true;
        } catch (err) {
            error.value = 'Failed to access camera. Please check permissions.';
            console.error('Camera error:', err);
            return false;
        }
    };

    // Stop camera
    const stopCamera = () => {
        if (stream.value) {
            stream.value.getTracks().forEach((track) => track.stop());
            stream.value = null;
            isCapturing.value = false;
        }
    };

    // Capture photo
    const capturePhoto = (videoElement: HTMLVideoElement): string | null => {
        if (!stream.value) {
            error.value = 'Camera not started';
            return null;
        }

        try {
            const canvas = document.createElement('canvas');
            canvas.width = videoElement.videoWidth;
            canvas.height = videoElement.videoHeight;

            const context = canvas.getContext('2d');
            if (!context) {
                error.value = 'Failed to create canvas context';
                return null;
            }

            context.drawImage(videoElement, 0, 0, canvas.width, canvas.height);
            return canvas.toDataURL('image/jpeg', 0.9);
        } catch (err) {
            error.value = 'Failed to capture photo';
            console.error('Capture error:', err);
            return null;
        }
    };

    // Upload from file input
    const uploadFromFile = (file: File): Promise<string> => {
        return new Promise((resolve, reject) => {
            if (!file.type.startsWith('image/')) {
                reject(new Error('File must be an image'));
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                if (e.target?.result) {
                    resolve(e.target.result as string);
                } else {
                    reject(new Error('Failed to read file'));
                }
            };
            reader.onerror = () => reject(new Error('Failed to read file'));
            reader.readAsDataURL(file);
        });
    };

    return {
        stream,
        isCapturing,
        error,
        isCameraAvailable,
        startCamera,
        stopCamera,
        capturePhoto,
        uploadFromFile,
    };
}
