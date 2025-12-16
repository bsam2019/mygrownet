<script setup lang="ts">
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import LifePlusLayout from '@/layouts/LifePlusLayout.vue';
import {
    ArrowLeftIcon,
    UserCircleIcon,
    MapPinIcon,
} from '@heroicons/vue/24/outline';

defineOptions({ layout: LifePlusLayout });

interface Profile {
    id: number;
    user_id: number;
    name: string;
    email: string;
    location: string | null;
    bio: string | null;
    skills: string[];
    avatar_url: string | null;
}

const props = defineProps<{
    profile: Profile;
}>();

const form = useForm({
    location: props.profile.location || '',
    bio: props.profile.bio || '',
});

const submitProfile = () => {
    form.put(route('lifeplus.profile.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="p-4 space-y-6">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Link 
                :href="route('lifeplus.profile.index')"
                class="p-2 rounded-lg hover:bg-gray-100"
            >
                <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
            </Link>
            <h1 class="text-xl font-bold text-gray-900">Settings</h1>
        </div>

        <!-- Profile Settings -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <h2 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <UserCircleIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                Profile Information
            </h2>
            
            <form @submit.prevent="submitProfile" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input 
                        :value="profile.name"
                        type="text"
                        disabled
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-500"
                    />
                    <p class="text-xs text-gray-400 mt-1">Name cannot be changed here</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input 
                        :value="profile.email"
                        type="email"
                        disabled
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-500"
                    />
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                    <div class="relative">
                        <MapPinIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
                        <input 
                            v-model="form.location"
                            type="text"
                            class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            placeholder="e.g., Lusaka, Zambia"
                        />
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bio</label>
                    <textarea 
                        v-model="form.bio"
                        rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                        placeholder="Tell us about yourself..."
                    ></textarea>
                </div>
                
                <button 
                    type="submit"
                    :disabled="form.processing"
                    class="w-full py-3 bg-emerald-500 text-white rounded-xl font-medium hover:bg-emerald-600 disabled:opacity-50 transition-colors"
                >
                    {{ form.processing ? 'Saving...' : 'Save Changes' }}
                </button>
            </form>
        </div>

        <!-- Skills Link -->
        <Link 
            :href="route('lifeplus.profile.skills')"
            class="block bg-white rounded-2xl p-4 shadow-sm border border-gray-100 hover:bg-gray-50 transition-colors"
        >
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-gray-900">🎯 Manage Skills</h3>
                    <p class="text-sm text-gray-500 mt-0.5">
                        {{ profile.skills?.length || 0 }} skills added
                    </p>
                </div>
                <span class="text-gray-400">→</span>
            </div>
        </Link>

        <!-- App Info -->
        <div class="bg-gray-50 rounded-2xl p-4 text-center">
            <p class="text-sm text-gray-500">MyGrowNet Life+</p>
            <p class="text-xs text-gray-400 mt-1">Version 1.0.0</p>
        </div>
    </div>
</template>
