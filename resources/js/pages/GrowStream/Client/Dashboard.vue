<template>
    <GrowStreamLayout :title="`${platform.brand_name || 'Academy'} - Client Portal`">
        <div class="mx-auto max-w-7xl py-6 space-y-8">
            <!-- Platform Brand Banner -->
            <div class="rounded-3xl bg-gradient-to-r from-neutral-900 via-surface-container to-neutral-900 p-8 border border-outline-variant/60 shadow-xl flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-5">
                    <div class="w-16 h-16 rounded-2xl bg-primary/10 border border-primary/30 flex items-center justify-center text-primary font-black text-2xl">
                        {{ (platform.brand_name || 'A').charAt(0).toUpperCase() }}
                    </div>
                    <div>
                        <span class="text-xs uppercase tracking-widest font-semibold text-primary">Student &amp; Client Portal</span>
                        <h1 class="text-3xl font-extrabold text-on-surface">{{ platform.brand_name || 'Tuition Academy' }}</h1>
                        <p class="text-xs text-on-surface-variant mt-1">Welcome back, <span class="text-on-surface font-semibold">{{ user.name }}</span>! Continue your learning journey below.</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 shrink-0">
                    <span :class="[
                        'px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider',
                        subscriptionActive ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-amber-500/20 text-amber-400 border border-amber-500/30'
                    ]">
                        {{ subscriptionActive ? 'Enrolled Member' : 'Guest / Inactive Pass' }}
                    </span>
                    <Link
                        v-if="!subscriptionActive"
                        :href="route('growstream.subscription')"
                        class="px-5 py-2.5 rounded-full bg-primary text-on-primary text-xs font-bold hover:bg-[#c94918] transition-colors"
                    >
                        Upgrade Pass
                    </Link>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-surface-container rounded-2xl p-5 border border-outline-variant/60">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-on-surface-variant font-label-md">{{ terminology.content_unit_plural || 'Enrolled Courses' }}</p>
                            <p class="text-2xl font-black text-on-surface mt-1">{{ enrolledCount }}</p>
                        </div>
                        <span class="material-symbols-outlined text-primary text-3xl">school</span>
                    </div>
                </div>

                <div class="bg-surface-container rounded-2xl p-5 border border-outline-variant/60">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-on-surface-variant font-label-md">Completed Lessons</p>
                            <p class="text-2xl font-black text-emerald-400 mt-1">{{ completedLessonsCount }}</p>
                        </div>
                        <span class="material-symbols-outlined text-emerald-400 text-3xl">task_alt</span>
                    </div>
                </div>

                <div class="bg-surface-container rounded-2xl p-5 border border-outline-variant/60">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-on-surface-variant font-label-md">Resource Downloads</p>
                            <p class="text-2xl font-black text-amber-400 mt-1">{{ resourcesCount }} Files</p>
                        </div>
                        <span class="material-symbols-outlined text-amber-400 text-3xl">folder_zip</span>
                    </div>
                </div>
            </div>

            <!-- Enrolled Courses / Content Grid -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-on-surface flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">play_circle</span>
                        My {{ terminology.content_unit_plural || 'Enrolled Courses' }}
                    </h2>
                    <Link :href="route('growstream.browse')" class="text-xs text-primary font-semibold hover:underline">
                        Explore Full Academy Catalogue →
                    </Link>
                </div>

                <div v-if="enrolledVideos.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="item in enrolledVideos"
                        :key="item.id"
                        class="bg-surface-container rounded-2xl border border-outline-variant/60 overflow-hidden shadow-lg flex flex-col group hover:border-primary/50 transition-colors"
                    >
                        <div class="relative aspect-video bg-black">
                            <div class="bg-cover bg-center w-full h-full absolute inset-0 group-hover:scale-105 transition-transform duration-300" :style="{ backgroundImage: `url('${item.thumbnail_url || fallbackThumb}')` }"></div>
                            <div class="absolute bottom-0 left-0 h-1.5 bg-primary" :style="{ width: `${item.progress_percentage || 0}%` }"></div>
                            <span class="absolute bottom-2 right-2 bg-black/80 text-white text-[10px] px-2 py-0.5 rounded font-mono font-bold">{{ formatDuration(item.duration) }}</span>
                        </div>

                        <div class="p-5 flex-1 flex flex-col justify-between space-y-3">
                            <div>
                                <span class="text-[10px] uppercase font-bold tracking-wider text-primary">{{ item.category || 'Tuition Lecture' }}</span>
                                <h3 class="font-bold text-sm text-on-surface mt-1 line-clamp-2">{{ item.title }}</h3>
                                <p class="text-xs text-on-surface-variant line-clamp-2 mt-1">{{ item.description }}</p>
                            </div>

                            <div class="pt-3 border-t border-outline-variant/40 flex items-center justify-between">
                                <span class="text-[11px] text-on-surface-variant font-mono">{{ item.progress_percentage || 0 }}% completed</span>
                                <Link
                                    :href="route('growstream.video.detail', { slug: item.slug })"
                                    class="px-4 py-1.5 rounded-full bg-primary text-on-primary text-xs font-bold hover:bg-[#c94918] transition-colors flex items-center gap-1"
                                >
                                    <span class="material-symbols-outlined text-xs">play_arrow</span> Resume
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-surface-container rounded-3xl p-12 text-center border border-outline-variant/60">
                    <span class="material-symbols-outlined text-5xl text-primary mb-3">auto_stories</span>
                    <h3 class="text-lg font-bold text-on-surface">No Enrolled {{ terminology.content_unit_plural || 'Courses' }} Yet</h3>
                    <p class="text-xs text-on-surface-variant max-w-md mx-auto mt-1 mb-6">
                        Browse our {{ platform.brand_name || 'academy' }} catalogue to enroll in your first {{ terminology.content_unit_label || 'course' }}.
                    </p>
                    <Link :href="route('growstream.browse')" class="px-6 py-3 rounded-full bg-primary text-on-primary text-xs font-bold hover:bg-[#c94918] transition-colors">
                        Browse Catalogue
                    </Link>
                </div>
            </div>

            <!-- Student Support & Tutor Announcements -->
            <div class="bg-surface-container rounded-3xl p-6 border border-outline-variant/60 flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="space-y-1 text-center md:text-left">
                    <h3 class="font-bold text-base text-on-surface">Need Help with Your Tuition Courses?</h3>
                    <p class="text-xs text-on-surface-variant">Contact your tutor directly or reach out to {{ platform.brand_name || 'Academy' }} student support.</p>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <Link :href="route('growstream.pages.help')" class="px-5 py-2.5 rounded-full bg-surface-container-high text-on-surface text-xs font-bold border border-outline-variant/60 hover:bg-surface-container-highest transition-colors">
                        Student Help Center
                    </Link>
                </div>
            </div>
        </div>
    </GrowStreamLayout>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import GrowStreamLayout from '@/Layouts/GrowStreamLayout.vue';

interface Platform {
    id: number;
    brand_name: string;
    subdomain: string;
    category: string;
}

interface Terminology {
    audience_label: string;
    content_unit_label: string;
    content_unit_plural: string;
    completion_metric: string;
}

interface EnrolledVideo {
    id: number;
    title: string;
    slug: string;
    description: string;
    thumbnail_url: string;
    duration: number;
    category: string;
    progress_percentage: number;
}

interface Props {
    user: { id: number; name: string; email: string };
    platform: Platform;
    terminology: Terminology;
    subscriptionActive: boolean;
    enrolledVideos: EnrolledVideo[];
    enrolledCount: number;
    completedLessonsCount: number;
    resourcesCount: number;
}

defineProps<Props>();

const fallbackThumb = 'https://placehold.co/440x248/e1bfb4/191c1d?text=Course';

const formatDuration = (seconds?: number): string => {
    if (!seconds) return '0:00';
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;
    return `${m}:${s.toString().padStart(2, '0')}`;
};
</script>
