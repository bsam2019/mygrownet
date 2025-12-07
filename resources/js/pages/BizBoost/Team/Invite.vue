<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import { ArrowLeftIcon, EnvelopeIcon } from '@heroicons/vue/24/outline';

const form = useForm({
    name: '',
    email: '',
    role: 'member',
    location_id: null as number | null,
});

const submit = () => {
    form.post('/bizboost/team/invite');
};
</script>

<template>
    <Head title="Invite Team Member - BizBoost" />
    <BizBoostLayout title="Invite Team Member">
        <div class="max-w-lg">
            <Link
                href="/bizboost/team"
                class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 mb-6"
            >
                <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                Back to Team
            </Link>

            <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="rounded-lg bg-violet-100 p-2">
                        <EnvelopeIcon class="h-5 w-5 text-violet-600" aria-hidden="true" />
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Invite Team Member</h2>
                        <p class="text-sm text-gray-500">Send an invitation to join your business</p>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Name *</label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            placeholder="Team member's name"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            placeholder="email@example.com"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500"
                        />
                        <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700">Role *</label>
                        <select
                            id="role"
                            v-model="form.role"
                            required
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500"
                        >
                            <option value="admin">Admin - Full access except billing</option>
                            <option value="editor">Editor - Create and edit content</option>
                            <option value="member">Member - View-only access</option>
                        </select>
                    </div>

                    <div class="bg-violet-50 rounded-lg p-4">
                        <p class="text-sm text-violet-700">
                            An invitation email will be sent with a link to join your business. 
                            The invitation expires in 7 days.
                        </p>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <Link
                            href="/bizboost/team"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-700 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Sending...' : 'Send Invitation' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </BizBoostLayout>
</template>
