<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import GrowBizLayout from '@/layouts/GrowBizLayout.vue';
import {
    ArrowLeftIcon,
    Cog6ToothIcon,
    ClockIcon,
    BellIcon,
    GlobeAltIcon,
    DocumentTextIcon,
    SwatchIcon,
} from '@heroicons/vue/24/outline';

interface Settings {
    business_name: string | null;
    booking_page_description: string | null;
    booking_page_slug: string | null;
    timezone: string;
    slot_duration_minutes: number;
    min_booking_notice_hours: number;
    max_booking_advance_days: number;
    require_approval: boolean;
    allow_cancellation: boolean;
    cancellation_notice_hours: number;
    send_confirmation_email: boolean;
    send_reminder_sms: boolean;
    send_reminder_whatsapp: boolean;
    reminder_timings: number[];
    cancellation_policy: string | null;
    primary_color: string;
}

const props = defineProps<{
    settings: Settings;
}>();

const isSubmitting = ref(false);

const form = ref({
    business_name: props.settings.business_name || '',
    booking_page_description: props.settings.booking_page_description || '',
    booking_page_slug: props.settings.booking_page_slug || '',
    timezone: props.settings.timezone || 'Africa/Lusaka',
    slot_duration_minutes: props.settings.slot_duration_minutes || 30,
    min_booking_notice_hours: props.settings.min_booking_notice_hours || 1,
    max_booking_advance_days: props.settings.max_booking_advance_days || 30,
    require_approval: props.settings.require_approval ?? false,
    allow_cancellation: props.settings.allow_cancellation ?? true,
    cancellation_notice_hours: props.settings.cancellation_notice_hours || 24,
    send_confirmation_email: props.settings.send_confirmation_email ?? true,
    send_reminder_sms: props.settings.send_reminder_sms ?? false,
    send_reminder_whatsapp: props.settings.send_reminder_whatsapp ?? false,
    cancellation_policy: props.settings.cancellation_policy || '',
    primary_color: props.settings.primary_color || '#10b981',
});

const colors = [
    '#10b981', '#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b',
    '#ef4444', '#06b6d4', '#84cc16', '#f97316', '#6366f1',
];

const timezones = [
    { value: 'Africa/Lusaka', label: 'Lusaka (CAT)' },
    { value: 'Africa/Johannesburg', label: 'Johannesburg (SAST)' },
    { value: 'Africa/Nairobi', label: 'Nairobi (EAT)' },
    { value: 'Africa/Lagos', label: 'Lagos (WAT)' },
    { value: 'UTC', label: 'UTC' },
];

const slotDurations = [
    { value: 15, label: '15 minutes' },
    { value: 30, label: '30 minutes' },
    { value: 45, label: '45 minutes' },
    { value: 60, label: '1 hour' },
];

