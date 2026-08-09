<template>
    <div class="bg-background text-on-background font-body-md antialiased min-h-screen flex flex-col">
        <Head title="GrowStream - Watch. Laugh. Binge." />

        <GrowStreamHeader show-promo />

        <!-- Hero -->
        <section class="relative overflow-hidden bg-gradient-to-b from-[#18110e] via-[#1f1713] to-background">
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-primary/20 via-transparent to-transparent"></div>
            <div class="hero-fade absolute inset-0"></div>
            <div ref="heroRef" class="relative max-w-3xl mx-auto text-center px-margin-mobile py-24 md:py-32">
                <span class="gs-hero-item inline-block bg-primary-container/60 text-on-primary-container border border-primary/40 px-3 py-1 rounded-full font-label-sm text-label-sm uppercase tracking-widest mb-6" style="animation-delay:0.05s">Made in Zambia</span>
                <h1 class="gs-hero-item font-display-lg text-display-lg mb-5 drop-shadow-lg" style="animation-delay:0.15s">Watch. Laugh. Binge.</h1>
                <p class="gs-hero-item font-body-lg text-body-lg text-on-surface-variant mb-9 max-w-xl mx-auto" style="animation-delay:0.25s">
                    Zambian movies, comedy, skits, dramas, soaps, and series — premium local entertainment in one place.
                </p>
                <div class="gs-hero-item flex flex-col sm:flex-row gap-3 justify-center items-center mb-4" style="animation-delay:0.35s">
                    <a :href="registerHref" class="cta-glow bg-primary text-on-primary px-9 py-4 rounded-full font-label-md text-label-md w-full sm:w-auto flex items-center justify-center gap-2 hover:bg-[#c94918] hover:scale-[1.03] active:scale-[0.98] transition-all">
                        Start Watching Free <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_forward</span>
                    </a>
                    <Link :href="route('growstream.browse')" class="bg-surface-container-high/80 backdrop-blur text-on-surface border border-outline-variant/60 px-8 py-4 rounded-full font-label-md text-label-md w-full sm:w-auto flex items-center justify-center gap-2 hover:bg-surface-container-highest hover:border-primary/40 transition-all">
                        Browse Catalogue <span class="material-symbols-outlined text-lg" aria-hidden="true">grid_view</span>
                    </Link>
                </div>
                <p class="gs-hero-item font-label-sm text-label-sm text-on-surface-variant" style="animation-delay:0.45s">Free tier available. Plans start at K35/month. Cancel anytime.</p>
            </div>
        </section>

        <div ref="revealRoot" class="flex-1">
            <!-- Stats strip -->
            <section data-reveal class="border-y border-outline-variant/60 bg-surface-container-lowest">
                <div class="max-w-6xl mx-auto px-margin-mobile md:px-margin-desktop py-8 grid grid-cols-3 gap-4 text-center">
                    <div>
                        <p class="font-display-lg text-3xl md:text-4xl font-extrabold text-primary">{{ formatNumber(countUp(stats.creators, 'creators')) }}+</p>
                        <p class="font-label-sm text-label-sm text-on-surface-variant mt-1">Local Creators</p>
                    </div>
                    <div>
                        <p class="font-display-lg text-3xl md:text-4xl font-extrabold text-primary">{{ formatNumber(countUp(stats.titles, 'titles')) }}+</p>
                        <p class="font-label-sm text-label-sm text-on-surface-variant mt-1">Episodes &amp; Titles</p>
                    </div>
                    <div>
                        <p class="font-display-lg text-3xl md:text-4xl font-extrabold text-primary">{{ stats.price }}</p>
                        <p class="font-label-sm text-label-sm text-on-surface-variant mt-1">Plans From /mo</p>
                    </div>
                </div>
            </section>

            <!-- Trending Now -->
            <section data-reveal class="max-w-6xl mx-auto px-margin-mobile md:px-margin-desktop py-16">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="font-headline-md text-headline-md">Trending Now</h2>
                    <Link :href="route('growstream.browse', { sort_by: 'view_count' })" class="font-label-md text-label-md text-primary flex items-center gap-1 group">View All <span class="material-symbols-outlined text-base group-hover:translate-x-0.5 transition-transform" aria-hidden="true">arrow_forward</span></Link>
                </div>
                <div class="flex gap-6 overflow-x-auto pb-4 gs-scroll-fade" style="scrollbar-width:none;">
                    <Link
                        v-for="(v, idx) in trending"
                        :key="v.id"
                        :href="route('growstream.video.detail', { slug: v.slug })"
                        class="poster-card relative min-w-[150px] aspect-[2/3] rounded-lg overflow-hidden bg-surface-container-highest shrink-0 shadow-xl"
                        :style="{ animationDelay: `${idx * 0.07}s` }"
                        data-reveal
                    >
                        <div class="bg-cover bg-center w-full h-full absolute inset-0" :style="{ backgroundImage: `url('${v.thumbnail_url || v.poster_url || fallbackPoster}')` }"></div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
                        <span class="absolute -bottom-2 -left-2 font-display-lg text-7xl italic font-black text-background" style="-webkit-text-stroke:2px #e2571f;">{{ idx + 1 }}</span>
                    </Link>
                    <div v-if="trending.length === 0" class="flex gap-6">
                        <div v-for="i in 5" :key="i" class="poster-card relative min-w-[150px] aspect-[2/3] rounded-lg overflow-hidden bg-surface-container-highest shrink-0 shadow-xl" :style="{ animationDelay: `${i * 0.07}s` }" data-reveal>
                            <div class="bg-cover bg-center w-full h-full absolute inset-0" :style="{ backgroundImage: `url('https://placehold.co/300x450/${i % 2 ? '241d19' : '332924'}/e2571f?text=${i}')` }"></div>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
                            <span class="absolute -bottom-2 -left-2 font-display-lg text-7xl italic font-black text-background" style="-webkit-text-stroke:2px #e2571f;">{{ i }}</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Why GrowStream -->
            <section data-reveal class="max-w-6xl mx-auto px-margin-mobile md:px-margin-desktop py-16">
                <h2 class="font-headline-md text-headline-md mb-8">Why GrowStream</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    <div v-for="(feat, i) in features" :key="feat.title" class="feature-card bg-surface-container rounded-xl p-6 border border-outline-variant/60 hover:border-primary/50 hover:translate-y-[-4px] transition-all" :style="{ animationDelay: `${i * 0.08}s` }" data-reveal>
                        <div class="w-11 h-11 rounded-full bg-primary-container flex items-center justify-center mb-4"><span class="material-symbols-outlined text-on-primary-container" aria-hidden="true">{{ feat.icon }}</span></div>
                        <h3 class="font-label-md text-label-md mb-2">{{ feat.title }}</h3>
                        <p class="font-label-sm text-label-sm text-on-surface-variant">{{ feat.body }}</p>
                    </div>
                </div>
            </section>

            <!-- FAQ -->
            <section data-reveal class="max-w-3xl mx-auto px-margin-mobile md:px-margin-desktop py-16">
                <h2 class="font-headline-md text-headline-md mb-6">Frequently Asked Questions</h2>
                <div class="flex flex-col gap-3">
                    <div v-for="(faq, i) in faqs" :key="i" class="faq-item bg-surface-container-low rounded-lg overflow-hidden border border-outline-variant/40" :class="{ open: openFaq === i }" data-reveal :style="{ animationDelay: `${i * 0.05}s` }">
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
            <section data-reveal class="max-w-3xl mx-auto px-margin-mobile md:px-margin-desktop py-16 text-center">
                <a :href="registerHref" class="cta-glow bg-primary text-on-primary px-9 py-4 rounded-full font-label-md text-label-md inline-flex items-center gap-2 hover:bg-[#c94918] hover:scale-[1.03] active:scale-[0.98] transition-all">
                    Sign Up Free <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_forward</span>
                </a>
                <p class="font-label-sm text-label-sm text-on-surface-variant mt-3">Cancel anytime. No hidden fees.</p>
            </section>
        </div>

        <GrowStreamFooter />
    </div>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import GrowStreamHeader from '@/components/GrowStream/GrowStreamHeader.vue';
