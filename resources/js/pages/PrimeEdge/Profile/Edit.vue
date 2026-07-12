<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import PrimeEdgeAppLayout from '@/layouts/PrimeEdgeAppLayout.vue';

const props = defineProps<{
    client: {
        id: string;
        name: string;
        email: string;
        phone: string;
        company: string;
    };
}>();

const form = useForm({
    name: props.client.name,
    email: props.client.email,
    phone: props.client.phone || '',
    company: props.client.company || '',
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submitProfile = () => {
    form.put(route('primeedge.profile.update'), {
        preserveScroll: true,
    });
};

const submitPassword = () => {
    passwordForm.put(route('primeedge.profile.password'), {
        onSuccess: () => passwordForm.reset(),
        onError: () => passwordForm.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <PrimeEdgeAppLayout>
        <Head title="Profile - PrimeEdge Advisory" />
        <div class="mb-6">
            <Link :href="route('primeedge.dashboard')" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to Dashboard</Link>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">Profile Settings</h1>
            <p class="text-gray-600 mt-1">Manage your account information and security.</p>
        </div>
        <div class="max-w-2xl space-y-8">
            <div class="bg-white rounded-xl p-8 border border-gray-100 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">Account Information</h2>
                <form @submit.prevent="submitProfile" class="space-y-5">
                    <div>
                        <label for="profile-name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input v-model="form.name" id="profile-name" type="text" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" />
                        <p v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <label for="profile-email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input v-model="form.email" id="profile-email" type="email" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" />
                        <p v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</p>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label for="profile-phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input v-model="form.phone" id="profile-phone" type="tel" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" />
                        </div>
                        <div>
                            <label for="profile-company" class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                            <input v-model="form.company" id="profile-company" type="text" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" />
                        </div>
                    </div>
                    <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white text-sm font-medium rounded-lg hover:from-emerald-700 hover:to-emerald-800 transition-all disabled:opacity-50 shadow-sm">
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                </form>
            </div>

            <div class="bg-white rounded-xl p-8 border border-gray-100 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">Change Password</h2>
                <form @submit.prevent="submitPassword" class="space-y-5">
                    <div>
                        <label for="password-current" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                        <input v-model="passwordForm.current_password" id="password-current" type="password" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" />
                        <p v-if="passwordForm.errors.current_password" class="text-sm text-red-600 mt-1">{{ passwordForm.errors.current_password }}</p>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label for="password-new" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                            <input v-model="passwordForm.password" id="password-new" type="password" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" />
                            <p v-if="passwordForm.errors.password" class="text-sm text-red-600 mt-1">{{ passwordForm.errors.password }}</p>
                        </div>
                        <div>
                            <label for="password-confirm" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                            <input v-model="passwordForm.password_confirmation" id="password-confirm" type="password" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" />
                        </div>
                    </div>
                    <button type="submit" :disabled="passwordForm.processing" class="px-6 py-2.5 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white text-sm font-medium rounded-lg hover:from-emerald-700 hover:to-emerald-800 transition-all disabled:opacity-50 shadow-sm">
                        {{ passwordForm.processing ? 'Updating...' : 'Update Password' }}
                    </button>
                </form>
            </div>
        </div>
    </PrimeEdgeAppLayout>
</template>