<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import MemberLayout from '@/layouts/MemberLayout.vue';
import { formatCurrency } from '@/utils/formatting';

const page = usePage();

// Safe access to props with defaults
const matrixStructure = computed(() => page.props.matrixStructure || []);
const downlineCounts = computed(() => page.props.downlineCounts || {
    level_1: 0,
    level_2: 0,
    level_3: 0,
    total: 0
});
const referralStats = computed(() => page.props.referralStats || {
    total_referrals: 0,
    active_referrals: 0,
    total_commission: 0,
    pending_commission: 0
});
const referralCode = computed(() => page.props.referralCode || '');
const referralLink = computed(() => page.props.referralLink || '');
</script>

<template>
    <MemberLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Matrix & Referrals
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="space-y-8">
                    <!-- Debug Info -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Debug Information</h3>
                        <div class="space-y-2 text-sm">
                            <div><strong>Referral Code:</strong> {{ referralCode || 'Not set' }}</div>
                            <div><strong>Referral Link:</strong> {{ referralLink || 'Not set' }}</div>
                            <div><strong>Matrix Structure Length:</strong> {{ matrixStructure.length }}</div>
                            <div><strong>Total Referrals:</strong> {{ referralStats.total_referrals }}</div>
                            <div><strong>Active Referrals:</strong> {{ referralStats.active_referrals }}</div>
                            <div><strong>Level 1 Count:</strong> {{ downlineCounts.level_1 }}</div>
                            <div><strong>Level 2 Count:</strong> {{ downlineCounts.level_2 }}</div>
                            <div><strong>Level 3 Count:</strong> {{ downlineCounts.level_3 }}</div>
                            <div><strong>Total Count:</strong> {{ downlineCounts.total }}</div>
                        </div>
                    </div>

                    <!-- Stats Overview -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-gray-900">{{ referralStats.total_referrals }}</div>
                                <div class="text-sm text-gray-500">Total Referrals</div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-gray-900">{{ referralStats.active_referrals }}</div>
                                <div class="text-sm text-gray-500">Active Referrals</div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600">{{ formatCurrency(referralStats.total_commission) }}</div>
                                <div class="text-sm text-gray-500">Total Commission</div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-yellow-600">{{ formatCurrency(referralStats.pending_commission) }}</div>
                                <div class="text-sm text-gray-500">Pending Commission</div>
                            </div>
                        </div>
                    </div>

                    <!-- Referral Link -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Your Referral Link</h3>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <div class="text-sm text-gray-600 mb-2">Referral Code: {{ referralCode }}</div>
                            <div class="text-sm text-gray-900 font-mono">{{ referralLink }}</div>
                        </div>
                    </div>

                    <!-- Matrix Status -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Matrix Status</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                <div class="text-2xl font-bold text-gray-900">{{ downlineCounts.level_1 }}</div>
                                <div class="text-sm text-gray-500">Level 1</div>
                            </div>
                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                <div class="text-2xl font-bold text-gray-900">{{ downlineCounts.level_2 }}</div>
                                <div class="text-sm text-gray-500">Level 2</div>
                            </div>
                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                <div class="text-2xl font-bold text-gray-900">{{ downlineCounts.level_3 }}</div>
                                <div class="text-sm text-gray-500">Level 3</div>
                            </div>
                            <div class="text-center p-4 bg-primary-50 rounded-lg">
                                <div class="text-2xl font-bold text-primary-600">{{ downlineCounts.total }}</div>
                                <div class="text-sm text-primary-600">Total</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>