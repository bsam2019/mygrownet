<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import GrowBizLayout from '@/Layouts/GrowBizLayout.vue';
import {
    ArrowLeftIcon,
    UserCircleIcon,
    EnvelopeIcon,
    PhoneIcon,
    CheckIcon,
} from '@heroicons/vue/24/outline';

interface Props {
    user: {
        id: number;
        name: string;
        email: string;
        phone: string | null;
    };
    userRole: string;
}

const props = defineProps<Props>();

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    phone: props.user.phone || '',
});

const submit = () => {
    form.put(route('growbiz.settings.profile.update'), {
        preserveScroll: true,
    });
};

const getInitials = (name: string) => {
    if (!name) return '?';
    const parts = name.split(' ');
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
};
</script>

<template>
    <GrowBizLayout>
        <Head title="My Profile - GrowBiz" />
        
        <div class="px-4 py-4 pb-6">
            <!-- Page Header -->
            <div class="flex items-center gap-3 mb-6">
                <Link 
                    :href="route('growbiz.settings.index')" 
                    class="p-2 -ml-2 rounded-xl hover:bg-gray-100 active:bg-gray-200 transition-colors"
                    aria-label="Back to settings"
                >
                    <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                </Link>
                <h1 class="text-xl font-bold text-gray-900">My Profile</h1>
            </div>

            <!-- Profile Avatar -->
            <div class="flex justify-center mb-6">
                <div class="relative">
                    <div class="h-24 w-24 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center shadow-lg shadow-emerald-200">
                        <span class="text-3xl font-bold text-white">{{ getInitials(form.name) }}</span>
                    </div>
                    <button 
                        class="absolute bottom-0 right-0 p-2 bg-white rounded-full shadow-md border border-gray-200 hover:bg-gray-50"
                        aria-label="Change profile photo"
                    >
                        <UserCircleIcon class="h-4 w-4 text-gray-600" aria-hidden="true" />
                    </button>
                </div>
            </div>

            <!-- Profile Form -->
            <form @submit.prevent="submit" class="space-y-4">
                <div class="bg-white rounded-2xl shadow-sm p-4 space-y-4">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            Full Name
                        </label>
                        <div class="relative">
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-colors"
                                placeholder="Enter your name"
                            />
                            <UserCircleIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
                        </div>
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            Email Address
                        </label>
                        <div class="relative">
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-colors"
                                placeholder="Enter your email"
                            />
                            <EnvelopeIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
                        </div>
                        <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                            Phone Number
                        </label>
                        <div class="relative">
                            <input
                                id="phone"
                                v-model="form.phone"
                                type="tel"
                                class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-colors"
                                placeholder="Enter your phone number"
                            />
                            <PhoneIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
                        </div>
                        <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
                    </div>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full flex items-center justify-center gap-2 py-3.5 px-4 bg-emerald-600 text-white font-semibold rounded-xl hover:bg-emerald-700 active:bg-emerald-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    <CheckIcon v-if="!form.processing" class="h-5 w-5" aria-hidden="true" />
                    <span v-if="form.processing">Saving...</span>
                    <span v-else>Save Changes</span>
                </button>
            </form>
        </div>
    </GrowBizLayout>
</template>
