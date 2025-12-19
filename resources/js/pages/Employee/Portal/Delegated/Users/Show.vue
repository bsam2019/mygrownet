<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/layouts/EmployeePortalLayout.vue';
import {
    UserIcon,
    ArrowLeftIcon,
    EnvelopeIcon,
    PhoneIcon,
    CalendarIcon,
    CheckCircleIcon,
    XCircleIcon,
    CreditCardIcon,
} from '@heroicons/vue/24/outline';

interface Subscription {
    id: number;
    name: string;
    status: string;
    created_at: string;
    ends_at: string | null;
}

interface Props {
    employee: { id: number; full_name: string };
    user: {
        id: number;
        name: string;
        email: string;
        phone: string | null;
        created_at: string;
        email_verified_at: string | null;
    };
    subscriptions: Subscription[];
}

const props = defineProps<Props>();

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatDateTime = (date: string) => {
    return new Date(date).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <EmployeePortalLayout>
        <Head :title="`User: ${user.name}`" />

        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Back Button -->
            <Link
                :href="route('employee.portal.delegated.users.index')"
                class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900"
            >
                <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                Back to Users
            </Link>

            <!-- User Header -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-purple-600 to-indigo-600 px-6 py-8">
                    <div class="flex items-center gap-4">
                        <div class="h-20 w-20 rounded-full bg-white/20 flex items-center justify-center">
                            <span class="text-3xl font-bold text-white">
                                {{ user.name.charAt(0).toUpperCase() }}
                            </span>
                        </div>
                        <div class="text-white">
                            <h1 class="text-2xl font-bold">{{ user.name }}</h1>
                            <p class="text-purple-100">Member ID: {{ user.id }}</p>
                        </div>
                    </div>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Contact Info -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Contact Information</h3>
                        
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-gray-100 rounded-lg">
                                <EnvelopeIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Email</p>
                                <p class="font-medium text-gray-900">{{ user.email }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-gray-100 rounded-lg">
                                <PhoneIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Phone</p>
                                <p class="font-medium text-gray-900">{{ user.phone || 'Not provided' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Account Info -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Account Status</h3>
                        
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-gray-100 rounded-lg">
                                <CalendarIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Member Since</p>
                                <p class="font-medium text-gray-900">{{ formatDate(user.created_at) }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div :class="[
                                'p-2 rounded-lg',
                                user.email_verified_at ? 'bg-green-100' : 'bg-amber-100'
                            ]">
                                <component
                                    :is="user.email_verified_at ? CheckCircleIcon : XCircleIcon"
                                    :class="[
                                        'h-5 w-5',
                                        user.email_verified_at ? 'text-green-600' : 'text-amber-600'
                                    ]"
                                    aria-hidden="true"
                                />
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Email Verification</p>
                                <p :class="[
                                    'font-medium',
                                    user.email_verified_at ? 'text-green-700' : 'text-amber-700'
                                ]">
                                    {{ user.email_verified_at ? 'Verified' : 'Not Verified' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subscriptions -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <CreditCardIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                        Subscriptions
                    </h2>
                </div>

                <div v-if="subscriptions.length === 0" class="p-8 text-center text-gray-500">
                    <CreditCardIcon class="h-12 w-12 mx-auto mb-3 text-gray-300" aria-hidden="true" />
                    <p>No subscriptions found</p>
                </div>

                <div v-else class="divide-y divide-gray-200">
                    <div
                        v-for="sub in subscriptions"
                        :key="sub.id"
                        class="px-6 py-4 flex items-center justify-between"
                    >
                        <div>
                            <p class="font-medium text-gray-900">{{ sub.name }}</p>
                            <p class="text-sm text-gray-500">Started {{ formatDateTime(sub.created_at) }}</p>
                        </div>
                        <span
                            :class="[
                                'px-3 py-1 text-xs font-medium rounded-full',
                                sub.status === 'active' ? 'bg-green-100 text-green-800' :
                                sub.status === 'cancelled' ? 'bg-red-100 text-red-800' :
                                'bg-gray-100 text-gray-800'
                            ]"
                        >
                            {{ sub.status }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Access Notice -->
            <div class="bg-purple-50 rounded-xl p-4 text-sm text-purple-700">
                <p class="font-medium">Delegated Access Notice</p>
                <p>You are viewing this user's information through delegated permissions. All access is logged for security purposes.</p>
            </div>
        </div>
    </EmployeePortalLayout>
</template>