import GrowStreamFooter from '@/components/GrowStream/GrowStreamFooter.vue';
import { useScrollReveal } from '@/composables/useScrollReveal';
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

const revealRoot = useScrollReveal();
const heroRef = ref<HTMLElement>();

const faqs = [
    { q: 'What is GrowStream?', a: 'GrowStream is a Zambian streaming platform for local creators — movies, comedy, dramas, series, and more, all made locally.' },
    { q: 'How much does it cost?', a: "There's a free tier to get started. Paid plans start at K35/month with more quality and downloads at higher tiers." },
    { q: 'How do I pay?', a: 'Pay easily with MTN, Airtel, or Zamtel Mobile Money — no bank card needed.' },
    { q: 'Can I cancel anytime?', a: 'Yes — cancel anytime from your account settings, no long-term contract.' },
    { q: 'Is there a free plan?', a: 'Yes — the Free tier lets you watch the catalogue with ads at standard quality, no payment required.' },
];

const features = [
    { icon: 'storefront', title: 'Local, by local creators', body: 'Content made for and by Zambians — not a foreign catalogue.' },
    { icon: 'play_circle', title: 'Free to start', body: 'Watch the first episode of any series free. No card required.' },
    { icon: 'account_balance_wallet', title: 'Pay with Mobile Money', body: 'MTN, Airtel, Zamtel — no international card needed.' },
    { icon: 'handshake', title: 'Creators get paid transparently', body: 'Your subscription directly supports the creators you watch.' },
];

