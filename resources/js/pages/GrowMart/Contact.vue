<template>
    <div class="max-w-3xl mx-auto px-4 py-12">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Contact Us</h1>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
            <div class="flex items-start gap-3">
                <MapPinIcon class="h-6 w-6 text-emerald-600 mt-0.5 shrink-0" />
                <div>
                    <h3 class="font-medium text-gray-900">Address</h3>
                    <p class="text-gray-600">{{ contact.address }}</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <PhoneIcon class="h-6 w-6 text-emerald-600 mt-0.5 shrink-0" />
                <div>
                    <h3 class="font-medium text-gray-900">Phone</h3>
                    <p class="text-gray-600">{{ contact.phone }}</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <EnvelopeIcon class="h-6 w-6 text-emerald-600 mt-0.5 shrink-0" />
                <div>
                    <h3 class="font-medium text-gray-900">Email</h3>
                    <p class="text-gray-600">{{ contact.email }}</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <ClockIcon class="h-6 w-6 text-emerald-600 mt-0.5 shrink-0" />
                <div>
                    <h3 class="font-medium text-gray-900">Business Hours</h3>
                    <p class="text-gray-600">Monday – Saturday: 8:00 AM – 6:00 PM</p>
                    <p class="text-gray-600">Sunday: 9:00 AM – 3:00 PM</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { MapPinIcon, PhoneIcon, EnvelopeIcon, ClockIcon } from '@heroicons/vue/24/outline';

defineProps<{
    contact: {
        email: string;
        phone: string;
        address: string;
        social?: {
            facebook?: string;
            instagram?: string;
        };
    };
}>();
</script>
