<template>
    <PortalLayout :platform="platform">
        <!-- Hero Banner -->
        <section class="mb-10 relative rounded-2xl overflow-hidden aspect-[21/9] bg-surface-container-highest">
            <div
                v-if="platform?.banner_url"
                class="bg-cover bg-center absolute inset-0"
                :style="{ backgroundImage: `url('${platform.banner_url}')` }"
            ></div>
            <div v-else class="absolute inset-0 bg-gradient-to-r from-primary/30 to-background flex items-center p-8 md:p-12">
                <div class="max-w-xl">
                    <span class="bg-primary/20 text-primary px-3 py-1 rounded-full font-label-sm text-xs uppercase tracking-widest font-semibold mb-3 inline-block">Official Platform</span>
                    <h1 class="font-display-lg text-display-lg font-extrabold text-on-surface mb-2">{{ platform?.brand_name || 'Welcome to Our Platform' }}</h1>
                    <p class="font-body-md text-body-md text-on-surface-variant">Explore private tuitions, exclusive courses, and video streaming titles directly from our academy.</p>
                </div>
            </div>
        </section>

        <!-- Course / Private Video Catalogue -->
        <section>
            <h2 class="font-headline-md text-headline-md font-bold mb-6 text-on-surface">Available Courses &amp; Videos</h2>

            <div v-if="videos && videos.length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <VideoCard
                    v-for="v in videos"
                    :key="v.id"
                    :video="v"
                />
            </div>

            <!-- Empty Portal State -->
            <div v-else class="text-center py-16 bg-surface-container rounded-2xl border border-outline-variant/60">
                <span class="material-symbols-outlined text-4xl text-on-surface-variant mb-2">video_library</span>
                <h3 class="font-headline-sm text-headline-sm text-on-surface font-semibold">Catalogue Coming Soon</h3>
                <p class="font-body-md text-body-md text-on-surface-variant max-w-sm mx-auto mt-1">Check back shortly for upcoming private tuitions and course video uploads.</p>
            </div>
        </section>
    </PortalLayout>
</template>

<script setup lang="ts">
import PortalLayout from '@/Layouts/GrowStream/PortalLayout.vue';
import VideoCard from '@/Components/GrowStream/VideoCard.vue';

interface Props {
    platform?: any;
    videos?: any[];
}

withDefaults(defineProps<Props>(), {
    platform: () => ({}),
    videos: () => [],
});
</script>
