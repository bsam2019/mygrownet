<script setup lang="ts">
import MemberLayout from '@/layouts/MemberLayout.vue';
import { Head } from '@inertiajs/vue3';

defineOptions({ layout: MemberLayout });

const breadcrumbs = [
    { label: 'Dashboard', href: '/dashboard' },
    { label: 'Compensation Plan', href: '/compensation-plan' },
];
</script>

<template>
    <Head title="Compensation Plan" />
    <MemberLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-7xl mx-auto space-y-8">
            <div class="text-center py-16 bg-gradient-to-br from-blue-50 to-green-50 rounded-lg">
                <h1 class="text-5xl font-bold text-blue-600 mb-4">MyGrowNet</h1>
                <h2 class="text-3xl font-semibold text-green-600 mb-6">Compensation Plan</h2>
                <p class="text-2xl text-gray-700">Your Path to Financial Freedom</p>
            </div>
            <div class="bg-blue-50 border-2 border-blue-600 rounded-lg p-6 text-center">
                <h3 class="text-xl font-semibold mb-3">View Complete Compensation Plan</h3>
                <a href="/compensation-plan.html" target="_blank" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    View Full Plan
                </a>
            </div>
        </div>
    </MemberLayout>
</template>
