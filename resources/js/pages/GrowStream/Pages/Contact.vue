<script setup lang="ts">
import { ref } from 'vue';
import PublicPage from '@/components/GrowStream/PublicPage.vue';

const form = ref({ name: '', email: '', subject: 'General', message: '' });
const submitted = ref(false);

const submit = () => {
    // Inertia-agnostic placeholder — real form posts via a future endpoint.
    submitted.value = true;
};

const contactChannels = [
    { icon: 'mail', label: 'Email', value: 'support@mygrownet.com', href: 'mailto:support@mygrownet.com' },
    { icon: 'forum', label: 'Creators', value: 'creator-support@mygrownet.com', href: 'mailto:creator-support@mygrownet.com' },
    { icon: 'language', label: 'Website', value: 'mygrownet.com', href: 'https://mygrownet.com' },
];
</script>

<template>
    <PublicPage title="Contact Us" subtitle="We would love to hear from you.">
        <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a
                v-for="c in contactChannels"
                :key="c.label"
                :href="c.href"
                class="bg-surface-container rounded-xl p-6 border border-outline-variant/60 hover:border-primary/50 transition-colors text-center"
            >
                <div class="w-12 h-12 rounded-full bg-primary-container flex items-center justify-center mx-auto mb-3">
                    <span class="material-symbols-outlined text-on-primary-container" aria-hidden="true">{{ c.icon }}</span>
                </div>
                <h3 class="font-label-md text-label-md mb-1">{{ c.label }}</h3>
                <p class="font-label-sm text-label-sm text-primary break-all">{{ c.value }}</p>
            </a>
        </section>

        <section class="bg-surface-container rounded-xl p-6 border border-outline-variant/60">
            <h2 class="font-headline-md text-headline-md mb-5">Send us a message</h2>

            <div v-if="submitted" class="rounded-lg bg-surface-container-low border border-primary/40 p-5 text-center">
                <span class="material-symbols-outlined text-primary text-3xl mb-2" aria-hidden="true">check_circle</span>
                <p class="font-label-md text-label-md text-on-surface">Thanks! We will get back to you soon.</p>
            </div>

            <form v-else class="space-y-4" @submit.prevent="submit">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1.5 font-label-sm text-label-sm text-on-surface-variant">Your name</label>
                        <input v-model="form.name" type="text" required class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-4 py-3 text-body-md focus:outline-none focus:ring-2 focus:ring-primary" />
                    </div>
                    <div>
                        <label class="block mb-1.5 font-label-sm text-label-sm text-on-surface-variant">Email</label>
                        <input v-model="form.email" type="email" required class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-4 py-3 text-body-md focus:outline-none focus:ring-2 focus:ring-primary" />
                    </div>
                </div>
                <div>
                    <label class="block mb-1.5 font-label-sm text-label-sm text-on-surface-variant">Subject</label>
                    <select v-model="form.subject" class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-4 py-3 text-body-md focus:outline-none focus:ring-2 focus:ring-primary">
                        <option>General</option>
                        <option>Billing</option>
                        <option>Content</option>
                        <option>Creator support</option>
                        <option>Report a problem</option>
                    </select>
                </div>
                <div>
                    <label class="block mb-1.5 font-label-sm text-label-sm text-on-surface-variant">Message</label>
                    <textarea v-model="form.message" rows="5" required class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-4 py-3 text-body-md focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                </div>
                <button type="submit" class="bg-primary text-on-primary px-6 py-3 rounded-full font-label-md text-label-md hover:bg-[#c94918] transition-colors">Send Message</button>
            </form>
        </section>
    </PublicPage>
</template>
