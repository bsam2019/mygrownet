<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ZamStayLayout from '@/layouts/ZamStayLayout.vue';
</script>

<template>
  <Head title="Terms of Service - ZamStay" />

  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <Link :href="route('zamstay.home')" class="text-emerald-600 hover:text-emerald-700 text-sm font-medium">&larr; Back to Home</Link>
    <h1 class="text-3xl font-bold text-gray-900 mt-4 mb-6">Terms of Service</h1>

    <div class="prose prose-emerald max-w-none text-gray-600 space-y-4">
      <p>By using ZamStay, you agree to the following terms and conditions. Please read them carefully.</p>

      <h2 class="text-xl font-semibold text-gray-900 mt-8">Account Registration</h2>
      <p>You must provide accurate information when creating an account. You are responsible for maintaining the confidentiality of your account credentials.</p>

      <h2 class="text-xl font-semibold text-gray-900 mt-8">Bookings</h2>
      <p>All bookings are subject to availability and confirmation. ZamStay acts as an intermediary between guests and property hosts. The booking contract is between the guest and the host.</p>

      <h2 class="text-xl font-semibold text-gray-900 mt-8">Payments</h2>
      <p>Full payment is required at the time of booking. Payments are processed securely through our payment partners. Refunds are subject to the property's cancellation policy.</p>

      <h2 class="text-xl font-semibold text-gray-900 mt-8">User Conduct</h2>
      <p>Users agree to treat hosts, agents, and other guests with respect. Any fraudulent activity will result in immediate account termination.</p>

      <h2 class="text-xl font-semibold text-gray-900 mt-8">Limitation of Liability</h2>
      <p>ZamStay is not liable for disputes between guests and hosts, or for issues arising from third-party services. Our liability is limited to the amount paid for the booking in question.</p>

      <h2 class="text-xl font-semibold text-gray-900 mt-8">Changes to Terms</h2>
      <p>We reserve the right to update these terms at any time. Users will be notified of material changes via email.</p>
    </div>
  </div>
</template>
