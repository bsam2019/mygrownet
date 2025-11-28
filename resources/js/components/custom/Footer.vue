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
              A community empowerment platform offering skill-based training, mentorship, and income opportunities.
            </p>
            <!-- Contact Info -->
            <div class="space-y-2 pt-2">
              <div v-for="contact in contactInfo" :key="contact.label"
                   class="flex items-center space-x-2 text-slate-400">
                <component :is="contact.icon" class="w-4 h-4 text-blue-500 flex-shrink-0"/>
                <span class="text-xs">{{ contact.value }}</span>
              </div>
            </div>
          </div>

          <!-- Opportunities -->
          <div>
            <h4 class="text-white font-semibold mb-4">Opportunities</h4>
            <ul class="space-y-3">
              <li v-for="link in opportunityLinks" :key="link.name">
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

          <!-- Resources -->
          <div>
            <h4 class="text-white font-semibold mb-4">Resources</h4>
            <ul class="space-y-3">
              <li v-for="link in resourceLinks" :key="link.name">
                <Link :href="route().has(link.route) ? route(link.route) : '#'"
                      class="text-slate-400 hover:text-white transition-colors duration-200 text-sm">
                  {{ link.name }}
                </Link>
              </li>
            </ul>
          </div>

          <!-- Company -->
          <div>
            <h4 class="text-white font-semibold mb-4">Company</h4>
            <ul class="space-y-3">
              <li v-for="link in companyLinks" :key="link.name">
                <Link :href="route().has(link.route) ? route(link.route) : '#'"
                      class="text-slate-400 hover:text-white transition-colors duration-200 text-sm">
                  {{ link.name }}
                </Link>
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
        opportunityLinks: [
          { name: 'Venture Builder', route: 'ventures.about' },
          { name: 'Business Growth Fund', route: 'bgf.about' },
          { name: 'Venture Investments', route: 'ventures.index' },
          { name: 'MyGrow Shop', route: 'shop.index' },
          { name: 'Membership Plans', route: 'membership.index' }
        ],
        resourceLinks: [
          { name: 'About MyGrowNet', route: 'about' },
          { name: 'How It Works', route: 'home' },
          { name: 'FAQ', route: 'faq' },
          { name: 'Success Stories', route: 'home' }
        ],
        companyLinks: [
          { name: 'Compliance', route: 'compliance.information' },
          { name: 'Careers', route: 'careers.index' },
          { name: 'Contact Us', route: 'contact' },
          { name: 'All Policies', route: 'policies' },
          { name: 'Privacy Policy', route: 'privacy' },
          { name: 'Terms of Service', route: 'terms' },
          { name: 'Shareholder Portal', route: 'investor.login' }
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
            value: '+260 977 563 730'
          },
          {
            icon: 'MapPinIcon',
            label: 'Address',
            value: 'Lusaka, Zambia'
          }
        ],
        legalLinks: [
          { name: 'All Policies', route: 'policies' },
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
