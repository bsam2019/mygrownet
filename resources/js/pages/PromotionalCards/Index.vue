<template>
    <ClientLayout>
        <Head title="Share & Earn - Promotional Cards" />

        <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Share & Earn</h1>
                    <p class="text-gray-600">Share promotional cards on social media and earn LGR activity credits!</p>
                </div>

                <!-- Stats Card -->
                <div v-if="userStats" class="mb-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold mb-1">Your Sharing Progress</h3>
                            <p class="text-blue-100 text-sm">Share 5 cards today to earn LGR credit</p>
                        </div>
                        <div class="text-right">
                            <div class="text-4xl font-bold">{{ userStats.today_shares }}/5</div>
                            <div class="text-sm text-blue-100">Today's Shares</div>
                        </div>
                    </div>
                    <div class="mt-4 bg-white/20 rounded-full h-2">
                        <div 
                            class="bg-white rounded-full h-2 transition-all duration-300"
                            :style="{ width: `${Math.min((userStats.today_shares / 5) * 100, 100)}%` }"
                        ></div>
                    </div>
                    <div class="mt-3 text-sm">
                        <span v-if="userStats.today_shares >= 5" class="flex items-center gap-2">
                            <CheckCircleIcon class="h-5 w-5" />
                            Great job! You've earned today's LGR credit
                        </span>
                        <span v-else>
                            {{ 5 - userStats.today_shares }} more {{ 5 - userStats.today_shares === 1 ? 'share' : 'shares' }} to earn today's credit
                        </span>
                    </div>
                </div>

                <!-- Category Filter -->
                <div class="mb-6 flex flex-wrap gap-2">
                    <button
                        @click="filterCategory(null)"
                        :class="[
                            'px-4 py-2 rounded-lg font-medium transition-colors',
                            !selectedCategory 
                                ? 'bg-blue-600 text-white' 
                                : 'bg-white text-gray-700 hover:bg-gray-50'
                        ]"
                    >
                        All Cards
                    </button>
                    <button
                        v-for="category in categories"
                        :key="category.value"
                        @click="filterCategory(category.value)"
                        :class="[
                            'px-4 py-2 rounded-lg font-medium transition-colors',
                            selectedCategory === category.value
                                ? 'bg-blue-600 text-white'
                                : 'bg-white text-gray-700 hover:bg-gray-50'
                        ]"
                    >
                        {{ category.label }}
                    </button>
                </div>

                <!-- Cards Grid -->
                <div v-if="cards.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="card in cards"
                        :key="card.id"
                        class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300"
                    >
                        <!-- Card Image -->
                        <div class="relative aspect-[16/9] bg-gray-100">
                            <img
                                :src="card.image_url"
                                :alt="card.title"
                                class="w-full h-full object-cover"
                            />
                            <div class="absolute top-3 right-3">
                                <span
                                    :class="[
                                        'px-3 py-1 rounded-full text-xs font-semibold',
                                        getCategoryColor(card.category)
                                    ]"
                                >
                                    {{ getCategoryLabel(card.category) }}
                                </span>
                            </div>
                        </div>

                        <!-- Card Content -->
                        <div class="p-5">
                            <h3 class="text-lg font-bold text-gray-900 mb-2">{{ card.title }}</h3>
                            <p v-if="card.description" class="text-sm text-gray-600 mb-4 line-clamp-2">
                                {{ card.description }}
                            </p>

                            <!-- Stats -->
                            <div class="flex items-center gap-4 text-sm text-gray-500 mb-4">
                                <span class="flex items-center gap-1">
                                    <ShareIcon class="h-4 w-4" />
                                    {{ card.share_count }} shares
                                </span>
                                <span class="flex items-center gap-1">
                                    <EyeIcon class="h-4 w-4" />
                                    {{ card.view_count }} views
                                </span>
                            </div>

                            <!-- Share Buttons -->
                            <SocialShareButtons
                                :url="getCardShareUrl(card)"
                                :text="card.og_title || card.title"
                                :platforms="['facebook', 'whatsapp', 'twitter', 'copy']"
                                @share="handleShare(card, $event)"
                            />
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-16">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                        <ShareIcon class="h-8 w-8 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No Cards Available</h3>
                    <p class="text-gray-600">Check back soon for new promotional cards to share!</p>
                </div>
            </div>
        </div>
    </ClientLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import ClientLayout from '@/layouts/ClientLayout.vue';
import SocialShareButtons from '@/components/SocialShareButtons.vue';
import { ShareIcon, EyeIcon, CheckCircleIcon } from 'lucide-vue-next';
import axios from 'axios';

interface Card {
    id: number;
    title: string;
    slug: string;
    description: string | null;
    image_url: string;
    category: string;
    share_count: number;
    view_count: number;
    og_title: string | null;
}

interface UserStats {
    today_shares: number;
    total_shares: number;
}

const props = defineProps<{
    cards: Card[];
    userStats: UserStats | null;
    selectedCategory: string | null;
}>();

const categories = [
    { value: 'general', label: 'General' },
    { value: 'opportunity', label: 'Opportunity' },
    { value: 'training', label: 'Training' },
    { value: 'success', label: 'Success Stories' },
    { value: 'announcement', label: 'Announcements' },
];

const filterCategory = (category: string | null) => {
    router.get(route('promotional-cards.index'), { category }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const getCategoryLabel = (category: string): string => {
    return categories.find(c => c.value === category)?.label || category;
};

const getCategoryColor = (category: string): string => {
    const colors: Record<string, string> = {
        general: 'bg-blue-100 text-blue-800',
        opportunity: 'bg-green-100 text-green-800',
        training: 'bg-purple-100 text-purple-800',
        success: 'bg-yellow-100 text-yellow-800',
        announcement: 'bg-red-100 text-red-800',
    };
    return colors[category] || 'bg-gray-100 text-gray-800';
};

const getCardShareUrl = (card: Card): string => {
    return route('promotional-cards.show', card.slug);
};

const handleShare = async (card: Card, platform: string) => {
    try {
        await axios.post(route('promotional-cards.share', card.id), {
            platform,
        });
        
        // Refresh stats
        router.reload({ only: ['userStats'] });
    } catch (error) {
        console.error('Failed to record share:', error);
    }
};
</script>
