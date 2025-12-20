<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { StarIcon } from '@heroicons/vue/24/solid';
import { StarIcon as StarOutlineIcon } from '@heroicons/vue/24/outline';

const props = defineProps<{
    orderId: number;
    productId: number;
    productName: string;
}>();

const emit = defineEmits(['close', 'submitted']);

const rating = ref(0);
const hoverRating = ref(0);
const comment = ref('');
const submitting = ref(false);
const error = ref('');

const setRating = (value: number) => {
    rating.value = value;
};

const submitReview = () => {
    if (rating.value === 0) {
        error.value = 'Please select a rating';
        return;
    }

    if (!comment.value.trim()) {
        error.value = 'Please write a review';
        return;
    }

    submitting.value = true;
    error.value = '';

    router.post(route('marketplace.reviews.store'), {
        order_id: props.orderId,
        product_id: props.productId,
        rating: rating.value,
        comment: comment.value,
    }, {
        onSuccess: () => {
            emit('submitted');
            emit('close');
        },
        onError: (errors) => {
            error.value = errors.message || 'Failed to submit review';
            submitting.value = false;
        },
    });
};
</script>

<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-xl max-w-lg w-full p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Write a Review</h2>
            <p class="text-sm text-gray-600 mb-6">{{ productName }}</p>

            <!-- Rating -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Your Rating *</label>
                <div class="flex gap-2">
                    <button
                        v-for="i in 5"
                        :key="i"
                        @click="setRating(i)"
                        @mouseenter="hoverRating = i"
                        @mouseleave="hoverRating = 0"
                        type="button"
                        class="focus:outline-none"
                    >
                        <StarIcon
                            v-if="i <= (hoverRating || rating)"
                            class="h-10 w-10 text-amber-400"
                            aria-hidden="true"
                        />
                        <StarOutlineIcon
                            v-else
                            class="h-10 w-10 text-gray-300"
                            aria-hidden="true"
                        />
                    </button>
                </div>
                <p v-if="rating > 0" class="text-sm text-gray-600 mt-1">
                    {{ ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'][rating] }}
                </p>
            </div>

            <!-- Comment -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Your Review *</label>
                <textarea
                    v-model="comment"
                    rows="4"
                    placeholder="Share your experience with this product..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                    :disabled="submitting"
                ></textarea>
                <p class="text-xs text-gray-500 mt-1">Minimum 10 characters</p>
            </div>

            <!-- Error -->
            <div v-if="error" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm text-red-800">{{ error }}</p>
            </div>

            <!-- Actions -->
            <div class="flex gap-3">
                <button
                    @click="submitReview"
                    :disabled="submitting"
                    class="flex-1 px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    {{ submitting ? 'Submitting...' : 'Submit Review' }}
                </button>
                <button
                    @click="emit('close')"
                    :disabled="submitting"
                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Cancel
                </button>
            </div>
        </div>
    </div>
</template>
