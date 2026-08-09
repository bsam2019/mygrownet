<template>
    <div class="flex min-h-screen bg-background text-on-background font-body-md antialiased">

        <!-- PREVIEW & CATEGORY SWITCHER TOGGLE (Interactive Category Selector) -->
        <div class="fixed bottom-4 right-4 z-50 bg-surface-container-highest/95 backdrop-blur border border-outline-variant/60 rounded-2xl p-2.5 shadow-2xl flex flex-col gap-2 max-w-sm">
            <div class="flex items-center justify-between px-1">
                <span class="font-label-sm text-label-sm text-primary font-bold uppercase tracking-wider">Tenant Category</span>
                <div class="flex items-center gap-1">
                    <button
                        @click="viewState = 'new'"
                        :class="['px-2 py-0.5 rounded-full text-[10px] font-bold', viewState === 'new' ? 'bg-primary text-white' : 'bg-surface-container-high text-on-surface-variant']"
                    >New</button>
                    <button
                        @click="viewState = 'established'"
                        :class="['px-2 py-0.5 rounded-full text-[10px] font-bold', viewState === 'established' ? 'bg-primary text-white' : 'bg-surface-container-high text-on-surface-variant']"
                    >Established</button>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-1">
                <button
                    v-for="cat in categoryOptions"
                    :key="cat.category"
                    @click="setCategory(cat.category)"
                    :class="[
                        'px-2.5 py-1.5 rounded-lg font-label-sm text-xs text-left transition-all truncate border',
                        activeCategory === cat.category
                            ? 'bg-primary text-on-primary font-bold border-primary shadow-sm'
                            : 'bg-surface-container-high text-on-surface-variant border-outline-variant/40 hover:text-on-surface'
                    ]"
                >
                    {{ cat.category_name }}
                </button>
            </div>
        </div>

        <!-- Sidebar -->
        <aside class="w-64 bg-surface-container-lowest border-r border-outline-variant/50 hidden md:flex flex-col shrink-0">
            <div class="p-6 border-b border-outline-variant/50 flex items-center justify-between">
                <div class="min-w-0">
                    <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Creator Hub</p>
                    <p class="font-headline-md text-headline-md text-primary truncate">{{ platform.brand_name || 'My Platform' }}</p>
                    <span class="inline-block mt-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-primary/10 text-primary border border-primary/20 uppercase tracking-widest">
                        {{ terminology.category_name }}
                    </span>
                </div>
            </div>

            <nav class="flex-1 px-3 py-4 space-y-5 overflow-y-auto">
                <div>
                    <Link
                        :href="route('growstream.creator.dashboard')"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg bg-primary/15 text-primary font-label-md text-label-md font-bold"
                    >
                        <span class="material-symbols-outlined text-lg">dashboard</span>Overview
                    </Link>
                </div>

                <!-- Content Group -->
                <div>
                    <p class="px-3 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-widest mb-2">Content</p>
                    <Link :href="route('growstream.creator.videos.index')" class="flex items-center gap-3 px-3 py-2 rounded-lg text-on-surface-variant hover:bg-surface-container-high font-label-md text-label-md">
                        <span class="material-symbols-outlined text-lg">{{ terminology.default_content_model === 'course' ? 'school' : 'video_library' }}</span>
                        {{ terminology.content_unit_plural }}
                    </Link>
                </div>

                <!-- Business / Audience Group -->
                <div>
                    <p class="px-3 font-label-sm text-label-sm text-on-surface-variant uppercase tracking-widest mb-2">Management</p>
                    <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg text-on-surface-variant hover:bg-surface-container-high font-label-md text-label-md">
                        <span class="material-symbols-outlined text-lg">group</span>{{ terminology.audience_label }}
                    </a>
                    <Link v-if="terminology.show_revenue" :href="route('growstream.creator.attribution')" class="flex items-center gap-3 px-3 py-2 rounded-lg text-on-surface-variant hover:bg-surface-container-high font-label-md text-label-md">
                        <span class="material-symbols-outlined text-lg">insights</span>Attribution Analytics
                    </Link>
                    <a v-if="terminology.allow_self_serve" href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg text-on-surface-variant hover:bg-surface-container-high font-label-md text-label-md">
                        <span class="material-symbols-outlined text-lg">receipt_long</span>Subscriptions &amp; Orders
                    </a>
                </div>

                <!-- Standalone Hub B2B Section -->
                <div class="pt-2 border-t border-outline-variant/40 space-y-1">
                    <p class="px-3 font-label-sm text-[10px] text-amber-400 font-bold uppercase tracking-widest mb-2 flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">domain</span> Standalone Hub B2B
                    </p>
                    <template v-if="platform && platform.subscription_status === 'active'">
                        <Link :href="route('growstream.creator.platform.show')" class="flex items-center gap-3 px-3 py-2 rounded-lg text-on-surface hover:bg-surface-container-high font-label-md text-label-md font-semibold">
                            <span class="material-symbols-outlined text-lg text-emerald-400">check_circle</span> Hub Platform Settings
                        </Link>
                    </template>
                    <template v-else>
                        <Link :href="route('growstream.hub.subscribe')" class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-amber-500/10 text-amber-300 border border-amber-500/30 hover:bg-amber-500/20 font-label-md text-xs font-bold transition-all">
                            <span class="material-symbols-outlined text-base text-amber-400">rocket_launch</span> Upgrade to Hub Platform
                        </Link>
                    </template>
                </div>
            </nav>

            <div class="p-4 border-t border-outline-variant/50 flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-surface-container-highest flex items-center justify-center text-primary font-bold border border-outline-variant">
                    {{ user.name.charAt(0).toUpperCase() }}
                </div>
                <div class="min-w-0">
                    <p class="font-label-md text-label-md truncate">{{ user.name }}</p>
                    <p class="font-label-sm text-label-sm text-on-surface-variant truncate">{{ terminology.category === 'business' ? 'Org Admin' : 'Academy Owner' }}</p>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6 md:p-10 overflow-y-auto">

            <!-- ============ NEW TENANT STATE ============ -->
            <div v-if="viewState === 'new'" class="max-w-4xl">
                <div class="flex items-center justify-between mb-2">
                    <h1 class="font-headline-lg-mobile text-headline-lg-mobile">Welcome to Creator Hub 👋</h1>
                </div>
                <p class="font-body-md text-body-md text-on-surface-variant mb-8">
                    Setting up {{ platform.brand_name || 'your Creator Hub' }} for <strong>{{ terminology.category_name }}</strong>.
                </p>

                <!-- Setup Progress Bar -->
                <div class="bg-surface-container rounded-xl border border-outline-variant/50 p-6 mb-8 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <p class="font-label-md text-label-md font-bold">Setup progress</p>
                        <p class="font-label-sm text-label-sm text-on-surface-variant">2 of 5 complete</p>
                    </div>
                    <div class="w-full h-2.5 bg-surface-container-high rounded-full overflow-hidden">
                        <div class="h-full bg-primary rounded-full transition-all duration-500" style="width: 40%"></div>
                    </div>
                </div>

                <!-- Category-Adaptive Checklist -->
                <div class="space-y-3">
                    <div class="flex items-center gap-4 bg-surface-container rounded-lg border border-outline-variant/50 p-4">
                        <span class="material-symbols-outlined text-success text-2xl" style="font-variation-settings:'FILL' 1;">check_circle</span>
                        <div class="flex-1">
                            <p class="font-label-md text-label-md font-bold">Creator Hub Created</p>
                            <p class="font-label-sm text-label-sm text-on-surface-variant">{{ platform.subdomain ? `${platform.subdomain}.mygrownet.com` : 'mrbanda.mygrownet.com' }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 bg-surface-container rounded-lg border border-outline-variant/50 p-4">
                        <span class="material-symbols-outlined text-success text-2xl" style="font-variation-settings:'FILL' 1;">check_circle</span>
                        <div class="flex-1">
                            <p class="font-label-md text-label-md font-bold">Category &amp; Branding Configured</p>
                            <p class="font-label-sm text-label-sm text-on-surface-variant">Category: {{ terminology.category_name }}</p>
                        </div>
                    </div>

                    <!-- Active Step -->
                    <div class="flex items-center gap-4 bg-surface-container rounded-lg border border-primary/60 p-4 shadow-md bg-primary/5">
                        <span class="material-symbols-outlined text-primary text-2xl">radio_button_unchecked</span>
                        <div class="flex-1">
                            <p class="font-label-md text-label-md font-bold text-primary">Upload your first {{ terminology.content_unit_label }}</p>
                            <p class="font-label-sm text-label-sm text-on-surface-variant">
                                Add video content tailored for {{ terminology.audience_label.toLowerCase() }}
                            </p>
                        </div>
                        <Link :href="route('growstream.creator.video.upload')" class="bg-primary text-on-primary px-5 py-2.5 rounded-full font-label-sm text-label-sm font-bold shadow-md hover:bg-primary/90">
                            Start Upload
                        </Link>
                    </div>

                    <!-- Step 4: Access Model (Adapted for Business vs Self-serve) -->
                    <div class="flex items-center gap-4 bg-surface-container rounded-lg border border-outline-variant/50 p-4 opacity-60">
                        <span class="material-symbols-outlined text-on-surface-variant text-2xl">radio_button_unchecked</span>
                        <div class="flex-1">
                            <p class="font-label-md text-label-md font-bold">
                                {{ terminology.allow_self_serve ? 'Connect Payment Gateway' : 'Import / Invite ' + terminology.audience_label }}
                            </p>
                            <p class="font-label-sm text-label-sm text-on-surface-variant">
                                {{ terminology.allow_self_serve ? 'Mobile Money or Card checkout for subscribers' : 'Assign ' + terminology.audience_label.toLowerCase() + ' via batch CSV or email invite' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 bg-surface-container rounded-lg border border-outline-variant/50 p-4 opacity-60">
                        <span class="material-symbols-outlined text-on-surface-variant text-2xl">radio_button_unchecked</span>
                        <div class="flex-1">
                            <p class="font-label-md text-label-md font-bold">Publish your platform</p>
                            <p class="font-label-sm text-label-sm text-on-surface-variant">Go live for {{ terminology.audience_label.toLowerCase() }} on your custom domain</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============ ESTABLISHED ACADEMY STATE ============ -->
            <div v-else class="max-w-6xl">
                <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
                    <div>
                        <h1 class="font-headline-lg-mobile text-headline-lg-mobile">Good morning, {{ user.name }}</h1>
                        <p class="font-body-md text-body-md text-on-surface-variant">
                            Here's how {{ platform.brand_name || 'your platform' }} is performing ({{ terminology.category_name }}).
                        </p>
                    </div>
                    <Link :href="route('growstream.creator.video.upload')" class="bg-primary text-on-primary px-5 py-2.5 rounded-full font-label-md text-label-md font-bold flex items-center gap-2 shadow-md hover:bg-primary/90">
                        <span class="material-symbols-outlined text-lg">add</span>New {{ terminology.content_unit_label }}
                    </Link>
                </div>

                <!-- Health Warning Alert -->
                <div v-if="terminology.allow_self_serve" class="bg-warning/10 border border-warning/40 rounded-xl p-4 mb-6 flex items-center gap-3">
                    <span class="material-symbols-outlined text-warning text-xl">warning</span>
                    <p class="font-label-md text-label-md text-on-surface">3 subscription payments require verification this week — <a href="#" class="underline text-warning font-bold">review orders</a></p>
                </div>

                <!-- Dynamic Category-Aware Stat Cards -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <!-- Card 1: Audience Count -->
                    <div class="bg-surface-container rounded-xl border border-outline-variant/50 p-5 shadow-sm">
                        <p class="font-label-sm text-label-sm text-on-surface-variant mb-2">Active {{ terminology.audience_label }}</p>
                        <p class="font-display-sm text-display-sm text-on-surface font-bold">
                            {{ terminology.category === 'business' ? '2,450' : '1,180' }}
                        </p>
                        <p class="font-label-sm text-label-sm text-success mt-1 flex items-center gap-0.5">
                            <span class="material-symbols-outlined text-sm">trending_up</span> 8.2% this month
                        </p>
                    </div>

                    <!-- Card 2: Revenue OR Employee Compliance Count -->
                    <div class="bg-surface-container rounded-xl border border-outline-variant/50 p-5 shadow-sm">
                        <template v-if="terminology.show_revenue">
                            <p class="font-label-sm text-label-sm text-on-surface-variant mb-2">Monthly Revenue</p>
                            <p class="font-display-sm text-display-sm text-primary font-bold">K59,000</p>
                            <p class="font-label-sm text-label-sm text-success mt-1 flex items-center gap-0.5">
                                <span class="material-symbols-outlined text-sm">trending_up</span> 12.4% this month
                            </p>
                        </template>
                        <template v-else>
                            <p class="font-label-sm text-label-sm text-on-surface-variant mb-2">Training Completed</p>
                            <p class="font-display-sm text-display-sm text-primary font-bold">1,890</p>
                            <p class="font-label-sm text-label-sm text-success mt-1 font-bold">Compliance Met</p>
                        </template>
                    </div>

                    <!-- Card 3: Completion Metric -->
                    <div class="bg-surface-container rounded-xl border border-outline-variant/50 p-5 shadow-sm">
                        <p class="font-label-sm text-label-sm text-on-surface-variant mb-2">{{ terminology.completion_metric }}</p>
                        <p class="font-display-sm text-display-sm text-on-surface font-bold">
                            {{ terminology.category === 'business' ? '92%' : '68%' }}
                        </p>
                        <p class="font-label-sm text-label-sm text-on-surface-variant mt-1">
                            {{ terminology.category === 'business' ? 'Mandatory policy modules' : 'Avg across all courses' }}
                        </p>
                    </div>

                    <!-- Card 4: Payout OR Active Modules -->
                    <div class="bg-surface-container rounded-xl border border-outline-variant/50 p-5 shadow-sm">
                        <template v-if="terminology.show_revenue">
                            <p class="font-label-sm text-label-sm text-on-surface-variant mb-2">Pending Payout</p>
                            <p class="font-display-sm text-display-sm text-on-surface font-bold">K12,400</p>
                            <button class="font-label-sm text-label-sm text-primary font-bold mt-1 hover:underline">Request payout →</button>
                        </template>
                        <template v-else>
                            <p class="font-label-sm text-label-sm text-on-surface-variant mb-2">Active Modules</p>
                            <p class="font-display-sm text-display-sm text-on-surface font-bold">18</p>
                            <p class="font-label-sm text-label-sm text-on-surface-variant mt-1">Internal catalog</p>
                        </template>
                    </div>
                </div>

                <!-- Watch Time Chart Card -->
                <div class="bg-surface-container rounded-xl border border-outline-variant/50 p-6 mb-8 shadow-sm">
                    <div class="flex justify-between items-center mb-6">
                        <p class="font-label-md text-label-md font-bold">
                            {{ terminology.category === 'business' ? 'Employee Training Hours' : 'Watch Time (Hours)' }}
                        </p>
                        <span class="bg-surface-container-high px-3 py-1 rounded-full font-label-sm text-label-sm text-on-surface-variant border border-outline-variant/40">Last 7 Days</span>
                    </div>
                    <div class="h-44 flex items-end justify-between gap-3 pt-4">
                        <div class="flex-1 bg-surface-container-high rounded-t hover:bg-primary/50 transition-all relative group" style="height: 45%">
                            <span class="absolute -top-7 left-1/2 -translate-x-1/2 bg-surface-container-highest text-xs px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity">45h</span>
                        </div>
                        <div class="flex-1 bg-primary rounded-t hover:bg-primary/90 transition-all relative group" style="height: 65%">
                            <span class="absolute -top-7 left-1/2 -translate-x-1/2 bg-surface-container-highest text-xs px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity">65h</span>
                        </div>
                        <div class="flex-1 bg-surface-container-high rounded-t hover:bg-primary/50 transition-all relative group" style="height: 30%">
                            <span class="absolute -top-7 left-1/2 -translate-x-1/2 bg-surface-container-highest text-xs px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity">30h</span>
                        </div>
                        <div class="flex-1 bg-primary rounded-t hover:bg-primary/90 transition-all relative group" style="height: 80%">
                            <span class="absolute -top-7 left-1/2 -translate-x-1/2 bg-surface-container-highest text-xs px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity">80h</span>
                        </div>
                        <div class="flex-1 bg-primary rounded-t hover:bg-primary/90 transition-all relative group" style="height: 95%">
                            <span class="absolute -top-7 left-1/2 -translate-x-1/2 bg-surface-container-highest text-xs px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity">95h</span>
                        </div>
                        <div class="flex-1 bg-surface-container-high rounded-t hover:bg-primary/50 transition-all relative group" style="height: 55%">
                            <span class="absolute -top-7 left-1/2 -translate-x-1/2 bg-surface-container-highest text-xs px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity">55h</span>
                        </div>
                        <div class="flex-1 bg-surface-container-high rounded-t hover:bg-primary/50 transition-all relative group" style="height: 40%">
                            <span class="absolute -top-7 left-1/2 -translate-x-1/2 bg-surface-container-highest text-xs px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity">40h</span>
                        </div>
                    </div>
                </div>

                <!-- Most Watched Content Table -->
                <div class="bg-surface-container rounded-xl border border-outline-variant/50 overflow-hidden shadow-sm">
                    <div class="p-5 border-b border-outline-variant/50 flex items-center justify-between">
                        <p class="font-label-md text-label-md font-bold">Top {{ terminology.content_unit_plural }}</p>
                        <Link :href="route('growstream.creator.videos.index')" class="text-primary font-label-sm text-label-sm hover:underline">View all</Link>
                    </div>
                    <div class="divide-y divide-outline-variant/40">
                        <div class="flex items-center justify-between p-4 hover:bg-surface-container-high/50 transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-primary">play_circle</span>
                                <p class="font-label-md text-label-md">
                                    {{ terminology.category === 'business' ? 'Safety Protocol & Workplace Compliance' : 'Algebra Basics — Module 1' }}
                                </p>
                            </div>
                            <p class="font-label-sm text-label-sm text-on-surface-variant font-mono">
                                {{ terminology.category === 'business' ? '540 active employees' : '312 active students' }}
                            </p>
                        </div>
                        <div class="flex items-center justify-between p-4 hover:bg-surface-container-high/50 transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-primary">play_circle</span>
                                <p class="font-label-md text-label-md">
                                    {{ terminology.category === 'business' ? 'Operational Equipment Handling' : 'Functions Explained — Module 2' }}
                                </p>
                            </div>
                            <p class="font-label-sm text-label-sm text-on-surface-variant font-mono">
                                {{ terminology.category === 'business' ? '410 active employees' : '288 active students' }}
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';

interface Platform {
    id: number;
    brand_name: string;
    category?: string;
    subdomain?: string;
    custom_domain?: string;
    brand_color?: string;
}

interface Terminology {
    category: string;
    category_name: string;
    audience_label: string;
    enrollment_action: string;
    content_unit_label: string;
    content_unit_plural: string;
    completion_metric: string;
    show_revenue: boolean;
    show_publishing_destination: boolean;
    allow_self_serve: boolean;
    default_content_model: string;
}

interface User {
    id: number;
    name: string;
    email: string;
}

interface Props {
    user: User;
    platform: Platform;
    terminology: Terminology;
    allCategories?: Terminology[];
    initialViewState?: 'new' | 'established';
}

const props = withDefaults(defineProps<Props>(), {
    initialViewState: 'new',
    allCategories: () => [],
});

const viewState = ref<'new' | 'established'>(props.initialViewState);
const activeCategory = ref(props.platform.category || 'education');
const setupOpen = ref(false);

const categoryOptions = computed(() => props.allCategories || []);
const terminology = computed(() => {
    const found = categoryOptions.value.find(c => c.category === activeCategory.value);
    return found || props.terminology;
});

const setCategory = (cat: string) => {
    activeCategory.value = cat;
    router.put(route('growstream.creator.platform.update'), {
        category: cat
    }, {
        preserveScroll: true,
        preserveState: true,
    });
};
</script>
