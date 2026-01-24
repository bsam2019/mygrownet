<template>
    <UbumiLayout :title="family ? `${family.name} - Members` : 'All People'">
        <div class="py-6 md:py-12 px-4 md:px-0">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        v-if="family"
                        :href="route('ubumi.families.show', family.slug)"
                        class="inline-flex items-center text-gray-600 hover:text-gray-900 mb-4"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Family Tree
                    </Link>
                    
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">
                                {{ family ? family.name : 'All People' }}
                            </h1>
                            <p class="mt-2 text-gray-600">
                                {{ family ? 'Family Members' : 'All family members across all families' }}
                            </p>
                        </div>
                        <Link
                            v-if="family"
                            :href="route('ubumi.families.persons.create', family.slug)"
                            class="hidden md:inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-500 to-indigo-600 border border-transparent rounded-xl font-semibold text-white hover:shadow-lg transition-all"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Member
                        </Link>
                    </div>
                </div>

                <!-- Members Grid -->
                <div v-if="persons.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
                    <div
                        v-for="person in persons"
                        :key="person.id"
                        class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-200"
                    >
                        <div class="p-4 md:p-6">
                            <!-- Photo -->
                            <div class="flex items-center mb-4">
                                <div class="flex-shrink-0">
                                    <div 
                                        v-if="person.photo_url" 
                                        class="h-16 w-16 rounded-full overflow-hidden ring-4 ring-white shadow-lg"
                                    >
                                        <img :src="person.photo_url" :alt="person.name" class="h-full w-full object-cover" />
                                    </div>
                                    <div 
                                        v-else 
                                        class="h-16 w-16 rounded-full flex items-center justify-center ring-4 ring-white shadow-lg text-white text-xl font-bold"
                                        :class="getGradientClass(person.gender)"
                                    >
                                        {{ getInitials(person.name) }}
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">{{ person.name }}</h3>
                                    <p v-if="person.age" class="text-sm text-gray-500">{{ person.age }} years old</p>
                                    <p v-if="person.is_deceased" class="text-sm text-gray-500 italic">Deceased</p>
                                    <p v-if="!family && person.family_name" class="text-xs text-purple-600 font-medium mt-1">
                                        {{ person.family_name }}
                                    </p>
                                </div>
                            </div>

                            <!-- Details -->
                            <div class="space-y-2 mb-4">
                                <div v-if="person.gender" class="flex items-center text-sm text-gray-600">
                                    <div 
                                        class="w-8 h-8 rounded-full flex items-center justify-center mr-2"
                                        :class="getGenderBadgeClass(person.gender)"
                                    >
                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                            <path v-if="person.gender === 'male'" d="M9 9l10-10m0 0v7m0-7h-7m-2 17a5 5 0 110-10 5 5 0 010 10z"/>
                                            <path v-else d="M12 2a5 5 0 100 10 5 5 0 000-10zm0 12c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                        </svg>
                                    </div>
                                    {{ formatGender(person.gender) }}
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                                <Link
                                    :href="route('ubumi.families.persons.show', [person.family_slug || family?.slug, person.slug])"
                                    class="text-purple-600 hover:text-purple-800 text-sm font-semibold"
                                >
                                    View Profile
                                </Link>
                                <Link
                                    v-if="family"
                                    :href="route('ubumi.families.persons.edit', [family.slug, person.slug])"
                                    class="text-gray-600 hover:text-gray-800 text-sm font-semibold"
                                >
                                    Edit
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white rounded-2xl shadow-lg p-12 text-center">
                    <div class="w-20 h-20 mx-auto bg-gradient-to-br from-purple-400 to-indigo-500 rounded-full flex items-center justify-center mb-4">
                        <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">No family members yet</h3>
                    <p class="mt-2 text-gray-600">Start building your family tree by adding members.</p>
                    <div v-if="family" class="mt-6">
                        <Link
                            :href="route('ubumi.families.persons.create', family.slug)"
                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-500 to-indigo-600 border border-transparent rounded-xl font-semibold text-white hover:shadow-lg transition-all"
                        >
                            Add First Member
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </UbumiLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import UbumiLayout from '@/layouts/UbumiLayout.vue';

interface Family {
    id: string;
    slug: string;
    name: string;
}

interface Person {
    id: string;
    slug: string;
    family_id: number;
    family_slug?: string;
    name: string;
    photo_url: string | null;
    age: number | null;
    gender: string | null;
    is_deceased: boolean;
    family_id?: string;
    family_name?: string;
}

defineProps<{
    family: Family | null;
    persons: Person[];
}>();

const formatGender = (gender: string): string => {
    const genderMap: Record<string, string> = {
        male: 'Male',
        female: 'Female',
        other: 'Other',
        prefer_not_to_say: 'Prefer not to say',
    };
    return genderMap[gender] || gender;
};

const getInitials = (name: string): string => {
    return name
        .split(' ')
        .map(part => part[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

const getGradientClass = (gender: string | null): string => {
    if (gender === 'male') return 'bg-gradient-to-br from-blue-400 to-blue-600';
    if (gender === 'female') return 'bg-gradient-to-br from-pink-400 to-pink-600';
    return 'bg-gradient-to-br from-purple-400 to-indigo-500';
};

const getGenderBadgeClass = (gender: string | null): string => {
    if (gender === 'male') return 'bg-blue-500';
    if (gender === 'female') return 'bg-pink-500';
    return 'bg-purple-500';
};
</script>
