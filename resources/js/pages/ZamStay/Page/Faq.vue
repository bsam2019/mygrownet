<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import ZamStayLayout from '@/layouts/ZamStayLayout.vue';

const faqs = [
  { q: 'How do I make a booking?', a: 'Browse available properties, select your dates, and proceed to checkout. You\'ll need to be logged in to complete your reservation.' },
  { q: 'What payment methods are accepted?', a: 'We accept mobile money (MTN, Airtel, Zamtel) and bank transfers. Full payment is required at booking time.' },
  { q: 'Can I cancel my booking?', a: 'Yes, cancellations can be made from your My Bookings page. Refunds are subject to the property\'s cancellation policy.' },
  { q: 'How do I become a host?', a: 'Navigate to the Host Dashboard and click "List Your Property". Fill in the details and submit for review.' },
  { q: 'What is a tour agent?', a: 'Tour agents are registered professionals who can book properties on behalf of their clients. Register as an agent from the Agent section.' },
  { q: 'Is my data secure?', a: 'Yes, we use industry-standard encryption and security practices to protect your personal and payment information.' },
];

const openIndex = ref<number | null>(null);

const toggle = (i: number) => {
  openIndex.value = openIndex.value === i ? null : i;
};
</script>

<template>
  <Head title="FAQ - ZamStay" />

  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <Link :href="route('zamstay.home')" class="text-emerald-600 hover:text-emerald-700 text-sm font-medium">&larr; Back to Home</Link>
    <h1 class="text-3xl font-bold text-gray-900 mt-4 mb-8">Frequently Asked Questions</h1>

    <div class="space-y-3">
      <div v-for="(faq, i) in faqs" :key="i" class="border border-gray-200 rounded-xl overflow-hidden">
        <button @click="toggle(i)" class="w-full flex items-center justify-between px-5 py-4 text-left text-gray-900 font-medium hover:bg-gray-50 transition-colors">
          {{ faq.q }}
          <svg :class="['w-5 h-5 text-gray-400 transition-transform', openIndex === i ? 'rotate-180' : '']" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
          </svg>
        </button>
        <div v-if="openIndex === i" class="px-5 pb-4 text-gray-600 text-sm">
          {{ faq.a }}
        </div>
      </div>
    </div>
  </div>
</template>
