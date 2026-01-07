<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRightIcon, PhoneIcon } from '@heroicons/vue/24/outline';
</script>

<template>
  <section class="py-20 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-gradient-to-r from-red-600 to-red-700 rounded-2xl p-12 text-center text-white shadow-2xl">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">
          Ready to Start Your Project?
        </h2>
        <p class="text-xl text-red-100 mb-8 max-w-2xl mx-auto">
          Get a free consultation and quote. Let's discuss how we can help bring your vision to life.
        </p>
        
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <Link
            href="/geopamu/contact"
            class="inline-flex items-center justify-center bg-white text-red-600 px-8 py-4 rounded-lg font-semibold hover:bg-gray-100 transition-all shadow-lg"
          >
            Get Free Quote
            <ArrowRightIcon class="ml-2 h-5 w-5" aria-hidden="true" />
          </Link>
          
          <a
            href="tel:+260XXXXXXXXX"
            class="inline-flex items-center justify-center bg-transparent border-2 border-white text-white px-8 py-4 rounded-lg font-semibold hover:bg-white/10 transition-all"
          >
            <PhoneIcon class="mr-2 h-5 w-5" aria-hidden="true" />
            Call Us Now
          </a>
        </div>
      </div>
    </div>
  </section>
</template>
