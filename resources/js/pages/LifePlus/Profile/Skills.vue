<script setup lang="ts">
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import LifePlusLayout from '@/layouts/LifePlusLayout.vue';
import {
    ArrowLeftIcon,
    PlusIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';

defineOptions({ layout: LifePlusLayout });

interface Profile {
    id: number;
    skills: string[];
}

const props = defineProps<{
    profile: Profile;
}>();

const newSkill = ref('');
const skills = ref<string[]>([...props.profile.skills || []]);

const form = useForm({
    skills: skills.value,
});

const addSkill = () => {
    const skill = newSkill.value.trim();
    if (skill && !skills.value.includes(skill)) {
        skills.value.push(skill);
        newSkill.value = '';
    }
};

const removeSkill = (index: number) => {
    skills.value.splice(index, 1);
};

const saveSkills = () => {
    form.skills = skills.value;
    form.put(route('lifeplus.profile.skills.update'), {
        preserveScroll: true,
    });
};

const suggestedSkills = [
    'Cleaning', 'Cooking', 'Babysitting', 'Tutoring', 'Driving',
    'Gardening', 'Painting', 'Plumbing', 'Electrical', 'Carpentry',
    'Tailoring', 'Hair Styling', 'Photography', 'Writing', 'Translation',
    'Computer Repair', 'Phone Repair', 'Farming', 'Catering', 'Event Planning',
];

const addSuggestedSkill = (skill: string) => {
    if (!skills.value.includes(skill)) {
        skills.value.push(skill);
    }
};
</script>

<template>
    <div class="p-4 space-y-6">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Link 
                :href="route('lifeplus.profile.settings')"
                class="p-2 rounded-lg hover:bg-gray-100"
            >
                <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
            </Link>
            <h1 class="text-xl font-bold text-gray-900">My Skills</h1>
        </div>

        <!-- Current Skills -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
            <h2 class="font-semibold text-gray-900 mb-3">Your Skills</h2>
            
            <div v-if="skills.length === 0" class="text-center py-6 text-gray-500">
                No skills added yet
            </div>
            
            <div v-else class="flex flex-wrap gap-2">
                <span 
                    v-for="(skill, index) in skills" 
                    :key="skill"
                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-50 text-emerald-700 rounded-full text-sm font-medium"
                >
                    {{ skill }}
                    <button 
                        @click="removeSkill(index)"
                        class="p-0.5 hover:bg-emerald-200 rounded-full"
                        aria-label="Remove skill"
                    >
                        <XMarkIcon class="h-3.5 w-3.5" aria-hidden="true" />
                    </button>
                </span>
            </div>
            
            <!-- Add New Skill -->
            <div class="mt-4 flex gap-2">
                <input 
                    v-model="newSkill"
                    @keyup.enter="addSkill"
                    type="text"
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                    placeholder="Add a skill..."
                />
                <button 
                    @click="addSkill"
                    class="px-4 py-2 bg-emerald-500 text-white rounded-xl font-medium hover:bg-emerald-600 transition-colors"
                >
                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                </button>
            </div>
        </div>

        <!-- Suggested Skills -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
            <h2 class="font-semibold text-gray-900 mb-3">Suggested Skills</h2>
            <p class="text-sm text-gray-500 mb-3">Tap to add</p>
            
            <div class="flex flex-wrap gap-2">
                <button 
                    v-for="skill in suggestedSkills.filter(s => !skills.includes(s))" 
                    :key="skill"
                    @click="addSuggestedSkill(skill)"
                    class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded-full text-sm font-medium hover:bg-gray-200 transition-colors"
                >
                    + {{ skill }}
                </button>
            </div>
        </div>

        <!-- Save Button -->
        <button 
            @click="saveSkills"
            :disabled="form.processing"
            class="w-full py-3 bg-emerald-500 text-white rounded-xl font-medium hover:bg-emerald-600 disabled:opacity-50 transition-colors"
        >
            {{ form.processing ? 'Saving...' : 'Save Skills' }}
        </button>
    </div>
</template>
