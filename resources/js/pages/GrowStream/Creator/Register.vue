<template>
    <AppLayout title="Become a Creator - GrowStream">
        <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold text-gray-900">Become a GrowStream Creator</h1>
                <p class="mt-2 text-gray-600">
                    Share your content with Zambia and earn from your creativity.
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-6 rounded-lg bg-white p-8 shadow">
                <div v-if="form.errors.length > 0" class="rounded-md bg-red-50 p-4">
                    <p class="text-sm font-medium text-red-800">Please fix the following:</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700">
                        <li v-for="error in form.errors" :key="error">{{ error }}</li>
                    </ul>
                </div>

                <div>
                    <label for="display_name" class="block text-sm font-medium text-gray-700">Display Name *</label>
                    <input
                        id="display_name"
                        v-model="form.display_name"
                        type="text"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Your public creator name"
                        required
                    />
                </div>

                <div>
                    <label for="channel_name" class="block text-sm font-medium text-gray-700">Channel Name</label>
                    <input
                        id="channel_name"
                        v-model="form.channel_name"
                        type="text"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Optional channel/brand name"
                    />
                </div>

                <div>
                    <label for="bio" class="block text-sm font-medium text-gray-700">Bio</label>
                    <textarea
                        id="bio"
                        v-model="form.bio"
                        rows="4"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Tell viewers what kind of content you create"
                    ></textarea>
                </div>

                <div>
                    <label for="website_url" class="block text-sm font-medium text-gray-700">Website</label>
                    <input
                        id="website_url"
                        v-model="form.website_url"
                        type="url"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="https://"
                    />
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="facebook_url" class="block text-sm font-medium text-gray-700">Facebook</label>
                        <input
                            id="facebook_url"
                            v-model="form.facebook_url"
                            type="url"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="https://facebook.com/..."
                        />
                    </div>
                    <div>
                        <label for="twitter_url" class="block text-sm font-medium text-gray-700">Twitter / X</label>
                        <input
                            id="twitter_url"
                            v-model="form.twitter_url"
                            type="url"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="https://twitter.com/..."
                        />
                    </div>
                    <div>
                        <label for="instagram_url" class="block text-sm font-medium text-gray-700">Instagram</label>
                        <input
                            id="instagram_url"
                            v-model="form.instagram_url"
                            type="url"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="https://instagram.com/..."
                        />
                    </div>
                    <div>
                        <label for="youtube_url" class="block text-sm font-medium text-gray-700">YouTube</label>
                        <input
                            id="youtube_url"
                            v-model="form.youtube_url"
                            type="url"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="https://youtube.com/..."
                        />
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="flex h-5 items-center">
                        <input
                            id="agree_to_terms"
                            v-model="form.agree_to_terms"
                            type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            required
                        />
                    </div>
                    <label for="agree_to_terms" class="ml-3 text-sm text-gray-600">
                        I confirm that I own the rights to all content I upload, and I accept the
                        GrowStream Creator Agreement (v{{ agreementVersion }}).
                    </label>
                </div>

                <div>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full rounded-md bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Submitting...' : 'Apply to Become a Creator' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

interface Props {
    agreementVersion: string;
}

defineProps<Props>();

const form = useForm({
    display_name: '',
    channel_name: '',
    bio: '',
    website_url: '',
    facebook_url: '',
    twitter_url: '',
    instagram_url: '',
    youtube_url: '',
    agree_to_terms: false,
});

const submit = () => {
    form.post(route('growstream.creator.register.store'));
};
</script>
