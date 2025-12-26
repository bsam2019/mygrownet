<script setup lang="ts">
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    HomeIcon,
    UserIcon,
    ShoppingBagIcon,
    DocumentTextIcon,
    UsersIcon,
    ArrowRightOnRectangleIcon,
} from '@heroicons/vue/24/outline';

interface Props {
    site: {
        id: number;
        name: string;
        subdomain: string;
        logo: string | null;
        theme: {
            primaryColor?: string;
        } | null;
    };
    user: {
        id: number;
        name: string;
        email: string;
        phone: string | null;
        role: { name: string; slug: string } | null;
        permissions: string[];
    };
}

const props = defineProps<Props>();
const page = usePage();

const primaryColor = computed(() => props.site.theme?.primaryColor || '#2563eb');
const subdomain = computed(() => props.site.subdomain);
const success = computed(() => page.props.flash?.success as string | undefined);

const form = useForm({
    name: props.user.name,
    phone: props.user.phone || '',
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
});

const hasPermission = (permission: string) => props.user.permissions.includes(permission);

const navigation = computed(() => {
    const items = [
        { name: 'Dashboard', href: route('site.member.dashboard', { subdomain: subdomain.value }), icon: HomeIcon },
        { name: 'Profile', href: route('site.member.profile', { subdomain: subdomain.value }), icon: UserIcon, current: true },
        { name: 'Orders', href: route('site.member.orders', { subdomain: subdomain.value }), icon: ShoppingBagIcon },
    ];

    if (hasPermission('posts.view')) {
        items.push({ name: 'Posts', href: route('site.member.posts.index', { subdomain: subdomain.value }), icon: DocumentTextIcon });
    }

    if (hasPermission('users.view')) {
        items.push({ name: 'Users', href: route('site.member.users.index', { subdomain: subdomain.value }), icon: UsersIcon });
    }

    return items;
});

const logout = () => {
    router.post(route('site.logout', { subdomain: subdomain.value }));
};

const submit = () => {
    form.put(route('site.member.profile.update', { subdomain: subdomain.value }), {
        onSuccess: () => {
            form.reset('current_password', 'new_password', 'new_password_confirmation');
        },
    });
};
</script>

<template>
    <Head :title="`Profile - ${site.name}`" />

    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <div class="flex items-center gap-3">
                        <img
                            v-if="site.logo"
                            :src="site.logo"
                            :alt="site.name"
                            class="h-10 w-auto"
                        />
                        <div
                            v-else
                            class="h-10 w-10 rounded-full flex items-center justify-center text-white font-bold"
                            :style="{ backgroundColor: primaryColor }"
                        >
                            {{ site.name.charAt(0) }}
                        </div>
                        <span class="text-xl font-semibold text-gray-900">{{ site.name }}</span>
                    </div>

                    <div class="flex items-center gap-4">
                        <span class="text-sm text-gray-600">{{ user.name }}</span>
                        <button
                            @click="logout"
                            class="flex items-center gap-1 text-sm text-gray-600 hover:text-gray-900"
                        >
                            <ArrowRightOnRectangleIcon class="h-5 w-5" aria-hidden="true" />
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex gap-8">
                <!-- Sidebar -->
                <aside class="w-64 flex-shrink-0">
                    <nav class="space-y-1">
                        <Link
                            v-for="item in navigation"
                            :key="item.name"
                            :href="item.href"
                            class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-colors"
                            :class="item.current ? 'text-white' : 'text-gray-700 hover:bg-gray-100'"
                            :style="item.current ? { backgroundColor: primaryColor } : {}"
                        >
                            <component :is="item.icon" class="h-5 w-5" aria-hidden="true" />
                            {{ item.name }}
                        </Link>
                    </nav>
                </aside>

                <!-- Main Content -->
                <main class="flex-1">
                    <div class="mb-8">
                        <h1 class="text-2xl font-bold text-gray-900">Profile Settings</h1>
                        <p class="text-gray-600">Manage your account information</p>
                    </div>

                    <!-- Success Message -->
                    <div v-if="success" class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                        <p class="text-sm text-green-700">{{ success }}</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Basic Info -->
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h2>
                            
                            <div class="space-y-4">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">
                                        Full name
                                    </label>
                                    <input
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        required
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:border-transparent"
                                        :class="{ 'border-red-500': form.errors.name }"
                                    />
                                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.name }}
                                    </p>
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">
                                        Email address
                                    </label>
                                    <input
                                        id="email"
                                        :value="user.email"
                                        type="email"
                                        disabled
                                        class="mt-1 block w-full px-3 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-500"
                                    />
                                    <p class="mt-1 text-xs text-gray-500">Email cannot be changed</p>
                                </div>

                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700">
                                        Phone number
                                    </label>
                                    <input
                                        id="phone"
                                        v-model="form.phone"
                                        type="tel"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:border-transparent"
                                        :class="{ 'border-red-500': form.errors.phone }"
                                    />
                                    <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.phone }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Change Password -->
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Change Password</h2>
                            <p class="text-sm text-gray-600 mb-4">Leave blank to keep current password</p>
                            
                            <div class="space-y-4">
                                <div>
                                    <label for="current_password" class="block text-sm font-medium text-gray-700">
                                        Current password
                                    </label>
                                    <input
                                        id="current_password"
                                        v-model="form.current_password"
                                        type="password"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:border-transparent"
                                        :class="{ 'border-red-500': form.errors.current_password }"
                                    />
                                    <p v-if="form.errors.current_password" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.current_password }}
                                    </p>
                                </div>

                                <div>
                                    <label for="new_password" class="block text-sm font-medium text-gray-700">
                                        New password
                                    </label>
                                    <input
                                        id="new_password"
                                        v-model="form.new_password"
                                        type="password"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:border-transparent"
                                        :class="{ 'border-red-500': form.errors.new_password }"
                                    />
                                    <p v-if="form.errors.new_password" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.new_password }}
                                    </p>
                                </div>

                                <div>
                                    <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700">
                                        Confirm new password
                                    </label>
                                    <input
                                        id="new_password_confirmation"
                                        v-model="form.new_password_confirmation"
                                        type="password"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:border-transparent"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="flex justify-end">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-6 py-2 rounded-lg text-white font-medium disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                :style="{ backgroundColor: primaryColor }"
                            >
                                <span v-if="form.processing">Saving...</span>
                                <span v-else>Save Changes</span>
                            </button>
                        </div>
                    </form>
                </main>
            </div>
        </div>
    </div>
</template>
