<template>
  <Head title="Contact Us - GrowCelebrate" />
  
  <div class="min-h-screen bg-gradient-to-b from-purple-50 via-white to-pink-50">
    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/90 backdrop-blur-sm border-b border-gray-100">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <a href="/celebrations" class="flex items-center gap-2">
            <img src="/images/growcelebrate/logo.png" alt="GrowCelebrate" class="h-10 w-auto" />
          </a>
          <div class="hidden md:flex items-center gap-8">
            <a href="/celebrations#portfolio" class="text-sm text-gray-600 hover:text-purple-600 transition-colors">Our Work</a>
            <a href="/celebrations#services" class="text-sm text-gray-600 hover:text-purple-600 transition-colors">Services</a>
            <a href="/celebrations#how-it-works" class="text-sm text-gray-600 hover:text-purple-600 transition-colors">How It Works</a>
            <a href="/celebrations/contact" class="text-sm text-purple-600 font-medium">Contact</a>
          </div>
          <div class="flex items-center gap-4">
            <a 
              href="#contact-form" 
              class="hidden sm:block px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-full hover:bg-purple-700 transition-colors"
            >
              Get Quote
            </a>
            <!-- Mobile menu button -->
            <button
              @click="mobileMenuOpen = !mobileMenuOpen"
              class="md:hidden p-2 text-gray-600 hover:text-purple-600 transition-colors"
              aria-label="Toggle menu"
            >
              <svg v-if="!mobileMenuOpen" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
              </svg>
              <svg v-else class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>
        
        <!-- Mobile menu -->
        <div v-if="mobileMenuOpen" class="md:hidden py-4 border-t border-gray-100">
          <div class="flex flex-col space-y-4">
            <a href="/celebrations#portfolio" @click="mobileMenuOpen = false" class="text-sm text-gray-600 hover:text-purple-600 transition-colors">Our Work</a>
            <a href="/celebrations#services" @click="mobileMenuOpen = false" class="text-sm text-gray-600 hover:text-purple-600 transition-colors">Services</a>
            <a href="/celebrations#how-it-works" @click="mobileMenuOpen = false" class="text-sm text-gray-600 hover:text-purple-600 transition-colors">How It Works</a>
            <a href="/celebrations/contact" class="text-sm text-purple-600 font-medium">Contact</a>
            <a 
              href="#contact-form" 
              @click="mobileMenuOpen = false"
              class="inline-block text-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-full hover:bg-purple-700 transition-colors"
            >
              Get Quote
            </a>
          </div>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <section class="pt-32 pb-16 px-4">
      <div class="max-w-4xl mx-auto text-center">
        <div class="inline-flex items-center gap-2 px-4 py-2 bg-purple-100 text-purple-700 rounded-full text-sm font-medium mb-6">
          <SparklesIcon class="h-4 w-4" aria-hidden="true" />
          Let's Create Something Beautiful Together
        </div>
        <h1 class="text-4xl md:text-6xl font-bold text-gray-900 mb-6 leading-tight">
          Get in <span class="bg-gradient-to-r from-purple-600 via-pink-600 to-yellow-500 bg-clip-text text-transparent">Touch</span>
        </h1>
        <p class="text-lg md:text-xl text-gray-600 mb-8 max-w-2xl mx-auto">
          Ready to create a stunning digital invitation for your special event? We're here to help bring your celebration to life!
        </p>
      </div>
    </section>

    <!-- Contact Methods -->
    <section class="py-12 px-4">
      <div class="max-w-6xl mx-auto">
        <div class="grid md:grid-cols-3 gap-8 mb-16">
          <!-- Phone -->
          <div class="bg-white rounded-2xl p-8 shadow-lg text-center hover:shadow-xl transition-shadow">
            <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center mx-auto mb-4">
              <PhoneIcon class="h-8 w-8 text-white" aria-hidden="true" />
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Call Us</h3>
            <p class="text-gray-600 mb-4">Mon-Sat, 9AM-6PM</p>
            <a href="tel:+260976244690" class="text-purple-600 font-medium hover:text-purple-700">
              +260 976 244 690
            </a>
          </div>

          <!-- Email -->
          <div class="bg-white rounded-2xl p-8 shadow-lg text-center hover:shadow-xl transition-shadow">
            <div class="w-16 h-16 bg-gradient-to-br from-pink-500 to-yellow-500 rounded-full flex items-center justify-center mx-auto mb-4">
              <EnvelopeIcon class="h-8 w-8 text-white" aria-hidden="true" />
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Email Us</h3>
            <p class="text-gray-600 mb-4">We'll respond within 24 hours</p>
            <a href="mailto:celebrations@mygrownet.com" class="text-purple-600 font-medium hover:text-purple-700">
              celebrations@mygrownet.com
            </a>
          </div>

          <!-- WhatsApp -->
          <div class="bg-white rounded-2xl p-8 shadow-lg text-center hover:shadow-xl transition-shadow">
            <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4">
              <ChatBubbleLeftRightIcon class="h-8 w-8 text-white" aria-hidden="true" />
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">WhatsApp</h3>
            <p class="text-gray-600 mb-4">Quick responses</p>
            <a href="https://wa.me/260976244690" target="_blank" class="text-purple-600 font-medium hover:text-purple-700">
              Chat on WhatsApp
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- Contact Form -->
    <section id="contact-form" class="py-12 px-4">
      <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
          <div class="grid md:grid-cols-2">
            <!-- Form Side -->
            <div class="p-8 md:p-12">
              <h2 class="text-3xl font-bold text-gray-900 mb-2">Request a Quote</h2>
              <p class="text-gray-600 mb-8">Fill out the form and we'll get back to you within 24 hours</p>

              <form @submit.prevent="submitForm" class="space-y-6">
                <!-- Name -->
                <div>
                  <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                  <input 
                    v-model="form.name"
                    type="text" 
                    id="name"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                    placeholder="John Doe"
                  />
                </div>

                <!-- Email -->
                <div>
                  <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                  <input 
                    v-model="form.email"
                    type="email" 
                    id="email"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                    placeholder="john@example.com"
                  />
                </div>

                <!-- Phone -->
                <div>
                  <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                  <input 
                    v-model="form.phone"
                    type="tel" 
                    id="phone"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                    placeholder="+260 XXX XXX XXX"
                  />
                </div>

                <!-- Event Type -->
                <div>
                  <label for="event_type" class="block text-sm font-medium text-gray-700 mb-2">Event Type *</label>
                  <select 
                    v-model="form.event_type"
                    id="event_type"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                  >
                    <option value="">Select event type</option>
                    <option value="wedding">💍 Wedding</option>
                    <option value="birthday">🎂 Birthday Party</option>
                    <option value="anniversary">💕 Anniversary</option>
                    <option value="party">🎉 General Party/Celebration</option>
                    <option value="other">✨ Other</option>
                  </select>
                </div>

                <!-- Event Date -->
                <div>
                  <label for="event_date" class="block text-sm font-medium text-gray-700 mb-2">Event Date *</label>
                  <input 
                    v-model="form.event_date"
                    type="date" 
                    id="event_date"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                  />
                </div>

                <!-- Services Needed -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-3">Services Needed *</label>
                  <div class="space-y-2">
                    <label class="flex items-center">
                      <input 
                        v-model="form.services"
                        type="checkbox" 
                        value="digital_invitation"
                        class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                      />
                      <span class="ml-3 text-gray-700">Digital Invitation Website</span>
                    </label>
                    <label class="flex items-center">
                      <input 
                        v-model="form.services"
                        type="checkbox" 
                        value="decor"
                        class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                      />
                      <span class="ml-3 text-gray-700">Event Decor & Setup</span>
                    </label>
                    <label class="flex items-center">
                      <input 
                        v-model="form.services"
                        type="checkbox" 
                        value="photography"
                        class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                      />
                      <span class="ml-3 text-gray-700">Photography Services</span>
                    </label>
                    <label class="flex items-center">
                      <input 
                        v-model="form.services"
                        type="checkbox" 
                        value="planning"
                        class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                      />
                      <span class="ml-3 text-gray-700">Full Event Planning</span>
                    </label>
                  </div>
                </div>

                <!-- Message -->
                <div>
                  <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Additional Details</label>
                  <textarea 
                    v-model="form.message"
                    id="message"
                    rows="4"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                    placeholder="Tell us more about your event, budget, guest count, or any special requirements..."
                  ></textarea>
                </div>

                <!-- Submit Button -->
                <button 
                  type="submit"
                  :disabled="form.submitting"
                  class="w-full px-8 py-4 bg-gradient-to-r from-purple-600 via-pink-600 to-yellow-500 text-white font-semibold rounded-lg hover:shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {{ form.submitting ? 'Sending...' : 'Send Request' }}
                </button>

                <!-- Success/Error Messages -->
                <div v-if="form.success" class="p-4 bg-green-50 border border-green-200 rounded-lg">
                  <p class="text-green-800 text-sm">✓ Thank you! We'll contact you within 24 hours.</p>
                </div>
                <div v-if="form.error" class="p-4 bg-red-50 border border-red-200 rounded-lg">
                  <p class="text-red-800 text-sm">{{ form.error }}</p>
                </div>
              </form>
            </div>

            <!-- Info Side -->
            <div class="bg-gradient-to-br from-purple-600 via-pink-600 to-yellow-500 p-8 md:p-12 text-white">
              <h3 class="text-2xl font-bold mb-6">Why Choose Us?</h3>
              
              <div class="space-y-6">
                <div class="flex items-start gap-4">
                  <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0">
                    <CheckCircleIcon class="h-6 w-6" aria-hidden="true" />
                  </div>
                  <div>
                    <h4 class="font-semibold mb-1">Professional Design</h4>
                    <p class="text-sm text-purple-100">We create stunning, custom digital invitations tailored to your event</p>
                  </div>
                </div>

                <div class="flex items-start gap-4">
                  <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0">
                    <CheckCircleIcon class="h-6 w-6" aria-hidden="true" />
                  </div>
                  <div>
                    <h4 class="font-semibold mb-1">Full-Service Decor</h4>
                    <p class="text-sm text-purple-100">From concept to setup, we handle all your event decoration needs</p>
                  </div>
                </div>

                <div class="flex items-start gap-4">
                  <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0">
                    <CheckCircleIcon class="h-6 w-6" aria-hidden="true" />
                  </div>
                  <div>
                    <h4 class="font-semibold mb-1">RSVP Management</h4>
                    <p class="text-sm text-purple-100">Track guest responses and manage your guest list effortlessly</p>
                  </div>
                </div>

                <div class="flex items-start gap-4">
                  <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0">
                    <CheckCircleIcon class="h-6 w-6" aria-hidden="true" />
                  </div>
                  <div>
                    <h4 class="font-semibold mb-1">Fast Turnaround</h4>
                    <p class="text-sm text-purple-100">Your invitation website ready within 3-5 business days</p>
                  </div>
                </div>

                <div class="flex items-start gap-4">
                  <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0">
                    <CheckCircleIcon class="h-6 w-6" aria-hidden="true" />
                  </div>
                  <div>
                    <h4 class="font-semibold mb-1">Affordable Pricing</h4>
                    <p class="text-sm text-purple-100">Competitive rates with flexible packages to fit your budget</p>
                  </div>
                </div>

                <div class="flex items-start gap-4">
                  <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0">
                    <CheckCircleIcon class="h-6 w-6" aria-hidden="true" />
                  </div>
                  <div>
                    <h4 class="font-semibold mb-1">Ongoing Support</h4>
                    <p class="text-sm text-purple-100">We're here to help before, during, and after your event</p>
                  </div>
                </div>
              </div>

              <div class="mt-8 pt-8 border-t border-white/20">
                <p class="text-sm text-purple-100 mb-4">Trusted by hundreds of happy clients</p>
                <div class="flex items-center gap-1">
                  <span class="text-2xl">⭐⭐⭐⭐⭐</span>
                  <span class="ml-2 text-sm">5.0 rating</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Location -->
    <section class="py-16 px-4 bg-white">
      <div class="max-w-6xl mx-auto">
        <div class="text-center mb-12">
          <h2 class="text-3xl font-bold text-gray-900 mb-4">Visit Our Office</h2>
          <p class="text-gray-600">Come see our portfolio and discuss your event in person</p>
        </div>

        <div class="grid md:grid-cols-2 gap-8">
          <div class="bg-gray-100 rounded-2xl h-96 flex items-center justify-center">
            <div class="text-center text-gray-500">
              <MapPinIcon class="h-16 w-16 mx-auto mb-4" aria-hidden="true" />
              <p class="text-lg font-medium">Map Coming Soon</p>
              <p class="text-sm">Lusaka, Zambia</p>
            </div>
          </div>

          <div class="space-y-6">
            <div class="flex items-start gap-4">
              <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0">
                <MapPinIcon class="h-6 w-6 text-purple-600" aria-hidden="true" />
              </div>
              <div>
                <h4 class="font-semibold text-gray-900 mb-1">Address</h4>
                <p class="text-gray-600">123 Celebration Street<br/>Lusaka, Zambia</p>
              </div>
            </div>

            <div class="flex items-start gap-4">
              <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0">
                <ClockIcon class="h-6 w-6 text-purple-600" aria-hidden="true" />
              </div>
              <div>
                <h4 class="font-semibold text-gray-900 mb-1">Business Hours</h4>
                <p class="text-gray-600">
                  Monday - Friday: 9:00 AM - 6:00 PM<br/>
                  Saturday: 10:00 AM - 4:00 PM<br/>
                  Sunday: Closed
                </p>
              </div>
            </div>

            <div class="flex items-start gap-4">
              <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0">
                <PhoneIcon class="h-6 w-6 text-purple-600" aria-hidden="true" />
              </div>
              <div>
                <h4 class="font-semibold text-gray-900 mb-1">Contact</h4>
                <p class="text-gray-600">
                  Phone: +260 976 244 690<br/>
                  Email: celebrations@mygrownet.com
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="py-12 px-4 bg-gray-900 text-white">
      <div class="max-w-6xl mx-auto">
        <div class="grid md:grid-cols-4 gap-8 mb-8">
          <div>
            <div class="mb-4">
              <img src="/images/growcelebrate/logo.png" alt="GrowCelebrate" class="h-8 w-auto" />
            </div>
            <p class="text-gray-400 text-sm">
              Professional digital invitations and event services for all your celebrations.
            </p>
          </div>
          <div>
            <h4 class="font-semibold mb-4">Services</h4>
            <ul class="space-y-2 text-sm text-gray-400">
              <li><a href="/celebrations#templates" class="hover:text-white transition-colors">Digital Invitations</a></li>
              <li><a href="/celebrations#services" class="hover:text-white transition-colors">Event Decor</a></li>
              <li><a href="/celebrations#services" class="hover:text-white transition-colors">Photography</a></li>
              <li><a href="/celebrations#services" class="hover:text-white transition-colors">Event Planning</a></li>
            </ul>
          </div>
          <div>
            <h4 class="font-semibold mb-4">Company</h4>
            <ul class="space-y-2 text-sm text-gray-400">
              <li><a href="/about" class="hover:text-white transition-colors">About Us</a></li>
              <li><a href="/celebrations/contact" class="hover:text-white transition-colors">Contact</a></li>
              <li><a href="/privacy" class="hover:text-white transition-colors">Privacy Policy</a></li>
              <li><a href="/terms" class="hover:text-white transition-colors">Terms of Service</a></li>
            </ul>
          </div>
          <div>
            <h4 class="font-semibold mb-4">Contact</h4>
            <ul class="space-y-2 text-sm text-gray-400">
              <li>celebrations@mygrownet.com</li>
              <li>+260 976 244 690</li>
              <li>Lusaka, Zambia</li>
            </ul>
          </div>
        </div>
        <div class="border-t border-gray-800 pt-8 text-center text-sm text-gray-500">
          <p>© {{ new Date().getFullYear() }} GrowCelebrate. All rights reserved.</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import { 
  SparklesIcon,
  PhoneIcon,
  EnvelopeIcon,
  ChatBubbleLeftRightIcon,
  CheckCircleIcon,
  MapPinIcon,
  ClockIcon,
} from '@heroicons/vue/24/outline'

const form = ref({
  name: '',
  email: '',
  phone: '',
  event_type: '',
  event_date: '',
  services: [],
  message: '',
  submitting: false,
  success: false,
  error: null
})

const mobileMenuOpen = ref(false)

const submitForm = async () => {
  form.value.submitting = true
  form.value.error = null
  form.value.success = false

  try {
    // In production, this would send to your backend
    await new Promise(resolve => setTimeout(resolve, 1500))
    
    form.value.success = true
    // Reset form
    form.value.name = ''
    form.value.email = ''
    form.value.phone = ''
    form.value.event_type = ''
    form.value.event_date = ''
    form.value.services = []
    form.value.message = ''
  } catch (error) {
    form.value.error = 'Failed to send request. Please try again or contact us directly.'
  } finally {
    form.value.submitting = false
  }
}
</script>
