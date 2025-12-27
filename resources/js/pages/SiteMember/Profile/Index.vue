<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import SiteMemberLayout from '@/layouts/SiteMemberLayout.vue';
import { ref, computed } from 'vue';
import { UserIcon, KeyIcon, CheckCircleIcon } from '@heroicons/vue/24/outline';

interface Props {
    site: { id: number; name: string; subdomain: string; theme: { primaryColor?: string } | null };
    settings: { navigation?: { logo?: string } } | null;
    user: {
        id: number;
        name: string;
        email: string;
        phone: string | null;
        avatar: string | null;
        role: { name: string; slug: string; level: number; type: string; color?: string } | null;
        permissions: string[];
        created_at: string;
    };
}

const props = defineProps<Props>();
const primaryColor = computed(() => props.site.theme?.primaryColor || '#2563eb');
const activeTab = ref<'profile' | 'password'>('profile');

const profileForm = useForm({
    name: props.user.name,
    phone: props.user.phone || '',
});

const passwordForm = useForm({
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
});

const submitProfile = () => {
    profileForm.put(`/sites/${props.site.subdomain}/dashboard/profile`);
};

const submitPassword = () => {
    passwordForm.put(`/sites/${props.site.subdomain}/dashboard/profile`, {
        onSuccess: () => passwordForm.reset(),
    });
};

const userInitials = computed(() => props.user.name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2));
const memberSince = computed(() => new Date(props.user.created_at).toLocaleDateString('en-ZM', { year: 'numeric', month: 'long' }));
</script>

<template>
    <SiteMemberLayout :site="site" :settings="settings" :user="user" title="Profile">
        <Head :title="`Profile - ${site.name}`" />

        <div class="max-w-3xl mx-auto">
            <!-- Profile Header -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
                <div class="flex items-center gap-4">
                    <div class="w-20 h-20 rounded-full flex items-center justify-center text-white text-2xl font-bold" :style="{ backgroundColor: primaryColor }">
                        {{ userInitials }}
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">{{ user.name }}</h1>
                        <p class="text-gray-500">{{ user.email }}</p>
                        <div class="flex items-center gap-2 mt-2">
                            <span v-if="user.role" class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-700">
                                {{ user.role.name }}
                            </span>
                            <span class="text-xs text-gray-400">Member since {{ memberSince }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="border-b border-gray-200">
                    <nav class="flex">
                        <button
                            @click="activeTab = 'profile'"
                            :class="['flex items-center gap-2 px-6 py-4 text-sm font-medium border-b-2 transition', activeTab === 'profile' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700']"
                        >
                            <UserIcon class="w-5 h-5" aria-hidden="true" />
                            Profile Info
                        </button>
                        <button
                            @click="activeTab = 'password'"
                            :class="['flex items-center gap-2 px-6 py-4 text-sm font-medium border-b-2 transition', activeTab === 'password' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700']"
                        >
                            <KeyIcon class="w-5 h-5" aria-hidden="true" />
                            Change Password
                        </button>
                    </nav>
                </div>

                <!-- Profile Tab -->
                <form v-show="activeTab === 'profile'" @submit.prevent="submitProfile" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input v-model="profileForm.name" type="text" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
                        <p v-if="profileForm.errors.name" class="mt-1 text-sm text-red-600">{{ profileForm.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input :value="user.email" type="email" disabled class="w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-gray-500" />
                        <p class="mt-1 text-xs text-gray-400">Email cannot be changed</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                        <input v-model="profileForm.phone" type="tel" placeholder="+260 97X XXX XXX" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div class="pt-4">
                        <button type="submit" :disabled="profileForm.processing" class="px-6 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50">
                            {{ profileForm.processing ? 'Saving...' : 'Save Changes' }}
                        </button>
                    </div>
                </form>

                <!-- Password Tab -->
                <form v-show="activeTab === 'password'" @submit.prevent="submitPassword" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                        <input v-model="passwordForm.current_password" type="password" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
                        <p v-if="passwordForm.errors.current_password" class="mt-1 text-sm text-red-600">{{ passwordForm.errors.current_password }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                        <input v-model="passwordForm.new_password" type="password" required minlength="8" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
                        <p v-if="passwordForm.errors.new_password" class="mt-1 text-sm text-red-600">{{ passwordForm.errors.new_password }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                        <input v-model="passwordForm.new_password_confirmation" type="password" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div class="pt-4">
                        <button type="submit" :disabled="passwordForm.processing" class="px-6 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50">
                            {{ passwordForm.processing ? 'Updating...' : 'Update Password' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </SiteMemberLayout>
</template>
