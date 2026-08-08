<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

const columns = [
    {
        title: 'Company',
        links: [
            { label: 'About', href: () => route('growstream.pages.about') },
            { label: 'Become a Creator', href: () => route('growstream.creator.register') },
            { label: 'Contact Us', href: () => route('growstream.pages.contact') },
        ],
    },
    {
        title: 'Support',
        links: [
            { label: 'Help Center', href: () => route('growstream.pages.help') },
            { label: 'Plans & Pricing', href: () => route('growstream.subscription') },
            { label: 'Browse', href: () => route('growstream.browse') },
        ],
    },
    {
        title: 'Legal',
        links: [
            { label: 'Terms of Service', href: () => route('growstream.pages.terms') },
            { label: 'Privacy Policy', href: () => route('growstream.pages.privacy') },
            { label: 'Community Guidelines', href: () => route('growstream.pages.help') },
        ],
    },
];
</script>

<template>
    <footer class="border-t border-outline-variant/60 bg-surface-container-lowest mt-16">
        <div class="max-w-6xl mx-auto px-margin-mobile md:px-margin-desktop py-12">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-10">
                <!-- Brand -->
                <div class="col-span-2 md:col-span-1">
                    <div class="font-headline-lg-mobile text-2xl font-extrabold text-primary tracking-tight mb-3">GrowStream</div>
                    <p class="font-label-sm text-label-sm text-on-surface-variant leading-relaxed">Zambian streaming for local creators — watch, laugh, binge.</p>
                </div>

                <div v-for="col in columns" :key="col.title">
                    <h4 class="font-label-md text-label-md text-on-surface mb-3">{{ col.title }}</h4>
                    <div class="space-y-2 font-label-sm text-label-sm text-on-surface-variant">
                        <Link
                            v-for="link in col.links"
                            :key="link.label"
                            :href="link.href()"
                            class="block hover:text-on-surface transition-colors"
                        >{{ link.label }}</Link>
                    </div>
                </div>
            </div>

            <div class="border-t border-outline-variant/40 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="font-label-sm text-label-sm text-on-surface-variant">GrowStream — Made in Zambia</p>
                <p class="font-label-sm text-label-sm text-on-surface-variant">© {{ new Date().getFullYear() }} MyGrowNet. All rights reserved.</p>
            </div>
        </div>
    </footer>
</template>
