<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { StarIcon } from '@heroicons/vue/24/solid';
import { HandThumbUpIcon, HandThumbDownIcon, CheckBadgeIcon } from '@heroicons/vue/24/outline';

interface Review {
    id: number;
    rating: number;
    comment: string;
    is_verified_purchase: boolean;
    helpful_count: number;
    not_helpful_count: number;
    seller_response?: string;
    seller_responded_at?: string;
    formatted_date: string;
    buyer: {
        name: string;
    };
}

const props = defineProps<{
    reviews: Review[];
    productId: number;
}>();

const votingReview = ref<number | null>(null);

const voteHelpful = (reviewId: number, isHelpful: boolean) => {
    votingReview.value = reviewId;
    
    router.post(route('marketplace.reviews.vote', reviewId), {
        is_helpful: isHelpful,
    }, {
        preserveScroll: true,
        onFinish: () => {
            votingReview.value = null;
        },
    });
};
</script>

<template>
    <div class="space-y-6">
        <div v-if="reviews.length === 0" class="text-center py-12">
            <StarIcon class="h-12 w-12 text-gray-300 mx-auto mb-3" aria-hidden="true" />
            <p class="text-gray-500">No reviews yet</p>
            <p class="text-sm text-gray-400 mt-1">Be the first to review this product</p>
        </div>

        <div
            v-for="review in reviews"
            :key="review.id"
            class="border-b border-gray-200 pb-6 last:border-0"
        >
            <!-- Reviewer Info -->
            <div class="flex items-start justify-between mb-3">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <p class="font-medium text-gray-900">{{ review.buyer.name }}</p>
                        <CheckBadgeIcon
                            v-if="review.is_verified_purchase"
                            class="h-4 w-4 text-green-600"
                            title="Verified Purchase"
                            aria-hidden="true"
                        />
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex">
                            <StarIcon
                                v-for="i in 5"
                                :key="i"
                                :class="[
                                    'h-4 w-4',
                                    i <= review.rating ? 'text-amber-400' : 'text-gray-300'
                                ]"
                                aria-hidden="true"
                            />
                        </div>
                        <span class="text-sm text-gray-500">{{ review.formatted_date }}</span>
                    </div>
                </div>
            </div>

            <!-- Review Comment -->
            <p class="text-gray-700 mb-4">{{ review.comment }}</p>

            <!-- Seller Response -->
            <div v-if="review.seller_response" class="bg-gray-50 rounded-lg p-4 mb-4">
                <p class="text-sm font-medium text-gray-900 mb-1">Seller Response</p>
                <p class="text-sm text-gray-700">{{ review.seller_response }}</p>
            </div>

            <!-- Helpful Votes -->
            <div class="flex items-center gap-4">
                <p class="text-sm text-gray-600">Was this review helpful?</p>
                <div class="flex gap-2">
                    <button
                        @click="voteHelpful(review.id, true)"
                        :disabled="votingReview === review.id"
                        class="flex items-center gap-1 px-3 py-1 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50"
                    >
                        <HandThumbUpIcon class="h-4 w-4" aria-hidden="true" />
                        Yes ({{ review.helpful_count }})
                    </button>
                    <button
                        @click="voteHelpful(review.id, false)"
                        :disabled="votingReview === review.id"
                        class="flex items-center gap-1 px-3 py-1 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50"
                    >
                        <HandThumbDownIcon class="h-4 w-4" aria-hidden="true" />
                        No ({{ review.not_helpful_count }})
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
