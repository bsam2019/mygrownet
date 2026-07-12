<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ZamStayLayout from '@/layouts/ZamStayLayout.vue';
</script>

<template>
  <Head title="About ZamStay" />

  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <Link :href="route('zamstay.home')" class="text-emerald-600 hover:text-emerald-700 text-sm font-medium">&larr; Back to Home</Link>
    <h1 class="text-3xl font-bold text-gray-900 mt-4 mb-6">About ZamStay</h1>

    <div class="prose prose-emerald max-w-none text-gray-600 space-y-4">
      <p>ZamStay is Zambia's premier accommodation booking platform, connecting travelers with the best places to stay across all 10 provinces of our beautiful country.</p>
      <p>Whether you're looking for a luxury lodge in Livingstone, a budget guest house in Lusaka, or a home stay in the Copperbelt, ZamStay makes it easy to find and book the perfect accommodation for your needs.</p>
      <h2 class="text-xl font-semibold text-gray-900 mt-8">Our Mission</h2>
      <p>To make travel in Zambia accessible, affordable, and enjoyable by providing a seamless platform for discovering and booking accommodations nationwide.</p>
      <h2 class="text-xl font-semibold text-gray-900 mt-8">Why ZamStay?</h2>
      <ul class="list-disc pl-5 space-y-2">
        <li><strong>Curated Listings</strong> — Every property is verified for quality and accuracy.</li>
        <li><strong>Best Price Guarantee</strong> — We ensure competitive rates across all listings.</li>
        <li><strong>Local Expertise</strong> — Built by Zambians, for travelers to Zambia.</li>
        <li><strong>Secure Booking</strong> — Your payments and data are always protected.</li>
      </ul>
    </div>
  </div>
</template>
