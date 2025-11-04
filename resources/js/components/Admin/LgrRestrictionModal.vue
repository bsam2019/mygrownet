<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" @click.self="close">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            
            <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
                <!-- Header -->
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        LGR Withdrawal Restrictions
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">
                        Manage LGR withdrawal limits for {{ user?.name }}
                    </p>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit">
                    <!-- Block Withdrawals -->
                    <div class="mb-4">
                        <label class="flex items-center">
                            <input
                                v-model="form.lgr_withdrawal_blocked"
                                type="checkbox"
                                class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded"
                            />
                            <span class="ml-2 text-sm font-medium text-gray-700">
                                Block all LGR withdrawals
                            </span>
                        </label>
                        <p class="text-xs text-gray-500 mt-1 ml-6">
                            User will not be able to transfer LGR to wallet
                        </p>
                    </div>

                    <!-- Custom Percentage -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Custom Withdrawable Percentage
                        </label>
                        <div class="flex items-center gap-2">
                            <input
                                v-model.number="form.lgr_custom_withdrawable_percentage"
                                type="number"
                                step="0.01"
                                min="0"
                                max="100"
                                placeholder="Leave empty to use global setting"
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                            />
                            <span class="text-gray-600">%</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            Override global setting ({{ globalPercentage }}%) for this user. Leave empty to use global.
                        </p>
                    </div>

                    <!-- Restriction Reason -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Restriction Reason
                        </label>
                        <textarea
                            v-model="form.lgr_restriction_reason"
                            rows="3"
                            placeholder="Explain why restrictions are applied (shown to user)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                        ></textarea>
                        <p class="text-xs text-gray-500 mt-1">
                            This message will be shown to the user on their wallet page
                        </p>
                    </div>

                    <!-- Current Status -->
                    <div class="mb-6 p-3 bg-gray-50 rounded-lg">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Current Status</h4>
                        <div class="space-y-1 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">LGR Balance:</span>
                                <span class="font-medium">K{{ parseFloat(user?.loyalty_points || 0).toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Awarded:</span>
                                <span class="font-medium">K{{ parseFloat(user?.loyalty_points_awarded_total || 0).toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Withdrawn:</span>
                                <span class="font-medium">K{{ parseFloat(user?.loyalty_points_withdrawn_total || 0).toFixed(2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3">
                        <button
                            type="button"
                            @click="close"
                            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="processing"
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                        >
                            {{ processing ? 'Saving...' : 'Save Restrictions' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    show: Boolean,
    user: Object,
    globalPercentage: {
        type: Number,
        default: 40
    }
});

const emit = defineEmits(['close', 'updated']);

const processing = ref(false);
const form = ref({
    lgr_withdrawal_blocked: false,
    lgr_custom_withdrawable_percentage: null,
    lgr_restriction_reason: ''
});

// Watch for user changes and update form
watch(() => props.user, (newUser) => {
    if (newUser) {
        form.value = {
            lgr_withdrawal_blocked: newUser.lgr_withdrawal_blocked || false,
            lgr_custom_withdrawable_percentage: newUser.lgr_custom_withdrawable_percentage || null,
            lgr_restriction_reason: newUser.lgr_restriction_reason || ''
        };
    }
}, { immediate: true });

const close = () => {
    emit('close');
};

const submit = () => {
    processing.value = true;
    
    router.post(route('admin.users.lgr-restrictions', props.user.id), form.value, {
        preserveScroll: true,
        onSuccess: () => {
            emit('updated');
            close();
        },
        onFinish: () => {
            processing.value = false;
        }
    });
};
</script>
