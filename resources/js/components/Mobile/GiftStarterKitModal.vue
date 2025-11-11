<template>
    <TransitionRoot appear :show="isOpen" as="template">
        <Dialog as="div" @close="closeModal" class="relative z-[200000]">
            <TransitionChild
                as="template"
                enter="duration-300 ease-out"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="duration-200 ease-in"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-black/50" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <TransitionChild
                        as="template"
                        enter="duration-300 ease-out"
                        enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100"
                        leave="duration-200 ease-in"
                        leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95"
                    >
                        <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white shadow-xl transition-all">
                            <!-- Header -->
                            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                                <div class="flex items-center justify-between">
                                    <DialogTitle class="text-lg font-semibold text-white">
                                        🎁 Gift Starter Kit
                                    </DialogTitle>
                                    <button
                                        @click="closeModal"
                                        class="text-white/80 hover:text-white transition-colors"
                                    >
                                        <XMarkIcon class="h-6 w-6" />
                                    </button>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="px-6 py-4 space-y-4">
                                <!-- Recipient Info -->
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <p class="text-sm text-gray-600 mb-1">Gifting to:</p>
                                    <p class="font-semibold text-gray-900">{{ recipient.name }}</p>
                                    <p class="text-sm text-gray-500">{{ recipient.phone }}</p>
                                </div>

                                <!-- Tier Selection -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Select Tier
                                    </label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <button
                                            @click="selectedTier = 'basic'"
                                            :class="[
                                                'relative rounded-lg border-2 p-4 text-left transition-all',
                                                selectedTier === 'basic'
                                                    ? 'border-blue-500 bg-blue-50'
                                                    : 'border-gray-200 hover:border-gray-300'
                                            ]"
                                        >
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="font-semibold text-gray-900">Basic</span>
                                                <CheckCircleIcon
                                                    v-if="selectedTier === 'basic'"
                                                    class="h-5 w-5 text-blue-500"
                                                />
                                            </div>
                                            <p class="text-2xl font-bold text-blue-600">K500</p>
                                        </button>

                                        <button
                                            @click="selectedTier = 'premium'"
                                            :class="[
                                                'relative rounded-lg border-2 p-4 text-left transition-all',
                                                selectedTier === 'premium'
                                                    ? 'border-indigo-500 bg-indigo-50'
                                                    : 'border-gray-200 hover:border-gray-300'
                                            ]"
                                        >
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="font-semibold text-gray-900">Premium</span>
                                                <CheckCircleIcon
                                                    v-if="selectedTier === 'premium'"
                                                    class="h-5 w-5 text-indigo-500"
                                                />
                                            </div>
                                            <p class="text-2xl font-bold text-indigo-600">K1,000</p>
                                        </button>
                                    </div>
                                </div>

                                <!-- Wallet Balance & Limits -->
                                <div v-if="limits" class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Your Wallet Balance:</span>
                                        <span class="font-semibold text-gray-900">
                                            K{{ limits.current_wallet_balance?.toLocaleString() || 0 }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Gifts Remaining This Month:</span>
                                        <span class="font-semibold text-gray-900">
                                            {{ limits.remaining_gifts || 0 }} / {{ limits.max_gifts_per_month || 0 }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Amount Remaining:</span>
                                        <span class="font-semibold text-gray-900">
                                            K{{ limits.remaining_amount?.toLocaleString() || 0 }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Warning Messages -->
                                <div v-if="validationError" class="bg-red-50 border border-red-200 rounded-lg p-3">
                                    <p class="text-sm text-red-800">{{ validationError }}</p>
                                </div>

                                <!-- Terms -->
                                <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                                    <p class="text-xs text-amber-800">
                                        <strong>Note:</strong> This gift is non-refundable. The recipient will receive full access to the starter kit immediately.
                                    </p>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="bg-gray-50 px-6 py-4 flex gap-3">
                                <button
                                    @click="closeModal"
                                    type="button"
                                    class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                                >
                                    Cancel
                                </button>
                                <button
                                    @click="confirmGift"
                                    :disabled="processing || !canGift"
                                    type="button"
                                    :class="[
                                        'flex-1 px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors',
                                        processing || !canGift
                                            ? 'bg-gray-400 cursor-not-allowed'
                                            : 'bg-blue-600 hover:bg-blue-700'
                                    ]"
                                >
                                    <span v-if="processing">Processing...</span>
                                    <span v-else>Confirm Gift</span>
                                </button>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionChild,
    TransitionRoot,
} from '@headlessui/vue';
import { XMarkIcon, CheckCircleIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';

interface Recipient {
    id: number;
    name: string;
    phone: string;
}

interface GiftLimits {
    feature_enabled: boolean;
    max_gifts_per_month: number;
    max_gift_amount_per_month: number;
    min_wallet_balance_required: number;
    gift_fee_percentage: number;
    gifts_used_this_month: number;
    amount_used_this_month: number;
    remaining_gifts: number;
    remaining_amount: number;
    current_wallet_balance: number;
}

const props = defineProps<{
    isOpen: boolean;
    recipient: Recipient;
    walletBalance?: number;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'success'): void;
}>();

const selectedTier = ref<'basic' | 'premium'>('basic');
const processing = ref(false);
const limits = ref<GiftLimits | null>(null);
const validationError = ref('');

const giftAmount = computed(() => selectedTier.value === 'premium' ? 1000 : 500);

const canGift = computed(() => {
    if (!limits.value) {
        console.log('Cannot gift: limits not loaded');
        return false;
    }
    
    const hasBalance = limits.value.current_wallet_balance >= giftAmount.value;
    const hasGiftsRemaining = limits.value.remaining_gifts > 0;
    const hasAmountRemaining = limits.value.remaining_amount >= giftAmount.value;
    const featureEnabled = limits.value.feature_enabled;
    const meetsMinBalance = limits.value.current_wallet_balance >= limits.value.min_wallet_balance_required;
    
    console.log('Gift validation:', {
        hasBalance,
        hasGiftsRemaining,
        hasAmountRemaining,
        featureEnabled,
        meetsMinBalance,
        currentBalance: limits.value.current_wallet_balance,
        minRequired: limits.value.min_wallet_balance_required,
        giftAmount: giftAmount.value,
        limits: limits.value
    });
    
    // Check minimum balance requirement
    if (!meetsMinBalance) {
        console.log(`❌ Below minimum balance. Need ${limits.value.min_wallet_balance_required}, have ${limits.value.current_wallet_balance}`);
    }
    
    return hasBalance && hasGiftsRemaining && hasAmountRemaining && featureEnabled && meetsMinBalance;
});

watch(() => props.isOpen, async (isOpen) => {
    console.log('GiftStarterKitModal isOpen changed to:', isOpen);
    if (isOpen) {
        console.log('Modal opened, fetching limits...');
        await fetchLimits();
        validateGift();
    }
}, { immediate: true });

watch(selectedTier, () => {
    validateGift();
});

async function fetchLimits() {
    try {
        console.log('Fetching gift limits from:', '/mygrownet/gifts/limits');
        const response = await axios.get('/mygrownet/gifts/limits');
        console.log('✅ Gift limits response:', response.data);
        
        // Override wallet balance with prop if provided
        if (props.walletBalance !== undefined) {
            response.data.current_wallet_balance = props.walletBalance;
            console.log('✅ Using wallet balance from prop:', props.walletBalance);
        }
        
        limits.value = response.data;
    } catch (error: any) {
        console.error('❌ Failed to fetch gift limits:', error);
        console.error('Error details:', {
            message: error.message,
            response: error.response?.data,
            status: error.response?.status,
            statusText: error.response?.statusText,
            url: error.config?.url
        });
        
        // Show user-friendly error
        if (error.response?.status === 404) {
            console.error('🔴 Route not found! Check if /mygrownet/gifts/limits exists');
        } else if (error.response?.status === 401) {
            console.error('🔴 Not authenticated! User needs to be logged in');
        } else if (error.response?.status === 500) {
            console.error('🔴 Server error! Check Laravel logs');
        }
        
        // Set default limits if API fails, but use prop wallet balance if available
        console.warn('⚠️ Using fallback limits');
        limits.value = {
            feature_enabled: true,
            max_gifts_per_month: 5,
            max_gift_amount_per_month: 5000,
            min_wallet_balance_required: 1000,
            gift_fee_percentage: 0,
            gifts_used_this_month: 0,
            amount_used_this_month: 0,
            remaining_gifts: 5,
            remaining_amount: 5000,
            current_wallet_balance: props.walletBalance || 0,
        };
    }
}

function validateGift() {
    validationError.value = '';
    
    if (!limits.value) return;
    
    if (!limits.value.feature_enabled) {
        validationError.value = 'Gift feature is currently disabled.';
        return;
    }
    
    if (limits.value.current_wallet_balance < giftAmount.value) {
        validationError.value = `Insufficient wallet balance. You need K${giftAmount.value.toLocaleString()}.`;
        return;
    }
    
    if (limits.value.remaining_gifts <= 0) {
        validationError.value = 'You have reached your monthly gift limit.';
        return;
    }
    
    if (limits.value.remaining_amount < giftAmount.value) {
        validationError.value = `You have exceeded your monthly gift amount limit.`;
        return;
    }
}

function confirmGift() {
    console.log('confirmGift called', { canGift: canGift.value, processing: processing.value });
    
    if (!canGift.value || processing.value) {
        console.log('Cannot gift - validation failed');
        return;
    }
    
    console.log('Submitting gift request...', {
        recipient_id: props.recipient.id,
        tier: selectedTier.value
    });
    
    processing.value = true;
    
    router.post(
        '/mygrownet/gifts/starter-kit',
        {
            recipient_id: props.recipient.id,
            tier: selectedTier.value,
        },
        {
            preserveScroll: true,
            onSuccess: (page) => {
                console.log('✅ Gift successful!', page);
                console.log('Flash messages:', page.props?.flash);
                
                // Check if there's actually a success message
                if (page.props?.flash?.success) {
                    console.log('✅ Backend confirmed success:', page.props.flash.success);
                } else if (page.props?.flash?.error) {
                    console.error('❌ Backend returned error:', page.props.flash.error);
                    validationError.value = page.props.flash.error;
                    return; // Don't close modal on error
                }
                
                // Always close modal and emit success after gift submission
                // (even if no flash message, the page reload indicates success)
                emit('success');
                closeModal();
            },
            onError: (errors) => {
                console.error('❌ Gift failed:', errors);
                validationError.value = errors.message || Object.values(errors).flat().join(', ') || 'Failed to gift starter kit. Please try again.';
            },
            onFinish: () => {
                console.log('Gift request finished');
                processing.value = false;
            },
        }
    );
}

function closeModal() {
    if (!processing.value) {
        emit('close');
        selectedTier.value = 'basic';
        validationError.value = '';
    }
}
</script>
