<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import LifePlusLayout from '@/layouts/LifePlusLayout.vue';
import {
    ArrowLeftIcon,
    MapPinIcon,
    CurrencyDollarIcon,
} from '@heroicons/vue/24/outline';

defineOptions({ layout: LifePlusLayout });

interface Category {
    id: string;
    name: string;
    icon: string;
}

const props = defineProps<{
    categories: Category[];
}>();

const form = useForm({
    title: '',
    description: '',
    category: '',
    payment_amount: '',
    location: '',
});

const submitGig = () => {
    form.post(route('lifeplus.gigs.store'));
};
</script>

<template>
    <div class="p-4 space-y-6">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Link 
                :href="route('lifeplus.gigs.index')"
                class="p-2 rounded-lg hover:bg-gray-100"
                aria-label="Back to gigs"
            >
                <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
            </Link>
            <h1 class="text-xl font-bold text-gray-900">Post a Gig</h1>
        </div>

        <!-- Form -->
        <form @submit.prevent="submitGig" class="space-y-5">
            <!-- Title -->
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    What do you need help with?
                </label>
                <input 
                    v-model="form.title"
                    type="text"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="e.g., Need help cleaning house"
                />
                <p v-if="form.errors.title" class="text-red-500 text-sm mt-1">{{ form.errors.title }}</p>
            </div>

            <!-- Description -->
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Description
                </label>
                <textarea 
                    v-model="form.description"
                    rows="4"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Describe the job in detail..."
                ></textarea>
                <p v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}</p>
            </div>

            <!-- Category -->
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    Category
                </label>
                <div class="grid grid-cols-3 gap-2">
                    <button 
                        v-for="cat in categories" 
                        :key="cat.id"
                        type="button"
                        @click="form.category = cat.id"
                        :class="[
                            'flex flex-col items-center gap-1 p-3 rounded-xl border-2 transition-colors',
                            form.category === cat.id 
                                ? 'border-blue-500 bg-blue-50' 
                                : 'border-gray-200 hover:border-gray-300'
                        ]"
                    >
                        <span class="text-2xl">{{ cat.icon }}</span>
                        <span class="text-xs font-medium text-gray-700">{{ cat.name }}</span>
                    </button>
                </div>
            </div>

            <!-- Payment & Location -->
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Payment Amount (K)
                    </label>
                    <div class="relative">
                        <CurrencyDollarIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
                        <input 
                            v-model="form.payment_amount"
                            type="number"
                            min="0"
                            class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="0"
                        />
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Leave empty if negotiable</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Location
                    </label>
                    <div class="relative">
                        <MapPinIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
                        <input 
                            v-model="form.location"
                            type="text"
                            class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="e.g., Chilenje, Lusaka"
                        />
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <button 
                type="submit"
                :disabled="form.processing || !form.title"
                class="w-full py-3 bg-blue-500 text-white rounded-xl font-medium hover:bg-blue-600 disabled:opacity-50 transition-colors"
            >
                {{ form.processing ? 'Posting...' : 'Post Gig' }}
            </button>
        </form>
    </div>
</template>
