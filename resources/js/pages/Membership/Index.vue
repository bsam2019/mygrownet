<template>
    <div class="min-h-screen bg-gray-50">
        <Navigation />
        
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-4xl font-bold mb-4">Membership Tiers</h1>
                <p class="text-xl text-blue-100">Choose the membership level that's right for you</p>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6">
                <div v-for="tier in tiers" :key="tier.name" 
                     class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow">
                    <div class="text-center mb-6">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ tier.name }}</h3>
                        <div class="text-3xl font-bold text-blue-600 mb-4">K{{ tier.amount }}</div>
                        <p class="text-sm text-gray-600">{{ tier.returns }}% Annual Returns</p>
                    </div>
                    
                    <ul class="space-y-3 mb-6">
                        <li v-for="benefit in tier.benefits" :key="benefit" 
                            class="flex items-start text-sm text-gray-700">
                            <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            {{ benefit }}
                        </li>
                    </ul>
                    
                    <Link :href="route('register')" 
                          class="block w-full text-center bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        Get Started
                    </Link>
                </div>
            </div>

            <div class="mt-12 bg-blue-50 rounded-lg p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Why Choose MyGrowNet?</h2>
                <div class="grid md:grid-cols-3 gap-6">
                    <div>
                        <h3 class="font-semibold text-lg mb-2">Five-Level Commissions</h3>
                        <p class="text-gray-600">Earn from your network up to 5 levels deep</p>
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg mb-2">Asset Rewards</h3>
                        <p class="text-gray-600">Qualify for physical assets and income-generating rewards</p>
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg mb-2">Community Projects</h3>
                        <p class="text-gray-600">Invest in community development and earn returns</p>
                    </div>
                </div>
            </div>
        </div>

        <Footer />
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Navigation from '@/components/custom/Navigation.vue';
import Footer from '@/components/custom/Footer.vue';

const tiers = [
    {
        name: 'Bronze',
        amount: '500',
        returns: 3,
        benefits: ['Basic network access', 'Level 1-2 commissions', 'Monthly reports']
    },
    {
        name: 'Silver',
        amount: '2,000',
        returns: 5,
        benefits: ['Enhanced network', 'Level 1-3 commissions', 'Quarterly bonuses', 'Priority support']
    },
    {
        name: 'Gold',
        amount: '5,000',
        returns: 8,
        benefits: ['Premium network', 'Level 1-4 commissions', 'Asset eligibility', 'Project access', 'VIP support']
    },
    {
        name: 'Diamond',
        amount: '8,000',
        returns: 12,
        benefits: ['Elite network', 'Level 1-5 commissions', 'Premium assets', 'Priority projects', 'Dedicated manager']
    },
    {
        name: 'Elite',
        amount: '10,000',
        returns: 15,
        benefits: ['Exclusive network', 'Maximum commissions', 'Top-tier assets', 'Exclusive projects', 'Personal advisor']
    }
];
</script>
