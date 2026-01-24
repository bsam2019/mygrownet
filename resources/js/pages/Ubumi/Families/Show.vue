<template>
    <UbumiLayout :title="`${family.name} - Ubumi`">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        :href="route('ubumi.families.index')"
                        class="inline-flex items-center text-gray-600 hover:text-gray-900 mb-4"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Families
                    </Link>
                    
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">{{ family.name }}</h1>
                            <p class="mt-2 text-gray-600">Family Tree</p>
                        </div>
                        <div class="flex gap-3">
                            <Link
                                :href="route('ubumi.families.show', family.slug)"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                </svg>
                                View List
                            </Link>
                            <Link
                                :href="route('ubumi.families.persons.create', family.slug)"
                                class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-white hover:bg-emerald-700"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Add Member
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Family Tree Visualization -->
                <FamilyTreeVisualization
                    :family-id="family.id"
                    :family-slug="family.slug"
                    :persons="persons"
                    :relationships="relationships"
                    @select-person="handlePersonSelect"
                />
            </div>
        </div>
    </UbumiLayout>
</template>

<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import UbumiLayout from '@/layouts/UbumiLayout.vue';
import FamilyTreeVisualization from '@/components/Ubumi/FamilyTreeVisualization.vue';

interface Family {
    id: string;
    slug: string;
    name: string;
    admin_user_id: number;
    created_at: string;
}

interface Person {
    id: string;
    slug: string;
    name: string;
    photo_url: string | null;
    age: number | null;
    gender: 'male' | 'female' | null;
    is_deceased: boolean;
}

interface Relationship {
    id: number;
    person_id: string;
    related_person_id: string;
    relationship_type: string;
}

const props = defineProps<{
    family: Family;
    persons: Person[];
    relationships: Relationship[];
}>();

const handlePersonSelect = (personId: string) => {
    const person = props.persons.find(p => p.id === personId);
    if (person) {
        router.visit(route('ubumi.families.persons.show', {
            family: props.family.slug,
            person: person.slug,
        }));
    }
};
</script>
