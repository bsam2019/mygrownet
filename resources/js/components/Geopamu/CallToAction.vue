<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ArrowRightIcon, PhoneIcon } from '@heroicons/vue/24/outline';

const ctaVisible = ref(false);

onMounted(() => {
  const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        ctaVisible.value = true;
      }
    });
  }, observerOptions);

  const ctaElement = document.querySelector('.cta-content');
  if (ctaElement) observer.observe(ctaElement);
});
</script>

<template>
  <section class="py-20 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
      <div 
        class="cta-content bg-gradient-to-r from-red-600 to-red-700 rounded-2xl p-12 text-center text-white shadow-2xl transform transition-all duration-700 hover:scale-105"
        :class="ctaVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'"
      >
        <h2 class="text-3xl md:text-4xl font-bold mb-4 transition-all duration-500"
            :class="ctaVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
            :style="{ transitionDelay: '200ms' }">
          Ready to Start Your Project?
        </h2>
        <p class="text-xl text-red-100 mb-8 max-w-2xl mx-auto transition-all duration-500"
           :class="ctaVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
           :style="{ transitionDelay: '400ms' }">
          Get a free consultation and quote. Let's discuss how we can help bring your vision to life.
        </p>
        
        <div class="flex flex-col sm:flex-row gap-4 justify-center transition-all duration-500"
             :class="ctaVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
             :style="{ transitionDelay: '600ms' }">
          <Link
            href="/geopamu/contact"
            class="inline-flex items-center justify-center bg-white text-red-600 px-8 py-4 rounded-lg font-semibold hover:bg-gray-100 transition-all shadow-lg transform hover:scale-105 hover:shadow-xl group"
          >
            Get Free Quote
            <ArrowRightIcon class="ml-2 h-5 w-5 transition-transform duration-300 group-hover:translate-x-2" aria-hidden="true" />
          </Link>
          
          <a
            href="tel:+260XXXXXXXXX"
            class="inline-flex items-center justify-center bg-transparent border-2 border-white text-white px-8 py-4 rounded-lg font-semibold hover:bg-white/10 transition-all transform hover:scale-105 group"
          >
            <PhoneIcon class="mr-2 h-5 w-5 transition-transform duration-300 group-hover:rotate-12" aria-hidden="true" />
            Call Us Now
          </a>
        </div>
      </div>
    </div>
  </section>
</template>
