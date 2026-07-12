<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ZamStayLayout from '@/layouts/ZamStayLayout.vue';
</script>

<template>
  <Head title="Privacy Policy - ZamStay" />

  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <Link :href="route('zamstay.home')" class="text-emerald-600 hover:text-emerald-700 text-sm font-medium">&larr; Back to Home</Link>
    <h1 class="text-3xl font-bold text-gray-900 mt-4 mb-6">Privacy Policy</h1>

    <div class="prose prose-emerald max-w-none text-gray-600 space-y-4">
      <p>Your privacy is important to us. This policy outlines how ZamStay collects, uses, and protects your personal information.</p>

      <h2 class="text-xl font-semibold text-gray-900 mt-8">Information We Collect</h2>
      <ul class="list-disc pl-5 space-y-1">
        <li>Name, email address, and phone number when you create an account.</li>
        <li>Payment information (processed securely through third-party providers).</li>
        <li>Booking history and preferences.</li>
        <li>Communications you send to hosts or agents through our platform.</li>
      </ul>

      <h2 class="text-xl font-semibold text-gray-900 mt-8">How We Use Your Information</h2>
      <ul class="list-disc pl-5 space-y-1">
        <li>To process and manage your bookings.</li>
        <li>To communicate with you about your reservations.</li>
        <li>To improve our platform and user experience.</li>
        <li>To send relevant travel recommendations (with your consent).</li>
      </ul>

      <h2 class="text-xl font-semibold text-gray-900 mt-8">Data Security</h2>
      <p>We implement appropriate security measures to protect your data. Your payment information is never stored on our servers — it is processed directly by our payment partners.</p>

      <h2 class="text-xl font-semibold text-gray-900 mt-8">Contact</h2>
      <p>If you have questions about this policy, please contact our support team.</p>
    </div>
  </div>
</template>
