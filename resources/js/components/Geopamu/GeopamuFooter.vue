<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { 
  PhoneIcon, 
  EnvelopeIcon, 
  MapPinIcon 
} from '@heroicons/vue/24/outline';

const footerLinks = {
  services: [
    { name: 'Digital Printing', href: '#' },
    { name: 'Brand Design', href: '#' },
    { name: 'Promotional Products', href: '#' },
    { name: 'Packaging', href: '#' }
  ],
  company: [
    { name: 'About Us', href: '/geopamu/about' },
    { name: 'Portfolio', href: '/geopamu/portfolio' },
    { name: 'Contact', href: '/geopamu/contact' },
    { name: 'Careers', href: '#' }
  ],
  support: [
    { name: 'FAQ', href: '#' },
    { name: 'Pricing', href: '#' },
    { name: 'Terms of Service', href: '#' },
    { name: 'Privacy Policy', href: '#' }
  ]
};
</script>

<template>
  <footer class="bg-gray-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8">
        <!-- Brand Column -->
        <div class="lg:col-span-2">
          <div class="flex items-center space-x-3 mb-4">
            <div class="w-10 h-10 rounded-full overflow-hidden">
              <img 
                src="/geopamu-assets/logo.png" 
                alt="Geopamu"
                class="w-full h-full object-contain"
              />
            </div>
            <div>
              <div class="text-xl font-bold">Geopamu</div>
              <div class="text-xs text-gray-400">Printing & Branding</div>
            </div>
          </div>
          <p class="text-gray-400 mb-6 max-w-sm">
            Your trusted partner for professional printing and branding solutions. 
            Quality, creativity, and reliability in every project.
          </p>
          
          <div class="space-y-2">
            <div class="flex items-center text-gray-400">
              <PhoneIcon class="h-5 w-5 mr-2" aria-hidden="true" />
              <span>+260 XXX XXX XXX</span>
            </div>
            <div class="flex items-center text-gray-400">
              <EnvelopeIcon class="h-5 w-5 mr-2" aria-hidden="true" />
              <span>info@geopamu.com</span>
            </div>
            <div class="flex items-center text-gray-400">
              <MapPinIcon class="h-5 w-5 mr-2" aria-hidden="true" />
              <span>Lusaka, Zambia</span>
            </div>
          </div>
        </div>

        <!-- Services Column -->
        <div>
          <h3 class="font-semibold text-lg mb-4">Services</h3>
          <ul class="space-y-2">
            <li v-for="link in footerLinks.services" :key="link.name">
              <a :href="link.href" class="text-gray-400 hover:text-white transition-colors">
                {{ link.name }}
              </a>
            </li>
          </ul>
        </div>

        <!-- Company Column -->
        <div>
          <h3 class="font-semibold text-lg mb-4">Company</h3>
          <ul class="space-y-2">
            <li v-for="link in footerLinks.company" :key="link.name">
              <Link :href="link.href" class="text-gray-400 hover:text-white transition-colors">
                {{ link.name }}
              </Link>
            </li>
          </ul>
        </div>

        <!-- Support Column -->
        <div>
          <h3 class="font-semibold text-lg mb-4">Support</h3>
          <ul class="space-y-2">
            <li v-for="link in footerLinks.support" :key="link.name">
              <a :href="link.href" class="text-gray-400 hover:text-white transition-colors">
                {{ link.name }}
              </a>
            </li>
          </ul>
        </div>
      </div>

      <!-- Bottom Bar -->
      <div class="border-t border-gray-800 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center">
        <p class="text-gray-400 text-sm">
          © {{ new Date().getFullYear() }} Geopamu. All rights reserved.
        </p>
        
        <div class="flex space-x-6 mt-4 md:mt-0">
          <a href="#" class="text-gray-400 hover:text-white transition-colors" aria-label="Facebook">
            <span class="sr-only">Facebook</span>
            <div class="w-8 h-8 bg-gray-800 rounded-full flex items-center justify-center hover:bg-blue-600">
              <span class="text-xs">f</span>
            </div>
          </a>
          <a href="#" class="text-gray-400 hover:text-white transition-colors" aria-label="Twitter">
            <span class="sr-only">Twitter</span>
            <div class="w-8 h-8 bg-gray-800 rounded-full flex items-center justify-center hover:bg-blue-400">
              <span class="text-xs">𝕏</span>
            </div>
          </a>
          <a href="#" class="text-gray-400 hover:text-white transition-colors" aria-label="Instagram">
            <span class="sr-only">Instagram</span>
            <div class="w-8 h-8 bg-gray-800 rounded-full flex items-center justify-center hover:bg-pink-600">
              <span class="text-xs">📷</span>
            </div>
          </a>
          <a href="#" class="text-gray-400 hover:text-white transition-colors" aria-label="LinkedIn">
            <span class="sr-only">LinkedIn</span>
            <div class="w-8 h-8 bg-gray-800 rounded-full flex items-center justify-center hover:bg-blue-700">
              <span class="text-xs">in</span>
            </div>
          </a>
        </div>
      </div>
    </div>
  </footer>
</template>
