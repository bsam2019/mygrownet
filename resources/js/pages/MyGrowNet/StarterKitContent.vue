<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import MemberLayout from '@/layouts/MemberLayout.vue';
import { BookOpenIcon, VideoIcon, FileTextIcon, ToolIcon, DownloadIcon, EyeIcon, LockIcon, CrownIcon } from 'lucide-vue-next';

interface ContentItem {
    id: number;
    title: string;
    description: string | null;
    category: string;
    category_label: string;
    tier_restriction: 'all' | 'premium';
    estimated_value: number;
    file_type: string | null;
    file_size: number | null;
    is_downloadable: boolean;
    download_count: number;
    thumbnail: string | null;
    last_accessed: string | null;
    has_file: boolean;
}

interface Props {
    contentItems: Record<string, ContentItem[]>;
    userTier: 'basic' | 'premium' | null;
    totalValue: number;
}

const props = defineProps<Props>();

const getCategoryIcon = (category: string) => {
    const icons: Record<string, any> = {
        training: BookOpenIcon,
        ebook: FileTextIcon,
        video: VideoIcon,
        tool: ToolIcon,
        library: BookOpenIcon,
    };
    return icons[category] || FileTextIcon;
};

const formatFileSize = (bytes: number | null): string => {
    if (!bytes) return 'N/A';
    const mb = bytes / (1024 * 1024);
    return mb < 1 ? `${(bytes / 1024).toFixed(0)} KB` : `${mb.toFixed(1)} MB`;
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 0,
    }).format(amount);
};

const isPremiumLocked = (item: ContentItem): boolean => {
    return item.tier_restriction === 'premium' && props.userTier !== 'premium';
};
</script>

<template>
    <Head title="My Content Library" />

    <MemberLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">My Content Library</h1>
                            <p class="mt-1 text-sm text-gray-600">
                                Access your digital products and resources
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600">Total Value</p>
                            <p class="text-2xl font-bold text-blue-600">{{ formatCurrency(totalValue) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Premium Upgrade Banner (for Basic tier) -->
                <div v-if="userTier === 'basic'" class="mb-6 bg-gradient-to-r from-purple-600 to-indigo-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-start justify-between flex-wrap gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <CrownIcon class="h-6 w-6 text-yellow-300" />
                                <h3 class="text-xl font-bold">Unlock Premium Content</h3>
                            </div>
                            <p class="text-purple-100 mb-3">Upgrade to Premium and get access to exclusive e-books, videos, and tools!</p>
                            <ul class="space-y-1 text-sm text-purple-100">
                                <li>✓ 3 exclusive advanced e-books</li>
                                <li>✓ 10 premium video trainings</li>
                                <li>✓ Business plan generator</li>
                                <li>✓ ROI calculator</li>
                            </ul>
                        </div>
                        <div class="flex-shrink-0">
                            <Link
                                :href="route('mygrownet.starter-kit.upgrade')"
                                class="inline-flex items-center px-6 py-3 bg-white text-purple-600 font-bold rounded-lg hover:bg-purple-50 transition-colors shadow-lg"
                            >
                                <CrownIcon class="w-5 h-5 mr-2" />
                                Upgrade to Premium
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Content Categories -->
                <div class="space-y-8">
                    <div v-for="(items, category) in contentItems" :key="category" class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <component :is="getCategoryIcon(category)" class="h-6 w-6 text-blue-600" />
                            {{ items[0]?.category_label || category }}s
                            <span class="text-sm font-normal text-gray-500">({{ items.length }})</span>
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div
                                v-for="item in items"
                                :key="item.id"
                                class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors relative"
                                :class="{ 'opacity-60': isPremiumLocked(item) }"
                            >
                                <!-- Premium Badge -->
                                <div v-if="item.tier_restriction === 'premium'" class="absolute top-2 right-2">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">
                                        <CrownIcon class="w-3 h-3 mr-1" />
                                        Premium
                                    </span>
                                </div>

                                <!-- Thumbnail -->
                                <div v-if="item.thumbnail" class="mb-3">
                                    <img :src="item.thumbnail" :alt="item.title" class="w-full h-32 object-cover rounded" />
                                </div>

                                <!-- Content Info -->
                                <h3 class="font-semibold text-gray-900 mb-2">{{ item.title }}</h3>
                                <p v-if="item.description" class="text-sm text-gray-600 mb-3 line-clamp-2">
                                    {{ item.description }}
                                </p>

                                <!-- Meta Info -->
                                <div class="flex items-center justify-between text-xs text-gray-500 mb-3">
                                    <span v-if="item.file_type">{{ item.file_type.toUpperCase() }}</span>
                                    <span v-if="item.file_size">{{ formatFileSize(item.file_size) }}</span>
                                    <span>{{ item.download_count }} downloads</span>
                                </div>

                                <!-- Actions -->
                                <div v-if="isPremiumLocked(item)" class="flex gap-2">
                                    <Link
                                        :href="route('mygrownet.starter-kit.upgrade')"
                                        class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-sm"
                                    >
                                        <LockIcon class="w-4 h-4 mr-2" />
                                        Unlock
                                    </Link>
                                </div>
                                <div v-else-if="item.has_file" class="flex gap-2">
                                    <Link
                                        :href="route('mygrownet.content.show', item.id)"
                                        class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm"
                                    >
                                        <EyeIcon class="w-4 h-4 mr-2" />
                                        View
                                    </Link>
                                    <Link
                                        v-if="item.is_downloadable"
                                        :href="route('mygrownet.content.download', item.id)"
                                        class="inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm"
                                    >
                                        <DownloadIcon class="w-4 h-4" />
                                    </Link>
                                </div>
                                <div v-else class="text-center text-sm text-gray-500 py-2">
                                    Coming Soon
                                </div>

                                <!-- Last Accessed -->
                                <p v-if="item.last_accessed" class="text-xs text-gray-400 mt-2">
                                    Last accessed: {{ new Date(item.last_accessed).toLocaleDateString() }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="Object.keys(contentItems).length === 0" class="bg-white rounded-lg shadow p-12 text-center">
                    <BookOpenIcon class="h-16 w-16 text-gray-400 mx-auto mb-4" />
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No Content Available</h3>
                    <p class="text-gray-600 mb-4">Content will be added soon. Check back later!</p>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>
