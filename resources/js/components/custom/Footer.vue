<template>
    <footer class="relative bg-slate-900 pt-16 pb-12 overflow-hidden">
      <!-- Gradient Effects -->
      <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-blue-500/20 rounded-full mix-blend-multiply filter blur-3xl opacity-10"></div>
        <div class="absolute -top-20 right-0 w-[400px] h-[400px] bg-blue-700/20 rounded-full mix-blend-multiply filter blur-3xl opacity-10"></div>
      </div>

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">
          <!-- Company Info -->
          <div class="space-y-4">
            <div class="flex items-center">
              <Logo size="lg" variant="white" />
            </div>
            <p class="text-slate-400 text-sm leading-relaxed">
              Empowering growth through five-level commissions, asset rewards, community projects, and comprehensive education.
            </p>
          </div>

          <!-- Quick Links -->
          <div>
            <h4 class="text-white font-semibold mb-4">Quick Links</h4>
            <ul class="space-y-3">
              <li v-for="link in quickLinks" :key="link.name">
                <Link :href="route().has(link.route) ? route(link.route) : '#'"
                      class="text-slate-400 hover:text-white transition-colors duration-200 text-sm flex items-center group">
                  <span>{{ link.name }}</span>
                  <svg class="w-4 h-4 ml-1 opacity-0 group-hover:opacity-100 transform translate-x-0 group-hover:translate-x-1 transition-all"
                       fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </Link>
              </li>
            </ul>
          </div>

          <!-- Membership Tiers -->
          <div>
            <h4 class="text-white font-semibold mb-4">Membership Tiers</h4>
            <ul class="space-y-3">
              <li v-for="plan in investmentPlans" :key="plan">
                <Link :href="route('register')"
                      class="text-slate-400 hover:text-white transition-colors duration-200 text-sm flex items-center group">
                  {{ plan }}
                </Link>
              </li>
            </ul>
          </div>

          <!-- Contact Info -->
          <div>
            <h4 class="text-white font-semibold mb-4">Contact Us</h4>
            <ul class="space-y-3">
              <li v-for="contact in contactInfo" :key="contact.label"
                  class="flex items-center space-x-3 text-slate-400 group">
                <component :is="contact.icon" class="w-5 h-5 text-blue-500"/>
                <span class="text-sm group-hover:text-white transition-colors duration-200">
                  {{ contact.value }}
                </span>
              </li>
            </ul>
          </div>
        </div>

        <!-- Bottom Bar -->
        <div class="mt-12 pt-8 border-t border-slate-800">
          <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
            <p class="text-slate-400 text-sm">
              © {{ currentYear }} MyGrowNet. All rights reserved.
            </p>
            <div class="flex items-center space-x-6">
              <Link v-for="legal in legalLinks"
                    :key="legal.name"
                    :href="route().has(legal.route) ? route(legal.route) : '#'"
                    class="text-slate-400 hover:text-white transition-colors duration-200 text-sm">
                {{ legal.name }}
              </Link>
            </div>
          </div>
        </div>
      </div>
    </footer>
  </template>

  <script>
  import { Link } from '@inertiajs/vue3';
  import Logo from '../Logo.vue';
  import {
    EnvelopeIcon,
    PhoneIcon,
    MapPinIcon
  } from '@heroicons/vue/24/outline';

  export default {
    name: 'Footer',
    components: {
      Link,
      Logo,
      EnvelopeIcon,
      PhoneIcon,
      MapPinIcon
    },
    data() {
      return {
        quickLinks: [
          { name: 'Home', route: 'home' },
          { name: 'About', route: 'about' },
          { name: 'Membership', route: 'membership.index' },
          { name: 'Compliance', route: 'compliance.information' },
          { name: 'Careers', route: 'careers.index' },
          { name: 'Contact', route: 'contact' }
        ],
        investmentPlans: [
          'Bronze Membership',
          'Silver Membership',
          'Gold Membership',
          'Diamond Membership',
          'Elite Membership'
        ],
        contactInfo: [
          {
            icon: 'EnvelopeIcon',
            label: 'Email',
            value: 'support@mygrownet.com'
          },
          {
            icon: 'PhoneIcon',
            label: 'Phone',
            value: '+260 977 563 730 / +260 961 144 812'
          },
          {
            icon: 'MapPinIcon',
            label: 'Address',
            value: 'Lusaka, Zambia'
          }
        ],
        legalLinks: [
          { name: 'Privacy Policy', route: 'privacy' },
          { name: 'Terms of Service', route: 'terms' }
        ]
      }
    },
    computed: {
      currentYear() {
        return new Date().getFullYear()
      }
    }
  }
  </script>
