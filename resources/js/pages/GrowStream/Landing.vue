<template>
    <div class="bg-background text-on-background font-body-md antialiased min-h-screen">
        <Head title="GrowStream - Watch. Laugh. Binge." />

        <!-- Promo banner -->
        <div class="bg-gradient-to-r from-primary to-[#a73400] text-white text-center py-2.5 px-4 font-label-sm text-label-sm flex items-center justify-center gap-2">
            <span class="material-symbols-outlined text-base" aria-hidden="true">redeem</span>
            New to GrowStream? First episode of every series is always free.
        </div>

        <!-- Header -->
        <header class="border-b border-outline-variant/60 sticky top-0 z-40 bg-background/90 backdrop-blur-md">
            <div class="max-w-6xl mx-auto flex items-center justify-between px-margin-mobile md:px-margin-desktop h-16">
                <div class="font-headline-lg-mobile text-2xl font-extrabold text-primary tracking-tight">GrowStream</div>
                <div class="flex items-center gap-3">
                    <a :href="route('growstream.login')" class="hidden sm:block font-label-md text-label-md text-on-surface-variant hover:text-on-surface transition-colors">Sign In</a>
                    <a :href="route('growstream.register')" class="bg-primary text-on-primary px-5 py-2 rounded-full font-label-md text-label-md hover:bg-[#c94918] transition-colors">Sign Up Free</a>
                </div>
            </div>
        </header>

        <!-- Hero -->
        <section class="relative overflow-hidden">
            <div class="bg-cover bg-center w-full h-full absolute inset-0" style="background-image:url('https://placehold.co/1600x900/241d19/e2571f?text=GrowStream')"></div>
            <div class="hero-fade absolute inset-0"></div>
            <div class="relative max-w-3xl mx-auto text-center px-margin-mobile py-24 md:py-32">
                <span class="inline-block bg-primary-container/60 text-on-primary-container border border-primary/40 px-3 py-1 rounded-full font-label-sm text-label-sm uppercase tracking-widest mb-6">Made in Zambia</span>
                <h1 class="font-display-lg text-display-lg mb-5 drop-shadow-lg">Watch. Laugh. Binge.</h1>
                <p class="font-body-lg text-body-lg text-on-surface-variant mb-9 max-w-xl mx-auto">
                    Zambian movies, comedy, skits, dramas, soaps, and series — premium local entertainment in one place.
                </p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center items-center mb-4">
                    <a :href="route('growstream.register')" class="cta-glow bg-primary text-on-primary px-9 py-4 rounded-full font-label-md text-label-md w-full sm:w-auto flex items-center justify-center gap-2 hover:bg-[#c94918] transition-colors">
                        Start Watching Free <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_forward</span>
                    </a>
                </div>
                <p class="font-label-sm text-label-sm text-on-surface-variant">Free tier available. Plans start at K35/month. Cancel anytime.</p>
            </div>
        </section>

        <!-- Stats strip -->
        <section class="border-y border-outline-variant/60 bg-surface-container-lowest">
            <div class="max-w-6xl mx-auto px-margin-mobile md:px-margin-desktop py-8 grid grid-cols-3 gap-4 text-center">
                <div>
                    <p class="font-display-lg text-3xl md:text-4xl font-extrabold text-primary">{{ formatNumber(stats.creators) }}+</p>
                    <p class="font-label-sm text-label-sm text-on-surface-variant mt-1">Local Creators</p>
                </div>
                <div>
                    <p class="font-display-lg text-3xl md:text-4xl font-extrabold text-primary">{{ formatNumber(stats.titles) }}+</p>
                    <p class="font-label-sm text-label-sm text-on-surface-variant mt-1">Episodes &amp; Titles</p>
                </div>
                <div>
                    <p class="font-display-lg text-3xl md:text-4xl font-extrabold text-primary">{{ stats.price }}</p>
                    <p class="font-label-sm text-label-sm text-on-surface-variant mt-1">Plans From /mo</p>
                </div>
            </div>
        </section>

        <!-- Trending Now -->
        <section class="max-w-6xl mx-auto px-margin-mobile md:px-margin-desktop py-16">
            <div class="flex items-center justify-between mb-6">
                <h2 class="font-headline-md text-headline-md">Trending Now</h2>
                <Link :href="route('growstream.browse', { sort_by: 'view_count' })" class="font-label-md text-label-md text-primary flex items-center gap-1">View All <span class="material-symbols-outlined text-base" aria-hidden="true">arrow_forward</span></Link>
            </div>
            <div class="flex gap-6 overflow-x-auto pb-4" style="scrollbar-width:none;">
                <Link
                    v-for="(v, idx) in trending"
                    :key="v.id"
                    :href="route('growstream.video.detail', { slug: v.slug })"
                    class="poster-card relative min-w-[150px] aspect-[2/3] rounded-lg overflow-hidden bg-surface-container-highest shrink-0 shadow-xl"
                >
                    <div class="bg-cover bg-center w-full h-full absolute inset-0" :style="{ backgroundImage: `url('${v.thumbnail_url || v.poster_url || fallbackPoster}')` }"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
                    <span class="absolute -bottom-2 -left-2 font-display-lg text-7xl italic font-black text-background" style="-webkit-text-stroke:2px #e2571f;">{{ idx + 1 }}</span>
                </Link>
                <div v-if="trending.length === 0" class="flex gap-6">
                    <div v-for="i in 5" :key="i" class="poster-card relative min-w-[150px] aspect-[2/3] rounded-lg overflow-hidden bg-surface-container-highest shrink-0 shadow-xl">
                        <div class="bg-cover bg-center w-full h-full absolute inset-0" :style="{ backgroundImage: `url('https://placehold.co/300x450/${i % 2 ? '241d19' : '332924'}/e2571f?text=${i}')` }"></div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
                        <span class="absolute -bottom-2 -left-2 font-display-lg text-7xl italic font-black text-background" style="-webkit-text-stroke:2px #e2571f;">{{ i }}</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Why GrowStream -->
        <section class="max-w-6xl mx-auto px-margin-mobile md:px-margin-desktop py-16">
            <h2 class="font-headline-md text-headline-md mb-8">Why GrowStream</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="bg-surface-container rounded-xl p-6 border border-outline-variant/60 hover:border-primary/50 transition-colors">
                    <div class="w-11 h-11 rounded-full bg-primary-container flex items-center justify-center mb-4"><span class="material-symbols-outlined text-on-primary-container" aria-hidden="true">storefront</span></div>
                    <h3 class="font-label-md text-label-md mb-2">Local, by local creators</h3>
                    <p class="font-label-sm text-label-sm text-on-surface-variant">Content made for and by Zambians — not a foreign catalogue.</p>
                </div>
                <div class="bg-surface-container rounded-xl p-6 border border-outline-variant/60 hover:border-primary/50 transition-colors">
                    <div class="w-11 h-11 rounded-full bg-primary-container flex items-center justify-center mb-4"><span class="material-symbols-outlined text-on-primary-container" aria-hidden="true">play_circle</span></div>
                    <h3 class="font-label-md text-label-md mb-2">Free to start</h3>
                    <p class="font-label-sm text-label-sm text-on-surface-variant">Watch the first episode of any series free. No card required.</p>
                </div>
                <div class="bg-surface-container rounded-xl p-6 border border-outline-variant/60 hover:border-primary/50 transition-colors">
                    <div class="w-11 h-11 rounded-full bg-primary-container flex items-center justify-center mb-4"><span class="material-symbols-outlined text-on-primary-container" aria-hidden="true">account_balance_wallet</span></div>
                    <h3 class="font-label-md text-label-md mb-2">Pay with Mobile Money</h3>
                    <p class="font-label-sm text-label-sm text-on-surface-variant">MTN, Airtel, Zamtel — no international card needed.</p>
                </div>
                <div class="bg-surface-container rounded-xl p-6 border border-outline-variant/60 hover:border-primary/50 transition-colors">
                    <div class="w-11 h-11 rounded-full bg-primary-container flex items-center justify-center mb-4"><span class="material-symbols-outlined text-on-primary-container" aria-hidden="true">handshake</span></div>
                    <h3 class="font-label-md text-label-md mb-2">Creators get paid transparently</h3>
                    <p class="font-label-sm text-label-sm text-on-surface-variant">Your subscription directly supports the creators you watch.</p>
                </div>
            </div>
        </section>

        <!-- FAQ -->
        <section class="max-w-3xl mx-auto px-margin-mobile md:px-margin-desktop py-16">
            <h2 class="font-headline-md text-headline-md mb-6">Frequently Asked Questions</h2>
            <div class="flex flex-col gap-3">
                <div v-for="(faq, i) in faqs" :key="i" class="faq-item bg-surface-container-low rounded-lg overflow-hidden border border-outline-variant/40" :class="{ open: openFaq === i }">
                    <button class="w-full flex items-center justify-between px-5 py-4 text-left" @click="toggleFaq(i)">
                        <span class="font-label-md text-label-md">{{ faq.q }}</span>
                        <span class="material-symbols-outlined text-primary transition-transform" :class="openFaq === i ? 'faq-icon-open' : ''" aria-hidden="true">add</span>
                    </button>
                    <div class="faq-body px-5" :style="openFaq === i ? 'max-height:200px;padding-top:.5rem;' : ''">
                        <p class="font-body-md text-body-md text-on-surface-variant pb-4">{{ faq.a }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Repeated CTA -->
        <section class="max-w-3xl mx-auto px-margin-mobile md:px-margin-desktop py-16 text-center">
            <a :href="route('growstream.register')" class="cta-glow bg-primary text-on-primary px-9 py-4 rounded-full font-label-md text-label-md inline-flex items-center gap-2 hover:bg-[#c94918] transition-colors">
                Sign Up Free <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_forward</span>
            </a>
            <p class="font-label-sm text-label-sm text-on-surface-variant mt-3">Cancel anytime. No hidden fees.</p>
        </section>

        <!-- Footer -->
        <footer class="border-t border-outline-variant/60 bg-surface-container-lowest">
            <div class="max-w-6xl mx-auto px-margin-mobile md:px-margin-desktop py-10">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8 font-label-sm text-label-sm text-on-surface-variant">
                    <Link class="block mb-2 hover:text-on-surface transition-colors" :href="route('growstream.browse')">Browse</Link>
                    <Link class="block mb-2 hover:text-on-surface transition-colors" :href="route('growstream.creator.register')">Become a Creator</Link>
                    <Link class="block mb-2 hover:text-on-surface transition-colors" :href="route('growstream.subscription')">Plans</Link>
                    <Link class="block mb-2 hover:text-on-surface transition-colors" :href="route('growstream.login')">Sign In</Link>
                </div>
                <p class="font-label-sm text-label-sm text-on-surface-variant">GrowStream — Made in Zambia</p>
            </div>
        </footer>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import type { Video } from '@/types/growstream';

interface Props {
    trending?: Video[];
    stats?: { creators: number; titles: number; price: string };
}

const props = withDefaults(defineProps<Props>(), {
    trending: () => [],
    stats: () => ({ creators: 500, titles: 10000, price: 'K35' }),
});

const fallbackPoster = 'https://placehold.co/300x450/241d19/e2571f?text=GrowStream';
const openFaq = ref<number | null>(null);

const faqs = [
    { q: 'What is GrowStream?', a: 'GrowStream is a Zambian streaming platform for local creators — movies, comedy, dramas, series, and more, all made locally.' },
    { q: 'How much does it cost?', a: "There's a free tier to get started. Paid plans start at K35/month with more quality and downloads at higher tiers." },
    { q: 'How do I pay?', a: 'Pay easily with MTN, Airtel, or Zamtel Mobile Money — no bank card needed.' },
    { q: 'Can I cancel anytime?', a: 'Yes — cancel anytime from your account settings, no long-term contract.' },
    { q: 'Is there a free plan?', a: 'Yes — the Free tier lets you watch the catalogue with ads at standard quality, no payment required.' },
];

const toggleFaq = (i: number) => { openFaq.value = openFaq.value === i ? null : i; };

const formatNumber = (n: number): string => {
    if (n >= 1000000) return (n / 1000000).toFixed(1) + 'M';
    if (n >= 1000) return (n / 1000).toFixed(0) + 'K';
    return n.toString();
};
</script>

<style scoped>
.faq-icon-open { transform: rotate(45deg); }
.poster-card { transition: transform 0.25s ease; }
.poster-card:hover { transform: translateY(-6px) scale(1.03); }
.cta-glow { box-shadow: 0 0 0 1px rgba(226,87,31,.4), 0 10px 40px -10px rgba(226,87,31,.6); }
.hero-fade { background: linear-gradient(180deg, rgba(20,16,14,.35) 0%, rgba(20,16,14,.85) 65%, #14100e 100%); }
</style>