const saveSettings = () => {
    if (isSubmitting.value) return;
    isSubmitting.value = true;

    router.post(route('growbiz.appointments.settings.save'), form.value, {
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};
</script>

<template>
    <GrowBizLayout>
        <div class="p-4 space-y-4">
            <!-- Header -->
            <div class="flex items-center gap-3">
                <Link :href="route('growbiz.appointments.index')" class="p-2 hover:bg-gray-100 rounded-lg" aria-label="Back to appointments">
                    <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                </Link>
                <div class="flex-1">
                    <h1 class="text-xl font-bold text-gray-900">Booking Settings</h1>
                    <p class="text-sm text-gray-500">Configure your appointment booking preferences</p>
                </div>
                <div class="p-2 bg-gray-100 rounded-lg">
                    <Cog6ToothIcon class="h-6 w-6 text-gray-600" aria-hidden="true" />
                </div>
            </div>

            <form @submit.prevent="saveSettings" class="space-y-6">
                <!-- Business Info -->
                <div class="bg-white rounded-xl p-4 border border-gray-100 space-y-4">
                    <div class="flex items-center gap-2 text-gray-700 font-medium">
                        <GlobeAltIcon class="h-5 w-5" aria-hidden="true" />
                        <span>Business Information</span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Business Name</label>
                        <input v-model="form.business_name" type="text" class="w-full border-gray-200 rounded-lg" placeholder="Your Business Name" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Booking Page Description</label>
                        <textarea v-model="form.booking_page_description" rows="2" class="w-full border-gray-200 rounded-lg" placeholder="Brief description for your booking page..."></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Booking Page URL Slug</label>
                        <div class="flex items-center">
                            <span class="text-sm text-gray-400 mr-1">book/</span>
                            <input v-model="form.booking_page_slug" type="text" class="flex-1 border-gray-200 rounded-lg" placeholder="your-business" />
                        </div>
                    </div>
                </div>

                <!-- Scheduling -->
                <div class="bg-white rounded-xl p-4 border border-gray-100 space-y-4">
                    <div class="flex items-center gap-2 text-gray-700 font-medium">
                        <ClockIcon class="h-5 w-5" aria-hidden="true" />
                        <span>Scheduling</span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Timezone</label>
                        <select v-model="form.timezone" class="w-full border-gray-200 rounded-lg">
                            <option v-for="tz in timezones" :key="tz.value" :value="tz.value">{{ tz.label }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Default Slot Duration</label>
                        <select v-model="form.slot_duration_minutes" class="w-full border-gray-200 rounded-lg">
                            <option v-for="d in slotDurations" :key="d.value" :value="d.value">{{ d.label }}</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Min Notice (hours)</label>
                            <input v-model.number="form.min_booking_notice_hours" type="number" min="0" max="168" class="w-full border-gray-200 rounded-lg" />
                            <p class="text-xs text-gray-400 mt-1">How far in advance customers must book</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Max Advance (days)</label>
                            <input v-model.number="form.max_booking_advance_days" type="number" min="1" max="365" class="w-full border-gray-200 rounded-lg" />
                            <p class="text-xs text-gray-400 mt-1">How far ahead customers can book</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <div>
                            <span class="text-sm font-medium text-gray-700">Require Approval</span>
                            <p class="text-xs text-gray-400">Manually approve each booking</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input v-model="form.require_approval" type="checkbox" class="sr-only peer" />
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                    </div>
                </div>

                <!-- Cancellation -->
                <div class="bg-white rounded-xl p-4 border border-gray-100 space-y-4">
                    <div class="flex items-center gap-2 text-gray-700 font-medium">
                        <DocumentTextIcon class="h-5 w-5" aria-hidden="true" />
                        <span>Cancellation Policy</span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <div>
                            <span class="text-sm font-medium text-gray-700">Allow Cancellation</span>
                            <p class="text-xs text-gray-400">Let customers cancel their bookings</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input v-model="form.allow_cancellation" type="checkbox" class="sr-only peer" />
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                    </div>
                    <div v-if="form.allow_cancellation">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cancellation Notice (hours)</label>
                        <input v-model.number="form.cancellation_notice_hours" type="number" min="0" max="168" class="w-full border-gray-200 rounded-lg" />
                        <p class="text-xs text-gray-400 mt-1">Minimum notice required for cancellation</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cancellation Policy Text</label>
                        <textarea v-model="form.cancellation_policy" rows="3" class="w-full border-gray-200 rounded-lg" placeholder="Your cancellation policy..."></textarea>
                    </div>
                </div>

                <!-- Notifications -->
                <div class="bg-white rounded-xl p-4 border border-gray-100 space-y-4">
                    <div class="flex items-center gap-2 text-gray-700 font-medium">
                        <BellIcon class="h-5 w-5" aria-hidden="true" />
                        <span>Notifications</span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <div>
                            <span class="text-sm font-medium text-gray-700">Email Confirmations</span>
                            <p class="text-xs text-gray-400">Send booking confirmation emails</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input v-model="form.send_confirmation_email" type="checkbox" class="sr-only peer" />
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <div>
                            <span class="text-sm font-medium text-gray-700">SMS Reminders</span>
                            <p class="text-xs text-gray-400">Send appointment reminders via SMS</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input v-model="form.send_reminder_sms" type="checkbox" class="sr-only peer" />
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <div>
                            <span class="text-sm font-medium text-gray-700">WhatsApp Reminders</span>
                            <p class="text-xs text-gray-400">Send reminders via WhatsApp</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input v-model="form.send_reminder_whatsapp" type="checkbox" class="sr-only peer" />
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                    </div>
                </div>

                <!-- Appearance -->
                <div class="bg-white rounded-xl p-4 border border-gray-100 space-y-4">
                    <div class="flex items-center gap-2 text-gray-700 font-medium">
                        <SwatchIcon class="h-5 w-5" aria-hidden="true" />
                        <span>Appearance</span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Primary Color</label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="color in colors"
                                :key="color"
                                type="button"
                                @click="form.primary_color = color"
                                class="w-8 h-8 rounded-full border-2 transition-all"
                                :class="form.primary_color === color ? 'border-gray-900 scale-110' : 'border-transparent'"
                                :style="{ backgroundColor: color }"
                                :aria-label="`Select color ${color}`"
                            ></button>
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <button
                    type="submit"
                    :disabled="isSubmitting"
                    class="w-full py-3 bg-emerald-600 text-white font-medium rounded-xl hover:bg-emerald-700 disabled:opacity-50"
                >
                    {{ isSubmitting ? 'Saving...' : 'Save Settings' }}
                </button>
            </form>
        </div>
    </GrowBizLayout>
</template>
