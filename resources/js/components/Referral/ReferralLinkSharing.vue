<template>
    <div class="referral-link-sharing space-y-6">
        <!-- Referral Code Section -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Your Referral Code</h3>
                    <p class="text-gray-600">Share this code with potential investors</p>
                </div>
                <button 
                    @click="generateNewCode"
                    :disabled="isGenerating"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors text-sm font-medium"
                >
                    <RefreshCwIcon v-if="isGenerating" class="h-4 w-4 animate-spin mr-2" />
                    <RefreshCwIcon v-else class="h-4 w-4 mr-2" />
                    {{ isGenerating ? 'Generating...' : 'Generate New' }}
                </button>
            </div>
            
            <div class="flex items-center space-x-4">
                <div class="flex-1">
                    <div class="relative">
                        <input 
                            type="text" 
                            :value="referralCode" 
                            readonly 
                            class="w-full px-4 py-3 text-lg font-mono font-bold text-center bg-gray-50 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-blue-500"
                        />
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <button 
                                @click="copyCode"
                                class="p-2 text-gray-400 hover:text-gray-600 transition-colors"
                                title="Copy code"
                            >
                                <CopyIcon class="h-5 w-5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Code Statistics -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <div class="text-lg font-bold text-gray-900">{{ codeStats.uses_count }}</div>
                    <div class="text-xs text-gray-600">Times Used</div>
                </div>
                <div class="text-center p-3 bg-emerald-50 rounded-lg">
                    <div class="text-lg font-bold text-emerald-600">{{ codeStats.successful_registrations }}</div>
                    <div class="text-xs text-gray-600">Registrations</div>
                </div>
                <div class="text-center p-3 bg-blue-50 rounded-lg">
                    <div class="text-lg font-bold text-blue-600">{{ codeStats.active_investors }}</div>
                    <div class="text-xs text-gray-600">Active Members</div>
                </div>
                <div class="text-center p-3 bg-purple-50 rounded-lg">
                    <div class="text-lg font-bold text-purple-600">{{ formatCurrency(codeStats.total_earnings) }}</div>
                    <div class="text-xs text-gray-600">Total Earnings</div>
                </div>
            </div>
        </div>

        <!-- Referral Link Section -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Referral Link</h3>
                    <p class="text-gray-600">Direct link for easy sharing</p>
                </div>
                <div class="flex items-center space-x-2">
                    <button 
                        @click="shortenLink"
                        :disabled="isShortening"
                        class="px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 disabled:opacity-50 transition-colors"
                    >
                        {{ isShortening ? 'Shortening...' : 'Shorten' }}
                    </button>
                    <button 
                        @click="generateQRCode"
                        class="px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors"
                    >
                        <QrCodeIcon class="h-4 w-4 mr-1" />
                        QR Code
                    </button>
                </div>
            </div>
            
            <div class="flex items-center space-x-4">
                <div class="flex-1">
                    <div class="relative">
                        <input 
                            type="text" 
                            :value="displayLink" 
                            readonly 
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-md text-sm focus:outline-none focus:border-blue-500"
                        />
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <button 
                                @click="copyLink"
                                class="p-2 text-gray-400 hover:text-gray-600 transition-colors"
                                title="Copy link"
                            >
                                <CopyIcon class="h-5 w-5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Link Analytics -->
            <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Link Clicks:</span>
                    <span class="font-medium text-gray-900">{{ linkStats.clicks }}</span>
                </div>
                <div class="flex items-center justify-between text-sm mt-2">
                    <span class="text-gray-600">Conversion Rate:</span>
                    <span class="font-medium text-emerald-600">{{ linkStats.conversion_rate }}%</span>
                </div>
            </div>
        </div>

        <!-- Social Sharing -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Share on Social Media</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <button 
                    @click="shareOnPlatform('whatsapp')"
                    class="flex items-center justify-center space-x-2 p-4 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors"
                >
                    <MessageCircleIcon class="h-5 w-5" />
                    <span class="font-medium">WhatsApp</span>
                </button>
                
                <button 
                    @click="shareOnPlatform('facebook')"
                    class="flex items-center justify-center space-x-2 p-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                    <FacebookIcon class="h-5 w-5" />
                    <span class="font-medium">Facebook</span>
                </button>
                
                <button 
                    @click="shareOnPlatform('twitter')"
                    class="flex items-center justify-center space-x-2 p-4 bg-sky-500 text-white rounded-lg hover:bg-sky-600 transition-colors"
                >
                    <TwitterIcon class="h-5 w-5" />
                    <span class="font-medium">Twitter</span>
                </button>
                
                <button 
                    @click="shareOnPlatform('email')"
                    class="flex items-center justify-center space-x-2 p-4 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
                >
                    <MailIcon class="h-5 w-5" />
                    <span class="font-medium">Email</span>
                </button>
            </div>
        </div>

        <!-- Custom Message Templates -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Message Templates</h3>
            <div class="space-y-4">
                <div v-for="template in messageTemplates" :key="template.id" class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-medium text-gray-900">{{ template.title }}</h4>
                        <button 
                            @click="useTemplate(template)"
                            class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                        >
                            Use Template
                        </button>
                    </div>
                    <p class="text-sm text-gray-600 mb-3">{{ template.description }}</p>
                    <div class="bg-gray-50 rounded-md p-3">
                        <p class="text-sm text-gray-800">{{ template.message }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- QR Code Modal -->
        <div v-if="showQRModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click="closeQRModal">
            <div class="bg-white rounded-lg p-6 max-w-sm w-full mx-4" @click.stop>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">QR Code</h3>
                    <button @click="closeQRModal" class="text-gray-400 hover:text-gray-600">
                        <XIcon class="h-6 w-6" />
                    </button>
                </div>
                <div class="text-center">
                    <div class="bg-white p-4 rounded-lg border-2 border-gray-200 inline-block">
                        <canvas ref="qrCanvas" class="max-w-full"></canvas>
                    </div>
                    <p class="text-sm text-gray-600 mt-4">Scan this QR code to access your referral link</p>
                    <button 
                        @click="downloadQR"
                        class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors text-sm font-medium"
                    >
                        <DownloadIcon class="h-4 w-4 mr-2" />
                        Download QR Code
                    </button>
                </div>
            </div>
        </div>

        <!-- Success Toast -->
        <div v-if="showToast" class="fixed bottom-4 right-4 bg-emerald-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center space-x-2">
            <CheckCircleIcon class="h-5 w-5" />
            <span>{{ toastMessage }}</span>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import { 
    RefreshCwIcon, 
    CopyIcon, 
    QrCodeIcon, 
    MessageCircleIcon,
    MailIcon,
    XIcon,
    DownloadIcon,
    CheckCircleIcon
} from 'lucide-vue-next';
import { formatCurrency } from '@/utils/formatting';

// Social media icons (you might want to use actual brand icons)
const FacebookIcon = MessageCircleIcon;
const TwitterIcon = MessageCircleIcon;

interface CodeStats {
    uses_count: number;
    successful_registrations: number;
    active_investors: number;
    total_earnings: number;
}

interface LinkStats {
    clicks: number;
    conversion_rate: number;
}

interface MessageTemplate {
    id: number;
    title: string;
    description: string;
    message: string;
}

interface Props {
    referralCode: string;
    referralLink: string;
    shortLink?: string;
    codeStats: CodeStats;
    linkStats: LinkStats;
    messageTemplates: MessageTemplate[];
}

const props = defineProps<Props>();

// Reactive state
const isGenerating = ref(false);
const isShortening = ref(false);
const showQRModal = ref(false);
const showToast = ref(false);
const toastMessage = ref('');
const qrCanvas = ref<HTMLCanvasElement>();

// Computed properties
const displayLink = computed(() => props.shortLink || props.referralLink);

// Methods
const generateNewCode = async () => {
    isGenerating.value = true;
    try {
        await router.post(route('my-team.generate-code'), {}, {
            preserveState: true,
            onSuccess: () => {
                showToastMessage('New referral code generated successfully!');
            },
            onError: () => {
                showToastMessage('Failed to generate new code. Please try again.');
            }
        });
    } finally {
        isGenerating.value = false;
    }
};

const copyCode = async () => {
    try {
        await navigator.clipboard.writeText(props.referralCode);
        showToastMessage('Referral code copied to clipboard!');
    } catch (err) {
        console.error('Failed to copy code:', err);
        showToastMessage('Failed to copy code. Please try again.');
    }
};

const copyLink = async () => {
    try {
        await navigator.clipboard.writeText(displayLink.value);
        showToastMessage('Referral link copied to clipboard!');
    } catch (err) {
        console.error('Failed to copy link:', err);
        showToastMessage('Failed to copy link. Please try again.');
    }
};

const shortenLink = async () => {
    isShortening.value = true;
    try {
        // This would typically call an API to shorten the link
        // For now, we'll simulate it
        await new Promise(resolve => setTimeout(resolve, 1000));
        showToastMessage('Link shortened successfully!');
    } finally {
        isShortening.value = false;
    }
};

const generateQRCode = async () => {
    showQRModal.value = true;
    await nextTick();
    
    if (qrCanvas.value) {
        // You would typically use a QR code library like qrcode.js here
        // For now, we'll create a placeholder
        const ctx = qrCanvas.value.getContext('2d');
        if (ctx) {
            qrCanvas.value.width = 200;
            qrCanvas.value.height = 200;
            ctx.fillStyle = '#000';
            ctx.fillRect(0, 0, 200, 200);
            ctx.fillStyle = '#fff';
            ctx.font = '12px Arial';
            ctx.textAlign = 'center';
            ctx.fillText('QR Code', 100, 100);
            ctx.fillText('Placeholder', 100, 120);
        }
    }
};

const closeQRModal = () => {
    showQRModal.value = false;
};

const downloadQR = () => {
    if (qrCanvas.value) {
        const link = document.createElement('a');
        link.download = `referral-qr-${props.referralCode}.png`;
        link.href = qrCanvas.value.toDataURL();
        link.click();
        showToastMessage('QR code downloaded successfully!');
    }
};

const shareOnPlatform = (platform: string) => {
    const message = `Join VBIF and start earning with my referral code: ${props.referralCode}. Click here: ${displayLink.value}`;
    
    const urls = {
        whatsapp: `https://wa.me/?text=${encodeURIComponent(message)}`,
        facebook: `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(displayLink.value)}`,
        twitter: `https://twitter.com/intent/tweet?text=${encodeURIComponent(message)}`,
        email: `mailto:?subject=${encodeURIComponent('Join VBIF Investment Fund')}&body=${encodeURIComponent(message)}`
    };
    
    const url = urls[platform as keyof typeof urls];
    if (url) {
        window.open(url, '_blank', 'width=600,height=400');
    }
};

const useTemplate = async (template: MessageTemplate) => {
    const message = template.message.replace('{referral_code}', props.referralCode).replace('{referral_link}', displayLink.value);
    
    try {
        await navigator.clipboard.writeText(message);
        showToastMessage('Template copied to clipboard!');
    } catch (err) {
        console.error('Failed to copy template:', err);
        showToastMessage('Failed to copy template. Please try again.');
    }
};

const showToastMessage = (message: string) => {
    toastMessage.value = message;
    showToast.value = true;
    setTimeout(() => {
        showToast.value = false;
    }, 3000);
};


</script>

<style scoped>
/* Custom animations */
@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.fixed.bottom-4.right-4 {
    animation: slideIn 0.3s ease-out;
}

/* Hover effects */
.bg-white:hover {
    @apply shadow-md;
    transition: box-shadow 0.2s ease-in-out;
}

/* Button hover effects */
button:hover {
    transform: translateY(-1px);
    transition: transform 0.2s ease-in-out;
}

/* Modal backdrop */
.fixed.inset-0.bg-black {
    backdrop-filter: blur(4px);
}
</style>