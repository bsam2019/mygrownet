<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ZamStayLayout from '@/layouts/ZamStayLayout.vue';
</script>

<template>
  <Head title="Cancellation Policy - ZamStay" />

  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <Link :href="route('zamstay.home')" class="text-emerald-600 hover:text-emerald-700 text-sm font-medium">&larr; Back to Home</Link>
    <h1 class="text-3xl font-bold text-gray-900 mt-4 mb-6">Cancellation Policy</h1>

    <div class="prose prose-emerald max-w-none text-gray-600 space-y-4">
      <p>Each property listed on ZamStay may have its own cancellation policy set by the host. The policy is displayed on the property detail page before you book.</p>

      <h2 class="text-xl font-semibold text-gray-900 mt-8">Standard Policies</h2>

      <h3 class="text-lg font-semibold text-gray-900 mt-6">Flexible</h3>
      <p>Full refund if cancelled at least 48 hours before check-in. 50% refund if cancelled between 24-48 hours. No refund within 24 hours.</p>

      <h3 class="text-lg font-semibold text-gray-900 mt-6">Moderate</h3>
      <p>Full refund if cancelled at least 7 days before check-in. 50% refund if cancelled between 3-7 days. No refund within 3 days.</p>

      <h3 class="text-lg font-semibold text-gray-900 mt-6">Strict</h3>
      <p>50% refund if cancelled at least 14 days before check-in. No refund within 14 days.</p>

      <h2 class="text-xl font-semibold text-gray-900 mt-8">How to Cancel</h2>
      <p>Log in to your account, go to My Bookings, find the booking you wish to cancel, and click the Cancel button. The refund amount will be calculated based on the property's cancellation policy.</p>

      <p class="mt-6 text-sm text-gray-500">If you have any questions about cancellations, please contact our support team.</p>
    </div>
  </div>
</template>
