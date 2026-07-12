<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    MagnifyingGlassIcon,
    FunnelIcon,
    PhoneIcon,
    EnvelopeIcon,
    GlobeAltIcon,
    MapPinIcon,
    StarIcon,
    CheckBadgeIcon
} from '@heroicons/vue/24/outline';
import { StarIcon as StarSolidIcon } from '@heroicons/vue/24/solid';
import type { Provider, ProviderCategory } from '@/types/growstart';

interface Props {
    providers: Provider[];
    categories: { value: ProviderCategory; label: string }[];
    provinces: string[];
}

const props = defineProps<Props>();

const searchQuery = ref('');
const selectedCategory = ref<ProviderCategory | 'all'>('all');
const selectedProvince = ref<string>('all');

const filteredProviders = computed(() => {
    return props.providers.filter(provider => {
        const matchesSearch = provider.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            provider.description?.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesCategory = selectedCategory.value === 'all' || provider.category === selectedCategory.value;
        const matchesProvince = selectedProvince.value === 'all' || provider.province === selectedProvince.value;
        return matchesSearch && matchesCategory && matchesProvince;
    });
});

const getCategoryIcon = (category: string) => {
    const icons: Record<string, string> = {
        accountant: '📊',
        designer: '🎨',
        pacra_agent: '📋',
        marketing: '📢',
        legal: '⚖️',
        supplier: '📦',
        consultant: '💼',
        other: '🤝'
    };
    return icons[category] || '🤝';
};

const renderStars = (rating: number) => {
    return Array.from({ length: 5 }, (_, i) => i < Math.round(rating));
};
</script>

<template>
    <Head title="GrowStart - Service Providers" />
    
    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-gray-900">Service Provider Directory</h1>
                    <p class="text-gray-600">Find trusted local professionals to help your business</p>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <!-- Search -->
                        <div class="flex-1 relative">
                            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search providers..."
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                            />
                        </div>

                        <!-- Category Filter -->
                        <select
                            v-model="selectedCategory"
                            class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="all">All Categories</option>
                            <option v-for="cat in categories" :key="cat.value" :value="cat.value">
                                {{ cat.label }}
                            </option>
                        </select>

                        <!-- Province Filter -->
                        <select
                            v-model="selectedProvince"
                            class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="all">All Provinces</option>
                            <option v-for="province in provinces" :key="province" :value="province">
                                {{ province }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Providers Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div
                        v-for="provider in filteredProviders"
                        :key="provider.id"
                        class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:border-blue-200 transition"
                    >
                        <div class="p-5">
                            <div class="flex items-start justify-between">
                                <div class="flex items-start gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center text-2xl">
                                        {{ getCategoryIcon(provider.category) }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h3 class="font-semibold text-gray-900">{{ provider.name }}</h3>
                                            <CheckBadgeIcon 
                                                v-if="provider.is_verified"
                                                class="h-5 w-5 text-blue-500" 
                                                aria-hidden="true" 
                                            />
                                        </div>
                                        <span class="text-sm text-gray-500 capitalize">
                                            {{ provider.category.replace('_', ' ') }}
                                        </span>
                                    </div>
                                </div>
                                <span 
                                    v-if="provider.is_featured"
                                    class="px-2 py-1 text-xs font-medium bg-amber-100 text-amber-700 rounded-full"
                                >
                                    Featured
                                </span>
                            </div>

                            <p class="mt-3 text-sm text-gray-600 line-clamp-2">
                                {{ provider.description }}
                            </p>

                            <!-- Rating -->
                            <div class="mt-3 flex items-center gap-1">
                                <template v-for="(filled, i) in renderStars(provider.rating)" :key="i">
                                    <StarSolidIcon 
                                        v-if="filled"
                                        class="h-4 w-4 text-amber-400" 
                                        aria-hidden="true" 
                                    />
                                    <StarIcon 
                                        v-else
                                        class="h-4 w-4 text-gray-300" 
                                        aria-hidden="true" 
                                    />
                                </template>
                                <span class="ml-1 text-sm text-gray-500">
                                    ({{ provider.review_count }} reviews)
                                </span>
                            </div>

                            <!-- Location -->
                            <div class="mt-3 flex items-center gap-1 text-sm text-gray-500">
                                <MapPinIcon class="h-4 w-4" aria-hidden="true" />
                                {{ provider.city }}, {{ provider.province }}
                            </div>
                        </div>

                        <!-- Contact Actions -->
                        <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex items-center gap-3">
                            <a
                                v-if="provider.contact_phone"
                                :href="`tel:${provider.contact_phone}`"
                                class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition"
                            >
                                <PhoneIcon class="h-4 w-4" aria-hidden="true" />
                                Call
                            </a>
                            <a
                                v-if="provider.contact_email"
                                :href="`mailto:${provider.contact_email}`"
                                class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition"
                            >
                                <EnvelopeIcon class="h-4 w-4" aria-hidden="true" />
                                Email
                            </a>
                            <a
                                v-if="provider.website"
                                :href="provider.website"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition"
                            >
                                <GlobeAltIcon class="h-4 w-4" aria-hidden="true" />
                                Website
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="filteredProviders.length === 0" class="text-center py-12">
                    <span class="text-5xl">🤝</span>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">No providers found</h3>
                    <p class="mt-2 text-gray-500">Try adjusting your search or filters</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
