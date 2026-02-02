<template>
    <AdminLayout>
        <Head title="Promotional Cards Management" />

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Promotional Cards</h1>
                        <p class="text-gray-600 mt-1">Manage shareable promotional cards for LGR activities</p>
                    </div>
                    <button
                        @click="showCreateModal = true"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        <PlusIcon class="h-5 w-5" />
                        Add Card
                    </button>
                </div>

                <!-- Cards Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Card</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stats</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="card in cards" :key="card.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <img
                                            :src="card.image_url"
                                            :alt="card.title"
                                            class="h-12 w-20 object-cover rounded"
                                        />
                                        <div>
                                            <div class="font-medium text-gray-900">{{ card.title }}</div>
                                            <div class="text-sm text-gray-500">{{ card.slug }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="[
                                            'px-2 py-1 rounded-full text-xs font-semibold',
                                            getCategoryColor(card.category)
                                        ]"
                                    >
                                        {{ getCategoryLabel(card.category) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <div>{{ card.share_count }} shares</div>
                                    <div>{{ card.view_count }} views</div>
                                </td>
                                <td class="px-6 py-4">
                                    <button
                                        @click="toggleActive(card)"
                                        :class="[
                                            'px-3 py-1 rounded-full text-xs font-semibold',
                                            card.is_active
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-gray-100 text-gray-800'
                                        ]"
                                    >
                                        {{ card.is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <button
                                            @click="editCard(card)"
                                            class="text-blue-600 hover:text-blue-800"
                                            title="Edit"
                                        >
                                            <PencilIcon class="h-5 w-5" />
                                        </button>
                                        <button
                                            @click="viewStats(card)"
                                            class="text-green-600 hover:text-green-800"
                                            title="View Stats"
                                        >
                                            <BarChartIcon class="h-5 w-5" />
                                        </button>
                                        <button
                                            @click="deleteCard(card)"
                                            class="text-red-600 hover:text-red-800"
                                            title="Delete"
                                        >
                                            <TrashIcon class="h-5 w-5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Empty State -->
                    <div v-if="cards.length === 0" class="text-center py-12">
                        <ShareIcon class="mx-auto h-12 w-12 text-gray-400" />
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No promotional cards</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by creating a new card.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <CardModal
            v-model="showCreateModal"
            :card="editingCard"
            @saved="handleSaved"
        />

        <!-- Stats Modal -->
        <StatsModal
            v-model="showStatsModal"
            :card="selectedCard"
            :statistics="cardStatistics"
        />
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import CardModal from '@/components/Admin/PromotionalCards/CardModal.vue';
import StatsModal from '@/components/Admin/PromotionalCards/StatsModal.vue';
import { PlusIcon, PencilIcon, TrashIcon, ShareIcon, BarChartIcon } from 'lucide-vue-next';
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
    is_active: boolean;
}

const props = defineProps<{
    cards: Card[];
}>();

const showCreateModal = ref(false);
const showStatsModal = ref(false);
const editingCard = ref<Card | null>(null);
const selectedCard = ref<Card | null>(null);
const cardStatistics = ref<any>(null);

const categories = [
    { value: 'general', label: 'General' },
    { value: 'opportunity', label: 'Opportunity' },
    { value: 'training', label: 'Training' },
    { value: 'success', label: 'Success Stories' },
    { value: 'announcement', label: 'Announcements' },
];

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

const editCard = (card: Card) => {
    editingCard.value = card;
    showCreateModal.value = true;
};

const toggleActive = async (card: Card) => {
    try {
        await axios.post(route('admin.promotional-cards.toggle-active', card.id));
        router.reload({ only: ['cards'] });
    } catch (error) {
        console.error('Failed to toggle card status:', error);
    }
};

const deleteCard = (card: Card) => {
    if (confirm(`Are you sure you want to delete "${card.title}"?`)) {
        router.delete(route('admin.promotional-cards.destroy', card.id));
    }
};

const viewStats = async (card: Card) => {
    try {
        const response = await axios.get(route('admin.promotional-cards.statistics', card.id));
        selectedCard.value = card;
        cardStatistics.value = response.data.statistics;
        showStatsModal.value = true;
    } catch (error) {
        console.error('Failed to load statistics:', error);
    }
};

const handleSaved = () => {
    showCreateModal.value = false;
    editingCard.value = null;
    router.reload({ only: ['cards'] });
};
</script>
