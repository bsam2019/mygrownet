<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import GeopamuLayout from '@/Layouts/GeopamuLayout.vue';
import PageHeader from '@/Components/Geopamu/PageHeader.vue';
import { 
  PhoneIcon, 
  EnvelopeIcon, 
  MapPinIcon,
  ClockIcon
} from '@heroicons/vue/24/outline';

const form = useForm({
  name: '',
  email: '',
  phone: '',
  service: '',
  message: ''
});

const services = [
  'Digital & Offset Printing',
  'Brand Identity Design',
  'Promotional Products',
  'Marketing Materials',
  'Packaging Design',
  'Signage & Display',
  'Other'
];

const contactInfo = [
  {
    icon: PhoneIcon,
    title: 'Phone',
    details: ['+260 XXX XXX XXX', '+260 XXX XXX XXX']
  },
  {
    icon: EnvelopeIcon,
    title: 'Email',
    details: ['info@geopamu.com', 'sales@geopamu.com']
  },
  {
    icon: MapPinIcon,
    title: 'Address',
    details: ['123 Business Street', 'Lusaka, Zambia']
  },
  {
    icon: ClockIcon,
    title: 'Business Hours',
    details: ['Mon - Fri: 8:00 AM - 5:00 PM', 'Sat: 9:00 AM - 1:00 PM']
  }
];

const submit = () => {
  // Placeholder for form submission
  console.log('Form submitted:', form.data());
};
</script>

<template>
  <Head title="Contact Us - Geopamu" />
  
  <GeopamuLayout>
    <PageHeader
      title="Get In Touch"
      subtitle="Let's discuss how we can help bring your vision to life"
    />

    <section class="py-16 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
          <!-- Contact Form -->
          <div>
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Send Us a Message</h2>
            
            <form @submit.prevent="submit" class="space-y-6">
              <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                  Full Name *
                </label>
                <input
                  id="name"
                  v-model="form.name"
                  type="text"
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                  placeholder="John Doe"
                />
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    Email Address *
                  </label>
                  <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                    placeholder="john@example.com"
                  />
                </div>

                <div>
                  <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                    Phone Number
                  </label>
                  <input
                    id="phone"
                    v-model="form.phone"
                    type="tel"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                    placeholder="+260 XXX XXX XXX"
                  />
                </div>
              </div>

              <div>
                <label for="service" class="block text-sm font-medium text-gray-700 mb-2">
                  Service Interested In
                </label>
                <select
                  id="service"
                  v-model="form.service"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                >
                  <option value="">Select a service</option>
                  <option v-for="service in services" :key="service" :value="service">
                    {{ service }}
                  </option>
                </select>
              </div>

              <div>
                <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                  Message *
                </label>
                <textarea
                  id="message"
                  v-model="form.message"
                  required
                  rows="5"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                  placeholder="Tell us about your project..."
                ></textarea>
              </div>

              <button
                type="submit"
                class="w-full bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors"
              >
                Send Message
              </button>
            </form>
          </div>

          <!-- Contact Information -->
          <div>
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Contact Information</h2>
            
            <div class="space-y-6 mb-8">
              <div 
                v-for="info in contactInfo" 
                :key="info.title"
                class="flex items-start"
              >
                <component 
                  :is="info.icon" 
                  class="h-6 w-6 text-blue-600 mr-4 mt-1 flex-shrink-0"
                  aria-hidden="true"
                />
                <div>
                  <h3 class="font-semibold text-gray-900 mb-1">{{ info.title }}</h3>
                  <p v-for="detail in info.details" :key="detail" class="text-gray-600">
                    {{ detail }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Map/Location Image -->
            <div class="rounded-lg h-64 overflow-hidden">
              <img 
                src="https://images.unsplash.com/photo-1524661135-423995f22d0b?w=800&h=400&fit=crop" 
                alt="Geopamu location"
                class="w-full h-full object-cover"
              />
            </div>

            <!-- Social Media -->
            <div class="mt-8">
              <h3 class="font-semibold text-gray-900 mb-4">Follow Us</h3>
              <div class="flex space-x-4">
                <a 
                  v-for="social in ['Facebook', 'Twitter', 'Instagram', 'LinkedIn']"
                  :key="social"
                  href="#"
                  class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center hover:bg-blue-600 hover:text-white transition-colors"
                  :aria-label="`Follow us on ${social}`"
                >
                  <span class="text-xs">{{ social[0] }}</span>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 bg-gradient-to-r from-blue-700 to-red-600 text-white">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold mb-4">Ready to Start Your Project?</h2>
        <p class="text-xl text-blue-100 mb-8">
          Get a free consultation and quote for your printing and branding needs
        </p>
        <a
          href="#"
          class="inline-block bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors"
        >
          Request a Quote
        </a>
      </div>
    </section>
  </GeopamuLayout>
</template>
