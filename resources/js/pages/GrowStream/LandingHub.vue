<template>
    <div class="min-h-screen bg-[#110e0c] text-on-surface antialiased font-body-md selection:bg-primary selection:text-on-primary">
        <Head title="GrowStream Hub - Own Your Branded Video & Online Tuition Platform" />

        <!-- Header -->
        <header class="sticky top-0 z-50 bg-[#18120e]/90 backdrop-blur-xl border-b border-outline-variant/40">
            <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="route('growstream.home')" class="font-headline-lg text-2xl font-black text-primary tracking-tight">GrowStream <span class="text-on-surface font-light text-xl">Hub</span></Link>
                </div>

                <nav class="hidden md:flex items-center gap-8 font-label-md text-sm text-on-surface-variant">
                    <a href="#features" class="hover:text-on-surface transition-colors">Features</a>
                    <a href="#solutions" class="hover:text-on-surface transition-colors">Solutions</a>
                    <a href="#pricing" class="hover:text-on-surface transition-colors">Pricing</a>
                </nav>

                <div class="flex items-center gap-4">
                    <Link :href="route('growstream.creator.platform.show')" class="bg-primary text-on-primary px-5 py-2.5 rounded-full font-label-md text-sm font-semibold hover:bg-[#c94918] transition-all shadow-lg shadow-primary/20">
                        Launch Your Hub
                    </Link>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="relative py-24 md:py-32 overflow-hidden">
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-primary/20 via-[#18120e]/50 to-[#110e0c] pointer-events-none"></div>

            <div class="max-w-5xl mx-auto px-6 text-center relative z-10">
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-primary/10 border border-primary/30 text-primary text-xs font-semibold uppercase tracking-widest mb-6">
                    <span class="material-symbols-outlined text-sm">rocket_launch</span> B2B Creator &amp; Tuition Platform Engine
                </span>

                <h1 class="font-display-lg text-4xl md:text-6xl font-black tracking-tight text-on-surface leading-tight mb-6">
                    Launch Your Own <span class="bg-gradient-to-r from-primary via-orange-400 to-amber-300 bg-clip-text text-transparent">Branded Video &amp; Tuition Platform</span>
                </h1>

                <p class="font-body-lg text-lg md:text-xl text-on-surface-variant max-w-3xl mx-auto mb-10 leading-relaxed">
                    Operate your independent video academy or studio under your custom domain (<code class="bg-surface-container-high px-2 py-0.5 rounded text-primary font-mono text-base">www.yourname.com</code>), publish private tuition lectures, and receive client payments directly.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <Link :href="route('growstream.creator.platform.show')" class="w-full sm:w-auto bg-primary text-on-primary px-8 py-4 rounded-full font-label-md text-base font-bold hover:bg-[#c94918] transition-all shadow-xl shadow-primary/25">
                        Start Your Free Hub
                    </Link>
                    <a href="#features" class="w-full sm:w-auto bg-surface-container-high/60 text-on-surface px-8 py-4 rounded-full font-label-md text-base font-semibold border border-outline-variant hover:bg-surface-container-high transition-all">
                        Explore Platform Features
                    </a>
                </div>
            </div>
        </section>

        <!-- Features Grid -->
        <section id="features" class="py-20 bg-[#16120f] border-t border-b border-outline-variant/40">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center max-w-2xl mx-auto mb-16">
                    <h2 class="font-headline-lg text-3xl md:text-4xl font-extrabold text-on-surface mb-3">Built for Educators, Tutors &amp; Independent Studios</h2>
                    <p class="font-body-md text-on-surface-variant text-base">Everything you need to deliver high-quality video courses without building infrastructure from scratch.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="bg-surface-container/60 p-8 rounded-3xl border border-outline-variant/60 hover:border-primary/50 transition-all duration-300 hover:-translate-y-1">
                        <div class="w-14 h-14 rounded-2xl bg-primary/10 text-primary flex items-center justify-center mb-6">
                            <span class="material-symbols-outlined text-3xl">language</span>
                        </div>
                        <h3 class="font-headline-sm text-xl font-bold text-on-surface mb-2">Custom Domain &amp; Branding</h3>
                        <p class="font-body-md text-on-surface-variant text-sm leading-relaxed">
                            Connect your custom domain (<code class="text-primary font-mono text-xs">www.acmeacademy.com</code>) or hosted subdomain with your own logo and brand accent colors.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="bg-surface-container/60 p-8 rounded-3xl border border-outline-variant/60 hover:border-primary/50 transition-all duration-300 hover:-translate-y-1">
                        <div class="w-14 h-14 rounded-2xl bg-primary/10 text-primary flex items-center justify-center mb-6">
                            <span class="material-symbols-outlined text-3xl">lock</span>
                        </div>
                        <h3 class="font-headline-sm text-xl font-bold text-on-surface mb-2">Private Tuition &amp; Course Scoping</h3>
                        <p class="font-body-md text-on-surface-variant text-sm leading-relaxed">
                            Publish private course videos exclusively accessible to your enrolled students, while streaming public teasers to attract new clients.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="bg-surface-container/60 p-8 rounded-3xl border border-outline-variant/60 hover:border-primary/50 transition-all duration-300 hover:-translate-y-1">
                        <div class="w-14 h-14 rounded-2xl bg-primary/10 text-primary flex items-center justify-center mb-6">
                            <span class="material-symbols-outlined text-3xl">account_balance_wallet</span>
                        </div>
                        <h3 class="font-headline-sm text-xl font-bold text-on-surface mb-2">Bring Your Own Payment (BYOP)</h3>
                        <p class="font-body-md text-on-surface-variant text-sm leading-relaxed">
                            Connect your Paystack, Flutterwave, or local gateway API keys so student tuition fees land directly into your own bank account.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section id="pricing" class="py-24 max-w-7xl mx-auto px-6">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <h2 class="font-headline-lg text-3xl md:text-4xl font-extrabold text-on-surface mb-3">Transparent Quota Pricing</h2>
                <p class="font-body-md text-on-surface-variant text-base">Simple plans that scale as your video storage and student audience grows.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div
                    v-for="tier in pricingTiers"
                    :key="tier.name"
                    :class="[
                        'rounded-3xl p-8 flex flex-col justify-between border transition-all duration-300 relative',
                        tier.is_popular ? 'bg-surface-container border-primary shadow-2xl shadow-primary/10 scale-105' : 'bg-surface-container/50 border-outline-variant/60 hover:border-outline-variant'
                    ]"
                >
                    <span v-if="tier.is_popular" class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-primary text-on-primary px-4 py-1 rounded-full text-xs font-bold uppercase tracking-wider">Most Popular</span>

                    <div>
                        <h3 class="font-headline-sm text-xl font-bold text-on-surface mb-1">{{ tier.name }}</h3>
                        <p class="font-body-md text-xs text-on-surface-variant mb-6">{{ tier.description }}</p>

                        <div class="mb-6">
                            <span class="font-display-md text-4xl font-black text-on-surface">{{ tier.price }}</span>
                            <span class="font-body-md text-xs text-on-surface-variant ml-1">{{ tier.period }}</span>
                        </div>

                        <ul class="space-y-3 font-body-md text-xs text-on-surface-variant mb-8">
                            <li class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary text-sm">check_circle</span> {{ tier.storage }}
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary text-sm">check_circle</span> {{ tier.bandwidth }}
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary text-sm">check_circle</span> {{ tier.domain }}
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary text-sm">check_circle</span> {{ tier.byop ? 'BYOP Direct Settlement' : 'Platform Payment Settlement' }}
                            </li>
                        </ul>
                    </div>

                    <Link :href="route('growstream.creator.platform.show')" class="w-full text-center bg-primary text-on-primary py-3 rounded-full font-label-md text-sm font-bold hover:bg-[#c94918] transition-colors">
                        {{ tier.cta }}
                    </Link>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-outline-variant/40 py-8 bg-[#0e0b09] text-center text-xs text-on-surface-variant">
            <div class="flex items-center justify-center gap-6 mb-3 font-label-sm">
                <Link :href="route('growstream.pages.terms')" class="hover:text-primary transition-colors">Terms &amp; Platform Policies</Link>
                <span>•</span>
                <Link :href="route('growstream.pages.privacy')" class="hover:text-primary transition-colors">Privacy &amp; Data Protection</Link>
                <span>•</span>
                <Link :href="route('growstream.pages.help')" class="hover:text-primary transition-colors">Support &amp; Help Center</Link>
            </div>
            <p>&copy; {{ new Date().getFullYear() }} GrowStream Hub — Powered by MyGrowNet Platform Core.</p>
        </footer>
    </div>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

interface Props {
    appName?: string;
    pricingTiers?: any[];
}

defineProps<Props>();
</script>
