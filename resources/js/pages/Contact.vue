<template>
    <div class="min-h-screen bg-gray-50">
      <Navigation />

      <!-- Hero Section -->
      <div class="py-12 bg-gradient-to-r from-blue-600 to-blue-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="text-center">
            <h1 class="text-4xl font-bold text-white mb-4">
              Get in Touch
            </h1>
            <p class="text-xl text-blue-100 max-w-3xl mx-auto">
              Have questions about our investment opportunities? We're here to help.
            </p>
          </div>
        </div>
      </div>

      <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
            <!-- Contact Form -->
            <div class="bg-white rounded-lg shadow-lg p-8">
              <h2 class="text-2xl font-bold text-gray-900 mb-6">Send us a Message</h2>
              <form @submit.prevent="submitForm">
                <div class="space-y-6">
    <div>
      <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
      <input
        type="text"
        id="name"
        v-model="form.name"
        :class="{'border-red-300': errors.name}"
        class="mt-1 block w-full px-4 py-3 rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-white disabled:bg-gray-50 disabled:opacity-75"
        :disabled="loading"
        required
      />
      <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
    </div>

    <div>
      <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
      <input
        type="email"
        id="email"
        v-model="form.email"
        class="mt-1 block w-full px-4 py-3 rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-white"
        required
      />
    </div>

    <div>
      <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
      <input
        type="tel"
        id="phone"
        v-model="form.phone"
        class="mt-1 block w-full px-4 py-3 rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-white"
      />
    </div>

    <div>
      <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
      <select
        id="subject"
        v-model="form.subject"
        class="mt-1 block w-full px-4 py-3 rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-white"
        required
      >
        <option value="">Select a subject</option>
        <option value="investment">Investment Inquiry</option>
        <option value="membership">Membership Information</option>
        <option value="support">General Support</option>
        <option value="other">Other</option>
      </select>
    </div>

    <div>
      <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
      <textarea
        id="message"
        v-model="form.message"
        rows="4"
        class="mt-1 block w-full px-4 py-3 rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-white"
        required
      ></textarea>
    </div>

    <button
      type="submit"
      class="w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white py-4 px-6 rounded-md hover:from-blue-600 hover:to-blue-700 transition-all duration-200 font-semibold text-base shadow-md disabled:opacity-75 disabled:cursor-not-allowed flex items-center justify-center"
      :disabled="loading"
    >
      <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      {{ loading ? 'Sending...' : 'Send Message' }}
    </button>
  </div>
              </form>
            </div>

            <!-- Contact Information -->
            <div class="space-y-8">
              <div class="bg-white rounded-lg shadow-lg p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Contact Information</h2>
                <div class="space-y-4">
                  <div class="flex items-start">
                    <div class="flex-shrink-0">
                      <MapPinIcon class="h-6 w-6 text-blue-500" />
                    </div>
                    <div class="ml-3">
                      <h3 class="text-lg font-medium text-gray-900">Office Location</h3>
                      <p class="mt-1 text-gray-500">
                        123 Investment Avenue<br />
                        Lusaka, Zambia
                      </p>
                    </div>
                  </div>

                  <div class="flex items-start">
                    <div class="flex-shrink-0">
                      <PhoneIcon class="h-6 w-6 text-blue-500" />
                    </div>
                    <div class="ml-3">
                      <h3 class="text-lg font-medium text-gray-900">Phone</h3>
                      <p class="mt-1 text-gray-500">+260 97X XXX XXX</p>
                    </div>
                  </div>

                  <div class="flex items-start">
                    <div class="flex-shrink-0">
                      <EnvelopeIcon class="h-6 w-6 text-blue-500" />
                    </div>
                    <div class="ml-3">
                      <h3 class="text-lg font-medium text-gray-900">Email</h3>
                      <p class="mt-1 text-gray-500">contact@vbif.com</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Business Hours -->
              <div class="bg-white rounded-lg shadow-lg p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Business Hours</h2>
                <div class="space-y-2">
                  <div class="flex justify-between">
                    <span class="text-gray-500">Monday - Friday</span>
                    <span class="text-gray-900">8:00 AM - 5:00 PM</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-500">Saturday</span>
                    <span class="text-gray-900">9:00 AM - 1:00 PM</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-500">Sunday</span>
                    <span class="text-gray-900">Closed</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <Footer />
    </div>
  </template>

  <script>
  import { ref } from 'vue';
  import Navigation from '@/components/custom/Navigation.vue';
  import Footer from '@/components/custom/Footer.vue';
  import { MapPinIcon, PhoneIcon, EnvelopeIcon } from '@heroicons/vue/24/outline';

  export default {
    components: {
      Navigation,
      Footer,
      MapPinIcon,
      PhoneIcon,
      EnvelopeIcon
    },
    setup() {
      const loading = ref(false);
      const errors = ref({});
      const form = ref({
        name: '',
        email: '',
        phone: '',
        subject: '',
        message: ''
      });

      const validateForm = () => {
        errors.value = {};
        if (!form.value.name) errors.value.name = 'Name is required';
        if (!form.value.email) errors.value.email = 'Email is required';
        if (!form.value.subject) errors.value.subject = 'Subject is required';
        if (!form.value.message) errors.value.message = 'Message is required';
        return Object.keys(errors.value).length === 0;
      };

      const submitForm = async () => {
        if (!validateForm()) return;

        loading.value = true;
        try {
          // Add your form submission logic here
          await new Promise(resolve => setTimeout(resolve, 1000)); // Simulated API call
          alert('Message sent successfully!');
          form.value = {
            name: '',
            email: '',
            phone: '',
            subject: '',
            message: ''
          };
        } catch (error) {
          alert('Error sending message. Please try again.');
        } finally {
          loading.value = false;
        }
      };

      return {
        form,
        loading,
        errors,
        submitForm
      };
    }
  };
  </script>
