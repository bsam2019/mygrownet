<template>
    <div class="min-h-screen bg-gray-50">
      <Navigation />

      <!-- Hero Section -->
      <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="bg-gradient-to-r from-blue-500 to-blue-700 rounded-lg shadow-xl p-8 md:p-12 relative overflow-hidden">
            <!-- Decorative circles -->
            <div class="absolute top-10 left-20 w-48 h-48 bg-white bg-opacity-5 rounded-full"></div>
            <div class="absolute bottom-16 right-16 w-32 h-32 bg-white bg-opacity-5 rounded-full"></div>

            <div class="relative z-10">
              <h1 class="text-4xl font-bold text-white leading-tight">About MyGrowNet</h1>
              <p class="mt-4 text-lg text-white text-opacity-90 max-w-3xl">
                Building a stronger financial future through community-driven investment solutions. We're transforming traditional community banking with innovative technology and sustainable practices.
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Mission & Vision -->
      <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Mission -->
            <div class="bg-white rounded-lg shadow-lg p-8">
              <h2 class="text-2xl font-bold text-gray-800 mb-4">Our Mission</h2>
              <p class="text-gray-600">
                To empower communities through accessible, sustainable, and rewarding investment opportunities.
                We believe in creating wealth by combining traditional community values with modern financial innovation.
              </p>
            </div>

            <!-- Vision -->
            <div class="bg-white rounded-lg shadow-lg p-8">
              <h2 class="text-2xl font-bold text-gray-800 mb-4">Our Vision</h2>
              <p class="text-gray-600">
                To become the leading community-driven investment platform in Zambia, setting new standards
                for sustainable wealth creation and financial inclusion.
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Core Values -->
      <div class="py-12 bg-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <h2 class="text-3xl font-bold text-gray-800 text-center mb-12">Our Core Values</h2>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white rounded-lg shadow-lg p-6">
              <div class="h-12 w-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mb-4">
                <span class="text-2xl">🤝</span>
              </div>
              <h3 class="text-xl font-semibold text-gray-800 mb-2">Community First</h3>
              <p class="text-gray-600">Building wealth through collective growth and shared success</p>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
              <div class="h-12 w-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mb-4">
                <span class="text-2xl">💫</span>
              </div>
              <h3 class="text-xl font-semibold text-gray-800 mb-2">Innovation</h3>
              <p class="text-gray-600">Leveraging technology to enhance traditional investment models</p>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
              <div class="h-12 w-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mb-4">
                <span class="text-2xl">🌱</span>
              </div>
              <h3 class="text-xl font-semibold text-gray-800 mb-2">Sustainability</h3>
              <p class="text-gray-600">Creating lasting value through responsible investment practices</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Team Section -->
      <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <h2 class="text-3xl font-bold text-gray-800 text-center mb-12">Our Leadership Team</h2>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white rounded-lg shadow-lg p-6 text-center">
              <div class="h-24 w-24 rounded-full bg-blue-100 mx-auto mb-4"></div>
              <h3 class="text-xl font-semibold text-gray-800">John Mulenga</h3>
              <p class="text-gray-500">Chief Executive Officer</p>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 text-center">
              <div class="h-24 w-24 rounded-full bg-blue-100 mx-auto mb-4"></div>
              <h3 class="text-xl font-semibold text-gray-800">Sarah Banda</h3>
              <p class="text-gray-500">Chief Operations Officer</p>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 text-center">
              <div class="h-24 w-24 rounded-full bg-blue-100 mx-auto mb-4"></div>
              <h3 class="text-xl font-semibold text-gray-800">David Tembo</h3>
              <p class="text-gray-500">Chief Investment Officer</p>
            </div>
          </div>
        </div>
      </div>

      <Footer />
    </div>
  </template>

  <script>
  import Navigation from '@/components/custom/Navigation.vue';
  import Footer from '@/components/custom/Footer.vue';

  export default {
    components: {
      Navigation,
      Footer
    }
  }
  </script>
