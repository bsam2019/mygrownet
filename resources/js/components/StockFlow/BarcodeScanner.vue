<script setup lang="ts">
import { ref, onUnmounted } from 'vue';
import { CameraIcon, XMarkIcon } from '@heroicons/vue/24/outline';

const emit = defineEmits<{
    scanned: [value: string];
}>();

const showScanner = ref(false);
const showInput = ref(false);
const manualBarcode = ref('');
const cameraError = ref('');
const videoRef = ref<HTMLVideoElement | null>(null);
let mediaStream: MediaStream | null = null;

const startCamera = async () => {
    cameraError.value = '';
    showInput.value = false;
    try {
        mediaStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
        if (videoRef.value) {
            videoRef.value.srcObject = mediaStream;
            await videoRef.value.play();
        }
        showScanner.value = true;
    } catch (err) {
        cameraError.value = 'Camera not available. Please enter barcode manually.';
        showInput.value = true;
    }
};

const captureFrame = async () => {
    if (!videoRef.value) return;
    try {
        if ('BarcodeDetector' in window) {
            const detector = new (window as any).BarcodeDetector({ formats: ['qr_code', 'ean_13', 'ean_8', 'code_128', 'code_39', 'upc_a', 'upc_e'] });
            const barcodes = await detector.detect(videoRef.value);
            if (barcodes.length > 0) {
                emit('scanned', barcodes[0].rawValue);
                closeScanner();
                return;
            }
        }
        cameraError.value = 'No barcode detected. Try again or enter manually.';
        showInput.value = true;
    } catch {
        cameraError.value = 'Barcode detection failed. Enter manually.';
        showInput.value = true;
    }
};

const submitManual = () => {
    if (manualBarcode.value.trim()) {
        emit('scanned', manualBarcode.value.trim());
        closeScanner();
    }
};

const closeScanner = () => {
    if (mediaStream) {
        mediaStream.getTracks().forEach(t => t.stop());
        mediaStream = null;
    }
    showScanner.value = false;
    showInput.value = false;
    cameraError.value = '';
    manualBarcode.value = '';
};

onUnmounted(() => {
    if (mediaStream) {
        mediaStream.getTracks().forEach(t => t.stop());
    }
});
</script>

<template>
    <div>
        <button @click="startCamera" type="button" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-600 hover:text-emerald-600 hover:border-emerald-200">
            <CameraIcon class="h-4 w-4" />
            Scan Barcode
        </button>

        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showScanner" class="fixed inset-0 z-[200] flex items-center justify-center bg-black/70 p-4">
                    <div class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-2xl">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Scan Barcode</h3>
                            <button @click="closeScanner" class="rounded p-1 hover:bg-gray-100">
                                <XMarkIcon class="h-5 w-5 text-gray-500" />
                            </button>
                        </div>

                        <div class="relative bg-black rounded-lg overflow-hidden">
                            <video ref="videoRef" class="w-full h-64 object-cover" playsinline />
                            <div class="absolute inset-0 border-2 border-emerald-500 rounded-lg pointer-events-none" />
                        </div>

                        <p v-if="cameraError" class="mt-2 text-sm text-red-600">{{ cameraError }}</p>

                        <div class="mt-4 flex gap-2">
                            <button @click="captureFrame" class="flex-1 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                                Capture
                            </button>
                            <button @click="closeScanner" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Cancel
                            </button>
                        </div>

                        <div v-if="showInput" class="mt-4">
                            <label class="block text-sm font-medium text-gray-700">Or enter barcode manually</label>
                            <div class="mt-1 flex gap-2">
                                <input v-model="manualBarcode" @keydown.enter.prevent="submitManual" type="text" class="flex-1 rounded-lg border-gray-300 text-sm focus:ring-emerald-500 focus:border-emerald-500" placeholder="Enter barcode..." />
                                <button @click="submitManual" class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-700">OK</button>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<style scoped>
.modal-enter-active { transition: all 0.2s ease-out; }
.modal-leave-active { transition: all 0.15s ease-in; }
.modal-enter-from { opacity: 0; }
.modal-leave-to { opacity: 0; }
</style>
