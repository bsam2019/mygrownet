<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" @click.self="close">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            
            <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
                <!-- Header -->
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Set Loan Limit
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">
                        Manage loan limit for {{ user?.name }}
                    </p>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit">
                    <!-- Current Info -->
                    <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Current Status</h4>
                        <div class="space-y-1 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tier:</span>
                                <span class="font-medium capitalize">{{ user?.starter_kit_tier || 'None' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Current Limit:</span>
                                <span class="font-medium">K{{ parseFloat(user?.loan_limit || 0).toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Loan Balance:</span>
                                <span class="font-medium">K{{ parseFloat(user?.loan_balance || 0).toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Available Credit:</span>
                                <span class="font-medium text-green-600">
                                    K{{ parseFloat((user?.loan_limit || 0) - (user?.loan_balance || 0)).toFixed(2) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Loan Limit Input -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            New Loan Limit (K)
                        </label>
                        <input
                            v-model.number="form.loan_limit"
                            type="number"
                            step="100"
                            min="0"
                            max="50000"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                            placeholder="5000"
                        />
                        <p class="text-xs text-gray-500 mt-1">
                            Recommended: Basic K2,000 | Premium K5,000 | Max K50,000
                        </p>
                    </div>

                    <!-- Quick Presets -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Quick Presets
                        </label>
                        <div class="grid grid-cols-4 gap-2">
                            <button
                                type="button"
                                @click="form.loan_limit = 0"
                                class="px-3 py-2 text-xs font-medium text-gray-700 bg-gray-100 rounded hover:bg-gray-200"
                            >
                                K0
                            </button>
                            <button
                                type="button"
                                @click="form.loan_limit = 2000"
                                class="px-3 py-2 text-xs font-medium text-gray-700 bg-blue-100 rounded hover:bg-blue-200"
                            >
                                K2,000
                            </button>
                            <button
                                type="button"
                                @click="form.loan_limit = 5000"
                                class="px-3 py-2 text-xs font-medium text-gray-700 bg-purple-100 rounded hover:bg-purple-200"
                            >
                                K5,000
                            </button>
                            <button
                                type="button"
                                @click="form.loan_limit = 10000"
                                class="px-3 py-2 text-xs font-medium text-gray-700 bg-green-100 rounded hover:bg-green-200"
                            >
                                K10,000
                            </button>
                        </div>
                    </div>

                    <!-- Info Box -->
                    <div class="mb-6 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-xs text-blue-800">
                            <strong>Note:</strong> The loan limit determines the maximum amount a member can borrow. 
                            They can apply for loans up to their available credit (limit - current balance).
                        </p>
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
                            {{ processing ? 'Saving...' : 'Update Limit' }}
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
});

const emit = defineEmits(['close', 'updated']);

const processing = ref(false);
const form = ref({
    loan_limit: 0,
});

// Watch for user changes and update form
watch(() => props.user, (newUser) => {
    if (newUser) {
        form.value = {
            loan_limit: newUser.loan_limit || 0,
        };
    }
}, { immediate: true });

const close = () => {
    emit('close');
};

const submit = () => {
    processing.value = true;
    
    router.post(route('admin.users.loan-limit', props.user.id), form.value, {
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
