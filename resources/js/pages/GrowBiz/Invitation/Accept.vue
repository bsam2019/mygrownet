<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { CheckCircleIcon, BuildingOfficeIcon, BriefcaseIcon } from '@heroicons/vue/24/outline';

interface Props {
    invitation: {
        id: number;
        expires_at: string;
    };
    employeeName: string;
    businessName: string;
    position: string | null;
    token: string;
    isLoggedIn: boolean;
}

const props = defineProps<Props>();

const form = useForm({});

const acceptInvitation = () => {
    form.post(route('growbiz.invitation.accept.submit', props.token));
};
</script>

<template>
    <Head :title="`Join ${businessName}`" />
    
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-8 text-center text-white">
                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <BuildingOfficeIcon class="w-8 h-8" aria-hidden="true" />
                </div>
                <h1 class="text-2xl font-bold mb-2">You're Invited!</h1>
                <p class="text-blue-100">Join {{ businessName }} on GrowBiz</p>
            </div>

            <!-- Content -->
            <div class="p-6">
                <div class="text-center mb-6">
                    <p class="text-gray-600">
                        <span class="font-semibold text-gray-900">{{ businessName }}</span> 
                        has invited you to join their team.
                    </p>
                </div>

                <!-- Details Card -->
                <div class="bg-gray-50 rounded-xl p-4 mb-6 space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <span class="text-blue-600 font-semibold">
                                {{ employeeName.charAt(0).toUpperCase() }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Your Name</p>
                            <p class="font-medium text-gray-900">{{ employeeName }}</p>
                        </div>
                    </div>
                    
                    <div v-if="position" class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                            <BriefcaseIcon class="w-5 h-5 text-indigo-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Position</p>
                            <p class="font-medium text-gray-900">{{ position }}</p>
                        </div>
                    </div>
                </div>

                <!-- Action -->
                <div v-if="isLoggedIn">
                    <button
                        @click="acceptInvitation"
                        :disabled="form.processing"
                        class="w-full py-3 px-4 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 disabled:opacity-50 flex items-center justify-center gap-2"
                    >
                        <CheckCircleIcon class="w-5 h-5" aria-hidden="true" />
                        {{ form.processing ? 'Joining...' : 'Accept & Join Team' }}
                    </button>
                </div>
                <div v-else class="space-y-3">
                    <Link
                        :href="route('login')"
                        class="block w-full py-3 px-4 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 text-center"
                    >
                        Log In to Accept
                    </Link>
                    <Link
                        :href="route('register')"
                        class="block w-full py-3 px-4 border border-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-50 text-center"
                    >
                        Create Account
                    </Link>
                </div>

                <p class="text-xs text-gray-500 text-center mt-4">
                    This invitation expires on {{ new Date(invitation.expires_at).toLocaleDateString() }}
                </p>
            </div>
        </div>
    </div>
</template>