const toggleFaq = (i: number) => { openFaq.value = openFaq.value === i ? null : i; };

const formatNumber = (n: number): string => {
    if (n >= 1000000) return (n / 1000000).toFixed(1) + 'M';
    if (n >= 1000) return (n / 1000).toFixed(0) + 'K';
    return n.toString();
};

// Count-up animation for the stats strip (driven by IntersectionObserver)
const counts = ref<Record<string, number>>({ creators: 0, titles: 0 });
let statObserver: IntersectionObserver | null = null;
let statAnimated = false;

const countUp = (target: number, key: string): number => (counts.value[key] ?? 0);

const animateCount = (key: string, target: number) => {
    const duration = 1200;
    const start = performance.now();
    const step = (now: number) => {
        const p = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - p, 3);
        counts.value[key] = Math.round(target * eased);
        if (p < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
};

onMounted(() => {
    document.querySelectorAll<HTMLElement>('[data-reveal]').forEach((el, i) => {
        if (!el.style.animationDelay) el.style.animationDelay = `${Math.min(i * 0.05, 0.6)}s`;
    });

    if (typeof IntersectionObserver !== 'undefined') {
        statObserver = new IntersectionObserver(
            (entries) => {
                if (entries.some((e) => e.isIntersecting) && !statAnimated) {
                    statAnimated = true;
                    animateCount('creators', props.stats.creators);
                    animateCount('titles', props.stats.titles);
                }
            },
            { threshold: 0.4 }
        );
        const statsEl = revealRoot.value?.querySelector('.border-y');
        if (statsEl) statObserver.observe(statsEl);
    }
});

onUnmounted(() => statObserver?.disconnect());

const registerHref = computed(() => {
    const base = route('growstream.register');
    if (typeof window !== 'undefined') {
        const { pathname, search } = window.location;
        const redirect = `${pathname}${search}`;
        if (redirect) return `${base}?redirect=${encodeURIComponent(redirect)}`;
    }
    return base;
});
</script>

<style scoped>
.faq-icon-open { transform: rotate(45deg); }
.poster-card {
    transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.3s ease;
}
.poster-card:hover {
    transform: translateY(-8px) scale(1.04);
    box-shadow: 0 20px 40px -12px rgba(226, 87, 31, 0.35);
}
.feature-card { transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1), border-color 0.3s ease; }
.cta-glow { box-shadow: 0 0 0 1px rgba(226,87,31,.4), 0 10px 40px -10px rgba(226,87,31,.6); transition: box-shadow 0.3s ease, transform 0.3s cubic-bezier(0.22,1,0.36,1), background-color 0.2s ease; }
.cta-glow:hover { box-shadow: 0 0 0 1px rgba(226,87,31,.6), 0 16px 55px -8px rgba(226,87,31,.75); }
.hero-fade { background: linear-gradient(180deg, rgba(20,16,14,.35) 0%, rgba(20,16,14,.85) 65%, #14100e 100%); }
.gs-hero-bg { animation: heroKenBurns 22s ease-in-out infinite alternate; }
@keyframes heroKenBurns {
    from { transform: scale(1) translateY(0); }
    to { transform: scale(1.08) translateY(-1.5%); }
}
.gs-hero-item { opacity: 0; animation: heroRise 0.8s cubic-bezier(0.22, 1, 0.36, 1) forwards; }
@keyframes heroRise {
    from { opacity: 0; transform: translateY(28px); }
    to { opacity: 1; transform: translateY(0); }
}
[data-reveal] {
    opacity: 0;
    transform: translateY(26px);
    transition: opacity 0.7s cubic-bezier(0.22, 1, 0.36, 1), transform 0.7s cubic-bezier(0.22, 1, 0.36, 1);
    transition-delay: var(--reveal-delay, 0s);
}
[data-reveal].is-revealed {
    opacity: 1;
    transform: translateY(0);
}
/* Fade the edge of the horizontally scrolling poster row */
.gs-scroll-fade {
    -webkit-mask-image: linear-gradient(90deg, #000 92%, transparent);
    mask-image: linear-gradient(90deg, #000 92%, transparent);
}
@media (prefers-reduced-motion: reduce) {
    .gs-hero-item, [data-reveal] { opacity: 1 !important; transform: none !important; animation: none !important; transition: none !important; }
    .gs-hero-bg { animation: none !important; }
}
</style>
