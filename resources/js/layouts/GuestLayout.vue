<script setup>
import Navigation from '@/components/custom/Navigation.vue';
import Footer from '@/components/custom/Footer.vue';
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Navigation -->
        <Navigation />

        <!-- Main Content -->
        <main>
            <slot />
        </main>

        <!-- Footer -->
        <Footer />
    </div>
</template>
