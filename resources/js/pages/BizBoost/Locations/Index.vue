<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import {
    MapPinIcon,
    PlusIcon,
    PencilIcon,
    TrashIcon,
    StarIcon,
    PhoneIcon,
    BuildingStorefrontIcon,
} from '@heroicons/vue/24/outline';
import { StarIcon as StarIconSolid } from '@heroicons/vue/24/solid';

interface Location {
    id: number;
    name: string;
    address: string | null;
    city: string | null;
    phone: string | null;
    whatsapp: string | null;
    is_primary: boolean;
    is_active: boolean;
}

interface Props {
    locations: Location[];
    locationLimit: number;
}

const props = defineProps<Props>();

const deleteLocation = (id: number) => {
    if (confirm('Are you sure you want to delete this location?')) {
        router.delete(`/bizboost/locations/${id}`);
    }
};

const setPrimary = (id: number) => {
    router.post(`/bizboost/locations/${id}/set-primary`);
};
</script>

<template>
    <Head title="Locations - BizBoost" />
    <BizBoostLayout title="Locations">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Business Locations</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Manage multiple locations for your business ({{ locations.length }}/{{ locationLimit }})
                    </p>
                </div>
                <Link
                    v-if="locations.length < locationLimit"
                    href="/bizboost/locations/create"
                    class="inline-flex items-center gap-2 rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-700"
                >
                    <PlusIcon class="h-4 w-4" aria-hidden="true" />
                    Add Location
                </Link>
                <Link
                    v-else
                    href="/bizboost/upgrade"
                    class="inline-flex items-center gap-2 rounded-lg bg-gray-100 dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600"
                >
                    Upgrade for more locations
                </Link>
            </div>

            <!-- Locations Grid -->
            <div v-if="locations.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="location in locations"
                    :key="location.id"
                    :class="[
                        'relative rounded-xl bg-white dark:bg-gray-800 p-5 shadow-sm ring-1',
                        location.is_primary ? 'ring-violet-300 dark:ring-violet-500' : 'ring-gray-200 dark:ring-gray-700',
                        !location.is_active && 'opacity-60'
                    ]"
                >
                    <!-- Primary Badge -->
                    <div v-if="location.is_primary" class="absolute -top-2 -right-2">
                        <span class="inline-flex items-center gap-1 rounded-full bg-violet-100 dark:bg-violet-900/30 px-2 py-1 text-xs font-medium text-violet-700 dark:text-violet-400">
                            <StarIconSolid class="h-3 w-3" aria-hidden="true" />
                            Primary
                        </span>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="rounded-lg bg-violet-100 dark:bg-violet-900/30 p-2">
                            <BuildingStorefrontIcon class="h-5 w-5 text-violet-600 dark:text-violet-400" aria-hidden="true" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ location.name }}</h3>
                            <p v-if="location.address" class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                <MapPinIcon class="h-4 w-4 inline mr-1" aria-hidden="true" />
                                {{ location.address }}{{ location.city ? `, ${location.city}` : '' }}
                            </p>
                            <p v-if="location.phone" class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                <PhoneIcon class="h-4 w-4 inline mr-1" aria-hidden="true" />
                                {{ location.phone }}
                            </p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2 mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <Link
                            :href="`/bizboost/locations/${location.id}/edit`"
                            class="inline-flex items-center gap-1 text-sm text-gray-600 dark:text-gray-400 hover:text-violet-600 dark:hover:text-violet-400"
                        >
                            <PencilIcon class="h-4 w-4" aria-hidden="true" />
                            Edit
                        </Link>
                        <button
                            v-if="!location.is_primary"
                            @click="setPrimary(location.id)"
                            class="inline-flex items-center gap-1 text-sm text-gray-600 dark:text-gray-400 hover:text-violet-600 dark:hover:text-violet-400"
                        >
                            <StarIcon class="h-4 w-4" aria-hidden="true" />
                            Set Primary
                        </button>
                        <button
                            v-if="!location.is_primary"
                            @click="deleteLocation(location.id)"
                            class="inline-flex items-center gap-1 text-sm text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 ml-auto"
                        >
                            <TrashIcon class="h-4 w-4" aria-hidden="true" />
                            Delete
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12 bg-white dark:bg-gray-800 rounded-xl ring-1 ring-gray-200 dark:ring-gray-700">
                <MapPinIcon class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" aria-hidden="true" />
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No locations yet</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Add your first business location to get started.</p>
                <Link
                    href="/bizboost/locations/create"
                    class="mt-4 inline-flex items-center gap-2 rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-700"
                >
                    <PlusIcon class="h-4 w-4" aria-hidden="true" />
                    Add Location
                </Link>
            </div>
        </div>
    </BizBoostLayout>
</template>
