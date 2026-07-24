<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref, onMounted } from 'vue';

interface TemplateCard {
    id: number;
    name: string;
    description: string;
    industry: string;
    thumbnail: string;
    isPremium: boolean;
}

const isSubdomain = computed(() => window.location.hostname === 'growbuilder.mygrownet.com');
const templates = ref<TemplateCard[]>([]);
const templatesLoading = ref(true);

onMounted(async () => {
    try {
        const res = await fetch('/templates-test.php');
        const json = await res.json();
        if (json.success && json.templates) {
            templates.value = json.templates;
        }
    } catch {
        // fallback — templates stays empty
    } finally {
        templatesLoading.value = false;
    }
});
const scrolled = ref(false);
const mobileMenuOpen = ref(false);
const pricingUrl = computed(() => isSubdomain.value ? '/pricing' : '/growbuilder/pricing');

onMounted(() => {
    const onScroll = () => { scrolled.value = window.scrollY > 20; };
    window.addEventListener('scroll', onScroll, { passive: true });
});
</script>

<template>
    <Head>
        <title>GrowBuilder — Professional Website Builder for Zambian Businesses</title>
        <meta name="description" content="Build professional websites for your Zambian business with GrowBuilder. Drag-and-drop builder, e-commerce, custom domains, and SEO tools." />
    </Head>

    <div class="min-h-screen bg-white text-gray-900 font-sans antialiased">
        <!-- ===== MINIMAL HEADER — NO CENTERED NAV ===== -->
        <header
            :class="[
                'fixed top-0 left-0 right-0 z-50 transition-all duration-500',
                scrolled ? 'bg-white/80 backdrop-blur-lg shadow-[0_1px_0_rgba(0,0,0,0.06)]' : 'bg-transparent'
            ]"
        >
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16 lg:h-18">
                    <!-- Logo — just the icon mark, no text clutter -->
                    <Link :href="isSubdomain ? '/' : '/growbuilder'" class="flex items-center gap-3 group">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-600 to-blue-700 flex items-center justify-center shadow-md shadow-blue-600/15">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
                        </div>
                        <span class="text-base font-extrabold tracking-tight text-gray-900">GrowBuilder</span>
                    </Link>

                    <!-- Right side: nav + auth, all pushed to the right -->
                    <div class="flex items-center gap-5">
                        <div class="hidden lg:flex items-center gap-1">
                            <a href="#features" class="px-3 py-1.5 text-sm font-medium text-gray-500 hover:text-gray-800 rounded-md hover:bg-gray-100/60 transition-colors">Features</a>
                            <a href="#templates" class="px-3 py-1.5 text-sm font-medium text-gray-500 hover:text-gray-800 rounded-md hover:bg-gray-100/60 transition-colors">Templates</a>
                            <Link :href="pricingUrl" class="px-3 py-1.5 text-sm font-medium text-gray-500 hover:text-gray-800 rounded-md hover:bg-gray-100/60 transition-colors">Pricing</Link>
                        </div>
                        <div class="flex items-center gap-3">
                            <Link href="/login" class="hidden sm:inline text-sm font-medium text-gray-500 hover:text-gray-800 transition-colors">
                                Sign In
                            </Link>
                        <Link
                            href="/register"
                            class="relative inline-flex items-center gap-2 px-4 py-2 text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all shadow-md shadow-blue-600/20 hover:shadow-blue-600/35"
                        >
                            Get Started Free
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                        </Link>
                        <!-- Mobile hamburger -->
                        <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden p-2 -mr-2 rounded-lg hover:bg-gray-100 transition-colors" aria-label="Menu">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                        </button>
                    </div>
                    </div><!-- end right-side wrapper -->
                </div>
            </div>

            <!-- Mobile dropdown — full width overlay style -->
            <div v-if="mobileMenuOpen" class="lg:hidden border-t border-gray-100 bg-white">
                <div class="px-4 py-5 space-y-1">
                    <a href="#features" @click="mobileMenuOpen = false" class="block px-4 py-3 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-50">Features</a>
                    <a href="#templates" @click="mobileMenuOpen = false" class="block px-4 py-3 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-50">Templates</a>
                    <Link :href="pricingUrl" @click="mobileMenuOpen = false" class="block px-4 py-3 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-50">Pricing</Link>
                    <Link href="/login" @click="mobileMenuOpen = false" class="block px-4 py-3 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-50">Sign In</Link>
                    <Link href="/register" @click="mobileMenuOpen = false" class="block px-4 py-3 text-sm font-bold text-white bg-blue-600 rounded-lg text-center mt-3">Get Started Free</Link>
                </div>
            </div>
        </header>

        <!-- ===== HERO ===== -->
        <section class="relative min-h-screen flex items-center overflow-hidden pt-20">
            <div class="absolute inset-0 bg-gradient-to-b from-blue-50/40 via-white to-white pointer-events-none"></div>
            <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-gradient-to-bl from-blue-100/50 to-transparent rounded-full blur-3xl -translate-y-1/3 translate-x-1/3 pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-gradient-to-tr from-blue-50/60 to-transparent rounded-full blur-3xl translate-y-1/3 -translate-x-1/4 pointer-events-none"></div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
                <div class="grid lg:grid-cols-2 gap-16 lg:gap-20 items-center">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-blue-50 border border-blue-200/60 text-blue-700 text-xs font-semibold mb-7 shadow-sm">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                            </span>
                            Now available — Website Builder for Zambia
                        </div>

                        <h1 class="text-5xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight leading-[1.05] text-gray-900">
                            Build Your Business
                            <span class="block text-transparent bg-clip-text bg-gradient-to-r from-blue-600 via-blue-500 to-blue-400">Online</span>
                        </h1>

                        <p class="mt-6 text-lg sm:text-xl text-gray-500 leading-relaxed max-w-lg">
                            Create a professional website in minutes. Drag, drop, and publish — no coding needed.
                        </p>

                        <div class="mt-10 flex flex-col sm:flex-row gap-4">
                            <Link
                                href="/register"
                                class="relative inline-flex items-center justify-center gap-2.5 px-8 py-4 text-base font-bold text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all shadow-xl shadow-blue-600/25 hover:shadow-blue-600/40 group"
                            >
                                Build Your Site Free
                                <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                            </Link>
                            <Link
                                :href="pricingUrl"
                                class="inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-all"
                            >
                                View Plans
                            </Link>
                        </div>

                        <div class="mt-12 flex flex-wrap items-center gap-x-8 gap-y-3">
                            <div v-for="item in ['Free to start', 'No coding', 'Mobile Money ready', 'Custom domain']" :key="item" class="flex items-center gap-2 text-sm text-gray-500">
                                <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                {{ item }}
                            </div>
                        </div>
                    </div>

                    <!-- Hero visual -->
                    <div class="hidden lg:block relative">
                        <div class="relative">
                            <div class="absolute -inset-4 bg-gradient-to-br from-blue-500/20 to-blue-600/10 rounded-3xl blur-2xl"></div>
                            <div class="relative bg-white rounded-2xl shadow-2xl border border-gray-200/60 overflow-hidden">
                                <div class="h-7 bg-gray-50 border-b border-gray-200 flex items-center px-4 gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-red-400"></span>
                                    <span class="w-2.5 h-2.5 rounded-full bg-yellow-400"></span>
                                    <span class="w-2.5 h-2.5 rounded-full bg-green-400"></span>
                                    <div class="ml-3 h-4 flex-1 max-w-[160px] bg-white rounded border border-gray-200 flex items-center px-2">
                                        <span class="text-[7px] text-gray-400 font-medium truncate">myshop.mygrownet.com</span>
                                    </div>
                                </div>
                                <div class="p-5 space-y-3.5">
                                    <div class="h-3.5 bg-gradient-to-r from-blue-500 to-blue-600 rounded w-3/5"></div>
                                    <div class="h-2 bg-gray-100 rounded w-full"></div>
                                    <div class="h-2 bg-gray-100 rounded w-4/5"></div>
                                    <div class="flex gap-3 pt-1">
                                        <div class="flex-1 h-14 bg-gray-50 rounded-lg border border-gray-100"></div>
                                        <div class="flex-1 h-14 bg-gray-50 rounded-lg border border-gray-100"></div>
                                        <div class="flex-1 h-14 bg-gray-50 rounded-lg border border-gray-100"></div>
                                    </div>
                                    <div class="h-7 bg-blue-600 rounded-lg w-1/3"></div>
                                </div>
                            </div>
                        </div>
                        <!-- Floating badge -->
                        <div class="absolute -bottom-3 -right-3 bg-white rounded-xl shadow-lg border border-gray-200 px-4 py-2.5 flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-900">Go Live in Minutes</p>
                                <p class="text-[10px] text-gray-500">No coding required</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== TRUST BAR ===== -->
        <section class="border-t border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <p class="text-center text-xs font-semibold uppercase tracking-[0.2em] text-gray-400 mb-8">Trusted by Zambian businesses</p>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 items-center justify-items-center">
                    <div class="text-center">
                        <p class="text-3xl font-extrabold text-gray-900">1,000+</p>
                        <p class="text-sm text-gray-500 mt-1">Websites Built</p>
                    </div>
                    <div class="text-center">
                        <p class="text-3xl font-extrabold text-gray-900">50+</p>
                        <p class="text-sm text-gray-500 mt-1">Templates</p>
                    </div>
                    <div class="text-center">
                        <p class="text-3xl font-extrabold text-gray-900">200+</p>
                        <p class="text-sm text-gray-500 mt-1">Online Stores</p>
                    </div>
                    <div class="text-center">
                        <p class="text-3xl font-extrabold text-gray-900">100%</p>
                        <p class="text-sm text-gray-500 mt-1">No Code Required</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== FEATURES ===== -->
        <section id="features" class="py-20 lg:py-28 bg-gray-50/40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto mb-16">
                    <span class="text-xs font-semibold uppercase tracking-[0.15em] text-blue-600">Features</span>
                    <h2 class="mt-3 text-3xl sm:text-4xl font-extrabold tracking-tight text-gray-900">Everything You Need to Grow Online</h2>
                    <p class="mt-4 text-lg text-gray-500">Powerful tools designed for Zambian businesses — no expertise required.</p>
                </div>
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="f in [
                        { icon: 'M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10', title: 'Drag & Drop Builder', desc: 'Build beautiful websites without writing a single line of code. Intuitive, fast, and flexible.' },
                        { icon: 'M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z', title: 'E-Commerce Ready', desc: 'Add products, accept mobile money payments, and manage orders — all from one dashboard.' },
                        { icon: 'M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418', title: 'Free Subdomain', desc: 'Get started instantly with yourname.mygrownet.com. Add a custom domain anytime.' },
                        { icon: 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 012.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z', title: 'Pro Templates', desc: 'Start with templates designed for shops, restaurants, services, and more.' },
                        { icon: 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z', title: 'SEO Tools', desc: 'Built-in SEO helps customers find you on Google. Meta tags, sitemaps, and analytics.' },
                        { icon: 'M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z', title: 'AI Section Generator', desc: 'Describe what you want and AI builds it. Headlines, layouts, images — generated instantly.' },
                    ]" :key="f.title" class="group p-6 sm:p-7 rounded-2xl bg-white border border-gray-200 hover:border-blue-200 hover:shadow-xl transition-all duration-300">
                        <div class="w-11 h-11 rounded-xl bg-blue-100 flex items-center justify-center mb-4 group-hover:bg-blue-200/60 transition-colors">
                            <svg class="w-5.5 h-5.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" :d="f.icon" /></svg>
                        </div>
                        <h3 class="text-base font-bold text-gray-900 mb-2 group-hover:text-blue-700 transition-colors">{{ f.title }}</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">{{ f.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== TEMPLATES SHOWCASE (real screenshots) ===== -->
        <section id="templates" class="py-20 lg:py-28">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto mb-14">
                    <span class="text-xs font-semibold uppercase tracking-[0.15em] text-blue-600">Templates</span>
                    <h2 class="mt-3 text-3xl sm:text-4xl font-extrabold tracking-tight text-gray-900">Start from a Template</h2>
                    <p class="mt-4 text-lg text-gray-500">Professionally designed for every industry — hit the ground running.</p>
                </div>

                <div v-if="templatesLoading" class="flex justify-center py-12">
                    <div class="w-6 h-6 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                </div>

                <div v-else-if="templates.length" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-5xl mx-auto">
                    <div v-for="t in templates" :key="t.id" class="group rounded-2xl bg-white border border-gray-200 overflow-hidden hover:shadow-xl hover:border-blue-300 transition-all duration-300">
                        <div class="h-48 bg-gray-100 relative overflow-hidden">
                            <img :src="t.thumbnail" :alt="t.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy" />
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors flex items-center justify-center">
                                <span class="text-white text-sm font-semibold opacity-0 group-hover:opacity-100 transition-opacity bg-white/20 backdrop-blur-sm px-5 py-2 rounded-lg">Preview</span>
                            </div>
                            <div v-if="t.isPremium" class="absolute top-3 right-3 bg-amber-400 text-amber-900 text-[10px] font-bold px-2 py-0.5 rounded-full shadow">PREMIUM</div>
                        </div>
                        <div class="p-4">
                            <span class="text-[10px] font-medium text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full capitalize">{{ t.industry }}</span>
                            <h4 class="font-bold text-gray-900 text-sm mt-2">{{ t.name }}</h4>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-12">
                    <p class="text-gray-400">Templates coming soon.</p>
                </div>

                <div class="text-center mt-10">
                    <Link :href="pricingUrl" class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                        See all templates &amp; start building
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                    </Link>
                </div>
            </div>
        </section>

        <!-- ===== BUILDING FOR ZAMBIA ===== -->
        <section class="py-20 bg-gray-50/60 border-t border-gray-100">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-blue-100 flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" /></svg>
                </div>
                <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-gray-900">Built for Zambia</h2>
                <p class="mt-4 text-lg text-gray-500 max-w-2xl mx-auto">
                    Mobile Money payments, local hosting, Zambian Kwacha pricing — everything you need to take your business online.
                </p>
            </div>
        </section>

        <!-- ===== CTA ===== -->
        <section class="py-20 lg:py-28 bg-gradient-to-br from-blue-600 via-blue-700 to-blue-800 relative overflow-hidden">
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(255,255,255,0.12)_0%,transparent_60%)] pointer-events-none"></div>
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-white/5 rounded-full blur-[100px] pointer-events-none"></div>
            <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-white">Ready to Build Your Website?</h2>
                <p class="mt-5 text-lg text-blue-200 max-w-xl mx-auto">Join hundreds of Zambian businesses. Start free — no credit card required.</p>
                <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center">
                    <Link href="/register" class="relative inline-flex items-center justify-center gap-2.5 px-10 py-4 text-base font-bold text-blue-700 bg-white hover:bg-blue-50 rounded-xl transition-all shadow-xl shadow-black/10 hover:shadow-black/20 group">
                        Create Your Free Site
                        <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                    </Link>
                    <Link :href="pricingUrl" class="inline-flex items-center justify-center gap-2 px-10 py-4 text-base font-semibold text-white/90 bg-white/10 hover:bg-white/20 rounded-xl transition-all border border-white/20">
                        Compare Plans
                    </Link>
                </div>
            </div>
        </section>

        <!-- ===== FOOTER ===== -->
        <footer class="bg-gray-950 text-gray-400 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-600 to-blue-700 flex items-center justify-center shadow-lg">
                            <svg class="w-4.5 h-4.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
                        </div>
                        <div>
                            <span class="text-sm font-bold text-white">GrowBuilder</span>
                            <span class="text-[10px] text-gray-600 ml-2">by MyGrowNet</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-8 text-sm">
                        <Link :href="pricingUrl" class="hover:text-white transition-colors">Pricing</Link>
                        <Link :href="isSubdomain ? '/privacy' : '/growbuilder/privacy'" class="hover:text-white transition-colors">Privacy</Link>
                        <Link :href="isSubdomain ? '/terms' : '/growbuilder/terms'" class="hover:text-white transition-colors">Terms</Link>
                        <a href="/contact" class="hover:text-white transition-colors">Contact</a>
                    </div>
                </div>
                <div class="mt-10 pt-8 border-t border-gray-800 text-center text-xs text-gray-600">
                    &copy; {{ new Date().getFullYear() }} MyGrowNet. All rights reserved.
                </div>
            </div>
        </footer>
    </div>
</template>
