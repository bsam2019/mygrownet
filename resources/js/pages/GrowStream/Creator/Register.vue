<template>
    <GrowStreamLayout title="Become a Creator - GrowStream">
        <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold text-[var(--gs-text)]">Become a GrowStream Creator</h1>
                <p class="mt-2 text-[var(--gs-muted)]">
                    Share your content with Zambia and earn from your creativity.
                </p>
            </div>

            <form @submit.prevent="submit" class="gs-card space-y-6 p-8">
                <div v-if="form.errors.length > 0" class="rounded-md bg-red-500/10 p-4">
                    <p class="text-sm font-medium text-red-400">Please fix the following:</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-400">
                        <li v-for="error in form.errors" :key="error">{{ error }}</li>
                    </ul>
                </div>

                <div>
                    <label for="display_name" class="gs-label">Display Name *</label>
                    <input
                        id="display_name"
                        v-model="form.display_name"
                        type="text"
                        class="gs-input"
                        placeholder="Your public creator name"
                        required
                    />
                </div>

                <div>
                    <label for="channel_name" class="gs-label">Channel Name</label>
                    <input
                        id="channel_name"
                        v-model="form.channel_name"
                        type="text"
                        class="gs-input"
                        placeholder="Optional channel/brand name"
                    />
                </div>

                <div>
                    <label for="bio" class="gs-label">Bio</label>
                    <textarea
                        id="bio"
                        v-model="form.bio"
                        rows="4"
                        class="gs-input"
                        placeholder="Tell viewers what kind of content you create"
                    ></textarea>
                </div>

                <div>
                    <label for="website_url" class="gs-label">Website</label>
                    <input
                        id="website_url"
                        v-model="form.website_url"
                        type="url"
                        class="gs-input"
                        placeholder="https://"
                    />
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="facebook_url" class="gs-label">Facebook</label>
                        <input
                            id="facebook_url"
                            v-model="form.facebook_url"
                            type="url"
                            class="gs-input"
                            placeholder="https://facebook.com/..."
                        />
                    </div>
                    <div>
                        <label for="twitter_url" class="gs-label">Twitter / X</label>
                        <input
                            id="twitter_url"
                            v-model="form.twitter_url"
                            type="url"
                            class="gs-input"
                            placeholder="https://twitter.com/..."
                        />
                    </div>
                    <div>
                        <label for="instagram_url" class="gs-label">Instagram</label>
                        <input
                            id="instagram_url"
                            v-model="form.instagram_url"
                            type="url"
                            class="gs-input"
                            placeholder="https://instagram.com/..."
                        />
                    </div>
                    <div>
                        <label for="youtube_url" class="gs-label">YouTube</label>
                        <input
                            id="youtube_url"
                            v-model="form.youtube_url"
                            type="url"
                            class="gs-input"
                            placeholder="https://youtube.com/..."
                        />
                    </div>
                </div>

                <div class="flex items-start rounded-[var(--gs-radius)] border border-[var(--gs-accent)]/30 bg-[var(--gs-accent-soft)] p-4">
                    <div class="flex h-5 items-center">
                        <input
                            id="agree_to_terms"
                            v-model="form.agree_to_terms"
                            type="checkbox"
                            class="h-4 w-4 rounded border-[var(--gs-accent)] text-[var(--gs-accent)] focus:ring-[var(--gs-accent)]"
                            required
                        />
                    </div>
                    <label for="agree_to_terms" class="ml-3 text-sm text-[var(--gs-accent)]">
                        I confirm that I own the rights to all content I upload, and I accept the
                        GrowStream Creator Agreement (v{{ agreementVersion }}).
                    </label>
                </div>

                <div>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="gs-btn gs-btn-accent w-full py-3"
                    >
                        {{ form.processing ? 'Submitting...' : 'Apply to Become a Creator' }}
                    </button>
                </div>
            </form>
        </div>
    </GrowStreamLayout>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import GrowStreamLayout from '@/Layouts/GrowStreamLayout.vue';

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
